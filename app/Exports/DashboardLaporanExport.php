<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardLaporanExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $summary = $this->data['summary'] ?? [];
        $dataset = $this->data['dataset'] ?? [];

        $topProduk = collect($this->data['topProduk'] ?? [])
            ->map(function ($item) {
                return [
                    'nama' => data_get($item, 'nama', '-'),
                    'jumlah' => (int) data_get($item, 'jumlah', 0),
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();

        $distribusiWaktu = collect($this->data['distribusiWaktu'] ?? [])
            ->values();

        $rules = collect($this->data['rules'] ?? [])
            ->values();

        $totalRulesTerbentuk = (int) ($summary['association_rules'] ?? $rules->count());

        $jumlahRulesDashboard = $totalRulesTerbentuk > 0
            ? max(1, (int) ceil($totalRulesTerbentuk * 0.10))
            : 0;

        $topRulesDashboard = $rules
            ->sortByDesc(function ($rule) {
                return (float) data_get($rule, 'lift', 0);
            })
            ->take($jumlahRulesDashboard)
            ->values();

        $totalTransaksi = (int) ($summary['total_basket'] ?? 0);

        $rangeWaktu = [
            'pagi' => '00.00 - 11.59',
            'siang' => '12.00 - 17.59',
            'malam' => '18.00 - 23.59',
        ];

        $waktuDominan = $distribusiWaktu
            ->sortByDesc(function ($item) {
                return (float) data_get($item, 'nilai', 0);
            })
            ->first();

        $labelWaktuDominan = data_get($waktuDominan, 'label', '-');
        $persentaseWaktuDominan = (float) data_get($waktuDominan, 'nilai', 0);

        $jumlahTransaksiWaktuDominan = $totalTransaksi > 0
            ? (int) round(($persentaseWaktuDominan / 100) * $totalTransaksi)
            : 0;

        $ruleUtama = $topRulesDashboard->first();

        $antecedentUtama = $this->bersihkanTeksPola(data_get($ruleUtama, 'antecedents', '-'));
        $consequentUtama = $this->bersihkanTeksPola(data_get($ruleUtama, 'consequents', '-'));

        $rows = [];

        $rows[] = ['LAPORAN DASHBOARD ANALISIS TRANSAKSI PENJUALAN'];
        $rows[] = ['Tanggal Unduh', now()->translatedFormat('d F Y')];
        $rows[] = [];

        $rows[] = ['RINGKASAN UTAMA'];
        $rows[] = ['Total Transaksi', (int) ($summary['total_basket'] ?? 0)];
        $rows[] = ['Total Produk', (int) ($summary['produk_unik'] ?? 0)];
        $rows[] = ['Total Operator', (int) ($summary['total_operator'] ?? 0)];
        $rows[] = ['Total Rules Asosiasi', (int) ($summary['association_rules'] ?? 0)];
        $rows[] = ['Rule Terbaik', $summary['rule_terbaik'] ?? 'Belum ada rule'];
        $rows[] = [];

        $rows[] = ['RINGKASAN DATASET TERAKHIR'];
        $rows[] = ['Nama File', $dataset['nama_file'] ?? '-'];
        $rows[] = ['Tanggal Analisis', $dataset['tanggal_analisis'] ?? '-'];
        $rows[] = ['Jumlah Data Awal', (int) ($dataset['jumlah_data_awal'] ?? 0)];
        $rows[] = ['Data Setelah Dibersihkan', (int) ($dataset['data_setelah_preprocessing'] ?? 0)];
        $rows[] = ['Transaksi Refund Dihapus', (int) ($dataset['transaksi_refund_dihapus'] ?? 0)];
        $rows[] = ['Transaksi yang Diproses', (int) ($dataset['basket_transaksi_terbentuk'] ?? 0)];
        $rows[] = [];

        $rows[] = ['TOP 10 PRODUK TERLARIS'];
        $rows[] = ['No', 'Nama Produk', 'Jumlah Transaksi'];

        if ($topProduk->isNotEmpty()) {
            foreach ($topProduk as $index => $produk) {
                $rows[] = [
                    $index + 1,
                    $produk['nama'],
                    $produk['jumlah'],
                ];
            }
        } else {
            $rows[] = ['-', 'Belum ada data produk', '-'];
        }

        $rows[] = [];

        $rows[] = ['DISTRIBUSI TRANSAKSI BERDASARKAN WAKTU'];
        $rows[] = ['Waktu', 'Rentang Jam', 'Persentase', 'Estimasi Jumlah Transaksi'];

        if ($distribusiWaktu->isNotEmpty()) {
            foreach ($distribusiWaktu as $waktu) {
                $label = data_get($waktu, 'label', '-');
                $nilai = (float) data_get($waktu, 'nilai', 0);
                $jumlahTransaksi = $totalTransaksi > 0
                    ? (int) round(($nilai / 100) * $totalTransaksi)
                    : 0;

                $rows[] = [
                    $label,
                    $rangeWaktu[strtolower($label)] ?? '-',
                    number_format($nilai, 2, ',', '.') . '%',
                    $jumlahTransaksi,
                ];
            }
        } else {
            $rows[] = ['-', '-', '-', '-'];
        }

        $rows[] = [];

        $rows[] = ['TOP ASSOCIATION RULES'];
        $rows[] = [
            'No',
            'Antecedents',
            'Consequents',
            'Support',
            'Confidence',
            'Kekuatan Pola',
        ];

        if ($topRulesDashboard->isNotEmpty()) {
            foreach ($topRulesDashboard as $index => $rule) {
                $rows[] = [
                    $index + 1,
                    data_get($rule, 'antecedents', '-'),
                    data_get($rule, 'consequents', '-'),
                    number_format((float) data_get($rule, 'support', 0), 4),
                    number_format((float) data_get($rule, 'confidence', 0), 4),
                    number_format((float) data_get($rule, 'lift', 0), 4),
                ];
            }
        } else {
            $rows[] = ['-', 'Belum ada association rules', '-', '-', '-', '-'];
        }

        $rows[] = [];

        $rows[] = ['INSIGHT OPERASIONAL'];

        if ($ruleUtama) {
            $confidenceUtama = (float) data_get($ruleUtama, 'confidence', 0);
            $confidenceUtamaPersen = number_format($confidenceUtama * 100, 2, ',', '.') . '%';

            $rows[] = [
                'Pola Pembelian Terkuat',
                'Data menunjukkan bahwa transaksi dengan ' . $antecedentUtama .
                ' paling sering berkaitan dengan ' . $consequentUtama .
                '. Pola ini memiliki tingkat kecenderungan sebesar ' . $confidenceUtamaPersen . '.',
            ];
        } else {
            $rows[] = [
                'Pola Pembelian Terkuat',
                'Belum ada pola pembelian yang dapat ditampilkan.',
            ];
        }

        $produkTerlaris = $topProduk->first();

        $rows[] = [
            'Produk Terlaris',
            $produkTerlaris
                ? 'Produk paling sering dibeli adalah ' . $produkTerlaris['nama'] . '.'
                : 'Belum ada data produk terlaris.',
        ];

        if ($waktuDominan) {
            $rangeWaktuDominan = $rangeWaktu[strtolower($labelWaktuDominan)] ?? null;

            $rows[] = [
                'Waktu Transaksi Optimal',
                'Transaksi terbanyak terjadi pada waktu ' . $labelWaktuDominan .
                ($rangeWaktuDominan ? ' pukul ' . $rangeWaktuDominan : '') .
                ' dengan ' . number_format($jumlahTransaksiWaktuDominan, 0, ',', '.') . ' transaksi.',
            ];
        }

        $rows[] = [
            'Top Rules Dashboard',
            'Dashboard menampilkan ' . $topRulesDashboard->count() .
            ' pola asosiasi teratas berdasarkan tingkat kekuatan hubungan dari total ' .
            number_format($totalRulesTerbentuk, 0, ',', '.') . ' pola yang terbentuk.',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        for ($row = 1; $row <= $highestRow; $row++) {
            $value = $sheet->getCell('A' . $row)->getValue();

            if (in_array($value, [
                'LAPORAN DASHBOARD ANALISIS TRANSAKSI PENJUALAN',
                'RINGKASAN UTAMA',
                'RINGKASAN DATASET TERAKHIR',
                'TOP 10 PRODUK TERLARIS',
                'DISTRIBUSI TRANSAKSI BERDASARKAN WAKTU',
                'TOP ASSOCIATION RULES',
                'INSIGHT OPERASIONAL',
            ], true)) {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFFCE7F3');
            }

            if (in_array($value, [
                'No',
                'Waktu',
            ], true)) {
                $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
            }
        }

        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A:F')->getAlignment()->setVertical('top');
        $sheet->getStyle('A:F')->getAlignment()->setWrapText(true);

        return [];
    }

    private function bersihkanTeksPola($teks): string
    {
        $teks = trim((string) $teks);

        if ($teks === '' || $teks === '-') {
            return 'belum tersedia';
        }

        $teks = str_replace(['Waktu:', 'waktu:', 'Waktu :', 'waktu :'], 'waktu ', $teks);
        $teks = str_replace(['Operator:', 'operator:', 'Operator :', 'operator :'], 'operator ', $teks);
        $teks = preg_replace('/\s+/', ' ', $teks);

        return trim($teks);
    }
}