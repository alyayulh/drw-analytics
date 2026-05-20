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
        'resources/css/asosiasi-dashboard.css',
        'resources/css/asosiasi-riwayat.css',
        'resources/css/asosiasi-layout.css',
        'resources/js/app.js',
        'resources/js/asosiasi-analisis.js'
    ])

    @stack('head')
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <div class="sb-logo-icon" style="background:none; box-shadow:none;">
                <img src="https://pos.drwskincare.com/logo_drw.svg"
                     alt="DRW Skincare"
                     style="width:36px; height:36px; object-fit:contain;">
            </div>

            <div>
                <div class="brand-title">DRW SKINCARE</div>
                <div class="brand-subtitle">Analisis penjualan & produk</div>
            </div>
        </div>
    </div>

    <div class="sidebar-menu">

        <div class="menu-section">
            <div class="menu-label">Penentuan Produk Promosi</div>

            <a href="{{ route('dashboard') }}" class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <rect x="2" y="2" width="5" height="5" rx="1.5"/>
                        <rect x="9" y="2" width="5" height="5" rx="1.5"/>
                        <rect x="2" y="9" width="5" height="5" rx="1.5"/>
                        <rect x="9" y="9" width="5" height="5" rx="1.5"/>
                    </svg>
                </span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('kriteria.index') }}" class="menu-link {{ request()->routeIs('kriteria.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <circle cx="8" cy="8" r="2"/>
                        <path d="M8 2v2M8 12v2M2 8h2M12 8h2M3.5 3.5l1.4 1.4M11 11l1.4 1.4M3.5 12.5l1.4-1.4M11 5l1.4-1.4" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Kelola Kriteria</span>
            </a>

            <a href="{{ route('input.index') }}" class="menu-link {{ request()->routeIs('input.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/>
                    </svg>
                </span>
                <span>Data Produk</span>
            </a>

            <a href="{{ route('input.index') }}" class="menu-link {{ request()->routeIs('input.*') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14 3v10" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Input Permintaan</span>
            </a>

            <a href="{{ route('perhitungan.index') }}" class="menu-link {{ request()->routeIs('perhitungan.*') && !request()->routeIs('perhitungan.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <rect x="3" y="3" width="10" height="10" rx="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M6.5 6.5h3" stroke-linecap="round"/>
                        <path d="M6.5 8.5h3" stroke-linecap="round"/>
                        <path d="M6.5 10.5h3" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Menghitung Prioritas</span>
            </a>

            <a href="{{ route('perhitungan.riwayat') }}" class="menu-link {{ request()->routeIs('perhitungan.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <circle cx="8" cy="8" r="6"/>
                        <path d="M8 5v3l-2 2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Riwayat Perhitungan</span>
            </a>
        </div>

        <div class="menu-divider"></div>

        <div class="menu-section">
            <div class="menu-label">Pola & Insight Penjualan</div>

            <a href="{{ route('asosiasi.dashboard') }}" class="menu-link {{ request()->routeIs('asosiasi.dashboard') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <rect x="2" y="2" width="5" height="5" rx="1.5"/>
                        <rect x="9" y="2" width="5" height="5" rx="1.5"/>
                        <rect x="2" y="9" width="5" height="5" rx="1.5"/>
                        <rect x="9" y="9" width="5" height="5" rx="1.5"/>
                    </svg>
                </span>
                <span>Dashboard Insight</span>
            </a>

            @if(auth()->check() && auth()->user()->role === 'Admin')
                <a href="{{ route('asosiasi.analisis') }}" class="menu-link {{ request()->routeIs('asosiasi.analisis') ? 'active' : '' }}">
                    <span class="menu-icon">
                        <svg viewBox="0 0 16 16">
                            <circle cx="7" cy="7" r="4"/>
                            <path d="M10 10l3.5 3.5" stroke-linecap="round"/>
                            <path d="M5.5 8.5V6.8" stroke-linecap="round"/>
                            <path d="M7 8.5V5.5" stroke-linecap="round"/>
                            <path d="M8.5 8.5V4.5" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>Analisis Pola</span>
                </a>
            @endif

            <a href="{{ route('asosiasi.riwayat') }}" class="menu-link {{ request()->routeIs('asosiasi.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <circle cx="8" cy="8" r="6"/>
                        <path d="M8 5v3l-2 2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Riwayat Analisis</span>
            </a>
        </div>

    </div>
    <!-- SIDEBAR FOOTER -->
@php
    $role = strtolower(auth()->user()->role ?? 'user');

    if ($role === 'manajer') {
        $displayName = 'Manajer DRW';
        $displayRole = 'Manajer';
        $avatarText = 'MA';
    } else {
        $displayName = 'Administrator';
        $displayRole = 'Admin';
        $avatarText = 'AD';
    }
@endphp

<div class="sb-footer">
    <div class="avatar">{{ $avatarText }}</div>

    <div class="sb-user-text">
        <div class="sb-user-name">{{ $displayName }}</div>
        <div class="sb-user-role">{{ $displayRole }}</div>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="sb-logout">
        @csrf

        <button type="submit" class="logout-button" title="Logout">
            <svg viewBox="0 0 16 16" width="15" height="15">
                <path d="M10 3h3a1 1 0 011 1v8a1 1 0 01-1 1h-3M7 11l3-3-3-3M10 8H3"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke="currentColor"
                      fill="none"
                      stroke-width="1.8"/>
            </svg>
        </button>
    </form>
</div>
</div>

<main class="main-content">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>