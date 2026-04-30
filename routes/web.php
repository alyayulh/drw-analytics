<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\InputPermintaanController;
use App\Http\Controllers\PerhitunganController;

Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', fn() => redirect('/dashboard'));

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', fn() => view('spk.dashboard'))->name('dashboard');

    // Khusus Admin
    Route::middleware('role:Admin')->group(function () {
        Route::get('/data-produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::post('/data-produk', [ProdukController::class, 'store'])->name('produk.store');

        // Import: upload → preview → konfirmasi
        Route::post('/data-produk/preview', [ProdukController::class, 'preview'])->name('produk.preview');
        Route::get('/data-produk/preview', [ProdukController::class, 'showPreview'])->name('produk.preview.show');
        Route::post('/data-produk/import-confirm', [ProdukController::class, 'importConfirm'])->name('produk.import.confirm');
        Route::post('/data-produk/cancel-preview', [ProdukController::class, 'cancelPreview'])->name('produk.preview.cancel');

        Route::put('/data-produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
        Route::delete('/data-produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    });

    // Admin dan Manajer
    Route::get('/kelola-kriteria', [KriteriaController::class, 'index'])->name('kriteria.index');
    Route::post('/kelola-kriteria', [KriteriaController::class, 'store'])->name('kriteria.store');
    Route::put('/kelola-kriteria/{id}', [KriteriaController::class, 'update'])->name('kriteria.update');
    Route::delete('/kelola-kriteria/{id}', [KriteriaController::class, 'destroy'])->name('kriteria.destroy');

    Route::get('/input-permintaan', [InputPermintaanController::class, 'index'])->name('input.index');
    Route::post('/input-permintaan', [InputPermintaanController::class, 'store'])->name('input.store');

    Route::get('/hitung-spk', [PerhitunganController::class, 'index'])->name('perhitungan.index');
    Route::post('/hitung-spk', [PerhitunganController::class, 'hitung'])->name('perhitungan.hitung');
    Route::get('/perhitungan/{id}/hasil', [PerhitunganController::class, 'hasil'])->name('perhitungan.hasil');
    Route::get('/riwayat', [PerhitunganController::class, 'riwayat'])->name('perhitungan.riwayat');
    Route::delete('/perhitungan/{id}', [PerhitunganController::class, 'destroy'])->name('perhitungan.destroy');
});
