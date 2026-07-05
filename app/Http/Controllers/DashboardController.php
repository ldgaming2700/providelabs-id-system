<?php

namespace App\Http\Controllers;

use App\Models\Cardholder;
use App\Models\CardType;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'totalRecords' => Cardholder::count(),
            'pending' => Cardholder::where('status', 'pending')->count(),
            'generated' => Cardholder::where('status', 'generated')->count(),
            'printed' => Cardholder::where('status', 'printed')->count(),
            'released' => Cardholder::where('status', 'released')->count(),
            'placeholders' => Cardholder::where('photo_status', 'placeholder')->count(),
            'cardTypes' => CardType::withCount('cardholders')->orderBy('name')->get(),
            'recentCardholders' => Cardholder::with('cardType')->latest()->limit(8)->get(),
        ]);
    }
}
