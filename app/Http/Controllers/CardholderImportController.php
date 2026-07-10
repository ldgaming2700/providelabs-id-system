<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cardholder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class CardholderImportController extends Controller
{
    public function create(): View
    {
        abort_unless($this->isAdmin(), 403);

        return view('cardholders.import');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
            'photos_zip' => ['nullable', 'file', 'mimes:zip'],
            'card_type_id' => ['required', 'exists:card_types,id'],
            'mode' => ['required', 'in:skip,update'],
        ]);

        $csvPath = $request->file('csv_file')->getRealPath();

        $photoMap = [];

        if ($request->hasFile('photos_zip')) {
            $photoMap = $this->extractPhotosFromZip($request->file('photos_zip')->getRealPath());
        }

        $rows = $this->readCsv($csvPath);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $missingPhotos = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $idNo = $this->value($row, 'ID NO');

                if (! $idNo) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Missing ID NO.";
                    continue;
                }

                $existing = Cardholder::where('id_no', $idNo)->first();

                if ($existing && $request->mode === 'skip') {
                    $skipped++;
                    continue;
                }

                $data = [
                    'card_type_id' => $request->integer('card_type_id'),
                    'registered_by' => Auth::id(),
                    'id_no' => $idNo,
                    'name' => $this->value($row, 'NAME'),
                    'sc_id' => $this->value($row, 'SC ID'),
                    'philhealth' => $this->value($row, 'PHILHEALTH'),
                    'cellphone_no' => $this->value($row, 'CELLPHONE NO'),
                    'address' => $this->value($row, 'ADDRESS'),
                    'position' => $this->value($row, 'POSITION'),
                    'birthday' => $this->parseBirthday($this->value($row, 'BIRTHDAY')),
                    'contact_name' => $this->value($row, 'CONTACT NAME'),
                    'emergency_contact_number' => $this->value($row, 'EMERGENCY CONTACT NUMBER'),
                    'relationship' => $this->value($row, 'RELATIONSHIP'),
                    'status' => 'pending',
                ];

                if (! $data['name']) {
                    $skipped++;
                    $errors[] = "Row {$rowNumber}: Missing NAME for ID {$idNo}.";
                    continue;
                }

                if ($existing) {
                    $existing->update($data);
                    $cardholder = $existing;
                    $updated++;
                } else {
                    $cardholder = Cardholder::create($data + [
                        'photo_status' => 'placeholder',
                    ]);

                    $created++;
                }

                if (isset($photoMap[$idNo])) {
                    $storedPhotoPath = $this->storeImportedPhoto($idNo, $photoMap[$idNo]);

                    if ($storedPhotoPath) {
                        $cardholder->update([
                            'photo_path' => $storedPhotoPath,
                            'photo_status' => 'uploaded',
                        ]);
                    }
                } else {
                    $missingPhotos++;

                    if (! $cardholder->photo_path) {
                        $cardholder->update([
                            'photo_status' => 'placeholder',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Row {$rowNumber}: " . $e->getMessage();
            }
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'cardholders.imported',
            'metadata' => [
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'missing_photos' => $missingPhotos,
                'errors' => $errors,
            ],
        ]);

        return redirect()
            ->route('cardholders.index')
            ->with('success', "Import completed. Created: {$created}. Updated: {$updated}. Skipped: {$skipped}. Missing photos: {$missingPhotos}.");
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            throw new \RuntimeException('Unable to open CSV file.');
        }

        $headers = fgetcsv($handle);

        if (! $headers) {
            fclose($handle);
            return [];
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader($header), $headers);

        $rows = [];

        while (($data = fgetcsv($handle)) !== false) {
            if ($this->isEmptyRow($data)) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = $data[$index] ?? '';
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    private function extractPhotosFromZip(string $zipPath): array
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Unable to open photos ZIP file.');
        }

        $photoMap = [];
        $tempRoot = storage_path('app/import-temp/' . Str::uuid());

        if (! is_dir($tempRoot)) {
            mkdir($tempRoot, 0775, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entryName = $zip->getNameIndex($i);

            if (str_ends_with($entryName, '/')) {
                continue;
            }

            $baseName = basename($entryName);
            $extension = strtolower(pathinfo($baseName, PATHINFO_EXTENSION));

            if (! in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
                continue;
            }

            $idNo = pathinfo($baseName, PATHINFO_FILENAME);
            $targetPath = $tempRoot . '/' . $baseName;

            copy("zip://{$zipPath}#{$entryName}", $targetPath);

            $photoMap[$idNo] = $targetPath;
        }

        $zip->close();

        return $photoMap;
    }

    private function storeImportedPhoto(string $idNo, string $localPhotoPath): ?string
    {
        if (! file_exists($localPhotoPath)) {
            return null;
        }

        $extension = strtolower(pathinfo($localPhotoPath, PATHINFO_EXTENSION)) ?: 'jpg';
        $storagePath = 'cardholder-photos/' . $idNo . '.' . $extension;

        Storage::disk(config('filesystems.default'))->put(
            $storagePath,
            file_get_contents($localPhotoPath)
        );

        return $storagePath;
    }

    private function parseBirthday(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $formats = [
            'm/d/Y',
            'm-d-Y',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);

            if ($date) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function value(array $row, string $key): ?string
    {
        $value = $row[$this->normalizeHeader($key)] ?? null;

        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }

    private function normalizeHeader(?string $header): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) $header)));
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isAdmin(): bool
    {
        return Auth::user()?->role === 'admin';
    }
}