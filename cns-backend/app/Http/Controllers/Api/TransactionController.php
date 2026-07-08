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

/**
 * Modul Transaksi & POS (laporan 4.4). Ini contoh CRUD yang sedikit
 * lebih rumit karena satu transaksi terdiri dari banyak item sekaligus
 * (relasi one-to-many transactions -> transaction_items).
 */
class TransactionController extends Controller
{
    /** READ — daftar transaksi + ringkasan + data grafik jam sibuk */
    public function index(Request $request)
    {
        $query = Transaction::with(['customer', 'items']);

        if ($request->filter === 'hari_ini') {
            $query->whereDate('transacted_at', Carbon::today());
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$keyword}%"));
            });
        }

        $transactions = $query->latest('transacted_at')->limit(100)->get();

        // Data untuk grafik "Trafik Pesanan per Jam": hitung jumlah transaksi
        // per jam (0-23) khusus untuk hari ini.
        $today = Carbon::today();
        $hourlyTraffic = DB::table('transactions')
            ->selectRaw('HOUR(transacted_at) as hour, COUNT(*) as total')
            ->whereDate('transacted_at', $today)
            ->groupBy('hour')
            ->pluck('total', 'hour');

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

    /** Daftar menu aktif, dipakai untuk mengisi pilihan menu di form POS */
    public function products()
    {
        return response()->json(['data' => Product::where('is_active', true)->orderBy('name')->get()]);
    }

    /**
     * CREATE — membuat satu transaksi baru berikut item-itemnya sekaligus.
     * Karena ada beberapa tabel yang harus konsisten (transaksi, item,
     * dan data pelanggan), semua proses dibungkus DB::transaction() supaya
     * kalau ada satu langkah gagal, semuanya dibatalkan (all-or-nothing).
     */
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

        // Kalau frontend mengirim nama pelanggan baru (bukan memilih dari daftar),
        // buatkan datanya dulu. firstOrCreate = cari yang sudah ada, kalau
        // belum ada baru dibuatkan baris baru.
        $customerId = $request->customer_id;
        if (! $customerId && $request->filled('customer_name')) {
            $customer = Customer::firstOrCreate(
                ['name' => $request->customer_name],
                ['segment' => 'Baru', 'joined_at' => now()]
            );
            $customerId = $customer->id;
        }

        $transaction = DB::transaction(function () use ($request, $customerId) {
            $transaction = $this->createTransactionHeader($customerId, $request->payment_method);
            $total = $this->createTransactionItems($transaction, $request->items);
            $transaction->update(['total' => $total]);

            if ($customerId) {
                $this->updateCustomerStats($customerId, $total);
            }

            return $transaction->load('items', 'customer');
        });

        return response()->json(['message' => 'Transaksi berhasil dibuat.', 'data' => $transaction], 201);
    }

    // Membuat baris "induk" transaksi dengan kode otomatis (TRX-2026-001, dst)
    private function createTransactionHeader(?int $customerId, string $paymentMethod): Transaction
    {
        $urutan = Transaction::whereYear('created_at', now()->year)->count() + 1;
        $code = 'TRX-' . now()->format('Y') . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);

        return Transaction::create([
            'code' => $code,
            'customer_id' => $customerId,
            'cashier_id' => JWTAuth::user()?->id,
            'payment_method' => $paymentMethod,
            'status' => 'selesai',
            'total' => 0, // diisi belakangan setelah semua item dihitung
            'transacted_at' => now(),
        ]);
    }

    // Membuat baris-baris item, dan mengembalikan total keseluruhan
    private function createTransactionItems(Transaction $transaction, array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $subtotal = $product->price * $item['qty'];
            $total += $subtotal;

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'product_name' => $product->name, // disalin, bukan sekadar disambungkan ke foreign key
                'qty' => $item['qty'],
                'price' => $product->price,
                'subtotal' => $subtotal,
            ]);
        }

        return $total;
    }

    // Update statistik pelanggan setelah belanja: kunjungan, total belanja, poin
    private function updateCustomerStats(int $customerId, float $total): void
    {
        $customer = Customer::find($customerId);

        $customer?->increment('visit_count');
        $customer?->increment('total_spent', $total);
        $customer?->increment('loyalty_points', intdiv((int) $total, 10000)); // 1 poin tiap Rp10.000
        $customer?->update(['last_visit_at' => now()]);
    }

    /** READ (satu data) — detail satu transaksi berikut itemnya */
    public function show(Transaction $transaction)
    {
        return response()->json(['data' => $transaction->load('items', 'customer', 'cashier')]);
    }

    /** UPDATE status transaksi (proses/selesai/dibatalkan) */
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

    /**
     * DELETE — menghapus transaksi. Kalau transaksi tsb sudah "selesai" dan
     * terhubung ke pelanggan, statistik pelanggan (kunjungan/poin/belanja)
     * dikembalikan dulu supaya datanya tetap konsisten.
     */
    public function destroy(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            if ($transaction->customer_id && $transaction->status === 'selesai') {
                $this->rollbackCustomerStats($transaction);
            }

            $transaction->delete(); // item-itemnya ikut terhapus (cascadeOnDelete di migration)
        });

        return response()->json(['message' => 'Transaksi berhasil dihapus.']);
    }

    private function rollbackCustomerStats(Transaction $transaction): void
    {
        $customer = $transaction->customer;
        if (! $customer) {
            return;
        }

        $poinYangPernahDidapat = intdiv((int) $transaction->total, 10000);

        // max(0, ...) supaya angkanya tidak sampai minus kalau ada data tidak konsisten
        $customer->update([
            'visit_count' => max(0, $customer->visit_count - 1),
            'total_spent' => max(0, $customer->total_spent - $transaction->total),
            'loyalty_points' => max(0, $customer->loyalty_points - $poinYangPernahDidapat),
        ]);
    }
}
