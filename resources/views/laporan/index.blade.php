@extends('layouts.app')
@section('title','Laporan & Analitik')
@section('content')
<div style="padding:24px;max-width:1400px;margin:0 auto;">

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0D530E;">Laporan & Analitik</h1>
        <p style="color:#5a6b57;font-size:14px;margin-top:2px;">Ringkasan kinerja operasional Cafe CNS</p>
    </div>
    <form method="GET" style="display:flex;gap:10px;align-items:center;">
        <select name="periode" class="form-input" style="width:160px;" onchange="this.form.submit()">
            <option value="hari_ini"   {{ $periode==='hari_ini'   ? 'selected' : '' }}>Hari Ini</option>
            <option value="minggu_ini" {{ $periode==='minggu_ini' ? 'selected' : '' }}>Minggu Ini</option>
            <option value="bulan_ini"  {{ $periode==='bulan_ini'  || !in_array($periode,['hari_ini','minggu_ini','bulan_lalu','kustom']) ? 'selected' : '' }}>Bulan Ini</option>
            <option value="bulan_lalu" {{ $periode==='bulan_lalu' ? 'selected' : '' }}>Bulan Lalu</option>
        </select>
        <a href="{{ route('laporan.export-pdf') }}" class="btn-secondary" style="text-decoration:none;white-space:nowrap;">
            ⬇ Export PDF
        </a>
    </form>
</div>

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
    @php
    $kpis = [
        ['label'=>'Total Pendapatan', 'val'=>'Rp '.number_format($revenue->sum('total'),0,',','.'), 'icon'=>'💰', 'color'=>'#0D530E'],
        ['label'=>'Total Transaksi',  'val'=>number_format($revenue->sum('count')),                 'icon'=>'🧾', 'color'=>'#306D29'],
        ['label'=>'Pelanggan Baru',   'val'=>number_format($customerGrowth->sum('count')),          'icon'=>'👥', 'color'=>'#1a6da6'],
        ['label'=>'Rata-rata / Hari', 'val'=>'Rp '.number_format($revenue->count() ? $revenue->sum('total')/$revenue->count() : 0,0,',','.'), 'icon'=>'📈', 'color'=>'#b8860b'],
    ];
    @endphp
    @foreach($kpis as $k)
    <div class="card" style="text-align:center;padding:20px;">
        <div style="font-size:28px;margin-bottom:6px;">{{ $k['icon'] }}</div>
        <div style="font-size:20px;font-weight:700;color:{{ $k['color'] }};">{{ $k['val'] }}</div>
        <div style="font-size:12px;color:#5a6b57;margin-top:4px;">{{ $k['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- Charts Row --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;align-items:start;">
    <div class="card" style="padding:20px;">
        <div style="font-size:15px;font-weight:700;color:#0D530E;margin-bottom:16px;">📊 Grafik Pendapatan Harian</div>
        <canvas id="revenueChart" height="100"></canvas>
    </div>
    <div class="card" style="padding:20px;">
        <div style="font-size:15px;font-weight:700;color:#0D530E;margin-bottom:16px;">💳 Metode Pembayaran</div>
        <canvas id="paymentChart" height="180"></canvas>
        <div id="paymentLegend" style="margin-top:12px;"></div>
    </div>
</div>

{{-- Products & Debt --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;align-items:start;">
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;font-size:15px;font-weight:700;color:#0D530E;">🏆 Top Menu</div>
        <table style="width:100%;border-collapse:collapse;">
            @foreach($topProducts as $i => $p)
            <tr style="{{ $i<count($topProducts)-1?'border-bottom:1px solid #f5f5f5;':'' }}">
                <td style="padding:12px 16px;width:30px;font-size:18px;font-weight:800;color:{{ ['#d4a017','#888','#b87333'][$i]??'#ccc' }};">{{ $i+1 }}</td>
                <td style="padding:12px 8px;font-size:14px;font-weight:600;">{{ $p->nama_menu }}</td>
                <td style="padding:12px 16px;text-align:right;font-size:13px;color:#5a6b57;">{{ number_format($p->total_terjual) }}×</td>
                <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:#0D530E;">Rp {{ number_format($p->total_revenue,0,',','.') }}</td>
            </tr>
            @endforeach
            @if(!count($topProducts))
            <tr><td colspan="4" style="padding:32px;text-align:center;color:#888;">Belum ada data penjualan</td></tr>
            @endif
        </table>
    </div>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;font-size:15px;font-weight:700;color:#0D530E;">📋 Laporan Hutang ke Mitra</div>
        <div style="padding:20px;">
            <div style="display:grid;gap:12px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#ffeaea;border-radius:10px;">
                    <div>
                        <div style="font-size:12px;color:#c0392b;font-weight:600;margin-bottom:2px;">Belum Dibayar</div>
                        <div style="font-size:22px;font-weight:700;color:#d4183d;">Rp {{ number_format($debtReport['belum_bayar'],0,',','.') }}</div>
                    </div>
                    <span style="font-size:32px;">💸</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#f0f9f0;border-radius:10px;">
                    <div>
                        <div style="font-size:12px;color:#306D29;font-weight:600;margin-bottom:2px;">Sudah Dibayar</div>
                        <div style="font-size:22px;font-weight:700;color:#0D530E;">Rp {{ number_format($debtReport['sudah_bayar'],0,',','.') }}</div>
                    </div>
                    <span style="font-size:32px;">✅</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#5a6b57;padding:4px 4px;">
                    <span>Total Keseluruhan</span>
                    <span style="font-weight:700;color:#0D530E;">Rp {{ number_format($debtReport['total'],0,',','.') }}</span>
                </div>
            </div>
            <a href="/kemitraan?tab=hutang" style="display:block;text-align:center;background:#306D29;color:#fff;border-radius:10px;padding:10px;font-weight:600;font-size:13px;text-decoration:none;">
                Kelola Hutang →
            </a>
        </div>
    </div>
</div>

{{-- Supplier Performance --}}
@if(count($supplierPerformance))
<div class="card" style="padding:0;overflow:hidden;margin-bottom:20px;">
    <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;font-size:15px;font-weight:700;color:#0D530E;">🌾 Performa Mitra Supplier</div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">
                    <th style="text-align:left;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Mitra</th>
                    <th style="text-align:right;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Total PO</th>
                    <th style="text-align:right;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Nilai Total</th>
                    <th style="text-align:right;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Lolos QC</th>
                    <th style="text-align:center;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Tingkat Lolos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supplierPerformance as $s)
                @php
                    $totalPo   = (int) ($s->total_po ?? 0);
                    $lolosQc   = (int) ($s->lolos_qc ?? 0);
                    $totalNilai = (float) ($s->total_nilai ?? 0);
                    $rate      = $totalPo > 0 ? round($lolosQc / $totalPo * 100) : 0;
                @endphp
                <tr class="table-row">
                    <td style="padding:12px 16px;font-weight:600;font-size:14px;">{{ $s->nama_mitra }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#5a6b57;">{{ $totalPo }}</td>
                    <td style="padding:12px 16px;text-align:right;font-weight:600;color:#0D530E;">Rp {{ number_format($totalNilai,0,',','.') }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#306D29;">{{ $lolosQc }}/{{ $totalPo }}</td>
                    <td style="padding:12px 16px;text-align:center;">
                        <div style="display:flex;align-items:center;gap:8px;justify-content:center;">
                            <div style="flex:1;height:6px;background:#E7E1B1;border-radius:3px;max-width:80px;">
                                <div style="width:{{ $rate }}%;height:100%;background:{{ $rate>=80?'#306D29':($rate>=50?'#b8860b':'#d4183d') }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:12px;font-weight:600;color:{{ $rate>=80?'#306D29':($rate>=50?'#b8860b':'#d4183d') }};">{{ $rate }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Hutang Detail --}}
@if(count($laporanHutang))
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;font-size:15px;font-weight:700;color:#0D530E;">📑 Riwayat Hutang Terbaru</div>
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">
                    <th style="text-align:left;padding:10px 16px;font-size:12px;font-weight:700;color:#5a6b57;">Mitra</th>
                    <th style="text-align:right;padding:10px 16px;font-size:12px;font-weight:700;color:#5a6b57;">Tagihan</th>
                    <th style="text-align:center;padding:10px 16px;font-size:12px;font-weight:700;color:#5a6b57;">Jatuh Tempo</th>
                    <th style="text-align:center;padding:10px 16px;font-size:12px;font-weight:700;color:#5a6b57;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($laporanHutang as $h)
                <tr class="table-row">
                    <td style="padding:10px 16px;font-weight:600;font-size:13px;">{{ $h->nama_mitra }}</td>
                    <td style="padding:10px 16px;text-align:right;font-weight:600;color:#0D530E;font-size:13px;">Rp {{ number_format($h->jumlah_tagihan,0,',','.') }}</td>
                    <td style="padding:10px 16px;text-align:center;font-size:13px;color:#5a6b57;">{{ \Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->format('d M Y') }}</td>
                    <td style="padding:10px 16px;text-align:center;">
                        @if($h->status_bayar === 'SUDAH_BAYAR')
                            <span style="background:#d4e8d0;color:#306D29;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">✓ Lunas</span>
                        @else
                            <span style="background:#ffe4e4;color:#d4183d;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">Belum Bayar</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</div>

<script>
// Revenue Chart
const revenueData = @json($revenue);
new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: revenueData.map(d => {
            const dt = new Date(d.tanggal);
            return dt.toLocaleDateString('id-ID',{day:'numeric',month:'short'});
        }),
        datasets: [{
            label: 'Pendapatan',
            data: revenueData.map(d => d.total || 0),
            backgroundColor: 'rgba(48,109,41,0.7)',
            borderColor: '#0D530E',
            borderWidth: 1,
            borderRadius: 5,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => 'Rp ' + Number(ctx.raw).toLocaleString('id-ID') } }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#5a6b57', font: { size: 11 } } },
            y: { grid: { color: '#E7E1B1' }, ticks: { color: '#5a6b57', font: { size: 11 }, callback: v => 'Rp '+(v/1000000).toFixed(1)+'jt' } }
        }
    }
});

// Payment Chart
const paymentData = @json($metodeBayar);
const payColors = ['#306D29','#8B6914','#1a6da6','#d4183d'];
if (paymentData.length) {
    new Chart(document.getElementById('paymentChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: paymentData.map(p => p.metode_bayar),
            datasets: [{ data: paymentData.map(p => p.jumlah), backgroundColor: payColors, borderWidth: 0 }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' transaksi' } }
            }
        }
    });
    const legend = paymentData.map((p,i) =>
        `<div style="display:flex;align-items:center;gap:6px;margin-bottom:4px;">
            <div style="width:10px;height:10px;border-radius:50%;background:${payColors[i]};flex-shrink:0;"></div>
            <span style="font-size:12px;color:#5a6b57;">${p.metode_bayar} (${p.jumlah}x)</span>
        </div>`
    ).join('');
    document.getElementById('paymentLegend').innerHTML = legend;
}
</script>
@endsection
