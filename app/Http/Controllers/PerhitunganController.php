<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\NilaiProduk;
use App\Models\Perhitungan;
use App\Models\HasilPerhitungan;
use App\Models\DetailPerhitungan;
use App\Models\InputPermintaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; #mengambil id user
use Illuminate\Support\Facades\DB;
 
#Mengelola tampilan form, eksekusi perhitungan, hasil, riwayat, dan penghapusan data.
class PerhitunganController extends Controller
{

    public function index()
    {
        #mengambil semua kriteria, total bobot, jumlah produk, 
        $kriterias     = Kriteria::all();
        $totalBobot    = $kriterias->sum('bobot');
        $totalProduk   = Produk::count();
        $riwayat       = Perhitungan::orderBy('created_at', 'desc')->take(5)->get(); #untuk menampilkan 5 riwayat perhitungan di halaman perhitungan

        #mengambil produk yang siap dihitung (nilai dan nama produk) dari database
        $produks = $this->queryProdukUntukSPK()
            ->with('nilaiProduk')
            ->orderBy('nama_produk')
            ->get();

        #menghitung jumlah produk yang siap
        $produkLengkap = $produks->count();

        #menghitung jumlah produk yang sudah dipilih di input permintaan tapi belum ada nilainya 
        $produkDipilihBelumLengkap = 0;
        if (Kriteria::where('sumber_data', 'Manual')->exists()) {
            $idDipilih = InputPermintaan::distinct()->pluck('id_produk');
            $produkDipilihBelumLengkap = Produk::whereIn('id_produk', $idDipilih)
                ->where('status_data', 'Belum Lengkap')
                ->count();
        }

        return view('spk.hitung-spk', compact(
            'kriterias', 'totalBobot', 'produkLengkap', 'totalProduk',
            'riwayat', 'produks', 'produkDipilihBelumLengkap'
        ));
    }
    
    #produk mana yang boleh masuk ke perhitungan.
    private function queryProdukUntukSPK()
    {
        #mengecek kriteria dengan sumber data manual
        $adaKriteriaManual = Kriteria::where('sumber_data', 'Manual')->exists();
        #mengambil produk dengan status lengkap.
        $query = Produk::where('status_data', 'Lengkap');

        if ($adaKriteriaManual) {
            $produkDipilih = InputPermintaan::distinct()->pluck('id_produk');
            $query->whereIn('id_produk', $produkDipilih);
        }

        return $query; #kenapa bukan get, karna lebih fleksibel bisa dipake buat diperhitungan & mengambil data produk.
    }

   #PROSES PERHITUNGAN MOORA
    public function hitung(Request $request)
    {  
        $request->validate([
            'periode_data' => 'required|string|max:100',
        ], [
            'periode_data.required' => 'Periode data wajib diisi.',
            'periode_data.max'      => 'Periode data maksimal 100 karakter (contoh: "Mei 2026").',
        ]);

        // Ambil semua kriteria
        $kriterias = Kriteria::all();

        if ($kriterias->isEmpty()) {
            return back()->with('error', 'Belum ada kriteria. Tambahkan kriteria terlebih dahulu.');
        }
        #mengambil inputan bobot
        $bobotOverride = $request->input('bobot_override', []);
        if (!empty($bobotOverride)) {
            foreach ($kriterias as $kriteria) {
                if (isset($bobotOverride[$kriteria->id_kriteria])) {
                    #mengganti menjadi bobot kriteria sementara
                    $kriteria->bobot = (float) $bobotOverride[$kriteria->id_kriteria];
                }
            }
        }

        // Cek total bobot harus 100
        $totalBobot = $kriterias->sum('bobot');
        if (abs($totalBobot - 100) > 0.01) {
            return back()->with('error', "Total bobot kriteria harus 100%. Saat ini: {$totalBobot}%.");
        }

        #validasi semua produk yang dipilih harus lengkap untuk dihitung.
        $adaKriteriaManual = Kriteria::where('sumber_data', 'Manual')->exists();

        if ($adaKriteriaManual) {
            $idProdukDipilih = InputPermintaan::distinct()->pluck('id_produk');
            $jumlahDipilih   = $idProdukDipilih->count();
            $jumlahLengkap = Produk::whereIn('id_produk', $idProdukDipilih)
                ->where('status_data', 'Lengkap')
                ->count();

            # kalau ada produk dipilih tapi belum dinilai di tolak
            if ($jumlahLengkap < $jumlahDipilih) {
                $belumLengkap = $jumlahDipilih - $jumlahLengkap;
                return back()->with('error',
                    "Tidak bisa hitung: ada {$belumLengkap} produk yang dipilih tapi belum dinilai lengkap. " .
                    "Lengkapi dulu penilaian di menu Input Permintaan."
                );
            }
        }

        #Ambil semua produk yang berhak masuk perhitungan 
        $produks = $this->queryProdukUntukSPK()->get();

        if ($produks->count() < 2) {
            return back()->with('error',
                'Minimal 2 produk dipilih dan datanya lengkap untuk menjalankan perhitungan. ' .
                'Silakan pilih produk di menu Input Permintaan.'
            );
        }

        // Ambil semua nilai produk
        $semuaNilai = NilaiProduk::whereIn('id_produk', $produks->pluck('id_produk'))
            ->get()
            ->groupBy('id_produk');

        // =============================================
        // STEP 1: Bangun matriks keputusan
        // =============================================
        $matriks = [];
        foreach ($produks as $produk) {
            #mengambil semua nilai produk
            $nilaiProduk = $semuaNilai->get($produk->id_produk, collect());
            foreach ($kriterias as $kriteria) {
                #mengambil nilai kriteria
                $n = $nilaiProduk->firstWhere('id_kriteria', $kriteria->id_kriteria);
                #menyimpan dengan format matriks. jika gada nilai 0
                $matriks[$produk->id_produk][$kriteria->id_kriteria] = $n ? (float)$n->nilai : 0;
            }
        }

        // =============================================
        // NORMALISASI 
        // STEP 1: hitung pembagi (akar kuadrat dari nilai)
        // =============================================
        $akarKuadrat = [];
        foreach ($kriterias as $kriteria) {
            $sumKuadrat = 0;
            #Menjumlahkan kuadrat nilai semua produk pada kriteria tersebut. #pow(nilai, 2) artinya nilai dipangkatkan 2.
            foreach ($produks as $produk) {
                $sumKuadrat += pow($matriks[$produk->id_produk][$kriteria->id_kriteria], 2);
            }   
                #menyimpan penjumlahan akar kuadrat. kalo lebih dari 0 maka dihitung. kalo ga maka 0.
                $akarKuadrat[$kriteria->id_kriteria] = $sumKuadrat > 0 ? sqrt($sumKuadrat) : 1;
        }

        // =============================================
        // NORMALISASI 
        // STEP 2: normalisasi matriks
        // =============================================
        $matriksNormal = [];
        foreach ($produks as $produk) {
            foreach ($kriterias as $kriteria) {
                $nilaiAsli = $matriks[$produk->id_produk][$kriteria->id_kriteria];
                #proses normalisasi
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
                #mengambil nilai normalisai dan mengubah bobot jadi desimal
                $nilaiNormal = $matriksNormal[$produk->id_produk][$kriteria->id_kriteria];
                $bobot       = $kriteria->bobot / 100;

                #strtolower untuk membaca dalam format tulisan apapun.
                #mengalikan normalisasi dengan bobot.
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
        // Tingkat 3: Cost terendah (tie-breaker 2)
        // Tingkat 4: Nama A-Z (tie-breaker 3)
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
            #strcmp untuk bandingan nama produk, lalu urutkan.
            return strcmp($a['produk']->nama_produk, $b['produk']->nama_produk);
        });

        // =============================================
        // Dense Ranking 
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
                // Yi berbeda → ranking = lanjutkan urutan
                $hasilYi[$idProduk]['ranking'] = $urutan;
            }

            $prevYi      = $data['yi'];
            $prevRanking = $hasilYi[$idProduk]['ranking'];
        }

        // ========================================================
        // STEP 6: Pemngelompokan Kuartil dari distribusi nilai Yi
        // ========================================================
        #mengambil nilai yi dari array hasil yi
        $semuaYi = array_map(fn($d) => $d['yi'], array_values($hasilYi));
        sort($semuaYi); // ascending untuk perhitungan kuartil

        $n  = count($semuaYi);
        $q1 = $semuaYi[(int) floor($n * 0.25)]; // Q1 = 25th percentile
        $q3 = $semuaYi[(int) floor($n * 0.75)]; // Q3 = 75th percentile

        // =============================================
        // STEP 7: Simpan ke database (dalam transaksi)
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
            
            #mmenyimpan produk prioritas. mengubah array Yi agar indexnya jadi 0
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
                    $prioritas = 'Utama';         // 75% teratas
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
            #menyimpan id perhitungan terakhir
            session(['last_perhitungan_id' => $perhitungan->id_perhitungan]);
        });

        return redirect()->route('perhitungan.hasil', session('last_perhitungan_id'))
            ->with('success', 'Perhitungan MOORA berhasil dijalankan.');
    }

    #Tampilkan halaman hasil perhitungan berdasarkan id perhitungan.
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

        #mengambil kriteria dari riwayat perhitungan dari bobot snapshot karna kriteria itu dinamis. 
        $kriterias = collect($perhitungan->bobot_snapshot);

        return view('spk.hasil-perhitungan', compact('perhitungan', 'hasil', 'kriterias'));
    }

    #Tampilkan riwayat perhitungan
    public function riwayat()
    {
        $riwayat = Perhitungan::orderBy('created_at', 'desc')->get();
        return view('spk.riwayat', compact('riwayat'));
    }

    #menghapus perhitungan
    public function destroy($id)
    {
        $perhitungan = Perhitungan::findOrFail($id);
        $perhitungan->delete();
        return redirect()->route('perhitungan.riwayat')
            ->with('success', 'Riwayat perhitungan berhasil dihapus.');
    }
}