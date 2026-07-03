<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AturanAsosiasi extends Model
{
    protected $table = 'aturan_asosiasi';

    protected $primaryKey = 'id_aturan_asosiasi';

    public $timestamps = false;

    protected $fillable = [
        'id_proses_analisis',
        'rule_asosiasi',
        'nilai_support',
        'nilai_confidence',
        'nilai_lift',
        'kategori_rule',
        'is_anomaly',
        'kanal_filter',
    ];

    protected $casts = [
        'id_proses_analisis' => 'integer',
        'nilai_support' => 'float',
        'nilai_confidence' => 'float',
        'nilai_lift' => 'float',
        'is_anomaly' => 'boolean',
        'kanal_filter' => 'string',
    ];

    public function prosesAnalisis()
    {
        return $this->belongsTo(
            ProsesAnalisis::class,
            'id_proses_analisis',
            'id_proses_analisis'
        );
    }

    public function getKanalFilterLabelAttribute()
    {
        return match (strtolower((string) $this->kanal_filter)) {
            'offline' => 'Offline',
            'online' => 'Online',
            default => 'Semua Kanal',
        };
    }
}