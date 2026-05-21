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
        'rekap_produk' => 'array',
        'distribusi_waktu' => 'array',
        'min_support' => 'float',
        'min_confidence' => 'float',
        'min_lift' => 'float',
        'total_data_awal' => 'integer',
        'total_data_bersih' => 'integer',
        'total_transaksi' => 'integer',
        'total_produk_unik' => 'integer',
        'total_frequent_itemsets' => 'integer',
        'total_rules' => 'integer',
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