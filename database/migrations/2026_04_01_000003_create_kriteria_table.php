<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kriteria', function (Blueprint $table) {
            $table->id('id_kriteria');
            $table->string('nama_kriteria', 150);

            // Benefit = makin besar makin baik (contoh: penjualan, stok)
            // Cost    = makin kecil makin baik (contoh: harga, retur)
            $table->enum('tipe_atribut', ['Benefit', 'Cost']);

            // Bobot dalam persen (total semua kriteria harus = 100)
            // Validasi total bobot dilakukan di level aplikasi (controller),
            // bukan di level database karena DB tidak bisa sum lintas baris
            $table->decimal('bobot', 5, 2)->default(0);

            // Excel  = nilai diambil otomatis dari file Excel yang diupload
            // Manual = nilai diisi manual oleh user lewat form Input Permintaan
            $table->enum('sumber_data', ['Excel', 'Manual'])->default('Excel');
            $table->string('nama_kolom_excel', 100)->nullable(); 


            $table->timestamps();
        
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriteria');
    }
};
