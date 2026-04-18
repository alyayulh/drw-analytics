<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\NilaiProduk;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProdukController extends Controller
{
    // Tampilkan halaman data produk
    public function index()
    {
        $produks = Produk::all();
        return view('spk.data-produk', compact('produks'));
    }

    // Tambah produk manual
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
        ]);

        Produk::create([
            'nama_produk' => $request->nama_produk,
            'status_data' => 'Belum Lengkap',
        ]);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    // Import dari Excel
    public function import(Request $request)
{
    $request->validate([
        'file_excel' => 'required|file|mimes:xlsx,xls',
    ]);

    $file = $request->file('file_excel');
    $path = $file->getRealPath();

    $reader = IOFactory::createReaderForFile($path);
    $spreadsheet = $reader->load($path);
    $sheet = $spreadsheet->getActiveSheet()->toArray();

    // Header ada di baris 4 (index 3), bukan baris 1
    $headers = array_map('strtoupper', array_map('trim', $sheet[3]));

    $colNama = array_search('NAMA BARANG', $headers);
    $colStok = array_search('STOCK AKHIR', $headers);
    $colPenjualan = array_search('TOTAL PENJUALAN', $headers);
    $colHarga = array_search('HARGA JUAL', $headers);

    if ($colNama === false) {
        return back()->with('error', 'Kolom NAMA BARANG tidak ditemukan di file Excel.');
    }

    $imported = 0;
    // Data mulai dari baris 5 (index 4)
    for ($i = 4; $i < count($sheet); $i++) {
        $row = $sheet[$i];
        $nama = trim($row[$colNama] ?? '');
        if (!$nama) continue;

        $produk = Produk::where('nama_produk', $nama)->first();
        if (!$produk) {
            Produk::create([
                'nama_produk' => $nama,
                'status_data' => 'Belum Lengkap',
            ]);
            $imported++;
        }
    }

    return back()->with('success', "$imported produk berhasil diimport dari Excel.");
}
    // Edit produk
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
}