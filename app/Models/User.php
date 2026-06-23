<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable; //kode ini mengimpor kelas User dari Laravel yang menyediakan fungsionalitas autentikasi dasar.

// membuat model User yang mewakili tabel 'user' di database. model ini digunakan untuk autentikasi user di aplikasi Laravel.
class User extends Authenticatable 
{
    //kenapa protected? karena table dan primarykey ini hanya bisa diakses oleh class ini dan turunannya, tidak bisa diakses dari luar class.
    protected $table = 'user';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama_lengkap',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function getAuthIdentifierName()
    {
        return 'id_user';
    }

    public function getAuthIdentifier()
    {
        return $this->id_user;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getAuthPasswordName()
    {
        return 'password';
    }
}