<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * CRUD lengkap untuk modul Manajemen Pelanggan (laporan 4.3).
 * CRUD = Create, Read, Update, Delete — 4 operasi dasar yang hampir
 * selalu ada di setiap fitur berbasis data. Di REST API, 4 operasi ini
 * biasanya dipetakan ke method HTTP:
 *   Create -> POST     Read -> GET     Update -> PUT/PATCH     Delete -> DELETE
 */
class CustomerController extends Controller
{
    /** READ (banyak data) — GET /api/customers */
    public function index(Request $request)
    {
        // Query builder: mulai dari "ambil semua pelanggan", lalu ditambah
        // filter secara bertahap tergantung parameter yang dikirim frontend.
        $query = Customer::query();

        if ($request->filled('segment') && $request->segment !== 'Semua') {
            $query->where('segment', $request->segment);
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            // where(function...) = mengelompokkan beberapa kondisi "OR" jadi satu,
            // supaya tidak tercampur dengan filter "AND" segment di atas.
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->get();

        return response()->json([
            'data' => $customers,
            // ringkasan angka untuk kartu KPI di halaman Pelanggan
            'summary' => [
                'total' => Customer::count(),
                'vip' => Customer::where('segment', 'VIP')->count(),
                'member' => Customer::where('segment', 'Member')->count(),
                'reguler' => Customer::where('segment', 'Reguler')->count(),
                'baru' => Customer::where('segment', 'Baru')->count(),
            ],
        ]);
    }

    /** CREATE — POST /api/customers */
    public function store(Request $request)
    {
        // Validasi: pastikan data yang dikirim frontend sesuai aturan sebelum disimpan
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
            ...$validator->validated(), // spread operator: salin semua data yang sudah divalidasi
            'segment' => $request->segment ?? 'Baru',
            'joined_at' => now(),
        ]);

        return response()->json(['message' => 'Pelanggan berhasil ditambahkan.', 'data' => $customer], 201);
    }

    /**
     * READ (satu data) — GET /api/customers/{customer}
     * Laravel otomatis mengubah {customer} di URL menjadi objek Customer
     * lewat fitur "Route Model Binding" (ID di URL dicari otomatis ke database).
     */
    public function show(Customer $customer)
    {
        return response()->json(['data' => $customer]);
    }

    /** UPDATE — PUT /api/customers/{customer} */
    public function update(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255', // "sometimes" = boleh tidak dikirim saat update sebagian
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

    /** DELETE — DELETE /api/customers/{customer} */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(['message' => 'Pelanggan berhasil dihapus.']);
    }

    /**
     * Endpoint tambahan di luar CRUD standar: menambah poin loyalitas
     * secara manual (laporan 4.3 — "penambahan poin loyalitas secara manual").
     */
    public function addPoints(Request $request, Customer $customer)
    {
        $validator = Validator::make($request->all(), [
            'points' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Poin tidak valid.'], 422);
        }

        // increment() = tambahkan angka ke kolom tertentu (lebih aman dari
        // race condition dibanding ambil-nilai-lalu-simpan-ulang manual)
        $customer->increment('loyalty_points', $request->points);

        return response()->json(['message' => 'Poin loyalitas ditambahkan.', 'data' => $customer->fresh()]);
    }
}
