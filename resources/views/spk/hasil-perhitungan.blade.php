<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hasil Perhitungan — DRW Skincare</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --pink: #e8005a; --pink-light: #fff0f5; --pink-mid: #ff4d8d;
  --pink-dark: #b3004a; --pink-soft: #ffd6e8;
  --red: #ef4444; --red-light: #fef2f2;
  --green: #10b981; --green-light: #ecfdf5;
  --amber: #f59e0b; --amber-light: #fef3c7;
  --blue: #3b82f6; --blue-light: #eff6ff;
  --purple: #8b5cf6; --purple-light: #f5f3ff;
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
.topbar { height: 56px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; gap: 12px; position: sticky; top: 0; z-index: 10; }
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); flex: 1; }
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
.btn-sm { padding: 6px 12px; font-size: 11px; }
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
.badge-green { background: var(--green-light); color: #065f46; }
.badge-amber { background: var(--amber-light); color: #92400e; }
.badge-pink { background: var(--pink-light); color: var(--pink); }
.badge-red { background: var(--red-light); color: var(--red); }
.badge-purple { background: var(--purple-light); color: #6d28d9; }
.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--pink-light); }
th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: var(--pink-dark); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); white-space: nowrap; }
td { padding: 10px 14px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--pink-light); }
.rank-1 td { background: linear-gradient(135deg, #fff0f5, #ffe4ef) !important; }
.rank-medal { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
.rank-1-medal { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #fff; }
.rank-2-medal { background: #e5e7eb; color: #6b7280; }
.rank-3-medal { background: linear-gradient(135deg, #d97706, #b45309); color: #fff; }
.rank-n-medal { background: var(--bg); color: var(--text-3); border: 1px solid var(--border); font-size: 11px; }
.hero-box { background: linear-gradient(135deg, var(--pink), var(--pink-mid)); border-radius: var(--radius-lg); padding: 24px 28px; color: #fff; margin-bottom: 16px; }
.hero-label { font-size: 11px; font-weight: 700; opacity: .8; letter-spacing: .05em; text-transform: uppercase; margin-bottom: 6px; }
.hero-name { font-size: 24px; font-weight: 800; letter-spacing: -.5px; margin-bottom: 4px; }
.hero-sub { font-size: 12px; opacity: .8; }
.meta-row { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 14px; }
.meta-item { font-size: 12px; opacity: .85; }
.meta-item b { font-weight: 700; opacity: 1; }
.detail-toggle { cursor: pointer; color: var(--pink); font-size: 11px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; }
.detail-row { display: none; background: var(--bg); }
.detail-row.open { display: table-row; }
.detail-inner { padding: 12px 14px; }
.detail-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.detail-table th { padding: 6px 10px; background: var(--pink-light); color: var(--pink-dark); font-size: 10px; font-weight: 700; text-transform: uppercase; }
.detail-table td { padding: 6px 10px; border-bottom: 1px solid var(--border); font-family: 'DM Mono', monospace; }
.detail-table tr:last-child td { border-bottom: none; }

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


<div class="main">
  <div class="topbar">
    <div class="topbar-title">Hasil Perhitungan</div>
    <a href="{{ route('perhitungan.riwayat') }}" class="btn btn-sm">← Riwayat</a>
    <a href="{{ route('perhitungan.index') }}" class="btn btn-pink btn-sm">Hitung Ulang</a>
  </div>
  <div class="content">

    {{-- HERO: PRODUK TERBAIK --}}
    <div class="hero-box">
      <div class="hero-label">🏆 Produk Terbaik — {{ $perhitungan->periode_data }}</div>
      <div class="hero-name">{{ $perhitungan->produk_prioritas }}</div>
      <div class="hero-sub">Ranking #1 berdasarkan metode MOORA</div>
      <div class="meta-row">
        <div class="meta-item"><b>{{ $perhitungan->jumlah_produk }}</b> produk dihitung</div>
        <div class="meta-item"><b>{{ $kriterias->count() }}</b> kriteria</div>
        <div class="meta-item">Dihitung: <b>{{ \Carbon\Carbon::parse($perhitungan->created_at)->format('d M Y H:i') }}</b></div>
      </div>
    </div>

    {{-- TABEL RANKING --}}
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Hasil Ranking Produk</div>
          <div class="card-sub">Klik "Detail" untuk melihat breakdown nilai per kriteria</div>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:50px">Rank</th>
              <th>Nama Produk</th>
              <th style="text-align:right">Total Benefit</th>
              <th style="text-align:right">Total Cost</th>
              <th style="text-align:right">Nilai Yi</th>
              <th>Prioritas</th>
              <th style="width:80px">Detail</th>
            </tr>
          </thead>
          <tbody>
            @foreach($hasil as $h)
            <tr class="{{ $h->ranking == 1 ? 'rank-1' : '' }}" id="row-{{ $h->id_hasil }}">
              <td>
                <div class="rank-medal {{ $h->ranking == 1 ? 'rank-1-medal' : ($h->ranking == 2 ? 'rank-2-medal' : ($h->ranking == 3 ? 'rank-3-medal' : 'rank-n-medal')) }}">
                  {{ $h->ranking }}
                </div>
              </td>
              <td style="font-weight:600">{{ $h->nama_produk }}</td>
              <td style="text-align:right;font-family:'DM Mono',monospace;color:var(--green);font-size:12px">
                {{ number_format($h->total_benefit, 4) }}
              </td>
              <td style="text-align:right;font-family:'DM Mono',monospace;color:var(--red);font-size:12px">
                {{ number_format($h->total_cost, 4) }}
              </td>
              <td style="text-align:right;font-family:'DM Mono',monospace;font-weight:700;color:var(--pink)">
                {{ number_format($h->nilai_yi, 4) }}
              </td>
              <td>
                @if($h->prioritas == 'Utama')
                  <span class="badge badge-green">⭐ Utama</span>
                @elseif($h->prioritas == 'Pertimbangkan')
                  <span class="badge badge-amber">~ Pertimbangkan</span>
                @else
                  <span class="badge badge-pink">Tunda</span>
                @endif
              </td>
              <td>
                <span class="detail-toggle" onclick="toggleDetail({{ $h->id_hasil }})">
                  <svg viewBox="0 0 16 16" width="12" height="12" stroke="currentColor" fill="none" stroke-width="2"><path d="M4 6l4 4 4-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  Detail
                </span>
              </td>
            </tr>
            {{-- DETAIL ROW --}}
            <tr class="detail-row" id="detail-{{ $h->id_hasil }}">
              <td colspan="7">
                <div class="detail-inner">
                  <div style="font-size:11px;font-weight:700;color:var(--text-2);margin-bottom:8px">
                    Breakdown nilai kriteria — {{ $h->nama_produk }}
                  </div>
                  <table class="detail-table">
                    <thead>
                      <tr>
                        <th>Kriteria</th>
                        <th>Tipe</th>
                        <th style="text-align:right">Nilai Asli</th>
                        <th style="text-align:right">Nilai Normal</th>
                        <th style="text-align:right">Bobot</th>
                        <th style="text-align:right">Kontribusi Yi</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($h->detailPerhitungan as $d)
                      <tr>
                        <td style="font-weight:600;font-family:inherit">{{ $d->nama_kriteria }}</td>
                        <td>
                          @if(strtolower($d->tipe_atribut) == 'benefit')
                            <span style="color:var(--green);font-weight:700;font-family:inherit">↑ B</span>
                          @else
                            <span style="color:var(--red);font-weight:700;font-family:inherit">↓ C</span>
                          @endif
                        </td>
                        <td style="text-align:right">{{ number_format($d->nilai_asli, 2) }}</td>
                        <td style="text-align:right">{{ number_format($d->nilai_normal, 6) }}</td>
                        <td style="text-align:right">{{ $d->bobot }}%</td>
                        <td style="text-align:right;font-weight:700;color:{{ strtolower($d->tipe_atribut) == 'benefit' ? 'var(--green)' : 'var(--red)' }}">
                          {{ strtolower($d->tipe_atribut) == 'benefit' ? '+' : '-' }}{{ number_format(abs($d->nilai_normal * $d->bobot / 100), 6) }}
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    {{-- BOBOT SNAPSHOT --}}
    <div class="card">
      <div class="card-hd">
        <div>
          <div class="card-title">Snapshot Bobot Kriteria</div>
          <div class="card-sub">Bobot yang digunakan saat perhitungan ini dijalankan</div>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        @foreach($kriterias as $k)
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:10px 14px;min-width:140px">
          <div style="font-size:11px;color:var(--text-3);margin-bottom:2px">{{ $k['nama'] }}</div>
          <div style="font-size:18px;font-weight:800;color:var(--pink);font-family:'DM Mono',monospace">{{ $k['bobot'] }}%</div>
          <div style="font-size:10px;margin-top:2px">
            @if(strtolower($k['tipe_atribut']) == 'benefit')
              <span style="color:var(--green);font-weight:700">↑ Benefit</span>
            @else
              <span style="color:var(--red);font-weight:700">↓ Cost</span>
            @endif
          </div>
        </div>
        @endforeach
      </div>
    </div>

  </div>
</div>

<script>
function toggleDetail(id) {
  const row = document.getElementById('detail-' + id);
  row.classList.toggle('open');
}
</script>
</body>
</html>