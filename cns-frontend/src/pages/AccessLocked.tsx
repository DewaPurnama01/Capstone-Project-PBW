import { Link } from 'react-router-dom';
import { Lock } from 'lucide-react';

export default function AccessLocked() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center bg-cream-50 text-center p-6">
      <div className="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center mb-4">
        <Lock size={28} />
      </div>
      <h1 className="text-xl font-semibold text-cafe-green-800 mb-2">Akses Terkunci</h1>
      <p className="text-sm text-slate-500 max-w-sm mb-6">
        Kamu mencoba mengakses modul yang berada di luar hak akses role kamu saat ini.
        Hubungi Owner jika kamu merasa ini keliru.
      </p>
      <Link to="/dashboard" className="text-sm font-medium text-white bg-cafe-green-700 hover:bg-cafe-green-600 rounded-lg px-4 py-2">
        Kembali ke Dashboard
      </Link>
    </div>
  );
}
