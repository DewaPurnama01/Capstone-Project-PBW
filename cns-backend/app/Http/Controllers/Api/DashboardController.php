<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Menyiapkan semua data untuk halaman Dashboard (laporan 4.2):
 * 4 KPI, grafik tren pendapatan, segmentasi pelanggan, produk terlaris,
 * transaksi terbaru, dan peringatan stok. Semua dihitung "live" dari
 * data yang ada saat endpoint ini dipanggil (bukan angka statis).
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        return response()->json([
            'kpi' => $this->buildKpi($today),
            'weekly_revenue' => $this->buildWeeklyRevenue($today),
            'customer_segmentation' => $this->buildSegmentation(),
            'top_products' => $this->buildTopProducts(),
            'recent_transactions' => $this->buildRecentTransactions(),
            'low_stock_items' => $this->buildLowStockItems(),
        ]);
    }

    // 4 kartu KPI di bagian atas Dashboard
    private function buildKpi(Carbon $today): array
    {
        return [
            'revenue_today' => (float) Transaction::whereDate('transacted_at', $today)
                ->where('status', 'selesai')->sum('total'),
            'total_customers' => Customer::count(),
            'transactions_today' => Transaction::whereDate('transacted_at', $today)->count(),
            'low_stock_alerts' => InventoryItem::whereColumn('current_stock', '<=', 'min_stock')->count(),
        ];
    }

    // Grafik area: total pendapatan per hari, untuk 7 hari terakhir (termasuk hari ini)
    private function buildWeeklyRevenue(Carbon $today): array
    {
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);

            $days[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->translatedFormat('D'), // nama hari singkat: Sen, Sel, dst
                'revenue' => (float) Transaction::whereDate('transacted_at', $date)
                    ->where('status', 'selesai')->sum('total'),
            ];
        }

        return $days;
    }

    // Grafik donat: jumlah pelanggan per segmen (Baru/Reguler/Member/VIP)
    private function buildSegmentation()
    {
        return Customer::select('segment', DB::raw('count(*) as total'))
            ->groupBy('segment')
            ->pluck('total', 'segment');
    }

    // Grafik batang: 5 menu dengan qty terjual terbanyak (dari semua waktu)
    private function buildTopProducts()
    {
        return DB::table('transaction_items')
            ->select('product_name', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();
    }

    // Tabel: 5 transaksi paling baru
    private function buildRecentTransactions()
    {
        return Transaction::with('customer')
            ->latest('transacted_at')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->code,
                'customer' => $t->customer?->name ?? 'Umum',
                'total' => (float) $t->total,
                'status' => $t->status,
                'time' => $t->transacted_at->format('H:i'),
            ]);
    }

    // Banner peringatan: item inventori yang stoknya sudah di bawah/sama dengan minimum
    private function buildLowStockItems()
    {
        return InventoryItem::whereColumn('current_stock', '<=', 'min_stock')
            ->get(['id', 'name', 'current_stock', 'min_stock', 'unit', 'is_coffee_bean']);
    }
}
