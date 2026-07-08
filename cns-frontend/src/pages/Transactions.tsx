import { useEffect, useState } from 'react';
import { Plus, Search, DollarSign, Receipt, Clock, TrendingUp, Minus, Download, Eye, Trash2 } from 'lucide-react';
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts';
import api from '../api/client';
import type { Transaction, Product } from '../types';
import Badge from '../components/Badge';
import Modal from '../components/Modal';
import KpiCard from '../components/KpiCard';
import { formatRupiah, formatDateTime } from '../lib/format';
import { downloadCsv } from '../lib/csv';

/**
 * Halaman Transaksi & POS (laporan 4.4). Menggabungkan beberapa konsep:
 * - CRUD (Create transaksi baru, Read daftar & detail, Delete)
 * - Form dengan state keranjang belanja (object { productId: qty })
 * - Grafik (BarChart trafik per jam) dari data yang dihitung backend
 * - Export CSV (lihat src/lib/csv.ts)
 */
export default function Transactions() {
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [summary, setSummary] = useState<any>({});
  const [hourly, setHourly] = useState<{ hour: string; total: number }[]>([]);
  const [products, setProducts] = useState<Product[]>([]);
  const [showNew, setShowNew] = useState(false);
  const [cart, setCart] = useState<Record<number, number>>({});
  const [customerName, setCustomerName] = useState('');
  const [paymentMethod, setPaymentMethod] = useState<'QRIS' | 'Tunai' | 'Transfer'>('QRIS');
  const [filter, setFilter] = useState<'hari_ini' | 'semua'>('hari_ini');
  const [search, setSearch] = useState('');
  const [detailTarget, setDetailTarget] = useState<Transaction | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Transaction | null>(null);

  function load() {
    api.get('/transactions', { params: { filter, search } }).then((res) => {
      setTransactions(res.data.data);
      setSummary(res.data.summary);
      const hourlyMap = res.data.hourly_traffic || {};
      setHourly(Array.from({ length: 13 }, (_, i) => {
        const h = i + 8;
        return { hour: `${h}:00`, total: hourlyMap[h] || 0 };
      }));
    });
  }

  useEffect(() => { load(); }, [filter, search]);
  useEffect(() => { api.get('/transactions/products').then((res) => setProducts(res.data.data)); }, []);

  // Total belanja dihitung ulang otomatis setiap kali "cart" berubah
  // (reduce = menjumlahkan qty * harga untuk semua produk di keranjang)
  const total = products.reduce((sum, p) => sum + (cart[p.id] || 0) * p.price, 0);

  // Menambah/mengurangi qty satu produk di keranjang. "cart" berbentuk
  // object seperti { 3: 2, 7: 1 } artinya produk id=3 sebanyak 2, id=7 sebanyak 1.
  function updateQty(id: number, delta: number) {
    setCart((prev) => {
      const next = { ...prev, [id]: Math.max(0, (prev[id] || 0) + delta) };
      if (next[id] === 0) delete next[id]; // qty 0 -> hapus dari keranjang
      return next;
    });
  }

  // CREATE transaksi baru: ubah object cart jadi array item, lalu kirim ke API
  async function handleSubmit() {
    const items = Object.entries(cart).map(([product_id, qty]) => ({ product_id: Number(product_id), qty }));
    if (items.length === 0) return;
    await api.post('/transactions', { customer_name: customerName || undefined, payment_method: paymentMethod, items });
    setShowNew(false);
    setCart({});
    setCustomerName('');
    load();
  }

  // DELETE satu transaksi
  async function handleDelete() {
    if (!deleteTarget) return;
    await api.delete(`/transactions/${deleteTarget.id}`);
    setDeleteTarget(null);
    load();
  }

  // Export: susun data transaksi yang sedang tampil jadi baris-baris CSV,
  // lalu panggil helper downloadCsv() untuk memicu proses unduh di browser
  function handleExport() {
    const rows: (string | number)[][] = [
      ['ID Transaksi', 'Waktu', 'Pelanggan', 'Item', 'Total', 'Pembayaran', 'Status'],
      ...transactions.map((t) => [
        t.code,
        formatDateTime(t.transacted_at),
        t.customer?.name || 'Umum',
        t.items.map((i) => `${i.product_name} x${i.qty}`).join('; '),
        t.total,
        t.payment_method,
        t.status,
      ]),
    ];
    downloadCsv(`transaksi-${filter}-${new Date().toISOString().slice(0, 10)}.csv`, rows);
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Transaksi & POS</h1>
          <p className="text-sm text-slate-500">Kelola transaksi dan penjualan harian</p>
        </div>
        <div className="flex gap-2">
          <button onClick={handleExport} className="flex items-center gap-2 bg-white border border-cream-200 text-slate-600 text-sm font-medium rounded-lg px-4 py-2.5">
            <Download size={16} /> Export
          </button>
          <button onClick={() => setShowNew(true)} className="flex items-center gap-2 bg-cafe-green-700 hover:bg-cafe-green-600 text-white text-sm font-medium rounded-lg px-4 py-2.5">
            <Plus size={16} /> Transaksi Baru
          </button>
        </div>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <KpiCard label="Pendapatan Hari Ini" value={formatRupiah(summary.revenue_today || 0)} icon={DollarSign} />
        <KpiCard label="Total Transaksi" value={String(summary.total_today || 0)} icon={Receipt} />
        <KpiCard label="Dalam Proses" value={String(summary.in_progress || 0)} icon={Clock} />
        <KpiCard label="Rata-rata Order" value={formatRupiah(summary.avg_order || 0)} icon={TrendingUp} />
      </div>

      <div className="bg-white rounded-xl border border-cream-200 p-5">
        <h3 className="text-sm font-semibold text-slate-700 mb-4">Trafik Pesanan per Jam</h3>
        <ResponsiveContainer width="100%" height={180}>
          <BarChart data={hourly}>
            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3E7C9" />
            <XAxis dataKey="hour" tick={{ fontSize: 10 }} axisLine={false} tickLine={false} />
            <YAxis tick={{ fontSize: 11 }} axisLine={false} tickLine={false} width={25} />
            <Tooltip />
            <Bar dataKey="total" fill="#2E7D4F" radius={[4, 4, 0, 0]} />
          </BarChart>
        </ResponsiveContainer>
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <div className="relative flex-1 min-w-[220px]">
          <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari transaksi atau pelanggan..."
            className="w-full pl-9 pr-3 py-2.5 rounded-lg border border-cream-200 bg-white text-sm" />
        </div>
        <div className="flex gap-2">
          {(['hari_ini', 'semua'] as const).map((f) => (
            <button key={f} onClick={() => setFilter(f)} className={`text-xs font-medium rounded-full px-3.5 py-2 ${filter === f ? 'bg-cafe-green-700 text-white' : 'bg-white border border-cream-200 text-slate-600'}`}>
              {f === 'hari_ini' ? 'Hari ini' : 'Semua'}
            </button>
          ))}
        </div>
      </div>

      <div className="bg-white rounded-xl border border-cream-200 overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs text-slate-400 border-b border-cream-200">
              <th className="py-3 px-4 font-medium">ID Transaksi</th>
              <th className="py-3 px-4 font-medium">Waktu</th>
              <th className="py-3 px-4 font-medium">Pelanggan</th>
              <th className="py-3 px-4 font-medium">Item</th>
              <th className="py-3 px-4 font-medium">Total</th>
              <th className="py-3 px-4 font-medium">Pembayaran</th>
              <th className="py-3 px-4 font-medium">Status</th>
              <th className="py-3 px-4 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {transactions.map((t) => (
              <tr key={t.id} className="border-b border-cream-100 last:border-0">
                <td className="py-3 px-4 font-medium text-cafe-green-800">{t.code}</td>
                <td className="py-3 px-4 text-slate-500">{new Date(t.transacted_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</td>
                <td className="py-3 px-4">{t.customer?.name || 'Umum'}</td>
                <td className="py-3 px-4 text-slate-500 max-w-[200px] truncate">{t.items.map((i) => `${i.product_name} x${i.qty}`).join(', ')}</td>
                <td className="py-3 px-4 font-medium">{formatRupiah(t.total)}</td>
                <td className="py-3 px-4"><Badge text={t.payment_method} /></td>
                <td className="py-3 px-4"><Badge text={t.status} /></td>
                <td className="py-3 px-4">
                  <div className="flex gap-2">
                    <button onClick={() => setDetailTarget(t)} title="Detail" className="text-slate-400 hover:text-cafe-green-700"><Eye size={16} /></button>
                    <button onClick={() => setDeleteTarget(t)} title="Hapus" className="text-slate-400 hover:text-red-600"><Trash2 size={16} /></button>
                  </div>
                </td>
              </tr>
            ))}
            {transactions.length === 0 && (
              <tr><td colSpan={8} className="py-8 text-center text-sm text-slate-400">Belum ada transaksi.</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {showNew && (
        <Modal title="Transaksi Baru" onClose={() => setShowNew(false)} wide>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p className="text-sm font-medium text-slate-700 mb-3">Menu</p>
              <div className="space-y-2 max-h-72 overflow-y-auto pr-1">
                {products.map((p) => (
                  <div key={p.id} className="flex items-center justify-between bg-cream-50 rounded-lg px-3 py-2">
                    <div>
                      <p className="text-sm font-medium text-slate-700">{p.name}</p>
                      <p className="text-xs text-slate-400">{formatRupiah(p.price)}</p>
                    </div>
                    <div className="flex items-center gap-2">
                      <button onClick={() => updateQty(p.id, -1)} className="w-6 h-6 rounded-full bg-cream-200 flex items-center justify-center"><Minus size={12} /></button>
                      <span className="w-5 text-center text-sm">{cart[p.id] || 0}</span>
                      <button onClick={() => updateQty(p.id, 1)} className="w-6 h-6 rounded-full bg-cafe-green-700 text-white flex items-center justify-center"><Plus size={12} /></button>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <div className="space-y-3">
              <p className="text-sm font-medium text-slate-700">Detail Pesanan</p>
              <div>
                <label className="text-xs text-slate-500">Nama Pelanggan (opsional)</label>
                <input value={customerName} onChange={(e) => setCustomerName(e.target.value)} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" placeholder="Nama pelanggan" />
              </div>
              <div>
                <label className="text-xs text-slate-500">Metode Pembayaran</label>
                <select value={paymentMethod} onChange={(e) => setPaymentMethod(e.target.value as any)} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm">
                  {['QRIS', 'Tunai', 'Transfer'].map((m) => <option key={m}>{m}</option>)}
                </select>
              </div>
              <div className="bg-cream-100 rounded-lg p-4 flex items-center justify-between">
                <span className="text-sm text-slate-600">Total</span>
                <span className="font-semibold text-cafe-green-800">{formatRupiah(total)}</span>
              </div>
              <div className="flex gap-3">
                <button onClick={() => setShowNew(false)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
                <button onClick={handleSubmit} className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Proses Pembayaran</button>
              </div>
            </div>
          </div>
        </Modal>
      )}

      {detailTarget && (
        <Modal title={`Detail Transaksi ${detailTarget.code}`} onClose={() => setDetailTarget(null)}>
          <div className="space-y-3 text-sm">
            <div className="grid grid-cols-2 gap-3">
              <div><p className="text-xs text-slate-400">Pelanggan</p><p>{detailTarget.customer?.name || 'Umum'}</p></div>
              <div><p className="text-xs text-slate-400">Waktu</p><p>{formatDateTime(detailTarget.transacted_at)}</p></div>
              <div><p className="text-xs text-slate-400">Pembayaran</p><Badge text={detailTarget.payment_method} /></div>
              <div><p className="text-xs text-slate-400">Status</p><Badge text={detailTarget.status} /></div>
            </div>
            <div className="border-t border-cream-200 pt-3 space-y-1.5">
              {detailTarget.items.map((item) => (
                <div key={item.id} className="flex justify-between">
                  <span>{item.product_name} x{item.qty}</span>
                  <span>{formatRupiah(item.subtotal)}</span>
                </div>
              ))}
            </div>
            <div className="bg-cafe-green-700 text-white rounded-lg p-4 flex items-center justify-between">
              <span>Total</span>
              <span className="font-semibold">{formatRupiah(detailTarget.total)}</span>
            </div>
          </div>
        </Modal>
      )}

      {deleteTarget && (
        <Modal title="Hapus Transaksi" onClose={() => setDeleteTarget(null)}>
          <div className="space-y-4">
            <p className="text-sm text-slate-600">
              Yakin ingin menghapus transaksi <span className="font-medium text-slate-800">{deleteTarget.code}</span>? Tindakan ini tidak bisa dibatalkan.
            </p>
            <div className="flex gap-3">
              <button onClick={() => setDeleteTarget(null)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button onClick={handleDelete} className="flex-1 rounded-lg bg-red-600 hover:bg-red-700 text-white py-2 text-sm font-medium">Hapus</button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
