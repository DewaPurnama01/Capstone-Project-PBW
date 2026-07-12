@extends('layouts.app')
@section('title','Edit Transaksi')
@section('content')
<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="{{ route('transaksi.show', $transaksi->id) }}" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;">←</a>
    <div>
        <h1 style="font-size:20px;font-weight:700;color:#0D530E;">Edit Transaksi</h1>
        <p style="color:#5a6b57;font-size:13px;margin-top:1px;">#{{ $transaksi->id }}</p>
    </div>
</div>

@if($transaksi->status === 'selesai')
    <div style="background:#fff3cd;border:1px solid #ffc107;color:#856404;padding:12px 16px;border-radius:6px;margin-bottom:20px;">
        ⚠️ Transaksi yang sudah selesai tidak dapat diedit. Untuk mengubah data, batalkan terlebih dahulu.
    </div>
@else
    <div class="card" style="max-width:600px;">
        <form method="POST" action="{{ route('transaksi.update', $transaksi->id) }}">
            @csrf @method('PUT')

            {{-- Pelanggan --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;color:#0D530E;margin-bottom:8px;font-size:14px;">
                    Pelanggan
                </label>
                <select name="pelanggan_id" style="width:100%;padding:10px;border:1px solid #e0e0e0;border-radius:6px;font-size:14px;">
                    <option value="">-- Walk-in (Tanpa Pelanggan) --</option>
                    @forelse($pelanggan as $p)
                        <option value="{{ $p->id }}" @selected($transaksi->pelanggan_id == $p->id)>
                            {{ $p->nama }} ({{ $p->segmen }})
                        </option>
                    @empty
                        <option disabled>Tidak ada pelanggan terdaftar</option>
                    @endforelse
                </select>
                @error('pelanggan_id')
                    <p style="color:#d4183d;font-size:12px;margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Metode Bayar --}}
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;color:#0D530E;margin-bottom:8px;font-size:14px;">
                    Metode Bayar <span style="color:#d4183d;">*</span>
                </label>
                <select name="metode_bayar" required style="width:100%;padding:10px;border:1px solid #e0e0e0;border-radius:6px;font-size:14px;">
                    <option value="">-- Pilih Metode --</option>
                    <option value="Tunai" @selected($transaksi->metode_bayar == 'Tunai')>💵 Tunai</option>
                    <option value="Transfer" @selected($transaksi->metode_bayar == 'Transfer')>🏦 Transfer</option>
                    <option value="QRIS" @selected($transaksi->metode_bayar == 'QRIS')>📱 QRIS</option>
                </select>
                @error('metode_bayar')
                    <p style="color:#d4183d;font-size:12px;margin-top:4px;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info Transaksi (Read-only) --}}
            <div style="background:#f9f9f9;padding:12px 16px;border-radius:6px;margin-bottom:20px;font-size:13px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <span style="color:#888;">Status</span>
                        <div style="font-weight:600;color:#0D530E;">{{ ucfirst($transaksi->status) }}</div>
                    </div>
                    <div>
                        <span style="color:#888;">Total</span>
                        <div style="font-weight:600;color:#0D530E;">Rp {{ number_format($transaksi->total, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <span style="color:#888;">Kasir</span>
                        <div style="font-weight:600;">{{ $transaksi->kasir ?? 'Kasir' }}</div>
                    </div>
                    <div>
                        <span style="color:#888;">Tanggal</span>
                        <div style="font-weight:600;">{{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <a href="{{ route('transaksi.show', $transaksi->id) }}" class="btn-secondary" style="padding:10px 20px;text-decoration:none;display:inline-block;">Batal</a>
                <button type="submit" class="btn-primary" style="padding:10px 20px;">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>

    {{-- Danger Zone --}}
    <div class="card" style="max-width:600px;margin-top:30px;border-color:#fdd7dc;background:#fcf0f1;">
        <h3 style="font-size:16px;font-weight:700;color:#d4183d;margin-bottom:12px;">⚠️ Zona Berbahaya</h3>
        <p style="color:#666;font-size:13px;margin-bottom:16px;">
            Menghapus transaksi akan menghilangkan semua data terkait. Tindakan ini tidak dapat dibatalkan.
        </p>
        <form method="POST" action="{{ route('transaksi.destroy', $transaksi->id) }}" style="display:inline;" onsubmit="return confirm('Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger" style="padding:10px 16px;font-size:13px;">🗑️ Hapus Transaksi</button>
        </form>
    </div>
@endif

@endsection
