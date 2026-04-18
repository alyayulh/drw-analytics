<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini memetakan nama kolom di file Excel ke kriteria di sistem.
        // Contoh: kolom Excel "TOTAL PENJUALAN" → kriteria "Penjualan"
        //
        // Manfaat: jika nama kolom Excel berubah di periode berikutnya,
        // Admin cukup update mapping lewat UI tanpa perlu ubah kode.
        Schema::create('mapping_nama', function (Blueprint $table) {
            $table->id('id_mapping');

            // Nama kolom persis seperti di header file Excel (case-sensitive)
            $table->string('nama_kolom_excel', 200);

            $table->foreignId('id_kriteria')
                  ->constrained('kriteria', 'id_kriteria')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->timestamps();

            // Satu kolom Excel hanya boleh dipetakan ke satu kriteria
            $table->unique('nama_kolom_excel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapping_nama');
    }
};
