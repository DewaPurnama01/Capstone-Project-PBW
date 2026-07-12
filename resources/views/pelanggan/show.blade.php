@extends('layouts.app')
@section('title','Detail Pelanggan')
@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="{{ route('pelanggan.index') }}" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;">←</a>
    <h1 style="font-size:20px;font-weight:700;color:#0D530E;">Detail Pelanggan</h1>
    <a href="{{ route('pelanggan.edit',$pelanggan->id) }}" class="btn-secondary" style="text-decoration:none;margin-left:auto;">✏️ Edit</a>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">
    {{-- Profile Card --}}
    <div class="card" style="text-align:center;padding:24px;">
        <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#0D530E,#306D29);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:28px;margin:0 auto 12px;">
            {{ strtoupper(substr($pelanggan->nama,0,1)) }}
        </div>
        <div style="font-size:18px;font-weight:700;color:#0D530E;">{{ $pelanggan->nama }}</div>

        @php
            $segColors = ['VIP'=>['#d4a017','#fff8e1'],'Member'=>['#306D29','#f0f9f0'],'Reguler'=>['#0D530E','#e8f5e8'],'Baru'=>['#888','#f5f5f5']];
            [$c, $b] = $segColors[$pelanggan->segmen] ?? ['#888','#f5f5f5'];
        @endphp
        <span style="background:{{$b}};color:{{$c}};border:1px solid {{$c}}44;padding:4px 14px;border-radius:20px;font-size:13px;font-weight:600;display:inline-block;margin-top:8px;">
            {{ $pelanggan->segmen }}
        </span>

        @if($pelanggan->no_hp)
        <div style="margin-top:12px;font-size:13px;color:#5a6b57;">📞 {{ $pelanggan->no_hp }}</div>
        @endif

        <div style="border-top:1px solid #E7E1B1;margin:16px 0;"></div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div style="background:#FBF5DD;border-radius:10px;padding:12px;">
                <div style="font-size:20px;font-weight:700;color:#0D530E;">{{ number_format($pelanggan->poin) }}</div>
                <div style="font-size:11px;color:#5a6b57;margin-top:2px;">Poin</div>
            </div>
            <div style="background:#FBF5DD;border-radius:10px;padding:12px;">
                <div style="font-size:20px;font-weight:700;color:#0D530E;">{{ number_format($pelanggan->total_kunjungan) }}</div>
                <div style="font-size:11px;color:#5a6b57;margin-top:2px;">Kunjungan</div>
            </div>
        </div>

        <div style="margin-top:12px;background:#f0f9f0;border-radius:10px;padding:12px;">
            <div style="font-size:16px;font-weight:700;color:#0D530E;">Rp {{ number_format($pelanggan->total_belanja ?? 0,0,',','.') }}</div>
            <div style="font-size:11px;color:#5a6b57;margin-top:2px;">Total Belanja</div>
        </div>

        <div style="margin-top:12px;font-size:12px;color:#bbb;">
            Bergabung {{ \Carbon\Carbon::parse($pelanggan->tanggal_daftar)->format('d M Y') }}
        </div>
        @if($pelanggan->terakhir_kunjungan)
        <div style="font-size:12px;color:#bbb;">
            Terakhir: {{ \Carbon\Carbon::parse($pelanggan->terakhir_kunjungan)->format('d M Y') }}
        </div>
        @endif

        @if($pelanggan->menu_favorit)
        <div style="margin-top:12px;padding:10px;background:#FBF5DD;border-radius:8px;font-size:13px;color:#5a6b57;">
            ☕ <strong>Favorit:</strong> {{ $pelanggan->menu_favorit }}
        </div>
        @endif
    </div>

    {{-- Transaksi --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;">
            <h3 style="font-size:15px;font-weight:700;color:#0D530E;">Riwayat Transaksi</h3>
        </div>
        @if($transaksi->count())
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#FBF5DD;">
                    <th style="text-align:left;padding:10px 16px;font-size:12px;color:#5a6b57;font-weight:600;text-transform:uppercase;">Tanggal</th>
                    <th style="text-align:left;padding:10px 16px;font-size:12px;color:#5a6b57;font-weight:600;text-transform:uppercase;">Metode</th>
                    <th style="text-align:center;padding:10px 16px;font-size:12px;color:#5a6b57;font-weight:600;text-transform:uppercase;">Status</th>
                    <th style="text-align:right;padding:10px 16px;font-size:12px;color:#5a6b57;font-weight:600;text-transform:uppercase;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksi as $t)
                <tr class="table-row" style="border-top:1px solid #f5f5f5;">
                    <td style="padding:11px 16px;font-size:13px;color:#5a6b57;">{{ \Carbon\Carbon::parse($t->created_at)->format('d M Y H:i') }}</td>
                    <td style="padding:11px 16px;font-size:13px;">{{ $t->metode_bayar }}</td>
                    <td style="padding:11px 16px;text-align:center;">
                        @php $stColor=['selesai'=>'#306D29','proses'=>'#b8860b','batal'=>'#d4183d'][$t->status]??'#888'; @endphp
                        <span style="color:{{$stColor}};font-weight:600;font-size:13px;">{{ ucfirst($t->status) }}</span>
                    </td>
                    <td style="padding:11px 16px;font-size:13px;font-weight:600;text-align:right;color:#0D530E;">Rp {{ number_format($t->total,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="padding:48px;text-align:center;color:#bbb;">
            <div style="font-size:36px;margin-bottom:8px;">🧾</div>
            <div style="font-weight:600;color:#888;">Belum ada transaksi</div>
            <div style="font-size:13px;margin-top:4px;">Pelanggan ini belum pernah bertransaksi</div>
        </div>
        @endif
    </div>
</div>
@endsection
