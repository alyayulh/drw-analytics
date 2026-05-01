<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\ProsesAnalisis;
use App\Models\AturanAsosiasi;

class AsosiasiController extends Controller
{
    public function dashboard()
    {
        // Hitung total transaksi
        $totalTransaksi = 0;

        // Hitung total produk
        $totalProduk = 0;

        // Ambil proses analisis terakhir
        $lastProses = ProsesAnalisis::latest('tanggal_proses')->first();

        // Hitung total itemset & rules dari proses terakhir
        $totalItemset = 0;
        $totalRules   = 0;
        $topRules     = collect();
        $top10Produk  = collect();

        if ($lastProses) {
            $totalRules = AturanAsosiasi::where('id_proses_analisis', $lastProses->id_proses_analisis)->count();

            // Top 3 rules berdasarkan lift tertinggi
            $topRules = AturanAsosiasi::where('id_proses_analisis', $lastProses->id_proses_analisis)
                ->orderByDesc('nilai_lift')
                ->limit(3)
                ->get();
        }

        return view('asosiasi.dashboard', compact(
            'totalTransaksi',
            'totalProduk',
            'totalItemset',
            'totalRules',
            'top10Produk',
            'topRules',
            'lastProses'
        ));
    }

    public function analisis()
    {
        // Halaman ini nanti diisi teman kamu
        return view('asosiasi.analisis');
    }

    public function riwayat()
    {
        // Halaman ini nanti diisi teman kamu
        return view('asosiasi.riwayat');
    }
}   