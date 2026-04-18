<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilPerhitungan extends Model
{
    protected $table = 'hasil_perhitungan';
    protected $primaryKey = 'id_hasil';
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_perhitungan',
        'id_produk',
        'nama_produk',
        'nilai_yi',
        'total_benefit',
        'total_cost',
        'ranking',
        'prioritas',
    ];

    public function perhitungan()
    {
        return $this->belongsTo(Perhitungan::class, 'id_perhitungan', 'id_perhitungan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function detailPerhitungan()
    {
        return $this->hasMany(DetailPerhitungan::class, 'id_hasil', 'id_hasil');
    }
}