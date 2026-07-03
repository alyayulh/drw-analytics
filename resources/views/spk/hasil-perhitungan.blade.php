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

/* ============ MODE TOGGLE ============ */
.mode-switcher {
  display: inline-flex;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 3px;
  gap: 2px;
}
.mode-btn {
  background: transparent;
  border: none;
  padding: 7px 14px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-3);
  border-radius: 7px;
  cursor: pointer;
  transition: all .15s ease;
  font-family: inherit;
}
.mode-btn.active {
  background: var(--surface);
  color: var(--pink);
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.mode-btn:hover:not(.active) { color: var(--text-2); }

/* Sembunyikan mode yang tidak aktif. Pakai !important supaya menang dari style inline. */
body[data-mode="awam"]   .mode-detail-only { display: none !important; }
body[data-mode="detail"] .mode-awam-only   { display: none !important; }

/* ============ KARTU PRODUK MODE AWAM ============ */
.produk-card-awam {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  margin-bottom: 10px;
  transition: border-color .15s, transform .15s;
}
.produk-card-awam:hover { border-color: var(--border-strong); transform: translateY(-1px); }
.produk-card-awam.top1 { border-color: #fbbf24; background: linear-gradient(to right, #fffbeb 0%, var(--surface) 50%); }
.produk-rank-bubble {
  width: 38px; height: 38px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 14px; flex-shrink: 0;
  background: #f3f4f6; color: #6b7280;
  font-family: 'DM Mono', monospace;
}
.produk-rank-bubble.top1 { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #fff; box-shadow: 0 2px 6px rgba(251,191,36,.35); }
.produk-rank-bubble.top2 { background: #d1d5db; color: #374151; }
.produk-rank-bubble.top3 { background: linear-gradient(135deg, #fdba74, #fb923c); color: #fff; }
.produk-info-awam { flex: 1; min-width: 0; }
.produk-nama-awam {
  font-weight: 700; font-size: 14px;
  color: var(--text); margin-bottom: 4px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.produk-alasan-awam {
  font-size: 12px; color: var(--text-2); line-height: 1.5;
}
.produk-alasan-awam .icon-bulb { margin-right: 4px; }
.produk-badge-awam {
  flex-shrink: 0;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 11px; font-weight: 700;
  white-space: nowrap;
}
.badge-awam-utama  { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.badge-awam-pertim { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
.badge-awam-tunda  { background: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }

.section-title-awam {
  font-size: 13px; font-weight: 700; color: var(--text-2);
  margin: 18px 0 10px;
  display: flex; align-items: center; gap: 8px;
}
.section-title-awam:first-child { margin-top: 0; }
.section-count-pill {
  background: var(--bg); border: 1px solid var(--border);
  padding: 2px 8px; border-radius: 6px;
  font-size: 11px; color: var(--text-3); font-weight: 600;
}
.empty-state-awam {
  text-align: center; padding: 30px 20px;
  color: var(--text-3); font-size: 12px;
  background: var(--bg); border-radius: 10px; border: 1px dashed var(--border);
}

/* ============ PANDUAN VERSI AWAM (simpel, bahasa bisnis) ============ */
.guide-awam {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}
.guide-awam-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 18px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  transition: transform .15s, border-color .15s, box-shadow .15s;
}
.guide-awam-card:hover {
  transform: translateY(-2px);
  border-color: var(--border-strong);
  box-shadow: 0 12px 30px rgba(0,0,0,.06);
}
.guide-awam-card .ga-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}
.guide-awam-card .ga-icon {
  width: 44px; height: 44px;
  border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.guide-awam-card.ga-utama { background: #fdf2f8; border-color: #fbcfe8; }
.guide-awam-card.ga-utama .ga-icon { background: #fce7f3; color: #9d174d; }
.guide-awam-card.ga-pertim { background: #fffbeb; border-color: #fde68a; }
.guide-awam-card.ga-pertim .ga-icon { background: #fef3c7; color: #92400e; }
.guide-awam-card.ga-tunda { background: #eff6ff; border-color: #bfdbfe; }
.guide-awam-card.ga-tunda .ga-icon { background: #dbeafe; color: #1d4ed8; }
.guide-awam-card .ga-body { min-width: 0; }
.guide-awam-card .ga-label {
  font-size: 13px; font-weight: 800; color: var(--text);
  margin-bottom: 4px;
}
.guide-awam-card .ga-desc {
  font-size: 12px; color: var(--text-3); line-height: 1.6;
}
.guide-awam-card .ga-count {
  font-size: 32px; font-weight: 800; color: var(--text);
  min-width: 54px;
  text-align: right;
}
.guide-awam-title {
  font-size: 13px; font-weight: 800; color: var(--text);
  margin-bottom: 4px;
}
.guide-awam-sub {
  font-size: 11px; color: var(--text-3);
  margin-bottom: 12px;
}

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
.sb-brand { padding: 22px 18px 16px; display: flex; align-items: center; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, #e8005a08, #ff4d8d05); }
.sb-logo-name { letter-spacing: -.3px; }
.sb-nav { overflow-y: auto; }
.nav-divider { height: 1px; background: var(--border); margin: 8px 10px; }
.sb-footer { background: linear-gradient(135deg, #fff5f8, #fff); }
.sb-logout { margin-left: auto; cursor: pointer; color: var(--text-3); }
.sb-logout:hover { color: var(--pink); }


/* ============================================================
   VERSI BARU — LAYOUT REKOMENDASI BERBEDA
   ============================================================ */
body {
  background:
    radial-gradient(circle at 14% 8%, rgba(232, 0, 90, .08), transparent 26%),
    radial-gradient(circle at 92% 0%, rgba(255, 77, 141, .10), transparent 30%),
    var(--bg);
}
.main { background: transparent; }
.topbar {
  height: 56px;
  background: var(--surface);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  padding: 0 28px;
  gap: 12px;
  position: sticky;
  top: 0;
  z-index: 10;
  box-shadow: none;
  backdrop-filter: none;
}
.content {
  padding: 30px 34px 44px;
  width: 100%;
  max-width: 1400px;
  margin: 0 auto;
}
.sidebar {
  background: var(--surface);
  border-right: 1px solid var(--border);
}
.sb-brand { background: linear-gradient(135deg, #e8005a08, #ff4d8d05); }
.sb-footer { background: linear-gradient(135deg, #fff5f8, #fff); }
.nav-item.active {
  background: linear-gradient(135deg, var(--pink-light), #ffe4ef);
  color: var(--pink);
}
.nav-item:hover { background: var(--pink-light); color: var(--pink-dark); }
.mode-switcher {
  display: inline-flex;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 3px;
  gap: 2px;
}
.mode-btn {
  background: transparent;
  border: none;
  padding: 7px 14px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-3);
  border-radius: 7px;
  cursor: pointer;
  transition: all .15s ease;
  font-family: inherit;
}
.mode-btn.active {
  background: var(--surface);
  color: var(--pink);
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.mode-btn:hover:not(.active) { color: var(--text-2); }
.btn { border-radius: 8px; border-color: var(--border-strong); }
.btn-pink {
  background: linear-gradient(135deg, var(--pink), var(--pink-mid));
  color: #fff;
  border: none;
  box-shadow: 0 2px 8px rgba(232,0,90,.25);
}
.card {
  border-radius: var(--radius-lg);
  border-color: var(--border);
  box-shadow: var(--shadow);
}
.hero-box {
  position: relative;
  overflow: hidden;
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) minmax(280px, .8fr);
  gap: 22px;
  align-items: stretch;
  padding: 28px;
  margin-bottom: 22px;
  border-radius: 28px;
  color: #fff;
  background:
    radial-gradient(circle at 12% 10%, rgba(255,255,255,.22), transparent 24%),
    radial-gradient(circle at 92% 18%, rgba(255,214,232,.35), transparent 25%),
    linear-gradient(135deg, #b3004a 0%, #e8005a 50%, #ff4d8d 100%);
  box-shadow: 0 24px 50px rgba(232,0,90,.20);
}
.hero-box::after {
  content: "";
  position: absolute;
  right: -90px;
  bottom: -110px;
  width: 260px;
  height: 260px;
  border-radius: 999px;
  border: 34px solid rgba(255,255,255,.12);
}
.hero-main, .hero-summary { position: relative; z-index: 1; }
.hero-main {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 150px;
}
.hero-label {
  width: fit-content;
  padding: 7px 12px;
  border-radius: 999px;
  background: rgba(255,255,255,.18);
  border: 1px solid rgba(255,255,255,.28);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
  margin-bottom: 14px;
}
.hero-name {
  font-size: clamp(25px, 3vw, 36px);
  font-weight: 800;
  letter-spacing: -.8px;
  line-height: 1.06;
  margin-bottom: 10px;
}
.hero-sub { font-size: 13px; opacity: .88; line-height: 1.6; max-width: 560px; }
.hero-action-note {
  margin-top: 16px;
  width: fit-content;
  padding: 9px 13px;
  border-radius: 14px;
  background: rgba(255,255,255,.17);
  border: 1px solid rgba(255,255,255,.24);
  font-size: 12px;
  font-weight: 700;
}
.hero-summary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  align-content: center;
}
.hero-summary-card {
  padding: 15px 16px;
  border-radius: 18px;
  background: rgba(255,255,255,.16);
  border: 1px solid rgba(255,255,255,.25);
  backdrop-filter: blur(10px);
}
.hero-summary-card span {
  display: block;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: .08em;
  opacity: .78;
  font-weight: 800;
  margin-bottom: 8px;
}
.hero-summary-card strong { font-size: 22px; font-weight: 800; }
.hero-summary-wide { grid-column: 1 / -1; }
.hero-summary-wide strong { font-size: 14px; }
.meta-row { display: none; }
.awam-board {
  display: grid;
  grid-template-columns: 350px minmax(0, 1fr);
  gap: 18px;
  align-items: start;
}
.insight-panel, .recommend-panel {
  background: rgba(255,255,255,.94);
  border: 1px solid var(--border);
  border-radius: 26px;
  box-shadow: 0 12px 28px rgba(232,0,90,.07);
}
.insight-panel {
  position: sticky;
  top: 82px;
  padding: 22px;
}
.panel-kicker {
  color: var(--pink);
  font-size: 10px;
  letter-spacing: .12em;
  text-transform: uppercase;
  font-weight: 800;
  margin-bottom: 6px;
}
.insight-panel h2, .recommend-head h2 {
  font-size: 18px;
  line-height: 1.2;
  letter-spacing: -.35px;
  margin: 0 0 6px;
  color: var(--text);
}
.insight-panel p, .recommend-head p {
  color: var(--text-3);
  font-size: 12px;
  line-height: 1.6;
  margin: 0;
}
.summary-stack { display: flex; flex-direction: column; gap: 12px; margin-top: 18px; }
.summary-tile {
  display: grid;
  grid-template-columns: 44px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 14px;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: #fff;
}
.summary-tile .tile-icon {
  width: 44px;
  height: 44px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  font-size: 18px;
}
.tile-copy span { display: block; font-size: 13px; font-weight: 800; color: var(--text); margin-bottom: 3px; }
.tile-copy small { display: block; font-size: 11px; line-height: 1.4; color: var(--text-3); }
.tile-count { font-size: 28px; line-height: 1; font-weight: 800; color: var(--text); }
.tile-utama { background: linear-gradient(135deg, #f0fdf4, #fff); border-color: #bbf7d0; }
.tile-utama .tile-icon { background: #dcfce7; }
.tile-pertim { background: linear-gradient(135deg, #fff7ed, #fff); border-color: #fed7aa; }
.tile-pertim .tile-icon { background: #ffedd5; }
.tile-tunda { background: linear-gradient(135deg, #eff6ff, #fff); border-color: #bfdbfe; }
.tile-tunda .tile-icon { background: #dbeafe; }
.recommend-panel { padding: 0; overflow: hidden; }
.recommend-head {
  padding: 22px 24px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(135deg, rgba(232,0,90,.05), rgba(255,77,141,.04));
}
.total-products-chip {
  flex-shrink: 0;
  padding: 8px 13px;
  border-radius: 999px;
  background: #fff;
  border: 1px solid var(--border);
  color: var(--pink);
  font-size: 12px;
  font-weight: 800;
}
.recommend-content { padding: 18px 22px 22px; }
.rec-section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 18px 0 10px;
  color: var(--text-2);
  font-size: 13px;
  font-weight: 800;
}
.rec-section-title:first-child { margin-top: 0; }
.rec-section-title b {
  padding: 3px 9px;
  border-radius: 999px;
  background: #fff;
  border: 1px solid var(--border);
  color: var(--text-3);
  font-size: 10px;
  font-weight: 800;
}
.rec-section-title em {
  margin-left: auto;
  font-size: 11px;
  color: var(--text-3);
  font-style: normal;
  font-weight: 700;
}
.produk-card-awam {
  position: relative;
  display: grid;
  grid-template-columns: 54px minmax(0, 1fr) auto;
  align-items: center;
  gap: 14px;
  padding: 15px 17px;
  margin-bottom: 10px;
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: 20px;
  transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
}
.produk-card-awam:hover {
  transform: translateY(-2px);
  border-color: var(--border-strong);
  box-shadow: 0 12px 28px rgba(232,0,90,.07);
}
.produk-card-awam.top1 {
  background: linear-gradient(135deg, #fff5f8 0%, #ffffff 68%);
  border-color: var(--border-strong);
  box-shadow: 0 14px 28px rgba(232,0,90,.10);
}
.produk-rank-bubble {
  width: 42px;
  height: 42px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  font-size: 12px;
  font-weight: 800;
  font-family: 'Plus Jakarta Sans', sans-serif;
  background: #f0ece8;
  color: #78625f;
}
.produk-rank-bubble.top1 {
  background: linear-gradient(135deg, var(--pink), var(--pink-mid));
  color: #fff;
  box-shadow: 0 10px 22px rgba(245,158,11,.23);
}
.produk-rank-bubble.top2 { background: #e5e7eb; color: #374151; }
.produk-rank-bubble.top3 { background: #ffedd5; color: #9a3412; }
.produk-info-awam { min-width: 0; }
.produk-nama-awam {
  font-size: 14px;
  font-weight: 800;
  letter-spacing: -.2px;
  color: var(--text);
  margin-bottom: 5px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.produk-alasan-awam {
  font-size: 11px;
  color: var(--text-3);
  line-height: 1.45;
}
.produk-badge-awam {
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 10px;
  font-weight: 800;
  white-space: nowrap;
}
.badge-awam-utama { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.badge-awam-pertim { background: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
.badge-awam-tunda { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.empty-state-awam {
  border-radius: 20px;
  background: #fff;
  border: 1px dashed var(--border-strong);
}
@media (max-width: 1100px) {
  .hero-box, .awam-board { grid-template-columns: 1fr; }
  .insight-panel { position: static; }
}
@media (max-width: 760px) {
  .content { padding: 20px 16px 32px; }
  .topbar { height: auto; min-height: 64px; flex-wrap: wrap; padding: 12px 16px; }
  .hero-box { padding: 22px; border-radius: 22px; }
  .hero-summary { grid-template-columns: 1fr; }
  .produk-card-awam { grid-template-columns: 44px minmax(0,1fr); }
  .produk-badge-awam { grid-column: 2 / -1; width: fit-content; }
}

</style>
</head>
{{-- 
  data-mode="awam" wajib agar mode default benar SEBELUM JS jalan
  (cegah flash kedua mode muncul bersamaan saat halaman pertama dimuat).
  Nanti JS akan override dari localStorage kalau user pernah pilih mode lain.
--}}
<body data-mode="awam">

{{-- Halaman hasil perhitungan SPK.
     2 mode: "awam" (default, bahasa bisnis) dan "detail" (perhitungan MOORA lengkap).
     Switching via tombol di topbar, preferensi disimpan di localStorage. --}}

<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon" style="background:none;box-shadow:none;">
        <img src="https://pos.drwskincare.com/logo_drw.svg" alt="DRW" style="width:36px;height:36px;object-fit:contain;">
      </div>
      <div>
        <div class="sb-logo-name">DRW SKINCARE</div>
        <div class="sb-logo-sub">Analisis penjualan &amp; produk</div>
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
      <a href="{{ route('input.index') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><path d="M2 8h8M8 5l3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 3v10" stroke-linecap="round"/></svg>
        Input Permintaan
      </a>
      <a href="{{ route('perhitungan.index') }}" class="nav-item">
        <svg viewBox="0 0 16 16"><rect x="3" y="3" width="10" height="10" rx="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.5 6.5h3" stroke-linecap="round"/><path d="M6.5 8.5h3" stroke-linecap="round"/><path d="M6.5 10.5h3" stroke-linecap="round"/></svg>
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

    <div class="mode-switcher" role="tablist" aria-label="Pilih mode tampilan">
      <button type="button" class="mode-btn active" data-mode="awam" onclick="switchMode('awam')">
        🎯 Rekomendasi
      </button>
      <button type="button" class="mode-btn" data-mode="detail" onclick="switchMode('detail')">
        📊 Detail Perhitungan
      </button>
    </div>

    <a href="{{ route('perhitungan.riwayat') }}" class="btn btn-sm">Lihat Riwayat</a>
    <a href="{{ route('perhitungan.index') }}" class="btn btn-pink btn-sm">Hitung Ulang</a>
  </div>
  <div class="content">

    {{-- HERO: PRODUK TERBAIK (tampil di kedua mode, karena ini ringkasan tertinggi) --}}
    <div class="hero-box">
      <div class="hero-main">
        <div class="hero-label">🏆 Produk Terbaik</div>
        <div class="hero-name">{{ $perhitungan->produk_prioritas }}</div>
        <div class="hero-sub">Produk dengan performa paling kuat berdasarkan hasil perhitungan sistem.</div>
        <div class="hero-action-note">Cocok dijadikan fokus utama dalam periode promosi ini.</div>
      </div>

      <div class="hero-summary">
        <div class="hero-summary-card">
          <span>Produk Dihitung</span>
          <strong>{{ $perhitungan->jumlah_produk }}</strong>
        </div>
        <div class="hero-summary-card">
          <span>Kriteria</span>
          <strong>{{ $kriterias->count() }}</strong>
        </div>
        <div class="hero-summary-card hero-summary-wide">
          <span>Waktu Proses</span>
          <strong>{{ \Carbon\Carbon::parse($perhitungan->created_at)->format('d M Y H:i') }}</strong>
        </div>
      </div>
    </div>

    {{-- ========== LEGENDA PRIORITAS (hanya muncul di mode DETAIL) ========== --}}
    <style>
      .priority-section { margin-bottom: 20px; }
      .priority-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
      .priority-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 18px;
        min-height: 130px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        box-shadow: var(--shadow);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        overflow: hidden;
      }
      .priority-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(232,0,90,.12);
        border-color: var(--border-strong);
      }
      .priority-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
      }
      .priority-card.priority-1::before { background: linear-gradient(90deg, #10b981, #34d399); }
      .priority-card.priority-2::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
      .priority-card.priority-3::before { background: linear-gradient(90deg, #e8005a, #ff4d8d); }
      .priority-icon {
        width: 52px;
        height: 52px;
        display: grid;
        place-items: center;
        border-radius: 16px;
        background: rgba(232, 0, 90, 0.08);
        font-size: 24px;
        flex-shrink: 0;
      }
      .priority-meta { display: flex; flex-direction: column; gap: 8px; width: 100%; }
      .priority-title { font-size: 13px; font-weight: 800; color: var(--text); margin: 0; }
      .priority-subtitle { font-size: 12px; color: var(--text-2); line-height: 1.4; margin: 0; }
      .priority-percentage { font-size: 14px; font-weight: 800; color: var(--text); margin-top: 4px; }
      .priority-body { display: grid; gap: 6px; font-size: 12px; color: var(--text-2); line-height: 1.5; }
      .priority-body p { margin: 0; }
      .priority-details { font-size: 11px; color: var(--text-3); background: var(--bg); padding: 10px 12px; border-radius: 12px; border: 1px solid var(--border); line-height: 1.5; }
      .priority-note { margin-top: 12px; padding: 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 12px; font-size: 11px; color: var(--text-3); line-height: 1.6; }
    </style>

    <div class="priority-section mode-detail-only">
      <div style="margin-bottom: 16px;">
        <div class="card-title" style="font-size:15px;margin-bottom:4px"> Panduan Kategori Prioritas</div>
        <div class="card-sub">Setiap produk diklasifikasikan berdasarkan performa hasil perhitungan sistem</div>
      </div>

      <div class="priority-grid">
        <div class="priority-card priority-1">
          <div class="priority-icon">🏆</div>
          <div class="priority-meta">
            <div>
              <div class="priority-title">Prioritas Utama</div>
              <div class="priority-subtitle">Produk bintang lima dengan performa terbaik</div>
            </div>
            <div class="priority-percentage">Top 75%</div>
            <div class="priority-body">
              <p>Produk dengan performa <b>tertinggi</b> dibanding yang lain</p>
              <p><b>Rekomendasi utama</b> untuk dipromosikan</p>
            </div>
          </div>
        </div>

        <div class="priority-card priority-2">
          <div class="priority-icon">⚖️</div>
          <div class="priority-meta">
            <div>
              <div class="priority-title">Perlu Dipertimbangkan</div>
              <div class="priority-subtitle">Produk bagus dengan potensi yang layak</div>
            </div>
            <div class="priority-percentage">25-75%</div>
            <div class="priority-body">
              <p>Performa <b>cukup baik</b> dan konsisten</p>
              <p><b>Layak dipromosikan</b> dengan pertimbangan lebih lanjut</p>
            </div>
            <div class="priority-details">
              Dapat menjadi produk pendamping promosi untuk menambah pilihan customer
            </div>
          </div>
        </div>

        <div class="priority-card priority-3">
          <div class="priority-icon">⏸️</div>
          <div class="priority-meta">
            <div>
              <div class="priority-title">Tunda</div>
              <div class="priority-subtitle">Produk performa rendah, belum siap promosi</div>
            </div>
            <div class="priority-percentage">25% terbawah</div>
            <div class="priority-body">
              <p>Performa <b>rendah</b> dibanding produk lain</p>
              <p><b>Belum direkomendasikan</b> untuk dipromosikan saat ini</p>
            </div>
            <div class="priority-details">
              Analisis ulang strategi penjualan atau pertimbangkan perbaikan produk sebelum promosi
            </div>
          </div>
        </div>
      </div>

      <div class="priority-note">
        <strong>Catatan Penting:</strong> Jika ada produk dengan skor sama, sistem akan mengurutkan berdasarkan: (1) Total benefit tertinggi, (2) Total cost terendah, (3) Nama produk (A–Z)
      </div>
    </div>

    {{-- Persiapan data untuk kedua mode --}}
    @php
      // Sort berlapis untuk memastikan urutan konsisten
      $hasil = $hasil->sortBy([
        fn($a, $b) => $a->ranking <=> $b->ranking,
        fn($a, $b) => $b->total_benefit <=> $a->total_benefit,
        fn($a, $b) => $a->total_cost <=> $b->total_cost,
        fn($a, $b) => strcmp($a->nama_produk, $b->nama_produk),
      ]);

      // ===== Generator narasi otomatis (mode awam) =====
      // Mapping nama kriteria umum -> frasa awam.
      // Kalau kriteria tidak ada di map, pakai fallback (nama kriteria + status).
      $frasaKriteria = [
        'jumlah_penjualan'   => ['baik' => 'penjualan tinggi',    'buruk' => 'penjualan rendah'],
        'penjualan'          => ['baik' => 'penjualan tinggi',    'buruk' => 'penjualan rendah'],
        'tingkat_permintaan' => ['baik' => 'permintaan tinggi',   'buruk' => 'permintaan rendah'],
        'permintaan'         => ['baik' => 'permintaan tinggi',   'buruk' => 'permintaan rendah'],
        'stok'               => ['baik' => 'stok mencukupi',      'buruk' => 'stok menipis'],
        'harga_jual'         => ['baik' => 'harga menarik',       'buruk' => 'harga kurang kompetitif'],
        'harga'              => ['baik' => 'harga menarik',       'buruk' => 'harga kurang kompetitif'],
        'rating'             => ['baik' => 'rating bagus',        'buruk' => 'rating kurang'],
        'margin'             => ['baik' => 'margin menguntungkan','buruk' => 'margin tipis'],
        'biaya'              => ['baik' => 'biaya rendah',        'buruk' => 'biaya tinggi'],
      ];

      $generateNarasi = function($detailPerhitungan) use ($frasaKriteria) {
          $faktor = [];
          foreach ($detailPerhitungan as $d) {
              $kontribusi = abs(($d->nilai_normal ?? 0) * ($d->bobot ?? 0) / 100);
              $isBenefit  = strtolower($d->tipe_atribut ?? '') === 'benefit';
              // Indikator "baik": Benefit dgn nilai normal tinggi, atau Cost dgn nilai normal rendah.
              // Threshold 0.4 = heuristic median normalisasi MOORA.
              $isPositif = $isBenefit ? ($d->nilai_normal >= 0.4) : ($d->nilai_normal < 0.4);

              $key = strtolower(str_replace([' ', '-'], '_', $d->nama_kriteria));
              $frasa = $frasaKriteria[$key] ?? [
                  'baik'  => strtolower($d->nama_kriteria) . ' bagus',
                  'buruk' => strtolower($d->nama_kriteria) . ' rendah',
              ];
              $faktor[] = [
                  'kontribusi' => $kontribusi,
                  'positif'    => $isPositif,
                  'frasa'      => $isPositif ? $frasa['baik'] : $frasa['buruk'],
              ];
          }
          // Sort kontribusi terbesar, ambil top 2 yg positif
          usort($faktor, fn($a, $b) => $b['kontribusi'] <=> $a['kontribusi']);
          $positif = array_values(array_filter($faktor, fn($f) => $f['positif']));
          $top = array_slice($positif, 0, 2);
          if (count($top) === 0) return 'Skor keseluruhan masih perlu ditingkatkan.';
          if (count($top) === 1) return ucfirst($top[0]['frasa']) . '.';
          return ucfirst($top[0]['frasa']) . ' dan ' . $top[1]['frasa'] . '.';
      };

      // Kelompokkan hasil berdasarkan prioritas
      $utama      = $hasil->where('prioritas', 'Utama')->values();
      $pertimbang = $hasil->where('prioritas', 'Pertimbangkan')->values();
      $tunda      = $hasil->whereNotIn('prioritas', ['Utama', 'Pertimbangkan'])->values();
    @endphp

    {{-- Layout ringkasan di kiri, daftar rekomendasi di kanan --}}
    <div class="awam-board mode-awam-only">
      <aside class="insight-panel">
        <div class="panel-kicker">Ringkasan Keputusan</div>
        <h2>Panduan Rekomendasi</h2>
        <p>Produk dibagi menjadi tiga kelompok agar keputusan promosi lebih cepat dibaca.</p>

        <div class="summary-stack">
          <div class="summary-tile tile-utama">
            <div class="tile-icon">⭐</div>
            <div class="tile-copy">
              <span>Sangat Direkomendasikan</span>
              <small>Siap diprioritaskan untuk promosi.</small>
            </div>
            <div class="tile-count">{{ $utama->count() }}</div>
          </div>

          <div class="summary-tile tile-pertim">
            <div class="tile-icon">⚖️</div>
            <div class="tile-copy">
              <span>Perlu Pertimbangan</span>
              <small>Layak dicek lagi sebelum final.</small>
            </div>
            <div class="tile-count">{{ $pertimbang->count() }}</div>
          </div>

          <div class="summary-tile tile-tunda">
            <div class="tile-icon">⏳</div>
            <div class="tile-copy">
              <span>Sebaiknya Ditunda</span>
              <small>Belum menjadi prioritas promosi.</small>
            </div>
            <div class="tile-count">{{ $tunda->count() }}</div>
          </div>
        </div>
      </aside>

      <section class="recommend-panel">
        <div class="recommend-head">
          <div>
            <div class="panel-kicker">Hasil Rekomendasi</div>
            <h2>Daftar Prioritas Produk</h2>
            <p>Urutan produk berdasarkan hasil perhitungan dan kategori promosi.</p>
          </div>
          <div class="total-products-chip">{{ $hasil->count() }} produk</div>
        </div>

        <div class="recommend-content">
          {{-- KATEGORI 1: SANGAT DIREKOMENDASIKAN --}}
          @if($utama->count() > 0)
            <div class="rec-section-title title-utama">
              <span>⭐ Sangat Direkomendasikan</span>
              <b>{{ $utama->count() }} produk</b>
            </div>
            @foreach($utama as $h)
              <div class="produk-card-awam {{ $h->ranking == 1 ? 'top1' : '' }}">
                <div class="produk-rank-bubble {{ $h->ranking == 1 ? 'top1' : ($h->ranking == 2 ? 'top2' : ($h->ranking == 3 ? 'top3' : '')) }}">
                  #{{ $h->ranking }}
                </div>
                <div class="produk-info-awam">
                  <div class="produk-nama-awam">{{ $h->nama_produk }}</div>
                  <div class="produk-alasan-awam">{{ $generateNarasi($h->detailPerhitungan) }}</div>
                </div>
                <span class="produk-badge-awam badge-awam-utama">Direkomendasikan</span>
              </div>
            @endforeach
          @endif

          {{-- KATEGORI 2: PERLU PERTIMBANGAN --}}
          @if($pertimbang->count() > 0)
            <div class="rec-section-title title-pertim">
              <span>⚖️ Perlu Pertimbangan</span>
              <b>{{ $pertimbang->count() }} produk</b>
            </div>
            @foreach($pertimbang as $h)
              <div class="produk-card-awam">
                <div class="produk-rank-bubble">#{{ $h->ranking }}</div>
                <div class="produk-info-awam">
                  <div class="produk-nama-awam">{{ $h->nama_produk }}</div>
                  <div class="produk-alasan-awam">{{ $generateNarasi($h->detailPerhitungan) }}</div>
                </div>
                <span class="produk-badge-awam badge-awam-pertim">Pertimbangkan</span>
              </div>
            @endforeach
          @endif

          {{-- KATEGORI 3: SEBAIKNYA DITUNDA --}}
          @if($tunda->count() > 0)
            <div class="rec-section-title title-tunda" onclick="toggleTunda()" style="cursor:pointer">
              <span>⏳ Sebaiknya Ditunda</span>
              <b>{{ $tunda->count() }} produk</b>
              <em id="tunda-toggle-label">Klik untuk lihat ▾</em>
            </div>
            <div id="tunda-list" style="display:none">
              @foreach($tunda as $h)
                <div class="produk-card-awam">
                  <div class="produk-rank-bubble">#{{ $h->ranking }}</div>
                  <div class="produk-info-awam">
                    <div class="produk-nama-awam">{{ $h->nama_produk }}</div>
                    <div class="produk-alasan-awam">{{ $generateNarasi($h->detailPerhitungan) }}</div>
                  </div>
                  <span class="produk-badge-awam badge-awam-tunda">Ditunda</span>
                </div>
              @endforeach
            </div>
          @endif

          @if($utama->count() === 0 && $pertimbang->count() === 0 && $tunda->count() === 0)
            <div class="empty-state-awam">Belum ada hasil perhitungan untuk ditampilkan.</div>
          @endif
        </div>
      </section>
    </div>

    {{-- ============================================================ --}}
    {{-- ========== DETAIL PERHITUNGAN MOORA ======================== --}}
    {{-- ============================================================ --}}
    <div class="card mode-detail-only">
      <div class="card-hd">
        <div>
          <div class="card-title">Daftar peringkat Produk</div>
          <div class="card-sub">Klik "Detail" untuk melihat rincian nilai tiap kriteria</div>
        </div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:50px">Rank</th>
              <th>Nama Produk</th>
              <th style="text-align:right">Total Nilai Benefit</th>
              <th style="text-align:right">Total Nilai Cost</th>
              <th style="text-align:right">Nilai Akhir (Yi)</th>
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
                {{ number_format($h->total_benefit, 6) }}
              </td>
              <td style="text-align:right;font-family:'DM Mono',monospace;color:var(--red);font-size:12px">
                {{ number_format($h->total_cost, 6) }}
              </td>
              <td style="text-align:right;font-family:'DM Mono',monospace;font-weight:700;color:var(--pink)">
                {{ number_format($h->nilai_yi, 4) }}
              </td>
              <td>
                @if($h->prioritas == 'Utama')
                  <span class="badge badge-green">⭐ Prioritas Utama</span>
                @elseif($h->prioritas == 'Pertimbangkan')
                  <span class="badge badge-amber">~ Perlu dipertimbangkan</span>
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
                    Rincian nilai kriteria — {{ $h->nama_produk }}
                  </div>
                  <table class="detail-table">
                    <thead>
                      <tr>
                        <th>Kriteria</th>
                        <th>Tipe</th>
                        <th style="text-align:right">Nilai Asli</th>
                        <th style="text-align:right">Nilai Normalisasi</th>
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
                            <span style="color:var(--green);font-weight:700;font-family:inherit">↑ Benefit</span>
                          @else
                            <span style="color:var(--red);font-weight:700;font-family:inherit">↓ Cost</span>
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
    <div class="card mode-detail-only">
      <div class="card-hd">
        <div>
          <div class="card-title">Ringkasan Bobot Kriteria</div>
          <div class="card-sub">Bobot yang digunakan saat perhitungan ini dijalankan</div>
        </div>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        @foreach($kriterias as $k)
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:10px 14px;min-width:140px">
          <div style="font-size:11px;color:var(--text-3);margin-bottom:2px">{{ $k['nama'] ?? '-' }}</div>
          <div style="font-size:18px;font-weight:800;color:var(--pink);font-family:'DM Mono',monospace">{{ $k['bobot'] ?? 0 }}%</div>
          <div style="font-size:10px;margin-top:2px">
            @php $tipe = strtolower($k['tipe_atribut'] ?? $k['tipe'] ?? ''); @endphp
            @if($tipe === 'benefit')
              <span style="color:var(--green);font-weight:700">↑ Benefit</span>
            @elseif($tipe === 'cost')
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
// ============================================================
// Navigasi hasil ke detail
// ============================================================
function switchMode(mode) {
  document.body.setAttribute('data-mode', mode);
  document.querySelectorAll('.mode-btn').forEach(function(btn) {
    btn.classList.toggle('active', btn.dataset.mode === mode);
  });
  try { localStorage.setItem('spk_hasil_mode', mode); } catch(e) {}
}

//restore saat halaman dibuka
(function restoreMode() {
  var mode = 'awam';
  try { mode = localStorage.getItem('spk_hasil_mode') || 'awam'; } catch(e) {}
  if (mode !== 'awam' && mode !== 'detail') mode = 'awam'; // sanitize
  document.body.setAttribute('data-mode', mode);
  document.querySelectorAll('.mode-btn').forEach(function(btn) {
    btn.classList.toggle('active', btn.dataset.mode === mode);
  });
})();

// ============================================================
// TOGGLE LIST PRODUK "DITUNDA" — collapse/expand di mode awam
// ============================================================
function toggleTunda() {
  var list  = document.getElementById('tunda-list');
  var label = document.getElementById('tunda-toggle-label');
  if (!list) return;
  var isOpen = list.style.display === 'block';
  list.style.display = isOpen ? 'none' : 'block';
  if (label) label.textContent = isOpen ? 'Klik untuk lihat ▾' : 'Sembunyikan ▴';
}

// ============================================================
// TOGGLE DETAIL ROW — expand baris detail di tabel mode detail
// ============================================================
function toggleDetail(id) {
  var row = document.getElementById('detail-' + id);
  if (row) row.classList.toggle('open');
}
</script>
</body>
</html>