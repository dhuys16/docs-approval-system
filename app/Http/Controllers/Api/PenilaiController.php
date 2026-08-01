<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\RiwayatPenilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PenilaiController extends Controller
{
    // List Seluruh Permohonan Masuk untuk Penilai (dengan Filter & Pagination Performa Tinggi)
    public function index(Request $request)
    {
        $query = Permohonan::with(['pemohon:id,name,email', 'dokumen'])
            ->where('status', '!=', 'draft')
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

    public function exportExcel(Request $request)
    {
        $query = Permohonan::with(['pemohon:id,name,email', 'dokumen'])
            ->where('status', '!=', 'draft')
            ->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_permohonan', 'LIKE', "%{$request->search}%")
                  ->orWhere('judul_project', 'LIKE', "%{$request->search}%");
            });
        }

        $permohonan = $query->get();

        $filename = "Data_Permohonan_Penilai_" . date('Y-m-d_H-i-s') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['No', 'Nomor Permohonan', 'Nama Pemohon', 'Email', 'Judul Project', 'Status', 'Tanggal Pengajuan'];

        $callback = function() use($permohonan, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $rowCounter = 1;
            foreach ($permohonan as $p) {
                $row = [
                    $rowCounter++,
                    $p->nomor_permohonan,
                    $p->pemohon->name ?? '-',
                    $p->pemohon->email ?? '-',
                    $p->judul_project,
                    $p->status,
                    $p->created_at->format('Y-m-d H:i:s'),
                ];
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Detail Permohonan untuk Penilai
    public function show($nomorPermohonan)
    {
        $permohonan = Permohonan::with(['pemohon:id,name,email', 'dokumen', 'riwayat.penilai:id,name'])
                        ->where('nomor_permohonan', $nomorPermohonan)
                        ->first();

        if (!$permohonan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => $permohonan
        ]);
    }

    // Proses Penilaian (Approved / Revisi / Rejected)
    public function review(Request $request, $nomorPermohonan)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,revisi,rejected',
            'catatan' => 'required|string|min:5', // Catatan wajib diisi saat review
        ]);

        $permohonan = Permohonan::where('nomor_permohonan', $nomorPermohonan)->firstOrFail();

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

            Cache::flush();
            
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