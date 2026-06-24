@php
$kriteriaJs = $kriterias->map(function($k) {
    return [
        "id"    => $k->id_kriteria,
        "nama"  => $k->nama_kriteria,
        "tipe"  => $k->tipe_atribut,
        "bobot" => $k->bobot,
    ];
})->values()->toArray();

// Siapkan data tersimpan dengan validasi max 5 per kategori
$savedData = [];
foreach ($inputs as $idProduk => $rows) {
    foreach ($rows as $row) {
        $savedData[(string)$idProduk][(string)$row->id_kriteria] = (int)$row->nilai_input;
    }
}
// Helper dinamis untuk menampilkan keterangan skala 1-5 berdasarkan kriteria saat ini
$scaleLabel = function(string $namaKriteria, int $nilai): string {
    $key = strtolower($namaKriteria);

    if (str_contains($key, 'permintaan') || str_contains($key, 'demand') || str_contains($key, 'minat')) {
        return match ($nilai) {
            1 => 'sangat jarang dicari',
            2 => 'jarang dicari',
            3 => 'cukup dicari',
            4 => 'sering dicari',
            5 => 'sangat sering dicari',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'popularitas') || str_contains($key, 'populer')) {
        return match ($nilai) {
            1 => 'sangat tidak populer',
            2 => 'kurang populer',
            3 => 'cukup populer',
            4 => 'populer',
            5 => 'sangat populer',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'tren') || str_contains($key, 'trend')) {
        return match ($nilai) {
            1 => 'tidak tren',
            2 => 'kurang tren',
            3 => 'cukup tren',
            4 => 'tren',
            5 => 'sangat tren',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'kepuasan')) {
        return match ($nilai) {
            1 => 'sangat buruk',
            2 => 'buruk',
            3 => 'cukup puas',
            4 => 'puas',
            5 => 'sangat puas',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'repeat') || str_contains($key, 'ulang')) {
        return match ($nilai) {
            1 => 'jarang dibeli ulang',
            2 => 'kadang dibeli ulang',
            3 => 'cukup sering dibeli ulang',
            4 => 'sering dibeli ulang',
            5 => 'sangat sering dibeli ulang',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'kemasan') || str_contains($key, 'menarik')) {
        return match ($nilai) {
            1 => 'kurang menarik',
            2 => 'sedikit menarik',
            3 => 'cukup menarik',
            4 => 'menarik',
            5 => 'sangat menarik',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'penjualan') || str_contains($key, 'jual')) {
        return match ($nilai) {
            1 => 'sangat sulit dijual',
            2 => 'sulit dijual',
            3 => 'cukup mudah dijual',
            4 => 'mudah dijual',
            5 => 'sangat mudah dijual',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'persaingan') || str_contains($key, 'kompetisi')) {
        return match ($nilai) {
            1 => 'persaingan sangat tinggi',
            2 => 'persaingan tinggi',
            3 => 'persaingan sedang',
            4 => 'persaingan rendah',
            5 => 'persaingan sangat rendah',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'kualitas')) {
        return match ($nilai) {
            1 => 'sangat buruk',
            2 => 'buruk',
            3 => 'cukup baik',
            4 => 'baik',
            5 => 'sangat baik',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'target') || str_contains($key, 'sesuai')) {
        return match ($nilai) {
            1 => 'kurang sesuai',
            2 => 'sedikit sesuai',
            3 => 'cukup sesuai',
            4 => 'sesuai',
            5 => 'sangat sesuai',
            default => 'nilai tidak diketahui',
        };
    }

    if (str_contains($key, 'harga') || str_contains($key, 'biaya') || str_contains($key, 'cost')) {
        return match ($nilai) {
            1 => 'biaya sangat rendah',
            2 => 'biaya rendah',
            3 => 'biaya sedang',
            4 => 'biaya tinggi',
            5 => 'biaya sangat tinggi',
            default => 'nilai tidak diketahui',
        };
    }

    return match ($nilai) {
        1 => 'sangat rendah',
        2 => 'rendah',
        3 => 'sedang',
        4 => 'tinggi',
        5 => 'sangat tinggi',
        default => 'nilai tidak diketahui',
    };
};

// Validasi ulang: max 5 per kategori
$savedProdukIds = [];
foreach ($produkByKategori as $_kat => $_items) {
    $countInKat = 0;
    foreach ($_items as $_p) {
        if (isset($savedData[(string)$_p->id_produk])) {
            if ($countInKat < 5) {
                $savedProdukIds[] = (string)$_p->id_produk;
                $countInKat++;
            } else {
                // Buang data yang melebihi 5
                unset($savedData[(string)$_p->id_produk]);
            }
        }
    }
}
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Permintaan — DRW Skincare SPK</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --pink: #e8005a;
  --pink-light: #fff0f5;
  --pink-mid: #ff4d8d;
  --pink-dark: #b3004a;
  --pink-border: #fce7ef;
  --pink-border-strong: #f9a8c9;
  --bg: #fff5f8;
  --surface: #ffffff;
  --border: #fce7ef;
  --text: #1a0a0f;
  --text-2: #5a3347;
  --text-3: #b07090;
  --sidebar-w: 220px;
  --radius: 10px;
  --radius-lg: 14px;
  --radius-pill: 999px;
  --shadow: 0 1px 3px rgba(232,0,90,.06);
  --shadow-md: 0 4px 16px rgba(232,0,90,.10);
  --green: #10b981;
  --green-light: #ecfdf5;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; font-size: 14px; }

/* ===== SIDEBAR ===== */
.sidebar { width: var(--sidebar-w); min-width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border); display: flex; flex-direction: column; height: 100vh; position: sticky; top: 0; overflow-y: auto; box-shadow: 2px 0 12px rgba(232,0,90,.06); }
.sb-brand { padding: 22px 18px 16px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #e8005a08, #ff4d8d05); }
.sb-logo { display: flex; align-items: center; gap: 10px; }
.sb-logo-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.sb-logo-name { font-size: 13px; font-weight: 800; color: var(--text); line-height: 1.2; letter-spacing: -.3px; }
.sb-logo-sub { font-size: 10px; color: var(--text-3); margin-top: 1px; }
.sb-nav { flex: 1; padding: 14px 10px; }
.nav-section { margin-bottom: 6px; }
.nav-label { font-size: 10px; font-weight: 700; color: var(--text-3); letter-spacing: .08em; text-transform: uppercase; padding: 6px 8px 4px; }
.nav-item { display: flex; align-items: center; gap: 9px; padding: 9px 12px; border-radius: 9px; cursor: pointer; font-size: 13px; font-weight: 500; color: var(--text-2); transition: all .15s; margin-bottom: 2px; position: relative; text-decoration: none; }
.nav-item:hover { background: var(--pink-light); color: var(--pink-dark); }
.nav-item.active { background: linear-gradient(135deg, var(--pink-light), #ffe4ef); color: var(--pink); font-weight: 700; }
.nav-item.active::before { content:''; position:absolute; left:0; top:6px; bottom:6px; width:3px; background: var(--pink); border-radius:0 3px 3px 0; }
.nav-divider { height: 1px; background: var(--border); margin: 8px 10px; }
.nav-item svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; }
.sb-footer { padding: 14px; border-top: 1px solid var(--border); display: flex; align-items: center; gap: 9px; background: linear-gradient(135deg, #fff5f8, #fff); }
.avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0; }
.sb-user-name { font-size: 12px; font-weight: 700; color: var(--text); }
.sb-user-role { font-size: 10px; color: var(--text-3); }
.sb-logout { margin-left: auto; }
.sb-logout button { background: none; border: none; cursor: pointer; color: var(--text-3); display: flex; align-items: center; }
.sb-logout button:hover { color: var(--pink); }

/* ===== MAIN ===== */
.main-wrap { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { height: 56px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; gap: 12px; position: sticky; top: 0; z-index: 10; box-shadow: 0 2px 8px rgba(232,0,90,.05); }
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); flex: 1; letter-spacing: -.3px; }
.topbar-pill { font-size: 11px; padding: 4px 12px; border-radius: 20px; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); color: #fff; font-weight: 700; box-shadow: 0 2px 8px rgba(232,0,90,.25); }
.content { flex: 1; padding: 28px; overflow-y: auto; }

/* ===== STEPPER ===== */
.stepper { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
.step-bubble { width: 30px; height: 30px; border-radius: 50%; background: #f5d9e4; color: #c9a0b0; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; transition: .25s; flex-shrink: 0; }
.step-bubble.active { background: var(--pink); color: #fff; }
.step-line { width: 44px; height: 2px; background: #f5d9e4; border-radius: 2px; }

/* ===== PAGE HEADER ===== */
.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 22px; font-weight: 800; letter-spacing: -.4px; }
.page-header p { color: var(--text-3); font-size: 13px; margin-top: 4px; }

/* ===== TOP FILTER ===== */
.top-filter { display: flex; gap: 12px; margin-bottom: 16px; }
.search-wrap { flex: 1; position: relative; }
.search-wrap svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-3); pointer-events: none; }
.search-input { width: 100%; height: 44px; border: 1px solid var(--pink-border-strong); border-radius: var(--radius-lg); padding: 0 16px 0 40px; background: var(--surface); outline: none; font-family: inherit; font-size: 13px; color: var(--text); transition: border .2s; }
.search-input:focus { border-color: var(--pink-mid); }
.cat-filter-wrap { height: 44px; border: 1px solid var(--pink-border-strong); border-radius: var(--radius-lg); padding: 0 14px; background: var(--surface); display: flex; align-items: center; gap: 8px; min-width: 200px; }
.cat-filter-wrap svg { color: var(--text-3); flex-shrink: 0; }
.cat-filter-wrap select { border: none; background: transparent; outline: none; font-family: inherit; font-size: 13px; cursor: pointer; color: var(--text); width: 100%; }

/* ===== SUMMARY BOX ===== */
.summary-box { background: var(--surface); border: 1px solid var(--pink-border-strong); border-radius: var(--radius-lg); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; box-shadow: var(--shadow); }
.summary-label { font-size: 11px; color: var(--text-3); margin-bottom: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.summary-count { font-size: 22px; font-weight: 800; color: var(--pink); }
.btn-next { display: flex; align-items: center; gap: 8px; border: none; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); color: #fff; padding: 11px 20px; border-radius: var(--radius-lg); cursor: pointer; font-weight: 700; font-size: 13px; font-family: inherit; transition: .2s; box-shadow: 0 2px 8px rgba(232,0,90,.25); }
.btn-next:hover { background: linear-gradient(135deg, var(--pink-dark), var(--pink)); }
.btn-next svg { width: 16px; height: 16px; stroke: #fff; fill: none; stroke-width: 2.5; }

/* ===== CATEGORY CARD ===== */
.category-card { background: var(--surface); border: 1px solid var(--pink-border); border-radius: var(--radius-lg); margin-bottom: 10px; overflow: hidden; transition: box-shadow .2s; box-shadow: var(--shadow); }
.category-card:hover { box-shadow: var(--shadow-md); }
.category-header { padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; }
.category-title { font-weight: 700; font-size: 13px; display: flex; align-items: center; gap: 10px; }
.cat-badge { background: #fff0f5; color: var(--pink); font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: var(--radius-pill); border: 1px solid var(--pink-border-strong); }
.chevron { width: 18px; height: 18px; color: var(--text-3); transition: transform .25s; flex-shrink: 0; stroke: currentColor; fill: none; stroke-width: 2.5; }
.chevron.open { transform: rotate(180deg); }
.category-body { padding: 0 18px 18px; }

/* ===== PRODUCT GRID ===== */
.product-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
.product-item { border: 1.5px solid var(--pink-border); border-radius: var(--radius-pill); padding: 9px 14px; cursor: pointer; display: flex; align-items: center; gap: 9px; font-size: 12px; transition: background .15s, border-color .15s; background: var(--surface); }
.product-item:hover { background: var(--pink-light); }
.product-item.selected { background: var(--pink-light); border-color: var(--pink-mid); }
.product-item.hidden { display: none; }
.category-card.hidden { display: none; }
.product-item input[type="checkbox"] { accent-color: var(--pink); width: 14px; height: 14px; cursor: pointer; flex-shrink: 0; }

/* ===== STEP 2 ===== */
.progress-box { background: var(--surface); border: 1px solid var(--pink-border-strong); border-radius: var(--radius-lg); padding: 16px 20px; display: flex; align-items: center; gap: 16px; margin-bottom: 16px; box-shadow: var(--shadow); }
.btn-back { display: flex; align-items: center; gap: 6px; border: 1.5px solid var(--pink); background: var(--surface); color: var(--pink); padding: 8px 16px; border-radius: var(--radius-pill); cursor: pointer; font-weight: 700; font-size: 12px; font-family: inherit; transition: .2s; }
.btn-back:hover { background: var(--pink-light); }
.btn-back svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2.5; }
.progress-label { font-size: 11px; color: var(--text-3); margin-bottom: 2px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.progress-count { font-size: 20px; font-weight: 800; color: var(--pink); }

/* ===== KRITERIA INFO BANNER ===== */
.kriteria-banner {
  background: var(--pink-light);
  border: 1px solid var(--pink-border-strong);
  border-left: 4px solid var(--pink);
  border-radius: var(--radius-lg);
  padding: 12px 18px;
  margin-bottom: 16px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}
.kriteria-banner svg { width: 18px; height: 18px; stroke: var(--pink); fill: none; stroke-width: 2; flex-shrink: 0; margin-top: 1px; }
.kriteria-banner-text { font-size: 12px; color: var(--text-2); line-height: 1.5; }
.kriteria-banner-text strong { font-weight: 700; color: var(--pink); }
.kriteria-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.kriteria-chip { display: inline-flex; align-items: center; gap: 5px; background: #fff; border: 1px solid var(--pink-border-strong); border-radius: var(--radius-pill); padding: 3px 10px; font-size: 11px; font-weight: 600; color: var(--pink); }
.kriteria-scale { margin-top: 14px; padding: 12px 14px; background: #fff; border: 1px solid var(--pink-border); border-radius: var(--radius); }
.kriteria-scale strong { display: block; margin-bottom: 8px; font-size: 12px; color: var(--pink-dark); }
.scale-list { list-style: disc; margin: 0; padding-left: 18px; color: var(--text-2); font-size: 12px; line-height: 1.5; }
.scale-list li { margin-bottom: 4px; }
.scale-list li span { font-weight: 700; color: var(--text); }

/* TABLE */
.table-wrapper { background: var(--surface); border: 1px solid var(--pink-border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow); overflow-x: auto; }
table { width: 100%; border-collapse: collapse; min-width: 600px; }
th { text-align: left; padding: 13px 16px; font-size: 11px; font-weight: 700; color: var(--text-3); border-bottom: 1px solid var(--pink-border); background: #fffafc; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap; }
th.kriteria-col { color: var(--pink); background: #fff5f8; }
td { padding: 12px 16px; border-bottom: 1px solid #f8dfe8; font-size: 13px; vertical-align: middle; }
tr:last-child td { border-bottom: none; }
td.no-col { color: var(--text-3); font-weight: 600; width: 44px; }
td.name-col { font-weight: 600; min-width: 180px; }
td.status-col { white-space: nowrap; }

/* Per-kriteria rating */
.rating-group { display: flex; gap: 5px; }
.rating-btn { width: 30px; height: 30px; border-radius: 50%; border: 1.5px solid var(--pink); background: var(--surface); color: var(--pink); cursor: pointer; font-size: 12px; font-weight: 700; font-family: inherit; transition: .15s; }
.rating-btn:hover { background: var(--pink-light); }
.rating-btn.active { background: var(--pink); color: #fff; border-color: var(--pink); }

/* Status per row */
.status-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-pill); background: #fce7ef; color: #c18ca0; white-space: nowrap; }
.status-badge.done { background: var(--green-light); color: #065f46; }
.status-badge.partial { background: #fef3c7; color: #92400e; }

.empty-row td { text-align: center; color: var(--text-3); padding: 32px 20px; font-size: 13px; }

/* SAVE */
.save-wrapper { display: flex; justify-content: flex-end; margin-top: 16px; }
.btn-save { display: flex; align-items: center; gap: 8px; border: none; background: linear-gradient(135deg, var(--pink), var(--pink-mid)); color: #fff; padding: 12px 22px; border-radius: var(--radius-lg); cursor: pointer; font-weight: 700; font-size: 13px; font-family: inherit; transition: .2s; box-shadow: 0 2px 8px rgba(232,0,90,.25); }
.btn-save:hover { background: linear-gradient(135deg, var(--pink-dark), var(--pink)); }
.btn-save svg { width: 15px; height: 15px; stroke: #fff; fill: none; stroke-width: 2.5; }
.btn-save.loading { opacity: .6; cursor: not-allowed; pointer-events: none; }

/* TOAST */
.toast { position: fixed; bottom: 24px; right: 24px; background: #1a0a0f; color: #fff; padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; box-shadow: 0 6px 24px #0003; transform: translateY(80px); opacity: 0; transition: .3s cubic-bezier(.34,1.56,.64,1); z-index: 999; pointer-events: none; }
.toast.show { transform: translateY(0); opacity: 1; }
.toast.success { background: #065f46; }
.toast.error { background: #dc2626; }

@media(max-width:900px){ .product-grid{grid-template-columns:1fr 1fr;} }
@media(max-width:640px){ .product-grid{grid-template-columns:1fr;} .top-filter{flex-direction:column;} }
</style>
</head>
<body>

{{-- ===== SIDEBAR ===== --}}
<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon">
        <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW" style="width:36px;height:36px;object-fit:contain;">
      </div>
      <div>
        <div class="sb-logo-name">DRW SKINCARE</div>
        <div class="sb-logo-sub">Analisis penjualan & produk</div>
      </div>
    </div>
  </div>
  <div class="sb-nav">
    <div class="nav-section">
      <div class="nav-label">Penentuan Produk Promosi</div>
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
      <a href="{{ route('input.index') }}" class="nav-item active">
        <svg viewBox="0 0 16 16"><path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v10" stroke-linecap="round"/></svg>
        Input Permintaan
      </a>
      <a href="{{ route('perhitungan.index') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><rect x="3" y="3" width="10" height="10" rx="2" fill="none"/><path d="M6.5 6.5h3M6.5 8.5h3M6.5 10.5h3" stroke-linecap="round"/></svg>
        Menghitung Prioritas
      </a>
      <a href="{{ route('perhitungan.riwayat') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l-2 2" stroke-linecap="round"/></svg>
        Riwayat Perhitungan
      </a>
    </div>
    <div class="nav-divider"></div>
    <div class="nav-section">
      <div class="nav-label">Pola & Insight Penjualan</div>
      <a href="{{ route('asosiasi.dashboard') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><rect x="2" y="2" width="5" height="5" rx="1.5"/><rect x="9" y="2" width="5" height="5" rx="1.5"/><rect x="2" y="9" width="5" height="5" rx="1.5"/><rect x="9" y="9" width="5" height="5" rx="1.5"/></svg>
        Dashboard Insight
      </a>
      @if(auth()->check() && auth()->user()->role === 'Admin')
      <a href="{{ route('asosiasi.analisis') }}" class="nav-item">
        <svg viewBox="0 0 16 16">
          <circle cx="7" cy="7" r="4"/>
          <path d="M10 10l3.5 3.5" stroke-linecap="round"/>
          <path d="M5.5 8.5V6.8" stroke-linecap="round"/>
          <path d="M7 8.5V5.5" stroke-linecap="round"/>
          <path d="M8.5 8.5V4.5" stroke-linecap="round"/>
        </svg>
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
        <button type="submit" title="Logout">
          <svg viewBox="0 0 16 16" width="15" height="15" stroke="currentColor" fill="none" stroke-width="1.8"><path d="M10 3h3a1 1 0 011 1v8a1 1 0 01-1 1h-3M7 11l3-3-3-3M10 8H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </form>
    </div>
  </div>
</div>

<!-- MAIN -->
<div class="main-wrap">
  <div class="topbar">
    <div class="topbar-title">Input Permintaan</div>
  </div>

  <div class="content">

    <!-- STEP 1 -->
    <div id="step1">

      <div class="stepper">
        <div class="step-bubble active">1</div>
        <div class="step-line"></div>
        <div class="step-bubble">2</div>
      </div>

      <div class="page-header">
        <h1>Input Permintaan Produk</h1>
        <p>Pilih maksimal 5 produk di setiap kategori sebelum melakukan penilaian kriteria.</p>
      </div>
        {{-- Banner: ada produk yang tidak muncul karena belum berkategori --}}
      @if(($produkBelumBerkategori ?? 0) > 0)
        <div style="background:#fef3c7;border:1px solid #f59e0b;color:#92400e;padding:10px 14px;border-radius:8px;margin-bottom:14px;font-size:13px;line-height:1.5">
          ⚠ <strong>{{ $produkBelumBerkategori }}</strong> produk tidak ditampilkan di sini karena belum berkategori.
          Admin perlu menetapkan kategorinya dulu di halaman <a href="{{ route('produk.index') }}" style="color:#b45309;text-decoration:underline;font-weight:600">Data Produk</a>.
        </div>
      @endif
      {{-- Filter --}}
      <div class="top-filter">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="text" class="search-input" id="searchInput" placeholder="Cari produk..." autocomplete="off">
        </div>
        <div class="cat-filter-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 6h18M7 12h10M11 18h2"/></svg>
          <select id="kategoriFilter">
            <option value="all">Semua Kategori</option>
            @foreach($produkByKategori as $kategori => $items)
              <option value="{{ Str::slug($kategori) }}">{{ $kategori }}</option>
            @endforeach
          </select>
        </div>
      </div>

      {{-- Summary --}}
      <div class="summary-box">
        <div>
          <div class="summary-label">Total Produk Dipilih</div>
          <div class="summary-count" id="totalSelected">0 produk</div>
        </div>
        <button class="btn-next" onclick="goToStep2()">
          Lanjut ke Penilaian
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </div>

      {{-- Kategori cards --}}
      @foreach($produkByKategori as $kategori => $items)
      @php $savedInCat = collect($items)->filter(fn($p) => in_array((string)$p->id_produk, $savedProdukIds))->count(); @endphp
      <div class="category-card" data-category="{{ Str::slug($kategori) }}">
        <div class="category-header" onclick="toggleCategory(this)">
          <div class="category-title">
            {{ $kategori }}
            <span class="cat-badge" id="count-{{ Str::slug($kategori) }}">{{ $savedInCat }}/5 dipilih</span>
          </div>
          <svg class="chevron open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </div>
        <div class="category-body" id="body-{{ Str::slug($kategori) }}" style="display:block;">
          <div class="product-grid">
            @foreach($items as $produk)
            @php $isSaved = in_array((string)$produk->id_produk, $savedProdukIds); @endphp
            <label class="product-item {{ $isSaved ? 'selected' : '' }}" data-name="{{ strtolower($produk->nama_produk) }}">
              <input type="checkbox" class="product-checkbox"
                data-id="{{ $produk->id_produk }}"
                data-name="{{ $produk->nama_produk }}"
                data-category-slug="{{ Str::slug($kategori) }}"
                onchange="toggleProduct(this)"
                {{ $isSaved ? 'checked' : '' }}>
              <span>{{ $produk->nama_produk }}</span>
            </label>
            @endforeach
          </div>
        </div>
      </div>
      @endforeach

    </div>{{-- /step1 --}}

    <!-- STEP 2 -->
    <div id="step2" style="display:none;">

      <div class="stepper">
        <div class="step-bubble">1</div>
        <div class="step-line"></div>
        <div class="step-bubble active">2</div>
      </div>

      <div class="page-header">
        <h1>Input Permintaan Produk</h1>
      </div>

      {{-- Progress --}}
      <div class="progress-box">
        <button class="btn-back" onclick="backToStep1()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
          Kembali
        </button>
        <div>
          <div class="progress-label">Progress Penilaian</div>
          <div class="progress-count" id="progressText">0 / 0 produk selesai</div>
        </div>
      </div>

      {{-- Kriteria info banner --}}
      <div class="kriteria-banner">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
        <div class="kriteria-banner-text">
          <strong>Beri nilai 1–5 untuk setiap kriteria per produk.</strong>
          Kriteria yang perlu dinilai secara manual:
          <div class="kriteria-chips" id="kriteriaChips">
            @foreach($kriterias as $k)
              <span class="kriteria-chip">
                @if(strtolower($k->tipe_atribut) === 'benefit')
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                @else
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                @endif
                {{ $k->nama_kriteria }}
              </span>
            @endforeach
          </div>
          <div class="kriteria-scale">
            <strong>Skala 1–5 untuk kriteria saat ini:</strong>
            <ul class="scale-list">
              @foreach($kriterias as $k)
                <li>
                  <span>{{ $k->nama_kriteria }}:</span>
                  1 = {{ $scaleLabel($k->nama_kriteria, 1) }},
                  2 = {{ $scaleLabel($k->nama_kriteria, 2) }},
                  3 = {{ $scaleLabel($k->nama_kriteria, 3) }},
                  4 = {{ $scaleLabel($k->nama_kriteria, 4) }},
                  5 = {{ $scaleLabel($k->nama_kriteria, 5) }}
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>

      {{-- Table --}}
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th class="no-col">No</th>
              <th>Nama Produk</th>
              {{-- Kolom per kriteria --}}
              @foreach($kriterias as $k)
              <th class="kriteria-col">
                {{ $k->nama_kriteria }}
                <span style="font-size:9px;font-weight:500;display:block;color:var(--text-3);text-transform:none;margin-top:1px;">
                  {{ strtolower($k->tipe_atribut) === 'benefit' ? '↑ Benefit' : '↓ Cost' }} · Bobot {{ $k->bobot }}%
                </span>
              </th>
              @endforeach
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="penilaianTable">
            <tr class="empty-row"><td colspan="{{ 3 + $kriterias->count() }}">Belum ada produk dipilih</td></tr>
          </tbody>
        </table>
      </div>

      <div class="save-wrapper">
        <button class="btn-save" id="btnSave" onclick="savePenilaian()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Penilaian
        </button>
      </div>

    </div>{{-- /step2 --}}

  </div>
</div>

<div class="toast" id="toast"></div>

{{-- Pass kriteria data to JS --}}
<script>
const KRITERIAS   = @json($kriteriaJs);
const SAVED_DATA  = @json($savedData);   // { id_produk: { id_kriteria: nilai } } dari DB

/* ======================================================
   STATE
====================================================== */
let selectedProducts = [];
// ratings[id_produk][id_kriteria] = nilai (1-5)
let ratings = {};

/* ======================================================
   ACCORDION
====================================================== */
function toggleCategory(headerEl) {
  const card = headerEl.closest('.category-card');
  const slug  = card.dataset.category;
  const body  = document.getElementById('body-' + slug);
  const chev  = headerEl.querySelector('.chevron');
  const open  = body.style.display !== 'none';
  body.style.display = open ? 'none' : 'block';
  chev.classList.toggle('open', !open);
}

/* ======================================================
   CHECKBOX
====================================================== */
function toggleProduct(el) {
  const slug    = el.dataset.categorySlug;
  const checked = document.querySelectorAll(`.product-checkbox[data-category-slug="${slug}"]:checked`);
  if (checked.length > 5) {
    el.checked = false;
    showToast('Maksimal 5 produk per kategori.', 'error');
    return;
  }
  document.getElementById('count-' + slug).textContent = checked.length + '/5 dipilih';
  el.closest('.product-item').classList.toggle('selected', el.checked);
  rebuildSelected();
}

function rebuildSelected() {
  selectedProducts = [];
  document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
    selectedProducts.push({ id: cb.dataset.id, name: cb.dataset.name });
  });
  document.getElementById('totalSelected').textContent = selectedProducts.length + ' produk';
}

/* ======================================================
   STEP NAVIGATION
====================================================== */
function goToStep2() {
  if (!selectedProducts.length) { showToast('Pilih minimal 1 produk terlebih dahulu.', 'error'); return; }
  if (!KRITERIAS.length) { showToast('Belum ada kriteria manual yang ditentukan.', 'error'); return; }
  // Reset ratings untuk sesi ini, lalu restore dari SAVED_DATA (data DB)
  // Hanya produk yang ada di selectedProducts yang dihitung
  ratings = {};
  selectedProducts.forEach(p => {
    if (SAVED_DATA[p.id]) {
      ratings[p.id] = Object.assign({}, SAVED_DATA[p.id]);
    } else {
      ratings[p.id] = {};
    }
  });
  document.getElementById('step1').style.display = 'none';
  document.getElementById('step2').style.display = 'block';
  renderTable();
}

function backToStep1() {
  document.getElementById('step2').style.display = 'none';
  document.getElementById('step1').style.display = 'block';
}

/* ======================================================
   RENDER TABLE — baris = produk, kolom = kriteria
====================================================== */
function renderTable() {
  const tbody = document.getElementById('penilaianTable');
  tbody.innerHTML = '';

  if (!selectedProducts.length) {
    tbody.innerHTML = `<tr class="empty-row"><td colspan="${3 + KRITERIAS.length}">Belum ada produk dipilih</td></tr>`;
    return;
  }

  selectedProducts.forEach((item, idx) => {
    ratings[item.id] = ratings[item.id] || {};

    const tr = document.createElement('tr');
    tr.id = 'row-' + item.id;

    // kolom No + Nama
    let html = `<td class="no-col">${idx + 1}</td><td class="name-col">${esc(item.name)}</td>`;

    // kolom per kriteria
    KRITERIAS.forEach(k => {
      const btns = [1,2,3,4,5].map(v =>
        `<button type="button" class="rating-btn" data-value="${v}" data-produk="${item.id}" data-kriteria="${k.id}" onclick="selectRating(this)">${v}</button>`
      ).join('');
      html += `<td><div class="rating-group" id="rg-${item.id}-${k.id}">${btns}</div></td>`;
    });

    // kolom status
    html += `<td class="status-col"><span class="status-badge" id="status-${item.id}">Belum Selesai</span></td>`;

    tr.innerHTML = html;
    tbody.appendChild(tr);
  });

  // Restore button states dari ratings (sudah include SAVED_DATA di goToStep2)
  // Setelah restore, baru hitung progress
  selectedProducts.forEach(function(item) {
    var savedRatings = ratings[item.id] || {};
    Object.entries(savedRatings).forEach(function(entry) {
      var kriteriaId = entry[0], nilai = entry[1];
      var rg = document.getElementById('rg-' + item.id + '-' + kriteriaId);
      if (!rg) return;
      rg.querySelectorAll('.rating-btn').forEach(function(btn) {
        btn.classList.toggle('active', parseInt(btn.dataset.value) === parseInt(nilai));
      });
      updateRowStatus(item.id);
    });
  });

  // Progress dihitung SETELAH semua button direstored
  updateProgress();
}

/* ======================================================
   RATING PER KRITERIA
====================================================== */
function selectRating(btn) {
  const produkId   = btn.dataset.produk;
  const kriteriaId = btn.dataset.kriteria;
  const value      = parseInt(btn.dataset.value);

  document.getElementById(`rg-${produkId}-${kriteriaId}`)
    .querySelectorAll('.rating-btn')
    .forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  if (!ratings[produkId]) ratings[produkId] = {};
  ratings[produkId][kriteriaId] = value;

  updateRowStatus(produkId);
  updateProgress();
}

function updateRowStatus(produkId) {
  const currentIds = KRITERIAS.map(k => String(k.id));
  const saved      = ratings[produkId] || {};

  // Hanya hitung kriteria yang ADA di KRITERIAS saat ini (bukan sisa data lama)
  const filledCurrent = currentIds.filter(kid => saved[kid] !== undefined && saved[kid] !== null).length;
  const total         = currentIds.length;
  const badge         = document.getElementById('status-' + produkId);

  if (filledCurrent === 0) {
    badge.textContent = 'Belum Selesai';
    badge.className   = 'status-badge';
  } else if (filledCurrent < total) {
    badge.textContent = `${filledCurrent}/${total} terisi`;
    badge.className   = 'status-badge partial';
  } else {
    badge.textContent = 'Selesai';
    badge.className   = 'status-badge done';
  }
}

function updateProgress() {
  const total = selectedProducts.length;
  const currentIds = KRITERIAS.map(k => String(k.id));
  const done  = selectedProducts.filter(p => {
    const saved = ratings[p.id] || {};
    return currentIds.every(kid => saved[kid] !== undefined && saved[kid] !== null);
  }).length;
  document.getElementById('progressText').textContent = `${done} / ${total} produk selesai`;
}

/* ======================================================
   SAVE
====================================================== */
function savePenilaian() {
  const total    = selectedProducts.length;
  const currentIds = KRITERIAS.map(k => String(k.id));
  const notDone  = selectedProducts.filter(p => {
    const saved = ratings[p.id] || {};
    return !currentIds.every(kid => saved[kid] !== undefined && saved[kid] !== null);
  });

  if (notDone.length > 0) {
    showToast(`${notDone.length} produk belum semua kriteria diisi.`, 'error');
    return;
  }

  const btn = document.getElementById('btnSave');
  btn.classList.add('loading');
  btn.textContent = 'Menyimpan...';

  fetch("{{ route('input.store') }}", {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: new URLSearchParams({ data: JSON.stringify(ratings) })
  })
  .then(r => {
    if (!r.ok) {
      return r.json().then(err => { throw new Error(err.message || 'Server error ' + r.status); });
    }
    return r.json();
  })
  .then(res => {
    if (res.success) {
      showToast('Penilaian berhasil disimpan!', 'success');
    } else {
      showToast(res.message || 'Gagal menyimpan data.', 'error');
    }
  })
  .catch(err => showToast('Gagal: ' + err.message, 'error'))
  .finally(() => {
    btn.classList.remove('loading');
    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan Penilaian`;
  });
}

/* ======================================================
   SEARCH
====================================================== */
document.getElementById('searchInput').addEventListener('input', function () {
  const kw = this.value.trim().toLowerCase();
  document.querySelectorAll('.category-card').forEach(card => {
    let any = false;
    card.querySelectorAll('.product-item').forEach(item => {
      const match = !kw || item.dataset.name.includes(kw);
      item.classList.toggle('hidden', !match);
      if (match) any = true;
    });
    card.classList.toggle('hidden', !any && kw !== '');
  });
});

/* ======================================================
   CATEGORY FILTER
====================================================== */
document.getElementById('kategoriFilter').addEventListener('change', function () {
  const val = this.value;
  document.querySelectorAll('.category-card').forEach(card => {
    card.classList.toggle('hidden', val !== 'all' && card.dataset.category !== val);
  });
});

/* ======================================================
   TOAST
====================================================== */
let _tt;
function showToast(msg, type) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = 'toast show ' + (type || '');
  clearTimeout(_tt);
  _tt = setTimeout(() => t.classList.remove('show'), 3500);
}

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ======================================================
   INIT — restore state dari DB saat halaman dimuat
====================================================== */
document.addEventListener('DOMContentLoaded', function() {
  // Update badge count & selectedProducts dari checkbox yg sudah pre-checked
  rebuildSelected();

  // Update setiap badge per kategori
  document.querySelectorAll('.category-card').forEach(function(card) {
    var slug    = card.dataset.category;
    var checked = card.querySelectorAll('.product-checkbox:checked').length;
    var badge   = document.getElementById('count-' + slug);
    if (badge) badge.textContent = checked + '/5 dipilih';
  });

  // Pre-load ratings dari SAVED_DATA ke state ratings
  Object.entries(SAVED_DATA).forEach(function(entry) {
    var produkId = entry[0], kriteriaMap = entry[1];
    ratings[produkId] = Object.assign({}, kriteriaMap);
  });
});

</script>
</body>
</html>