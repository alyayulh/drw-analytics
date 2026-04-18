<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->string('nama_produk', 200);
            
            // Status kelengkapan data nilai kriteria.
            // 'Lengkap'       = semua nilai kriteria sudah terisi
            // 'Belum Lengkap' = masih ada kriteria yang belum ada nilainya
            // Produk hanya bisa ikut perhitungan jika status = 'Lengkap'
            $table->enum('status_data', ['Lengkap', 'Belum Lengkap'])->default('Belum Lengkap');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
