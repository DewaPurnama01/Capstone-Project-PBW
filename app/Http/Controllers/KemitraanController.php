<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KemitraanController extends Controller
{
    // INDEX — Portal Kemitraan
    
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'workflow');

        $mitra = DB::table('tb_mitra')->orderBy('nama_mitra')->get();

        // ── Broadcast aktif ──
        $broadcastAktif = DB::table('tb_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->select('tb_broadcast.*', 'tb_bahan.nama_bahan', 'tb_bahan.satuan')
            ->where('tb_broadcast.status_broadcast', 'AKTIF')
            ->orderByDesc('tb_broadcast.tanggal_kirim')
            ->first();

        // ── Riwayat broadcast ──
        $riwayatBroadcast = DB::table('tb_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->select('tb_broadcast.*', 'tb_bahan.nama_bahan', 'tb_bahan.satuan')
            ->orderByDesc('tb_broadcast.tanggal_kirim')
            ->paginate(10);

        // ── Penawaran untuk broadcast aktif ──
        $penawaran = [];
        if ($broadcastAktif) {
            $penawaran = DB::table('tb_penawaran')
                ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
                ->where('tb_penawaran.id_broadcast', $broadcastAktif->id_broadcast)
                ->select('tb_penawaran.*', 'tb_mitra.nama_mitra', 'tb_mitra.no_hp', 'tb_mitra.rating', 'tb_mitra.alamat')
                ->orderBy('tb_penawaran.harga_satuan')
                ->get();
        }

        // ── Purchase Orders ──
        $purchaseOrders = DB::table('tb_purchase_order')
            ->join('tb_penawaran', 'tb_penawaran.id_penawaran', '=', 'tb_purchase_order.id_penawaran')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
            ->join('tb_broadcast', 'tb_broadcast.id_broadcast', '=', 'tb_penawaran.id_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->select(
                'tb_purchase_order.*',
                'tb_mitra.nama_mitra', 'tb_mitra.no_hp',
                'tb_bahan.nama_bahan', 'tb_bahan.satuan',
                'tb_penawaran.harga_satuan', 'tb_penawaran.jumlah_tersedia',
                'tb_penawaran.estimasi_kirim', 'tb_broadcast.jumlah_dibutuhkan'
            )
            ->orderByDesc('tb_purchase_order.tanggal_terbit')
            ->limit(20)
            ->get();

        // ── Hutang ──
        $hutang = DB::table('tb_hutang')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_hutang.id_mitra')
            ->select('tb_hutang.*', 'tb_mitra.nama_mitra', 'tb_mitra.no_hp')
            ->orderBy('tb_hutang.tanggal_jatuh_tempo')
            ->get();

        $stokRendah  = DB::table('tb_bahan')->whereIn('status_stok', ['RENDAH','HABIS'])->get();
        $semuaBahan  = DB::table('tb_bahan')->orderBy('nama_bahan')->get();

        // ── Hitung langkah workflow aktif ──
        $workflowStep   = 1;
        $workflowPO     = null;
        $workflowQC     = null;
        $workflowActive = null; // broadcast yang sedang dalam proses (AKTIF atau SELESAI tapi PO belum selesai)

        // Cari broadcast yang masih "in-progress" (belum benar-benar selesai)
        $latestBroadcast = DB::table('tb_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->select('tb_broadcast.*', 'tb_bahan.nama_bahan', 'tb_bahan.satuan')
            ->orderByDesc('tb_broadcast.tanggal_kirim')
            ->first();

        if ($latestBroadcast) {
            $workflowActive = $latestBroadcast;

            if ($latestBroadcast->status_broadcast === 'AKTIF') {
                $penawaranCount = DB::table('tb_penawaran')
                    ->where('id_broadcast', $latestBroadcast->id_broadcast)
                    ->count();
                $workflowStep = $penawaranCount > 0 ? 4 : 3;
            } elseif ($latestBroadcast->status_broadcast === 'SELESAI' || $latestBroadcast->status_broadcast === 'DITUTUP') {
                // Cek PO
                $workflowPO = DB::table('tb_purchase_order')
                    ->join('tb_penawaran', 'tb_penawaran.id_penawaran', '=', 'tb_purchase_order.id_penawaran')
                    ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
                    ->where('tb_penawaran.id_broadcast', $latestBroadcast->id_broadcast)
                    ->select('tb_purchase_order.*', 'tb_mitra.nama_mitra', 'tb_penawaran.estimasi_kirim')
                    ->orderByDesc('tb_purchase_order.tanggal_terbit')
                    ->first();

                if (!$workflowPO) {
                    $workflowStep = 5; // Pilih terbaik (belum ada PO)
                } elseif ($workflowPO->status_po === 'DITERBITKAN') {
                    $workflowStep = 6; // PO diterbitkan
                } elseif ($workflowPO->status_po === 'DIKIRIM') {
                    $workflowStep = 7; // Pengiriman
                } elseif (in_array($workflowPO->status_po, ['SELESAI','DIBATALKAN'])) {
                    // Cek QC
                    $penerimaan = DB::table('tb_penerimaan')->where('id_po', $workflowPO->id_po)->first();
                    if ($penerimaan) {
                        $workflowQC = DB::table('tb_quality_control')
                            ->where('id_penerimaan', $penerimaan->id_penerimaan)
                            ->first();
                        $workflowStep = ($workflowQC && $workflowQC->hasil_qc === 'LOLOS') ? 9 : 8;
                    } else {
                        $workflowStep = 8;
                    }
                }
            }
        }

        // Petani yang sudah dinotifikasi untuk broadcast aktif
        $notifikasiTerkirim = [];
        if ($broadcastAktif) {
            $notifikasiTerkirim = DB::table('tb_broadcast_token')
                ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_broadcast_token.mitra_id')
                ->where('tb_broadcast_token.broadcast_id', $broadcastAktif->id_broadcast)
                ->select('tb_mitra.nama_mitra', 'tb_mitra.alamat', 'tb_mitra.no_hp', 'tb_broadcast_token.used')
                ->get();
        }

        return view('kemitraan.index', compact(
            'tab', 'mitra', 'broadcastAktif', 'penawaran', 'purchaseOrders',
            'hutang', 'stokRendah', 'semuaBahan', 'riwayatBroadcast',
            'workflowStep', 'workflowActive', 'workflowPO', 'workflowQC',
            'notifikasiTerkirim'
        ));
    }

    // MITRA (Petani)
    public function storeMitra(Request $request)
    {
        $validated = $request->validate([
            'nama_mitra' => 'required|string|max:100',
            'no_hp'      => 'required|string|max:15',
            'alamat'     => 'required|string',
            'komoditas'  => 'required|string|max:100',
            'catatan'    => 'nullable|string|max:255',
        ]);

        DB::table('tb_mitra')->insert(array_merge($validated, [
            'status_aktif'    => 1,
            'rating'          => 4.5,
            'total_order'     => 0,
            'persen_on_time'  => 100,
            'persen_kualitas' => 100,
            'tanggal_daftar'  => now()->toDateString(),
        ]));

        return back()->with('success', 'Mitra baru berhasil didaftarkan.');
    }

    public function updateMitra(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_mitra'   => 'required|string|max:100',
            'no_hp'        => 'required|string|max:15',
            'alamat'       => 'required|string',
            'komoditas'    => 'required|string|max:100',
            'status_aktif' => 'required|in:0,1',
            'catatan'      => 'nullable|string|max:255',
        ]);
        DB::table('tb_mitra')->where('id_mitra', $id)->update($validated);
        return back()->with('success', 'Data mitra berhasil diperbarui.');
    }

    public function destroyMitra($id)
    {
        $hasTranx = DB::table('tb_penawaran')->where('id_mitra', $id)->exists();
        if ($hasTranx) {
            return back()->with('error', 'Mitra tidak dapat dihapus karena memiliki riwayat transaksi.');
        }
        DB::table('tb_mitra')->where('id_mitra', $id)->delete();
        return back()->with('success', 'Mitra berhasil dihapus.');
    }

    
    public function storeBroadcast(Request $request)
    {
        $validated = $request->validate([
            'id_bahan'          => 'required|integer|exists:tb_bahan,id_bahan',
            'jumlah_dibutuhkan' => 'required|numeric|min:0.1',
            'harga_target'      => 'required|numeric|min:0',
            'batas_respon'      => 'required|date|after:now',
            'catatan'           => 'nullable|string|max:500',
        ]);

        // Ambil ID mitra yang dipilih user di form (checkbox)
        $mitraDipilih = $request->input('mitra_dipilih', []);

        if (!empty($mitraDipilih)) {
            // Hanya kirim ke mitra yang dicentang
            $mitraAktif = DB::table('tb_mitra')
                ->where('status_aktif', 1)
                ->whereIn('id_mitra', $mitraDipilih)
                ->get();
        } else {
            // Fallback: filter otomatis berdasarkan komoditas
            $bahanNama  = DB::table('tb_bahan')->where('id_bahan', $validated['id_bahan'])->value('nama_bahan');
            $mitraAktif = DB::table('tb_mitra')
                ->where('status_aktif', 1)
                ->where('komoditas', 'like', '%' . $bahanNama . '%')
                ->get();
            // Kalau tidak ada yang cocok komoditasnya, kirim ke semua
            if ($mitraAktif->isEmpty()) {
                $mitraAktif = DB::table('tb_mitra')->where('status_aktif', 1)->get();
            }
        }

        if ($mitraAktif->isEmpty()) {
            return back()->with('error', 'Tidak ada mitra aktif yang sesuai. Daftarkan mitra terlebih dahulu.');
        }

        // Tutup broadcast aktif sebelumnya untuk bahan yang sama
        DB::table('tb_broadcast')
            ->where('id_bahan', $validated['id_bahan'])
            ->where('status_broadcast', 'AKTIF')
            ->update(['status_broadcast' => 'DITUTUP']);

        $broadcastId = DB::table('tb_broadcast')->insertGetId([
            'id_bahan'          => $validated['id_bahan'],
            'jumlah_dibutuhkan' => $validated['jumlah_dibutuhkan'],
            'harga_target'      => $validated['harga_target'],
            'batas_respon'      => $validated['batas_respon'],
            'catatan'           => $validated['catatan'] ?? null,
            'status_broadcast'  => 'AKTIF',
            'tanggal_kirim'     => now(),
        ]);

        $bahan    = DB::table('tb_bahan')->where('id_bahan', $validated['id_bahan'])->first();
        $mitraList = $mitraAktif->values();

        foreach ($mitraList as $idx => $mitra) {
            $token = Str::random(40);
            DB::table('tb_broadcast_token')->insert([
                'broadcast_id' => $broadcastId,
                'mitra_id'     => $mitra->id_mitra,
                'token'        => $token,
                'used'         => 0,
                'created_at'   => now(),
            ]);
        }

        $msg = " Request broadcast dibuat untuk {$mitraAktif->count()} mitra! Bagikan link penawaran secara manual via WhatsApp di tab Alur Pengadaan.";

        return redirect()->route('kemitraan.index', ['tab' => 'workflow'])
            ->with('success', $msg);
    }

    public function tutupBroadcast($id)
    {
        DB::table('tb_broadcast')->where('id_broadcast', $id)
            ->where('status_broadcast', 'AKTIF')
            ->update(['status_broadcast' => 'DITUTUP']);
        return back()->with('success', 'Broadcast berhasil ditutup.');
    }

    
    public function indexPenawaran($broadcast_id)
    {
        $broadcast = DB::table('tb_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->where('tb_broadcast.id_broadcast', $broadcast_id)
            ->select('tb_broadcast.*', 'tb_bahan.nama_bahan', 'tb_bahan.satuan')
            ->firstOrFail();

        $penawaran = DB::table('tb_penawaran')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
            ->where('tb_penawaran.id_broadcast', $broadcast_id)
            ->select('tb_penawaran.*', 'tb_mitra.nama_mitra', 'tb_mitra.rating', 'tb_mitra.alamat')
            ->orderBy('tb_penawaran.harga_satuan')
            ->get();

        return view('kemitraan.penawaran', compact('broadcast', 'penawaran'));
    }

    public function storePenawaranManual(Request $request)
    {
        $validated = $request->validate([
            'id_broadcast'    => 'required|integer|exists:tb_broadcast,id_broadcast',
            'id_mitra'        => 'required|integer|exists:tb_mitra,id_mitra',
            'harga_satuan'    => 'required|numeric|min:1',
            'jumlah_tersedia' => 'required|numeric|min:0.1',
            'estimasi_kirim'  => 'required|date|after:today',
            'catatan_mitra'   => 'nullable|string|max:500',
        ]);

        $existing = DB::table('tb_penawaran')
            ->where('id_broadcast', $validated['id_broadcast'])
            ->where('id_mitra', $validated['id_mitra'])
            ->first();
        if ($existing) {
            return back()->with('error', 'Mitra ini sudah mengajukan penawaran untuk broadcast yang sama.');
        }

        DB::table('tb_penawaran')->insert([
            'id_broadcast'     => $validated['id_broadcast'],
            'id_mitra'         => $validated['id_mitra'],
            'harga_satuan'     => $validated['harga_satuan'],
            'jumlah_tersedia'  => $validated['jumlah_tersedia'],
            'estimasi_kirim'   => $validated['estimasi_kirim'],
            'catatan_mitra'    => $validated['catatan_mitra'] ?? null,
            'status_penawaran' => 'MENUNGGU',
            'tanggal_input'    => now(),
        ]);

        return back()->with('success', 'Penawaran mitra berhasil dicatat.');
    }

    public function pilihPenawaran(Request $request, $id)
    {
        $penawaran = DB::table('tb_penawaran')->where('id_penawaran', $id)->firstOrFail();
        if ($penawaran->status_penawaran !== 'MENUNGGU') {
            return back()->with('error', 'Penawaran sudah tidak dapat dipilih.');
        }

        $broadcast = DB::table('tb_broadcast')->where('id_broadcast', $penawaran->id_broadcast)->firstOrFail();

        DB::table('tb_penawaran')->where('id_penawaran', $id)->update(['status_penawaran' => 'DITERIMA']);
        DB::table('tb_penawaran')
            ->where('id_broadcast', $penawaran->id_broadcast)
            ->where('id_penawaran', '!=', $id)
            ->where('status_penawaran', 'MENUNGGU')
            ->update(['status_penawaran' => 'DITOLAK']);
        DB::table('tb_broadcast')->where('id_broadcast', $penawaran->id_broadcast)
            ->update(['status_broadcast' => 'SELESAI']);

        // Generate PO
        $noPO = 'PO-' . now()->format('Y') . '-' . str_pad(
            DB::table('tb_purchase_order')->whereYear('tanggal_terbit', now()->year)->count() + 1,
            3, '0', STR_PAD_LEFT
        );
        $totalNilai = $penawaran->harga_satuan * $broadcast->jumlah_dibutuhkan;

        DB::table('tb_purchase_order')->insertGetId([
            'nomor_po'       => $noPO,
            'id_penawaran'   => $id,
            'tanggal_terbit' => now()->toDateString(),
            'total_nilai'    => $totalNilai,
            'status_po'      => 'DITERBITKAN',
        ]);

        $mitra = DB::table('tb_mitra')->where('id_mitra', $penawaran->id_mitra)->first();
        Log::info("[WA PO] Ke: {$mitra->no_hp} | No PO: {$noPO} | Total: Rp " . number_format($totalNilai, 0, ',', '.'));

        return redirect()->route('kemitraan.index', ['tab' => 'workflow'])
            ->with('success', "✅ Purchase Order {$noPO} berhasil diterbitkan untuk {$mitra->nama_mitra}!");
    }

    // PUBLIC FORM PENAWARAN (via link WA)
   
    public function publicFormPenawaran($token)
    {
        $tokenRecord = DB::table('tb_broadcast_token')->where('token', $token)->first();
        if (!$tokenRecord) {
            return view('kemitraan.penawaran-public-closed', ['alasan' => 'Link tidak valid.']);
        }
        if ($tokenRecord->used) {
            return view('kemitraan.penawaran-public-closed', ['alasan' => 'Anda sudah mengirimkan penawaran melalui link ini.']);
        }
        $broadcast = DB::table('tb_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->where('tb_broadcast.id_broadcast', $tokenRecord->broadcast_id)
            ->select('tb_broadcast.*', 'tb_bahan.nama_bahan', 'tb_bahan.satuan')
            ->first();
        if (!$broadcast || $broadcast->status_broadcast !== 'AKTIF') {
            return view('kemitraan.penawaran-public-closed', ['alasan' => 'Periode penawaran sudah ditutup.']);
        }
        if (now()->isAfter($broadcast->batas_respon)) {
            return view('kemitraan.penawaran-public-closed', ['alasan' => 'Batas waktu penawaran sudah lewat.']);
        }
        $mitra = DB::table('tb_mitra')->where('id_mitra', $tokenRecord->mitra_id)->first();
        return view('kemitraan.penawaran-public-form', compact('broadcast', 'mitra', 'token'));
    }

    public function publicStorePenawaran(Request $request, $token)
    {
        $tokenRecord = DB::table('tb_broadcast_token')->where('token', $token)->where('used', 0)->first();
        if (!$tokenRecord) {
            return view('kemitraan.penawaran-public-closed', ['alasan' => 'Link tidak valid atau sudah digunakan.']);
        }
        $broadcast = DB::table('tb_broadcast')
            ->where('id_broadcast', $tokenRecord->broadcast_id)
            ->where('status_broadcast', 'AKTIF')
            ->first();
        if (!$broadcast || now()->isAfter($broadcast->batas_respon)) {
            return view('kemitraan.penawaran-public-closed', ['alasan' => 'Periode penawaran sudah berakhir.']);
        }

        $validated = $request->validate([
            'harga_satuan'    => 'required|numeric|min:1',
            'jumlah_tersedia' => 'required|numeric|min:0.1',
            'estimasi_kirim'  => 'required|date|after:today',
            'catatan_mitra'   => 'nullable|string|max:500',
        ]);

        $existing = DB::table('tb_penawaran')
            ->where('id_broadcast', $broadcast->id_broadcast)
            ->where('id_mitra', $tokenRecord->mitra_id)
            ->first();
        if ($existing) {
            return view('kemitraan.penawaran-public-success', ['sudahSubmit' => true]);
        }

        DB::table('tb_penawaran')->insert([
            'id_broadcast'     => $broadcast->id_broadcast,
            'id_mitra'         => $tokenRecord->mitra_id,
            'harga_satuan'     => $validated['harga_satuan'],
            'jumlah_tersedia'  => $validated['jumlah_tersedia'],
            'estimasi_kirim'   => $validated['estimasi_kirim'],
            'catatan_mitra'    => $validated['catatan_mitra'] ?? null,
            'status_penawaran' => 'MENUNGGU',
            'tanggal_input'    => now(),
        ]);
        DB::table('tb_broadcast_token')->where('token', $token)->update(['used' => 1]);

        return view('kemitraan.penawaran-public-success', ['sudahSubmit' => false]);
    }

    // QUALITY CONTROL — RF-09, RF-10
    public function formQC($po_id)
    {
        $po = DB::table('tb_purchase_order')
            ->join('tb_penawaran', 'tb_penawaran.id_penawaran', '=', 'tb_purchase_order.id_penawaran')
            ->join('tb_mitra', 'tb_mitra.id_mitra', '=', 'tb_penawaran.id_mitra')
            ->join('tb_broadcast', 'tb_broadcast.id_broadcast', '=', 'tb_penawaran.id_broadcast')
            ->join('tb_bahan', 'tb_bahan.id_bahan', '=', 'tb_broadcast.id_bahan')
            ->where('tb_purchase_order.id_po', $po_id)
            ->select(
                'tb_purchase_order.*',
                'tb_mitra.nama_mitra', 'tb_mitra.id_mitra', 'tb_mitra.no_hp',
                'tb_bahan.nama_bahan', 'tb_bahan.id_bahan', 'tb_bahan.satuan',
                'tb_penawaran.harga_satuan', 'tb_broadcast.jumlah_dibutuhkan'
            )
            ->firstOrFail();

        $penerimaanExist = DB::table('tb_penerimaan')->where('id_po', $po_id)->first();
        if ($penerimaanExist) {
            return redirect()->route('kemitraan.index', ['tab' => 'workflow'])
                ->with('error', 'QC untuk Purchase Order ini sudah dilakukan.');
        }
        if (!in_array($po->status_po, ['DITERBITKAN','DIKIRIM'])) {
            return redirect()->route('kemitraan.index', ['tab' => 'workflow'])
                ->with('error', 'Status PO tidak valid untuk QC.');
        }

        return view('kemitraan.qc-form', compact('po'));
    }

    public function storeQC(Request $request)
    {
        $validated = $request->validate([
            'id_po'            => 'required|integer|exists:tb_purchase_order,id_po',
            'jumlah_diterima'  => 'required|numeric|min:0',
            'kondisi_fisik'    => 'required|string|max:500',
            'hasil_qc'         => 'required|in:LOLOS,TIDAK_LOLOS',
            'catatan_qc'       => 'nullable|string|max:500',
            'foto_dokumentasi' => 'nullable|image|max:2048',
            'skor_aroma'       => 'required|integer|min:1|max:5',
            'skor_warna'       => 'required|integer|min:1|max:5',
            'skor_ukuran'      => 'required|integer|min:1|max:5',
            'skor_kebersihan'  => 'required|integer|min:1|max:5',
        ]);

        $po = DB::table('tb_purchase_order')
            ->join('tb_penawaran', 'tb_penawaran.id_penawaran', '=', 'tb_purchase_order.id_penawaran')
            ->join('tb_broadcast', 'tb_broadcast.id_broadcast', '=', 'tb_penawaran.id_broadcast')
            ->where('tb_purchase_order.id_po', $validated['id_po'])
            ->select('tb_purchase_order.*', 'tb_penawaran.id_mitra', 'tb_penawaran.harga_satuan', 'tb_broadcast.id_bahan', 'tb_broadcast.jumlah_dibutuhkan')
            ->first();

        $adminId  = session('user') ? session('user')['id'] : null;
        $fotoPath = null;
        if ($request->hasFile('foto_dokumentasi')) {
            $fotoPath = $request->file('foto_dokumentasi')->store('qc_photos', 'public');
        }

        $penerimaanId = DB::table('tb_penerimaan')->insertGetId([
            'id_po'           => $validated['id_po'],
            'tanggal_terima'  => now()->toDateString(),
            'jumlah_diterima' => $validated['jumlah_diterima'],
            'kondisi_fisik'   => $validated['kondisi_fisik'],
            'id_admin'        => $adminId,
        ]);

        $qcId = DB::table('tb_quality_control')->insertGetId([
            'id_penerimaan'    => $penerimaanId,
            'hasil_qc'         => $validated['hasil_qc'],
            'catatan_qc'       => $validated['catatan_qc'] ?? null,
            'foto_dokumentasi' => $fotoPath,
            'skor_aroma'       => $validated['skor_aroma'],
            'skor_warna'       => $validated['skor_warna'],
            'skor_ukuran'      => $validated['skor_ukuran'],
            'skor_kebersihan'  => $validated['skor_kebersihan'],
            'tanggal_qc'       => now(),
            'id_admin'         => $adminId,
        ]);

        DB::table('tb_purchase_order')->where('id_po', $validated['id_po'])->update([
            'status_po' => $validated['hasil_qc'] === 'LOLOS' ? 'SELESAI' : 'DIBATALKAN',
        ]);

        if ($validated['hasil_qc'] === 'LOLOS') {
            // Update inventaris — RF-10
            DB::table('tb_bahan')->where('id_bahan', $po->id_bahan)->increment('jumlah_stok', $validated['jumlah_diterima']);
            $bahan = DB::table('tb_bahan')->where('id_bahan', $po->id_bahan)->first();
            $statusBaru = $bahan->jumlah_stok <= 0 ? 'HABIS' : ($bahan->jumlah_stok < $bahan->batas_minimum ? 'RENDAH' : 'NORMAL');
            DB::table('tb_bahan')->where('id_bahan', $po->id_bahan)->update(['status_stok' => $statusBaru, 'tanggal_update' => now()]);

            // Catat hutang — RF-11
            $jumlahTagihan = $po->harga_satuan * $validated['jumlah_diterima'];
            DB::table('tb_hutang')->insert([
                'id_qc'               => $qcId,
                'id_mitra'            => $po->id_mitra,
                'jumlah_tagihan'      => $jumlahTagihan,
                'tanggal_jatuh_tempo' => now()->addDays(14)->toDateString(),
                'status_bayar'        => 'BELUM_BAYAR',
                'tanggal_lunas'       => null,
                'bukti_bayar'         => null,
            ]);

            return redirect()->route('kemitraan.index', ['tab' => 'workflow'])
                ->with('success', '🎉 QC Lolos! Stok diperbarui dan hutang ke mitra dicatat.');
        }

        return redirect()->route('kemitraan.index', ['tab' => 'workflow'])
            ->with('warning', '❌ QC Tidak Lolos. Silakan ajukan retur ke petani.');
    }

    // ═══════════════════════════════════════════════════════════
    // KONFIRMASI BAYAR — RF-12
    // ═══════════════════════════════════════════════════════════
    public function konfirmasiBayar(Request $request, $id)
    {
        $request->validate(['bukti_bayar' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048']);
        $buktiBayar = null;
        if ($request->hasFile('bukti_bayar')) {
            $buktiBayar = $request->file('bukti_bayar')->store('bukti_bayar', 'public');
        }
        DB::table('tb_hutang')->where('id_hutang', $id)->update([
            'status_bayar'  => 'SUDAH_BAYAR',
            'tanggal_lunas' => now()->toDateString(),
            'bukti_bayar'   => $buktiBayar,
        ]);
        $hutang = DB::table('tb_hutang')->where('id_hutang', $id)->first();
        if ($hutang) {
            DB::table('tb_mitra')->where('id_mitra', $hutang->id_mitra)->increment('total_order');
        }
        return back()->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    // Update status PO (misal: DITERBITKAN → DIKIRIM)
    public function updateStatusPO(Request $request, $id)
    {
        $request->validate(['status_po' => 'required|in:DITERBITKAN,DIKIRIM,SELESAI,DIBATALKAN']);
        DB::table('tb_purchase_order')->where('id_po', $id)->update(['status_po' => $request->status_po]);
        return back()->with('success', 'Status PO diperbarui.');
    }
}
