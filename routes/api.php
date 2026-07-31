<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PermohonanController;
use App\Http\Controllers\Api\PenilaiController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route khusus Pemohon
    Route::prefix('pemohon')->group(function () {
        Route::get('/permohonan', [PermohonanController::class, 'index']);
        Route::post('/permohonan', [PermohonanController::class, 'store']);
        Route::get('/permohonan/{id}', [PermohonanController::class, 'show']);
        Route::post('/permohonan/{id}', [PermohonanController::class, 'update']);
    });

    // Route khusus Penilai
    Route::prefix('penilai')->group(function () {
        Route::get('/permohonan', [PenilaiController::class, 'index']);
        Route::post('/permohonan/{id}/review', [PenilaiController::class, 'review']);
        Route::get('/histori', [PenilaiController::class, 'historiPenilaian']);
    });

    // Dashboard Stats (Bisa diakses Pemohon & Penilai)
    Route::get('/dashboard/stats', [DashboardController::class, 'index']);
});