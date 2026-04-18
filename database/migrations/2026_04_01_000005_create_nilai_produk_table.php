<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menyimpan nilai kriteria yang bersumber dari Excel (sumber_data = 'Excel').
        // Setiap produk x kriteria Excel punya tepat 1 baris di tabel ini.
        Schema::create('nilai_produk', function (Blueprint $table) {
            $table->id('id_nilai');

            $table->foreignId('id_produk')
                  ->constrained('produk', 'id_produk')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreignId('id_kriteria')
                  ->constrained('kriteria', 'id_kriteria')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Nilai 0 valid (contoh: penjualan bulan ini = 0 karena stok habis)
            // Yang tidak boleh adalah kolom kosong / null
            $table->decimal('nilai', 15, 4)->default(0);

            $table->timestamps();

            // Satu produk hanya boleh punya 1 nilai per kriteria
            $table->unique(['id_produk', 'id_kriteria'], 'uq_nilai_produk_kriteria');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_produk');
    }
};
