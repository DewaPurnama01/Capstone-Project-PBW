import { useEffect, useState } from 'react';
import { Plus, AlertTriangle, Handshake, PackagePlus, Trash2 } from 'lucide-react';
import { Link } from 'react-router-dom';
import api from '../api/client';
import type { InventoryItem } from '../types';
import Modal from '../components/Modal';
import { formatRupiah } from '../lib/format';

const CATEGORIES = ['Semua', 'Bahan Baku', 'Kemasan', 'Makanan'];
const STATUSES = ['Semua', 'Kritis', 'Rendah', 'Aman'];

const STATUS_BAR: Record<string, string> = {
  kritis: 'bg-red-500',
  rendah: 'bg-amber-500',
  aman: 'bg-cafe-green-600',
};

const STATUS_LABEL: Record<string, string> = { kritis: 'Kritis', rendah: 'Rendah', aman: 'Aman' };

export default function Inventory() {
  const [items, setItems] = useState<InventoryItem[]>([]);
  const [summary, setSummary] = useState<any>({});
  const [criticalItems, setCriticalItems] = useState<InventoryItem[]>([]);
  const [category, setCategory] = useState('Semua');
  const [status, setStatus] = useState('Semua');
  const [showAdd, setShowAdd] = useState(false);
  const [restockTarget, setRestockTarget] = useState<InventoryItem | null>(null);
  const [restockQty, setRestockQty] = useState('');
  const [form, setForm] = useState({ name: '', category: 'Bahan Baku', unit: 'kg', current_stock: '', min_stock: '', max_stock: '', unit_price: '', supplier_name: '' });

  function load() {
    api.get('/inventory', { params: { category, status } }).then((res) => {
      setItems(res.data.data);
      setSummary(res.data.summary);
      setCriticalItems(res.data.critical_items);
    });
  }

  useEffect(() => { load(); }, [category, status]);

  async function handleAdd(e: React.FormEvent) {
    e.preventDefault();
    await api.post('/inventory', form);
    setShowAdd(false);
    setForm({ name: '', category: 'Bahan Baku', unit: 'kg', current_stock: '', min_stock: '', max_stock: '', unit_price: '', supplier_name: '' });
    load();
  }

  async function handleRestock() {
    if (!restockTarget || !restockQty) return;
    await api.post(`/inventory/${restockTarget.id}/restock`, { qty: Number(restockQty) });
    setRestockTarget(null);
    setRestockQty('');
    load();
  }

  async function handleDelete(id: number) {
    await api.delete(`/inventory/${id}`);
    load();
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Inventori</h1>
          <p className="text-sm text-slate-500">Pantau stok bahan baku, kemasan, dan makanan Cafe CNS</p>
        </div>
        <button onClick={() => setShowAdd(true)} className="flex items-center gap-2 bg-cafe-green-700 hover:bg-cafe-green-600 text-white text-sm font-medium rounded-lg px-4 py-2.5">
          <Plus size={16} /> Tambah Item
        </button>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Total Item</p><p className="text-xl font-semibold text-cafe-green-800">{summary.total_items || 0}</p></div>
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Stok Kritis</p><p className="text-xl font-semibold text-red-600">{summary.critical || 0}</p></div>
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Stok Rendah</p><p className="text-xl font-semibold text-amber-600">{summary.low || 0}</p></div>
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Nilai Total Stok</p><p className="text-xl font-semibold text-cafe-green-800">{formatRupiah(summary.total_value || 0)}</p></div>
      </div>

      {criticalItems.length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex flex-wrap items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <AlertTriangle className="text-red-600" size={20} />
            <p className="text-sm font-medium text-red-700">{criticalItems.length} item stok kritis: {criticalItems.map((i) => i.name).join(', ')}</p>
          </div>
          {criticalItems.some((i) => i.is_coffee_bean) && (
            <Link to="/kemitraan" className="text-xs font-medium bg-red-600 text-white rounded-lg px-3 py-1.5 hover:bg-red-700">Restock Biji Kopi &rarr;</Link>
          )}
        </div>
      )}

      <div className="flex flex-wrap gap-4">
        <div className="flex gap-2">
          {CATEGORIES.map((c) => (
            <button key={c} onClick={() => setCategory(c)} className={`text-xs font-medium rounded-full px-3.5 py-2 ${category === c ? 'bg-cafe-green-700 text-white' : 'bg-white border border-cream-200 text-slate-600'}`}>{c}</button>
          ))}
        </div>
        <div className="flex gap-2">
          {STATUSES.map((s) => (
            <button key={s} onClick={() => setStatus(s)} className={`text-xs font-medium rounded-full px-3.5 py-2 ${status === s ? 'bg-cafe-green-700 text-white' : 'bg-white border border-cream-200 text-slate-600'}`}>{s}</button>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {items.map((item) => (
          <div key={item.id} className="bg-white rounded-xl border border-cream-200 p-4 space-y-3">
            <div className="flex items-center justify-between">
              <div>
                <p className="font-medium text-slate-700">{item.name}</p>
                <p className="text-xs text-slate-400">{item.category}</p>
              </div>
              <span className={`text-xs font-medium px-2.5 py-1 rounded-full ${item.stock_status === 'kritis' ? 'bg-red-100 text-red-600' : item.stock_status === 'rendah' ? 'bg-amber-100 text-amber-700' : 'bg-cafe-green-100 text-cafe-green-700'}`}>
                {STATUS_LABEL[item.stock_status]}
              </span>
            </div>
            <div>
              <div className="flex justify-between text-xs text-slate-500 mb-1">
                <span>Stok saat ini</span><span>{item.current_stock} {item.unit}</span>
              </div>
              <div className="h-2 rounded-full bg-cream-100 overflow-hidden">
                <div className={`h-full ${STATUS_BAR[item.stock_status]}`} style={{ width: `${item.stock_percent}%` }} />
              </div>
              <p className="text-xs text-slate-400 mt-1">Min {item.min_stock} &middot; Maks {item.max_stock}</p>
            </div>
            <div className="flex items-center justify-between text-xs text-slate-500">
              <span>{item.supplier_name || '-'}</span>
              <span>{formatRupiah(item.unit_price)}/{item.unit}</span>
            </div>
            <div className="flex gap-2">
              {item.is_coffee_bean ? (
                <Link to="/kemitraan" className="flex-1 text-center text-xs font-medium bg-cafe-green-100 text-cafe-green-700 rounded-lg py-2 flex items-center justify-center gap-1.5">
                  <Handshake size={13} /> Restock via Portal Kemitraan
                </Link>
              ) : (
                <button onClick={() => setRestockTarget(item)} className="flex-1 text-xs font-medium bg-cafe-green-700 text-white rounded-lg py-2 flex items-center justify-center gap-1.5">
                  <PackagePlus size={13} /> Tambah Stok Manual
                </button>
              )}
              <button onClick={() => handleDelete(item.id)} className="px-2.5 rounded-lg bg-red-50 text-red-500"><Trash2 size={14} /></button>
            </div>
          </div>
        ))}
      </div>

      {showAdd && (
        <Modal title="Tambah Item Inventori" onClose={() => setShowAdd(false)}>
          <form onSubmit={handleAdd} className="space-y-3">
            <input placeholder="Nama item" value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
            <select value={form.category} onChange={(e) => setForm({ ...form, category: e.target.value })} className="w-full rounded-lg border border-cream-200 px-3 py-2 text-sm">
              {['Bahan Baku', 'Kemasan', 'Makanan'].map((c) => <option key={c}>{c}</option>)}
            </select>
            <div className="grid grid-cols-2 gap-3">
              <input placeholder="Satuan (kg/liter/pcs)" value={form.unit} onChange={(e) => setForm({ ...form, unit: e.target.value })} className="rounded-lg border border-cream-200 px-3 py-2 text-sm" />
              <input placeholder="Harga/satuan" type="number" value={form.unit_price} onChange={(e) => setForm({ ...form, unit_price: e.target.value })} className="rounded-lg border border-cream-200 px-3 py-2 text-sm" />
            </div>
            <div className="grid grid-cols-3 gap-3">
              <input placeholder="Stok saat ini" type="number" value={form.current_stock} onChange={(e) => setForm({ ...form, current_stock: e.target.value })} className="rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
              <input placeholder="Stok minimum" type="number" value={form.min_stock} onChange={(e) => setForm({ ...form, min_stock: e.target.value })} className="rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
              <input placeholder="Stok maksimum" type="number" value={form.max_stock} onChange={(e) => setForm({ ...form, max_stock: e.target.value })} className="rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
            </div>
            <input placeholder="Nama supplier" value={form.supplier_name} onChange={(e) => setForm({ ...form, supplier_name: e.target.value })} className="w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" />
            <div className="flex gap-3 pt-2">
              <button type="button" onClick={() => setShowAdd(false)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button type="submit" className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Simpan</button>
            </div>
          </form>
        </Modal>
      )}

      {restockTarget && (
        <Modal title={`Restock ${restockTarget.name}`} onClose={() => setRestockTarget(null)}>
          <div className="space-y-3">
            <input placeholder={`Jumlah (${restockTarget.unit})`} type="number" value={restockQty} onChange={(e) => setRestockQty(e.target.value)} className="w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" autoFocus />
            <div className="flex gap-3 pt-2">
              <button onClick={() => setRestockTarget(null)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button onClick={handleRestock} className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Simpan</button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
