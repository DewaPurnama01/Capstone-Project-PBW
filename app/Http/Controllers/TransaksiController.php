<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // Menu statis dengan ID, nama, harga, kategori
    private array $menu = [
        ['id' => 1,  'nama_menu' => 'Kopi Susu',     'harga' => 30000, 'kategori' => 'KOPI'],
        ['id' => 2,  'nama_menu' => 'Americano',      'harga' => 25000, 'kategori' => 'KOPI'],
        ['id' => 3,  'nama_menu' => 'Cappuccino',     'harga' => 32000, 'kategori' => 'KOPI'],
        ['id' => 4,  'nama_menu' => 'Cold Brew',      'harga' => 28000, 'kategori' => 'KOPI'],
        ['id' => 5,  'nama_menu' => 'Espresso',       'harga' => 20000, 'kategori' => 'KOPI'],
        ['id' => 6,  'nama_menu' => 'Matcha Latte',   'harga' => 35000, 'kategori' => 'NON_KOPI'],
        ['id' => 7,  'nama_menu' => 'Teh Tarik',      'harga' => 18000, 'kategori' => 'NON_KOPI'],
        ['id' => 8,  'nama_menu' => 'Cokelat Panas',  'harga' => 28000, 'kategori' => 'NON_KOPI'],
        ['id' => 9,  'nama_menu' => 'Croissant',      'harga' => 18000, 'kategori' => 'MAKANAN'],
        ['id' => 10, 'nama_menu' => 'Roti Bakar',     'harga' => 22000, 'kategori' => 'MAKANAN'],
        ['id' => 11, 'nama_menu' => 'Kue Lapis',      'harga' => 15000, 'kategori' => 'MAKANAN'],
        ['id' => 12, 'nama_menu' => 'Sandwich',       'harga' => 25000, 'kategori' => 'MAKANAN'],
    ];

    /**
     * Peta bahan baku per 1 unit menu.
     * Key: nama_menu (lowercase)
     * Value: array of ['bahan' => nama_bahan_di_tb_bahan, 'jumlah' => decimal]
     * Satuan mengikuti satuan di tb_bahan (gram / ml).
     */
    private array $resepBahan = [
        'espresso' => [
            ['bahan' => 'Biji Kopi', 'jumlah' => 9],
        ],
        'kopi susu' => [
            ['bahan' => 'Biji Kopi',       'jumlah' => 9],
            ['bahan' => 'Susu UHT',        'jumlah' => 100],
            ['bahan' => 'Gula Aren Cair',  'jumlah' => 25],
            ['bahan' => 'Creamer Bubuk',   'jumlah' => 5],
            ['bahan' => 'Es Batu',         'jumlah' => 150],
        ],
        'americano' => [
            ['bahan' => 'Biji Kopi', 'jumlah' => 9],
            ['bahan' => 'Es Batu',   'jumlah' => 120],
        ],
        'cappuccino' => [
            ['bahan' => 'Biji Kopi',        'jumlah' => 9],
            ['bahan' => 'Susu UHT',         'jumlah' => 180],
            ['bahan' => 'Bubuk Kayu Manis', 'jumlah' => 0.5],
        ],
        'cold brew' => [
            ['bahan' => 'Konsentrat Cold Brew', 'jumlah' => 120],
            ['bahan' => 'Air Mineral',           'jumlah' => 120],
            ['bahan' => 'Es Batu',               'jumlah' => 100],
        ],
        'matcha latte' => [
            ['bahan' => 'Bubuk Matcha Murni', 'jumlah' => 5],
            ['bahan' => 'Susu UHT',           'jumlah' => 180],
            ['bahan' => 'Gula Cair',          'jumlah' => 20],
            ['bahan' => 'Es Batu',            'jumlah' => 120],
        ],
        'teh tarik' => [
            ['bahan' => 'Teh Hitam Bubuk',   'jumlah' => 10],
            ['bahan' => 'Susu Kental Manis', 'jumlah' => 30],
            ['bahan' => 'Susu Evaporasi',    'jumlah' => 30],
            ['bahan' => 'Es Batu',           'jumlah' => 120],
        ],
        'cokelat panas' => [
            ['bahan' => 'Bubuk Cokelat Premium', 'jumlah' => 30],
            ['bahan' => 'Susu UHT',              'jumlah' => 200],
            ['bahan' => 'Gula Cair',             'jumlah' => 10],
            ['bahan' => 'Whipping Cream',        'jumlah' => 15],
        ],
        'croissant' => [
            ['bahan' => 'Tepung Terigu Protein Tinggi', 'jumlah' => 35],
            ['bahan' => 'Mentega',                      'jumlah' => 25],
            ['bahan' => 'Gula Pasir',                   'jumlah' => 4],
            ['bahan' => 'Ragi Instan',                  'jumlah' => 1],
        ],
        'roti bakar' => [
            ['bahan' => 'Roti Tawar',        'jumlah' => 100],
            ['bahan' => 'Mentega',           'jumlah' => 15],
            ['bahan' => 'Susu Kental Manis', 'jumlah' => 20],
            ['bahan' => 'Keju Cheddar',      'jumlah' => 25],
        ],
        'kue lapis' => [
            ['bahan' => 'Kuning Telur',                   'jumlah' => 50],
            ['bahan' => 'Mentega',                        'jumlah' => 20],
            ['bahan' => 'Gula Pasir',                     'jumlah' => 15],
            ['bahan' => 'Tepung Terigu Protein Rendah',   'jumlah' => 5],
            ['bahan' => 'Susu Bubuk',                     'jumlah' => 2],
        ],
        'sandwich' => [
            ['bahan' => 'Roti Tawar',   'jumlah' => 90],
            ['bahan' => 'Daging Asap',  'jumlah' => 50],
            ['bahan' => 'Telur Ayam',   'jumlah' => 50],
            ['bahan' => 'Keju Cheddar', 'jumlah' => 20],
            ['bahan' => 'Mentega',      'jumlah' => 5],
        ],
    ];

    // ─── INDEX ───────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = DB::table('transaksi')->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $transaksi = $query->paginate(20);

        $hariIni      = DB::table('transaksi')->whereDate('created_at', today())->where('status','selesai')->sum('total') ?? 0;
        $bulanIni     = DB::table('transaksi')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->where('status','selesai')->sum('total') ?? 0;
        $countHari    = DB::table('transaksi')->whereDate('created_at', today())->count();
        $countSelesai = DB::table('transaksi')->whereDate('created_at', today())->where('status','selesai')->count();

        $stats = [
            'hari_ini'       => $hariIni,
            'bulan_ini'      => $bulanIni,
            'count_hari_ini' => $countHari,
            'avg'            => $countSelesai > 0 ? $hariIni / $countSelesai : 0,
            'total'          => DB::table('transaksi')->count(),
            'proses'         => DB::table('transaksi')->whereDate('created_at', today())->where('status','proses')->count(),
            'pendapatan'     => $hariIni,
        ];

        return view('transaksi.index', compact('transaksi', 'stats'));
    }

    // ─── CREATE ──────────────────────────────────────────────────
    public function create()
    {
        $pelanggan   = DB::table('pelanggan')->where('status', 'aktif')->orderBy('nama')->get();
        $menuObjects = array_map(fn($m) => (object) array_merge((array)$m, [
            'resep' => $this->getMenuResep($m['nama_menu'])
        ]), $this->menu);
        // Kirim info stok bahan agar view bisa cek ketersediaan (opsional)
        $stokBahan = DB::table('tb_bahan')->pluck('jumlah_stok', 'nama_bahan');
        return view('transaksi.create', ['menu' => $menuObjects, 'pelanggan' => $pelanggan, 'stokBahan' => $stokBahan]);
    }

    // ─── STORE ───────────────────────────────────────────────────
    public function store(Request $request)
    {
        // Parse cart JSON dari form POS
        $cartData = json_decode($request->input('cart_data', '[]'), true);
        if (empty($cartData)) {
            return back()->withErrors(['cart' => 'Keranjang belanja masih kosong.'])->withInput();
        }

        // Map metode bayar: CASH→Tunai, TRANSFER→Transfer
        $metodeMap   = ['CASH' => 'Tunai', 'TRANSFER' => 'Transfer', 'QRIS' => 'QRIS', 'Tunai' => 'Tunai'];
        $metodeBayar = $metodeMap[$request->input('metode_bayar', 'CASH')] ?? 'Tunai';

        // Resolusi pelanggan
        $pelangganId  = $request->input('id') ?: null;
        $namaOrder    = 'Walk-in';
        $segmen       = 'Reguler';
        $pelangganObj = null;

        if ($pelangganId) {
            $pelangganObj = DB::table('pelanggan')->where('id', $pelangganId)->first();
            if ($pelangganObj) {
                $namaOrder = $pelangganObj->nama;
                $segmen    = $pelangganObj->segmen;
            } else {
                $pelangganId = null;
            }
        }

        // Hitung subtotal dari cart
        $subtotal = collect($cartData)->sum(fn($i) => ($i['qty'] ?? 0) * ($i['harga'] ?? 0));

        // Diskon poin (1 poin = Rp 100)
        $poinDigunakan = max(0, intval($request->input('poin_digunakan', 0)));
        // Pastikan tidak melebihi poin tersedia
        if ($pelangganObj && $poinDigunakan > $pelangganObj->poin) {
            $poinDigunakan = (int) $pelangganObj->poin;
        }
        $diskon     = min($poinDigunakan * 100, $subtotal);
        $totalBayar = max(0, $subtotal - $diskon);

        $kasirName = session('user') ? session('user')['name'] : 'Kasir';

        // Simpan transaksi
        $transaksiId = DB::table('transaksi')->insertGetId([
            'nama'         => $namaOrder,
            'pelanggan_id' => $pelangganId,
            'segmen'       => $segmen,
            'metode_bayar' => $metodeBayar,
            'total'        => $totalBayar,
            'status'       => 'proses',
            'kasir'        => $kasirName,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Simpan detail item
        foreach ($cartData as $item) {
            DB::table('detail_transaksi')->insert([
                'transaksi_id' => $transaksiId,
                'nama_item'    => $item['nama'] ?? $item['nama_menu'] ?? 'Item',
                'qty'          => intval($item['qty'] ?? 1),
                'harga'        => floatval($item['harga'] ?? 0),
                'subtotal'     => intval($item['qty'] ?? 1) * floatval($item['harga'] ?? 0),
            ]);
        }

        // Update poin & statistik pelanggan terdaftar
        if ($pelangganId && $pelangganObj) {
            $poinDidapat = intdiv((int) $totalBayar, 10000);
            $poinDelta   = $poinDidapat - $poinDigunakan;
            if ($poinDelta !== 0) {
                DB::table('pelanggan')->where('id', $pelangganId)->increment('poin', $poinDelta);
            }
            DB::table('pelanggan')->where('id', $pelangganId)->increment('total_belanja', $totalBayar);
            DB::table('pelanggan')->where('id', $pelangganId)->increment('total_kunjungan');
            DB::table('pelanggan')->where('id', $pelangganId)->update(['terakhir_kunjungan' => now()->toDateString()]);
        }

        return redirect()->route('transaksi.show', $transaksiId)
            ->with('success', "Transaksi #{$transaksiId} berhasil dibuat. Klik 'Selesaikan' untuk memproses stok.");
    }

    // ─── SHOW ────────────────────────────────────────────────────
    public function show($id)
    {
        $transaksi = DB::table('transaksi')->where('id', $id)->firstOrFail();
        $items     = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
        return view('transaksi.show', compact('transaksi', 'items'));
    }

    // ─── UPDATE STATUS ────────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:proses,selesai,batal']);

        $transaksi   = DB::table('transaksi')->where('id', $id)->firstOrFail();
        $statusLama  = $transaksi->status;
        $statusBaru  = $request->status;

        DB::table('transaksi')->where('id', $id)->update([
            'status'     => $statusBaru,
            'updated_at' => now(),
        ]);

        // ── Kurangi stok bahan saat transaksi SELESAI ──
        if ($statusBaru === 'selesai' && $statusLama !== 'selesai') {
            $items = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
            $this->kurangiStokBahan($items);
        }

        // ── Kembalikan stok jika BATAL setelah SELESAI ──
        if ($statusBaru === 'batal' && $statusLama === 'selesai') {
            $items = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
            $this->kembalikanStokBahan($items);
        }

        $pesan = [
            'selesai' => 'Transaksi selesai. Stok bahan baku otomatis dikurangi.',
            'batal'   => 'Transaksi dibatalkan.',
            'proses'  => 'Status transaksi diperbarui.',
        ][$statusBaru] ?? 'Status transaksi diperbarui.';

        return back()->with('success', $pesan);
    }

    // ─── PRIVATE: Kurangi stok bahan berdasarkan resep ───────────
    private function kurangiStokBahan($items): void
    {
        foreach ($items as $item) {
            $namaMenu = strtolower(trim($item->nama_item));
            $qty      = (int) $item->qty;

            if (!isset($this->resepBahan[$namaMenu])) {
                \Log::warning("[STOK] Resep tidak ditemukan untuk menu: {$item->nama_item}");
                continue;
            }

            foreach ($this->resepBahan[$namaMenu] as $bahan) {
                $namaBahan  = $bahan['bahan'];
                $jumlahKurang = $bahan['jumlah'] * $qty;

                $row = DB::table('tb_bahan')
                    ->whereRaw('LOWER(nama_bahan) = ?', [strtolower($namaBahan)])
                    ->first();

                if (!$row) {
                    \Log::warning("[STOK] Bahan tidak ditemukan di database: {$namaBahan}");
                    continue;
                }

                $stokLama = $row->jumlah_stok;
                $stokBaru = max(0, $row->jumlah_stok - $jumlahKurang);
                $status   = $this->hitungStatusStok($stokBaru, $row->batas_minimum);

                DB::table('tb_bahan')->where('id_bahan', $row->id_bahan)->update([
                    'jumlah_stok'    => $stokBaru,
                    'status_stok'    => $status,
                    'tanggal_update' => now(),
                ]);

                \Log::info("[STOK] Menu: {$item->nama_item} ({$qty}x) | Bahan: {$namaBahan} | {$stokLama} - {$jumlahKurang} = {$stokBaru}");
            }
        }
    }

    private function kembalikanStokBahan($items): void
    {
        foreach ($items as $item) {
            $namaMenu = strtolower(trim($item->nama_item));
            $qty      = (int) $item->qty;

            if (!isset($this->resepBahan[$namaMenu])) {
                \Log::warning("[STOK] Resep tidak ditemukan untuk menu: {$item->nama_item}");
                continue;
            }

            foreach ($this->resepBahan[$namaMenu] as $bahan) {
                $namaBahan     = $bahan['bahan'];
                $jumlahKembali = $bahan['jumlah'] * $qty;

                $row = DB::table('tb_bahan')
                    ->whereRaw('LOWER(nama_bahan) = ?', [strtolower($namaBahan)])
                    ->first();

                if (!$row) {
                    \Log::warning("[STOK] Bahan tidak ditemukan di database: {$namaBahan}");
                    continue;
                }

                $stokLama = $row->jumlah_stok;
                $stokBaru = $row->jumlah_stok + $jumlahKembali;
                $status   = $this->hitungStatusStok($stokBaru, $row->batas_minimum);

                DB::table('tb_bahan')->where('id_bahan', $row->id_bahan)->update([
                    'jumlah_stok'    => $stokBaru,
                    'status_stok'    => $status,
                    'tanggal_update' => now(),
                ]);

                \Log::info("[STOK] Pembatalan - Menu: {$item->nama_item} ({$qty}x) | Bahan: {$namaBahan} | {$stokLama} + {$jumlahKembali} = {$stokBaru}");
            }
        }
    }

    private function hitungStatusStok(float $stok, float $min): string
    {
        if ($stok <= 0) return 'HABIS';
        if ($stok < $min) return 'RENDAH';
        return 'NORMAL';
    }

    // ─── GET MENU RESEP ──────────────────────────────────────────
    private function getMenuResep(string $namaMenu): array
    {
        $namaMenu = strtolower(trim($namaMenu));
        return $this->resepBahan[$namaMenu] ?? [];
    }

    // ─── EDIT ────────────────────────────────────────────────────
    public function edit($id)
    {
        $transaksi = DB::table('transaksi')->where('id', $id)->firstOrFail();
        $items     = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
        $pelanggan = DB::table('pelanggan')->where('status', 'aktif')->orderBy('nama')->get();

        return view('transaksi.edit', compact('transaksi', 'items', 'pelanggan'));
    }

    // ─── UPDATE (FULL) ────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $transaksi = DB::table('transaksi')->where('id', $id)->firstOrFail();

        // Jika transaksi sudah selesai, tidak bisa diedit
        if ($transaksi->status === 'selesai') {
            return back()->withErrors(['error' => 'Transaksi yang sudah selesai tidak dapat diedit.']);
        }

        $request->validate([
            'pelanggan_id' => 'nullable|integer|exists:pelanggan,id',
            'metode_bayar' => 'required|in:Tunai,Transfer,QRIS',
        ]);

        $pelangganId = $request->input('pelanggan_id') ?: null;
        $namaOrder   = 'Walk-in';
        $segmen      = 'Reguler';

        if ($pelangganId) {
            $pelangganObj = DB::table('pelanggan')->where('id', $pelangganId)->first();
            if ($pelangganObj) {
                $namaOrder = $pelangganObj->nama;
                $segmen    = $pelangganObj->segmen;
            } else {
                $pelangganId = null;
            }
        }

        DB::table('transaksi')->where('id', $id)->update([
            'nama'         => $namaOrder,
            'pelanggan_id' => $pelangganId,
            'segmen'       => $segmen,
            'metode_bayar' => $request->input('metode_bayar'),
            'updated_at'   => now(),
        ]);

        return redirect()->route('transaksi.show', $id)
            ->with('success', 'Data transaksi berhasil diperbarui.');
    }

    // ─── DESTROY (DELETE) ────────────────────────────────────────
    public function destroy($id)
    {
        $transaksi = DB::table('transaksi')->where('id', $id)->firstOrFail();

        // Jika transaksi sudah selesai, perlu kembalikan stok dulu
        if ($transaksi->status === 'selesai') {
            $items = DB::table('detail_transaksi')->where('transaksi_id', $id)->get();
            $this->kembalikanStokBahan($items);
        }

        // Hapus detail transaksi
        DB::table('detail_transaksi')->where('transaksi_id', $id)->delete();

        // Hapus transaksi
        DB::table('transaksi')->where('id', $id)->delete();

        return redirect()->route('transaksi.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
