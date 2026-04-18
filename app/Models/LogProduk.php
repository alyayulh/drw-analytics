<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogProduk extends Model
{
    protected $table = 'log_produk';
    protected $primaryKey = 'id_log';
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_user',
        'id_produk',
        'aksi',
        'nama_produk',
        'keterangan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}