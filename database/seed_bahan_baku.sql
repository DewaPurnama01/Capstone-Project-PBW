-- ═══════════════════════════════════════════════════════════════
--  SEED: Bahan Baku Menu Cafe CNS
--  Jalankan SETELAH cns_db_setup.sql (USE cns_db terlebih dahulu)
--  Perintah: mysql -u root -p cns_db < seed_bahan_baku.sql
-- ═══════════════════════════════════════════════════════════════
USE cns_db;

INSERT INTO tb_bahan (nama_bahan, kategori, satuan, jumlah_stok, batas_minimum, batas_maksimum, harga_per_unit, supplier, status_stok, is_coffee, tanggal_update) VALUES
-- ☕ Kopi & Espresso
('Biji Kopi',               'Kopi',       'gram', 500,   100,  2000, 160, 'Petani Lokal', 'NORMAL', 1, NOW()),
('Konsentrat Cold Brew',    'Kopi',       'ml',   1000,  200,  5000,  50, 'Cafe CNS',     'NORMAL', 1, NOW()),

-- 🥛 Susu & Dairy
('Susu UHT',               'Dairy',      'ml',   5000,  500, 20000,   8, 'Indomaret',    'NORMAL', 0, NOW()),
('Susu Kental Manis',      'Dairy',      'gram', 2000,  200,  8000,   5, 'Indomaret',    'NORMAL', 0, NOW()),
('Susu Evaporasi',         'Dairy',      'ml',   1000,  100,  5000,  10, 'Indomaret',    'NORMAL', 0, NOW()),
('Susu Bubuk',             'Dairy',      'gram', 500,    50,  2000,  20, 'Indomaret',    'NORMAL', 0, NOW()),
('Whipping Cream',         'Dairy',      'gram', 500,    50,  2000,  30, 'Supplier',     'NORMAL', 0, NOW()),
('Creamer Bubuk',          'Dairy',      'gram', 500,    50,  2000,  15, 'Indomaret',    'NORMAL', 0, NOW()),
('Keju Cheddar',           'Dairy',      'gram', 500,    50,  2000,  40, 'Indomaret',    'NORMAL', 0, NOW()),

-- 🌿 Non-Dairy & Rempah
('Bubuk Matcha Murni',     'Non-Kopi',   'gram', 200,    20,  1000, 150, 'Supplier',     'NORMAL', 0, NOW()),
('Teh Hitam Bubuk',        'Non-Kopi',   'gram', 500,    50,  2000,  30, 'Supplier',     'NORMAL', 0, NOW()),
('Bubuk Cokelat Premium',  'Non-Kopi',   'gram', 500,    50,  2000,  80, 'Supplier',     'NORMAL', 0, NOW()),
('Bubuk Kayu Manis',       'Rempah',     'gram', 100,    10,   500, 120, 'Supplier',     'NORMAL', 0, NOW()),

-- 🍯 Gula & Sirup
('Gula Aren Cair',         'Gula',       'ml',   2000,  200,  8000,  25, 'Supplier',     'NORMAL', 0, NOW()),
('Gula Cair',              'Gula',       'ml',   2000,  200,  8000,  15, 'Supplier',     'NORMAL', 0, NOW()),
('Gula Pasir',             'Gula',       'gram', 2000,  200,  8000,   8, 'Indomaret',    'NORMAL', 0, NOW()),

-- 🧊 Es Batu & Air
('Es Batu',                'Lainnya',    'gram', 5000, 1000, 20000,   1, 'Cafe CNS',     'NORMAL', 0, NOW()),
('Air Mineral',            'Lainnya',    'ml',   5000, 1000, 20000,   1, 'Cafe CNS',     'NORMAL', 0, NOW()),

-- 🍞 Bahan Bakery
('Tepung Terigu Protein Tinggi', 'Bakery','gram', 2000, 200, 10000,  10, 'Supplier',    'NORMAL', 0, NOW()),
('Tepung Terigu Protein Rendah', 'Bakery','gram', 2000, 200, 10000,  10, 'Supplier',    'NORMAL', 0, NOW()),
('Mentega',                'Bakery',     'gram', 1000,  100,  5000,  50, 'Indomaret',    'NORMAL', 0, NOW()),
('Ragi Instan',            'Bakery',     'gram', 100,    10,   500,  80, 'Indomaret',    'NORMAL', 0, NOW()),
('Roti Tawar',             'Bakery',     'gram', 1000,  100,  5000,  15, 'Indomaret',    'NORMAL', 0, NOW()),

-- 🥚 Telur & Protein
('Telur Ayam',             'Protein',    'gram', 1000,  100,  5000,  25, 'Pasar',        'NORMAL', 0, NOW()),
('Kuning Telur',           'Protein',    'gram', 500,    50,  2000,  30, 'Pasar',        'NORMAL', 0, NOW()),
('Daging Asap',            'Protein',    'gram', 500,    50,  2000, 120, 'Supplier',     'NORMAL', 0, NOW()),

-- 🥬 Sayur & Kondimen
('Daun Selada Fresh',      'Sayur',      'gram', 300,    30,  1000,  30, 'Pasar',        'NORMAL', 0, NOW()),
('Tomat',                  'Sayur',      'gram', 500,    50,  2000,  15, 'Pasar',        'NORMAL', 0, NOW()),
('Saus Mayones',           'Kondimen',   'ml',   500,    50,  2000,  30, 'Indomaret',    'NORMAL', 0, NOW()),
('Selai',                  'Lainnya',    'gram', 500,    50,  2000,  40, 'Indomaret',    'NORMAL', 0, NOW());
