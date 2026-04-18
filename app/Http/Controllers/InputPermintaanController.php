<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kriteria;
use App\Models\InputPermintaan;
use Illuminate\Http\Request;

class InputPermintaanController extends Controller
{
    public function index()
    {
        $produks = Produk::all();
        $kriterias = Kriteria::where('sumber_data', 'Manual')->get();
        
        // Ambil data input yang sudah ada
        $inputs = InputPermintaan::all()->groupBy('id_produk');
        
        return view('spk.input-permintaan', compact('produks', 'kriterias', 'inputs'));
    }

    public function store(Request $request)
    {
        // Handle bulk input from table
        if ($request->has('data')) {
            $data = json_decode($request->data, true);
            
            foreach ($data as $id_produk => $kriterias) {
                foreach ($kriterias as $id_kriteria => $nilai) {
                    InputPermintaan::updateOrCreate(
                        [
                            'id_produk' => $id_produk,
                            'id_kriteria' => $id_kriteria,
                        ],
                        [
                            'nilai_input' => $nilai,
                        ]
                    );
                }
                
                // Update status produk
                $jumlahKriteria = Kriteria::where('sumber_data', 'Manual')->count();
                $produk = Produk::find($id_produk);
                $terisi = $produk->inputPermintaan()->count();
                
                $produk->update([
                    'status_data' => ($terisi >= $jumlahKriteria) ? 'Lengkap' : 'Belum Lengkap'
                ]);
            }
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'Data tidak valid']);
    }
}