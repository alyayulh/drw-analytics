@extends('layouts.app')

@section('content')

<div class="riwayat-page">

    <div class="page-header">
        <h1>Riwayat Analisis</h1>
        <p>Lihat dan kelola riwayat analisis asosiasi yang telah dilakukan</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div>
                <p>Total Analisis</p>
                <h2>3</h2>
            </div>
            <div class="summary-icon pink">▥</div>
        </div>

        <div class="summary-card">
            <div>
                <p>Analisis Terakhir</p>
                <h2>8 Mei 2026</h2>
            </div>
            <div class="summary-icon blue">▣</div>
        </div>

        <div class="summary-card">
            <div>
                <p>Total File Diproses</p>
                <h2>3</h2>
            </div>
            <div class="summary-icon green">▤</div>
        </div>

        <div class="summary-card">
            <div>
                <p>Total Rules Tersimpan</p>
                <h2>896</h2>
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

    <input type="text" placeholder="Cari nama file...">

</div>

            <input type="date" class="filter-input">

            <select class="filter-input">
                <option>Urutkan: Terbaru</option>
                <option>Urutkan: Terlama</option>
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
                    <th>Periode Data</th>
                    <th>Data<br>Awal</th>
                    <th>Data<br>Bersih</th>
                    <th>Transaksi<br>Diproses</th>
                    <th>Jumlah<br>Rules</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>1</td>
                    <td>2026-05-08</td>
                    <td>data_penjualan_april_2026.xlsx</td>
                    <td>1 Apr - 30 Apr<br>2026</td>
                    <td>1285</td>
                    <td>1220</td>
                    <td>1220</td>
                    <td><span class="rules-badge">342</span></td>
                    <td><span class="status-badge">Selesai</span></td>
                    <td>
                        <div class="action-icons">
                            <a href="{{ route('asosiasi.riwayat.detail', 1) }}" class="detail-icon" title="Lihat Detail">
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

                            <a href="#" class="download-icon" title="Unduh">
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
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>2</td>
                    <td>2026-05-05</td>
                    <td>sales_data_maret_2026.xlsx</td>
                    <td>1 Mar - 31 Mar<br>2026</td>
                    <td>1156</td>
                    <td>1098</td>
                    <td>1098</td>
                    <td><span class="rules-badge">298</span></td>
                    <td><span class="status-badge">Selesai</span></td>
                    <td>
                        <div class="action-icons">
                            <a href="{{ route('asosiasi.riwayat.detail', 2) }}" class="detail-icon" title="Lihat Detail">
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

                            <a href="#" class="download-icon" title="Unduh">
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
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>3</td>
                    <td>2026-05-02</td>
                    <td>transaksi_februari_2026.xlsx</td>
                    <td>1 Feb - 28 Feb<br>2026</td>
                    <td>987</td>
                    <td>945</td>
                    <td>945</td>
                    <td><span class="rules-badge">256</span></td>
                    <td><span class="status-badge">Selesai</span></td>
                    <td>
                        <div class="action-icons">
                            <a href="{{ route('asosiasi.riwayat.detail', 3) }}" class="detail-icon" title="Lihat Detail">
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

                            <a href="#" class="download-icon" title="Unduh">
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
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

@endsection