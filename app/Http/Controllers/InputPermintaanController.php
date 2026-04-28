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
        $produks   = Produk::all();
        $kriterias = Kriteria::where('sumber_data', 'Manual')->get();

        // Ambil input yang sudah tersimpan, dikelompokkan per produk
        $inputs = InputPermintaan::all()->groupBy('id_produk');

        return view('spk.input-permintaan', compact('produks', 'kriterias', 'inputs'));
    }

    public function store(Request $request)
    {
        if (!$request->has('data')) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid']);
        }

        $data = json_decode($request->data, true);

        foreach ($data as $id_produk => $kriteriaValues) {
            $produk = Produk::find($id_produk);
            if (!$produk) continue;

            foreach ($kriteriaValues as $id_kriteria => $nilai) {
                // Simpan ke input_permintaan (catatan historis)
                InputPermintaan::updateOrCreate(
                    [
                        'id_produk'   => $id_produk,
                        'id_kriteria' => $id_kriteria,
                    ],
                    ['nilai_input' => $nilai]
                );

                // Simpan juga ke nilai_produk (untuk kalkulasi SPK)
                NilaiProduk::updateOrCreate(
                    [
                        'id_produk'   => $id_produk,
                        'id_kriteria' => $id_kriteria,
                    ],
                    ['nilai' => (float) $nilai]
                );
            }

            // Update status: cek semua kriteria (Excel + Manual)
            $totalKriteria = Kriteria::count();
            $totalNilai    = NilaiProduk::where('id_produk', $id_produk)->count();

            $produk->update([
                'status_data' => ($totalNilai >= $totalKriteria && $totalKriteria > 0)
                    ? 'Lengkap'
                    : 'Belum Lengkap',
            ]);
        }

        return response()->json(['success' => true]);
    }
}