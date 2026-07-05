import { Navigate } from 'react-router-dom';
import type { ReactNode } from 'react';
import { useAuth } from '../context/AuthContext';
import Layout from './Layout';

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

  if (roles && !roles.includes(user.role)) {
    return <Navigate to="/akses-terkunci" replace />;
  }

  return <Layout>{children}</Layout>;
}
