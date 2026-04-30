<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\NilaiProduk;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shuchkin\SimpleXLSX;

class ProdukController extends Controller
{
    public function index()
    {
        $sortBy = request('sort', 'abjad');
        $produks = Produk::with('nilaiProduk.kriteria')
            ->when($sortBy === 'abjad',   fn($q) => $q->orderBy('nama_produk', 'asc'))
            ->when($sortBy === 'terbaru', fn($q) => $q->orderBy('created_at', 'desc'))
            ->when($sortBy === 'terlama', fn($q) => $q->orderBy('created_at', 'asc'))
            ->get();

        $kriteriaManual = Kriteria::where('sumber_data', 'Manual')->get();
        $semuaKriteria  = Kriteria::all();
        $kriteriaExcel  = Kriteria::where('sumber_data', 'Excel')
            ->whereNotNull('nama_kolom_excel')
            ->get();

        return view('spk.data-produk', compact(
            'produks', 'kriteriaManual', 'semuaKriteria', 'kriteriaExcel'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_produk'    => 'required|string|max:255',
            'nilai_kriteria' => 'nullable|array',
        ]);

        $produk = Produk::create([
            'nama_produk' => $request->nama_produk,
            'status_data' => 'Belum Lengkap',
        ]);

        if ($request->has('nilai_kriteria')) {
            foreach ($request->nilai_kriteria as $idKriteria => $nilai) {
                if ($nilai !== null && $nilai !== '') {
                    NilaiProduk::updateOrCreate(
                        ['id_produk' => $produk->id_produk, 'id_kriteria' => $idKriteria],
                        ['nilai' => (float) $nilai]
                    );
                }
            }
        }

        $this->updateStatusProduk($produk);
        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    // STEP 1: Upload file, baca, validasi, simpan ke session, redirect ke preview
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

        // Deteksi baris header secara fleksibel (cari di max 15 baris pertama)
        [$headerRowIdx, $headers] = $this->detectHeaderRow($rows, $kriteriaExcel);

        if ($headerRowIdx === null) {
            @unlink($path);
            return back()->with('error',
                'Kolom "NAMA BARANG" tidak ditemukan di file Excel (dicari di 15 baris pertama). ' .
                'Pastikan file memiliki kolom dengan nama tersebut.'
            );
        }

        $colNama = array_search('NAMA BARANG', $headers);

        // Petakan kriteria ke indeks kolom
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

        // Kumpulkan preview 5 baris pertama & hitung total data
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

        // Simpan ke session
        session([
            'import_filename'        => $filename,
            'import_total_data'      => $totalData,
            'import_kolom_ditemukan' => $kolomDitemukan,
            'import_kolom_tidak_ada' => $kolomTidakAda,
            'import_preview_rows'    => $previewRows,
        ]);

        return redirect()->route('produk.preview.show');
    }

    // STEP 2: Tampilkan halaman preview
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

    // STEP 3: Konfirmasi — import ke DB dalam transaksi
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

                    $isNew  = !Produk::where('nama_produk', $nama)->exists();
                    $produk = Produk::firstOrCreate(
                        ['nama_produk' => $nama],
                        ['status_data' => 'Belum Lengkap']
                    );

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

    // Batal preview
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
        $request->validate(['nama_produk' => 'required|string|max:255']);
        $produk->update(['nama_produk' => $request->nama_produk]);
        return back()->with('success', 'Produk berhasil diupdate.');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────────────────────

    // Deteksi baris header secara fleksibel (mendukung multi-row header)
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

            // Coba gabung dengan baris berikutnya (sub-header)
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
                return [$idx + 1, $mergedHeaders]; // Data mulai setelah baris sub-header
            }

            return [$idx, $normalizedRow];
        }

        return [null, []];
    }

    // Parse angka dengan deteksi format Indonesia/Internasional otomatis
    private function parseAngka($raw): float
    {
        if ($raw === null || $raw === '') return 0.0;

        $str = preg_replace('/[Rp\s]/u', '', (string) $raw);

        if (substr_count($str, '.') > 1) {
            // Format Indonesia: 1.234.567,89
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } elseif (substr_count($str, ',') > 1) {
            // Format internasional: 1,234,567
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
