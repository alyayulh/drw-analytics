<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'nama_produk',
        'id_kategori',
        'status_data'
    ];

    // RENAMED: kategori() → kategoriProduk()
    // Karena tabel produk punya kolom 'kategori' (string lama),
    // Eloquent bentrok antara kolom dan nama method relasi.
    public function kategoriProduk()
    {
        return $this->belongsTo(
            KategoriProduk::class,
            'id_kategori',
            'id_kategori'
        );
    }

    public function nilaiProduk()
    {
        return $this->hasMany(NilaiProduk::class, 'id_produk', 'id_produk');
    }
}