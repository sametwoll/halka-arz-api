<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ipo;
use Illuminate\Http\Request;

class IpoController extends Controller
{
    // Tüm halka arzları listeler
    public function index()
    {
        $ipos = Ipo::orderBy('created_at', 'desc')->get()->map(function($ipo) {
            // Tahmin Algoritması: 2M, 3M ve 4M katılımcı senaryoları
            $ipo->estimations = [
                '2m_participants' => $ipo->total_lots > 0 ? floor($ipo->total_lots / 2000000) : 0,
                '3m_participants' => $ipo->total_lots > 0 ? floor($ipo->total_lots / 3000000) : 0,
                '4m_participants' => $ipo->total_lots > 0 ? floor($ipo->total_lots / 4000000) : 0,
            ];
            return $ipo;
        });

        return response()->json([
            'success' => true,
            'data' => $ipos
        ], 200);
    }

    // Sadece "Yaklaşan" (Talep toplaması henüz başlamamış) halka arzları listeler
    public function upcoming()
    {
        $ipos = Ipo::where('status', 'upcoming')->orderBy('start_date', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => $ipos
        ], 200);
    }

    // Şu an "Talep Toplanan" (Aktif) halka arzları listeler
    public function active()
    {
        $ipos = Ipo::where('status', 'active')->get();
        return response()->json([
            'success' => true,
            'data' => $ipos
        ], 200);
    }
}