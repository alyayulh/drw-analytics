<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MappingNama extends Model
{
    protected $table = 'mapping_nama';
    protected $primaryKey = 'id_mapping';
    public $timestamps = true;

    protected $fillable = [
        'nama_kolom_excel',
        'id_kriteria',
    ];

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class, 'id_kriteria', 'id_kriteria');
    }
}