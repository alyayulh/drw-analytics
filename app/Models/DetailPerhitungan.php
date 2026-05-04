<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model detail nilai kriteria untuk setiap produk.
 * Menyimpan nilai asli, normalisasi, bobot, dan kontribusi terhadap Yi.
 */
class DetailPerhitungan extends Model
{
    protected $table = 'detail_perhitungan';
    protected $primaryKey = 'id_detail';
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'id_hasil',
        'nama_kriteria',
        'tipe_atribut',
        'nilai_asli',
        'nilai_normal',
        'bobot',
    ];

    public function hasilPerhitungan()
    {
        return $this->belongsTo(HasilPerhitungan::class, 'id_hasil', 'id_hasil');
    }
}