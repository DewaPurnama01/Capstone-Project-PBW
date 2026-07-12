@extends('layouts.app')
@section('title','Edit Pelanggan')
@section('content')
<div style="max-width:520px;margin:0 auto;padding:24px 0;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('pelanggan.show', $pelanggan->id) }}" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;">←</a>
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#0D530E;">Edit Pelanggan</h1>
            <p style="color:#5a6b57;font-size:13px;margin-top:2px;">{{ $pelanggan->nama }}</p>
        </div>
    </div>

    <div class="card" style="padding:24px;">
        @if($errors->any())
        <div class="alert-error" style="margin-bottom:20px;">
            <strong>Perhatian:</strong>
            <ul style="margin-top:6px;margin-left:16px;">
                @foreach($errors->all() as $e)<li style="font-size:13px;">{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('pelanggan.update', $pelanggan->id) }}">
            @csrf @method('PUT')
            <div style="display:grid;gap:18px;">

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        Nama <span style="color:#d4183d;">*</span>
                    </label>
                    <input type="text" name="nama" class="form-input" value="{{ old('nama', $pelanggan->nama) }}" required>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        No. HP / WhatsApp <span style="font-weight:400;color:#9ca3af;">(opsional)</span>
                    </label>
                    <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp', $pelanggan->no_hp) }}" placeholder="08xx-xxxx-xxxx">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:8px;">
                        Segmen <span style="color:#d4183d;">*</span>
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        @php
                        $segmenConfig = [
                            'Baru'    => ['desc'=>'Pertama kali','color'=>'#888','bg'=>'#f5f5f5'],
                            'Reguler' => ['desc'=>'Sering datang','color'=>'#0D530E','bg'=>'#e8f5e8'],
                            'Member'  => ['desc'=>'Sudah member','color'=>'#306D29','bg'=>'#d4e8d0'],
                            'VIP'     => ['desc'=>'Pelanggan setia','color'=>'#b8860b','bg'=>'#fff8e1'],
                        ];
                        $currentSegmen = old('segmen', $pelanggan->segmen);
                        @endphp
                        @foreach($segmenConfig as $val => $info)
                        <label style="cursor:pointer;">
                            <input type="radio" name="segmen" value="{{ $val }}" style="display:none;"
                                {{ $currentSegmen === $val ? 'checked' : '' }}
                                onchange="updateSegmenUI()">
                            <div class="segmen-opt" data-val="{{ $val }}"
                                style="padding:10px 12px;border:2px solid {{ $currentSegmen === $val ? $info['color'] : '#E7E1B1' }};border-radius:10px;background:{{ $currentSegmen === $val ? $info['bg'] : '#fff' }};transition:all 0.15s;">
                                <div style="font-size:13px;font-weight:600;color:{{ $info['color'] }};">{{ $val }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $info['desc'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        Menu Favorit <span style="font-weight:400;color:#9ca3af;">(opsional)</span>
                    </label>
                    <input type="text" name="menu_favorit" class="form-input" value="{{ old('menu_favorit', $pelanggan->menu_favorit) }}" placeholder="cth: Kopi Susu">
                </div>

                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        Status <span style="color:#d4183d;">*</span>
                    </label>
                    <select name="status" class="form-input">
                        <option value="aktif"       {{ old('status',$pelanggan->status)==='aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak aktif" {{ old('status',$pelanggan->status)==='tidak aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn-primary" style="flex:1;padding:12px;font-size:14px;">
                    Simpan Perubahan
                </button>
                <a href="{{ route('pelanggan.show', $pelanggan->id) }}" class="btn-secondary"
                    style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;padding:12px 20px;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
<script>
const segmenColors = {
    'Baru':    { border: '#888',    bg: '#f5f5f5' },
    'Reguler': { border: '#0D530E', bg: '#e8f5e8' },
    'Member':  { border: '#306D29', bg: '#d4e8d0' },
    'VIP':     { border: '#b8860b', bg: '#fff8e1' },
};
function updateSegmenUI() {
    document.querySelectorAll('.segmen-opt').forEach(el => {
        const radio = document.querySelector(`input[value="${el.dataset.val}"]`);
        const c = segmenColors[el.dataset.val];
        if (radio.checked) { el.style.borderColor = c.border; el.style.background = c.bg; }
        else { el.style.borderColor = '#E7E1B1'; el.style.background = '#fff'; }
    });
}
document.querySelectorAll('input[name="segmen"]').forEach(r => r.addEventListener('change', updateSegmenUI));
</script>
@endsection
