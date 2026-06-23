<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produk extends Model
{
    #Produk yang dihapus admin tidak benar-benar dihilangkan dari DB,
    # agar riwayat perhitungan tetap ada meskipun dihapus, ditandai dengan kolom deleted_at, otomatis dari laravel.
    use SoftDeletes;

    protected $table = 'produk';
    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'nama_produk',
        'id_kategori',
        'status_data'
    ];


    public function kategoriProduk()
    {
        # 1 produk dimiliki oleh 1 kategori.
        return $this->belongsTo(
            KategoriProduk::class,
            'id_kategori',
            'id_kategori'
        );
    }

    public function nilaiProduk()
    {
        #1 produk memiliki banyak nilai kriteria.
        return $this->hasMany(NilaiProduk::class, 'id_produk', 'id_produk');
    }
}