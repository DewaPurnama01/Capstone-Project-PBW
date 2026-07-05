# Cafe CNS — Backend API (Laravel + JWT)

REST API untuk Sistem Informasi Manajemen Cafe CNS, dibangun sesuai
`RANCANGAN APLIKASI BERBASIS WEB`. Backend ini **API-only** (tanpa Blade),
dikonsumsi oleh frontend React terpisah (folder `cns-frontend`).

## Arsitektur singkat
- Laravel 11, autentikasi **JWT** (`php-open-source-saver/jwt-auth`)
- RBAC 3 role: `owner`, `admin`, `kasir` — lihat `routes/api.php`
- Skema database baru (lihat `database/migrations`), berbeda total dari
  project lama `cns-laravel-v2` (nama tabel, kolom, dan relasi dirombak)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

# Sesuaikan DB_* di .env (nama database boleh tetap cns_db, skema sudah baru)
php artisan migrate --seed

php artisan serve   # http://localhost:8000
```

## Akun demo (dibuat oleh seeder)

| Role  | Username | Password   |
|-------|----------|------------|
| Owner | owner    | owner2026  |
| Admin | admin    | admin2026  |
| Kasir | kasir    | kasir2026  |

## Endpoint utama

- `POST /api/auth/login` — login, mengembalikan JWT
- `GET  /api/auth/me` — profil user aktif
- `GET  /api/dashboard` — KPI + chart data
- `GET/POST /api/customers` — Manajemen Pelanggan (owner, kasir)
- `GET/POST /api/transactions` — Transaksi & POS (owner, kasir)
- `GET/POST /api/inventory` — Manajemen Inventori (owner, admin)
- `GET/POST /api/partnership/*` — Portal Kemitraan Petani (owner, admin)
- `GET/POST /api/purchase-orders/*` — Purchase Orders (owner, admin)
- `GET /api/reports` — Laporan & Analitik (owner saja)

Semua endpoint terproteksi butuh header:
```
Authorization: Bearer <token>
```

Jika role tidak sesuai, server membalas `403` dengan `code: ACCESS_LOCKED`
— dipetakan di frontend menjadi halaman "Akses Terkunci".
