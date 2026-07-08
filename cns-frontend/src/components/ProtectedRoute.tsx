import { Navigate } from 'react-router-dom';
import type { ReactNode } from 'react';
import { useAuth } from '../context/AuthContext';
import Layout from './Layout';

/**
 * "Penjaga pintu" untuk halaman yang butuh login. Dipasang membungkus
 * setiap <Route> di App.tsx. Logikanya:
 *
 * 1. Kalau data auth masih dicek (loading) -> tampilkan teks "Memuat..."
 * 2. Kalau belum login sama sekali -> lempar ke /login
 * 3. Kalau sudah login TAPI role-nya tidak diizinkan untuk halaman ini
 *    -> lempar ke halaman "Akses Terkunci" (laporan bagian 4.1)
 * 4. Kalau semua syarat terpenuhi -> tampilkan halaman aslinya (children)
 */
export default function ProtectedRoute({
  children,
  roles,
}: {
  children: ReactNode;
  roles?: Array<'owner' | 'admin' | 'kasir'>;
}) {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="min-h-screen flex items-center justify-center text-cafe-green-700">Memuat...</div>;
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  // roles tidak diisi = halaman ini boleh diakses semua role (contoh: Dashboard)
  if (roles && !roles.includes(user.role)) {
    return <Navigate to="/akses-terkunci" replace />;
  }

  return <Layout>{children}</Layout>;
}
