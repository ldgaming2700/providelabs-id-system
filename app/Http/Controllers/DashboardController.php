<?php

namespace App\Http\Controllers;

use App\Models\Cardholder;
use App\Models\CardType;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('cardholders.create');
        }

        $totalRecords = Cardholder::count();

        $encoderStats = Cardholder::query()
            ->with('encoder')
            ->select('registered_by', DB::raw('COUNT(*) as entries_count'))
            ->groupBy('registered_by')
            ->orderByDesc('entries_count')
            ->get()
            ->map(function ($row) use ($totalRecords) {
                $count = (int) $row->entries_count;

                return [
                    'name' => $row->encoder->name ?? 'Unknown Encoder',
                    'count' => $count,
                    'percentage' => $totalRecords > 0
                        ? round(($count / $totalRecords) * 100, 1)
                        : 0,
                ];
            });

        return view('dashboard', [
            'totalRecords' => $totalRecords,
            'pending' => Cardholder::where('status', 'pending')->count(),
            'generated' => Cardholder::where('status', 'generated')->count(),
            'printed' => Cardholder::where('status', 'printed')->count(),
            'released' => Cardholder::where('status', 'released')->count(),
            'placeholders' => Cardholder::where('photo_status', 'placeholder')->count(),
            'cardTypes' => CardType::withCount('cardholders')->orderBy('name')->get(),
            'recentCardholders' => Cardholder::with('cardType')->latest()->limit(8)->get(),
            'encoderStats' => $encoderStats,
        ]);
    }
}