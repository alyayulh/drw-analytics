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

<div id="clientErrorContainer"></div>

@if(session('error'))
    <div class="analysis-alert analysis-alert-error" role="alert">
        {{ session('error') }}
    </div>
@endif

@if(session('success'))
    <div class="analysis-alert analysis-alert-success" role="alert">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="analysis-alert analysis-alert-error" role="alert">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form id="formAnalisisApi" action="{{ route('asosiasi.proses') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <input type="hidden" name="min_support" value="{{ old('min_support', '0.02') }}">
    <input type="hidden" name="min_confidence" value="{{ old('min_confidence', '0.6') }}">
    <input type="hidden" name="min_lift" value="{{ old('min_lift', '1.0') }}">
    <input type="hidden" name="kanal_filter" value="semua">

    <div class="analysis-card">
        <h2>Dataset Upload</h2>

        <div class="upload-box simple-upload">
            <input type="file" name="file" id="file" accept=".xlsx,.xls" hidden>

            <label for="file" class="btn-upload">
                <span class="btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 16V5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M8 9l4-4 4 4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M5 15v3a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-3" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Pilih File Excel</span>
            </label>

            <p id="fileName" class="selected-file-name">
                Belum ada file yang dipilih
            </p>
        </div>

        <div class="upload-notes">
            <div>
                <span class="note-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 17v-6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                        <path d="M12 7.5h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                Format file yang diterima: .xlsx atau .xls
            </div>

            <div>
                <span class="note-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M7 4h7l4 4v12H7V4z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                        <path d="M14 4v5h5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </span>
                Dataset harus berisi data transaksi penjualan
            </div>

            <div>
                <span class="note-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M5 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                Dataset minimal harus memiliki kolom nomor transaksi, produk, operator, waktu/tanggal transaksi, dan tipe penjualan
            </div>

            <div>
                <span class="note-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M12 3v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M12 18v3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4.2 7.5l2.6 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M17.2 15l2.6 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M4.2 16.5l2.6-1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M17.2 9l2.6-1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                Sistem akan melakukan preprocessing, pembentukan basket transaksi, dan analisis FP-Growth secara otomatis
            </div>
        </div>

        <div class="action-row">
            <button type="submit" id="btnProses" class="btn-process">
                <span class="btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5.14v13.72c0 .8.87 1.3 1.57.9l10.28-5.86c.71-.4.71-1.41 0-1.82L9.57 4.24A1.04 1.04 0 0 0 8 5.14z"/>
                    </svg>
                </span>
                <span class="btn-text-default">Proses Analisis</span>
            </button>

            <button type="button" id="btnReset" class="btn-reset">
                <span class="btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M3 12a9 9 0 1 0 3-6.7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3 4v5h5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Reset</span>
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
                <strong>0.02</strong>
            </div>

            <div class="parameter-item">
                <span>Minimum Confidence</span>
                <strong>0.6</strong>
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
            <p>Membersihkan dan memvalidasi data penjualan</p>
        </div>

        <div class="process-step" data-step="2">
            <span class="process-icon"></span>
            <p>Mengubah waktu transaksi menjadi kategori shift</p>
        </div>

        <div class="process-step" data-step="3">
            <span class="process-icon"></span>
            <p>Mengidentifikasi kanal penjualan offline dan online</p>
        </div>

        <div class="process-step" data-step="4">
            <span class="process-icon"></span>
            <p>Membentuk basket transaksi per kanal</p>
        </div>

        <div class="process-step" data-step="5">
            <span class="process-icon"></span>
            <p>Menjalankan algoritma FP-Growth</p>
        </div>

        <div class="process-step" data-step="6">
            <span class="process-icon"></span>
            <p>Membentuk association rules</p>
        </div>

        <div class="process-step" data-step="7">
            <span class="process-icon"></span>
            <p>Menyimpan dan menampilkan hasil analisis</p>
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
        const clientErrorContainer = document.getElementById('clientErrorContainer');

        const maxFileSize = 20 * 1024 * 1024;

        let stepInterval = null;
        let isSubmitting = false;

        function showClientError(message) {
            if (!clientErrorContainer) return;

            clientErrorContainer.innerHTML = '';

            const alert = document.createElement('div');
            alert.className = 'analysis-alert analysis-alert-error';
            alert.setAttribute('role', 'alert');
            alert.textContent = message;

            clientErrorContainer.appendChild(alert);

            clientErrorContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        function clearClientError() {
            if (!clientErrorContainer) return;
            clientErrorContainer.innerHTML = '';
        }

        function resetProcessSteps() {
            processSteps.forEach(function (step) {
                step.classList.remove('active', 'done');
            });
        }

        function stopProcessAnimation() {
            if (stepInterval) {
                clearInterval(stepInterval);
                stepInterval = null;
            }

            resetProcessSteps();
        }

        function startProcessAnimation() {
            stopProcessAnimation();

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
                    stepInterval = null;
                }
            }, 900);
        }

        function getFileExtension(file) {
            if (!file || !file.name) {
                return '';
            }

            const fileNameParts = file.name.split('.');

            if (fileNameParts.length <= 1) {
                return '';
            }

            return fileNameParts.pop().toLowerCase();
        }

        function isValidExcelFile(file) {
            const allowedExtensions = ['xlsx', 'xls'];
            const extension = getFileExtension(file);

            return allowedExtensions.includes(extension);
        }

        function validateSelectedFile(file) {
            if (!file) {
                return 'Silakan pilih file Excel terlebih dahulu sebelum memproses analisis.';
            }

            if (!isValidExcelFile(file)) {
                return 'Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.';
            }

            if (file.size > maxFileSize) {
                return 'Ukuran file terlalu besar. Maksimal ukuran file adalah 20 MB.';
            }

            return null;
        }

        function setButtonLoading() {
            if (!btnProses) return;

            btnProses.disabled = true;
            btnProses.innerHTML = `
                <span class="btn-loading-content">
                    <span class="btn-mini-spinner"></span>
                    <span>Memproses...</span>
                </span>
            `;
        }

        function resetButtonLoading() {
            if (!btnProses) return;

            btnProses.disabled = false;
            btnProses.innerHTML = `
                <span class="btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5.14v13.72c0 .8.87 1.3 1.57.9l10.28-5.86c.71-.4.71-1.41 0-1.82L9.57 4.24A1.04 1.04 0 0 0 8 5.14z"/>
                    </svg>
                </span>
                <span class="btn-text-default">Proses Analisis</span>
            `;
        }

        function resetFileInput() {
            if (fileInput) {
                fileInput.value = '';
            }

            if (fileName) {
                fileName.textContent = 'Belum ada file yang dipilih';
                fileName.classList.remove('file-selected');
            }
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                clearClientError();

                const selectedFile = fileInput.files && fileInput.files.length > 0
                    ? fileInput.files[0]
                    : null;

                if (!selectedFile) {
                    resetFileInput();
                    return;
                }

                const validationMessage = validateSelectedFile(selectedFile);

                if (validationMessage) {
                    resetFileInput();
                    showClientError(validationMessage);
                    return;
                }

                if (fileName) {
                    fileName.textContent = selectedFile.name;
                    fileName.classList.add('file-selected');
                }
            });
        }

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                if (isSubmitting) return;

                resetFileInput();
                clearClientError();
                stopProcessAnimation();
                resetButtonLoading();

                if (loadingAnalisis) {
                    loadingAnalisis.classList.add('hidden');
                }
            });
        }

        if (formAnalisis) {
            formAnalisis.addEventListener('submit', function (event) {
                clearClientError();

                if (isSubmitting) {
                    event.preventDefault();
                    return;
                }

                const selectedFile = fileInput && fileInput.files && fileInput.files.length > 0
                    ? fileInput.files[0]
                    : null;

                const validationMessage = validateSelectedFile(selectedFile);

                if (validationMessage) {
                    event.preventDefault();
                    showClientError(validationMessage);
                    return;
                }

                isSubmitting = true;
                setButtonLoading();

                if (loadingAnalisis) {
                    loadingAnalisis.classList.remove('hidden');
                    loadingAnalisis.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }

                startProcessAnimation();
            });
        }
    });
</script>

@endsection