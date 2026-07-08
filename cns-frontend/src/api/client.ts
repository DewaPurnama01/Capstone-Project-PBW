import axios from 'axios';

/**
 * File ini menyiapkan satu "klien HTTP" (axios) yang dipakai di seluruh
 * halaman untuk berbicara dengan backend Laravel lewat REST API (JSON).
 * Konsep dasarnya: frontend TIDAK pernah menyentuh database secara
 * langsung — semua data lewat request HTTP (GET/POST/PUT/DELETE) ke API.
 */

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

export const api = axios.create({
  baseURL: API_BASE_URL,
  headers: { 'Content-Type': 'application/json' },
});

/**
 * INTERCEPTOR REQUEST: kode ini otomatis berjalan SEBELUM setiap request
 * dikirim. Di sini kita mengambil token JWT yang tersimpan di localStorage
 * (browser storage yang bertahan meski halaman di-refresh) dan menyisipkannya
 * ke header "Authorization". Ini sesuai konsep di laporan bagian 4.1:
 * "data sesi disimpan di localStorage, memungkinkan pengguna tetap masuk
 * meskipun halaman dimuat ulang".
 */
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('cns_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

/**
 * INTERCEPTOR RESPONSE: berjalan setelah balasan dari server diterima.
 * Kalau server membalas status 401 (Unauthenticated — biasanya artinya
 * token sudah kadaluarsa), maka sesi di localStorage dihapus dan halaman
 * dialihkan otomatis ke /login.
 */
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      localStorage.removeItem('cns_token');
      localStorage.removeItem('cns_user');
      if (!window.location.pathname.startsWith('/login')) {
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);

export default api;
