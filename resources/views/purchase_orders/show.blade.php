@extends('layouts.app')
@section('title','Detail PO')
@section('content')

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="{{ route('po.index') }}"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;">←</a>
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0D530E;">{{ $po->nomor_po }}</h1>
        <p style="color:#5a6b57;font-size:14px;margin-top:2px;">Purchase Order Digital</p>
    </div>
    <button onclick="window.print()" class="btn-secondary" style="margin-left:auto;">🖨️ Cetak PO</button>
</div>

<div style="max-width:640px;">
    <div class="card" id="poDocument" style="padding:28px;">

        {{-- PO Header --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:20px;border-bottom:2px solid #E7E1B1;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
            <div>
                <div style="font-size:20px;font-weight:700;color:#0D530E;">☕ CAFE CNS</div>
                <div style="font-size:12px;color:#888;margin-top:2px;">Catch New Serenity · Singaraja, Bali</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:20px;font-weight:800;color:#0D530E;letter-spacing:1px;">PURCHASE ORDER</div>
                <div style="font-size:14px;font-weight:600;font-family:monospace;color:#306D29;">{{ $po->nomor_po }}</div>
                <div style="margin-top:8px;">
                    @php
                        $sc=['DITERBITKAN'=>['#1a6da6','#e8f0fc'],'DIKIRIM'=>['#b8860b','#fff8e1'],'SELESAI'=>['#306D29','#f0f9f0'],'DIBATALKAN'=>['#d4183d','#ffeaea']];
                        [$c,$b]=$sc[$po->status_po]??['#888','#f5f5f5'];
                    @endphp
                    <span style="background:{{$b}};color:{{$c}};padding:4px 14px;border-radius:20px;font-size:13px;font-weight:700;">{{ $po->status_po }}</span>
                </div>
            </div>
        </div>

        {{-- Info Grid — pakai flat columns dari DB query --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
            <div>
                <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Mitra Pemasok</div>
                <div style="font-weight:700;font-size:15px;color:#1a2a1a;">{{ $po->nama_mitra }}</div>
                <div style="font-size:13px;color:#5a6b57;margin-top:2px;">{{ $po->no_hp }}</div>
                <div style="font-size:13px;color:#5a6b57;">{{ $po->alamat }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Detail PO</div>
                <table style="font-size:13px;width:100%;">
                    <tr>
                        <td style="color:#5a6b57;padding-bottom:4px;">Tanggal Terbit</td>
                        <td style="font-weight:600;text-align:right;">{{ \Carbon\Carbon::parse($po->tanggal_terbit)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color:#5a6b57;padding-bottom:4px;">Est. Pengiriman</td>
                        <td style="font-weight:600;text-align:right;">
                            {{ $po->estimasi_kirim ? \Carbon\Carbon::parse($po->estimasi_kirim)->format('d M Y') : '-' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#5a6b57;">No. PO</td>
                        <td style="font-weight:600;font-family:monospace;text-align:right;font-size:12px;">{{ $po->nomor_po }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Items Table --}}
        <div style="border:1px solid #E7E1B1;border-radius:10px;overflow:hidden;margin-bottom:20px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#FBF5DD;">
                        <th style="text-align:left;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Deskripsi</th>
                        <th style="text-align:right;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Qty</th>
                        <th style="text-align:right;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Harga Satuan</th>
                        <th style="text-align:right;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding:14px 16px;">
                            {{-- Gunakan flat column: nama_bahan, catatan_mitra --}}
                            <div style="font-weight:600;font-size:14px;">{{ $po->nama_bahan }}</div>
                            @if($po->catatan_mitra)
                            <div style="font-size:12px;color:#888;margin-top:2px;">{{ $po->catatan_mitra }}</div>
                            @endif
                        </td>
                        <td style="padding:14px 16px;text-align:right;font-size:14px;">
                            {{ number_format($po->jumlah_tersedia, 2, ',', '.') }} {{ $po->satuan }}
                        </td>
                        <td style="padding:14px 16px;text-align:right;font-size:14px;">
                            Rp {{ number_format($po->harga_satuan, 0, ',', '.') }}
                        </td>
                        <td style="padding:14px 16px;text-align:right;font-size:14px;font-weight:700;color:#0D530E;">
                            Rp {{ number_format($po->total_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background:#FBF5DD;border-top:2px solid #E7E1B1;">
                        <td colspan="3" style="padding:14px 16px;font-weight:700;font-size:15px;text-align:right;">TOTAL NILAI PO</td>
                        <td style="padding:14px 16px;font-weight:800;font-size:16px;color:#0D530E;text-align:right;">
                            Rp {{ number_format($po->total_nilai, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Catatan --}}
        <div style="background:#FBF5DD;border:1px solid #E7E1B1;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13px;">
            <div style="font-weight:600;margin-bottom:6px;color:#0D530E;">Catatan:</div>
            <div style="color:#5a6b57;line-height:1.6;">
                1. Pembayaran dilakukan setelah barang diterima dan lolos Quality Control.<br>
                2. Harap konfirmasi penerimaan PO ini kepada tim Cafe CNS.<br>
                3. Pengiriman sesuai estimasi yang tertera di atas.
                @if($po->catatan_cafe)
                <br>4. {{ $po->catatan_cafe }}
                @endif
            </div>
        </div>

        {{-- Info QC jika sudah dilakukan --}}
        @if($penerimaan)
        <div style="background:{{ $qc&&$qc->hasil_qc==='LOLOS'?'#f0f9f0':'#fdecea' }};border-radius:10px;padding:14px 16px;margin-bottom:20px;">
            <div style="font-weight:700;font-size:13px;color:{{ $qc&&$qc->hasil_qc==='LOLOS'?'#0D530E':'#d4183d' }};margin-bottom:8px;">
                🔬 Hasil Quality Control
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;">
                <div><span style="color:#5a6b57;">Tanggal Terima:</span> <strong>{{ \Carbon\Carbon::parse($penerimaan->tanggal_terima)->format('d M Y') }}</strong></div>
                <div><span style="color:#5a6b57;">Jumlah Diterima:</span> <strong>{{ $penerimaan->jumlah_diterima }} {{ $po->satuan }}</strong></div>
                @if($qc)
                <div><span style="color:#5a6b57;">Hasil QC:</span>
                    <strong style="color:{{ $qc->hasil_qc==='LOLOS'?'#0D530E':'#d4183d' }};">{{ $qc->hasil_qc }}</strong>
                </div>
                <div><span style="color:#5a6b57;">Skor Rata-rata:</span> <strong>{{ round(($qc->skor_aroma+$qc->skor_warna+$qc->skor_ukuran+$qc->skor_kebersihan)/4,1) }}/5</strong></div>
                @endif
            </div>
            @if($qc&&$qc->catatan_qc)
            <div style="margin-top:8px;font-size:12px;color:#5a6b57;">📝 {{ $qc->catatan_qc }}</div>
            @endif
        </div>
        @endif

        {{-- Info Hutang --}}
        @if($hutang)
        <div style="background:#f7f4e8;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13px;">
            <div style="font-weight:700;color:#0D530E;margin-bottom:6px;">💳 Status Pembayaran</div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <p style="color:#5a6b57;">Tagihan: <strong>Rp {{ number_format($hutang->jumlah_tagihan,0,',','.') }}</strong></p>
                    <p style="color:#5a6b57;">Jatuh Tempo: <strong>{{ \Carbon\Carbon::parse($hutang->tanggal_jatuh_tempo)->format('d M Y') }}</strong></p>
                </div>
                <span style="padding:4px 14px;border-radius:20px;font-size:12px;font-weight:700;background:{{ $hutang->status_bayar==='SUDAH_BAYAR'?'#d4e8d0':'#fdecea' }};color:{{ $hutang->status_bayar==='SUDAH_BAYAR'?'#306D29':'#d4183d' }};">
                    {{ $hutang->status_bayar==='SUDAH_BAYAR' ? '✓ Lunas' : 'Belum Bayar' }}
                </span>
            </div>
        </div>
        @endif

        {{-- Aksi update status --}}
        @if(in_array($po->status_po, ['DITERBITKAN','DIKIRIM']))
        <div style="border-top:1px solid #E7E1B1;padding-top:20px;">
            <div style="font-size:13px;font-weight:600;color:#5a6b57;margin-bottom:10px;">Update Status PO:</div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @if($po->status_po === 'DITERBITKAN')
                <form method="POST" action="{{ route('po.update-status', $po->id_po) }}" style="margin:0;">
                    @csrf @method('PUT')
                    <input type="hidden" name="status_po" value="DIKIRIM">
                    <button type="submit" class="btn-primary" style="font-size:13px;"
                        onclick="return confirm('Tandai PO ini sudah dikirim?')">
                        🚚 Tandai Sudah Dikirim
                    </button>
                </form>
                @endif

                @if($po->status_po === 'DIKIRIM' && !$penerimaan)
                <a href="{{ route('kemitraan.qc.form', $po->id_po) }}"
                   class="btn-primary" style="text-decoration:none;font-size:13px;">
                    🔬 Proses QC Barang
                </a>
                @endif

                <form method="POST" action="{{ route('po.update-status', $po->id_po) }}" style="margin:0;">
                    @csrf @method('PUT')
                    <input type="hidden" name="status_po" value="DIBATALKAN">
                    <button type="submit"
                        style="padding:8px 16px;background:#ffeaea;color:#d4183d;border:1px solid #f5c6c6;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;"
                        onclick="return confirm('Yakin batalkan PO ini?')">
                        Batalkan PO
                    </button>
                </form>
            </div>
        </div>
        @endif

    </div>{{-- /card --}}
</div>

<style>
@media print {
    nav, aside, .btn-secondary, .btn-primary, form[method="POST"], a[href*="qc"] { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ccc !important; }
    body { padding: 0; }
}
</style>
@endsection
