import {
  LayoutDashboard, Users, Receipt, Boxes, Handshake, ClipboardList, LineChart,
} from 'lucide-react';

export interface NavItem {
  to: string;
  label: string;
  icon: typeof LayoutDashboard;
  roles: Array<'owner' | 'admin' | 'kasir'>;
}

export const NAV_ITEMS: NavItem[] = [
  { to: '/dashboard', label: 'Dashboard', icon: LayoutDashboard, roles: ['owner', 'admin', 'kasir'] },
  { to: '/pelanggan', label: 'Pelanggan', icon: Users, roles: ['owner', 'kasir'] },
  { to: '/transaksi', label: 'Transaksi', icon: Receipt, roles: ['owner', 'kasir'] },
  { to: '/inventori', label: 'Inventori', icon: Boxes, roles: ['owner', 'admin'] },
  { to: '/kemitraan', label: 'Portal Kemitraan', icon: Handshake, roles: ['owner', 'admin'] },
  { to: '/purchase-orders', label: 'Purchase Orders', icon: ClipboardList, roles: ['owner', 'admin'] },
  { to: '/laporan', label: 'Laporan', icon: LineChart, roles: ['owner'] },
];
