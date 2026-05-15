<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — DRW Skincare SPK</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex" style="background: linear-gradient(135deg, #fff0f5 0%, #ffe4ee 50%, #ffd6e8 100%);">

    {{-- Sisi kiri — branding --}}
    <div class="hidden lg:flex lg:w-1/2 flex-col items-center justify-center p-12 relative overflow-hidden" style="background: linear-gradient(160deg, #e8005a 0%, #ff4d8d 60%, #ff85b3 100%);">
        <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW Skincare" class="w-48 mb-6 brightness-0 invert">
        <h2 class="text-white text-2xl font-bold text-center mb-3">
            DRW SKINCARE BANJARMASIN
        </h2>
        <p class="text-white text-center text-base font-semibold leading-relaxed max-w-xs">
            Tentukan prioritas produk promosi dan pahami pola penjualan dengan lebih mudah, cepat dan akurat.
        </p>
        {{-- Decorative circles --}}
        <div class="absolute top-10 left-10 w-32 h-32 rounded-full opacity-10" style="background:white"></div>
        <div class="absolute bottom-20 left-20 w-48 h-48 rounded-full opacity-10" style="background:white"></div>
    </div>

    {{-- Sisi kanan — form login --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-10">

            {{-- Logo mobile --}}
            <div class="flex justify-center mb-6 lg:hidden">
                <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW Skincare" class="h-12">
            </div>

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Selamat datang!</h1>
                <p class="text-gray-400 text-sm mt-1">Silakan masuk untuk melanjutkan ke sistem</p>
            </div>

            {{-- Error session (flash dari controller selain dari validation) --}}
            @if(session('error'))
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- 
                Form login.
                novalidate = matikan tooltip native HTML5 (yg jelek tampilannya),
                kita pakai validation pesan kita sendiri (server + JS).
                id=loginForm untuk JS hook.
            --}}
            <form method="POST" action="/login" id="loginForm" novalidate>
                @csrf

                {{-- ========== USERNAME ========== --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="username"
                            id="usernameInput"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}"
                            aria-describedby="usernameError"
                            class="w-full pl-10 pr-4 py-3 border rounded-xl text-sm focus:outline-none focus:ring-2 transition
                                   {{ $errors->has('username')
                                      ? 'border-red-400 bg-red-50 focus:ring-red-300'
                                      : 'border-gray-200 focus:ring-pink-400 focus:border-transparent' }}"
                        >
                    </div>
                    {{-- Error message di bawah input --}}
                    <p id="usernameError"
                       class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 {{ $errors->has('username') ? '' : 'hidden' }}">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="usernameErrorText">{{ $errors->first('username') }}</span>
                    </p>
                </div>

                {{-- ========== KATA SANDI ========== --}}
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Kata sandi</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            name="password"
                            id="passwordInput"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            aria-describedby="passwordError"
                            class="w-full pl-10 pr-4 py-3 border rounded-xl text-sm focus:outline-none focus:ring-2 transition
                                   {{ $errors->has('password')
                                      ? 'border-red-400 bg-red-50 focus:ring-red-300'
                                      : 'border-gray-200 focus:ring-pink-400 focus:border-transparent' }}"
                        >
                    </div>
                    {{-- Error message di bawah input.
                         Khusus password: hanya tampil kalau pesannya BERISI (bukan spasi/kosong).
                         Pesan ' ' (spasi) dipakai sbg trik agar border merah aktif saat kredensial salah,
                         tanpa duplikasi pesan (pesan utama sudah di bawah username). --}}
                    @php $passwordError = trim($errors->first('password')); @endphp
                    <p id="passwordError"
                       class="mt-1.5 flex items-center gap-1.5 text-xs text-red-600 {{ $passwordError !== '' ? '' : 'hidden' }}">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="passwordErrorText">{{ $passwordError }}</span>
                    </p>
                </div>

                {{-- Tombol Login --}}
                <button
                    type="submit"
                    class="w-full text-white font-semibold py-3 rounded-xl text-sm transition duration-150 shadow-lg hover:shadow-xl hover:opacity-90"
                    style="background: linear-gradient(135deg, #e8005a 0%, #ff4d8d 100%)"
                >
                    Masuk ke Sistem
                </button>

                {{-- Hint --}}
                <div class="mt-6 p-4 bg-pink-50 rounded-xl border border-pink-100">
                    <p class="text-xs text-gray-500 font-semibold mb-1">Akun Demo:</p>
                    <p class="text-xs text-gray-400">Admin: <b class="text-gray-600">admin</b> / <b class="text-gray-600">admin123</b></p>
                    <p class="text-xs text-gray-400">Manajer: <b class="text-gray-600">manajer</b> / <b class="text-gray-600">drw2025</b></p>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ Validasi client-side (UX) ============ 
         Validasi sebenarnya tetap di server (AuthController), ini hanya layer UX
         supaya feedback instan tanpa harus reload halaman.
    --}}
    <script>
    (function() {
        var form     = document.getElementById('loginForm');
        var username = document.getElementById('usernameInput');
        var password = document.getElementById('passwordInput');

        // Helper: tampilkan error di bawah satu input
        function showError(input, message) {
            var errorBox  = document.getElementById(input.id.replace('Input', 'Error'));
            var errorText = document.getElementById(input.id.replace('Input', 'ErrorText'));
            if (!errorBox || !errorText) return;
            errorText.textContent = message;
            errorBox.classList.remove('hidden');
            input.setAttribute('aria-invalid', 'true');
            input.classList.remove('border-gray-200', 'focus:ring-pink-400', 'focus:border-transparent');
            input.classList.add('border-red-400', 'bg-red-50', 'focus:ring-red-300');
        }

        // Helper: bersihkan error
        function clearError(input) {
            var errorBox = document.getElementById(input.id.replace('Input', 'Error'));
            if (errorBox) errorBox.classList.add('hidden');
            input.setAttribute('aria-invalid', 'false');
            input.classList.remove('border-red-400', 'bg-red-50', 'focus:ring-red-300');
            input.classList.add('border-gray-200', 'focus:ring-pink-400', 'focus:border-transparent');
        }

        // Bersihkan error saat user mulai mengetik (UX yg natural)
        [username, password].forEach(function(input) {
            if (!input) return;
            input.addEventListener('input', function() {
                if (input.value.trim() !== '') clearError(input);
            });
        });

        // Cegah submit kalau ada field kosong, fokus ke yg pertama bermasalah
        form.addEventListener('submit', function(e) {
            var hasError = false;
            var firstErrorInput = null;

            if (!username.value.trim()) {
                showError(username, 'Username wajib diisi.');
                if (!firstErrorInput) firstErrorInput = username;
                hasError = true;
            }
            if (!password.value.trim()) {
                showError(password, 'Kata sandi wajib diisi.');
                if (!firstErrorInput) firstErrorInput = password;
                hasError = true;
            }

            if (hasError) {
                e.preventDefault();
                if (firstErrorInput) firstErrorInput.focus();
            }
        });
    })();
    </script>

</body>
</html>