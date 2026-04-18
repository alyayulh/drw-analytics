<?php

use Illuminate\Support\Facades\Route;

#halaman login
use App\Http\Controllers\AuthController;
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


 Route::get('/dashboard', function () {
    return view('spk.dashboard');
})->name('dashboard');

// halaman data produk
use App\Http\Controllers\ProdukController;

Route::get('/data-produk', [ProdukController::class, 'index'])->name('produk.index');
Route::post('/data-produk', [ProdukController::class, 'store'])->name('produk.store');
Route::post('/data-produk/import', [ProdukController::class, 'import'])->name('produk.import');
Route::put('/data-produk/{id}', [ProdukController::class, 'update'])->name('produk.update');
Route::delete('/data-produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');


// halaman kriteria
use App\Http\Controllers\KriteriaController;

Route::get('/kelola-kriteria', [KriteriaController::class, 'index'])->name('kriteria.index');
Route::post('/kelola-kriteria', [KriteriaController::class, 'store'])->name('kriteria.store');
Route::put('/kelola-kriteria/{id}', [KriteriaController::class, 'update'])->name('kriteria.update');
Route::delete('/kelola-kriteria/{id}', [KriteriaController::class, 'destroy'])->name('kriteria.destroy');


//halaman input permintaan
use App\Http\Controllers\InputPermintaanController;

Route::get('/input-permintaan', [InputPermintaanController::class, 'index'])->name('input.index');
Route::post('/input-permintaan', [InputPermintaanController::class, 'store'])->name('input.store');