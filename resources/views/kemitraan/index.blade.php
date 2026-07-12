@extends('layouts.app')
@section('title', 'Portal Kemitraan')

@section('content')
<div style="padding:24px;max-width:1300px;margin:0 auto;">

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0D530E;">Portal Kemitraan</h1>
        <p style="color:#5a6b57;font-size:14px;margin-top:2px;">Pengadaan bahan baku dari mitra lokal Cafe CNS</p>
    </div>
    <button onclick="openModal('modalBroadcast')"
        style="background:#306D29;color:#fff;border-radius:10px;padding:10px 18px;font-weight:600;font-size:14px;border:none;cursor:pointer;">
        + Request Restock Bahan
    </button>
</div>

{{-- Tabs --}}
@php $tab = request('tab','workflow'); @endphp
<div style="background:#f0ede0;border-radius:12px;padding:4px;display:flex;gap:4px;margin-bottom:20px;overflow-x:auto;">
    @foreach(['workflow'=>'🔄 Alur Pengadaan','mitra'=>'🤝 Mitra','hutang'=>'💳 Rekonsiliasi Hutang','riwayat'=>'📋 Riwayat Request'] as $key=>$label)
    <a href="?tab={{ $key }}"
       style="flex:1;min-width:120px;text-align:center;padding:9px 12px;border-radius:9px;font-size:13px;text-decoration:none;white-space:nowrap;font-weight:{{ $tab===$key?'600':'400' }};background:{{ $tab===$key?'#fff':'transparent' }};color:{{ $tab===$key?'#0D530E':'#5a6b57' }};box-shadow:{{ $tab===$key?'0 1px 3px rgba(0,0,0,0.08)':'' }};">
        {{ $label }}
    </a>
    @endforeach
</div>

@if($tab === 'workflow')
{{-- ══════════════════════════════════════════
     TAB: ALUR PENGADAAN
══════════════════════════════════════════ --}}

    {{-- Alert stok rendah --}}
    @foreach($stokRendah as $stok)
    <div style="background:#fdecea;border:1px solid #f5b8b8;border-radius:12px;padding:14px 18px;margin-bottom:12px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:18px;flex-shrink:0;">⚠️</span>
        <div style="flex:1;">
            <p style="color:#d4183d;font-weight:700;font-size:14px;">Stok {{ $stok->nama_bahan }} Kritis!</p>
            <p style="color:#d4183d;font-size:13px;">Sisa <strong>{{ $stok->jumlah_stok }} {{ $stok->satuan }}</strong> (min: {{ $stok->batas_minimum }} {{ $stok->satuan }})</p>
        </div>
        <button onclick="openModal('modalBroadcast');document.getElementById('select_bahan').value='{{ $stok->id_bahan }}';updateBahanInfo()"
            style="background:#d4183d;color:#fff;border-radius:8px;padding:8px 14px;font-weight:600;font-size:13px;border:none;cursor:pointer;white-space:nowrap;">
            Request Restock →
        </button>
    </div>
    @endforeach

    @php
    // Tentukan step yang ditampilkan — bisa dinagivasi lewat ?step=N
    $viewStep = (int)request('step', $workflowStep);
    // Clamp: tidak bisa lihat step yang belum tercapai
    $viewStep = max(1, min($viewStep, max($workflowStep, 1)));

    $steps = [
        1 => ['label'=>'Deteksi Stok',    'icon'=>'🔍'],
        2 => ['label'=>'Form Request',     'icon'=>'📝'],
        3 => ['label'=>'Broadcast',        'icon'=>'📡'],
        4 => ['label'=>'Penawaran',        'icon'=>'💰'],
        5 => ['label'=>'Pilih Terbaik',    'icon'=>'⭐'],
        6 => ['label'=>'Generate PO',      'icon'=>'📄'],
        7 => ['label'=>'Pengiriman',       'icon'=>'🚚'],
        8 => ['label'=>'Quality Control',  'icon'=>'🔬'],
        9 => ['label'=>'Selesai',          'icon'=>'✅'],
    ];
    $namabahanAktif = $workflowActive ? $workflowActive->nama_bahan : 'Bahan Baku';
    $satuanAktif    = $workflowActive ? $workflowActive->satuan : '';
    @endphp

    {{-- Progress Stepper — setiap step yang sudah tercapai bisa diklik --}}
    <div class="card" style="padding:20px 24px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <h3 style="font-size:15px;font-weight:700;color:#0D530E;">
                Alur Pengadaan
                @if($workflowActive)
                <span style="font-weight:400;color:#5a6b57;font-size:13px;">— {{ $namabahanAktif }}</span>
                @endif
            </h3>
            @if($workflowStep > 1)
            <span style="font-size:11px;color:#9ca3af;">Klik lingkaran langkah untuk navigasi</span>
            @endif
        </div>
        <div style="display:flex;align-items:flex-start;overflow-x:auto;gap:0;margin-top:12px;">
            @foreach($steps as $num => $step)
            @php
                $done     = $num < $workflowStep;
                $active   = $num === $workflowStep;
                $viewing  = $num === $viewStep;
                $reachable= $num <= $workflowStep;
                $circBg   = $done ? '#306D29' : ($active ? '#0D530E' : '#e0dbd0');
                $circTxt  = ($done || $active) ? '#fff' : '#9ca3af';
                $lblClr   = $viewing ? '#0D530E' : ($done ? '#5a6b57' : ($active ? '#0D530E' : '#9ca3af'));
                $lblWgt   = $viewing ? '700' : ($done || $active ? '500' : '400');
                // Ring untuk step yang sedang dilihat
                $ringStyle= $viewing && $num !== $workflowStep ? 'box-shadow:0 0 0 3px #306D29,0 0 0 5px #d4e8d0;' : '';
            @endphp
            <div style="display:flex;align-items:center;flex:1;min-width:65px;">
                <div style="display:flex;flex-direction:column;align-items:center;min-width:58px;">
                    @if($reachable)
                    <a href="?tab=workflow&step={{ $num }}"
                       style="width:38px;height:38px;border-radius:50%;background:{{ $circBg }};display:flex;align-items:center;justify-content:center;font-size:{{ $done?'15px':'13px' }};color:{{ $circTxt }};font-weight:700;flex-shrink:0;text-decoration:none;{{ $ringStyle }}cursor:pointer;"
                       title="Lihat {{ $step['label'] }}">
                        @if($done) ✓ @else {{ $step['icon'] }} @endif
                    </a>
                    @else
                    <div style="width:38px;height:38px;border-radius:50%;background:#e0dbd0;display:flex;align-items:center;justify-content:center;font-size:13px;color:#c0bdb0;flex-shrink:0;">
                        {{ $step['icon'] }}
                    </div>
                    @endif
                    <div style="font-size:10px;font-weight:{{ $lblWgt }};color:{{ $lblClr }};margin-top:5px;text-align:center;line-height:1.3;">{{ $step['label'] }}</div>
                </div>
                @if($num < 9)
                <div style="flex:1;height:2px;background:{{ $done?'#306D29':'#e0dbd0' }};margin:0 2px;margin-bottom:18px;min-width:4px;"></div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Navigasi antar step --}}
    @if($workflowStep > 1)
    <div style="display:flex;gap:8px;margin-bottom:16px;align-items:center;">
        @if($viewStep > 1)
        <a href="?tab=workflow&step={{ $viewStep - 1 }}"
           style="padding:6px 14px;background:#fff;border:1px solid #E7E1B1;border-radius:8px;font-size:13px;color:#5a6b57;text-decoration:none;font-weight:500;">
            ← Sebelumnya
        </a>
        @endif
        <span style="font-size:13px;color:#9ca3af;flex:1;text-align:center;">
            Melihat: <strong style="color:#0D530E;">Langkah {{ $viewStep }} — {{ $steps[$viewStep]['label'] }}</strong>
            @if($viewStep !== $workflowStep)
            (saat ini di langkah {{ $workflowStep }})
            @endif
        </span>
        @if($viewStep < $workflowStep)
        <a href="?tab=workflow&step={{ $viewStep + 1 }}"
           style="padding:6px 14px;background:#306D29;border-radius:8px;font-size:13px;color:#fff;text-decoration:none;font-weight:500;">
            Selanjutnya →
        </a>
        @endif
    </div>
    @endif

    {{-- KONTEN BERDASARKAN $viewStep --}}
    <div class="card" style="padding:28px;">

        @if($viewStep === 1)
        {{-- STEP 1: Deteksi Stok --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#fdecea;display:flex;align-items:center;justify-content:center;font-size:22px;">🔍</div>
            <div><h3 style="color:#0D530E;font-size:16px;font-weight:700;">Deteksi Stok Bahan</h3>
            <p style="color:#5a6b57;font-size:13px;">Sistem memantau stok dan memberikan peringatan saat di bawah minimum.</p></div>
        </div>
        @if($stokRendah->count() > 0)
            @foreach($stokRendah as $s)
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:#fdecea;border-radius:10px;margin-bottom:8px;">
                <div>
                    <p style="font-weight:600;color:#d4183d;">{{ $s->nama_bahan }}</p>
                    <p style="font-size:12px;color:#d4183d;">{{ $s->jumlah_stok }} {{ $s->satuan }} — min {{ $s->batas_minimum }} {{ $s->satuan }}</p>
                </div>
                <span style="background:#d4183d;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;align-self:center;">{{ $s->status_stok }}</span>
            </div>
            @endforeach
        @else
            <div style="text-align:center;padding:20px;color:#306D29;"><p style="font-size:24px;">✅</p><p>Semua stok aman saat ini.</p></div>
        @endif
        <div style="margin-top:20px;">
            <button onclick="openModal('modalBroadcast')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:12px 24px;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                📝 Buat Request Restock →
            </button>
        </div>

        @elseif($viewStep === 2)
        {{-- STEP 2: Form Request --}}
        <div style="text-align:center;padding:20px 0;">
            <div style="font-size:40px;margin-bottom:12px;">📝</div>
            <h3 style="color:#0D530E;font-size:18px;font-weight:700;margin-bottom:8px;">Buat Form Request</h3>
            <p style="color:#5a6b57;font-size:14px;max-width:480px;margin:0 auto 24px;">Pilih bahan, tentukan jumlah & budget, lalu kirim ke mitra pemasok.</p>
            <button onclick="openModal('modalBroadcast')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:12px 24px;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                📝 Buka Form Request →
            </button>
        </div>

        @elseif($viewStep === 3)
        {{-- STEP 3: Broadcast --}}
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#d4e8d0;display:flex;align-items:center;justify-content:center;font-size:22px;">📡</div>
                <div>
                    <h3 style="color:#0D530E;font-size:16px;font-weight:700;">Request Terkirim ke Mitra</h3>
                    @if($workflowActive)
                    <p style="color:#5a6b57;font-size:13px;">{{ $workflowActive->jumlah_dibutuhkan }} {{ $satuanAktif }} {{ $namabahanAktif }} • Budget Maks. Rp {{ number_format($workflowActive->harga_target,0,',','.') }}/{{ $satuanAktif }}</p>
                    @endif
                </div>
            </div>
            <div style="display:grid;gap:10px;margin-bottom:20px;">
                @foreach($notifikasiTerkirim as $nt)
                @php
                    $tokenRow = \Illuminate\Support\Facades\DB::table('tb_broadcast_token')
                        ->join('tb_mitra','tb_mitra.id_mitra','=','tb_broadcast_token.mitra_id')
                        ->where('tb_mitra.nama_mitra', $nt->nama_mitra)
                        ->where('tb_broadcast_token.broadcast_id', $broadcastAktif->id_broadcast ?? 0)
                        ->select('tb_broadcast_token.token')->first();
                @endphp
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#f7f4e8;border-radius:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#306D29;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                        {{ strtoupper(substr($nt->nama_mitra,0,1)) }}
                    </div>
                    <div style="flex:1;">
                        <p style="font-weight:600;color:#1a2e18;font-size:14px;">{{ $nt->nama_mitra }}</p>
                        <p style="color:#5a6b57;font-size:12px;">📞 {{ $nt->no_hp }}</p>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;background:{{ $nt->used?'#d4e8d0':'#e8f4fd' }};color:{{ $nt->used?'#306D29':'#1a6da6' }};">
                            {{ $nt->used ? '✓ Sudah Respons' : '⏳ Menunggu' }}
                        </span>
                        @if($tokenRow)
                        <a href="{{ url('/form-penawaran/'.$tokenRow->token) }}" target="_blank"
                           style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;background:#FBF5DD;color:#306D29;text-decoration:none;border:1px solid #E7E1B1;">
                            🔗 Link
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            <div style="background:#FBF5DD;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#5a6b57;">
                💡 Klik <strong>🔗 Link</strong> di atas → salin URL → kirim manual ke mitra via WhatsApp.
            </div>
            <button onclick="openModal('modalPenawaranManual')"
                style="background:#E7E1B1;color:#306D29;border-radius:8px;padding:10px 20px;font-weight:600;font-size:13px;border:none;cursor:pointer;">
                + Input Penawaran Manual (via Telepon)
            </button>
        </div>

        @elseif($viewStep === 4 || $viewStep === 5)
        {{-- STEP 4 & 5: Penawaran & Pilih Terbaik --}}
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#FBF5DD;display:flex;align-items:center;justify-content:center;font-size:22px;">💰</div>
                <div>
                    <h3 style="color:#0D530E;font-size:16px;font-weight:700;">Kompilasi Penawaran dari Mitra</h3>
                    <p style="color:#5a6b57;font-size:13px;">{{ count($penawaran) }} penawaran masuk.</p>
                </div>
            </div>
            @if($workflowActive)
            <div style="display:flex;gap:14px;padding:10px 14px;background:#FBF5DD;border-radius:10px;margin-bottom:16px;font-size:13px;flex-wrap:wrap;">
                <span>📦 <strong>{{ $workflowActive->jumlah_dibutuhkan }} {{ $satuanAktif }} {{ $namabahanAktif }}</strong></span>
                <span>💰 Budget Maks: <strong>Rp {{ number_format($workflowActive->harga_target,0,',','.') }}/{{ $satuanAktif }}</strong></span>
            </div>
            @endif
            @forelse($penawaran as $offer)
            @php
                $total       = $offer->harga_satuan * ($workflowActive->jumlah_dibutuhkan ?? 0);
                $dalamBudget = !$workflowActive || $offer->harga_satuan <= $workflowActive->harga_target;
                $terpilih    = $offer->status_penawaran === 'DITERIMA';
                $ditolak     = $offer->status_penawaran === 'DITOLAK';
            @endphp
            <div style="border:2px solid {{ $terpilih?'#306D29':($dalamBudget?'#e0dbd0':'#f5c0c0') }};border-radius:12px;padding:16px;background:{{ $terpilih?'#f0f9f0':'#fff' }};margin-bottom:12px;">
                <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:50%;background:{{ $terpilih?'#306D29':'#E7E1B1' }};display:flex;align-items:center;justify-content:center;color:{{ $terpilih?'#fff':'#306D29' }};font-weight:700;font-size:15px;flex-shrink:0;">
                            {{ strtoupper(substr($offer->nama_mitra,0,1)) }}
                        </div>
                        <div>
                            <p style="font-weight:700;color:#1a2e18;">{{ $offer->nama_mitra }}</p>
                            <p style="color:#5a6b57;font-size:12px;">⭐ {{ $offer->rating }}</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        @if(!$dalamBudget)<span style="background:#fdecea;color:#d4183d;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;">Over Budget</span>@endif
                        @if($terpilih)<span style="background:#306D29;color:#fff;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;">✓ Terpilih</span>@endif
                        @if($ditolak)<span style="background:#f0f0f0;color:#9ca3af;font-size:11px;padding:3px 8px;border-radius:20px;">Ditolak</span>@endif
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:10px;">
                    <div style="background:#f7f4e8;border-radius:8px;padding:10px;text-align:center;">
                        <p style="color:#5a6b57;font-size:11px;">Harga/{{ $satuanAktif }}</p>
                        <p style="color:#1a2e18;font-weight:700;font-size:15px;">Rp {{ number_format($offer->harga_satuan,0,',','.') }}</p>
                    </div>
                    <div style="background:#f7f4e8;border-radius:8px;padding:10px;text-align:center;">
                        <p style="color:#5a6b57;font-size:11px;">Stok</p>
                        <p style="color:#1a2e18;font-weight:700;font-size:15px;">{{ $offer->jumlah_tersedia }} {{ $satuanAktif }}</p>
                    </div>
                    <div style="background:#f7f4e8;border-radius:8px;padding:10px;text-align:center;">
                        <p style="color:#5a6b57;font-size:11px;">Est. Kirim</p>
                        <p style="color:#1a2e18;font-weight:700;font-size:13px;">{{ \Carbon\Carbon::parse($offer->estimasi_kirim)->format('d M Y') }}</p>
                    </div>
                </div>
                @if($offer->catatan_mitra)<p style="color:#5a6b57;font-size:12px;margin-bottom:8px;font-style:italic;">📝 {{ $offer->catatan_mitra }}</p>@endif
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#5a6b57;">Total: <strong style="color:#0D530E;">Rp {{ number_format($total,0,',','.') }}</strong></span>
                    @if($offer->status_penawaran === 'MENUNGGU')
                    <form method="POST" action="/kemitraan/penawaran/{{ $offer->id_penawaran }}/pilih" style="margin:0;">
                        @csrf @method('PUT')
                        <button type="submit" style="background:#306D29;color:#fff;border-radius:8px;padding:8px 16px;font-weight:600;font-size:13px;border:none;cursor:pointer;"
                            onclick="return confirm('Pilih penawaran dari {{ $offer->nama_mitra }}?')">
                            📄 Pilih & Generate PO →
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:24px;background:#FBF5DD;border-radius:10px;color:#5a6b57;">
                Belum ada penawaran masuk. Tunggu mitra mengisi link form yang sudah dibagikan.
            </div>
            @endforelse
            <div style="margin-top:12px;">
                <button onclick="openModal('modalPenawaranManual')"
                    style="background:#E7E1B1;color:#306D29;border-radius:8px;padding:10px 20px;font-weight:600;font-size:13px;border:none;cursor:pointer;">
                    + Input Penawaran Manual
                </button>
            </div>
        </div>

        @elseif($viewStep === 6)
        {{-- STEP 6: Generate PO --}}
        @if($workflowPO)
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#d4e8d0;display:flex;align-items:center;justify-content:center;font-size:24px;">📄</div>
                <div>
                    <h3 style="color:#0D530E;font-size:16px;font-weight:700;">Purchase Order Diterbitkan</h3>
                    <p style="color:#5a6b57;font-size:13px;">{{ $workflowPO->nomor_po }} → {{ $workflowPO->nama_mitra }}</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;background:#f7f4e8;border-radius:12px;padding:16px;margin-bottom:20px;">
                @foreach(['No. PO'=>$workflowPO->nomor_po,'Mitra'=>$workflowPO->nama_mitra,'Bahan'=>$namabahanAktif,'Jumlah'=>($workflowActive->jumlah_dibutuhkan??'-').' '.$satuanAktif,'Total Biaya'=>'Rp '.number_format($workflowPO->total_nilai,0,',','.'),'Status PO'=>$workflowPO->status_po] as $lbl=>$val)
                <div><p style="color:#9ca3af;font-size:11px;margin-bottom:2px;">{{ $lbl }}</p><p style="color:#1a2e18;font-weight:700;font-size:14px;">{{ $val }}</p></div>
                @endforeach
            </div>
            @if($workflowPO->status_po === 'DITERBITKAN')
            <form method="POST" action="/kemitraan/po/{{ $workflowPO->id_po }}/status">
                @csrf @method('PUT')
                <input type="hidden" name="status_po" value="DIKIRIM">
                <button type="submit" style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;border:none;cursor:pointer;" onclick="return confirm('Tandai sudah dikirim?')">
                    🚚 Tandai Barang Sudah Dikirim →
                </button>
            </form>
            @else
            <div style="background:#d4e8d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#306D29;font-weight:600;">✓ PO sudah dikirim / selesai.</div>
            @endif
        </div>
        @else
        <div style="text-align:center;padding:20px;color:#888;">PO belum digenerate. Pilih penawaran terlebih dahulu di langkah 4.</div>
        @endif

        @elseif($viewStep === 7)
        {{-- STEP 7: Pengiriman --}}
        @if($workflowPO)
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:24px;">🚚</div>
                <div>
                    <h3 style="color:#1d4ed8;font-size:16px;font-weight:700;">Dalam Proses Pengiriman</h3>
                    <p style="color:#5a6b57;font-size:13px;">Mitra sedang memproses & mengirim ke café</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;background:#f7f4e8;border-radius:12px;padding:16px;margin-bottom:20px;">
                @foreach(['No. PO'=>$workflowPO->nomor_po,'Mitra'=>$workflowPO->nama_mitra,'Bahan'=>$namabahanAktif,'Jumlah'=>($workflowActive->jumlah_dibutuhkan??'-').' '.$satuanAktif,'Total Biaya'=>'Rp '.number_format($workflowPO->total_nilai,0,',','.'),'Est. Tiba'=>\Carbon\Carbon::parse($workflowPO->estimasi_kirim??now())->format('d M Y')] as $lbl=>$val)
                <div><p style="color:#9ca3af;font-size:11px;margin-bottom:2px;">{{ $lbl }}</p><p style="color:#1a2e18;font-weight:700;font-size:14px;">{{ $val }}</p></div>
                @endforeach
            </div>
            <a href="/kemitraan/qc/{{ $workflowPO->id_po }}"
               style="display:block;text-align:center;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;text-decoration:none;">
                🔬 Barang Tiba — Mulai Quality Control →
            </a>
        </div>
        @else
        <div style="text-align:center;padding:20px;color:#888;">Data pengiriman tidak tersedia.</div>
        @endif

        @elseif($viewStep === 8)
        {{-- STEP 8: Quality Control --}}
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#FBF5DD;display:flex;align-items:center;justify-content:center;font-size:24px;">🔬</div>
                <div>
                    <h3 style="color:#8B6914;font-size:16px;font-weight:700;">Quality Control</h3>
                    <p style="color:#5a6b57;font-size:13px;">Pengecekan kualitas barang yang diterima</p>
                </div>
            </div>
            @if($workflowQC)
                {{-- QC sudah dilakukan — tampilkan hasil --}}
                @php $lolos = $workflowQC->hasil_qc === 'LOLOS'; @endphp
                <div style="background:{{ $lolos?'#d4e8d0':'#fdecea' }};border-radius:12px;padding:20px;margin-bottom:20px;text-align:center;">
                    <div style="font-size:40px;margin-bottom:8px;">{{ $lolos?'✅':'❌' }}</div>
                    <h3 style="font-size:18px;font-weight:700;color:{{ $lolos?'#0D530E':'#d4183d' }};margin-bottom:6px;">
                        {{ $lolos ? 'QC Lolos — Barang Diterima' : 'QC Tidak Lolos — Barang Ditolak' }}
                    </h3>
                    @if($workflowQC->catatan_qc)
                    <p style="color:#5a6b57;font-size:13px;">📝 {{ $workflowQC->catatan_qc }}</p>
                    @endif
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-top:14px;">
                        @foreach(['Aroma'=>$workflowQC->skor_aroma,'Warna'=>$workflowQC->skor_warna,'Ukuran'=>$workflowQC->skor_ukuran,'Kebersihan'=>$workflowQC->skor_kebersihan] as $k=>$v)
                        <div style="background:rgba(255,255,255,0.6);border-radius:8px;padding:8px;">
                            <p style="font-size:11px;color:#5a6b57;">{{ $k }}</p>
                            <p style="font-size:18px;font-weight:700;color:{{ $lolos?'#0D530E':'#d4183d' }};">{{ $v }}/5</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @if(!$lolos)
                <div style="background:#fff8e1;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:#8B6914;">
                    ⚠️ <strong>Barang tidak lolos QC.</strong> Ajukan retur ke mitra secara langsung, lalu buat request baru jika diperlukan.
                </div>
                <button onclick="openModal('modalBroadcast')"
                    style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                    🔄 Mulai Request Pengadaan Baru →
                </button>
                @endif
            @elseif($workflowPO && in_array($workflowPO->status_po, ['DITERBITKAN','DIKIRIM']))
                <a href="/kemitraan/qc/{{ $workflowPO->id_po }}"
                   style="display:block;text-align:center;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;text-decoration:none;">
                    🔬 Buka Form Quality Control →
                </a>
            @else
                <div style="text-align:center;padding:20px;color:#888;">QC belum bisa dilakukan. Pastikan barang sudah tiba dan PO dalam status DIKIRIM.</div>
            @endif
        </div>

        @elseif($viewStep === 9)
        {{-- STEP 9: Selesai --}}
        <div style="text-align:center;padding:20px 0;">
            <div style="width:72px;height:72px;border-radius:50%;background:#d4e8d0;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 16px;">✅</div>
            <h3 style="color:#0D530E;font-size:20px;font-weight:700;margin-bottom:8px;">Pengadaan Selesai! 🎉</h3>
            @if($workflowPO)
            <p style="color:#5a6b57;font-size:14px;">Stok <strong>{{ $namabahanAktif }}</strong> berhasil diperbarui. Hutang <strong>Rp {{ number_format($workflowPO->total_nilai,0,',','.') }}</strong> telah dicatat.</p>
            @endif
            <button onclick="openModal('modalBroadcast')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:12px 24px;font-weight:700;font-size:14px;border:none;cursor:pointer;margin-top:20px;">
                + Mulai Request Restock Baru
            </button>
        </div>
        @endif

    </div>{{-- /card workflow --}}

@elseif($tab === 'mitra')
{{-- ═══ TAB: MITRA ═══ --}}
    @php $mitraAktif = $mitra->where('status_aktif',1); $avgRating = $mitra->count() ? round($mitra->avg('rating'),1) : 0; @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
        @foreach([['Total Mitra',$mitra->count(),'🤝'],['Aktif',$mitraAktif->count(),'✅'],['Rating Rata-rata',$avgRating.' ⭐','🌟']] as [$lbl,$val,$icon])
        <div class="card" style="text-align:center;padding:16px;"><div style="font-size:24px;margin-bottom:4px;">{{ $icon }}</div><div style="font-size:20px;font-weight:700;color:#0D530E;">{{ $val }}</div><div style="font-size:12px;color:#5a6b57;margin-top:2px;">{{ $lbl }}</div></div>
        @endforeach
    </div>
    <div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
        <button onclick="openModal('modalTambahMitra')" style="background:#306D29;color:#fff;border-radius:10px;padding:10px 18px;font-weight:600;font-size:14px;border:none;cursor:pointer;">+ Daftarkan Mitra Baru</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
        @foreach($mitra as $m)
        <div class="card" style="padding:18px;border-color:{{ $m->status_aktif?'rgba(48,109,41,0.15)':'rgba(0,0,0,0.06)' }};">
            <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:44px;height:44px;border-radius:50%;background:{{ $m->status_aktif?'#306D29':'#E7E1B1' }};display:flex;align-items:center;justify-content:center;color:{{ $m->status_aktif?'#fff':'#5a6b57' }};font-weight:700;font-size:16px;flex-shrink:0;">{{ strtoupper(substr($m->nama_mitra,0,1)) }}</div>
                    <div>
                        <p style="font-weight:700;color:#1a2e18;font-size:14px;">{{ $m->nama_mitra }}</p>
                        <p style="color:#5a6b57;font-size:12px;">📍 {{ $m->alamat }}</p>
                        <p style="color:#5a6b57;font-size:12px;">📞 {{ $m->no_hp }} • {{ $m->komoditas }}</p>
                    </div>
                </div>
                <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;background:{{ $m->status_aktif?'#d4e8d0':'#f0ede0' }};color:{{ $m->status_aktif?'#306D29':'#9ca3af' }};flex-shrink:0;">{{ $m->status_aktif?'Aktif':'Nonaktif' }}</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:12px;">
                @foreach(['Order'=>$m->total_order.'x','Tepat Waktu'=>$m->persen_on_time.'%','Kualitas'=>$m->persen_kualitas.'%'] as $lbl=>$val)
                <div style="background:#f7f4e8;border-radius:8px;padding:8px;text-align:center;"><p style="color:#9ca3af;font-size:10px;">{{ $lbl }}</p><p style="color:#0D530E;font-weight:700;font-size:13px;">{{ $val }}</p></div>
                @endforeach
            </div>
            <div style="display:flex;gap:8px;">
                <button onclick="editMitra({{ json_encode($m) }})" style="flex:1;background:#E7E1B1;color:#306D29;border-radius:8px;padding:8px;font-weight:600;font-size:12px;border:none;cursor:pointer;">✏️ Edit</button>
                <form method="POST" action="/kemitraan/mitra/{{ $m->id_mitra }}" style="flex:1;margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" style="width:100%;background:#fdecea;color:#d4183d;border-radius:8px;padding:8px;font-weight:600;font-size:12px;border:none;cursor:pointer;" onclick="return confirm('Hapus mitra {{ $m->nama_mitra }}?')">🗑️ Hapus</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

@elseif($tab === 'hutang')
{{-- ═══ TAB: HUTANG ═══ --}}
    @php $totalBelumBayar=$hutang->where('status_bayar','BELUM_BAYAR')->sum('jumlah_tagihan'); $totalLunas=$hutang->where('status_bayar','SUDAH_BAYAR')->sum('jumlah_tagihan'); @endphp
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
        <div class="card" style="text-align:center;padding:20px;border-color:rgba(212,24,61,0.2);"><div style="font-size:12px;color:#d4183d;font-weight:600;margin-bottom:4px;">Belum Dibayar</div><div style="font-size:22px;font-weight:700;color:#d4183d;">Rp {{ number_format($totalBelumBayar,0,',','.') }}</div></div>
        <div class="card" style="text-align:center;padding:20px;border-color:rgba(48,109,41,0.2);"><div style="font-size:12px;color:#306D29;font-weight:600;margin-bottom:4px;">Sudah Dibayar</div><div style="font-size:22px;font-weight:700;color:#0D530E;">Rp {{ number_format($totalLunas,0,',','.') }}</div></div>
    </div>
    <div class="card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">@foreach(['Mitra','Tagihan','Jatuh Tempo','Status','Aksi'] as $h)<th style="text-align:left;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
                @forelse($hutang as $h)
                <tr class="table-row" style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:12px 16px;font-weight:600;font-size:14px;">{{ $h->nama_mitra }}</td>
                    <td style="padding:12px 16px;font-weight:700;color:#0D530E;">Rp {{ number_format($h->jumlah_tagihan,0,',','.') }}</td>
                    <td style="padding:12px 16px;font-size:13px;color:#5a6b57;">{{ \Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->format('d M Y') }}</td>
                    <td style="padding:12px 16px;"><span style="font-size:12px;padding:4px 10px;border-radius:20px;font-weight:600;background:{{ $h->status_bayar==='SUDAH_BAYAR'?'#d4e8d0':'#fdecea' }};color:{{ $h->status_bayar==='SUDAH_BAYAR'?'#306D29':'#d4183d' }};">{{ $h->status_bayar==='SUDAH_BAYAR'?'✓ Lunas':'Belum Bayar' }}</span></td>
                    <td style="padding:12px 16px;">
                        @if($h->status_bayar==='BELUM_BAYAR')
                        <form method="POST" action="/kemitraan/hutang/{{ $h->id_hutang }}/bayar" style="display:inline;">@csrf @method('PUT')
                            <button type="submit" style="background:#306D29;color:#fff;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;border:none;cursor:pointer;" onclick="return confirm('Konfirmasi pelunasan?')">✓ Bayar</button>
                        </form>
                        @else
                        <span style="color:#9ca3af;font-size:12px;">{{ $h->tanggal_lunas?\Carbon\Carbon::parse($h->tanggal_lunas)->format('d M Y'):'-' }}</span>
                        @endif
                    </td>
                </tr>
                @empty<tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;">Belum ada data hutang</td></tr>@endforelse
            </tbody>
        </table>
    </div>

@elseif($tab === 'riwayat')
{{-- ═══ TAB: RIWAYAT ═══ --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;font-size:15px;font-weight:700;color:#0D530E;">Riwayat Request Pengadaan</div>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">@foreach(['Tanggal','Bahan','Kebutuhan','Budget Maks','Status'] as $h)<th style="text-align:left;padding:10px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
                @forelse($riwayatBroadcast as $r)
                @php $stMap=['AKTIF'=>['#d4e8d0','#306D29'],'DITUTUP'=>['#f0ede0','#5a6b57'],'SELESAI'=>['#dbeafe','#1d4ed8']]; $sc=$stMap[$r->status_broadcast]??['#f0ede0','#5a6b57']; @endphp
                <tr class="table-row" style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:10px 16px;font-size:13px;color:#5a6b57;">{{ \Carbon\Carbon::parse($r->tanggal_kirim)->format('d M Y H:i') }}</td>
                    <td style="padding:10px 16px;font-weight:600;font-size:13px;">{{ $r->nama_bahan }}</td>
                    <td style="padding:10px 16px;font-size:13px;">{{ $r->jumlah_dibutuhkan }} {{ $r->satuan }}</td>
                    <td style="padding:10px 16px;font-size:13px;">Rp {{ number_format($r->harga_target,0,',','.') }}/{{ $r->satuan }}</td>
                    <td style="padding:10px 16px;"><span style="font-size:12px;padding:3px 10px;border-radius:20px;font-weight:600;background:{{ $sc[0] }};color:{{ $sc[1] }};">{{ $r->status_broadcast }}</span></td>
                </tr>
                @empty<tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;">Belum ada riwayat</td></tr>@endforelse
            </tbody>
        </table>
        @if($riwayatBroadcast->hasPages())
        <div style="padding:12px 16px;border-top:1px solid #E7E1B1;">{{ $riwayatBroadcast->appends(request()->query())->links() }}</div>
        @endif
    </div>
@endif

</div>

{{-- ════ MODALS ════ --}}

{{-- Modal: Request Restock --}}
<div id="modalBroadcast" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Form Request Restock</h2><p style="color:#5a6b57;font-size:13px;">Permintaan pengadaan ke mitra pemasok</p></div>
            <button onclick="closeModal('modalBroadcast')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button>
        </div>
        <form method="POST" action="/kemitraan/broadcast" style="display:grid;gap:14px;">
            @csrf
            <input type="hidden" name="mode_broadcast" value="manual">
            <div>
                <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Bahan yang Dibutuhkan <span style="color:#d4183d;">*</span></label>
                <select name="id_bahan" id="select_bahan" class="form-input" onchange="updateBahanInfo()" required>
                    <option value="">-- Pilih Bahan --</option>
                    @foreach($semuaBahan as $b)
                    <option value="{{ $b->id_bahan }}" data-satuan="{{ $b->satuan }}" data-stok="{{ $b->jumlah_stok }}" data-min="{{ $b->batas_minimum }}">
                        {{ $b->nama_bahan }} (stok: {{ $b->jumlah_stok }} {{ $b->satuan }})
                    </option>
                    @endforeach
                </select>
                <div id="bahanInfo" style="display:none;margin-top:6px;padding:8px 12px;background:#fdecea;border-radius:8px;font-size:12px;color:#d4183d;"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Jumlah <span style="color:#d4183d;">*</span></label>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input type="number" name="jumlah_dibutuhkan" class="form-input" placeholder="cth: 500" min="0.1" step="0.1" required>
                        <span id="label_satuan" style="font-size:13px;color:#5a6b57;white-space:nowrap;">—</span>
                    </div>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Budget Maks. (Rp/<span id="label_satuan2">unit</span>) <span style="color:#d4183d;">*</span></label>
                    <input type="number" name="harga_target" class="form-input" placeholder="cth: 50000" min="1" required>
                </div>
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Batas Waktu Respon <span style="color:#d4183d;">*</span></label>
                <input type="datetime-local" name="batas_respon" class="form-input" required min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan <span style="font-weight:400;color:#9ca3af;">(opsional)</span></label>
                <textarea name="catatan" class="form-input" rows="2" placeholder="Spesifikasi kualitas, dll."></textarea>
            </div>
            <div style="background:#FBF5DD;border-radius:10px;padding:14px;">
                <label style="font-size:13px;font-weight:700;color:#1a2a1a;display:block;margin-bottom:10px;">Mode Pengiriman</label>
                <div style="display:flex;align-items:start;gap:10px;padding:10px;border:2px solid #306D29;border-radius:8px;background:#f0f9f0;">
                    <input type="radio" name="mode_broadcast" value="manual" checked style="margin-top:2px;" disabled>
                    <div>
                        <p style="font-weight:600;color:#0D530E;font-size:13px;">✋ Manual</p>
                        <p style="font-size:12px;color:#5a6b57;">Sistem siapkan link, Anda kirim manual via WhatsApp satu-satu ke mitra.</p>
                    </div>
                </div>
            </div>
            {{-- Pilih Mitra --}}
            <div id="mitraCheckboxSection">
                <label style="font-size:13px;font-weight:700;color:#1a2a1a;display:block;margin-bottom:8px;">
                    Pilih Mitra yang Akan Dikirimi
                    <span style="font-weight:400;color:#9ca3af;font-size:12px;">(biarkan kosong untuk filter otomatis berdasarkan komoditas)</span>
                </label>
                <div style="display:grid;gap:6px;max-height:180px;overflow-y:auto;padding:10px;background:#f7f4e8;border-radius:8px;">
                    @foreach($mitra->where('status_aktif',1) as $m)
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:6px 8px;border-radius:6px;" onmouseover="this.style.background='#e8f5e8'" onmouseout="this.style.background='transparent'">
                        <input type="checkbox" name="mitra_dipilih[]" value="{{ $m->id_mitra }}"
                            style="width:16px;height:16px;accent-color:#306D29;cursor:pointer;"
                            class="mitra-cb">
                        <div>
                            <p style="font-size:13px;font-weight:600;color:#1a2a1a;line-height:1.2;">{{ $m->nama_mitra }}</p>
                            <p style="font-size:11px;color:#5a6b57;">{{ $m->komoditas }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
                <div style="display:flex;gap:8px;margin-top:6px;">
                    <button type="button" onclick="toggleAllMitra(true)"
                        style="font-size:11px;color:#306D29;background:none;border:none;cursor:pointer;text-decoration:underline;">Pilih Semua</button>
                    <span style="color:#ddd;">|</span>
                    <button type="button" onclick="toggleAllMitra(false)"
                        style="font-size:11px;color:#888;background:none;border:none;cursor:pointer;text-decoration:underline;">Hapus Semua</button>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <button type="button" onclick="closeModal('modalBroadcast')" style="padding:12px;border:2px solid #E7E1B1;border-radius:10px;background:#fff;color:#5a6b57;font-weight:600;font-size:14px;cursor:pointer;">Batal</button>
                <button type="submit" style="padding:12px;background:#306D29;color:#fff;border-radius:10px;font-weight:700;font-size:14px;border:none;cursor:pointer;">📡 Kirim Request</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Tambah Mitra --}}
<div id="modalTambahMitra" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;"><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Daftarkan Mitra Baru</h2><button onclick="closeModal('modalTambahMitra')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button></div>
        <form method="POST" action="/kemitraan/mitra" style="display:grid;gap:14px;">
            @csrf
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Nama Mitra <span style="color:#d4183d;">*</span></label><input type="text" name="nama_mitra" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">No. HP / WhatsApp <span style="color:#d4183d;">*</span></label><input type="text" name="no_hp" class="form-input" required><p style="font-size:11px;color:#9ca3af;margin-top:3px;">Format: 081234567890</p></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Alamat <span style="color:#d4183d;">*</span></label><input type="text" name="alamat" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Komoditas / Produk <span style="color:#d4183d;">*</span></label><input type="text" name="komoditas" class="form-input" placeholder="cth: Biji Kopi, Susu Segar, Telur" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan</label><textarea name="catatan" class="form-input" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><button type="button" onclick="closeModal('modalTambahMitra')" style="padding:11px;border:2px solid #E7E1B1;border-radius:10px;background:#fff;color:#5a6b57;font-weight:600;cursor:pointer;">Batal</button><button type="submit" style="padding:11px;background:#306D29;color:#fff;border-radius:10px;font-weight:700;border:none;cursor:pointer;">Simpan</button></div>
        </form>
    </div>
</div>

{{-- Modal: Edit Mitra --}}
<div id="modalEditMitra" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;"><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Edit Mitra</h2><button onclick="closeModal('modalEditMitra')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button></div>
        <form id="formEditMitra" method="POST" action="" style="display:grid;gap:14px;">
            @csrf @method('PUT')
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Nama <span style="color:#d4183d;">*</span></label><input type="text" id="edit_nama_mitra" name="nama_mitra" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">No. HP <span style="color:#d4183d;">*</span></label><input type="text" id="edit_no_hp" name="no_hp" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Alamat <span style="color:#d4183d;">*</span></label><input type="text" id="edit_alamat" name="alamat" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Komoditas <span style="color:#d4183d;">*</span></label><input type="text" id="edit_komoditas" name="komoditas" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Status</label><select id="edit_status_aktif" name="status_aktif" class="form-input"><option value="1">Aktif</option><option value="0">Tidak Aktif</option></select></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan</label><textarea id="edit_catatan" name="catatan" class="form-input" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><button type="button" onclick="closeModal('modalEditMitra')" style="padding:11px;border:2px solid #E7E1B1;border-radius:10px;background:#fff;color:#5a6b57;font-weight:600;cursor:pointer;">Batal</button><button type="submit" style="padding:11px;background:#306D29;color:#fff;border-radius:10px;font-weight:700;border:none;cursor:pointer;">Simpan</button></div>
        </form>
    </div>
</div>

{{-- Modal: Penawaran Manual --}}
@if($broadcastAktif)
<div id="modalPenawaranManual" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;"><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Input Penawaran Manual</h2><button onclick="closeModal('modalPenawaranManual')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button></div>
        <p style="color:#5a6b57;font-size:13px;margin-bottom:16px;">Untuk mitra yang merespons via telepon.</p>
        <form method="POST" action="/kemitraan/penawaran/manual" style="display:grid;gap:14px;">
            @csrf
            <input type="hidden" name="id_broadcast" value="{{ $broadcastAktif->id_broadcast }}">
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Mitra <span style="color:#d4183d;">*</span></label>
                <select name="id_mitra" class="form-input" required><option value="">-- Pilih Mitra --</option>@foreach($mitra->where('status_aktif',1) as $m)<option value="{{ $m->id_mitra }}">{{ $m->nama_mitra }}</option>@endforeach</select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Harga/{{ $broadcastAktif->satuan }} (Rp) <span style="color:#d4183d;">*</span></label><input type="number" name="harga_satuan" class="form-input" min="1" required></div>
                <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Stok ({{ $broadcastAktif->satuan }}) <span style="color:#d4183d;">*</span></label><input type="number" name="jumlah_tersedia" class="form-input" min="0.1" step="0.1" required></div>
            </div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Est. Pengiriman <span style="color:#d4183d;">*</span></label><input type="date" name="estimasi_kirim" class="form-input" min="{{ now()->addDay()->format('Y-m-d') }}" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan</label><textarea name="catatan_mitra" class="form-input" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><button type="button" onclick="closeModal('modalPenawaranManual')" style="padding:11px;border:2px solid #E7E1B1;border-radius:10px;background:#fff;color:#5a6b57;font-weight:600;cursor:pointer;">Batal</button><button type="submit" style="padding:11px;background:#306D29;color:#fff;border-radius:10px;font-weight:700;border:none;cursor:pointer;">Simpan</button></div>
        </form>
    </div>
</div>
@endif

<script>
function openModal(id)  { document.getElementById(id)?.classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id)?.classList.add('hidden'); }
document.querySelectorAll('.modal-overlay').forEach(o => { o.addEventListener('click', e => { if(e.target===o) closeModal(o.id); }); });

function autoCheckMitraByBahan(namaBahan) {
    const nama = namaBahan.toLowerCase();
    document.querySelectorAll('.mitra-cb').forEach(cb => {
        const label  = cb.closest('label');
        const komod  = label ? (label.querySelector('p:last-child')?.textContent || '').toLowerCase() : '';
        cb.checked   = komod.includes(nama);
    });
}

function updateBahanInfo() {
    const sel = document.getElementById('select_bahan');
    const opt = sel.options[sel.selectedIndex];
    const info = document.getElementById('bahanInfo');
    const ls   = document.getElementById('label_satuan');
    const ls2  = document.getElementById('label_satuan2');
    if (opt && opt.value) {
        const satuan = opt.dataset.satuan || '';
        const stok = parseFloat(opt.dataset.stok || 0);
        const min  = parseFloat(opt.dataset.min  || 0);
        if (ls)  ls.textContent  = satuan;
        if (ls2) ls2.textContent = satuan;
        // Auto-centang mitra yang komoditasnya sesuai bahan
        const namaBahan = opt.text.split(' (')[0].trim();
        autoCheckMitraByBahan(namaBahan);
        if (stok < min) { info.style.display='block'; info.textContent=`⚠️ Stok ${stok} ${satuan} — di bawah minimum ${min} ${satuan}`; }
        else { info.style.display='none'; }
    } else {
        if (ls)  ls.textContent  = '—';
        if (ls2) ls2.textContent = 'unit';
        if (info) info.style.display = 'none';
    }
}

function toggleAllMitra(check) {
    document.querySelectorAll('.mitra-cb').forEach(cb => cb.checked = check);
}

function editMitra(m) {
    document.getElementById('formEditMitra').action = '/kemitraan/mitra/' + m.id_mitra;
    document.getElementById('edit_nama_mitra').value   = m.nama_mitra  || '';
    document.getElementById('edit_no_hp').value        = m.no_hp       || '';
    document.getElementById('edit_alamat').value       = m.alamat      || '';
    document.getElementById('edit_komoditas').value    = m.komoditas   || '';
    document.getElementById('edit_status_aktif').value = m.status_aktif != null ? String(m.status_aktif) : '1';
    document.getElementById('edit_catatan').value      = m.catatan     || '';
    openModal('modalEditMitra');
}
</script>
@endsection
