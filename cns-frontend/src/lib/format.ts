export function formatRupiah(value: number): string {
  return 'Rp ' + Math.round(value).toLocaleString('id-ID');
}

export function formatDate(value: string | null): string {
  if (!value) return '-';
  return new Date(value).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
}

export function formatDateTime(value: string | null): string {
  if (!value) return '-';
  return new Date(value).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}
