-- MySQL dump 10.13  Distrib 9.6.0, for Win64 (x86_64)
--
-- Host: localhost    Database: spk_moora
-- ------------------------------------------------------
-- Server version	9.6.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '58ba92b9-2b81-11f1-805f-30a2991ab43b:1-9211';

--
-- Table structure for table `batasan`
--

DROP TABLE IF EXISTS `batasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `batasan` (
  `id_batasan` int NOT NULL AUTO_INCREMENT,
  `jenis_batasan` varchar(100) NOT NULL,
  `nilai_batasan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_batasan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `batasan`
--

LOCK TABLES `batasan` WRITE;
/*!40000 ALTER TABLE `batasan` DISABLE KEYS */;
/*!40000 ALTER TABLE `batasan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hasil_ranking`
--

DROP TABLE IF EXISTS `hasil_ranking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hasil_ranking` (
  `id_hasil` int NOT NULL AUTO_INCREMENT,
  `id_perhitungan` int NOT NULL,
  `id_produk` int NOT NULL,
  `nilai_yi` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `ranking` int NOT NULL,
  `prioritas` enum('Tinggi','Sedang','Rendah') DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_hasil`),
  KEY `fk_hasil_perhitungan` (`id_perhitungan`),
  KEY `fk_hasil_produk` (`id_produk`),
  CONSTRAINT `fk_hasil_perhitungan` FOREIGN KEY (`id_perhitungan`) REFERENCES `perhitungan` (`id_perhitungan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hasil_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hasil_ranking`
--

LOCK TABLES `hasil_ranking` WRITE;
/*!40000 ALTER TABLE `hasil_ranking` DISABLE KEYS */;
/*!40000 ALTER TABLE `hasil_ranking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `input_permintaan`
--

DROP TABLE IF EXISTS `input_permintaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `input_permintaan` (
  `id_input` int NOT NULL AUTO_INCREMENT,
  `id_produk` int NOT NULL,
  `id_kriteria` int NOT NULL,
  `nilai_input` int NOT NULL,
  `skala` tinyint NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_input`),
  KEY `fk_input_produk` (`id_produk`),
  KEY `fk_input_kriteria` (`id_kriteria`),
  CONSTRAINT `fk_input_kriteria` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_input_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_skala` CHECK ((`skala` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `input_permintaan`
--

LOCK TABLES `input_permintaan` WRITE;
/*!40000 ALTER TABLE `input_permintaan` DISABLE KEYS */;
/*!40000 ALTER TABLE `input_permintaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kriteria`
--

DROP TABLE IF EXISTS `kriteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kriteria` (
  `id_kriteria` int NOT NULL AUTO_INCREMENT,
  `nama_kriteria` varchar(150) NOT NULL,
  `tipe_atribut` enum('Benefit','Cost') NOT NULL,
  `bobot` decimal(5,2) NOT NULL DEFAULT '0.00',
  `sumber_data` enum('Import Excel','Input Manual') NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kriteria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kriteria`
--

LOCK TABLES `kriteria` WRITE;
/*!40000 ALTER TABLE `kriteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `kriteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nilai_produk`
--

DROP TABLE IF EXISTS `nilai_produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nilai_produk` (
  `id_nilai` int NOT NULL AUTO_INCREMENT,
  `id_produk` int NOT NULL,
  `id_kriteria` int NOT NULL,
  `nilai` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_nilai`),
  UNIQUE KEY `uq_produk_kriteria` (`id_produk`,`id_kriteria`),
  KEY `fk_nilai_kriteria` (`id_kriteria`),
  CONSTRAINT `fk_nilai_kriteria` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_nilai_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nilai_produk`
--

LOCK TABLES `nilai_produk` WRITE;
/*!40000 ALTER TABLE `nilai_produk` DISABLE KEYS */;
/*!40000 ALTER TABLE `nilai_produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perhitungan`
--

DROP TABLE IF EXISTS `perhitungan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perhitungan` (
  `id_perhitungan` int NOT NULL AUTO_INCREMENT,
  `id_batasan` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal_hitung` datetime DEFAULT CURRENT_TIMESTAMP,
  `matriks_normal` json DEFAULT NULL,
  `matriks_keputusan` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_perhitungan`),
  KEY `fk_perhitungan_batasan` (`id_batasan`),
  KEY `fk_perhitungan_user` (`id_user`),
  CONSTRAINT `fk_perhitungan_batasan` FOREIGN KEY (`id_batasan`) REFERENCES `batasan` (`id_batasan`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_perhitungan_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perhitungan`
--

LOCK TABLES `perhitungan` WRITE;
/*!40000 ALTER TABLE `perhitungan` DISABLE KEYS */;
/*!40000 ALTER TABLE `perhitungan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perhitungan_kriteria`
--

DROP TABLE IF EXISTS `perhitungan_kriteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perhitungan_kriteria` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_perhitungan` int NOT NULL,
  `id_kriteria` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hitung_kriteria` (`id_perhitungan`,`id_kriteria`),
  KEY `fk_hk_kriteria` (`id_kriteria`),
  CONSTRAINT `fk_hk_kriteria` FOREIGN KEY (`id_kriteria`) REFERENCES `kriteria` (`id_kriteria`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hk_perhitungan` FOREIGN KEY (`id_perhitungan`) REFERENCES `perhitungan` (`id_perhitungan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perhitungan_kriteria`
--

LOCK TABLES `perhitungan_kriteria` WRITE;
/*!40000 ALTER TABLE `perhitungan_kriteria` DISABLE KEYS */;
/*!40000 ALTER TABLE `perhitungan_kriteria` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `perhitungan_produk`
--

DROP TABLE IF EXISTS `perhitungan_produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `perhitungan_produk` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_perhitungan` int NOT NULL,
  `id_produk` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hitung_produk` (`id_perhitungan`,`id_produk`),
  KEY `fk_hp_produk` (`id_produk`),
  CONSTRAINT `fk_hp_perhitungan` FOREIGN KEY (`id_perhitungan`) REFERENCES `perhitungan` (`id_perhitungan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_hp_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `perhitungan_produk`
--

LOCK TABLES `perhitungan_produk` WRITE;
/*!40000 ALTER TABLE `perhitungan_produk` DISABLE KEYS */;
/*!40000 ALTER TABLE `perhitungan_produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produk`
--

DROP TABLE IF EXISTS `produk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `produk` (
  `id_produk` int NOT NULL AUTO_INCREMENT,
  `nama_produk` varchar(150) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `status_data` enum('Lengkap','Belum Lengkap') NOT NULL DEFAULT 'Belum Lengkap',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_produk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produk`
--

LOCK TABLES `produk` WRITE;
/*!40000 ALTER TABLE `produk` DISABLE KEYS */;
/*!40000 ALTER TABLE `produk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat`
--

DROP TABLE IF EXISTS `riwayat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat` (
  `id_riwayat` int NOT NULL AUTO_INCREMENT,
  `id_perhitungan` int NOT NULL,
  `tanggal_simpan` datetime DEFAULT CURRENT_TIMESTAMP,
  `keterangan` text,
  PRIMARY KEY (`id_riwayat`),
  UNIQUE KEY `id_perhitungan` (`id_perhitungan`),
  CONSTRAINT `fk_riwayat_perhitungan` FOREIGN KEY (`id_perhitungan`) REFERENCES `perhitungan` (`id_perhitungan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat`
--

LOCK TABLES `riwayat` WRITE;
/*!40000 ALTER TABLE `riwayat` DISABLE KEYS */;
/*!40000 ALTER TABLE `riwayat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Manajer') NOT NULL DEFAULT 'Admin',
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-29 15:03:54
