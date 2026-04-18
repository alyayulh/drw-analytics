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

            {{-- Error message --}}
            @if(session('error'))
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-5">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="/login">
                @csrf

                {{-- Username --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition"
                        >
                    </div>
                </div>

                {{-- kata sandi --}}
                <div class="mb-8">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Kata sandi</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-pink-400 focus:border-transparent transition"
                        >
                    </div>
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

</body>
</html>