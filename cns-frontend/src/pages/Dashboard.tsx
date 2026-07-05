import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { DollarSign, Users, Receipt, AlertTriangle } from 'lucide-react';
import {
  AreaChart, Area, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid,
  PieChart, Pie, Cell, BarChart, Bar,
} from 'recharts';
import api from '../api/client';
import KpiCard from '../components/KpiCard';
import Badge from '../components/Badge';
import LiveClock from '../components/LiveClock';
import { formatRupiah } from '../lib/format';

const PIE_COLORS = ['#1B4D2E', '#2E7D4F', '#3F9963', '#F3E7C9'];
const REFRESH_INTERVAL_MS = 30000;

interface DashboardData {
  kpi: { revenue_today: number; total_customers: number; transactions_today: number; low_stock_alerts: number };
  weekly_revenue: { label: string; revenue: number }[];
  customer_segmentation: Record<string, number>;
  top_products: { product_name: string; total_qty: number }[];
  recent_transactions: { id: string; customer: string; total: number; status: string; time: string }[];
  low_stock_items: { id: number; name: string; current_stock: number; min_stock: number; unit: string; is_coffee_bean: boolean }[];
}

export default function Dashboard() {
  const [data, setData] = useState<DashboardData | null>(null);
  const [loading, setLoading] = useState(true);
  const [lastUpdated, setLastUpdated] = useState<Date | null>(null);

  function load() {
    api.get('/dashboard').then((res) => {
      setData(res.data);
      setLastUpdated(new Date());
    }).finally(() => setLoading(false));
  }

  useEffect(() => {
    load();
    const id = setInterval(load, REFRESH_INTERVAL_MS);
    return () => clearInterval(id);
  }, []);

  if (loading || !data) {
    return <p className="text-slate-400 text-sm">Memuat dashboard...</p>;
  }

  const segmentData = Object.entries(data.customer_segmentation).map(([name, value]) => ({ name, value }));

  return (
    <div className="space-y-6">
      <div className="flex items-start justify-between flex-wrap gap-3">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Dashboard</h1>
          <p className="text-sm text-slate-500">Ringkasan operasional harian Cafe CNS</p>
        </div>
        <div className="flex flex-col items-end gap-1">
          <LiveClock />
          {lastUpdated && (
            <p className="text-xs text-slate-400">
              Data diperbarui otomatis tiap 30 detik &middot; terakhir {lastUpdated.toLocaleTimeString('id-ID')}
            </p>
          )}
        </div>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <KpiCard label="Pendapatan Hari Ini" value={formatRupiah(data.kpi.revenue_today)} icon={DollarSign} />
        <KpiCard label="Total Pelanggan" value={String(data.kpi.total_customers)} icon={Users} />
        <KpiCard label="Total Transaksi" value={String(data.kpi.transactions_today)} icon={Receipt} />
        <KpiCard label="Peringatan Stok" value={String(data.kpi.low_stock_alerts)} icon={AlertTriangle} tone="warning" />
      </div>

      {data.low_stock_items.length > 0 && (
        <div className="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex flex-wrap items-center justify-between gap-3">
          <div className="flex items-center gap-3">
            <AlertTriangle className="text-red-600" size={20} />
            <div>
              <p className="text-sm font-medium text-red-700">
                {data.low_stock_items.length} item stok kritis: {data.low_stock_items.map((i) => i.name).join(', ')}
              </p>
              <p className="text-xs text-red-500">Segera lakukan restock agar operasional tidak terganggu.</p>
            </div>
          </div>
          <div className="flex gap-2">
            <Link to="/inventori" className="text-xs font-medium bg-white border border-red-300 text-red-700 rounded-lg px-3 py-1.5 hover:bg-red-100">
              Buka Inventori
            </Link>
            {data.low_stock_items.some((i) => i.is_coffee_bean) && (
              <Link to="/kemitraan" className="text-xs font-medium bg-red-600 text-white rounded-lg px-3 py-1.5 hover:bg-red-700">
                Portal Kemitraan
              </Link>
            )}
          </div>
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div className="lg:col-span-2 bg-white rounded-xl border border-cream-200 p-5">
          <h3 className="text-sm font-semibold text-slate-700 mb-4">Tren Pendapatan Mingguan</h3>
          <ResponsiveContainer width="100%" height={220}>
            <AreaChart data={data.weekly_revenue}>
              <defs>
                <linearGradient id="rev" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stopColor="#2E7D4F" stopOpacity={0.4} />
                  <stop offset="100%" stopColor="#2E7D4F" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3E7C9" />
              <XAxis dataKey="label" tick={{ fontSize: 12 }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11 }} axisLine={false} tickLine={false} width={40} tickFormatter={(v) => `${v / 1000}k`} />
              <Tooltip formatter={(v: any) => formatRupiah(Number(v ?? 0))} />
              <Area type="monotone" dataKey="revenue" stroke="#1B4D2E" fill="url(#rev)" strokeWidth={2} />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        <div className="bg-white rounded-xl border border-cream-200 p-5">
          <h3 className="text-sm font-semibold text-slate-700 mb-4">Segmentasi Pelanggan</h3>
          <ResponsiveContainer width="100%" height={220}>
            <PieChart>
              <Pie data={segmentData} dataKey="value" nameKey="name" innerRadius={45} outerRadius={75} paddingAngle={2}>
                {segmentData.map((_, i) => <Cell key={i} fill={PIE_COLORS[i % PIE_COLORS.length]} />)}
              </Pie>
              <Tooltip />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div className="bg-white rounded-xl border border-cream-200 p-5">
          <h3 className="text-sm font-semibold text-slate-700 mb-4">Produk Terlaris</h3>
          <ResponsiveContainer width="100%" height={220}>
            <BarChart data={data.top_products}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#F3E7C9" />
              <XAxis dataKey="product_name" tick={{ fontSize: 10 }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fontSize: 11 }} axisLine={false} tickLine={false} width={30} />
              <Tooltip />
              <Bar dataKey="total_qty" fill="#2E7D4F" radius={[6, 6, 0, 0]} />
            </BarChart>
          </ResponsiveContainer>
        </div>

        <div className="lg:col-span-2 bg-white rounded-xl border border-cream-200 p-5">
          <h3 className="text-sm font-semibold text-slate-700 mb-4">Transaksi Terbaru</h3>
          <table className="w-full text-sm">
            <thead>
              <tr className="text-left text-xs text-slate-400 border-b border-cream-200">
                <th className="py-2 font-medium">ID</th>
                <th className="py-2 font-medium">Pelanggan</th>
                <th className="py-2 font-medium">Waktu</th>
                <th className="py-2 font-medium">Total</th>
                <th className="py-2 font-medium">Status</th>
              </tr>
            </thead>
            <tbody>
              {data.recent_transactions.map((t) => (
                <tr key={t.id} className="border-b border-cream-100 last:border-0">
                  <td className="py-2.5 font-medium text-cafe-green-800">{t.id}</td>
                  <td className="py-2.5">{t.customer}</td>
                  <td className="py-2.5 text-slate-500">{t.time}</td>
                  <td className="py-2.5">{formatRupiah(t.total)}</td>
                  <td className="py-2.5"><Badge text={t.status} /></td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}
