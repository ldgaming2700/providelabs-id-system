<?php

namespace App\Http\Controllers;

use App\Models\Cardholder;
use Illuminate\View\View;

class CardGenerationController extends Controller
{
    public function show(Cardholder $cardholder): View
    {
        $cardholder->load('cardType');

        return view('cardholders.generate', ['cardholder' => $cardholder]);
    }
}
