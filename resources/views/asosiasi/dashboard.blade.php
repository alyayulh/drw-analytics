@extends('layouts.app')

@section('title', 'Dashboard Insight - DRW Skincare Analytics')

@section('content')

@php
    $user = auth()->user();

    $userRole = strtolower(
        $user->role
        ?? $user->level
        ?? $user->tipe_user
        ?? $user->akses
        ?? ''
    );

    $isManajer = in_array($userRole, ['manajer', 'manager']);

    $toFloat = function ($value) {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = str_replace('%', '', (string) $value);
        $value = str_replace(',', '.', $value);
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : 0.0;
    };

    $normalizeWaktuLabel = function ($label) {
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
    };

    $shiftRange = [
        'Pagi' => '08.00 - 12.59',
        'Siang' => '13.00 - 22.00',
    ];

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
            default => 'Tidak Diketahui',
        };
    };

    $topProdukChart = collect($topProduk ?? [])
        ->map(function ($item) {
            return [
                'nama' => data_get($item, 'nama', '-'),
                'jumlah' => (int) data_get($item, 'jumlah', 0),
            ];
        })
        ->sortByDesc('jumlah')
        ->take(10)
        ->values();

    $rawDistribusiWaktu = collect($distribusiWaktu ?? []);

    $waktuGroups = [
        'Pagi' => collect(),
        'Siang' => collect(),
    ];

    foreach ($rawDistribusiWaktu as $item) {
        $label = $normalizeWaktuLabel(
            data_get($item, 'label')
            ?? data_get($item, 'waktu')
            ?? data_get($item, 'nama')
            ?? data_get($item, 'name')
            ?? ''
        );

        if (!in_array($label, ['Pagi', 'Siang'], true)) {
            continue;
        }

        $jumlahRaw = data_get($item, 'jumlah')
            ?? data_get($item, 'jumlah_transaksi')
            ?? data_get($item, 'count')
            ?? data_get($item, 'total')
            ?? null;

        $nilaiRaw = data_get($item, 'nilai')
            ?? data_get($item, 'value')
            ?? data_get($item, 'persentase')
            ?? data_get($item, 'percentage')
            ?? 0;

        $waktuGroups[$label]->push([
            'jumlah' => $jumlahRaw !== null ? (int) $jumlahRaw : null,
            'nilai' => $toFloat($nilaiRaw),
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

        $distribusiWaktuChart = collect(['Pagi', 'Siang'])
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
    } else {
        $averageByWaktu = collect(['Pagi', 'Siang'])
            ->mapWithKeys(function ($label) use ($waktuGroups) {
                $records = $waktuGroups[$label];

                $average = $records->count() > 0
                    ? (float) $records->avg('nilai')
                    : 0;

                return [$label => $average];
            });

        $totalAverage = (float) $averageByWaktu->sum();

        $distribusiWaktuChart = collect(['Pagi', 'Siang'])
            ->map(function ($label) use ($averageByWaktu, $totalAverage, $summary) {
                $rawValue = (float) $averageByWaktu->get($label, 0);

                $persentase = $totalAverage > 0
                    ? ($rawValue / $totalAverage) * 100
                    : 0;

                $totalTransaksi = (int) ($summary['total_basket'] ?? 0);

                return [
                    'label' => $label,
                    'nilai' => round($persentase, 2),
                    'jumlah' => $totalTransaksi > 0 ? (int) round(($persentase / 100) * $totalTransaksi) : 0,
                ];
            })
            ->values();
    }

    $allRules = collect($rules ?? [])
        ->map(function ($rule) use ($normalizeRuleKanal, $formatKanalLabel) {
            if ($rule instanceof \Illuminate\Contracts\Support\Arrayable) {
                $rule = $rule->toArray();
            } elseif (is_object($rule)) {
                $rule = get_object_vars($rule);
            }

            $rule = is_array($rule) ? $rule : [];

            $kanal = $normalizeRuleKanal(
                data_get($rule, 'kanal_filter')
                ?? data_get($rule, 'kanal')
                ?? data_get($rule, 'channel')
                ?? data_get($rule, 'tipe_penjualan')
                ?? ''
            );

            $rule['kanal_filter'] = $kanal;
            $rule['kanal_label'] = $formatKanalLabel($kanal);

            return $rule;
        })
        ->values();

    $totalRulesTerbentuk = (int) ($summary['association_rules'] ?? $allRules->count());

    $isDashboardRuleAnomaly = function ($rule) {
        $statusAnomali = strtolower(trim((string) data_get($rule, 'status_anomali', '')));
        $isAnomaly = data_get($rule, 'is_anomaly', false);

        return $statusAnomali === 'anomali'
            || $isAnomaly === true
            || $isAnomaly === 1
            || $isAnomaly === '1'
            || strtolower((string) $isAnomaly) === 'true';
    };

    $normalRulesDashboard = $allRules
        ->reject(function ($rule) use ($isDashboardRuleAnomaly) {
            return $isDashboardRuleAnomaly($rule);
        })
        ->values();

    $totalRulesNormalDashboard = $normalRulesDashboard->count();

    $jumlahRulesDashboard = $totalRulesNormalDashboard > 0
        ? max(1, (int) ceil($totalRulesNormalDashboard * 0.10))
        : 0;

    $topRulesDashboard = $normalRulesDashboard
        ->sortByDesc(function ($rule) {
            return (float) data_get($rule, 'lift', 0);
        })
        ->take($jumlahRulesDashboard)
        ->values();

    $ruleUtama = $topRulesDashboard->first();

    $ruleTerkuatDashboard = $ruleUtama
        ? ((data_get($ruleUtama, 'antecedents', '-') ?: '-') . ' → ' . (data_get($ruleUtama, 'consequents', '-') ?: '-'))
        : 'Belum ada rule normal';

    $bersihkanTeksPola = function ($teks) {
        $teks = trim((string) $teks);

        if ($teks === '' || $teks === '-') {
            return 'belum tersedia';
        }

        $teks = str_replace(['Waktu:', 'waktu:', 'Waktu :', 'waktu :'], 'waktu ', $teks);
        $teks = str_replace(['Operator:', 'operator:', 'Operator :', 'operator :'], 'operator ', $teks);
        $teks = preg_replace('/\s+/', ' ', $teks);

        return trim($teks);
    };

    $antecedentUtama = $bersihkanTeksPola(data_get($ruleUtama, 'antecedents', '-'));
    $consequentUtama = $bersihkanTeksPola(data_get($ruleUtama, 'consequents', '-'));

    $confidenceUtama = (float) data_get($ruleUtama, 'confidence', 0);
    $confidenceUtamaPersen = number_format($confidenceUtama * 100, 2, ',', '.');

    $totalTransaksi = (int) ($summary['total_basket'] ?? 0);

    $waktuDominan = $distribusiWaktuChart
        ->sortByDesc(function ($item) {
            return (float) data_get($item, 'nilai', 0);
        })
        ->first();

    $labelWaktuDominan = data_get($waktuDominan, 'label', '-');
    $persentaseWaktuDominan = (float) data_get($waktuDominan, 'nilai', 0);

    $jumlahTransaksiWaktuDominan = (int) data_get($waktuDominan, 'jumlah', 0);

    if ($jumlahTransaksiWaktuDominan <= 0 && $totalTransaksi > 0) {
        $jumlahTransaksiWaktuDominan = (int) round(($persentaseWaktuDominan / 100) * $totalTransaksi);
    }

    $rangeWaktuDominan = $shiftRange[$labelWaktuDominan] ?? null;
@endphp

<style>
    .association-dashboard .kanal-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 13px;
        border-radius: 999px;
        background: #f3e8ff;
        color: #7e22ce;
        border: 1px solid #d8b4fe;
        font-size: 12px;
        font-weight: 900;
        line-height: 1.2;
        white-space: nowrap;
    }

    .association-dashboard .association-table th,
    .association-dashboard .association-table td {
        vertical-align: middle;
    }

    .association-dashboard .association-table th:nth-child(1),
    .association-dashboard .association-table td:nth-child(1) {
        text-align: center;
        white-space: nowrap;
    }
</style>

<div class="association-dashboard">

    <div class="dashboard-header">
        <h1>Analisis Transaksi Penjualan</h1>
        <p>Sistem ini membantu menganalisis pola hubungan pada transaksi penjualan berdasarkan produk, operator, dan waktu pembelian.</p>
    </div>

    <div class="metric-grid">
        <div class="metric-card">
            <div>
                <span>Total Transaksi</span>
                <strong>{{ number_format($summary['total_basket'] ?? 0) }}</strong>
            </div>
            <div class="metric-icon pink">🛒</div>
        </div>

        <div class="metric-card">
            <div>
                <span>Total Produk</span>
                <strong>{{ number_format($summary['produk_unik'] ?? 0) }}</strong>
            </div>
            <div class="metric-icon pink">📦</div>
        </div>

        <div class="metric-card">
            <div>
                <span>Total Operator</span>
                <strong>{{ number_format($summary['total_operator'] ?? 0) }}</strong>
            </div>
            <div class="metric-icon pink">👥</div>
        </div>

        <div class="metric-card">
            <div>
                <span>Total Pola Hubungan</span>
                <strong>{{ number_format($summary['association_rules'] ?? 0) }}</strong>
            </div>
            <div class="metric-icon green">↗</div>
        </div>

        <div class="metric-card rule-best-card">
            <div class="rule-best-content">
                <span>Pola Terkuat</span>
                <strong class="rule-best-text">
                    {{ $ruleTerkuatDashboard }}
                </strong>
            </div>

            <div class="metric-icon yellow">🎖</div>
        </div>
    </div>

    <div class="dashboard-card">
        <h2>Ringkasan Dataset Terakhir</h2>

        <div class="dataset-grid">
            <div class="dataset-item">
                <span>Nama File</span>
                <strong>{{ $dataset['nama_file'] ?? '-' }}</strong>
            </div>

            <div class="dataset-item">
                <span>Tanggal Analisis</span>
                <strong>{{ $dataset['tanggal_analisis'] ?? '-' }}</strong>
            </div>

            <div class="dataset-item">
                <span>Jumlah Data Awal</span>
                <strong>{{ number_format((int) ($dataset['jumlah_data_awal'] ?? 0), 0, ',', '.') }}</strong>
            </div>

            <div class="dataset-item">
                <span>Data Setelah Dibersihkan</span>
                <strong>{{ number_format((int) ($dataset['data_setelah_preprocessing'] ?? 0), 0, ',', '.') }}</strong>
            </div>

            <div class="dataset-item">
                <span>Produk Unik</span>
                <strong>{{ number_format((int) ($summary['produk_unik'] ?? 0), 0, ',', '.') }}</strong>
            </div>

            <div class="dataset-item">
                <span>Transaksi yang Akan Diproses</span>
                <strong>{{ number_format((int) ($dataset['basket_transaksi_terbentuk'] ?? 0), 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div class="chart-row">
        <div class="dashboard-card chart-card">
            <h2>Top 10 Produk Terlaris</h2>

            <div class="bar-chart-wrapper">
                <canvas id="topProdukChart"></canvas>
            </div>
        </div>

        <div class="dashboard-card chart-card">
            <h2>Distribusi Transaksi Berdasarkan Waktu</h2>

            <div class="pie-chart-wrapper">
                <canvas id="waktuChart"></canvas>
            </div>
        </div>
    </div>

    <div class="dashboard-card rules-card">
        <h2>Pola Pembelian Teratas</h2>

        <div class="table-wrapper">
            <table class="association-table">
                <thead>
                    <tr>
                        <th>Kanal</th>
                        <th>Kondisi Transaksi</th>
                        <th>Pola yang Berkaitan</th>
                        <th>Tingkat Kemunculan</th>
                        <th>Tingkat Kepercayaan</th>
                        <th>Kekuatan Hubungan</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($topRulesDashboard as $rule)
                        @php
                            $ruleKanal = $rule['kanal_filter'] ?? 'unknown';
                            $ruleKanalLabel = $rule['kanal_label'] ?? $formatKanalLabel($ruleKanal);
                        @endphp

                        <tr>
                            <td>
                                <span class="kanal-badge">
                                    {{ $ruleKanalLabel }}
                                </span>
                            </td>

                            <td>{{ $rule['antecedents'] ?? '-' }}</td>
                            <td>{{ $rule['consequents'] ?? '-' }}</td>
                            <td>{{ number_format((float) ($rule['support'] ?? 0), 4) }}</td>
                            <td>{{ number_format((float) ($rule['confidence'] ?? 0), 4) }}</td>
                            <td>
                                <span class="lift-badge">
                                    {{ number_format((float) ($rule['lift'] ?? 0), 4) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-table-message">
                                Belum ada association rules normal yang dapat ditampilkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-card insight-card">
        <h2>↗ Insight Operasional</h2>

        <ul>
            @if ($ruleUtama)
                <li>
                    <strong>Pola Pembelian Terkuat:</strong>
                    Data menunjukkan bahwa transaksi dengan <strong>{{ $antecedentUtama }}</strong>
                    paling sering berkaitan dengan <strong>{{ $consequentUtama }}</strong>.
                    Pola ini memiliki tingkat kepercayaan sebesar <strong>{{ $confidenceUtamaPersen }}%</strong>.
                </li>
            @else
                <li>
                    <strong>Pola Pembelian Terkuat:</strong>
                    Belum ada pola pembelian yang dapat ditampilkan.
                </li>
            @endif

            <li>
                <strong>Produk Terlaris:</strong>
                Produk paling sering dibeli adalah <strong>{{ $topProdukChart->first()['nama'] ?? 'belum tersedia' }}</strong>.
            </li>

            @if ($waktuDominan && $persentaseWaktuDominan > 0)
                <li>
                    <strong>Waktu Transaksi Optimal:</strong>
                    Transaksi terbanyak terjadi pada shift <strong>{{ $labelWaktuDominan }}</strong>
                    @if ($rangeWaktuDominan)
                        pukul <strong>{{ $rangeWaktuDominan }}</strong>
                    @endif
                    dengan <strong>{{ number_format($jumlahTransaksiWaktuDominan, 0, ',', '.') }}</strong> transaksi.
                </li>
            @else
                <li>
                    <strong>Waktu Transaksi Optimal:</strong>
                    Belum ada distribusi waktu transaksi yang dapat ditampilkan.
                </li>
            @endif

            <li>
                <strong>Pola Teratas:</strong>
                Dashboard menampilkan <strong>{{ $topRulesDashboard->count() }}</strong> pola asosiasi normal teratas berdasarkan tingkat kekuatan hubungan dari total <strong>{{ number_format($totalRulesNormalDashboard, 0, ',', '.') }}</strong> pola normal yang terbentuk.
            </li>
        </ul>
    </div>

    <div class="dashboard-actions">
        @if (!$isManajer)
            <a href="{{ route('asosiasi.analisis') }}" class="btn-action primary">
                <span class="btn-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19h16"></path>
                        <path d="M7 16V8"></path>
                        <path d="M12 16V5"></path>
                        <path d="M17 16v-4"></path>
                    </svg>
                </span>
                <span>Mulai Analisis Data</span>
            </a>
        @endif

        <a href="{{ route('asosiasi.riwayat') }}" class="btn-action secondary">
            <span class="btn-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12a9 9 0 1 0 3-6.7"></path>
                    <path d="M3 4v5h5"></path>
                    <path d="M12 7v6l4 2"></path>
                </svg>
            </span>
            <span>Lihat Riwayat Analisis</span>
        </a>

        <a href="{{ route('asosiasi.download') }}" class="btn-action secondary">
            <span class="btn-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 3v12"></path>
                    <path d="M7 10l5 5 5-5"></path>
                    <path d="M5 21h14"></path>
                </svg>
            </span>
            <span>Unduh Laporan</span>
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const topProdukLabels = @json($topProdukChart->pluck('nama'));
const topProdukValues = @json($topProdukChart->pluck('jumlah'));

const waktuLabels = @json($distribusiWaktuChart->pluck('label'));
const waktuValues = @json($distribusiWaktuChart->pluck('nilai'));

const waktuRange = {
    'pagi': '08.00 - 12.59',
    'siang': '13.00 - 22.00'
};

function getWaktuRange(label) {
    const key = String(label).toLowerCase().trim();

    return waktuRange[key] || '';
}

function formatPercent(value) {
    const numberValue = Number(value || 0);

    return numberValue.toFixed(2);
}

const topProdukCanvas = document.getElementById('topProdukChart');

if (topProdukCanvas) {
    new Chart(topProdukCanvas, {
        type: 'bar',
        data: {
            labels: topProdukLabels,
            datasets: [{
                data: topProdukValues,
                backgroundColor: '#e8007a',
                borderRadius: 6,
                barThickness: 42
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f3f4f6'
                    }
                }
            }
        }
    });
}

const waktuCanvas = document.getElementById('waktuChart');

if (waktuCanvas) {
    new Chart(waktuCanvas, {
        type: 'pie',
        data: {
            labels: waktuLabels.map((label, index) => {
                const range = getWaktuRange(label);
                const value = formatPercent(waktuValues[index]);

                if (range) {
                    return `${label} (${range}) ${value}%`;
                }

                return `${label} ${value}%`;
            }),
            datasets: [{
                data: waktuValues,
                backgroundColor: ['#e8007a', '#f9a8d4'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label;
                        }
                    }
                }
            }
        }
    });
}
</script>
@endpush