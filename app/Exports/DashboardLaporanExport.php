<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DashboardLaporanExport implements FromArray, ShouldAutoSize, WithStyles
{
    protected array $data;

    protected array $sectionRows = [];

    protected array $tableHeaderRows = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $this->sectionRows = [];
        $this->tableHeaderRows = [];
        $summary = $this->data['summary'] ?? [];
        $dataset = $this->data['dataset'] ?? [];
        $rules = collect($this->data['rules'] ?? [])->values();

        $topProduk = $this->getCollectionFromData(['topProduk', 'top_produk'])
            ->map(function ($item) {
                $nama = $this->firstValue($item, [
                    'nama',
                    'nama_produk',
                    'produk',
                    'item',
                    'product',
                ], '-');

                $jumlah = $this->toInt($this->firstValue($item, [
                    'jumlah',
                    'jumlah_terjual',
                    'total',
                    'count',
                    'frekuensi',
                ], 0));

                return [
                    'nama' => $nama ?: '-',
                    'jumlah' => $jumlah,
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();

        $totalDataAwal = $this->toInt(
            $this->firstValue($summary, [
                'total_data_awal',
                'jumlah_data_awal',
                'data_awal',
            ], $this->firstValue($dataset, [
                'total_data_awal',
                'jumlah_data_awal',
                'data_awal',
            ], 0))
        );

        $dataSetelahDibersihkan = $this->toInt(
            $this->firstValue($summary, [
                'setelah_preprocessing',
                'total_data_bersih',
                'data_setelah_dibersihkan',
                'data_setelah_preprocessing',
            ], $this->firstValue($dataset, [
                'setelah_preprocessing',
                'total_data_bersih',
                'data_setelah_dibersihkan',
                'data_setelah_preprocessing',
            ], 0))
        );

        $totalTransaksi = $this->toInt(
            $this->firstValue($summary, [
                'total_basket',
                'total_transaksi',
                'transaksi_diproses',
                'transaksi_yang_diproses',
                'basket_transaksi_terbentuk',
            ], $this->firstValue($dataset, [
                'total_basket',
                'total_transaksi',
                'transaksi_diproses',
                'transaksi_yang_diproses',
                'basket_transaksi_terbentuk',
            ], 0))
        );

        $totalProduk = $this->toInt(
            $this->firstValue($summary, [
                'produk_unik',
                'total_produk',
                'total_produk_unik',
            ], $this->firstValue($dataset, [
                'produk_unik',
                'total_produk',
                'total_produk_unik',
            ], 0))
        );

        $totalOperator = $this->toInt(
            $this->firstValue($summary, [
                'total_operator',
                'operator_unik',
                'jumlah_operator_unik',
            ], $this->firstValue($dataset, [
                'total_operator',
                'operator_unik',
                'jumlah_operator_unik',
            ], 0))
        );

        if ($totalOperator <= 0) {
            $totalOperator = $this->countOperatorFromRules($rules);
        }

        $totalRules = $this->toInt(
            $this->firstValue($summary, [
                'association_rules',
                'total_rules',
                'total_rules_asosiasi',
                'jumlah_rules',
            ], $rules->count())
        );

        $namaFile = $this->firstValue($dataset, [
            'nama_file',
            'file_name',
            'filename',
        ], '-');

        $tanggalAnalisis = $this->formatTanggalIndonesia(
            $this->firstValue($dataset, [
                'tanggal_analisis',
                'tanggal_proses',
                'created_at',
            ], '-')
        );

        $ruleTerbaik = $this->firstValue($summary, [
            'rule_terbaik',
            'best_rule',
            'pola_terbaik',
        ], null);

        if (!$ruleTerbaik) {
            $ruleTerbaik = $rules
                ->sortByDesc(function ($rule) {
                    return (float) data_get($rule, 'lift', 0);
                })
                ->map(function ($rule) {
                    $antecedents = $this->bersihkanTeksPola(data_get($rule, 'antecedents', '-'));
                    $consequents = $this->bersihkanTeksPola(data_get($rule, 'consequents', '-'));

                    return $antecedents . ' → ' . $consequents;
                })
                ->first() ?? 'Belum ada rule';
        }

        $distribusiWaktu = $this->buildDistribusiWaktuDashboard($totalTransaksi);

        $rangeWaktu = [
            'pagi' => '00.00 - 12.59',
            'siang' => '13.00 - 22.00',
        ];

        $waktuDominan = $distribusiWaktu
            ->sortByDesc(function ($item) {
                return (float) data_get($item, 'nilai', 0);
            })
            ->first();

        $labelWaktuDominan = data_get($waktuDominan, 'label', '-');
        $nilaiWaktuDominan = (float) data_get($waktuDominan, 'nilai', 0);
        $jumlahTransaksiWaktuDominan = (int) data_get($waktuDominan, 'jumlah', 0);

        if ($jumlahTransaksiWaktuDominan <= 0) {
            $jumlahTransaksiWaktuDominan = $this->hitungJumlahTransaksiWaktu($nilaiWaktuDominan, $totalTransaksi);
        }

        $topRulesDashboard = $this->getTopRulesDashboard($rules, $totalRules);
        $ruleUtama = $topRulesDashboard->first();

        $antecedentUtama = $this->bersihkanTeksPola(data_get($ruleUtama, 'antecedents', '-'));
        $consequentUtama = $this->bersihkanTeksPola(data_get($ruleUtama, 'consequents', '-'));
        $confidenceUtama = (float) data_get($ruleUtama, 'confidence', 0);

        $produkTerlaris = data_get($topProduk->first(), 'nama', '-');

        $rows = [];

        $addRow = function (array $row) use (&$rows) {
            $rows[] = $row;
            return count($rows);
        };

        $addSection = function (string $title) use ($addRow) {
            $rowNumber = $addRow([$title]);
            $this->sectionRows[] = $rowNumber;
        };

        $addTableHeader = function (array $row) use ($addRow) {
            $rowNumber = $addRow($row);
            $this->tableHeaderRows[] = $rowNumber;
        };

        $addRow(['LAPORAN DASHBOARD ANALISIS TRANSAKSI PENJUALAN']);
        $addRow(['Tanggal Unduh', now()->translatedFormat('d F Y')]);
        $addRow([]);

        $addSection('RINGKASAN UTAMA');
        $addRow(['Total Transaksi', $totalTransaksi]);
        $addRow(['Total Produk', $totalProduk]);
        $addRow(['Total Operator', $totalOperator]);
        $addRow(['Total Rules Asosiasi', $totalRules]);
        $addRow(['Rule Terbaik', $ruleTerbaik]);
        $addRow([]);

        $addSection('RINGKASAN DATASET TERAKHIR');
        $addRow(['Nama File', $namaFile]);
        $addRow(['Tanggal Analisis', $tanggalAnalisis]);
        $addRow(['Jumlah Data Awal', $totalDataAwal]);
        $addRow(['Data Setelah Dibersihkan', $dataSetelahDibersihkan]);
        $addRow(['Transaksi yang Diproses', $totalTransaksi]);
        $addRow([]);

        $addSection('TOP 10 PRODUK TERLARIS');
        $addTableHeader(['No', 'Nama Produk', 'Jumlah Transaksi']);

        if ($topProduk->isNotEmpty()) {
            foreach ($topProduk as $index => $produk) {
                $addRow([
                    $index + 1,
                    data_get($produk, 'nama', '-'),
                    data_get($produk, 'jumlah', 0),
                ]);
            }
        } else {
            $addRow(['-', 'Belum ada data produk', '-']);
        }

        $addRow([]);

        $addSection('DISTRIBUSI TRANSAKSI BERDASARKAN WAKTU');
        $addTableHeader(['Waktu', 'Rentang Jam', 'Persentase', 'Estimasi Jumlah Transaksi']);

        if ($distribusiWaktu->isNotEmpty()) {
            foreach ($distribusiWaktu as $waktu) {
                $label = data_get($waktu, 'label', '-');
                $nilai = (float) data_get($waktu, 'nilai', 0);
                $jumlahTransaksi = (int) data_get($waktu, 'jumlah', 0);

                if ($jumlahTransaksi <= 0) {
                    $jumlahTransaksi = $this->hitungJumlahTransaksiWaktu($nilai, $totalTransaksi);
                }

                $addRow([
                    $label,
                    $rangeWaktu[strtolower((string) $label)] ?? '-',
                    number_format($nilai, 2, ',', '.') . '%',
                    $jumlahTransaksi,
                ]);
            }
        } else {
            $addRow(['-', '-', '0,00%', 0]);
        }

        $addRow([]);

        $addSection('INSIGHT OPERASIONAL');

        if ($antecedentUtama !== '-' && $consequentUtama !== '-') {
            $addRow([
                'Pola Pembelian Terkuat',
                'Data menunjukkan bahwa transaksi dengan ' . $antecedentUtama .
                ' paling sering berkaitan dengan ' . $consequentUtama .
                '. Pola ini memiliki tingkat kepercayaan sebesar ' .
                number_format($confidenceUtama * 100, 2, ',', '.') . '%.',
            ]);
        } else {
            $addRow([
                'Pola Pembelian Terkuat',
                'Belum ada pola pembelian terkuat yang dapat ditampilkan.',
            ]);
        }

        $addRow([
            'Produk Terlaris',
            $produkTerlaris !== '-'
                ? 'Produk paling sering dibeli adalah ' . $produkTerlaris . '.'
                : 'Belum ada data produk terlaris yang dapat ditampilkan.',
        ]);

        $addRow([
            'Waktu Transaksi Optimal',
            $labelWaktuDominan !== '-'
                ? 'Transaksi terbanyak terjadi pada shift ' . $labelWaktuDominan .
                    ' pukul ' . ($rangeWaktu[strtolower((string) $labelWaktuDominan)] ?? '-') .
                    ' dengan ' . number_format($jumlahTransaksiWaktuDominan, 0, ',', '.') . ' transaksi.'
                : 'Belum ada data distribusi waktu transaksi yang dapat ditampilkan.',
        ]);

        $addRow([
            'Pola Teratas',
            'Dashboard menampilkan ' . $topRulesDashboard->count() .
            ' pola asosiasi teratas berdasarkan tingkat kekuatan hubungan dari total ' .
            number_format($totalRules, 0, ',', '.') . ' pola yang terbentuk.',
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $sheet->mergeCells('A1:G1');

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 15,
                'color' => ['rgb' => '06122D'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FCE7F3'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        foreach ($this->sectionRows as $row) {
            $sheet->mergeCells("A{$row}:G{$row}");

            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '06122D'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FCE7F3'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        foreach ($this->tableHeaderRows as $row) {
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '06122D'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF7FB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'F9A8D4'],
                    ],
                ],
            ]);
        }

        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_HAIR,
                    'color' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ]);

        $sheet->getStyle("A1:A{$highestRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle("B1:D{$highestRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle("E1:G{$highestRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(26);

        for ($row = 1; $row <= $highestRow; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(-1);
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(55);
        $sheet->getColumnDimension('D')->setWidth(42);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(22);

        return [];
    }

    private function getCollectionFromData(array $keys): Collection
    {
        foreach ($keys as $key) {
            $value = data_get($this->data, $key);

            if ($value instanceof Collection) {
                return $value;
            }

            if (is_array($value) && count($value) > 0) {
                return collect($value);
            }
        }

        return collect();
    }

    private function firstValue($source, array $keys, $default = null)
    {
        foreach ($keys as $key) {
            $value = data_get($source, $key);

            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function toInt($value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) round($value);
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        if (is_string($value)) {
            $value = trim($value);

            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $value)) {
                return (int) str_replace('.', '', $value);
            }

            $value = str_replace([' ', ','], ['', '.'], $value);

            return is_numeric($value) ? (int) round((float) $value) : 0;
        }

        return 0;
    }

    private function toFloat($value): float
    {
        if (is_float($value) || is_int($value)) {
            return (float) $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            $value = str_replace('%', '', $value);

            if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $value)) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);

                return is_numeric($value) ? (float) $value : 0;
            }

            $value = str_replace(',', '.', $value);

            return is_numeric($value) ? (float) $value : 0;
        }

        return 0;
    }

    private function formatTanggalIndonesia($value): string
    {
        if (!$value || $value === '-') {
            return '-';
        }

        $bulanIndonesia = [
            'January' => 'Januari',
            'February' => 'Februari',
            'March' => 'Maret',
            'April' => 'April',
            'May' => 'Mei',
            'June' => 'Juni',
            'July' => 'Juli',
            'August' => 'Agustus',
            'September' => 'September',
            'October' => 'Oktober',
            'November' => 'November',
            'December' => 'Desember',
        ];

        try {
            if ($value instanceof \Carbon\Carbon) {
                return strtr($value->format('d F Y'), $bulanIndonesia);
            }

            return strtr(\Carbon\Carbon::parse($value)->format('d F Y'), $bulanIndonesia);
        } catch (\Throwable $e) {
            return strtr((string) $value, $bulanIndonesia);
        }
    }

    private function bersihkanTeksPola($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        $value = (string) $value;

        $value = str_replace(['[', ']', '{', '}', '"', "'"], '', $value);
        $value = str_replace(['antecedents:', 'consequents:'], '', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value) ?: '-';
    }

    private function countOperatorFromRules(Collection $rules): int
    {
        $operators = collect();

        foreach ($rules as $rule) {
            $texts = [
                (string) data_get($rule, 'antecedents', ''),
                (string) data_get($rule, 'consequents', ''),
            ];

            foreach ($texts as $text) {
                if (preg_match_all('/Operator:\s*([^,]+)/i', $text, $matches)) {
                    foreach ($matches[1] as $operator) {
                        $operators->push(trim($operator));
                    }
                }
            }
        }

        return $operators->filter()->unique()->count();
    }

    private function getTopRulesDashboard(Collection $rules, int $totalRules): Collection
    {
        if ($totalRules > 0) {
            $jumlahRulesDashboard = max(1, (int) ceil($totalRules * 0.10));
        } else {
            $jumlahRulesDashboard = 0;
        }

        return $rules
            ->sortByDesc(function ($rule) {
                return (float) data_get($rule, 'lift', 0);
            })
            ->take($jumlahRulesDashboard)
            ->values();
    }

    private function buildDistribusiWaktuDashboard(int $totalTransaksi): Collection
    {
        $rawDistribusiWaktu = $this->getCollectionFromData(['distribusiWaktu', 'distribusi_waktu']);

        $waktuGroups = [
            'Pagi' => collect(),
            'Siang' => collect(),
        ];

        foreach ($rawDistribusiWaktu as $item) {
            $label = $this->normalizeWaktuLabel(
                $this->firstValue($item, [
                    'label',
                    'kategori_waktu',
                    'waktu',
                    'nama',
                    'name',
                ], '')
            );

            if (!in_array($label, ['Pagi', 'Siang'], true)) {
                continue;
            }

            $jumlahRaw = $this->firstValue($item, [
                'jumlah',
                'jumlah_transaksi',
                'count',
                'total',
            ], null);

            $nilaiRaw = $this->firstValue($item, [
                'nilai',
                'value',
                'persentase',
                'percentage',
            ], 0);

            $waktuGroups[$label]->push([
                'jumlah' => $jumlahRaw !== null ? $this->toInt($jumlahRaw) : null,
                'nilai' => $this->toFloat($nilaiRaw),
            ]);
        }

        $hasJumlahWaktu = collect($waktuGroups)
            ->flatten(1)
            ->contains(function ($item) {
                return data_get($item, 'jumlah') !== null;
            });

        if ($hasJumlahWaktu) {
            $totalJumlahWaktu = collect($waktuGroups)
                ->flatten(1)
                ->sum(function ($item) {
                    return (int) data_get($item, 'jumlah', 0);
                });

            return collect(['Pagi', 'Siang'])
                ->map(function ($label) use ($waktuGroups, $totalJumlahWaktu) {
                    $jumlah = $waktuGroups[$label]->sum(function ($item) {
                        return (int) data_get($item, 'jumlah', 0);
                    });

                    $persentase = $totalJumlahWaktu > 0
                        ? ($jumlah / $totalJumlahWaktu) * 100
                        : 0;

                    return [
                        'label' => $label,
                        'nilai' => round($persentase, 2),
                        'jumlah' => $jumlah,
                    ];
                })
                ->values();
        }

        $averageByWaktu = collect(['Pagi', 'Siang'])
            ->mapWithKeys(function ($label) use ($waktuGroups) {
                $records = $waktuGroups[$label];

                $average = $records->count() > 0
                    ? (float) $records->avg('nilai')
                    : 0;

                return [$label => $average];
            });

        $totalAverage = (float) $averageByWaktu->sum();

        return collect(['Pagi', 'Siang'])
            ->map(function ($label) use ($averageByWaktu, $totalAverage, $totalTransaksi) {
                $rawValue = (float) $averageByWaktu->get($label, 0);

                $persentase = $totalAverage > 0
                    ? ($rawValue / $totalAverage) * 100
                    : 0;

                return [
                    'label' => $label,
                    'nilai' => round($persentase, 2),
                    'jumlah' => $totalTransaksi > 0 ? (int) round(($persentase / 100) * $totalTransaksi) : 0,
                ];
            })
            ->values();
    }

    private function normalizeWaktuLabel($label): ?string
    {
        $label = strtolower(trim((string) $label));

        if ($label === '') {
            return null;
        }

        if (str_contains($label, 'pagi')) {
            return 'Pagi';
        }

        if (
            str_contains($label, 'siang') ||
            str_contains($label, 'sore') ||
            str_contains($label, 'malam') ||
            str_contains($label, 'night')
        ) {
            return 'Siang';
        }

        return ucfirst($label);
    }

    private function normalizeKanalFilterValue($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return null;
        }

        $value = str_replace(['_', '-', '/', '\\'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);
        $value = trim($value);

        if (in_array($value, ['offline', 'off line', 'luring', 'store', 'toko', 'langsung'], true)) {
            return 'offline';
        }

        if (in_array($value, ['online', 'on line', 'daring', 'website', 'web', 'marketplace', 'e commerce', 'ecommerce'], true)) {
            return 'online';
        }

        if (str_contains($value, 'offline')) {
            return 'offline';
        }

        if (str_contains($value, 'online')) {
            return 'online';
        }

        return null;
    }

    private function formatKanalFilter($kanalFilter): string
    {
        $kanalFilter = $this->normalizeKanalFilterValue($kanalFilter);

        return match ($kanalFilter) {
            'offline' => 'Offline',
            'online' => 'Online',
            default => 'Tidak Diketahui',
        };
    }

    private function hitungJumlahTransaksiWaktu(float $nilai, int $totalTransaksi): int
    {
        if ($totalTransaksi <= 0) {
            return 0;
        }

        if ($nilai <= 100) {
            return (int) round(($nilai / 100) * $totalTransaksi);
        }

        return (int) round($nilai);
    }
}
