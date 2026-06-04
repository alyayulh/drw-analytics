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
        <p>Lihat dan kelola riwayat analisis asosiasi yang telah dilakukan</p>
    </div>

    @if (session('success'))
        <div id="riwayatToast" class="riwayat-toast riwayat-toast-success" data-show="true">
            <div class="riwayat-toast-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M20 6L9 17l-5-5"
                          stroke="currentColor"
                          stroke-width="2.6"
                          stroke-linecap="round"
                          stroke-linejoin="round" />
                </svg>
            </div>

            <div class="riwayat-toast-content">
                <strong>Berhasil</strong>
                <p>{{ session('success') }}</p>
            </div>

            <button type="button" class="riwayat-toast-close" aria-label="Tutup notifikasi">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18"
                          stroke="currentColor"
                          stroke-width="2.2"
                          stroke-linecap="round" />
                    <path d="M6 6l12 12"
                          stroke="currentColor"
                          stroke-width="2.2"
                          stroke-linecap="round" />
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div id="riwayatToast" class="riwayat-toast riwayat-toast-error" data-show="true">
            <div class="riwayat-toast-icon">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M12 8v5"
                          stroke="currentColor"
                          stroke-width="2.4"
                          stroke-linecap="round" />
                    <path d="M12 16.5h.01"
                          stroke="currentColor"
                          stroke-width="3"
                          stroke-linecap="round" />
                    <circle cx="12" cy="12" r="9"
                            stroke="currentColor"
                            stroke-width="2" />
                </svg>
            </div>

            <div class="riwayat-toast-content">
                <strong>Gagal</strong>
                <p>{{ session('error') }}</p>
            </div>

            <button type="button" class="riwayat-toast-close" aria-label="Tutup notifikasi">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M18 6L6 18"
                          stroke="currentColor"
                          stroke-width="2.2"
                          stroke-linecap="round" />
                    <path d="M6 6l12 12"
                          stroke="currentColor"
                          stroke-width="2.2"
                          stroke-linecap="round" />
                </svg>
            </button>
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

                        $isSuccessStatus = in_array($statusLower, ['selesai', 'berhasil', 'success'], true);
                        $isFailedStatus = in_array($statusLower, ['gagal', 'failed', 'error'], true);

                        $canDownload = $id && $isSuccessStatus;
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
                            @if ($isFailedStatus)
                                <span class="status-badge status-anomaly">
                                    Gagal
                                </span>
                            @else
                                <span class="status-badge status-normal">
                                    {{ $status ?: 'Selesai' }}
                                </span>
                            @endif
                        </td>

                        <td>
                            <div class="action-icons">
                                @if ($id)
                                    <a href="{{ route('asosiasi.riwayat.detail', $id) }}"
                                       class="detail-icon"
                                       title="Lihat Detail">
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"
                                                  stroke="currentColor"
                                                  stroke-width="2"
                                                  stroke-linejoin="round" />
                                            <circle cx="12" cy="12" r="3"
                                                    stroke="currentColor"
                                                    stroke-width="2" />
                                        </svg>
                                    </a>

                                    @if ($canDownload)
                                        <a href="{{ route('asosiasi.riwayat.download', $id) }}"
                                           class="download-icon"
                                           title="Unduh Hasil">
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
                                        </a>
                                    @else
                                        <span class="disabled-download-icon" title="Hasil belum tersedia">
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
                                    @endif

                                    <form action="{{ route('asosiasi.riwayat.destroy', $id) }}"
                                          method="POST"
                                          class="delete-form"
                                          onsubmit="return confirm('Yakin ingin menghapus riwayat analisis ini? Data yang sudah dihapus tidak dapat dikembalikan.')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="delete-icon" title="Hapus Riwayat">
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M4 7h16"
                                                      stroke="currentColor"
                                                      stroke-width="2"
                                                      stroke-linecap="round" />
                                                <path d="M10 11v6"
                                                      stroke="currentColor"
                                                      stroke-width="2"
                                                      stroke-linecap="round" />
                                                <path d="M14 11v6"
                                                      stroke="currentColor"
                                                      stroke-width="2"
                                                      stroke-linecap="round" />
                                                <path d="M6 7l1 14h10l1-14"
                                                      stroke="currentColor"
                                                      stroke-width="2"
                                                      stroke-linejoin="round" />
                                                <path d="M9 7V4h6v3"
                                                      stroke="currentColor"
                                                      stroke-width="2"
                                                      stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyRiwayatRowStatic">
                        <td colspan="9" style="text-align: center;">
                            Belum ada riwayat analisis.
                        </td>
                    </tr>
                @endforelse

                <tr id="emptyRiwayatRow" style="display: none;">
                    <td colspan="9" style="text-align: center;">
                        Data riwayat tidak ditemukan.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="riwayat-pagination">
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
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('riwayatToast');

    if (toast && toast.dataset.show === 'true') {
        setTimeout(() => {
            toast.classList.add('show');
        }, 150);

        const closeButton = toast.querySelector('.riwayat-toast-close');

        if (closeButton) {
            closeButton.addEventListener('click', function () {
                toast.classList.remove('show');
            });
        }

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3500);
    }

    const searchInput = document.getElementById('searchRiwayat');
    const dateInput = document.getElementById('filterTanggal');
    const sortSelect = document.getElementById('sortRiwayat');
    const tableBody = document.getElementById('riwayatTableBody');

    const prevButton = document.getElementById('prevRiwayatPage');
    const nextButton = document.getElementById('nextRiwayatPage');
    const pageInfo = document.getElementById('riwayatPageInfo');
    const emptyRow = document.getElementById('emptyRiwayatRow');

    const rows = Array.from(document.querySelectorAll('.riwayat-row'));

    let currentPage = 1;
    const rowsPerPage = 10;
    let filteredRows = [...rows];

    function normalizeText(value) {
        return String(value || '').toLowerCase().trim();
    }

    function getFilteredRows() {
        const keyword = normalizeText(searchInput ? searchInput.value : '');
        const selectedDate = dateInput ? dateInput.value : '';
        const sortValue = sortSelect ? sortSelect.value : 'terbaru';

        let result = rows.filter(function (row) {
            const fileName = normalizeText(row.dataset.file);
            const rowDate = row.dataset.date || '';

            const matchKeyword = fileName.includes(keyword);
            const matchDate = !selectedDate || rowDate === selectedDate;

            return matchKeyword && matchDate;
        });

        result.sort(function (a, b) {
            const dateA = a.dataset.date || '';
            const dateB = b.dataset.date || '';
            const idA = Number(a.dataset.id || 0);
            const idB = Number(b.dataset.id || 0);

            if (dateA === dateB) {
                return sortValue === 'terbaru' ? idB - idA : idA - idB;
            }

            return sortValue === 'terbaru'
                ? dateB.localeCompare(dateA)
                : dateA.localeCompare(dateB);
        });

        return result;
    }

    function renderTable() {
        rows.forEach(function (row) {
            row.style.display = 'none';
        });

        filteredRows.forEach(function (row) {
            tableBody.appendChild(row);
        });

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

        if (emptyRow) {
            emptyRow.style.display = totalRows === 0 ? '' : 'none';
        }

        if (pageInfo) {
            if (totalRows === 0) {
                pageInfo.textContent = 'Tidak ada data';
            } else {
                pageInfo.textContent = `${startIndex + 1}-${Math.min(endIndex, totalRows)} dari ${totalRows}`;
            }
        }

        if (prevButton) {
            prevButton.disabled = currentPage <= 1 || totalRows === 0;
        }

        if (nextButton) {
            nextButton.disabled = currentPage >= totalPages || totalRows === 0;
        }
    }

    function applyFilters() {
        currentPage = 1;
        filteredRows = getFilteredRows();
        renderTable();
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    if (dateInput) {
        dateInput.addEventListener('change', applyFilters);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', applyFilters);
    }

    if (prevButton) {
        prevButton.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                renderTable();
            }
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', function () {
            const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));

            if (currentPage < totalPages) {
                currentPage++;
                renderTable();
            }
        });
    }

    applyFilters();
});
</script>
@endpush