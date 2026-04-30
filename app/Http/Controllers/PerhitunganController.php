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

class PerhitunganController extends Controller
{
    // Halaman form hitung SPK
    public function index()
    {
        $kriterias     = Kriteria::all();
        $totalBobot    = $kriterias->sum('bobot');
        $totalProduk   = Produk::count();
        $riwayat       = Perhitungan::orderBy('created_at', 'desc')->take(5)->get();

        // Produk lengkap beserta nilai tiap kriterianya (untuk preview matriks di view)
        $produks       = Produk::with('nilaiProduk')
            ->where('status_data', 'Lengkap')
            ->orderBy('nama_produk')
            ->get();
        $produkLengkap = $produks->count();

        return view('spk.hitung-spk', compact(
            'kriterias', 'totalBobot', 'produkLengkap', 'totalProduk', 'riwayat', 'produks'
        ));
    }

    // Jalankan perhitungan MOORA
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

        // Terapkan bobot override dari slider jika ada (dikirim JS dari halaman hitung-spk)
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

        // Ambil semua nilai produk sekaligus (efisien, 1 query)
        $semuaNilai = NilaiProduk::whereIn('id_produk', $produks->pluck('id_produk'))
            ->get()
            ->groupBy('id_produk');

        // =============================================
        // STEP 1: Bangun matriks keputusan
        // Format: [id_produk => [id_kriteria => nilai]]
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
        // sqrt(sum(xij^2)) untuk setiap kolom kriteria
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
        // x*ij = xij / sqrt(sum(xij^2))
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
        // STEP 4: Hitung Yi = sum(benefit * bobot) - sum(cost * bobot)
        // bobot dalam persen, dibagi 100
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
                'produk'        => $produk,
                'benefit'       => $benefit,
                'cost'          => $cost,
                'yi'            => $benefit - $cost,
            ];
        }

        // =============================================
        // STEP 5: Ranking berdasarkan Yi (descending)
        // =============================================
        uasort($hasilYi, fn($a, $b) => $b['yi'] <=> $a['yi']);

        // =============================================
        // STEP 6: Simpan ke database (dalam transaksi)
        // =============================================
        DB::transaction(function () use (
            $request, $kriterias, $produks, $matriks, $matriksNormal, $hasilYi
        ) {
            // Snapshot bobot kriteria
            $bobotSnapshot = $kriterias->map(fn($k) => [
                'id_kriteria'  => $k->id_kriteria,
                'nama'         => $k->nama_kriteria,
                'tipe_atribut' => $k->tipe_atribut,
                'bobot'        => $k->bobot,
                'sumber_data'  => $k->sumber_data,
            ])->toArray();

            // Produk ranking 1
            $produkPrioritas = array_values($hasilYi)[0]['produk']->nama_produk;

            // Simpan header perhitungan
            $perhitungan = Perhitungan::create([
                'id_user' => Auth::id(),
                'periode_data'      => $request->periode_data,
                'jumlah_produk'     => count($hasilYi),
                'total_produk'      => Produk::count(),
                'produk_prioritas'  => $produkPrioritas,
                'bobot_snapshot'    => $bobotSnapshot,
                'matriks_keputusan' => $matriks,
                'matriks_normal'    => $matriksNormal,
            ]);

            // Simpan hasil per produk
            $ranking = 1;
            foreach ($hasilYi as $idProduk => $data) {
                // Tentukan label prioritas
                if ($ranking === 1) {
                    $prioritas = 'Utama';
                } elseif ($ranking <= 3) {
                    $prioritas = 'Pertimbangkan';
                } else {
                    $prioritas = 'Tunda';
                }

                $hasil = HasilPerhitungan::create([
                    'id_perhitungan' => $perhitungan->id_perhitungan,
                    'id_produk'      => $idProduk,
                    'nama_produk'    => $data['produk']->nama_produk,
                    'nilai_yi'       => $data['yi'],
                    'total_benefit'  => $data['benefit'],
                    'total_cost'     => $data['cost'],
                    'ranking'        => $ranking,
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

                $ranking++;
            }

            // Simpan id perhitungan ke session untuk redirect ke hasil
            session(['last_perhitungan_id' => $perhitungan->id_perhitungan]);
        });

        return redirect()->route('perhitungan.hasil', session('last_perhitungan_id'))
            ->with('success', 'Perhitungan MOORA berhasil dijalankan.');
    }

    // Halaman hasil perhitungan
    public function hasil($id)
    {
        $perhitungan = Perhitungan::findOrFail($id);
        $hasil = HasilPerhitungan::with('detailPerhitungan')
            ->where('id_perhitungan', $id)
            ->orderBy('ranking')
            ->get();

        $kriterias = collect($perhitungan->bobot_snapshot);

        return view('spk.hasil-perhitungan', compact('perhitungan', 'hasil', 'kriterias'));
    }

    // Halaman riwayat semua perhitungan
    public function riwayat()
    {
        $riwayat = Perhitungan::orderBy('created_at', 'desc')->get();
        return view('spk.riwayat', compact('riwayat'));
    }

    // Hapus perhitungan
    public function destroy($id)
    {
        $perhitungan = Perhitungan::findOrFail($id);
        $perhitungan->delete();
        return redirect()->route('perhitungan.riwayat')
            ->with('success', 'Riwayat perhitungan berhasil dihapus.');
    }
}