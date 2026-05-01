<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hitung SPK — DRW Skincare</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --pink: #e8005a; --pink-light: #fff0f5; --pink-mid: #ff4d8d;
  --pink-dark: #b3004a; --pink-soft: #ffd6e8;
  --red: #ef4444; --red-light: #fef2f2;
  --green: #10b981; --green-light: #ecfdf5;
  --amber: #f59e0b; --amber-light: #fef3c7;
  --bg: #fff5f8; --surface: #ffffff;
  --border: #fce7ef; --border-strong: #f9a8c9;
  --text: #1a0a0f; --text-2: #5a3347; --text-3: #b07090;
  --sidebar-w: 220px; --radius: 10px; --radius-lg: 14px;
  --shadow: 0 1px 3px rgba(232,0,90,.06);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; font-size: 14px; }

.sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; height: 100vh; position: sticky; top: 0; overflow-y: auto; box-shadow: 2px 0 12px rgba(232,0,90,.06); }
.sb-brand { padding: 22px 18px 16px; border-bottom: 1px solid var(--border); }
.sb-logo { display: flex; align-items: center; gap: 10px; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 10px; overflow: hidden; }
.sb-logo-name { font-size: 13px; font-weight: 800; color: var(--text); line-height: 1.2; }
.sb-logo-sub { font-size: 10px; color: var(--text-3); margin-top: 1px; }
.sb-nav { flex: 1; padding: 14px 10px; }
.nav-section { margin-bottom: 6px; }
.nav-label { font-size: 10px; font-weight: 700; color: var(--text-3); letter-spacing: .08em; text-transform: uppercase; padding: 6px 8px 4px; }
.nav-item { display: flex; align-items: center; gap: 9px; padding: 9px 12px; border-radius: 9px; font-size: 13px; font-weight: 500; color: var(--text-2); transition: all .15s; margin-bottom: 2px; position: relative; text-decoration: none; }
.nav-item:hover { background: var(--pink-light); color: var(--pink-dark); }
.nav-item.active { background: linear-gradient(135deg, var(--pink-light), #ffe4ef); color: var(--pink); font-weight: 700; }
.nav-item.active::before { content:''; position:absolute; left:0; top:6px; bottom:6px; width:3px; background: var(--pink); border-radius:0 3px 3px 0; }
.nav-item svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; }
.sb-footer { padding: 14px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 9px; }
.avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.sb-user-name { font-size: 12px; font-weight: 700; color: var(--text); }
.sb-user-role { font-size: 10px; color: var(--text-3); }

.main { flex: 1; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }
.topbar { height: 56px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; position: sticky; top: 0; z-index: 10; }
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); }
.content { flex: 1; padding: 24px 28px; overflow-y: auto; }

.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 20px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
.page-header p { font-size: 12px; color: var(--text-3); margin-top: 3px; }

.alert { padding: 10px 14px; border-radius: 9px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
.alert-success { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }
.alert-error   { background: var(--red-light); color: var(--red); border: 1px solid #fca5a5; }

.stat-strip { display: flex; gap: 12px; margin-bottom: 20px; }
.stat-box { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 16px; flex: 1; }
.stat-label { font-size: 10px; color: var(--text-3); font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
.stat-val { font-size: 22px; font-weight: 800; color: var(--text); font-family: 'DM Mono', monospace; line-height: 1; }
.stat-sub { font-size: 11px; color: var(--text-3); margin-top: 3px; }

.two-col { display: grid; grid-template-columns: 360px 1fr; gap: 16px; align-items: start; }

.card { background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); box-shadow: var(--shadow); }
.card-hd { padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }
.card-body { padding: 16px 18px; }

.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; }
.badge-benefit { background: var(--green-light); color: #065f46; }
.badge-cost    { background: var(--amber-light); color: #92400e; }
.badge-pink    { background: var(--pink-light); color: var(--pink); }

.kriteria-item { margin-bottom: 16px; }
.kriteria-item:last-child { margin-bottom: 0; }
.kriteria-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
.kriteria-name { font-size: 12px; font-weight: 600; color: var(--text); }
.bobot-display { font-size: 13px; font-weight: 800; color: var(--pink); font-family: 'DM Mono', monospace; min-width: 38px; text-align: right; }

input[type="range"] { width: 100%; height: 5px; border-radius: 10px; outline: none; -webkit-appearance: none; cursor: pointer; background: linear-gradient(to right, var(--pink) 0%, var(--pink) var(--pct, 0%), var(--border-strong) var(--pct, 0%), var(--border-strong) 100%); }
input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; width: 16px; height: 16px; border-radius: 50%; background: var(--pink); cursor: pointer; box-shadow: 0 1px 4px rgba(232,0,90,.35); border: 2px solid #fff; }
input[type="range"]::-moz-range-thumb { width: 16px; height: 16px; border-radius: 50%; background: var(--pink); cursor: pointer; border: 2px solid #fff; }

.info-box { display: flex; align-items: flex-start; gap: 8px; padding: 9px 12px; border-radius: 8px; font-size: 12px; }
.info-box svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; margin-top: 1px; }
.info-amber { background: var(--amber-light); color: #92400e; border: 1px solid #fcd34d; }
.info-green { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }
.info-pink  { background: var(--pink-light);  color: var(--pink-dark); border: 1px solid var(--pink-soft); }

.form-input { width: 100%; padding: 9px 13px; border: 1px solid var(--border-strong); border-radius: 8px; font-size: 13px; font-family: inherit; color: var(--text); background: var(--surface); outline: none; transition: border .15s; }
.form-input:focus { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(232,0,90,.08); }
.form-input:disabled { background: var(--bg); color: var(--text-3); cursor: not-allowed; }
.form-label { font-size: 11px; font-weight: 600; color: var(--text-2); display: block; margin-bottom: 5px; }
.form-hint { font-size: 11px; color: var(--text-3); margin-top: 4px; }

.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border-strong); background: var(--surface); font-size: 12px; font-weight: 600; color: var(--text-2); cursor: pointer; font-family: inherit; transition: all .15s; text-decoration: none; }
.btn:hover { background: var(--bg); }
.btn-pink { background: linear-gradient(135deg, var(--pink), var(--pink-mid)); color: #fff; border: none; box-shadow: 0 2px 8px rgba(232,0,90,.25); }
.btn-pink:hover { opacity: .9; }
.btn-pink:disabled { opacity: .45; cursor: not-allowed; }
.btn-sm { padding: 5px 12px; font-size: 11px; }

/* MATRIKS */
.matrix-wrap { overflow-y: auto; overflow-x: auto; max-height: 430px; }
.matrix-wrap::-webkit-scrollbar { width: 5px; height: 5px; }
.matrix-wrap::-webkit-scrollbar-thumb { background: var(--border-strong); border-radius: 3px; }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
thead { background: var(--pink-light); position: sticky; top: 0; z-index: 2; }
th { padding: 9px 12px; text-align: left; font-size: 10px; font-weight: 700; color: var(--pink-dark); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); white-space: nowrap; }
th .sub { font-size: 9px; font-weight: 500; color: var(--text-3); display: block; text-transform: none; letter-spacing: 0; margin-top: 1px; }
td { padding: 8px 12px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--pink-light); }
.td-produk { font-weight: 600; color: var(--text); font-size: 12px; white-space: nowrap; max-width: 180px; overflow: hidden; text-overflow: ellipsis; }
.td-num { font-family: 'DM Mono', monospace; color: var(--text-2); font-size: 12px; }
.td-miss { color: #f59e0b; font-size: 14px; }
.empty-state { text-align: center; padding: 40px 20px; color: var(--text-3); font-size: 12px; }

/* RIWAYAT */
.riwayat-list { display: flex; flex-direction: column; gap: 8px; }
.riwayat-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: var(--bg); border-radius: 9px; border: 1px solid var(--border); }
.riwayat-periode { font-weight: 700; font-size: 12px; color: var(--text); }
.riwayat-meta { font-size: 11px; color: var(--text-3); margin-top: 1px; }
.riwayat-best { font-size: 11px; font-weight: 600; color: var(--pink); }

/* ── SIDEBAR ── */
.sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; height: 100vh; position: sticky; top: 0; overflow-y: auto; box-shadow: 2px 0 12px rgba(232,0,90,.06); }
.sb-brand { height: 56px; padding: 0 18px; display: flex; align-items: center; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #e8005a08, #ff4d8d05); }
.sb-logo { display: flex; align-items: center; gap: 10px; }
.sb-logo-name { font-size: 13px; font-weight: 800; color: var(--text); line-height: 1.2; letter-spacing: -.3px; }
.sb-logo-sub { font-size: 10px; color: var(--text-3); margin-top: 1px; }
.sb-nav { flex: 1; padding: 14px 10px; overflow-y: auto; }
.nav-section { margin-bottom: 6px; }
.nav-label { font-size: 10px; font-weight: 700; color: var(--text-3); letter-spacing: .08em; text-transform: uppercase; padding: 6px 8px 4px; }
.nav-item { display: flex; align-items: center; gap: 9px; padding: 9px 12px; border-radius: 9px; font-size: 13px; font-weight: 500; color: var(--text-2); transition: all .15s; margin-bottom: 2px; position: relative; text-decoration: none; }
.nav-item:hover { background: var(--pink-light); color: var(--pink-dark); }
.nav-item.active { background: linear-gradient(135deg, var(--pink-light), #ffe4ef); color: var(--pink); font-weight: 700; }
.nav-item.active::before { content:''; position:absolute; left:0; top:6px; bottom:6px; width:3px; background: var(--pink); border-radius:0 3px 3px 0; }
.nav-item svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; }
.nav-divider { height: 1px; background: var(--border); margin: 8px 10px; }
.sb-footer { padding: 14px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 9px; background: linear-gradient(135deg, #fff5f8, #fff); }
.avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.sb-user-name { font-size: 12px; font-weight: 700; color: var(--text); }
.sb-user-role { font-size: 10px; color: var(--text-3); }
.sb-logout { margin-left: auto; cursor: pointer; color: var(--text-3); }
.sb-logout:hover { color: var(--pink); }

</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon" style="background:none;box-shadow:none;">
        <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW" style="width:36px;height:36px;object-fit:contain;">
      </div>
      <div>
        <div class="sb-logo-name">DRW BANJARMASIN</div>
        <div class="sb-logo-sub">Analisis penjualan &amp; produk</div>
      </div>
    </div>
  </div>
  <div class="sb-nav">
    <div class="nav-section">
      <div class="nav-label">Menentukan Produk Promosi</div>
      <a href="{{ route('dashboard') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><rect x="2" y="2" width="5" height="5" rx="1.5"/><rect x="9" y="2" width="5" height="5" rx="1.5"/><rect x="2" y="9" width="5" height="5" rx="1.5"/><rect x="9" y="9" width="5" height="5" rx="1.5"/></svg>
        Dashboard
      </a>
      <a href="{{ route('kriteria.index') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="2"/><path d="M8 2v2M8 12v2M2 8h2M12 8h2M3.5 3.5l1.4 1.4M11 11l1.4 1.4M3.5 12.5l1.4-1.4M11 5l1.4-1.4" stroke-linecap="round"/></svg>
        Kelola Kriteria
      </a>
      @if(auth()->check() && auth()->user()->role === 'Admin')
      <a href="{{ route('produk.index') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/></svg>
        Data Produk
      </a>
      @endif
      <a href="{{ route('input.index') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v10" stroke-linecap="round"/></svg>
        Input Permintaan
      </a>
      <a href="{{ route('perhitungan.index') }}" class="nav-item active">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2" stroke-linecap="round"/></svg>
        Hitung SPK
      </a>
      <a href="{{ route('perhitungan.riwayat') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l-2 2" stroke-linecap="round"/></svg>
        Riwayat
      </a>
    </div>

    <div class="nav-divider"></div>

    <div class="nav-section">
      <div class="nav-label">Analisis Asosiasi</div>
      <a href="{{ route('asosiasi.dashboard') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><rect x="2" y="2" width="5" height="5" rx="1.5"/><rect x="9" y="2" width="5" height="5" rx="1.5"/><rect x="2" y="9" width="5" height="5" rx="1.5"/><rect x="9" y="9" width="5" height="5" rx="1.5"/></svg>
        Dashboard
      </a>
      @if(auth()->check() && auth()->user()->role === 'Admin')
      <a href="{{ route('asosiasi.analisis') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="6" cy="6" r="4"/><path d="M10 10l4 4" stroke-linecap="round"/></svg>
        Analisis Pola
      </a>
      @endif
      <a href="{{ route('asosiasi.riwayat') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l-2 2" stroke-linecap="round"/></svg>
        Riwayat Analisis
      </a>
    </div>
  </div>
  <div class="sb-footer">
    <div class="avatar">{{ strtoupper(substr(auth()->user()?->nama_lengkap ?? 'U', 0, 2)) }}</div>
    <div>
      <div class="sb-user-name">{{ auth()->user()?->nama_lengkap ?? '-' }}</div>
      <div class="sb-user-role">{{ auth()->user()?->role ?? '-' }}</div>
    </div>
    <div class="sb-logout">
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center" title="Logout">
          <svg viewBox="0 0 16 16" width="15" height="15"><path d="M10 3h3a1 1 0 011 1v8a1 1 0 01-1 1h-3M7 11l3-3-3-3M10 8H3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor" fill="none" stroke-width="1.8"/></svg>
        </button>
      </form>
    </div>
  </div>
</div>


<div class="main">
  <div class="topbar">
    <div class="topbar-title">Hitung SPK</div>
  </div>
  <div class="content">

    <div class="page-header">
      <h1>Hitung SPK MOORA</h1>
      <p>Sesuaikan bobot kriteria, cek data produk, lalu jalankan perhitungan.</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    {{-- STAT STRIP --}}
    <div class="stat-strip">
      <div class="stat-box">
        <div class="stat-label">Produk Siap Hitung</div>
        <div class="stat-val" style="color:var(--green)">{{ $produkLengkap }}</div>
        <div class="stat-sub">dari {{ $totalProduk }} produk</div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Total Kriteria</div>
        <div class="stat-val">{{ $kriterias->count() }}</div>
        <div class="stat-sub">kriteria aktif</div>
      </div>
      <div class="stat-box">
        <div class="stat-label">Total Bobot</div>
        <div class="stat-val" style="color:{{ $totalBobot == 100 ? 'var(--green)' : 'var(--red)' }}">{{ $totalBobot }}%</div>
        <div class="stat-sub">{{ $totalBobot == 100 ? 'Siap dihitung' : 'Harus = 100%' }}</div>
      </div>
    </div>

    {{-- TWO COLUMN --}}
    <div class="two-col">

      {{-- KIRI: slider bobot + form --}}
      <div style="display:flex;flex-direction:column;gap:14px">

        {{-- Card bobot --}}
        <div class="card">
          <div class="card-hd">
            <div>
              <div class="card-title">Atur bobot kriteria</div>
              <div class="card-sub">Geser slider untuk menyesuaikan</div>
            </div>
            <span id="total-badge" style="font-size:13px;font-weight:800;font-family:'DM Mono',monospace;color:{{ $totalBobot == 100 ? 'var(--green)' : 'var(--red)' }}">
              Total: {{ $totalBobot }}%
            </span>
          </div>
          <div class="card-body">
            @if($kriterias->isEmpty())
              <div class="info-box info-amber">
                <svg viewBox="0 0 16 16"><path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/></svg>
                Belum ada kriteria. Tambah di <a href="/kelola-kriteria" style="color:var(--pink);font-weight:700">Kelola Kriteria</a>.
              </div>
            @else
              @foreach($kriterias as $k)
              <div class="kriteria-item">
                <div class="kriteria-header">
                  <div style="display:flex;align-items:center;gap:6px">
                    <span class="kriteria-name">{{ $k->nama_kriteria }}</span>
                    @if(strtolower($k->tipe_atribut) === 'benefit')
                      <span class="badge badge-benefit">↑ Benefit</span>
                    @else
                      <span class="badge badge-cost">↓ Cost</span>
                    @endif
                  </div>
                  <span class="bobot-display" id="lbl-{{ $k->id_kriteria }}">{{ $k->bobot }}%</span>
                </div>
                <input type="range" min="0" max="100" step="5"
                       value="{{ $k->bobot }}"
                       data-id="{{ $k->id_kriteria }}"
                       class="bobot-slider"
                       style="--pct:{{ $k->bobot }}%"
                       oninput="onSliderChange(this)">
              </div>
              @endforeach

              <div id="bobot-info" class="info-box {{ $totalBobot == 100 ? 'info-green' : 'info-amber' }}" style="margin-top:14px;margin-bottom:0">
                <svg viewBox="0 0 16 16" id="bobot-info-icon">
                  @if($totalBobot == 100)
                    <path d="M3 8l4 4 6-6" stroke-linecap="round" stroke-linejoin="round"/>
                  @else
                    <path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/>
                  @endif
                </svg>
                <span id="bobot-info-text">
                  @if($totalBobot == 100)
                    Bobot sudah pas 100%. Siap dihitung.
                  @else
                    Sisa bobot: <b>{{ 100 - $totalBobot }}%</b>. Total harus tepat 100%.
                  @endif
                </span>
              </div>
            @endif
          </div>
        </div>

        {{-- Card form periode + submit --}}
        <div class="card">
          <div class="card-body">
            @if($produkLengkap < 2)
              <div class="info-box info-amber" style="margin-bottom:14px">
                <svg viewBox="0 0 16 16"><path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/></svg>
                Minimal 2 produk dengan data lengkap. Saat ini: <b>{{ $produkLengkap }}</b> produk siap.
              </div>
            @endif

            <form method="POST" action="{{ route('perhitungan.hitung') }}" id="form-hitung">
              @csrf
              <div id="hidden-bobots"></div>
              <div style="margin-bottom:12px">
                <label class="form-label">Nama Periode / Sesi Promosi</label>
                <input class="form-input" name="periode_data"
                       placeholder="cth: Ramadhan Sale April 2026"
                       required
                       {{ ($totalBobot != 100 || $produkLengkap < 2) ? 'disabled' : '' }}>
                <div class="form-hint">Nama ini muncul di riwayat perhitungan.</div>
              </div>
              <div style="display:flex;justify-content:flex-end">
                <button type="submit" class="btn btn-pink" id="btn-hitung"
                        {{ ($totalBobot != 100 || $produkLengkap < 2) ? 'disabled' : '' }}>
                  <svg viewBox="0 0 16 16" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2" stroke-linecap="round"/></svg>
                  Proses Perhitungan
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>{{-- end kiri --}}

      {{-- KANAN: Pratinjau matriks --}}
      <div class="card" style="overflow:hidden">
        <div class="card-hd">
          <div>
            <div class="card-title">Pratinjau matriks keputusan</div>
            <div class="card-sub">Data nilai tiap produk per kriteria</div>
          </div>
          <span class="badge badge-pink">{{ $produks->count() }} produk</span>
        </div>

        @if($produks->isEmpty())
          <div class="empty-state">
            <svg viewBox="0 0 40 40" width="36" height="36" style="margin:0 auto 10px;display:block;stroke:var(--border-strong);fill:none;stroke-width:1.5"><rect x="4" y="8" width="32" height="28" rx="2"/><path d="M12 18h16M12 24h10"/></svg>
            <p>Belum ada produk dengan data lengkap.<br>
            Upload data di <a href="/data-produk" style="color:var(--pink);font-weight:600">Data Produk</a>.</p>
          </div>
        @else
          <div class="matrix-wrap">
            <table>
              <thead>
                <tr>
                  <th style="min-width:160px;position:sticky;left:0;background:var(--pink-light);z-index:3">Produk</th>
                  @foreach($kriterias as $k)
                  <th style="min-width:120px">
                    {{ $k->nama_kriteria }}
                    <span class="sub">{{ strtolower($k->tipe_atribut) }}</span>
                  </th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($produks as $p)
                @php $nilaiMap = $p->nilaiProduk->keyBy('id_kriteria'); @endphp
                <tr>
                  <td class="td-produk" style="position:sticky;left:0;background:var(--surface);z-index:1" title="{{ $p->nama_produk }}">
                    {{ $p->nama_produk }}
                  </td>
                  @foreach($kriterias as $k)
                  @php $n = $nilaiMap->get($k->id_kriteria); @endphp
                  <td>
                    @if($n)
                      <span class="td-num">{{ number_format($n->nilai, 0, ',', '.') }}</span>
                    @else
                      <span class="td-miss">?</span>
                    @endif
                  </td>
                  @endforeach
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>

    </div>{{-- end two-col --}}

    {{-- RIWAYAT --}}
    @if($riwayat->count() > 0)
    <div class="card" style="margin-top:16px">
      <div class="card-hd">
        <div>
          <div class="card-title">Perhitungan Terakhir</div>
          <div class="card-sub">5 sesi terbaru</div>
        </div>
        <a href="{{ route('perhitungan.riwayat') }}" class="btn btn-sm">Lihat Semua</a>
      </div>
      <div class="card-body" style="padding-top:8px">
        <div class="riwayat-list">
          @foreach($riwayat as $r)
          <div class="riwayat-item">
            <div style="flex:1;min-width:0">
              <div class="riwayat-periode">{{ $r->periode_data }}</div>
              <div class="riwayat-meta">{{ $r->jumlah_produk }} produk · {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y, H:i') }}</div>
            </div>
            <div class="riwayat-best">🏆 {{ $r->produk_prioritas }}</div>
            <a href="{{ route('perhitungan.hasil', $r->id_perhitungan) }}" class="btn btn-sm">Lihat</a>
          </div>
          @endforeach
        </div>
      </div>
    </div>
    @endif

  </div>
</div>

<script>
const bobotState = @json($kriterias->pluck('bobot', 'id_kriteria')->map(fn($v) => (int)$v));

function onSliderChange(el) {
  const id  = el.dataset.id;
  const val = parseInt(el.value);
  bobotState[id] = val;
  document.getElementById('lbl-' + id).textContent = val + '%';
  el.style.setProperty('--pct', val + '%');
  const total = Object.values(bobotState).reduce((a, b) => a + b, 0);
  updateTotalUI(total);
}

function updateTotalUI(total) {
  const ok = total === 100;

  // Badge total
  const badge = document.getElementById('total-badge');
  badge.textContent = 'Total: ' + total + '%';
  badge.style.color = ok ? 'var(--green)' : 'var(--red)';

  // Info box
  const box  = document.getElementById('bobot-info');
  const icon = document.getElementById('bobot-info-icon');
  const text = document.getElementById('bobot-info-text');
  if (ok) {
    box.className = 'info-box info-green';
    icon.innerHTML = '<path d="M3 8l4 4 6-6" stroke-linecap="round" stroke-linejoin="round"/>';
    text.innerHTML = 'Bobot sudah pas 100%. Siap dihitung.';
  } else {
    const sisa = 100 - total;
    box.className = 'info-box info-amber';
    icon.innerHTML = '<path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/>';
    text.innerHTML = sisa > 0
      ? 'Sisa bobot: <b>' + sisa + '%</b>. Total harus tepat 100%.'
      : 'Kelebihan: <b>' + Math.abs(sisa) + '%</b>. Kurangi bobot salah satu kriteria.';
  }

  // Tombol & input
  const siapProduk = {{ $produkLengkap }} >= 2;
  const btn   = document.getElementById('btn-hitung');
  const input = document.querySelector('input[name="periode_data"]');
  const aktif = ok && siapProduk;
  btn.disabled   = !aktif;
  input.disabled = !aktif;
}

// Inject hidden bobot sebelum submit
document.getElementById('form-hitung').addEventListener('submit', function() {
  const c = document.getElementById('hidden-bobots');
  c.innerHTML = '';
  Object.entries(bobotState).forEach(([id, val]) => {
    const inp = document.createElement('input');
    inp.type  = 'hidden';
    inp.name  = 'bobot_override[' + id + ']';
    inp.value = val;
    c.appendChild(inp);
  });
});

// Init track fill
document.querySelectorAll('.bobot-slider').forEach(el => {
  el.style.setProperty('--pct', el.value + '%');
});
</script>

<script>
@if(session('error'))
  Swal.fire({ icon: 'error', title: 'Gagal!', html: '{{ session('error') }}', confirmButtonColor: '#e8005a' });
@endif
</script>
</body>
</html>