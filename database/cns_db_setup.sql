-- ============================================================
--  SETUP DATABASE — SISTEM INFORMASI CAFE CNS
--  Catch New Serenity · Portal Kemitraan Rantai Pasok
--
--  Cara pakai:
--  1. Buka MySQL Workbench / phpMyAdmin / terminal MySQL
--  2. Jalankan file SQL ini: mysql -u root -p < cns_db_setup.sql
--  3. Setelah selesai, jalankan Laravel: php artisan serve
--  4. Buka http://localhost:8000/register dan buat akun pertama (Owner)
-- ============================================================

-- 1. BUAT & PILIH DATABASE
CREATE DATABASE IF NOT EXISTS cns_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE cns_db;

-- 2. HAPUS TABEL LAMA (urutan sesuai FK)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS detail_transaksi;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS tb_hutang;
DROP TABLE IF EXISTS tb_quality_control;
DROP TABLE IF EXISTS tb_penerimaan;
DROP TABLE IF EXISTS tb_purchase_order;
DROP TABLE IF EXISTS tb_penawaran;
DROP TABLE IF EXISTS tb_broadcast_token;
DROP TABLE IF EXISTS tb_broadcast;
DROP TABLE IF EXISTS tb_mitra;
DROP TABLE IF EXISTS tb_bahan;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- 3. BUAT SEMUA TABEL

-- Tabel 0: users (Akun Internal Sistem)
CREATE TABLE users (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100)    NOT NULL,
    username    VARCHAR(50)     NOT NULL,
    password    VARCHAR(255)    NOT NULL,
    role        ENUM('Owner','Admin','Kasir') NOT NULL DEFAULT 'Kasir',
    created_at  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 1: tb_bahan (Data Stok Bahan Baku — RF-02)
CREATE TABLE tb_bahan (
    id_bahan        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nama_bahan      VARCHAR(100)    NOT NULL,
    kategori        VARCHAR(50)     NOT NULL DEFAULT 'Bahan Baku',
    satuan          VARCHAR(20)     NOT NULL,
    jumlah_stok     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    batas_minimum   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    batas_maksimum  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    harga_per_unit  DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    supplier        VARCHAR(100)    NULL,
    status_stok     ENUM('NORMAL','RENDAH','HABIS') NOT NULL DEFAULT 'NORMAL',
    is_coffee       TINYINT(1)      NOT NULL DEFAULT 0,
    tanggal_update  DATETIME        NULL,
    PRIMARY KEY (id_bahan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 2: tb_mitra (Data Profil Mitra / Petani — RF-01)
CREATE TABLE tb_mitra (
    id_mitra        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nama_mitra      VARCHAR(100)    NOT NULL,
    no_hp           VARCHAR(15)     NOT NULL,
    alamat          TEXT            NULL,
    komoditas       VARCHAR(100)    NOT NULL DEFAULT 'Biji Kopi',
    status_aktif    TINYINT(1)      NOT NULL DEFAULT 1,
    rating          DECIMAL(3,1)    NOT NULL DEFAULT 4.5,
    total_order     INT             NOT NULL DEFAULT 0,
    persen_on_time  INT             NOT NULL DEFAULT 100,
    persen_kualitas INT             NOT NULL DEFAULT 100,
    catatan         VARCHAR(255)    NULL,
    tanggal_daftar  DATE            NOT NULL,
    PRIMARY KEY (id_mitra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 3: tb_broadcast (Data Pengiriman Permintaan — RF-04)
CREATE TABLE tb_broadcast (
    id_broadcast        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_bahan            INT UNSIGNED    NOT NULL,
    jumlah_dibutuhkan   DECIMAL(10,2)   NOT NULL,
    harga_target        DECIMAL(15,2)   NOT NULL,
    tanggal_kirim       DATETIME        NOT NULL,
    batas_respon        DATETIME        NOT NULL,
    catatan             TEXT            NULL,
    status_broadcast    ENUM('AKTIF','DITUTUP','SELESAI') NOT NULL DEFAULT 'AKTIF',
    PRIMARY KEY (id_broadcast),
    CONSTRAINT fk_broadcast_bahan
        FOREIGN KEY (id_bahan) REFERENCES tb_bahan(id_bahan)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 4: tb_broadcast_token (Token unik per mitra per broadcast — RF-04)
CREATE TABLE tb_broadcast_token (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    broadcast_id    INT UNSIGNED    NOT NULL,
    mitra_id        INT UNSIGNED    NOT NULL,
    token           VARCHAR(64)     NOT NULL,
    used            TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_token (token),
    CONSTRAINT fk_btoken_broadcast
        FOREIGN KEY (broadcast_id) REFERENCES tb_broadcast(id_broadcast)
        ON DELETE CASCADE,
    CONSTRAINT fk_btoken_mitra
        FOREIGN KEY (mitra_id) REFERENCES tb_mitra(id_mitra)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 5: tb_penawaran (Data Penawaran dari Mitra — RF-05, RF-06)
CREATE TABLE tb_penawaran (
    id_penawaran        INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_broadcast        INT UNSIGNED    NOT NULL,
    id_mitra            INT UNSIGNED    NOT NULL,
    harga_satuan        DECIMAL(15,2)   NOT NULL,
    jumlah_tersedia     DECIMAL(10,2)   NOT NULL,
    estimasi_kirim      DATE            NOT NULL,
    catatan_mitra       TEXT            NULL,
    status_penawaran    ENUM('MENUNGGU','DITERIMA','DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
    tanggal_input       DATETIME        NOT NULL,
    PRIMARY KEY (id_penawaran),
    CONSTRAINT fk_penawaran_broadcast
        FOREIGN KEY (id_broadcast) REFERENCES tb_broadcast(id_broadcast),
    CONSTRAINT fk_penawaran_mitra
        FOREIGN KEY (id_mitra) REFERENCES tb_mitra(id_mitra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 6: tb_purchase_order (Data Purchase Order — RF-07)
CREATE TABLE tb_purchase_order (
    id_po           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nomor_po        VARCHAR(30)     NOT NULL,
    id_penawaran    INT UNSIGNED    NOT NULL,
    tanggal_terbit  DATE            NOT NULL,
    total_nilai     DECIMAL(15,2)   NOT NULL,
    status_po       ENUM('DITERBITKAN','DIKIRIM','SELESAI','DIBATALKAN') NOT NULL DEFAULT 'DITERBITKAN',
    PRIMARY KEY (id_po),
    UNIQUE KEY uq_nomor_po (nomor_po),
    CONSTRAINT fk_po_penawaran
        FOREIGN KEY (id_penawaran) REFERENCES tb_penawaran(id_penawaran)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 7: tb_penerimaan (Data Penerimaan Barang — RF-08)
CREATE TABLE tb_penerimaan (
    id_penerimaan   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_po           INT UNSIGNED    NOT NULL,
    tanggal_terima  DATE            NOT NULL,
    jumlah_diterima DECIMAL(10,2)   NOT NULL,
    kondisi_fisik   TEXT            NULL,
    id_admin        BIGINT UNSIGNED NULL,
    PRIMARY KEY (id_penerimaan),
    CONSTRAINT fk_penerimaan_po
        FOREIGN KEY (id_po) REFERENCES tb_purchase_order(id_po),
    CONSTRAINT fk_penerimaan_admin
        FOREIGN KEY (id_admin) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 8: tb_quality_control (Data Hasil QC — RF-09, RF-10)
CREATE TABLE tb_quality_control (
    id_qc               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_penerimaan       INT UNSIGNED    NOT NULL,
    hasil_qc            ENUM('LOLOS','TIDAK_LOLOS') NOT NULL,
    catatan_qc          TEXT            NULL,
    foto_dokumentasi    VARCHAR(255)    NULL COMMENT 'Max 2MB, path di storage/app/public/qc_photos/',
    skor_aroma          TINYINT         NOT NULL DEFAULT 0,
    skor_warna          TINYINT         NOT NULL DEFAULT 0,
    skor_ukuran         TINYINT         NOT NULL DEFAULT 0,
    skor_kebersihan     TINYINT         NOT NULL DEFAULT 0,
    tanggal_qc          DATETIME        NOT NULL,
    id_admin            BIGINT UNSIGNED NULL,
    PRIMARY KEY (id_qc),
    CONSTRAINT fk_qc_penerimaan
        FOREIGN KEY (id_penerimaan) REFERENCES tb_penerimaan(id_penerimaan),
    CONSTRAINT fk_qc_admin
        FOREIGN KEY (id_admin) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 9: tb_hutang (Data Rekonsiliasi & Pembayaran — RF-11, RF-12)
CREATE TABLE tb_hutang (
    id_hutang           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_qc               INT UNSIGNED    NOT NULL,
    id_mitra            INT UNSIGNED    NOT NULL,
    jumlah_tagihan      DECIMAL(15,2)   NOT NULL,
    tanggal_jatuh_tempo DATE            NOT NULL,
    status_bayar        ENUM('BELUM_BAYAR','SUDAH_BAYAR') NOT NULL DEFAULT 'BELUM_BAYAR',
    tanggal_lunas       DATE            NULL,
    bukti_bayar         VARCHAR(255)    NULL,
    PRIMARY KEY (id_hutang),
    CONSTRAINT fk_hutang_qc
        FOREIGN KEY (id_qc) REFERENCES tb_quality_control(id_qc),
    CONSTRAINT fk_hutang_mitra
        FOREIGN KEY (id_mitra) REFERENCES tb_mitra(id_mitra)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 10: pelanggan (CRM Pelanggan)
CREATE TABLE pelanggan (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama                VARCHAR(100)    NOT NULL,
    no_hp               VARCHAR(15)     NOT NULL,
    email               VARCHAR(100)    NULL,
    segmen              ENUM('VIP','Member','Reguler','Baru') NOT NULL DEFAULT 'Baru',
    menu_favorit        VARCHAR(100)    NULL,
    poin                INT             NOT NULL DEFAULT 0,
    total_kunjungan     INT             NOT NULL DEFAULT 0,
    total_belanja       DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    status              ENUM('aktif','tidak aktif') NOT NULL DEFAULT 'aktif',
    tanggal_daftar      DATE            NOT NULL,
    terakhir_kunjungan  DATE            NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 11: transaksi (Transaksi Penjualan / POS)
CREATE TABLE transaksi (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nama  VARCHAR(100)    NOT NULL,
    pelanggan_id    BIGINT UNSIGNED NULL,
    segmen          ENUM('VIP','Member','Reguler','Baru') NOT NULL DEFAULT 'Reguler',
    metode_bayar    ENUM('QRIS','Tunai','Transfer')       NOT NULL,
    total           DECIMAL(15,2)   NOT NULL,
    status          ENUM('proses','selesai','batal')      NOT NULL DEFAULT 'proses',
    kasir           VARCHAR(100)    NULL,
    created_at      TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_transaksi_pelanggan
        FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel 12: detail_transaksi (Item dalam Transaksi)
CREATE TABLE detail_transaksi (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    transaksi_id    BIGINT UNSIGNED NOT NULL,
    nama_item       VARCHAR(100)    NOT NULL,
    qty             INT             NOT NULL,
    harga           DECIMAL(15,2)   NOT NULL,
    subtotal        DECIMAL(15,2)   NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_detail_transaksi
        FOREIGN KEY (transaksi_id) REFERENCES transaksi(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SELESAI — Database cns_db siap digunakan
-- Langkah selanjutnya:
-- 1. php artisan serve
-- 2. Buka http://localhost:8000/register
-- 3. Buat akun Owner terlebih dahulu, kemudian Admin dan Kasir
-- 4. Login dan mulai menggunakan sistem
-- ============================================================
SELECT TABLE_NAME AS 'Tabel Berhasil Dibuat'
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'cns_db'
ORDER BY TABLE_NAME;
