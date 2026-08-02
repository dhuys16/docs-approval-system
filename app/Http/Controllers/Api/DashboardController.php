<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $role = 'pemohon';
            if (method_exists($user, 'getRoleNames') && $user->getRoleNames()->first()) {
                $role = $user->getRoleNames()->first();
            } elseif (isset($user->role)) {
                $role = $user->role;
            }

            $cacheKey = "dashboard_stats_{$role}_{$user->id}";

            $stats = Cache::remember($cacheKey, 300, function () use ($user, $role) {
                $query = Permohonan::query();

                if ($role === 'pemohon') {
                    $query->where('pemohon_id', $user->id);
                }

                $recent = (clone $query)
                    ->where('created_at', '>=', now()->subDays(6)->startOfDay())
                    ->get(['created_at']);
                
                $timeSeries = [];
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $timeSeries[$date] = 0;
                }

                foreach ($recent as $item) {
                    $date = $item->created_at->format('Y-m-d');
                    if (isset($timeSeries[$date])) {
                        $timeSeries[$date]++;
                    }
                }

                $data = [
                    'total' => (clone $query)->count(),
                    'draft' => (clone $query)->where('status', 'draft')->count(),
                    'submitted' => (clone $query)->where('status', 'submitted')->count(),
                    'revisi' => (clone $query)->where('status', 'revisi')->count(),
                    'approved' => (clone $query)->where('status', 'approved')->count(),
                    'rejected' => (clone $query)->where('status', 'rejected')->count(),
                    'time_series' => $timeSeries,
                ];

                if ($role === 'admin') {
                    $data['users_total'] = User::count();
                    $data['users_pemohon'] = User::role('pemohon')->count();
                    $data['users_penilai'] = User::role('penilai')->count();
                    $data['users_admin'] = User::role('admin')->count();
                }

                return $data;
            });

            return response()->json([
                'message' => 'Statistik Dashboard berhasil diambil',
                'role' => $role,
                'statistics' => $stats
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}