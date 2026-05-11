@extends('layouts.app')

@section('title', 'Dashboard Analisis Asosiasi - DRW Skincare')

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endpush

@section('content')

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

<div class="grid2">
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

    <div style="display:flex;flex-direction:column;gap:16px">
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

@endsection

@push('scripts')
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
@endpush