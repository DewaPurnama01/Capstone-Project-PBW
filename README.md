# Sistem Informasi Manajemen & Portal Kemitraan Cafe CNS
**Catch New Serenity (CNS) — Supply Chain Management System**

---

## 📋 Deskripsi

Sistem informasi manajemen untuk UMKM Cafe CNS yang mencakup:
- **Portal Kemitraan Rantai Pasok** — pengadaan biji kopi langsung dari petani lokal (tanpa perantara)
- **Manajemen Inventori** — monitoring stok bahan baku secara real-time
- **CRM Pelanggan** — manajemen data pelanggan dan poin loyalitas
- **Transaksi POS** — pencatatan transaksi penjualan harian
- **Laporan Manajerial** — laporan pendapatan, performa supplier, hutang

---

## 🚀 Cara Setup (Fresh Install)

### Prasyarat
- PHP >= 8.2
- MySQL / MariaDB
- Composer

### Langkah 1: Setup Database

**Opsi A — Via MySQL Workbench / phpMyAdmin:**
```
File > Open SQL Script > pilih database/cns_db_setup.sql
Klik Execute (Ctrl+Shift+Enter)
```

**Opsi B — Via Terminal:**
```bash
mysql -u root -p < database/cns_db_setup.sql
```

**Opsi C — Via Laravel Migration:**
```bash
php artisan migrate:fresh
```

### Langkah 2: Konfigurasi .env

Salin file `.env.example` menjadi `.env` dan sesuaikan:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cns_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

Generate app key (jika belum ada):
```bash
php artisan key:generate
```

### Langkah 3: Setup Storage (untuk upload foto QC)
```bash
php artisan storage:link
```

### Langkah 4: Jalankan Server
```bash
php artisan serve
```

Akses di: **http://localhost:8000**

---

## 🔐 Alur Penggunaan (Mulai dari Nol)

### Step 1: Registrasi Akun Owner
1. Buka `http://localhost:8000/register`
2. Isi nama, username, password
3. Pilih role **Owner** (hanya boleh 1 Owner)
4. Klik "Buat Akun"

### Step 2: Login dan Tambah Staff
1. Login sebagai Owner di `/login`
2. Buka `/register` lagi untuk tambah akun **Admin** dan **Kasir**

### Step 3: Setup Inventori
1. Login sebagai Owner atau Admin
2. Buka menu **Inventori**
3. Tambahkan bahan baku yang dikelola cafe (Biji Kopi, Susu, dll.)

### Step 4: Daftarkan Petani Mitra
1. Buka menu **Portal Kemitraan** → tab **Petani Mitra**
2. Klik "Daftarkan Petani Baru"
3. Isi data petani: nama, no. HP/WhatsApp, alamat, komoditas

### Step 5: Alur Pengadaan Biji Kopi
1. Buka **Portal Kemitraan** → **Request Restock Biji Kopi**
2. Isi kebutuhan dan budget → klik **Broadcast ke Petani**
3. Sistem akan men-generate link unik per petani (dikirim via WA / log)
4. Petani buka link dan isi form penawaran (tanpa perlu login)
5. Owner evaluasi penawaran → klik **Pilih & Generate PO**
6. Admin tandai PO sebagai **Dikirim** ketika barang dalam perjalanan
7. Admin lakukan **QC Barang** ketika barang tiba
8. Jika lolos QC → stok otomatis bertambah + hutang tercatat
9. Owner konfirmasi bayar setelah transfer ke petani

### Step 6: Kelola Pelanggan & Transaksi
- **Kasir/Owner**: menu Pelanggan → tambah pelanggan
- **Kasir/Owner**: menu Transaksi → catat transaksi penjualan

---

## 👥 Peran Pengguna

| Role | Akses |
|------|-------|
| **Owner** | Semua menu: Dashboard, Pelanggan, Transaksi, Inventori, Portal Kemitraan, Purchase Orders, Laporan |
| **Admin** | Inventori, Portal Kemitraan (termasuk QC), Purchase Orders, Dashboard |
| **Kasir** | Transaksi, Pelanggan, Dashboard |

---

## 🌾 Akses Petani (Portal Publik)

Petani menerima **link WhatsApp** → buka di browser → isi form penawaran → submit.

Tidak perlu akun atau login. Setiap petani mendapat link unik per broadcast.

Link format: `http://localhost:8000/form-penawaran/{token}`

Log link ada di: `storage/logs/laravel.log`

---

## 📁 Struktur Database

| Tabel | Fungsi |
|-------|--------|
| `users` | Akun internal (Owner, Admin, Kasir) |
| `tb_bahan` | Inventori bahan baku |
| `tb_mitra` | Data petani mitra |
| `tb_broadcast` | Request pengadaan |
| `tb_broadcast_token` | Token unik petani per broadcast |
| `tb_penawaran` | Penawaran harga dari petani |
| `tb_purchase_order` | Dokumen PO |
| `tb_penerimaan` | Penerimaan barang |
| `tb_quality_control` | Hasil inspeksi QC |
| `tb_hutang` | Rekonsiliasi pembayaran |
| `pelanggan` | CRM pelanggan |
| `transaksi` | Transaksi penjualan |
| `detail_transaksi` | Item dalam transaksi |

---

## ⚡ Kebutuhan Performansi (per dokumen SI)

- Halaman utama: ≤ 3 detik (koneksi 4G)
- Broadcast ke petani: ≤ 5 menit
- Generate PO otomatis: ≤ 10 detik
- Update stok pasca QC: ≤ 5 detik
- Upload foto QC: maksimal 2MB (JPG/PNG)
- Sistem uptime: ≥ 99%/bulan

---

## 🔧 WhatsApp Business API

Saat ini broadcast disimulasikan melalui log file (`storage/logs/laravel.log`).

Untuk mengaktifkan broadcast WhatsApp nyata, edit `.env`:
```env
WA_API_URL=https://api.whatsapp-gateway.example.com
WA_API_KEY=your_api_key
WA_SENDER_NUMBER=628xxxxxxxxxx
```

Lalu uncomment kode `Http::post(...)` di `KemitraanController::logBroadcastWA()`.

---

## 📄 Referensi

Dokumen SI: *Spesifikasi Kebutuhan Portal Kemitraan Rantai Pasok — Sistem Informasi Manajemen UMKM Cafe CNS (2025)*
