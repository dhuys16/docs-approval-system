<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Ambil role secara aman (fallback ke kolom 'role' jika Spatie belum terpasang)
            $role = 'pemohon';
            if (method_exists($user, 'getRoleNames') && $user->getRoleNames()->first()) {
                $role = $user->getRoleNames()->first();
            } elseif (isset($user->role)) {
                $role = $user->role;
            }

            $cacheKey = "dashboard_stats_{$role}_{$user->id}";

            $stats = Cache::remember($cacheKey, 300, function () use ($user, $role) {
                $query = Permohonan::query();

                // Pemohon hanya melihat data miliknya
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

        } catch (Throwable $e) {
            // Jika ada error internal, kembalikan pesan jelas untuk debugging
            return response()->json([
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}