<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Preview Import Excel — DRW Skincare SPK</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --pink: #e8005a; --pink-light: #fff0f5; --pink-mid: #ff4d8d;
  --pink-dark: #b3004a; --pink-soft: #ffd6e8;
  --green: #10b981; --green-light: #ecfdf5; --green-dark: #065f46;
  --amber: #f59e0b; --amber-light: #fef3c7; --amber-dark: #92400e;
  --red: #ef4444; --red-light: #fef2f2;
  --blue: #3b82f6; --blue-light: #eff6ff;
  --bg: #fff5f8; --surface: #ffffff;
  --border: #fce7ef; --border-strong: #f9a8c9;
  --text: #1a0a0f; --text-2: #5a3347; --text-3: #b07090;
  --radius: 10px; --radius-lg: 14px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; padding: 32px 16px; font-size: 14px; }
.container { max-width: 820px; margin: 0 auto; }

/* Header */
.page-header { margin-bottom: 28px; }
.breadcrumb { font-size: 12px; color: var(--text-3); margin-bottom: 8px; }
.breadcrumb a { color: var(--pink); text-decoration: none; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -.4px; }
.page-header p  { font-size: 13px; color: var(--text-2); margin-top: 4px; }

/* Card */
.card { background: var(--surface); border-radius: var(--radius-lg); border: 1px solid var(--border); padding: 20px; margin-bottom: 16px; }
.card-title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.card-title .dot { width: 8px; height: 8px; border-radius: 50%; }
.dot-green { background: var(--green); }
.dot-red   { background: var(--red); }
.dot-blue  { background: var(--blue); }

/* Kolom status */
.kolom-list { display: flex; flex-direction: column; gap: 6px; }
.kolom-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 8px; font-size: 12px; }
.kolom-ok   { background: var(--green-light); border: 1px solid #6ee7b7; }
.kolom-miss { background: var(--red-light);   border: 1px solid #fca5a5; }
.kolom-ok   .icon { color: var(--green); font-weight: 700; }
.kolom-miss .icon { color: var(--red);   font-weight: 700; }
.kolom-name   { font-weight: 600; color: var(--text); }
.kolom-kriteria { color: var(--text-2); font-size: 11px; margin-left: auto; }

/* Warning box */
.warn-box { background: var(--amber-light); border: 1px solid #fcd34d; border-radius: 9px; padding: 12px 14px; font-size: 12px; color: var(--amber-dark); margin-bottom: 16px; display: flex; gap: 8px; align-items: flex-start; }
.warn-box b { display: block; font-weight: 700; margin-bottom: 2px; }

/* Info box */
.info-box { background: var(--blue-light); border: 1px solid #bfdbfe; border-radius: 9px; padding: 10px 14px; font-size: 12px; color: #1e40af; margin-bottom: 16px; }

/* Stat strip */
.stat-strip { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.stat { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; padding: 12px 16px; flex: 1; min-width: 120px; }
.stat-val { font-size: 24px; font-weight: 800; color: var(--text); line-height: 1; }
.stat-lbl { font-size: 11px; color: var(--text-3); margin-top: 4px; }

/* Preview table */
.table-wrap { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
table { width: 100%; border-collapse: collapse; font-size: 12px; }
thead { background: var(--pink-light); }
th { padding: 9px 12px; text-align: left; font-weight: 700; color: var(--pink-dark); font-size: 10px; text-transform: uppercase; letter-spacing: .05em; border-bottom: 1px solid var(--border); white-space: nowrap; }
td { padding: 9px 12px; border-bottom: 1px solid var(--border); }
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--pink-light); }
.num { font-family: monospace; color: var(--text-2); }

/* Actions */
.actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px; flex-wrap: wrap; }
.btn { padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; border: 1px solid var(--border-strong); background: var(--surface); color: var(--text-2); cursor: pointer; font-family: inherit; transition: all .15s; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.btn:hover { background: var(--bg); }
.btn-pink { background: var(--pink); color: #fff; border-color: var(--pink); }
.btn-pink:hover { background: var(--pink-dark); border-color: var(--pink-dark); }
.btn-red { background: var(--red-light); color: var(--red); border-color: #fca5a5; }
.btn-red:hover { background: #fee2e2; }
.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; }
.badge-green { background: var(--green-light); color: var(--green-dark); }
.badge-red   { background: var(--red-light); color: var(--red); }
.badge-amber { background: var(--amber-light); color: var(--amber-dark); }
</style>
</head>
<body>
<div class="container">

  <div class="page-header">
    <div class="breadcrumb">
      <a href="{{ route('produk.index') }}">← Data Produk</a> / Preview Import
    </div>
    <h1>Preview Data Import </h1>
    <p>Periksa data sebelum disimpan ke sistem.</p>
  </div>

  {{-- Statistik ringkas --}}
  <div class="stat-strip">
    <div class="stat">
      <div class="stat-val">{{ $totalData }}</div>
      <div class="stat-lbl">Total produk</div>
    </div>
    <div class="stat">
      <div class="stat-val" style="color:var(--green)">{{ count($kolomDitemukan) }}</div>
      <div class="stat-lbl">Jumlah kriteria terdeteksi</div>
    </div>
    @if(count($kolomTidakAda) > 0)
    <div class="stat">
      <div class="stat-val" style="color:var(--red)">{{ count($kolomTidakAda) }}</div>
      <div class="stat-lbl">Kolom tidak ditemukan</div>
    </div>
    @endif
  </div>

  {{-- Peringatan jika ada kolom tidak ditemukan --}}
  @if(count($kolomTidakAda) > 0)
  <div class="warn-box">
    <span style="font-size:16px">⚠️</span>
    <div>
      <b>{{ count($kolomTidakAda) }} kolom kriteria tidak ditemukan di file Excel.</b>
      Produk yang diimport tidak akan memiliki nilai untuk kriteria tersebut dan statusnya akan <b>Belum Lengkap</b> — tidak bisa masuk perhitungan MOORA sampai nilai dilengkapi manual.
      Jika ini bukan yang dimaksud, batalkan dan periksa nama kolom di file Excel Anda.
    </div>
  </div>
  @else
  <div class="info-box">
    ✓ Semua kolom kriteria Excel berhasil ditemukan di file. Data siap diimport.
  </div>
  @endif

  {{-- Status kolom --}}
  <div class="card">
    <div class="card-title">
      <span class="dot dot-green"></span> Kolom yang dikenali
    </div>
    <div class="kolom-list">
      <div class="kolom-item kolom-ok">
        <span class="icon">✓</span>
        <span class="kolom-name">NAMA BARANG</span>
        <span class="kolom-kriteria">— Nama produk</span>
      </div>
      @foreach($kolomDitemukan as $k)
      <div class="kolom-item kolom-ok">
        <span class="icon">✓</span>
        <span class="kolom-name">{{ $k['nama_kolom_excel'] }}</span>
        <span class="kolom-kriteria">— Kriteria: {{ $k['nama_kriteria'] }}</span>
      </div>
      @endforeach
    </div>

    @if(count($kolomTidakAda) > 0)
    <div class="card-title" style="margin-top:16px">
      <span class="dot dot-red"></span> Kolom Tidak Ditemukan di File
    </div>
    <div class="kolom-list">
      @foreach($kolomTidakAda as $k)
      <div class="kolom-item kolom-miss">
        <span class="icon">✗</span>
        <span class="kolom-name">{{ $k['nama_kolom_excel'] }}</span>
        <span class="kolom-kriteria">— Kriteria: {{ $k['nama_kriteria'] }}</span>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- Preview data --}}
  <div class="card">
    <div class="card-title">
      <span class="dot dot-blue"></span>
      Preview 5 Baris Pertama
      <span class="badge badge-amber" style="margin-left:auto;font-size:11px">{{ $totalData }} total baris</span>
    </div>

    @if(count($previewRows) > 0)
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>No</th>
            <th>Nama Produk</th>
            @foreach($kolomDitemukan as $k)
              <th>{{ $k['nama_kriteria'] }}</th>
            @endforeach
          </tr>
        </thead>
        <tbody>
          @foreach($previewRows as $i => $row)
          <tr>
            <td style="color:var(--text-3)">{{ $i + 1 }}</td>
            <td style="font-weight:600">{{ $row['nama_produk'] }}</td>
            @foreach($kolomDitemukan as $k)
              <td class="num">{{ number_format($row['nilai'][$k['nama_kriteria']] ?? 0, 0, ',', '.') }}</td>
            @endforeach
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @if($totalData > 5)
      <div style="font-size:11px;color:var(--text-3);margin-top:8px;text-align:center">
        ... dan {{ $totalData - 5 }} baris lainnya
      </div>
    @endif
    @else
    <p style="font-size:13px;color:var(--text-3);text-align:center;padding:20px">Tidak ada data untuk ditampilkan.</p>
    @endif
  </div>

  {{-- Tombol aksi --}}
  <div class="actions">
    {{-- Batal --}}
    <form method="POST" action="{{ route('produk.preview.cancel') }}">
      @csrf
      <button type="submit" class="btn btn-red"> Batal & hapus file</button>
    </form>

    {{-- Lanjutkan import --}}
    <form method="POST" action="{{ route('produk.import.confirm') }}">
      @csrf
      <button type="submit" class="btn btn-pink">
        Simpan {{ $totalData }} Produk
      </button>
    </form>
  </div>

</div>
</body>
</html>
