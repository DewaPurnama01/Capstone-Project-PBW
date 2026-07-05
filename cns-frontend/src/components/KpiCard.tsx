import type { LucideIcon } from 'lucide-react';

export default function KpiCard({
  label, value, icon: Icon, tone = 'default',
}: {
  label: string;
  value: string;
  icon: LucideIcon;
  tone?: 'default' | 'warning';
}) {
  return (
    <div className="bg-white rounded-xl border border-cream-200 p-4 flex items-start justify-between shadow-sm">
      <div>
        <p className="text-xs text-slate-500 mb-1">{label}</p>
        <p className={`text-2xl font-semibold ${tone === 'warning' ? 'text-amber-600' : 'text-cafe-green-800'}`}>
          {value}
        </p>
      </div>
      <div className={`w-9 h-9 rounded-lg flex items-center justify-center ${tone === 'warning' ? 'bg-amber-50 text-amber-600' : 'bg-cafe-green-100 text-cafe-green-700'}`}>
        <Icon size={18} />
      </div>
    </div>
  );
}
