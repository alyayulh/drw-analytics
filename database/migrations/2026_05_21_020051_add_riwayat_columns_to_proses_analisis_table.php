<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {
            if (!Schema::hasColumn('proses_analisis', 'nama_file')) {
                $table->string('nama_file')->nullable()->after('nama_proses');
            }

            if (!Schema::hasColumn('proses_analisis', 'path_file')) {
                $table->string('path_file')->nullable()->after('nama_file');
            }

            if (!Schema::hasColumn('proses_analisis', 'status')) {
                $table->string('status', 30)->default('pending')->after('path_file');
            }

            if (!Schema::hasColumn('proses_analisis', 'total_data_awal')) {
                $table->unsignedInteger('total_data_awal')->default(0)->after('min_lift');
            }

            if (!Schema::hasColumn('proses_analisis', 'total_data_bersih')) {
                $table->unsignedInteger('total_data_bersih')->default(0)->after('total_data_awal');
            }

            if (!Schema::hasColumn('proses_analisis', 'total_transaksi')) {
                $table->unsignedInteger('total_transaksi')->default(0)->after('total_data_bersih');
            }

            if (!Schema::hasColumn('proses_analisis', 'total_produk_unik')) {
                $table->unsignedInteger('total_produk_unik')->default(0)->after('total_transaksi');
            }

            if (!Schema::hasColumn('proses_analisis', 'total_frequent_itemsets')) {
                $table->unsignedInteger('total_frequent_itemsets')->default(0)->after('total_produk_unik');
            }

            if (!Schema::hasColumn('proses_analisis', 'total_rules')) {
                $table->unsignedInteger('total_rules')->default(0)->after('total_frequent_itemsets');
            }

            if (!Schema::hasColumn('proses_analisis', 'pesan_error')) {
                $table->text('pesan_error')->nullable()->after('total_rules');
            }
        });
    }

    public function down(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {
            $columns = [
                'nama_file',
                'path_file',
                'status',
                'total_data_awal',
                'total_data_bersih',
                'total_transaksi',
                'total_produk_unik',
                'total_frequent_itemsets',
                'total_rules',
                'pesan_error',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('proses_analisis', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};