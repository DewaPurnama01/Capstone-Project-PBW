<?php $__env->startSection('title', 'Inventori'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-5 max-w-screen-2xl mx-auto">

    
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

    
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4">
            <p style="color:#5a6b57;font-size:0.8rem;">Total Item</p>
            <p style="color:#0D530E;font-size:1.8rem;font-weight:700;"><?php echo e($stats['total']); ?></p>
        </div>
        <div class="card p-4" style="border-color:rgba(212,24,61,0.2);">
            <p style="color:#d4183d;font-size:0.8rem;">Stok Kritis/Rendah</p>
            <p style="color:#d4183d;font-size:1.8rem;font-weight:700;"><?php echo e($stats['kritis']); ?></p>
        </div>
        <div class="card p-4" style="border-color:rgba(48,109,41,0.2);">
            <p style="color:#306D29;font-size:0.8rem;">Stok Aman</p>
            <p style="color:#306D29;font-size:1.8rem;font-weight:700;"><?php echo e($stats['aman']); ?></p>
        </div>
    </div>

    
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama bahan..." class="form-input" style="width:220px;">
        <select name="status" class="form-input" style="width:150px;">
            <option value="">Semua Status</option>
            <option value="NORMAL" <?php echo e(request('status')==='NORMAL'?'selected':''); ?>>Normal</option>
            <option value="RENDAH" <?php echo e(request('status')==='RENDAH'?'selected':''); ?>>Rendah</option>
            <option value="HABIS"  <?php echo e(request('status')==='HABIS'?'selected':''); ?>>Habis</option>
        </select>
        <select name="kategori" class="form-input" style="width:150px;">
            <option value="">Semua Kategori</option>
            <option value="Bahan Baku" <?php echo e(request('kategori')==='Bahan Baku'?'selected':''); ?>>Bahan Baku</option>
            <option value="Kemasan"    <?php echo e(request('kategori')==='Kemasan'?'selected':''); ?>>Kemasan</option>
            <option value="Makanan"    <?php echo e(request('kategori')==='Makanan'?'selected':''); ?>>Makanan</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <a href="/inventori" class="btn-secondary">Reset</a>
    </form>

    
    <div class="card overflow-hidden">
        <table class="w-full">
            <thead>
                <tr style="background:#FBF5DD;">
                    <?php $__currentLoopData = ['Nama Bahan','Kategori','Stok Saat Ini','Min/Maks','Harga/Unit','Supplier','Status','Aksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <th class="px-4 py-3 text-left text-xs" style="color:#5a6b57;font-weight:600;border-bottom:1px solid #E7E1B1;"><?php echo e($h); ?></th>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="table-row" style="border-bottom:1px solid #f0ede0;">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <?php if($item->is_coffee): ?><span>☕</span><?php endif; ?>
                            <span style="color:#1a2e18;font-weight:600;font-size:0.875rem;"><?php echo e($item->nama_bahan); ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3"><span style="background:#E7E1B1;color:#306D29;border-radius:6px;padding:2px 8px;font-size:0.75rem;"><?php echo e($item->kategori); ?></span></td>
                    <td class="px-4 py-3">
                        <div>
                            <p style="color:#1a2e18;font-weight:600;font-size:0.875rem;"><?php echo e($item->jumlah_stok); ?> <?php echo e($item->satuan); ?></p>
                            <?php $pct = min(100, ($item->jumlah_stok / max($item->batas_minimum,1)) * 100); ?>
                            <div style="height:4px;background:#E7E1B1;border-radius:2px;width:80px;margin-top:4px;">
                                <div style="height:100%;background:<?php echo e($pct<40?'#d4183d':($pct<80?'#f59e0b':'#22c55e')); ?>;border-radius:2px;width:<?php echo e($pct); ?>%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm" style="color:#5a6b57;"><?php echo e($item->batas_minimum); ?> / <?php echo e($item->batas_maksimum); ?> <?php echo e($item->satuan); ?></td>
                    <td class="px-4 py-3 text-sm" style="color:#1a2e18;">Rp <?php echo e(number_format($item->harga_per_unit,0,',','.')); ?></td>
                    <td class="px-4 py-3 text-sm" style="color:#5a6b57;"><?php echo e($item->supplier); ?></td>
                    <td class="px-4 py-3">
                        <?php $statusMap = ['NORMAL'=>['badge-normal','Normal'],'RENDAH'=>['badge-rendah','Rendah'],'HABIS'=>['badge-kritis','Habis']]; ?>
                        <span class="text-xs px-2 py-1 rounded-full font-semibold <?php echo e($statusMap[$item->status_stok][0] ?? ''); ?>">
                            <?php echo e($statusMap[$item->status_stok][1] ?? $item->status_stok); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button onclick="editItem(<?php echo e(json_encode($item)); ?>)"
                                    style="font-size:0.75rem;padding:4px 8px;background:#FBF5DD;color:#306D29;border-radius:6px;font-weight:600;">Edit</button>
                            <form method="POST" action="/inventori/<?php echo e($item->id_bahan); ?>" style="margin:0;">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    style="font-size:0.75rem;padding:4px 8px;background:#fdecea;color:#d4183d;border-radius:6px;font-weight:600;border:none;cursor:pointer;"
                                    onclick="return confirm('Hapus bahan <?php echo e($item->nama_bahan); ?>? Tindakan ini tidak bisa dibatalkan.')">
                                    Hapus
                                </button>
                            </form>
                            <?php if($item->is_coffee && in_array($item->status_stok, ['RENDAH','HABIS'])): ?>
                            <a href="/kemitraan" style="font-size:0.75rem;padding:4px 8px;background:#d4e8d0;color:#0D530E;border-radius:6px;font-weight:600;text-decoration:none;">Request Petani</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" class="px-4 py-10 text-center" style="color:#9ca3af;">Tidak ada item ditemukan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3"><?php echo e($items->withQueryString()->links()); ?></div>
    </div>
</div>


<div id="modalTambah" class="modal-overlay hidden">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;">
        <div class="flex items-center justify-between mb-5">
            <h2 style="color:#0D530E;font-size:1.1rem;font-weight:700;">Tambah Item Inventori</h2>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" style="color:#9ca3af;">✕</button>
        </div>
        <form method="POST" action="/inventori" class="space-y-3">
            <?php echo csrf_field(); ?>
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


<div id="modalEdit" class="modal-overlay hidden">
    <div style="background:#fff;border-radius:16px;padding:24px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;">
        <div class="flex items-center justify-between mb-5">
            <h2 style="color:#0D530E;font-size:1.1rem;font-weight:700;">Edit Item Inventori</h2>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" style="color:#9ca3af;">✕</button>
        </div>
        <form id="formEdit" method="POST" class="space-y-3">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
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

<?php $__env->startPush('scripts'); ?>
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/inventori/index.blade.php ENDPATH**/ ?>