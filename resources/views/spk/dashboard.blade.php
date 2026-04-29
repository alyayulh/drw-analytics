<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — DRW Skincare SPK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
:root {
  --pink: #e8005a;
  --pink-light: #fff0f5;
  --pink-mid: #ff4d8d;
  --pink-dark: #b3004a;
  --pink-soft: #ffd6e8;
  --amber: #f59e0b;
  --amber-light: #fef3c7;
  --red: #ef4444;
  --red-light: #fef2f2;
  --blue: #3b82f6;
  --blue-light: #eff6ff;
  --green: #10b981;
  --green-light: #ecfdf5;
  --purple: #8b5cf6;
  --purple-light: #f5f3ff;
  --bg: #fff5f8;
  --surface: #ffffff;
  --border: #fce7ef;
  --border-strong: #f9a8c9;
  --text: #1a0a0f;
  --text-2: #5a3347;
  --text-3: #b07090;
  --sidebar-w: 220px;
  --radius: 10px;
  --radius-lg: 14px;
  --shadow: 0 1px 3px rgba(232,0,90,.06), 0 1px 2px rgba(232,0,90,.04);
  --shadow-md: 0 4px 16px rgba(232,0,90,.10);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; font-size: 14px; }
code, .mono { font-family: 'DM Mono', monospace; }
.sidebar {
  width: var(--sidebar-w); min-width: var(--sidebar-w);
  background: var(--surface);
  border-right: 1px solid var(--border);
  display: flex; flex-direction: column; height: 100vh;
  position: sticky; top: 0; overflow-y: auto;
  box-shadow: 2px 0 12px rgba(232,0,90,.06);
}
.sb-brand {
  padding: 22px 18px 16px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(135deg, #e8005a08, #ff4d8d05);
}
.sb-logo { display: flex; align-items: center; gap: 10px; }
.sb-logo-icon {
  width: 36px; height: 36px;
  background: linear-gradient(135deg, var(--pink), var(--pink-mid));
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 12px rgba(232,0,90,.3);
}
.sb-logo-icon svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2.2; }
.sb-logo-name { font-size: 13px; font-weight: 800; color: var(--text); line-height: 1.2; letter-spacing: -.3px; }
.sb-logo-sub { font-size: 10px; color: var(--text-3); margin-top: 1px; }
.sb-nav { flex: 1; padding: 14px 10px; }
.nav-section { margin-bottom: 6px; }
.nav-label { font-size: 10px; font-weight: 700; color: var(--text-3); letter-spacing: .08em; text-transform: uppercase; padding: 6px 8px 4px; }
.nav-item {
  display: flex; align-items: center; gap: 9px; padding: 9px 12px;
  border-radius: 9px; cursor: pointer; font-size: 13px; font-weight: 500;
  color: var(--text-2); transition: all .15s; margin-bottom: 2px; position: relative;
  text-decoration: none;
}
.nav-item:hover { background: var(--pink-light); color: var(--pink-dark); }
.nav-item.active {
  background: linear-gradient(135deg, var(--pink-light), #ffe4ef);
  color: var(--pink); font-weight: 700;
}
.nav-item.active::before {
  content:''; position:absolute; left:0; top:6px; bottom:6px;
  width:3px; background: var(--pink); border-radius:0 3px 3px 0;
}
.nav-item svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; }
.sb-footer {
  padding: 14px; border-top: 1px solid var(--border);
  display: flex; align-items: center; gap: 9px;
  background: linear-gradient(135deg, #fff5f8, #fff);
}
.avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg, var(--pink), var(--pink-mid));
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.sb-user-name { font-size: 12px; font-weight: 700; color: var(--text); }
.sb-user-role { font-size: 10px; color: var(--text-3); }
.sb-logout { margin-left: auto; cursor: pointer; color: var(--text-3); }
.sb-logout:hover { color: var(--pink); }
.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar {
  height: 56px; background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; padding: 0 28px; gap: 12px;
  position: sticky; top: 0; z-index: 10;
  box-shadow: 0 2px 8px rgba(232,0,90,.05);
}
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); flex: 1; letter-spacing: -.3px; }
.topbar-pill {
  font-size: 11px; padding: 4px 12px; border-radius: 20px;
  background: linear-gradient(135deg, var(--pink), var(--pink-mid));
  color: #fff; font-weight: 700;
  box-shadow: 0 2px 8px rgba(232,0,90,.25);
}
.content { flex: 1; padding: 28px; overflow-y: auto; }
.grid3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px; }
.grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; align-items: stretch; }
.metric-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--radius-lg); padding: 18px 20px;
  box-shadow: var(--shadow); transition: box-shadow .2s, transform .2s;
}
.metric-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }
.metric-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.metric-icon svg { width: 20px; height: 20px; stroke: currentColor; fill: none; stroke-width: 1.8; }
.metric-label { font-size: 11px; color: var(--text-3); font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: .05em; }
.metric-value { font-size: 26px; font-weight: 800; color: var(--text); line-height: 1; letter-spacing: -.5px; }
.metric-sub { font-size: 11px; color: var(--text-3); margin-top: 5px; }
.card {
  background: var(--surface); border-radius: var(--radius-lg);
  border: 1px solid var(--border); padding: 20px 22px;
  box-shadow: var(--shadow); display: flex; flex-direction: column;
}
.card-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }
.btn-sm {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-strong);
  background: var(--surface); font-size: 11px; font-weight: 600;
  color: var(--pink); cursor: pointer; font-family: inherit;
  transition: all .15s; text-decoration: none;
}
.btn-sm:hover { background: var(--pink-light); }
.divider { height: 1px; background: var(--border); margin: 12px 0; }
.empty-state { text-align: center; padding: 40px 20px; color: var(--text-3); }
.empty-state svg { width: 40px; height: 40px; stroke: var(--border-strong); fill: none; stroke-width: 1.5; margin: 0 auto 12px; display: block; }
.empty-state p { font-size: 13px; line-height: 1.6; }
/* ── PAGE HEADER ROW ── */
.page-header-row {
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px; margin-bottom: 24px;
}
.page-header { flex: 1; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
.page-header p { font-size: 13px; color: var(--text-3); margin-top: 4px; }

/* ── TUTORIAL BANNER ── */
.tutorial-banner {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  background: var(--surface);
  border: 1px solid var(--border-strong);
  border-left: 4px solid var(--pink);
  border-radius: var(--radius-lg);
  padding: 10px 14px;
  margin-bottom: 0;
  box-shadow: var(--shadow);
  flex-shrink: 0;
  width: auto;
}
.tutorial-banner-left { display: flex; align-items: center; gap: 10px; }
.tutorial-banner-icon {
  width: 32px; height: 32px; border-radius: 9px;
  background: var(--pink-light);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tutorial-banner-icon svg { width: 16px; height: 16px; stroke: var(--pink); fill: none; stroke-width: 2; }
.tutorial-banner-title { font-size: 12px; font-weight: 700; color: var(--text); margin-bottom: 0; }
.tutorial-banner-sub { display: none; }
.tutorial-banner-btn {
  display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
  padding: 6px 12px; border-radius: 8px;
  background: var(--pink); color: #fff;
  font-size: 11px; font-weight: 700; font-family: inherit;
  border: none; cursor: pointer; transition: background .15s;
}
.tutorial-banner-btn:hover { background: var(--pink-dark); }
.tutorial-banner-btn svg { width: 12px; height: 12px; stroke: #fff; fill: none; stroke-width: 2; }

/* ── MODAL TUTORIAL ── */
.modal-overlay {
  display: none; position: fixed; inset: 0; z-index: 100;
  background: rgba(26,10,15,.45);
  align-items: center; justify-content: center; padding: 24px 16px;
}
.modal-overlay.show { display: flex; }
.modal-box {
  width: 100%; max-width: 540px;
  background: var(--surface);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(232,0,90,.18), 0 4px 16px rgba(0,0,0,.12);
  display: flex; flex-direction: column;
  max-height: 90vh; overflow: hidden;
  animation: modalIn .2s ease;
}
@keyframes modalIn {
  from { opacity: 0; transform: scale(.96) translateY(8px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 22px 14px;
  border-bottom: 1px solid var(--border);
}
.modal-header-title { font-size: 14px; font-weight: 800; color: var(--text); letter-spacing: -.3px; }
.modal-header-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }
.modal-close {
  width: 30px; height: 30px; border-radius: 8px;
  border: 1px solid var(--border); background: var(--surface);
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--text-3); transition: all .15s; flex-shrink: 0;
}
.modal-close:hover { background: var(--pink-light); color: var(--pink); border-color: var(--border-strong); }
.modal-close svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2.5; }

/* Progress bar */
.modal-progress { display: flex; gap: 5px; padding: 14px 22px 0; }
.prog-bar {
  flex: 1; height: 4px; border-radius: 4px;
  background: var(--border); transition: background .25s;
}
.prog-bar.active { background: var(--pink); }

/* Step body */
.modal-body { flex: 1; overflow-y: auto; padding: 18px 22px; min-height: 260px; }
.step-wrap { display: flex; gap: 14px; align-items: flex-start; }
.step-num {
  width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; font-weight: 800;
  border: 2px solid;
}
.step-num.pink  { background: var(--pink-light);   color: var(--pink);   border-color: var(--border-strong); }
.step-num.blue  { background: var(--blue-light);   color: var(--blue);   border-color: #bfdbfe; }
.step-num.purple{ background: var(--purple-light); color: var(--purple); border-color: #ddd6fe; }
.step-num.green { background: var(--green-light);  color: var(--green);  border-color: #a7f3d0; }
.step-info { flex: 1; }
.step-title { font-size: 14px; font-weight: 800; color: var(--text); margin-bottom: 3px; }
.step-meta { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.step-role {
  font-size: 10px; font-weight: 700; padding: 2px 9px; border-radius: 20px;
}
.step-role.blue   { background: var(--blue-light);   color: var(--blue); }
.step-role.purple { background: var(--purple-light); color: var(--purple); }
.step-role.green  { background: var(--green-light);  color: var(--green); }
.step-role.pink   { background: var(--pink-light);   color: var(--pink); }
.step-menu { font-size: 11px; color: var(--text-3); }
.step-menu span { font-weight: 600; color: var(--text-2); }
.step-actions { list-style: none; display: flex; flex-direction: column; gap: 7px; margin-bottom: 12px; }
.step-actions li { display: flex; align-items: flex-start; gap: 9px; font-size: 12px; color: var(--text-2); line-height: 1.5; }
.step-dot {
  width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; margin-top: 5px;
}
.step-dot.pink   { background: var(--pink); }
.step-dot.blue   { background: var(--blue); }
.step-dot.purple { background: var(--purple); }
.step-dot.green  { background: var(--green); }
.step-tip {
  background: var(--pink-light);
  border-left: 3px solid var(--border-strong);
  border-radius: 0 8px 8px 0;
  padding: 8px 12px;
  font-size: 11px; color: var(--pink-dark); line-height: 1.5;
}
.step-tip strong { font-weight: 700; }

/* Modal footer */
.modal-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 22px;
  border-top: 1px solid var(--border);
  gap: 12px;
}
.modal-counter { font-size: 11px; color: var(--text-3); font-weight: 600; }
.btn-nav {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 8px 16px; border-radius: 9px; font-size: 12px; font-weight: 700;
  font-family: inherit; cursor: pointer; transition: all .15s; border: 1px solid;
}
.btn-nav.secondary {
  background: var(--surface); color: var(--text-2);
  border-color: var(--border-strong);
}
.btn-nav.secondary:hover { background: var(--pink-light); }
.btn-nav.secondary:disabled { opacity: .35; cursor: not-allowed; }
.btn-nav.primary {
  background: var(--pink); color: #fff; border-color: var(--pink);
}
.btn-nav.primary:hover { background: var(--pink-dark); border-color: var(--pink-dark); }
.btn-nav svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2.5; }

.step-content { display: none; }
.step-content.active { display: block; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon" style="background:none; box-shadow:none;">
        <img src="https://pos.drwskincare.com/logo_drw.svg"
             alt="DRW Skincare"
             style="width:36px; height:36px; object-fit:contain;">
      </div>
      <div>
        <div class="sb-logo-name">DRW BANJARMASIN</div>
        <div class="sb-logo-sub">Analisis penjualan & produk</div>
      </div>
    </div>
  </div>
  <div class="sb-nav">
    <div class="nav-section">
      <div class="nav-label">Menu Utama</div>
      <a href="#" class="nav-item active">
        <svg viewBox="0 0 16 16"><rect x="2" y="2" width="5" height="5" rx="1.5"/><rect x="9" y="2" width="5" height="5" rx="1.5"/><rect x="2" y="9" width="5" height="5" rx="1.5"/><rect x="9" y="9" width="5" height="5" rx="1.5"/></svg>
        Dashboard
      </a>
      @if(auth()->user()->role === 'Admin')
      <a href="data-produk" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/></svg>
        Data Produk
      </a>
      @endif
      <a href="input-permintaan" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v10" stroke-linecap="round"/></svg>
        Input Permintaan
      </a>
      <a href="hitung-spk" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2" stroke-linecap="round"/></svg>
        Hitung SPK
      </a>
      <a href="hasil-perhitungan" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 12l4-4 3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Hasil & Laporan
      </a>
      <a href="riwayat" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l-2 2" stroke-linecap="round"/></svg>
        Riwayat
      </a>
    </div>
    <div class="nav-section">
      <div class="nav-label">Pengaturan</div>
      <a href="kelola-kriteria" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="2"/><path d="M8 2v2M8 12v2M2 8h2M12 8h2M3.5 3.5l1.4 1.4M11 11l1.4 1.4M3.5 12.5l1.4-1.4M11 5l1.4-1.4" stroke-linecap="round"/></svg>
        Kelola Kriteria
      </a>
    </div>
  </div>
  <div class="sb-footer">
    <div class="avatar">{{ strtoupper(substr(auth()->user()->nama_lengkap, 0, 2)) }}</div>
    <div>
      <div class="sb-user-name">{{ auth()->user()->nama_lengkap }}</div>
      <div class="sb-user-role">{{ auth()->user()->role }}</div>
    </div>
    <div class="sb-logout">
      <form method="POST" action="/logout">
        @csrf
        <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center" title="Logout">
          <svg viewBox="0 0 16 16" width="15" height="15"><path d="M10 3h3a1 1 0 011 1v8a1 1 0 01-1 1h-3M7 11l3-3-3-3M10 8H3" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor" fill="none" stroke-width="1.8"/></svg>
        </button>
      </form>
    </div>
  </div>
</div>

<div class="main">
  <div class="content">

    <div class="page-header-row">
      <div class="page-header">
        <h1>Selamat datang 👋</h1>
        <p>Ringkasan sistem pendukung keputusan promosi produk DRW Skincare Banjarmasin.</p>
      </div>

    {{-- ── TUTORIAL BANNER ────────────────────────────────────── --}}
    <div class="tutorial-banner">
      <div class="tutorial-banner-left">
        <div class="tutorial-banner-icon">
          <svg viewBox="0 0 24 24">
            <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
            <path d="M9 18h6"/><path d="M10 22h4"/>
          </svg>
        </div>
        <div>
          <div class="tutorial-banner-title">Panduan penggunaan</div>
        </div>
      </div>
      <button class="tutorial-banner-btn" onclick="openTutorial()">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        Lihat panduan
      </button>
    </div>
    {{-- ── END TUTORIAL BANNER ─────────────────────────────────── --}}

    </div>

    <div class="grid3">
      <div class="metric-card" style="border-left:3px solid var(--pink)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Total Produk</div>
            <div class="metric-value">0</div>
            <div class="metric-sub">produk terdaftar</div>
          </div>
          <div class="metric-icon" style="background:var(--pink-light);color:var(--pink)">
            <svg viewBox="0 0 16 16"><path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/></svg>
          </div>
        </div>
      </div>
      <div class="metric-card" style="border-left:3px solid var(--blue)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Kriteria Aktif</div>
            <div class="metric-value" style="color:var(--blue)">0</div>
            <div class="metric-sub">kriteria terdaftar</div>
          </div>
          <div class="metric-icon" style="background:var(--blue-light);color:var(--blue)">
            <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="2"/><path d="M8 2v2M8 12v2M2 8h2M12 8h2" stroke-linecap="round"/></svg>
          </div>
        </div>
      </div>
      <div class="metric-card" style="border-left:3px solid var(--purple)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Prioritas #1</div>
            <div class="metric-value" style="font-size:14px;color:var(--purple);padding-top:4px">Belum dihitung</div>
            <div class="metric-sub">jalankan perhitungan dulu</div>
          </div>
          <div class="metric-icon" style="background:var(--purple-light);color:var(--purple)">
            <svg viewBox="0 0 16 16"><path d="M8 2l1.5 3.5L13 6l-2.5 2.5.5 3.5L8 10.5 5 12l.5-3.5L3 6l3.5-.5L8 2z"/></svg>
          </div>
        </div>
      </div>
    </div>

    <div class="grid2">
      <div class="card">
        <div class="card-hd">
          <div>
            <div class="card-title">Top 5 Produk Rekomendasi</div>
            <div class="card-sub">Berdasarkan nilai Yi score tertinggi</div>
          </div>
          <a href="#" class="btn-sm">Lihat detail →</a>
        </div>
        <div style="flex:1;display:flex;align-items:center;justify-content:center;min-height:200px">
          <div class="empty-state">
            <svg viewBox="0 0 40 40"><rect x="4" y="20" width="6" height="16" rx="1"/><rect x="13" y="14" width="6" height="22" rx="1"/><rect x="22" y="8" width="6" height="28" rx="1"/><rect x="31" y="16" width="6" height="20" rx="1"/></svg>
            <p>Belum ada data ranking.<br>Hitung SPK terlebih dahulu.</p>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-hd">
          <div><div class="card-title">Bobot Kriteria</div></div>
          <a href="#" class="btn-sm">Kelola →</a>
        </div>
        <div style="flex:1">
          <div class="empty-state">
            <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="2"/><path d="M20 4v4M20 32v4M4 20h4M32 20h4" stroke-linecap="round"/></svg>
            <p>Belum ada kriteria.<br>Tambahkan di menu Kelola Kriteria.</p>
          </div>
        </div>
        <div class="divider"></div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span style="font-size:12px;color:var(--text-2)">Total bobot</span>
          <span style="font-size:13px;font-weight:700;color:var(--pink)">0%</span>
        </div>
      </div>
    </div>

  </div>
</div>


{{-- ── MODAL TUTORIAL ──────────────────────────────────────────── --}}
<div class="modal-overlay" id="modalTutorial" onclick="if(event.target===this) closeTutorial()">
  <div class="modal-box">

    {{-- Header --}}
    <div class="modal-header">
      <div>
        <div class="modal-header-title">Panduan Penggunaan Sistem</div>
        <div class="modal-header-sub">SPK MOORA — DRW Skincare Banjarmasin</div>
      </div>
      <button class="modal-close" onclick="closeTutorial()">
        <svg viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </button>
    </div>

    {{-- Progress --}}
    <div class="modal-progress">
      <div class="prog-bar active" id="bar0"></div>
      <div class="prog-bar" id="bar1"></div>
      <div class="prog-bar" id="bar2"></div>
      <div class="prog-bar" id="bar3"></div>
      <div class="prog-bar" id="bar4"></div>
    </div>

    {{-- Steps --}}
    <div class="modal-body">

      <div id="step-0" class="step-content active">
        <div class="step-wrap">
          <div class="step-num blue">1</div>
          <div class="step-info">
            <div class="step-title">Tambah data produk</div>
            <div class="step-meta">
              <span class="step-role blue">Admin</span>
              <span class="step-menu">Menu: <span>Data Produk</span></span>
            </div>
            <ul class="step-actions">
              <li><span class="step-dot blue"></span>Buka menu Data Produk di sidebar kiri.</li>
              <li><span class="step-dot blue"></span>Klik tombol Tambah Produk, isi nama produk dan data yang diperlukan.</li>
              <li><span class="step-dot blue"></span>Pastikan semua produk yang akan dibandingkan sudah terdaftar.</li>
              <li><span class="step-dot blue"></span>Data produk ini menjadi alternatif dalam perhitungan MOORA.</li>
            </ul>
            <div class="step-tip"><strong>Catatan:</strong> Minimal 2 produk dibutuhkan agar perhitungan SPK dapat dijalankan.</div>
          </div>
        </div>
      </div>

      <div id="step-1" class="step-content">
        <div class="step-wrap">
          <div class="step-num purple">2</div>
          <div class="step-info">
            <div class="step-title">Tentukan kriteria & bobot</div>
            <div class="step-meta">
              <span class="step-role purple">Manajer</span>
              <span class="step-menu">Menu: <span>Kelola Kriteria</span></span>
            </div>
            <ul class="step-actions">
              <li><span class="step-dot purple"></span>Buka menu Kelola Kriteria, lalu klik Tambah Kriteria.</li>
              <li><span class="step-dot purple"></span>Isi nama kriteria, pilih jenis: Benefit (makin besar makin baik) atau Cost (makin kecil makin baik).</li>
              <li><span class="step-dot purple"></span>Tentukan bobot masing-masing kriteria sesuai prioritas bisnis.</li>
              <li><span class="step-dot purple"></span>Pastikan total bobot semua kriteria = 100% sebelum lanjut.</li>
            </ul>
            <div class="step-tip"><strong>Catatan:</strong> Diskusikan bobot dengan manajer — ini mencerminkan prioritas bisnis.</div>
          </div>
        </div>
      </div>

      <div id="step-2" class="step-content">
        <div class="step-wrap">
          <div class="step-num blue">3</div>
          <div class="step-info">
            <div class="step-title">Isi nilai setiap produk</div>
            <div class="step-meta">
              <span class="step-role blue">Admin</span>
              <span class="step-menu">Menu: <span>Input Permintaan</span></span>
            </div>
            <ul class="step-actions">
              <li><span class="step-dot blue"></span>Buka menu Input Permintaan di sidebar.</li>
              <li><span class="step-dot blue"></span>Isi nilai tiap produk pada setiap kriteria yang sudah dibuat.</li>
              <li><span class="step-dot blue"></span>Gunakan data aktual dari laporan penjualan atau inventaris.</li>
              <li><span class="step-dot blue"></span>Periksa kembali semua nilai sebelum menyimpan.</li>
            </ul>
            <div class="step-tip"><strong>Catatan:</strong> Nilai yang salah atau kosong akan langsung mempengaruhi hasil perangkingan.</div>
          </div>
        </div>
      </div>

      <div id="step-3" class="step-content">
        <div class="step-wrap">
          <div class="step-num green">4</div>
          <div class="step-info">
            <div class="step-title">Jalankan perhitungan SPK</div>
            <div class="step-meta">
              <span class="step-role green">Admin / Manajer</span>
              <span class="step-menu">Menu: <span>Hitung SPK</span></span>
            </div>
            <ul class="step-actions">
              <li><span class="step-dot green"></span>Buka menu Hitung SPK di sidebar.</li>
              <li><span class="step-dot green"></span>Klik tombol Hitung — sistem akan normalisasi matriks secara otomatis.</li>
              <li><span class="step-dot green"></span>Tunggu hingga hasil Yi score tiap produk muncul.</li>
              <li><span class="step-dot green"></span>Hasil berupa peringkat produk dari Yi score tertinggi ke terendah.</li>
            </ul>
            <div class="step-tip"><strong>Catatan:</strong> Ulangi perhitungan setiap kali ada perubahan data produk, kriteria, atau bobot.</div>
          </div>
        </div>
      </div>

      <div id="step-4" class="step-content">
        <div class="step-wrap">
          <div class="step-num purple">5</div>
          <div class="step-info">
            <div class="step-title">Baca & gunakan hasil</div>
            <div class="step-meta">
              <span class="step-role purple">Manajer</span>
              <span class="step-menu">Menu: <span>Hasil & Laporan / Dashboard</span></span>
            </div>
            <ul class="step-actions">
              <li><span class="step-dot purple"></span>Buka menu Hasil & Laporan untuk melihat detail Yi score tiap produk.</li>
              <li><span class="step-dot purple"></span>Kembali ke Dashboard — Top 5 Rekomendasi akan terisi otomatis.</li>
              <li><span class="step-dot purple"></span>Produk dengan Yi score tertinggi adalah prioritas utama promosi.</li>
              <li><span class="step-dot purple"></span>Cek menu Riwayat untuk melihat histori perhitungan sebelumnya.</li>
            </ul>
            <div class="step-tip"><strong>Catatan:</strong> Hasil SPK adalah rekomendasi berbasis data — tetap pertimbangkan konteks lapangan.</div>
          </div>
        </div>
      </div>

    </div>

    {{-- Footer --}}
    <div class="modal-footer">
      <button id="btnPrev" class="btn-nav secondary" onclick="prevStep()" disabled>
        <svg viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg>
        Sebelumnya
      </button>
      <span class="modal-counter" id="stepCounter">1 / 5</span>
      <button id="btnNext" class="btn-nav primary" onclick="nextStep()">
        Berikutnya
        <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
      </button>
    </div>

  </div>
</div>
{{-- ── END MODAL TUTORIAL ───────────────────────────────────────── --}}


<script>
  let currentStep = 0;
  const totalSteps = 5;

  function openTutorial() {
    document.getElementById('modalTutorial').classList.add('show');
  }

  function closeTutorial() {
    document.getElementById('modalTutorial').classList.remove('show');
    currentStep = 0;
    showStep(0);
  }

  function showStep(index) {
    document.querySelectorAll('.step-content').forEach((el, i) => {
      el.classList.toggle('active', i === index);
    });
    for (let i = 0; i < totalSteps; i++) {
      document.getElementById('bar' + i).classList.toggle('active', i <= index);
    }
    document.getElementById('stepCounter').textContent = (index + 1) + ' / ' + totalSteps;
    document.getElementById('btnPrev').disabled = index === 0;

    const btnNext = document.getElementById('btnNext');
    if (index === totalSteps - 1) {
      btnNext.innerHTML = `Selesai <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5"><path d="M20 6 9 17l-5-5"/></svg>`;
      btnNext.onclick = closeTutorial;
    } else {
      btnNext.innerHTML = `Berikutnya <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:currentColor;fill:none;stroke-width:2.5"><path d="m9 18 6-6-6-6"/></svg>`;
      btnNext.onclick = nextStep;
    }
  }

  function nextStep() {
    if (currentStep < totalSteps - 1) { currentStep++; showStep(currentStep); }
  }
  function prevStep() {
    if (currentStep > 0) { currentStep--; showStep(currentStep); }
  }
</script>

</body>
</html> 