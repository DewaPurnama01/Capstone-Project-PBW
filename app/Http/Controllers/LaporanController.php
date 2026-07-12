<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $periode = $request->get('periode', 'bulan_ini');
        [$dari, $sampai] = $this->getPeriode($periode, $request);

        // Pendapatan per hari dalam periode
        $revenue = DB::table('transaksi')
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as total, COUNT(*) as count')
            ->whereBetween('created_at', [$dari, $sampai])
            ->where('status', 'selesai')
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        // Total ringkasan
        $ringkasan = [
            'total_pendapatan' => $revenue->sum('total'),
            'total_transaksi'  => $revenue->sum('count'),
            'rata2_per_hari'   => $revenue->count() > 0 ? $revenue->avg('total') : 0,
        ];

        // Produk terlaris
        $topProducts = DB::table('detail_transaksi')
            ->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.created_at', [$dari, $sampai])
            ->selectRaw('nama_item as nama_menu, SUM(qty) as total_terjual, SUM(subtotal) as total_revenue')
            ->groupBy('nama_item')
            ->orderByDesc('total_terjual')
            ->limit(10)
            ->get();

        // Pertumbuhan pelanggan per bulan (6 bulan terakhir)
        $customerGrowth = DB::table('pelanggan')
            ->selectRaw("DATE_FORMAT(tanggal_daftar, '%Y-%m') as bulan, COUNT(*) as count")
            ->where('tanggal_daftar', '>=', now()->subMonths(6)->toDateString())
            ->groupBy('bulan')->orderBy('bulan')->get();

        // Performa supplier — FIX: tambahkan total_po dan lolos_qc
        $performaSupplier = DB::table('tb_mitra')
            ->leftJoin('tb_penawaran', 'tb_penawaran.id_mitra', '=', 'tb_mitra.id_mitra')
            ->leftJoin('tb_purchase_order', 'tb_purchase_order.id_penawaran', '=', 'tb_penawaran.id_penawaran')
            ->leftJoin('tb_penerimaan', 'tb_penerimaan.id_po', '=', 'tb_purchase_order.id_po')
            ->leftJoin('tb_quality_control', 'tb_quality_control.id_penerimaan', '=', 'tb_penerimaan.id_penerimaan')
            ->selectRaw(
                'tb_mitra.nama_mitra,
                 tb_mitra.rating,
                 tb_mitra.total_order,
                 tb_mitra.persen_on_time,
                 tb_mitra.persen_kualitas,
                 COUNT(DISTINCT tb_purchase_order.id_po) as total_po,
                 SUM(CASE WHEN tb_quality_control.hasil_qc = "LOLOS" THEN 1 ELSE 0 END) as lolos_qc,
                 COALESCE(SUM(DISTINCT tb_purchase_order.total_nilai), 0) as total_nilai'
            )
            ->where('tb_mitra.status_aktif', 1)
            ->groupBy(
                'tb_mitra.id_mitra',
                'tb_mitra.nama_mitra',
                'tb_mitra.rating',
                'tb_mitra.total_order',
                'tb_mitra.persen_on_time',
                'tb_mitra.persen_kualitas'
            )
            ->orderByDesc('total_po')
            ->get();

        // Segmen pelanggan
        $segmenPelanggan = DB::table('pelanggan')
            ->selectRaw('segmen, COUNT(*) as jumlah')
            ->groupBy('segmen')
            ->get();

        // Metode pembayaran
        $metodeBayar = DB::table('transaksi')
            ->selectRaw('metode_bayar, COUNT(*) as jumlah, SUM(total) as total')
            ->whereBetween('created_at', [$dari, $sampai])
            ->where('status', 'selesai')
            ->groupBy('metode_bayar')
            ->get();

        // Laporan hutang
        $laporanHutang = DB::table('tb_hutang')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_hutang.id_mitra')
            ->select('tb_hutang.*', 'tb_mitra.nama_mitra')
            ->orderByDesc('tb_hutang.id_hutang')
            ->limit(20)
            ->get();

        $totalHutang = DB::table('tb_hutang')->where('status_bayar', 'BELUM_BAYAR')->sum('jumlah_tagihan');
        $totalLunas  = DB::table('tb_hutang')->where('status_bayar', 'SUDAH_BAYAR')->sum('jumlah_tagihan');

        $supplierPerformance = $performaSupplier;
        $paymentMix = $metodeBayar;

        $debtReport = [
            'belum_bayar' => $totalHutang ?? 0,
            'sudah_bayar' => $totalLunas  ?? 0,
            'total'       => ($totalHutang ?? 0) + ($totalLunas ?? 0),
        ];

        return view('laporan.index', compact(
            'periode', 'dari', 'sampai',
            'revenue', 'ringkasan',
            'topProducts', 'customerGrowth',
            'performaSupplier', 'supplierPerformance', 'segmenPelanggan',
            'metodeBayar', 'paymentMix', 'laporanHutang',
            'totalHutang', 'totalLunas', 'debtReport'
        ));
    }

    public function exportPdf(Request $request)
    {
        $periode = $request->get('periode', 'bulan_ini');
        [$dari, $sampai] = $this->getPeriode($periode, $request);

        $revenue = DB::table('transaksi')
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as total, COUNT(*) as count')
            ->whereBetween('created_at', [$dari, $sampai])
            ->where('status', 'selesai')
            ->groupBy('tanggal')->orderBy('tanggal')->get();

        $ringkasan = [
            'total_pendapatan' => $revenue->sum('total'),
            'total_transaksi'  => $revenue->sum('count'),
            'rata2_per_hari'   => $revenue->count() > 0 ? $revenue->avg('total') : 0,
        ];

        $topProducts = DB::table('detail_transaksi')
            ->join('transaksi', 'transaksi.id', '=', 'detail_transaksi.transaksi_id')
            ->where('transaksi.status', 'selesai')
            ->whereBetween('transaksi.created_at', [$dari, $sampai])
            ->selectRaw('nama_item as nama_menu, SUM(qty) as total_terjual, SUM(subtotal) as total_revenue')
            ->groupBy('nama_item')->orderByDesc('total_terjual')->limit(10)->get();

        $metodeBayar = DB::table('transaksi')
            ->selectRaw('metode_bayar, COUNT(*) as jumlah, SUM(total) as total')
            ->whereBetween('created_at', [$dari, $sampai])
            ->where('status', 'selesai')
            ->groupBy('metode_bayar')->get();

        $laporanHutang = DB::table('tb_hutang')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_hutang.id_mitra')
            ->select('tb_hutang.*', 'tb_mitra.nama_mitra')
            ->orderByDesc('tb_hutang.id_hutang')->limit(20)->get();

        $totalHutang = DB::table('tb_hutang')->where('status_bayar', 'BELUM_BAYAR')->sum('jumlah_tagihan');
        $totalLunas  = DB::table('tb_hutang')->where('status_bayar', 'SUDAH_BAYAR')->sum('jumlah_tagihan');
        $debtReport  = ['belum_bayar' => $totalHutang ?? 0, 'sudah_bayar' => $totalLunas ?? 0, 'total' => ($totalHutang ?? 0) + ($totalLunas ?? 0)];

        $supplierPerformance = DB::table('tb_mitra')
            ->leftJoin('tb_penawaran', 'tb_penawaran.id_mitra', '=', 'tb_mitra.id_mitra')
            ->leftJoin('tb_purchase_order', 'tb_purchase_order.id_penawaran', '=', 'tb_penawaran.id_penawaran')
            ->leftJoin('tb_penerimaan', 'tb_penerimaan.id_po', '=', 'tb_purchase_order.id_po')
            ->leftJoin('tb_quality_control', 'tb_quality_control.id_penerimaan', '=', 'tb_penerimaan.id_penerimaan')
            ->selectRaw('tb_mitra.nama_mitra, COUNT(DISTINCT tb_purchase_order.id_po) as total_po, SUM(CASE WHEN tb_quality_control.hasil_qc = "LOLOS" THEN 1 ELSE 0 END) as lolos_qc, COALESCE(SUM(DISTINCT tb_purchase_order.total_nilai), 0) as total_nilai')
            ->where('tb_mitra.status_aktif', 1)
            ->groupBy('tb_mitra.id_mitra', 'tb_mitra.nama_mitra')
            ->orderByDesc('total_po')->get();

        return view('laporan.print', compact(
            'periode', 'dari', 'sampai',
            'revenue', 'ringkasan', 'topProducts',
            'metodeBayar', 'laporanHutang',
            'totalHutang', 'totalLunas', 'debtReport', 'supplierPerformance'
        ));
    }

    private function getPeriode(string $periode, Request $request): array
    {
        return match($periode) {
            'hari_ini'   => [now()->startOfDay(), now()->endOfDay()],
            'minggu_ini' => [now()->startOfWeek(), now()->endOfWeek()],
            'bulan_lalu' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'kustom'     => [$request->get('dari', now()->startOfMonth()), $request->get('sampai', now())],
            default      => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
