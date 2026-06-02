<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\NilaiProduk;
use App\Models\Kriteria;
use App\Models\KategoriProduk;
use App\Models\InputPermintaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;

class ProdukController extends Controller
{
    public function index()
    {
        $sortBy = request('sort', 'abjad');
        $produks = Produk::with(['nilaiProduk.kriteria', 'kategoriProduk'])
            ->when($sortBy === 'abjad',   fn($q) => $q->orderBy('nama_produk', 'asc'))
            ->when($sortBy === 'terbaru', fn($q) => $q->orderBy('created_at', 'desc'))
            ->when($sortBy === 'terlama', fn($q) => $q->orderBy('created_at', 'asc'))
            ->get();

        $kriteriaManual = Kriteria::where('sumber_data', 'Manual')->get();
        $semuaKriteria  = Kriteria::all();
        $kriteriaExcel  = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();

        // List kategori untuk dropdown di modal Tambah & Edit
        $kategoris = KategoriProduk::orderBy('nama_kategori')->get();
        // Untuk warning banner: berapa produk yang belum berkategori
        $produkBelumBerkategori = $produks->whereNull('id_kategori')->count();

        return view('spk.data-produk', compact(
            'produks', 'kriteriaManual', 'semuaKriteria', 'kriteriaExcel',
            'kategoris', 'produkBelumBerkategori'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'      => 'required|string|max:255',
            'id_kategori'      => 'nullable|integer|exists:kategori_produk,id_kategori',
            'nilai_kriteria'   => 'nullable|array',
            // Skala 1-5 sesuai constraint CHECK di tabel input_permintaan
            'nilai_kriteria.*' => 'nullable|integer|between:1,5',
        ]);

        // Whitelist id_kriteria yang sah (cegah payload isi nilai utk kriteria Excel)
        $manualKriteriaIds = Kriteria::where('sumber_data', 'Manual')
            ->pluck('id_kriteria')->map(fn($id) => (string)$id)->all();

        // Prioritas penentuan kategori:
        // 1) Dropdown eksplisit dari admin, 2) Auto-resolve dari nama, 3) NULL
        $idKategori = $request->filled('id_kategori')
            ? (int) $request->id_kategori
            : $this->resolveKategori($request->nama_produk);

        DB::transaction(function () use ($request, $idKategori, $manualKriteriaIds) {
            $produk = Produk::create([
                'nama_produk' => $request->nama_produk,
                'id_kategori' => $idKategori,
                'status_data' => 'Belum Lengkap',
            ]);

            if ($request->filled('nilai_kriteria')) {
                foreach ($request->nilai_kriteria as $idKriteria => $nilai) {
                    if ($nilai === null || $nilai === '') continue;
                    if (!in_array((string)$idKriteria, $manualKriteriaIds, true)) continue;

                    $nilaiInt = (int) $nilai;

                    // Tulis ke DUA tabel agar konsisten dgn halaman Input Permintaan
                    InputPermintaan::updateOrCreate(
                        ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                        ['nilai_input' => $nilaiInt]
                    );
                    NilaiProduk::updateOrCreate(
                        ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                        ['nilai' => (float) $nilaiInt]
                    );
                }
            }

            $this->updateStatusProduk($produk);
        });

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        $file     = $request->file('file_excel');
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $file->move(storage_path('app/temp'), $filename);
        $path = storage_path('app/temp/' . $filename);

        if (!$xlsx = SimpleXLSX::parse($path)) {
            @unlink($path);
            return back()->with('error', 'Gagal membaca file Excel: ' . SimpleXLSX::parseError());
        }

        $rows = $xlsx->rows();

        if (count($rows) < 2) {
            @unlink($path);
            return back()->with('error', 'File Excel kosong atau tidak punya cukup baris data.');
        }

        $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();

        [$headerRowIdx, $headers] = $this->detectHeaderRow($rows, $kriteriaExcel);

        if ($headerRowIdx === null) {
            @unlink($path);
            return back()->with('error',
                'Kolom "NAMA BARANG" tidak ditemukan di file Excel (dicari di 15 baris pertama).'
            );
        }

        $colNama        = array_search('NAMA BARANG', $headers);
        $kolomMap       = [];
        $kolomDitemukan = [];
        $kolomTidakAda  = [];

        foreach ($kriteriaExcel as $kriteria) {
            $kolomExcel = strtoupper(trim($kriteria->nama_kolom_excel));
            $colIdx     = array_search($kolomExcel, $headers);

            if ($colIdx !== false) {
                $kolomMap[$kriteria->id_kriteria] = $colIdx;
                $kolomDitemukan[] = [
                    'nama_kriteria'    => $kriteria->nama_kriteria,
                    'nama_kolom_excel' => $kriteria->nama_kolom_excel,
                ];
            } else {
                $kolomTidakAda[] = [
                    'nama_kriteria'    => $kriteria->nama_kriteria,
                    'nama_kolom_excel' => $kriteria->nama_kolom_excel,
                ];
            }
        }

        $dataStart   = $headerRowIdx + 1;
        $previewRows = [];
        $totalData   = 0;

        for ($i = $dataStart; $i < count($rows); $i++) {
            $nama = trim($rows[$i][$colNama] ?? '');
            if (!$nama) continue;
            $totalData++;

            if (count($previewRows) < 5) {
                $rowData = ['nama_produk' => $nama, 'nilai' => []];
                foreach ($kolomMap as $idKriteria => $colIdx) {
                    $k = $kriteriaExcel->firstWhere('id_kriteria', $idKriteria);
                    $rowData['nilai'][$k->nama_kriteria] = $this->parseAngka($rows[$i][$colIdx] ?? '');
                }
                $previewRows[] = $rowData;
            }
        }

        if ($totalData === 0) {
            @unlink($path);
            return back()->with('error', 'Tidak ada baris data yang valid ditemukan di file Excel.');
        }

        session([
            'import_filename'        => $filename,
            'import_total_data'      => $totalData,
            'import_kolom_ditemukan' => $kolomDitemukan,
            'import_kolom_tidak_ada' => $kolomTidakAda,
            'import_preview_rows'    => $previewRows,
        ]);

        return redirect()->route('produk.preview.show');
    }

    public function showPreview()
    {
        if (!session('import_filename')) {
            return redirect()->route('produk.index')
                ->with('error', 'Tidak ada file yang sedang diproses. Silakan upload ulang.');
        }

        $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();

        return view('spk.import-preview', [
            'filename'       => session('import_filename'),
            'totalData'      => session('import_total_data'),
            'kolomDitemukan' => session('import_kolom_ditemukan'),
            'kolomTidakAda'  => session('import_kolom_tidak_ada'),
            'previewRows'    => session('import_preview_rows'),
            'kriteriaExcel'  => $kriteriaExcel,
        ]);
    }

    public function importConfirm(Request $request)
    {
        $filename = session('import_filename');

        if (!$filename) {
            return redirect()->route('produk.index')
                ->with('error', 'Sesi import sudah kedaluwarsa. Silakan upload ulang.');
        }

        $path = storage_path('app/temp/' . $filename);

        if (!file_exists($path)) {
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'File sementara tidak ditemukan. Silakan upload ulang.');
        }

        if (!$xlsx = SimpleXLSX::parse($path)) {
            @unlink($path);
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'Gagal membaca file Excel: ' . SimpleXLSX::parseError());
        }

        $rows          = $xlsx->rows();
        $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();

        [$headerRowIdx, $headers] = $this->detectHeaderRow($rows, $kriteriaExcel);

        if ($headerRowIdx === null) {
            @unlink($path);
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'Gagal menemukan header saat konfirmasi import.');
        }

        $colNama  = array_search('NAMA BARANG', $headers);
        $kolomMap = [];

        foreach ($kriteriaExcel as $kriteria) {
            $kolomExcel = strtoupper(trim($kriteria->nama_kolom_excel));
            $colIdx     = array_search($kolomExcel, $headers);
            if ($colIdx !== false) {
                $kolomMap[$kriteria->id_kriteria] = $colIdx;
            }
        }

        $imported = 0;
        $updated  = 0;
        $skipped  = 0;

        try {
            DB::transaction(function () use (
                $rows, $headerRowIdx, $colNama, $kolomMap,
                &$imported, &$updated, &$skipped
            ) {
                $dataStart = $headerRowIdx + 1;

                for ($i = $dataStart; $i < count($rows); $i++) {
                    $nama = trim($rows[$i][$colNama] ?? '');
                    if (!$nama) { $skipped++; continue; }

                    $idKategori = $this->resolveKategori($nama);
                    $isNew      = !Produk::where('nama_produk', $nama)->exists();

                    $produk = Produk::firstOrCreate(
                        ['nama_produk' => $nama],
                        ['status_data' => 'Belum Lengkap', 'id_kategori' => $idKategori]
                    );

                    // Update kategori jika produk sudah ada tapi belum punya kategori
                    if (!$isNew && !$produk->id_kategori && $idKategori) {
                        $produk->update(['id_kategori' => $idKategori]);
                    }

                    if ($isNew) $imported++; else $updated++;

                    foreach ($kolomMap as $idKriteria => $colIdx) {
                        $nilai = $this->parseAngka($rows[$i][$colIdx] ?? '');
                        NilaiProduk::updateOrCreate(
                            ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                            ['nilai' => $nilai]
                        );
                    }

                    $this->updateStatusProduk($produk);
                }
            });
        } catch (\Throwable $e) {
            @unlink($path);
            $this->clearImportSession();
            return redirect()->route('produk.index')
                ->with('error', 'Import gagal dan dibatalkan seluruhnya: ' . $e->getMessage());
        }

        @unlink($path);
        $this->clearImportSession();

        $msg = "{$imported} produk baru ditambahkan";
        if ($updated > 0) $msg .= ", {$updated} produk diperbarui nilainya";
        if ($skipped > 0) $msg .= ", {$skipped} baris kosong dilewati";

        return redirect()->route('produk.index')->with('success', $msg . ' dari Excel.');
    }

    public function cancelPreview()
    {
        $filename = session('import_filename');
        if ($filename) @unlink(storage_path('app/temp/' . $filename));
        $this->clearImportSession();
        return redirect()->route('produk.index')->with('info', 'Import dibatalkan.');
    }

   public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'nullable|integer|exists:kategori_produk,id_kategori',
        ]);

        // Prioritas kategori saat update:
        // - Kalau dropdown muncul di payload -> hormati pilihan admin (termasuk kosong)
        // - Kalau dropdown tidak ada -> coba auto-resolve, fallback ke kategori lama
        if ($request->has('id_kategori')) {
            $idKategori = $request->filled('id_kategori') ? (int) $request->id_kategori : null;
        } else {
            $idKategori = $this->resolveKategori($request->nama_produk) ?? $produk->id_kategori;
        }

        $produk->update([
            'nama_produk' => $request->nama_produk,
            'id_kategori' => $idKategori,
        ]);

        return back()->with('success', 'Produk berhasil diupdate.');
    }

    /**
     * Hapus produk.
     *
     * FIX BUG #3: pakai SOFT DELETE supaya riwayat perhitungan tetap utuh.
     *
     * Strategi:
     * - Produk hanya ditandai `deleted_at` (tidak benar-benar dihapus dari DB).
     * - Data terkait yang harus ikut hilang: nilai_produk & input_permintaan
     *   (karena ini data "operasional" yang tidak perlu dipertahankan).
     * - Data yang HARUS DIPERTAHANKAN: hasil_perhitungan (riwayat ranking).
     *   Baris ini tetap di DB dan tetap merujuk ke produk (via id_produk +
     *   snapshot nama_produk), sehingga riwayat tetap konsisten.
     *
     * Bug lama:
     *   HasilPerhitungan::where('id_produk', $id)->delete();
     *   → menghapus seluruh riwayat ranking produk ini.
     *   → riwayat perhitungan jadi tidak konsisten dengan jumlah_produk
     *     dan produk_prioritas yang sudah snapshot di tabel perhitungan.
     *
     * Catatan:
     * - Kalau di masa depan butuh "Restore Produk", tinggal panggil
     *   $produk->restore() pada produk yang trashed.
     * - Halaman Data Produk otomatis menyembunyikan produk soft-deleted
     *   karena SoftDeletes trait di Model Produk.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        DB::transaction(function () use ($produk, $id) {
            // Hapus data operasional terkait (tidak dipertahankan)
            \App\Models\NilaiProduk::where('id_produk', $id)->delete();
            \App\Models\InputPermintaan::where('id_produk', $id)->delete();

            // PENTING: HasilPerhitungan TIDAK dihapus.
            // Riwayat ranking tetap utuh — tabel hasil_perhitungan sudah
            // menyimpan snapshot nama_produk dan nilai-nilai ranking.

            // Soft delete produk (hanya set deleted_at, baris fisik tetap ada)
            $produk->delete();
        });

        return back()->with('success', 'Produk berhasil dihapus. Riwayat perhitungan tetap tersimpan.');
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────

    /**
     * Resolve id_kategori dari nama produk berdasarkan keyword rules.
     * Urutan PENTING — keyword lebih spesifik di atas, lebih umum di bawah.
     * Ditest terhadap 168 produk DRW Skincare: 0 produk tanpa kategori.
     */
    private function resolveKategori(string $namaProduk): ?int
    {
        $upper = strtoupper(trim($namaProduk));
        $upper = str_replace(['’', '‘', '`', '´'], "'", $upper);

        // Paket produk tidak diberi kategori.
        // Nanti otomatis masuk grup "Tanpa Kategori".
        if (str_starts_with($upper, 'PAKET')) {
            return null;
        }

        $rules = [
            // PERAWATAN RAMBUT
            'HAIR SERUM'             => 'Perawatan Rambut',
            'HAIR TONIC'             => 'Perawatan Rambut',
            'ALOE VERA SHAMPOO'      => 'Perawatan Rambut',
            'SHAMPOO'                => 'Perawatan Rambut',

            // LIP PRODUCT
            'AMOUR MATTE LIP'        => 'Lip Product',
            'LIPS CREAM'             => 'Lip Product',
            'LIP CREAM'              => 'Lip Product',
            'LIPS CARE'              => 'Lip Product',
            'LIP CARE'               => 'Lip Product',
            'LIPGLOSS'               => 'Lip Product',
            'LIPSTIK'                => 'Lip Product',

            // SABUN
            'KOJIC ACID MILK SOAP'   => 'Sabun',
            'KOJIC SULFUR SOAP'      => 'Sabun',
            'SULFUR SOAP'            => 'Sabun',
            'MILK SOAP'              => 'Sabun',
            'BAMBOO CHARCOAL'        => 'Sabun',
            'SOAP'                   => 'Sabun',

            // KRIM WAJAH ACNE
            'ACNE BRIGHTENING CREAM' => 'Krim Wajah Acne',
            'DAY ACNE CREAM'         => 'Krim Wajah Acne',
            'DAY CREAM ACNE'         => 'Krim Wajah Acne',
            'SOFT ACNE CREAM'        => 'Krim Wajah Acne',
            'ACNE CREAM'             => 'Krim Wajah Acne',

            // KRIM WAJAH BRIGHTENING
            'SOFT BRIGHTENING CREAM' => 'Krim Wajah Brightening',
            'BRIGHTENING CREAM'      => 'Krim Wajah Brightening',
            'DAY WHITE CREAM'        => 'Krim Wajah Brightening',
            'DAY CREAM WHITE'        => 'Krim Wajah Brightening',
            'DAY PINK CREAM'         => 'Krim Wajah Brightening',
            'DAY CREAM PINK'         => 'Krim Wajah Brightening',
            'RADIANT BRIGHT'         => 'Krim Wajah Brightening',
            'RADIANT GLOW'           => 'Krim Wajah Brightening',

            // KRIM WAJAH ANTI AGING
            'SNAIL CREAM'            => 'Krim Wajah Anti Aging',
            'ANTI AGING EYE GEL'     => 'Krim Wajah Anti Aging',

            // MOISTURIZER & TREATMENT WAJAH
            'CNR PLUS'               => 'Moisturizer & Treatment Wajah',
            'DAILY CERAMOIST'        => 'Moisturizer & Treatment Wajah',
            'MOISTURIZER GEL'        => 'Moisturizer & Treatment Wajah',
            'GLOWTECH SPICULE'       => 'Moisturizer & Treatment Wajah',
            'REJUVENATION'           => 'Moisturizer & Treatment Wajah',

            // PEMBERSIH WAJAH
            'FACIAL WASH'            => 'Pembersih Wajah',
            'CLEANSING MILK'         => 'Pembersih Wajah',
            'MILK CLEANSER'          => 'Pembersih Wajah',
            'MICELLAR CLEAN'         => 'Pembersih Wajah',
            'MICELLAR WATER'         => 'Pembersih Wajah',
            'MICELLAR'               => 'Pembersih Wajah',

            // TONER & ESSENCE
            'EXFOLIATING COMPLEX TONER' => 'Toner & Essence',
            'HYDRATING ESSENCE TONER'   => 'Toner & Essence',
            'HYDRATING ESSENCE'         => 'Toner & Essence',
            'FACE MIST'                 => 'Toner & Essence',
            'T- CHAMOMILE'              => 'Toner & Essence',
            'TONER'                     => 'Toner & Essence',

            // EXFOLIATING
            'EXFOLIATING APPLE'      => 'Exfoliating',
            'EXFOLIATING STRAWBERRY' => 'Exfoliating',
            '3 IN 1 EXFOLIATING'     => 'Exfoliating',
            'EXFOLIATING DERMA'      => 'Exfoliating',
            'EXFOLIATING GEL'        => 'Exfoliating',
            'EXFOLIATING'            => 'Exfoliating',

            // MASKER & PEELING
            'BRIGHTENING PEEL'       => 'Masker & Peeling',
            'PEEL OFF MASK'          => 'Masker & Peeling',
            'PEEL OF MASK'           => 'Masker & Peeling',
            'PEELING GEL'            => 'Masker & Peeling',
            'GREEN TEA FACE MASK'    => 'Masker & Peeling',
            'HONEY FACE MASK'        => 'Masker & Peeling',
            'TEA TREE OIL FACE MASK' => 'Masker & Peeling',
            'RICE FACE MASK'         => 'Masker & Peeling',
            'FACE MASK'              => 'Masker & Peeling',
            'MASK'                   => 'Masker & Peeling',

            // SUNSCREEN
            'SUNSCREEN'              => 'Sunscreen',
            'SUNCREEN'               => 'Sunscreen',
            'SUNBLOK'                => 'Sunscreen',
            'SUNBLOCK'               => 'Sunscreen',

            // MAKEUP WAJAH
            'DAILY COMPACT POWDER'   => 'Makeup Wajah',
            'COMPACT POWDER'         => 'Makeup Wajah',
            'SILKY SOFT FACE POWDER' => 'Makeup Wajah',
            'SILKY SOFT POWDER'      => 'Makeup Wajah',
            'LIGHT SILKY SOFT POWDER'=> 'Makeup Wajah',
            'LIGHTENING SILKY'       => 'Makeup Wajah',
            'BB -'                   => 'Makeup Wajah',
            'BB CUSHION'             => 'Makeup Wajah',
            'BB CREAM'               => 'Makeup Wajah',
            'BODY FOUNDATION'        => 'Makeup Wajah',

            // SERUM WAJAH
            'LUMINOUS BRIGHTENING'   => 'Serum Wajah',
            'BEAUTY DNA SALMON'      => 'Serum Wajah',
            'DNA SALMON EXTRA'       => 'Serum Wajah',
            'SERUM'                  => 'Serum Wajah',

            // PERAWATAN TUBUH
            'BREAST CREAM'           => 'Perawatan Tubuh',
            'BODY FIRMING'           => 'Perawatan Tubuh',
            'FIRMING BODY'           => 'Perawatan Tubuh',
            'DAY BODY LOTION'        => 'Perawatan Tubuh',
            'NIGHT BODY LOTION'      => 'Perawatan Tubuh',
            'BODY LOTION'            => 'Perawatan Tubuh',
            'BODY SCRUB'             => 'Perawatan Tubuh',
            'BODY WASH'              => 'Perawatan Tubuh',
            'HAND BODY'              => 'Perawatan Tubuh',
            'LULUR'                  => 'Perawatan Tubuh',
            'STRETCH MARK'           => 'Perawatan Tubuh',
            'STRETCHMARK'            => 'Perawatan Tubuh',
            'COOLBRIGHT'             => 'Perawatan Tubuh',
            'DEO HERBA'              => 'Perawatan Tubuh',

            // SUPLEMEN & MINUMAN
            "D'ETAWA"                => 'Suplemen & Minuman',
            'DETAWA'                 => 'Suplemen & Minuman',
            'SUSU ETAWA'             => 'Suplemen & Minuman',
            'DRW KAPSUL'             => 'Suplemen & Minuman',
            'DRW SLIMMING'           => 'Suplemen & Minuman',
            'SLIMMING CAPSUL'        => 'Suplemen & Minuman',
            'KAPSUL GEMUK'           => 'Suplemen & Minuman',
            'HB DOSTING'             => 'Suplemen & Minuman',

            // AKSESORIS
            'POUCH'                  => 'Aksesoris / Pouch',
        ];

        foreach ($rules as $keyword => $namaKategori) {
            if (str_contains($upper, strtoupper($keyword))) {
                $kat = KategoriProduk::firstOrCreate([
                    'nama_kategori' => $namaKategori
                ]);

                return $kat->id_kategori;
            }
        }

        return null;
    }

    private function detectHeaderRow(array $rows, $kriteriaExcel): array
    {
        $kolomDicari = collect($kriteriaExcel->pluck('nama_kolom_excel'))
            ->map(fn($k) => strtoupper(trim($k)))
            ->push('NAMA BARANG')
            ->unique()->values()->toArray();

        foreach ($rows as $idx => $row) {
            if ($idx >= 15) break;

            $normalizedRow = array_map(fn($v) => strtoupper(trim($v ?? '')), $row);

            if (!in_array('NAMA BARANG', $normalizedRow)) continue;

            $nextRow        = $rows[$idx + 1] ?? [];
            $nextNormalized = array_map(fn($v) => strtoupper(trim($v ?? '')), $nextRow);
            $mergedHeaders  = [];

            foreach ($normalizedRow as $i => $val) {
                $sub = $nextNormalized[$i] ?? '';
                $mergedHeaders[$i] = ($sub && $val !== 'NAMA BARANG') ? $sub : $val;
            }

            $baseFound   = count(array_intersect($kolomDicari, $normalizedRow));
            $mergedFound = count(array_intersect($kolomDicari, $mergedHeaders));

            if ($mergedFound > $baseFound) {
                return [$idx + 1, $mergedHeaders];
            }

            return [$idx, $normalizedRow];
        }

        return [null, []];
    }

    private function parseAngka($raw): float
    {
        if ($raw === null || $raw === '') return 0.0;

        $str = preg_replace('/[Rp\s]/u', '', (string) $raw);

        if (substr_count($str, '.') > 1) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (substr_count($str, ',') > 1) {
            $str = str_replace(',', '', $str);
        } elseif (strpos($str, ',') !== false && strpos($str, '.') !== false) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
            $str = str_replace(',', '.', $str);
        }

        return is_numeric($str) ? (float) $str : 0.0;
    }

    private function updateStatusProduk(Produk $produk): void
    {
        $totalKriteria = Kriteria::count();
        $totalNilai    = NilaiProduk::where('id_produk', $produk->id_produk)->count();

        $produk->update([
            'status_data' => ($totalKriteria > 0 && $totalNilai >= $totalKriteria)
                ? 'Lengkap'
                : 'Belum Lengkap',
        ]);
    }

    private function clearImportSession(): void
    {
        session()->forget([
            'import_filename', 'import_total_data',
            'import_kolom_ditemukan', 'import_kolom_tidak_ada',
            'import_preview_rows',
        ]);
    }
}