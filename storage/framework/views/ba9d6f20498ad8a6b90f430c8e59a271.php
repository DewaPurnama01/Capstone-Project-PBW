<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penawaran Terkirim — Cafe CNS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body style="background: #f7f4ea; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
<div style="max-width: 440px; width: 100%; text-align: center;">

    <?php if($sudahSubmit): ?>
    <div style="background:#fff;border-radius:20px;padding:40px 32px;border:1px solid #E7E1B1;">
        <div style="width:72px;height:72px;background:#fff3cd;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem;">ℹ️</div>
        <h2 style="color:#8B6914;font-size:1.4rem;font-weight:700;margin-bottom:10px;">Sudah Dikirim</h2>
        <p style="color:#5a6b57;font-size:0.9rem;line-height:1.6;">
            Anda sudah mengirimkan penawaran melalui link ini sebelumnya.
            Silakan tunggu konfirmasi dari Cafe CNS.
        </p>
    </div>
    <?php else: ?>
    <div style="background:#fff;border-radius:20px;padding:40px 32px;border:1px solid #E7E1B1;">
        <div style="width:72px;height:72px;background:#d4e8d0;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem;">✅</div>
        <h2 style="color:#0D530E;font-size:1.4rem;font-weight:700;margin-bottom:10px;">Penawaran Terkirim!</h2>
        <p style="color:#5a6b57;font-size:0.9rem;line-height:1.6;">
            Terima kasih! Penawaran Anda telah diterima oleh sistem Cafe CNS.
            Tim kami akan mengevaluasi dan menghubungi Anda jika penawaran dipilih.
        </p>
        <div style="background:#d4e8d0;border-radius:12px;padding:14px;margin-top:20px;">
            <p style="color:#0D530E;font-size:0.82rem;font-weight:500;">
                💡 Jika penawaran Anda terpilih, Anda akan menerima dokumen Purchase Order (PO) via WhatsApp.
            </p>
        </div>
    </div>
    <?php endif; ?>

    <p style="color:#9ca3af;font-size:0.72rem;margin-top:20px;">
        © 2026 Cafe Catch New Serenity · Portal Kemitraan Rantai Pasok
    </p>
</div>
</body>
</html>
<?php /**PATH C:\cns-laravel-v2\cns-fixed\resources\views/kemitraan/penawaran-public-success.blade.php ENDPATH**/ ?>