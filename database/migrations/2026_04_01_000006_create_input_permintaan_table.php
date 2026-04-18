<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menyimpan nilai kriteria yang diisi manual oleh user (sumber_data = 'Manual').
        // Nilai menggunakan skala 1–5, constraint CHECK mencegah nilai 0 atau di luar range.
        // Ini yang membedakan dari nilai_produk: nilai 0 tidak mungkin ada di sini.
        Schema::create('input_permintaan', function (Blueprint $table) {
            $table->id('id_input');

            $table->foreignId('id_produk')
                  ->constrained('produk', 'id_produk')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreignId('id_kriteria')
                  ->constrained('kriteria', 'id_kriteria')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Skala 1–5, tidak boleh 0 atau kosong
            $table->tinyInteger('nilai_input')->default(1);

            $table->timestamps();

            $table->unique(['id_produk', 'id_kriteria'], 'uq_input_produk_kriteria');
        });

        // Tambahkan CHECK constraint manual karena Laravel Blueprint
        // tidak punya method check() secara native
        DB::statement('ALTER TABLE input_permintaan ADD CONSTRAINT chk_nilai_input CHECK (nilai_input BETWEEN 1 AND 5)');
    }

    public function down(): void
    {
        Schema::dropIfExists('input_permintaan');
    }
};
