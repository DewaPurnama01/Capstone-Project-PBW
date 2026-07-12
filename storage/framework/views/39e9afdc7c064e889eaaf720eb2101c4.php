<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Penawaran — Cafe CNS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body style="background: #f7f4ea; min-height: 100vh; padding: 20px;">
<div style="max-width: 540px; margin: 0 auto;">

    
    <div style="background: linear-gradient(135deg, #0c4a0d, #1a6b1c); border-radius: 16px; padding: 24px; margin-bottom: 20px; text-align: center;">
        <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
            <svg width="28" height="28" fill="none" viewBox="0 0 24 24"><path d="M17 8h1a4 4 0 0 1 0 8h-1" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1 style="color:#fff;font-size:1.3rem;font-weight:700;margin-bottom:4px;">Form Penawaran</h1>
        <p style="color:rgba(255,255,255,0.75);font-size:0.85rem;">Cafe Catch New Serenity (CNS)</p>
    </div>

    
    <div style="background:#fff;border-radius:16px;padding:20px;margin-bottom:16px;border:1px solid #E7E1B1;">
        <p style="color:#5a6b57;font-size:0.875rem;">Halo, <strong style="color:#0D530E;"><?php echo e($mitra->nama_mitra); ?></strong>!</p>
        <p style="color:#5a6b57;font-size:0.85rem;margin-top:6px;line-height:1.6;">
            Cafe CNS mengundang Anda untuk mengajukan penawaran bahan baku berikut. Isi form di bawah sesuai dengan ketersediaan dan harga terbaik Anda.
        </p>
    </div>

    
    <div style="background:#d4e8d0;border-radius:16px;padding:20px;margin-bottom:16px;border:1px solid #a8d4a0;">
        <h3 style="color:#0D530E;font-weight:700;font-size:0.95rem;margin-bottom:12px;">📋 Detail Kebutuhan Cafe CNS</h3>
        <div class="grid grid-cols-2 gap-3">
            <?php $__currentLoopData = [
                ['Bahan', $broadcast->nama_bahan],
                ['Jumlah Dibutuhkan', number_format($broadcast->jumlah_dibutuhkan, 2, ',', '.') . ' ' . $broadcast->satuan],
                ['Budget Maksimal', 'Rp ' . number_format($broadcast->harga_target, 0, ',', '.') . '/' . $broadcast->satuan],
                ['Batas Respon', \Carbon\Carbon::parse($broadcast->batas_respon)->format('d M Y, H:i') . ' WIB'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$lbl, $val]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="background:rgba(255,255,255,0.6);border-radius:10px;padding:10px;">
                <p style="color:#5a6b57;font-size:0.72rem;margin-bottom:2px;"><?php echo e($lbl); ?></p>
                <p style="color:#0D530E;font-weight:600;font-size:0.85rem;"><?php echo e($val); ?></p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if($broadcast->catatan): ?>
        <div style="margin-top:12px;background:rgba(255,255,255,0.6);border-radius:10px;padding:10px;">
            <p style="color:#5a6b57;font-size:0.72rem;margin-bottom:2px;">Catatan dari Cafe</p>
            <p style="color:#0D530E;font-size:0.85rem;"><?php echo e($broadcast->catatan); ?></p>
        </div>
        <?php endif; ?>
    </div>

    
    <?php if($errors->any()): ?>
    <div style="background:#ffe4e4;color:#d4183d;border:1px solid #f5c0c0;border-radius:12px;padding:14px;margin-bottom:16px;">
        <p style="font-weight:600;font-size:0.85rem;margin-bottom:6px;">Perbaiki kesalahan berikut:</p>
        <ul class="list-disc list-inside space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li style="font-size:0.8rem;"><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
    <?php endif; ?>

    
    <form method="POST" action="/form-penawaran/<?php echo e($token); ?>" style="background:#fff;border-radius:16px;padding:20px;border:1px solid #E7E1B1;">
        <?php echo csrf_field(); ?>
        <h3 style="color:#0D530E;font-weight:700;font-size:0.95rem;margin-bottom:16px;">✏️ Penawaran Anda</h3>

        <div class="space-y-4">
            <div>
                <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">
                    Harga per <?php echo e($broadcast->satuan); ?> (Rp) <span style="color:#d4183d;">*</span>
                </label>
                <input type="number" name="harga_satuan" value="<?php echo e(old('harga_satuan')); ?>"
                       style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                       placeholder="cth: 145000" min="1" required>
                <p style="color:#9ca3af;font-size:0.73rem;margin-top:4px;">
                    Budget cafe: Rp <?php echo e(number_format($broadcast->harga_target, 0, ',', '.')); ?>/<?php echo e($broadcast->satuan); ?>

                </p>
            </div>

            <div>
                <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">
                    Stok Tersedia (<?php echo e($broadcast->satuan); ?>) <span style="color:#d4183d;">*</span>
                </label>
                <input type="number" name="jumlah_tersedia" value="<?php echo e(old('jumlah_tersedia')); ?>"
                       style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                       placeholder="cth: 20" step="0.1" min="0.1" required>
            </div>

            <div>
                <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">
                    Estimasi Tanggal Pengiriman <span style="color:#d4183d;">*</span>
                </label>
                <input type="date" name="estimasi_kirim" value="<?php echo e(old('estimasi_kirim')); ?>"
                       style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                       min="<?php echo e(date('Y-m-d', strtotime('+1 day'))); ?>" required>
            </div>

            <div>
                <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">
                    Catatan Tambahan (opsional)
                </label>
                <textarea name="catatan_mitra" rows="3"
                          style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;resize:vertical;box-sizing:border-box;"
                          placeholder="cth: kualitas grade A, bisa negosiasi harga untuk jumlah besar, dll."><?php echo e(old('catatan_mitra')); ?></textarea>
            </div>
        </div>

        <button type="submit"
                style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:12px;font-weight:600;font-size:0.95rem;cursor:pointer;border:none;margin-top:16px;">
            Kirim Penawaran →
        </button>
        <p style="text-align:center;color:#9ca3af;font-size:0.75rem;margin-top:10px;">
            Form ini hanya bisa disubmit sekali. Pastikan data sudah benar.
        </p>
    </form>

    <p style="text-align:center;color:#9ca3af;font-size:0.72rem;margin-top:20px;">
        © 2026 Cafe Catch New Serenity · Portal Kemitraan Rantai Pasok
    </p>
</div>
</body>
</html>
<?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/kemitraan/penawaran-public-form.blade.php ENDPATH**/ ?>