<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoriController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tb_bahan')->orderBy('status_stok')->orderBy('nama_bahan');

        if ($request->filled('search')) {
            $query->where('nama_bahan', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status_stok', $request->status);
        }
        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        $items = $query->paginate(15);

        $stats = [
            'total'  => DB::table('tb_bahan')->count(),
            'kritis' => DB::table('tb_bahan')->where('status_stok', 'HABIS')->orWhere('status_stok', 'RENDAH')->count(),
            'aman'   => DB::table('tb_bahan')->where('status_stok', 'NORMAL')->count(),
        ];

        return view('inventori.index', compact('items', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_bahan'    => 'required|string|max:100',
            'kategori'      => 'required|string|max:50',
            'satuan'        => 'required|string|max:20',
            'jumlah_stok'   => 'required|numeric|min:0',
            'batas_minimum' => 'required|numeric|min:0',
            'batas_maksimum'=> 'required|numeric|min:0',
            'harga_per_unit'=> 'required|numeric|min:0',
            'supplier'      => 'required|string|max:100',
        ]);

        $status = $this->hitungStatus($validated['jumlah_stok'], $validated['batas_minimum']);

        DB::table('tb_bahan')->insert(array_merge($validated, [
            'status_stok'    => $status,
            'tanggal_update' => now(),
        ]));

        return back()->with('success', 'Item inventori berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_bahan'    => 'required|string|max:100',
            'kategori'      => 'required|string|max:50',
            'satuan'        => 'required|string|max:20',
            'jumlah_stok'   => 'required|numeric|min:0',
            'batas_minimum' => 'required|numeric|min:0',
            'batas_maksimum'=> 'required|numeric|min:0',
            'harga_per_unit'=> 'required|numeric|min:0',
            'supplier'      => 'required|string|max:100',
        ]);

        $status = $this->hitungStatus($validated['jumlah_stok'], $validated['batas_minimum']);

        DB::table('tb_bahan')->where('id_bahan', $id)->update(array_merge($validated, [
            'status_stok'    => $status,
            'tanggal_update' => now(),
        ]));

        return back()->with('success', 'Item inventori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::table('tb_bahan')->where('id_bahan', $id)->delete();
        return back()->with('success', 'Item inventori berhasil dihapus.');
    }

    private function hitungStatus(float $stok, float $minimum): string
    {
        if ($stok <= 0) return 'HABIS';
        if ($stok < $minimum) return 'RENDAH';
        return 'NORMAL';
    }
}
