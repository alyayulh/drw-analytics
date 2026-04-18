<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Header/induk setiap sesi perhitungan MOORA.
        // Menyimpan snapshot kondisi saat perhitungan dijalankan
        // sehingga hasil lama tetap bisa diaudit meski kriteria berubah kemudian.
        Schema::create('perhitungan', function (Blueprint $table) {
            $table->id('id_perhitungan');

            $table->foreignId('id_user')
                  ->constrained('user', 'id_user')
                  ->onDelete('restrict') // user tidak boleh dihapus jika punya riwayat perhitungan
                  ->onUpdate('cascade');

            // Periode data yang dihitung, contoh: "November 2025"
            $table->string('periode_data', 20);

            // Jumlah produk yang ikut dihitung (status Lengkap saja)
            $table->integer('jumlah_produk')->default(0);

            // Total seluruh produk di sistem saat perhitungan dijalankan
            $table->integer('total_produk')->default(0);

            // Nama produk ranking #1 — disimpan langsung agar mudah ditampilkan
            // tanpa perlu join ke hasil_perhitungan
            $table->string('produk_prioritas', 200)->nullable();

            // Snapshot bobot kriteria saat dihitung
            // Format: [{"nama":"Penjualan","tipe_atribut":"Benefit","bobot":40}, ...]
            $table->json('bobot_snapshot');

            // Matriks keputusan awal (nilai asli sebelum dinormalisasi)
            $table->json('matriks_keputusan')->nullable();

            // Matriks setelah normalisasi MOORA (x*ij = xij / sqrt(sum xij²))
            $table->json('matriks_normal')->nullable();

            // Hanya created_at, tidak ada updated_at karena perhitungan tidak diedit
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perhitungan');
    }
};
