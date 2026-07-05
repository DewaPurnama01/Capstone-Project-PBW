import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Coffee, LogIn } from 'lucide-react';
import { useAuth } from '../context/AuthContext';

const DEMO_ACCOUNTS = [
  { role: 'Owner CNS', username: 'owner', password: 'owner2026' },
  { role: 'Dani Admin', username: 'admin', password: 'admin2026' },
  { role: 'Rini Kasir', username: 'kasir', password: 'kasir2026' },
];

export default function Login() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const { login } = useAuth();
  const navigate = useNavigate();

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await login(username, password);
      navigate('/dashboard');
    } catch (err: any) {
      setError(err?.response?.data?.message || 'Login gagal. Periksa kembali kredensial kamu.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen grid grid-cols-1 lg:grid-cols-2">
      <div className="hidden lg:flex flex-col justify-between bg-cafe-green-800 text-cream-100 p-12">
        <div className="flex items-center gap-2">
          <Coffee size={26} />
          <span className="font-semibold text-lg">Cafe CNS</span>
        </div>
        <div>
          <h1 className="text-3xl font-semibold leading-tight mb-3">Sistem Informasi<br />Manajemen Café</h1>
          <p className="text-cream-100/70 max-w-sm">
            Platform CRM untuk mengelola pelanggan, inventori, portal kemitraan petani,
            dan laporan analitik cafe secara terpusat.
          </p>
        </div>
        <p className="text-xs text-cream-100/40">&copy; 2026 Cafe CNS. Catch New Serenity.</p>
      </div>

      <div className="flex items-center justify-center p-8 bg-cream-50">
        <div className="w-full max-w-sm">
          <h2 className="text-2xl font-semibold text-cafe-green-800 mb-1">Masuk</h2>
          <p className="text-sm text-slate-500 mb-6">Masuk ke sistem manajemen Cafe CNS</p>

          <form onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="text-sm font-medium text-slate-700">Username</label>
              <input
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                className="mt-1 w-full rounded-lg border border-cream-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-cafe-green-500"
                placeholder="username"
                required
              />
            </div>
            <div>
              <label className="text-sm font-medium text-slate-700">Password</label>
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                className="mt-1 w-full rounded-lg border border-cream-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-cafe-green-500"
                placeholder="password"
                required
              />
            </div>

            {error && <p className="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2">{error}</p>}

            <button
              type="submit"
              disabled={loading}
              className="w-full flex items-center justify-center gap-2 bg-cafe-green-700 hover:bg-cafe-green-600 text-white font-medium rounded-lg py-2.5 transition-colors disabled:opacity-60"
            >
              <LogIn size={16} />
              {loading ? 'Memproses...' : 'Masuk'}
            </button>
          </form>

          <div className="mt-6 pt-6 border-t border-cream-200">
            <p className="text-xs text-slate-400 mb-2">Akun demo</p>
            <div className="space-y-1.5">
              {DEMO_ACCOUNTS.map((acc) => (
                <button
                  key={acc.username}
                  onClick={() => { setUsername(acc.username); setPassword(acc.password); }}
                  className="w-full flex items-center justify-between text-xs bg-cream-100 hover:bg-cream-200 rounded-lg px-3 py-2 transition-colors"
                >
                  <span className="font-medium text-cafe-green-800">{acc.role}</span>
                  <span className="text-slate-400">{acc.username}</span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
