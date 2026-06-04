@extends('layouts.app')

@section('title', 'Hasil Analisis - DRW Skincare Analytics')

@section('content')

@php
    $rulesCollection = collect($rules ?? []);

    $isRuleAnomaly = function ($rule) {
        $statusAnomali = strtolower((string) ($rule['status_anomali'] ?? ''));
        $isAnomaly = $rule['is_anomaly'] ?? false;

        return $statusAnomali === 'anomali'
            || $isAnomaly === true
            || $isAnomaly === 1
            || $isAnomaly === '1'
            || strtolower((string) $isAnomaly) === 'true';
    };

    $chartRules = $rulesCollection
        ->filter(function ($rule) use ($isRuleAnomaly) {
            return !$isRuleAnomaly($rule);
        })
        ->sortByDesc(function ($rule) {
            return (float) ($rule['lift'] ?? 0);
        })
        ->take(5)
        ->values()
        ->map(function ($rule, $index) {
            return [
                'label' => 'Rule ' . ($index + 1),
                'rule' => ($rule['antecedents'] ?? '-') . ' → ' . ($rule['consequents'] ?? '-'),
                'lift' => round((float) ($rule['lift'] ?? 0), 4),
                'confidence' => round((float) ($rule['confidence'] ?? 0), 4),
            ];
        });

    $jumlahAnomali = $summary['jumlah_anomali']
        ?? $rulesCollection->filter(function ($rule) use ($isRuleAnomaly) {
            return $isRuleAnomaly($rule);
        })->count();

    $normalRulesForComposition = $rulesCollection->filter(function ($rule) use ($isRuleAnomaly) {
        return !$isRuleAnomaly($rule);
    });

    $strongPatternCount = $normalRulesForComposition->filter(function ($rule) {
        $kategori = strtolower((string) ($rule['kategori_rule'] ?? ($rule['status'] ?? '')));
        return str_contains($kategori, 'strong');
    })->count();

    $moderatePatternCount = $normalRulesForComposition->filter(function ($rule) {
        $kategori = strtolower((string) ($rule['kategori_rule'] ?? ($rule['status'] ?? '')));
        return str_contains($kategori, 'moderate');
    })->count();

    $weakPatternCount = $normalRulesForComposition->filter(function ($rule) {
        $kategori = strtolower((string) ($rule['kategori_rule'] ?? ($rule['status'] ?? '')));
        return str_contains($kategori, 'weak');
    })->count();

    $anomalyPatternCount = $rulesCollection->filter(function ($rule) use ($isRuleAnomaly) {
        return $isRuleAnomaly($rule);
    })->count();

    $ruleComposition = collect([
        [
            'label' => 'Strong Pattern',
            'value' => $strongPatternCount,
        ],
        [
            'label' => 'Moderate Pattern',
            'value' => $moderatePatternCount,
        ],
        [
            'label' => 'Weak Pattern',
            'value' => $weakPatternCount,
        ],
        [
            'label' => 'Anomali',
            'value' => $anomalyPatternCount,
        ],
    ])->values();

    $getHeatmapStyle = function ($lift, $minLift, $maxLift) {
        $lift = (float) $lift;
        $minLift = (float) $minLift;
        $maxLift = (float) $maxLift;

        if ($lift <= 0 || $maxLift <= 0) {
            return [
                'bg_color' => '#f9fafb',
                'border_color' => '#e5e7eb',
                'text_color' => '#111827',
                'opacity' => 0,
            ];
        }

        if ($maxLift == $minLift) {
            $normalized = 1;
        } else {
            $normalized = ($lift - $minLift) / ($maxLift - $minLift);
        }

        $normalized = max(0, min(1, $normalized));
        $contrast = pow($normalized, 1.65);

        $opacity = round(0.08 + ($contrast * 0.92), 2);
        $borderOpacity = round(min(1, $opacity + 0.22), 2);

        return [
            'bg_color' => 'rgba(185, 28, 28, ' . $opacity . ')',
            'border_color' => 'rgba(185, 28, 28, ' . $borderOpacity . ')',
            'text_color' => $opacity >= 0.50 ? '#ffffff' : '#7f1d1d',
            'opacity' => $opacity,
        ];
    };

    $heatmapSource = $heatmapData ?? ($heatmap ?? null);

    $heatmapRows = collect();
    $heatmapColumns = collect();
    $heatmapLegend = collect();
    $heatmapMinLift = 0;
    $heatmapMaxLift = 0;

    if (is_array($heatmapSource) || $heatmapSource instanceof \Illuminate\Support\Collection) {
        $heatmapSourceCollection = collect($heatmapSource);

        $heatmapRows = collect($heatmapSourceCollection->get('rows', []));
        $heatmapColumns = collect($heatmapSourceCollection->get('columns', []));
        $heatmapLegend = collect($heatmapSourceCollection->get('legend', []));
        $heatmapMinLift = (float) ($heatmapSourceCollection->get('min_lift', 0) ?? 0);
        $heatmapMaxLift = (float) ($heatmapSourceCollection->get('max_lift', 0) ?? 0);
    }

    if ($heatmapRows->isEmpty() || $heatmapColumns->isEmpty()) {
        $heatmapRules = $rulesCollection
            ->sortByDesc(function ($rule) {
                return (float) ($rule['lift'] ?? 0);
            })
            ->take(10)
            ->values()
            ->map(function ($rule) use ($isRuleAnomaly) {
                return [
                    'antecedents' => $rule['antecedents'] ?? '-',
                    'consequents' => $rule['consequents'] ?? '-',
                    'lift' => round((float) ($rule['lift'] ?? 0), 4),
                    'confidence' => round((float) ($rule['confidence'] ?? 0), 4),
                    'is_anomaly' => $isRuleAnomaly($rule),
                ];
            });

        $heatmapAntecedents = $heatmapRules->pluck('antecedents')->unique()->values();
        $heatmapConsequents = $heatmapRules->pluck('consequents')->unique()->values();

        $heatmapMinLift = (float) ($heatmapRules->min('lift') ?? 0);
        $heatmapMaxLift = (float) ($heatmapRules->max('lift') ?? 0);

        $heatmapColumns = $heatmapConsequents
            ->map(function ($consequent, $index) {
                return [
                    'key' => $consequent,
                    'code' => 'C' . ($index + 1),
                    'label' => 'C' . ($index + 1),
                    'name' => $consequent,
                ];
            })
            ->values();

        $heatmapRows = $heatmapAntecedents
            ->map(function ($antecedent, $rowIndex) use ($heatmapColumns, $heatmapRules, $heatmapMinLift, $heatmapMaxLift, $getHeatmapStyle) {
                $cells = $heatmapColumns
                    ->map(function ($column) use ($antecedent, $heatmapRules, $heatmapMinLift, $heatmapMaxLift, $getHeatmapStyle) {
                        $matchedRule = $heatmapRules->first(function ($item) use ($antecedent, $column) {
                            return ($item['antecedents'] ?? '') === $antecedent
                                && ($item['consequents'] ?? '') === ($column['key'] ?? '');
                        });

                        if (!$matchedRule) {
                            return [
                                'exists' => false,
                                'lift' => null,
                                'support' => null,
                                'confidence' => null,
                                'bg_color' => '#f9fafb',
                                'border_color' => '#e5e7eb',
                                'text_color' => '#111827',
                                'opacity' => 0,
                                'rule' => null,
                            ];
                        }

                        $lift = (float) ($matchedRule['lift'] ?? 0);
                        $style = $getHeatmapStyle($lift, $heatmapMinLift, $heatmapMaxLift);

                        return [
                            'exists' => true,
                            'lift' => $lift,
                            'support' => null,
                            'confidence' => (float) ($matchedRule['confidence'] ?? 0),
                            'bg_color' => $style['bg_color'],
                            'border_color' => $style['border_color'],
                            'text_color' => $style['text_color'],
                            'opacity' => $style['opacity'],
                            'rule' => $matchedRule,
                        ];
                    })
                    ->values();

                return [
                    'key' => $antecedent,
                    'code' => 'A' . ($rowIndex + 1),
                    'label' => 'A' . ($rowIndex + 1),
                    'name' => $antecedent,
                    'cells' => $cells,
                ];
            })
            ->values();

        $heatmapLegend = $heatmapRows
            ->map(function ($row) {
                return ($row['code'] ?? '-') . ' = ' . ($row['name'] ?? '-');
            })
            ->merge(
                $heatmapColumns->map(function ($column) {
                    return ($column['code'] ?? '-') . ' = ' . ($column['name'] ?? '-');
                })
            )
            ->values();
    }

    $hasHeatmapData = $heatmapRows->count() > 0 && $heatmapColumns->count() > 0;
@endphp

<div class="hasil-page">

    <div class="hasil-card summary-card">
        <h2>Ringkasan Hasil Analisis</h2>

        <div class="summary-grid">
            <div class="summary-item">
                <span>Total Data Awal</span>
                <strong>{{ number_format($summary['total_data_awal'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Setelah Dibersihkan</span>
                <strong>{{ number_format($summary['setelah_preprocessing'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Total Transaksi Akhir</span>
                <strong>{{ number_format($summary['total_basket'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Produk Unik</span>
                <strong>{{ number_format($summary['produk_unik'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Total Operator</span>
                <strong>{{ number_format($summary['total_operator'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Pola Sering Muncul</span>
                <strong>{{ number_format($summary['frequent_itemsets'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Pola Hubungan</span>
                <strong>{{ number_format($summary['association_rules'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Jumlah Anomali</span>
                <strong>{{ number_format($jumlahAnomali) }}</strong>
            </div>

            <div class="summary-item best-rule">
                <span>Pola Terbaik</span>
                <strong>{{ $summary['rule_terbaik'] ?? 'Belum ada rule' }}</strong>
            </div>
        </div>
    </div>

    <div class="hasil-card anomaly-card">
        <div class="anomaly-content">
            <h2>Mode Deteksi Anomali</h2>
            <p>
                Aktifkan mode deteksi anomali untuk melihat rule yang terdeteksi sebagai anomali berdasarkan model.
            </p>
        </div>

        <label class="switch">
            <input type="checkbox" id="toggleAnomaly">
            <span class="slider"></span>
        </label>
    </div>

    <div class="hasil-card filter-card">
        <h2>Filter dan Pencarian Hasil</h2>

        <div class="filter-tabs">
            <button type="button" class="filter-tab active" data-filter="all">Semua</button>
            <button type="button" class="filter-tab" data-filter="produk_operator">Produk × Operator</button>
            <button type="button" class="filter-tab" data-filter="produk_produk">Produk × Produk</button>
            <button type="button" class="filter-tab" data-filter="operator_waktu">Operator × Waktu</button>
            <button type="button" class="filter-tab" data-filter="produk_waktu">Produk × Waktu</button>
            <button type="button" class="filter-tab" data-filter="produk_operator_waktu">Produk × Operator × Waktu</button>
        </div>

        <input type="text" id="searchRules" class="search-input" placeholder="Cari item, rule, kategori, atau status anomali...">
    </div>

    <div class="hasil-card table-card">
        <h2>Tabel Hasil Analisis Pola Transaksi</h2>

        <div class="table-wrapper">
            <table class="rules-table" id="rulesTable" style="width: 100%; table-layout: fixed;">
                <colgroup>
                    <col style="width: 4%;">
                    <col style="width: 11%;">
                    <col style="width: 13%;">
                    <col style="width: 7%;">
                    <col style="width: 8%;">
                    <col style="width: 8%;">
                    <col style="width: 10%;">
                    <col style="width: 7%;">
                    <col style="width: 32%;">
                </colgroup>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kondisi<br>Transaksi</th>
                        <th>Pola yang<br>Berkaitan</th>
                        <th>Tingkat<br>Kemunculan</th>
                        <th>Tingkat<br>Kepercayaan</th>
                        <th>Kekuatan<br>Hubungan</th>
                        <th>Kategori<br>Pola</th>
                        <th>Status<br>Pola</th>
                        <th>Interpretasi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rulesCollection as $rule)
                        @php
                            $kategoriRule = $rule['kategori_rule'] ?? ($rule['status'] ?? 'Weak Pattern');
                            $isAnomaly = $isRuleAnomaly($rule);
                            $jenisRule = $rule['jenis_rule'] ?? '';

                            $kategoriLower = strtolower($kategoriRule);

                            if (str_contains($kategoriLower, 'strong')) {
                                $kategoriClass = 'status-strong';
                            } elseif (str_contains($kategoriLower, 'moderate')) {
                                $kategoriClass = 'status-moderate';
                            } else {
                                $kategoriClass = 'status-weak';
                            }
                        @endphp

                        <tr
                            class="{{ $isAnomaly ? 'row-anomaly' : 'row-normal' }}"
                            data-jenis-rule="{{ $jenisRule }}"
                            data-anomaly="{{ $isAnomaly ? 'true' : 'false' }}"
                        >
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rule['antecedents'] ?? '-' }}</td>
                            <td>{{ $rule['consequents'] ?? '-' }}</td>
                            <td>{{ number_format((float) ($rule['support'] ?? 0), 4) }}</td>
                            <td>{{ number_format((float) ($rule['confidence'] ?? 0), 4) }}</td>
                            <td>{{ number_format((float) ($rule['lift'] ?? 0), 4) }}</td>

                            <td>
                                <span class="status-badge {{ $kategoriClass }}">
                                    {{ $kategoriRule }}
                                </span>
                            </td>

                            <td>
                                @if ($isAnomaly)
                                    <span class="status-badge status-anomaly">
                                        Anomali
                                    </span>
                                @else
                                    <span class="status-badge status-normal">
                                        Normal
                                    </span>
                                @endif
                            </td>

                            <td class="interpretasi-cell">
                                {{ $rule['interpretasi'] ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center;">
                                Belum ada association rules yang tersedia.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            <button type="button" id="prevPage" class="pagination-btn">
                ‹ Sebelumnya
            </button>

            <span id="pageInfo" class="pagination-info">
                1-10 dari {{ $rulesCollection->count() }}
            </span>

            <button type="button" id="nextPage" class="pagination-btn">
                Berikutnya ›
            </button>
        </div>
    </div>

    <div class="chart-grid">
        <div class="hasil-card chart-card chart-card-left">
            <h2>Grafik 5 Pola Hubungan Teratas</h2>

            @if ($chartRules->count() > 0)
                <div class="bar-chart-wrapper">
                    <canvas id="liftChart"></canvas>
                </div>
            @else
                <div class="heatmap-placeholder">
                    <p>Tidak ada rule non-anomali yang dapat ditampilkan pada chart.</p>
                </div>
            @endif

            <div class="composition-section">
                <div class="composition-header">
                    <h3>Komposisi Kategori Pola</h3>
                    <p>Menampilkan perbandingan jumlah rule berdasarkan kategori hasil analisis.</p>
                </div>

                <div class="composition-content">
                    <div class="composition-chart-box">
                        <canvas id="compositionChart"></canvas>
                    </div>

                    <div class="composition-legend">
                        @foreach ($ruleComposition as $item)
                            <div class="composition-legend-item">
                                <span class="composition-dot composition-dot-{{ $loop->iteration }}"></span>
                                <span class="composition-label">{{ $item['label'] }}</span>
                                <strong>{{ number_format($item['value']) }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="hasil-card chart-card">
            <h2>Peta Kekuatan Pola Hubungan</h2>

            @if ($hasHeatmapData)
                <p class="heatmap-note">
                    Peta ini menampilkan kekuatan asosiasi berdasarkan nilai lift.
                    Semakin pekat warna merah pada cell, semakin tinggi nilai lift rule tersebut.
                </p>

                <div class="heatmap-wrapper">
                    <div
                        class="association-heatmap"
                        style="grid-template-columns: 190px repeat({{ max($heatmapColumns->count(), 1) }}, minmax(78px, 1fr));"
                    >
                        <div class="heatmap-corner">A ↓ / C →</div>

                        @foreach ($heatmapColumns as $column)
                            @php
                                $columnCode = $column['code'] ?? $column['label'] ?? ('C' . $loop->iteration);
                                $columnName = $column['name'] ?? $column['key'] ?? '-';
                            @endphp

                            <div class="heatmap-axis heatmap-axis-x" title="{{ $columnName }}">
                                {{ $columnCode }}
                            </div>
                        @endforeach

                        @foreach ($heatmapRows as $row)
                            @php
                                $rowCode = $row['code'] ?? $row['label'] ?? ('A' . $loop->iteration);
                                $rowName = $row['name'] ?? $row['key'] ?? '-';
                                $rowCells = collect($row['cells'] ?? []);
                            @endphp

                            <div class="heatmap-axis heatmap-axis-y" title="{{ $rowName }}">
                                {{ $rowCode }}
                            </div>

                            @foreach ($rowCells as $cell)
                                @php
                                    $cellExists = (bool) ($cell['exists'] ?? false);
                                    $cellLift = $cell['lift'] ?? null;
                                    $cellConfidence = $cell['confidence'] ?? null;
                                    $cellSupport = $cell['support'] ?? null;

                                    $cellBg = $cell['bg_color'] ?? $cell['heatmap_bg_color'] ?? null;
                                    $cellBorder = $cell['border_color'] ?? $cell['heatmap_border_color'] ?? null;
                                    $cellText = $cell['text_color'] ?? $cell['heatmap_text_color'] ?? null;

                                    if ($cellExists && (!$cellBg || !$cellBorder || !$cellText)) {
                                        $style = $getHeatmapStyle((float) ($cellLift ?? 0), $heatmapMinLift, $heatmapMaxLift);

                                        $cellBg = $cellBg ?: $style['bg_color'];
                                        $cellBorder = $cellBorder ?: $style['border_color'];
                                        $cellText = $cellText ?: $style['text_color'];
                                    }

                                    $cellBg = $cellBg ?: '#f9fafb';
                                    $cellBorder = $cellBorder ?: '#e5e7eb';
                                    $cellText = $cellText ?: '#111827';

                                    $cellTitleParts = [];

                                    if ($cellLift !== null) {
                                        $cellTitleParts[] = 'Lift: ' . number_format((float) $cellLift, 4);
                                    }

                                    if ($cellConfidence !== null) {
                                        $cellTitleParts[] = 'Confidence: ' . number_format((float) $cellConfidence, 4);
                                    }

                                    if ($cellSupport !== null) {
                                        $cellTitleParts[] = 'Support: ' . number_format((float) $cellSupport, 4);
                                    }

                                    $cellTitle = implode(' | ', $cellTitleParts);
                                @endphp

                                @if ($cellExists)
                                    <div
                                        class="heatmap-cell has-value"
                                        style="
                                            background: {{ $cellBg }} !important;
                                            border-color: {{ $cellBorder }} !important;
                                            color: {{ $cellText }} !important;
                                        "
                                        title="{{ $cellTitle }}"
                                    ></div>
                                @else
                                    <div
                                        class="heatmap-cell empty"
                                        style="
                                            background: #f9fafb !important;
                                            border-color: #e5e7eb !important;
                                        "
                                    ></div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="heatmap-legend-text">
                    <div class="heatmap-legend-columns">
                        <div class="heatmap-legend-column">
                            <h4>A: Kondisi Transaksi</h4>

                            @foreach ($heatmapRows as $row)
                                <div class="heatmap-legend-row">
                                    <span class="heatmap-legend-code">
                                        {{ $row['code'] ?? $row['label'] ?? ('A' . $loop->iteration) }}
                                    </span>

                                    <span class="heatmap-legend-equal">=</span>

                                    <span class="heatmap-legend-name">
                                        {{ $row['name'] ?? $row['key'] ?? '-' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="heatmap-legend-column">
                            <h4>C: Pola yang Berkaitan</h4>

                            @foreach ($heatmapColumns as $column)
                                <div class="heatmap-legend-row">
                                    <span class="heatmap-legend-code">
                                        {{ $column['code'] ?? $column['label'] ?? ('C' . $loop->iteration) }}
                                    </span>

                                    <span class="heatmap-legend-equal">=</span>

                                    <span class="heatmap-legend-name">
                                        {{ $column['name'] ?? $column['key'] ?? '-' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="heatmap-placeholder">
                    <p>Belum ada data rule untuk ditampilkan pada heatmap.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="hasil-action-row">
        <a href="{{ route('asosiasi.hasil.download') }}" class="btn-primary-action btn-with-icon">
            <span class="action-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3v11m0 0 4-4m-4 4-4-4"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round" />
                    <path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round" />
                </svg>
            </span>
            <span>Unduh Hasil</span>
        </a>

        <a href="{{ route('asosiasi.riwayat') }}" class="btn-secondary-action btn-with-icon">
            <span class="action-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 8v5l3 2"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round" />
                    <path d="M3.05 11a9 9 0 1 1 2.64 6.36"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linecap="round" />
                    <path d="M3 17v-6h6"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linejoin="round" />
                </svg>
            </span>
            <span>Lihat Riwayat Analisis</span>
        </a>

        <a href="{{ route('asosiasi.dashboard') }}" class="btn-secondary-action btn-with-icon">
            <span class="action-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M4 13h6V4H4v9Z"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linejoin="round" />
                    <path d="M14 20h6V4h-6v16Z"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linejoin="round" />
                    <path d="M4 20h6v-3H4v3Z"
                          stroke="currentColor"
                          stroke-width="2"
                          stroke-linejoin="round" />
                </svg>
            </span>
            <span>Lihat Dashboard</span>
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartRules = @json($chartRules);
    const ruleComposition = @json($ruleComposition);

    const ctx = document.getElementById('liftChart');

    if (ctx && chartRules.length > 0) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartRules.map(item => item.label),
                datasets: [{
                    label: 'Lift',
                    data: chartRules.map(item => item.lift),
                    backgroundColor: '#e8007a',
                    borderRadius: 6,
                    barThickness: 44
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function (context) {
                                const index = context[0].dataIndex;
                                return chartRules[index].label;
                            },
                            label: function (context) {
                                const index = context.dataIndex;
                                const item = chartRules[index];

                                return [
                                    'Lift: ' + item.lift,
                                    'Confidence: ' + item.confidence,
                                    'Rule: ' + item.rule
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    const compositionCtx = document.getElementById('compositionChart');

    if (compositionCtx && ruleComposition.length > 0) {
        const compositionTotal = ruleComposition.reduce((total, item) => {
            return total + Number(item.value || 0);
        }, 0);

        if (compositionTotal > 0) {
            new Chart(compositionCtx, {
                type: 'doughnut',
                data: {
                    labels: ruleComposition.map(item => item.label),
                    datasets: [{
                        data: ruleComposition.map(item => item.value),
                        backgroundColor: [
                            '#be185d',
                            '#f59e0b',
                            '#0284c7',
                            '#dc2626'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    const value = Number(context.raw || 0);
                                    const percentage = compositionTotal > 0
                                        ? ((value / compositionTotal) * 100).toFixed(1)
                                        : 0;

                                    return context.label + ': ' + value + ' rule (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    const searchInput = document.getElementById('searchRules');
    const rows = Array.from(document.querySelectorAll('#rulesTable tbody tr[data-jenis-rule]'));
    const filterTabs = document.querySelectorAll('.filter-tab');
    const toggleAnomaly = document.getElementById('toggleAnomaly');

    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const pageInfo = document.getElementById('pageInfo');

    let activeFilter = 'all';
    let currentPage = 1;
    const rowsPerPage = 10;
    let filteredRows = rows;

    function getFilteredRows() {
        const keyword = searchInput ? searchInput.value.toLowerCase() : '';
        const anomalyOnly = toggleAnomaly ? toggleAnomaly.checked : false;

        return rows.filter(row => {
            const rowText = row.innerText.toLowerCase();
            const rowJenis = row.dataset.jenisRule || '';
            const rowAnomaly = row.dataset.anomaly === 'true';

            const matchSearch = rowText.includes(keyword);
            const matchFilter = activeFilter === 'all' || rowJenis === activeFilter;
            const matchAnomaly = !anomalyOnly || rowAnomaly;

            return matchSearch && matchFilter && matchAnomaly;
        });
    }

    function renderTablePage() {
        rows.forEach(row => {
            row.style.display = 'none';
        });

        const totalRows = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;

        const rowsToShow = filteredRows.slice(startIndex, endIndex);

        rowsToShow.forEach(row => {
            row.style.display = '';
        });

        if (pageInfo) {
            if (totalRows === 0) {
                pageInfo.textContent = 'Tidak ada data';
            } else {
                pageInfo.textContent = `${startIndex + 1}-${Math.min(endIndex, totalRows)} dari ${totalRows}`;
            }
        }

        if (prevPageBtn) {
            prevPageBtn.disabled = currentPage <= 1;
        }

        if (nextPageBtn) {
            nextPageBtn.disabled = currentPage >= totalPages;
        }
    }

    function applyFilters() {
        currentPage = 1;
        filteredRows = getFilteredRows();
        renderTablePage();
    }

    if (prevPageBtn) {
        prevPageBtn.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderTablePage();
            }
        });
    }

    if (nextPageBtn) {
        nextPageBtn.addEventListener('click', function () {
            const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));

            if (currentPage < totalPages) {
                currentPage++;
                renderTablePage();
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (toggleAnomaly) {
        toggleAnomaly.addEventListener('change', applyFilters);
    }

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            filterTabs.forEach(item => item.classList.remove('active'));
            this.classList.add('active');

            activeFilter = this.dataset.filter || 'all';
            applyFilters();
        });
    });

    applyFilters();
});
</script>
@endpush