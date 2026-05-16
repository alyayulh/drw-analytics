@extends('layouts.app')

@section('content')

@php
    $ruleTerbaik = $riwayat['rule_terbaik'] ?? null;

    if (empty($ruleTerbaik) && !empty($rules)) {
        $bestRule = collect($rules)->sortByDesc('lift')->first();

        $ruleTerbaik = ($bestRule['antecedents'] ?? '-') . ' → ' . ($bestRule['consequents'] ?? '-');
    }
@endphp

<div class="detail-riwayat-page hasil-page">

    <a href="{{ route('asosiasi.riwayat') }}" class="back-link">
        ← Kembali ke Riwayat
    </a>

    <div class="page-header">
        <h1>Detail Riwayat Analisis #{{ $riwayat['id'] }}</h1>
        <p>Hasil analisis asosiasi yang telah disimpan</p>
    </div>

    <div class="detail-card">
        <h3>Informasi File dan Analisis</h3>

        <div class="info-grid">
            <div class="info-box">
                <p>Tanggal Analisis</p>
                <h4>{{ $riwayat['tanggal_analisis'] }}</h4>
            </div>

            <div class="info-box">
                <p>Nama File</p>
                <h4>{{ $riwayat['nama_file'] }}</h4>
            </div>

            <div class="info-box">
                <p>Periode Data</p>
                <h4>{{ $riwayat['periode_data'] }}</h4>
            </div>
        </div>
    </div>

    <div class="hasil-card summary-card">
        <h3>Ringkasan Hasil Analisis</h3>

        <div class="summary-grid">
            <div class="summary-box">
                <p>Total Data Awal</p>
                <h4>{{ number_format($riwayat['total_data_awal']) }}</h4>
            </div>

            <div class="summary-box">
                <p>Data Setelah Dibersihkan</p>
                <h4>{{ number_format($riwayat['setelah_preprocessing']) }}</h4>
            </div>

            <div class="summary-box">
                <p>Total Transaksi Akhir</p>
                <h4>{{ number_format($riwayat['total_basket']) }}</h4>
            </div>

            <div class="summary-box">
                <p>Produk Unik</p>
                <h4>{{ number_format($riwayat['produk_unik']) }}</h4>
            </div>

            <div class="summary-box">
                <p>Total Operator</p>
                <h4>{{ number_format($riwayat['total_operator']) }}</h4>
            </div>

            <div class="summary-box">
                <p>Frequent Itemsets</p>
                <h4>{{ number_format($riwayat['frequent_itemsets']) }}</h4>
            </div>

            <div class="summary-box">
                <p>Association Rules</p>
                <h4>{{ number_format($riwayat['association_rules']) }}</h4>
            </div>

            <div class="summary-box highlight">
                <p>Rule Terbaik</p>
                <h4>{{ $riwayat['rule_terbaik'] ?? 'Belum ada rule' }}</h4>
            </div>
        </div>
    </div>

    <div class="hasil-card anomaly-card">
        <div>
            <h3>Mode Deteksi Anomali</h3>
            <p>Aktifkan mode deteksi anomali untuk melihat rules dengan pola tidak biasa.</p>
        </div>

        <label class="switch">
            <input type="checkbox" id="toggleAnomali">
            <span class="slider"></span>
        </label>
    </div>

    <div class="hasil-card filter-card">
        <h3>Filter dan Pencarian Hasil</h3>

        <div class="filter-buttons">
            <button type="button" class="filter-btn active" data-filter="semua">Semua</button>
            <button type="button" class="filter-btn" data-filter="produk_operator">Produk × Operator</button>
            <button type="button" class="filter-btn" data-filter="produk_produk">Produk × Produk</button>
            <button type="button" class="filter-btn" data-filter="operator_waktu">Operator × Waktu</button>
            <button type="button" class="filter-btn" data-filter="kategori_waktu">Kategori × Waktu</button>
        </div>

        <input type="text" id="searchInput" class="search-input" placeholder="Cari item...">
    </div>

    <div class="hasil-card table-card">
        <h3>Tabel Hasil Association Rules</h3>

        <div class="table-wrapper">
            <table>
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

                <tbody id="rulesTableBody">
                    @foreach ($rules as $rule)
                        <tr class="{{ strtolower($rule['status'] ?? '') === 'anomali' ? 'row-anomaly' : 'row-normal' }}"
                            data-status="{{ strtolower($rule['status'] ?? 'normal') }}"
                            data-kategori="{{ strtolower($rule['kategori_rule'] ?? 'semua') }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $rule['antecedents'] ?? '-' }}</td>
                            <td>{{ $rule['consequents'] ?? '-' }}</td>
                            <td>{{ $rule['support'] ?? '-' }}</td>
                            <td>{{ $rule['confidence'] ?? '-' }}</td>
                            <td>{{ $rule['lift'] ?? '-' }}</td>
                            <td>{{ $rule['operator'] ?? '-' }}</td>
                            <td>{{ $rule['kategori_waktu'] ?? '-' }}</td>
                            <td>
                                @if(strtolower($rule['status'] ?? '') === 'anomali')
                                    <span class="status-badge status-anomaly">Anomali</span>
                                @else
                                    <span class="status-badge status-normal">Normal</span>
                                @endif
                            </td>
                            <td>{{ $rule['interpretasi'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleAnomali = document.getElementById('toggleAnomali');
        const searchInput = document.getElementById('searchInput');
        const filterButtons = document.querySelectorAll('.filter-btn');
        const rows = document.querySelectorAll('#rulesTableBody tr');

        let activeFilter = 'semua';

        function filterRows() {
            const searchValue = searchInput.value.toLowerCase();
            const anomalyOnly = toggleAnomali.checked;

            rows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const rowStatus = row.dataset.status;
                const rowKategori = row.dataset.kategori;

                const matchSearch = rowText.includes(searchValue);
                const matchAnomaly = !anomalyOnly || rowStatus === 'anomali';
                const matchFilter = activeFilter === 'semua' || rowKategori === activeFilter;

                row.style.display = matchSearch && matchAnomaly && matchFilter ? '' : 'none';
            });
        }

        if (toggleAnomali) {
            toggleAnomali.addEventListener('change', filterRows);
        }

        if (searchInput) {
            searchInput.addEventListener('keyup', filterRows);
        }

        filterButtons.forEach(button => {
            button.addEventListener('click', function () {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');

                activeFilter = this.dataset.filter;
                filterRows();
            });
        });
    });
</script>
@endpush