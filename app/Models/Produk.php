<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    // Nama tabel di database
    protected $table = 'produk';

    // Primary key tabel ini
    protected $primaryKey = 'id_produk';

    // Kolom yang boleh diisi lewat kode
    protected $fillable = [
        'nama_produk',  // nama produk
        'status_data',  // 'Lengkap' atau 'Belum Lengkap'

    ];

    // Relasi ke tabel nilai_produk
    // Satu produk bisa punya banyak nilai kriteria
    public function nilaiProduk()
    {
        return $this->hasMany(NilaiProduk::class, 'id_produk', 'id_produk');
    }

    // Relasi ke tabel input_permintaan
    // Satu produk bisa punya banyak input permintaan manual
    public function inputPermintaan()
    {
        return $this->hasMany(InputPermintaan::class, 'id_produk', 'id_produk');
    }
}