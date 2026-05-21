<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {
            $table->json('rekap_produk')->nullable()->after('total_rules');
            $table->json('distribusi_waktu')->nullable()->after('rekap_produk');
        });
    }

    public function down(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {
            $table->dropColumn(['rekap_produk', 'distribusi_waktu']);
        });
    }
};