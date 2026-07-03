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
        'nama_file',
        'path_file',
        'status',
        'tanggal_proses',

        'min_support',
        'min_confidence',
        'min_lift',

        'total_data_awal',
        'total_data_bersih',
        'total_transaksi',
        'total_produk_unik',
        'total_operator',
        'total_frequent_itemsets',
        'total_rules',

        'rekap_produk',
        'distribusi_waktu',

        'kanal_filter',
        'pesan_error',
    ];

    protected $casts = [
        'tanggal_proses' => 'datetime',

        'min_support' => 'float',
        'min_confidence' => 'float',
        'min_lift' => 'float',

        'total_data_awal' => 'integer',
        'total_data_bersih' => 'integer',
        'total_transaksi' => 'integer',
        'total_produk_unik' => 'integer',
        'total_operator' => 'integer',
        'total_frequent_itemsets' => 'integer',
        'total_rules' => 'integer',

        'rekap_produk' => 'array',
        'distribusi_waktu' => 'array',

        'kanal_filter' => 'string',
    ];

    public function aturanAsosiasi()
    {
        return $this->hasMany(
            AturanAsosiasi::class,
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

    public function getStatusLabelAttribute()
    {
        return match (strtolower((string) $this->status)) {
            'berhasil' => 'Selesai',
            'gagal' => 'Gagal',
            'pending' => 'Diproses',
            default => $this->status,
        };
    }
}