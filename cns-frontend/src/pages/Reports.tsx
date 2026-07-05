import { useEffect, useState } from 'react';
import { Download, TrendingUp } from 'lucide-react';
import {
  BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid,
  PieChart, Pie, Cell, LineChart, Line,
} from 'recharts';
import api from '../api/client';
import LiveClock from '../components/LiveClock';
import { formatRupiah } from '../lib/format';
import { downloadCsv } from '../lib/csv';

const TABS = ['Keuangan', 'Produk', 'Pelanggan', 'Supplier'];
const PIE_COLORS = ['#1B4D2E', '#2E7D4F', '#F3E7C9'];
const RANGE_OPTIONS = [
  { label: '3 Bulan Terakhir', value: 3 },
  { label: '6 Bulan Terakhir', value: 6 },
  { label: '12 Bulan Terakhir', value: 12 },
];

export default function Reports() {
  const [data, setData] = useState<any>(null);
  const [tab, setTab] = useState(0);
  const [months, setMonths] = useState(6);

  useEffect(() => {
    setData(null);
    api.get('/reports', { params: { months } }).then((res) => setData(res.data));
  }, [months]);

  if (!data) return <p className="text-slate-400 text-sm">Memuat laporan...</p>;

  const paymentMix = Object.entries(data.finance.payment_mix).map(([name, value]) => ({ name, value }));

  function handleExport() {
    if (tab === 0) {
      downloadCsv(`laporan-keuangan-${months}bln.csv`, [
        ['Bulan', 'Pendapatan', 'Target', 'Jumlah Transaksi'],
        ...data.finance.series.map((s: any) => [s.label, s.revenue, s.target, s.transactions]),
      ]);
    } else if (tab === 1) {
      downloadCsv('laporan-produk.csv', [
        ['Produk', 'Qty Terjual', 'Pendapatan', 'Margin (%)'],
        ...data.product.products.map((p: any) => [p.name, p.qty_sold, p.revenue, p.margin_percent]),
      ]);
    } else if (tab === 2) {
      downloadCsv(`laporan-pelanggan-${months}bln.csv`, [
        ['Bulan', 'Pelanggan Aktif', 'Pelanggan Baru'],
        ...data.customer.growth.map((g: any) => [g.label, g.active_customers, g.new_customers]),
      ]);
    } else {
      downloadCsv('laporan-supplier.csv', [
        ['Petani Mitra', 'Ketepatan Waktu (%)', 'Skor Kualitas'],
        ...data.supplier.partners.map((p: any) => [p.name, p.on_time_rate, p.quality_score]),
      ]);
    }
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Laporan &amp; Analitik</h1>
          <p className="text-sm text-slate-500">Analisis performa bisnis Cafe CNS secara komprehensif</p>
        </div>
        <div className="flex items-center gap-2">
          <LiveClock />
          <select
            value={months}
            onChange={(e) => setMonths(Number(e.target.value))}
            className="text-sm border border-cream-200 rounded-lg px-3 py-2.5 bg-white text-slate-600"
          >
            {RANGE_OPTIONS.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
          </select>
          <button onClick={handleExport} className="flex items-center gap-2 bg-white border border-cream-200 text-slate-600 text-sm font-medium rounded-lg px-4 py-2.5">
            <Download size={16} /> Export Laporan
          </button>
        </div>
      </div>

      <div className="flex gap-2 bg-white rounded-xl border border-cream-200 p-1.5 w-fit">
        {TABS.map((t, i) => (
          <button key={t} onClick={() => setTab(i)} className={`text-sm font-medium rounded-lg px-4 py-2 ${tab === i ? 'bg-cafe-green-700 text-white' : 'text-slate-500'}`}>{t}</button>
        ))}
      </div>

      {tab === 0 && (
        <div className="space-y-5">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div className="bg-white rounded-xl border border-cream-200 p-4">
              <p className="text-xs text-slate-500">Total Pendapatan ({months} Bln)</p>
              <p className="text-xl font-semibold text-cafe-green-800">{formatRupiah(data.finance.total_revenue)}</p>
            </div>
          </div>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div className="bg-white rounded-xl border border-cream-200 p-5">
              <h3 className="text-sm font-semibold text-slate-700 mb-4">Pendapatan vs Target ({months} Bulan)</h3>
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={data.finance.series}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3E7C9" />
                  <XAxis dataKey="label" tick={{ fontSize: 12 }} axisLine={false} tickLine={false} />
                  <YAxis tick={{ fontSize: 11 }} axisLine={false} tickLine={false} width={40} tickFormatter={(v) => `${v / 1000000}jt`} />
                  <Tooltip formatter={(v: any) => formatRupiah(Number(v ?? 0))} />
                  <Bar dataKey="revenue" fill="#1B4D2E" radius={[4, 4, 0, 0]} name="Pendapatan" />
                  <Bar dataKey="target" fill="#F3E7C9" radius={[4, 4, 0, 0]} name="Target" />
                </BarChart>
              </ResponsiveContainer>
            </div>
            <div className="bg-white rounded-xl border border-cream-200 p-5">
              <h3 className="text-sm font-semibold text-slate-700 mb-4">Mix Pembayaran</h3>
              <ResponsiveContainer width="100%" height={220}>
                <PieChart>
                  <Pie data={paymentMix} dataKey="value" nameKey="name" innerRadius={45} outerRadius={75}>
                    {paymentMix.map((_, i) => <Cell key={i} fill={PIE_COLORS[i % PIE_COLORS.length]} />)}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </div>
          </div>
          <div className="bg-white rounded-xl border border-cream-200 p-5">
            <h3 className="text-sm font-semibold text-slate-700 mb-4">Tren Jumlah Transaksi</h3>
            <ResponsiveContainer width="100%" height={200}>
              <LineChart data={data.finance.series}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3E7C9" />
                <XAxis dataKey="label" tick={{ fontSize: 12 }} axisLine={false} tickLine={false} />
                <YAxis tick={{ fontSize: 11 }} axisLine={false} tickLine={false} width={40} />
                <Tooltip />
                <Line type="monotone" dataKey="transactions" stroke="#1B4D2E" strokeWidth={2} dot={false} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}

      {tab === 1 && (
        <div className="bg-white rounded-xl border border-cream-200 overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-xs text-slate-400 border-b border-cream-200">
                <th className="py-3 px-4 font-medium">Produk</th>
                <th className="py-3 px-4 font-medium">Qty Terjual</th>
                <th className="py-3 px-4 font-medium">Pendapatan</th>
                <th className="py-3 px-4 font-medium">Margin</th>
              </tr>
            </thead>
            <tbody>
              {data.product.products.map((p: any) => (
                <tr key={p.name} className="border-b border-cream-100 last:border-0">
                  <td className="py-3 px-4 font-medium text-slate-700">{p.name}</td>
                  <td className="py-3 px-4">{p.qty_sold}</td>
                  <td className="py-3 px-4">{formatRupiah(p.revenue)}</td>
                  <td className="py-3 px-4"><span className="text-cafe-green-700 font-medium">{p.margin_percent}%</span></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {tab === 2 && (
        <div className="space-y-5">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Retention Rate</p><p className="text-xl font-semibold text-cafe-green-800">{data.customer.retention_rate}%</p></div>
            <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Average Lifetime Value</p><p className="text-xl font-semibold text-cafe-green-800">{formatRupiah(data.customer.average_lifetime_value)}</p></div>
            <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Churn Rate</p><p className="text-xl font-semibold text-red-600">{data.customer.churn_rate}%</p></div>
          </div>
          <div className="bg-white rounded-xl border border-cream-200 p-5">
            <h3 className="text-sm font-semibold text-slate-700 mb-4">Tren Pertumbuhan Pelanggan</h3>
            <ResponsiveContainer width="100%" height={220}>
              <LineChart data={data.customer.growth}>
                <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3E7C9" />
                <XAxis dataKey="label" tick={{ fontSize: 12 }} axisLine={false} tickLine={false} />
                <YAxis tick={{ fontSize: 11 }} axisLine={false} tickLine={false} width={30} />
                <Tooltip />
                <Line type="monotone" dataKey="active_customers" stroke="#1B4D2E" strokeWidth={2} name="Aktif" dot={false} />
                <Line type="monotone" dataKey="new_customers" stroke="#3F9963" strokeWidth={2} name="Baru" dot={false} />
              </LineChart>
            </ResponsiveContainer>
          </div>
        </div>
      )}

      {tab === 3 && (
        <div className="bg-white rounded-xl border border-cream-200 overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-xs text-slate-400 border-b border-cream-200">
                <th className="py-3 px-4 font-medium">Petani Mitra</th>
                <th className="py-3 px-4 font-medium">Ketepatan Waktu</th>
                <th className="py-3 px-4 font-medium">Skor Kualitas</th>
              </tr>
            </thead>
            <tbody>
              {data.supplier.partners.map((p: any) => (
                <tr key={p.name} className="border-b border-cream-100 last:border-0">
                  <td className="py-3 px-4 font-medium text-slate-700">{p.name}</td>
                  <td className="py-3 px-4 flex items-center gap-1.5"><TrendingUp size={13} className="text-cafe-green-600" /> {p.on_time_rate}%</td>
                  <td className="py-3 px-4">{p.quality_score}/100</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
