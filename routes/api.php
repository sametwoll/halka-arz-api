<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IpoController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('ipos')->group(function () {
    Route::get('/', [IpoController::class, 'index']); // Tüm liste: /api/ipos
    Route::get('/upcoming', [IpoController::class, 'upcoming']); // Yaklaşanlar: /api/ipos/upcoming
    Route::get('/active', [IpoController::class, 'active']); // Aktifler: /api/ipos/active
});