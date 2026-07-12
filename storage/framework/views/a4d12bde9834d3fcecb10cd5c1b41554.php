<?php $__env->startSection('title','Transaksi'); ?>
<?php $__env->startSection('content'); ?>
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:#0D530E;">Transaksi Penjualan</h1>
        <p style="color:#5a6b57;font-size:14px;margin-top:2px;">Riwayat dan pencatatan transaksi kasir</p>
    </div>
    <a href="<?php echo e(route('transaksi.create')); ?>" class="btn-primary" style="text-decoration:none;">+ Transaksi Baru</a>
</div>


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
    <div class="card" style="text-align:center;">
        <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Pendapatan Hari Ini</div>
        <div style="font-size:20px;font-weight:700;color:#0D530E;">Rp <?php echo e(number_format($stats['hari_ini'],0,',','.')); ?></div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Bulan Ini</div>
        <div style="font-size:20px;font-weight:700;color:#0D530E;">Rp <?php echo e(number_format($stats['bulan_ini'],0,',','.')); ?></div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Transaksi Hari Ini</div>
        <div style="font-size:20px;font-weight:700;color:#0D530E;"><?php echo e($stats['count_hari_ini']); ?></div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Rata-rata / Transaksi</div>
        <div style="font-size:20px;font-weight:700;color:#0D530E;">Rp <?php echo e(number_format($stats['avg'],0,',','.')); ?></div>
    </div>
    <div class="card" style="text-align:center;">
        <div style="font-size:11px;color:#5a6b57;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Sedang Diproses</div>
        <div style="font-size:20px;font-weight:700;color:#b8860b;"><?php echo e($stats['proses']); ?></div>
    </div>
</div>


<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama pelanggan..." class="form-input" style="width:200px;">
        <input type="date" name="tanggal" value="<?php echo e(request('tanggal')); ?>" class="form-input" style="width:160px;">
        <select name="status" class="form-input" style="width:150px;">
            <option value="">Semua Status</option>
            <option value="proses"   <?php echo e(request('status')==='proses'   ? 'selected' : ''); ?>>Proses</option>
            <option value="selesai"  <?php echo e(request('status')==='selesai'  ? 'selected' : ''); ?>>Selesai</option>
            <option value="batal"    <?php echo e(request('status')==='batal'    ? 'selected' : ''); ?>>Batal</option>
        </select>
        <button type="submit" class="btn-primary">Filter</button>
        <?php if(request()->anyFilled(['search','tanggal','status'])): ?>
        <a href="<?php echo e(route('transaksi.index')); ?>" class="btn-secondary" style="text-decoration:none;">Reset</a>
        <?php endif; ?>
    </form>
</div>


<div class="card" style="overflow:hidden;padding:0;">
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;">
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">#</th>
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Tanggal & Waktu</th>
                    <th style="text-align:left;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Pelanggan</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Metode</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Status</th>
                    <th style="text-align:right;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Total</th>
                    <th style="text-align:center;padding:14px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $transaksi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="table-row" style="border-bottom:1px solid #f0ede0;">
                    
                    <td style="padding:13px 16px;font-size:12px;color:#888;font-family:monospace;">#<?php echo e($t->id); ?></td>

                    
                    <td style="padding:13px 16px;font-size:13px;color:#5a6b57;">
                        <?php echo e(\Carbon\Carbon::parse($t->created_at)->format('d M Y')); ?><br>
                        <span style="font-size:12px;"><?php echo e(\Carbon\Carbon::parse($t->created_at)->format('H:i')); ?></span>
                    </td>

                    
                    <td style="padding:13px 16px;font-size:14px;">
                        <?php if($t->nama && $t->nama !== 'Walk-in'): ?>
                            <span style="font-weight:600;color:#1a2e18;"><?php echo e($t->nama); ?></span>
                        <?php else: ?>
                            <span style="color:#9ca3af;">Walk-in</span>
                        <?php endif; ?>
                        <?php if($t->kasir): ?>
                        <br><span style="font-size:11px;color:#9ca3af;">Kasir: <?php echo e($t->kasir); ?></span>
                        <?php endif; ?>
                    </td>

                    
                    <td style="padding:13px 16px;text-align:center;">
                        <?php
                            $mc  = ['Tunai'=>'#0D530E','QRIS'=>'#1a6da6','Transfer'=>'#7b3fbe'];
                            $bgm = ['Tunai'=>'#e8f5e8','QRIS'=>'#e8f0fc','Transfer'=>'#f3e8ff'];
                        ?>
                        <span style="background:<?php echo e($bgm[$t->metode_bayar]??'#f5f5f5'); ?>;color:<?php echo e($mc[$t->metode_bayar]??'#888'); ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                            <?php echo e($t->metode_bayar); ?>

                        </span>
                    </td>

                    
                    <td style="padding:13px 16px;text-align:center;">
                        <?php
                            $sc  = ['selesai'=>'#306D29','proses'=>'#b8860b','batal'=>'#d4183d'];
                            $sbg = ['selesai'=>'#d4e8d0','proses'=>'#fff3cd','batal'=>'#fdecea'];
                        ?>
                        <span style="background:<?php echo e($sbg[$t->status]??'#f5f5f5'); ?>;color:<?php echo e($sc[$t->status]??'#888'); ?>;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;">
                            <?php echo e(ucfirst($t->status)); ?>

                        </span>
                    </td>

                    
                    <td style="padding:13px 16px;text-align:right;font-weight:700;font-size:14px;color:#0D530E;">
                        Rp <?php echo e(number_format($t->total,0,',','.')); ?>

                    </td>

                    
                    <td style="padding:13px 16px;text-align:center;">
                        <div style="display:flex;gap:6px;justify-content:center;">
                            <a href="<?php echo e(route('transaksi.show', $t->id)); ?>"
                               style="padding:5px 12px;background:#e8f5e8;color:#0D530E;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid #b8dbb8;">
                                Detail
                            </a>
                            <?php if($t->status === 'proses'): ?>
                            <form method="POST" action="<?php echo e(route('transaksi.update-status', $t->id)); ?>" style="margin:0;">
                                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                                <input type="hidden" name="status" value="selesai">
                                <button type="submit"
                                    style="padding:5px 12px;background:#d4e8d0;color:#306D29;border-radius:6px;font-size:12px;font-weight:600;border:1px solid #a8d4a0;cursor:pointer;"
                                    onclick="return confirm('Selesaikan transaksi ini? Stok bahan akan dikurangi otomatis.')">
                                    ✓ Selesai
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="padding:48px;text-align:center;color:#888;">
                        <div style="font-size:36px;margin-bottom:8px;">🧾</div>
                        <div style="font-weight:600;">Belum ada transaksi</div>
                        <div style="font-size:13px;margin-top:4px;">Mulai buat transaksi baru</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($transaksi->hasPages()): ?>
    <div style="padding:16px;border-top:1px solid #E7E1B1;"><?php echo e($transaksi->appends(request()->query())->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/transaksi/index.blade.php ENDPATH**/ ?>