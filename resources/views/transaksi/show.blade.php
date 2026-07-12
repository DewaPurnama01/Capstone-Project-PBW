@extends('layouts.app')
@section('title','Detail Transaksi')
@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="{{ route('transaksi.index') }}" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;">←</a>
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#0D530E;">Detail Transaksi</h1>
        <p style="color:#5a6b57;font-size:13px;margin-top:1px;">#{{ $transaksi->id }}</p>
    </div>
    <div style="margin-left:auto;display:flex;gap:10px;align-items:center;">
        @if($transaksi->status === 'proses')
        <a href="{{ route('transaksi.edit', $transaksi->id) }}" class="btn-secondary" style="font-size:13px;text-decoration:none;">✎ Edit</a>
        <form method="POST" action="{{ route('transaksi.update-status', $transaksi->id) }}">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="selesai">
            <button type="submit" class="btn-primary" style="font-size:13px;">✓ Selesaikan</button>
        </form>
        @endif
        <button onclick="window.print()" class="btn-secondary" style="font-size:13px;">🖨️ Cetak Struk</button>
    </div>
</div>

<div style="max-width:480px;">
    {{-- Receipt Card --}}
    <div class="card" id="receipt" style="font-family:'Courier New',monospace;">
        <div style="text-align:center;padding-bottom:16px;border-bottom:2px dashed #E7E1B1;margin-bottom:16px;">
            <div style="font-size:22px;font-weight:700;color:#0D530E;font-family:'Inter',sans-serif;">☕ CAFE CNS</div>
            <div style="font-size:12px;color:#888;margin-top:2px;">Catch New Serenity</div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;font-size:12px;">
            <div>
                <div style="color:#888;">No. Transaksi</div>
                <div style="font-weight:700;font-family:'Inter',sans-serif;">#{{ $transaksi->id }}</div>
            </div>
            <div>
                <div style="color:#888;">Tanggal</div>
                <div style="font-weight:700;">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}</div>
            </div>
            <div>
                <div style="color:#888;">Pelanggan</div>
                <div style="font-weight:700;">{{ $transaksi->nama ?: 'Walk-in' }}</div>
            </div>
            <div>
                <div style="color:#888;">Kasir</div>
                <div style="font-weight:700;">{{ $transaksi->kasir ?? session('user.name','Kasir') }}</div>
            </div>
        </div>

        {{-- Items --}}
        <div style="border-top:1px dashed #E7E1B1;border-bottom:1px dashed #E7E1B1;padding:12px 0;margin-bottom:12px;">
            @foreach($items as $d)
            <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
                <div>
                    <div>{{ $d->nama_item }}</div>
                    <div style="color:#888;font-size:11px;">{{ $d->qty }} × Rp {{ number_format($d->harga,0,',','.') }}</div>
                </div>
                <div style="font-weight:600;">Rp {{ number_format($d->subtotal,0,',','.') }}</div>
            </div>
            @endforeach
        </div>

        {{-- Totals --}}
        <div style="font-size:13px;">
            @php $subtotal = $items->sum('subtotal'); @endphp
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="color:#888;">Subtotal</span>
                <span>Rp {{ number_format($subtotal,0,',','.') }}</span>
            </div>
            @if($subtotal > $transaksi->total)
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;color:#d4183d;">
                <span>Diskon</span>
                <span>- Rp {{ number_format($subtotal - $transaksi->total,0,',','.') }}</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#0D530E;padding-top:8px;border-top:1px solid #E7E1B1;margin-top:4px;">
                <span>TOTAL</span>
                <span>Rp {{ number_format($transaksi->total,0,',','.') }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:13px;">
                <span style="color:#888;">Metode</span>
                <span style="font-weight:600;background:#e8f5e8;color:#0D530E;padding:2px 8px;border-radius:4px;">{{ $transaksi->metode_bayar }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:13px;">
                <span style="color:#888;">Status</span>
                @php $stColor=['selesai'=>'#306D29','proses'=>'#b8860b','batal'=>'#d4183d'][$transaksi->status]??'#888'; @endphp
                <span style="font-weight:600;color:{{ $stColor }};">{{ ucfirst($transaksi->status) }}</span>
            </div>
        </div>

        <div style="text-align:center;margin-top:16px;padding-top:12px;border-top:2px dashed #E7E1B1;font-size:11px;color:#aaa;">
            Terima kasih sudah berkunjung!<br>Sampai jumpa kembali ☕
        </div>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    #receipt, #receipt * { visibility: visible; }
    #receipt { position: absolute; top: 0; left: 0; width: 300px; }
    .btn-secondary, .btn-primary, nav, aside { display: none !important; }
}
</style>
@endsection
