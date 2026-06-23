<?php

use Illuminate\Support\Facades\Route; #class route dari Laravel untuk mendefinisikan route.
use App\Http\Controllers\AuthController; 
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

#user klik url login, maka akan tampil halaman login tanpa perlu controller khusus karena hanya menampilkan view login saja.
Route::get('/login', fn() => view('auth.login'))->name('login');    

#user submit form login dengan method POST, lalu diproses oleh method login() di AuthController untuk melakukan autentikasi user. 
Route::post('/login', [AuthController::class, 'login'])->name('login.post'); 

#user submit form logout dengan method POST, lalu diproses oleh method logout() di AuthController untuk melakukan proses logout user.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); 

/*
|--------------------------------------------------------------------------
| DEFAULT ROUTE
|--------------------------------------------------------------------------
*/
#ketika user akses halaman utama web yaitu / , maka akan diarahkan ke halaman dashboard.
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
    #user mengakses halaman dashboard, maka akan diproses oleh method index() di DashboardController untuk menampilkan halaman dashboard.
    #kenapa manggil controller? karena dashboard mungkin menampilkan data dinamis yang diambil dari database, sehingga perlu diproses terlebih dahulu di controller sebelum ditampilkan di view.
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