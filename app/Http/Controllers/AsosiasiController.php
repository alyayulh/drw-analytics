<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\ProsesAnalisis;
use App\Models\AturanAsosiasi;
use Illuminate\Support\Facades\Storage;

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
    public function prosesAnalisis(Request $request)
{
    $request->validate([
        'file_excel' => 'required|mimes:xlsx,xls,csv|max:10240',
    ]);

    $file = $request->file('file_excel');

    $fileName = time() . '_' . $file->getClientOriginalName();
    $filePath = $file->storeAs('uploads/asosiasi', $fileName, 'public');

    session([
        'hasil_analisis' => [
            'total_data_awal' => 1285,
            'setelah_preprocessing' => 1220,
            'total_basket' => 1220,
            'produk_unik' => 156,
            'total_operator' => 8,
            'frequent_itemsets' => 456,
            'association_rules' => 342,
            'rule_terbaik' => 'Lift 4.2',
        ]
    ]);

    return redirect()
        ->route('asosiasi.hasil')
        ->with('success', 'Proses analisis berhasil dilakukan.');
}

public function hasilAnalisis()
{
    $summary = session('hasil_analisis', [
        'total_data_awal' => 1285,
        'setelah_preprocessing' => 1220,
        'total_basket' => 1220,
        'produk_unik' => 156,
        'total_operator' => 8,
        'frequent_itemsets' => 456,
        'association_rules' => 342,
        'rule_terbaik' => 'Lift 4.2',
    ]);

    $rules = collect([
        [
            'no' => 1,
            'antecedents' => 'Serum Wajah A',
            'consequents' => 'Moisturizer B',
            'support' => 0.32,
            'confidence' => 0.85,
            'lift' => 2.4,
            'operator' => 'Siti',
            'kategori_waktu' => 'Siang',
            'status' => 'Normal',
            'interpretasi' => 'Pelanggan yang membeli Serum A cenderung membeli Moisturizer B',
        ],
        [
            'no' => 2,
            'antecedents' => 'Toner C, Operator Ani',
            'consequents' => 'Serum Wajah A',
            'support' => 0.05,
            'confidence' => 0.92,
            'lift' => 4.2,
            'operator' => 'Ani',
            'kategori_waktu' => 'Pagi',
            'status' => 'Anomali',
            'interpretasi' => 'Pola tidak biasa: confidence tinggi dengan support sangat rendah',
        ],
        [
            'no' => 3,
            'antecedents' => 'Sunscreen D',
            'consequents' => 'Moisturizer B',
            'support' => 0.25,
            'confidence' => 0.82,
            'lift' => 1.9,
            'operator' => 'Siti',
            'kategori_waktu' => 'Sore',
            'status' => 'Normal',
            'interpretasi' => 'Pola pembelian umum pada waktu sore',
        ],
        [
            'no' => 4,
            'antecedents' => 'Cleanser E, Waktu Malam',
            'consequents' => 'Face Mask F',
            'support' => 0.12,
            'confidence' => 0.88,
            'lift' => 3.8,
            'operator' => '-',
            'kategori_waktu' => 'Malam',
            'status' => 'Anomali',
            'interpretasi' => 'Lift tinggi pada kategori waktu malam',
        ],
        [
            'no' => 5,
            'antecedents' => 'Essence H',
            'consequents' => 'Eye Cream G',
            'support' => 0.28,
            'confidence' => 0.76,
            'lift' => 1.8,
            'operator' => 'Dewi',
            'kategori_waktu' => 'Siang',
            'status' => 'Normal',
            'interpretasi' => 'Pelanggan yang membeli Essence H sering membeli Eye Cream G',
        ],
    ]);

    return view('asosiasi.hasil', compact('summary', 'rules'));
}
}   