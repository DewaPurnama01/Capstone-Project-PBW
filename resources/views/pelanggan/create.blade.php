@extends('layouts.app')
@section('title','Tambah Pelanggan')
@section('content')
<div style="max-width:520px;margin:0 auto;padding:24px 0;">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
        <a href="{{ route('pelanggan.index') }}" style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;background:#f0f9f0;border-radius:50%;color:#306D29;text-decoration:none;font-size:18px;border:1px solid #b8dbb8;">←</a>
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#0D530E;">Tambah Pelanggan</h1>
            <p style="color:#5a6b57;font-size:13px;margin-top:2px;">Daftarkan pelanggan ke sistem CRM Cafe CNS</p>
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

        <form method="POST" action="{{ route('pelanggan.store') }}">
            @csrf
            <div style="display:grid;gap:18px;">

                {{-- Nama --}}
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        Nama <span style="color:#d4183d;">*</span>
                    </label>
                    <input type="text" name="nama" class="form-input" value="{{ old('nama') }}"
                        placeholder="cth: Budi Santoso" required autofocus>
                    <p style="font-size:12px;color:#9ca3af;margin-top:4px;">Cukup nama panggilan atau nama lengkap</p>
                </div>

                {{-- No HP --}}
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        No. HP / WhatsApp
                        <span style="font-weight:400;color:#9ca3af;">(opsional)</span>
                    </label>
                    <input type="text" name="no_hp" class="form-input" value="{{ old('no_hp') }}"
                        placeholder="cth: 08123456789">
                </div>

                {{-- Segmen --}}
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:8px;">
                        Segmen <span style="color:#d4183d;">*</span>
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        @foreach(['Baru' => ['desc'=>'Pertama kali','color'=>'#888','bg'=>'#f5f5f5'],
                                  'Reguler' => ['desc'=>'Sering datang','color'=>'#0D530E','bg'=>'#e8f5e8'],
                                  'Member'  => ['desc'=>'Sudah member','color'=>'#306D29','bg'=>'#d4e8d0'],
                                  'VIP'     => ['desc'=>'Pelanggan setia','color'=>'#b8860b','bg'=>'#fff8e1']] as $val => $info)
                        <label style="cursor:pointer;">
                            <input type="radio" name="segmen" value="{{ $val }}" style="display:none;"
                                {{ old('segmen','Baru') === $val ? 'checked' : '' }}
                                onchange="updateSegmenUI()">
                            <div class="segmen-opt" data-val="{{ $val }}"
                                style="padding:10px 12px;border:2px solid {{ old('segmen','Baru')===$val ? $info['color'] : '#E7E1B1' }};border-radius:10px;background:{{ old('segmen','Baru')===$val ? $info['bg'] : '#fff' }};transition:all 0.15s;">
                                <div style="font-size:13px;font-weight:600;color:{{ $info['color'] }};">{{ $val }}</div>
                                <div style="font-size:11px;color:#9ca3af;">{{ $info['desc'] }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Menu Favorit --}}
                <div>
                    <label style="display:block;font-size:13px;font-weight:600;color:#1a2a1a;margin-bottom:6px;">
                        Menu Favorit
                        <span style="font-weight:400;color:#9ca3af;">(opsional)</span>
                    </label>
                    <input type="text" name="menu_favorit" class="form-input" value="{{ old('menu_favorit') }}"
                        placeholder="cth: Kopi Susu, Matcha Latte">
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn-primary" style="flex:1;padding:12px;font-size:14px;">
                    Simpan Pelanggan
                </button>
                <a href="{{ route('pelanggan.index') }}" class="btn-secondary"
                    style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;padding:12px 20px;">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
const segmenColors = {
    'Baru':    { border: '#888',   bg: '#f5f5f5' },
    'Reguler': { border: '#0D530E', bg: '#e8f5e8' },
    'Member':  { border: '#306D29', bg: '#d4e8d0' },
    'VIP':     { border: '#b8860b', bg: '#fff8e1' },
};
function updateSegmenUI() {
    document.querySelectorAll('.segmen-opt').forEach(el => {
        const radio = document.querySelector(`input[value="${el.dataset.val}"]`);
        const c = segmenColors[el.dataset.val];
        if (radio.checked) {
            el.style.borderColor = c.border;
            el.style.background  = c.bg;
        } else {
            el.style.borderColor = '#E7E1B1';
            el.style.background  = '#fff';
        }
    });
}
document.querySelectorAll('input[name="segmen"]').forEach(r => r.addEventListener('change', updateSegmenUI));
</script>
@endsection
