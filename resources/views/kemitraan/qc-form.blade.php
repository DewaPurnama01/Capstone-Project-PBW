@extends('layouts.app')
@section('title', 'Quality Control')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="/kemitraan?tab=po" style="color:#306D29;text-decoration:none;font-size:0.875rem;">← Kembali ke Purchase Orders</a>
        <h1 style="color:#0D530E;font-size:1.5rem;font-weight:700;margin-top:8px;">Pengecekan Kualitas Barang</h1>
        <p style="color:#5a6b57;font-size:0.875rem;">Quality Control saat penerimaan barang di Cafe CNS</p>
    </div>

    {{-- Info PO --}}
    <div style="background:#FBF5DD;border:1px solid #E7E1B1;border-radius:12px;padding:16px;" class="mb-5">
        <div class="grid grid-cols-2 gap-3">
            @foreach(['No. PO'=>$po->nomor_po,'Bahan'=>$po->nama_bahan,'Petani'=>$po->nama_mitra,'HP Petani'=>$po->no_hp,'Jumlah PO'=>$po->jumlah_dibutuhkan.' '.$po->satuan,'Harga Satuan'=>'Rp '.number_format($po->harga_satuan,0,',','.'),'Total Nilai'=>'Rp '.number_format($po->total_nilai,0,',','.'),'Tgl Terbit'=>\Carbon\Carbon::parse($po->tanggal_terbit)->format('d M Y')] as $lbl=>$val)
            <div>
                <p style="color:#5a6b57;font-size:0.72rem;">{{ $lbl }}</p>
                <p style="color:#1a2e18;font-weight:600;font-size:0.875rem;">{{ $val }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="/kemitraan/qc" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <input type="hidden" name="id_po" value="{{ $po->id_po }}">

        <div class="card p-5">
            <h3 style="color:#0D530E;font-weight:600;margin-bottom:16px;">☕ Penilaian Kualitas Biji Kopi</h3>

            @foreach(['skor_aroma'=>'Aroma Kopi','skor_warna'=>'Warna & Penampakan Biji','skor_ukuran'=>'Keseragaman Ukuran','skor_kebersihan'=>'Kebersihan (bebas kotoran)'] as $field=>$label)
            <div class="mb-4">
                <div class="flex justify-between mb-2">
                    <label style="color:#1a2e18;font-size:0.875rem;font-weight:500;">{{ $label }}</label>
                    <span id="{{ $field }}_display" style="color:#306D29;font-weight:600;font-size:0.875rem;">4/5</span>
                </div>
                <div class="flex gap-2">
                    @for($i = 1; $i <= 5; $i++)
                    <input type="radio" name="{{ $field }}" id="{{ $field }}_{{ $i }}" value="{{ $i }}" {{ $i===4?'checked':'' }} class="hidden" onchange="updateScore('{{ $field }}', {{ $i }})">
                    <label for="{{ $field }}_{{ $i }}"
                           id="{{ $field }}_btn_{{ $i }}"
                           class="flex-1 py-2 text-center rounded-lg cursor-pointer text-sm transition-all"
                           style="background:{{ $i<=4?'#306D29':'#E7E1B1' }};color:{{ $i<=4?'#fff':'#5a6b57' }};font-weight:{{ $i<=4?'600':'400' }};">
                        {{ $i }}
                    </label>
                    @endfor
                </div>
            </div>
            @endforeach

            {{-- Auto QC result --}}
            <div id="qcResult" style="background:#d4e8d0;border:1px solid #a8d4a0;border-radius:10px;padding:12px;text-align:center;margin-top:16px;">
                <p style="color:#0D530E;font-weight:700;">Skor Rata-rata: <span id="avgScore">4.0</span>/5</p>
                <p id="qcLabel" style="color:#306D29;font-size:0.8rem;margin-top:4px;">✅ LULUS Quality Control — Layak diterima</p>
            </div>
        </div>

        <div class="card p-5 space-y-4">
            <h3 style="color:#0D530E;font-weight:600;">📝 Detail Penerimaan</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Jumlah Fisik Diterima ({{ $po->satuan }})</label>
                    <input name="jumlah_diterima" type="number" step="0.01" class="form-input" value="{{ $po->jumlah_dibutuhkan }}" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Hasil QC (auto-kalkulasi)</label>
                    <select name="hasil_qc" id="hasil_qc_select" class="form-input" required>
                        <option value="LOLOS">LOLOS</option>
                        <option value="TIDAK_LOLOS">TIDAK LOLOS</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs mb-1" style="color:#5a6b57;">Kondisi Fisik Barang</label>
                <textarea name="kondisi_fisik" rows="2" class="form-input" placeholder="Deskripsikan kondisi fisik barang saat tiba..." required></textarea>
            </div>

            <div>
                <label class="block text-xs mb-1" style="color:#5a6b57;">Catatan QC</label>
                <textarea name="catatan_qc" rows="2" class="form-input" placeholder="Catatan hasil pemeriksaan..."></textarea>
            </div>

            <div>
                <label class="block text-xs mb-1" style="color:#5a6b57;">Foto Dokumentasi (maks. 2MB, JPG/PNG)</label>
                <input name="foto_dokumentasi" type="file" accept="image/jpeg,image/png" class="form-input" style="padding:6px;">
                <p style="color:#9ca3af;font-size:0.72rem;margin-top:4px;">Foto akan disimpan sebagai bukti dokumentasi QC.</p>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="/kemitraan?tab=po" class="flex-1 py-3 rounded-xl border text-sm text-center" style="border-color:#E7E1B1;color:#5a6b57;">Batal</a>
            <button type="submit" class="flex-1 btn-primary py-3 rounded-xl text-base">
                ✅ Simpan Hasil QC & Update Stok
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
const scores = { skor_aroma: 4, skor_warna: 4, skor_ukuran: 4, skor_kebersihan: 4 };

function updateScore(field, value) {
    scores[field] = value;
    document.getElementById(field + '_display').textContent = value + '/5';

    // Update button styles
    for (let i = 1; i <= 5; i++) {
        const btn = document.getElementById(field + '_btn_' + i);
        if (i <= value) {
            btn.style.background = '#306D29';
            btn.style.color = '#fff';
            btn.style.fontWeight = '600';
        } else {
            btn.style.background = '#E7E1B1';
            btn.style.color = '#5a6b57';
            btn.style.fontWeight = '400';
        }
    }

    // Update average
    const vals = Object.values(scores);
    const avg = vals.reduce((a,b) => a+b, 0) / vals.length;
    const passed = avg >= 3.5;

    document.getElementById('avgScore').textContent = avg.toFixed(1);
    const resultDiv = document.getElementById('qcResult');
    const label = document.getElementById('qcLabel');

    if (passed) {
        resultDiv.style.background = '#d4e8d0';
        resultDiv.style.borderColor = '#a8d4a0';
        resultDiv.querySelector('p').style.color = '#0D530E';
        label.style.color = '#306D29';
        label.textContent = '✅ LULUS Quality Control — Layak diterima';
        document.getElementById('hasil_qc_select').value = 'LOLOS';
    } else {
        resultDiv.style.background = '#ffe4e4';
        resultDiv.style.borderColor = '#f5c0c0';
        resultDiv.querySelector('p').style.color = '#d4183d';
        label.style.color = '#d4183d';
        label.textContent = '❌ TIDAK LULUS — Ajukan retur ke petani';
        document.getElementById('hasil_qc_select').value = 'TIDAK_LOLOS';
    }
}
</script>
@endpush
@endsection
