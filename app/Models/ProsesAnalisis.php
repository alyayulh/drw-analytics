<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesAnalisis extends Model
{
    protected $table = 'proses_analisis';

    protected $primaryKey = 'id_proses_analisis';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'tanggal_proses' => 'datetime',
    ];

    public function aturanAsosiasi()
    {
        return $this->hasMany(
            AturanAsosiasi::class,
            'id_proses_analisis',
            'id_proses_analisis'
        );
    }
}