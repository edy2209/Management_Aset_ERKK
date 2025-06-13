<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PeminjamAuthController;
use App\Models\Peminjam;
use App\Models\Atasan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpRegisterController;
use App\Models\Barang;
use App\Http\Controllers\BarangPeminjamanController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\KbarangController;
use App\Http\Controllers\BarangPengembalianController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\BarangMaintenanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\DendaController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\HistoryDendaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register-peminjam', [PeminjamAuthController::class, 'register']);

// ini login untuk atasan dan peminjam

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [OtpRegisterController::class, 'register']);
Route::post('/verify-otp', [OtpRegisterController::class, 'verifyOtp']);

Route::get('/statistik-barang', function () {
    return response()->json([
        'totalBarang' => Barang::sum('jumlah_barang'),
    ]);
});

Route::post('/peminjaman', [BarangPeminjamanController::class, 'store']);
Route::get('/peminjaman', [BarangPeminjamanController::class, 'index']);
Route::get('/barang', [BarangController::class, 'index']);
Route::get('/kbarang', [KbarangController::class, 'index']);

Route::post('/pengembalian', [BarangPengembalianController::class, 'store']);
Route::get('/pengembalian', [BarangPengembalianController::class, 'index']);

// Route untuk rating

Route::post('/rating/check', [RatingController::class, 'check']);
Route::post('/rating', [RatingController::class, 'store']);
// untuk maintenance barang
Route::post('/barang-maintenance', [BarangMaintenanceController::class, 'store']);
Route::post('/barang-maintenance/{id}/selesai', [BarangMaintenanceController::class, 'selesai']);

Route::get('/maintenance', [BarangMaintenanceController::class, 'index']);

// routes/api.php
Route::post('/payment/generate-token', [PaymentController::class, 'generateSnapToken']);
Route::post('/payment/callback', [PaymentController::class, 'handleCallback']);

// Denda routes
Route::post('/denda', [DendaController::class, 'store']);
// routes/api.php
Route::get('/denda/user/{id}', [DendaController::class, 'getUserDenda']);

Route::post('midtrans/notification', [MidtransController::class, 'handle'])
    ->withoutMiddleware(['throttle:api', 'auth:api']);

Route::get('/history-denda/{userId}', [HistoryDendaController::class, 'index']);

Route::get('/rating', [RatingController::class, 'index']);

//konfirmasi peminjaman
Route::post('/peminjaman/{id}/konfirmasi', [BarangPeminjamanController::class, 'konfirmasi']);