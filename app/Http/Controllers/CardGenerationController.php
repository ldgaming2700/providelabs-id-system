<?php

namespace App\Http\Controllers;

use App\Models\Cardholder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CardGenerationController extends Controller
{
    public function show(Cardholder $cardholder): View
    {
        abort_unless(
            Auth::user()?->role === 'admin' || $cardholder->registered_by === Auth::id(),
            403
        );

        $cardholder->load('cardType');

        $birthday = $cardholder->birthday
            ? Carbon::parse($cardholder->birthday)
            : null;

        $cardTypeName = $cardholder->cardType?->name ?? 'Senior Citizen Card';

        $payload = [
            'id' => $cardholder->id,
            'id_no' => $cardholder->id_no,
            'name' => $cardholder->name,
            'sc_id' => $cardholder->sc_id,
            'philhealth' => $cardholder->philhealth,
            'cellphone_no' => $cardholder->cellphone_no,
            'address' => $cardholder->address,
            'position' => $cardholder->position,
            'birthday' => $birthday ? $birthday->format('m/d/Y') : '',
            'age' => $birthday ? $birthday->age : '',
            'contact_name' => $cardholder->contact_name,
            'emergency_contact_number' => $cardholder->emergency_contact_number,
            'relationship' => $cardholder->relationship,
            'photo_url' => $cardholder->photo_url,
            'card_type' => $cardTypeName,
            'card_type_slug' => Str::slug($cardTypeName),
        ];

        return view('cardholders.generate', [
            'cardholder' => $cardholder,
            'payload' => $payload,
        ]);
    }
}