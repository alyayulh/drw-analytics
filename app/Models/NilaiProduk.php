<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiProduk extends Model
{
    protected $table = 'nilai_produk';
    protected $primaryKey = 'id_nilai';
    public $timestamps = true;

    protected $fillable = [
        'id_produk',
        'id_kriteria',
        'nilai',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class, 'id_kriteria', 'id_kriteria');
    }
}