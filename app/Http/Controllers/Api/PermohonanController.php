<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permohonan;
use App\Models\DokumenPermohonan;
use App\Models\RiwayatPenilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PermohonanController extends Controller
{
    // List Permohonan Milik Pemohon (dengan Pagination & Eager Loading)
    public function index(Request $request)
    {
        $permohonan = Permohonan::with(['dokumen', 'riwayat.penilai'])
            ->where('pemohon_id', $request->user()->id)
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->latest()
            ->paginate(15);

        return response()->json($permohonan);
    }

    // Buat Permohonan Baru (Draft / Submit Langsung)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_project' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_submit' => 'boolean',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,png|max:5000',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $status = ($request->is_submit ?? false) ? 'submitted' : 'draft';

            $permohonan = Permohonan::create([
                'nomor_permohonan' => 'PRM-' . time() . rand(100, 999),
                'pemohon_id' => $request->user()->id,
                'judul_project' => $validated['judul_project'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'status' => $status,
            ]);

            // Handle Upload Dokumen
            if ($request->hasFile('dokumen')) {
                $file = $request->file('dokumen');
                $path = $file->store('dokumen_permohonan', 'public');

                DokumenPermohonan::create([
                    'permohonan_id' => $permohonan->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => round($file->getSize() / 1024), // dalam KB
                ]);
            }

            // Catat Riwayat
            RiwayatPenilaian::create([
                'permohonan_id' => $permohonan->id,
                'status_sebelumnya' => null,
                'status_baru' => $status,
                'catatan' => $status === 'submitted' ? 'Permohonan berhasil dikirim' : 'Draft permohonan dibuat',
            ]);

            return response()->json([
                'message' => 'Permohonan berhasil dibuat',
                'data' => $permohonan->load('dokumen'),
            ], 201);
        });
    }

    public function show($identifier)
    {
        $permohonan = Permohonan::with(['dokumen', 'riwayat'])
                        ->where('nomor_permohonan', $identifier)
                        ->first();

        if (!$permohonan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => $permohonan
        ]);
}
    // Update / Re-submit Permohonan Revisi
    public function update(Request $request, $nomor_permohonan)
    {
        $permohonan = Permohonan::where('pemohon_id', $request->user()->id)
            ->where('nomor_permohonan', $nomor_permohonan)
            ->firstOrFail();

        if (!in_array($permohonan->status, ['draft', 'revisi'])) {
            return response()->json(['message' => 'Permohonan tidak dapat diubah pada status ini'], 422);
        }

        $validated = $request->validate([
            'judul_project' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'is_submit' => 'boolean',
            'dokumen' => 'nullable|file|mimes:pdf,jpg,png|max:5000',
        ]);

        return DB::transaction(function () use ($request, $permohonan, $validated) {
            $statusLama = $permohonan->status;
            $statusBaru = ($request->is_submit ?? false) ? 'submitted' : $statusLama;

            $permohonan->update([
                'judul_project' => $validated['judul_project'],
                'deskripsi' => $validated['deskripsi'] ?? $permohonan->deskripsi,
                'status' => $statusBaru,
            ]);

            if ($request->hasFile('dokumen')) {
                $file = $request->file('dokumen');
                $path = $file->store('dokumen_permohonan', 'public');

                DokumenPermohonan::create([
                    'permohonan_id' => $permohonan->id,
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => round($file->getSize() / 1024),
                ]);
            }

            if ($statusLama !== $statusBaru) {
                RiwayatPenilaian::create([
                    'permohonan_id' => $permohonan->id,
                    'status_sebelumnya' => $statusLama,
                    'status_baru' => $statusBaru,
                    'catatan' => 'Perbaikan permohonan dikirim kembali',
                ]);
            }

            Cache::flush();

            return response()->json([
                'message' => 'Permohonan berhasil diperbarui',
                'data' => $permohonan->load('dokumen'),
            ]);
        });
    }
}