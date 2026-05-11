<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DRW Analytics')</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite([
        'resources/css/app.css',
        'resources/css/asosiasi-analisis.css',
        'resources/css/asosiasi-hasil.css',
        'resources/js/app.js',
        'resources/js/asosiasi-analisis.js'
    ])

    @stack('head')
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <svg viewBox="0 0 24 24">
                <path d="M5 19V9"></path>
                <path d="M12 19V5"></path>
                <path d="M19 19v-7"></path>
            </svg>
        </div>

        <div>
            <div class="brand-title">DRW<br>Analytics</div>
            <div class="brand-subtitle">Sistem Manajemen<br>Terintegrasi</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="menu-section">
            <div class="menu-label">Menu Utama</div>

            <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="menu-icon">⌞</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('input.index') }}" class="menu-link {{ request()->routeIs('input.*') ? 'active' : '' }}">
                <span class="menu-icon">▤</span>
                <span>Input Permintaan</span>
            </a>

            <a href="{{ route('perhitungan.index') }}" class="menu-link {{ request()->routeIs('perhitungan.*') && !request()->routeIs('perhitungan.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">▦</span>
                <span>Hitung SPK</span>
            </a>

            <a href="{{ route('perhitungan.riwayat') }}" class="menu-link {{ request()->routeIs('perhitungan.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">▣</span>
                <span>Hasil & Laporan</span>
            </a>

            <a href="{{ route('perhitungan.riwayat') }}" class="menu-link">
                <span class="menu-icon">↺</span>
                <span>Riwayat Perhitungan</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Pengaturan</div>

            <a href="{{ route('kriteria.index') }}" class="menu-link {{ request()->routeIs('kriteria.*') ? 'active' : '' }}">
                <span class="menu-icon">⚙</span>
                <span>Kelola Kriteria</span>
            </a>
        </div>

        <div class="menu-section">
            <div class="menu-label">Analisis Asosiasi</div>

            <a href="{{ route('asosiasi.dashboard') }}" class="menu-link {{ request()->routeIs('asosiasi.dashboard') ? 'active' : '' }}">
                <span class="menu-icon">↗</span>
                <span>Dashboard</span>
            </a>

            @if(auth()->check() && auth()->user()->role === 'Admin')
                <a href="{{ route('asosiasi.analisis') }}" class="menu-link {{ request()->routeIs('asosiasi.analisis') ? 'active' : '' }}">
                    <span class="menu-icon">▤</span>
                    <span>Analisis Data</span>
                </a>
            @endif

            <a href="{{ route('asosiasi.riwayat') }}" class="menu-link {{ request()->routeIs('asosiasi.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">↺</span>
                <span>Riwayat Analisis</span>
            </a>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="user-avatar">
            {{ strtoupper(substr(auth()->user()?->nama_lengkap ?? 'AD', 0, 2)) }}
        </div>

        <div class="user-info">
            <div class="user-name">Admin User</div>
            <div class="user-role">Administrator</div>
        </div>
    </div>
</div>

<main class="main-content">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>