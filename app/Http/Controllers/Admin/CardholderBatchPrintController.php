<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cardholder;
use Illuminate\Http\Request;

class CardholderBatchPrintController extends Controller
{
    public function create(Request $request, string $side)
    {
        $this->ensureAdmin();

        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $validated = $request->validate([
            'cardholder_ids' => ['required', 'array', 'min:1', 'max:500'],
            'cardholder_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:cardholders,id',
            ],
        ]);

        $requestedIds = array_map('intval', $validated['cardholder_ids']);

        $cardholdersById = Cardholder::query()
            ->with('cardType')
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $cardholders = collect($requestedIds)
            ->map(fn (int $id) => $cardholdersById->get($id))
            ->filter()
            ->values();

        $payload = $cardholders->map(function (Cardholder $cardholder) {
            $birthday = $cardholder->birthday
                ? \Carbon\Carbon::parse($cardholder->birthday)
                : null;

            return [
                'id' => $cardholder->id,
                'id_no' => $cardholder->id_no ?? '',
                'name' => $cardholder->name ?? '',
                'sc_id' => $cardholder->sc_id ?? '',
                'philhealth' => $cardholder->philhealth ?? '',
                'cellphone_no' => $cardholder->cellphone_no ?? '',
                'address' => $cardholder->address ?? '',
                'position' => $cardholder->position ?? '',
                'birthday' => $birthday?->format('m/d/Y') ?? '',
                'age' => $birthday?->age ?? '',
                'contact_name' => $cardholder->contact_name ?? '',
                'emergency_contact_number' => $cardholder->emergency_contact_number ?? '',
                'relationship' => $cardholder->relationship ?? '',
                'photo_url' => $cardholder->photo_url,
                'card_type' => [
                    'name' => $cardholder->cardType?->name ?? '',
                    'slug' => $cardholder->cardType?->slug ?? '',
                ],
            ];
        })->values();

        return view('admin.cardholders.batch-print', [
            'side' => $side,
            'cardholders' => $cardholders,
            'payload' => $payload,
        ]);
    }

    private function ensureAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()?->role === 'admin',
            403
        );
    }
}
