<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('tb_purchase_order')
            ->join('tb_penawaran', 'tb_penawaran.id_penawaran', '=', 'tb_purchase_order.id_penawaran')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
            ->join('tb_broadcast', 'tb_broadcast.id_broadcast', '=', 'tb_penawaran.id_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->select(
                'tb_purchase_order.*',
                'tb_mitra.nama_mitra', 'tb_mitra.no_hp as hp_mitra',
                'tb_bahan.nama_bahan', 'tb_bahan.satuan',
                'tb_penawaran.harga_satuan', 'tb_penawaran.estimasi_kirim',
                'tb_broadcast.jumlah_dibutuhkan'
            );

        if ($request->filled('status')) {
            $query->where('tb_purchase_order.status_po', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tb_purchase_order.nomor_po', 'like', '%' . $request->search . '%')
                  ->orWhere('tb_mitra.nama_mitra', 'like', '%' . $request->search . '%');
            });
        }

        $purchaseOrders = $query->orderByDesc('tb_purchase_order.tanggal_terbit')->paginate(15);

        $stats = [
            'total'       => DB::table('tb_purchase_order')->count(),
            'diterbitkan' => DB::table('tb_purchase_order')->where('status_po','DITERBITKAN')->count(),
            'selesai'     => DB::table('tb_purchase_order')->where('status_po','SELESAI')->count(),
            'nilai_total' => DB::table('tb_purchase_order')->sum('total_nilai'),
        ];

        return view('purchase_orders.index', compact('purchaseOrders', 'stats'));
    }

    public function show($id)
    {
        $po = DB::table('tb_purchase_order')
            ->join('tb_penawaran', 'tb_penawaran.id_penawaran', '=', 'tb_purchase_order.id_penawaran')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
            ->join('tb_broadcast', 'tb_broadcast.id_broadcast', '=', 'tb_penawaran.id_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->where('tb_purchase_order.id_po', $id)
            ->select('tb_purchase_order.*', 'tb_mitra.*', 'tb_bahan.nama_bahan', 'tb_bahan.satuan',
                     'tb_penawaran.harga_satuan', 'tb_penawaran.jumlah_tersedia', 'tb_penawaran.catatan_mitra', 'tb_penawaran.estimasi_kirim',
                     'tb_broadcast.jumlah_dibutuhkan', 'tb_broadcast.catatan as catatan_cafe')
            ->firstOrFail();

        $penerimaan = DB::table('tb_penerimaan')->where('id_po', $id)->first();
        $qc = null;
        if ($penerimaan) {
            $qc = DB::table('tb_quality_control')->where('id_penerimaan', $penerimaan->id_penerimaan)->first();
        }
        $hutang = DB::table('tb_hutang')
            ->where('id_mitra', $po->id_mitra)
            ->orderByDesc('id_hutang')
            ->first();

        return view('purchase_orders.show', compact('po', 'penerimaan', 'qc', 'hutang'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status_po' => 'required|in:DITERBITKAN,DIKIRIM,SELESAI,DIBATALKAN']);
        DB::table('tb_purchase_order')->where('id_po', $id)->update(['status_po' => $request->status_po]);
        return back()->with('success', 'Status Purchase Order diperbarui.');
    }
}
