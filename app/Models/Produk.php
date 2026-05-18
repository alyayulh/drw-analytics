<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    // FIX BUG #3: Pakai SoftDeletes trait.
    // Produk yang dihapus admin tidak benar-benar dihilangkan dari DB,
    // melainkan ditandai dengan kolom `deleted_at`. Tujuan: menjaga
    // integritas riwayat perhitungan SPK — produk yang pernah masuk
    // ranking tetap bisa di-trace meskipun "dihapus".
    //use SoftDeletes;

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