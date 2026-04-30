<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    public function index()
    {
        $kriterias  = Kriteria::all();
        $totalBobot = $kriterias->sum('bobot');
        return view('spk.kelola-kriteria', compact('kriterias', 'totalBobot'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kriteria'    => 'required|string|max:255',
            'tipe_atribut'     => 'required|in:Benefit,Cost',
            'bobot'            => 'required|numeric|min:0|max:100',
            'sumber_data'      => 'required|in:Excel,Manual',
            'nama_kolom_excel' => 'nullable|string|max:100',
        ]);

        $totalBobot = Kriteria::sum('bobot') + $request->bobot;
        if ($totalBobot > 100) {
            return back()->with('error',
                'Total bobot kriteria melebihi 100%. Sisa bobot: ' . (100 - Kriteria::sum('bobot')) . '%'
            );
        }

        Kriteria::create([
            'nama_kriteria'    => $request->nama_kriteria,
            'tipe_atribut'     => $request->tipe_atribut,   // Benefit / Cost (kapital, sesuai DB enum)
            'bobot'            => $request->bobot,
            'sumber_data'      => $request->sumber_data,
            'nama_kolom_excel' => $request->sumber_data === 'Excel'
                ? strtoupper(trim($request->nama_kolom_excel))
                : null,
        ]);

        return back()->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kriteria = Kriteria::findOrFail($id);

        $request->validate([
            'nama_kriteria'    => 'required|string|max:255',
            'tipe_atribut'     => 'required|in:Benefit,Cost',
            'bobot'            => 'required|numeric|min:0|max:100',
            'sumber_data'      => 'required|in:Excel,Manual',
            'nama_kolom_excel' => 'nullable|string|max:100',
        ]);

        $totalBobot = Kriteria::where('id_kriteria', '!=', $id)->sum('bobot') + $request->bobot;
        if ($totalBobot > 100) {
            return back()->with('error', 'Total bobot kriteria melebihi 100%.');
        }

        $kriteria->update([
            'nama_kriteria'    => $request->nama_kriteria,
            'tipe_atribut'     => $request->tipe_atribut,
            'bobot'            => $request->bobot,
            'sumber_data'      => $request->sumber_data,
            'nama_kolom_excel' => $request->sumber_data === 'Excel'
                ? strtoupper(trim($request->nama_kolom_excel))
                : null,
        ]);

        return back()->with('success', 'Kriteria berhasil diupdate.');
    }

    public function destroy($id)
    {
        $kriteria = Kriteria::findOrFail($id);
        $kriteria->delete();
        return back()->with('success', 'Kriteria berhasil dihapus.');
    }
}
