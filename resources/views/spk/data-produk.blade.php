<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Data Produk — DRW Skincare SPK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* Rating button untuk modal tambah */
.rating-btn-tambah {
  display: block;
  padding: 7px 4px;
  border: 2px solid var(--border-strong);
  border-radius: 7px;
  font-size: 13px;
  font-weight: 700;
  color: var(--text-2);
  background: var(--surface);
  cursor: pointer;
  transition: all .15s;
  text-align: center;
}
.radio-kriteria:checked + .rating-btn-tambah {
  border-color: var(--pink);
  background: var(--pink);
  color: #fff;
}
:root {
  --pink: #e8005a; --pink-light: #fff0f5; --pink-mid: #ff4d8d;
  --pink-dark: #b3004a; --pink-soft: #ffd6e8;
  --red: #ef4444; --red-light: #fef2f2;
  --green: #10b981; --green-light: #ecfdf5;
  --amber: #f59e0b; --amber-light: #fef3c7;
  --blue: #3b82f6; --blue-light: #eff6ff;
  --bg: #fff5f8; --surface: #ffffff;
  --border: #fce7ef; --border-strong: #f9a8c9;
  --text: #1a0a0f; --text-2: #5a3347; --text-3: #b07090;
  --sidebar-w: 220px; --radius: 10px; --radius-lg: 14px;
  --shadow: 0 1px 3px rgba(232,0,90,.06);
  --shadow-md: 0 4px 16px rgba(232,0,90,.10);
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

/* UPLOAD ZONE */
.upload-zone { display: block; border: 2px dashed var(--border-strong); border-radius: var(--radius-lg); padding: 28px; text-align: center; cursor: pointer; transition: all .2s; background: var(--pink-light); }
.upload-zone:hover { border-color: var(--pink); background: #fff0f5; }
.upload-icon { width: 44px; height: 44px; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; box-shadow: 0 4px 12px rgba(232,0,90,.25); }
.upload-icon svg { width: 22px; height: 22px; stroke: #fff; fill: none; stroke-width: 2; }

/* INFO BOX */
.info-box { display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; border-radius: 9px; font-size: 12px; margin-bottom: 14px; }
.info-box svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; margin-top: 1px; }
.info-pink { background: var(--pink-light); color: var(--pink-dark); border: 1px solid var(--pink-soft); }
.info-green { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }
.info-amber { background: var(--amber-light); color: #92400e; border: 1px solid #fcd34d; }

/* BADGE */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
.badge-gray { background: var(--bg); color: var(--text-3); border: 1px solid var(--border); }
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
.form-input { width: 100%; padding: 8px 12px; border: 1px solid var(--border-strong); border-radius: 8px; font-size: 13px; font-family: inherit; color: var(--text); background: var(--surface); outline: none; transition: border .15s; }
.form-input:focus { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(232,0,90,.08); }
.form-label { font-size: 12px; font-weight: 600; color: var(--text-2); display: block; margin-bottom: 5px; }
.form-group { margin-bottom: 14px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* EMPTY STATE */
.empty-state { text-align: center; padding: 48px 20px; color: var(--text-3); }
.empty-state svg { width: 44px; height: 44px; stroke: var(--border-strong); fill: none; stroke-width: 1.5; margin: 0 auto 12px; display: block; }
.empty-state p { font-size: 13px; line-height: 1.6; }

/* MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(26,10,15,.4); z-index: 100; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
.modal-overlay.open { display: flex; }
.modal-box { background: var(--surface); border-radius: var(--radius-lg); padding: 24px; width: 100%; max-width: 420px; box-shadow: 0 20px 60px rgba(232,0,90,.15); border: 1px solid var(--border); }
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
      <a href="{{ route('produk.index') }}" class="nav-item active">
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
    <div class="topbar-title">Data Produk</div>
  </div>

  <div class="content">
    <div class="page-header">
      <h1>Kelola Data Produk</h1>
      <p>Upload data produk melalui file Excel atau tambahkan produk secara manual.</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    <!-- UPLOAD EXCEL -->
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">① Upload File Excel</div>
          <div class="card-sub">
            Nilai setiap kriteria akan diambil otomatis dari kolom pada file Excel
          </div>
        </div>
        <div style="display:flex;gap:8px;align-items:center">
          <span class="badge badge-gray" id="stok-badge">Belum diupload</span>
        </div>
      </div>

      {{-- Info kolom dinamis dari DB --}}
      <div class="info-box info-pink">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 7v5M8 5.5v.01" stroke-linecap="round"/></svg>
        <div>
          Kolom yang dibutuhkan sistem saat ini:
          <b>NAMA BARANG</b>
          @foreach($kriteriaExcel as $k)
            , <b>{{ strtoupper($k->nama_kolom_excel) }}</b>
          @endforeach
          @if($kriteriaExcel->isEmpty())
            <span style="color:var(--pink-dark)"> — belum ada kriteria Excel. Tambahkan dulu di Kelola Kriteria.</span>
          @endif
          <br>
          <span style="font-weight:400;margin-top:2px;display:block">
            Kolom lain di file akan diabaikan. Pastikan nama kolom sama persis.
          </span>
        </div>
      </div>

      <form method="POST" action="{{ route('produk.preview') }}" enctype="multipart/form-data" id="import-form">
        @csrf
        <label class="upload-zone" id="upload-zone">
          <input type="file" name="file_excel" id="file-input" accept=".xlsx,.xls" style="display:none" onchange="handleFileChange(this)">
          <div class="upload-icon">
            <svg viewBox="0 0 20 20"><path d="M10 13V4M7 7l3-3 3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 15h14" stroke-linecap="round"/></svg>
          </div>
          <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:4px">Klik untuk memilih  file Excel (.xlsx / .xls)</div>
          <div style="font-size:11px;color:var(--text-3)">Pastikan file memiliki kolom NAMA BARANG dan kolom kriteria yang telah ditentukan.</div>
        </label>
        <div id="file-preview" style="display:none;margin-top:12px">
          <div class="info-box info-green">
            <svg viewBox="0 0 16 16"><path d="M3 8l4 4 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span id="file-name">File dipilih</span>
          </div>
          <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">
            <button type="button" class="btn btn-sm" onclick="batalUpload()">Batal</button>
            <button type="submit" class="btn btn-pink btn-sm">
              <svg viewBox="0 0 16 16" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M10 13V4M7 7l3-3 3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M3 15h14" stroke-linecap="round"/></svg>
              Tampilkan Data
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- DAFTAR PRODUK -->
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Daftar Produk</div>
          <div class="card-sub">{{ $produks->count() }} produk terdaftar</div>
        </div>
        <div class="btn-group">
  <input class="form-input" style="width:180px;padding:6px 10px;font-size:12px" placeholder="Cari nama produk..." oninput="filterProduk(this.value)" id="search-produk">
  
  {{-- Dropdown sort --}}
  <select class="form-input" style="width:160px;padding:6px 10px;font-size:12px" onchange="sortProduk(this.value)">
    <option value="abjad" {{ request('sort','abjad') === 'abjad' ? 'selected' : '' }}>↑ A–Z</option>
    <option value="terbaru" {{ request('sort') === 'terbaru' ? 'selected' : '' }}>↓ Terbaru</option>
    <option value="terlama" {{ request('sort') === 'terlama' ? 'selected' : '' }}>↑ Terlama</option>
  </select>

  <button class="btn btn-pink btn-sm" onclick="openModal('modal-tambah')">
    <svg viewBox="0 0 16 16" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2.2"><path d="M8 3v10M3 8h10" stroke-linecap="round"/></svg>
    Tambah nama produk
  </button>
</div>
      </div>

      @if($produks->count() > 0)
      <div class="table-wrap">
        <table id="tabel-produk">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Produk</th>
              <th>Status Data</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($produks as $i => $produk)
            <tr>
              <td style="color:var(--text-3);font-size:12px">{{ $i + 1 }}</td>
              <td style="font-weight:600">{{ $produk->nama_produk }}</td>
              <td>
                @if($produk->status_data === 'Lengkap')
                  <span class="badge badge-green">✓ Data Lengkap</span>
                @else
                  <span class="badge badge-amber">⚠ Data Belum Lengkap</span>
                @endif
              </td>
              <td>
                <div style="display:flex;gap:6px">
                  <button class="btn btn-sm" onclick="openEdit({{ $produk->id_produk }}, '{{ addslashes($produk->nama_produk) }}')">Edit</button>
                  <button class="btn btn-red btn-sm" onclick="openHapus({{ $produk->id_produk }}, '{{ addslashes($produk->nama_produk) }}')">Hapus</button>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @else
      <div class="empty-state">
        <svg viewBox="0 0 40 40"><rect x="4" y="8" width="32" height="28" rx="2"/><path d="M12 4h16"/></svg>
        <p>Belum ada produk.<br>Upload file stok Excel atau tambah manual.</p>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal-overlay" id="modal-tambah">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-title">Tambah Produk</div>
    <form method="POST" action="{{ route('produk.store') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Nama Produk</label>
        <input class="form-input" name="nama_produk" placeholder="Nama produk" required>
      </div>

      {{-- Field nilai kriteria Manual -- tampil dinamis sesuai kriteria yang ada --}}
      @if($kriteriaManual->count() > 0)
        <div style="margin-bottom:10px;padding-top:4px;border-top:1px solid var(--border)">
          <div style="font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin:12px 0 10px">
            Nilai Kriteria Manual
          </div>
          @foreach($kriteriaManual as $k)
            <div class="form-group">
              <label class="form-label">
                {{ $k->nama_kriteria }}
                <span style="font-weight:400;color:var(--text-3)">(skala 1–5)</span>
              </label>
              <div style="display:flex;gap:6px">
                @for($v = 1; $v <= 5; $v++)
                  <label style="flex:1;text-align:center;cursor:pointer">
                    <input type="radio" name="nilai_kriteria[{{ $k->id_kriteria }}]"
                           value="{{ $v }}" style="display:none"
                           class="radio-kriteria"
                           onchange="highlightRadio(this)">
                    <span class="rating-btn-tambah" data-val="{{ $v }}">{{ $v }}</span>
                  </label>
                @endfor
              </div>
            </div>
          @endforeach
        </div>
      @endif

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
    <div class="modal-title">Edit Produk</div>
    <form method="POST" id="form-edit">
      @csrf
      @method('PUT')
      <div class="form-group">
        <label class="form-label">Nama Produk</label>
        <input class="form-input" name="nama_produk" id="edit-nama" required>
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
    <div class="modal-title">Hapus Produk?</div>
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

function sortProduk(val) {
  const url = new URL(window.location.href);
  url.searchParams.set('sort', val);
  window.location.href = url.toString();
}

function highlightRadio(input) {
  // Hapus highlight semua tombol dalam grup yang sama
  const name = input.name;
  document.querySelectorAll(`input[name="${name}"] + .rating-btn-tambah`).forEach(span => {
    span.style.borderColor = '';
    span.style.background = '';
    span.style.color = '';
  });
}
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

function openEdit(id, nama) {
  document.getElementById('edit-nama').value = nama;
  document.getElementById('form-edit').action = '/data-produk/' + id;
  openModal('modal-edit');
}

function openHapus(id, nama) {
  document.getElementById('hapus-text').textContent = 'Yakin ingin menghapus produk "' + nama + '"? Data nilai kriteria produk ini juga akan terhapus.';
  document.getElementById('form-hapus').action = '/data-produk/' + id;
  openModal('modal-hapus');
}

function handleFileChange(input) {
  if (input.files.length > 0) {
    document.getElementById('file-name').textContent = input.files[0].name;
    document.getElementById('file-preview').style.display = 'block';
    document.getElementById('stok-badge').textContent = 'File dipilih';
    document.getElementById('stok-badge').className = 'badge badge-pink';
  }
}

function batalUpload() {
  document.getElementById('file-input').value = '';
  document.getElementById('file-preview').style.display = 'none';
  document.getElementById('stok-badge').textContent = 'Belum diupload';
  document.getElementById('stok-badge').className = 'badge badge-gray';
}

function filterProduk(keyword) {
  const rows = document.querySelectorAll('#tabel-produk tbody tr');
  keyword = keyword.toLowerCase();
  rows.forEach(row => {
    const nama = row.cells[1]?.textContent.toLowerCase() || '';
    row.style.display = nama.includes(keyword) ? '' : 'none';
  });
}

// Tutup modal kalau klik di luar
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
  });
});
</script>
<script>

@if(session('success'))
  Swal.fire({
    icon: 'success',
    title: 'Berhasil!',
    text: '{{ session('success') }}',
    confirmButtonColor: '#e8005a'
  });
@endif

@if(session('error'))
  Swal.fire({
    icon: 'error',
    title: 'Gagal!',
    html: '{{ session('error') }}',
    confirmButtonColor: '#e8005a',
    width: '500px'
  });
@endif
</script>
</body>
</html>