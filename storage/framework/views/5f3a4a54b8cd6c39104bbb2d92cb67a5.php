<?php $__env->startSection('title','Purchase Orders'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0D530E;">Purchase Orders</h1>
        <p style="color:#5a6b57;font-size:14px;margin-top:2px;">Arsip dokumen pemesanan bahan baku ke mitra</p>
    </div>
</div>


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:24px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:26px;font-weight:700;color:#0D530E;"><?php echo e(number_format($stats['total'])); ?></div>
        <div style="font-size:12px;color:#5a6b57;margin-top:4px;">Total PO</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:26px;font-weight:700;color:#1a6da6;"><?php echo e(number_format($stats['diterbitkan'])); ?></div>
        <div style="font-size:12px;color:#5a6b57;margin-top:4px;">Diterbitkan</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:26px;font-weight:700;color:#306D29;"><?php echo e(number_format($stats['selesai'])); ?></div>
        <div style="font-size:12px;color:#5a6b57;margin-top:4px;">Selesai</div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:26px;font-weight:700;color:#0D530E;font-size:18px;">Rp <?php echo e(number_format($stats['nilai_total'],0,',','.')); ?></div>
        <div style="font-size:12px;color:#5a6b57;margin-top:4px;">Total Nilai</div>
    </div>
</div>


<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari No. PO atau mitra..." class="form-input" style="width:220px;">
        <select name="status" class="form-input" style="width:180px;">
            <option value="">Semua Status</option>
            <option value="DITERBITKAN" <?php echo e(request('status')==='DITERBITKAN'?'selected':''); ?>>Diterbitkan</option>
            <option value="DIKIRIM"     <?php echo e(request('status')==='DIKIRIM'    ?'selected':''); ?>>Dikirim</option>
            <option value="SELESAI"     <?php echo e(request('status')==='SELESAI'    ?'selected':''); ?>>Selesai</option>
            <option value="DIBATALKAN"  <?php echo e(request('status')==='DIBATALKAN' ?'selected':''); ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <?php if(request()->anyFilled(['status','search'])): ?>
        <a href="<?php echo e(route('po.index')); ?>" class="btn-secondary" style="text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>
</div>


<div class="card" style="overflow:hidden;padding:0;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">No. PO</th>
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Mitra</th>
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Bahan</th>
                    <th style="text-align:right;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Nilai</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Status</th>
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Terbit</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $purchaseOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $po): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $sc=['DITERBITKAN'=>['#1a6da6','#e8f0fc'],'DIKIRIM'=>['#b8860b','#fff8e1'],'SELESAI'=>['#306D29','#f0f9f0'],'DIBATALKAN'=>['#d4183d','#ffeaea']];
                    [$c,$b]=$sc[$po->status_po]??['#888','#f5f5f5'];
                ?>
                <tr class="table-row" style="border-bottom:1px solid #f0ede0;">
                    <td style="padding:13px 16px;font-family:monospace;font-size:13px;font-weight:600;color:#0D530E;"><?php echo e($po->nomor_po); ?></td>
                    
                    <td style="padding:13px 16px;font-size:13px;font-weight:600;"><?php echo e($po->nama_mitra ?? '-'); ?></td>
                    <td style="padding:13px 16px;font-size:13px;color:#5a6b57;"><?php echo e($po->nama_bahan ?? '-'); ?></td>
                    <td style="padding:13px 16px;text-align:right;font-weight:700;color:#0D530E;">Rp <?php echo e(number_format($po->total_nilai,0,',','.')); ?></td>
                    <td style="padding:13px 16px;text-align:center;">
                        <span style="background:<?php echo e($b); ?>;color:<?php echo e($c); ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><?php echo e($po->status_po); ?></span>
                    </td>
                    <td style="padding:13px 16px;font-size:13px;color:#5a6b57;"><?php echo e(\Carbon\Carbon::parse($po->tanggal_terbit)->format('d M Y')); ?></td>
                    <td style="padding:13px 16px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <a href="<?php echo e(route('po.show', $po->id_po)); ?>"
                               style="padding:5px 12px;background:#e8f5e8;color:#0D530E;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #b8dbb8;">
                                Detail
                            </a>
                            <?php if($po->status_po === 'DITERBITKAN'): ?>
                            <form method="POST" action="<?php echo e(route('po.update-status', $po->id_po)); ?>" style="margin:0;">
                                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="status_po" value="DIKIRIM">
                                <button type="submit"
                                    style="padding:5px 12px;background:#fff8e1;color:#b8860b;border-radius:6px;font-size:12px;font-weight:600;border:1px solid #f3e099;cursor:pointer;"
                                    onclick="return confirm('Tandai PO ini sudah dikirim?')">
                                    🚚 Kirim
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php if($po->status_po === 'DIKIRIM'): ?>
                            <a href="<?php echo e(route('kemitraan.qc.form', $po->id_po)); ?>"
                               style="padding:5px 12px;background:#d4e8d0;color:#0D530E;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #a8d4a0;">
                                🔬 QC
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="padding:48px;text-align:center;color:#888;">
                        <div style="font-size:36px;margin-bottom:8px;">📄</div>
                        <div style="font-weight:600;">Belum ada Purchase Order</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($purchaseOrders->hasPages()): ?>
    <div style="padding:16px;border-top:1px solid #E7E1B1;"><?php echo e($purchaseOrders->appends(request()->query())->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/purchase_orders/index.blade.php ENDPATH**/ ?>