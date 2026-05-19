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
    $heatmapRangeLift = $heatmapMaxLift - $heatmapMinLift;
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
                <span>Frequent Itemsets</span>
                <strong>{{ number_format($summary['frequent_itemsets'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Association Rules</span>
                <strong>{{ number_format($summary['association_rules'] ?? 0) }}</strong>
            </div>

            <div class="summary-item">
                <span>Jumlah Anomali</span>
                <strong>{{ number_format($jumlahAnomali) }}</strong>
            </div>

            <div class="summary-item best-rule">
                <span>Rule Terbaik</span>
                <strong>{{ $summary['rule_terbaik'] ?? 'Belum ada rule' }}</strong>
            </div>
        </div>
    </div>

    <div class="hasil-card anomaly-card">
        <div>
            <h2>Mode Deteksi Anomali</h2>
            <p>Aktifkan mode deteksi anomali untuk melihat rule yang terdeteksi sebagai anomali berdasarkan model.</p>
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
        <h2>Tabel Hasil Association Rules</h2>

        <div class="table-wrapper">
            <table class="rules-table" id="rulesTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Antecedents</th>
                        <th>Consequents</th>
                        <th>Support</th>
                        <th>Confidence</th>
                        <th>Lift</th>
                        <th>Kategori Rule</th>
                        <th>Deteksi Anomali</th>
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
                                @if($isAnomaly)
                                    <span class="status-badge status-anomaly">
                                        Anomali
                                    </span>
                                @else
                                    <span class="status-badge status-normal">
                                        Normal
                                    </span>
                                @endif
                            </td>

                            <td>{{ $rule['interpretasi'] ?? '-' }}</td>
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
        <div class="hasil-card chart-card">
            <h2>Bar Chart Top 5 Rules Non-Anomali Berdasarkan Lift</h2>

            @if($chartRules->count() > 0)
                <canvas id="liftChart" height="130"></canvas>
            @else
                <div class="heatmap-placeholder">
                    <p>Tidak ada rule non-anomali yang dapat ditampilkan pada chart.</p>
                </div>
            @endif
        </div>

        <div class="hasil-card chart-card">
            <h2>Heatmap Asosiasi</h2>

            @if($heatmapRules->count() > 0)
                <p class="heatmap-note">
                    Heatmap menampilkan kekuatan asosiasi berdasarkan nilai lift.
                    Semakin tegas warna cell, semakin tinggi nilai lift rule tersebut.
                </p>

                <div class="heatmap-wrapper">
                    <div
                        class="association-heatmap"
                        style="grid-template-columns: 165px repeat({{ max($heatmapConsequents->count(), 1) }}, 54px);"
                    >
                        <div class="heatmap-corner">A ↓ / C →</div>

                        @foreach($heatmapConsequents as $index => $consequent)
                            <div class="heatmap-axis heatmap-axis-x" title="{{ $consequent }}">
                                C{{ $index + 1 }}
                            </div>
                        @endforeach

                        @foreach($heatmapAntecedents as $rowIndex => $antecedent)
                            <div class="heatmap-axis heatmap-axis-y" title="{{ $antecedent }}">
                                A{{ $rowIndex + 1 }}
                            </div>

                            @foreach($heatmapConsequents as $consequent)
                                @php
                                    $cellRule = $heatmapRules->first(function ($item) use ($antecedent, $consequent) {
                                        return $item['antecedents'] === $antecedent
                                            && $item['consequents'] === $consequent;
                                    });

                                    $cellLift = $cellRule['lift'] ?? 0;
                                    $cellConfidence = $cellRule['confidence'] ?? 0;
                                    $cellIsAnomaly = $cellRule['is_anomaly'] ?? false;

                                    if ($cellRule) {
                                        if ($heatmapRangeLift > 0) {
                                            $intensity = 0.12 + ((($cellLift - $heatmapMinLift) / $heatmapRangeLift) * 0.88);
                                        } else {
                                            $intensity = 0.65;
                                        }

                                        $intensity = max(0.12, min(1, $intensity));
                                    } else {
                                        $intensity = 0;
                                    }
                                @endphp

                                @if($cellRule)
                                    <div
                                        class="heatmap-cell has-value {{ $cellIsAnomaly ? 'heatmap-anomaly-cell' : '' }}"
                                        style="--heatmap-intensity: {{ $intensity }};"
                                        title="Lift: {{ number_format($cellLift, 4) }} | Confidence: {{ number_format($cellConfidence, 4) }}{{ $cellIsAnomaly ? ' | Anomali' : '' }}"
                                    ></div>
                                @else
                                    <div class="heatmap-cell empty"></div>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="heatmap-legend-text">
                    <strong>Keterangan:</strong>

                    @foreach($heatmapAntecedents as $index => $antecedent)
                        A{{ $index + 1 }} = {{ $antecedent }}@if(!$loop->last), @endif
                    @endforeach

                    <br>

                    @foreach($heatmapConsequents as $index => $consequent)
                        C{{ $index + 1 }} = {{ $consequent }}@if(!$loop->last), @endif
                    @endforeach
                </div>
            @else
                <div class="heatmap-placeholder">
                    <p>Belum ada data rule untuk ditampilkan pada heatmap.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="hasil-action-row">
        <a href="{{ route('asosiasi.download') }}" class="btn-primary-action btn-with-icon">
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
                          stroke-linecap="round"
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