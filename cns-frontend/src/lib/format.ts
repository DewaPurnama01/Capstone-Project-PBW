/**
 * Kumpulan fungsi kecil untuk memformat data mentah jadi teks yang enak
 * dibaca di UI (format Rupiah, tanggal, tanggal+jam). Dipakai di banyak
 * halaman lewat: import { formatRupiah } from '../lib/format'.
 */

// 145000 -> "Rp 145.000"
export function formatRupiah(value: number): string {
  return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

// "2026-07-06" -> "6 Jul 2026"
export function formatDate(value: string | null): string {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
}

// "2026-07-06T10:45:00" -> "6 Jul 2026, 10.45"
export function formatDateTime(value: string | null): string {
  if (!value) return '-';
  return new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}
