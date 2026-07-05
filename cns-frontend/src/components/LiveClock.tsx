import { useEffect, useState } from 'react';
import { Clock } from 'lucide-react';

/**
 * Menampilkan tanggal & jam saat ini, ter-update tiap detik (real-time).
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
