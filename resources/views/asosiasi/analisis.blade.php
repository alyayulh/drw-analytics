@extends('layouts.app')

@section('title', 'Analisis Pola - DRW Skincare Analytics')

@section('content')

<div class="analysis-header">
    <h1>Analisis Data Penjualan</h1>
    <p>
        Upload dataset penjualan dalam format Excel untuk menemukan pola hubungan antar produk,
        operator, dan kategori waktu transaksi menggunakan algoritma FP-Growth.
    </p>
</div>

@if(session('error'))
    <div class="analysis-card" style="border-left: 5px solid #ef4444; color: #991b1b;">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="analysis-card" style="border-left: 5px solid #22c55e; color: #166534;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="analysis-card" style="border-left: 5px solid #ef4444; color: #991b1b;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="formAnalisisApi" action="{{ route('asosiasi.proses') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Parameter dikirim otomatis ke controller --}}
    <input type="hidden" name="min_support" value="0.01">
    <input type="hidden" name="min_confidence" value="0.5">
    <input type="hidden" name="min_lift" value="1.0">

    <div class="analysis-card">
        <h2>Dataset Upload</h2>

        <div class="upload-box simple-upload">
            <input type="file" name="file" id="file" accept=".xlsx,.xls" required hidden>

            <label for="file" class="btn-upload">
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

<script>
    const fileInput = document.getElementById('file');
    const fileName = document.getElementById('fileName');
    const btnReset = document.getElementById('btnReset');
    const formAnalisis = document.getElementById('formAnalisisApi');
    const loadingAnalisis = document.getElementById('loadingAnalisis');
    const btnProses = document.getElementById('btnProses');

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            fileName.textContent = this.files[0].name;
        } else {
            fileName.textContent = 'Belum ada file yang dipilih';
        }
    });

    btnReset.addEventListener('click', function () {
        fileInput.value = '';
        fileName.textContent = 'Belum ada file yang dipilih';
    });

    formAnalisis.addEventListener('submit', function () {
        loadingAnalisis.classList.remove('hidden');
        btnProses.disabled = true;
        btnProses.textContent = 'Memproses...';
    });
</script>

@endsection