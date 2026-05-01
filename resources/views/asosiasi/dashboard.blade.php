<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Analisis Asosiasi — DRW Skincare</title>
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

/* ── SIDEBAR ── */
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
.nav-divider { height: 1px; background: var(--border); margin: 8px 10px; }
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
.sb-logout svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 1.8; }

/* ── MAIN ── */
.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.content { flex: 1; padding: 28px; overflow-y: auto; }

/* ── PAGE HEADER ── */
.page-header-row {
  display: flex; align-items: center; justify-content: space-between;
  gap: 16px; margin-bottom: 24px;
}
.page-header { flex: 1; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.5px; }
.page-header p { font-size: 13px; color: var(--text-3); margin-top: 4px; }

/* ── METRIC CARDS ── */
.grid4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
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

/* ── CARDS ── */
.card {
  background: var(--surface); border-radius: var(--radius-lg);
  border: 1px solid var(--border); padding: 20px 22px;
  box-shadow: var(--shadow); display: flex; flex-direction: column;
}
.card-hd { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; gap: 12px; }
.card-title { font-size: 13px; font-weight: 700; color: var(--text); }
.card-sub { font-size: 11px; color: var(--text-3); margin-top: 2px; }

/* ── BUTTONS ── */
.btn-sm {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-strong);
  background: var(--surface); font-size: 11px; font-weight: 600;
  color: var(--pink); cursor: pointer; font-family: inherit;
  transition: all .15s; text-decoration: none;
}
.btn-sm:hover { background: var(--pink-light); }
.btn-pink {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px; border-radius: 9px;
  background: var(--pink); color: #fff;
  font-size: 12px; font-weight: 700; font-family: inherit;
  border: none; cursor: pointer; transition: background .15s; text-decoration: none;
}
.btn-pink:hover { background: var(--pink-dark); }
.btn-pink svg { width: 14px; height: 14px; stroke: #fff; fill: none; stroke-width: 2; }

/* ── EMPTY STATE ── */
.empty-state { text-align: center; padding: 40px 20px; color: var(--text-3); }
.empty-state svg { width: 40px; height: 40px; stroke: var(--border-strong); fill: none; stroke-width: 1.5; margin: 0 auto 12px; display: block; }
.empty-state p { font-size: 13px; line-height: 1.6; }

/* ── RULE LIST ── */
.rule-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 12px 0; border-bottom: 1px solid var(--border);
}
.rule-item:last-child { border-bottom: none; }
.rule-rank {
  width: 26px; height: 26px; border-radius: 50%; flex-shrink: 0;
  background: var(--pink-light); color: var(--pink);
  display: flex; align-items: center; justify-content: center;
  font-size: 11px; font-weight: 800;
}
.rule-rank.gold { background: var(--amber-light); color: #92400e; }
.rule-rank.silver { background: #f3f4f6; color: #374151; }
.rule-rank.bronze { background: #fff7ed; color: #9a3412; }
.rule-body { flex: 1; }
.rule-text { font-size: 12px; font-weight: 600; color: var(--text); margin-bottom: 5px; }
.rule-arrow { color: var(--pink); margin: 0 4px; }
.rule-metrics { display: flex; gap: 6px; flex-wrap: wrap; }
.rule-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.rb-support { background: var(--green-light); color: #065f46; }
.rb-conf { background: var(--pink-light); color: var(--pink-dark); }
.rb-lift { background: var(--purple-light); color: #5b21b6; }

/* ── PARAM TABLE ── */
.param-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 9px 0; border-bottom: 1px solid var(--border); font-size: 12px;
}
.param-row:last-child { border-bottom: none; }
.param-key { color: var(--text-3); font-weight: 600; }
.param-val { font-weight: 800; color: var(--text); font-family: 'DM Mono', monospace; }
.param-val.pink { color: var(--pink); }

/* ── INFO BANNER ── */
.info-banner {
  font-size: 11px; color: var(--pink-dark); line-height: 1.6;
  padding: 10px 12px; background: var(--pink-light);
  border-radius: 8px; border-left: 3px solid var(--pink);
  margin-top: 12px;
}
.info-banner strong { font-weight: 700; }

/* ── BADGE ── */
.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; }
.badge-green { background: var(--green-light); color: #065f46; }
.badge-pink { background: var(--pink-light); color: var(--pink-dark); }
</style>
</head>
<body>

{{-- ── SIDEBAR ── --}}
<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon" style="background:none;box-shadow:none;">
        <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW Skincare" style="width:36px;height:36px;object-fit:contain;">
      </div>
      <div>
        <div class="sb-logo-name">DRW BANJARMASIN</div>
        <div class="sb-logo-sub">Analisis penjualan & produk</div>
      </div>
    </div>
  </div>
  <div class="sb-nav">

    {{-- SPK --}}
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
      <a href="{{ route('produk.index') }}" class="nav-item"> {{-- ✅ FIX: was route('data-produk') --}}
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
      <a href="{{ route('perhitungan.hasil', ['id' => 'latest']) }}" class="nav-item"> {{-- ✅ atau sesuaikan dengan route yang benar --}}
        <svg viewBox="0 0 16 16"><path d="M2 12l4-4 3 3 5-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Hasil & Laporan
      </a>
      <a href="{{ route('perhitungan.riwayat') }}" class="nav-item"> {{-- ✅ FIX: was route('riwayat') --}}
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l-2 2" stroke-linecap="round"/></svg>
        Riwayat
      </a>
    </div>

    <div class="nav-divider"></div>

    {{-- Analisis Asosiasi --}}
    <div class="nav-section">
      <div class="nav-label">Analisis Asosiasi</div>
      <a href="{{ route('asosiasi.dashboard') }}" class="nav-item active">
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
    {{-- ✅ FIX: null-safe operator untuk menghindari error jika user null --}}
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

{{-- ── MAIN CONTENT ── --}}
<div class="main">
  <div class="content">

    {{-- Page Header --}}
    <div class="page-header-row">
      <div class="page-header">
        <h1>Dashboard Analisis Asosiasi 📊</h1>
        <p>Ringkasan hasil analisis pola pembelian produk DRW Skincare menggunakan metode FP-Growth.</p>
      </div>
      @if(auth()->check() && auth()->user()->role === 'Admin')
      <a href="{{ route('asosiasi.analisis') }}" class="btn-pink">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        Jalankan Analisis
      </a>
      @endif
    </div>

    {{-- Metric Cards --}}
    <div class="grid4">
      <div class="metric-card" style="border-left:3px solid var(--pink)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Total Transaksi</div>
            <div class="metric-value" style="color:var(--pink)">{{ number_format($totalTransaksi) }}</div>
            <div class="metric-sub">data transaksi</div>
          </div>
          <div class="metric-icon" style="background:var(--pink-light);color:var(--pink)">
            <svg viewBox="0 0 20 20"><path d="M3 3h14v14H3zM3 8h14M8 8v9" stroke-linecap="round"/></svg>
          </div>
        </div>
      </div>
      <div class="metric-card" style="border-left:3px solid var(--blue)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Jumlah Produk</div>
            <div class="metric-value" style="color:var(--blue)">{{ number_format($totalProduk) }}</div>
            <div class="metric-sub">produk terdaftar</div>
          </div>
          <div class="metric-icon" style="background:var(--blue-light);color:var(--blue)">
            <svg viewBox="0 0 20 20"><path d="M3 5h14v11a1 1 0 01-1 1H4a1 1 0 01-1-1V5zM6 5V4a1 1 0 011-1h6a1 1 0 011 1v1" stroke-linecap="round"/></svg>
          </div>
        </div>
      </div>
      <div class="metric-card" style="border-left:3px solid var(--amber)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Frequent Itemset</div>
            <div class="metric-value" style="color:var(--amber)">{{ number_format($totalItemset) }}</div>
            <div class="metric-sub">itemset ditemukan</div>
          </div>
          <div class="metric-icon" style="background:var(--amber-light);color:var(--amber)">
            <svg viewBox="0 0 20 20"><polygon points="10,2 12.5,7.5 18,8.5 14,12.5 15,18 10,15.5 5,18 6,12.5 2,8.5 7.5,7.5"/></svg>
          </div>
        </div>
      </div>
      <div class="metric-card" style="border-left:3px solid var(--purple)">
        <div style="display:flex;align-items:flex-start;justify-content:space-between">
          <div>
            <div class="metric-label">Association Rules</div>
            <div class="metric-value" style="color:var(--purple)">{{ number_format($totalRules) }}</div>
            <div class="metric-sub">aturan ditemukan</div>
          </div>
          <div class="metric-icon" style="background:var(--purple-light);color:var(--purple)">
            <svg viewBox="0 0 20 20"><path d="M4 10h12M13 7l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </div>
        </div>
      </div>
    </div>

    {{-- Chart + Top Rules --}}
    <div class="grid2">

      {{-- Chart Top 10 Produk --}}
      <div class="card">
        <div class="card-hd">
          <div>
            <div class="card-title">Top 10 Produk Terlaris</div>
            <div class="card-sub">Berdasarkan frekuensi kemunculan dalam transaksi</div>
          </div>
          <a href="{{ route('asosiasi.riwayat') }}" class="btn-sm">Lihat detail →</a>
        </div>
        @if(isset($top10Produk) && count($top10Produk) > 0)
          <div style="flex:1;display:flex;flex-direction:column;justify-content:center">
            <canvas id="chartTop10" style="max-height:300px"></canvas>
            <div class="info-banner">
              📊 <strong>Insight:</strong>
              {{ $top10Produk[0]->nama_produk }} adalah produk yang paling sering muncul dalam transaksi dengan frekuensi {{ number_format($top10Produk[0]->frekuensi) }} kali.
            </div>
          </div>
        @else
          <div style="flex:1;display:flex;align-items:center;justify-content:center;min-height:220px">
            <div class="empty-state">
              <svg viewBox="0 0 40 40"><rect x="4" y="20" width="6" height="16" rx="1"/><rect x="13" y="14" width="6" height="22" rx="1"/><rect x="22" y="8" width="6" height="28" rx="1"/><rect x="31" y="16" width="6" height="20" rx="1"/></svg>
              <p>Belum ada data analisis.<br>Jalankan analisis FP-Growth terlebih dahulu.</p>
            </div>
          </div>
        @endif
      </div>

      {{-- Top Rules + Parameter --}}
      <div style="display:flex;flex-direction:column;gap:16px">

        {{-- Top 3 Association Rules --}}
        <div class="card">
          <div class="card-hd">
            <div>
              <div class="card-title">Top Aturan Asosiasi</div>
              <div class="card-sub">Berdasarkan nilai lift tertinggi</div>
            </div>
            <a href="{{ route('asosiasi.riwayat') }}" class="btn-sm">Lihat semua →</a>
          </div>
          @if(isset($topRules) && count($topRules) > 0)
            @foreach($topRules as $i => $rule)
            @php
              $ruleParts = preg_split('/\s*(=>|→|->)\s*/u', $rule->rule_asosiasi, 2);
              $antecedent = $ruleParts[0] ?? $rule->rule_asosiasi;
              $consequent = $ruleParts[1] ?? '';
            @endphp
            <div class="rule-item">
              <div class="rule-rank {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : 'bronze') }}">
                {{ $i + 1 }}
              </div>
              <div class="rule-body">
                <div class="rule-text">
                  {{ $antecedent }}
                  @if($consequent)
                    <span class="rule-arrow">→</span>
                    {{ $consequent }}
                  @endif
                </div>
                <div class="rule-metrics">
                  <span class="rule-badge rb-support">Sup {{ number_format($rule->nilai_support, 0) }}%</span>
                  <span class="rule-badge rb-conf">Conf {{ number_format($rule->nilai_confidence, 0) }}%</span>
                  <span class="rule-badge rb-lift">Lift {{ number_format($rule->nilai_lift, 2) }}</span>
                </div>
              </div>
            </div>
            @endforeach
          @else
            <div class="empty-state" style="padding:24px 20px">
              <svg viewBox="0 0 40 40"><path d="M8 20h24M20 8l12 12-12 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <p>Belum ada aturan asosiasi.<br>Jalankan analisis terlebih dahulu.</p>
            </div>
          @endif
        </div>

        {{-- Parameter Analisis --}}
        <div class="card">
          <div class="card-hd">
            <div>
              <div class="card-title">Parameter Analisis Terakhir</div>
              <div class="card-sub">Konfigurasi sesi FP-Growth</div>
            </div>
            @if(isset($lastProses))
              <span class="badge badge-green">✓ Selesai</span>
            @else
              <span class="badge badge-pink">Belum ada</span>
            @endif
          </div>
          @if(isset($lastProses))
            <div class="param-row">
              <span class="param-key">Min Support</span>
              <span class="param-val pink">{{ $lastProses->min_support }}%</span>
            </div>
            <div class="param-row">
              <span class="param-key">Min Confidence</span>
              <span class="param-val pink">{{ $lastProses->min_confidence }}%</span>
            </div>
            <div class="param-row">
              <span class="param-key">Min Lift</span>
              <span class="param-val pink">{{ $lastProses->min_lift }}</span>
            </div>
            <div class="param-row">
              <span class="param-key">Total Itemset</span>
              <span class="param-val">{{ number_format($totalItemset) }}</span>
            </div>
            <div class="param-row">
              <span class="param-key">Total Rules</span>
              <span class="param-val">{{ number_format($totalRules) }}</span>
            </div>
            <div class="param-row">
              <span class="param-key">Tanggal Analisis</span>
              <span class="param-val" style="font-family:inherit;font-size:12px">{{ \Carbon\Carbon::parse($lastProses->tanggal_proses)->format('d M Y') }}</span>
            </div>
          @else
            <div class="empty-state" style="padding:20px">
              <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="2"/><path d="M20 4v4M20 32v4M4 20h4M32 20h4" stroke-linecap="round"/></svg>
              <p>Belum ada analisis yang dijalankan.</p>
            </div>
          @endif
        </div>

      </div>
    </div>

  </div>
</div>

<script>
const top10Data = @json($top10Produk ?? []);

if (top10Data && top10Data.length > 0 && document.getElementById('chartTop10')) {
  const labels = top10Data.map((item, idx) => `#${idx + 1} ${item.nama_produk.substring(0, 16)}...`);
  const values = top10Data.map(item => parseInt(item.frekuensi));
  const maxValue = Math.max(...values);

  const colors = [
    '#e8005a','#f01870','#f43085','#f7489a','#f960af',
    '#fb78c4','#fc8fce','#fda6d8','#febde2','#ffd4ec'
  ];

  const ctx = document.getElementById('chartTop10').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Frekuensi',
        data: values,
        backgroundColor: colors,
        borderWidth: 0,
        borderRadius: 6,
        barThickness: 'flex',
        maxBarThickness: 45
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#1a0a0f',
          padding: 10,
          titleFont: { size: 12, weight: '700' },
          bodyFont: { size: 11 },
          callbacks: {
            label: (context) => `Frekuensi: ${context.parsed.x} transaksi`
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          max: maxValue * 1.15,
          grid: { color: 'rgba(232, 0, 90, 0.08)', drawBorder: false },
          ticks: { font: { size: 11 }, color: '#b07090', callback: (v) => v }
        },
        y: {
          grid: { display: false },
          ticks: { font: { size: 11, weight: '600' }, color: '#1a0a0f', padding: 8 }
        }
      }
    }
  });
}
</script>

</body>
</html>