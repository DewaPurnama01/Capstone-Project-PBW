<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Laporan & Analitik - fitur eksklusif Owner. 4 tab: Keuangan, Produk, Pelanggan, Supplier.
     */
    public function index(Request $request)
    {
        $months = (int) ($request->months ?? 6);

        return response()->json([
            'finance' => $this->financeReport($months),
            'product' => $this->productReport(),
            'customer' => $this->customerReport($months),
            'supplier' => $this->supplierReport(),
        ]);
    }

    private function financeReport(int $months): array
    {
        $series = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $revenue = (float) Transaction::whereYear('transacted_at', $month->year)
                ->whereMonth('transacted_at', $month->month)
                ->where('status', 'selesai')->sum('total');

            $series[] = [
                'label' => $month->translatedFormat('M'),
                'revenue' => $revenue,
                'target' => round($revenue * 1.1, -3), // target simulasi 10% di atas realisasi
                'transactions' => Transaction::whereYear('transacted_at', $month->year)
                    ->whereMonth('transacted_at', $month->month)->count(),
            ];
        }

        $paymentMix = Transaction::where('status', 'selesai')
            ->select('payment_method', DB::raw('count(*) as total'))
            ->groupBy('payment_method')->pluck('total', 'payment_method');

        return [
            'total_revenue' => array_sum(array_column($series, 'revenue')),
            'series' => $series,
            'payment_mix' => $paymentMix,
        ];
    }

    private function productReport(): array
    {
        $products = DB::table('transaction_items')
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

        return ['products' => $products];
    }

    private function customerReport(int $months): array
    {
        $growth = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $growth[] = [
                'label' => $month->translatedFormat('M'),
                'active_customers' => Customer::whereYear('last_visit_at', $month->year)
                    ->whereMonth('last_visit_at', $month->month)->count(),
                'new_customers' => Customer::whereYear('joined_at', $month->year)
                    ->whereMonth('joined_at', $month->month)->count(),
            ];
        }

        $totalCustomers = Customer::count();
        $churned = Customer::where('last_visit_at', '<', now()->subDays(60))->count();
        $churnRate = $totalCustomers > 0 ? round(($churned / $totalCustomers) * 100, 1) : 0;
        $alv = (float) (Customer::avg('total_spent') ?? 0);

        return [
            'growth' => $growth,
            'churn_rate' => $churnRate,
            'average_lifetime_value' => round($alv, 0),
            'retention_rate' => round(100 - $churnRate, 1),
        ];
    }

    private function supplierReport(): array
    {
        $partners = Partner::orderByDesc('quality_score')->get(['name', 'on_time_rate', 'quality_score']);

        return ['partners' => $partners];
    }
}
