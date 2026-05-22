@extends('layouts.app')

@section('title', 'Riwayat Analisis - DRW Skincare Analytics')

@section('content')

@php
    $summary = $summary ?? [];
    $riwayats = collect($riwayats ?? []);

    $analisisTerakhir = $summary['analisis_terakhir'] ?? null;

    if ($analisisTerakhir instanceof \Carbon\Carbon) {
        $analisisTerakhirText = $analisisTerakhir->translatedFormat('d M Y');
    } elseif (!empty($analisisTerakhir)) {
        try {
            $analisisTerakhirText = \Carbon\Carbon::parse($analisisTerakhir)->translatedFormat('d M Y');
        } catch (\Throwable $e) {
            $analisisTerakhirText = $analisisTerakhir;
        }
    } else {
        $analisisTerakhirText = '-';
    }
@endphp

<div class="riwayat-page">

    <div class="page-header">
        <h1>Riwayat Analisis</h1>
        <p>Lihat dan kelola riwayat analisis pola hubungan dari data transaksi yang telah dilakukan</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="margin: 20px 0; padding: 14px 18px; border-radius: 10px; background: #eafaf1; color: #157347;">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" style="margin: 20px 0; padding: 14px 18px; border-radius: 10px; background: #fdecec; color: #b42318;">
            {{ session('error') }}
        </div>
    @endif

    <div class="summary-grid">
        <div class="summary-card">
            <div>
                <p>Total Analisis</p>
                <h2>{{ number_format($summary['total_analisis'] ?? 0, 0, ',', '.') }}</h2>
            </div>
            <div class="summary-icon pink">▥</div>
        </div>

        <div class="summary-card">
            <div>
                <p>Analisis Terakhir</p>
                <h2>{{ $analisisTerakhirText }}</h2>
            </div>
            <div class="summary-icon blue">▣</div>
        </div>

        <div class="summary-card">
            <div>
                <p>Total File Diproses</p>
                <h2>{{ number_format($summary['total_file_diproses'] ?? 0, 0, ',', '.') }}</h2>
            </div>
            <div class="summary-icon green">▤</div>
        </div>

        <div class="summary-card">
            <div>
                <p>Total Rules Tersimpan</p>
                <h2>{{ number_format($summary['total_rules'] ?? 0, 0, ',', '.') }}</h2>
            </div>
            <div class="summary-icon yellow">↗</div>
        </div>
    </div>

    <div class="filter-card">
        <h3>Filter dan Pencarian Riwayat</h3>

        <div class="filter-grid">
            <div class="search-box">
                <span class="search-icon">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         width="20"
                         height="20"
                         viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>

                <input type="text" id="searchRiwayat" placeholder="Cari nama file...">
            </div>

            <input type="date" id="filterTanggal" class="filter-input">

            <select id="sortRiwayat" class="filter-input">
                <option value="terbaru">Urutkan: Terbaru</option>
                <option value="terlama">Urutkan: Terlama</option>
            </select>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal<br>Analisis</th>
                    <th>Nama File</th>
                    <th>Data<br>Awal</th>
                    <th>Data<br>Bersih</th>
                    <th>Transaksi<br>Diproses</th>
                    <th>Jumlah<br>Rules</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody id="riwayatTableBody">
                @forelse ($riwayats as $index => $item)
                    @php
                        $id = data_get($item, 'id');
                        $tanggalAnalisis = data_get($item, 'tanggal_analisis', '-');
                        $tanggalFilter = data_get($item, 'tanggal_filter', '');
                        $namaFile = data_get($item, 'nama_file', '-');
                        $dataAwal = data_get($item, 'total_data_awal', 0);
                        $dataBersih = data_get($item, 'setelah_preprocessing', 0);
                        $totalBasket = data_get($item, 'total_basket', 0);
                        $jumlahRules = data_get($item, 'association_rules', 0);
                        $status = data_get($item, 'status', '-');

                        $statusLower = strtolower(trim((string) $status));
                        $canDownload = $id && in_array($statusLower, ['selesai', 'berhasil', 'success'], true);
                    @endphp

                    <tr class="riwayat-row"
                        data-file="{{ strtolower($namaFile) }}"
                        data-date="{{ $tanggalFilter }}"
                        data-id="{{ $id ?? 0 }}"
                        data-index="{{ $index }}">
                        <td class="row-number">{{ $index + 1 }}</td>

                        <td>{{ $tanggalAnalisis }}</td>

                        <td>{{ $namaFile }}</td>

                        <td>{{ number_format((int) $dataAwal, 0, ',', '.') }}</td>

                        <td>{{ number_format((int) $dataBersih, 0, ',', '.') }}</td>

                        <td>{{ number_format((int) $totalBasket, 0, ',', '.') }}</td>

                        <td>
                            <span class="rules-badge">
                                {{ number_format((int) $jumlahRules, 0, ',', '.') }}
                            </span>
                        </td>

                        <td>
                            <span class="status-badge {{ $canDownload ? 'status-normal' : 'status-anomaly' }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td>
                            <div class="action-icons">
                                @if ($id)
                                    <a href="{{ route('asosiasi.riwayat.detail', $id) }}"
                                       class="detail-icon"
                                       title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </a>
                                @endif

                                @if ($canDownload)
                                    <a href="{{ route('asosiasi.riwayat.download', $id) }}"
                                       class="download-icon"
                                       title="Unduh hasil analisis">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7 10 12 15 17 10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                    </a>
                                @else
                                    <button type="button"
                                            class="disabled-download-icon"
                                            title="Hasil tidak dapat diunduh karena analisis belum berhasil"
                                            disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             viewBox="0 0 24 24"
                                             fill="none"
                                             stroke="currentColor"
                                             stroke-width="2"
                                             stroke-linecap="round"
                                             stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7 10 12 15 17 10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                    </button>
                                @endif

                                @if ($id)
                                    <form action="{{ route('asosiasi.riwayat.destroy', $id) }}"
                                          method="POST"
                                          class="delete-form"
                                          onsubmit="return confirm('Yakin ingin menghapus riwayat analisis ini? Data yang sudah dihapus tidak bisa dikembalikan.');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="delete-icon"
                                                title="Hapus Riwayat">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 viewBox="0 0 24 24"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 stroke-width="2"
                                                 stroke-linecap="round"
                                                 stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyRiwayatRow">
                        <td colspan="9" style="text-align: center; padding: 32px;">
                            Belum ada riwayat analisis.
                        </td>
                    </tr>
                @endforelse

                @if ($riwayats->isNotEmpty())
                    <tr id="noFilterResultRow" style="display: none;">
                        <td colspan="9" style="text-align: center; padding: 32px;">
                            Data riwayat tidak ditemukan.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if ($riwayats->isNotEmpty())
        <div class="riwayat-pagination" id="riwayatPagination">
            <button type="button" id="prevRiwayatPage" class="riwayat-pagination-btn">
                ‹ Sebelumnya
            </button>

            <span id="riwayatPageInfo" class="riwayat-pagination-info">
                1-10 dari {{ $riwayats->count() }}
            </span>

            <button type="button" id="nextRiwayatPage" class="riwayat-pagination-btn">
                Berikutnya ›
            </button>
        </div>
    @endif

</div>

<style>
    .riwayat-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 16px;
        margin-bottom: 8px;
    }

    .riwayat-pagination-btn {
        height: 36px;
        padding: 0 15px;
        border-radius: 9px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #344054;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .riwayat-pagination-btn:hover:not(:disabled) {
        background: #fdf2f8;
        border-color: #f9a8d4;
        color: #e8007a;
    }

    .riwayat-pagination-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .riwayat-pagination-info {
        font-size: 13px;
        font-weight: 800;
        color: #344054;
    }

    @media (max-width: 768px) {
        .riwayat-pagination {
            justify-content: center;
            flex-wrap: wrap;
        }

        .riwayat-pagination-btn {
            width: 100%;
        }

        .riwayat-pagination-info {
            width: 100%;
            text-align: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRiwayat');
        const dateInput = document.getElementById('filterTanggal');
        const sortSelect = document.getElementById('sortRiwayat');
        const tbody = document.getElementById('riwayatTableBody');
        const noFilterResultRow = document.getElementById('noFilterResultRow');

        const prevPageBtn = document.getElementById('prevRiwayatPage');
        const nextPageBtn = document.getElementById('nextRiwayatPage');
        const pageInfo = document.getElementById('riwayatPageInfo');

        const rowsPerPage = 10;
        let currentPage = 1;
        let filteredRows = [];

        if (!tbody) {
            return;
        }

        function getRows() {
            return Array.from(tbody.querySelectorAll('.riwayat-row'));
        }

        function applySort() {
            const rows = getRows();
            const sortValue = sortSelect ? sortSelect.value : 'terbaru';

            rows.sort(function (a, b) {
                const dateA = a.dataset.date || '';
                const dateB = b.dataset.date || '';

                const idA = parseInt(a.dataset.id || '0', 10);
                const idB = parseInt(b.dataset.id || '0', 10);

                if (sortValue === 'terlama') {
                    const dateCompare = dateA.localeCompare(dateB);

                    if (dateCompare !== 0) {
                        return dateCompare;
                    }

                    return idA - idB;
                }

                const dateCompare = dateB.localeCompare(dateA);

                if (dateCompare !== 0) {
                    return dateCompare;
                }

                return idB - idA;
            });

            rows.forEach(function (row) {
                tbody.appendChild(row);
            });

            if (noFilterResultRow) {
                tbody.appendChild(noFilterResultRow);
            }
        }

        function getFilteredRows() {
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedDate = dateInput ? dateInput.value : '';
            const rows = getRows();

            return rows.filter(function (row) {
                const fileName = row.dataset.file || '';
                const rowDate = row.dataset.date || '';

                const matchKeyword = keyword === '' || fileName.includes(keyword);
                const matchDate = selectedDate === '' || rowDate === selectedDate;

                return matchKeyword && matchDate;
            });
        }

        function hideAllRows() {
            getRows().forEach(function (row) {
                row.style.display = 'none';
            });
        }

        function renderTablePage() {
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

            if (noFilterResultRow) {
                noFilterResultRow.style.display = totalRows === 0 ? '' : 'none';
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

        function refreshTable(resetPage = true) {
            applySort();

            if (resetPage) {
                currentPage = 1;
            }

            filteredRows = getFilteredRows();
            renderTablePage();
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                refreshTable(true);
            });
        }

        if (dateInput) {
            dateInput.addEventListener('change', function () {
                refreshTable(true);
            });
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                refreshTable(true);
            });
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

        refreshTable(true);
    });
</script>

@endsection