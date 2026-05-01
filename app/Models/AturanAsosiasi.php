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
        'nilai_support',
        'nilai_confidence',
        'nilai_lift',
        'rule_asosiasi',
    ];
}