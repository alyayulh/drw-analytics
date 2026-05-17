<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\Perhitungan;
use App\Models\HasilPerhitungan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil perhitungan terakhir
        $perhitunganTerakhir = Perhitungan::latest('created_at')->first();

        // Top 5 rekomendasi dari perhitungan terakhir
        $top5Rekomendasi = collect();
        $produkPrioritasUtama = null;

        if ($perhitunganTerakhir) {
            // FIX: sort konsisten dengan halaman Hasil Perhitungan (4-tingkat).
            // Sebelumnya cuma orderBy nilai_yi → bisa beda urutan kalau ada Yi tie.
            $top5Rekomendasi = HasilPerhitungan::where('id_perhitungan', $perhitunganTerakhir->id_perhitungan)
                ->orderBy('ranking')
                ->orderByDesc('total_benefit')
                ->orderBy('total_cost')
                ->orderBy('nama_produk')
                ->take(5)
                ->get();

            // Ambil produk prioritas (ranking 1)
            $produkPrioritasUtama = HasilPerhitungan::where('id_perhitungan', $perhitunganTerakhir->id_perhitungan)
                ->where('ranking', 1)
                ->orderByDesc('total_benefit')
                ->orderBy('nama_produk')
                ->first();
        }

        // Hitung total metrics
        $totalProduk = Produk::count();
        $kriterias = Kriteria::all();
        $totalKriteria = $kriterias->count();
        $totalBobot = $kriterias->sum('bobot');

        return view('spk.dashboard', compact(
            'top5Rekomendasi',
            'produkPrioritasUtama',
            'totalProduk',
            'totalKriteria',
            'totalBobot',
            'kriterias'
        ));
    }
}