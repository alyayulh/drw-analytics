<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {
            if (!Schema::hasColumn('proses_analisis', 'total_operator')) {
                $table->integer('total_operator')->default(0)->after('total_produk_unik');
            }
        });

        Schema::table('aturan_asosiasi', function (Blueprint $table) {
            if (!Schema::hasColumn('aturan_asosiasi', 'kategori_rule')) {
                $table->string('kategori_rule')->nullable()->after('rule_asosiasi');
            }

            if (!Schema::hasColumn('aturan_asosiasi', 'is_anomaly')) {
                $table->boolean('is_anomaly')->default(false)->after('kategori_rule');
            }
        });
    }

    public function down(): void
    {
        Schema::table('aturan_asosiasi', function (Blueprint $table) {
            if (Schema::hasColumn('aturan_asosiasi', 'is_anomaly')) {
                $table->dropColumn('is_anomaly');
            }

            if (Schema::hasColumn('aturan_asosiasi', 'kategori_rule')) {
                $table->dropColumn('kategori_rule');
            }
        });

        Schema::table('proses_analisis', function (Blueprint $table) {
            if (Schema::hasColumn('proses_analisis', 'total_operator')) {
                $table->dropColumn('total_operator');
            }
        });
    }
};