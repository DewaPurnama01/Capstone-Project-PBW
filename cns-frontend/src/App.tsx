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

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/akses-terkunci" element={<AccessLocked />} />

          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />

          <Route path="/pelanggan" element={<ProtectedRoute roles={['owner', 'kasir']}><Customers /></ProtectedRoute>} />
          <Route path="/transaksi" element={<ProtectedRoute roles={['owner', 'kasir']}><Transactions /></ProtectedRoute>} />
          <Route path="/inventori" element={<ProtectedRoute roles={['owner', 'admin']}><Inventory /></ProtectedRoute>} />
          <Route path="/kemitraan" element={<ProtectedRoute roles={['owner', 'admin']}><Partnership /></ProtectedRoute>} />
          <Route path="/purchase-orders" element={<ProtectedRoute roles={['owner', 'admin']}><PurchaseOrders /></ProtectedRoute>} />
          <Route path="/laporan" element={<ProtectedRoute roles={['owner']}><Reports /></ProtectedRoute>} />

          <Route path="*" element={<Navigate to="/dashboard" replace />} />
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  );
}
