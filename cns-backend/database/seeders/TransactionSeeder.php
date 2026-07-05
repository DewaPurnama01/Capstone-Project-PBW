<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $products = Product::all();
        $cashier = User::where('role', 'kasir')->first();

        $counter = 1;

        // Data historis 6 bulan untuk laporan analitik
        for ($m = 5; $m >= 0; $m--) {
            $monthDate = Carbon::now()->subMonths($m);
            $txCount = rand(30, 60);

            for ($i = 0; $i < $txCount; $i++) {
                $date = $monthDate->copy()->startOfMonth()->addDays(rand(0, 27))->addHours(rand(8, 20))->addMinutes(rand(0, 59));
                $customer = rand(0, 4) > 0 ? $customers->random() : null;
                $itemCount = rand(1, 3);
                $total = 0;

                $trx = Transaction::create([
                    'code' => 'TRX-' . $date->format('Y') . '-' . str_pad($counter++, 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customer?->id,
                    'cashier_id' => $cashier?->id,
                    'payment_method' => collect(['QRIS', 'Tunai', 'Transfer'])->random(),
                    'status' => 'selesai',
                    'total' => 0,
                    'transacted_at' => $date,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                for ($j = 0; $j < $itemCount; $j++) {
                    $product = $products->random();
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;
                    $total += $subtotal;

                    TransactionItem::create([
                        'transaction_id' => $trx->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'qty' => $qty,
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);
                }

                $trx->update(['total' => $total]);
            }
        }

        // Beberapa transaksi hari ini yang masih "proses" untuk demo dashboard/POS
        $today = Carbon::today();
        $sampleToday = [
            [$customers[0], 'QRIS', 'selesai', ['Kopi Susu' => 2, 'Croissant' => 1]],
            [$customers[1], 'Tunai', 'selesai', ['Matcha Latte' => 1]],
            [$customers[2], 'Transfer', 'proses', ['Cold Brew' => 2, 'Roti Bakar' => 1]],
            [$customers[3], 'QRIS', 'selesai', ['Cappuccino' => 1]],
            [$customers[4], 'QRIS', 'proses', ['Americano' => 3]],
        ];

        foreach ($sampleToday as $idx => [$customer, $method, $status, $items]) {
            $time = $today->copy()->addHours(10)->addMinutes($idx * 17);
            $total = 0;

            $trx = Transaction::create([
                'code' => 'TRX-' . $today->format('Y') . '-' . str_pad($counter++, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'cashier_id' => $cashier?->id,
                'payment_method' => $method,
                'status' => $status,
                'total' => 0,
                'transacted_at' => $time,
            ]);

            foreach ($items as $name => $qty) {
                $product = $products->firstWhere('name', $name);
                $subtotal = $product->price * $qty;
                $total += $subtotal;

                TransactionItem::create([
                    'transaction_id' => $trx->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $qty,
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }

            $trx->update(['total' => $total]);
        }
    }
}
