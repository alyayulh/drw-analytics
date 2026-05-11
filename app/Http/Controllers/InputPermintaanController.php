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
    $inputs    = InputPermintaan::all()->groupBy('id_produk');

    $produkByKategori = Produk::all()
        ->groupBy(fn($p) => $p->kategori ?: 'Tanpa Kategori')
        ->sortKeys();

    return view('spk.input-permintaan', compact('produkByKategori', 'kriterias', 'inputs'));
}

    public function store(Request $request)
    {
        if (!$request->has('data')) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid'
            ]);
        }

        $data = json_decode($request->data, true);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Format data salah'
            ]);
        }

        foreach ($data as $id_produk => $kriteriaValues) {

            $produk = Produk::find($id_produk);

            if (!$produk) {
                continue;
            }

            foreach ($kriteriaValues as $id_kriteria => $nilai) {

                // Simpan ke tabel input_permintaan
                InputPermintaan::updateOrCreate(
                    [
                        'id_produk'   => $id_produk,
                        'id_kriteria' => $id_kriteria,
                    ],
                    [
                        'nilai_input' => $nilai
                    ]
                );

                // Simpan ke tabel nilai_produk
                NilaiProduk::updateOrCreate(
                    [
                        'id_produk'   => $id_produk,
                        'id_kriteria' => $id_kriteria,
                    ],
                    [
                        'nilai' => (float) $nilai
                    ]
                );
            }

            // Cek kelengkapan data
            $totalKriteria = Kriteria::count();

            $totalNilai = NilaiProduk::where('id_produk', $id_produk)
                ->count();

            $produk->update([
                'status_data' => (
                    $totalNilai >= $totalKriteria &&
                    $totalKriteria > 0
                )
                    ? 'Lengkap'
                    : 'Belum Lengkap'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil disimpan'
        ]);
    }
}