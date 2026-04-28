<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Permintaan — DRW Skincare SPK</title>
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

.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { height: 56px; background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; gap: 12px; position: sticky; top: 0; z-index: 10; }
.topbar-title { font-size: 15px; font-weight: 800; color: var(--text); flex: 1; }
.content { flex: 1; padding: 28px; overflow-y: auto; }
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
.btn-sm { padding: 6px 12px; font-size: 11px; }
.btn-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.info-box { display: flex; align-items: flex-start; gap: 8px; padding: 10px 12px; border-radius: 9px; font-size: 12px; margin-bottom: 14px; }
.info-box svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 1.8; flex-shrink: 0; margin-top: 1px; }
.info-pink { background: var(--pink-light); color: var(--pink-dark); border: 1px solid var(--pink-soft); }

.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
.badge-green { background: var(--green-light); color: #065f46; }
.badge-amber { background: var(--amber-light); color: #92400e; }

.form-input, .form-select { width: 100%; padding: 8px 12px; border: 1px solid var(--border-strong); border-radius: 8px; font-size: 13px; font-family: inherit; color: var(--text); background: var(--surface); outline: none; transition: border .15s; }
.form-input:focus, .form-select:focus { border-color: var(--pink); box-shadow: 0 0 0 3px rgba(232,0,90,.08); }

.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--pink-light); }
th { padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: var(--pink-dark); text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); white-space: nowrap; }
td { padding: 11px 14px; font-size: 13px; border-bottom: 1px solid var(--border); vertical-align: middle; }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--pink-light); }

.rating-cell { display: flex; gap: 4px; justify-content: center; }
.rating-btn-sm { width: 30px; height: 30px; border: 2px solid var(--border); border-radius: 6px; background: var(--surface); font-size: 11px; font-weight: 700; color: var(--text-2); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; }
.rating-btn-sm:hover { border-color: var(--pink-soft); background: var(--pink-light); }
.rating-btn-sm.selected { border-color: var(--pink); background: var(--pink); color: #fff; }

.empty-state { text-align: center; padding: 48px 20px; color: var(--text-3); }
.empty-state svg { width: 44px; height: 44px; stroke: var(--border-strong); fill: none; stroke-width: 1.5; margin: 0 auto 12px; display: block; }
.empty-state p { font-size: 13px; line-height: 1.6; }

.alert { padding: 10px 14px; border-radius: 9px; font-size: 12px; font-weight: 600; margin-bottom: 16px; }
.alert-success { background: var(--green-light); color: #065f46; border: 1px solid #6ee7b7; }

.warn-banner { background: var(--amber-light); border: 1px solid #fcd34d; border-radius: var(--radius-lg); padding: 16px 18px; margin-bottom: 20px; font-size: 13px; color: #92400e; line-height: 1.6; }
.warn-banner b { font-weight: 700; }
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo">
      <div class="sb-logo-icon">
        <svg viewBox="0 0 18 18"><path d="M9 2C5.1 2 2 5.1 2 9s3.1 7 7 7 7-3.1 7-7-3.1-7-7-7" stroke-linecap="round"/><path d="M6 9l2.5 2.5L13 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div>
        <div class="sb-logo-name">SPK Promosi</div>
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
      <a href="/input-permintaan" class="nav-item active">
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
    <div class="topbar-title">Input Permintaan</div>
  </div>

  <div class="content">
    <div class="page-header">
      <h1>Input Permintaan</h1>
      <p>Isi nilai kriteria manual (skala 1-5) untuk semua produk sekaligus.</p>
    </div>

    @if(session('success'))
      <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif

    @if($kriterias->count() === 0)
      <div class="warn-banner">
        <b>Tidak ada kriteria input manual.</b> Halaman ini tidak aktif karena semua kriteria menggunakan sumber data Excel. Tambahkan kriteria dengan sumber "Input Manual" di menu Kelola Kriteria.
      </div>
    @elseif($produks->count() === 0)
      <div class="warn-banner">
        <b>Belum ada produk.</b> Tambahkan produk terlebih dahulu di halaman Data Produk.
      </div>
    @else
      <div class="card">
        <div class="card-hd">
          <div>
            <div class="card-title">Input Nilai Kriteria Manual</div>
            <div class="card-sub">Isi nilai untuk setiap produk ({{ $produks->count() }} produk)</div>
          </div>
          <div class="btn-group">
            <select class="form-select" style="width:150px;padding:6px 10px" id="filter-status">
              <option value="all">Semua produk</option>
              <option value="empty">Belum diisi</option>
              <option value="filled">Sudah diisi</option>
            </select>
            <button type="button" class="btn btn-pink btn-sm" onclick="saveAllInputs()">
              <svg viewBox="0 0 16 16" width="13" height="13" stroke="currentColor" fill="none" stroke-width="2"><path d="M3 8l4 4 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
              Simpan Semua
            </button>
          </div>
        </div>

        <div class="info-box info-pink">
          <svg viewBox="0 0 16 16"><circle cx="8" cy="8" r="6"/><path d="M8 7v5M8 5.5v.01" stroke-linecap="round"/></svg>
          <div>
            <b>Skala penilaian 1-5:</b><br>
            1 = Sangat Kurang, 2 = Kurang, 3 = Cukup, 4 = Baik, 5 = Sangat Baik
          </div>
        </div>

        <form id="form-input-all">
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th style="width:40px">No</th>
                  <th style="min-width:200px">Nama Produk</th>
                  @foreach($kriterias as $k)
                    <th style="text-align:center;min-width:160px">{{ $k->nama_kriteria }}</th>
                  @endforeach
                  <th style="text-align:center;width:80px">Status</th>
                </tr>
              </thead>
              <tbody id="tbody-input">
                @foreach($produks as $i => $produk)
                  @php
                    $produkInputs = $inputs->get($produk->id_produk);
                    $filled = $produkInputs ? $produkInputs->count() : 0;
                    $total = $kriterias->count();
                  @endphp
                  <tr data-status="{{ $filled == $total ? 'filled' : 'empty' }}">
                    <td style="color:var(--text-3);font-size:12px">{{ $i + 1 }}</td>
                    <td style="font-weight:600">{{ $produk->nama_produk }}</td>
                    @foreach($kriterias as $k)
                      @php
                        $existing = $produkInputs ? $produkInputs->firstWhere('id_kriteria', $k->id_kriteria) : null;
                        $currentValue = $existing ? $existing->nilai_input : null;
                      @endphp
                      <td>
                        <div class="rating-cell">
                          @for($v = 1; $v <= 5; $v++)
                            <button type="button" 
                                    class="rating-btn-sm {{ $currentValue == $v ? 'selected' : '' }}" 
                                    data-produk="{{ $produk->id_produk }}" 
                                    data-kriteria="{{ $k->id_kriteria }}" 
                                    data-value="{{ $v }}"
                                    onclick="selectValue(this)">
                              {{ $v }}
                            </button>
                          @endfor
                        </div>
                      </td>
                    @endforeach
                    <td style="text-align:center">
                      @if($filled == $total)
                        <span class="badge badge-green">✓ Lengkap</span>
                      @else
                        <span class="badge badge-amber">{{ $filled }}/{{ $total }}</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </form>
      </div>
    @endif
  </div>
</div>

<script>
const selectedValues = {};

document.querySelectorAll('.rating-btn-sm.selected').forEach(btn => {
  const produk = btn.dataset.produk;
  const kriteria = btn.dataset.kriteria;
  const value = btn.dataset.value;
  if (!selectedValues[produk]) selectedValues[produk] = {};
  selectedValues[produk][kriteria] = value;
});

function selectValue(btn) {
  const produk = btn.dataset.produk;
  const kriteria = btn.dataset.kriteria;
  const value = btn.dataset.value;
  
  const row = btn.closest('td');
  row.querySelectorAll('.rating-btn-sm').forEach(b => b.classList.remove('selected'));
  
  btn.classList.add('selected');
  
  if (!selectedValues[produk]) selectedValues[produk] = {};
  selectedValues[produk][kriteria] = value;
  
  updateStatusBadge(btn.closest('tr'));
}

function updateStatusBadge(row) {
  const produk = row.querySelector('[data-produk]').dataset.produk;
  const totalKriteria = {{ $kriterias->count() }};
  const filled = selectedValues[produk] ? Object.keys(selectedValues[produk]).length : 0;
  
  const badge = row.querySelector('.badge');
  if (filled === totalKriteria) {
    badge.className = 'badge badge-green';
    badge.textContent = '✓ Lengkap';
    row.dataset.status = 'filled';
  } else {
    badge.className = 'badge badge-amber';
    badge.textContent = filled + '/' + totalKriteria;
    row.dataset.status = 'empty';
  }
}

function saveAllInputs() {
  if (Object.keys(selectedValues).length === 0) {
    alert('Belum ada nilai yang diinput.');
    return;
  }
  
  const formData = new FormData();
  formData.append('_token', '{{ csrf_token() }}');
  formData.append('data', JSON.stringify(selectedValues));
  
  fetch('{{ route("input.store") }}', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Semua data berhasil disimpan!');
      location.reload();
    } else {
      alert('Gagal menyimpan: ' + data.message);
    }
  })
  .catch(err => {
    alert('Error: ' + err.message);
  });
}

document.getElementById('filter-status').addEventListener('change', function() {
  const value = this.value;
  const rows = document.querySelectorAll('#tbody-input tr');
  rows.forEach(row => {
    if (value === 'all') {
      row.style.display = '';
    } else {
      row.style.display = row.dataset.status === value ? '' : 'none';
    }
  });
});
</script>
</body>
</html> 