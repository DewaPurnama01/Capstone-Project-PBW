<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Cafe CNS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body style="background: #FBF5DD; min-height: 100vh;" class="flex">

    
    <div class="hidden lg:flex flex-col justify-between p-12" style="width: 420px; background: linear-gradient(160deg, #0c4a0d 0%, #1a6b1c 55%, #246b24 100%); flex-shrink: 0;">
        <div class="flex items-center gap-3">
            <div style="width:40px;height:40px;background:rgba(255,255,255,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M17 8h1a4 4 0 0 1 0 8h-1" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z" stroke="#FBF5DD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <p style="color:#fff;font-weight:700;font-size:1.1rem;">Cafe CNS</p>
                <p style="color:rgba(255,255,255,0.5);font-size:0.8rem;">Catch New Serenity</p>
            </div>
        </div>

        <div>
            <h1 style="color:#fff;font-size:2rem;font-weight:700;line-height:1.3;" class="mb-4">Sistem Informasi<br>Manajemen & Portal<br>Kemitraan</h1>
            <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;line-height:1.6;">Menghubungkan Cafe CNS dengan petani kopi lokal secara langsung, transparan, dan efisien.</p>

            <div class="mt-8 space-y-3">
                <?php $__currentLoopData = [['☕', 'Supply Chain', 'Pengadaan biji kopi langsung dari petani'], ['📊', 'Dashboard CRM', 'Monitor operasional café secara real-time'], ['🔔', 'Broadcast WA', 'Notifikasi otomatis ke seluruh jaringan petani']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$icon, $title, $desc]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-start gap-3">
                    <span class="text-2xl"><?php echo e($icon); ?></span>
                    <div>
                        <p style="color:#fff;font-weight:600;font-size:0.85rem;"><?php echo e($title); ?></p>
                        <p style="color:rgba(255,255,255,0.5);font-size:0.78rem;"><?php echo e($desc); ?></p>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <p style="color:rgba(255,255,255,0.3);font-size:0.72rem;">© 2026 Cafe CNS · Sistem Informasi Manajemen UMKM</p>
    </div>

    
    <div class="flex-1 flex items-center justify-center p-8">
        <div style="width: 100%; max-width: 400px;">
            <div class="lg:hidden mb-8 text-center">
                <h1 style="color:#0D530E;font-size:1.5rem;font-weight:700;">Cafe CNS</h1>
                <p style="color:#5a6b57;font-size:0.85rem;">Sistem Informasi Manajemen</p>
            </div>

            <h2 style="color:#0D530E;font-size:1.5rem;font-weight:700;" class="mb-1">Selamat datang!</h2>
            <p style="color:#5a6b57;font-size:0.875rem;" class="mb-6">Masukkan kredensial Anda untuk mengakses sistem.</p>

            
            <?php if(session('success')): ?>
            <div style="background:#d4e8d0;color:#0D530E;border:1px solid #a8d4a0;border-radius:10px;padding:12px 16px;" class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>

            
            <?php if($errors->has('login')): ?>
            <div style="background:#ffe4e4;color:#d4183d;border:1px solid #f5c0c0;border-radius:10px;padding:12px 16px;" class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <?php echo e($errors->first('login')); ?>

            </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
            <div style="background:#ffe4e4;color:#d4183d;border:1px solid #f5c0c0;border-radius:10px;padding:12px 16px;" class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                <?php echo e(session('error')); ?>

            </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Username</label>
                    <input type="text" name="username" value="<?php echo e(old('username')); ?>"
                           style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                           placeholder="Masukkan username Anda" required autofocus>
                </div>
                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Password</label>
                    <input type="password" name="password"
                           style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                           placeholder="••••••••" required>
                </div>
                <button type="submit"
                        style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:12px;font-weight:600;font-size:0.95rem;cursor:pointer;border:none;">
                    Masuk ke Sistem
                </button>
            </form>

            <p style="text-align:center;margin-top:20px;color:#5a6b57;font-size:0.875rem;">
                Belum punya akun?
                <a href="/register" style="color:#306D29;font-weight:600;text-decoration:none;">Daftar sekarang →</a>
            </p>

            
            <div style="background:#FBF5DD;border:1px solid #E7E1B1;border-radius:12px;padding:14px;margin-top:24px;">
                <p style="color:#5a6b57;font-size:0.75rem;font-weight:600;margin-bottom:8px;">ℹ️ INFORMASI:</p>
                <p style="color:#5a6b57;font-size:0.78rem;line-height:1.6;">
                    Jika belum memiliki akun, klik <strong>"Daftar sekarang"</strong> untuk membuat akun baru.
                    Owner hanya boleh ada satu. Admin dan Kasir dapat didaftarkan sesuai kebutuhan.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/auth/login.blade.php ENDPATH**/ ?>