@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="p-6 space-y-6 max-w-screen-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="color:#0D530E;font-size:1.5rem;font-weight:700;">Dashboard CRM</h1>
            <p style="color:#5a6b57;font-size:0.875rem;">Selamat datang, {{ session('user.name') }}! Berikut ringkasan operasional Cafe CNS hari ini.</p>
        </div>
        <div style="background:#E7E1B1;color:#306D29;border-radius:8px;" class="flex items-center gap-2 px-3 py-2 text-sm">
            🕐 {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-4 gap-4">
        @php
            $kpis = [
                ['title'=>'Pendapatan Hari Ini', 'value'=>'Rp '.number_format($pendapatanHariIni,0,',','.'), 'sub'=>'Transaksi selesai hari ini', 'up'=>true, 'bg'=>'#306D29', 'fg'=>'#fff'],
                ['title'=>'Total Pelanggan',     'value'=>number_format($totalPelanggan),                   'sub'=>'Pelanggan aktif terdaftar', 'up'=>true, 'bg'=>'#FBF5DD', 'fg'=>'#306D29'],
                ['title'=>'Transaksi Hari Ini',  'value'=>number_format($transaksiHariIni),                 'sub'=>'Order masuk hari ini',      'up'=>true, 'bg'=>'#FBF5DD', 'fg'=>'#306D29'],
                ['title'=>'Peringatan Stok',     'value'=>count($stokRendah),                               'sub'=>'Item perlu restock segera', 'up'=>false,'bg'=>count($stokRendah)>0?'#fff5f5':'#FBF5DD', 'fg'=>count($stokRendah)>0?'#d4183d':'#306D29', 'link'=>'/inventori'],
            ];
        @endphp

        @foreach($kpis as $kpi)
        <a href="{{ $kpi['link'] ?? '#' }}" style="background:{{ $kpi['bg'] }};border:1px solid rgba(48,109,41,0.1);border-radius:16px;text-decoration:none;" class="p-5 flex flex-col gap-3 shadow-sm block">
            <div class="flex items-start justify-between">
                <p style="color:{{ $kpi['fg']==='#fff'?'rgba(255,255,255,0.8)':'#5a6b57' }};font-size:0.8rem;">{{ $kpi['title'] }}</p>
            </div>
            <p style="color:{{ $kpi['fg'] }};font-size:1.5rem;font-weight:700;">{{ $kpi['value'] }}</p>
            <p style="color:{{ $kpi['fg']==='#fff'?'rgba(255,255,255,0.75)':($kpi['up']?'#5a6b57':'#d4183d') }};font-size:0.75rem;">
                {{ $kpi['up'] ? '↑' : '↓' }} {{ $kpi['sub'] }}
            </p>
        </a>
        @endforeach
    </div>

    {{-- Charts Row --}}
    <div class="grid gap-4" style="grid-template-columns: 2fr 1fr;">
        {{-- Revenue Chart --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 style="color:#0D530E;font-weight:600;">Pendapatan 7 Hari Terakhir</h3>
                <span style="background:#E7E1B1;color:#306D29;border-radius:20px;padding:4px 10px;font-size:0.75rem;">Minggu ini</span>
            </div>
            <canvas id="revenueChart" height="120"></canvas>
        </div>

        {{-- Segmen Pelanggan --}}
        <div class="card p-5">
            <h3 style="color:#0D530E;font-weight:600;" class="mb-4">Segmen Pelanggan</h3>
            <canvas id="segmenChart" height="160"></canvas>
            <div class="grid grid-cols-2 gap-1 mt-3">
                @foreach($segmenPelanggan as $seg)
                    @php $colors = ['VIP'=>'#0D530E','Member'=>'#306D29','Reguler'=>'#E7E1B1','Baru'=>'#8B6914']; @endphp
                    <div class="flex items-center gap-1.5">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $colors[$seg->segmen] ?? '#ccc' }};flex-shrink:0;"></div>
                        <span style="font-size:0.72rem;color:#5a6b57;">{{ $seg->segmen }} ({{ $seg->jumlah }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid gap-4" style="grid-template-columns: 1.5fr 1fr 1fr;">

        {{-- Transaksi Terbaru --}}
        <div class="card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 style="color:#0D530E;font-weight:600;">Transaksi Terbaru</h3>
                <a href="/transaksi" style="background:#E7E1B1;color:#306D29;border-radius:20px;padding:4px 10px;font-size:0.75rem;font-weight:600;text-decoration:none;">Lihat Semua</a>
            </div>
            <div class="space-y-3">
                @forelse($transaksiTerbaru as $trx)
                <div class="flex items-center gap-3 py-2 border-b" style="border-color:#f0ede0;">
                    <div style="width:36px;height:36px;background:#FBF5DD;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">☕</div>
                    <div class="flex-1 min-w-0">
                        <p style="color:#1a2e18;font-weight:600;font-size:0.875rem;" class="truncate">{{ $trx->nama }}</p>
                        <p style="color:#5a6b57;font-size:0.75rem;">{{ $trx->metode_bayar }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p style="color:#0D530E;font-weight:600;font-size:0.875rem;">Rp {{ number_format($trx->total,0,',','.') }}</p>
                        <span style="font-size:0.7rem;padding:2px 6px;border-radius:10px;background:{{ $trx->status==='selesai'?'#d4e8d0':'#fff3cd' }};color:{{ $trx->status==='selesai'?'#306D29':'#8B6914' }};">
                            {{ $trx->status }}
                        </span>
                    </div>
                </div>
                @empty
                    <p style="color:#9ca3af;font-size:0.875rem;text-align:center;padding:20px 0;">Belum ada transaksi hari ini</p>
                @endforelse
            </div>
        </div>

        {{-- Produk Terlaris --}}
        <div class="card p-5">
            <h3 style="color:#0D530E;font-weight:600;" class="mb-4">Produk Terlaris</h3>
            <div class="space-y-3">
                @forelse($produkTerlaris as $i => $produk)
                <div class="flex items-center gap-2">
                    <span style="width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:700;flex-shrink:0;background:{{ $i===0?'#306D29':'#E7E1B1' }};color:{{ $i===0?'#fff':'#306D29' }};">{{ $i+1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p style="color:#1a2e18;font-weight:500;font-size:0.85rem;" class="truncate">{{ $produk->nama_item }}</p>
                        <div style="height:6px;background:#E7E1B1;border-radius:3px;margin-top:4px;">
                            <div style="height:100%;background:#306D29;border-radius:3px;width:{{ min(100, ($produk->total_terjual / max($produkTerlaris->first()->total_terjual,1))*100) }}%;"></div>
                        </div>
                    </div>
                    <span style="font-size:0.75rem;color:#5a6b57;flex-shrink:0;">{{ $produk->total_terjual }}x</span>
                </div>
                @empty
                    <p style="color:#9ca3af;font-size:0.875rem;">Belum ada data</p>
                @endforelse
            </div>
        </div>

        {{-- Stok Menipis / Hutang --}}
        <div class="card p-5" style="border-color:rgba(212,24,61,0.2);">
            <div class="flex items-center gap-2 mb-4">
                <span>⚠️</span>
                <h3 style="color:#d4183d;font-weight:600;">Stok Menipis</h3>
            </div>
            @forelse($stokRendah as $item)
            @php $pct = min(100, ($item->jumlah_stok / max($item->batas_minimum, 1)) * 100); @endphp
            <div class="space-y-1 mb-3">
                <div class="flex justify-between">
                    <p style="color:#1a2e18;font-weight:500;font-size:0.85rem;">{{ $item->nama_bahan }}</p>
                    <span style="color:#d4183d;font-weight:600;font-size:0.8rem;">{{ $item->jumlah_stok }} {{ $item->satuan }}</span>
                </div>
                <div style="height:8px;background:#ffe4e4;border-radius:4px;">
                    <div style="height:100%;background:{{ $pct<40?'#d4183d':'#f59e0b' }};border-radius:4px;width:{{ $pct }}%;"></div>
                </div>
                <p style="color:#9ca3af;font-size:0.72rem;">Min: {{ $item->batas_minimum }} {{ $item->satuan }}</p>
            </div>
            @empty
                <div style="text-align:center;padding:20px 0;">
                    <p style="font-size:1.5rem;">✅</p>
                    <p style="color:#5a6b57;font-size:0.85rem;">Semua stok aman</p>
                </div>
            @endforelse

            @if(count($stokRendah) > 0)
            <a href="/kemitraan" style="display:block;text-align:center;background:#306D29;color:#fff;border-radius:10px;padding:10px;font-weight:600;font-size:0.875rem;margin-top:12px;text-decoration:none;">
                Buat Request ke Petani →
            </a>
            @endif
        </div>
    </div>

    {{-- Hutang jatuh tempo --}}
    @if(count($hutangJatuhTempo) > 0)
    <div class="card p-5" style="border-color:rgba(212,24,61,0.2);">
        <h3 style="color:#d4183d;font-weight:600;" class="mb-3">⏰ Hutang Segera Jatuh Tempo</h3>
        <div class="space-y-2">
            @foreach($hutangJatuhTempo as $hutang)
            <div class="flex items-center justify-between p-3 rounded-xl" style="background:#ffe4e4;">
                <div>
                    <p style="color:#1a2e18;font-weight:600;font-size:0.875rem;">{{ $hutang->nama_mitra }}</p>
                    <p style="color:#d4183d;font-size:0.75rem;">Jatuh tempo: {{ \Carbon\Carbon::parse($hutang->tanggal_jatuh_tempo)->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p style="color:#d4183d;font-weight:700;font-size:1rem;">Rp {{ number_format($hutang->jumlah_tagihan,0,',','.') }}</p>
                    <a href="/kemitraan?tab=hutang" style="font-size:0.75rem;color:#306D29;font-weight:600;text-decoration:none;">Bayar Sekarang →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
// Revenue Chart
const revenueData = @json($pendapatan7Hari);
const labels = revenueData.map(d => {
    const date = new Date(d.tanggal);
    return date.toLocaleDateString('id-ID', {weekday:'short', day:'numeric'});
});
const values = revenueData.map(d => d.total || 0);

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: labels.length ? labels : ['Sen','Sel','Rab','Kam','Jum','Sab','Min'],
        datasets: [{
            label: 'Pendapatan',
            data: values.length ? values : [1250000,980000,1450000,1320000,1680000,2100000,1950000],
            borderColor: '#306D29',
            backgroundColor: 'rgba(48,109,41,0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 2.5,
            pointBackgroundColor: '#306D29',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => 'Rp ' + ctx.raw.toLocaleString('id-ID')
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#5a6b57', font: {size:11} } },
            y: { grid: { color: '#E7E1B1' }, ticks: { color: '#5a6b57', font:{size:11}, callback: (v) => 'Rp '+(v/1000000).toFixed(1)+'jt' } }
        }
    }
});

// Segmen Chart
const segmenData = @json($segmenPelanggan);
const segmenColors = { VIP: '#0D530E', Member: '#306D29', Reguler: '#E7E1B1', Baru: '#8B6914' };
new Chart(document.getElementById('segmenChart'), {
    type: 'doughnut',
    data: {
        labels: segmenData.map(s => s.segmen),
        datasets: [{
            data: segmenData.map(s => s.jumlah),
            backgroundColor: segmenData.map(s => segmenColors[s.segmen] || '#ccc'),
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + ctx.raw + ' pelanggan' } } }
    }
});
</script>
@endpush
