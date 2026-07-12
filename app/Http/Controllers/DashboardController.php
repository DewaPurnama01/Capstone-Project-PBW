<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Ringkasan stok rendah dari tb_bahan
        $stokRendah = DB::table('tb_bahan')
            ->whereIn('status_stok', ['RENDAH', 'HABIS'])
            ->orderByRaw("FIELD(status_stok, 'HABIS', 'RENDAH')")
            ->get();

        // KPI: Total pelanggan aktif
        $totalPelanggan = DB::table('pelanggan')->where('status', 'aktif')->count();

        // KPI: Transaksi hari ini
        $transaksiHariIni = DB::table('transaksi')
            ->whereDate('created_at', today())
            ->count();

        // KPI: Pendapatan hari ini
        $pendapatanHariIni = DB::table('transaksi')
            ->whereDate('created_at', today())
            ->where('status', 'selesai')
            ->sum('total');

        // Pendapatan 7 hari terakhir
        $pendapatan7Hari = DB::table('transaksi')
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as total, COUNT(*) as jumlah_order')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->where('status', 'selesai')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Transaksi terbaru (5 terakhir)
        $transaksiTerbaru = DB::table('transaksi')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Produk terlaris
        $produkTerlaris = DB::table('detail_transaksi')
            ->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
            ->where('transaksi.status', 'selesai')
            ->selectRaw('nama_item, SUM(qty) as total_terjual, SUM(subtotal) as total_pendapatan')
            ->groupBy('nama_item')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        // Segmen pelanggan
        $segmenPelanggan = DB::table('pelanggan')
            ->selectRaw('segmen, COUNT(*) as jumlah')
            ->groupBy('segmen')
            ->get();

        // Hutang jatuh tempo
        $hutangJatuhTempo = DB::table('tb_hutang')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_hutang.id_mitra')
            ->where('tb_hutang.status_bayar', 'BELUM_BAYAR')
            ->where('tb_hutang.tanggal_jatuh_tempo', '<=', now()->addDays(7))
            ->select('tb_hutang.*', 'tb_mitra.nama_mitra')
            ->orderBy('tb_hutang.tanggal_jatuh_tempo')
            ->limit(3)
            ->get();

        return view('dashboard.index', compact(
            'stokRendah',
            'totalPelanggan',
            'transaksiHariIni',
            'pendapatanHariIni',
            'pendapatan7Hari',
            'transaksiTerbaru',
            'produkTerlaris',
            'segmenPelanggan',
            'hutangJatuhTempo'
        ));
    }
}
