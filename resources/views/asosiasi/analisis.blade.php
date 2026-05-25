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
            <input type="file" name="file" id="file" accept=".xlsx,.xls" hidden>

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
            <div>ⓘ Dataset minimal harus memiliki kolom nomor transaksi, produk, operator, dan waktu/tanggal transaksi</div>
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
        const clientErrorContainer = document.getElementById('clientErrorContainer');

        let stepInterval = null;
        let isSubmitting = false;

        function showClientError(message) {
            if (!clientErrorContainer) return;

            clientErrorContainer.innerHTML = '';

            const alert = document.createElement('div');
            alert.className = 'analysis-alert analysis-alert-error';
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

        function isValidExcelFile(file) {
            if (!file) {
                return false;
            }

            const fileNameLower = file.name.toLowerCase();
            const allowedExtensions = ['.xlsx', '.xls'];

            return allowedExtensions.some(function (extension) {
                return fileNameLower.endsWith(extension);
            });
        }

        function hideLoading() {
            if (loadingAnalisis) {
                loadingAnalisis.classList.add('hidden');
            }
        }

        function showLoading() {
            if (loadingAnalisis) {
                loadingAnalisis.classList.remove('hidden');

                setTimeout(function () {
                    loadingAnalisis.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 100);
            }
        }

        function resetButtonState() {
            if (btnProses) {
                btnProses.disabled = false;
                btnProses.innerHTML = '▷ Proses Analisis';
            }

            if (btnReset) {
                btnReset.disabled = false;
            }
        }

        function setValidatingButtonState() {
            if (btnProses) {
                btnProses.disabled = true;
                btnProses.innerHTML = `
                    <span class="btn-loading-content">
                        <span class="btn-mini-spinner"></span>
                        Memvalidasi...
                    </span>
                `;
            }

            if (btnReset) {
                btnReset.disabled = true;
            }
        }

        function setProcessingButtonState() {
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
        }

        async function validateDatasetFormat(selectedFile) {
            const tokenInput = formAnalisis.querySelector('input[name="_token"]');

            const formData = new FormData();
            formData.append('file', selectedFile);

            if (tokenInput) {
                formData.append('_token', tokenInput.value);
            }

            const response = await fetch("{{ route('asosiasi.validasiFormat') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            let result;

            try {
                result = await response.json();
            } catch (error) {
                result = {
                    valid: false,
                    message: 'Terjadi kesalahan saat memvalidasi dataset.'
                };
            }

            if (!response.ok || !result.valid) {
                return {
                    valid: false,
                    message: result.message || 'Format dataset tidak sesuai.'
                };
            }

            return {
                valid: true,
                message: result.message || 'Format dataset sesuai.'
            };
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                clearClientError();
                hideLoading();
                stopProcessAnimation();
                resetButtonState();
                isSubmitting = false;

                if (this.files && this.files.length > 0) {
                    const selectedFile = this.files[0];

                    if (fileName) {
                        fileName.textContent = selectedFile.name;
                    }

                    if (!isValidExcelFile(selectedFile)) {
                        showClientError('Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.');
                    }
                } else {
                    if (fileName) {
                        fileName.textContent = 'Belum ada file yang dipilih';
                    }
                }
            });
        }

        if (btnReset) {
            btnReset.addEventListener('click', function () {
                clearClientError();

                if (fileInput) {
                    fileInput.value = '';
                }

                if (fileName) {
                    fileName.textContent = 'Belum ada file yang dipilih';
                }

                hideLoading();
                stopProcessAnimation();
                resetButtonState();
                isSubmitting = false;
            });
        }

        if (formAnalisis) {
            formAnalisis.addEventListener('submit', async function (event) {
                if (isSubmitting) {
                    return;
                }

                event.preventDefault();

                clearClientError();
                hideLoading();
                stopProcessAnimation();
                resetButtonState();

                if (!fileInput.files || fileInput.files.length === 0) {
                    showClientError('File dataset wajib diunggah.');
                    return;
                }

                const selectedFile = fileInput.files[0];

                if (!isValidExcelFile(selectedFile)) {
                    showClientError('Format file tidak sesuai. Gunakan file Excel dengan format .xlsx atau .xls.');
                    return;
                }

                setValidatingButtonState();

                try {
                    const validationResult = await validateDatasetFormat(selectedFile);

                    if (!validationResult.valid) {
                        showClientError(validationResult.message);
                        hideLoading();
                        stopProcessAnimation();
                        resetButtonState();
                        isSubmitting = false;
                        return;
                    }

                    showLoading();
                    setProcessingButtonState();
                    startProcessAnimation();

                    isSubmitting = true;
                    formAnalisis.submit();

                } catch (error) {
                    showClientError('Terjadi kesalahan saat memvalidasi dataset.');
                    hideLoading();
                    stopProcessAnimation();
                    resetButtonState();
                    isSubmitting = false;
                }
            });
        }
    });
</script>

@endsection