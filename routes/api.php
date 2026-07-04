<?php

use App\Http\Controllers\Api\AbsensiGuruController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\JadwalController;
use App\Http\Controllers\Api\KelasController;
use App\Http\Controllers\Api\PengajuanIzinGuruController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Aplikasi Mobile Guru (React Native)
|--------------------------------------------------------------------------
| Auth berbasis token (Laravel Sanctum). Semua route di bawah `auth:sanctum`
| butuh header: Authorization: Bearer <token>.
*/

Route::prefix('v1')->group(function () {

    // Publik
    Route::post('login', [AuthController::class, 'login']);

    // Terproteksi (Bearer token)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Registrasi token FCM perangkat (Fase 2)
        Route::post('device-token', [DeviceTokenController::class, 'store']);
        Route::delete('device-token', [DeviceTokenController::class, 'destroy']);

        // Fase 1 — Absensi guru (GPS + selfie)
        Route::prefix('absensi-guru')->group(function () {
            Route::get('today', [AbsensiGuruController::class, 'today']);
            Route::get('riwayat', [AbsensiGuruController::class, 'riwayat']);
            Route::post('masuk', [AbsensiGuruController::class, 'masuk']);
            Route::post('pulang', [AbsensiGuruController::class, 'pulang']);
        });

        // Pengajuan Izin/Sakit
        Route::prefix('pengajuan-izin')->group(function () {
            Route::get('/', [PengajuanIzinGuruController::class, 'index']);
            Route::post('/', [PengajuanIzinGuruController::class, 'store']);
            Route::get('{id}', [PengajuanIzinGuruController::class, 'show']);
            Route::delete('{id}', [PengajuanIzinGuruController::class, 'destroy']);
        });

        // Fase 2 — Jadwal mengajar
        Route::prefix('jadwal')->group(function () {
            Route::get('today', [JadwalController::class, 'today']);
            Route::get('/', [JadwalController::class, 'week']);
        });

        // Fase 3 — Masuk kelas: absen siswa → materi/RPP → nilai (gated)
        Route::prefix('kelas/{jadwal}')->group(function () {
            Route::get('siswa', [KelasController::class, 'siswa']);
            Route::post('absensi', [KelasController::class, 'absensiStore']);
            Route::post('absensi/scan', [KelasController::class, 'absensiScan']);
            Route::get('materi', [KelasController::class, 'materi']);
            Route::get('nilai', [KelasController::class, 'nilaiSheet']);
            Route::post('nilai', [KelasController::class, 'nilaiStore']);
        });
    });
});
