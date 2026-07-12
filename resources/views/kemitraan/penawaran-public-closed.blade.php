<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Tidak Tersedia — Cafe CNS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body style="background: #f7f4ea; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;">
<div style="max-width: 440px; width: 100%; text-align: center;">
    <div style="background:#fff;border-radius:20px;padding:40px 32px;border:1px solid #E7E1B1;">
        <div style="width:72px;height:72px;background:#ffe4e4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem;">🔒</div>
        <h2 style="color:#d4183d;font-size:1.4rem;font-weight:700;margin-bottom:10px;">Form Tidak Tersedia</h2>
        <p style="color:#5a6b57;font-size:0.9rem;line-height:1.6;">
            {{ $alasan ?? 'Form penawaran ini sudah tidak aktif atau link tidak valid.' }}
        </p>
        <div style="background:#FBF5DD;border-radius:12px;padding:14px;margin-top:20px;">
            <p style="color:#5a6b57;font-size:0.82rem;">
                Jika ada pertanyaan, silakan hubungi Cafe CNS secara langsung.
            </p>
        </div>
    </div>
    <p style="color:#9ca3af;font-size:0.72rem;margin-top:20px;">
        © 2026 Cafe Catch New Serenity · Portal Kemitraan Rantai Pasok
    </p>
</div>
</body>
</html>
