<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kategori_produk')) {
            Schema::create('kategori_produk', function (Blueprint $table) {
                $table->id('id_kategori');
                $table->string('nama_kategori', 100)->unique();
            });
        }

        if (Schema::hasTable('produk') && !Schema::hasColumn('produk', 'id_kategori')) {
            Schema::table('produk', function (Blueprint $table) {
                $table->unsignedBigInteger('id_kategori')->nullable()->after('nama_produk');
                $table->foreign('id_kategori')
                      ->references('id_kategori')->on('kategori_produk')
                      ->onDelete('set null')->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('produk') && Schema::hasColumn('produk', 'id_kategori')) {
            Schema::table('produk', function (Blueprint $table) {
                try { $table->dropForeign(['id_kategori']); } catch (\Throwable $e) {}
                $table->dropColumn('id_kategori');
            });
        }
        Schema::dropIfExists('kategori_produk');
    }
};