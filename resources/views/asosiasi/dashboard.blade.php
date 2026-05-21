@extends('layouts.app')

@section('title', 'Dashboard Insight - DRW Skincare Analytics')

@section('content')

@php
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

    $distribusiWaktuChart = collect($distribusiWaktu ?? [])->values();

    $allRules = collect($rules ?? [])->values();

    $totalRulesTerbentuk = (int) ($summary['association_rules'] ?? $allRules->count());

    $jumlahRulesDashboard = $totalRulesTerbentuk > 0
        ? max(1, (int) ceil($totalRulesTerbentuk * 0.10))
        : 0;

    $topRulesDashboard = $allRules
        ->sortByDesc(function ($rule) {
            return (float) data_get($rule, 'lift', 0);
        })
        ->take($jumlahRulesDashboard)
        ->values();

    $ruleUtama = $topRulesDashboard->first();

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

    $jumlahTransaksiWaktuDominan = $totalTransaksi > 0
        ? (int) round(($persentaseWaktuDominan / 100) * $totalTransaksi)
        : 0;

    $rangeWaktu = [
        'pagi' => '00.00 - 11.59',
        'siang' => '12.00 - 17.59',
        'malam' => '18.00 - 23.59',
    ];

    $rangeWaktuDominan = $rangeWaktu[strtolower($labelWaktuDominan)] ?? null;
@endphp

<div class="association-dashboard">

    <div class="dashboard-header">
        <h1>Dashboard Analisis Transaksi Penjualan</h1>
        <p>Menampilkan insight transaksi penjualan berdasarkan pola data historis</p>
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
                <span>Total Pola Pembelian</span>
                <strong>{{ number_format($summary['association_rules'] ?? 0) }}</strong>
            </div>
            <div class="metric-icon green">↗</div>
        </div>

        <div class="metric-card rule-best-card">
    <div class="rule-best-content">
        <span>Pola Terkuat</span>
        <strong class="rule-best-text">
            {{ $summary['rule_terbaik'] ?? 'Belum ada rule' }}
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
                <span>Transaksi Refund Dihapus</span>
                <strong>{{ number_format((int) ($dataset['transaksi_refund_dihapus'] ?? 0), 0, ',', '.') }}</strong>
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
                        <th>Kondisi Transaksi</th>
                        <th>Pola yang Berkaitan</th>
                        <th>Frekuensi Kemunculan</th>
                        <th>Tingkat Kecenderungan</th>
                        <th>Kekuatan Pola</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($topRulesDashboard as $rule)
                        <tr>
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
                            <td colspan="5" class="empty-table-message">
                                Belum ada association rules.
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
                    Pola ini memiliki tingkat kecenderungan sebesar <strong>{{ $confidenceUtamaPersen }}%</strong>.
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

            @if ($waktuDominan)
                <li>
                    <strong>Waktu Transaksi Optimal:</strong>
                    Transaksi terbanyak terjadi pada waktu <strong>{{ $labelWaktuDominan }}</strong>
                    @if ($rangeWaktuDominan)
                        pukul <strong>{{ $rangeWaktuDominan }}</strong>
                    @endif
                    dengan <strong>{{ number_format($jumlahTransaksiWaktuDominan, 0, ',', '.') }}</strong> transaksi.
                </li>
            @endif

            <li>
                <strong>Pola Teratas:</strong>
                Dashboard menampilkan <strong>{{ $topRulesDashboard->count() }}</strong> pola asosiasi teratas berdasarkan tingkat kekuatan hubungan dari total <strong>{{ number_format($totalRulesTerbentuk, 0, ',', '.') }}</strong> pola yang terbentuk.
            </li>
        </ul>
    </div>

    <div class="dashboard-actions">
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
    'pagi': '00.00 - 11.59',
    'siang': '12.00 - 17.59',
    'malam': '18.00 - 23.59'
};

function getWaktuRange(label) {
    const key = String(label).toLowerCase().trim();

    return waktuRange[key] || '';
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
                const value = waktuValues[index];

                if (range) {
                    return `${label} (${range}) ${value}%`;
                }

                return `${label} ${value}%`;
            }),
            datasets: [{
                data: waktuValues,
                backgroundColor: ['#e8007a', '#f472b6', '#f9a8d4', '#fbcfe8'],
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