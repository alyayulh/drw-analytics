@extends('layouts.app')

@section('title', 'Dashboard Analisis Asosiasi - DRW Analytics')

@section('content')

<div class="association-dashboard">

    <div class="dashboard-header">
        <h1>Dashboard Analisis Transaksi Penjualan</h1>
        <p>Menampilkan insight transaksi penjualan berdasarkan pola data historis</p>
    </div>

    <div class="metric-grid">
        <div class="metric-card">
            <div>
                <span>Total Transaksi</span>
                <strong>{{ number_format($summary['total_basket']) }}</strong>
            </div>
            <div class="metric-icon pink">🛒</div>
        </div>

        <div class="metric-card">
            <div>
                <span>Total Produk</span>
                <strong>{{ number_format($summary['produk_unik']) }}</strong>
            </div>
            <div class="metric-icon pink">📦</div>
        </div>

        <div class="metric-card">
            <div>
                <span>Total Operator</span>
                <strong>{{ number_format($summary['total_operator']) }}</strong>
            </div>
            <div class="metric-icon pink">👥</div>
        </div>

        <div class="metric-card">
            <div>
                <span>Total Rules Asosiasi</span>
                <strong>{{ number_format($summary['association_rules']) }}</strong>
            </div>
            <div class="metric-icon green">↗</div>
        </div>

        <div class="metric-card">
    <div>
        <span>Rule Terbaik</span>
        <strong>{{ $summary['rule_terbaik'] ?? 'Belum ada rule' }}</strong>
    </div>

    <div class="metric-icon yellow">🎖</div>
</div>
    </div>

    <div class="dashboard-card">
        <h2>Ringkasan Dataset Terakhir</h2>

        <div class="dataset-grid">
            <div class="dataset-item">
                <span>Nama File</span>
                <strong>{{ $dataset['nama_file'] }}</strong>
            </div>

            <div class="dataset-item">
                <span>Periode Data</span>
                <strong>{{ $dataset['periode_data'] }}</strong>
            </div>

            <div class="dataset-item">
                <span>Jumlah Data Awal</span>
                <strong>{{ $dataset['jumlah_data_awal'] }}</strong>
            </div>

            <div class="dataset-item">
                <span>Data Setelah Dibersihkan</span>
                <strong>{{ $dataset['data_setelah_preprocessing'] }}</strong>
            </div>

            <div class="dataset-item">
                <span>Transaksi Refund Dihapus</span>
                <strong>{{ $dataset['transaksi_refund_dihapus'] }}</strong>
            </div>

            <div class="dataset-item">
                <span>Transaksi yang Akan Diproses</span>
                <strong>{{ $dataset['basket_transaksi_terbentuk'] }}</strong>
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

    <div class="dashboard-card rules-card">
        <h2>Top Association Rules</h2>

        <div class="table-wrapper">
            <table class="association-table">
                <thead>
                    <tr>
                        <th>Antecedents</th>
                        <th>Consequents</th>
                        <th>Support</th>
                        <th>Confidence</th>
                        <th>Lift</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules as $rule)
                        <tr>
                            <td>{{ $rule['antecedents'] }}</td>
                            <td>{{ $rule['consequents'] }}</td>
                            <td>{{ $rule['support'] }}</td>
                            <td>{{ $rule['confidence'] }}</td>
                            <td>
                                <span class="lift-badge">{{ $rule['lift'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="dashboard-card insight-card">
        <h2>↗ Insight Operasional</h2>

        <ul>
            <li><strong>Pola Pembelian Terkuat:</strong> Pelanggan yang membeli Serum Wajah A cenderung membeli Moisturizer B dengan tingkat kepercayaan 85%.</li>
            <li><strong>Waktu Transaksi Optimal:</strong> Transaksi terbanyak terjadi pada waktu siang (12:00-17:00) dengan 412 transaksi.</li>
            <li><strong>Rekomendasi:</strong> Pertimbangkan untuk menempatkan produk-produk dengan asosiasi tinggi berdekatan atau membuat paket bundling.</li>
        </ul>
    </div>

    <div class="dashboard-actions">
        <a href="{{ route('asosiasi.analisis') }}" class="btn-action primary">
            ▣ Mulai Analisis Data
        </a>

        <a href="{{ route('asosiasi.riwayat') }}" class="btn-action secondary">
            ↺ Lihat Riwayat Analisis
        </a>

        <a href="{{ route('asosiasi.download') }}" class="btn-action secondary">
            ⇩ Unduh Laporan
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const topProdukLabels = @json($topProduk->pluck('nama'));
const topProdukValues = @json($topProduk->pluck('jumlah'));

const waktuLabels = @json($distribusiWaktu->pluck('label'));
const waktuValues = @json($distribusiWaktu->pluck('nilai'));

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
                barThickness: 48
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 10 }
                    },
                    grid: { display: false }
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
            labels: waktuLabels.map((label, index) => `${label} ${waktuValues[index]}%`),
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
                }
            }
        }
    });
}  
</script>
@endpush