<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\RiwayatPenilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenilaiController extends Controller
{
    // List Seluruh Permohonan Masuk untuk Penilai (dengan Filter & Pagination Performa Tinggi)
    public function index(Request $request)
    {
        $query = Permohonan::with(['pemohon:id,name,email', 'dokumen'])
            ->latest();

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_permohonan', 'LIKE', "%{$request->search}%")
                  ->orWhere('judul_project', 'LIKE', "%{$request->search}%");
            });
        }

        return response()->json($query->paginate(15));
    }

    // Proses Penilaian (Approved / Revisi / Rejected)
    public function review(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,revisi,rejected',
            'catatan' => 'required|string|min:5', // Catatan wajib diisi saat review
        ]);

        $permohonan = Permohonan::findOrFail($id);

        return DB::transaction(function () use ($request, $permohonan, $validated) {
            $statusLama = $permohonan->status;
            $statusBaru = $validated['status'];

            $permohonan->update(['status' => $statusBaru]);

            RiwayatPenilaian::create([
                'permohonan_id' => $permohonan->id,
                'penilai_id' => $request->user()->id,
                'status_sebelumnya' => $statusLama,
                'status_baru' => $statusBaru,
                'catatan' => $validated['catatan'],
            ]);

            return response()->json([
                'message' => "Keputusan penilaian berhasil disimpan ($statusBaru)",
                'data' => $permohonan->load(['dokumen', 'riwayat.penilai']),
            ]);
        });
    }

    // Histori Penilaian
    public function historiPenilaian(Request $request)
    {
        $histori = RiwayatPenilaian::with(['permohonan.pemohon', 'penilai'])
            ->whereNotNull('penilai_id')
            ->latest()
            ->paginate(15);

        return response()->json($histori);
    }
}