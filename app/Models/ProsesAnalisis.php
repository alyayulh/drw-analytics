<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesAnalisis extends Model
{
    protected $table = 'proses_analisis';
    protected $primaryKey = 'id_proses_analisis';
    public $timestamps = false;

    protected $fillable = [
        'nama_proses',
        'tanggal_proses',
        'min_support',
        'min_confidence',
        'min_lift',
    ];
}