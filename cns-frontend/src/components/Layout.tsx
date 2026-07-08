import type { ReactNode } from 'react';
import Sidebar from './Sidebar';

/**
 * Kerangka halaman yang dipakai berulang: Sidebar tetap di kiri, dan
 * "children" (isi halaman yang berbeda-beda tiap route) tampil di kanan.
 * Ini konsep "component reuse" di React — daripada menulis ulang sidebar
 * di setiap halaman, cukup dibungkus lewat <Layout> sekali di ProtectedRoute.
 */
export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-screen bg-cream-50">
      <Sidebar />
      <main className="flex-1 p-6 lg:p-8 max-w-[1600px]">{children}</main>
    </div>
  );
}
