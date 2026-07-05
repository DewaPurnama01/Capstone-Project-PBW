<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('segment') && $request->segment !== 'Semua') {
            $query->where('segment', $request->segment);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->get();

        return response()->json([
            'data' => $customers,
            'summary' => [
                'total' => Customer::count(),
                'vip' => Customer::where('segment', 'VIP')->count(),
                'member' => Customer::where('segment', 'Member')->count(),
                'reguler' => Customer::where('segment', 'Reguler')->count(),
                'baru' => Customer::where('segment', 'Baru')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'segment' => 'nullable|in:Baru,Reguler,Member,VIP',
            'favorite_menu' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $customer = Customer::create([
            ...$validator->validated(),
            'segment' => $request->segment ?? 'Baru',
            'joined_at' => now(),
        ]);

        return response()->json(['message' => 'Pelanggan berhasil ditambahkan.', 'data' => $customer], 201);
    }

    public function show(Customer $customer)
    {
        return response()->json(['data' => $customer]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'segment' => 'nullable|in:Baru,Reguler,Member,VIP',
            'favorite_menu' => 'nullable|string|max:255',
            'loyalty_points' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
        }

        $customer->update($validator->validated());

        return response()->json(['message' => 'Pelanggan berhasil diperbarui.', 'data' => $customer]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(['message' => 'Pelanggan berhasil dihapus.']);
    }

    /**
     * Menambah poin loyalitas secara manual (owner/kasir).
     */
    public function addPoints(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Poin tidak valid.'], 422);
        }

        $customer->increment('loyalty_points', $request->points);

        return response()->json(['message' => 'Poin loyalitas ditambahkan.', 'data' => $customer->fresh()]);
    }
}
