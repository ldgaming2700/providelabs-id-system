<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CardGenerationController;
use App\Http\Controllers\CardholderController;
use App\Http\Controllers\CardholderImportController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/cardholders/check-name', [CardholderController::class, 'checkName'])
        ->name('cardholders.check-name');

    Route::get('/cardholders-import', [CardholderImportController::class, 'create'])
        ->name('cardholders.import');

    Route::post('/cardholders-import', [CardholderImportController::class, 'store'])
        ->name('cardholders.import.store');

    Route::get('/cardholders/{cardholder}/photo', [CardholderController::class, 'photo'])
        ->name('cardholders.photo');

    Route::get('/cardholders/{cardholder}/generate', [CardGenerationController::class, 'show'])
        ->name('cardholders.generate');

    Route::post('/cardholders/{cardholder}/mark-generated', [CardholderController::class, 'markGenerated'])
        ->name('cardholders.mark-generated');

    Route::post('/cardholders/{cardholder}/mark-printed', [CardholderController::class, 'markPrinted'])
        ->name('cardholders.mark-printed');

    Route::post('/cardholders/{cardholder}/mark-released', [CardholderController::class, 'markReleased'])
        ->name('cardholders.mark-released');

    Route::post('/cardholders/{cardholder}/mark-for-correction', [CardholderController::class, 'markForCorrection'])
        ->name('cardholders.mark-for-correction');

    Route::resource('cardholders', CardholderController::class);
});