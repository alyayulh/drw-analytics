<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\Produk;
use App\Models\NilaiProduk;
use App\Models\InputPermintaan;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    public function index() 
    {
        $kriterias = Kriteria::orderByDesc('bobot')->get();
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

        #aturan bobot antar kriteria tidak boleh sama
        if (Kriteria::where('bobot', $request->bobot)->exists()) {
            return back()->with('error',
                "Bobot {$request->bobot}% sudah dipakai oleh kriteria lain. Setiap kriteria harus punya bobot yang berbeda."
            )->withInput();
        }

        Kriteria::create([
            'nama_kriteria'    => $request->nama_kriteria,
            'tipe_atribut'     => $request->tipe_atribut,   
            'bobot'            => $request->bobot,
            'sumber_data'      => $request->sumber_data,
            'nama_kolom_excel' => $request->sumber_data === 'Excel'
                ? strtoupper(trim($request->nama_kolom_excel))
                : null,
        ]);

        #Setelah tambah kriteria baru, semua produk harus di-recalculate.
        #Kriteria baru belum ada nilainya di nilai_produk, jadi semua produk otomatis
        #akan menjadi 'Belum Lengkap' (kecuali sudah punya nilai untuk kriteria ini).
        $this->recalculateSemuaProduk();

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

        #Bobot antar kriteria tidak boleh sama, kecuali dengan dirinya sendiri (kriteria yang sedang diedit).
        if (Kriteria::where('id_kriteria', '!=', $id)->where('bobot', $request->bobot)->exists()) {
            return back()->with('error',
                "Bobot {$request->bobot}% sudah dipakai oleh kriteria lain. Setiap kriteria harus punya bobot yang berbeda."
            )->withInput();
        }

        // Simpan sumber_data LAMA sebelum update untuk deteksi perubahan
        $sumberDataLama = $kriteria->sumber_data;
        $sumberDataBaru = $request->sumber_data;

        $kriteria->update([
            'nama_kriteria'    => $request->nama_kriteria,
            'tipe_atribut'     => $request->tipe_atribut,
            'bobot'            => $request->bobot,
            'sumber_data'      => $sumberDataBaru,
            'nama_kolom_excel' => $sumberDataBaru === 'Excel'
                ? strtoupper(trim($request->nama_kolom_excel))
                : null,
        ]);

        #mengecek apakah sumber_data kriteria diubah, jika ya maka hapus data lama terkait kritreia tersebut karena sudah tidak relevan.
        if ($sumberDataLama !== $sumberDataBaru) {
            InputPermintaan::where('id_kriteria', $id)->delete();
            NilaiProduk::where('id_kriteria', $id)->delete();
        }

        $this->recalculateSemuaProduk();

        return back()->with('success', 'Kriteria berhasil diupdate.');
    }

    public function destroy($id)
    {
        $kriteria = Kriteria::findOrFail($id);
        NilaiProduk::where('id_kriteria', $id)->delete();
        InputPermintaan::where('id_kriteria', $id)->delete();

        $kriteria->delete();

        $this->recalculateSemuaProduk();

        return back()->with('success', 'Kriteria berhasil dihapus.');
    }

    #Recalculate status_data semua produk berdasarkan kriteria yang aktif saat ini. dipanggil setiap kali ada perubahan.
    private function recalculateSemuaProduk(): void
    {
        $totalKriteria = Kriteria::count();

        $semuaProduk = Produk::all();

        foreach ($semuaProduk as $produk) {
            $totalNilai = NilaiProduk::where('id_produk', $produk->id_produk)->count();

            $produk->update([
                'status_data' => ($totalKriteria > 0 && $totalNilai >= $totalKriteria)
                    ? 'Lengkap'
                    : 'Belum Lengkap',
            ]);
        }
    }
}