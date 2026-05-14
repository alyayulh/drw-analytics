<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\InputPermintaan;
use App\Models\NilaiProduk;
use Illuminate\Http\Request;

class InputPermintaanController extends Controller
{
    public function index()
    {
        $kriterias = Kriteria::where('sumber_data', 'Manual')->get();

        // FIX BUG #2 & #5: eager load relasi 'kategori' agar $p->kategori adalah object,
        // lalu akses ->nama_kategori untuk groupBy yang benar.
        // Sebelumnya: ->groupBy(fn($p) => $p->kategori ?: 'Tanpa Kategori')
        //   → $p->kategori memanggil relasi Eloquent dan mengembalikan object/null, bukan string!
        //   → Sehingga semua produk masuk 'Tanpa Kategori'.
        $produkByKategori = Produk::with('kategoriProduk')->get()
            ->groupBy(fn($p) => $p->kategoriProduk?->nama_kategori ?? 'Tanpa Kategori')
            ->sortKeys();

        // Ambil semua inputs, filter max 5 per kategori
        $allInputs = InputPermintaan::all()->groupBy('id_produk');

        $validProdukIds = collect();
        foreach ($produkByKategori as $kategori => $items) {
            $saved = $items
                ->filter(fn($p) => $allInputs->has((string)$p->id_produk))
                ->take(5)
                ->pluck('id_produk')
                ->map(fn($id) => (string)$id);
            $validProdukIds = $validProdukIds->merge($saved);
        }

        $inputs = $allInputs->filter(
            fn($rows, $id) => $validProdukIds->contains((string)$id)
        );

        return view('spk.input-permintaan', compact('produkByKategori', 'kriterias', 'inputs'));
    }

    public function store(Request $request)
    {
        if (!$request->has('data')) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid']);
        }

        $data = json_decode($request->data, true);

        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Format data salah']);
        }

        // FIX BUG #2 & #5: eager load relasi kategori, gunakan nama_kategori untuk groupBy
        $produkPerKategori = [];
        foreach (array_keys($data) as $id_produk) {
            $produk = Produk::with('kategoriProduk')->find($id_produk);
            if (!$produk) continue;
            // Sebelumnya: $produk->kategori → mengembalikan object, bukan string
            $kat = $produk->kategoriProduk?->nama_kategori ?? 'Tanpa Kategori';
            $produkPerKategori[$kat][] = (string)$id_produk;
        }

        $allowedProdukIds = [];
        foreach ($produkPerKategori as $kat => $ids) {
            foreach (array_slice($ids, 0, 5) as $id) {
                $allowedProdukIds[] = $id;
            }
        }

        try {
            \DB::transaction(function () use ($data, $allowedProdukIds) {
                $manualKriteriaIds = Kriteria::where('sumber_data', 'Manual')->pluck('id_kriteria');

                // Hapus SEMUA data manual lama — setiap simpan = set baru yang menggantikan sebelumnya
                InputPermintaan::whereIn('id_kriteria', $manualKriteriaIds)->delete();
                NilaiProduk::whereIn('id_kriteria', $manualKriteriaIds)->delete();

                // Reset status_data semua produk yang punya nilai manual ke Belum Lengkap dulu
                // (akan diupdate ulang di bawah untuk yang masuk sesi ini)
                Produk::whereIn('id_produk', function($q) use ($manualKriteriaIds) {
                    $q->select('id_produk')->from('nilai_produk')
                      ->whereIn('id_kriteria', $manualKriteriaIds);
                })->update(['status_data' => 'Belum Lengkap']);

                foreach ($data as $id_produk => $kriteriaValues) {
                    if (!in_array((string)$id_produk, $allowedProdukIds)) continue;

                    $produk = Produk::find($id_produk);
                    if (!$produk) continue;

                    foreach ($kriteriaValues as $id_kriteria => $nilai) {
                        InputPermintaan::updateOrCreate(
                            ['id_produk'   => $id_produk, 'id_kriteria' => $id_kriteria],
                            ['nilai_input' => $nilai]
                        );

                        NilaiProduk::updateOrCreate(
                            ['id_produk'   => $id_produk, 'id_kriteria' => $id_kriteria],
                            ['nilai'       => (float) $nilai]
                        );
                    }

                    $totalKriteria = Kriteria::count();
                    $totalNilai    = NilaiProduk::where('id_produk', $id_produk)->count();
                    $produk->update([
                        'status_data' => ($totalNilai >= $totalKriteria && $totalKriteria > 0)
                            ? 'Lengkap' : 'Belum Lengkap'
                    ]);
                }
            });
        } catch (\Exception $e) {
            \Log::error('InputPermintaan store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan: ' . $e->getMessage()
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
    }
}