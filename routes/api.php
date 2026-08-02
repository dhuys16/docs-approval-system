<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PenilaiController;
use App\Http\Controllers\Api\PermohonanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('pemohon')->middleware('role:pemohon')->group(function () {
        Route::get('/permohonan', [PermohonanController::class, 'index']);
        Route::post('/permohonan', [PermohonanController::class, 'store']);
        Route::get('/permohonan/{nomor_permohonan}', [PermohonanController::class, 'show']);
        Route::post('/permohonan/{nomor_permohonan}', [PermohonanController::class, 'update']);
    });

    Route::prefix('penilai')->middleware('role:penilai')->group(function () {
        Route::get('/permohonan', [PenilaiController::class, 'index']);
        Route::get('/permohonan/export', [PenilaiController::class, 'exportExcel']);
        Route::get('/permohonan/{nomor_permohonan}', [PenilaiController::class, 'show']);
        Route::post('/permohonan/{nomor_permohonan}/review', [PenilaiController::class, 'review']);
        Route::get('/histori', [PenilaiController::class, 'historiPenilaian']);
    });

    Route::get('/dashboard/stats', [DashboardController::class, 'index']);

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    });
});