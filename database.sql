/*M!999999\- enable the sandbox mode */ 
-- Database Dump for PT. Nakakin Indonesia Leave Management System
-- Generated: 2026-10-01 08:14:11
-- ------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_cuti_nakakin` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `db_cuti_nakakin`;

--
-- Table structure for table `absensi_shift`
--

DROP TABLE IF EXISTS `absensi_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi_shift` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `leader_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `shift` enum('Shift 1','Shift 1 (Pagi)','Shift 2 (Malam)','Shift 2 (Maju)') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Shift 1 (Pagi)',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mangkir',
  `keterangan_mangkir` text COLLATE utf8mb4_unicode_ci,
  `status_revisi` enum('original','revised') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'original',
  `direvisi_menjadi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alasan_revisi` text COLLATE utf8mb4_unicode_ci,
  `bukti_lampiran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `direvisi_pada` datetime DEFAULT NULL,
  `hrd_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_emp_tgl_shift` (`employee_id`,`tanggal`,`shift`),
  KEY `idx_emp` (`employee_id`),
  KEY `idx_leader` (`leader_id`),
  KEY `idx_tgl` (`tanggal`),
  CONSTRAINT `fk_abs_emp` FOREIGN KEY (`employee_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_abs_leader` FOREIGN KEY (`leader_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absensi_shift`
--

-- (No records for `absensi_shift`)

--
-- Table structure for table `departemen`
--

DROP TABLE IF EXISTS `departemen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departemen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_dept` varchar(20) NOT NULL,
  `nama_dept` varchar(100) NOT NULL,
  `deskripsi` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_dept` (`kode_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departemen`
--

LOCK TABLES `departemen` WRITE;
/*!40000 ALTER TABLE `departemen` DISABLE KEYS */;
INSERT INTO `departemen` VALUES 
(1,'ACC','Accounting','Accounting & Finance Department','2026-10-01 15:11:34'),
(2,'CAST','Casting','Casting & Foundry Department','2026-10-01 15:11:34'),
(3,'CORE','Core','Core Production Department','2026-10-01 15:11:34'),
(4,'DC','Diecasting','Die Casting Production Department','2026-10-01 15:11:34'),
(5,'ENG','Engineering','Engineering & Technical Department','2026-10-01 15:11:34'),
(6,'FET','Fettling','Fettling & Finishing Department','2026-10-01 15:11:34'),
(7,'GA','GA','General Affairs Department','2026-10-01 15:11:34'),
(8,'HRD','HRD','Human Resources Department','2026-10-01 15:11:34'),
(9,'MC','Machining','Machining Production Department','2026-10-01 15:11:34'),
(10,'MKT','Marketing','Sales & Marketing Department','2026-10-01 15:11:34'),
(11,'MAINT','Maintenance','Machine & Utility Maintenance Department','2026-10-01 15:11:34'),
(12,'PPIC','PPIC','Production Planning & Inventory Control','2026-10-01 15:11:34'),
(13,'PURCH','Purchasing','Purchasing & Procurement Department','2026-10-01 15:11:34'),
(14,'QC','QC','Quality Control Department','2026-10-01 15:11:34'),
(15,'QCL','QC Line','Quality Control Line Inspection Department','2026-10-01 15:11:34');
/*!40000 ALTER TABLE `departemen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jabatan`
--

DROP TABLE IF EXISTS `jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jabatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  `level_hierarki` int NOT NULL DEFAULT '1' COMMENT '1: Operator, 2: Staff, 3: Leader, 4: Supervisor, 5: Assistant Manager, 6: Manager, 7: HRD Admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jabatan`
--

LOCK TABLES `jabatan` WRITE;
/*!40000 ALTER TABLE `jabatan` DISABLE KEYS */;
INSERT INTO `jabatan` VALUES 
(1,'Operator Produksi',1,'2026-10-01 15:11:34'),
(2,'Staff',2,'2026-10-01 15:11:34'),
(3,'Leader',3,'2026-10-01 15:11:34'),
(4,'Supervisor (Spv)',4,'2026-10-01 15:11:34'),
(5,'Department Manager',6,'2026-10-01 15:11:35'),
(6,'HRD',7,'2026-10-01 15:11:35'),
(7,'Super Admin',8,'2026-10-01 15:11:35');
/*!40000 ALTER TABLE `jabatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jenis_cuti`
--

DROP TABLE IF EXISTS `jenis_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_cuti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_cuti` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Cuti Reguler',
  `kode` varchar(20) NOT NULL,
  `durasi_maksimal` varchar(50) DEFAULT '1 Hari',
  `deskripsi` text,
  `potong_kuota` tinyint(1) NOT NULL DEFAULT '0',
  `potong_gaji` tinyint(1) NOT NULL DEFAULT '0',
  `max_hari_default` int NOT NULL DEFAULT '12',
  `butuh_lampiran` tinyint(1) NOT NULL DEFAULT '0',
  `wewenang_approval` varchar(100) DEFAULT 'Leader & Spv Dept',
  `catatan_khusus` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_cuti`
--

LOCK TABLES `jenis_cuti` WRITE;
/*!40000 ALTER TABLE `jenis_cuti` DISABLE KEYS */;
INSERT INTO `jenis_cuti` VALUES 
(1,'Sakit Surat Dokter (Tidak Potong Gaji)','Cuti Reguler','SSD','1 Hari','Izin sakit resmi dengan melampirkan surat keterangan dokter.',0,0,14,1,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35'),
(2,'Sakit Tanpa Surat Dokter (Potong Jatah/Gaji)','Cuti Reguler','STSD','1 Hari','Izin sakit tanpa surat dokter.',1,0,3,0,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35'),
(3,'Cuti Tahunan (Reguler HRD)','Cuti Reguler','CT','1 Hari','Hak cuti tahunan yang dialokasikan oleh HRD.',1,0,12,0,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35'),
(4,'Cuti Haid','Cuti Reguler','CH','1 Hari','Cuti haid/menstruasi bagi karyawati.',1,0,2,0,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35'),
(5,'Cuti Melahirkan / Bersalin (3 Bulan)','Cuti Reguler','CML','1 Hari','Hak cuti bersalin bagi karyawati (3 bulan / 90 hari).',0,0,90,1,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35'),
(6,'Cuti Khusus (Keluarga Meninggal)','Cuti Reguler','CKH','1 Hari','Cuti duka cita karena anggota keluarga inti meninggal.',0,0,2,0,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35'),
(7,'Ijin Tidak Masuk (Potong Gaji)','Cuti Reguler','IJN','1 Hari','Izin tidak masuk kerja untuk keperluan mendesak.',0,0,7,0,'Leader & Spv Dept',NULL,'2026-10-01 15:11:35');
/*!40000 ALTER TABLE `jenis_cuti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `karyawan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'operator',
  `departemen_id` int NOT NULL,
  `jabatan_id` int NOT NULL,
  `current_shift` enum('Shift 1','Shift 1 (Pagi)','Shift 2 (Malam)','Shift 2 (Maju)') NOT NULL DEFAULT 'Shift 1 (Pagi)',
  `tanggal_masuk` date NOT NULL,
  `kuota_cuti` decimal(4,1) NOT NULL DEFAULT '12.0',
  `cuti_terpakai` decimal(4,1) NOT NULL DEFAULT '0.0',
  `sisa_cuti` decimal(4,1) NOT NULL DEFAULT '12.0',
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL DEFAULT 'Laki-laki',
  `agama` varchar(50) NOT NULL DEFAULT 'Islam',
  `status_pernikahan` enum('Belum Menikah','Menikah','Duda','Janda') NOT NULL DEFAULT 'Belum Menikah',
  `no_hp` varchar(30) DEFAULT NULL,
  `alamat` text,
  `foto` varchar(255) DEFAULT NULL,
  `status_aktif` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nik` (`nik`),
  UNIQUE KEY `email` (`email`),
  KEY `departemen_id` (`departemen_id`),
  KEY `jabatan_id` (`jabatan_id`),
  CONSTRAINT `karyawan_ibfk_1` FOREIGN KEY (`departemen_id`) REFERENCES `departemen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `karyawan_ibfk_2` FOREIGN KEY (`jabatan_id`) REFERENCES `jabatan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karyawan`
--

LOCK TABLES `karyawan` WRITE;
/*!40000 ALTER TABLE `karyawan` DISABLE KEYS */;
INSERT INTO `karyawan` VALUES 
(1,'ADM-001','Hermawan','admin@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','superadmin',8,7,'Shift 1 (Pagi)','2020-01-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081100000001','Kantor Pusat PT. Nakakin Indonesia',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(2,'NAK-001','Risma (HRD)','hrd@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','hrd',8,6,'Shift 1 (Pagi)','2020-03-01',12.0,0.0,12.0,'Perempuan','Islam','Menikah','081234567890','Kawasan Industri KIIC, Karawang Barat',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(3,'NAK-002','Bambang Setyo (GA)','ga@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','staff',7,2,'Shift 1 (Pagi)','2021-02-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081234567891','Perum Resinda, Karawang Barat',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(4,'MGR-001','Ir. Hendra Wijaya (Manager)','manager@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','manager',5,5,'Shift 1 (Pagi)','2018-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081399990001','Grand Taruma, Karawang Barat',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(5,'SPV-ACC','Spv Accounting','spv.acc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',1,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000001','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(6,'LDR-ACC','Leader Accounting','ldr.acc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',1,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000001','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(7,'OP1-ACC','Operator 1 Accounting','op1.acc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',1,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000001','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(8,'OP2-ACC','Operator 2 Accounting','op2.acc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',1,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000001','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(9,'SPV-CAST','Spv Casting','spv.cast@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',2,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000002','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(10,'LDR-CAST','Leader Casting','ldr.cast@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',2,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000002','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(11,'OP1-CAST','Operator 1 Casting','op1.cast@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',2,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000002','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(12,'OP2-CAST','Operator 2 Casting','op2.cast@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',2,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000002','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(13,'SPV-CORE','Spv Core','spv.core@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',3,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000003','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(14,'LDR-CORE','Leader Core','ldr.core@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',3,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000003','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(15,'OP1-CORE','Operator 1 Core','op1.core@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',3,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000003','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(16,'OP2-CORE','Operator 2 Core','op2.core@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',3,1,'Shift 1 (Pagi)','2024-01-15',12.0,2.0,10.0,'Perempuan','Islam','Menikah','08170000003','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(17,'SPV-DC','Spv Diecasting','spv.dc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',4,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000004','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(18,'LDR-DC','Leader Diecasting','ldr.dc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',4,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000004','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(19,'OP1-DC','Operator 1 Diecasting','op1.dc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',4,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000004','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(20,'OP2-DC','Operator 2 Diecasting','op2.dc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',4,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000004','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(21,'SPV-ENG','Spv Engineering','spv.eng@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',5,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000005','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(22,'LDR-ENG','Leader Engineering','ldr.eng@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',5,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000005','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(23,'OP1-ENG','Operator 1 Engineering','op1.eng@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',5,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000005','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(24,'OP2-ENG','Operator 2 Engineering','op2.eng@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',5,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000005','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(25,'SPV-FET','Spv Fettling','spv.fet@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',6,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000006','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(26,'LDR-FET','Leader Fettling','ldr.fet@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',6,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000006','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(27,'OP1-FET','Operator 1 Fettling','op1.fet@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',6,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000006','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(28,'OP2-FET','Operator 2 Fettling','op2.fet@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',6,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000006','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(29,'SPV-GA','Spv GA','spv.ga@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',7,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000007','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(30,'LDR-GA','Leader GA','ldr.ga@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',7,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000007','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(31,'OP1-GA','Operator 1 GA','op1.ga@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',7,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000007','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(32,'OP2-GA','Operator 2 GA','op2.ga@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',7,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000007','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(33,'SPV-HRD','Spv HRD','spv.hrd@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',8,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000008','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(34,'LDR-HRD','Leader HRD','ldr.hrd@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',8,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000008','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(35,'OP1-HRD','Operator 1 HRD','op1.hrd@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',8,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000008','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(36,'OP2-HRD','Operator 2 HRD','op2.hrd@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',8,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000008','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(37,'SPV-MC','Spv Machining','spv.mc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',9,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000009','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(38,'LDR-MC','Leader Machining','ldr.mc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',9,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000009','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(39,'OP1-MC','Operator 1 Machining','op1.mc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',9,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000009','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(40,'OP2-MC','Operator 2 Machining','op2.mc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',9,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000009','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(41,'SPV-MKT','Spv Marketing','spv.mkt@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',10,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000010','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(42,'LDR-MKT','Leader Marketing','ldr.mkt@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',10,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000010','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(43,'OP1-MKT','Operator 1 Marketing','op1.mkt@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',10,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000010','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(44,'OP2-MKT','Operator 2 Marketing','op2.mkt@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',10,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000010','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(45,'SPV-MAINT','Spv Maintenance','spv.maint@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',11,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000011','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(46,'LDR-MAINT','Leader Maintenance','ldr.maint@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',11,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000011','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(47,'OP1-MAINT','Operator 1 Maintenance','op1.maint@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',11,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000011','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(48,'OP2-MAINT','Operator 2 Maintenance','op2.maint@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',11,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000011','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(49,'SPV-PPIC','Spv PPIC','spv.ppic@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',12,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000012','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(50,'LDR-PPIC','Leader PPIC','ldr.ppic@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',12,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000012','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(51,'OP1-PPIC','Operator 1 PPIC','op1.ppic@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',12,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000012','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(52,'OP2-PPIC','Operator 2 PPIC','op2.ppic@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',12,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000012','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(53,'SPV-PURCH','Spv Purchasing','spv.purch@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',13,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000013','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(54,'LDR-PURCH','Leader Purchasing','ldr.purch@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',13,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000013','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(55,'OP1-PURCH','Operator 1 Purchasing','op1.purch@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',13,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000013','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(56,'OP2-PURCH','Operator 2 Purchasing','op2.purch@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',13,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000013','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(57,'SPV-QC','Spv QC','spv.qc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',14,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000014','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(58,'LDR-QC','Leader QC','ldr.qc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',14,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000014','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(59,'OP1-QC','Operator 1 QC','op1.qc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',14,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000014','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(60,'OP2-QC','Operator 2 QC','op2.qc@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',14,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000014','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(61,'SPV-QCL','Spv QC Line','spv.qcl@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','supervisor',15,4,'Shift 1 (Pagi)','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000015','Telukjambe Timur, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(62,'LDR-QCL','Leader QC Line','ldr.qcl@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','leader',15,3,'Shift 1 (Pagi)','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000015','Klari, Karawang Timur',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(63,'OP1-QCL','Operator 1 QC Line','op1.qcl@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',15,1,'Shift 1 (Pagi)','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000015','Kosambi, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35'),
(64,'OP2-QCL','Operator 2 QC Line','op2.qcl@nakakin.co.id','$2y$10$2.4DdrjnOcP6P45tL2XaAuGvxQ.om6DgbWIIur6Zpgxrr6fdrMMQG','operator',15,1,'Shift 1 (Pagi)','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000015','Cikampek, Karawang',NULL,'Aktif','2026-10-01 15:11:35','2026-10-01 15:11:35');
/*!40000 ALTER TABLE `karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengajuan_cuti`
--

DROP TABLE IF EXISTS `pengajuan_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengajuan_cuti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(60) NOT NULL,
  `employee_id` int NOT NULL,
  `leave_type_id` int NOT NULL,
  `shift` enum('Shift 1','Shift 1 (Pagi)','Shift 2 (Malam)','Shift 2 (Maju)','Non-Shift') NOT NULL DEFAULT 'Shift 1 (Pagi)',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `total_hari` decimal(4,1) NOT NULL DEFAULT '1.0',
  `alasan` text NOT NULL,
  `alamat_selama_cuti` text,
  `kontak_darurat` varchar(50) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` int DEFAULT NULL COMMENT 'ID User Atasan yang menyetujui/menolak',
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text,
  `catatan_atasan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approval_step` varchar(30) NOT NULL DEFAULT 'pending_spv' COMMENT 'pending_spv, pending_manager, pending_hrd, approved, rejected, cancelled',
  `spv_id` int DEFAULT NULL,
  `spv_at` datetime DEFAULT NULL,
  `spv_notes` text,
  `manager_id` int DEFAULT NULL,
  `manager_at` datetime DEFAULT NULL,
  `manager_notes` text,
  `hrd_id` int DEFAULT NULL,
  `hrd_at` datetime DEFAULT NULL,
  `hrd_notes` text,
  `notif_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_surat` (`nomor_surat`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `jenis_cuti` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_cuti_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengajuan_cuti`
--

LOCK TABLES `pengajuan_cuti` WRITE;
/*!40000 ALTER TABLE `pengajuan_cuti` DISABLE KEYS */;
INSERT INTO `pengajuan_cuti` VALUES 
(1,'CUTI/NAK/2026/10/001',7,3,'Shift 1 (Pagi)','2026-10-02','2026-10-03',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(2,'CUTI/NAK/2026/10/002',11,3,'Shift 1 (Pagi)','2026-10-03','2026-10-04',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(3,'CUTI/NAK/2026/10/003',15,3,'Shift 1 (Pagi)','2026-10-04','2026-10-05',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(4,'CUTI/NAK/2026/10/004',19,3,'Shift 1 (Pagi)','2026-10-05','2026-10-06',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(5,'CUTI/NAK/2026/10/005',23,3,'Shift 1 (Pagi)','2026-10-06','2026-10-07',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(6,'CUTI/NAK/2026/10/006',27,3,'Shift 1 (Pagi)','2026-10-07','2026-10-08',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(7,'CUTI/NAK/2026/10/007',31,3,'Shift 1 (Pagi)','2026-10-08','2026-10-09',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(8,'CUTI/NAK/2026/10/008',35,3,'Shift 1 (Pagi)','2026-10-09','2026-10-10',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(9,'CUTI/NAK/2026/10/009',39,3,'Shift 1 (Pagi)','2026-10-10','2026-10-11',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(10,'CUTI/NAK/2026/10/010',43,3,'Shift 1 (Pagi)','2026-10-11','2026-10-12',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(11,'CUTI/NAK/2026/10/011',47,3,'Shift 1 (Pagi)','2026-10-12','2026-10-13',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(12,'CUTI/NAK/2026/10/012',51,3,'Shift 1 (Pagi)','2026-10-13','2026-10-14',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(13,'CUTI/NAK/2026/10/013',55,3,'Shift 1 (Pagi)','2026-10-14','2026-10-15',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(14,'CUTI/NAK/2026/10/014',59,3,'Shift 1 (Pagi)','2026-10-15','2026-10-16',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(15,'CUTI/NAK/2026/10/015',63,3,'Shift 1 (Pagi)','2026-10-16','2026-10-17',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-10-01 15:11:35','2026-10-01 15:11:35','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(16,'CUTI/NAK/2026/10/016',16,3,'Shift 1 (Pagi)','2026-09-01','2026-09-02',NULL,NULL,2.0,'Acara keluarga dan mudik awal bulan.','Karawang','0812345678',NULL,'approved',2,'2026-09-01 10:00:00',NULL,NULL,'2026-08-30 08:00:00','2026-09-01 10:00:00','approved',13,'2026-08-31 09:00:00','Disetujui oleh Spv',4,'2026-08-31 14:00:00','Disetujui oleh Manager',2,'2026-09-01 10:00:00','Disetujui HRD dan kuota dipotong',1);
/*!40000 ALTER TABLE `pengajuan_cuti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pengaturan_aplikasi`
--

DROP TABLE IF EXISTS `pengaturan_aplikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_aplikasi` (
  `id` int NOT NULL,
  `nama_aplikasi` varchar(150) NOT NULL DEFAULT 'Sistem Informasi Cuti Karyawan',
  `singkatan_aplikasi` varchar(50) NOT NULL DEFAULT 'E-Cuti',
  `tagline` varchar(255) NOT NULL DEFAULT 'Precision Machinery, Pumps & Tooling Manufacturing',
  `nama_perusahaan` varchar(150) NOT NULL DEFAULT 'PT. Nakakin Indonesia',
  `singkatan_perusahaan` varchar(50) NOT NULL DEFAULT 'NAKAKIN',
  `sub_singkatan_perusahaan` varchar(50) NOT NULL DEFAULT 'INDONESIA',
  `alamat_perusahaan` text,
  `telepon` varchar(50) DEFAULT '(0267) 845-1234',
  `email_perusahaan` varchar(100) DEFAULT 'hrd@nakakin.co.id',
  `website` varchar(100) DEFAULT 'www.nakakin.co.id',
  `logo` varchar(255) DEFAULT 'Nakakin.png',
  `favicon` varchar(255) DEFAULT 'Nakakin.png',
  `prefix_nomor_surat` varchar(30) NOT NULL DEFAULT 'CUTI/NAK',
  `default_kuota_cuti` int NOT NULL DEFAULT '12',
  `nama_kepala_hrd` varchar(150) NOT NULL DEFAULT 'Siti Rahmawati, S.Psi',
  `jabatan_kepala_hrd` varchar(100) NOT NULL DEFAULT 'HRD & GA Manager',
  `lokasi_surat` varchar(100) NOT NULL DEFAULT 'Karawang',
  `footer_text` varchar(255) NOT NULL DEFAULT 'Sistem Informasi Manajemen Cuti Karyawan',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_aplikasi`
--

LOCK TABLES `pengaturan_aplikasi` WRITE;
/*!40000 ALTER TABLE `pengaturan_aplikasi` DISABLE KEYS */;
INSERT INTO `pengaturan_aplikasi` VALUES 
(1,'Nakakin Mobile','Nakakin Mobile','Precision Machinery, Pumps & Tooling Manufacturing','PT. NAKAKIN INDONESIA','NAKAKIn','INDONESIA','EJIP INDUSTRIAL PARK PLOT 5L-4 CIKARANG SELATAN, BEKASI 17550','(0267) 845-1234','hrd@nakakin.co.id','www.nakakin.co.id','Nakakin.png','Nakakin.png','CUTI/NAK',10,'Hermawan','HRD GA Manager','Bekasi','Sistem Informasi Manajemen Cuti Karyawan','2026-09-29 08:06:41');
/*!40000 ALTER TABLE `pengaturan_aplikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_kuota_cuti`
--

DROP TABLE IF EXISTS `riwayat_kuota_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_kuota_cuti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `kuota_sebelum` int NOT NULL,
  `perubahan` int NOT NULL,
  `kuota_sesudah` int NOT NULL,
  `tipe` enum('alokasi_tahunan','penyesuaian_hrd','bonus_lembur','potong_cuti') NOT NULL DEFAULT 'alokasi_tahunan',
  `keterangan` text NOT NULL,
  `created_by` int DEFAULT NULL COMMENT 'ID User HRD/Admin yang memproses perubahan',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `riwayat_kuota_cuti_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_kuota_cuti_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_kuota_cuti`
--

LOCK TABLES `riwayat_kuota_cuti` WRITE;
/*!40000 ALTER TABLE `riwayat_kuota_cuti` DISABLE KEYS */;
INSERT INTO `riwayat_kuota_cuti` VALUES 
(1,16,12,-2,10,'potong_cuti','Pemotongan cuti disetujui (CUTI/NAK/2026/10/016) oleh HRD',2,'2026-09-01 10:00:00');
/*!40000 ALTER TABLE `riwayat_kuota_cuti` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `permission_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_role_perm` (`role`,`permission_key`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` VALUES 
(1,'operator','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(2,'operator','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(3,'operator','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(4,'operator','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(5,'operator','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(6,'operator','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(7,'operator','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(8,'operator','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(9,'operator','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',0,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(10,'staff','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:52','2026-09-25 14:52:52'),
(11,'staff','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(12,'staff','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(13,'staff','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(14,'staff','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(15,'staff','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(16,'staff','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(17,'staff','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',0,'2026-09-25 14:52:53','2026-09-25 14:58:56'),
(18,'staff','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(19,'leader','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(20,'leader','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(21,'leader','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(22,'leader','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(23,'leader','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(24,'leader','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(25,'leader','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(26,'leader','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(27,'leader','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(28,'supervisor','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(29,'supervisor','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(30,'supervisor','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(31,'supervisor','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(32,'supervisor','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(33,'supervisor','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(34,'supervisor','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(35,'supervisor','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(36,'supervisor','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(37,'manager','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(38,'manager','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(39,'manager','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(40,'manager','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(41,'manager','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(42,'manager','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(43,'manager','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(44,'manager','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(45,'manager','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(46,'hrd','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(47,'hrd','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(48,'hrd','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(49,'hrd','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',0,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(50,'hrd','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(51,'hrd','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(52,'hrd','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(53,'hrd','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(54,'hrd','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(55,'superadmin','leave_request','Pengajuan Cuti Pribadi','Cuti & Kehadiran','Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(56,'superadmin','view_public_board','Papan Informasi & Kalender Bersama','Cuti & Kehadiran','Melihat jadwal cuti rekan kerja, kalender pabrik, dan statistik umum',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(57,'superadmin','approval_tier1','Persetujuan Tier 1 (Leader / Supervisor)','Persetujuan (Approval)','Verifikasi dan review pengajuan cuti operator/staff di departemen sendiri',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(58,'superadmin','approval_tier2','Persetujuan Tier 2 (Plant Manager)','Persetujuan (Approval)','Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(59,'superadmin','approval_tier3','Persetujuan Tier 3 / Final (HRD)','Persetujuan (Approval)','Persetujuan akhir resmi dan eksekusi otomatis pemotongan kuota cuti tahunan',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(60,'superadmin','manage_employees','Kelola Master Data Karyawan','Manajemen HRD','Tambah, ubah, hapus, reset password, dan import massal Excel data karyawan',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(61,'superadmin','manage_quotas','Penyesuaian & Alokasi Kuota Cuti','Manajemen HRD','Penyesuaian sisa kuota cuti individual maupun alokasi kuota massal (Bulk)',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(62,'superadmin','view_reports','Rekap Laporan & Cetak Surat Cuti','Laporan & Dokumen','Melihat filter analitik rekapitulasi cuti dan cetak surat resmi format PDF/A4',1,'2026-09-25 14:52:53','2026-09-25 14:52:53'),
(63,'superadmin','manage_settings','Pengaturan Sistem, Kop Surat & Privilege','Sistem & Konfigurasi','Mengatur nama instansi, logo perusahaan, nomor surat, dan matriks hak akses',1,'2026-09-25 14:52:53','2026-09-25 14:52:53');
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
-- Dump completed on 2026-10-01 08:14:11
