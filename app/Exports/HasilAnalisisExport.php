<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilAnalisisExport implements WithMultipleSheets
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new RingkasanHasilSheet($this->data),
            new AssociationRulesSheet($this->data),
        ];
    }
}

class RingkasanHasilSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function array(): array
    {
        $summary = $this->data['summary'] ?? [];
        $dataset = $this->data['dataset'] ?? [];
        $rules = collect($this->data['rules'] ?? [])->values();

        $normalRules = $rules
            ->reject(function ($rule) {
                return $this->isAnomalyRule($rule);
            })
            ->values();

        $anomalyRules = $rules
            ->filter(function ($rule) {
                return $this->isAnomalyRule($rule);
            })
            ->values();

        $bestNormalRule = $normalRules
            ->sortByDesc(function ($rule) {
                return (float) $this->getRuleValue($rule, 'lift', 0);
            })
            ->first();

        $bestNormalRuleText = $bestNormalRule
            ? ($this->getRuleValue($bestNormalRule, 'antecedents', '-') . ' → ' . $this->getRuleValue($bestNormalRule, 'consequents', '-'))
            : ($summary['rule_terbaik'] ?? 'Belum ada pola normal');

        $strong = $normalRules->filter(function ($rule) {
            $kategori = strtolower((string) ($this->getRuleValue($rule, 'kategori_rule', $this->getRuleValue($rule, 'status', ''))));

            return str_contains($kategori, 'strong');
        })->count();

        $moderate = $normalRules->filter(function ($rule) {
            $kategori = strtolower((string) ($this->getRuleValue($rule, 'kategori_rule', $this->getRuleValue($rule, 'status', ''))));

            return str_contains($kategori, 'moderate');
        })->count();

        $weak = $normalRules->filter(function ($rule) {
            $kategori = strtolower((string) ($this->getRuleValue($rule, 'kategori_rule', $this->getRuleValue($rule, 'status', ''))));

            return str_contains($kategori, 'weak');
        })->count();

        $offlineRules = $rules->filter(function ($rule) {
            return $this->formatKanalLabel(
                $this->getRuleValue($rule, 'kanal_filter_label', $this->getRuleValue($rule, 'kanal_filter', null))
            ) === 'Offline';
        })->count();

        $onlineRules = $rules->filter(function ($rule) {
            return $this->formatKanalLabel(
                $this->getRuleValue($rule, 'kanal_filter_label', $this->getRuleValue($rule, 'kanal_filter', null))
            ) === 'Online';
        })->count();

        return [
            ['LAPORAN HASIL ANALISIS ASOSIASI'],
            [],

            ['Informasi Dataset'],
            ['Nama File', $dataset['nama_file'] ?? '-'],
            ['Tanggal Analisis', $this->formatTanggalIndonesia($dataset['tanggal_analisis'] ?? '-')],
            ['Status', $dataset['status'] ?? '-'],
            ['Kanal Ditampilkan', $summary['kanal_filter_label'] ?? ($dataset['kanal_filter_label'] ?? 'Semua Kanal')],
            [],

            ['Ringkasan Dataset'],
            ['Total Data Awal', (int) ($summary['total_data_awal'] ?? 0)],
            ['Data Setelah Dibersihkan', (int) ($summary['setelah_preprocessing'] ?? 0)],
            ['Total Transaksi Akhir', (int) ($summary['total_basket'] ?? 0)],
            ['Produk Unik', (int) ($summary['produk_unik'] ?? 0)],
            ['Jumlah Operator', (int) ($summary['total_operator'] ?? 0)],
            [],

            ['Ringkasan Pola'],
            ['Pola Sering Muncul (Frequent Itemsets)', (int) ($summary['frequent_itemsets'] ?? 0)],
            ['Pola Hubungan (Association Rules)', $rules->count()],
            ['Total Pola Hubungan Dataset', (int) ($summary['association_rules_total'] ?? $summary['total_rules'] ?? $rules->count())],
            ['Pola Hubungan Offline', $offlineRules],
            ['Pola Hubungan Online', $onlineRules],
            ['Jumlah Pola Normal', $normalRules->count()],
            ['Jumlah Anomali', $anomalyRules->count()],
            ['Pola Hubungan Terbaik Normal', $bestNormalRuleText],
            [],

            ['Komposisi Kategori Pola Normal'],
            ['Strong Pattern', $strong],
            ['Moderate Pattern', $moderate],
            ['Weak Pattern', $weak],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->mergeCells('A1:B1');
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(105);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->getStyle('A1:B' . $highestRow)
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '111827'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sectionStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8007A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ];

        $sectionRows = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $label = trim((string) $sheet->getCell('A' . $row)->getValue());

            if (in_array($label, [
                'Informasi Dataset',
                'Ringkasan Dataset',
                'Ringkasan Pola',
                'Komposisi Kategori Pola Normal',
            ], true)) {
                $sectionRows[] = $row;
                $sheet->mergeCells('A' . $row . ':B' . $row);
                $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray($sectionStyle);
            }
        }

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];

        $sheet->getStyle('A3:B' . $highestRow)->applyFromArray($borderStyle);

        $bestRuleRow = null;

        for ($row = 2; $row <= $highestRow; $row++) {
            $label = trim((string) $sheet->getCell('A' . $row)->getValue());

            if ($label === 'Pola Hubungan Terbaik Normal') {
                $bestRuleRow = $row;
            }

            if (in_array($row, $sectionRows, true)) {
                $sheet->getRowDimension($row)->setRowHeight(20);
                continue;
            }

            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getRowDimension($row)->setRowHeight(19);
        }

        if ($bestRuleRow !== null) {
            $sheet->getStyle('B' . $bestRuleRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getRowDimension($bestRuleRow)->setRowHeight(42);
        }

        return [];
    }

    private function isAnomalyRule($rule): bool
    {
        $statusAnomali = strtolower(trim((string) $this->getRuleValue($rule, 'status_anomali', '')));
        $isAnomaly = $this->getRuleValue($rule, 'is_anomaly', false);

        return $statusAnomali === 'anomali' || $this->normalizeBoolean($isAnomaly);
    }

    private function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['true', '1', 'yes', 'ya', 'anomali'], true);
        }

        return false;
    }

    private function getRuleValue($rule, string $key, $default = null)
    {
        if (is_array($rule)) {
            return array_key_exists($key, $rule) ? $rule[$key] : $default;
        }

        if (is_object($rule)) {
            return $rule->{$key} ?? $default;
        }

        return $default;
    }

    private function formatKanalLabel($value): string
    {
        $value = strtolower(trim((string) ($value ?? '')));

        if ($value === 'offline' || str_contains($value, 'offline')) {
            return 'Offline';
        }

        if ($value === 'online' || str_contains($value, 'online')) {
            return 'Online';
        }

        if ($value === 'semua' || str_contains($value, 'semua')) {
            return 'Semua Kanal';
        }

        return 'Tidak Diketahui';
    }

    private function formatTanggalIndonesia($tanggal): string
    {
        if (!$tanggal || $tanggal === '-') {
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
            if ($tanggal instanceof \Carbon\CarbonInterface) {
                $tanggalFormatted = $tanggal->format('d F Y');
            } else {
                $tanggalFormatted = Carbon::parse($tanggal)->format('d F Y');
            }

            return strtr($tanggalFormatted, $bulanIndonesia);
        } catch (\Exception $e) {
            return strtr((string) $tanggal, $bulanIndonesia);
        }
    }
}

class AssociationRulesSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Pola Hubungan';
    }

    public function array(): array
    {
        $rules = collect($this->data['rules'] ?? [])->values();

        $rows = [
            [
                'No',
                'Kanal',
                'Kondisi Transaksi',
                'Pola yang Berkaitan',
                'Tingkat Kemunculan',
                'Tingkat Kepercayaan',
                'Kekuatan Hubungan',
                'Kategori Pola',
                'Status Pola',
                'Interpretasi',
            ],
        ];

        foreach ($rules as $index => $rule) {
            $isAnomaly = $this->isAnomalyRule($rule);

            $rows[] = [
                $index + 1,
                $this->formatKanalLabel(
                    $this->getRuleValue($rule, 'kanal_filter_label', $this->getRuleValue($rule, 'kanal_filter', $this->getRuleValue($rule, 'kanal', null)))
                ),
                $this->getRuleValue($rule, 'antecedents', '-'),
                $this->getRuleValue($rule, 'consequents', '-'),
                (float) $this->getRuleValue($rule, 'support', 0),
                (float) $this->getRuleValue($rule, 'confidence', 0),
                (float) $this->getRuleValue($rule, 'lift', 0),
                $this->getRuleValue($rule, 'kategori_rule', $this->getRuleValue($rule, 'status', '-')),
                $isAnomaly ? 'Anomali' : 'Normal',
                $this->getRuleValue($rule, 'interpretasi', '-'),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:J' . $highestRow);

        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8007A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A1:J' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getStyle('A1:J' . $highestRow)
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        if ($highestRow >= 2) {
            $sheet->getStyle('E2:G' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode('0.0000');

            $sheet->getStyle('C2:D' . $highestRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('J2:J' . $highestRow)->getAlignment()->setWrapText(true);

            $sheet->getStyle('A2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E2:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('H2:I' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            for ($row = 2; $row <= $highestRow; $row++) {
                $kanalCell = 'B' . $row;
                $kategoriCell = 'H' . $row;
                $statusCell = 'I' . $row;

                $kategori = strtolower((string) $sheet->getCell($kategoriCell)->getValue());
                $status = strtolower((string) $sheet->getCell($statusCell)->getValue());

                if ($status === 'anomali') {
                    $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF7ED'],
                        ],
                    ]);
                }

                $sheet->getStyle($kanalCell)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3E8FF'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '7E22CE'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                if (str_contains($kategori, 'strong')) {
                    $sheet->getStyle($kategoriCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FCE7F3'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'BE185D'],
                        ],
                    ]);
                } elseif (str_contains($kategori, 'moderate')) {
                    $sheet->getStyle($kategoriCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEF3C7'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '92400E'],
                        ],
                    ]);
                } else {
                    $sheet->getStyle($kategoriCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E0F2FE'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '0369A1'],
                        ],
                    ]);
                }

                if ($status === 'anomali') {
                    $sheet->getStyle($statusCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FEE2E2'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'DC2626'],
                        ],
                    ]);
                } else {
                    $sheet->getStyle($statusCell)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'DCFCE7'],
                        ],
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => '15803D'],
                        ],
                    ]);
                }
            }
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(45);
        $sheet->getColumnDimension('D')->setWidth(45);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(22);
        $sheet->getColumnDimension('H')->setWidth(22);
        $sheet->getColumnDimension('I')->setWidth(18);
        $sheet->getColumnDimension('J')->setWidth(85);

        return [];
    }

    private function isAnomalyRule($rule): bool
    {
        $statusAnomali = strtolower(trim((string) $this->getRuleValue($rule, 'status_anomali', '')));
        $isAnomaly = $this->getRuleValue($rule, 'is_anomaly', false);

        return $statusAnomali === 'anomali' || $this->normalizeBoolean($isAnomaly);
    }

    private function normalizeBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['true', '1', 'yes', 'ya', 'anomali'], true);
        }

        return false;
    }

    private function getRuleValue($rule, string $key, $default = null)
    {
        if (is_array($rule)) {
            return array_key_exists($key, $rule) ? $rule[$key] : $default;
        }

        if (is_object($rule)) {
            return $rule->{$key} ?? $default;
        }

        return $default;
    }

    private function formatKanalLabel($value): string
    {
        $value = strtolower(trim((string) ($value ?? '')));

        if ($value === 'offline' || str_contains($value, 'offline')) {
            return 'Offline';
        }

        if ($value === 'online' || str_contains($value, 'online')) {
            return 'Online';
        }

        if ($value === 'semua' || str_contains($value, 'semua')) {
            return 'Semua Kanal';
        }

        return 'Tidak Diketahui';
    }
}
