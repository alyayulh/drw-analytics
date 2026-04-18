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
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
.page-header p { font-size: 13px; color: var(--text-3); margin-top: 4px; }
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
        <div class="sb-logo-sub">Analisis penjualan & produk </div>
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
      <a href="data-produk" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/></svg>
        Data Produk
      </a>
      <a href="input-permintaan" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v10" stroke-linecap="round"/></svg>
        Input Permintaan
      </a>
      <a href="#" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2" stroke-linecap="round"/></svg>
        Hitung SPK
      </a>
      <a href="#" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 12l4-4 3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Hasil & Laporan
      </a>
      <a href="#" class="nav-item">
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
    <div class="avatar">AD</div>
    <div>
      <div class="sb-user-name">Administrator</div>
      <div class="sb-user-role">Admin</div>
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
    <div class="page-header">
      <h1>Selamat datang 👋</h1>
      <p>Ringkasan sistem pendukung keputusan promosi produk DRW Skincare Banjarmasin.</p>
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

</body>
</html>