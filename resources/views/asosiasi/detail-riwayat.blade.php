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

    $isRuleAnomaly = function ($rule) {
        $statusAnomali = strtolower((string) ($rule['status_anomali'] ?? ''));
        $isAnomaly = $rule['is_anomaly'] ?? false;

        return $statusAnomali === 'anomali'
            || $isAnomaly === true
            || $isAnomaly === 1
            || $isAnomaly === '1'
            || strtolower((string) $isAnomaly) === 'true';
    };

    $jumlahAnomali = $summaryData['jumlah_anomali']
        ?? $rulesCollection->filter(function ($rule) use ($isRuleAnomaly) {
            return $isRuleAnomaly($rule);
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
        <p>Detail hasil analisis pola hubungan dari data transaksi yang telah disimpan</p>
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
                <p>Pola Sering Muncul</p>
                <h4>
                    {{ number_format($summaryData['frequent_itemsets'] ?? ($riwayat['frequent_itemsets'] ?? 0), 0, ',', '.') }}
                </h4>
            </div>

            <div class="summary-box">
                <p>Pola Hubungan</p>
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
                <p>Pola Terbaik</p>
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
        <h3>Tabel Hasil Analisis Pola Transaksi</h3>

        <div class="table-wrapper">
            <table class="detail-rules-table">
                <colgroup>
                    <col class="col-no">
                    <col class="col-kondisi">
                    <col class="col-pola">
                    <col class="col-support">
                    <col class="col-confidence">
                    <col class="col-lift">
                    <col class="col-kategori">
                    <col class="col-status">
                    <col class="col-interpretasi">
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

                <tbody id="rulesTableBody">
                    @forelse ($rulesCollection as $rule)
                        @php
                            $antecedents = $rule['antecedents'] ?? '-';
                            $consequents = $rule['consequents'] ?? '-';

                            $support = $rule['support'] ?? 0;
                            $confidence = $rule['confidence'] ?? 0;
                            $lift = $rule['lift'] ?? 0;

                            $kategoriRule = $rule['kategori_rule'] ?? ($rule['status'] ?? 'Weak Pattern');
                            $kategoriLower = strtolower((string) $kategoriRule);

                            if (str_contains($kategoriLower, 'strong')) {
                                $kategoriClass = 'status-strong';
                            } elseif (str_contains($kategoriLower, 'moderate')) {
                                $kategoriClass = 'status-moderate';
                            } else {
                                $kategoriClass = 'status-weak';
                            }

                            $jenisRule = $normalizeJenisRule($rule['jenis_rule'] ?? 'produk_operator_waktu');

                            $isAnomaly = $isRuleAnomaly($rule);
                            $statusAnomali = $isAnomaly ? 'Anomali' : 'Normal';

                            $interpretasi = $rule['interpretasi'] ?? (
                                'Jika terdapat ' . $antecedents .
                                ', maka cenderung berkaitan dengan ' . $consequents . '.'
                            );

                            $searchText = strtolower(
                                $antecedents . ' ' .
                                $consequents . ' ' .
                                $kategoriRule . ' ' .
                                $statusAnomali . ' ' .
                                $interpretasi
                            );
                        @endphp

                        <tr
                            class="rule-row {{ $isAnomaly ? 'row-anomaly' : 'row-normal' }}"
                            data-jenis="{{ $jenisRule }}"
                            data-anomali="{{ $isAnomaly ? 'anomali' : 'normal' }}"
                            data-search="{{ e($searchText) }}"
                        >
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
                                <span class="rule-category {{ $kategoriClass }}">
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
                                Data pola hubungan tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse

                    @if ($rulesCollection->isNotEmpty())
                        <tr id="noResultRow" style="display: none;">
                            <td colspan="9" style="text-align: center; padding: 32px;">
                                Data pola hubungan tidak ditemukan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($rulesCollection->isNotEmpty())
            <div class="riwayat-pagination" id="detailRulesPagination">
                <button type="button" id="prevRulesPage" class="riwayat-pagination-btn">
                    ‹ Sebelumnya
                </button>

                <span id="rulesPageInfo" class="riwayat-pagination-info">
                    1-10 dari {{ $rulesCollection->count() }}
                </span>

                <button type="button" id="nextRulesPage" class="riwayat-pagination-btn">
                    Berikutnya ›
                </button>
            </div>
        @endif
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const searchInput = document.getElementById('searchInput');
        const toggleAnomali = document.getElementById('toggleAnomali');
        const rows = Array.from(document.querySelectorAll('.rule-row'));
        const noResultRow = document.getElementById('noResultRow');

        const prevPageBtn = document.getElementById('prevRulesPage');
        const nextPageBtn = document.getElementById('nextRulesPage');
        const pageInfo = document.getElementById('rulesPageInfo');

        let activeFilter = 'semua';
        let currentPage = 1;
        const rowsPerPage = 10;
        let filteredRows = [];

        function normalizeText(value) {
            return (value || '').toString().toLowerCase().trim();
        }

        function getFilteredRows() {
            const keyword = normalizeText(searchInput ? searchInput.value : '');
            const anomalyOnly = toggleAnomali ? toggleAnomali.checked : false;

            return rows.filter(function (row) {
                const rowJenis = row.dataset.jenis || '';
                const rowAnomali = row.dataset.anomali || 'normal';
                const rowSearch = row.dataset.search || '';

                const matchFilter = activeFilter === 'semua' || rowJenis === activeFilter;
                const matchSearch = keyword === '' || rowSearch.includes(keyword);
                const matchAnomali = !anomalyOnly || rowAnomali === 'anomali';

                return matchFilter && matchSearch && matchAnomali;
            });
        }

        function hideAllRows() {
            rows.forEach(function (row) {
                row.style.display = 'none';
            });
        }

        function renderPage() {
            hideAllRows();

            const totalRows = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }

            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = startIndex + rowsPerPage;
            const rowsToShow = filteredRows.slice(startIndex, endIndex);

            rowsToShow.forEach(function (row, index) {
                row.style.display = '';

                const numberCell = row.querySelector('.row-number');

                if (numberCell) {
                    numberCell.textContent = startIndex + index + 1;
                }
            });

            if (noResultRow) {
                noResultRow.style.display = totalRows === 0 ? '' : 'none';
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

        function applyFilter(resetPage = true) {
            if (resetPage) {
                currentPage = 1;
            }

            filteredRows = getFilteredRows();
            renderPage();
        }

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                filterButtons.forEach(function (btn) {
                    btn.classList.remove('active');
                });

                button.classList.add('active');
                activeFilter = button.dataset.filter || 'semua';

                applyFilter(true);
            });
        });

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyFilter(true);
            });
        }

        if (toggleAnomali) {
            toggleAnomali.addEventListener('change', function () {
                applyFilter(true);
            });
        }

        if (prevPageBtn) {
            prevPageBtn.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                }
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', function () {
                const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));

                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            });
        }

        applyFilter(true);
    });
</script>

@endsection