import { useEffect, useState } from 'react';
import { Plus, Search, Eye, Pencil, Star, Trash2 } from 'lucide-react';
import api from '../api/client';
import type { Customer } from '../types';
import Badge from '../components/Badge';
import Modal from '../components/Modal';
import { formatRupiah, formatDate } from '../lib/format';

const SEGMENTS = ['Semua', 'VIP', 'Member', 'Reguler', 'Baru'];

type CustomerForm = { name: string; phone: string; email: string; segment: string; favorite_menu: string };

const EMPTY_FORM: CustomerForm = { name: '', phone: '', email: '', segment: 'Baru', favorite_menu: '' };

export default function Customers() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [summary, setSummary] = useState<Record<string, number>>({});
  const [segment, setSegment] = useState('Semua');
  const [search, setSearch] = useState('');
  const [showAdd, setShowAdd] = useState(false);
  const [editTarget, setEditTarget] = useState<Customer | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Customer | null>(null);
  const [detail, setDetail] = useState<Customer | null>(null);
  const [form, setForm] = useState<CustomerForm>(EMPTY_FORM);

  function load() {
    api.get('/customers', { params: { segment, search } }).then((res) => {
      setCustomers(res.data.data);
      setSummary(res.data.summary);
    });
  }

  useEffect(() => { load(); }, [segment, search]);

  async function handleAdd(e: React.FormEvent) {
    e.preventDefault();
    await api.post('/customers', form);
    setShowAdd(false);
    setForm(EMPTY_FORM);
    load();
  }

  function openEdit(c: Customer) {
    setEditTarget(c);
    setForm({
      name: c.name,
      phone: c.phone || '',
      email: c.email || '',
      segment: c.segment,
      favorite_menu: c.favorite_menu || '',
    });
  }

  async function handleEdit(e: React.FormEvent) {
    e.preventDefault();
    if (!editTarget) return;
    await api.put(`/customers/${editTarget.id}`, form);
    setEditTarget(null);
    setForm(EMPTY_FORM);
    load();
  }

  async function handleDelete() {
    if (!deleteTarget) return;
    await api.delete(`/customers/${deleteTarget.id}`);
    setDeleteTarget(null);
    load();
  }

  const FIELD_LABELS: Record<string, string> = {
    name: 'Nama Lengkap', phone: 'Nomor Telepon', email: 'Email', favorite_menu: 'Menu Favorit',
  };

  function renderForm(onSubmit: (e: React.FormEvent) => void, onCancel: () => void, submitLabel: string) {
    return (
      <form onSubmit={onSubmit} className="space-y-3">
        {(['name', 'phone', 'email', 'favorite_menu'] as const).map((field) => (
          <div key={field}>
            <label className="text-sm font-medium text-slate-700">{FIELD_LABELS[field]}</label>
            <input
              value={form[field]}
              onChange={(e) => setForm({ ...form, [field]: e.target.value })}
              className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm"
              required={field === 'name'}
            />
          </div>
        ))}
        <div>
          <label className="text-sm font-medium text-slate-700">Segmen</label>
          <select
            value={form.segment}
            onChange={(e) => setForm({ ...form, segment: e.target.value })}
            className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm"
          >
            {['Baru', 'Reguler', 'Member', 'VIP'].map((s) => <option key={s}>{s}</option>)}
          </select>
        </div>
        <div className="flex gap-3 pt-2">
          <button type="button" onClick={onCancel} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
          <button type="submit" className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">{submitLabel}</button>
        </div>
      </form>
    );
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Manajemen Pelanggan</h1>
          <p className="text-sm text-slate-500">Kelola data & loyalitas pelanggan Cafe CNS</p>
        </div>
        <button
          onClick={() => { setForm(EMPTY_FORM); setShowAdd(true); }}
          className="flex items-center gap-2 bg-cafe-green-700 hover:bg-cafe-green-600 text-white text-sm font-medium rounded-lg px-4 py-2.5"
        >
          <Plus size={16} /> Tambah Pelanggan
        </button>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {[
          ['Total', summary.total],
          ['VIP', summary.vip],
          ['Member', summary.member],
          ['Reguler', summary.reguler],
        ].map(([label, value]) => (
          <div key={label as string} className="bg-white rounded-xl border border-cream-200 p-4">
            <p className="text-xs text-slate-500">{label}</p>
            <p className="text-xl font-semibold text-cafe-green-800">{value ?? 0}</p>
          </div>
        ))}
      </div>

      <div className="flex flex-wrap items-center gap-3">
        <div className="relative flex-1 min-w-[220px]">
          <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Cari nama, email, atau telepon..."
            className="w-full pl-9 pr-3 py-2.5 rounded-lg border border-cream-200 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-cafe-green-500"
          />
        </div>
        <div className="flex gap-2">
          {SEGMENTS.map((s) => (
            <button
              key={s}
              onClick={() => setSegment(s)}
              className={`text-xs font-medium rounded-full px-3.5 py-2 transition-colors ${
                segment === s ? 'bg-cafe-green-700 text-white' : 'bg-white border border-cream-200 text-slate-600'
              }`}
            >
              {s}
            </button>
          ))}
        </div>
      </div>

      <div className="bg-white rounded-xl border border-cream-200 overflow-x-auto">
        <table className="w-full text-sm">
          <thead>
            <tr className="text-left text-xs text-slate-400 border-b border-cream-200">
              <th className="py-3 px-4 font-medium">Pelanggan</th>
              <th className="py-3 px-4 font-medium">Kontak</th>
              <th className="py-3 px-4 font-medium">Segmen</th>
              <th className="py-3 px-4 font-medium">Poin</th>
              <th className="py-3 px-4 font-medium">Kunjungan</th>
              <th className="py-3 px-4 font-medium">Total Belanja</th>
              <th className="py-3 px-4 font-medium">Aksi</th>
            </tr>
          </thead>
          <tbody>
            {customers.map((c) => (
              <tr key={c.id} className="border-b border-cream-100 last:border-0">
                <td className="py-3 px-4">
                  <div className="flex items-center gap-2.5">
                    <div className="w-8 h-8 rounded-full bg-cafe-green-700 text-white flex items-center justify-center text-xs font-semibold">
                      {c.name.charAt(0)}
                    </div>
                    <div>
                      <p className="font-medium text-slate-700">{c.name}</p>
                      <p className="text-xs text-slate-400">Menu fav: {c.favorite_menu || '-'}</p>
                    </div>
                  </div>
                </td>
                <td className="py-3 px-4 text-slate-600">
                  <p>{c.phone}</p>
                  <p className="text-xs text-slate-400">{c.email}</p>
                </td>
                <td className="py-3 px-4"><Badge text={c.segment} /></td>
                <td className="py-3 px-4">
                  <span className="flex items-center gap-1 text-amber-600"><Star size={12} fill="currentColor" /> {c.loyalty_points}</span>
                </td>
                <td className="py-3 px-4 text-slate-600">{c.visit_count}x</td>
                <td className="py-3 px-4 font-medium text-slate-700">{formatRupiah(c.total_spent)}</td>
                <td className="py-3 px-4">
                  <div className="flex gap-2">
                    <button onClick={() => setDetail(c)} title="Detail" className="text-slate-400 hover:text-cafe-green-700"><Eye size={16} /></button>
                    <button onClick={() => openEdit(c)} title="Edit" className="text-slate-400 hover:text-cafe-green-700"><Pencil size={16} /></button>
                    <button onClick={() => setDeleteTarget(c)} title="Hapus" className="text-slate-400 hover:text-red-600"><Trash2 size={16} /></button>
                  </div>
                </td>
              </tr>
            ))}
            {customers.length === 0 && (
              <tr><td colSpan={7} className="py-8 text-center text-sm text-slate-400">Belum ada pelanggan.</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {showAdd && (
        <Modal title="Tambah Pelanggan Baru" onClose={() => setShowAdd(false)}>
          {renderForm(handleAdd, () => setShowAdd(false), 'Simpan Pelanggan')}
        </Modal>
      )}

      {editTarget && (
        <Modal title={`Edit Pelanggan — ${editTarget.name}`} onClose={() => setEditTarget(null)}>
          {renderForm(handleEdit, () => setEditTarget(null), 'Simpan Perubahan')}
        </Modal>
      )}

      {deleteTarget && (
        <Modal title="Hapus Pelanggan" onClose={() => setDeleteTarget(null)}>
          <div className="space-y-4">
            <p className="text-sm text-slate-600">
              Yakin ingin menghapus <span className="font-medium text-slate-800">{deleteTarget.name}</span>? Tindakan ini tidak bisa dibatalkan.
            </p>
            <div className="flex gap-3">
              <button onClick={() => setDeleteTarget(null)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button onClick={handleDelete} className="flex-1 rounded-lg bg-red-600 hover:bg-red-700 text-white py-2 text-sm font-medium">Hapus</button>
            </div>
          </div>
        </Modal>
      )}

      {detail && (
        <Modal title="Detail Pelanggan" onClose={() => setDetail(null)}>
          <div className="space-y-3">
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-full bg-cafe-green-700 text-white flex items-center justify-center font-semibold">
                {detail.name.charAt(0)}
              </div>
              <div>
                <p className="font-semibold text-slate-800">{detail.name}</p>
                <Badge text={detail.segment} />
              </div>
            </div>
            <div className="grid grid-cols-2 gap-3 text-sm">
              <div><p className="text-slate-400 text-xs">Telepon</p><p>{detail.phone || '-'}</p></div>
              <div><p className="text-slate-400 text-xs">Email</p><p>{detail.email || '-'}</p></div>
              <div><p className="text-slate-400 text-xs">Bergabung</p><p>{formatDate(detail.joined_at)}</p></div>
              <div><p className="text-slate-400 text-xs">Menu Favorit</p><p>{detail.favorite_menu || '-'}</p></div>
              <div><p className="text-slate-400 text-xs">Poin Loyalitas</p><p>{detail.loyalty_points} poin</p></div>
              <div><p className="text-slate-400 text-xs">Total Kunjungan</p><p>{detail.visit_count}x</p></div>
            </div>
            <div className="bg-cafe-green-700 text-white rounded-lg p-4 flex items-center justify-between">
              <span className="text-sm">Total Pengeluaran</span>
              <span className="font-semibold">{formatRupiah(detail.total_spent)}</span>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
