import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import api from '../api/client';
import type { User } from '../types';

/**
 * AuthContext = tempat menyimpan "siapa yang sedang login" supaya bisa
 * diakses dari halaman/komponen manapun tanpa harus mengoper data lewat
 * props satu-satu (konsep React: Context API untuk state global).
 */

interface AuthContextValue {
  user: User | null;
  loading: boolean;
  login: (username: string, password: string) => Promise<void>;
  logout: () => void;
  hasAccess: (roles: string[]) => boolean;
}

// createContext = wadah kosong dulu, nanti diisi lewat <AuthProvider>
const AuthContext = createContext<AuthContextValue | undefined>(undefined);

/**
 * AuthProvider membungkus seluruh aplikasi (lihat App.tsx) sehingga semua
 * halaman di dalamnya bisa memanggil useAuth() untuk tahu user yang login.
 */
export function AuthProvider({ children }: { children: ReactNode }) {
  // useState = "kotak penyimpanan" nilai yang bisa berubah; tiap kali diubah,
  // React otomatis menggambar ulang (re-render) bagian tampilan yang memakainya.
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  // useEffect dengan dependency [] = hanya dijalankan SEKALI saat komponen
  // pertama kali muncul di layar. Di sini dipakai untuk mengecek localStorage:
  // kalau sebelumnya sudah pernah login, sesi langsung dipulihkan.
  useEffect(() => {
    const storedUser = localStorage.getItem('cns_user');
    const storedToken = localStorage.getItem('cns_token');

    if (storedUser && storedToken) {
      setUser(JSON.parse(storedUser));
    }

    setLoading(false);
  }, []);

  // Fungsi login: memanggil API, lalu menyimpan token + data user
  async function login(username: string, password: string) {
    const res = await api.post('/auth/login', { username, password });
    const { token, user } = res.data;

    localStorage.setItem('cns_token', token);
    localStorage.setItem('cns_user', JSON.stringify(user));
    setUser(user);
  }

  function logout() {
    api.post('/auth/logout').catch(() => {}); // best-effort, tidak masalah kalau gagal
    localStorage.removeItem('cns_token');
    localStorage.removeItem('cns_user');
    setUser(null);
  }

  // Dipakai Sidebar & ProtectedRoute untuk cek RBAC di sisi frontend:
  // apakah role user termasuk dalam daftar role yang diizinkan untuk suatu menu?
  function hasAccess(roles: string[]) {
    if (!user) return false;
    return roles.includes(user.role);
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, hasAccess }}>
      {children}
    </AuthContext.Provider>
  );
}

// Custom hook: cara singkat memakai AuthContext di komponen lain,
// misalnya: const { user, logout } = useAuth();
export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
