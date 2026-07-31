<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->getRoleNames()->first(); // Cek apakah dia pemohon atau penilai
        
        // Buat nama key cache yang unik berdasarkan role dan user ID
        $cacheKey = "dashboard_stats_{$role}_{$user->id}";
        
        // Simpan hasil hitungan ke Cache selama 5 menit (300 detik)
        $stats = Cache::remember($cacheKey, 300, function () use ($user, $role) {
            $query = Permohonan::query();
            
            // Jika yang login pemohon, hitung miliknya saja. Jika penilai, hitung semua.
            if ($role === 'pemohon') {
                $query->where('pemohon_id', $user->id);
            }

            return [
                'total' => (clone $query)->count(),
                'draft' => (clone $query)->where('status', 'draft')->count(),
                'submitted' => (clone $query)->where('status', 'submitted')->count(),
                'revisi' => (clone $query)->where('status', 'revisi')->count(),
                'approved' => (clone $query)->where('status', 'approved')->count(),
                'rejected' => (clone $query)->where('status', 'rejected')->count(),
            ];
        });

        return response()->json([
            'message' => 'Statistik Dashboard berhasil diambil',
            'role' => $role,
            'statistics' => $stats
        ]);
    }
}