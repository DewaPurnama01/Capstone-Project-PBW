<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Cafe CNS — {{ now()->format('d M Y') }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',Arial,sans-serif; }
        body { color:#1a1a1a; font-size:12px; background:#fff; padding:20px; }

        .header { text-align:center; margin-bottom:24px; padding-bottom:16px; border-bottom:3px solid #306D29; }
        .header h1 { font-size:22px; color:#0D530E; font-weight:700; }
        .header p  { color:#555; font-size:12px; margin-top:4px; }
        .header .periode { display:inline-block; margin-top:8px; background:#e8f5e8; color:#306D29; padding:4px 14px; border-radius:20px; font-size:11px; font-weight:600; }

        .section { margin-bottom:24px; }
        .section-title { font-size:13px; font-weight:700; color:#0D530E; margin-bottom:10px; padding-bottom:6px; border-bottom:1px solid #e0dbd0; }

        .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px; }
        .kpi-box { border:1px solid #e0dbd0; border-radius:8px; padding:12px; text-align:center; }
        .kpi-box .label { font-size:10px; color:#888; margin-bottom:4px; font-weight:600; text-transform:uppercase; }
        .kpi-box .value { font-size:16px; font-weight:700; color:#0D530E; }

        table { width:100%; border-collapse:collapse; font-size:11px; }
        thead tr { background:#f0f9f0; }
        th { text-align:left; padding:8px 10px; font-weight:700; color:#306D29; border-bottom:2px solid #a8d4a0; text-transform:uppercase; font-size:10px; }
        td { padding:7px 10px; border-bottom:1px solid #f0ede0; color:#333; }
        tr:last-child td { border-bottom:none; }

        .badge-lunas  { background:#d4e8d0; color:#306D29; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600; }
        .badge-belum  { background:#fdecea; color:#d4183d; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:600; }

        .debt-summary { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .debt-box { border:1px solid #e0dbd0; border-radius:8px; padding:12px; }
        .debt-box .label { font-size:10px; color:#888; margin-bottom:4px; }
        .debt-box .value { font-size:15px; font-weight:700; }

        .footer { margin-top:24px; padding-top:12px; border-top:1px solid #e0dbd0; text-align:center; font-size:10px; color:#888; }

        @media print {
            body { padding:10px; }
            .no-print { display:none !important; }
            @page { margin:1cm; size:A4; }
        }
    </style>
</head>
<body>

{{-- Print Button (hidden saat print) --}}
<div class="no-print" style="text-align:right;margin-bottom:16px;">
    <button onclick="window.print()"
        style="background:#306D29;color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;">
        🖨️ Cetak / Simpan PDF
    </button>
    <a href="/laporan" style="margin-left:8px;color:#5a6b57;text-decoration:none;font-size:13px;">← Kembali</a>
</div>

{{-- Header --}}
<div class="header">
    <h1>☕ Cafe CNS — Catch New Serenity</h1>
    <p>Laporan Kinerja Operasional</p>
    <span class="periode">
        Periode: {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} — {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}
    </span>
    <p style="font-size:10px;color:#888;margin-top:6px;">Dicetak: {{ now()->format('d M Y, H:i') }}</p>
</div>

{{-- KPI --}}
<div class="section">
    <div class="section-title">Ringkasan Kinerja</div>
    <div class="kpi-grid">
        <div class="kpi-box">
            <div class="label">Total Pendapatan</div>
            <div class="value" style="font-size:13px;">Rp {{ number_format($ringkasan['total_pendapatan'],0,',','.') }}</div>
        </div>
        <div class="kpi-box">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ number_format($ringkasan['total_transaksi']) }}</div>
        </div>
        <div class="kpi-box">
            <div class="label">Rata-rata / Hari</div>
            <div class="value" style="font-size:13px;">Rp {{ number_format($ringkasan['rata2_per_hari'],0,',','.') }}</div>
        </div>
        <div class="kpi-box">
            <div class="label">Hutang Belum Bayar</div>
            <div class="value" style="color:#d4183d;font-size:13px;">Rp {{ number_format($debtReport['belum_bayar'],0,',','.') }}</div>
        </div>
    </div>
</div>

{{-- Top Menu --}}
@if(count($topProducts))
<div class="section">
    <div class="section-title">Menu Terlaris</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Menu</th>
                <th style="text-align:right;">Terjual</th>
                <th style="text-align:right;">Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $i => $p)
            <tr>
                <td style="color:#888;">{{ $i+1 }}</td>
                <td style="font-weight:600;">{{ $p->nama_menu }}</td>
                <td style="text-align:right;">{{ number_format($p->total_terjual) }}×</td>
                <td style="text-align:right;font-weight:600;color:#0D530E;">Rp {{ number_format($p->total_revenue,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Metode Pembayaran --}}
@if(count($metodeBayar))
<div class="section">
    <div class="section-title">Metode Pembayaran</div>
    <table>
        <thead>
            <tr>
                <th>Metode</th>
                <th style="text-align:right;">Jumlah Transaksi</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($metodeBayar as $m)
            <tr>
                <td style="font-weight:600;">{{ $m->metode_bayar }}</td>
                <td style="text-align:right;">{{ number_format($m->jumlah) }}</td>
                <td style="text-align:right;color:#0D530E;font-weight:600;">Rp {{ number_format($m->total,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Laporan Hutang --}}
<div class="section">
    <div class="section-title">Rekonsiliasi Hutang ke Mitra</div>
    <div class="debt-summary" style="margin-bottom:12px;">
        <div class="debt-box">
            <div class="label">Belum Dibayar</div>
            <div class="value" style="color:#d4183d;">Rp {{ number_format($debtReport['belum_bayar'],0,',','.') }}</div>
        </div>
        <div class="debt-box">
            <div class="label">Sudah Dibayar</div>
            <div class="value" style="color:#0D530E;">Rp {{ number_format($debtReport['sudah_bayar'],0,',','.') }}</div>
        </div>
    </div>
    @if(count($laporanHutang))
    <table>
        <thead>
            <tr>
                <th>Mitra</th>
                <th style="text-align:right;">Tagihan</th>
                <th>Jatuh Tempo</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanHutang as $h)
            <tr>
                <td style="font-weight:600;">{{ $h->nama_mitra }}</td>
                <td style="text-align:right;font-weight:600;">Rp {{ number_format($h->jumlah_tagihan,0,',','.') }}</td>
                <td>{{ \Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->format('d M Y') }}</td>
                <td>
                    @if($h->status_bayar === 'SUDAH_BAYAR')
                        <span class="badge-lunas">✓ Lunas</span>
                    @else
                        <span class="badge-belum">Belum Bayar</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

{{-- Performa Mitra --}}
@if(count($supplierPerformance))
<div class="section">
    <div class="section-title">Performa Mitra Pemasok</div>
    <table>
        <thead>
            <tr>
                <th>Mitra</th>
                <th style="text-align:right;">Total PO</th>
                <th style="text-align:right;">Nilai Total</th>
                <th style="text-align:right;">Lolos QC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($supplierPerformance as $s)
            @php $totalPo = (int)($s->total_po ?? 0); $lolosQc = (int)($s->lolos_qc ?? 0); @endphp
            <tr>
                <td style="font-weight:600;">{{ $s->nama_mitra }}</td>
                <td style="text-align:right;">{{ $totalPo }}</td>
                <td style="text-align:right;color:#0D530E;">Rp {{ number_format($s->total_nilai ?? 0,0,',','.') }}</td>
                <td style="text-align:right;">{{ $lolosQc }}/{{ $totalPo }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="footer">
    Laporan ini digenerate otomatis oleh Sistem Informasi Manajemen Cafe CNS • {{ now()->format('d M Y H:i') }}
</div>

<script>
// Auto print jika diminta dari server
@if(request('autoprint'))
window.onload = () => setTimeout(() => window.print(), 500);
@endif
</script>
</body>
</html>
