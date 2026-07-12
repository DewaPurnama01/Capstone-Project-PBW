<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun — Cafe CNS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>* { font-family: 'Inter', sans-serif; }</style>
</head>
<body style="background: #FBF5DD; min-height: 100vh;" class="flex">

    {{-- LEFT: Brand Panel --}}
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
            <h1 style="color:#fff;font-size:2rem;font-weight:700;line-height:1.3;" class="mb-4">Buat Akun<br>Pengguna Baru</h1>
            <p style="color:rgba(255,255,255,0.65);font-size:0.9rem;line-height:1.6;">Daftarkan akun staf Cafe CNS untuk mengakses Sistem Informasi Manajemen & Portal Kemitraan Rantai Pasok.</p>

            <div class="mt-8 space-y-4">
                @foreach([
                    ['👑', 'Owner', 'Akses penuh: dashboard, laporan, kemitraan, semua modul. Hanya boleh 1 akun Owner.'],
                    ['⚙️', 'Admin', 'Akses: inventori, portal kemitraan, purchase order, QC barang masuk.'],
                    ['🧾', 'Kasir', 'Akses: transaksi penjualan, manajemen pelanggan (CRM).'],
                ] as [$icon, $role, $desc])
                <div class="flex items-start gap-3">
                    <span class="text-xl mt-0.5">{{ $icon }}</span>
                    <div>
                        <p style="color:#fff;font-weight:600;font-size:0.85rem;">{{ $role }}</p>
                        <p style="color:rgba(255,255,255,0.5);font-size:0.75rem;line-height:1.5;">{{ $desc }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <p style="color:rgba(255,255,255,0.3);font-size:0.72rem;">© 2026 Cafe CNS · Sistem Informasi Manajemen UMKM</p>
    </div>

    {{-- RIGHT: Register Form --}}
    <div class="flex-1 flex items-center justify-center p-8 overflow-y-auto">
        <div style="width: 100%; max-width: 440px;">

            <div class="lg:hidden mb-8 text-center">
                <h1 style="color:#0D530E;font-size:1.5rem;font-weight:700;">Cafe CNS</h1>
                <p style="color:#5a6b57;font-size:0.85rem;">Daftar Akun Baru</p>
            </div>

            <h2 style="color:#0D530E;font-size:1.5rem;font-weight:700;" class="mb-1">Buat Akun Baru</h2>
            <p style="color:#5a6b57;font-size:0.875rem;" class="mb-6">Isi data di bawah untuk mendaftar sebagai pengguna sistem.</p>

            {{-- Success --}}
            @if(session('success'))
            <div style="background:#d4e8d0;color:#0D530E;border:1px solid #a8d4a0;border-radius:10px;padding:12px 16px;" class="mb-4 flex items-center gap-2">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ session('success') }}
            </div>
            @endif

            {{-- Errors --}}
            @if($errors->any())
            <div style="background:#ffe4e4;color:#d4183d;border:1px solid #f5c0c0;border-radius:10px;padding:12px 16px;" class="mb-4">
                <p style="font-weight:600;font-size:0.85rem;margin-bottom:6px;">Perbaiki kesalahan berikut:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li style="font-size:0.82rem;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="/register" class="space-y-4">
                @csrf

                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Nama Lengkap <span style="color:#d4183d;">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           style="width:100%;border:1px solid {{ $errors->has('name') ? '#d4183d' : '#E7E1B1' }};background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                           placeholder="cth: Budi Santoso" required>
                </div>

                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Username <span style="color:#d4183d;">*</span></label>
                    <input type="text" name="username" value="{{ old('username') }}"
                           style="width:100%;border:1px solid {{ $errors->has('username') ? '#d4183d' : '#E7E1B1' }};background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                           placeholder="cth: budisantoso (huruf & angka, min. 3 karakter)" required>
                    <p style="color:#9ca3af;font-size:0.73rem;margin-top:4px;">Hanya huruf dan angka, tanpa spasi.</p>
                </div>

                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Role / Jabatan <span style="color:#d4183d;">*</span></label>
                    <select name="role"
                            style="width:100%;border:1px solid {{ $errors->has('role') ? '#d4183d' : '#E7E1B1' }};background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;" required>
                        <option value="">-- Pilih Role --</option>
                        <option value="Owner"  {{ old('role') === 'Owner'  ? 'selected' : '' }}>👑 Owner (Manajer Operasional)</option>
                        <option value="Admin"  {{ old('role') === 'Admin'  ? 'selected' : '' }}>⚙️ Admin (Inventori & Kemitraan)</option>
                        <option value="Kasir"  {{ old('role') === 'Kasir'  ? 'selected' : '' }}>🧾 Kasir (Transaksi & Pelanggan)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Password <span style="color:#d4183d;">*</span></label>
                    <input type="password" name="password"
                           style="width:100%;border:1px solid {{ $errors->has('password') ? '#d4183d' : '#E7E1B1' }};background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                           placeholder="Minimal 6 karakter" required>
                </div>

                <div>
                    <label style="display:block;color:#5a6b57;font-size:0.85rem;font-weight:500;margin-bottom:6px;">Konfirmasi Password <span style="color:#d4183d;">*</span></label>
                    <input type="password" name="password_confirmation"
                           style="width:100%;border:1px solid #E7E1B1;background:#FBF5DD;border-radius:10px;padding:10px 14px;font-size:0.9rem;outline:none;box-sizing:border-box;"
                           placeholder="Ulangi password" required>
                </div>

                <button type="submit"
                        style="width:100%;background:#306D29;color:#fff;border-radius:10px;padding:12px;font-weight:600;font-size:0.95rem;cursor:pointer;border:none;">
                    Buat Akun
                </button>
            </form>

            <p style="text-align:center;margin-top:20px;color:#5a6b57;font-size:0.875rem;">
                Sudah punya akun?
                <a href="/login" style="color:#306D29;font-weight:600;text-decoration:none;">Masuk di sini →</a>
            </p>
        </div>
    </div>
</body>
</html>
