<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom deleted_at ke tabel produk untuk soft delete.
 *
 * Tujuan: ketika admin menghapus produk yang sudah pernah masuk perhitungan
 * SPK, baris produk tidak benar-benar dihapus dari DB melainkan ditandai
 * `deleted_at`. Ini menjaga integritas riwayat perhitungan — produk yang
 * pernah diranking dapat tetap di-trace meskipun sudah "dihapus" oleh admin.
 *
 * Laravel SoftDeletes trait otomatis akan:
 *   - Mengecualikan baris ber-deleted_at dari semua query default
 *   - Menyediakan ->withTrashed() untuk query yang menyertakan soft-deleted
 *   - Menyediakan ->onlyTrashed() untuk query khusus soft-deleted
 *   - Menyediakan ->restore() untuk membatalkan soft delete
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};