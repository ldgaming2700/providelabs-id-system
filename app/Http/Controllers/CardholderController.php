<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Cardholder;
use App\Models\CardType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;


class CardholderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Cardholder::query()->with(['cardType', 'encoder']);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('id_no', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('sc_id', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($cardTypeId = $request->integer('card_type_id')) {
            $query->where('card_type_id', $cardTypeId);
        }

        return view('cardholders.index', [
            'cardholders' => $query->latest()->paginate(15)->withQueryString(),
            'cardTypes' => CardType::where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(): View
    {
        return view('cardholders.create', [
            'cardholder' => new Cardholder(),
            'cardTypes' => CardType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
    
    public function photo(Cardholder $cardholder): Response
    {
        abort_unless($cardholder->photo_path, 404);
    
        $disk = Storage::disk(config('filesystems.default'));
    
        abort_unless($disk->exists($cardholder->photo_path), 404);
    
        return response($disk->get($cardholder->photo_path), 200)
            ->header('Content-Type', $disk->mimeType($cardholder->photo_path) ?: 'image/jpeg')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['registered_by'] = Auth::id();
        $data['photo_status'] = 'placeholder';

        $cardholder = DB::transaction(function () use ($data) {
        $data['id_no'] = $this->generateNextIdNo();

        return Cardholder::create($data);
        });

$this->savePhotoIfPresent($request, $cardholder);

        AuditLog::create([
            'user_id' => Auth::id(),
            'cardholder_id' => $cardholder->id,
            'action' => 'cardholder.created',
            'metadata' => ['id_no' => $cardholder->id_no],
        ]);

        return redirect()->route('cardholders.show', $cardholder)->with('success', 'Cardholder record created.');
    }

    public function show(Cardholder $cardholder): View
    {
        $cardholder->load('cardType', 'encoder');
        return view('cardholders.show', ['cardholder' => $cardholder]);
    }

    public function edit(Cardholder $cardholder): View
    {
        return view('cardholders.edit', [
            'cardholder' => $cardholder,
            'cardTypes' => CardType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Cardholder $cardholder): RedirectResponse
    {
        $data = $this->validatedData($request, $cardholder);
        $cardholder->update($data);
        $this->savePhotoIfPresent($request, $cardholder);

        AuditLog::create([
            'user_id' => Auth::id(),
            'cardholder_id' => $cardholder->id,
            'action' => 'cardholder.updated',
            'metadata' => ['id_no' => $cardholder->id_no],
        ]);

        return redirect()->route('cardholders.show', $cardholder)->with('success', 'Cardholder record updated.');
    }

    public function markGenerated(Cardholder $cardholder): RedirectResponse
    {
        return $this->markStatus($cardholder, 'generated', 'generated_at', 'ID marked as generated.');
    }

    public function markPrinted(Cardholder $cardholder): RedirectResponse
    {
        return $this->markStatus($cardholder, 'printed', 'printed_at', 'ID marked as printed.');
    }

    public function markReleased(Cardholder $cardholder): RedirectResponse
    {
        return $this->markStatus($cardholder, 'released', 'released_at', 'ID marked as released.');
    }

    public function markForCorrection(Cardholder $cardholder): RedirectResponse
    {
        $cardholder->update(['status' => 'for_correction']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'cardholder_id' => $cardholder->id,
            'action' => 'cardholder.for_correction',
            'metadata' => ['id_no' => $cardholder->id_no],
        ]);

        return back()->with('success', 'ID marked for correction.');
    }

    private function markStatus(Cardholder $cardholder, string $status, string $timestampColumn, string $message): RedirectResponse
    {
        $cardholder->update([
            'status' => $status,
            $timestampColumn => now(),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'cardholder_id' => $cardholder->id,
            'action' => "cardholder.{$status}",
            'metadata' => ['id_no' => $cardholder->id_no],
        ]);

        return back()->with('success', $message);
    }

    private function validatedData(Request $request, ?Cardholder $cardholder = null): array
    {
        return $request->validate([
            'card_type_id' => ['required', 'exists:card_types,id'],
            'name' => ['required', 'string', 'max:255'],
            'sc_id' => ['nullable', 'string', 'max:100'],
            'philhealth' => ['nullable', 'string', 'max:100'],
            'cellphone_no' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:1000'],
            'position' => ['nullable', 'string', 'max:100'],
            'birthday' => ['nullable', 'date'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_number' => ['nullable', 'string', 'max:50'],
            'relationship' => ['nullable', 'string', 'max:100'],
            'photo_upload' => ['nullable', 'image', 'max:5120'],
            'captured_photo' => ['nullable', 'string'],
        ]);
    }

    private function savePhotoIfPresent(Request $request, Cardholder $cardholder): void
    {
        $path = null;

        if ($request->filled('captured_photo')) {
            $path = $this->saveCapturedPhoto($request->input('captured_photo'), $cardholder->id_no);
        } elseif ($request->hasFile('photo_upload')) {
            $extension = $request->file('photo_upload')->extension() ?: 'jpg';
            $path = $request->file('photo_upload')->storeAs(
                'cardholder-photos',
                $cardholder->id_no . '.' . $extension,
                config('filesystems.default')
            );
        }

        if ($path) {
            if ($cardholder->photo_path && $cardholder->photo_path !== $path) {
                Storage::disk(config('filesystems.default'))->delete($cardholder->photo_path);
            }

            $cardholder->update([
                'photo_path' => $path,
                'photo_status' => 'uploaded',
            ]);
        }
    }

    private function saveCapturedPhoto(string $dataUrl, string $idNo): ?string
    {
        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $dataUrl)) {
            return null;
        }

        $image = substr($dataUrl, strpos($dataUrl, ',') + 1);
        $binary = base64_decode($image, true);

        if ($binary === false) {
            return null;
        }

        $path = 'cardholder-photos/' . $idNo . '.jpg';
        Storage::disk(config('filesystems.default'))->put($path, $binary);

        return $path;
    }

    private function statuses(): array
    {
        return [
            'pending' => 'Pending',
            'generated' => 'Generated',
            'printed' => 'Printed',
            'released' => 'Released',
            'for_correction' => 'For Correction',
        ];
    }

    private function generateNextIdNo(): string
{
    $prefix = now()->format('y');

    $latestNumber = Cardholder::query()
        ->where('id_no', 'like', $prefix . '-%')
        ->lockForUpdate()
        ->get(['id_no'])
        ->map(function (Cardholder $cardholder) {
            if (preg_match('/^\d{2}-(\d{5})$/', $cardholder->id_no, $matches)) {
                return (int) $matches[1];
            }

            return 0;
        })
        ->max();

    $nextNumber = ((int) $latestNumber) + 1;

    return $prefix . '-' . str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
}

    public function checkName(Request $request): JsonResponse
    {
        $name = trim((string) $request->query('name', ''));
        $excludeId = $request->integer('exclude');

        if (strlen($name) < 3) {
            return response()->json([
                'match' => false,
                'matches' => [],
            ]);
        }

        $normalizedName = strtoupper(preg_replace('/\s+/', ' ', $name));
        $tokens = collect(explode(' ', $normalizedName))
            ->filter(fn ($token) => strlen($token) >= 3)
            ->take(4)
            ->values();

        $matches = Cardholder::query()
            ->select('id', 'id_no', 'name')
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where(function ($query) use ($normalizedName, $tokens) {
                $query->whereRaw('UPPER(name) = ?', [$normalizedName]);

                foreach ($tokens as $token) {
                    $query->orWhereRaw('UPPER(name) LIKE ?', ['%' . $token . '%']);
                }
            })
            ->limit(5)
            ->get();

        return response()->json([
            'match' => $matches->isNotEmpty(),
            'matches' => $matches,
        ]);
    }

    public function destroy(Cardholder $cardholder): RedirectResponse
    {
        abort_unless(Auth::user()?->role === 'admin', 403);

        $idNo = $cardholder->id_no;
        $photoPath = $cardholder->photo_path;

        AuditLog::create([
            'user_id' => Auth::id(),
            'cardholder_id' => $cardholder->id,
            'action' => 'cardholder.deleted',
            'metadata' => [
                'id_no' => $idNo,
                'name' => $cardholder->name,
            ],
        ]);

        $cardholder->delete();

        if ($photoPath) {
            Storage::disk(config('filesystems.default'))->delete($photoPath);
        }

        return redirect()
            ->route('cardholders.index')
            ->with('success', "Cardholder {$idNo} has been deleted.");
    }
}
