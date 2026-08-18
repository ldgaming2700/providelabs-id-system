<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cardholder;
use App\Models\CardType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CardholderManagementController extends Controller
{
    private const STATUS_OPTIONS = [
        'pending' => 'Pending',
        'generated' => 'Generated',
        'printed' => 'Printed',
        'released' => 'Released',
    ];

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $allowedSorts = [
            'id_no',
            'name',
            'status',
            'position',
            'birthday',
            'created_at',
        ];

        $requestedSort = $request->string('sort')->toString();

        $sort = in_array($requestedSort, $allowedSorts, true)
            ? $requestedSort
            : 'id_no';

        $direction = $request->string('direction')->lower()->toString() === 'desc'
            ? 'desc'
            : 'asc';

        $query = Cardholder::query();

        $search = trim($request->string('search')->toString());

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('id_no', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('cellphone_no', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");

                if (Schema::hasColumn('cardholders', 'school')) {
                    $builder->orWhere('school', 'like', "%{$search}%");
                }
            });
        }

        $status = $request->string('status')->toString();

        if ($status !== '' && array_key_exists($status, self::STATUS_OPTIONS)) {
            $query->where('status', $status);
        }

        $cardTypeId = $request->integer('card_type_id');

        if ($cardTypeId > 0) {
            $query->where('card_type_id', $cardTypeId);
        }

        $cardholders = $query
            ->orderBy($sort, $direction)
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        $cardTypes = CardType::query()
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('admin.cardholders.index', [
            'cardholders' => $cardholders,
            'cardTypes' => $cardTypes,
            'statusOptions' => self::STATUS_OPTIONS,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function exportCsv(): StreamedResponse
    {
        $this->ensureAdmin();

        $filename = 'cardholders_' . now()->format('Y-m-d_His') . '.csv';

        $cardTypes = CardType::query()->pluck('name', 'id');
        $hasSchool = Schema::hasColumn('cardholders', 'school');

        return response()->streamDownload(function () use ($cardTypes, $hasSchool) {
            $output = fopen('php://output', 'w');

            // Helps Microsoft Excel detect UTF-8 correctly.
            fwrite($output, "\xEF\xBB\xBF");

            $headers = [
                'ID NO',
                'CARD TYPE',
                'NAME',
                'SC ID',
                'PHILHEALTH',
                'CONTACT NO',
                'POSITION',
            ];

            if ($hasSchool) {
                $headers[] = 'SCHOOL';
            }

            $headers = array_merge($headers, [
                'ADDRESS',
                'BIRTHDATE',
                'AGE',
                'CONTACT PERSON',
                'EMERGENCY NO',
                'RELATIONSHIP',
                'STATUS',
                'DATE CREATED',
                'DATE GENERATED',
                'DATE PRINTED',
                'DATE RELEASED',
            ]);

            fputcsv($output, $headers);

            foreach (
                Cardholder::query()
                    ->orderBy('id_no')
                    ->orderBy('id')
                    ->cursor() as $cardholder
            ) {
                $birthday = $cardholder->birthday
                    ? Carbon::parse($cardholder->birthday)
                    : null;

                $row = [
                    $cardholder->id_no,
                    $cardTypes[$cardholder->card_type_id] ?? '',
                    $cardholder->name,
                    $cardholder->sc_id,
                    $cardholder->philhealth,
                    $cardholder->cellphone_no,
                    $cardholder->position,
                ];

                if ($hasSchool) {
                    $row[] = $cardholder->school;
                }

                $row = array_merge($row, [
                    $cardholder->address,
                    $birthday?->format('Y-m-d') ?? '',
                    $birthday?->age ?? '',
                    $cardholder->contact_name,
                    $cardholder->emergency_contact_number,
                    $cardholder->relationship,
                    $cardholder->status,
                    $this->dateTimeForCsv($cardholder->created_at),
                    $this->dateTimeForCsv($cardholder->generated_at),
                    $this->dateTimeForCsv($cardholder->printed_at),
                    $this->dateTimeForCsv($cardholder->released_at),
                ]);

                fputcsv($output, $row);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    public function batchStatus(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'cardholder_ids' => ['required', 'array', 'min:1'],
            'cardholder_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:cardholders,id',
            ],
            'status' => [
                'required',
                Rule::in(array_keys(self::STATUS_OPTIONS)),
            ],
        ]);

        $cardholders = Cardholder::query()
            ->whereIn('id', $validated['cardholder_ids'])
            ->get();

        foreach ($cardholders as $cardholder) {
            $cardholder->status = $validated['status'];

            // Preserve existing workflow timestamps.
            if ($validated['status'] === 'generated' && ! $cardholder->generated_at) {
                $cardholder->generated_at = now();
            }

            if ($validated['status'] === 'printed' && ! $cardholder->printed_at) {
                $cardholder->printed_at = now();
            }

            if ($validated['status'] === 'released' && ! $cardholder->released_at) {
                $cardholder->released_at = now();
            }

            $cardholder->save();
        }

        return back()->with(
            'success',
            $cardholders->count()
                . ' cardholder record(s) updated to '
                . self::STATUS_OPTIONS[$validated['status']]
                . '.'
        );
    }

    private function ensureAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()?->role === 'admin',
            403
        );
    }

    private function dateTimeForCsv($value): string
    {
        return $value
            ? Carbon::parse($value)->format('Y-m-d H:i:s')
            : '';
    }
}
