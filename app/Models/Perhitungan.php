<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model metadata perhitungan SPK.
 * Menyimpan header perhitungan, snapshot bobot, matriks keputusan, dan relasi ke hasil per produk.
 */
class Perhitungan extends Model
{
    // Nama tabel di database
    protected $table = 'perhitungan';

    // Primary key tabel ini
    protected $primaryKey = 'id_perhitungan';

    // Tidak ada updated_at karena tabel hanya punya created_at
    public $timestamps = false;  // matikan otomatis dua-duanya
    const CREATED_AT = 'created_at';  // tapi aktifkan created_at saja secara manual

    // Kolom yang boleh diisi
    protected $fillable = [
        'id_user',           // user yang melakukan perhitungan
        'periode_data',      // contoh: "November 2025"
        'jumlah_produk',     // jumlah produk yang dihitung
        'total_produk',      // total semua produk di sistem
        'produk_prioritas',  // nama produk ranking #1
        'bobot_snapshot',    // snapshot bobot kriteria saat dihitung (JSON)
        'matriks_keputusan', // matriks keputusan awal (JSON)
        'matriks_normal',    // matriks normalisasi MOORA (JSON)
    ];

    // Cast kolom JSON supaya otomatis jadi array saat dibaca
    protected $casts = [
        'bobot_snapshot'    => 'array',
        'matriks_keputusan' => 'array',
        'matriks_normal'    => 'array',
    ];
    //array itu kumpulan data yg tipe datanya sama.

    // Relasi ke tabel user
    // Satu perhitungan dilakukan oleh satu user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Relasi ke tabel hasil_perhitungan
    // Satu perhitungan menghasilkan banyak hasil (per produk)
    public function hasilPerhitungan()
    {
        return $this->hasMany(HasilPerhitungan::class, 'id_perhitungan', 'id_perhitungan');
    }
}