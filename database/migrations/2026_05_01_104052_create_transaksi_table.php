<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->dateTime('tanggal_transaksi');

            $table->unsignedBigInteger('id_operator');
            $table->unsignedBigInteger('id_metode_pembayaran');

            $table->foreign('id_operator')
                  ->references('id_operator')
                  ->on('operator')
                  ->onDelete('cascade');

            $table->foreign('id_metode_pembayaran')
                  ->references('id_metode_pembayaran')
                  ->on('metode_pembayaran')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};