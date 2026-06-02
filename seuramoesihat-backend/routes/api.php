<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DokterController;
use App\Http\Controllers\KonsultasiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RekamMedisController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — SeuramoeSihat
|--------------------------------------------------------------------------
*/

// ─── Auth (publik) ────────────────────────────────────────────────────────────
Route::post('/register',        [AuthController::class, 'register']);
Route::post('/login',           [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp',      [AuthController::class, 'verifyOtp']);
Route::post('/reset-password',  [AuthController::class, 'resetPassword']);

// ─── Dokter (publik) ──────────────────────────────────────────────────────────
Route::get('/dokter',              [DokterController::class, 'index']);
Route::get('/dokter/{id}',         [DokterController::class, 'show']);
Route::get('/dokter/{id}/jadwal',  [DokterController::class, 'jadwal']);

// ─── Protected (butuh token) ──────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Booking & Antrian
    Route::post('/booking',                [BookingController::class, 'store']);
    Route::get('/antrian',                 [BookingController::class, 'aktif']);
    Route::get('/antrian/riwayat',         [BookingController::class, 'riwayat']);
    Route::get('/antrian/{id}',            [BookingController::class, 'show']);
    Route::delete('/antrian/{id}',         [BookingController::class, 'cancel']);
    Route::get('/antrian/{id}/status',     [BookingController::class, 'status']);

    // Rekam Medis
    Route::get('/rekam-medis',     [RekamMedisController::class, 'index']);
    Route::get('/rekam-medis/{id}',[RekamMedisController::class, 'show']);

    // Konsultasi Chat
    Route::get('/konsultasi',                      [KonsultasiController::class, 'index']);
    Route::post('/konsultasi',                     [KonsultasiController::class, 'store']);
    Route::get('/konsultasi/{id}/pesan',           [KonsultasiController::class, 'pesan']);
    Route::post('/konsultasi/{id}/pesan',          [KonsultasiController::class, 'kirimPesan']);

    // Notifikasi
    Route::get('/notifikasi',                      [NotifikasiController::class, 'index']);
    Route::patch('/notifikasi/baca-semua',         [NotifikasiController::class, 'bacaSemua']);
    Route::patch('/notifikasi/{id}/baca',          [NotifikasiController::class, 'baca']);

    // Profil
    Route::put('/profil',            [ProfilController::class, 'update']);
    Route::put('/profil/kesehatan',  [ProfilController::class, 'updateKesehatan']);
    Route::put('/profil/password',   [ProfilController::class, 'updatePassword']);

    // ─── Admin only ───────────────────────────────────────────────────────────
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        // Antrian
        Route::get('/antrian',                        [AdminController::class, 'indexAntrian']);
        Route::patch('/antrian/{id}/status',          [AdminController::class, 'updateStatusAntrian']);
        Route::delete('/antrian/{id}',                [AdminController::class, 'deleteAntrian']);
        Route::post('/antrian/{id}/rekam-medis',      [AdminController::class, 'buatRekamMedis']);

        // Users
        Route::get('/users',                          [AdminController::class, 'indexUsers']);
        Route::delete('/users/{id}',                  [AdminController::class, 'deleteUser']);

        // Dokter
        Route::get('/dokter',                         [AdminController::class, 'indexDokter']);
        Route::post('/dokter',                        [AdminController::class, 'storeDokter']);
        Route::put('/dokter/{id}',                    [AdminController::class, 'updateDokter']);
        Route::delete('/dokter/{id}',                 [AdminController::class, 'deleteDokter']);
        Route::patch('/dokter/{id}/tersedia',         [AdminController::class, 'toggleDokter']);

        // Faskes (untuk dropdown form dokter)
        Route::get('/faskes',                         [AdminController::class, 'indexFaskes']);
    });
});
