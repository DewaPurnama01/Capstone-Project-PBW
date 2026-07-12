-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: localhost    Database: cns_db
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `detail_transaksi`
--

DROP TABLE IF EXISTS `detail_transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_transaksi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `transaksi_id` bigint(20) unsigned NOT NULL,
  `nama_item` varchar(100) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detail_transaksi` (`transaksi_id`),
  CONSTRAINT `fk_detail_transaksi` FOREIGN KEY (`transaksi_id`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_transaksi`
--

LOCK TABLES `detail_transaksi` WRITE;
/*!40000 ALTER TABLE `detail_transaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `detail_transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pelanggan`
--

DROP TABLE IF EXISTS `pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelanggan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `segmen` enum('VIP','Member','Reguler','Baru') NOT NULL DEFAULT 'Baru',
  `menu_favorit` varchar(100) DEFAULT NULL,
  `poin` int(11) NOT NULL DEFAULT 0,
  `total_kunjungan` int(11) NOT NULL DEFAULT 0,
  `total_belanja` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` enum('aktif','tidak aktif') NOT NULL DEFAULT 'aktif',
  `tanggal_daftar` date NOT NULL,
  `terakhir_kunjungan` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pelanggan`
--

LOCK TABLES `pelanggan` WRITE;
/*!40000 ALTER TABLE `pelanggan` DISABLE KEYS */;
INSERT INTO `pelanggan` VALUES (1,'I Gede Manuke','087654321','gedemanuke@contoh.com','VIP','americano',0,0,0.00,'aktif','2026-06-25',NULL),(2,'I Made Contoh','08123456789','madepelanggan@contoh.com','Baru','americano',0,0,0.00,'aktif','2026-06-25',NULL),(3,'I Dewa Gede Manuke','081696696696','dewagede@contoh.com','Member','Matcha Latte',0,0,0.00,'aktif','2026-06-26',NULL);
/*!40000 ALTER TABLE `pelanggan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_bahan`
--

DROP TABLE IF EXISTS `tb_bahan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_bahan` (
  `id_bahan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_bahan` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Bahan Baku',
  `satuan` varchar(20) NOT NULL,
  `jumlah_stok` decimal(10,2) NOT NULL DEFAULT 0.00,
  `batas_minimum` decimal(10,2) NOT NULL DEFAULT 0.00,
  `batas_maksimum` decimal(10,2) NOT NULL DEFAULT 0.00,
  `harga_per_unit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `supplier` varchar(100) DEFAULT NULL,
  `status_stok` enum('NORMAL','RENDAH','HABIS') NOT NULL DEFAULT 'NORMAL',
  `is_coffee` tinyint(1) NOT NULL DEFAULT 0,
  `tanggal_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id_bahan`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_bahan`
--

LOCK TABLES `tb_bahan` WRITE;
/*!40000 ALTER TABLE `tb_bahan` DISABLE KEYS */;
INSERT INTO `tb_bahan` VALUES (1,'Biji Kopi Arabika','Bahan Baku','kg',2.00,2.00,5.00,180000.00,'Mang EkOk Bagus','NORMAL',0,'2026-06-26 15:07:14'),(2,'Susu Sapi Segar','Bahan Baku','liter',2.00,2.00,4.00,25000.00,'Mas Bahlil Gendeng','NORMAL',0,'2026-06-26 15:09:34');
/*!40000 ALTER TABLE `tb_bahan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_broadcast`
--

DROP TABLE IF EXISTS `tb_broadcast`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_broadcast` (
  `id_broadcast` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_bahan` int(10) unsigned NOT NULL,
  `jumlah_dibutuhkan` decimal(10,2) NOT NULL,
  `harga_target` decimal(15,2) NOT NULL,
  `tanggal_kirim` datetime NOT NULL,
  `batas_respon` datetime NOT NULL,
  `catatan` text DEFAULT NULL,
  `status_broadcast` enum('AKTIF','DITUTUP','SELESAI') NOT NULL DEFAULT 'AKTIF',
  PRIMARY KEY (`id_broadcast`),
  KEY `fk_broadcast_bahan` (`id_bahan`),
  CONSTRAINT `fk_broadcast_bahan` FOREIGN KEY (`id_bahan`) REFERENCES `tb_bahan` (`id_bahan`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_broadcast`
--

LOCK TABLES `tb_broadcast` WRITE;
/*!40000 ALTER TABLE `tb_broadcast` DISABLE KEYS */;
INSERT INTO `tb_broadcast` VALUES (1,1,2.00,200000.00,'2026-06-26 15:16:14','2026-06-29 15:15:00',NULL,'AKTIF');
/*!40000 ALTER TABLE `tb_broadcast` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_broadcast_token`
--

DROP TABLE IF EXISTS `tb_broadcast_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_broadcast_token` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `broadcast_id` int(10) unsigned NOT NULL,
  `mitra_id` int(10) unsigned NOT NULL,
  `token` varchar(64) NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `fk_btoken_broadcast` (`broadcast_id`),
  KEY `fk_btoken_mitra` (`mitra_id`),
  CONSTRAINT `fk_btoken_broadcast` FOREIGN KEY (`broadcast_id`) REFERENCES `tb_broadcast` (`id_broadcast`) ON DELETE CASCADE,
  CONSTRAINT `fk_btoken_mitra` FOREIGN KEY (`mitra_id`) REFERENCES `tb_mitra` (`id_mitra`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_broadcast_token`
--

LOCK TABLES `tb_broadcast_token` WRITE;
/*!40000 ALTER TABLE `tb_broadcast_token` DISABLE KEYS */;
INSERT INTO `tb_broadcast_token` VALUES (1,1,1,'0aKMu1yX4jrdJAApDMyXi4N6F6AE9qI1hP8ATVd5',0,'2026-06-26 07:16:14'),(2,1,2,'5z158Vgnm6QOqGdhNqeqgHujfC8PA0MyVWMFmWUz',0,'2026-06-26 07:16:14'),(3,1,3,'GzQUa4MnTIXVGpnB9sHSODiUpHrstpskwj40mPZE',0,'2026-06-26 07:16:14');
/*!40000 ALTER TABLE `tb_broadcast_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_hutang`
--

DROP TABLE IF EXISTS `tb_hutang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_hutang` (
  `id_hutang` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_qc` int(10) unsigned NOT NULL,
  `id_mitra` int(10) unsigned NOT NULL,
  `jumlah_tagihan` decimal(15,2) NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `status_bayar` enum('BELUM_BAYAR','SUDAH_BAYAR') NOT NULL DEFAULT 'BELUM_BAYAR',
  `tanggal_lunas` date DEFAULT NULL,
  `bukti_bayar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_hutang`),
  KEY `fk_hutang_qc` (`id_qc`),
  KEY `fk_hutang_mitra` (`id_mitra`),
  CONSTRAINT `fk_hutang_mitra` FOREIGN KEY (`id_mitra`) REFERENCES `tb_mitra` (`id_mitra`),
  CONSTRAINT `fk_hutang_qc` FOREIGN KEY (`id_qc`) REFERENCES `tb_quality_control` (`id_qc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_hutang`
--

LOCK TABLES `tb_hutang` WRITE;
/*!40000 ALTER TABLE `tb_hutang` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_hutang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_mitra`
--

DROP TABLE IF EXISTS `tb_mitra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_mitra` (
  `id_mitra` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_mitra` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `alamat` text DEFAULT NULL,
  `komoditas` varchar(100) NOT NULL DEFAULT 'Biji Kopi',
  `status_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `rating` decimal(3,1) NOT NULL DEFAULT 4.5,
  `total_order` int(11) NOT NULL DEFAULT 0,
  `persen_on_time` int(11) NOT NULL DEFAULT 100,
  `persen_kualitas` int(11) NOT NULL DEFAULT 100,
  `catatan` varchar(255) DEFAULT NULL,
  `tanggal_daftar` date NOT NULL,
  PRIMARY KEY (`id_mitra`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_mitra`
--

LOCK TABLES `tb_mitra` WRITE;
/*!40000 ALTER TABLE `tb_mitra` DISABLE KEYS */;
INSERT INTO `tb_mitra` VALUES (1,'Mang EkOk Bagus','081223334444','Mengwi, Badung, Bali','Biji Kopi',1,4.5,0,100,100,NULL,'2026-06-26'),(2,'Mas Bahlil Gendeng','087762954214','Jimbaran, Badung','Biji Kopi',1,4.5,0,100,100,NULL,'2026-06-26'),(3,'Mas Bahlil Gendeng','087762954214','Jimbaran, Badung','Biji Kopi',1,4.5,0,100,100,NULL,'2026-06-26');
/*!40000 ALTER TABLE `tb_mitra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_penawaran`
--

DROP TABLE IF EXISTS `tb_penawaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_penawaran` (
  `id_penawaran` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_broadcast` int(10) unsigned NOT NULL,
  `id_mitra` int(10) unsigned NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL,
  `jumlah_tersedia` decimal(10,2) NOT NULL,
  `estimasi_kirim` date NOT NULL,
  `catatan_mitra` text DEFAULT NULL,
  `status_penawaran` enum('MENUNGGU','DITERIMA','DITOLAK') NOT NULL DEFAULT 'MENUNGGU',
  `tanggal_input` datetime NOT NULL,
  PRIMARY KEY (`id_penawaran`),
  KEY `fk_penawaran_broadcast` (`id_broadcast`),
  KEY `fk_penawaran_mitra` (`id_mitra`),
  CONSTRAINT `fk_penawaran_broadcast` FOREIGN KEY (`id_broadcast`) REFERENCES `tb_broadcast` (`id_broadcast`),
  CONSTRAINT `fk_penawaran_mitra` FOREIGN KEY (`id_mitra`) REFERENCES `tb_mitra` (`id_mitra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_penawaran`
--

LOCK TABLES `tb_penawaran` WRITE;
/*!40000 ALTER TABLE `tb_penawaran` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_penawaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_penerimaan`
--

DROP TABLE IF EXISTS `tb_penerimaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_penerimaan` (
  `id_penerimaan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_po` int(10) unsigned NOT NULL,
  `tanggal_terima` date NOT NULL,
  `jumlah_diterima` decimal(10,2) NOT NULL,
  `kondisi_fisik` text DEFAULT NULL,
  `id_admin` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_penerimaan`),
  KEY `fk_penerimaan_po` (`id_po`),
  KEY `fk_penerimaan_admin` (`id_admin`),
  CONSTRAINT `fk_penerimaan_admin` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_penerimaan_po` FOREIGN KEY (`id_po`) REFERENCES `tb_purchase_order` (`id_po`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_penerimaan`
--

LOCK TABLES `tb_penerimaan` WRITE;
/*!40000 ALTER TABLE `tb_penerimaan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_penerimaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_purchase_order`
--

DROP TABLE IF EXISTS `tb_purchase_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_purchase_order` (
  `id_po` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nomor_po` varchar(30) NOT NULL,
  `id_penawaran` int(10) unsigned NOT NULL,
  `tanggal_terbit` date NOT NULL,
  `total_nilai` decimal(15,2) NOT NULL,
  `status_po` enum('DITERBITKAN','DIKIRIM','SELESAI','DIBATALKAN') NOT NULL DEFAULT 'DITERBITKAN',
  PRIMARY KEY (`id_po`),
  UNIQUE KEY `uq_nomor_po` (`nomor_po`),
  KEY `fk_po_penawaran` (`id_penawaran`),
  CONSTRAINT `fk_po_penawaran` FOREIGN KEY (`id_penawaran`) REFERENCES `tb_penawaran` (`id_penawaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_purchase_order`
--

LOCK TABLES `tb_purchase_order` WRITE;
/*!40000 ALTER TABLE `tb_purchase_order` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_purchase_order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_quality_control`
--

DROP TABLE IF EXISTS `tb_quality_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_quality_control` (
  `id_qc` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_penerimaan` int(10) unsigned NOT NULL,
  `hasil_qc` enum('LOLOS','TIDAK_LOLOS') NOT NULL,
  `catatan_qc` text DEFAULT NULL,
  `foto_dokumentasi` varchar(255) DEFAULT NULL COMMENT 'Max 2MB, path di storage/app/public/qc_photos/',
  `skor_aroma` tinyint(4) NOT NULL DEFAULT 0,
  `skor_warna` tinyint(4) NOT NULL DEFAULT 0,
  `skor_ukuran` tinyint(4) NOT NULL DEFAULT 0,
  `skor_kebersihan` tinyint(4) NOT NULL DEFAULT 0,
  `tanggal_qc` datetime NOT NULL,
  `id_admin` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id_qc`),
  KEY `fk_qc_penerimaan` (`id_penerimaan`),
  KEY `fk_qc_admin` (`id_admin`),
  CONSTRAINT `fk_qc_admin` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_qc_penerimaan` FOREIGN KEY (`id_penerimaan`) REFERENCES `tb_penerimaan` (`id_penerimaan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_quality_control`
--

LOCK TABLES `tb_quality_control` WRITE;
/*!40000 ALTER TABLE `tb_quality_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_quality_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transaksi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` varchar(100) NOT NULL,
  `pelanggan_id` bigint(20) unsigned DEFAULT NULL,
  `segmen` enum('VIP','Member','Reguler','Baru') NOT NULL DEFAULT 'Reguler',
  `metode_bayar` enum('QRIS','Tunai','Transfer') NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `status` enum('proses','selesai','batal') NOT NULL DEFAULT 'proses',
  `kasir` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_transaksi_pelanggan` (`pelanggan_id`),
  CONSTRAINT `fk_transaksi_pelanggan` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transaksi`
--

LOCK TABLES `transaksi` WRITE;
/*!40000 ALTER TABLE `transaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `transaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Owner','Admin','Kasir') NOT NULL DEFAULT 'Kasir',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'I Dewa Nyoman Purnama Jayaningrat','purnama','$2y$12$FVd9uARIFTvFijY020RWjO9d94YnUTiR81wriD2PZJXN3BO3GcO9q','Owner','2026-06-25 11:51:02','2026-06-25 11:51:02'),(2,'Rocky Gerung','rocky','$2y$12$bl2ASBmPVvgdps/s87EALuF.mfNccPQlReYocnQqpvfMofFvULuou','Admin','2026-06-25 14:38:14','2026-06-25 14:38:14'),(3,'Budi Santoso','budi','$2y$12$SJ9AY9535.eAecViYwIIrOhmUzIZpPZMJPbVan30I0esn1PdHiHMe','Kasir','2026-06-25 14:42:24','2026-06-25 14:42:24');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-26 15:35:22
