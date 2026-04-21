<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IpoController;
use Illuminate\Support\Facades\Artisan;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('ipos')->group(function () {
    Route::get('/', [IpoController::class, 'index']); // Tüm liste: /api/ipos
    Route::get('/upcoming', [IpoController::class, 'upcoming']); // Yaklaşanlar: /api/ipos/upcoming
    Route::get('/active', [IpoController::class, 'active']); // Aktifler: /api/ipos/active
});

// Botu uzaktan tetiklemek için gizli URL
Route::get('/botu-calistir', function () {
    try {
        // Komutun adı senin sistemindeki botun adıdır (app:scrape-ipos gibi)
        Artisan::call('app:scrape-ipos'); 
        return response()->json([
            'success' => true,
            'message' => 'Basgan, bot calisti ve veritabanina veriler basariyla yazildi!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Bot calisirken hata olustu: ' . $e->getMessage()
        ]);
    }
});