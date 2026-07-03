@extends('layouts.app')

@section('title', 'Hasil Analisis - DRW Skincare Analytics')

@section('content')

@php
    $normalizeSelectedKanal = function ($value) {
        $value = strtolower(trim((string) $value));

        return in_array($value, ['semua', 'offline', 'online'], true)
            ? $value
            : 'semua';
    };

    $normalizeRuleKanal = function ($value) {
        $value = strtolower(trim((string) $value));

        if ($value === 'offline' || str_contains($value, 'offline')) {
            return 'offline';
        }

        if ($value === 'online' || str_contains($value, 'online')) {
            return 'online';
        }

        return 'unknown';
    };

    $formatKanalLabel = function ($value) {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            'offline' => 'Offline',
            'online' => 'Online',
            'unknown' => 'Tidak Diketahui',
            default => 'Semua Kanal',
        };
    };

    $selectedKanal = $normalizeSelectedKanal(request('kanal', request('kanal_filter', 'semua')));
    $selectedKanalLabel = $formatKanalLabel($selectedKanal);

    $allRulesCollection = collect($rules ?? [])
        ->map(function ($rule) use ($normalizeRuleKanal, $formatKanalLabel) {
            if ($rule instanceof \Illuminate\Contracts\Support\Arrayable) {
                $rule = $rule->toArray();
            } elseif (is_object($rule)) {
                $rule = get_object_vars($rule);
            }

            $rule = is_array($rule) ? $rule : [];

            $ruleKanal = $normalizeRuleKanal(
                $rule['kanal_filter']
                    ?? $rule['kanal']
                    ?? $rule['channel']
                    ?? $rule['tipe_penjualan']
                    ?? null
            );

            $rule['kanal_filter'] = $ruleKanal;
            $rule['kanal_filter_label'] = $formatKanalLabel($ruleKanal);

            return $rule;
        })
        ->values();

    $totalRulesAll = $allRulesCollection->count();
    $totalRulesOffline = $allRulesCollection->where('kanal_filter', 'offline')->count();
    $totalRulesOnline = $allRulesCollection->where('kanal_filter', 'online')->count();
    $totalRulesUnknown = $allRulesCollection->where('kanal_filter', 'unknown')->count();

    $rulesCollection = $selectedKanal === 'semua'
        ? $allRulesCollection
        : $allRulesCollection->where('kanal_filter', $selectedKanal)->values();

    $isRuleAnomaly = function ($rule) {
        $statusAnomali = strtolower((string) ($rule['status_anomali'] ?? ''));
        $isAnomaly = $rule['is_anomaly'] ?? false;

        return $statusAnomali === 'anomali'
            || $isAnomaly === true
            || $isAnomaly === 1
            || $isAnomaly === '1'
            || strtolower((string) $isAnomaly) === 'true';
    };

    $jumlahAnomali = $rulesCollection
        ->filter(function ($rule) use ($isRuleAnomaly) {
            return $isRuleAnomaly($rule);
        })
        ->count();

    /*
    |--------------------------------------------------------------------------
    | Pola terbaik hanya dari rule normal
    |--------------------------------------------------------------------------
    | Rule anomali tidak dipakai untuk card Pola Terbaik, chart pola teratas,
    | dan dasar pembacaan pola normal. Jadi walaupun lift anomali lebih tinggi,
    | yang tampil sebagai Pola Terbaik tetap rule normal dengan lift tertinggi.
    */
    $normalRulesCollection = $rulesCollection
        ->reject(function ($rule) use ($isRuleAnomaly) {
            return $isRuleAnomaly($rule);
        })
        ->values();

    $bestRule = $normalRulesCollection
        ->sortByDesc(function ($rule) {
            return (float) ($rule['lift'] ?? 0);
        })
        ->first();

    $bestRuleText = $bestRule
        ? (($bestRule['antecedents'] ?? '-') . ' → ' . ($bestRule['consequents'] ?? '-'))
        : 'Belum ada rule normal';

    $chartRules = $normalRulesCollection
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

    $normalRulesForComposition = $normalRulesCollection;

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
                'support' => round((float) ($rule['support'] ?? 0), 4),
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
                        'support' => (float) ($matchedRule['support'] ?? 0),
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

    $hasHeatmapData = $heatmapRows->count() > 0 && $heatmapColumns->count() > 0;
    $polaSeringMunculCount = (int) (
        $summary['pola_sering_muncul']
            ?? $summary['frequent_itemsets']
            ?? $summary['total_frequent_itemsets']
            ?? 0
    );

    $polaHubunganCount = $rulesCollection->count();
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
                <strong>{{ number_format($polaSeringMunculCount) }}</strong>
            </div>

            <div class="summary-item">
                <span>Pola Hubungan</span>
                <strong>{{ number_format($polaHubunganCount) }}</strong>
            </div>

            <div class="summary-item">
                <span>Jumlah Anomali</span>
                <strong>{{ number_format($jumlahAnomali) }}</strong>
            </div>

            <div class="summary-item">
                <span>Kanal Ditampilkan</span>
                <strong>{{ $selectedKanalLabel }}</strong>
            </div>

            <div class="summary-item">
                <span>Pola Hubungan Offline</span>
                <strong>{{ number_format($totalRulesOffline) }}</strong>
            </div>

            <div class="summary-item">
                <span>Pola Hubungan Online</span>
                <strong>{{ number_format($totalRulesOnline) }}</strong>
            </div>

            @if($totalRulesUnknown > 0)
                <div class="summary-item">
                    <span>Pola Hubungan Tidak Diketahui</span>
                    <strong>{{ number_format($totalRulesUnknown) }}</strong>
                </div>
            @endif

            <div class="summary-item best-rule">
                <span>Pola Terbaik</span>
                <strong>{{ $bestRuleText }}</strong>
            </div>
        </div>
    </div>

    <div class="hasil-card filter-card">
        <h2>Filter Kanal Hasil Analisis</h2>

        <form method="GET" action="{{ route('asosiasi.hasil') }}">
            <div class="filter-tabs kanal-filter-tabs">
                <button
                    type="submit"
                    name="kanal"
                    value="semua"
                    class="filter-tab {{ $selectedKanal === 'semua' ? 'active' : '' }}"
                >
                    Semua
                </button>

                <button
                    type="submit"
                    name="kanal"
                    value="offline"
                    class="filter-tab {{ $selectedKanal === 'offline' ? 'active' : '' }}"
                >
                    Offline
                </button>

                <button
                    type="submit"
                    name="kanal"
                    value="online"
                    class="filter-tab {{ $selectedKanal === 'online' ? 'active' : '' }}"
                >
                    Online
                </button>
            </div>
        </form>

        <p class="parameter-info">
            Filter kanal untuk melihat pola hubungan berdasarkan tipe penjualan offline atau online.
        </p>
    </div>

    <div class="hasil-card anomaly-card">
        <div class="anomaly-content">
            <h2>Mode Deteksi Anomali</h2>
            <p>
                Aktifkan mode deteksi anomali untuk melihat pola hubungan yang terdeteksi sebagai anomali berdasarkan model.
            </p>
        </div>

        <label class="switch">
            <input type="checkbox" id="toggleAnomaly">
            <span class="slider"></span>
        </label>
    </div>

    <div class="hasil-card filter-card">
        <h2>Filter Pola dan Pencarian Hasil</h2>

        <div class="filter-tabs">
            <button type="button" class="filter-tab active" data-filter="all">Semua Pola</button>
            <button type="button" class="filter-tab" data-filter="produk_operator_waktu">Produk × Operator × Waktu</button>
            <button type="button" class="filter-tab" data-filter="produk_operator">Produk × Operator</button>
            <button type="button" class="filter-tab" data-filter="produk_produk">Produk × Produk</button>
            <button type="button" class="filter-tab" data-filter="operator_waktu">Operator × Waktu</button>
            <button type="button" class="filter-tab" data-filter="produk_waktu">Produk × Waktu</button>
        </div>

        <input type="text" id="searchRules" class="search-input" placeholder="Cari produk, operator, waktu, kanal, kategori, atau status anomali...">
    </div>

    <div class="hasil-card table-card">
        <h2>Tabel Pola Hubungan Hasil Analisis</h2>

        <div class="table-wrapper">
            <table class="rules-table" id="rulesTable" style="width: 100%; table-layout: fixed;">
                <colgroup>
                    <col style="width: 64px;">
                    <col style="width: 110px;">
                    <col style="width: 185px;">
                    <col style="width: 205px;">
                    <col style="width: 125px;">
                    <col style="width: 135px;">
                    <col style="width: 125px;">
                    <col style="width: 155px;">
                    <col style="width: 120px;">
                    <col style="width: 436px;">
                </colgroup>

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kanal</th>
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

                            $ruleKanal = $rule['kanal_filter'] ?? 'unknown';
                            $ruleKanalLabel = $rule['kanal_filter_label'] ?? $formatKanalLabel($ruleKanal);

                            $kanalBadgeClass = match ($ruleKanal) {
                                'offline' => 'kanal-offline',
                                'online' => 'kanal-online',
                                default => 'kanal-unknown',
                            };
                        @endphp

                        <tr
                            class="{{ $isAnomaly ? 'row-anomaly' : 'row-normal' }}"
                            data-jenis-rule="{{ $jenisRule }}"
                            data-anomaly="{{ $isAnomaly ? 'true' : 'false' }}"
                            data-kanal="{{ $ruleKanal }}"
                        >
                            <td class="row-number">{{ $loop->iteration }}</td>

                            <td>
                                <span class="status-badge kanal-badge {{ $kanalBadgeClass }}">
                                    {{ $ruleKanalLabel }}
                                </span>
                            </td>

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
                            <td colspan="10" style="text-align: center;">
                                Belum ada pola hubungan yang tersedia untuk kanal ini.
                            </td>
                        </tr>
                    @endforelse

                    @if($rulesCollection->count() > 0)
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="10" style="text-align: center;">
                                Tidak ada pola hubungan yang sesuai dengan filter atau pencarian.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            <button type="button" id="prevPage" class="pagination-btn">
                ‹ Sebelumnya
            </button>

            <span id="pageInfo" class="pagination-info">
                1-10 dari {{ $polaHubunganCount }}
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
                    <p>Tidak ada pola hubungan normal yang dapat ditampilkan pada chart untuk kanal ini.</p>
                </div>
            @endif

            <div class="composition-section">
                <div class="composition-header">
                    <h3>Komposisi Kategori Pola</h3>
                    <p>Menampilkan perbandingan jumlah pola hubungan berdasarkan kategori hasil analisis.</p>
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
                    Peta ini menampilkan kekuatan asosiasi berdasarkan nilai lift pada kanal
                    <strong>{{ $selectedKanalLabel }}</strong>.
                    Semakin pekat warna merah pada cell, semakin tinggi nilai lift pola hubungan tersebut.
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

                                    $cellBg = $cell['bg_color'] ?? '#f9fafb';
                                    $cellBorder = $cell['border_color'] ?? '#e5e7eb';
                                    $cellText = $cell['text_color'] ?? '#111827';

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
                    <p>Belum ada data pola hubungan untuk ditampilkan pada heatmap untuk kanal ini.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="hasil-action-row">
        <a href="{{ route('asosiasi.hasil.download', ['kanal' => $selectedKanal]) }}" class="btn-primary-action btn-with-icon">
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
                                    'Pola: ' + item.rule
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

                                    return context.label + ': ' + value + ' pola hubungan (' + percentage + '%)';
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
    const filterTabs = document.querySelectorAll('.filter-tab[data-filter]');
    const toggleAnomaly = document.getElementById('toggleAnomaly');
    const noResultsRow = document.getElementById('noResultsRow');

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

    function updateVisibleRowNumbers(rowsToShow, startIndex) {
        rowsToShow.forEach((row, index) => {
            const numberCell = row.querySelector('.row-number');

            if (numberCell) {
                numberCell.textContent = startIndex + index + 1;
            }
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

        updateVisibleRowNumbers(rowsToShow, startIndex);

        if (noResultsRow) {
            noResultsRow.style.display = totalRows === 0 ? '' : 'none';
        }

        if (pageInfo) {
            if (totalRows === 0) {
                pageInfo.textContent = 'Tidak ada data';
            } else {
                pageInfo.textContent = `${startIndex + 1}-${Math.min(endIndex, totalRows)} dari ${totalRows}`;
            }
        }

        if (prevPageBtn) {
            prevPageBtn.disabled = currentPage <= 1 || totalRows === 0;
        }

        if (nextPageBtn) {
            nextPageBtn.disabled = currentPage >= totalPages || totalRows === 0;
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