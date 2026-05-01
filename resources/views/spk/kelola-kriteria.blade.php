<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kriteria — DRW Skincare SPK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --pink: #e8005a; --pink-light: #fff0f5; --pink-mid: #ff4d8d; --pink-dark: #b3004a; --pink-soft: #ffd6e8;
  --red: #ef4444; --red-light: #fef2f2; --green: #10b981; --green-light: #ecfdf5;
  --amber: #f59e0b; --amber-light: #fef3c7; --blue: #3b82f6; --blue-light: #eff6ff;
  --bg: #fff5f8; --surface: #ffffff; --border: #fce7ef; --border-strong: #f9a8c9;
  --text: #1a0a0f; --text-2: #5a3347; --text-3: #b07090;
  --sidebar-w: 220px; --radius: 10px; --radius-lg: 14px;
  --shadow: 0 1px 3px rgba(232,0,90,.06); --shadow-md: 0 4px 16px rgba(232,0,90,.10);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; font-size: 14px; }

/* SIDEBAR */
.sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; height: 100vh; position: sticky; top: 0; overflow-y: auto; box-shadow: 2px 0 12px rgba(232,0,90,.06); }
.sb-brand { padding: 22px 18px 16px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #e8005a08, #ff4d8d05); }
.sb-logo { display: flex; align-items: center; gap: 10px; }
.sb-logo-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(232,0,90,.3); }
.sb-logo-icon svg { width: 18px; height: 18px; stroke: #fff; fill: none; stroke-width: 2.2; }
.sb-logo-name { font-size: 13px; font-weight: 800; color: var(--text); line-height: 1.2; letter-spacing: -.3px; }
.sb-logo-sub { font-size: 10px; color: var(--text-3); margin-top: 1px; }
.sb-nav { flex: 1; padding: 14px 10px; }
.nav-section { margin-bottom: 6px; }
.nav-label { font-size: 10px; font-weight: 700; color: var(--text-3); letter-spacing: .08em; text-transform: uppercase; padding: 6px 8px 4px; }
.nav-item { display: flex; align-items: center; gap: 9px; padding: 9px 12px; border-radius: 9px; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-2); transition: all .15s; margin-bottom: 2px; position: relative; text-decoration: none; }
.nav-item:hover { background: var(--pink-light); color: var(--pink-dark); }
.nav-item.active { background: linear-gradient(135deg, var(--pink-light), #ffe4ef); color: var(--pink); font-weight: 700; }
.nav-item.active::before { content:''; position:absolute; left:0; top:6px; bottom:6px; width:3px; background: var(--pink); border-radius:0 3px 3px 0; }
.nav-item svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; }
.sb-footer { padding: 14px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 9px; }
.avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.sb-user-name { font-size: 12px; font-weight: 700; color: var(--text); }
.sb-user-role { font-size: 10px; color: var(--text-3); }

/* MAIN */
.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { height: 56px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; gap: 12px; position: sticky; top: 0; z-index: 10; }
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); flex: 1; }
.content { flex: 1; padding: 28px; overflow-y: auto; }
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
.page-header p { font-size: 13px; color: var(--text-3); margin-top: 4px; }

/* CARDS */
.card { background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 20px 22px; box-shadow: var(--shadow); margin-bottom: 16px; }
.card-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }

/* BUTTONS */
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border-strong); background: var(--surface); font-size: 12px; font-weight: 600; color: var(--text-2); cursor: pointer; font-family: inherit; transition: all .15s; text-decoration: none; }
.btn:hover { background: var(--bg); }
.btn-pink { background: linear-gradient(135deg, var(--pink), var(--pink-mid)); color: #fff; border: none; box-shadow: 0 2px 8px rgba(232,0,90,.25); }
.btn-pink:hover { opacity: .9; }
.btn-red { background: var(--red-light); color: var(--red); border-color: #fca5a5; }
.btn-red:hover { background: #fee2e2; }
.btn-sm { padding: 6px 12px; font-size: 11px; }
.btn-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

/* INFO BOX */
.info-box { display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; border-radius: 9px; font-size: 12px; margin-bottom: 14px; }
.info-box svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; margin-top: 1px; }
.info-pink { background: var(--pink-light); color: var(--pink-dark); border: 1px solid var(--pink-soft); }
.info-amber { background: var(--amber-light); color: #92400e; border: 1px solid #fcd34d; }

/* BADGE */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
.badge-pink { background: var(--pink-light); color: var(--pink); }
.badge-green { background: var(--green-light); color: #065f46; }
.badge-amber { background: var(--amber-light); color: #92400e; }

/* TABLE */
.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--pink-light); }
th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: var(--pink-dark); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); white-space: nowrap; }
td { padding: 11px 14px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--pink-light); }

/* FORM */
.form-input, .form-select { width: 100%; padding: 8px 12px; border: 1px solid var(--border-strong); border-radius: 8px; font-size: 13px; font-family: inherit; color: var(--text); background: var(--surface); outline: none; transition: border .15s; }
.form-input:focus, .form-select:focus { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(232,0,90,.08); }
.form-label { font-size: 12px; font-weight: 600; color: var(--text-2); display: block; margin-bottom: 5px; }
.form-group { margin-bottom: 14px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* SLIDER */
.slider-row { display: flex; align-items: center; gap: 12px; }
input[type="range"] { flex: 1; height: 6px; border-radius: 10px; background: var(--border); outline: none; -webkit-appearance: none; }
input[type="range"]::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 18px; height: 18px; border-radius: 50%; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); cursor: pointer; box-shadow: 0 2px 6px rgba(232,0,90,.3); }
input[type="range"]::-moz-range-thumb { width: 18px; height: 18px; border-radius: 50%; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); cursor: pointer; border: none; box-shadow: 0 2px 6px rgba(232,0,90,.3); }
.slider-val { font-size: 14px; font-weight: 700; color: var(--pink); min-width: 45px; text-align: right; font-family: 'DM Mono', monospace; }

/* PROGRESS BAR */
.progress-bar { height: 8px; background: var(--bg); border-radius: 20px; overflow: hidden; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--pink), var(--pink-mid)); border-radius: 20px; transition: width .5s ease; }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 48px 20px; color: var(--text-3); }
.empty-state svg { width: 44px; height: 44px; stroke: var(--border-strong); fill: none; stroke-width: 1.5; margin: 0 auto 12px; display: block; }
.empty-state p { font-size: 13px; line-height: 1.6; }

/* MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(26,10,15,.4); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: var(--radius-lg); padding: 24px; width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(232,0,90,.15); border: 1px solid var(--border); }
.modal-title { font-size: 15px; font-weight: 800; color: var(--text); margin-bottom: 18px; letter-spacing: -.3px; }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 18px; }

/* ALERT */
.alert { padding: 10px 14px; border-radius: 9px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
.alert-success { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }
.alert-error { background: var(--red-light); color: var(--red); border: 1px solid #fca5a5; }

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
      <a href="{{ route('kriteria.index') }}" class="nav-item active">
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
      <a href="{{ route('perhitungan.index') }}" class="nav-item">
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


<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-title">Kelola Kriteria</div>
  </div>

  <div class="content">
    <div class="page-header">
      <h1>Kelola Kriteria</h1>
      <p>Atur kriteria penilaian untuk sistem SPK MOORA. Total bobot harus 100%.</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    <!-- TOTAL BOBOT -->
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Total Bobot Kriteria</div>
          <div class="card-sub">Pastikan total bobot = 100% sebelum menjalankan perhitungan</div>
        </div>
        <button class="btn btn-pink btn-sm" onclick="openModal('modal-tambah')">
          <svg viewBox="0 0 16 16" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2.2"><path d="M8 3v10M3 8h10" stroke-linecap="round"/></svg>
          Tambah Kriteria
        </button>
      </div>
      <div class="progress-bar">
        <div class="progress-fill" style="width: {{ $totalBobot }}%"></div>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px">
        <span style="font-size:12px;color:var(--text-2)">Bobot terpakai</span>
        <span style="font-size:15px;font-weight:800;color:var(--pink);font-family:'DM Mono',monospace">{{ $totalBobot }}%</span>
      </div>
      @if($totalBobot < 100)
        <div class="info-box info-amber" style="margin-top:12px;margin-bottom:0">
          <svg viewBox="0 0 16 16"><path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/></svg>
          Sisa bobot yang bisa dialokasikan: <b>{{ 100 - $totalBobot }}%</b>
        </div>
      @endif
    </div>

    <!-- DAFTAR KRITERIA -->
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Daftar Kriteria</div>
          <div class="card-sub">{{ $kriterias->count() }} kriteria terdaftar</div>
        </div>
      </div>

      @if($kriterias->count() > 0)
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Kriteria</th>
              <th>Tipe</th>
              <th>Bobot</th>
              <th>Sumber Data</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($kriterias as $i => $k)
            <tr>
              <td style="color:var(--text-3);font-size:12px">{{ $i + 1 }}</td>
              <td style="font-weight:600">{{ $k->nama_kriteria }}</td>
              <td>
                @if(trim(strtolower($k->tipe_atribut)) == 'benefit')
                  <span class="badge badge-green">↑ Benefit</span>
                @else
                  <span class="badge badge-amber">↓ Cost</span>
                @endif
              </td>
              <td style="font-family:'DM Mono',monospace;font-weight:700;color:var(--pink)">{{ $k->bobot }}%</td>
              <td><span class="badge badge-pink">{{ $k->sumber_data }}</span></td>
              <td>
                <div style="display:flex;gap:6px">
                  <button class="btn btn-sm" onclick="openEdit({{ $k->id_kriteria }}, '{{ addslashes($k->nama_kriteria) }}', '{{ strtolower(trim($k->tipe_atribut)) }}', {{ $k->bobot }}, '{{ $k->sumber_data }}', '{{ $k->nama_kolom_excel ?? '' }}')">Edit</button>
                  <button class="btn btn-red btn-sm" onclick="openHapus({{ $k->id_kriteria }}, '{{ addslashes($k->nama_kriteria) }}')">Hapus</button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="empty-state">
        <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="3"/><path d="M20 6v4M20 30v4M6 20h4M30 20h4M10 10l2.8 2.8M27.2 27.2l2.8 2.8M10 30l2.8-2.8M27.2 12.8l2.8-2.8" stroke-linecap="round"/></svg>
        <p>Belum ada kriteria.<br>Tambah kriteria baru untuk memulai penilaian.</p>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal-overlay" id="modal-tambah">
  <div class="modal-box">
    <div class="modal-title">Tambah Kriteria Baru</div>
    <form method="POST" action="{{ route('kriteria.store') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Nama Kriteria</label>
        <input class="form-input" name="nama_kriteria" placeholder="cth: Daya Tarik Kemasan" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tipe Atribut</label>
          <select class="form-select" name="tipe_atribut" required>
            <option value="Benefit">Benefit (lebih besar = lebih baik)</option>
            <option value="Cost">Cost (lebih kecil = lebih baik)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Sumber Data</label>
          <select class="form-select" name="sumber_data" id="sumber-tambah" onchange="toggleKolomExcel('tambah', this.value)" required>
            <option value="Excel">Import Excel (otomatis)</option>
            <option value="Manual">Input Manual (di web)</option>
          </select>
        </div>
      </div>

      <!-- Field Nama Kolom Excel — tampil by default karena default sumber = Excel -->
      <div class="form-group" id="kolom-excel-group-tambah">
        <label class="form-label">Nama Kolom Excel <span style="color:var(--pink)">*</span></label>
        <input class="form-input" name="nama_kolom_excel" id="kolom-excel-tambah" placeholder="cth: HARGA JUAL" required>
        <div style="font-size:11px;color:var(--text-3);margin-top:4px">Nama kolom di file Excel, tidak case-sensitive. Contoh: STOCK AKHIR, TOTAL PENJUALAN</div>
      </div>

      <div class="form-group">
        <label class="form-label">Bobot (%)</label>
        <div class="slider-row">
          <input type="range" min="0" max="100" step="5" value="10" name="bobot" id="slider-tambah" oninput="document.getElementById('val-tambah').textContent=this.value+'%'">
          <span class="slider-val" id="val-tambah">10%</span>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn" onclick="closeModal('modal-tambah')">Batal</button>
        <button type="submit" class="btn btn-pink">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT -->
<div class="modal-overlay" id="modal-edit">
  <div class="modal-box">
    <div class="modal-title">Edit Kriteria</div>
    <form method="POST" id="form-edit">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label class="form-label">Nama Kriteria</label>
        <input class="form-input" name="nama_kriteria" id="edit-nama" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tipe Atribut</label>
          <select class="form-select" name="tipe_atribut" id="edit-tipe" required>
            <option value="Benefit">Benefit (lebih besar = lebih baik)</option>
            <option value="Cost">Cost (lebih kecil = lebih baik)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Sumber Data</label>
          <select class="form-select" name="sumber_data" id="edit-sumber" onchange="toggleKolomExcel('edit', this.value)" required>
            <option value="Excel">Import Excel (otomatis)</option>
            <option value="Manual">Input Manual (di web)</option>
          </select>
        </div>
      </div>
      
      <!-- Field Nama Kolom Excel - DI LUAR form-row -->
      <div class="form-group" id="kolom-excel-group-edit" style="display:none">
        <label class="form-label">Nama Kolom Excel</label>
        <input class="form-input" name="nama_kolom_excel" id="kolom-excel-edit" placeholder="Contoh: HARGA JUAL">
        <div style="font-size:11px;color:var(--text-3);margin-top:4px">Isi dengan nama kolom persis seperti di file Excel (case-sensitive)</div>
      </div>

      <div class="form-group">
        <label class="form-label">Bobot (%)</label>
        <div class="slider-row">
          <input type="range" min="0" max="100" step="5" name="bobot" id="edit-bobot" oninput="document.getElementById('val-edit').textContent=this.value+'%'">
          <span class="slider-val" id="val-edit">0%</span>
        </div>
      </div>
      <div class="modal-actions">
        <button type="button" class="btn" onclick="closeModal('modal-edit')">Batal</button>
        <button type="submit" class="btn btn-pink">Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL HAPUS -->
<div class="modal-overlay" id="modal-hapus">
  <div class="modal-box">
    <div class="modal-title">Hapus Kriteria?</div>
    <p id="hapus-text" style="font-size:13px;color:var(--text-2);line-height:1.6;margin-bottom:0"></p>
    <div class="modal-actions">
      <button class="btn" onclick="closeModal('modal-hapus')">Batal</button>
      <form method="POST" id="form-hapus" style="display:inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-red">Ya, Hapus</button>
      </form>
    </div>
  </div>
</div>

<script>
function openModal(id) { 
  document.getElementById(id).classList.add('open');
}

function closeModal(id) { 
  document.getElementById(id).classList.remove('open');
  // Reset modal tambah ke kondisi awal saat ditutup
  if (id === 'modal-tambah') {
    document.querySelector('#modal-tambah form').reset();
    document.getElementById('val-tambah').textContent = '10%';
    toggleKolomExcel('tambah', 'Excel'); // default kembali ke Excel
  }
}

function openEdit(id, nama, tipe, bobot, sumber, kolomExcel) {
  document.getElementById('edit-nama').value = nama;
  // Pastikan value match kapital: 'Benefit' atau 'Cost'
  const tipeKapital = tipe.charAt(0).toUpperCase() + tipe.slice(1).toLowerCase();
  document.getElementById('edit-tipe').value = tipeKapital;
  document.getElementById('edit-bobot').value = bobot;
  document.getElementById('edit-sumber').value = sumber;
  document.getElementById('kolom-excel-edit').value = kolomExcel || '';
  document.getElementById('val-edit').textContent = bobot + '%';
  document.getElementById('form-edit').action = '/kelola-kriteria/' + id;
  toggleKolomExcel('edit', sumber);
  openModal('modal-edit');
}

function openHapus(id, nama) {
  document.getElementById('hapus-text').textContent = 'Yakin ingin menghapus kriteria "' + nama + '"? Data nilai produk terkait juga akan terhapus.';
  document.getElementById('form-hapus').action = '/kelola-kriteria/' + id;
  openModal('modal-hapus');
}

function toggleKolomExcel(mode, value) {
  const group = document.getElementById('kolom-excel-group-' + mode);
  const input = document.getElementById('kolom-excel-' + mode);
  if (value === 'Excel') {
    group.style.display = 'block';
    input.required = true;
  } else {
    group.style.display = 'none';
    input.required = false;
    input.value = '';
  }
}

// Tutup modal kalau klik di luar
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});
</script>
</body>
</html>