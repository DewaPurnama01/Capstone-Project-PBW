<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('category') && $request->category !== 'Semua') {
            $query->where('category', $request->category);
        }

        $items = $query->orderBy('name')->get()->map(function ($item) {
            $item->stock_status = $item->stock_status;
            $item->stock_percent = $item->stock_percent;
            return $item;
        });

        if ($request->filled('status') && $request->status !== 'Semua') {
            $statusMap = ['Kritis' => 'kritis', 'Rendah' => 'rendah', 'Aman' => 'aman'];
            $target = $statusMap[$request->status] ?? null;
            $items = $items->filter(fn ($i) => $i->stock_status === $target)->values();
        }

        $all = InventoryItem::get();
        $critical = $all->filter(fn ($i) => $i->stock_status === 'kritis');

        return response()->json([
            'data' => $items,
            'summary' => [
                'total_items' => $all->count(),
                'critical' => $critical->count(),
                'low' => $all->filter(fn ($i) => $i->stock_status === 'rendah')->count(),
                'total_value' => (float) $all->reduce(fn ($carry, $i) => $carry + ($i->current_stock * $i->unit_price), 0),
            ],
            'critical_items' => $critical->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:Bahan Baku,Kemasan,Makanan',
            'unit' => 'required|string|max:20',
            'current_stock' => 'required|numeric|min:0',
            'min_stock' => 'required|numeric|min:0',
            'max_stock' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'is_coffee_bean' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $item = InventoryItem::create($validator->validated());

        return response()->json(['message' => 'Item inventori berhasil ditambahkan.', 'data' => $item], 201);
    }

    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $inventoryItem->update($request->only([
            'name', 'category', 'unit', 'current_stock', 'min_stock', 'max_stock', 'unit_price', 'supplier_name',
        ]));

        return response()->json(['message' => 'Item diperbarui.', 'data' => $inventoryItem]);
    }

    /**
     * Restock manual (non-kopi). Untuk biji kopi, arahkan ke Portal Kemitraan.
     */
    public function restock(Request $request, InventoryItem $inventoryItem)
    {
        $validator = Validator::make($request->all(), [
            'qty' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Jumlah restock tidak valid.'], 422);
        }

        $inventoryItem->increment('current_stock', $request->qty);

        return response()->json(['message' => 'Stok berhasil ditambahkan.', 'data' => $inventoryItem->fresh()]);
    }

    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return response()->json(['message' => 'Item dihapus.']);
    }
}
