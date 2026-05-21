<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AturanAsosiasi extends Model
{
    protected $table = 'aturan_asosiasi';

    protected $primaryKey = 'id_aturan_asosiasi';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'is_anomaly' => 'boolean',
    ];

    public function prosesAnalisis()
    {
        return $this->belongsTo(
            ProsesAnalisis::class,
            'id_proses_analisis',
            'id_proses_analisis'
        );
    }
}