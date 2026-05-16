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
            <span class="brand-icon">
                <img src="https://pos.drwskincare.com/logo_drw.svg"
                     alt="DRW Skincare"
                     style="width:36px; height:36px; object-fit:contain;">
            </span>

            <div>
                <div class="brand-title">DRW BANJARMASIN</div>
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
                <span>Hitung SPK</span>
            </a>

            <a href="{{ route('perhitungan.riwayat') }}" class="menu-link {{ request()->routeIs('perhitungan.riwayat') ? 'active' : '' }}">
                <span class="menu-icon">
                    <svg viewBox="0 0 16 16">
                        <circle cx="8" cy="8" r="6"/>
                        <path d="M8 5v3l-2 2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Riwayat</span>
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
<div class="sb-footer">

    <div class="avatar">
        AD
    </div>

    <div>
        <div class="sb-user-name">Administrator</div>
        <div class="sb-user-role">Admin</div>
    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf

        <button type="submit" class="sb-logout">

            <svg xmlns="http://www.w3.org/2000/svg"
                 width="20"
                 height="20"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 stroke-linecap="round"
                 stroke-linejoin="round">

                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>

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