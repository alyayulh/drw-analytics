<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogKriteria extends Model
{
    protected $table = 'log_kriteria';
    protected $primaryKey = 'id_log';
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_user',
        'id_kriteria',
        'aksi',
        'nama_kriteria',
        'detail',
    ];

    #kolom detail dibaca sebagai array. biasanya detail disimpan dalam JSON.
    protected $casts = [
        'detail' => 'array',
    ];

    public function user()
    {
        #1logkriteria dimiliki 1 user
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}