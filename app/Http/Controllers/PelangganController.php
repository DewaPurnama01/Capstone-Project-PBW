<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('pelanggan')->orderBy('nama');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('no_hp', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('segmen')) {
            $query->where('segmen', $request->segmen);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pelanggan = $query->paginate(15);

        $stats = [
            'total'  => DB::table('pelanggan')->count(),
            'vip'    => DB::table('pelanggan')->where('segmen','VIP')->count(),
            'member' => DB::table('pelanggan')->where('segmen','Member')->count(),
            'baru'   => DB::table('pelanggan')->where('segmen','Baru')->count(),
        ];

        return view('pelanggan.index', compact('pelanggan', 'stats'));
    }

    public function create()
    {
        return view('pelanggan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:100',
            'no_hp'        => 'nullable|string|max:15',
            'segmen'       => 'required|in:VIP,Member,Reguler,Baru',
            'menu_favorit' => 'nullable|string|max:100',
        ], [
            'nama.required'   => 'Nama pelanggan wajib diisi.',
            'segmen.required' => 'Segmen wajib dipilih.',
        ]);

        DB::table('pelanggan')->insert(array_merge($validated, [
            'email'              => null,
            'poin'               => 0,
            'total_kunjungan'    => 0,
            'total_belanja'      => 0,
            'status'             => 'aktif',
            'tanggal_daftar'     => now()->toDateString(),
            'terakhir_kunjungan' => null,
        ]));

        return redirect()->route('pelanggan.index')->with('success', 'Pelanggan baru berhasil ditambahkan.');
    }

    public function show($id)
    {
        $pelanggan = DB::table('pelanggan')->where('id', $id)->firstOrFail();
        $transaksi = DB::table('transaksi')
            ->where('pelanggan_id', $id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        return view('pelanggan.show', compact('pelanggan', 'transaksi'));
    }

    public function edit($id)
    {
        $pelanggan = DB::table('pelanggan')->where('id', $id)->firstOrFail();
        return view('pelanggan.edit', compact('pelanggan'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama'         => 'required|string|max:100',
            'no_hp'        => 'nullable|string|max:15',
            'segmen'       => 'required|in:VIP,Member,Reguler,Baru',
            'menu_favorit' => 'nullable|string|max:100',
            'status'       => 'required|in:aktif,tidak aktif',
        ]);
        DB::table('pelanggan')->where('id', $id)->update($validated);
        return redirect()->route('pelanggan.index')->with('success', 'Data pelanggan berhasil diperbarui.');
    }

    // Hapus permanen (FK transaksi.pelanggan_id ON DELETE SET NULL akan jaga integritas)
    public function destroy($id)
    {
        DB::table('pelanggan')->where('id', $id)->delete();
        return back()->with('success', 'Pelanggan berhasil dihapus.');
    }
}
