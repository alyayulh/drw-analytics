<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail setiap perubahan kriteria.
        // Penting untuk sistem dinamis: jika kriteria diubah bobot/tipe-nya,
        // log ini menjadi bukti kondisi sebelum dan sesudah perubahan.
        Schema::create('log_kriteria', function (Blueprint $table) {
            $table->id('id_log');

            $table->foreignId('id_user')
                  ->constrained('user', 'id_user')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Nullable karena jika kriteria dihapus, id-nya tidak bisa
            // direference lagi — tapi log tetap harus tersimpan
            $table->unsignedBigInteger('id_kriteria')->nullable();

            $table->enum('aksi', ['Tambah', 'Ubah', 'Hapus']);

            // Nama kriteria disimpan langsung agar log tetap terbaca
            // meski kriteria sudah dihapus dari tabel kriteria
            $table->string('nama_kriteria', 150);

            // Snapshot perubahan dalam format JSON
            // Contoh untuk aksi 'Ubah':
            // {
            //   "sebelum": {"bobot": 30, "tipe_atribut": "Benefit"},
            //   "sesudah": {"bobot": 40, "tipe_atribut": "Benefit"}
            // }
            // Untuk aksi 'Tambah': hanya ada key "sesudah"
            // Untuk aksi 'Hapus': hanya ada key "sebelum"
            $table->json('detail')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_kriteria');
    }
};
