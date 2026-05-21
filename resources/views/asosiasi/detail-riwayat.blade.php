@extends('layouts.app')

@section('content')

@php
    $rulesCollection = collect($rules ?? []);
    $summaryData = $summary ?? [];
    $datasetData = $dataset ?? [];

    $ruleTerbaik = $summaryData['rule_terbaik']
        ?? ($riwayat['rule_terbaik'] ?? null);

    if (empty($ruleTerbaik) && $rulesCollection->isNotEmpty()) {
        $bestRule = $rulesCollection->sortByDesc('lift')->first();
        $ruleTerbaik = ($bestRule['antecedents'] ?? '-') . ' → ' . ($bestRule['consequents'] ?? '-');
    }

    $jumlahAnomali = $summaryData['jumlah_anomali']
        ?? $rulesCollection->filter(function ($rule) {
            $value = $rule['is_anomaly'] ?? false;

            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value === 1;
            }

            if (is_string($value)) {
                return in_array(strtolower($value), ['1', 'true', 'yes', 'ya', 'anomali'], true);
            }

            return false;
        })->count();

    $normalizeJenisRule = function ($jenisRule) {
        $jenisRule = strtolower((string) $jenisRule);

        $allowed = [
            'produk_operator',
            'produk_produk',
            'operator_waktu',
            'produk_waktu',
            'produk_operator_waktu',
        ];

        if (in_array($jenisRule, $allowed, true)) {
            return $jenisRule;
        }

        return 'produk_operator_waktu';
    };
@endphp

<div class="detail-riwayat-page hasil-page">

    <a href="{{ route('asosiasi.riwayat') }}" class="back-link">
        ← Kembali ke Riwayat
    </a>

    <div class="page-header">
        <h1>Detail Riwayat Analisis #{{ $riwayat['id'] ?? '-' }}</h1>
        <p>Hasil analisis asosiasi yang telah disimpan</p>
    </div>

    <div class="detail-card">
        <h3>Informasi File dan Analisis</h3>

        <div class="info-grid">
            <div class="info-box">
                <p>Tanggal Analisis</p>
                <h4>{{ $riwayat['tanggal_analisis'] ?? ($datasetData['tanggal_analisis'] ?? '-') }}</h4>
            </div>

            <div class="info-box">
                <p>Nama File</p>
                <h4>{{ $riwayat['nama_file'] ?? ($datasetData['nama_file'] ?? '-') }}</h4>
            </div>

            <div class="info-box">
                <p>Periode Data</p>
                <h4>{{ $riwayat['periode_data'] ?? ($datasetData['periode_data'] ?? '-') }}</h4>
            </div>
        </div>
    </div>

    <div class="hasil-card summary-card">
        <h3>Ringkasan Hasil Analisis</h3>

        <div class="summary-grid">
            <div class="summary-box">
                <p>Total Data Awal</p>
                <h4>
                    {{ number_format($summaryData['total_data_awal'] ?? ($riwayat['total_data_awal'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Data Setelah Dibersihkan</p>
                <h4>
                    {{ number_format($summaryData['setelah_preprocessing'] ?? ($riwayat['setelah_preprocessing'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Total Transaksi Akhir</p>
                <h4>
                    {{ number_format($summaryData['total_basket'] ?? ($riwayat['total_basket'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Produk Unik</p>
                <h4>
                    {{ number_format($summaryData['produk_unik'] ?? ($riwayat['produk_unik'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Total Operator</p>
                <h4>
                    {{ number_format($summaryData['total_operator'] ?? ($riwayat['total_operator'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Frequent Itemsets</p>
                <h4>
                    {{ number_format($summaryData['frequent_itemsets'] ?? ($riwayat['frequent_itemsets'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Association Rules</p>
                <h4>
                    {{ number_format($summaryData['association_rules'] ?? ($riwayat['association_rules'] ?? $rulesCollection->count()), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Jumlah Anomali</p>
                <h4>
                    {{ number_format($jumlahAnomali, 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box highlight">
                <p>Rule Terbaik</p>
                <h4>{{ $ruleTerbaik ?? 'Belum ada rule' }}</h4>
            </div>
        </div>
    </div>

    <div class="hasil-card anomaly-card">
        <div>
            <h3>Mode Deteksi Anomali</h3>
            <p>Aktifkan mode deteksi anomali untuk melihat rule yang terdeteksi sebagai anomali berdasarkan model.</p>
        </div>

        <label class="switch">
            <input type="checkbox" id="toggleAnomali">
            <span class="slider"></span>
        </label>
    </div>

    <div class="hasil-card filter-card">
        <h3>Filter dan Pencarian Hasil</h3>

        <div class="filter-buttons">
            <button type="button" class="filter-btn active" data-filter="semua">
                Semua
            </button>

            <button type="button" class="filter-btn" data-filter="produk_operator">
                Produk × Operator
            </button>

            <button type="button" class="filter-btn" data-filter="produk_produk">
                Produk × Produk
            </button>

            <button type="button" class="filter-btn" data-filter="operator_waktu">
                Operator × Waktu
            </button>

            <button type="button" class="filter-btn" data-filter="produk_waktu">
                Produk × Waktu
            </button>

            <button type="button" class="filter-btn" data-filter="produk_operator_waktu">
                Produk × Operator × Waktu
            </button>
        </div>

        <input
            type="text"
            id="searchInput"
            class="search-input"
            placeholder="Cari item, rule, kategori, atau status anomali..."
        >
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
                        <th>Kategori Rule</th>
                        <th>Deteksi Anomali</th>
                        <th>Interpretasi</th>
                    </tr>
                </thead>

                <tbody id="rulesTableBody">
                    @forelse ($rulesCollection as $rule)
                        @php
                            $antecedents = $rule['antecedents'] ?? '-';
                            $consequents = $rule['consequents'] ?? '-';

                            $support = $rule['support'] ?? 0;
                            $confidence = $rule['confidence'] ?? 0;
                            $lift = $rule['lift'] ?? 0;

                            $kategoriRule = $rule['kategori_rule'] ?? ($rule['status'] ?? 'Weak Pattern');

                            $jenisRule = $normalizeJenisRule($rule['jenis_rule'] ?? 'produk_operator_waktu');

                            $isAnomaly = $rule['is_anomaly'] ?? false;

                            if (is_string($isAnomaly)) {
                                $isAnomaly = in_array(strtolower($isAnomaly), ['1', 'true', 'yes', 'ya', 'anomali'], true);
                            } elseif (is_numeric($isAnomaly)) {
                                $isAnomaly = (int) $isAnomaly === 1;
                            } else {
                                $isAnomaly = (bool) $isAnomaly;
                            }

                            $statusAnomali = $isAnomaly ? 'Anomali' : 'Normal';

                            $interpretasi = $rule['interpretasi'] ?? (
                                'Jika terdapat ' . $antecedents .
                                ', maka cenderung berasosiasi dengan ' . $consequents .
                                ' dengan confidence ' . number_format((float) $confidence * 100, 2) .
                                '% dan lift ' . number_format((float) $lift, 2) .
                                '. Kategori rule: ' . $kategoriRule . '.'
                            );

                            $searchText = strtolower(
                                $antecedents . ' ' .
                                $consequents . ' ' .
                                $kategoriRule . ' ' .
                                $statusAnomali . ' ' .
                                $interpretasi
                            );
                        @endphp

                        <tr class="rule-row {{ $isAnomaly ? 'row-anomaly' : 'row-normal' }}"
                            data-jenis="{{ $jenisRule }}"
                            data-anomali="{{ $isAnomaly ? 'anomali' : 'normal' }}"
                            data-search="{{ e($searchText) }}">
                            <td class="row-number">{{ $loop->iteration }}</td>

                            <td>{{ $antecedents }}</td>

                            <td>{{ $consequents }}</td>

                            <td>
                                {{ is_numeric($support) ? number_format((float) $support, 4) : $support }}
                            </td>

                            <td>
                                {{ is_numeric($confidence) ? number_format((float) $confidence, 4) : $confidence }}
                            </td>

                            <td>
                                {{ is_numeric($lift) ? number_format((float) $lift, 4) : $lift }}
                            </td>

                            <td>
                                <span class="rule-category">
                                    {{ $kategoriRule }}
                                </span>
                            </td>

                            <td>
                                <span class="anomali-badge {{ $isAnomaly ? 'anomali' : 'normal' }}">
                                    {{ $statusAnomali }}
                                </span>
                            </td>

                            <td class="interpretasi-cell">
                                {{ $interpretasi }}
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyRulesRow">
                            <td colspan="9" style="text-align: center; padding: 32px;">
                                Data rules tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse

                    @if ($rulesCollection->isNotEmpty())
                        <tr id="noResultRow" style="display: none;">
                            <td colspan="9" style="text-align: center; padding: 32px;">
                                Data rules tidak ditemukan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const searchInput = document.getElementById('searchInput');
        const toggleAnomali = document.getElementById('toggleAnomali');
        const rows = Array.from(document.querySelectorAll('.rule-row'));
        const noResultRow = document.getElementById('noResultRow');

        let activeFilter = 'semua';

        function normalizeText(value) {
            return (value || '').toString().toLowerCase().trim();
        }

        function applyFilter() {
            const keyword = normalizeText(searchInput ? searchInput.value : '');
            const anomalyOnly = toggleAnomali ? toggleAnomali.checked : false;

            let visibleCount = 0;

            rows.forEach(function (row) {
                const rowJenis = row.dataset.jenis || '';
                const rowAnomali = row.dataset.anomali || 'normal';
                const rowSearch = row.dataset.search || '';

                const matchFilter = activeFilter === 'semua' || rowJenis === activeFilter;
                const matchSearch = keyword === '' || rowSearch.includes(keyword);
                const matchAnomali = !anomalyOnly || rowAnomali === 'anomali';

                if (matchFilter && matchSearch && matchAnomali) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            let number = 1;

            rows.forEach(function (row) {
                if (row.style.display !== 'none') {
                    const numberCell = row.querySelector('.row-number');

                    if (numberCell) {
                        numberCell.textContent = number;
                    }

                    number++;
                }
            });

            if (noResultRow) {
                noResultRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                filterButtons.forEach(function (btn) {
                    btn.classList.remove('active');
                });

                button.classList.add('active');
                activeFilter = button.dataset.filter || 'semua';

                applyFilter();
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', applyFilter);
        }

        if (toggleAnomali) {
            toggleAnomali.addEventListener('change', applyFilter);
        }

        applyFilter();
    });
</script>

@endsection