<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\NilaiProduk;
use App\Models\Kriteria;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSX;

class ProdukController extends Controller
{
    // Tampilkan halaman data produk
    public function index()
    {
        $sortBy = request('sort', 'abjad'); // default urut abjad
        $produks = Produk::with('nilaiProduk.kriteria')
            ->when($sortBy === 'abjad', fn($q) => $q->orderBy('nama_produk', 'asc'))
            ->when($sortBy === 'terbaru', fn($q) => $q->orderBy('created_at', 'desc'))
            ->when($sortBy === 'terlama', fn($q) => $q->orderBy('created_at', 'asc'))
            ->get();

        // Kirim daftar kriteria Manual ke view untuk form tambah manual
        $kriteriaManual = Kriteria::where('sumber_data', 'Manual')->get();

        // Kirim semua kriteria untuk ditampilkan di kolom tabel
        $semuaKriteria = Kriteria::all();

        return view('spk.data-produk', compact('produks', 'kriteriaManual', 'semuaKriteria'));
    }

    // Tambah produk manual (nama + nilai kriteria Manual)
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

        // Simpan nilai kriteria Manual jika ada
        if ($request->has('nilai_kriteria')) {
            foreach ($request->nilai_kriteria as $idKriteria => $nilai) {
                if ($nilai !== null && $nilai !== '') {
                    NilaiProduk::updateOrCreate(
                        [
                            'id_produk'   => $produk->id_produk,
                            'id_kriteria' => $idKriteria,
                        ],
                        ['nilai' => (float) $nilai]
                    );
                }
            }
        }

        // Update status produk
        $this->updateStatusProduk($produk);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    // Import dari Excel
    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file_excel');

        // Buat folder temp kalau belum ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(storage_path('app/temp'), $filename);
        $path = storage_path('app/temp/' . $filename);

        if ($xlsx = SimpleXLSX::parse($path)) {
            $rows = $xlsx->rows();

                // Gabung baris 4 dan 5 sebagai header
                // Baris 4 (index 3) untuk kolom utama, baris 5 (index 4) untuk sub-header
                $headers = [];
                foreach ($rows[3] as $i => $val) {
                    $sub = strtoupper(trim($rows[4][$i] ?? ''));
                    $main = strtoupper(trim($val ?? ''));
                    // Prioritaskan sub-header jika ada, kecuali untuk kolom utama
                    $headers[$i] = $sub ?: $main;
                }
            // Ambil semua kriteria yang sumber datanya Excel dan punya nama_kolom_excel
            $kriteriaExcel = Kriteria::where('sumber_data', 'Excel')
                ->whereNotNull('nama_kolom_excel')
                ->get();

            // Validasi: semua kolom kriteria harus ada di file Excel
            $missingColumns = [];
            foreach ($kriteriaExcel as $kriteria) {
                $kolomExcel = strtoupper(trim($kriteria->nama_kolom_excel));
                if (!in_array($kolomExcel, $headers)) {
                    $missingColumns[] = $kriteria->nama_kolom_excel
                        . ' (kriteria: ' . $kriteria->nama_kriteria . ')';
                }
            }

            if (!empty($missingColumns)) {
                unlink($path);
                return back()->with('error',
                    'Kolom berikut tidak ditemukan di file Excel: '
                    . implode(', ', $missingColumns)
                    . '. Pastikan nama kolom persis sama.'
                );
            }

            // Cari index kolom NAMA BARANG
            $colNama = array_search('NAMA BARANG', $headers);
            if ($colNama === false) {
                unlink($path);
                return back()->with('error', 'Kolom NAMA BARANG tidak ditemukan di file Excel.');
            }

            // Buat peta: id_kriteria => index kolom di Excel
            $kolomMap = [];
            foreach ($kriteriaExcel as $kriteria) {
                $kolomExcel = strtoupper(trim($kriteria->nama_kolom_excel));
                $colIdx = array_search($kolomExcel, $headers);
                if ($colIdx !== false) {
                    $kolomMap[$kriteria->id_kriteria] = $colIdx;
                }
            }

            $imported = 0;
            $updated  = 0;

            // Data mulai dari baris ke-5 (index 4)
            for ($i = 5; $i < count($rows); $i++) {
                $nama = trim($rows[$i][$colNama] ?? '');
                if (!$nama) continue;

                // Buat atau temukan produk
                $produk = Produk::firstOrCreate(
                    ['nama_produk' => $nama],
                    ['status_data' => 'Belum Lengkap']
                );

                if ($produk->wasRecentlyCreated) {
                    $imported++;
                } else {
                    $updated++;
                }

                // Simpan nilai per kriteria Excel
                foreach ($kolomMap as $idKriteria => $colIdx) {
                    $rawNilai = $rows[$i][$colIdx] ?? 0;

                    // Bersihkan format angka (hapus titik ribuan, ganti koma desimal)
                    $rawNilai = str_replace('.', '', $rawNilai);
                    $rawNilai = str_replace(',', '.', $rawNilai);
                    $nilai    = is_numeric($rawNilai) ? (float) $rawNilai : 0;

                    NilaiProduk::updateOrCreate(
                        [
                            'id_produk'   => $produk->id_produk,
                            'id_kriteria' => $idKriteria,
                        ],
                        ['nilai' => $nilai]
                    );
                }

                // Update status setelah nilai tersimpan
                $this->updateStatusProduk($produk);
            }

            unlink($path);

            $msg = "$imported produk baru ditambahkan";
            if ($updated > 0) $msg .= ", $updated produk diperbarui nilainya";
            return back()->with('success', $msg . ' dari Excel.');
        }

        if (file_exists($path)) unlink($path);

        $error = SimpleXLSX::parseError();
        return back()->with('error', 'Gagal membaca file Excel: ' . $error);
    }

    // Edit nama produk
    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);
        $request->validate([
            'nama_produk' => 'required|string|max:255',
        ]);

        $produk->update([
            'nama_produk' => $request->nama_produk,
        ]);

        return back()->with('success', 'Produk berhasil diupdate.');
    }

    // Hapus produk
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();
        return back()->with('success', 'Produk berhasil dihapus.');
    }

    // Helper: hitung ulang dan update status_data produk
    // Produk dianggap Lengkap jika semua kriteria (Excel + Manual) sudah ada nilainya
    private function updateStatusProduk(Produk $produk): void
    {
        $totalKriteria = Kriteria::count();
        $totalNilai    = NilaiProduk::where('id_produk', $produk->id_produk)->count();

        $produk->update([
            'status_data' => ($totalNilai >= $totalKriteria && $totalKriteria > 0)
                ? 'Lengkap'
                : 'Belum Lengkap',
        ]);
    }
}