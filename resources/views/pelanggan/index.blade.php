@extends('layouts.app')
@section('title','Manajemen Pelanggan')
@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0D530E;">Manajemen Pelanggan</h1>
        <p style="color:#5a6b57;font-size:14px;margin-top:2px;">Data dan segmentasi pelanggan Cafe CNS</p>
    </div>
    <a href="{{ route('pelanggan.create') }}" class="btn-primary" style="text-decoration:none;">+ Tambah Pelanggan</a>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:28px;font-weight:700;color:#0D530E;">{{ number_format($stats['total']) }}</div>
        <div style="font-size:13px;color:#5a6b57;margin-top:4px;">Total Pelanggan</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:28px;font-weight:700;color:#d4a017;">{{ number_format($stats['vip']) }}</div>
        <div style="font-size:13px;color:#5a6b57;margin-top:4px;">VIP</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:28px;font-weight:700;color:#306D29;">{{ number_format($stats['member']) }}</div>
        <div style="font-size:13px;color:#5a6b57;margin-top:4px;">Member</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:28px;font-weight:700;color:#888;">{{ number_format($stats['baru']) }}</div>
        <div style="font-size:13px;color:#5a6b57;margin-top:4px;">Baru</div>
    </div>
</div>

{{-- Filter --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau no. HP..." class="form-input" style="flex:1;min-width:200px;">
        <select name="segmen" class="form-input" style="width:160px;">
            <option value="">Semua Segmen</option>
            <option value="VIP"     {{ request('segmen')=='VIP'     ?'selected':'' }}>VIP</option>
            <option value="Member"  {{ request('segmen')=='Member'  ?'selected':'' }}>Member</option>
            <option value="Reguler" {{ request('segmen')=='Reguler' ?'selected':'' }}>Reguler</option>
            <option value="Baru"    {{ request('segmen')=='Baru'    ?'selected':'' }}>Baru</option>
        </select>
        <select name="status" class="form-input" style="width:140px;">
            <option value="">Semua Status</option>
            <option value="aktif"       {{ request('status')=='aktif'       ?'selected':'' }}>Aktif</option>
            <option value="tidak aktif" {{ request('status')=='tidak aktif' ?'selected':'' }}>Tidak Aktif</option>
        </select>
        <button type="submit" class="btn-primary">Cari</button>
        @if(request()->anyFilled(['search','segmen','status']))
        <a href="{{ route('pelanggan.index') }}" class="btn-secondary" style="text-decoration:none;">Reset</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="card" style="overflow:hidden;padding:0;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Pelanggan</th>
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">No. HP</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Segmen</th>
                    <th style="text-align:right;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Poin</th>
                    <th style="text-align:right;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Kunjungan</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pelanggan as $p)
                <tr class="table-row">
                    <td style="padding:14px 16px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#0D530E,#306D29);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:15px;flex-shrink:0;">
                                {{ strtoupper(substr($p->nama,0,1)) }}
                            </div>
                            <div>
                                <div style="font-weight:600;color:#1a2a1a;font-size:14px;">{{ $p->nama }}</div>
                                <div style="font-size:12px;color:#888;">Bergabung {{ \Carbon\Carbon::parse($p->tanggal_daftar)->format('M Y') }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:14px 16px;color:#5a6b57;font-size:14px;">{{ $p->no_hp ?? '-' }}</td>
                    <td style="padding:14px 16px;text-align:center;">
                        @php
                            $sc=['VIP'=>'#d4a017','Member'=>'#306D29','Reguler'=>'#0D530E','Baru'=>'#888'];
                            $bg=['VIP'=>'#fff8e1','Member'=>'#f0f9f0','Reguler'=>'#e8f5e8','Baru'=>'#f5f5f5'];
                            $c=$sc[$p->segmen]??'#888';
                            $b=$bg[$p->segmen]??'#f5f5f5';
                        @endphp
                        <span style="background:{{$b}};color:{{$c}};border:1px solid {{$c}}33;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">{{ $p->segmen }}</span>
                    </td>
                    <td style="padding:14px 16px;text-align:right;font-weight:600;color:#0D530E;">{{ number_format($p->poin) }}</td>
                    <td style="padding:14px 16px;text-align:right;color:#5a6b57;">{{ number_format($p->total_kunjungan) }}x</td>
                    <td style="padding:14px 16px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <a href="{{ route('pelanggan.show',$p->id) }}" style="padding:5px 12px;background:#e8f5e8;color:#0D530E;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #b8dbb8;">Detail</a>
                            <a href="{{ route('pelanggan.edit',$p->id) }}" style="padding:5px 12px;background:#fff8e1;color:#b8860b;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #e6d07a;">Edit</a>
                            <form method="POST" action="{{ route('pelanggan.destroy',$p->id) }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="padding:5px 12px;background:#fdecea;color:#d4183d;border-radius:6px;font-size:12px;font-weight:600;border:1px solid #f5b8b8;cursor:pointer;"
                                    onclick="return confirm('Hapus pelanggan {{ $p->nama }}? Data transaksi yang terkait akan tetap tersimpan.')">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding:48px;text-align:center;color:#888;">
                        <div style="font-size:36px;margin-bottom:8px;">👥</div>
                        <div style="font-weight:600;">Belum ada data pelanggan</div>
                        <div style="font-size:13px;margin-top:4px;">Mulai tambahkan pelanggan pertama Anda</div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pelanggan->hasPages())
    <div style="padding:16px;border-top:1px solid #E7E1B1;">{{ $pelanggan->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection