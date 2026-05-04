<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\NilaiProduk;
use App\Models\Perhitungan;
use App\Models\HasilPerhitungan;
use App\Models\DetailPerhitungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller untuk alur SPK MOORA.
 * Mengelola tampilan form, eksekusi perhitungan, hasil, riwayat, dan penghapusan data.
 */
class PerhitunganController extends Controller
{
    /**
     * Tampilkan halaman form perhitungan SPK.
     * Mengambil kriteria, produk yang siap dihitung, dan riwayat perhitungan terakhir.
     */
    public function index()
    {
        $kriterias     = Kriteria::all();
        $totalBobot    = $kriterias->sum('bobot');
        $totalProduk   = Produk::count();
        $riwayat       = Perhitungan::orderBy('created_at', 'desc')->take(5)->get();

        $produks       = Produk::with('nilaiProduk')
            ->where('status_data', 'Lengkap')
            ->orderBy('nama_produk')
            ->get();
        $produkLengkap = $produks->count();

        return view('spk.hitung-spk', compact(
            'kriterias', 'totalBobot', 'produkLengkap', 'totalProduk', 'riwayat', 'produks'
        ));
    }

    /**
     * Jalankan perhitungan MOORA.
     * Validasi input, hitung normalisasi, nilai Yi, ranking, dan simpan hasil ke database.
     */
    public function hitung(Request $request)
    {
        $request->validate([
            'periode_data' => 'required|string|max:50',
        ]);

        // Ambil semua kriteria
        $kriterias = Kriteria::all();

        if ($kriterias->isEmpty()) {
            return back()->with('error', 'Belum ada kriteria. Tambahkan kriteria terlebih dahulu.');
        }

        // Terapkan bobot override dari slider jika ada
        $bobotOverride = $request->input('bobot_override', []);
        if (!empty($bobotOverride)) {
            foreach ($kriterias as $kriteria) {
                if (isset($bobotOverride[$kriteria->id_kriteria])) {
                    $kriteria->bobot = (float) $bobotOverride[$kriteria->id_kriteria];
                }
            }
        }

        // Cek total bobot harus 100
        $totalBobot = $kriterias->sum('bobot');
        if (abs($totalBobot - 100) > 0.01) {
            return back()->with('error', "Total bobot kriteria harus 100%. Saat ini: {$totalBobot}%.");
        }

        // Ambil semua produk yang statusnya Lengkap
        $produks = Produk::where('status_data', 'Lengkap')->get();

        if ($produks->count() < 2) {
            return back()->with('error', 'Minimal 2 produk dengan data lengkap untuk menjalankan perhitungan.');
        }

        // Ambil semua nilai produk sekaligus (1 query)
        $semuaNilai = NilaiProduk::whereIn('id_produk', $produks->pluck('id_produk'))
            ->get()
            ->groupBy('id_produk');

        // =============================================
        // STEP 1: Bangun matriks keputusan
        // =============================================
        $matriks = [];
        foreach ($produks as $produk) {
            $nilaiProduk = $semuaNilai->get($produk->id_produk, collect());
            foreach ($kriterias as $kriteria) {
                $n = $nilaiProduk->firstWhere('id_kriteria', $kriteria->id_kriteria);
                $matriks[$produk->id_produk][$kriteria->id_kriteria] = $n ? (float)$n->nilai : 0;
            }
        }

        // =============================================
        // STEP 2: Hitung akar jumlah kuadrat per kriteria
        // =============================================
        $akarKuadrat = [];
        foreach ($kriterias as $kriteria) {
            $sumKuadrat = 0;
            foreach ($produks as $produk) {
                $sumKuadrat += pow($matriks[$produk->id_produk][$kriteria->id_kriteria], 2);
            }
            $akarKuadrat[$kriteria->id_kriteria] = $sumKuadrat > 0 ? sqrt($sumKuadrat) : 1;
        }

        // =============================================
        // STEP 3: Normalisasi matriks
        // =============================================
        $matriksNormal = [];
        foreach ($produks as $produk) {
            foreach ($kriterias as $kriteria) {
                $nilaiAsli = $matriks[$produk->id_produk][$kriteria->id_kriteria];
                $matriksNormal[$produk->id_produk][$kriteria->id_kriteria] =
                    $akarKuadrat[$kriteria->id_kriteria] > 0
                    ? $nilaiAsli / $akarKuadrat[$kriteria->id_kriteria]
                    : 0;
            }
        }

        // =============================================
        // STEP 4: Hitung Yi = sum(benefit*bobot) - sum(cost*bobot)
        // =============================================
        $hasilYi = [];
        foreach ($produks as $produk) {
            $benefit = 0;
            $cost    = 0;
            foreach ($kriterias as $kriteria) {
                $nilaiNormal = $matriksNormal[$produk->id_produk][$kriteria->id_kriteria];
                $bobot       = $kriteria->bobot / 100;

                if (strtolower($kriteria->tipe_atribut) === 'benefit') {
                    $benefit += $nilaiNormal * $bobot;
                } else {
                    $cost += $nilaiNormal * $bobot;
                }
            }
            $hasilYi[$produk->id_produk] = [
                'produk'  => $produk,
                'benefit' => $benefit,
                'cost'    => $cost,
                'yi'      => $benefit - $cost,
            ];
        }

        // =============================================
        // STEP 5: Urutkan dengan sort berlapis 4 tingkat
        //
        // Tingkat 1: Yi tertinggi (utama)
        // Tingkat 2: Benefit tertinggi (tie-breaker 1)
        //            → produk dengan kontribusi nilai lebih besar didahulukan
        // Tingkat 3: Cost terendah (tie-breaker 2)
        //            → produk lebih efisien biaya didahulukan
        // Tingkat 4: Nama A-Z (tie-breaker 3)
        //            → netral dan konsisten untuk produk benar-benar identik
        // =============================================
        uasort($hasilYi, function ($a, $b) {
            // Tingkat 1: Yi tertinggi
            if (round($b['yi'], 8) !== round($a['yi'], 8)) {
                return $b['yi'] <=> $a['yi'];
            }
            // Tingkat 2: Benefit tertinggi
            if (round($b['benefit'], 8) !== round($a['benefit'], 8)) {
                return $b['benefit'] <=> $a['benefit'];
            }
            // Tingkat 3: Cost terendah
            if (round($a['cost'], 8) !== round($b['cost'], 8)) {
                return $a['cost'] <=> $b['cost'];
            }
            // Tingkat 4: Nama A-Z
            return strcmp($a['produk']->nama_produk, $b['produk']->nama_produk);
        });

        // =============================================
        // STEP 5b: Dense Ranking
        // Produk dengan Yi sama mendapat ranking yang sama.
        // Contoh: jika 4 produk bernilai Yi=0.0749, semuanya
        // mendapat ranking yang sama (bukan 7,8,9,10).
        // =============================================
        $urutan      = 0;
        $prevYi      = null;
        $prevRanking = 1;

        foreach ($hasilYi as $idProduk => $data) {
            $urutan++;

            if ($prevYi === null) {
                $hasilYi[$idProduk]['ranking'] = 1;
            } elseif (round($data['yi'], 8) === round($prevYi, 8)) {
                // Yi sama → ranking sama dengan sebelumnya
                $hasilYi[$idProduk]['ranking'] = $prevRanking;
            } else {
                // Yi berbeda → ranking = urutan absolut saat ini
                $hasilYi[$idProduk]['ranking'] = $urutan;
            }

            $prevYi      = $data['yi'];
            $prevRanking = $hasilYi[$idProduk]['ranking'];
        }

        // =============================================
        // STEP 5c: Hitung Kuartil dari distribusi nilai Yi
        //
        // Threshold ditentukan secara statistik (bukan asumsi manual):
        //   Q3 (75th percentile) = batas atas
        //   Q1 (25th percentile) = batas bawah
        //
        // Kategori prioritas:
        //   Yi >= Q3  → Utama         (25% produk terbaik)
        //   Yi >= Q1  → Pertimbangkan (50% produk menengah)
        //   Yi <  Q1  → Tunda         (25% produk terbawah)
        // =============================================
        $semuaYi = array_map(fn($d) => $d['yi'], array_values($hasilYi));
        sort($semuaYi); // ascending untuk perhitungan kuartil

        $n  = count($semuaYi);
        $q1 = $semuaYi[(int) floor($n * 0.25)]; // Q1 = 25th percentile
        $q3 = $semuaYi[(int) floor($n * 0.75)]; // Q3 = 75th percentile

        // =============================================
        // STEP 6: Simpan ke database (dalam transaksi)
        // =============================================
        DB::transaction(function () use (
            $request, $kriterias, $produks, $matriks, $matriksNormal, $hasilYi, $q1, $q3
        ) {
            $bobotSnapshot = $kriterias->map(fn($k) => [
                'id_kriteria'  => $k->id_kriteria,
                'nama'         => $k->nama_kriteria,
                'tipe_atribut' => $k->tipe_atribut,
                'bobot'        => $k->bobot,
                'sumber_data'  => $k->sumber_data,
            ])->toArray();

            $produkPrioritas = array_values($hasilYi)[0]['produk']->nama_produk;

            // Simpan header perhitungan
            $perhitungan = Perhitungan::create([
                'id_user'           => Auth::id(),
                'periode_data'      => $request->periode_data,
                'jumlah_produk'     => count($hasilYi),
                'total_produk'      => Produk::count(),
                'produk_prioritas'  => $produkPrioritas,
                'bobot_snapshot'    => $bobotSnapshot,
                'matriks_keputusan' => $matriks,
                'matriks_normal'    => $matriksNormal,
            ]);

            // Simpan hasil per produk
            foreach ($hasilYi as $idProduk => $data) {

                // Tentukan prioritas berdasarkan kuartil Yi
                if ($data['yi'] >= $q3) {
                    $prioritas = 'Utama';         // 25% teratas
                } elseif ($data['yi'] >= $q1) {
                    $prioritas = 'Pertimbangkan'; // 50% menengah
                } else {
                    $prioritas = 'Tunda';         // 25% terbawah
                }

                $hasil = HasilPerhitungan::create([
                    'id_perhitungan' => $perhitungan->id_perhitungan,
                    'id_produk'      => $idProduk,
                    'nama_produk'    => $data['produk']->nama_produk,
                    'nilai_yi'       => $data['yi'],
                    'total_benefit'  => $data['benefit'],
                    'total_cost'     => $data['cost'],
                    'ranking'        => $data['ranking'],
                    'prioritas'      => $prioritas,
                ]);

                // Simpan detail per kriteria
                foreach ($kriterias as $kriteria) {
                    DetailPerhitungan::create([
                        'id_hasil'      => $hasil->id_hasil,
                        'nama_kriteria' => $kriteria->nama_kriteria,
                        'tipe_atribut'  => $kriteria->tipe_atribut,
                        'nilai_asli'    => $matriks[$idProduk][$kriteria->id_kriteria],
                        'nilai_normal'  => $matriksNormal[$idProduk][$kriteria->id_kriteria],
                        'bobot'         => $kriteria->bobot,
                    ]);
                }
            }

            session(['last_perhitungan_id' => $perhitungan->id_perhitungan]);
        });

        return redirect()->route('perhitungan.hasil', session('last_perhitungan_id'))
            ->with('success', 'Perhitungan MOORA berhasil dijalankan.');
    }

    /**
     * Tampilkan halaman hasil perhitungan berdasarkan id perhitungan.
     * Menyiapkan data hasil produk dan snapshot bobot kriteria.
     */
    public function hasil($id)
    {
        $perhitungan = Perhitungan::findOrFail($id);
        $hasil = HasilPerhitungan::with('detailPerhitungan')
            ->where('id_perhitungan', $id)
            ->orderBy('ranking')
            ->orderByDesc('total_benefit')  // tie-breaker 1
            ->orderBy('total_cost')         // tie-breaker 2
            ->orderBy('nama_produk')       // tie-breaker 3
            ->get();

        $kriterias = collect($perhitungan->bobot_snapshot);

        return view('spk.hasil-perhitungan', compact('perhitungan', 'hasil', 'kriterias'));
    }

    /**
     * Tampilkan riwayat semua perhitungan SPK.
     * Menyajikan daftar perhitungan yang sudah disimpan untuk ditinjau ulang.
     */
    public function riwayat()
    {
        $riwayat = Perhitungan::orderBy('created_at', 'desc')->get();
        return view('spk.riwayat', compact('riwayat'));
    }

    /**
     * Hapus perhitungan terpilih.
     * Menghapus data perhitungan beserta relasi hasilnya dari database.
     */
    public function destroy($id)
    {
        $perhitungan = Perhitungan::findOrFail($id);
        $perhitungan->delete();
        return redirect()->route('perhitungan.riwayat')
            ->with('success', 'Riwayat perhitungan berhasil dihapus.');
    }
}