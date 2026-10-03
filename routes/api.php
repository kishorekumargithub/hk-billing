<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsageController;
use App\Http\Controllers\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['merchant', 'throttle:usage'])->group(function () {
    Route::post('/usage', [UsageController::class, 'store']);
});

Route::middleware('merchant')->get('/merchants/{id}/dashboard', [DashboardController::class, 'show']);
