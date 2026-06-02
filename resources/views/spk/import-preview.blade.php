<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Data Import</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #111827;
            background:
                radial-gradient(circle at top left, rgba(236, 72, 153, .13), transparent 30%),
                radial-gradient(circle at top right, rgba(59, 130, 246, .10), transparent 28%),
                linear-gradient(135deg, #fff7fb 0%, #ffffff 48%, #f8fbff 100%);
        }

        .import-page {
            min-height: 100vh;
            padding: 34px;
        }

        .import-container {
            max-width: 1120px;
            margin: 0 auto;
        }

        .breadcrumb-modern {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 16px;
            font-weight: 700;
        }

        .breadcrumb-modern a {
            text-decoration: none;
        }

        .breadcrumb-home {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ec4899;
            line-height: 1;
        }

        .breadcrumb-home svg {
            width: 14px;
            height: 14px;
            stroke-width: 2.4;
        }

        .breadcrumb-arrow {
            color: #ec4899;
            font-size: 14px;
            font-weight: 900;
            line-height: 1;
        }

        .breadcrumb-link {
            color: #ec4899;
            font-weight: 800;
        }

        .breadcrumb-separator {
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 800;
        }

        .breadcrumb-current {
            color: #64748b;
            font-weight: 700;
        }

        .page-hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .page-title {
            margin: 0;
            font-size: 31px;
            font-weight: 900;
            letter-spacing: -0.04em;
            color: #0f172a;
        }

        .page-subtitle {
            margin: 8px 0 0;
            font-size: 15px;
            color: #64748b;
            line-height: 1.5;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 11px 15px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            font-size: 13px;
            font-weight: 850;
            white-space: nowrap;
            box-shadow: 0 10px 25px rgba(16, 185, 129, .12);
        }

        .status-pill.warning {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
            box-shadow: 0 10px 25px rgba(249, 115, 22, .12);
        }

        .status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 5px rgba(16, 185, 129, .14);
        }

        .status-pill.warning .status-dot {
            background: #f97316;
            box-shadow: 0 0 0 5px rgba(249, 115, 22, .14);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, .92);
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .06);
        }

        .stat-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(236, 72, 153, .08), transparent 60%);
            pointer-events: none;
        }

        .stat-label {
            position: relative;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .stat-value {
            position: relative;
            font-size: 32px;
            font-weight: 950;
            line-height: 1;
            letter-spacing: -0.05em;
            color: #0f172a;
        }

        .stat-desc {
            position: relative;
            margin-top: 9px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 600;
        }

        .stat-pink { color: #db2777; }
        .stat-blue { color: #2563eb; }
        .stat-green { color: #059669; }
        .stat-orange { color: #f97316; }

        .mapping-panel {
            background: rgba(255, 255, 255, .94);
            border: 1px solid #f1d8e5;
            border-radius: 28px;
            padding: 24px;
            margin-bottom: 22px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
        }

        .mapping-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 22px;
            margin-bottom: 18px;
        }

        .mapping-eyebrow {
            width: fit-content;
            padding: 7px 11px;
            border-radius: 999px;
            background: #fdf2f8;
            color: #db2777;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .mapping-title {
            margin: 0;
            font-size: 22px;
            font-weight: 950;
            color: #0f172a;
            letter-spacing: -0.035em;
        }

        .mapping-desc {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 14px;
            max-width: 650px;
            line-height: 1.55;
        }

        .mapping-score {
            min-width: 128px;
            padding: 16px;
            border-radius: 22px;
            text-align: center;
            border: 1px solid;
        }

        .mapping-score.success {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }

        .mapping-score.warning {
            background: #fff7ed;
            border-color: #fed7aa;
            color: #c2410c;
        }

        .score-number {
            display: block;
            font-size: 28px;
            font-weight: 950;
            line-height: 1;
            letter-spacing: -0.04em;
        }

        .score-label {
            display: block;
            margin-top: 6px;
            font-size: 12px;
            font-weight: 800;
        }

        .mapping-status {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border-radius: 20px;
            margin-bottom: 18px;
            border: 1px solid;
        }

        .mapping-status.success {
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
            border-color: #a7f3d0;
        }

        .mapping-status.warning {
            background: linear-gradient(135deg, #fff7ed, #fffbeb);
            border-color: #fed7aa;
        }

        .status-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: #10b981;
            color: #ffffff;
            font-size: 17px;
            font-weight: 950;
        }

        .mapping-status.warning .status-icon {
            background: #f97316;
        }

        .mapping-status strong {
            display: block;
            color: #0f172a;
            font-size: 14px;
            font-weight: 900;
            margin-bottom: 3px;
        }

        .mapping-status p {
            margin: 0;
            color: #64748b;
            font-size: 13px;
            line-height: 1.45;
        }

        .mapping-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .mapping-card {
            position: relative;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 22px;
            padding: 18px;
            min-height: 156px;
            transition: .2s ease;
        }

        .mapping-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(16, 185, 129, .11), transparent 58%);
            pointer-events: none;
        }

        .mapping-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(15, 23, 42, .09);
        }

        .mapping-card.fixed {
            border-color: #f9a8d4;
        }

        .mapping-card.fixed::before {
            background: linear-gradient(135deg, rgba(236, 72, 153, .13), transparent 58%);
        }

        .mapping-card.missing {
            border-color: #fed7aa;
        }

        .mapping-card.missing::before {
            background: linear-gradient(135deg, rgba(249, 115, 22, .13), transparent 58%);
        }

        .mapping-card-top {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 17px;
        }

        .mapping-icon {
            width: 34px;
            height: 34px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #ffffff;
            font-size: 15px;
            font-weight: 950;
        }

        .mapping-icon.success {
            background: #10b981;
        }

        .mapping-icon.warning {
            background: #f97316;
        }

        .mapping-badge {
            padding: 7px 10px;
            border-radius: 999px;
            background: #fdf2f8;
            color: #db2777;
            font-size: 11px;
            font-weight: 900;
        }

        .mapping-badge.success {
            background: #ecfdf5;
            color: #047857;
        }

        .mapping-badge.warning {
            background: #fff7ed;
            color: #c2410c;
        }

        .mapping-name {
            position: relative;
            color: #0f172a;
            font-size: 14px;
            font-weight: 950;
            margin-bottom: 14px;
            letter-spacing: -0.01em;
        }

        .mapping-arrow {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 750;
            margin-bottom: 11px;
        }

        .mapping-result {
            position: relative;
            width: fit-content;
            padding: 9px 12px;
            border-radius: 13px;
            background: #f3f4f6;
            color: #374151;
            font-size: 13px;
            font-weight: 900;
        }

        .warning-text {
            background: #fff7ed;
            color: #c2410c;
        }

        .modern-card {
            background: rgba(255, 255, 255, .94);
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 28px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .065);
            padding: 24px;
            margin-bottom: 22px;
        }

        .card-header-modern {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            margin-bottom: 18px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 11px;
            margin: 0;
            font-size: 18px;
            font-weight: 950;
            color: #0f172a;
            letter-spacing: -0.025em;
        }

        .section-icon {
            width: 34px;
            height: 34px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            background: #fdf2f8;
            color: #db2777;
            font-size: 15px;
            font-weight: 950;
        }

        .mini-badge {
            padding: 8px 12px;
            border-radius: 999px;
            background: #fdf2f8;
            color: #be185d;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .table-wrap {
            overflow: hidden;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .modern-table thead {
            background: #fdf2f8;
        }

        .modern-table th {
            padding: 15px 16px;
            text-align: left;
            font-size: 12px;
            color: #be185d;
            text-transform: uppercase;
            letter-spacing: .045em;
            font-weight: 950;
            border-bottom: 1px solid #fbcfe8;
        }

        .modern-table td {
            padding: 15px 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .modern-table tbody tr:hover {
            background: #fff7fb;
        }

        .modern-table tbody tr:last-child td {
            border-bottom: none;
        }

        .product-name {
            font-weight: 900;
            color: #0f172a;
        }

        .number-cell {
            font-variant-numeric: tabular-nums;
            font-weight: 750;
        }

        .more-text {
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
            margin-top: 14px;
            font-weight: 650;
        }

        .action-bar {
            position: sticky;
            bottom: 18px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 16px;
            background: rgba(255, 255, 255, .82);
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .12);
            backdrop-filter: blur(14px);
            margin-top: 26px;
        }

        .btn-modern {
            border: none;
            cursor: pointer;
            border-radius: 15px;
            padding: 13px 19px;
            font-weight: 900;
            font-size: 14px;
            text-decoration: none;
            transition: .2s ease;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 9px;
        }

        .btn-secondary {
            background: #ffffff;
            color: #ef4444;
            border: 1px solid #fecaca;
        }

        .btn-secondary:hover {
            background: #fef2f2;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ec4899, #db2777);
            color: #ffffff;
            box-shadow: 0 12px 26px rgba(219, 39, 119, .28);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 34px rgba(219, 39, 119, .36);
        }

        @media (max-width: 980px) {
            .import-page {
                padding: 22px;
            }

            .page-hero {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .mapping-header {
                flex-direction: column;
            }

            .mapping-score {
                width: 100%;
            }

            .mapping-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .card-header-modern {
                align-items: flex-start;
                flex-direction: column;
            }

            .table-wrap {
                overflow-x: auto;
            }

            .modern-table {
                min-width: 720px;
            }

            .action-bar {
                flex-direction: column;
            }

            .btn-modern {
                width: 100%;
            }
        }
    </style>
</head>

<body>
@php
    $totalDataValue = $totalData ?? 0;
    $kriteriaExcelValue = $kriteriaExcel ?? [];
    $kolomDitemukanValue = $kolomDitemukan ?? [];
    $kolomTidakAdaValue = $kolomTidakAda ?? [];
    $previewRowsValue = $previewRows ?? [];

    $jumlahKolomDitemukan = count($kolomDitemukanValue);
    $jumlahKolomTidakAda = count($kolomTidakAdaValue);

    $previewHeaders = collect($previewRowsValue)
        ->flatMap(function ($row) {
            return array_keys($row['nilai'] ?? []);
        })
        ->unique()
        ->values();

    $totalKolomMapping = $jumlahKolomDitemukan + $jumlahKolomTidakAda + 1;
    $totalKolomBerhasil = $jumlahKolomDitemukan + 1;
@endphp

<div class="import-page">
    <div class="import-container">

        <div class="breadcrumb-modern">
            <a href="{{ route('produk.index') }}" class="breadcrumb-home" title="Data Produk">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 11.5L12 4l9 7.5M5.5 10.5V20h5v-5h3v5h5v-9.5" />
                </svg>
            </a>

            <span class="breadcrumb-arrow">›</span>

            <a href="{{ route('produk.index') }}" class="breadcrumb-link">
                Data Produk
            </a>

            <span class="breadcrumb-separator">›</span>

            <span class="breadcrumb-current">
                Preview Import
            </span>
        </div>

        <div class="page-hero">
            <div>
                <h1 class="page-title">Preview Data Import</h1>
                <p class="page-subtitle">
                    Periksa hasil pembacaan file Excel sebelum data produk disimpan ke sistem.
                </p>
            </div>

            <div class="status-pill {{ $jumlahKolomTidakAda === 0 ? '' : 'warning' }}">
                <span class="status-dot"></span>
                {{ $jumlahKolomTidakAda === 0 ? 'Siap Diimport' : 'Perlu Dicek' }}
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Produk</div>
                <div class="stat-value stat-pink">{{ $totalDataValue }}</div>
                <div class="stat-desc">Total baris data</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Kriteria Excel</div>
                <div class="stat-value stat-blue">{{ count($kriteriaExcelValue) }}</div>
                <div class="stat-desc">Jumlah kriteria terdeteksi</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Kolom Dikenali</div>
                <div class="stat-value stat-green">{{ $totalKolomBerhasil }}</div>
                <div class="stat-desc">Kolom berhasil ditemukan</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Kolom Tidak Ada</div>
                <div class="stat-value stat-orange">{{ $jumlahKolomTidakAda }}</div>
                <div class="stat-desc">Kolom belum ditemukan</div>
            </div>
        </div>

        <div class="mapping-panel">
            <div class="mapping-header">
                <div>
                    <div class="mapping-eyebrow">Validasi Kolom Excel</div>
                    <h2 class="mapping-title">
                        {{ $jumlahKolomTidakAda === 0 ? 'Pemetaan Kolom Berhasil' : 'Pemetaan Kolom Perlu Dicek' }}
                    </h2>
                    <p class="mapping-desc">
                        Sistem membaca kolom dari file Excel, lalu mencocokkannya dengan data produk dan kriteria yang digunakan pada sistem.
                    </p>
                </div>

                <div class="mapping-score {{ $jumlahKolomTidakAda === 0 ? 'success' : 'warning' }}">
                    <span class="score-number">{{ $totalKolomBerhasil }}/{{ $totalKolomMapping }}</span>
                    <span class="score-label">Kolom cocok</span>
                </div>
            </div>

            <div class="mapping-status {{ $jumlahKolomTidakAda === 0 ? 'success' : 'warning' }}">
                <div class="status-icon">
                    {{ $jumlahKolomTidakAda === 0 ? '✓' : '!' }}
                </div>

                <div>
                    <strong>
                        {{ $jumlahKolomTidakAda === 0 ? 'File siap diimport' : 'Ada kolom yang belum cocok' }}
                    </strong>
                    <p>
                        @if($jumlahKolomTidakAda === 0)
                            Semua kolom utama dan kriteria berhasil ditemukan. Data dapat langsung disimpan ke sistem.
                        @else
                            Beberapa kolom kriteria belum ditemukan. Periksa kembali nama kolom pada file Excel agar data lebih lengkap.
                        @endif
                    </p>
                </div>
            </div>

            <div class="mapping-grid">
                <div class="mapping-card fixed">
                    <div class="mapping-card-top">
                        <div class="mapping-icon success">✓</div>
                        <span class="mapping-badge">Wajib</span>
                    </div>

                    <div class="mapping-name">NAMA BARANG</div>

                    <div class="mapping-arrow">
                        <span>Excel</span>
                        <span>→</span>
                        <span>Sistem</span>
                    </div>

                    <div class="mapping-result">
                        Nama Produk
                    </div>
                </div>

                @foreach($kolomDitemukanValue as $kolom)
                    <div class="mapping-card">
                        <div class="mapping-card-top">
                            <div class="mapping-icon success">✓</div>
                            <span class="mapping-badge success">Terdeteksi</span>
                        </div>

                        <div class="mapping-name">
                            {{ strtoupper($kolom['nama_kolom_excel'] ?? '-') }}
                        </div>

                        <div class="mapping-arrow">
                            <span>Excel</span>
                            <span>→</span>
                            <span>Kriteria</span>
                        </div>

                        <div class="mapping-result">
                            {{ ucfirst($kolom['nama_kriteria'] ?? '-') }}
                        </div>
                    </div>
                @endforeach

                @foreach($kolomTidakAdaValue as $kolom)
                    <div class="mapping-card missing">
                        <div class="mapping-card-top">
                            <div class="mapping-icon warning">!</div>
                            <span class="mapping-badge warning">Belum Cocok</span>
                        </div>

                        <div class="mapping-name">
                            {{ strtoupper($kolom['nama_kolom_excel'] ?? '-') }}
                        </div>

                        <div class="mapping-arrow">
                            <span>Excel</span>
                            <span>→</span>
                            <span>Kriteria</span>
                        </div>

                        <div class="mapping-result warning-text">
                            {{ ucfirst($kolom['nama_kriteria'] ?? '-') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="modern-card">
            <div class="card-header-modern">
                <h2 class="section-title">
                    <span class="section-icon">▦</span>
                    Preview 5 Baris Pertama
                </h2>

                <span class="mini-badge">{{ $totalDataValue }} total baris</span>
            </div>

            <div class="table-wrap">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 70px;">No</th>
                            <th>Nama Produk</th>

                            @foreach($previewHeaders as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($previewRowsValue as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td class="product-name">
                                    {{ $row['nama_produk'] ?? '-' }}
                                </td>

                                @foreach($previewHeaders as $header)
                                    <td class="number-cell">
                                        @php
                                            $nilai = $row['nilai'][$header] ?? null;
                                        @endphp

                                        @if(is_numeric($nilai))
                                            {{ number_format($nilai, 0, ',', '.') }}
                                        @else
                                            {{ $nilai ?? '-' }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 2 + $previewHeaders->count() }}" style="text-align: center; color: #94a3b8;">
                                    Tidak ada data preview yang dapat ditampilkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($totalDataValue > 5)
                <div class="more-text">
                    ... dan {{ $totalDataValue - 5 }} baris lainnya akan ikut diimport.
                </div>
            @endif
        </div>

        <div class="action-bar">
            @if(\Illuminate\Support\Facades\Route::has('produk.import.cancel'))
                <form action="{{ route('produk.import.cancel') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-modern btn-secondary">
                        🗑 Batal & Hapus File
                    </button>
                </form>
            @else
                <a href="{{ route('produk.index') }}" class="btn-modern btn-secondary">
                    Batal
                </a>
            @endif

            <form action="{{ route('produk.import.confirm') }}" method="POST">
                @csrf
                <button type="submit" class="btn-modern btn-primary">
                     Simpan {{ $totalDataValue }} Produk
                </button>
            </form>
        </div>

    </div>
</div>

</body>
</html>
