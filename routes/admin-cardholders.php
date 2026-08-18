<?php

use App\Http\Controllers\Admin\CardholderManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get(
            '/cardholders/manage',
            [CardholderManagementController::class, 'index']
        )->name('cardholders.manage');

        Route::get(
            '/cardholders/export-csv',
            [CardholderManagementController::class, 'exportCsv']
        )->name('cardholders.export-csv');

        Route::post(
            '/cardholders/batch-status',
            [CardholderManagementController::class, 'batchStatus']
        )->name('cardholders.batch-status');
    });
