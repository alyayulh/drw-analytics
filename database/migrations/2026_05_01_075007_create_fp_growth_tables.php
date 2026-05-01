<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proses_analisis', function (Blueprint $table) {
            $table->id('id_proses_analisis');
            $table->string('nama_proses', 150);
            $table->dateTime('tanggal_proses')->useCurrent();
            $table->decimal('min_support', 5, 2)->nullable();
            $table->decimal('min_confidence', 5, 2)->nullable();
            $table->decimal('min_lift', 6, 2)->nullable();
        });

        Schema::create('aturan_asosiasi', function (Blueprint $table) {
            $table->id('id_aturan_asosiasi');
            $table->unsignedBigInteger('id_proses_analisis');
            $table->decimal('nilai_support', 5, 2);
            $table->decimal('nilai_confidence', 5, 2);
            $table->decimal('nilai_lift', 6, 2);
            $table->longText('rule_asosiasi');

            $table->foreign('id_proses_analisis')
                  ->references('id_proses_analisis')
                  ->on('proses_analisis');
        });

        Schema::create('deteksi_anomali', function (Blueprint $table) {
            $table->id('id_deteksi_anomali');
            $table->unsignedBigInteger('id_proses_analisis');
            $table->decimal('batas_bawah', 10, 2)->nullable();
            $table->decimal('batas_atas', 10, 2)->nullable();
            $table->decimal('nilai', 10, 2)->nullable();

            $table->foreign('id_proses_analisis')
                  ->references('id_proses_analisis')
                  ->on('proses_analisis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deteksi_anomali');
        Schema::dropIfExists('aturan_asosiasi');
        Schema::dropIfExists('proses_analisis');
    }
};