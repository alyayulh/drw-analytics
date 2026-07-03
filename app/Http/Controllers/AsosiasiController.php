<?php

namespace App\Http\Controllers;

# penghubung antara user di web Laravel dengan proses analisis di API Python + ngatur data hasilnya
use App\Exports\DashboardLaporanExport;
use App\Exports\HasilAnalisisExport;
use App\Models\AturanAsosiasi;
use App\Models\ProsesAnalisis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class AsosiasiController extends Controller
{
    private const API_TOP_N = 0;

    public function dashboard(Request $request)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $data = $this->getLatestAnalysisData($kanalFilter);

        return view('asosiasi.dashboard', [
            'summary' => $data['summary'],
            'rules' => $data['rules'],
            'dataset' => $data['dataset'],
            'topProduk' => $data['topProduk'],
            'distribusiWaktu' => $data['distribusiWaktu'],
            'heatmapData' => $data['heatmapData'],
            'heatmap' => $data['heatmap'],
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

    public function dashboardInsight(Request $request)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $data = $this->getLatestAnalysisData($kanalFilter);

        return view('asosiasi.dashboard-insight', [
            'summary' => $data['summary'],
            'rules' => $data['rules'],
            'heatmapData' => $data['heatmapData'],
            'heatmap' => $data['heatmap'],
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

    public function hasil(Request $request)
    {
        return $this->hasilAnalisis($request);
    }

    public function hasilAnalisis(Request $request)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $data = $this->getLatestAnalysisData($kanalFilter);

        return view('asosiasi.hasil', [
            'summary' => $data['summary'],
            'rules' => $data['rules'],
            'dataset' => $data['dataset'],
            'topProduk' => $data['topProduk'],
            'distribusiWaktu' => $data['distribusiWaktu'],
            'heatmapData' => $data['heatmapData'],
            'heatmap' => $data['heatmap'],
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

    public function detailRiwayat(Request $request, $id)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $proses = ProsesAnalisis::with('aturanAsosiasi')
            ->where('id_proses_analisis', $id)
            ->firstOrFail();

        $data = $this->getAnalysisDataFromDatabase($proses, $kanalFilter);
        $riwayat = $this->formatRiwayatItem($proses);

        return view('asosiasi.detail-riwayat', [
            'riwayat' => $riwayat,
            'rules' => $data['rules'],
            'summary' => $data['summary'],
            'dataset' => $data['dataset'],
            'topProduk' => $data['topProduk'],
            'distribusiWaktu' => $data['distribusiWaktu'],
            'heatmapData' => $data['heatmapData'],
            'heatmap' => $data['heatmap'],
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
                ->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('asosiasi.riwayat')
                ->with('error', 'Gagal menghapus data riwayat analisis.');
        }
    }

    public function validasiFormat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:20480',
        ], [
            'file.required' => 'File dataset wajib diunggah.',
            'file.file' => 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.',
            'file.max' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 20 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'message' => $validator->errors()->first('file'),
            ], 422);
        }

        $file = $request->file('file');

        if (!$this->isAllowedExcelFile($file)) {
            return response()->json([
                'valid' => false,
                'message' => 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.',
            ], 422);
        }

        $validasiFormatDataset = $this->validateDatasetColumns($file);

        if (!$validasiFormatDataset['valid']) {
            return response()->json([
                'valid' => false,
                'message' => $validasiFormatDataset['message']
                    ?? $this->getDatasetFormatErrorMessage($validasiFormatDataset['missing_groups'] ?? []),
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Format dataset sesuai.',
        ]);
    }

    public function prosesAnalisis(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
            'min_support' => 'nullable|numeric|min:0.0001|max:1',
            'min_confidence' => 'nullable|numeric|min:0.0001|max:1',
            'min_lift' => 'nullable|numeric|min:0',
            'kanal_filter' => 'nullable|in:semua,offline,online',
        ], [
            'file.required' => 'File dataset wajib diunggah.',
            'file.file' => 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.',
            'file.max' => 'Ukuran file terlalu besar. Maksimal ukuran file adalah 20 MB.',
        ]);

        $file = $request->file('file');

        if (!$this->isAllowedExcelFile($file)) {
            return back()->with('error', 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.');
        }

        $validasiFormatDataset = $this->validateDatasetColumns($file);

        if (!$validasiFormatDataset['valid']) {
            return back()->with(
                'error',
                $validasiFormatDataset['message'] ?? $this->getDatasetFormatErrorMessage($validasiFormatDataset['missing_groups'] ?? [])
            );
        }

        $apiUrl = env('FPGROWTH_API_URL');

        if (!$apiUrl) {
            return back()->with('error', 'FPGROWTH_API_URL belum diatur di file .env Laravel.');
        }

        $minSupport = (float) $request->input('min_support', 0.02);
        $minConfidence = (float) $request->input('min_confidence', 0.6);
        $minLift = (float) $request->input('min_lift', 1.0);
        $kanalFilter = strtolower((string) $request->input('kanal_filter', 'semua'));

        if (!in_array($kanalFilter, ['semua', 'offline', 'online'], true)) {
            $kanalFilter = 'semua';
        }

        $proses = null;
        $stream = null;

        try {
            $storedDataset = $this->storeUploadedDatasetFile($file);
            $path = $storedDataset['relative_path'];
            $filePath = $storedDataset['absolute_path'];

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

            if ($this->prosesAnalisisHasColumn('kanal_filter')) {
                $createData['kanal_filter'] = $kanalFilter;
            }

            $proses = ProsesAnalisis::create($createData);

            if (!$filePath || !is_file($filePath)) {
                throw new \Exception('File upload tidak ditemukan setelah disimpan ke storage. Silakan pilih ulang file Excel, lalu tekan Proses Analisis lagi.');
            }

            $stream = fopen($filePath, 'r');

            $response = Http::timeout(900)
                ->attach('file', $stream, $file->getClientOriginalName())
                ->post($apiUrl, [
                    'min_support' => $minSupport,
                    'min_confidence' => $minConfidence,
                    'min_lift' => $minLift,
                    'kanal_filter' => $kanalFilter,
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

                if ($this->isFormatColumnError($response->body())) {
                    $message = $this->getDatasetFormatErrorMessage();
                }

                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => $message,
                ]);

                return back()->with('error', $message);
            }

            $apiResult = $response->json();

            if (!is_array($apiResult)) {
                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => 'Response API tidak valid.',
                ]);

                return back()->with('error', 'Response API tidak valid. Pastikan API Python mengembalikan data JSON.');
            }

            if (($apiResult['status'] ?? null) !== 'success') {
                $message = $apiResult['message'] ?? 'Analisis gagal diproses.';

                if ($this->isFormatColumnError($message)) {
                    $message = $this->getDatasetFormatErrorMessage();
                }

                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => $message,
                ]);

                return back()->with('error', $message);
            }

            $topRules = $this->getRulesFromApiResult($apiResult);

            $summaryApi = $apiResult['summary'] ?? [];
            $rulesReturned = count($topRules);

            DB::transaction(function () use ($proses, $summaryApi, $topRules, $rulesReturned, $apiResult, $kanalFilter) {
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
                    'total_frequent_itemsets' => (int) ($summaryApi['pola_sering_muncul'] ?? $summaryApi['frequent_itemsets'] ?? $summaryApi['total_frequent_itemsets'] ?? 0),
                    'total_rules' => (int) ($summaryApi['pola_hubungan'] ?? $summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? $rulesReturned),
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

                if ($this->prosesAnalisisHasColumn('kanal_filter')) {
                    $updateData['kanal_filter'] = $summaryApi['kanal_filter'] ?? $kanalFilter;
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

                    if ($this->aturanAsosiasiHasColumn('kanal_filter')) {
                        $ruleData['kanal_filter'] = $this->getRuleKanalFilter($rule, $kanalFilter, $summaryApi);
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
                    'tanggal_analisis' => $this->formatTanggalIndonesia(now()),
                    'tanggal_filter' => Carbon::now()->format('Y-m-d'),
                    'status' => 'Selesai',
                    'kanal_filter' => $summaryApi['kanal_filter'] ?? $kanalFilter,
                    'kanal_filter_label' => $this->formatKanalFilter($summaryApi['kanal_filter'] ?? $kanalFilter),
                    'top_n_request' => self::API_TOP_N,
                    'rules_returned' => $rulesReturned,
                ],
            ]);

            return redirect()
                ->route('asosiasi.hasil')
                ->with('success', 'Analisis dataset berhasil diproses dan disimpan ke riwayat. Pola hubungan diterima: ' . $rulesReturned);
        } catch (\Exception $e) {
            if (is_resource($stream)) {
                fclose($stream);
            }

            $message = $e->getMessage();

            if ($this->isFormatColumnError($message)) {
                $message = $this->getDatasetFormatErrorMessage();
            } elseif (
                str_contains(strtolower($message), 'curl error 7') ||
                str_contains(strtolower($message), 'connection refused') ||
                str_contains(strtolower($message), 'failed to connect')
            ) {
                $message = 'API Python tidak terhubung. Pastikan server FastAPI di port 8000 sudah berjalan.';
            } else {
                $message = 'Terjadi kesalahan: ' . $message;
            }

            if ($proses) {
                $proses->update([
                    'status' => 'gagal',
                    'pesan_error' => $message,
                ]);
            }

            return back()->with('error', $message);
        }
    }

    public function downloadLaporan(Request $request)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $data = $this->getLatestAnalysisData($kanalFilter);

        $fileName = 'laporan_dashboard_analisis_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new DashboardLaporanExport($data), $fileName);
    }

    public function downloadHasil(Request $request)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $data = $this->getLatestAnalysisData($kanalFilter);

        $fileName = 'hasil_analisis_asosiasi_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new HasilAnalisisExport($data), $fileName);
    }

    public function downloadHasilRiwayat(Request $request, $id)
    {
        $kanalFilter = $this->getSelectedKanalFilter(
            $request->input('kanal_filter', $request->input('kanal', 'semua'))
        );

        $proses = ProsesAnalisis::with('aturanAsosiasi')
            ->where('id_proses_analisis', $id)
            ->firstOrFail();

        if ($proses->status !== 'berhasil') {
            return back()->with('error', 'Hasil analisis tidak dapat diunduh karena proses analisis gagal.');
        }

        $data = $this->getAnalysisDataFromDatabase($proses, $kanalFilter);

        $namaFile = pathinfo($proses->nama_file ?? 'hasil_analisis', PATHINFO_FILENAME);
        $namaFile = preg_replace('/[^A-Za-z0-9_\-]/', '_', $namaFile);

        $fileName = 'hasil_analisis_riwayat_' .
            $proses->id_proses_analisis . '_' .
            $namaFile . '_' .
            now()->format('Ymd_His') .
            '.xlsx';

        return Excel::download(new HasilAnalisisExport($data), $fileName);
    }

    private function getRulesFromApiResult(array $apiResult)
    {
        $candidateKeys = [
            'top_rules',
            'rules',
            'association_rules_data',
            'association_rules_result',
            'association_rules',
        ];

        foreach ($candidateKeys as $key) {
            if (!array_key_exists($key, $apiResult) || !is_array($apiResult[$key])) {
                continue;
            }

            $rules = collect($apiResult[$key])
                ->filter(function ($rule) {
                    if (!is_array($rule)) {
                        return false;
                    }

                    return array_key_exists('antecedents_display', $rule)
                        || array_key_exists('antecedents_raw', $rule)
                        || array_key_exists('antecedents', $rule)
                        || array_key_exists('rule_asosiasi', $rule)
                        || array_key_exists('rule', $rule);
                })
                ->values()
                ->all();

            if (!empty($rules)) {
                return $rules;
            }
        }

        return [];
    }

    private function formatTanggalIndonesia($tanggal)
    {
        if (!$tanggal || $tanggal === '-') {
            return '-';
        }

        $bulanIndonesia = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        try {
            if ($tanggal instanceof \Carbon\CarbonInterface) {
                $tanggalFormatted = $tanggal->format('d F Y');
            } else {
                $tanggalFormatted = Carbon::parse($tanggal)->format('d F Y');
            }

            return strtr($tanggalFormatted, $bulanIndonesia);
        } catch (\Exception $e) {
            return strtr((string) $tanggal, $bulanIndonesia);
        }
    }

    private function getDatasetFormatErrorMessage(array $missingGroups = [])
    {
        $baseMessage = 'Format dataset tidak sesuai. File Excel harus memiliki kolom wajib: nomor transaksi, produk, operator, waktu/tanggal transaksi, dan tipe penjualan.';

        if (empty($missingGroups)) {
            return $baseMessage;
        }

        $missingLabels = collect($missingGroups)
            ->map(function ($groupName) {
                return $this->getReadableDatasetColumnName($groupName);
            })
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        if ($missingLabels === '') {
            return $baseMessage;
        }

        return 'Format dataset tidak sesuai. Kolom wajib yang belum ditemukan: ' . $missingLabels . '. Pastikan file Excel memiliki kolom wajib: nomor transaksi, produk, operator, waktu/tanggal transaksi, dan tipe penjualan.';
    }

    private function getReadableDatasetColumnName($groupName)
    {
        $labels = [
            'nomor_transaksi' => 'nomor transaksi',
            'produk' => 'produk',
            'operator' => 'operator',
            'waktu_transaksi' => 'waktu/tanggal transaksi',
            'tipe_penjualan' => 'tipe penjualan',
        ];

        return $labels[$groupName] ?? str_replace('_', ' ', (string) $groupName);
    }

    private function isAllowedExcelFile($file)
    {
        if (!$file || !$file->isValid()) {
            return false;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        return in_array($extension, ['xls', 'xlsx'], true);
    }

    private function storeUploadedDatasetFile($file)
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('File upload tidak valid atau gagal diterima oleh server. Silakan pilih ulang file Excel.');
        }

        $sourcePath = $file->getPathname();

        if (!$sourcePath || !is_file($sourcePath)) {
            $sourcePath = $file->getRealPath();
        }

        if (!$sourcePath || !is_file($sourcePath)) {
            throw new \Exception('File upload tidak ditemukan oleh sistem. Silakan pilih ulang file Excel, lalu tekan Proses Analisis lagi.');
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            throw new \Exception('Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.');
        }

        $storageDirectory = storage_path('app/public/datasets');

        if (!is_dir($storageDirectory)) {
            mkdir($storageDirectory, 0775, true);
        }

        $safeOriginalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeOriginalName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $safeOriginalName) ?: 'dataset';

        $storedFileName = now()->format('Ymd_His') . '_' . uniqid() . '_' . $safeOriginalName . '.' . $extension;
        $absolutePath = $storageDirectory . DIRECTORY_SEPARATOR . $storedFileName;

        if (!copy($sourcePath, $absolutePath)) {
            throw new \Exception('File upload gagal disimpan ke storage. Pastikan folder storage/app/public/datasets bisa ditulis.');
        }

        return [
            'relative_path' => 'datasets/' . $storedFileName,
            'absolute_path' => $absolutePath,
        ];
    }

    private function validateDatasetColumns($file)
    {
        try {
            $filePath = $file->getPathname();

            // Setelah deploy/pindah environment, beberapa server menyimpan file upload
            // di path sementara yang berbeda. Karena itu, gunakan fallback getRealPath().
            if (!$filePath || !is_file($filePath)) {
                $filePath = $file->getRealPath();
            }

            if (!$filePath || !is_file($filePath)) {
                return [
                    'valid' => false,
                    'message' => 'File upload tidak ditemukan oleh sistem. Silakan pilih ulang file Excel, lalu tekan Proses Analisis lagi.',
                ];
            }

            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);

            $spreadsheet = $reader->load($filePath);
            $sheet = [];

            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $rows = $worksheet->toArray(null, true, true, false);

                $hasFilledCell = collect($rows)->flatten()->contains(function ($value) {
                    return trim((string) $value) !== '';
                });

                if ($hasFilledCell) {
                    $sheet = $rows;
                    break;
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } catch (\Throwable $e) {
            \Log::error('Excel read error: ' . $e->getMessage(), [
                'file' => $file ? $file->getClientOriginalName() : null,
                'extension' => $file ? $file->getClientOriginalExtension() : null,
                'mime' => $file ? $file->getMimeType() : null,
                'client_mime' => $file ? $file->getClientMimeType() : null,
                'pathname' => $file ? $file->getPathname() : null,
            ]);

            return [
                'valid' => false,
                'message' => 'File Excel gagal dibaca. Pastikan file bukan hasil rename manual, tidak rusak, dan sudah disimpan ulang sebagai Excel Workbook (.xlsx/.xls). Detail: ' . $e->getMessage(),
            ];
        }

        if (!is_array($sheet) || count($sheet) < 2) {
            return [
                'valid' => false,
                'message' => $this->getDatasetFormatErrorMessage(),
            ];
        }

        $requiredColumnGroups = $this->getRequiredDatasetColumnGroups();
        $maxRowsToScan = min(count($sheet), 20);
        $bestMatchedCount = 0;
        $bestMissingGroups = array_keys($requiredColumnGroups);
        $bestHeaderRowIndex = null;

        for ($rowIndex = 0; $rowIndex < $maxRowsToScan; $rowIndex++) {
            $row = $sheet[$rowIndex] ?? [];

            if (!is_array($row)) {
                continue;
            }

            $headers = collect($row)
                ->map(function ($value) {
                    return $this->normalizeHeaderName($value);
                })
                ->filter(function ($value) {
                    return $value !== '';
                })
                ->values()
                ->all();

            if (empty($headers)) {
                continue;
            }

            $missingGroups = [];
            $matchedCount = 0;

            foreach ($requiredColumnGroups as $groupName => $aliases) {
                if ($this->hasMatchingHeader($headers, $aliases)) {
                    $matchedCount++;
                } else {
                    $missingGroups[] = $groupName;
                }
            }

            if ($matchedCount > $bestMatchedCount) {
                $bestMatchedCount = $matchedCount;
                $bestMissingGroups = $missingGroups;
                $bestHeaderRowIndex = $rowIndex;
            }

            if (empty($missingGroups)) {
                $hasDataRow = false;

                for ($dataRowIndex = $rowIndex + 1; $dataRowIndex < count($sheet); $dataRowIndex++) {
                    $dataRow = $sheet[$dataRowIndex] ?? [];

                    if (!is_array($dataRow)) {
                        continue;
                    }

                    $filledColumns = collect($dataRow)
                        ->filter(function ($value) {
                            return trim((string) $value) !== '';
                        })
                        ->count();

                    if ($filledColumns >= 2) {
                        $hasDataRow = true;
                        break;
                    }
                }

                if (!$hasDataRow) {
                    return [
                        'valid' => false,
                        'message' => 'Format dataset tidak sesuai. File Excel harus memiliki baris data setelah header kolom.',
                    ];
                }

                return [
                    'valid' => true,
                    'message' => 'Format file sesuai',
                    'header_row_index' => $rowIndex,
                ];
            }
        }

        return [
            'valid' => false,
            'message' => $this->getDatasetFormatErrorMessage($bestMissingGroups),
            'missing_groups' => $bestMissingGroups,
            'header_row_index' => $bestHeaderRowIndex,
        ];
    }

    private function getRequiredDatasetColumnGroups()
    {
        // Alias dibuat fleksibel karena nama header Excel bisa berbeda-beda,
        // misalnya no_transaksi, Nomor Transaksi, Transaction ID, Nama Produk, dst.
        // Semua alias akan dinormalisasi oleh normalizeHeaderName(), jadi underscore,
        // strip, slash, titik, dan huruf besar/kecil tetap aman.
        return [
            'nomor_transaksi' => [
                'no transaksi',
                'no transaksi penjualan',
                'nomor transaksi',
                'nomor transaksi penjualan',
                'kode transaksi',
                'id transaksi',
                'id transaksi penjualan',
                'transaksi',
                'transaction',
                'transaction id',
                'transaction number',
                'transaction no',
                'order id',
                'order number',
                'invoice',
                'invoice no',
                'invoice number',
                'no invoice',
                'nomor invoice',
                'bill no',
                'bill number',
                'receipt no',
                'nomor struk',
                'no struk',
                'sales id',
                'nota',
                'no nota',
                'nomor nota',
            ],
            'produk' => [
                'produk',
                'nama produk',
                'nama barang',
                'barang',
                'product',
                'product name',
                'item',
                'item name',
                'nama item',
                'sku',
                'sku name',
                'product sku',
                'service',
                'treatment',
                'description',
                'item description',
                'nama layanan',
                'layanan',
            ],
            'operator' => [
                'operator',
                'nama operator',
                'kasir',
                'nama kasir',
                'cashier',
                'cashier name',
                'staff',
                'staff name',
                'pegawai',
                'nama pegawai',
                'employee',
                'employee name',
                'user',
                'username',
                'created by',
                'served by',
                'handled by',
                'beautician',
                'therapist',
                'admin',
                'sales',
                'sales name',
                'nama sales',
            ],
            'waktu_transaksi' => [
                'tanggal',
                'tgl',
                'waktu',
                'jam',
                'tanggal waktu',
                'tanggal dan waktu',
                'tanggal transaksi',
                'waktu transaksi',
                'jam transaksi',
                'tanggal pembelian',
                'waktu pembelian',
                'tgl transaksi',
                'tgl pembelian',
                'transaction date',
                'transaction time',
                'transaction datetime',
                'date',
                'time',
                'datetime',
                'created at',
                'paid at',
                'order date',
                'sales date',
                'purchase date',
                'payment date',
            ],
            'tipe_penjualan' => [
                'tipe penjualan',
                'tipe_penjualan',
                'type penjualan',
                'jenis penjualan',
                'sales type',
                'sale type',
                'order type',
                'tipe order',
                'channel',
                'kanal',
                'kanal penjualan',
            ],
        ];
    }

    private function normalizeHeaderName($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = strtolower($value);
        $value = str_replace(['_', '-', '/', '\\', '.', ':', ';', '(', ')', '[', ']'], ' ', $value);
        $value = preg_replace('/[^a-z0-9\s]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function hasMatchingHeader(array $headers, array $aliases)
    {
        $normalizedAliases = collect($aliases)
            ->map(function ($alias) {
                return $this->normalizeHeaderName($alias);
            })
            ->filter()
            ->values()
            ->all();

        foreach ($headers as $header) {
            $header = $this->normalizeHeaderName($header);

            if ($header === '') {
                continue;
            }

            foreach ($normalizedAliases as $alias) {
                if ($header === $alias) {
                    return true;
                }

                // Cocokkan bentuk jamak/kolom yang mengandung kata tambahan,
                // contoh: "No Transaksi Penjualan" tetap cocok dengan "no transaksi".
                if (strlen($alias) >= 3 && preg_match('/(^| )' . preg_quote($alias, '/') . '( |$)/', $header)) {
                    return true;
                }

                if (strlen($header) >= 3 && preg_match('/(^| )' . preg_quote($header, '/') . '( |$)/', $alias)) {
                    return true;
                }

                // Fallback contains untuk header yang panjang.
                if (strlen($alias) >= 5 && str_contains($header, $alias)) {
                    return true;
                }

                if (strlen($header) >= 5 && str_contains($alias, $header)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isFormatColumnError($message)
    {
        $message = strtolower((string) $message);

        return str_contains($message, 'format file tidak sesuai') ||
            str_contains($message, 'kolom') ||
            str_contains($message, 'column') ||
            str_contains($message, 'columns') ||
            str_contains($message, 'required') ||
            str_contains($message, 'missing') ||
            str_contains($message, 'tidak ditemukan') ||
            str_contains($message, 'tidak sesuai') ||
            str_contains($message, 'dataset tidak valid');
    }

    private function getLatestAnalysisData($selectedKanalFilter = 'semua')
    {
        $selectedKanalFilter = $this->getSelectedKanalFilter($selectedKanalFilter);
        $apiResult = session('hasil_analisis_api');

        if ($apiResult) {
            return $this->getAnalysisDataFromSession($apiResult, $selectedKanalFilter);
        }

        $latestProses = ProsesAnalisis::with('aturanAsosiasi')
            ->where('status', 'berhasil')
            ->orderByDesc('tanggal_proses')
            ->first();

        if ($latestProses) {
            return $this->getAnalysisDataFromDatabase($latestProses, $selectedKanalFilter);
        }

        return $this->getEmptyAnalysisData($selectedKanalFilter);
    }

    private function getAnalysisDataFromSession(array $apiResult, $selectedKanalFilter = 'semua')
    {
        $selectedKanalFilter = $this->getSelectedKanalFilter($selectedKanalFilter);
        $summaryApi = $apiResult['summary'] ?? [];
        $datasetInfo = session('dataset_info_api', []);

        $topRules = $this->getRulesFromApiResult($apiResult);

        $prosesKanalFilter = $this->getSelectedKanalFilter(
            $summaryApi['kanal_filter'] ?? ($datasetInfo['kanal_filter'] ?? 'semua')
        );

        $summary = [
            'total_data_awal' => $summaryApi['total_data_awal'] ?? 0,
            'setelah_preprocessing' => $summaryApi['setelah_preprocessing'] ?? $summaryApi['total_data_bersih'] ?? 0,
            'total_basket' => $summaryApi['total_basket'] ?? $summaryApi['total_transaksi'] ?? 0,
            'produk_unik' => $summaryApi['produk_unik'] ?? $summaryApi['total_produk_unik'] ?? 0,
            'total_operator' => $summaryApi['operator_unik'] ?? ($summaryApi['jumlah_operator_unik'] ?? ($summaryApi['total_operator'] ?? 0)),
            'frequent_itemsets' => $summaryApi['pola_sering_muncul'] ?? $summaryApi['frequent_itemsets'] ?? $summaryApi['total_frequent_itemsets'] ?? 0,
            'pola_sering_muncul' => $summaryApi['pola_sering_muncul'] ?? $summaryApi['frequent_itemsets'] ?? $summaryApi['total_frequent_itemsets'] ?? 0,
            'association_rules' => $summaryApi['pola_hubungan'] ?? $summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? count($topRules),
            'pola_hubungan' => $summaryApi['pola_hubungan'] ?? $summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? count($topRules),
            'association_rules_total' => $summaryApi['pola_hubungan_total'] ?? $summaryApi['association_rules_total'] ?? $summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? count($topRules),
            'pola_hubungan_total' => $summaryApi['pola_hubungan_total'] ?? $summaryApi['association_rules_total'] ?? $summaryApi['association_rules'] ?? $summaryApi['total_rules'] ?? count($topRules),
            'pola_sering_muncul_offline' => $summaryApi['pola_sering_muncul_offline'] ?? 0,
            'pola_sering_muncul_online' => $summaryApi['pola_sering_muncul_online'] ?? 0,
            'association_rules_offline' => $summaryApi['association_rules_offline'] ?? ($summaryApi['pola_hubungan_offline'] ?? 0),
            'association_rules_online' => $summaryApi['association_rules_online'] ?? ($summaryApi['pola_hubungan_online'] ?? 0),
            'pola_hubungan_offline' => $summaryApi['pola_hubungan_offline'] ?? ($summaryApi['association_rules_offline'] ?? 0),
            'pola_hubungan_online' => $summaryApi['pola_hubungan_online'] ?? ($summaryApi['association_rules_online'] ?? 0),
            'jumlah_anomali' => $summaryApi['jumlah_anomali'] ?? 0,
            'rule_terbaik' => $summaryApi['rule_terbaik'] ?? $this->getBestRuleTextFromApiRules($topRules),
            'rules_ditampilkan' => count($topRules),
            'rules_ditampilkan_total' => count($topRules),
            'top_n_request' => $datasetInfo['top_n_request'] ?? self::API_TOP_N,
            'kanal_filter' => $selectedKanalFilter,
            'kanal_filter_label' => $this->formatKanalFilter($selectedKanalFilter),
            'proses_kanal_filter' => $prosesKanalFilter,
            'proses_kanal_filter_label' => $this->formatKanalFilter($prosesKanalFilter),
        ];

        $dataset = [
            'nama_file' => $datasetInfo['nama_file'] ?? 'Dataset Upload',
            'periode_data' => $datasetInfo['periode_data'] ?? '-',
            'tanggal_analisis' => $this->formatTanggalIndonesia($datasetInfo['tanggal_analisis'] ?? now()),
            'jumlah_data_awal' => $summary['total_data_awal'],
            'data_setelah_preprocessing' => $summary['setelah_preprocessing'],
            'transaksi_refund_dihapus' => $summaryApi['jumlah_data_dihapus'] ?? 0,
            'basket_transaksi_terbentuk' => $summary['total_basket'],
            'status' => $datasetInfo['status'] ?? 'Selesai',
            'kanal_filter' => $summary['kanal_filter'],
            'kanal_filter_label' => $summary['kanal_filter_label'],
            'proses_kanal_filter' => $summary['proses_kanal_filter'],
            'proses_kanal_filter_label' => $summary['proses_kanal_filter_label'],
        ];

        $rules = collect($topRules)->map(function ($rule, $index) use ($prosesKanalFilter, $summaryApi) {
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
                'kanal_filter' => $this->getRuleKanalFilter($rule, $prosesKanalFilter, $summaryApi),
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
                'kanal_filter' => $ruleArray['kanal_filter'],
                'kanal_filter_label' => $this->formatKanalFilter($ruleArray['kanal_filter']),
            ];
        });

        $rules = $this->filterMappedRulesByKanal($rules, $selectedKanalFilter);
        $summary['association_rules'] = $rules->count();
        $summary['pola_hubungan'] = $rules->count();
        $summary['rules_ditampilkan'] = $rules->count();
        $summary['jumlah_anomali'] = $rules->filter(function ($rule) {
            return $this->normalizeBoolean($rule['is_anomaly'] ?? false);
        })->count();

        $normalRulesForBestRule = $rules
            ->reject(function ($rule) {
                return $this->isRuleAnomaly($rule);
            })
            ->values();

        $bestRuleFiltered = $normalRulesForBestRule
            ->sortByDesc(function ($rule) {
                return (float) ($rule['lift'] ?? 0);
            })
            ->first();

        $summary['rule_terbaik'] = $bestRuleFiltered
            ? (($bestRuleFiltered['antecedents'] ?? '-') . ' → ' . ($bestRuleFiltered['consequents'] ?? '-'))
            : 'Belum ada rule normal';

        $rules = $this->applyHeatmapColorsToRules($rules);
        $heatmapData = $this->buildHeatmapData($rules);

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
            'heatmapData' => $heatmapData,
            'heatmap' => $heatmapData,
        ];
    }

    private function getAnalysisDataFromDatabase(?ProsesAnalisis $proses = null, $selectedKanalFilter = 'semua')
    {
        $selectedKanalFilter = $this->getSelectedKanalFilter($selectedKanalFilter);

        if (!$proses) {
            $proses = ProsesAnalisis::with('aturanAsosiasi')
                ->where('status', 'berhasil')
                ->orderByDesc('tanggal_proses')
                ->first();
        }

        if (!$proses) {
            return $this->getEmptyAnalysisData($selectedKanalFilter);
        }

        $processKanalFilter = $this->prosesAnalisisHasColumn('kanal_filter')
            ? $this->getSelectedKanalFilter($proses->kanal_filter ?? 'semua')
            : 'semua';

        $allRulesDb = collect($proses->aturanAsosiasi ?? []);
        $rulesDb = $this->filterDatabaseRulesByKanal($allRulesDb, $selectedKanalFilter, $processKanalFilter);

        $normalRulesForBestRule = $rulesDb
            ->reject(function ($rule) {
                return $this->isRuleAnomaly($rule);
            })
            ->values();

        $bestRule = $normalRulesForBestRule
            ->sortByDesc('nilai_lift')
            ->first();

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
            'pola_sering_muncul' => $proses->total_frequent_itemsets ?? 0,
            'association_rules' => $rulesDb->count(),
            'pola_hubungan' => $rulesDb->count(),
            'association_rules_total' => $proses->total_rules ?? $allRulesDb->count(),
            'pola_hubungan_total' => $proses->total_rules ?? $allRulesDb->count(),
            'jumlah_anomali' => $jumlahAnomali,
            'rule_terbaik' => $bestRule ? $bestRule->rule_asosiasi : 'Belum ada rule',
            'rules_ditampilkan' => $rulesDb->count(),
            'rules_ditampilkan_total' => $allRulesDb->count(),
            'top_n_request' => self::API_TOP_N,
            'kanal_filter' => $selectedKanalFilter,
            'kanal_filter_label' => $this->formatKanalFilter($selectedKanalFilter),
            'proses_kanal_filter' => $processKanalFilter,
            'proses_kanal_filter_label' => $this->formatKanalFilter($processKanalFilter),
        ];

        $tanggal = $proses->tanggal_proses
            ? Carbon::parse($proses->tanggal_proses)
            : now();

        $dataset = [
            'nama_file' => $proses->nama_file ?? $proses->nama_proses ?? 'Dataset Upload',
            'periode_data' => '-',
            'tanggal_analisis' => $this->formatTanggalIndonesia($tanggal),
            'jumlah_data_awal' => $proses->total_data_awal ?? 0,
            'data_setelah_preprocessing' => $proses->total_data_bersih ?? 0,
            'transaksi_refund_dihapus' => 0,
            'basket_transaksi_terbentuk' => $proses->total_transaksi ?? 0,
            'status' => $this->formatStatus($proses->status ?? 'berhasil'),
            'kanal_filter' => $summary['kanal_filter'],
            'kanal_filter_label' => $summary['kanal_filter_label'],
            'proses_kanal_filter' => $summary['proses_kanal_filter'],
            'proses_kanal_filter_label' => $summary['proses_kanal_filter_label'],
        ];

        $rules = $rulesDb->values()->map(function ($rule, $index) use ($processKanalFilter) {
            [$antecedents, $consequents] = $this->splitRuleText($rule->rule_asosiasi);

            $support = (float) ($rule->nilai_support ?? 0);
            $confidence = (float) ($rule->nilai_confidence ?? 0);
            $lift = (float) ($rule->nilai_lift ?? 0);
            $ruleKanalFilter = $this->getDatabaseRuleKanalFilter($rule, $processKanalFilter);

            $kategoriRule = $rule->kategori_rule ?: $this->getKategoriRule($confidence, $lift);
            $isAnomaly = $this->normalizeBoolean($rule->is_anomaly ?? false);

            $ruleArray = [
                'antecedents_display' => $antecedents,
                'consequents_display' => $consequents,
                'antecedents_raw' => $antecedents,
                'consequents_raw' => $consequents,
                'kategori_rule' => $kategoriRule,
                'is_anomaly' => $isAnomaly,
                'kanal_filter' => $ruleKanalFilter,
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
                'kanal_filter' => $ruleKanalFilter,
                'kanal_filter_label' => $this->formatKanalFilter($ruleKanalFilter),
            ];
        });

        $rules = $this->applyHeatmapColorsToRules($rules);
        $heatmapData = $this->buildHeatmapData($rules);

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
            'heatmapData' => $heatmapData,
            'heatmap' => $heatmapData,
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

        $normalRulesForBestRule = collect($rules)
            ->reject(function ($rule) {
                return $this->isRuleAnomaly($rule);
            })
            ->values();

        $bestRule = $normalRulesForBestRule
            ->sortByDesc('nilai_lift')
            ->first();

        $kanalFilter = $this->prosesAnalisisHasColumn('kanal_filter')
            ? ($proses->kanal_filter ?? 'semua')
            : 'semua';

        return [
            'id' => $proses->id_proses_analisis,
            'tanggal_analisis' => $this->formatTanggalIndonesia($tanggal),
            'tanggal_filter' => $tanggal->format('Y-m-d'),
            'nama_file' => $proses->nama_file ?? $proses->nama_proses ?? '-',
            'periode_data' => '-',
            'total_data_awal' => $proses->total_data_awal ?? 0,
            'setelah_preprocessing' => $proses->total_data_bersih ?? 0,
            'total_basket' => $proses->total_transaksi ?? 0,
            'produk_unik' => $proses->total_produk_unik ?? 0,
            'total_operator' => $this->getTotalOperatorFromProsesOrRules($proses, $rules),
            'frequent_itemsets' => $proses->total_frequent_itemsets ?? 0,
            'pola_sering_muncul' => $proses->total_frequent_itemsets ?? 0,
            'association_rules' => $proses->total_rules ?? $rules->count(),
            'pola_hubungan' => $proses->total_rules ?? $rules->count(),
            'rule_terbaik' => $bestRule ? $bestRule->rule_asosiasi : 'Belum ada rule',
            'status' => $this->formatStatus($proses->status ?? '-'),
            'min_support' => $proses->min_support ?? 0,
            'min_confidence' => $proses->min_confidence ?? 0,
            'min_lift' => $proses->min_lift ?? 0,
            'pesan_error' => $proses->pesan_error ?? null,
            'kanal_filter' => $kanalFilter,
            'kanal_filter_label' => $this->formatKanalFilter($kanalFilter),
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
            return 'Belum ada rule normal';
        }

        $bestRule = collect($rules)
            ->reject(function ($rule) {
                return $this->isRuleAnomaly($rule);
            })
            ->sortByDesc(function ($rule) {
                return (float) ($rule['lift'] ?? 0);
            })
            ->first();

        return $bestRule ? $this->makeRuleText($bestRule) : 'Belum ada rule normal';
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

    private function isRuleAnomaly($rule)
    {
        if ($rule instanceof \Illuminate\Contracts\Support\Arrayable) {
            $rule = $rule->toArray();
        }

        if (is_array($rule)) {
            $statusAnomali = strtolower(trim((string) ($rule['status_anomali'] ?? '')));
            $isAnomaly = $rule['is_anomaly'] ?? false;

            return $statusAnomali === 'anomali' || $this->normalizeBoolean($isAnomaly);
        }

        if (is_object($rule)) {
            $statusAnomali = strtolower(trim((string) ($rule->status_anomali ?? '')));
            $isAnomaly = $rule->is_anomaly ?? false;

            return $statusAnomali === 'anomali' || $this->normalizeBoolean($isAnomaly);
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

        $antecedentGroups = $this->groupItemsForFriendlyInterpretation($antecedents);
        $consequentGroups = $this->groupItemsForFriendlyInterpretation($consequents);

        $antecedentText = $this->buildFriendlyAntecedentText($antecedentGroups, $antecedents);
        $consequentText = $this->buildFriendlyConsequentText($consequentGroups, $consequents);

        return trim($antecedentText . ' ' . $consequentText);
    }

    private function buildFriendlyAntecedentText(array $groups, $fallbackText)
    {
        $products = $this->formatFriendlyList($groups['produk']);
        $operators = $this->formatFriendlyList($groups['operator']);
        $times = $this->formatFriendlyList($groups['waktu']);

        $contexts = [];

        if ($times !== '-') {
            $contexts[] = 'pada waktu ' . $times;
        }

        if ($operators !== '-') {
            $contexts[] = 'pada transaksi yang ditangani Operator ' . $operators;
        }

        if ($products !== '-') {
            $mainText = 'pelanggan yang membeli ' . $products;

            if (!empty($contexts)) {
                return ucfirst(implode(' dan ', $contexts)) . ', ' . $mainText;
            }

            return ucfirst($mainText);
        }

        if (!empty($contexts)) {
            return ucfirst(implode(' dan ', $contexts));
        }

        return 'Transaksi dengan pola ' . $fallbackText;
    }

    private function buildFriendlyConsequentText(array $groups, $fallbackText)
    {
        $products = $this->formatFriendlyList($groups['produk']);
        $operators = $this->formatFriendlyList($groups['operator']);
        $times = $this->formatFriendlyList($groups['waktu']);

        if ($products !== '-' && $times !== '-' && $operators !== '-') {
            return 'sering memiliki pola pembelian tambahan berupa ' . $products .
                ', terutama pada transaksi yang terjadi di waktu ' . $times .
                ' dan ditangani Operator ' . $operators . '.';
        }

        if ($products !== '-' && $times !== '-') {
            return 'sering memiliki pola pembelian tambahan berupa ' . $products .
                ', terutama pada transaksi yang terjadi di waktu ' . $times . '.';
        }

        if ($products !== '-' && $operators !== '-') {
            return 'sering memiliki pola pembelian tambahan berupa ' . $products .
                ' pada transaksi yang ditangani Operator ' . $operators . '.';
        }

        if ($products !== '-') {
            return 'sering memiliki pola pembelian tambahan berupa ' . $products . '.';
        }

        if ($operators !== '-' && $times !== '-') {
            return 'sering berkaitan dengan transaksi yang ditangani Operator ' . $operators .
                ' pada waktu ' . $times . '.';
        }

        if ($operators !== '-') {
            return 'sering berkaitan dengan transaksi yang ditangani Operator ' . $operators . '.';
        }

        if ($times !== '-') {
            return 'sering berkaitan dengan transaksi yang terjadi di waktu ' . $times . '.';
        }

        return 'sering berkaitan dengan ' . $fallbackText . '.';
    }

    private function groupItemsForFriendlyInterpretation($text)
    {
        $items = $this->splitItemsForFriendlyInterpretation($text);

        $groups = [
            'produk' => [],
            'operator' => [],
            'waktu' => [],
        ];

        foreach ($items as $item) {
            if ($this->isOperatorItem($item)) {
                $groups['operator'][] = $this->cleanOperatorForFriendlyInterpretation($item);
                continue;
            }

            if ($this->isWaktuItem($item)) {
                $groups['waktu'][] = $this->cleanWaktuForFriendlyInterpretation($item);
                continue;
            }

            $groups['produk'][] = $this->cleanProductForFriendlyInterpretation($item);
        }

        $groups['produk'] = array_values(array_unique(array_filter($groups['produk'])));
        $groups['operator'] = array_values(array_unique(array_filter($groups['operator'])));
        $groups['waktu'] = array_values(array_unique(array_filter($groups['waktu'])));

        return $groups;
    }

    private function splitItemsForFriendlyInterpretation($text)
    {
        $text = trim((string) $text);

        if ($text === '' || $text === '-') {
            return [];
        }

        $items = preg_split('/,|\||→|->/', $text);

        return collect($items)
            ->map(function ($item) {
                return trim((string) $item);
            })
            ->filter(function ($item) {
                return $item !== '';
            })
            ->values()
            ->all();
    }

    private function cleanProductForFriendlyInterpretation($item)
    {
        $item = trim((string) $item);
        $item = preg_replace('/^produk\s*[:_]\s*/i', '', $item);
        $item = preg_replace('/\s+/', ' ', $item);

        return trim($item);
    }

    private function cleanOperatorForFriendlyInterpretation($item)
    {
        $item = trim((string) $item);
        $item = preg_replace('/^operator\s*[:_]\s*/i', '', $item);
        $item = preg_replace('/\s+/', ' ', $item);

        return trim($item);
    }

    private function cleanWaktuForFriendlyInterpretation($item)
    {
        $item = trim((string) $item);
        $item = preg_replace('/^waktu\s*[:_]\s*/i', '', $item);
        $item = preg_replace('/\s+/', ' ', $item);
        $item = strtolower(trim($item));

        return ucfirst($item);
    }

    private function formatFriendlyList(array $items)
    {
        $items = array_values(array_filter(array_map('trim', $items)));
        $count = count($items);

        if ($count === 0) {
            return '-';
        }

        if ($count === 1) {
            return $items[0];
        }

        if ($count === 2) {
            return $items[0] . ' dan ' . $items[1];
        }

        $lastItem = array_pop($items);

        return implode(', ', $items) . ', dan ' . $lastItem;
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

    private function applyHeatmapColorsToRules($rules)
    {
        $rules = collect($rules)->values();

        $liftValues = $rules
            ->pluck('lift')
            ->filter(function ($lift) {
                return is_numeric($lift) && (float) $lift > 0;
            })
            ->map(function ($lift) {
                return (float) $lift;
            })
            ->values();

        $minLift = $liftValues->min();
        $maxLift = $liftValues->max();

        $minLift = $minLift !== null ? (float) $minLift : 0;
        $maxLift = $maxLift !== null ? (float) $maxLift : 0;

        return $rules->map(function ($rule) use ($minLift, $maxLift) {
            $lift = (float) ($rule['lift'] ?? 0);
            $style = $this->getHeatmapStyleByLift($lift, $minLift, $maxLift);

            $rule['heatmap_bg_color'] = $style['bg_color'];
            $rule['heatmap_color'] = $style['bg_color'];
            $rule['heatmap_border_color'] = $style['border_color'];
            $rule['heatmap_text_color'] = $style['text_color'];
            $rule['heatmap_opacity'] = $style['opacity'];
            $rule['heatmap_intensity'] = $style['intensity'];
            $rule['heatmap_min_lift'] = $minLift;
            $rule['heatmap_max_lift'] = $maxLift;

            return $rule;
        })->values();
    }

    private function buildHeatmapData($rules)
    {
        $rules = collect($rules)->values();

        $validRules = $rules
            ->filter(function ($rule) {
                return !empty($rule['antecedents'])
                    && !empty($rule['consequents'])
                    && is_numeric($rule['lift'] ?? null)
                    && (float) ($rule['lift'] ?? 0) > 0;
            })
            ->sortByDesc(function ($rule) {
                return (float) ($rule['lift'] ?? 0);
            })
            ->values();

        $liftValues = $validRules
            ->pluck('lift')
            ->map(function ($lift) {
                return (float) $lift;
            });

        $minLift = $liftValues->min();
        $maxLift = $liftValues->max();

        $minLift = $minLift !== null ? (float) $minLift : 0;
        $maxLift = $maxLift !== null ? (float) $maxLift : 0;

        $antecedents = $validRules
            ->pluck('antecedents')
            ->unique()
            ->take(5)
            ->values();

        $consequents = $validRules
            ->pluck('consequents')
            ->unique()
            ->take(5)
            ->values();

        $columns = $consequents->map(function ($consequent, $index) {
            return [
                'key' => $consequent,
                'code' => 'C' . ($index + 1),
                'label' => 'C' . ($index + 1),
                'name' => $consequent,
            ];
        })->values();

        $rows = $antecedents->map(function ($antecedent, $rowIndex) use ($columns, $validRules, $minLift, $maxLift) {
            $cells = $columns->map(function ($column) use ($antecedent, $validRules, $minLift, $maxLift) {
                $matchedRule = $validRules
                    ->filter(function ($rule) use ($antecedent, $column) {
                        return ($rule['antecedents'] ?? '') === $antecedent
                            && ($rule['consequents'] ?? '') === $column['key'];
                    })
                    ->sortByDesc(function ($rule) {
                        return (float) ($rule['lift'] ?? 0);
                    })
                    ->first();

                if (!$matchedRule) {
                    return [
                        'exists' => false,
                        'lift' => null,
                        'support' => null,
                        'confidence' => null,
                        'bg_color' => '#f9fafb',
                        'border_color' => '#e5e7eb',
                        'text_color' => '#111827',
                        'opacity' => 0,
                        'intensity' => 0,
                        'title' => '',
                        'rule' => null,
                    ];
                }

                $lift = (float) ($matchedRule['lift'] ?? 0);
                $style = $this->getHeatmapStyleByLift($lift, $minLift, $maxLift);

                return [
                    'exists' => true,
                    'lift' => $lift,
                    'support' => (float) ($matchedRule['support'] ?? 0),
                    'confidence' => (float) ($matchedRule['confidence'] ?? 0),
                    'bg_color' => $style['bg_color'],
                    'border_color' => $style['border_color'],
                    'text_color' => $style['text_color'],
                    'opacity' => $style['opacity'],
                    'intensity' => $style['intensity'],
                    'title' => 'Lift: ' . number_format($lift, 2),
                    'rule' => $matchedRule,
                ];
            })->values();

            return [
                'key' => $antecedent,
                'code' => 'A' . ($rowIndex + 1),
                'label' => 'A' . ($rowIndex + 1),
                'name' => $antecedent,
                'cells' => $cells,
            ];
        })->values();

        $legendAntecedents = $rows->map(function ($row) {
            return $row['code'] . ' = ' . $row['name'];
        });

        $legendConsequents = $columns->map(function ($column) {
            return $column['code'] . ' = ' . $column['name'];
        });

        return [
            'rows' => $rows,
            'columns' => $columns,
            'legend' => $legendAntecedents->merge($legendConsequents)->values(),
            'min_lift' => $minLift,
            'max_lift' => $maxLift,
            'base_color' => 'rgba(220, 38, 38, opacity)',
            'empty_color' => '#f9fafb',
        ];
    }

    private function getHeatmapStyleByLift($lift, $minLift, $maxLift)
    {
        $lift = (float) $lift;
        $minLift = (float) $minLift;
        $maxLift = (float) $maxLift;

        if ($lift <= 0 || $maxLift <= 0) {
            return [
                'bg_color' => '#f9fafb',
                'border_color' => '#e5e7eb',
                'text_color' => '#111827',
                'opacity' => 0,
                'intensity' => 0,
            ];
        }

        if ($maxLift == $minLift) {
            $normalized = 1;
        } else {
            $normalized = ($lift - $minLift) / ($maxLift - $minLift);
        }

        $normalized = max(0, min(1, $normalized));
        $contrast = pow($normalized, 1.65);

        $opacity = round(0.08 + ($contrast * 0.92), 2);
        $borderOpacity = round(min(1, $opacity + 0.22), 2);

        return [
            'bg_color' => 'rgba(185, 28, 28, ' . $opacity . ')',
            'border_color' => 'rgba(185, 28, 28, ' . $borderOpacity . ')',
            'text_color' => $opacity >= 0.50 ? '#ffffff' : '#7f1d1d',
            'opacity' => $opacity,
            'intensity' => round($normalized, 4),
        ];
    }


    private function getSelectedKanalFilter($kanalFilter = 'semua')
    {
        $normalized = $this->normalizeKanalFilterValue($kanalFilter, true);

        return $normalized ?: 'semua';
    }

    private function normalizeKanalFilterValue($value, $allowSemua = true)
    {
        if ($value === null) {
            return $allowSemua ? 'semua' : null;
        }

        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return $allowSemua ? 'semua' : null;
        }

        $value = str_replace(['_', '-', '/', '\\'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        if (in_array($value, ['offline', 'off line', 'luring', 'store', 'toko', 'langsung'], true)) {
            return 'offline';
        }

        if (in_array($value, ['online', 'on line', 'daring', 'website', 'web', 'marketplace', 'e commerce', 'ecommerce'], true)) {
            return 'online';
        }

        if ($allowSemua && in_array($value, ['semua', 'semua kanal', 'all', 'all channel', 'all channels', 'both', 'gabungan'], true)) {
            return 'semua';
        }

        if (str_contains($value, 'offline')) {
            return 'offline';
        }

        if (str_contains($value, 'online')) {
            return 'online';
        }

        if ($allowSemua && (str_contains($value, 'semua') || str_contains($value, 'all'))) {
            return 'semua';
        }

        return $allowSemua ? 'semua' : null;
    }

    private function getRuleKanalFilter(array $rule, $defaultKanalFilter = 'semua', array $summary = [])
    {
        $candidateKeys = [
            'kanal_filter',
            'kanal',
            'channel',
            'tipe_penjualan',
            'tipe penjualan',
            'jenis_penjualan',
            'jenis penjualan',
            'sales_type',
            'sale_type',
            'order_type',
            'tipe_order',
        ];

        foreach ($candidateKeys as $key) {
            if (array_key_exists($key, $rule)) {
                $normalized = $this->normalizeKanalFilterValue($rule[$key], false);

                if (in_array($normalized, ['offline', 'online'], true)) {
                    return $normalized;
                }
            }
        }

        if (isset($rule['metadata']) && is_array($rule['metadata'])) {
            foreach ($candidateKeys as $key) {
                if (array_key_exists($key, $rule['metadata'])) {
                    $normalized = $this->normalizeKanalFilterValue($rule['metadata'][$key], false);

                    if (in_array($normalized, ['offline', 'online'], true)) {
                        return $normalized;
                    }
                }
            }
        }

        foreach ($candidateKeys as $key) {
            if (array_key_exists($key, $summary)) {
                $normalized = $this->normalizeKanalFilterValue($summary[$key], false);

                if (in_array($normalized, ['offline', 'online'], true)) {
                    return $normalized;
                }
            }
        }

        $defaultNormalized = $this->normalizeKanalFilterValue($defaultKanalFilter, false);

        if (in_array($defaultNormalized, ['offline', 'online'], true)) {
            return $defaultNormalized;
        }

        return null;
    }

    private function getDatabaseRuleKanalFilter($rule, $processKanalFilter = 'semua')
    {
        if ($this->aturanAsosiasiHasColumn('kanal_filter') && isset($rule->kanal_filter)) {
            $normalized = $this->normalizeKanalFilterValue($rule->kanal_filter, false);

            if (in_array($normalized, ['offline', 'online'], true)) {
                return $normalized;
            }
        }

        return $this->getSelectedKanalFilter($processKanalFilter);
    }

    private function filterMappedRulesByKanal($rules, $selectedKanalFilter = 'semua')
    {
        $selectedKanalFilter = $this->getSelectedKanalFilter($selectedKanalFilter);

        $rules = collect($rules);

        if ($selectedKanalFilter === 'semua') {
            return $rules->values()->map(function ($rule, $index) {
                $rule['no'] = $index + 1;

                return $rule;
            });
        }

        return $rules
            ->filter(function ($rule) use ($selectedKanalFilter) {
                $ruleKanalFilter = $this->normalizeKanalFilterValue($rule['kanal_filter'] ?? null, false);

                return $ruleKanalFilter === $selectedKanalFilter;
            })
            ->values()
            ->map(function ($rule, $index) {
                $rule['no'] = $index + 1;

                return $rule;
            });
    }

    private function filterDatabaseRulesByKanal($rules, $selectedKanalFilter = 'semua', $processKanalFilter = 'semua')
    {
        $selectedKanalFilter = $this->getSelectedKanalFilter($selectedKanalFilter);
        $rules = collect($rules);

        if ($selectedKanalFilter === 'semua') {
            return $rules->values();
        }

        return $rules
            ->filter(function ($rule) use ($selectedKanalFilter, $processKanalFilter) {
                $ruleKanalFilter = $this->getDatabaseRuleKanalFilter($rule, $processKanalFilter);

                return $ruleKanalFilter === $selectedKanalFilter;
            })
            ->values();
    }

    private function formatKanalFilter($kanalFilter)
    {
        $kanalFilter = strtolower((string) ($kanalFilter ?? 'semua'));

        return match ($kanalFilter) {
            'offline' => 'Offline',
            'online' => 'Online',
            default => 'Semua Kanal',
        };
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

    private function getEmptyAnalysisData($selectedKanalFilter = 'semua')
    {
        $selectedKanalFilter = $this->getSelectedKanalFilter($selectedKanalFilter);
        $heatmapData = [
            'rows' => collect(),
            'columns' => collect(),
            'legend' => collect(),
            'min_lift' => 0,
            'max_lift' => 0,
            'base_color' => 'rgba(220, 38, 38, opacity)',
            'empty_color' => '#f9fafb',
        ];

        return [
            'summary' => [
                'total_data_awal' => 0,
                'setelah_preprocessing' => 0,
                'total_basket' => 0,
                'produk_unik' => 0,
                'total_operator' => 0,
                'frequent_itemsets' => 0,
                'pola_sering_muncul' => 0,
                'association_rules' => 0,
                'pola_hubungan' => 0,
                'association_rules_total' => 0,
                'pola_hubungan_total' => 0,
                'jumlah_anomali' => 0,
                'rule_terbaik' => 'Belum ada rule',
                'rules_ditampilkan' => 0,
                'rules_ditampilkan_total' => 0,
                'top_n_request' => self::API_TOP_N,
                'kanal_filter' => $selectedKanalFilter,
                'kanal_filter_label' => $this->formatKanalFilter($selectedKanalFilter),
                'proses_kanal_filter' => 'semua',
                'proses_kanal_filter_label' => 'Semua Kanal',
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
                'kanal_filter' => $selectedKanalFilter,
                'kanal_filter_label' => $this->formatKanalFilter($selectedKanalFilter),
                'proses_kanal_filter' => 'semua',
                'proses_kanal_filter_label' => 'Semua Kanal',
            ],
            'topProduk' => collect(),
            'distribusiWaktu' => collect(),
            'heatmapData' => $heatmapData,
            'heatmap' => $heatmapData,
        ];
    }
}