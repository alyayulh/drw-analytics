<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\ProsesAnalisis;
use App\Models\AturanAsosiasi;
use Illuminate\Support\Facades\Storage;

class AsosiasiController extends Controller
{
    public function dashboard()
{
    $summary = session('hasil_analisis', [
        'total_data_awal' => 1285,
        'setelah_preprocessing' => 1220,
        'total_basket' => 1220,
        'produk_unik' => 156,
        'total_operator' => 8,
        'frequent_itemsets' => 456,
        'association_rules' => 342,
        'rule_terbaik' => 'Lift: 2.4',
    ]);

    $dataset = [
        'nama_file' => 'data_penjualan_april_2026.xlsx',
        'periode_data' => '1 April - 30 April 2026',
        'jumlah_data_awal' => '1,285 transaksi',
        'data_setelah_preprocessing' => '1,220 transaksi',
        'transaksi_refund_dihapus' => '65 transaksi',
        'basket_transaksi_terbentuk' => '1,220 basket',
    ];

    $topProduk = collect([
        ['nama' => 'Serum Wajah A', 'jumlah' => 250],
        ['nama' => 'Moisturizer B', 'jumlah' => 195],
        ['nama' => 'Toner C', 'jumlah' => 165],
        ['nama' => 'Sunscreen D', 'jumlah' => 140],
        ['nama' => 'Cleanser E', 'jumlah' => 125],
        ['nama' => 'Face Mask F', 'jumlah' => 108],
        ['nama' => 'Eye Cream G', 'jumlah' => 94],
        ['nama' => 'Essence H', 'jumlah' => 82],
        ['nama' => 'Lip Balm I', 'jumlah' => 73],
        ['nama' => 'Body Lotion J', 'jumlah' => 63],
    ]);

    $distribusiWaktu = collect([
        ['label' => 'Pagi', 'nilai' => 23],
        ['label' => 'Siang', 'nilai' => 34],
        ['label' => 'Sore', 'nilai' => 29],
        ['label' => 'Malam', 'nilai' => 14],
    ]);

    $rules = collect([
        [
            'antecedents' => 'Serum Wajah A',
            'consequents' => 'Moisturizer B',
            'support' => 0.32,
            'confidence' => 0.85,
            'lift' => 2.4,
        ],
        [
            'antecedents' => 'Toner C',
            'consequents' => 'Serum Wajah A',
            'support' => 0.28,
            'confidence' => 0.78,
            'lift' => 2.1,
        ],
        [
            'antecedents' => 'Sunscreen D, Operator Siti',
            'consequents' => 'Moisturizer B',
            'support' => 0.25,
            'confidence' => 0.82,
            'lift' => 1.9,
        ],
        [
            'antecedents' => 'Cleanser E',
            'consequents' => 'Face Mask F',
            'support' => 0.22,
            'confidence' => 0.76,
            'lift' => 1.8,
        ],
        [
            'antecedents' => 'Waktu Siang',
            'consequents' => 'Serum Wajah A',
            'support' => 0.19,
            'confidence' => 0.71,
            'lift' => 1.7,
        ],
    ]);

    return view('asosiasi.dashboard', compact(
        'summary',
        'dataset',
        'topProduk',
        'distribusiWaktu',
        'rules'
    ));
}

public function downloadLaporan()
{
    $filename = 'laporan_analisis_asosiasi.xls';

    $summary = [
        'Total Transaksi' => 1220,
        'Total Produk' => 156,
        'Total Operator' => 8,
        'Total Rules Asosiasi' => 342,
        'Rule Terbaik' => 'Lift: 2.4',
    ];

    $rules = [
        ['Serum Wajah A', 'Moisturizer B', 0.32, 0.85, 2.4],
        ['Toner C', 'Serum Wajah A', 0.28, 0.78, 2.1],
        ['Sunscreen D, Operator Siti', 'Moisturizer B', 0.25, 0.82, 1.9],
        ['Cleanser E', 'Face Mask F', 0.22, 0.76, 1.8],
        ['Waktu Siang', 'Serum Wajah A', 0.19, 0.71, 1.7],
    ];

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <table border="1">
            <tr>
                <th colspan="2">Ringkasan Hasil Analisis Asosiasi</th>
            </tr>';

    foreach ($summary as $label => $value) {
        $html .= '
            <tr>
                <td>' . $label . '</td>
                <td>' . $value . '</td>
            </tr>';
    }

    $html .= '
        </table>

        <br>

        <table border="1">
            <tr>
                <th>Antecedents</th>
                <th>Consequents</th>
                <th>Support</th>
                <th>Confidence</th>
                <th>Lift</th>
            </tr>';

    foreach ($rules as $rule) {
        $html .= '
            <tr>
                <td>' . $rule[0] . '</td>
                <td>' . $rule[1] . '</td>
                <td>' . $rule[2] . '</td>
                <td>' . $rule[3] . '</td>
                <td>' . $rule[4] . '</td>
            </tr>';
    }

    $html .= '
        </table>
    </body>
    </html>';

    return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
}

    public function analisis()
    {
        // Halaman ini nanti diisi teman kamu
        return view('asosiasi.analisis');
    }

    public function riwayat()
    {
        // Halaman ini nanti diisi teman kamu
        return view('asosiasi.riwayat');
    }
    public function prosesAnalisis(Request $request)
{
    $request->validate([
        'file_excel' => 'required|mimes:xlsx,xls,csv|max:10240',
    ]);

    $file = $request->file('file_excel');

    $fileName = time() . '_' . $file->getClientOriginalName();
    $filePath = $file->storeAs('uploads/asosiasi', $fileName, 'public');

    session([
        'hasil_analisis' => [
            'total_data_awal' => 1285,
            'setelah_preprocessing' => 1220,
            'total_basket' => 1220,
            'produk_unik' => 156,
            'total_operator' => 8,
            'frequent_itemsets' => 456,
            'association_rules' => 342,
            'rule_terbaik' => 'Lift 4.2',
        ]
    ]);

    return redirect()
        ->route('asosiasi.hasil')
        ->with('success', 'Proses analisis berhasil dilakukan.');
}

public function hasilAnalisis()
{
    $summary = session('hasil_analisis', [
        'total_data_awal' => 1285,
        'setelah_preprocessing' => 1220,
        'total_basket' => 1220,
        'produk_unik' => 156,
        'total_operator' => 8,
        'frequent_itemsets' => 456,
        'association_rules' => 342,
        'rule_terbaik' => 'Lift 4.2',
    ]);

    $rules = collect([
        [
            'no' => 1,
            'antecedents' => 'Serum Wajah A',
            'consequents' => 'Moisturizer B',
            'support' => 0.32,
            'confidence' => 0.85,
            'lift' => 2.4,
            'operator' => 'Siti',
            'kategori_waktu' => 'Siang',
            'status' => 'Normal',
            'interpretasi' => 'Pelanggan yang membeli Serum A cenderung membeli Moisturizer B',
        ],
        [
            'no' => 2,
            'antecedents' => 'Toner C, Operator Ani',
            'consequents' => 'Serum Wajah A',
            'support' => 0.05,
            'confidence' => 0.92,
            'lift' => 4.2,
            'operator' => 'Ani',
            'kategori_waktu' => 'Pagi',
            'status' => 'Anomali',
            'interpretasi' => 'Pola tidak biasa: confidence tinggi dengan support sangat rendah',
        ],
        [
            'no' => 3,
            'antecedents' => 'Sunscreen D',
            'consequents' => 'Moisturizer B',
            'support' => 0.25,
            'confidence' => 0.82,
            'lift' => 1.9,
            'operator' => 'Siti',
            'kategori_waktu' => 'Sore',
            'status' => 'Normal',
            'interpretasi' => 'Pola pembelian umum pada waktu sore',
        ],
        [
            'no' => 4,
            'antecedents' => 'Cleanser E, Waktu Malam',
            'consequents' => 'Face Mask F',
            'support' => 0.12,
            'confidence' => 0.88,
            'lift' => 3.8,
            'operator' => '-',
            'kategori_waktu' => 'Malam',
            'status' => 'Anomali',
            'interpretasi' => 'Lift tinggi pada kategori waktu malam',
        ],
        [
            'no' => 5,
            'antecedents' => 'Essence H',
            'consequents' => 'Eye Cream G',
            'support' => 0.28,
            'confidence' => 0.76,
            'lift' => 1.8,
            'operator' => 'Dewi',
            'kategori_waktu' => 'Siang',
            'status' => 'Normal',
            'interpretasi' => 'Pelanggan yang membeli Essence H sering membeli Eye Cream G',
        ],
    ]);

    return view('asosiasi.hasil', compact('summary', 'rules'));
}
}   