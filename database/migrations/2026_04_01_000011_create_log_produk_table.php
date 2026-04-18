<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail setiap perubahan data produk (alternatif SPK).
        // Lebih ringan dari log_kriteria karena perubahan produk tidak
        // mengubah cara perhitungan, hanya mengubah siapa yang dihitung.
        Schema::create('log_produk', function (Blueprint $table) {
            $table->id('id_log');

            $table->foreignId('id_user')
                  ->constrained('user', 'id_user')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Nullable karena produk yang sudah dihapus tidak bisa direference
            $table->unsignedBigInteger('id_produk')->nullable();

            $table->enum('aksi', ['Tambah', 'Edit', 'Hapus']);

            // Nama produk disimpan langsung sebagai snapshot
            $table->string('nama_produk', 200);

            // Keterangan bebas, opsional
            // Contoh: "Dihapus karena produk diskontinyu"
            $table->string('keterangan', 255)->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_produk');
    }
};
