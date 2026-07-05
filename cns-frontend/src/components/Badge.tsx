const COLOR_MAP: Record<string, string> = {
  VIP: 'bg-amber-100 text-amber-700',
  Member: 'bg-cafe-green-100 text-cafe-green-700',
  Reguler: 'bg-slate-100 text-slate-600',
  Baru: 'bg-blue-100 text-blue-600',
  selesai: 'bg-cafe-green-100 text-cafe-green-700',
  proses: 'bg-amber-100 text-amber-700',
  dibatalkan: 'bg-red-100 text-red-600',
  kritis: 'bg-red-100 text-red-600',
  rendah: 'bg-amber-100 text-amber-700',
  aman: 'bg-cafe-green-100 text-cafe-green-700',
  dikirim: 'bg-blue-100 text-blue-600',
  qc_lulus: 'bg-cafe-green-100 text-cafe-green-700',
  retur: 'bg-red-100 text-red-600',
  belum_bayar: 'bg-red-100 text-red-600',
  sebagian: 'bg-amber-100 text-amber-700',
  lunas: 'bg-cafe-green-100 text-cafe-green-700',
};

export default function Badge({ text, label }: { text: string; label?: string }) {
  const cls = COLOR_MAP[text] || 'bg-slate-100 text-slate-600';
  return (
    <span className={`inline-block px-2.5 py-1 rounded-full text-xs font-medium capitalize ${cls}`}>
      {(label ?? text).replace(/_/g, ' ')}
    </span>
  );
}
