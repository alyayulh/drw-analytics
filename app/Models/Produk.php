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

    public function kategori()
    {
        return $this->belongsTo(
            KategoriProduk::class,
            'id_kategori',
            'id_kategori'
        );
    }
}