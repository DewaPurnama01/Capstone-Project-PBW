import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import Customers from './pages/Customers';
import Transactions from './pages/Transactions';
import Inventory from './pages/Inventory';
import Partnership from './pages/Partnership';
import PurchaseOrders from './pages/PurchaseOrders';
import Reports from './pages/Reports';
import AccessLocked from './pages/AccessLocked';

/**
 * App.tsx adalah "peta halaman" dari seluruh aplikasi — konsep client-side
 * routing (React Router). Di aplikasi web tradisional, ganti halaman = load
 * ulang dari server (mis. dashboard.html). Di sini, ganti halaman hanya
 * mengganti komponen React yang tampil, TANPA reload browser sama sekali.
 *
 * <Route path="..." element={...} /> = "kalau alamat URL cocok dengan path
 * ini, tampilkan komponen tsb". <ProtectedRoute> membungkus halaman yang
 * mewajibkan login, dan bisa dibatasi lagi per role (RBAC).
 */
export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          {/* Halaman publik: tidak butuh login */}
          <Route path="/login" element={<Login />} />
          <Route path="/akses-terkunci" element={<AccessLocked />} />

          {/* "/" otomatis dialihkan ke /dashboard */}
          <Route path="/" element={<Navigate to="/dashboard" replace />} />

          {/* Dashboard bisa diakses semua role yang sudah login (tanpa batasan roles) */}
          <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />

          {/* Halaman-halaman berikut dibatasi per role, sesuai tabel akses laporan bagian 2 */}
          <Route path="/pelanggan" element={<ProtectedRoute roles={['owner', 'kasir']}><Customers /></ProtectedRoute>} />
          <Route path="/transaksi" element={<ProtectedRoute roles={['owner', 'kasir']}><Transactions /></ProtectedRoute>} />
          <Route path="/inventori" element={<ProtectedRoute roles={['owner', 'admin']}><Inventory /></ProtectedRoute>} />
          <Route path="/kemitraan" element={<ProtectedRoute roles={['owner', 'admin']}><Partnership /></ProtectedRoute>} />
          <Route path="/purchase-orders" element={<ProtectedRoute roles={['owner', 'admin']}><PurchaseOrders /></ProtectedRoute>} />
          <Route path="/laporan" element={<ProtectedRoute roles={['owner']}><Reports /></ProtectedRoute>} />

          {/* Alamat yang tidak dikenal -> alihkan ke dashboard */}
          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}
