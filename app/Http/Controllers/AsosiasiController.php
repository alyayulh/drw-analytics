<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AsosiasiController extends Controller
{
    public function dashboard()
    {
        return view('asosiasi.dashboard');
    }

    public function kelolaKriteria()
    {
        return view('asosiasi.kelola-kriteria');
    }

    public function dataProduk()
    {
        return view('asosiasi.data-produk');
    }

    public function inputPermintaan()
    {
        return view('asosiasi.input-permintaan');
    }

    public function menghitungPrioritas()
    {
        return view('asosiasi.menghitung-prioritas');
    }

    public function dashboardInsight()
    {
        return view('asosiasi.dashboard-insight');
    }

    public function analisisPola()
    {
        return view('asosiasi.analisis-pola');
    }

    public function hasil()
    {
        $rules = $this->getDummyRules();

        $bestRule = $rules->sortByDesc('lift')->first();

        $summary = [
            'total_data_awal' => 1285,
            'setelah_preprocessing' => 1220,
            'total_basket' => 1220,
            'produk_unik' => 156,
            'total_operator' => 8,
            'frequent_itemsets' => 456,
            'association_rules' => 342,
            'rule_terbaik' => $bestRule
                ? $bestRule['antecedents'] . ' → ' . $bestRule['consequents']
                : 'Belum ada rule',
        ];

        return view('asosiasi.hasil', compact('summary', 'rules'));
    }

    public function riwayat()
    {
        $riwayats = $this->getDummyRiwayat();

        return view('asosiasi.riwayat', compact('riwayats'));
    }

    public function detailRiwayat($id)
    {
        $riwayat = $this->getDummyRiwayat()->firstWhere('id', (int) $id);

        if (!$riwayat) {
            abort(404);
        }

        $rules = $this->getDummyRules();

        $bestRule = $rules->sortByDesc('lift')->first();

        $riwayat['rule_terbaik'] = $bestRule
            ? $bestRule['antecedents'] . ' → ' . $bestRule['consequents']
            : 'Belum ada rule';

        return view('asosiasi.detail-riwayat', compact('riwayat', 'rules'));
    }

    private function getDummyRiwayat()
    {
        return collect([
            [
                'id' => 1,
                'tanggal_analisis' => '8 Mei 2026',
                'tanggal_filter' => '2026-05-08',
                'nama_file' => 'data_penjualan_april_2026.xlsx',
                'periode_data' => '1 April - 30 April 2026',
                'total_data_awal' => 1285,
                'setelah_preprocessing' => 1220,
                'total_basket' => 1220,
                'produk_unik' => 156,
                'total_operator' => 8,
                'frequent_itemsets' => 456,
                'association_rules' => 342,
                'rule_terbaik' => 'Toner C, Operator Ani → Serum Wajah A',
                'status' => 'Selesai',
            ],
            [
                'id' => 2,
                'tanggal_analisis' => '5 Mei 2026',
                'tanggal_filter' => '2026-05-05',
                'nama_file' => 'sales_data_maret_2026.xlsx',
                'periode_data' => '1 Maret - 31 Maret 2026',
                'total_data_awal' => 1156,
                'setelah_preprocessing' => 1098,
                'total_basket' => 1098,
                'produk_unik' => 142,
                'total_operator' => 7,
                'frequent_itemsets' => 398,
                'association_rules' => 298,
                'rule_terbaik' => 'Serum Wajah A → Moisturizer B',
                'status' => 'Selesai',
            ],
            [
                'id' => 3,
                'tanggal_analisis' => '2 Mei 2026',
                'tanggal_filter' => '2026-05-02',
                'nama_file' => 'transaksi_februari_2026.xlsx',
                'periode_data' => '1 Februari - 28 Februari 2026',
                'total_data_awal' => 987,
                'setelah_preprocessing' => 945,
                'total_basket' => 945,
                'produk_unik' => 128,
                'total_operator' => 6,
                'frequent_itemsets' => 341,
                'association_rules' => 256,
                'rule_terbaik' => 'Cleanser E, Waktu Malam → Face Mask F',
                'status' => 'Selesai',
            ],
        ]);
    }

    private function getDummyRules()
    {
        return collect([
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
                'interpretasi' => 'Pelanggan yang membeli Serum Wajah A cenderung membeli Moisturizer B',
                'kategori_rule' => 'produk_produk',
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
                'kategori_rule' => 'produk_operator',
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
                'kategori_rule' => 'produk_produk',
            ],
            [
                'no' => 4,
                'antecedents' => 'Cleanser E, Waktu Malam',
                'consequents' => 'Face Mask F',
                'support' => 0.12,
                'confidence' => 0.88,
                'lift' => 3.8,
                'operator' => 'Rina',
                'kategori_waktu' => 'Malam',
                'status' => 'Anomali',
                'interpretasi' => 'Rule memiliki lift tinggi pada kategori waktu tertentu',
                'kategori_rule' => 'kategori_waktu',
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
                'interpretasi' => 'Produk sering muncul sebagai kombinasi pembelian',
                'kategori_rule' => 'produk_produk',
            ],
        ]);
    }
}