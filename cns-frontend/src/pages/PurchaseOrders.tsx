import { useEffect, useState } from 'react';
import { Download, CreditCard, PackageCheck } from 'lucide-react';
import api from '../api/client';
import type { PurchaseOrder } from '../types';
import Badge from '../components/Badge';
import Modal from '../components/Modal';
import { formatRupiah, formatDate } from '../lib/format';
import { downloadCsv } from '../lib/csv';

const STATUSES = ['Semua', 'Dikirim', 'QC Lulus', 'Selesai', 'Retur'];

export default function PurchaseOrders() {
  const [orders, setOrders] = useState<PurchaseOrder[]>([]);
  const [summary, setSummary] = useState<any>({});
  const [status, setStatus] = useState('Semua');
  const [payTarget, setPayTarget] = useState<PurchaseOrder | null>(null);
  const [payAmount, setPayAmount] = useState('');
  const [receiveTarget, setReceiveTarget] = useState<PurchaseOrder | null>(null);
  const [qcScore, setQcScore] = useState('');

  function load() {
    api.get('/purchase-orders', { params: { status } }).then((res) => {
      setOrders(res.data.data);
      setSummary(res.data.summary);
    });
  }

  useEffect(() => { load(); }, [status]);

  async function handlePay() {
    if (!payTarget || !payAmount) return;
    await api.post(`/purchase-orders/${payTarget.id}/payments`, { amount: Number(payAmount), method: 'Transfer' });
    setPayTarget(null);
    setPayAmount('');
    load();
  }

  async function handleReceive() {
    if (!receiveTarget || !qcScore) return;
    await api.post(`/purchase-orders/${receiveTarget.id}/receive`, { quality_score: Number(qcScore) });
    setReceiveTarget(null);
    setQcScore('');
    load();
  }

  function handleExport() {
    const rows: (string | number)[][] = [
      ['Kode PO', 'Referensi', 'Petani', 'Item', 'Qty', 'Satuan', 'Harga Satuan', 'Total', 'Status Pengiriman', 'Status Pembayaran', 'Est. Kirim'],
      ...orders.map((po) => [
        po.code,
        po.reference_code || '-',
        po.partner.name,
        po.unit,
        po.qty,
        po.unit,
        po.unit_price,
        po.total,
        po.delivery_status,
        po.payment_status,
        formatDate(po.estimated_delivery),
      ]),
    ];
    downloadCsv(`purchase-orders-${new Date().toISOString().slice(0, 10)}.csv`, rows);
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Purchase Orders</h1>
          <p className="text-sm text-slate-500">Pantau status pemesanan bahan baku dari petani mitra</p>
        </div>
        <button onClick={handleExport} className="flex items-center gap-2 bg-white border border-cream-200 text-slate-600 text-sm font-medium rounded-lg px-4 py-2.5">
          <Download size={16} /> Export PO
        </button>
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Total PO</p><p className="text-xl font-semibold text-cafe-green-800">{summary.total || 0}</p></div>
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Sedang Dikirim</p><p className="text-xl font-semibold text-blue-600">{summary.dikirim || 0}</p></div>
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Hutang Belum Bayar</p><p className="text-xl font-semibold text-red-600">{formatRupiah(summary.belum_bayar_amount || 0)}</p></div>
        <div className="bg-white rounded-xl border border-cream-200 p-4"><p className="text-xs text-slate-500">Selesai Bulan Ini</p><p className="text-xl font-semibold text-cafe-green-800">{summary.selesai_bulan_ini || 0}</p></div>
      </div>

      {summary.belum_bayar_amount > 0 && (
        <div className="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 text-sm text-amber-700">
          Terdapat hutang pembelian sebesar {formatRupiah(summary.belum_bayar_amount)} yang belum dibayar. Segera lakukan pembayaran ke petani mitra.
        </div>
      )}

      <div className="flex gap-2">
        {STATUSES.map((s) => (
          <button key={s} onClick={() => setStatus(s)} className={`text-xs font-medium rounded-full px-3.5 py-2 ${status === s ? 'bg-cafe-green-700 text-white' : 'bg-white border border-cream-200 text-slate-600'}`}>{s}</button>
        ))}
      </div>

      <div className="space-y-4">
        {orders.map((po) => (
          <div key={po.id} className="bg-white rounded-xl border border-cream-200 p-5">
            <div className="flex flex-wrap items-center justify-between gap-3 mb-3">
              <div>
                <p className="font-medium text-slate-700">{po.code} {po.reference_code && <span className="text-xs text-slate-400 font-normal">Ref. {po.reference_code}</span>}</p>
              </div>
              <div className="flex gap-2">
                <Badge text={po.delivery_status} />
                <Badge text={po.payment_status} />
              </div>
            </div>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm mb-4">
              <div><p className="text-xs text-slate-400">Petani</p><p>{po.partner.name}</p></div>
              <div><p className="text-xs text-slate-400">Item</p><p>{po.qty} {po.unit}</p></div>
              <div><p className="text-xs text-slate-400">Est. Kirim</p><p>{formatDate(po.estimated_delivery)}</p></div>
              <div><p className="text-xs text-slate-400">Total</p><p className="font-medium">{formatRupiah(po.total)}</p></div>
            </div>
            <div className="flex gap-2">
              {po.payment_status !== 'lunas' && (
                <button onClick={() => setPayTarget(po)} className="text-xs font-medium bg-cafe-green-700 text-white rounded-lg px-3 py-2 flex items-center gap-1.5">
                  <CreditCard size={13} /> Catat Pembayaran
                </button>
              )}
              {(po.delivery_status === 'dikirim') && (
                <button onClick={() => setReceiveTarget(po)} className="text-xs font-medium bg-blue-600 text-white rounded-lg px-3 py-2 flex items-center gap-1.5">
                  <PackageCheck size={13} /> Konfirmasi Penerimaan
                </button>
              )}
            </div>
          </div>
        ))}
      </div>

      {payTarget && (
        <Modal title={`Catat Pembayaran ${payTarget.code}`} onClose={() => setPayTarget(null)}>
          <div className="space-y-3">
            <p className="text-sm text-slate-500">Sisa tagihan: <span className="font-medium text-slate-700">{formatRupiah(payTarget.remaining_amount ?? payTarget.total)}</span></p>
            <input type="number" placeholder="Jumlah pembayaran" value={payAmount} onChange={(e) => setPayAmount(e.target.value)} className="w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" autoFocus />
            <div className="flex gap-3 pt-2">
              <button onClick={() => setPayTarget(null)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button onClick={handlePay} className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Simpan</button>
            </div>
          </div>
        </Modal>
      )}

      {receiveTarget && (
        <Modal title={`Konfirmasi Penerimaan ${receiveTarget.code}`} onClose={() => setReceiveTarget(null)}>
          <div className="space-y-3">
            <label className="text-sm text-slate-600">Skor Quality Control (0-100)</label>
            <input type="number" min={0} max={100} value={qcScore} onChange={(e) => setQcScore(e.target.value)} className="w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" autoFocus />
            <p className="text-xs text-slate-400">Skor &ge; 70 otomatis lolos QC dan menambah stok inventori.</p>
            <div className="flex gap-3 pt-2">
              <button onClick={() => setReceiveTarget(null)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button onClick={handleReceive} className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Simpan</button>
            </div>
          </div>
        </Modal>
      )}
    </div>
  );
}
