<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ForcePasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('password/change', [ForcePasswordChangeController::class, 'edit'])->name('password.change');
    Route::post('password/change', [ForcePasswordChangeController::class, 'update'])->name('password.change.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
