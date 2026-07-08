import { useEffect, useState } from 'react';
import { Clock } from 'lucide-react';

/**
 * Menampilkan tanggal & jam saat ini, dan memperbaruinya tiap detik.
 *
 * Konsep yang dipakai: useEffect + setInterval. setInterval menjalankan
 * kode di dalamnya berulang setiap 1000ms (1 detik). Setiap kali berjalan,
 * setNow() dipanggil dengan waktu terbaru, yang otomatis membuat React
 * menggambar ulang teks jam di layar (re-render).
 *
 * "return () => clearInterval(id)" adalah cleanup function: dijalankan
 * saat komponen ini hilang dari layar (misalnya pindah halaman), supaya
 * interval-nya dihentikan dan tidak terus berjalan di belakang layar (memory leak).
 */
export default function LiveClock() {
  const [now, setNow] = useState(new Date());

  useEffect(() => {
    const id = setInterval(() => setNow(new Date()), 1000);
    return () => clearInterval(id);
  }, []);

  const dateLabel = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  const timeLabel = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

  return (
    <div className="flex items-center gap-2 text-sm text-slate-500 bg-white border border-cream-200 rounded-lg px-3 py-2">
      <Clock size={14} className="text-cafe-green-600" />
      <span>{dateLabel}</span>
      <span className="font-medium text-cafe-green-800 tabular-nums">{timeLabel}</span>
    </div>
  );
}
