<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Detail nilai per kriteria per produk dalam satu sesi perhitungan.
        // Digunakan untuk menampilkan tabel matriks normalisasi di halaman
        // "Detail Proses MOORA" — breakdown lengkap bagaimana Yi terbentuk.
        //
        // nama_kriteria dan tipe_atribut disimpan langsung (snapshot)
        // agar detail tetap terbaca meski kriteria diubah/dihapus kemudian.
        Schema::create('detail_perhitungan', function (Blueprint $table) {
            $table->id('id_detail');

            $table->foreignId('id_hasil')
                  ->constrained('hasil_perhitungan', 'id_hasil')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Snapshot nama dan tipe saat dihitung
            $table->string('nama_kriteria', 150);
            $table->enum('tipe_atribut', ['Benefit', 'Cost']);

            // Nilai asli dari tabel nilai_produk / input_permintaan
            $table->decimal('nilai_asli', 15, 4)->default(0);

            // Nilai setelah normalisasi MOORA: xij / sqrt(sum xij²)
            $table->decimal('nilai_normal', 15, 6)->default(0);

            // Bobot kriteria saat dihitung (snapshot, bukan FK ke kriteria)
            $table->decimal('bobot', 5, 2)->default(0);

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_perhitungan');
    }
};
