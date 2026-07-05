# Cafe CNS — Sistem Informasi Manajemen (v2, arsitektur baru)

Dua project terpisah sesuai `RANCANGAN APLIKASI BERBASIS WEB`:

- **cns-backend.zip** — Laravel 11 REST API, autentikasi JWT, RBAC 3 role
  (owner/admin/kasir), skema database baru dan berbeda total dari project
  lama `cns-laravel-v2` (tabel & relasi dirombak, nama database boleh tetap
  `cns_db` sesuai arahanmu).
- **cns-frontend.zip** — React + TypeScript + Tailwind (Vite), memanggil API
  di atas lewat axios, JWT disimpan di localStorage, sidebar dengan menu
  terkunci sesuai role, dan seluruh 8 modul di laporan (Auth, Dashboard,
  Pelanggan, Transaksi/POS, Inventori, Portal Kemitraan, Purchase Orders,
  Laporan & Analitik).

## Cara menjalankan

**1. Backend**
```bash
cd cns-backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
# atur DB_* di .env (MySQL, nama database bebas — default cns_db)
php artisan migrate --seed
php artisan serve   # http://localhost:8000
```

**2. Frontend** (di terminal terpisah)
```bash
cd cns-frontend
npm install
cp .env.example .env   # pastikan VITE_API_BASE_URL mengarah ke backend
npm run dev             # http://localhost:5173
```

Login dengan salah satu akun demo (dibuat oleh seeder):

| Role  | Username | Password  |
|-------|----------|-----------|
| Owner | owner    | owner2026 |
| Admin | admin    | admin2026 |
| Kasir | kasir    | kasir2026 |

## Catatan penting
- Kedua project sudah lulus pengecekan sintaks PHP (`php -l`) dan build
  TypeScript (`tsc -b` + `vite build`) di lingkungan ini.
- Karena sandbox tidak punya akses ke Packagist, `composer install` belum
  bisa dijalankan/diverifikasi di sini — jalankan di komputer/servermu yang
  terhubung internet.
- Database sama sekali baru: nama tabel, kolom, dan relasi berbeda total
  dari `cns-laravel-v2` (mis. `tb_mitra` → `partners`, `tb_hutang` →
  `purchase_order_payments`, ditambah kolom loyalitas, segmentasi,
  min/max stok, skor QC, dll sesuai kebutuhan fitur di laporan).
- README masing-masing folder berisi detail endpoint & struktur lebih lanjut.
