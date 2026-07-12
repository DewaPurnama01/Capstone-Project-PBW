<?php $__env->startSection('title', 'Portal Kemitraan'); ?>

<?php $__env->startSection('content'); ?>
<div style="padding:24px;max-width:1300px;margin:0 auto;">


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


<?php $tab = request('tab','workflow'); ?>
<div style="background:#f0ede0;border-radius:12px;padding:4px;display:flex;gap:4px;margin-bottom:20px;overflow-x:auto;">
    <?php $__currentLoopData = ['workflow'=>'🔄 Alur Pengadaan','mitra'=>'🤝 Mitra','hutang'=>'💳 Rekonsiliasi Hutang','riwayat'=>'📋 Riwayat Request']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="?tab=<?php echo e($key); ?>"
       style="flex:1;min-width:120px;text-align:center;padding:9px 12px;border-radius:9px;font-size:13px;text-decoration:none;white-space:nowrap;font-weight:<?php echo e($tab===$key?'600':'400'); ?>;background:<?php echo e($tab===$key?'#fff':'transparent'); ?>;color:<?php echo e($tab===$key?'#0D530E':'#5a6b57'); ?>;box-shadow:<?php echo e($tab===$key?'0 1px 3px rgba(0,0,0,0.08)':''); ?>;">
        <?php echo e($label); ?>

    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<?php if($tab === 'workflow'): ?>


    
    <?php $__currentLoopData = $stokRendah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stok): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="background:#fdecea;border:1px solid #f5b8b8;border-radius:12px;padding:14px 18px;margin-bottom:12px;display:flex;align-items:center;gap:12px;">
        <span style="font-size:18px;flex-shrink:0;">⚠️</span>
        <div style="flex:1;">
            <p style="color:#d4183d;font-weight:700;font-size:14px;">Stok <?php echo e($stok->nama_bahan); ?> Kritis!</p>
            <p style="color:#d4183d;font-size:13px;">Sisa <strong><?php echo e($stok->jumlah_stok); ?> <?php echo e($stok->satuan); ?></strong> (min: <?php echo e($stok->batas_minimum); ?> <?php echo e($stok->satuan); ?>)</p>
        </div>
        <button onclick="openModal('modalBroadcast');document.getElementById('select_bahan').value='<?php echo e($stok->id_bahan); ?>';updateBahanInfo()"
            style="background:#d4183d;color:#fff;border-radius:8px;padding:8px 14px;font-weight:600;font-size:13px;border:none;cursor:pointer;white-space:nowrap;">
            Request Restock →
        </button>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php
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
    ?>

    
    <div class="card" style="padding:20px 24px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
            <h3 style="font-size:15px;font-weight:700;color:#0D530E;">
                Alur Pengadaan
                <?php if($workflowActive): ?>
                <span style="font-weight:400;color:#5a6b57;font-size:13px;">— <?php echo e($namabahanAktif); ?></span>
                <?php endif; ?>
            </h3>
            <?php if($workflowStep > 1): ?>
            <span style="font-size:11px;color:#9ca3af;">Klik lingkaran langkah untuk navigasi</span>
            <?php endif; ?>
        </div>
        <div style="display:flex;align-items:flex-start;overflow-x:auto;gap:0;margin-top:12px;">
            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $num => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
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
            ?>
            <div style="display:flex;align-items:center;flex:1;min-width:65px;">
                <div style="display:flex;flex-direction:column;align-items:center;min-width:58px;">
                    <?php if($reachable): ?>
                    <a href="?tab=workflow&step=<?php echo e($num); ?>"
                       style="width:38px;height:38px;border-radius:50%;background:<?php echo e($circBg); ?>;display:flex;align-items:center;justify-content:center;font-size:<?php echo e($done?'15px':'13px'); ?>;color:<?php echo e($circTxt); ?>;font-weight:700;flex-shrink:0;text-decoration:none;<?php echo e($ringStyle); ?>cursor:pointer;"
                       title="Lihat <?php echo e($step['label']); ?>">
                        <?php if($done): ?> ✓ <?php else: ?> <?php echo e($step['icon']); ?> <?php endif; ?>
                    </a>
                    <?php else: ?>
                    <div style="width:38px;height:38px;border-radius:50%;background:#e0dbd0;display:flex;align-items:center;justify-content:center;font-size:13px;color:#c0bdb0;flex-shrink:0;">
                        <?php echo e($step['icon']); ?>

                    </div>
                    <?php endif; ?>
                    <div style="font-size:10px;font-weight:<?php echo e($lblWgt); ?>;color:<?php echo e($lblClr); ?>;margin-top:5px;text-align:center;line-height:1.3;"><?php echo e($step['label']); ?></div>
                </div>
                <?php if($num < 9): ?>
                <div style="flex:1;height:2px;background:<?php echo e($done?'#306D29':'#e0dbd0'); ?>;margin:0 2px;margin-bottom:18px;min-width:4px;"></div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <?php if($workflowStep > 1): ?>
    <div style="display:flex;gap:8px;margin-bottom:16px;align-items:center;">
        <?php if($viewStep > 1): ?>
        <a href="?tab=workflow&step=<?php echo e($viewStep - 1); ?>"
           style="padding:6px 14px;background:#fff;border:1px solid #E7E1B1;border-radius:8px;font-size:13px;color:#5a6b57;text-decoration:none;font-weight:500;">
            ← Sebelumnya
        </a>
        <?php endif; ?>
        <span style="font-size:13px;color:#9ca3af;flex:1;text-align:center;">
            Melihat: <strong style="color:#0D530E;">Langkah <?php echo e($viewStep); ?> — <?php echo e($steps[$viewStep]['label']); ?></strong>
            <?php if($viewStep !== $workflowStep): ?>
            (saat ini di langkah <?php echo e($workflowStep); ?>)
            <?php endif; ?>
        </span>
        <?php if($viewStep < $workflowStep): ?>
        <a href="?tab=workflow&step=<?php echo e($viewStep + 1); ?>"
           style="padding:6px 14px;background:#306D29;border-radius:8px;font-size:13px;color:#fff;text-decoration:none;font-weight:500;">
            Selanjutnya →
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    
    <div class="card" style="padding:28px;">

        <?php if($viewStep === 1): ?>
        
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#fdecea;display:flex;align-items:center;justify-content:center;font-size:22px;">🔍</div>
            <div><h3 style="color:#0D530E;font-size:16px;font-weight:700;">Deteksi Stok Bahan</h3>
            <p style="color:#5a6b57;font-size:13px;">Sistem memantau stok dan memberikan peringatan saat di bawah minimum.</p></div>
        </div>
        <?php if($stokRendah->count() > 0): ?>
            <?php $__currentLoopData = $stokRendah; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="display:flex;justify-content:space-between;padding:12px 16px;background:#fdecea;border-radius:10px;margin-bottom:8px;">
                <div>
                    <p style="font-weight:600;color:#d4183d;"><?php echo e($s->nama_bahan); ?></p>
                    <p style="font-size:12px;color:#d4183d;"><?php echo e($s->jumlah_stok); ?> <?php echo e($s->satuan); ?> — min <?php echo e($s->batas_minimum); ?> <?php echo e($s->satuan); ?></p>
                </div>
                <span style="background:#d4183d;color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;align-self:center;"><?php echo e($s->status_stok); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php else: ?>
            <div style="text-align:center;padding:20px;color:#306D29;"><p style="font-size:24px;">✅</p><p>Semua stok aman saat ini.</p></div>
        <?php endif; ?>
        <div style="margin-top:20px;">
            <button onclick="openModal('modalBroadcast')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:12px 24px;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                📝 Buat Request Restock →
            </button>
        </div>

        <?php elseif($viewStep === 2): ?>
        
        <div style="text-align:center;padding:20px 0;">
            <div style="font-size:40px;margin-bottom:12px;">📝</div>
            <h3 style="color:#0D530E;font-size:18px;font-weight:700;margin-bottom:8px;">Buat Form Request</h3>
            <p style="color:#5a6b57;font-size:14px;max-width:480px;margin:0 auto 24px;">Pilih bahan, tentukan jumlah & budget, lalu kirim ke mitra pemasok.</p>
            <button onclick="openModal('modalBroadcast')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:12px 24px;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                📝 Buka Form Request →
            </button>
        </div>

        <?php elseif($viewStep === 3): ?>
        
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#d4e8d0;display:flex;align-items:center;justify-content:center;font-size:22px;">📡</div>
                <div>
                    <h3 style="color:#0D530E;font-size:16px;font-weight:700;">Request Terkirim ke Mitra</h3>
                    <?php if($workflowActive): ?>
                    <p style="color:#5a6b57;font-size:13px;"><?php echo e($workflowActive->jumlah_dibutuhkan); ?> <?php echo e($satuanAktif); ?> <?php echo e($namabahanAktif); ?> • Budget Maks. Rp <?php echo e(number_format($workflowActive->harga_target,0,',','.')); ?>/<?php echo e($satuanAktif); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div style="display:grid;gap:10px;margin-bottom:20px;">
                <?php $__currentLoopData = $notifikasiTerkirim; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $tokenRow = \Illuminate\Support\Facades\DB::table('tb_broadcast_token')
                        ->join('tb_mitra','tb_mitra.id_mitra','=','tb_broadcast_token.mitra_id')
                        ->where('tb_mitra.nama_mitra', $nt->nama_mitra)
                        ->where('tb_broadcast_token.broadcast_id', $broadcastAktif->id_broadcast ?? 0)
                        ->select('tb_broadcast_token.token')->first();
                ?>
                <div style="display:flex;align-items:center;gap:12px;padding:12px 16px;background:#f7f4e8;border-radius:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:#306D29;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                        <?php echo e(strtoupper(substr($nt->nama_mitra,0,1))); ?>

                    </div>
                    <div style="flex:1;">
                        <p style="font-weight:600;color:#1a2e18;font-size:14px;"><?php echo e($nt->nama_mitra); ?></p>
                        <p style="color:#5a6b57;font-size:12px;">📞 <?php echo e($nt->no_hp); ?></p>
                    </div>
                    <div style="display:flex;gap:6px;align-items:center;">
                        <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;background:<?php echo e($nt->used?'#d4e8d0':'#e8f4fd'); ?>;color:<?php echo e($nt->used?'#306D29':'#1a6da6'); ?>;">
                            <?php echo e($nt->used ? '✓ Sudah Respons' : '⏳ Menunggu'); ?>

                        </span>
                        <?php if($tokenRow): ?>
                        <a href="<?php echo e(url('/form-penawaran/'.$tokenRow->token)); ?>" target="_blank"
                           style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;background:#FBF5DD;color:#306D29;text-decoration:none;border:1px solid #E7E1B1;">
                            🔗 Link
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div style="background:#FBF5DD;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#5a6b57;">
                💡 Klik <strong>🔗 Link</strong> di atas → salin URL → kirim manual ke mitra via WhatsApp.
            </div>
            <button onclick="openModal('modalPenawaranManual')"
                style="background:#E7E1B1;color:#306D29;border-radius:8px;padding:10px 20px;font-weight:600;font-size:13px;border:none;cursor:pointer;">
                + Input Penawaran Manual (via Telepon)
            </button>
        </div>

        <?php elseif($viewStep === 4 || $viewStep === 5): ?>
        
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#FBF5DD;display:flex;align-items:center;justify-content:center;font-size:22px;">💰</div>
                <div>
                    <h3 style="color:#0D530E;font-size:16px;font-weight:700;">Kompilasi Penawaran dari Mitra</h3>
                    <p style="color:#5a6b57;font-size:13px;"><?php echo e(count($penawaran)); ?> penawaran masuk.</p>
                </div>
            </div>
            <?php if($workflowActive): ?>
            <div style="display:flex;gap:14px;padding:10px 14px;background:#FBF5DD;border-radius:10px;margin-bottom:16px;font-size:13px;flex-wrap:wrap;">
                <span>📦 <strong><?php echo e($workflowActive->jumlah_dibutuhkan); ?> <?php echo e($satuanAktif); ?> <?php echo e($namabahanAktif); ?></strong></span>
                <span>💰 Budget Maks: <strong>Rp <?php echo e(number_format($workflowActive->harga_target,0,',','.')); ?>/<?php echo e($satuanAktif); ?></strong></span>
            </div>
            <?php endif; ?>
            <?php $__empty_1 = true; $__currentLoopData = $penawaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $total       = $offer->harga_satuan * ($workflowActive->jumlah_dibutuhkan ?? 0);
                $dalamBudget = !$workflowActive || $offer->harga_satuan <= $workflowActive->harga_target;
                $terpilih    = $offer->status_penawaran === 'DITERIMA';
                $ditolak     = $offer->status_penawaran === 'DITOLAK';
            ?>
            <div style="border:2px solid <?php echo e($terpilih?'#306D29':($dalamBudget?'#e0dbd0':'#f5c0c0')); ?>;border-radius:12px;padding:16px;background:<?php echo e($terpilih?'#f0f9f0':'#fff'); ?>;margin-bottom:12px;">
                <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:12px;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:38px;height:38px;border-radius:50%;background:<?php echo e($terpilih?'#306D29':'#E7E1B1'); ?>;display:flex;align-items:center;justify-content:center;color:<?php echo e($terpilih?'#fff':'#306D29'); ?>;font-weight:700;font-size:15px;flex-shrink:0;">
                            <?php echo e(strtoupper(substr($offer->nama_mitra,0,1))); ?>

                        </div>
                        <div>
                            <p style="font-weight:700;color:#1a2e18;"><?php echo e($offer->nama_mitra); ?></p>
                            <p style="color:#5a6b57;font-size:12px;">⭐ <?php echo e($offer->rating); ?></p>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <?php if(!$dalamBudget): ?><span style="background:#fdecea;color:#d4183d;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;">Over Budget</span><?php endif; ?>
                        <?php if($terpilih): ?><span style="background:#306D29;color:#fff;font-size:11px;padding:3px 8px;border-radius:20px;font-weight:600;">✓ Terpilih</span><?php endif; ?>
                        <?php if($ditolak): ?><span style="background:#f0f0f0;color:#9ca3af;font-size:11px;padding:3px 8px;border-radius:20px;">Ditolak</span><?php endif; ?>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:10px;">
                    <div style="background:#f7f4e8;border-radius:8px;padding:10px;text-align:center;">
                        <p style="color:#5a6b57;font-size:11px;">Harga/<?php echo e($satuanAktif); ?></p>
                        <p style="color:#1a2e18;font-weight:700;font-size:15px;">Rp <?php echo e(number_format($offer->harga_satuan,0,',','.')); ?></p>
                    </div>
                    <div style="background:#f7f4e8;border-radius:8px;padding:10px;text-align:center;">
                        <p style="color:#5a6b57;font-size:11px;">Stok</p>
                        <p style="color:#1a2e18;font-weight:700;font-size:15px;"><?php echo e($offer->jumlah_tersedia); ?> <?php echo e($satuanAktif); ?></p>
                    </div>
                    <div style="background:#f7f4e8;border-radius:8px;padding:10px;text-align:center;">
                        <p style="color:#5a6b57;font-size:11px;">Est. Kirim</p>
                        <p style="color:#1a2e18;font-weight:700;font-size:13px;"><?php echo e(\Carbon\Carbon::parse($offer->estimasi_kirim)->format('d M Y')); ?></p>
                    </div>
                </div>
                <?php if($offer->catatan_mitra): ?><p style="color:#5a6b57;font-size:12px;margin-bottom:8px;font-style:italic;">📝 <?php echo e($offer->catatan_mitra); ?></p><?php endif; ?>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:13px;color:#5a6b57;">Total: <strong style="color:#0D530E;">Rp <?php echo e(number_format($total,0,',','.')); ?></strong></span>
                    <?php if($offer->status_penawaran === 'MENUNGGU'): ?>
                    <form method="POST" action="/kemitraan/penawaran/<?php echo e($offer->id_penawaran); ?>/pilih" style="margin:0;">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                        <button type="submit" style="background:#306D29;color:#fff;border-radius:8px;padding:8px 16px;font-weight:600;font-size:13px;border:none;cursor:pointer;"
                            onclick="return confirm('Pilih penawaran dari <?php echo e($offer->nama_mitra); ?>?')">
                            📄 Pilih & Generate PO →
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div style="text-align:center;padding:24px;background:#FBF5DD;border-radius:10px;color:#5a6b57;">
                Belum ada penawaran masuk. Tunggu mitra mengisi link form yang sudah dibagikan.
            </div>
            <?php endif; ?>
            <div style="margin-top:12px;">
                <button onclick="openModal('modalPenawaranManual')"
                    style="background:#E7E1B1;color:#306D29;border-radius:8px;padding:10px 20px;font-weight:600;font-size:13px;border:none;cursor:pointer;">
                    + Input Penawaran Manual
                </button>
            </div>
        </div>

        <?php elseif($viewStep === 6): ?>
        
        <?php if($workflowPO): ?>
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#d4e8d0;display:flex;align-items:center;justify-content:center;font-size:24px;">📄</div>
                <div>
                    <h3 style="color:#0D530E;font-size:16px;font-weight:700;">Purchase Order Diterbitkan</h3>
                    <p style="color:#5a6b57;font-size:13px;"><?php echo e($workflowPO->nomor_po); ?> → <?php echo e($workflowPO->nama_mitra); ?></p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;background:#f7f4e8;border-radius:12px;padding:16px;margin-bottom:20px;">
                <?php $__currentLoopData = ['No. PO'=>$workflowPO->nomor_po,'Mitra'=>$workflowPO->nama_mitra,'Bahan'=>$namabahanAktif,'Jumlah'=>($workflowActive->jumlah_dibutuhkan??'-').' '.$satuanAktif,'Total Biaya'=>'Rp '.number_format($workflowPO->total_nilai,0,',','.'),'Status PO'=>$workflowPO->status_po]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><p style="color:#9ca3af;font-size:11px;margin-bottom:2px;"><?php echo e($lbl); ?></p><p style="color:#1a2e18;font-weight:700;font-size:14px;"><?php echo e($val); ?></p></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php if($workflowPO->status_po === 'DITERBITKAN'): ?>
            <form method="POST" action="/kemitraan/po/<?php echo e($workflowPO->id_po); ?>/status">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status_po" value="DIKIRIM">
                <button type="submit" style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;border:none;cursor:pointer;" onclick="return confirm('Tandai sudah dikirim?')">
                    🚚 Tandai Barang Sudah Dikirim →
                </button>
            </form>
            <?php else: ?>
            <div style="background:#d4e8d0;border-radius:10px;padding:12px 16px;font-size:13px;color:#306D29;font-weight:600;">✓ PO sudah dikirim / selesai.</div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:20px;color:#888;">PO belum digenerate. Pilih penawaran terlebih dahulu di langkah 4.</div>
        <?php endif; ?>

        <?php elseif($viewStep === 7): ?>
        
        <?php if($workflowPO): ?>
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:24px;">🚚</div>
                <div>
                    <h3 style="color:#1d4ed8;font-size:16px;font-weight:700;">Dalam Proses Pengiriman</h3>
                    <p style="color:#5a6b57;font-size:13px;">Mitra sedang memproses & mengirim ke café</p>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;background:#f7f4e8;border-radius:12px;padding:16px;margin-bottom:20px;">
                <?php $__currentLoopData = ['No. PO'=>$workflowPO->nomor_po,'Mitra'=>$workflowPO->nama_mitra,'Bahan'=>$namabahanAktif,'Jumlah'=>($workflowActive->jumlah_dibutuhkan??'-').' '.$satuanAktif,'Total Biaya'=>'Rp '.number_format($workflowPO->total_nilai,0,',','.'),'Est. Tiba'=>\Carbon\Carbon::parse($workflowPO->estimasi_kirim??now())->format('d M Y')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><p style="color:#9ca3af;font-size:11px;margin-bottom:2px;"><?php echo e($lbl); ?></p><p style="color:#1a2e18;font-weight:700;font-size:14px;"><?php echo e($val); ?></p></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <a href="/kemitraan/qc/<?php echo e($workflowPO->id_po); ?>"
               style="display:block;text-align:center;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;text-decoration:none;">
                🔬 Barang Tiba — Mulai Quality Control →
            </a>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:20px;color:#888;">Data pengiriman tidak tersedia.</div>
        <?php endif; ?>

        <?php elseif($viewStep === 8): ?>
        
        <div>
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="width:56px;height:56px;border-radius:50%;background:#FBF5DD;display:flex;align-items:center;justify-content:center;font-size:24px;">🔬</div>
                <div>
                    <h3 style="color:#8B6914;font-size:16px;font-weight:700;">Quality Control</h3>
                    <p style="color:#5a6b57;font-size:13px;">Pengecekan kualitas barang yang diterima</p>
                </div>
            </div>
            <?php if($workflowQC): ?>
                
                <?php $lolos = $workflowQC->hasil_qc === 'LOLOS'; ?>
                <div style="background:<?php echo e($lolos?'#d4e8d0':'#fdecea'); ?>;border-radius:12px;padding:20px;margin-bottom:20px;text-align:center;">
                    <div style="font-size:40px;margin-bottom:8px;"><?php echo e($lolos?'✅':'❌'); ?></div>
                    <h3 style="font-size:18px;font-weight:700;color:<?php echo e($lolos?'#0D530E':'#d4183d'); ?>;margin-bottom:6px;">
                        <?php echo e($lolos ? 'QC Lolos — Barang Diterima' : 'QC Tidak Lolos — Barang Ditolak'); ?>

                    </h3>
                    <?php if($workflowQC->catatan_qc): ?>
                    <p style="color:#5a6b57;font-size:13px;">📝 <?php echo e($workflowQC->catatan_qc); ?></p>
                    <?php endif; ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:8px;margin-top:14px;">
                        <?php $__currentLoopData = ['Aroma'=>$workflowQC->skor_aroma,'Warna'=>$workflowQC->skor_warna,'Ukuran'=>$workflowQC->skor_ukuran,'Kebersihan'=>$workflowQC->skor_kebersihan]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="background:rgba(255,255,255,0.6);border-radius:8px;padding:8px;">
                            <p style="font-size:11px;color:#5a6b57;"><?php echo e($k); ?></p>
                            <p style="font-size:18px;font-weight:700;color:<?php echo e($lolos?'#0D530E':'#d4183d'); ?>;"><?php echo e($v); ?>/5</p>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php if(!$lolos): ?>
                <div style="background:#fff8e1;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:13px;color:#8B6914;">
                    ⚠️ <strong>Barang tidak lolos QC.</strong> Ajukan retur ke mitra secara langsung, lalu buat request baru jika diperlukan.
                </div>
                <button onclick="openModal('modalBroadcast')"
                    style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;border:none;cursor:pointer;">
                    🔄 Mulai Request Pengadaan Baru →
                </button>
                <?php endif; ?>
            <?php elseif($workflowPO && in_array($workflowPO->status_po, ['DITERBITKAN','DIKIRIM'])): ?>
                <a href="/kemitraan/qc/<?php echo e($workflowPO->id_po); ?>"
                   style="display:block;text-align:center;background:#306D29;color:#fff;border-radius:10px;padding:13px;font-weight:700;font-size:14px;text-decoration:none;">
                    🔬 Buka Form Quality Control →
                </a>
            <?php else: ?>
                <div style="text-align:center;padding:20px;color:#888;">QC belum bisa dilakukan. Pastikan barang sudah tiba dan PO dalam status DIKIRIM.</div>
            <?php endif; ?>
        </div>

        <?php elseif($viewStep === 9): ?>
        
        <div style="text-align:center;padding:20px 0;">
            <div style="width:72px;height:72px;border-radius:50%;background:#d4e8d0;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 16px;">✅</div>
            <h3 style="color:#0D530E;font-size:20px;font-weight:700;margin-bottom:8px;">Pengadaan Selesai! 🎉</h3>
            <?php if($workflowPO): ?>
            <p style="color:#5a6b57;font-size:14px;">Stok <strong><?php echo e($namabahanAktif); ?></strong> berhasil diperbarui. Hutang <strong>Rp <?php echo e(number_format($workflowPO->total_nilai,0,',','.')); ?></strong> telah dicatat.</p>
            <?php endif; ?>
            <button onclick="openModal('modalBroadcast')"
                style="background:#306D29;color:#fff;border-radius:10px;padding:12px 24px;font-weight:700;font-size:14px;border:none;cursor:pointer;margin-top:20px;">
                + Mulai Request Restock Baru
            </button>
        </div>
        <?php endif; ?>

    </div>

<?php elseif($tab === 'mitra'): ?>

    <?php $mitraAktif = $mitra->where('status_aktif',1); $avgRating = $mitra->count() ? round($mitra->avg('rating'),1) : 0; ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:20px;">
        <?php $__currentLoopData = [['Total Mitra',$mitra->count(),'🤝'],['Aktif',$mitraAktif->count(),'✅'],['Rating Rata-rata',$avgRating.' ⭐','🌟']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$lbl,$val,$icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card" style="text-align:center;padding:16px;"><div style="font-size:24px;margin-bottom:4px;"><?php echo e($icon); ?></div><div style="font-size:20px;font-weight:700;color:#0D530E;"><?php echo e($val); ?></div><div style="font-size:12px;color:#5a6b57;margin-top:2px;"><?php echo e($lbl); ?></div></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div style="display:flex;justify-content:flex-end;margin-bottom:14px;">
        <button onclick="openModal('modalTambahMitra')" style="background:#306D29;color:#fff;border-radius:10px;padding:10px 18px;font-weight:600;font-size:14px;border:none;cursor:pointer;">+ Daftarkan Mitra Baru</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px;">
        <?php $__currentLoopData = $mitra; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card" style="padding:18px;border-color:<?php echo e($m->status_aktif?'rgba(48,109,41,0.15)':'rgba(0,0,0,0.06)'); ?>;">
            <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:44px;height:44px;border-radius:50%;background:<?php echo e($m->status_aktif?'#306D29':'#E7E1B1'); ?>;display:flex;align-items:center;justify-content:center;color:<?php echo e($m->status_aktif?'#fff':'#5a6b57'); ?>;font-weight:700;font-size:16px;flex-shrink:0;"><?php echo e(strtoupper(substr($m->nama_mitra,0,1))); ?></div>
                    <div>
                        <p style="font-weight:700;color:#1a2e18;font-size:14px;"><?php echo e($m->nama_mitra); ?></p>
                        <p style="color:#5a6b57;font-size:12px;">📍 <?php echo e($m->alamat); ?></p>
                        <p style="color:#5a6b57;font-size:12px;">📞 <?php echo e($m->no_hp); ?> • <?php echo e($m->komoditas); ?></p>
                    </div>
                </div>
                <span style="font-size:11px;padding:3px 10px;border-radius:20px;font-weight:600;background:<?php echo e($m->status_aktif?'#d4e8d0':'#f0ede0'); ?>;color:<?php echo e($m->status_aktif?'#306D29':'#9ca3af'); ?>;flex-shrink:0;"><?php echo e($m->status_aktif?'Aktif':'Nonaktif'); ?></span>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-bottom:12px;">
                <?php $__currentLoopData = ['Order'=>$m->total_order.'x','Tepat Waktu'=>$m->persen_on_time.'%','Kualitas'=>$m->persen_kualitas.'%']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lbl=>$val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div style="background:#f7f4e8;border-radius:8px;padding:8px;text-align:center;"><p style="color:#9ca3af;font-size:10px;"><?php echo e($lbl); ?></p><p style="color:#0D530E;font-weight:700;font-size:13px;"><?php echo e($val); ?></p></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div style="display:flex;gap:8px;">
                <button onclick="editMitra(<?php echo e(json_encode($m)); ?>)" style="flex:1;background:#E7E1B1;color:#306D29;border-radius:8px;padding:8px;font-weight:600;font-size:12px;border:none;cursor:pointer;">✏️ Edit</button>
                <form method="POST" action="/kemitraan/mitra/<?php echo e($m->id_mitra); ?>" style="flex:1;margin:0;">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" style="width:100%;background:#fdecea;color:#d4183d;border-radius:8px;padding:8px;font-weight:600;font-size:12px;border:none;cursor:pointer;" onclick="return confirm('Hapus mitra <?php echo e($m->nama_mitra); ?>?')">🗑️ Hapus</button>
                </form>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

<?php elseif($tab === 'hutang'): ?>

    <?php $totalBelumBayar=$hutang->where('status_bayar','BELUM_BAYAR')->sum('jumlah_tagihan'); $totalLunas=$hutang->where('status_bayar','SUDAH_BAYAR')->sum('jumlah_tagihan'); ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
        <div class="card" style="text-align:center;padding:20px;border-color:rgba(212,24,61,0.2);"><div style="font-size:12px;color:#d4183d;font-weight:600;margin-bottom:4px;">Belum Dibayar</div><div style="font-size:22px;font-weight:700;color:#d4183d;">Rp <?php echo e(number_format($totalBelumBayar,0,',','.')); ?></div></div>
        <div class="card" style="text-align:center;padding:20px;border-color:rgba(48,109,41,0.2);"><div style="font-size:12px;color:#306D29;font-weight:600;margin-bottom:4px;">Sudah Dibayar</div><div style="font-size:22px;font-weight:700;color:#0D530E;">Rp <?php echo e(number_format($totalLunas,0,',','.')); ?></div></div>
    </div>
    <div class="card" style="padding:0;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;"><?php $__currentLoopData = ['Mitra','Tagihan','Jatuh Tempo','Status','Aksi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th style="text-align:left;padding:12px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;"><?php echo e($h); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $hutang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="table-row" style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:12px 16px;font-weight:600;font-size:14px;"><?php echo e($h->nama_mitra); ?></td>
                    <td style="padding:12px 16px;font-weight:700;color:#0D530E;">Rp <?php echo e(number_format($h->jumlah_tagihan,0,',','.')); ?></td>
                    <td style="padding:12px 16px;font-size:13px;color:#5a6b57;"><?php echo e(\Carbon\Carbon::parse($h->tanggal_jatuh_tempo)->format('d M Y')); ?></td>
                    <td style="padding:12px 16px;"><span style="font-size:12px;padding:4px 10px;border-radius:20px;font-weight:600;background:<?php echo e($h->status_bayar==='SUDAH_BAYAR'?'#d4e8d0':'#fdecea'); ?>;color:<?php echo e($h->status_bayar==='SUDAH_BAYAR'?'#306D29':'#d4183d'); ?>;"><?php echo e($h->status_bayar==='SUDAH_BAYAR'?'✓ Lunas':'Belum Bayar'); ?></span></td>
                    <td style="padding:12px 16px;">
                        <?php if($h->status_bayar==='BELUM_BAYAR'): ?>
                        <form method="POST" action="/kemitraan/hutang/<?php echo e($h->id_hutang); ?>/bayar" style="display:inline;"><?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                            <button type="submit" style="background:#306D29;color:#fff;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:600;border:none;cursor:pointer;" onclick="return confirm('Konfirmasi pelunasan?')">✓ Bayar</button>
                        </form>
                        <?php else: ?>
                        <span style="color:#9ca3af;font-size:12px;"><?php echo e($h->tanggal_lunas?\Carbon\Carbon::parse($h->tanggal_lunas)->format('d M Y'):'-'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;">Belum ada data hutang</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>

<?php elseif($tab === 'riwayat'): ?>

    <div class="card" style="padding:0;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #E7E1B1;font-size:15px;font-weight:700;color:#0D530E;">Riwayat Request Pengadaan</div>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#FBF5DD;border-bottom:2px solid #E7E1B1;"><?php $__currentLoopData = ['Tanggal','Bahan','Kebutuhan','Budget Maks','Status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th style="text-align:left;padding:10px 16px;font-size:12px;font-weight:700;color:#5a6b57;text-transform:uppercase;"><?php echo e($h); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $riwayatBroadcast; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $stMap=['AKTIF'=>['#d4e8d0','#306D29'],'DITUTUP'=>['#f0ede0','#5a6b57'],'SELESAI'=>['#dbeafe','#1d4ed8']]; $sc=$stMap[$r->status_broadcast]??['#f0ede0','#5a6b57']; ?>
                <tr class="table-row" style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:10px 16px;font-size:13px;color:#5a6b57;"><?php echo e(\Carbon\Carbon::parse($r->tanggal_kirim)->format('d M Y H:i')); ?></td>
                    <td style="padding:10px 16px;font-weight:600;font-size:13px;"><?php echo e($r->nama_bahan); ?></td>
                    <td style="padding:10px 16px;font-size:13px;"><?php echo e($r->jumlah_dibutuhkan); ?> <?php echo e($r->satuan); ?></td>
                    <td style="padding:10px 16px;font-size:13px;">Rp <?php echo e(number_format($r->harga_target,0,',','.')); ?>/<?php echo e($r->satuan); ?></td>
                    <td style="padding:10px 16px;"><span style="font-size:12px;padding:3px 10px;border-radius:20px;font-weight:600;background:<?php echo e($sc[0]); ?>;color:<?php echo e($sc[1]); ?>;"><?php echo e($r->status_broadcast); ?></span></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" style="padding:40px;text-align:center;color:#9ca3af;">Belum ada riwayat</td></tr><?php endif; ?>
            </tbody>
        </table>
        <?php if($riwayatBroadcast->hasPages()): ?>
        <div style="padding:12px 16px;border-top:1px solid #E7E1B1;"><?php echo e($riwayatBroadcast->appends(request()->query())->links()); ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

</div>




<div id="modalBroadcast" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Form Request Restock</h2><p style="color:#5a6b57;font-size:13px;">Permintaan pengadaan ke mitra pemasok</p></div>
            <button onclick="closeModal('modalBroadcast')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button>
        </div>
        <form method="POST" action="/kemitraan/broadcast" style="display:grid;gap:14px;">
            <?php echo csrf_field(); ?>
            <div>
                <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Bahan yang Dibutuhkan <span style="color:#d4183d;">*</span></label>
                <select name="id_bahan" id="select_bahan" class="form-input" onchange="updateBahanInfo()" required>
                    <option value="">-- Pilih Bahan --</option>
                    <?php $__currentLoopData = $semuaBahan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($b->id_bahan); ?>" data-satuan="<?php echo e($b->satuan); ?>" data-stok="<?php echo e($b->jumlah_stok); ?>" data-min="<?php echo e($b->batas_minimum); ?>">
                        <?php echo e($b->nama_bahan); ?> (stok: <?php echo e($b->jumlah_stok); ?> <?php echo e($b->satuan); ?>)
                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <input type="datetime-local" name="batas_respon" class="form-input" required min="<?php echo e(now()->addHour()->format('Y-m-d\TH:i')); ?>">
            </div>
            <div>
                <label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan <span style="font-weight:400;color:#9ca3af;">(opsional)</span></label>
                <textarea name="catatan" class="form-input" rows="2" placeholder="Spesifikasi kualitas, dll."></textarea>
            </div>
            <div style="background:#FBF5DD;border-radius:10px;padding:14px;">
                <label style="font-size:13px;font-weight:700;color:#1a2a1a;display:block;margin-bottom:10px;">Mode Pengiriman</label>
                <div style="display:grid;gap:8px;">
                    <label style="display:flex;align-items:start;gap:10px;cursor:pointer;padding:10px;border:2px solid #306D29;border-radius:8px;background:#f0f9f0;">
                        <input type="radio" name="mode_broadcast" value="manual" checked style="margin-top:2px;">
                        <div><p style="font-weight:600;color:#0D530E;font-size:13px;">✋ Manual (Disarankan)</p><p style="font-size:12px;color:#5a6b57;">Sistem siapkan link, kamu kirim manual via WA satu-satu. Aman dari ban WA.</p></div>
                    </label>
                    <label style="display:flex;align-items:start;gap:10px;cursor:pointer;padding:10px;border:2px solid #E7E1B1;border-radius:8px;">
                        <input type="radio" name="mode_broadcast" value="otomatis" style="margin-top:2px;">
                        <div><p style="font-weight:600;color:#5a6b57;font-size:13px;">🤖 Otomatis (Fonnte)</p><p style="font-size:12px;color:#9ca3af;">WA otomatis dengan delay 15-30 detik. Butuh token Fonnte.</p></div>
                    </label>
                </div>
            </div>
            
            <div id="mitraCheckboxSection">
                <label style="font-size:13px;font-weight:700;color:#1a2a1a;display:block;margin-bottom:8px;">
                    Pilih Mitra yang Akan Dikirimi
                    <span style="font-weight:400;color:#9ca3af;font-size:12px;">(biarkan kosong untuk filter otomatis berdasarkan komoditas)</span>
                </label>
                <div style="display:grid;gap:6px;max-height:180px;overflow-y:auto;padding:10px;background:#f7f4e8;border-radius:8px;">
                    <?php $__currentLoopData = $mitra->where('status_aktif',1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:6px 8px;border-radius:6px;" onmouseover="this.style.background='#e8f5e8'" onmouseout="this.style.background='transparent'">
                        <input type="checkbox" name="mitra_dipilih[]" value="<?php echo e($m->id_mitra); ?>"
                            style="width:16px;height:16px;accent-color:#306D29;cursor:pointer;"
                            class="mitra-cb">
                        <div>
                            <p style="font-size:13px;font-weight:600;color:#1a2a1a;line-height:1.2;"><?php echo e($m->nama_mitra); ?></p>
                            <p style="font-size:11px;color:#5a6b57;"><?php echo e($m->komoditas); ?></p>
                        </div>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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


<div id="modalTambahMitra" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;"><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Daftarkan Mitra Baru</h2><button onclick="closeModal('modalTambahMitra')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button></div>
        <form method="POST" action="/kemitraan/mitra" style="display:grid;gap:14px;">
            <?php echo csrf_field(); ?>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Nama Mitra <span style="color:#d4183d;">*</span></label><input type="text" name="nama_mitra" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">No. HP / WhatsApp <span style="color:#d4183d;">*</span></label><input type="text" name="no_hp" class="form-input" required><p style="font-size:11px;color:#9ca3af;margin-top:3px;">Format: 081234567890</p></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Alamat <span style="color:#d4183d;">*</span></label><input type="text" name="alamat" class="form-input" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Komoditas / Produk <span style="color:#d4183d;">*</span></label><input type="text" name="komoditas" class="form-input" placeholder="cth: Biji Kopi, Susu Segar, Telur" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan</label><textarea name="catatan" class="form-input" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><button type="button" onclick="closeModal('modalTambahMitra')" style="padding:11px;border:2px solid #E7E1B1;border-radius:10px;background:#fff;color:#5a6b57;font-weight:600;cursor:pointer;">Batal</button><button type="submit" style="padding:11px;background:#306D29;color:#fff;border-radius:10px;font-weight:700;border:none;cursor:pointer;">Simpan</button></div>
        </form>
    </div>
</div>


<div id="modalEditMitra" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;"><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Edit Mitra</h2><button onclick="closeModal('modalEditMitra')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button></div>
        <form id="formEditMitra" method="POST" action="" style="display:grid;gap:14px;">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
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


<?php if($broadcastAktif): ?>
<div id="modalPenawaranManual" class="modal-overlay hidden">
    <div class="modal-box">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;"><h2 style="color:#0D530E;font-size:17px;font-weight:700;">Input Penawaran Manual</h2><button onclick="closeModal('modalPenawaranManual')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#9ca3af;">✕</button></div>
        <p style="color:#5a6b57;font-size:13px;margin-bottom:16px;">Untuk mitra yang merespons via telepon.</p>
        <form method="POST" action="/kemitraan/penawaran/manual" style="display:grid;gap:14px;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id_broadcast" value="<?php echo e($broadcastAktif->id_broadcast); ?>">
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Mitra <span style="color:#d4183d;">*</span></label>
                <select name="id_mitra" class="form-input" required><option value="">-- Pilih Mitra --</option><?php $__currentLoopData = $mitra->where('status_aktif',1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($m->id_mitra); ?>"><?php echo e($m->nama_mitra); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Harga/<?php echo e($broadcastAktif->satuan); ?> (Rp) <span style="color:#d4183d;">*</span></label><input type="number" name="harga_satuan" class="form-input" min="1" required></div>
                <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Stok (<?php echo e($broadcastAktif->satuan); ?>) <span style="color:#d4183d;">*</span></label><input type="number" name="jumlah_tersedia" class="form-input" min="0.1" step="0.1" required></div>
            </div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Est. Pengiriman <span style="color:#d4183d;">*</span></label><input type="date" name="estimasi_kirim" class="form-input" min="<?php echo e(now()->addDay()->format('Y-m-d')); ?>" required></div>
            <div><label style="font-size:13px;font-weight:600;color:#1a2a1a;display:block;margin-bottom:6px;">Catatan</label><textarea name="catatan_mitra" class="form-input" rows="2"></textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;"><button type="button" onclick="closeModal('modalPenawaranManual')" style="padding:11px;border:2px solid #E7E1B1;border-radius:10px;background:#fff;color:#5a6b57;font-weight:600;cursor:pointer;">Batal</button><button type="submit" style="padding:11px;background:#306D29;color:#fff;border-radius:10px;font-weight:700;border:none;cursor:pointer;">Simpan</button></div>
        </form>
    </div>
</div>
<?php endif; ?>

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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/kemitraan/index.blade.php ENDPATH**/ ?>