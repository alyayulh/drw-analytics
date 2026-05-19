<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AsosiasiController extends Controller
{
    private const API_TOP_N = 100;

    public function dashboard()
    {
        $data = $this->getLatestAnalysisData();

        return view('asosiasi.dashboard', [
            'summary' => $data['summary'],
            'rules' => $data['rules'],
            'dataset' => $data['dataset'],
            'topProduk' => $data['topProduk'],
            'distribusiWaktu' => $data['distribusiWaktu'],
        ]);
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
        $data = $this->getLatestAnalysisData();

        return view('asosiasi.dashboard-insight', [
            'summary' => $data['summary'],
            'rules' => $data['rules'],
        ]);
    }

    public function analisisPola()
    {
        return view('asosiasi.analisis-pola');
    }

    public function analisis()
    {
        return view('asosiasi.analisis');
    }

    public function hasil()
    {
        return $this->hasilAnalisis();
    }

    public function hasilAnalisis()
    {
        $data = $this->getLatestAnalysisData();

        return view('asosiasi.hasil', [
            'summary' => $data['summary'],
            'rules' => $data['rules'],
            'dataset' => $data['dataset'],
            'topProduk' => $data['topProduk'],
            'distribusiWaktu' => $data['distribusiWaktu'],
        ]);
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

        $data = $this->getLatestAnalysisData();

        $riwayat['rule_terbaik'] = $data['summary']['rule_terbaik'] ?? 'Belum ada rule';

        return view('asosiasi.detail-riwayat', [
            'riwayat' => $riwayat,
            'rules' => $data['rules'],
        ]);
    }

    public function prosesAnalisis(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx',
            'min_support' => 'nullable|numeric|min:0.0001|max:1',
            'min_confidence' => 'nullable|numeric|min:0.0001|max:1',
            'min_lift' => 'nullable|numeric|min:0',
        ]);

        try {
            $file = $request->file('file');
            $apiUrl = env('FPGROWTH_API_URL');

            if (!$apiUrl) {
                return back()->with('error', 'FPGROWTH_API_URL belum diatur di file .env Laravel.');
            }

            $response = Http::timeout(600)
                ->attach(
                    'file',
                    fopen($file->getRealPath(), 'r'),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl, [
                    // Parameter disamakan dengan model.py
                    'min_support' => $request->input('min_support', 0.01),
                    'min_confidence' => $request->input('min_confidence', 0.4),
                    'min_lift' => $request->input('min_lift', 1.0),

                    // Multivariable association rules: produk + operator + waktu
                    'include_operator' => 'true',
                    'include_waktu' => 'true',
                    'only_product_rules' => 'false',

                    // Supaya Laravel menerima lebih dari 20 rules.
                    // Kalau rules terbentuk 66, maka 66 bisa masuk ke tabel.
                    'top_n' => self::API_TOP_N,
                ]);

            if (!$response->successful()) {
                return back()->with('error', 'API Python gagal merespons. Status: ' . $response->status());
            }

            $apiResult = $response->json();

            if (($apiResult['status'] ?? null) !== 'success') {
                return back()->with('error', $apiResult['message'] ?? 'Analisis gagal diproses.');
            }

            $rulesReturned = is_array($apiResult['top_rules'] ?? null)
                ? count($apiResult['top_rules'])
                : 0;

            // Hapus hasil lama agar session tidak tetap memakai data lama 20 rules.
            session()->forget([
                'hasil_analisis_api',
                'dataset_info_api',
            ]);

            session([
                'hasil_analisis_api' => $apiResult,
                'dataset_info_api' => [
                    'nama_file' => $file->getClientOriginalName(),
                    'periode_data' => '-',
                    'tanggal_analisis' => Carbon::now()->translatedFormat('d F Y'),
                    'tanggal_filter' => Carbon::now()->format('Y-m-d'),
                    'status' => 'Selesai',
                    'top_n_request' => self::API_TOP_N,
                    'rules_returned' => $rulesReturned,
                ],
            ]);

            return redirect()
                ->route('asosiasi.hasil')
                ->with('success', 'Analisis dataset berhasil diproses. Rules diterima: ' . $rulesReturned);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadLaporan()
    {
        return back()->with('error', 'Fitur download laporan belum tersedia.');
    }

    private function getLatestAnalysisData()
    {
        $apiResult = session('hasil_analisis_api');

        if (!$apiResult) {
            return $this->getDummyAnalysisData();
        }

        $summaryApi = $apiResult['summary'] ?? [];
        $datasetInfo = session('dataset_info_api', []);

        $topRules = $apiResult['top_rules'] ?? [];

        if (!is_array($topRules)) {
            $topRules = [];
        }

        $summary = [
            'total_data_awal' => $summaryApi['total_data_awal'] ?? 0,
            'setelah_preprocessing' => $summaryApi['setelah_preprocessing'] ?? 0,
            'total_basket' => $summaryApi['total_basket'] ?? 0,
            'produk_unik' => $summaryApi['produk_unik'] ?? 0,
            'total_operator' => $summaryApi['operator_unik'] ?? ($summaryApi['jumlah_operator_unik'] ?? 0),
            'frequent_itemsets' => $summaryApi['frequent_itemsets'] ?? 0,
            'association_rules' => $summaryApi['association_rules'] ?? 0,
            'jumlah_anomali' => $summaryApi['jumlah_anomali'] ?? 0,
            'rule_terbaik' => $summaryApi['rule_terbaik'] ?? 'Belum ada rule',

            // Tambahan info debug ringan
            'rules_ditampilkan' => count($topRules),
            'top_n_request' => $datasetInfo['top_n_request'] ?? self::API_TOP_N,
        ];

        $dataset = [
            'nama_file' => $datasetInfo['nama_file'] ?? 'Dataset Upload',
            'periode_data' => $datasetInfo['periode_data'] ?? '-',
            'tanggal_analisis' => $datasetInfo['tanggal_analisis'] ?? Carbon::now()->translatedFormat('d F Y'),
            'jumlah_data_awal' => $summaryApi['total_data_awal'] ?? 0,
            'data_setelah_preprocessing' => $summaryApi['setelah_preprocessing'] ?? 0,
            'transaksi_refund_dihapus' => $summaryApi['jumlah_data_dihapus'] ?? 0,
            'basket_transaksi_terbentuk' => $summaryApi['total_basket'] ?? 0,
            'status' => $datasetInfo['status'] ?? 'Selesai',
        ];

        $rules = collect($topRules)->map(function ($rule, $index) {
            $support = (float) ($rule['support'] ?? 0);
            $confidence = (float) ($rule['confidence'] ?? 0);
            $lift = (float) ($rule['lift'] ?? 0);

            // Ambil langsung dari model/API Python
            $kategoriRule = $rule['kategori_rule'] ?? 'Weak Pattern';
            $isAnomaly = $this->normalizeBoolean($rule['is_anomaly'] ?? false);

            return [
                'no' => $index + 1,
                'antecedents' => $rule['antecedents_display'] ?? ($rule['antecedents_raw'] ?? '-'),
                'consequents' => $rule['consequents_display'] ?? ($rule['consequents_raw'] ?? '-'),
                'support' => $support,
                'confidence' => $confidence,
                'lift' => $lift,

                // Tetap disediakan kalau view lama masih manggil
                'operator' => $this->extractOperatorFromRule($rule),
                'kategori_waktu' => $this->extractWaktuFromRule($rule),

                // Status utama mengikuti model Python
                'kategori_rule' => $kategoriRule,
                'status' => $kategoriRule,

                // Anomali mengikuti model Python
                'is_anomaly' => $isAnomaly,
                'status_anomali' => $isAnomaly ? 'Anomali' : 'Normal',

                'interpretasi' => $this->generateInterpretasi($rule, $confidence, $lift),

                // Jenis kombinasi rule untuk filter tab
                'jenis_rule' => $this->getJenisRule($rule),
            ];
        });

        $topProduk = collect($apiResult['rekap_produk'] ?? [])->map(function ($produk) {
            return [
                'nama' => $produk['nama_produk'] ?? '-',
                'jumlah' => $produk['jumlah_terjual'] ?? 0,
            ];
        });

        $distribusiWaktu = collect($apiResult['distribusi_waktu'] ?? [])->map(function ($waktu) {
            return [
                'label' => $waktu['kategori_waktu'] ?? '-',
                'nilai' => round($waktu['persentase'] ?? 0, 2),
            ];
        });

        return [
            'summary' => $summary,
            'rules' => $rules,
            'dataset' => $dataset,
            'topProduk' => $topProduk,
            'distribusiWaktu' => $distribusiWaktu,
        ];
    }

    private function normalizeBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'ya'], true);
        }

        return false;
    }

    private function extractOperatorFromRule($rule)
    {
        $text = ($rule['antecedents_display'] ?? '') . ' ' . ($rule['consequents_display'] ?? '');

        if (preg_match('/Operator:\s*([^,]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return '-';
    }

    private function extractWaktuFromRule($rule)
    {
        $text = ($rule['antecedents_display'] ?? '') . ' ' . ($rule['consequents_display'] ?? '');

        if (preg_match('/Waktu:\s*([^,]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return '-';
    }

    private function generateInterpretasi($rule, $confidence, $lift)
    {
        $antecedents = $rule['antecedents_display'] ?? ($rule['antecedents_raw'] ?? '-');
        $consequents = $rule['consequents_display'] ?? ($rule['consequents_raw'] ?? '-');

        $kategoriRule = $rule['kategori_rule'] ?? 'Weak Pattern';
        $isAnomaly = $this->normalizeBoolean($rule['is_anomaly'] ?? false);

        $anomaliText = $isAnomaly
            ? ' Rule ini terdeteksi sebagai anomali berdasarkan pendekatan IQR pada confidence dan lift.'
            : ' Rule ini tidak terdeteksi sebagai anomali berdasarkan pendekatan IQR pada confidence dan lift.';

        return 'Jika terdapat ' . $antecedents .
            ', maka cenderung berasosiasi dengan ' . $consequents .
            ' dengan confidence ' . number_format($confidence * 100, 2) .
            '% dan lift ' . number_format($lift, 2) .
            '. Kategori rule: ' . $kategoriRule . '.' .
            $anomaliText;
    }

    private function getJenisRule($rule)
    {
        $text = strtolower(($rule['antecedents_raw'] ?? '') . ' ' . ($rule['consequents_raw'] ?? ''));

        $hasProduk = str_contains($text, 'produk_');
        $hasOperator = str_contains($text, 'operator_');
        $hasWaktu = str_contains($text, 'waktu_');

        if ($hasProduk && $hasOperator && $hasWaktu) {
            return 'produk_operator_waktu';
        }

        if ($hasProduk && $hasOperator) {
            return 'produk_operator';
        }

        if ($hasProduk && $hasWaktu) {
            return 'produk_waktu';
        }

        if ($hasOperator && $hasWaktu) {
            return 'operator_waktu';
        }

        if ($hasProduk) {
            return 'produk_produk';
        }

        return 'multivariable';
    }

    private function getDummyAnalysisData()
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
            'jumlah_anomali' => $rules->where('is_anomaly', true)->count(),
            'rule_terbaik' => $bestRule
                ? $bestRule['antecedents'] . ' → ' . $bestRule['consequents']
                : 'Belum ada rule',
            'rules_ditampilkan' => $rules->count(),
            'top_n_request' => self::API_TOP_N,
        ];

        $dataset = [
            'nama_file' => 'data_penjualan_april_2026.xlsx',
            'periode_data' => '1 April - 30 April 2026',
            'tanggal_analisis' => '8 Mei 2026',
            'jumlah_data_awal' => 1285,
            'data_setelah_preprocessing' => 1220,
            'transaksi_refund_dihapus' => 65,
            'basket_transaksi_terbentuk' => 1220,
            'status' => 'Selesai',
        ];

        $topProduk = collect([
            ['nama' => 'Serum Wajah A', 'jumlah' => 120],
            ['nama' => 'Moisturizer B', 'jumlah' => 95],
            ['nama' => 'Toner C', 'jumlah' => 80],
            ['nama' => 'Sunscreen D', 'jumlah' => 72],
            ['nama' => 'Cleanser E', 'jumlah' => 60],
        ]);

        $distribusiWaktu = collect([
            ['label' => 'Pagi', 'nilai' => 320],
            ['label' => 'Siang', 'nilai' => 450],
            ['label' => 'Sore', 'nilai' => 280],
            ['label' => 'Malam', 'nilai' => 170],
        ]);

        return [
            'summary' => $summary,
            'rules' => $rules,
            'dataset' => $dataset,
            'topProduk' => $topProduk,
            'distribusiWaktu' => $distribusiWaktu,
        ];
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
                'status' => 'Strong Pattern',
                'kategori_rule' => 'Strong Pattern',
                'is_anomaly' => false,
                'status_anomali' => 'Normal',
                'interpretasi' => 'Jika terdapat Serum Wajah A, maka cenderung berasosiasi dengan Moisturizer B.',
                'jenis_rule' => 'produk_produk',
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
                'status' => 'Strong Pattern',
                'kategori_rule' => 'Strong Pattern',
                'is_anomaly' => true,
                'status_anomali' => 'Anomali',
                'interpretasi' => 'Jika terdapat Toner C dan Operator Ani, maka cenderung berasosiasi dengan Serum Wajah A.',
                'jenis_rule' => 'produk_operator',
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
                'status' => 'Strong Pattern',
                'kategori_rule' => 'Strong Pattern',
                'is_anomaly' => false,
                'status_anomali' => 'Normal',
                'interpretasi' => 'Jika terdapat Sunscreen D, maka cenderung berasosiasi dengan Moisturizer B.',
                'jenis_rule' => 'produk_produk',
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
                'status' => 'Strong Pattern',
                'kategori_rule' => 'Strong Pattern',
                'is_anomaly' => true,
                'status_anomali' => 'Anomali',
                'interpretasi' => 'Jika terdapat Cleanser E dan Waktu Malam, maka cenderung berasosiasi dengan Face Mask F.',
                'jenis_rule' => 'produk_waktu',
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
                'status' => 'Strong Pattern',
                'kategori_rule' => 'Strong Pattern',
                'is_anomaly' => false,
                'status_anomali' => 'Normal',
                'interpretasi' => 'Jika terdapat Essence H, maka cenderung berasosiasi dengan Eye Cream G.',
                'jenis_rule' => 'produk_produk',
            ],
        ]);
    }
}