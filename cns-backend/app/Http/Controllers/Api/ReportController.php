<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Modul Laporan & Analitik (laporan 4.8) — khusus Owner (lihat RBAC di routes/api.php).
 * Terdiri dari 4 tab: Keuangan, Produk, Pelanggan, Supplier.
 * Tiap tab dipisah jadi method sendiri supaya masing-masing tetap sederhana dan mudah dibaca.
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) ($request->months ?? 6); // rentang waktu bisa dipilih di frontend (3/6/12 bulan)

        return response()->json([
            'finance' => $this->laporanKeuangan($bulan),
            'product' => $this->laporanProduk(),
            'customer' => $this->laporanPelanggan($bulan),
            'supplier' => $this->laporanSupplier(),
        ]);
    }

    // Tab "Keuangan": pendapatan vs target per bulan, dan mix metode pembayaran
    private function laporanKeuangan(int $bulan): array
    {
        $data = [];

        for ($i = $bulan - 1; $i >= 0; $i--) {
            $tanggal = Carbon::now()->subMonths($i);

            $pendapatan = (float) Transaction::whereYear('transacted_at', $tanggal->year)
                ->whereMonth('transacted_at', $tanggal->month)
                ->where('status', 'selesai')->sum('total');

            $data[] = [
                'label' => $tanggal->translatedFormat('M'),
                'revenue' => $pendapatan,
                'target' => round($pendapatan * 1.1, -3), // target contoh: 10% di atas realisasi
                'transactions' => Transaction::whereYear('transacted_at', $tanggal->year)
                    ->whereMonth('transacted_at', $tanggal->month)->count(),
            ];
        }

        $mixPembayaran = Transaction::where('status', 'selesai')
            ->select('payment_method', DB::raw('count(*) as total'))
            ->groupBy('payment_method')->pluck('total', 'payment_method');

        return [
            'total_revenue' => array_sum(array_column($data, 'revenue')),
            'series' => $data,
            'payment_mix' => $mixPembayaran,
        ];
    }

    // Tab "Produk": total qty terjual, pendapatan, dan margin keuntungan per produk
    private function laporanProduk(): array
    {
        $produk = DB::table('transaction_items')
            ->join('products', 'products.id', '=', 'transaction_items.product_id')
            ->select(
                'products.name',
                DB::raw('SUM(transaction_items.qty) as total_qty'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue'),
                DB::raw('SUM(transaction_items.qty * products.cost_price) as total_cost')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function ($p) {
                // margin (%) = (pendapatan - modal) / pendapatan * 100
                $margin = $p->total_revenue > 0
                    ? round((($p->total_revenue - $p->total_cost) / $p->total_revenue) * 100, 1)
                    : 0;

                return [
                    'name' => $p->name,
                    'qty_sold' => (int) $p->total_qty,
                    'revenue' => (float) $p->total_revenue,
                    'margin_percent' => $margin,
                ];
            });

        return ['products' => $produk];
    }

    // Tab "Pelanggan": pertumbuhan pelanggan, retention rate, ALV, churn rate
    private function laporanPelanggan(int $bulan): array
    {
        $pertumbuhan = [];

        for ($i = $bulan - 1; $i >= 0; $i--) {
            $tanggal = Carbon::now()->subMonths($i);

            $pertumbuhan[] = [
                'label' => $tanggal->translatedFormat('M'),
                'active_customers' => Customer::whereYear('last_visit_at', $tanggal->year)
                    ->whereMonth('last_visit_at', $tanggal->month)->count(),
                'new_customers' => Customer::whereYear('joined_at', $tanggal->year)
                    ->whereMonth('joined_at', $tanggal->month)->count(),
            ];
        }

        $totalPelanggan = Customer::count();
        // Dianggap "churn" kalau sudah 60 hari lebih tidak pernah datang lagi
        $churn = Customer::where('last_visit_at', '<', now()->subDays(60))->count();
        $churnRate = $totalPelanggan > 0 ? round(($churn / $totalPelanggan) * 100, 1) : 0;

        return [
            'growth' => $pertumbuhan,
            'churn_rate' => $churnRate,
            'average_lifetime_value' => round((float) (Customer::avg('total_spent') ?? 0), 0),
            'retention_rate' => round(100 - $churnRate, 1),
        ];
    }

    // Tab "Supplier": perbandingan performa antar petani mitra
    private function laporanSupplier(): array
    {
        return [
            'partners' => Partner::orderByDesc('quality_score')->get(['name', 'on_time_rate', 'quality_score']),
        ];
    }
}
