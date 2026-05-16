@extends('layouts.app')

@section('title', 'Hasil Analisis - DRW Skincare Analytics')

@section('content')

<div class="hasil-page">

    <div class="hasil-card summary-card">
        <h2>Ringkasan Hasil Analisis</h2>

        <div class="summary-grid">
            <div class="summary-item">
                <span>Total Data Awal</span>
                <strong>{{ number_format($summary['total_data_awal']) }}</strong>
            </div>

            <div class="summary-item">
                <span>Setelah Dibersihlan</span>
                <strong>{{ number_format($summary['setelah_preprocessing']) }}</strong>
            </div>

            <div class="summary-item">
                <span>Total Transaksi Akhir</span>
                <strong>{{ number_format($summary['total_basket']) }}</strong>
            </div>

            <div class="summary-item">
                <span>Produk Unik</span>
                <strong>{{ number_format($summary['produk_unik']) }}</strong>
            </div>

            <div class="summary-item">
                <span>Total Operator</span>
                <strong>{{ number_format($summary['total_operator']) }}</strong>
            </div>

            <div class="summary-item">
                <span>Frequent Itemsets</span>
                <strong>{{ number_format($summary['frequent_itemsets']) }}</strong>
            </div>

            <div class="summary-item">
                <span>Association Rules</span>
                <strong>{{ number_format($summary['association_rules']) }}</strong>
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
            <p>Aktifkan mode deteksi anomali untuk melihat rule dengan pola tidak biasa.</p>
        </div>

        <label class="switch">
            <input type="checkbox" id="toggleAnomaly">
            <span class="slider"></span>
        </label>
    </div>

    <div class="hasil-card filter-card">
        <h2>Filter dan Pencarian Hasil</h2>

        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Semua</button>
            <button class="filter-tab" data-filter="produk-operator">Produk × Operator</button>
            <button class="filter-tab" data-filter="produk-produk">Produk × Produk</button>
            <button class="filter-tab" data-filter="operator-waktu">Operator × Waktu</button>
            <button class="filter-tab" data-filter="kategori-waktu">Kategori × Waktu</button>
        </div>

        <input type="text" id="searchRules" class="search-input" placeholder="Cari item...">
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
                        <th>Operator</th>
                        <th>Kategori Waktu</th>
                        <th>Status</th>
                        <th>Interpretasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rules as $rule)
                            <tr class="{{ strtolower($rule['status'] ?? '') === 'anomali' ? 'row-anomaly' : 'row-normal' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rule['antecedents'] }}</td>
                            <td>{{ $rule['consequents'] }}</td>
                            <td>{{ $rule['support'] }}</td>
                            <td>{{ $rule['confidence'] }}</td>
                            <td>{{ $rule['lift'] }}</td>
                            <td>{{ $rule['operator'] }}</td>
                            <td>{{ $rule['kategori_waktu'] }}</td>
                            <td>
                                @if(strtolower($rule['status']) === 'anomali')
                                    <span class="status-badge status-anomaly">Anomali</span>
                                @else
                                    <span class="status-badge status-normal">Normal</span>
                                @endif
                            </td>
                            <td>{{ $rule['interpretasi'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="chart-grid">
        <div class="hasil-card chart-card">
            <h2>Bar Chart Top Rules Berdasarkan Lift Tertinggi</h2>
            <canvas id="liftChart" height="130"></canvas>
        </div>

        <div class="hasil-card chart-card">
            <h2>Heatmap Asosiasi</h2>
            <div class="heatmap-placeholder">
                <p>Heatmap menampilkan kekuatan hubungan asosiasi</p>
            </div>
        </div>
    </div>

    <div class="hasil-action-row">
        <button class="btn-primary-action">
            ⇩ Unduh Hasil
        </button>

        <a href="{{ route('asosiasi.riwayat') }}" class="btn-secondary-action">
            ↺ Lihat Riwayat Analisis
        </button>

        <a href="{{ route('asosiasi.dashboard') }}" class="btn-secondary-action">
            ↗ Lihat Dashboard
        </a>

    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('liftChart');

if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Rule 1', 'Rule 3', 'Rule 5'],
            datasets: [{
                label: 'Lift',
                data: [2.4, 1.9, 1.8],
                backgroundColor: '#e8007a',
                borderRadius: 6,
                barThickness: 70
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 8
                }
            }
        }
    });
}

const searchInput = document.getElementById('searchRules');
const rows = document.querySelectorAll('#rulesTable tbody tr');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const keyword = this.value.toLowerCase();

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });
}

const toggleAnomaly = document.getElementById('toggleAnomaly');

if (toggleAnomaly) {
    toggleAnomaly.addEventListener('change', function () {
        rows.forEach(row => {
            if (this.checked) {
                row.style.display = row.classList.contains('row-anomaly') ? '' : 'none';
            } else {
                row.style.display = '';
            }
        });
    });
}
</script>
@endpush