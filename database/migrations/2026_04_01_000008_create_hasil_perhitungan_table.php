<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hasil ranking per produk untuk setiap sesi perhitungan.
        // nama_produk disimpan langsung (bukan hanya FK) supaya histori
        // tetap terbaca meski produk dihapus dari sistem kemudian.
        Schema::create('hasil_perhitungan', function (Blueprint $table) {
            $table->id('id_hasil');

            $table->foreignId('id_perhitungan')
                  ->constrained('perhitungan', 'id_perhitungan')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreignId('id_produk')
                  ->constrained('produk', 'id_produk')
                  ->onDelete('restrict') // produk tidak boleh dihapus jika sudah pernah dihitung
                  ->onUpdate('cascade');

            // Nama produk disimpan langsung sebagai snapshot
            $table->string('nama_produk', 200);

            // Nilai Yi = total_benefit - total_cost (hasil MOORA)
            $table->decimal('nilai_yi', 15, 6);

            // Komponen Yi dipisah untuk keperluan audit/tampilan detail
            $table->decimal('total_benefit', 15, 6)->default(0);
            $table->decimal('total_cost', 15, 6)->default(0);

            $table->integer('ranking');

            // Utama       = ranking 1
            // Pertimbangkan = ranking 2–3
            // Tunda       = ranking 4 dst
            $table->enum('prioritas', ['Utama', 'Pertimbangkan', 'Tunda']);

            $table->timestamp('created_at')->useCurrent();

            // Satu produk hanya muncul sekali per sesi perhitungan
            $table->unique(['id_perhitungan', 'id_produk'], 'uq_hasil_perhitungan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_perhitungan');
    }
};
