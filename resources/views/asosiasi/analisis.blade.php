@extends('layouts.app')

@section('title', 'Analisis Data Penjualan - DRW Analytics')

@section('content')

<div class="analysis-header">
    <h1>Analisis Data Penjualan</h1>
    <p>
        Upload dataset penjualan dalam format Excel untuk menemukan pola hubungan antar produk,
        operator, dan kategori waktu transaksi menggunakan algoritma FP-Growth.
    </p>
</div>

<form id="formAnalisis" action="{{ route('asosiasi.proses') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="analysis-card">
        <h2>Dataset Upload</h2>

        <div class="upload-box simple-upload">
    <input type="file" name="file_excel" id="file_excel" accept=".xlsx,.xls,.csv" required hidden>

    <label for="file_excel" class="btn-upload">
        Pilih File Excel
    </label>

    <p id="fileName" class="selected-file-name">
        Belum ada file yang dipilih
    </p>
</div>

        <div class="upload-notes">
            <div>ⓘ Format file yang diterima: .xlsx atau .xls</div>
            <div>ⓘ Dataset harus berisi data transaksi penjualan</div>
            <div>ⓘ Sistem akan melakukan preprocessing, pembentukan basket transaksi, dan analisis FP-Growth secara otomatis</div>
        </div>

        <div class="action-row">
            <button type="submit" id="btnProses" class="btn-process">
                ▷ Proses Analisis
            </button>

            <button type="button" id="btnReset" class="btn-reset">
                ↺ Reset
            </button>
        </div>
    </div>

    <div class="analysis-card">
        <h2>Parameter Analisis Otomatis</h2>

        <div class="parameter-grid">
            <div class="parameter-item">
                <span>Metode</span>
                <strong>FP-Growth</strong>
            </div>

            <div class="parameter-item">
                <span>Minimum Support</span>
                <strong>0.01</strong>
            </div>

            <div class="parameter-item">
                <span>Minimum Confidence</span>
                <strong>0.5</strong>
            </div>

            <div class="parameter-item">
                <span>Minimum Lift</span>
                <strong>1.0</strong>
            </div>
        </div>

        <p class="parameter-info">
            Parameter analisis menggunakan nilai default berdasarkan hasil pengujian model,
            sehingga user tidak perlu mengatur threshold secara manual.
        </p>
    </div>
</form>

<div id="loadingAnalisis" class="analysis-card process-card hidden">
    <h2>Proses Analisis</h2>

    <div class="process-list">
        <div class="process-step" data-step="0">
            <span class="process-icon"></span>
            <p>Membaca file Excel</p>
        </div>

        <div class="process-step" data-step="1">
            <span class="process-icon"></span>
            <p>Membersihkan data refund</p>
        </div>

        <div class="process-step" data-step="2">
            <span class="process-icon"></span>
            <p>Mengubah waktu transaksi menjadi kategori</p>
        </div>

        <div class="process-step" data-step="3">
            <span class="process-icon"></span>
            <p>Membentuk basket transaksi</p>
        </div>

        <div class="process-step" data-step="4">
            <span class="process-icon"></span>
            <p>Menjalankan algoritma FP-Growth</p>
        </div>

        <div class="process-step" data-step="5">
            <span class="process-icon"></span>
            <p>Membentuk association rules</p>
        </div>

        <div class="process-step" data-step="6">
            <span class="process-icon"></span>
            <p>Menampilkan hasil analisis</p>
        </div>
    </div>
</div>

@endsection