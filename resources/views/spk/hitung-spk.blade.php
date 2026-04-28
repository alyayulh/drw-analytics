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
.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { height: 56px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; position: sticky; top: 0; z-index: 10; }
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); }
.content { flex: 1; padding: 28px; }
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
.page-header p { font-size: 13px; color: var(--text-3); margin-top: 4px; }
.card { background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 20px 22px; box-shadow: var(--shadow); margin-bottom: 16px; }
.card-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; flex-wrap: wrap; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }
.btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border-strong); background: var(--surface); font-size: 12px; font-weight: 600; color: var(--text-2); cursor: pointer; font-family: inherit; transition: all .15s; text-decoration: none; }
.btn:hover { background: var(--bg); }
.btn-pink { background: linear-gradient(135deg, var(--pink), var(--pink-mid)); color: #fff; border: none; box-shadow: 0 2px 8px rgba(232,0,90,.25); }
.btn-pink:hover { opacity: .9; }
.btn-pink:disabled { opacity: .5; cursor: not-allowed; }
.btn-red { background: var(--red-light); color: var(--red); border-color: #fca5a5; }
.btn-sm { padding: 6px 12px; font-size: 11px; }
.form-input { width: 100%; padding: 10px 14px; border: 1px solid var(--border-strong); border-radius: 8px; font-size: 13px; font-family: inherit; color: var(--text); background: var(--surface); outline: none; transition: border .15s; }
.form-input:focus { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(232,0,90,.08); }
.form-label { font-size: 12px; font-weight: 600; color: var(--text-2); display: block; margin-bottom: 6px; }
.form-group { margin-bottom: 16px; }
.form-hint { font-size: 11px; color: var(--text-3); margin-top: 4px; }
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
.badge-green { background: var(--green-light); color: #065f46; }
.badge-amber { background: var(--amber-light); color: #92400e; }
.badge-pink { background: var(--pink-light); color: var(--pink); }
.badge-red { background: var(--red-light); color: var(--red); }
.info-box { display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; border-radius: 9px; font-size: 12px; margin-bottom: 14px; }
.info-box svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; margin-top: 1px; }
.info-pink { background: var(--pink-light); color: var(--pink-dark); border: 1px solid var(--pink-soft); }
.info-amber { background: var(--amber-light); color: #92400e; border: 1px solid #fcd34d; }
.info-green { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }
.alert { padding: 10px 14px; border-radius: 9px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
.alert-success { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }
.alert-error { background: var(--red-light); color: var(--red); border: 1px solid #fca5a5; }
.stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 20px; }
.stat-box { background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px 16px; }
.stat-label { font-size: 11px; color: var(--text-3); font-weight: 600; margin-bottom: 4px; }
.stat-val { font-size: 22px; font-weight: 800; color: var(--text); font-family: 'DM Mono', monospace; }
.stat-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }
.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--pink-light); }
th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: var(--pink-dark); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); white-space: nowrap; }
td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--pink-light); }
.empty-state { text-align: center; padding: 32px 20px; color: var(--text-3); }
.empty-state p { font-size: 13px; }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon">
        <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW" style="width:36px;height:36px;object-fit:contain;">
      </div>
      <div>
        <div class="sb-logo-name">DRW BANJARMASIN</div>
        <div class="sb-logo-sub">DRW Skincare Banjarmasin</div>
      </div>
    </div>
  </div>
  <div class="sb-nav">
    <div class="nav-section">
      <div class="nav-label">Menu Utama</div>
      <a href="/dashboard" class="nav-item">
        <svg viewBox="0 0 16 16"><rect x="2" y="2" width="5" height="5" rx="1.5"/><rect x="9" y="2" width="5" height="5" rx="1.5"/><rect x="2" y="9" width="5" height="5" rx="1.5"/><rect x="9" y="9" width="5" height="5" rx="1.5"/></svg>
        Dashboard
      </a>
         @if(auth()->user()->role === 'Admin')
      <a href="/data-produk" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 4h12v9a1 1 0 01-1 1H3a1 1 0 01-1-1V4zM5 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/></svg>
        Data Produk
      </a>
          @endif
      <a href="/input-permintaan" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v10" stroke-linecap="round"/></svg>
        Input Permintaan
      </a>
      <a href="/hitung-spk" class="nav-item active">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2" stroke-linecap="round"/></svg>
        Hitung SPK
      </a>
      <a href="/riwayat" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l-2 2" stroke-linecap="round"/></svg>
        Riwayat
      </a>
    </div>
    <div class="nav-section">
      <div class="nav-label">Pengaturan</div>
      <a href="/kelola-kriteria" class="nav-item">
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
    <div style="margin-left:auto">
      <form method="POST" action="/logout">
        @csrf
        <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-3)" title="Logout">
          <svg viewBox="0 0 16 16" width="15" height="15" stroke="currentColor" fill="none" stroke-width="1.8"><path d="M10 3h3a1 1 0 011 1v8a1 1 0 01-1 1h-3M7 11l3-3-3-3M10 8H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
      <p>Jalankan perhitungan untuk mendapatkan ranking produk terbaik untuk dipromosikan.</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
      <div class="alert alert-error">✗ {{ session('error') }}</div>
    @endif

    {{-- RINGKASAN STATUS --}}
    <div class="stat-grid">
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

    {{-- FORM HITUNG --}}
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Jalankan Perhitungan</div>
          <div class="card-sub">Semua produk dengan status Lengkap akan ikut dihitung</div>
        </div>
      </div>

      @if($totalBobot != 100)
        <div class="info-box info-amber">
          <svg viewBox="0 0 16 16"><path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/></svg>
          Total bobot kriteria harus 100% sebelum bisa menjalankan perhitungan. Saat ini: <b>{{ $totalBobot }}%</b>. Sesuaikan di halaman Kelola Kriteria.
        </div>
      @elseif($produkLengkap < 2)
        <div class="info-box info-amber">
          <svg viewBox="0 0 16 16"><path d="M8 2L2 14h12L8 2z" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 7v3M8 11v.5" stroke-linecap="round"/></svg>
          Minimal 2 produk dengan data lengkap diperlukan. Saat ini hanya ada <b>{{ $produkLengkap }}</b> produk siap.
        </div>
      @else
        <div class="info-box info-green">
          <svg viewBox="0 0 16 16"><path d="M3 8l4 4 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Sistem siap! <b>{{ $produkLengkap }} produk</b> akan dihitung menggunakan metode MOORA dengan <b>{{ $kriterias->count() }} kriteria</b>.
        </div>
      @endif

      <form method="POST" action="{{ route('perhitungan.hitung') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">Nama Periode / Sesi Promosi</label>
          <input class="form-input" name="periode_data" placeholder="cth: Ramadhan Sale April 2026, Harbolnas Juni 2026" required
                 {{ ($totalBobot != 100 || $produkLengkap < 2) ? 'disabled' : '' }}>
          <div class="form-hint">Nama ini akan muncul di riwayat perhitungan sebagai identitas sesi promosi ini.</div>
        </div>
        <div style="display:flex;justify-content:flex-end">
          <button type="submit" class="btn btn-pink"
                  {{ ($totalBobot != 100 || $produkLengkap < 2) ? 'disabled' : '' }}>
            <svg viewBox="0 0 16 16" width="14" height="14" stroke="currentColor" fill="none" stroke-width="2"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2" stroke-linecap="round"/></svg>
            Jalankan Perhitungan MOORA
          </button>
        </div>
      </form>
    </div>

    {{-- KRITERIA AKTIF --}}
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Kriteria yang Digunakan</div>
          <div class="card-sub">Bobot dan tipe atribut yang akan dipakai dalam perhitungan</div>
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
              <th>Sumber</th>
            </tr>
          </thead>
          <tbody>
            @foreach($kriterias as $i => $k)
            <tr>
              <td style="color:var(--text-3);font-size:12px">{{ $i + 1 }}</td>
              <td style="font-weight:600">{{ $k->nama_kriteria }}</td>
              <td>
                @if(strtolower($k->tipe_atribut) == 'benefit')
                  <span class="badge badge-green">↑ Benefit</span>
                @else
                  <span class="badge badge-amber">↓ Cost</span>
                @endif
              </td>
              <td style="font-family:'DM Mono',monospace;font-weight:700;color:var(--pink)">{{ $k->bobot }}%</td>
              <td><span class="badge badge-pink">{{ $k->sumber_data }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @endif
    </div>

    {{-- RIWAYAT TERAKHIR --}}
    @if($riwayat->count() > 0)
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Perhitungan Terakhir</div>
          <div class="card-sub">5 perhitungan terbaru</div>
        </div>
        <a href="{{ route('perhitungan.riwayat') }}" class="btn btn-sm">Lihat Semua</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Periode</th>
              <th>Produk Dihitung</th>
              <th>Produk Terbaik</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($riwayat as $r)
            <tr>
              <td style="font-weight:600">{{ $r->periode_data }}</td>
              <td style="font-family:'DM Mono',monospace">{{ $r->jumlah_produk }} produk</td>
              <td style="color:var(--pink);font-weight:600">{{ $r->produk_prioritas }}</td>
              <td style="color:var(--text-3);font-size:12px">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y H:i') }}</td>
              <td>
                <a href="{{ route('perhitungan.hasil', $r->id_perhitungan) }}" class="btn btn-sm">Lihat Hasil</a>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endif

  </div>
</div>

<script>
@if(session('error'))
  Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ session('error') }}', confirmButtonColor: '#e8005a' });
@endif
</script>
</body>
</html>