@extends('layouts.app')

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

<style>
    .action-icons {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .delete-form {
        display: inline-flex;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .delete-icon {
        border: none;
        background: transparent;
        padding: 0;
        margin: 0;
        cursor: pointer;
        color: #ff1493;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .delete-icon svg {
        width: 17px;
        height: 17px;
    }

    .delete-icon:hover {
        color: #c4004f;
    }
</style>

<div class="riwayat-page">

    <div class="page-header">
        <h1>Riwayat Analisis</h1>
        <p>Lihat dan kelola riwayat analisis asosiasi yang telah dilakukan</p>
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
                            <span class="status-badge">
                                {{ $status }}
                            </span>
                        </td>
                        <td>
                            <div class="action-icons">
                                @if ($id)
                                    <a href="{{ route('asosiasi.riwayat.detail', $id) }}" class="detail-icon" title="Lihat Detail">
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
                                @else
                                    <a href="#" class="detail-icon" title="Detail tidak tersedia">
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

                                <a href="#"
                                   class="download-icon"
                                   title="Unduh"
                                   onclick="event.preventDefault(); alert('Fitur unduh laporan belum tersedia.');">
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

                                @if ($id)
                                    <form action="{{ route('asosiasi.riwayat.destroy', $id) }}"
                                          method="POST"
                                          class="delete-form"
                                          onsubmit="return confirm('Yakin ingin menghapus riwayat analisis ini? Data yang sudah dihapus tidak bisa dikembalikan.');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="delete-icon" title="Hapus Riwayat">
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

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchRiwayat');
        const dateInput = document.getElementById('filterTanggal');
        const sortSelect = document.getElementById('sortRiwayat');
        const tbody = document.getElementById('riwayatTableBody');
        const noFilterResultRow = document.getElementById('noFilterResultRow');

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

        function applyFilter() {
            const keyword = searchInput ? searchInput.value.toLowerCase().trim() : '';
            const selectedDate = dateInput ? dateInput.value : '';
            const rows = getRows();

            let visibleCount = 0;

            rows.forEach(function (row) {
                const fileName = row.dataset.file || '';
                const rowDate = row.dataset.date || '';

                const matchKeyword = keyword === '' || fileName.includes(keyword);
                const matchDate = selectedDate === '' || rowDate === selectedDate;

                if (matchKeyword && matchDate) {
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

            if (noFilterResultRow) {
                noFilterResultRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }

        function refreshTable() {
            applySort();
            applyFilter();
        }

        if (searchInput) {
            searchInput.addEventListener('input', refreshTable);
        }

        if (dateInput) {
            dateInput.addEventListener('change', refreshTable);
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', refreshTable);
        }

        refreshTable();
    });
</script>

@endsection