<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * CRUD untuk modul Manajemen Inventori (laporan 4.5).
 */
class InventoryController extends Controller
{
    /** READ — daftar item, bisa difilter berdasarkan kategori & status stok */
    public function index(Request $request)
    {
        $query = InventoryItem::query();

        if ($request->filled('category') && $request->category !== 'Semua') {
            $query->where('category', $request->category);
        }

        // stock_status (kritis/rendah/aman) adalah accessor yang dihitung
        // di model, bukan kolom asli -> makanya difilter di sisi PHP (setelah
        // data diambil), bukan lewat query database.
        $items = $query->orderBy('name')->get();

        if ($request->filled('status') && $request->status !== 'Semua') {
            $statusMap = ['Kritis' => 'kritis', 'Rendah' => 'rendah', 'Aman' => 'aman'];
            $target = $statusMap[$request->status] ?? null;
            $items = $items->filter(fn ($item) => $item->stock_status === $target)->values();
        }

        $all = InventoryItem::all();
        $critical = $all->filter(fn ($item) => $item->stock_status === 'kritis');

        return response()->json([
            'data' => $items,
            'summary' => [
                'total_items' => $all->count(),
                'critical' => $critical->count(),
                'low' => $all->filter(fn ($item) => $item->stock_status === 'rendah')->count(),
                'total_value' => (float) $all->sum(fn ($item) => $item->current_stock * $item->unit_price),
            ],
            'critical_items' => $critical->values(),
        ]);
    }

    /** CREATE */
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

    /** UPDATE */
    public function update(Request $request, InventoryItem $inventoryItem)
    {
        $inventoryItem->update($request->only([
            'name', 'category', 'unit', 'current_stock', 'min_stock', 'max_stock', 'unit_price', 'supplier_name',
        ]));

        return response()->json(['message' => 'Item diperbarui.', 'data' => $inventoryItem]);
    }

    /**
     * Restock manual (tambah stok langsung). Khusus biji kopi TIDAK lewat
     * endpoint ini — harus lewat alur Portal Kemitraan (lihat PartnershipController),
     * sesuai laporan bagian 4.5.
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

    /** DELETE */
    public function destroy(InventoryItem $inventoryItem)
    {
        $inventoryItem->delete();

        return response()->json(['message' => 'Item dihapus.']);
    }
}
