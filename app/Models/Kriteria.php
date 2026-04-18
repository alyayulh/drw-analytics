<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    // Nama tabel di database
    protected $table = 'kriteria';

    // Primary key tabel ini
    protected $primaryKey = 'id_kriteria';

    //  ada updated_at
    public $timestamps = true;

    // Kolom yang boleh diisi
    protected $fillable = [
        'nama_kriteria',     // nama kriteria
        'tipe_atribut',     // contoh: "benefit/cost"
        'bobot',            // bobot kriteria
        'sumber_data',      // total semua produk di sistem
    ];

    // Relasi ke tabel nilai,input permintaan dan mapping nama
    // Satu kriteria  menghasilkan banyak nilai
    public function nilaiProduk()
    {
    return $this->hasMany(NilaiProduk::class, 'id_kriteria', 'id_kriteria');
    }
    // Satu kriteria  memiliki banyak input nilai permintaan
    public function inputPermintaan()
    {
    return $this->hasMany(InputPermintaan::class, 'id_kriteria', 'id_kriteria');
    }
    // Satu kriteria  
    public function mappingNama()
    {   
    return $this->hasMany(MappingNama::class, 'id_kriteria', 'id_kriteria');
    }
}
