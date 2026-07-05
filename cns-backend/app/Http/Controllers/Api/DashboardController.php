<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ringkasan operasional harian: 4 KPI, tren pendapatan mingguan,
     * segmentasi pelanggan, produk terlaris, transaksi terbaru, peringatan stok.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();

        $revenueToday = Transaction::whereDate('transacted_at', $today)
            ->where('status', 'selesai')->sum('total');

        $totalCustomers = Customer::count();

        $transactionsToday = Transaction::whereDate('transacted_at', $today)->count();

        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'min_stock')->count();

        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $weeklyRevenue[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->translatedFormat('D'),
                'revenue' => (float) Transaction::whereDate('transacted_at', $date)
                    ->where('status', 'selesai')->sum('total'),
            ];
        }

        $segmentation = Customer::select('segment', DB::raw('count(*) as total'))
            ->groupBy('segment')->pluck('total', 'segment');

        $topProducts = DB::table('transaction_items')
            ->select('product_name', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $recentTransactions = Transaction::with('customer')
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

        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'min_stock')
            ->get(['id', 'name', 'current_stock', 'min_stock', 'unit', 'is_coffee_bean']);

        return response()->json([
            'kpi' => [
                'revenue_today' => (float) $revenueToday,
                'total_customers' => $totalCustomers,
                'transactions_today' => $transactionsToday,
                'low_stock_alerts' => $lowStockCount,
            ],
            'weekly_revenue' => $weeklyRevenue,
            'customer_segmentation' => $segmentation,
            'top_products' => $topProducts,
            'recent_transactions' => $recentTransactions,
            'low_stock_items' => $lowStockItems,
        ]);
    }
}
