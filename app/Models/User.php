<?php

namespace App\Models;

// Menggunakan class Authenticatable dari Laravel
// supaya model ini bisa digunakan untuk login/autentikasi
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // Memberitahu Laravel bahwa tabel yang digunakan
    // adalah 'user' bukan 'users' (default Laravel)
    protected $table = 'user';
    protected $username = 'username';

    // Memberitahu Laravel bahwa primary key tabel ini
    // adalah 'id_user' bukan 'id' (default Laravel)
    protected $primaryKey = 'id_user';

    // Kolom-kolom yang boleh diisi / disimpan lewat kode
    // (mass assignment) — kolom selain ini tidak bisa diisi
    protected $fillable = [
        'nama_lengkap', // nama lengkap user
        'username',     // username untuk login
        'password',     // password (akan di-hash otomatis)
        'role',         // role: 'Admin' atau 'Manajer'
        'status',       // status: 'Aktif' atau 'Nonaktif'
    ];

    // Kolom yang disembunyikan ketika data user ditampilkan
    // misal saat return response JSON — password tidak ikut tampil
    protected $hidden = [
        'password',
    ];

    public function getAuthIdentifierName()
    {
    return 'username';
    }

    public function getAuthPassword()
    {
    return $this->password;
    }
    protected $authIdentifierName = 'username';
}