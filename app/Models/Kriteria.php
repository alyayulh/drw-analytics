<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteria extends Model
{
    protected $table = 'kriteria';
    protected $primaryKey = 'id_kriteria';
    public $timestamps = true;

    protected $fillable = [
        'nama_kriteria',
        'tipe_atribut',
        'bobot',
        'sumber_data',
        'nama_kolom_excel', // hanya diisi jika sumber_data = 'Excel'
    ];

    // Satu kriteria menghasilkan banyak nilai produk (dari Excel)
    public function nilaiProduk()
    {
        return $this->hasMany(NilaiProduk::class, 'id_kriteria', 'id_kriteria');
    }

    // Satu kriteria memiliki banyak input permintaan (dari Manual)
    public function inputPermintaan()
    {
        return $this->hasMany(InputPermintaan::class, 'id_kriteria', 'id_kriteria');
    }
}