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

    protected $casts = [
        'detail' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}