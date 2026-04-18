<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InputPermintaan extends Model
{
    protected $table = 'input_permintaan';
    protected $primaryKey = 'id_input';
    public $timestamps = true;

    protected $fillable = [
        'id_produk',
        'id_kriteria',
        'nilai_input',
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