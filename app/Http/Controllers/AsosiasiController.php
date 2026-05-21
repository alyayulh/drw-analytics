<?php

namespace App\Http\Controllers;

use App\Exports\DashboardLaporanExport;
use App\Models\AturanAsosiasi;
use App\Models\ProsesAnalisis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

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
        $riwayats = $this->getRiwayatFromDatabase();

        $summary = [
            'total_analisis' => ProsesAnalisis::count(),
            'analisis_terakhir' => optional(
                ProsesAnalisis::orderByDesc('tanggal_proses')->first()
            )->tanggal_proses,
            'total_file_diproses' => ProsesAnalisis::where('status', 'berhasil')->count(),
            'total_rules' => AturanAsosiasi::count(),
        ];

        return view('asosiasi.riwayat', compact('riwayats', 'summary'));
    }

    public function detailRiwayat($id)
    {
        $proses = ProsesAnalisis::with('aturanAsosiasi')
            ->where('id_proses_analisis', $id)
            ->firstOrFail();

        $data = $this->getAnalysisDataFromDatabase($proses);
        $riwayat = $this->formatRiwayatItem($proses);

        return view('asosiasi.detail-riwayat', [
            'riwayat' => $riwayat,
            'rules' => $data['rules'],
            'summary' => $data['summary'],
            'dataset' => $data['dataset'],
        ]);
    }

    public function destroyRiwayat($id)
    {
        try {
            $proses = ProsesAnalisis::where('id_proses_analisis', $id)->firstOrFail();

            $pathFile = $proses->path_file;

            DB::transaction(function () use ($proses) {
                AturanAsosiasi::where('id_proses_analisis', $proses->id_proses_analisis)->delete();

                $proses->delete();
            });

            if ($pathFile && Storage::disk('public')->exists($pathFile)) {
                Storage::disk('public')->delete($pathFile);
            }

            session()->forget([
                'hasil_analisis_api',
                'dataset_info_api',
            ]);

            return redirect()
                ->route('asosiasi.riwayat')
                ->with('success', 'Riwayat analisis berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('asosiasi.riwayat')
                ->with('error', 'Gagal menghapus riwayat analisis: ' . $e->getMessage());
        }
    }

    public function prosesAnalisis(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx',
            'min_support' => 'nullable|numeric|min:0.0001|max:1',
            'min_confidence' => 'nullable|numeric|min:0.0001|max:1',
            'min_lift' => 'nullable|numeric|min:0',
        ]);

        $file = $request->file('file');
        $apiUrl = env('FPGROWTH_API_URL');

        if (!$apiUrl) {
            return back()->with('error', 'FPGROWTH_API_URL belum diatur di file .env Laravel.');
        }

        $minSupport = (float) $request->input('min_support', 0.01);
        $minConfidence = (float) $request->input('min_confidence', 0.4);
        $minLift = (float) $request->input('min_lift', 1.0);

        $proses = null;
        $stream = null;

        try {
            $path = $file->store('datasets', 'public');

            $createData = [
                'nama_proses' => 'Analisis ' . $file->getClientOriginalName(),
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'status' => 'pending',
                'tanggal_proses' => now(),
                'min_support' => $minSupport,
                'min_confidence' => $minConfidence,
                'min_lift' => $minLift,
                'total_data_awal' => 0,
                'total_data_bersih' => 0,
                'total_transaksi' => 0,
                'total_produk_unik' => 0,
                'total_frequent_itemsets' => 0,
                'total_rules' => 0,
                'pesan_error' => null,
            ];

            if ($this->prosesAnalisisHasColumn('total_operator')) {
                $createData['total_operator'] = 0;
            }

            if ($this->prosesAnalisisHasColumn('rekap_produk')) {
                $createData['rekap_produk'] = [];
            }

            if ($this->prosesAnalisisHasColumn('distribusi_waktu')) {
                $createData['distribusi_waktu'] = [];
            }

            $proses = ProsesAnalisis::create($createData);

            $stream = fopen($file->getRealPath(), 'r');

            $response = Http::timeout(600)
                ->attach('file', $stream, $file->getClientOriginalName())
                ->post($apiUrl, [
                    'min_support' => $minSupport,
                    'min_confidence' => $minConfidence,
                    'min_lift' => $minLift,
                    'include_operator' => 'true',
                    'include_waktu' => 'true',
                    'only_product_rules' => 'false',
                    'top_n' => self::API_TOP_N,
                ]);

            if (is_resource($stream)) {
                fclose($stream);
                $stream = null;
            }

            if (!$response->successful()) {
                $message = 'API Python gagal merespons. Status: ' . $response->status();

                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => $message,
                ]);

                return back()->with('error', $message);
            }

            $apiResult = $response->json();

            if (($apiResult['status'] ?? null) !== 'success') {
                $message = $apiResult['message'] ?? 'Analisis gagal diproses.';

                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => $message,
                ]);

                return back()->with('error', $message);
            }

            $topRules = $apiResult['top_rules']
                ?? $apiResult['rules']
                ?? $apiResult['association_rules_data']
                ?? [];

            if (!is_array($topRules)) {
                $topRules = [];
            }

            $summaryApi = $apiResult['summary'] ?? [];
            $rulesReturned = count($topRules);

            DB::transaction(function () use ($proses, $summaryApi, $topRules, $rulesReturned, $apiResult) {
                $totalOperator = $this->getSummaryInt($summaryApi, [
                    'operator_unik',
                    'jumlah_operator_unik',
                    'total_operator',
                ]);

                $updateData = [
                    'status' => 'berhasil',
                    'total_data_awal' => (int) ($summaryApi['total_data_awal'] ?? 0),
                    'total_data_bersih' => (int) ($summaryApi['setelah_preprocessing'] ?? $summaryApi['total_data_bersih'] ?? 0),
                    'total_transaksi' => (int) ($summaryApi['total_basket'] ?? $summaryApi['total_transaksi'] ?? 0),
                    'total_produk_unik' => (int) ($summaryApi['produk_unik'] ?? $summaryApi['total_produk_unik'] ?? 0),
                    'total_frequent_itemsets' => (int) ($summaryApi['frequent_itemsets'] ?? $summaryApi['total_frequent_itemsets'] ?? 0),
                    'total_rules' => (int) ($summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? $rulesReturned),
                    'pesan_error' => null,
                ];

                if ($this->prosesAnalisisHasColumn('total_operator')) {
                    $updateData['total_operator'] = $totalOperator;
                }

                if ($this->prosesAnalisisHasColumn('rekap_produk')) {
                    $updateData['rekap_produk'] = $apiResult['rekap_produk'] ?? [];
                }

                if ($this->prosesAnalisisHasColumn('distribusi_waktu')) {
                    $updateData['distribusi_waktu'] = $apiResult['distribusi_waktu'] ?? [];
                }

                $proses->update($updateData);

                AturanAsosiasi::where('id_proses_analisis', $proses->id_proses_analisis)->delete();

                foreach ($topRules as $rule) {
                    $support = (float) ($rule['support'] ?? 0);
                    $confidence = (float) ($rule['confidence'] ?? 0);
                    $lift = (float) ($rule['lift'] ?? 0);

                    $ruleData = [
                        'id_proses_analisis' => $proses->id_proses_analisis,
                        'nilai_support' => $support,
                        'nilai_confidence' => $confidence,
                        'nilai_lift' => $lift,
                        'rule_asosiasi' => $this->makeRuleText($rule),
                    ];

                    if ($this->aturanAsosiasiHasColumn('kategori_rule')) {
                        $ruleData['kategori_rule'] = $rule['kategori_rule'] ?? $this->getKategoriRule($confidence, $lift);
                    }

                    if ($this->aturanAsosiasiHasColumn('is_anomaly')) {
                        $ruleData['is_anomaly'] = $this->normalizeBoolean($rule['is_anomaly'] ?? false);
                    }

                    AturanAsosiasi::create($ruleData);
                }
            });

            session()->forget([
                'hasil_analisis_api',
                'dataset_info_api',
            ]);

            session([
                'hasil_analisis_api' => $apiResult,
                'dataset_info_api' => [
                    'id_proses_analisis' => $proses->id_proses_analisis,
                    'nama_file' => $file->getClientOriginalName(),
                    'path_file' => $path,
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
                ->with('success', 'Analisis dataset berhasil diproses dan disimpan ke riwayat. Rules diterima: ' . $rulesReturned);
        } catch (\Exception $e) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($proses) {
                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => $e->getMessage(),
                ]);
            }

            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadLaporan()
    {
        $data = $this->getLatestAnalysisData();

        $fileName = 'laporan_dashboard_analisis_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new DashboardLaporanExport($data), $fileName);
    }

    private function getLatestAnalysisData()
    {
        $apiResult = session('hasil_analisis_api');

        if ($apiResult) {
            return $this->getAnalysisDataFromSession($apiResult);
        }

        $latestProses = ProsesAnalisis::with('aturanAsosiasi')
            ->where('status', 'berhasil')
            ->orderByDesc('tanggal_proses')
            ->first();

        if ($latestProses) {
            return $this->getAnalysisDataFromDatabase($latestProses);
        }

        return $this->getEmptyAnalysisData();
    }

    private function getAnalysisDataFromSession(array $apiResult)
    {
        $summaryApi = $apiResult['summary'] ?? [];
        $datasetInfo = session('dataset_info_api', []);

        $topRules = $apiResult['top_rules']
            ?? $apiResult['rules']
            ?? $apiResult['association_rules_data']
            ?? [];

        if (!is_array($topRules)) {
            $topRules = [];
        }

        $summary = [
            'total_data_awal' => $summaryApi['total_data_awal'] ?? 0,
            'setelah_preprocessing' => $summaryApi['setelah_preprocessing'] ?? $summaryApi['total_data_bersih'] ?? 0,
            'total_basket' => $summaryApi['total_basket'] ?? $summaryApi['total_transaksi'] ?? 0,
            'produk_unik' => $summaryApi['produk_unik'] ?? $summaryApi['total_produk_unik'] ?? 0,
            'total_operator' => $summaryApi['operator_unik'] ?? ($summaryApi['jumlah_operator_unik'] ?? ($summaryApi['total_operator'] ?? 0)),
            'frequent_itemsets' => $summaryApi['frequent_itemsets'] ?? $summaryApi['total_frequent_itemsets'] ?? 0,
            'association_rules' => $summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? count($topRules),
            'jumlah_anomali' => $summaryApi['jumlah_anomali'] ?? 0,
            'rule_terbaik' => $summaryApi['rule_terbaik'] ?? $this->getBestRuleTextFromApiRules($topRules),
            'rules_ditampilkan' => count($topRules),
            'top_n_request' => $datasetInfo['top_n_request'] ?? self::API_TOP_N,
        ];

        $dataset = [
            'nama_file' => $datasetInfo['nama_file'] ?? 'Dataset Upload',
            'periode_data' => $datasetInfo['periode_data'] ?? '-',
            'tanggal_analisis' => $datasetInfo['tanggal_analisis'] ?? Carbon::now()->translatedFormat('d F Y'),
            'jumlah_data_awal' => $summary['total_data_awal'],
            'data_setelah_preprocessing' => $summary['setelah_preprocessing'],
            'transaksi_refund_dihapus' => $summaryApi['jumlah_data_dihapus'] ?? 0,
            'basket_transaksi_terbentuk' => $summary['total_basket'],
            'status' => $datasetInfo['status'] ?? 'Selesai',
        ];

        $rules = collect($topRules)->map(function ($rule, $index) {
            $support = (float) ($rule['support'] ?? 0);
            $confidence = (float) ($rule['confidence'] ?? 0);
            $lift = (float) ($rule['lift'] ?? 0);

            $antecedents = $this->normalizeRulePart(
                $rule['antecedents_display'] ?? ($rule['antecedents_raw'] ?? ($rule['antecedents'] ?? '-'))
            );

            $consequents = $this->normalizeRulePart(
                $rule['consequents_display'] ?? ($rule['consequents_raw'] ?? ($rule['consequents'] ?? '-'))
            );

            $kategoriRule = $rule['kategori_rule'] ?? $this->getKategoriRule($confidence, $lift);
            $isAnomaly = $this->normalizeBoolean($rule['is_anomaly'] ?? false);

            $ruleArray = [
                'antecedents_display' => $antecedents,
                'consequents_display' => $consequents,
                'antecedents_raw' => $antecedents,
                'consequents_raw' => $consequents,
                'kategori_rule' => $kategoriRule,
                'is_anomaly' => $isAnomaly,
            ];

            return [
                'no' => $index + 1,
                'antecedents' => $antecedents,
                'consequents' => $consequents,
                'support' => $support,
                'confidence' => $confidence,
                'lift' => $lift,
                'operator' => $this->extractOperatorFromRule($ruleArray),
                'kategori_waktu' => $this->extractWaktuFromRule($ruleArray),
                'kategori_rule' => $kategoriRule,
                'status' => $kategoriRule,
                'is_anomaly' => $isAnomaly,
                'status_anomali' => $isAnomaly ? 'Anomali' : 'Normal',
                'interpretasi' => $this->generateInterpretasi($ruleArray, $confidence, $lift),
                'jenis_rule' => $this->getJenisRule($ruleArray),
            ];
        });

        $topProduk = collect($apiResult['rekap_produk'] ?? [])
            ->map(function ($produk) {
                return [
                    'nama' => $produk['nama'] ?? $produk['nama_produk'] ?? '-',
                    'jumlah' => (int) ($produk['jumlah'] ?? $produk['jumlah_terjual'] ?? 0),
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();

        $distribusiWaktu = collect($apiResult['distribusi_waktu'] ?? [])
            ->map(function ($waktu) {
                return [
                    'label' => $waktu['label'] ?? $waktu['kategori_waktu'] ?? '-',
                    'nilai' => round((float) ($waktu['nilai'] ?? $waktu['persentase'] ?? 0), 2),
                ];
            })
            ->values();

        return [
            'summary' => $summary,
            'rules' => $rules,
            'dataset' => $dataset,
            'topProduk' => $topProduk,
            'distribusiWaktu' => $distribusiWaktu,
        ];
    }

    private function getAnalysisDataFromDatabase(?ProsesAnalisis $proses = null)
    {
        if (!$proses) {
            $proses = ProsesAnalisis::with('aturanAsosiasi')
                ->where('status', 'berhasil')
                ->orderByDesc('tanggal_proses')
                ->first();
        }

        if (!$proses) {
            return $this->getEmptyAnalysisData();
        }

        $rulesDb = $proses->aturanAsosiasi ?? collect();
        $bestRule = $rulesDb->sortByDesc('nilai_lift')->first();

        $jumlahAnomali = $rulesDb->filter(function ($rule) {
            return $this->normalizeBoolean($rule->is_anomaly ?? false);
        })->count();

        $totalOperator = $this->getTotalOperatorFromProsesOrRules($proses, $rulesDb);

        $summary = [
            'total_data_awal' => $proses->total_data_awal ?? 0,
            'setelah_preprocessing' => $proses->total_data_bersih ?? 0,
            'total_basket' => $proses->total_transaksi ?? 0,
            'produk_unik' => $proses->total_produk_unik ?? 0,
            'total_operator' => $totalOperator,
            'frequent_itemsets' => $proses->total_frequent_itemsets ?? 0,
            'association_rules' => $proses->total_rules ?? $rulesDb->count(),
            'jumlah_anomali' => $jumlahAnomali,
            'rule_terbaik' => $bestRule ? $bestRule->rule_asosiasi : 'Belum ada rule',
            'rules_ditampilkan' => $rulesDb->count(),
            'top_n_request' => self::API_TOP_N,
        ];

        $tanggal = $proses->tanggal_proses
            ? Carbon::parse($proses->tanggal_proses)
            : now();

        $dataset = [
            'nama_file' => $proses->nama_file ?? $proses->nama_proses ?? 'Dataset Upload',
            'periode_data' => '-',
            'tanggal_analisis' => $tanggal->translatedFormat('d F Y'),
            'jumlah_data_awal' => $proses->total_data_awal ?? 0,
            'data_setelah_preprocessing' => $proses->total_data_bersih ?? 0,
            'transaksi_refund_dihapus' => 0,
            'basket_transaksi_terbentuk' => $proses->total_transaksi ?? 0,
            'status' => $this->formatStatus($proses->status ?? 'berhasil'),
        ];

        $rules = $rulesDb->values()->map(function ($rule, $index) {
            [$antecedents, $consequents] = $this->splitRuleText($rule->rule_asosiasi);

            $support = (float) ($rule->nilai_support ?? 0);
            $confidence = (float) ($rule->nilai_confidence ?? 0);
            $lift = (float) ($rule->nilai_lift ?? 0);

            $kategoriRule = $rule->kategori_rule ?: $this->getKategoriRule($confidence, $lift);
            $isAnomaly = $this->normalizeBoolean($rule->is_anomaly ?? false);

            $ruleArray = [
                'antecedents_display' => $antecedents,
                'consequents_display' => $consequents,
                'antecedents_raw' => $antecedents,
                'consequents_raw' => $consequents,
                'kategori_rule' => $kategoriRule,
                'is_anomaly' => $isAnomaly,
            ];

            return [
                'no' => $index + 1,
                'antecedents' => $antecedents,
                'consequents' => $consequents,
                'support' => $support,
                'confidence' => $confidence,
                'lift' => $lift,
                'operator' => $this->extractOperatorFromRule($ruleArray),
                'kategori_waktu' => $this->extractWaktuFromRule($ruleArray),
                'kategori_rule' => $kategoriRule,
                'status' => $kategoriRule,
                'is_anomaly' => $isAnomaly,
                'status_anomali' => $isAnomaly ? 'Anomali' : 'Normal',
                'interpretasi' => $this->generateInterpretasi($ruleArray, $confidence, $lift),
                'jenis_rule' => $this->getJenisRule($ruleArray),
            ];
        });

        $rekapProdukRaw = $proses->rekap_produk ?? [];
        $distribusiWaktuRaw = $proses->distribusi_waktu ?? [];

        if (is_string($rekapProdukRaw)) {
            $rekapProdukRaw = json_decode($rekapProdukRaw, true) ?? [];
        }

        if (is_string($distribusiWaktuRaw)) {
            $distribusiWaktuRaw = json_decode($distribusiWaktuRaw, true) ?? [];
        }

        $topProduk = collect($rekapProdukRaw)
            ->map(function ($produk) {
                return [
                    'nama' => $produk['nama'] ?? $produk['nama_produk'] ?? '-',
                    'jumlah' => (int) ($produk['jumlah'] ?? $produk['jumlah_terjual'] ?? 0),
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();

        $distribusiWaktu = collect($distribusiWaktuRaw)
            ->map(function ($waktu) {
                return [
                    'label' => $waktu['label'] ?? $waktu['kategori_waktu'] ?? '-',
                    'nilai' => round((float) ($waktu['nilai'] ?? $waktu['persentase'] ?? 0), 2),
                ];
            })
            ->values();

        return [
            'summary' => $summary,
            'rules' => $rules,
            'dataset' => $dataset,
            'topProduk' => $topProduk,
            'distribusiWaktu' => $distribusiWaktu,
        ];
    }

    private function getRiwayatFromDatabase()
    {
        return ProsesAnalisis::with('aturanAsosiasi')
            ->orderByDesc('tanggal_proses')
            ->get()
            ->map(function ($proses) {
                return $this->formatRiwayatItem($proses);
            });
    }

    private function formatRiwayatItem(ProsesAnalisis $proses)
    {
        $tanggal = $proses->tanggal_proses
            ? Carbon::parse($proses->tanggal_proses)
            : now();

        $rules = $proses->aturanAsosiasi ?? collect();
        $bestRule = $rules->sortByDesc('nilai_lift')->first();

        return [
            'id' => $proses->id_proses_analisis,
            'tanggal_analisis' => $tanggal->translatedFormat('d F Y'),
            'tanggal_filter' => $tanggal->format('Y-m-d'),
            'nama_file' => $proses->nama_file ?? $proses->nama_proses ?? '-',
            'periode_data' => '-',
            'total_data_awal' => $proses->total_data_awal ?? 0,
            'setelah_preprocessing' => $proses->total_data_bersih ?? 0,
            'total_basket' => $proses->total_transaksi ?? 0,
            'produk_unik' => $proses->total_produk_unik ?? 0,
            'total_operator' => $this->getTotalOperatorFromProsesOrRules($proses, $rules),
            'frequent_itemsets' => $proses->total_frequent_itemsets ?? 0,
            'association_rules' => $proses->total_rules ?? $rules->count(),
            'rule_terbaik' => $bestRule ? $bestRule->rule_asosiasi : 'Belum ada rule',
            'status' => $this->formatStatus($proses->status ?? '-'),
            'min_support' => $proses->min_support ?? 0,
            'min_confidence' => $proses->min_confidence ?? 0,
            'min_lift' => $proses->min_lift ?? 0,
            'pesan_error' => $proses->pesan_error ?? null,
        ];
    }

    private function makeRuleText(array $rule)
    {
        if (!empty($rule['rule_asosiasi'])) {
            return $rule['rule_asosiasi'];
        }

        if (!empty($rule['rule'])) {
            return $rule['rule'];
        }

        $antecedents = $rule['antecedents_display']
            ?? $rule['antecedents_raw']
            ?? $rule['antecedents']
            ?? '-';

        $consequents = $rule['consequents_display']
            ?? $rule['consequents_raw']
            ?? $rule['consequents']
            ?? '-';

        $antecedents = $this->normalizeRulePart($antecedents);
        $consequents = $this->normalizeRulePart($consequents);

        return $antecedents . ' → ' . $consequents;
    }

    private function normalizeRulePart($value)
    {
        if (is_array($value)) {
            return implode(', ', $value);
        }

        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
    }

    private function splitRuleText($text)
    {
        $text = $text ?: '-';

        if (str_contains($text, '→')) {
            $parts = explode('→', $text, 2);

            return [
                trim($parts[0] ?? '-'),
                trim($parts[1] ?? '-'),
            ];
        }

        if (str_contains($text, '->')) {
            $parts = explode('->', $text, 2);

            return [
                trim($parts[0] ?? '-'),
                trim($parts[1] ?? '-'),
            ];
        }

        return [$text, '-'];
    }

    private function getBestRuleTextFromApiRules(array $rules)
    {
        if (empty($rules)) {
            return 'Belum ada rule';
        }

        $bestRule = collect($rules)->sortByDesc(function ($rule) {
            return (float) ($rule['lift'] ?? 0);
        })->first();

        return $bestRule ? $this->makeRuleText($bestRule) : 'Belum ada rule';
    }

    private function formatStatus($status)
    {
        return match ($status) {
            'berhasil' => 'Selesai',
            'gagal' => 'Gagal',
            'pending' => 'Diproses',
            default => $status,
        };
    }

    private function getKategoriRule($confidence, $lift)
    {
        if ($confidence >= 0.55 && $lift >= 1.3) {
            return 'Strong Pattern';
        }

        if ($confidence >= 0.4 && $lift > 1.0) {
            return 'Moderate Pattern';
        }

        return 'Weak Pattern';
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
        $text = strtolower(
            $this->normalizeRulePart($rule['antecedents_display'] ?? ($rule['antecedents_raw'] ?? '')) .
            ' ' .
            $this->normalizeRulePart($rule['consequents_display'] ?? ($rule['consequents_raw'] ?? ''))
        );

        if (preg_match('/operator\s*:\s*([^,]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/operator_([^,]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return '-';
    }

    private function extractWaktuFromRule($rule)
    {
        $text = strtolower(
            $this->normalizeRulePart($rule['antecedents_display'] ?? ($rule['antecedents_raw'] ?? '')) .
            ' ' .
            $this->normalizeRulePart($rule['consequents_display'] ?? ($rule['consequents_raw'] ?? ''))
        );

        if (preg_match('/waktu\s*:\s*([^,]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/waktu_([^,]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        if (str_contains($text, 'malam')) {
            return 'Malam';
        }

        if (str_contains($text, 'pagi')) {
            return 'Pagi';
        }

        if (str_contains($text, 'siang')) {
            return 'Siang';
        }

        if (str_contains($text, 'sore')) {
            return 'Sore';
        }

        return '-';
    }

    private function generateInterpretasi($rule, $confidence, $lift)
    {
        $antecedents = $this->normalizeRulePart(
            $rule['antecedents_display'] ?? ($rule['antecedents_raw'] ?? ($rule['antecedents'] ?? '-'))
        );

        $consequents = $this->normalizeRulePart(
            $rule['consequents_display'] ?? ($rule['consequents_raw'] ?? ($rule['consequents'] ?? '-'))
        );

        $kategoriRule = $rule['kategori_rule'] ?? $this->getKategoriRule($confidence, $lift);
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
        $antecedents = $this->normalizeRulePart(
            $rule['antecedents_raw'] ?? ($rule['antecedents_display'] ?? ($rule['antecedents'] ?? ''))
        );

        $consequents = $this->normalizeRulePart(
            $rule['consequents_raw'] ?? ($rule['consequents_display'] ?? ($rule['consequents'] ?? ''))
        );

        $hasProduk = $this->containsProductItem($antecedents) ||
            $this->containsProductItem($consequents);

        $hasOperator = $this->containsOperatorItem($antecedents) ||
            $this->containsOperatorItem($consequents);

        $hasWaktu = $this->containsWaktuItem($antecedents) ||
            $this->containsWaktuItem($consequents);

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

        return 'produk_operator_waktu';
    }

    private function containsProductItem($text)
    {
        $items = $this->splitRuleItems($text);

        foreach ($items as $item) {
            if (
                !$this->isOperatorItem($item) &&
                !$this->isWaktuItem($item)
            ) {
                return true;
            }
        }

        return false;
    }

    private function containsOperatorItem($text)
    {
        $items = $this->splitRuleItems($text);

        foreach ($items as $item) {
            if ($this->isOperatorItem($item)) {
                return true;
            }
        }

        return false;
    }

    private function containsWaktuItem($text)
    {
        $items = $this->splitRuleItems($text);

        foreach ($items as $item) {
            if ($this->isWaktuItem($item)) {
                return true;
            }
        }

        return false;
    }

    private function splitRuleItems($text)
    {
        $text = strtolower((string) $text);

        if ($text === '' || $text === '-') {
            return [];
        }

        $items = preg_split('/,|\||→|->/', $text);

        return collect($items)
            ->map(function ($item) {
                return trim($item);
            })
            ->filter(function ($item) {
                return $item !== '';
            })
            ->values()
            ->all();
    }

    private function isOperatorItem($item)
    {
        $item = strtolower(trim((string) $item));

        return str_starts_with($item, 'operator_') ||
            str_starts_with($item, 'operator:') ||
            str_starts_with($item, 'operator ');
    }

    private function isWaktuItem($item)
    {
        $item = strtolower(trim((string) $item));

        return str_starts_with($item, 'waktu_') ||
            str_starts_with($item, 'waktu:') ||
            str_starts_with($item, 'waktu ') ||
            in_array($item, ['pagi', 'siang', 'sore', 'malam'], true);
    }

    private function getSummaryInt(array $summary, array $keys, $default = 0)
    {
        foreach ($keys as $key) {
            if (isset($summary[$key]) && is_numeric($summary[$key])) {
                return (int) $summary[$key];
            }
        }

        return $default;
    }

    private function getTotalOperatorFromProsesOrRules($proses, $rules)
    {
        if ($this->prosesAnalisisHasColumn('total_operator') && isset($proses->total_operator)) {
            return (int) $proses->total_operator;
        }

        return $this->countUniqueOperatorsFromRules($rules);
    }

    private function countUniqueOperatorsFromRules($rules)
    {
        $operators = collect();

        foreach ($rules as $rule) {
            $text = strtolower((string) ($rule->rule_asosiasi ?? ''));

            if (preg_match_all('/operator\s*:\s*([^,→]+)/i', $text, $matches)) {
                foreach ($matches[1] as $operator) {
                    $operators->push(trim($operator));
                }
            }

            if (preg_match_all('/operator_([^,→]+)/i', $text, $matches)) {
                foreach ($matches[1] as $operator) {
                    $operators->push(trim($operator));
                }
            }
        }

        return $operators
            ->filter()
            ->unique()
            ->count();
    }

    private function prosesAnalisisHasColumn($column)
    {
        return Schema::hasColumn('proses_analisis', $column);
    }

    private function aturanAsosiasiHasColumn($column)
    {
        return Schema::hasColumn('aturan_asosiasi', $column);
    }

    private function getEmptyAnalysisData()
    {
        return [
            'summary' => [
                'total_data_awal' => 0,
                'setelah_preprocessing' => 0,
                'total_basket' => 0,
                'produk_unik' => 0,
                'total_operator' => 0,
                'frequent_itemsets' => 0,
                'association_rules' => 0,
                'jumlah_anomali' => 0,
                'rule_terbaik' => 'Belum ada rule',
                'rules_ditampilkan' => 0,
                'top_n_request' => self::API_TOP_N,
            ],
            'rules' => collect(),
            'dataset' => [
                'nama_file' => '-',
                'periode_data' => '-',
                'tanggal_analisis' => '-',
                'jumlah_data_awal' => 0,
                'data_setelah_preprocessing' => 0,
                'transaksi_refund_dihapus' => 0,
                'basket_transaksi_terbentuk' => 0,
                'status' => '-',
            ],
            'topProduk' => collect(),
            'distribusiWaktu' => collect(),
        ];
    }
}
