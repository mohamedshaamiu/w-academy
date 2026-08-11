<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ChildrenController;
use App\Http\Controllers\Api\V1\FrameworkController;
use App\Http\Controllers\Api\V1\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::get('children', [ChildrenController::class, 'index'])->name('children');
        Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule');
        Route::get('framework', [FrameworkController::class, 'index'])->name('framework');
    });
});
