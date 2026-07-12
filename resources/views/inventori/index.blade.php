@extends('layouts.app')
@section('title', 'Inventori')

@section('content')
<div class="p-6 space-y-5 max-w-screen-2xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 style="color:#0D530E;font-size:1.5rem;font-weight:700;">Inventori</h1>
            <p style="color:#5a6b57;font-size:0.875rem;">Kelola stok bahan baku dan persediaan Cafe CNS</p>
        </div>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:8px 16px;font-weight:600;font-size:0.875rem;">
            + Tambah Item
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4">
            <p style="color:#5a6b57;font-size:0.8rem;">Total Item</p>
            <p style="color:#0D530E;font-size:1.8rem;font-weight:700;">{{ $stats['total'] }}</p>
        </div>
        <div class="card p-4" style="border-color:rgba(212,24,61,0.2);">
            <p style="color:#d4183d;font-size:0.8rem;">Stok Kritis/Rendah</p>
            <p style="color:#d4183d;font-size:1.8rem;font-weight:700;">{{ $stats['kritis'] }}</p>
        </div>
        <div class="card p-4" style="border-color:rgba(48,109,41,0.2);">
            <p style="color:#306D29;font-size:0.8rem;">Stok Aman</p>
            <p style="color:#306D29;font-size:1.8rem;font-weight:700;">{{ $stats['aman'] }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama bahan..." class="form-input" style="width:220px;">
        <select name="status" class="form-input" style="width:150px;">
            <option value="">Semua Status</option>
            <option value="NORMAL" {{ request('status')==='NORMAL'?'selected':'' }}>Normal</option>
            <option value="RENDAH" {{ request('status')==='RENDAH'?'selected':'' }}>Rendah</option>
            <option value="HABIS"  {{ request('status')==='HABIS'?'selected':'' }}>Habis</option>
        </select>
        <select name="kategori" class="form-input" style="width:150px;">
            <option value="">Semua Kategori</option>
            <option value="Bahan Baku" {{ request('kategori')==='Bahan Baku'?'selected':'' }}>Bahan Baku</option>
            <option value="Kemasan"    {{ request('kategori')==='Kemasan'?'selected':'' }}>Kemasan</option>
            <option value="Makanan"    {{ request('kategori')==='Makanan'?'selected':'' }}>Makanan</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="/inventori" class="btn-secondary">Reset</a>
    </form>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr style="background:#FBF5DD;">
                    @foreach(['Nama Bahan','Kategori','Stok Saat Ini','Min/Maks','Harga/Unit','Supplier','Status','Aksi'] as $h)
                    <th class="px-4 py-3 text-left text-xs" style="color:#5a6b57;font-weight:600;border-bottom:1px solid #E7E1B1;">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr class="table-row" style="border-bottom:1px solid #f0ede0;">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($item->is_coffee)<span>☕</span>@endif
                            <span style="color:#1a2e18;font-weight:600;font-size:0.875rem;">{{ $item->nama_bahan }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3"><span style="background:#E7E1B1;color:#306D29;border-radius:6px;padding:2px 8px;font-size:0.75rem;">{{ $item->kategori }}</span></td>
                    <td class="px-4 py-3">
                        <div>
                            <p style="color:#1a2e18;font-weight:600;font-size:0.875rem;">{{ $item->jumlah_stok }} {{ $item->satuan }}</p>
                            @php $pct = min(100, ($item->jumlah_stok / max($item->batas_minimum,1)) * 100); @endphp
                            <div style="height:4px;background:#E7E1B1;border-radius:2px;width:80px;margin-top:4px;">
                                <div style="height:100%;background:{{ $pct<40?'#d4183d':($pct<80?'#f59e0b':'#22c55e') }};border-radius:2px;width:{{ $pct }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm" style="color:#5a6b57;">{{ $item->batas_minimum }} / {{ $item->batas_maksimum }} {{ $item->satuan }}</td>
                    <td class="px-4 py-3 text-sm" style="color:#1a2e18;">Rp {{ number_format($item->harga_per_unit,0,',','.') }}</td>
                    <td class="px-4 py-3 text-sm" style="color:#5a6b57;">{{ $item->supplier }}</td>
                    <td class="px-4 py-3">
                        @php $statusMap = ['NORMAL'=>['badge-normal','Normal'],'RENDAH'=>['badge-rendah','Rendah'],'HABIS'=>['badge-kritis','Habis']]; @endphp
                        <span class="text-xs px-2 py-1 rounded-full font-semibold {{ $statusMap[$item->status_stok][0] ?? '' }}">
                            {{ $statusMap[$item->status_stok][1] ?? $item->status_stok }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick="editItem({{ json_encode($item) }})"
                                    style="font-size:0.75rem;padding:4px 8px;background:#FBF5DD;color:#306D29;border-radius:6px;font-weight:600;">Edit</button>
                            <form method="POST" action="/inventori/{{ $item->id_bahan }}" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    style="font-size:0.75rem;padding:4px 8px;background:#fdecea;color:#d4183d;border-radius:6px;font-weight:600;border:none;cursor:pointer;"
                                    onclick="return confirm('Hapus bahan {{ $item->nama_bahan }}? Tindakan ini tidak bisa dibatalkan.')">
                                    Hapus
                                </button>
                            </form>
                            @if($item->is_coffee && in_array($item->status_stok, ['RENDAH','HABIS']))
                            <a href="/kemitraan" style="font-size:0.75rem;padding:4px 8px;background:#d4e8d0;color:#0D530E;border-radius:6px;font-weight:600;text-decoration:none;">Request Petani</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center" style="color:#9ca3af;">Tidak ada item ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3">{{ $items->withQueryString()->links() }}</div>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" class="modal-overlay hidden">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;">
        <div class="flex items-center justify-between mb-5">
            <h2 style="color:#0D530E;font-size:1.1rem;font-weight:700;">Tambah Item Inventori</h2>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" style="color:#9ca3af;">✕</button>
        </div>
        <form method="POST" action="/inventori" class="space-y-3">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Nama Bahan</label>
                    <input name="nama_bahan" class="form-input" placeholder="Contoh: Biji Kopi Arabika" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Kategori</label>
                    <select name="kategori" class="form-input">
                        <option>Bahan Baku</option><option>Kemasan</option><option>Makanan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Satuan</label>
                    <select name="satuan" class="form-input">
                        <option>gram</option><option>ml</option><option>kg</option><option>liter</option><option>pcs</option><option>botol</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Stok Awal</label>
                    <input name="jumlah_stok" type="number" step="0.01" class="form-input" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Batas Minimum</label>
                    <input name="batas_minimum" type="number" step="0.01" class="form-input" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Batas Maksimum</label>
                    <input name="batas_maksimum" type="number" step="0.01" class="form-input" placeholder="0" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Harga/Unit (Rp)</label>
                    <input name="harga_per_unit" type="number" class="form-input" placeholder="0" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Supplier</label>
                    <input name="supplier" class="form-input" placeholder="Nama supplier" required>
                </div>
            </div>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl border text-sm" style="border-color:#E7E1B1;color:#5a6b57;">Batal</button>
                <button type="submit" class="flex-1 btn-primary py-2.5 rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" class="modal-overlay hidden">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;">
        <div class="flex items-center justify-between mb-5">
            <h2 style="color:#0D530E;font-size:1.1rem;font-weight:700;">Edit Item Inventori</h2>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" style="color:#9ca3af;">✕</button>
        </div>
        <form id="formEdit" method="POST" class="space-y-3">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Nama Bahan</label>
                    <input name="nama_bahan" id="edit_nama" class="form-input" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Kategori</label>
                    <select name="kategori" id="edit_kategori" class="form-input">
                        <option>Bahan Baku</option><option>Kemasan</option><option>Makanan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Satuan</label>
                    <select name="satuan" id="edit_satuan" class="form-input">
                        <option>gram</option><option>ml</option><option>kg</option><option>liter</option><option>pcs</option><option>botol</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Jumlah Stok</label>
                    <input name="jumlah_stok" id="edit_stok" type="number" step="0.01" class="form-input" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Batas Minimum</label>
                    <input name="batas_minimum" id="edit_min" type="number" step="0.01" class="form-input" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Batas Maksimum</label>
                    <input name="batas_maksimum" id="edit_maks" type="number" step="0.01" class="form-input" required>
                </div>
                <div>
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Harga/Unit (Rp)</label>
                    <input name="harga_per_unit" id="edit_harga" type="number" class="form-input" required>
                </div>
                <div class="col-span-2">
                    <label class="block text-xs mb-1" style="color:#5a6b57;">Supplier</label>
                    <input name="supplier" id="edit_supplier" class="form-input" required>
                </div>
            </div>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="flex-1 py-2.5 rounded-xl border text-sm" style="border-color:#E7E1B1;color:#5a6b57;">Batal</button>
                <button type="submit" class="flex-1 btn-primary py-2.5 rounded-xl">Update</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editItem(item) {
    document.getElementById('formEdit').action = '/inventori/' + item.id_bahan;
    document.getElementById('edit_nama').value = item.nama_bahan;
    document.getElementById('edit_kategori').value = item.kategori;
    document.getElementById('edit_satuan').value = item.satuan;
    document.getElementById('edit_stok').value = item.jumlah_stok;
    document.getElementById('edit_min').value = item.batas_minimum;
    document.getElementById('edit_maks').value = item.batas_maksimum;
    document.getElementById('edit_harga').value = item.harga_per_unit;
    document.getElementById('edit_supplier').value = item.supplier;
    document.getElementById('modalEdit').classList.remove('hidden');
}
</script>
@endpush
@endsection
