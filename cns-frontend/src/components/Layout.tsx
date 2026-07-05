import type { ReactNode } from 'react';
import Sidebar from './Sidebar';

export default function Layout({ children }: { children: ReactNode }) {
  return (
    <div className="flex min-h-screen bg-cream-50">
      <Sidebar />
      <main className="flex-1 p-6 lg:p-8 max-w-[1600px]">{children}</main>
    </div>
  );
}
