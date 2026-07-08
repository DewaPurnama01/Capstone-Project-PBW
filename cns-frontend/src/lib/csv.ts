/**
 * Fungsi untuk fitur "Export": mengubah data tabel (array baris) menjadi
 * file .csv dan langsung memicu proses download di browser — semua
 * dikerjakan di sisi frontend (JavaScript murni), tanpa perlu request ke backend.
 */
export function downloadCsv(filename: string, rows: (string | number)[][]) {
  // 1. Ubah tiap baris array jadi satu baris teks dipisah koma (format CSV)
  const csvContent = rows
    .map((row) =>
      row
        .map((cell) => {
          const value = String(cell ?? '');
          // Kalau isi sel mengandung koma/tanda kutip/baris baru, bungkus
          // dengan tanda kutip supaya tidak merusak format CSV
          if (value.includes(',') || value.includes('"') || value.includes('\n')) {
            return `"${value.replace(/"/g, '""')}"`;
          }
          return value;
        })
        .join(',')
    )
    .join('\n');

  // 2. Bungkus teks CSV jadi "Blob" (file sementara di memori browser)
  const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);

  // 3. Buat elemen <a> tak terlihat, "klik" secara otomatis lewat kode
  //    supaya browser memicu proses download, lalu buang elemen tsb
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}
