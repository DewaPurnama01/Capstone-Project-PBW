<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'items']);

        if ($request->filter === 'hari_ini') {
            $query->whereDate('transacted_at', Carbon::today());
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$s}%"));
            });
        }

        $transactions = $query->latest('transacted_at')->limit(100)->get();

        $today = Carbon::today();
        $hourlyTraffic = DB::table('transactions')
            ->selectRaw('HOUR(transacted_at) as hour, COUNT(*) as total')
            ->whereDate('transacted_at', $today)
            ->groupBy('hour')->pluck('total', 'hour');

        return response()->json([
            'data' => $transactions,
            'summary' => [
                'revenue_today' => (float) Transaction::whereDate('transacted_at', $today)->where('status', 'selesai')->sum('total'),
                'total_today' => Transaction::whereDate('transacted_at', $today)->count(),
                'in_progress' => Transaction::where('status', 'proses')->count(),
                'avg_order' => (float) (Transaction::whereDate('transacted_at', $today)->avg('total') ?? 0),
            ],
            'hourly_traffic' => $hourlyTraffic,
        ]);
    }

    public function products()
    {
        return response()->json(['data' => Product::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:QRIS,Tunai,Transfer',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data transaksi tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $customerId = $request->customer_id;
        if (! $customerId && $request->filled('customer_name')) {
            $customer = Customer::firstOrCreate(
                ['name' => $request->customer_name],
                ['segment' => 'Baru', 'joined_at' => now()]
            );
            $customerId = $customer->id;
        }

        $transaction = DB::transaction(function () use ($request, $customerId) {
            $count = Transaction::whereYear('created_at', now()->year)->count() + 1;
            $code = 'TRX-' . now()->format('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $trx = Transaction::create([
                'code' => $code,
                'customer_id' => $customerId,
                'cashier_id' => JWTAuth::user()?->id,
                'payment_method' => $request->payment_method,
                'status' => 'selesai',
                'total' => 0,
                'transacted_at' => now(),
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['qty'];
                $total += $subtotal;

                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $trx->update(['total' => $total]);

            if ($customerId) {
                $customerModel = Customer::find($customerId);
                $customerModel?->increment('visit_count');
                $customerModel?->increment('total_spent', $total);
                $customerModel?->increment('loyalty_points', intdiv((int) $total, 10000));
                $customerModel?->update(['last_visit_at' => now()]);
            }

            return $trx->load('items', 'customer');
        });

        return response()->json(['message' => 'Transaksi berhasil dibuat.', 'data' => $transaction], 201);
    }

    public function show(Transaction $transaction)
    {
        return response()->json(['data' => $transaction->load('items', 'customer', 'cashier')]);
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:proses,selesai,dibatalkan',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Status tidak valid.'], 422);
        }

        $transaction->update(['status' => $request->status]);

        return response()->json(['message' => 'Status transaksi diperbarui.', 'data' => $transaction]);
    }

    public function destroy(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            if ($transaction->customer_id && $transaction->status === 'selesai') {
                $customer = $transaction->customer;
                if ($customer) {
                    $pointsEarned = intdiv((int) $transaction->total, 10000);
                    $customer->update([
                        'visit_count' => max(0, $customer->visit_count - 1),
                        'total_spent' => max(0, $customer->total_spent - $transaction->total),
                        'loyalty_points' => max(0, $customer->loyalty_points - $pointsEarned),
                    ]);
                }
            }

            $transaction->delete();
        });

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }
}
