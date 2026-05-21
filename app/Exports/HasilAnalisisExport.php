<?php

namespace App\Exports;

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
            new HeatmapSheet($this->data),
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
        $rules = collect($this->data['rules'] ?? []);

        $anomalyRules = $rules->filter(function ($rule) {
            return $this->isAnomalyRule($rule);
        });

        $normalRules = $rules->filter(function ($rule) {
            return !$this->isAnomalyRule($rule);
        });

        $strong = $normalRules->filter(function ($rule) {
            $kategori = strtolower((string) ($rule['kategori_rule'] ?? ($rule['status'] ?? '')));
            return str_contains($kategori, 'strong');
        })->count();

        $moderate = $normalRules->filter(function ($rule) {
            $kategori = strtolower((string) ($rule['kategori_rule'] ?? ($rule['status'] ?? '')));
            return str_contains($kategori, 'moderate');
        })->count();

        $weak = $normalRules->filter(function ($rule) {
            $kategori = strtolower((string) ($rule['kategori_rule'] ?? ($rule['status'] ?? '')));
            return str_contains($kategori, 'weak');
        })->count();

        $anomali = $anomalyRules->count();

        return [
            ['LAPORAN HASIL ANALISIS ASOSIASI'],

            ['Informasi Dataset'],
            ['Nama File', $dataset['nama_file'] ?? '-'],
            ['Tanggal Analisis', $dataset['tanggal_analisis'] ?? '-'],
            ['Status', $dataset['status'] ?? '-'],

            ['Ringkasan Hasil Analisis'],
            ['Total Data Awal', $summary['total_data_awal'] ?? 0],
            ['Setelah Dibersihkan', $summary['setelah_preprocessing'] ?? 0],
            ['Total Transaksi Akhir', $summary['total_basket'] ?? 0],
            ['Produk Unik', $summary['produk_unik'] ?? 0],
            ['Total Operator', $summary['total_operator'] ?? 0],
            ['Frequent Itemsets', $summary['frequent_itemsets'] ?? 0],
            ['Association Rules', $summary['association_rules'] ?? 0],
            ['Jumlah Anomali', $summary['jumlah_anomali'] ?? $anomali],
            ['Rule Terbaik', $summary['rule_terbaik'] ?? 'Belum ada rule'],

            ['Komposisi Kategori Rule'],
            ['Strong Pattern', $strong],
            ['Moderate Pattern', $moderate],
            ['Weak Pattern', $weak],
            ['Anomali', $anomali],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->mergeCells('A1:B1');
        $sheet->mergeCells('A2:B2');
        $sheet->mergeCells('A6:B6');
        $sheet->mergeCells('A16:B16');

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('B')->setWidth(95);

        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(15)->setRowHeight(44);

        $sheet->getStyle('A1:B' . $highestRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:B' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '111827'],
            ],
        ]);

        $sectionStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '991B1B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ];

        $sheet->getStyle('A2:B2')->applyFromArray($sectionStyle);
        $sheet->getStyle('A6:B6')->applyFromArray($sectionStyle);
        $sheet->getStyle('A16:B16')->applyFromArray($sectionStyle);

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];

        $sheet->getStyle('A2:B20')->applyFromArray($borderStyle);

        $sheet->getStyle('A3:A5')->getFont()->setBold(true);
        $sheet->getStyle('A7:A15')->getFont()->setBold(true);
        $sheet->getStyle('A17:A20')->getFont()->setBold(true);

        $sheet->getStyle('B7:B14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B17:B20')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle('A15:B15')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF2F2'],
            ],
        ]);

        $sheet->getStyle('B15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }

    private function isAnomalyRule($rule): bool
    {
        $statusAnomali = strtolower((string) ($rule['status_anomali'] ?? ''));
        $isAnomaly = $rule['is_anomaly'] ?? false;

        return $statusAnomali === 'anomali'
            || $isAnomaly === true
            || $isAnomaly === 1
            || $isAnomaly === '1'
            || strtolower((string) $isAnomaly) === 'true';
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
        return 'Association Rules';
    }

    public function array(): array
    {
        $rules = collect($this->data['rules'] ?? []);

        $rows = [
            [
                'No',
                'Antecedents',
                'Consequents',
                'Support',
                'Confidence',
                'Lift',
                'Kategori Rule',
                'Deteksi Anomali',
                'Interpretasi',
            ],
        ];

        foreach ($rules as $index => $rule) {
            $isAnomaly = $this->isAnomalyRule($rule);

            $rows[] = [
                $index + 1,
                $rule['antecedents'] ?? '-',
                $rule['consequents'] ?? '-',
                (float) ($rule['support'] ?? 0),
                (float) ($rule['confidence'] ?? 0),
                (float) ($rule['lift'] ?? 0),
                $rule['kategori_rule'] ?? ($rule['status'] ?? '-'),
                $isAnomaly ? 'Anomali' : 'Normal',
                $rule['interpretasi'] ?? '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        $sheet->freezePane('A2');

        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '991B1B'],
            ],
        ]);

        $sheet->getStyle('A1:I' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        if ($highestRow >= 2) {
            $sheet->getStyle('D2:F' . $highestRow)
                ->getNumberFormat()
                ->setFormatCode('0.0000');

            $sheet->getStyle('B2:C' . $highestRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('I2:I' . $highestRow)->getAlignment()->setWrapText(true);

            $sheet->getStyle('A2:A' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D2:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(38);
        $sheet->getColumnDimension('C')->setWidth(38);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(65);

        return [];
    }

    private function isAnomalyRule($rule): bool
    {
        $statusAnomali = strtolower((string) ($rule['status_anomali'] ?? ''));
        $isAnomaly = $rule['is_anomaly'] ?? false;

        return $statusAnomali === 'anomali'
            || $isAnomaly === true
            || $isAnomaly === 1
            || $isAnomaly === '1'
            || strtolower((string) $isAnomaly) === 'true';
    }
}

class HeatmapSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Heatmap';
    }

    public function array(): array
    {
        $heatmap = $this->data['heatmapData'] ?? ($this->data['heatmap'] ?? []);
        $rows = collect($heatmap['rows'] ?? []);
        $columns = collect($heatmap['columns'] ?? []);

        $sheetRows = [];

        $header = ['A / C'];

        foreach ($columns as $column) {
            $header[] = $column['code'] ?? $column['label'] ?? '-';
        }

        $sheetRows[] = ['HEATMAP ASOSIASI BERDASARKAN NILAI LIFT'];
        $sheetRows[] = $header;

        foreach ($rows as $row) {
            $line = [
                $row['code'] ?? $row['label'] ?? '-',
            ];

            foreach (collect($row['cells'] ?? []) as $cell) {
                $line[] = !empty($cell['exists'])
                    ? (float) ($cell['lift'] ?? 0)
                    : '';
            }

            $sheetRows[] = $line;
        }

        $sheetRows[] = ['Keterangan'];
        $sheetRows[] = ['A', 'Kondisi Transaksi'];
        $sheetRows[] = ['C', 'Pola yang Berkaitan'];

        $sheetRows[] = ['A: Kondisi Transaksi'];

        foreach ($rows as $row) {
            $sheetRows[] = [
                $row['code'] ?? $row['label'] ?? '-',
                $row['name'] ?? $row['key'] ?? '-',
            ];
        }

        $sheetRows[] = ['C: Pola yang Berkaitan'];

        foreach ($columns as $column) {
            $sheetRows[] = [
                $column['code'] ?? $column['label'] ?? '-',
                $column['name'] ?? $column['key'] ?? '-',
            ];
        }

        return $sheetRows;
    }

    public function styles(Worksheet $sheet)
    {
        $heatmap = $this->data['heatmapData'] ?? ($this->data['heatmap'] ?? []);
        $rows = collect($heatmap['rows'] ?? []);
        $columns = collect($heatmap['columns'] ?? []);

        $rowCount = $rows->count();
        $columnCount = $columns->count();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        $matrixHeaderRow = 2;
        $matrixEndRow = 2 + $rowCount;

        $legendTitleRow = $matrixEndRow + 1;
        $legendARow = $legendTitleRow + 1;
        $legendCRow = $legendTitleRow + 2;

        $aTitleRow = $legendTitleRow + 3;
        $aEndRow = $aTitleRow + $rowCount;

        $cTitleRow = $aEndRow + 1;

        $sheet->mergeCells('A1:' . $highestColumn . '1');

        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '111827'],
            ],
        ]);

        $sheet->getStyle('A' . $matrixHeaderRow . ':' . $highestColumn . $matrixHeaderRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '111827'],
            ],
        ]);

        $sheet->getStyle('A' . $matrixHeaderRow . ':' . $highestColumn . $matrixEndRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        if ($rowCount > 0 && $columnCount > 0) {
            $sheet->getStyle('B3:' . $highestColumn . $matrixEndRow)
                ->getNumberFormat()
                ->setFormatCode('0.0000');

            $sheet->getStyle('B3:' . $highestColumn . $matrixEndRow)
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sectionRows = [
            $legendTitleRow,
            $aTitleRow,
            $cTitleRow,
        ];

        foreach ($sectionRows as $rowNumber) {
            if ($rowNumber <= $highestRow) {
                $sheet->mergeCells('A' . $rowNumber . ':B' . $rowNumber);
                $sheet->getStyle('A' . $rowNumber . ':B' . $rowNumber)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '991B1B'],
                    ],
                ]);
            }
        }

        $sheet->getStyle('A' . $legendTitleRow . ':B' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getColumnDimension('A')->setWidth(18);

        for ($i = 2; $i <= max(2, $columnCount + 1); $i++) {
            $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($columnLetter)->setWidth(18);
        }

        $sheet->getColumnDimension('B')->setWidth(55);

        return [];
    }
}