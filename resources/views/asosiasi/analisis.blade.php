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
    <div class="analysis-alert analysis-alert-error">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="analysis-alert analysis-alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="analysis-alert analysis-alert-error">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="formAnalisisApi" action="{{ route('asosiasi.proses') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="min_support" value="0.01">
    <input type="hidden" name="min_confidence" value="0.4">
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
                <strong>0.4</strong>
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
    <div class="loading-header">
        <div class="loading-spinner"></div>

        <div class="loading-header-text">
            <h2>Proses Analisis</h2>
            <p>Mohon tunggu, sistem sedang memproses dataset menggunakan algoritma FP-Growth.</p>
        </div>
    </div>

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
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('file');
        const fileName = document.getElementById('fileName');
        const btnReset = document.getElementById('btnReset');
        const formAnalisis = document.getElementById('formAnalisisApi');
        const loadingAnalisis = document.getElementById('loadingAnalisis');
        const btnProses = document.getElementById('btnProses');
        const processSteps = document.querySelectorAll('.process-step');

        let stepInterval = null;

        function resetProcessSteps() {
            processSteps.forEach(function (step) {
                step.classList.remove('active', 'done');
            });
        }

        function startProcessAnimation() {
            resetProcessSteps();

            let currentStep = 0;

            if (processSteps.length > 0) {
                processSteps[0].classList.add('active');
            }

            stepInterval = setInterval(function () {
                if (currentStep < processSteps.length) {
                    processSteps[currentStep].classList.remove('active');
                    processSteps[currentStep].classList.add('done');
                }

                currentStep++;

                if (currentStep < processSteps.length) {
                    processSteps[currentStep].classList.add('active');
                } else {
                    clearInterval(stepInterval);
                }
            }, 900);
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    fileName.textContent = this.files[0].name;
                } else {
                    fileName.textContent = 'Belum ada file yang dipilih';
                }
            });
        }

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                fileInput.value = '';
                fileName.textContent = 'Belum ada file yang dipilih';

                if (loadingAnalisis) {
                    loadingAnalisis.classList.add('hidden');
                }

                resetProcessSteps();

                if (stepInterval) {
                    clearInterval(stepInterval);
                }
            });
        }

        if (formAnalisis) {
            formAnalisis.addEventListener('submit', function (event) {
                if (!fileInput.files || fileInput.files.length === 0) {
                    event.preventDefault();
                    alert('Silakan pilih file Excel terlebih dahulu.');
                    return;
                }

                if (loadingAnalisis) {
                    loadingAnalisis.classList.remove('hidden');

                    setTimeout(function () {
                        loadingAnalisis.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 100);
                }

                if (btnProses) {
                    btnProses.disabled = true;
                    btnProses.innerHTML = `
                        <span class="btn-loading-content">
                            <span class="btn-mini-spinner"></span>
                            Memproses...
                        </span>
                    `;
                }

                if (btnReset) {
                    btnReset.disabled = true;
                }

                startProcessAnimation();
            });
        }
    });
</script>

@endsection