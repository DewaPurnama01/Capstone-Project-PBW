import { useEffect, useState } from 'react';
import { AlertTriangle, Plus, Radio, CheckCircle2, HandCoins } from 'lucide-react';
import api from '../api/client';
import type { RestockRequest, Partner, InventoryItem } from '../types';
import Modal from '../components/Modal';
import { formatRupiah } from '../lib/format';

/**
 * Halaman Portal Kemitraan (laporan 4.6). Menampilkan alur pengadaan biji
 * kopi lewat tab: Alur Pengadaan (stepper), Petani Mitra (daftar performa),
 * dan Riwayat Request (yang sudah selesai). Tombol "Siarkan", "Input
 * Penawaran", dan "Pilih & Terbitkan PO" memanggil endpoint backend yang
 * berbeda-beda sesuai tahap prosesnya (lihat PartnershipController).
 */

const TABS = ['Alur Pengadaan', 'Petani Mitra', 'Riwayat Request'];

const STEPS = ['Deteksi Stok', 'Form Request', 'Broadcast', 'Penawaran', 'PO Terbit', 'Pengiriman', 'Quality Control', 'Selesai'];

interface POSummary { id: number; code: string; reference_code: string | null; delivery_status: string; payment_status: string }

/**
 * Menentukan tahap (0-7) mana yang sedang aktif untuk sebuah request,
 * supaya ikon stepper di UI ikut berubah warna/isi sesuai kondisi saat ini
 * (bukan selalu diam di tahap pertama). Aturannya sederhana: kalau sudah
 * ada PO terkait, tahapnya ikut status PO; kalau belum, ikut status request.
 */
function getActiveStep(req: RestockRequest, relatedPO?: POSummary): number {
  if (relatedPO) {
    if (relatedPO.delivery_status === 'selesai') return 7; // Selesai
    if (relatedPO.delivery_status === 'retur') return 6;   // gagal QC, tetap tampil di tahap QC
    if (relatedPO.delivery_status === 'dikirim' || relatedPO.delivery_status === 'diterima') return 5; // Pengiriman
  }

  switch (req.status) {
    case 'draft': return 1;      // sudah dibuat, belum disiarkan
    case 'disiarkan': return 2;  // sudah disiarkan ke petani
    case 'ditawar': return 3;    // ada tawaran masuk
    case 'po_dibuat': return 4;  // tawaran dipilih, PO terbit
    case 'selesai': return 7;
    default: return 0;           // draft baru dibuat = baru tahap "deteksi stok"
  }
}

export default function Partnership() {
  const [tab, setTab] = useState(0);
  const [coffeeStock, setCoffeeStock] = useState<InventoryItem | null>(null);
  const [coffeeCritical, setCoffeeCritical] = useState(false);
  const [activeRequests, setActiveRequests] = useState<RestockRequest[]>([]);
  const [history, setHistory] = useState<RestockRequest[]>([]);
  const [partners, setPartners] = useState<Partner[]>([]);
  const [purchaseOrders, setPurchaseOrders] = useState<POSummary[]>([]);
  const [showRequestForm, setShowRequestForm] = useState(false);
  const [form, setForm] = useState({ specification: '', qty_needed: '' });
  const [offerTarget, setOfferTarget] = useState<RestockRequest | null>(null);
  const [offerForm, setOfferForm] = useState({ partner_id: '', price_per_unit: '', eta_days: '' });

  function load() {
    api.get('/partnership').then((res) => {
      setCoffeeStock(res.data.coffee_stock);
      setCoffeeCritical(res.data.coffee_critical);
      setActiveRequests(res.data.active_requests);
      setHistory(res.data.history);
      setPartners(res.data.partners);
      setPurchaseOrders(res.data.purchase_orders || []);
    });
  }

  useEffect(() => { load(); }, []);

  function findPO(code: string) {
    return purchaseOrders.find((po) => po.reference_code === code);
  }

  async function handleCreateRequest(e: React.FormEvent) {
    e.preventDefault();
    if (!coffeeStock) return;
    await api.post('/partnership/requests', {
      inventory_item_id: coffeeStock.id,
      specification: form.specification,
      qty_needed: Number(form.qty_needed),
      unit: coffeeStock.unit,
    });
    setShowRequestForm(false);
    setForm({ specification: '', qty_needed: '' });
    load();
  }

  async function handleBroadcast(reqId: number) {
    await api.post(`/partnership/requests/${reqId}/broadcast`);
    load();
  }

  function openOfferForm(req: RestockRequest) {
    setOfferTarget(req);
    setOfferForm({ partner_id: '', price_per_unit: '', eta_days: '' });
  }

  async function handleSubmitOffer(e: React.FormEvent) {
    e.preventDefault();
    if (!offerTarget) return;
    await api.post(`/partnership/requests/${offerTarget.id}/offers`, {
      partner_id: Number(offerForm.partner_id),
      price_per_unit: Number(offerForm.price_per_unit),
      eta_days: Number(offerForm.eta_days),
    });
    setOfferTarget(null);
    load();
  }

  async function handleSelectOffer(offerId: number) {
    await api.post(`/partnership/offers/${offerId}/select`);
    load();
  }

  return (
    <div className="space-y-5">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold text-cafe-green-800">Portal Kemitraan</h1>
          <p className="text-sm text-slate-500">Pengadaan Biji Kopi dari petani lokal mitra Cafe CNS</p>
        </div>
        <button onClick={() => setShowRequestForm(true)} className="flex items-center gap-2 bg-cafe-green-700 hover:bg-cafe-green-600 text-white text-sm font-medium rounded-lg px-4 py-2.5">
          <Plus size={16} /> Request Restock Biji Kopi
        </button>
      </div>

      <div className="flex gap-2 bg-white rounded-xl border border-cream-200 p-1.5 w-fit">
        {TABS.map((t, i) => (
          <button key={t} onClick={() => setTab(i)} className={`text-sm font-medium rounded-lg px-4 py-2 ${tab === i ? 'bg-cafe-green-700 text-white' : 'text-slate-500'}`}>{t}</button>
        ))}
      </div>

      {coffeeCritical && (
        <div className="bg-red-50 border border-red-200 rounded-xl px-5 py-4 flex items-center gap-3">
          <AlertTriangle className="text-red-600" size={20} />
          <p className="text-sm font-medium text-red-700">
            Stok Biji Kopi Kritis! Sistem POS mendeteksi stok biji kopi tersisa {coffeeStock?.current_stock} {coffeeStock?.unit}
            {' '}(minimum {coffeeStock?.min_stock} {coffeeStock?.unit}). Segera lakukan pengadaan.
          </p>
        </div>
      )}

      {tab === 0 && (
        <div className="space-y-4">
          {activeRequests.length === 0 ? (
            <div className="bg-white rounded-xl border border-cream-200 p-6 text-center py-8">
              <AlertTriangle className="mx-auto text-red-400 mb-3" size={32} />
              <p className="font-medium text-red-600 mb-1">Belum ada permintaan aktif</p>
              <p className="text-sm text-slate-500 mb-4 max-w-md mx-auto">
                Mulai proses pengadaan dengan membuat form permintaan ke seluruh petani mitra.
              </p>
              <button onClick={() => setShowRequestForm(true)} className="bg-cafe-green-700 text-white text-sm font-medium rounded-lg px-5 py-2.5">
                Buat Form Request
              </button>
            </div>
          ) : (
            activeRequests.map((req) => {
              const relatedPO = findPO(req.code);
              const activeStep = getActiveStep(req, relatedPO);

              return (
                <div key={req.id} className="bg-white rounded-xl border border-cream-200 p-6 space-y-5">
                  <div className="flex flex-wrap items-center justify-between gap-2">
                    <div>
                      <p className="font-medium text-slate-700">{req.code} &mdash; {req.qty_needed} {req.unit} biji kopi</p>
                      <p className="text-xs text-slate-400">{req.specification || 'Tanpa spesifikasi khusus'}</p>
                    </div>
                    <span className="text-xs font-medium bg-cafe-green-100 text-cafe-green-700 rounded-full px-3 py-1 capitalize">{req.status.replace('_', ' ')}</span>
                  </div>

                  <div className="flex flex-wrap gap-2">
                    {STEPS.map((s, i) => {
                      const state = i < activeStep ? 'done' : i === activeStep ? 'current' : 'todo';
                      return (
                        <div key={s} className="flex items-center gap-2">
                          <div className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold ${
                            state === 'done' ? 'bg-cafe-green-600 text-white'
                            : state === 'current' ? (relatedPO?.delivery_status === 'retur' ? 'bg-red-500 text-white' : 'bg-amber-500 text-white')
                            : 'bg-cream-100 text-slate-400'
                          }`}>
                            {state === 'done' ? '✓' : i + 1}
                          </div>
                          <span className={`text-xs ${state === 'todo' ? 'text-slate-400' : 'text-slate-600 font-medium'}`}>{s}</span>
                          {i < STEPS.length - 1 && <div className="w-4 h-px bg-cream-200" />}
                        </div>
                      );
                    })}
                  </div>

                  {req.status === 'draft' && (
                    <button onClick={() => handleBroadcast(req.id)} className="flex items-center gap-2 text-sm font-medium bg-cafe-green-700 text-white rounded-lg px-4 py-2">
                      <Radio size={14} /> Siarkan ke Petani Mitra
                    </button>
                  )}

                  {(req.status === 'disiarkan' || req.status === 'ditawar') && (
                    <div className="space-y-2">
                      <div className="flex items-center justify-between">
                        <p className="text-xs text-slate-500 font-medium">Penawaran masuk:</p>
                        <button onClick={() => openOfferForm(req)} className="flex items-center gap-1.5 text-xs font-medium bg-cafe-green-100 text-cafe-green-700 rounded-lg px-3 py-1.5">
                          <HandCoins size={13} /> Input Penawaran Petani
                        </button>
                      </div>
                      {req.offers.length === 0 && (
                        <p className="text-xs text-slate-400 italic">Belum ada penawaran. Gunakan "Input Penawaran Petani" untuk mencatat penawaran yang masuk lewat telepon/WhatsApp.</p>
                      )}
                      {req.offers.map((offer) => (
                        <div key={offer.id} className="flex items-center justify-between bg-cream-50 rounded-lg px-3 py-2">
                          <div className="text-sm">
                            <span className="font-medium">{offer.partner.name}</span>
                            <span className="text-slate-400"> &middot; {formatRupiah(offer.price_per_unit)}/{req.unit} &middot; est. {offer.eta_days} hari</span>
                          </div>
                          {offer.status === 'menunggu' ? (
                            <button onClick={() => handleSelectOffer(offer.id)} className="text-xs font-medium bg-cafe-green-700 text-white rounded-lg px-3 py-1.5">Pilih &amp; Terbitkan PO</button>
                          ) : (
                            <span className={`text-xs font-medium px-2.5 py-1 rounded-full ${offer.status === 'dipilih' ? 'bg-cafe-green-100 text-cafe-green-700' : 'bg-red-100 text-red-500'}`}>{offer.status}</span>
                          )}
                        </div>
                      ))}
                    </div>
                  )}

                  {req.status === 'po_dibuat' && (
                    <div className="bg-cream-50 rounded-lg px-4 py-3 text-sm text-slate-600">
                      PO {relatedPO?.code} sudah diterbitkan. Lanjutkan proses pengiriman & pembayaran di menu{' '}
                      <a href="/purchase-orders" className="text-cafe-green-700 font-medium">Purchase Orders</a>.
                    </div>
                  )}
                </div>
              );
            })
          )}
        </div>
      )}

      {tab === 1 && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {partners.map((p) => (
            <div key={p.id} className="bg-white rounded-xl border border-cream-200 p-4">
              <div className="flex items-center justify-between mb-3">
                <div>
                  <p className="font-medium text-slate-700">{p.name}</p>
                  <p className="text-xs text-slate-400">{p.address}</p>
                </div>
                <span className="text-xs font-medium bg-cafe-green-100 text-cafe-green-700 rounded-full px-2.5 py-1">{p.commodity}</span>
              </div>
              <div className="grid grid-cols-2 gap-3 text-sm">
                <div>
                  <p className="text-xs text-slate-400">Ketepatan Waktu</p>
                  <p className="font-semibold text-cafe-green-800">{p.on_time_rate}%</p>
                </div>
                <div>
                  <p className="text-xs text-slate-400">Skor Kualitas</p>
                  <p className="font-semibold text-cafe-green-800">{p.quality_score}/100</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}

      {tab === 2 && (
        <div className="bg-white rounded-xl border border-cream-200 divide-y divide-cream-100">
          {history.length === 0 && <p className="p-6 text-sm text-slate-400">Belum ada riwayat request selesai.</p>}
          {history.map((req) => (
            <div key={req.id} className="p-4 flex items-center justify-between">
              <div className="flex items-center gap-3">
                <CheckCircle2 className="text-cafe-green-600" size={18} />
                <div>
                  <p className="font-medium text-slate-700">{req.code}</p>
                  <p className="text-xs text-slate-400">{req.qty_needed} {req.unit} &middot; {req.inventory_item?.name}</p>
                </div>
              </div>
              <span className="text-xs text-slate-400">Selesai</span>
            </div>
          ))}
        </div>
      )}

      {showRequestForm && (
        <Modal title="Request Restock Biji Kopi" onClose={() => setShowRequestForm(false)}>
          <form onSubmit={handleCreateRequest} className="space-y-3">
            <div>
              <label className="text-sm font-medium text-slate-700">Spesifikasi (opsional)</label>
              <textarea value={form.specification} onChange={(e) => setForm({ ...form, specification: e.target.value })} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" rows={3} placeholder="Mis. Arabica, roast medium, grade A" />
            </div>
            <div>
              <label className="text-sm font-medium text-slate-700">Jumlah dibutuhkan ({coffeeStock?.unit})</label>
              <input type="number" value={form.qty_needed} onChange={(e) => setForm({ ...form, qty_needed: e.target.value })} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
            </div>
            <p className="text-xs text-slate-400">Permintaan akan dibuat sebagai draf. Kamu bisa menyiarkannya ke petani mitra kapan saja dari daftar "Alur Pengadaan".</p>
            <div className="flex gap-3 pt-2">
              <button type="button" onClick={() => setShowRequestForm(false)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button type="submit" className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Buat Draf Request</button>
            </div>
          </form>
        </Modal>
      )}

      {offerTarget && (
        <Modal title={`Input Penawaran — ${offerTarget.code}`} onClose={() => setOfferTarget(null)}>
          <form onSubmit={handleSubmitOffer} className="space-y-3">
            <div>
              <label className="text-sm font-medium text-slate-700">Petani Mitra</label>
              <select value={offerForm.partner_id} onChange={(e) => setOfferForm({ ...offerForm, partner_id: e.target.value })} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" required>
                <option value="">Pilih petani...</option>
                {partners.map((p) => <option key={p.id} value={p.id}>{p.name}</option>)}
              </select>
            </div>
            <div>
              <label className="text-sm font-medium text-slate-700">Harga per {offerTarget.unit}</label>
              <input type="number" value={offerForm.price_per_unit} onChange={(e) => setOfferForm({ ...offerForm, price_per_unit: e.target.value })} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
            </div>
            <div>
              <label className="text-sm font-medium text-slate-700">Estimasi Pengiriman (hari)</label>
              <input type="number" value={offerForm.eta_days} onChange={(e) => setOfferForm({ ...offerForm, eta_days: e.target.value })} className="mt-1 w-full rounded-lg border border-cream-200 px-3 py-2 text-sm" required />
            </div>
            <div className="flex gap-3 pt-2">
              <button type="button" onClick={() => setOfferTarget(null)} className="flex-1 rounded-lg border border-cream-200 py-2 text-sm font-medium">Batal</button>
              <button type="submit" className="flex-1 rounded-lg bg-cafe-green-700 text-white py-2 text-sm font-medium">Simpan Penawaran</button>
            </div>
          </form>
        </Modal>
      )}
    </div>
  );
}
