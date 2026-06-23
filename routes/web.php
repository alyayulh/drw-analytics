<?php

use Illuminate\Support\Facades\Route; #class route dari Laravel untuk mendefinisikan route.
use App\Http\Controllers\AuthController; #menghubungkan route dengan controller AuthController.
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KriteriaController;
use App\Http\Controllers\InputPermintaanController;
use App\Http\Controllers\PerhitunganController;
use App\Http\Controllers\AsosiasiController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

#route: GET -> saat membuka atau menampilkan halaman.
#route: POST -> saat mengirim data (form login) ke server untuk diproses.

#jenis route → URL → controller → method → nama route

#bagian routing untuk autentikasi (login/logout) user.
#1. Route untuk menampilkan halaman login (GET /login). cuma menampilkan form login, tidak ada proses autentikasi/logika. makanya dipakai closure langsung tanpa controller khusus.
#closure maksudnya function anonim (tanpa nama) tanpa perlu membuat controller khusus untuk menampilkan view login
Route::get('/login', fn() => view('auth.login'))->name('login');    
#ketika user akses GET /login, tampilkan view auth.login (form login).
#fn() => view('auth.login') -> closure yang langsung return view login tanpa perlu controller khusus.
#name('login') -> beri nama route 'login' untuk memudahkan referensi di kode lain (misal: route('login')).

#2. Route untuk memproses login (POST /login). methodnya post karena form login mengirim data username/password ke server untuk diproses autentikasi.
# arahkan ke AuthController@login untuk memproses login.
Route::post('/login', [AuthController::class, 'login'])->name('login.post'); 

#3. Route untuk logout (POST /logout). methodnya post karena logout mengubah state atau session user di server.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); 
#ketika user submit form logout (POST /logout), panggil method logout() di AuthController untuk proses logout.

/*
|--------------------------------------------------------------------------
| DEFAULT ROUTE
|--------------------------------------------------------------------------
*/
#ketika user mengakses root URL (/) dari aplikasi, langsung redirect ke /dashboard.
Route::get('/', fn() => redirect('/dashboard'));

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD UTAMA
    |--------------------------------------------------------------------------
    */
    #route untuk menampilkan dashboard utama setelah login. memanggil method index() di DashboardController untuk menampilkan halaman dashboard.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | KHUSUS ADMIN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:Admin')->group(function () {
        
        Route::get('/data-produk', [ProdukController::class, 'index'])->name('produk.index'); #Menampilkan halaman Data Produk. memanggil method index() di ProdukController untuk menampilkan halaman data produk.
        Route::post('/data-produk', [ProdukController::class, 'store'])->name('produk.store'); #Menambahkan produk baru. memanggil method store() di ProdukController untuk menyimpan data produk baru ke database.

        Route::post('/data-produk/preview', [ProdukController::class, 'preview'])->name('produk.preview'); #Menampilkan preview data produk yang akan diimpor. memanggil method preview() di ProdukController untuk menampilkan halaman preview data produk sebelum diimpor ke database.
        Route::get('/data-produk/preview', [ProdukController::class, 'showPreview'])->name('produk.preview.show');#Menampilkan halaman preview data produk yang sudah diunggah. memanggil method showPreview() di ProdukController untuk menampilkan halaman preview data produk yang sudah diunggah sebelumnya.
        Route::post('/data-produk/import-confirm', [ProdukController::class, 'importConfirm'])->name('produk.import.confirm');
        Route::post('/data-produk/cancel-preview', [ProdukController::class, 'cancelPreview'])->name('produk.preview.cancel');

        Route::put('/data-produk/{id}', [ProdukController::class, 'update'])->name('produk.update'); #Mengupdate data produk yang sudah ada. memanggil method update() di ProdukController untuk mengupdate data produk yang sudah ada di database berdasarkan id produk yang diberikan.
        Route::delete('/data-produk/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN DAN MANAJER
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | ANALISIS ASOSIASI
    |--------------------------------------------------------------------------
    */

    Route::prefix('asosiasi')->name('asosiasi.')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Halaman umum asosiasi
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [AsosiasiController::class, 'dashboard'])->name('dashboard');

        Route::get('/riwayat', [AsosiasiController::class, 'riwayat'])->name('riwayat');
        Route::get('/riwayat/{id}', [AsosiasiController::class, 'detailRiwayat'])->name('riwayat.detail');
        Route::get('/riwayat/{id}/download', [AsosiasiController::class, 'downloadHasilRiwayat'])->name('riwayat.download');

        /*
        |--------------------------------------------------------------------------
        | Hapus riwayat analisis
        | Ditaruh di luar role:Admin supaya tidak kena 403 dari middleware role.
        |--------------------------------------------------------------------------
        */

        Route::delete('/riwayat/{id}', [AsosiasiController::class, 'destroyRiwayat'])->name('riwayat.destroy');

        /*
        |--------------------------------------------------------------------------
        | Download laporan dashboard asosiasi
        |--------------------------------------------------------------------------
        */

        Route::get('/download-laporan', [AsosiasiController::class, 'downloadLaporan'])->name('download');

        /*
        |--------------------------------------------------------------------------
        | Khusus Admin untuk proses analisis
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:Admin')->group(function () {

            Route::get('/analisis', [AsosiasiController::class, 'analisis'])->name('analisis');

            /*
            |--------------------------------------------------------------------------
            | Validasi format dataset sebelum loading proses berjalan
            |--------------------------------------------------------------------------
            */

            Route::post('/validasi-format', [AsosiasiController::class, 'validasiFormat'])->name('validasiFormat');

            /*
            |--------------------------------------------------------------------------
            | Proses analisis dataset
            |--------------------------------------------------------------------------
            */

            Route::post('/analisis/proses', [AsosiasiController::class, 'prosesAnalisis'])->name('proses');

            Route::post('/proses-analisis', [AsosiasiController::class, 'prosesAnalisis'])->name('prosesAnalisis');

            Route::get('/hasil', [AsosiasiController::class, 'hasilAnalisis'])->name('hasil');

            /*
            |--------------------------------------------------------------------------
            | Download hasil analisis halaman hasil
            |--------------------------------------------------------------------------
            */

            Route::get('/hasil/download', [AsosiasiController::class, 'downloadHasil'])->name('hasil.download');
        });
    });
});