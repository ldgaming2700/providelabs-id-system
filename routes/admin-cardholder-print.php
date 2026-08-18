<?php

use App\Http\Controllers\Admin\CardholderBatchPrintController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post(
            '/cardholders/batch-print/{side}',
            [CardholderBatchPrintController::class, 'create']
        )
            ->whereIn('side', ['front', 'back'])
            ->name('cardholders.batch-print');
    });
