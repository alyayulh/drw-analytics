<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('nama_lengkap', 150);
            $table->string('username', 100)->unique();
            $table->string('password', 255);
            $table->enum('role', ['Admin', 'Manajer'])->default('Manajer');
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->timestamps(); // created_at & updated_at
        });

        // Seed 2 akun default langsung di migration
        // supaya sistem bisa langsung dipakai setelah migrate
        DB::table('user')->insert([
            [
                'nama_lengkap' => 'Administrator',
                'username'     => 'admin',
                'password'     => Hash::make('admin123'),
                'role'         => 'Admin',
                'status'       => 'Aktif',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'nama_lengkap' => 'Manajer DRW',
                'username'     => 'manajer',
                'password'     => Hash::make('drw2025'),
                'role'         => 'Manajer',
                'status'       => 'Aktif',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
