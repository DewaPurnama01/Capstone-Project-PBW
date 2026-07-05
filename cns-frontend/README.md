# Cafe CNS — Frontend (React + TypeScript + Tailwind)

Frontend terpisah untuk Sistem Informasi Manajemen Cafe CNS, mengonsumsi
REST API dari folder `cns-backend` (Laravel + JWT). Dibangun sesuai
`RANCANGAN APLIKASI BERBASIS WEB` bagian 3 & 4.

## Stack
- React 19 + TypeScript + Vite
- Tailwind CSS v4 (palet krem & hijau khas Cafe CNS)
- React Router (routing + RBAC route guard)
- Axios (dengan interceptor JWT dari localStorage)
- Recharts (grafik area, donat, batang, garis)
- lucide-react (ikon)

## Instalasi

```bash
npm install
cp .env.example .env
# sesuaikan VITE_API_BASE_URL jika backend tidak berjalan di localhost:8000

npm run dev      # http://localhost:5173
```

## Fitur RBAC di sisi frontend
- Token JWT & data user disimpan di `localStorage` (`cns_token`, `cns_user`).
- Menu sidebar yang berada di luar hak akses role tetap tampil namun terkunci
  (ikon gembok, tidak bisa diklik) — lihat `src/components/Sidebar.tsx`.
- Jika user memaksa membuka URL yang bukan haknya, `ProtectedRoute` akan
  mengarahkan ke halaman `/akses-terkunci` ("Akses Terkunci").
- Jika token API kadaluarsa/invalid (401), interceptor otomatis membersihkan
  sesi dan mengarahkan ke halaman login.

## Halaman

| Route | Modul | Role |
|---|---|---|
| `/login` | Login | publik |
| `/dashboard` | Dashboard & KPI | semua |
| `/pelanggan` | Manajemen Pelanggan | owner, kasir |
| `/transaksi` | Transaksi & POS | owner, kasir |
| `/inventori` | Manajemen Inventori | owner, admin |
| `/kemitraan` | Portal Kemitraan Petani | owner, admin |
| `/purchase-orders` | Purchase Orders | owner, admin |
| `/laporan` | Laporan & Analitik | owner |

## Build produksi

```bash
npm run build   # menghasilkan folder dist/
npm run preview
```
