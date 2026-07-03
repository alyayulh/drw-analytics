<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {

            $table->string('kanal_filter')
                  ->default('semua')
                  ->after('min_lift');

        });
    }

    public function down(): void
    {
        Schema::table('proses_analisis', function (Blueprint $table) {

            $table->dropColumn('kanal_filter');

        });
    }
};