<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\Api\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

use App\Http\Controllers\Api\BukuController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\TransaksiController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Kategori - Admin Only
    Route::apiResource('kategori', KategoriController::class)->middleware('role:Admin');

    // Buku - Read for all authenticated roles, Write for Admin only
    Route::get('/buku', [BukuController::class, 'index']);
    Route::get('/buku/{buku}', [BukuController::class, 'show']);

    Route::middleware('role:Admin')->group(function () {
        Route::post('/buku', [BukuController::class, 'store']);
        Route::put('/buku/{buku}', [BukuController::class, 'update']);
        Route::patch('/buku/{buku}', [BukuController::class, 'update']);
        Route::delete('/buku/{buku}', [BukuController::class, 'destroy']);
    });

    // Transaksi Peminjaman, Pengembalian, & Riwayat
    Route::post('/pinjam', [TransaksiController::class, 'pinjam']);
    Route::post('/kembali/{peminjam_id}', [TransaksiController::class, 'kembali']);
    Route::get('/riwayat', [TransaksiController::class, 'riwayat']);
});



