/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.20-12.3.3-MariaDB, for Android (aarch64)
--
-- Host: 127.0.0.1    Database: db_cuti_nakakin
-- ------------------------------------------------------
-- Server version	12.3.3-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Current Database: `db_cuti_nakakin`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `db_cuti_nakakin` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `db_cuti_nakakin`;

--
-- Table structure for table `absensi_shift`
--

DROP TABLE IF EXISTS `absensi_shift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `absensi_shift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `shift` enum('Shift 1','Shift 2 (Maju)') NOT NULL DEFAULT 'Shift 1',
  `status` varchar(30) NOT NULL DEFAULT 'mangkir',
  `keterangan_mangkir` text DEFAULT NULL,
  `status_revisi` enum('original','revised') NOT NULL DEFAULT 'original',
  `direvisi_menjadi` varchar(50) DEFAULT NULL,
  `alasan_revisi` text DEFAULT NULL,
  `bukti_lampiran` varchar(255) DEFAULT NULL,
  `direvisi_pada` datetime DEFAULT NULL,
  `hrd_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `absensi_shift` WRITE;
/*!40000 ALTER TABLE `absensi_shift` DISABLE KEYS */;
/*!40000 ALTER TABLE `absensi_shift` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `departemen`
--

DROP TABLE IF EXISTS `departemen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departemen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_dept` varchar(20) NOT NULL,
  `nama_dept` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_dept` (`kode_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departemen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `departemen` WRITE;
/*!40000 ALTER TABLE `departemen` DISABLE KEYS */;
INSERT INTO `departemen` VALUES
(1,'ACC','Accounting','Accounting & Finance Department','2026-09-23 11:50:09'),
(2,'CAST','Casting','Casting & Foundry Department','2026-09-23 11:50:09'),
(3,'CORE','Core','Core Production Department','2026-09-23 11:50:09'),
(4,'DC','Diecasting','Die Casting Production Department','2026-09-23 11:50:09'),
(5,'ENG','Engineering','Engineering & Technical Department','2026-09-23 11:50:09'),
(6,'FET','Fettling','Fettling & Finishing Department','2026-09-23 11:50:09'),
(7,'GA','GA','General Affairs Department','2026-09-23 11:50:09'),
(8,'HRD','HRD','Human Resources Department','2026-09-23 11:50:09'),
(9,'MC','Machining','Machining Production Department','2026-09-23 11:50:09'),
(10,'MKT','Marketing','Sales & Marketing Department','2026-09-23 11:50:09'),
(11,'MAINT','Maintenance','Machine & Utility Maintenance Department','2026-09-23 11:50:09'),
(12,'PPIC','PPIC','Production Planning & Inventory Control','2026-09-23 11:50:09'),
(13,'PURCH','Purchasing','Purchasing & Procurement Department','2026-09-23 11:50:09'),
(14,'QC','QC','Quality Control Department','2026-09-23 11:50:09'),
(15,'QCL','QC Line','Quality Control Line Inspection Department','2026-09-23 11:50:09');
/*!40000 ALTER TABLE `departemen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jabatan`
--

DROP TABLE IF EXISTS `jabatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jabatan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  `level_hierarki` int(11) NOT NULL DEFAULT 1 COMMENT '1: Operator, 2: Staff, 3: Leader, 4: Supervisor, 5: Assistant Manager, 6: Manager, 7: HRD Admin',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jabatan`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jabatan` WRITE;
/*!40000 ALTER TABLE `jabatan` DISABLE KEYS */;
INSERT INTO `jabatan` VALUES
(1,'Operator Produksi',1,'2026-09-23 11:50:09'),
(2,'Staff',2,'2026-09-23 11:50:09'),
(3,'Leader',3,'2026-09-23 11:50:09'),
(4,'Supervisor (Spv)',4,'2026-09-23 11:50:09'),
(5,'Department Manager',6,'2026-09-23 11:50:09'),
(6,'HRD',7,'2026-09-23 11:50:09'),
(7,'Super Admin',8,'2026-09-23 11:50:09');
/*!40000 ALTER TABLE `jabatan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jenis_cuti`
--

DROP TABLE IF EXISTS `jenis_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_cuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_cuti` varchar(100) NOT NULL,
  `kategori` varchar(50) NOT NULL DEFAULT 'Cuti Reguler',
  `kode` varchar(20) NOT NULL,
  `durasi_maksimal` varchar(50) DEFAULT '1 Hari',
  `deskripsi` text DEFAULT NULL,
  `potong_kuota` tinyint(1) NOT NULL DEFAULT 0,
  `potong_gaji` tinyint(1) NOT NULL DEFAULT 0,
  `max_hari_default` int(11) NOT NULL DEFAULT 12,
  `butuh_lampiran` tinyint(1) NOT NULL DEFAULT 0,
  `wewenang_approval` varchar(100) DEFAULT 'Leader & Spv Dept',
  `catatan_khusus` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jenis_cuti`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jenis_cuti` WRITE;
/*!40000 ALTER TABLE `jenis_cuti` DISABLE KEYS */;
INSERT INTO `jenis_cuti` VALUES
(1,'Cuti Tahunan Penuh','Cuti Reguler','CT','Sesuai Sisa',NULL,1,0,12,0,'Leader -> Mgr -> HRD','Kuota tahunan reguler','2026-09-29 08:06:41'),
(2,'Cuti Setengah Hari (Pagi / Siang)','Cuti Reguler','CT-HALF','0.5 Hari (4 Jam)',NULL,1,0,1,0,'Leader & Spv Dept','4 jam = 1/2 hari','2026-09-29 08:06:41'),
(3,'Izin Pulang Cepat (PC) - Pribadi','Jam-Jaman','PC-PRI','Sisa Jam Shift',NULL,0,1,1,0,'Leader & Spv Dept','4 jam = 1/2 hari','2026-09-29 08:06:41'),
(4,'Izin Pulang Cepat (PC) - Sakit Klinik','Jam-Jaman','PC-SKT','Sisa Jam Shift',NULL,0,1,1,1,'Dokter Klinik & Leader','4 jam = 1/2 hari','2026-09-29 08:06:41'),
(5,'Izin Keluar Sementara (Kembali Masuk)','Jam-Jaman','IK-TMP','1 - 3 Jam',NULL,0,1,1,0,'Leader & Spv Dept','4 jam = 1/2 hari','2026-09-29 08:06:41'),
(6,'Izin Datang Terlambat','Jam-Jaman','T','1 - 2 Jam',NULL,0,1,1,0,'Leader & Spv Dept','tidak boleh diawal masuk','2026-09-29 08:06:41'),
(7,'Sakit Surat Dokter (SD)','Medis','SD','Sesuai Surat',NULL,0,0,14,1,'Leader & HRD','Surat Keterangan Dokter Resmi','2026-09-29 08:06:41'),
(8,'Sakit Tanpa Surat Dokter (ST)','Medis','ST','Maks 1 Hari',NULL,1,1,1,0,'Leader & Spv Dept','Potong cuti, jika kuota 0 potong gaji','2026-09-29 08:06:41'),
(9,'Cuti Haid (Karyawati, H1 & H2)','Normatif','CH','2 Hari',NULL,0,0,2,1,'Leader & Spv Dept','Wajib Surat Dokter','2026-09-29 08:06:41'),
(10,'Cuti Melahirkan / Bersalin','Normatif','CML','90 Hari (3 Bln)',NULL,0,0,90,1,'Leader -> Mgr -> HRD','Surat Bidan / RS / HPL','2026-09-29 08:06:41'),
(11,'Cuti Keguguran Kandungan','Normatif','CKG','45 Hari (1.5 Bln)',NULL,0,0,45,1,'Leader -> Mgr -> HRD','Surat Dokter Kandungan (Obgyn)','2026-09-29 08:06:41'),
(12,'Pekerja Menikah','Cuti Khusus','CK-NIK','3 Hari',NULL,0,0,3,1,'Leader -> Mgr -> HRD','Surat Nikah/akta Nikah','2026-09-29 08:06:41'),
(13,'Menikahkan Anak Sah','Cuti Khusus','CK-ANK','2 Hari',NULL,0,0,2,1,'Leader -> Mgr -> HRD','Undangan Pernikahan','2026-09-29 08:06:41'),
(14,'Khitanan / Baptis Anak','Cuti Khusus','CK-KHT','2 Hari',NULL,0,0,2,1,'Leader -> Mgr -> HRD','Keterangan Khitan/Baptis','2026-09-29 08:06:41'),
(15,'Istri Melahirkan / Keguguran','Cuti Khusus','CK-IMS','2 Hari',NULL,0,0,2,1,'Leader -> Mgr -> HRD','Surat Keterangan RS/Klinik','2026-09-29 08:06:41'),
(16,'Duka Cita (Keluarga Inti Meninggal)','Cuti Khusus','CK-DK1','2 Hari',NULL,0,0,2,1,'Leader -> Mgr -> HRD','Surat Kematian (Suami/Istri/Anak/Ortu/Mertua)','2026-09-29 08:06:41'),
(17,'Duka Cita (Keluarga Serumah Meninggal)','Cuti Khusus','CK-DK2','1 Hari',NULL,0,0,1,1,'Leader -> Mgr -> HRD','Surat Kematian & Ket. RT','2026-09-29 08:06:41'),
(18,'Ibadah Haji (Pertama Kali)','Cuti Khusus','CK-HAJ','40 Hari',NULL,0,0,40,1,'Leader -> Mgr -> HRD','Porsi Haji Kemenag','2026-09-29 08:06:41'),
(19,'Dispensasi Serikat Pekerja (PUK/Serikat)','Dispensasi','DISP-SP','Sesuai Agenda',NULL,0,0,30,1,'Leader & Spv Dept (CC HRD)','Surat Mandat / Undangan Resmi Serikat','2026-09-29 08:06:41'),
(20,'Tugas Perusahaan / Dinas Luar','Dispensasi','DISP-DNS','Sesuai Tugas',NULL,0,0,30,1,'Manager Dept','Surat Perintah Perjalanan Dinas (SPPD)','2026-09-29 08:06:41'),
(21,'Bencana Alam / Force Majeure','Dispensasi','DISP-BNC','1 - 2 Hari',NULL,0,0,2,1,'Leader & Spv Dept','Foto lokasi & Surat RT/RW (Banjir/Kebakaran)','2026-09-29 08:06:41'),
(22,'Pendidikan / Ujian Akhir / Wisuda','Izin Khusus','DISP-STD','1 - 2 Hari',NULL,0,0,2,1,'Leader & Spv Dept','masuknya ke ijin','2026-09-29 08:06:41'),
(23,'Panggilan Negara / Pengadilan / Donor','Cuti Khusus','DISP-NGR','Sesuai Acara',NULL,0,0,7,1,'Leader & Spv Dept','jika casenya wajib militer dan pemilu masuknya ka kecuti khusus','2026-09-29 08:06:41'),
(24,'Izin Tidak Masuk (Keperluan Pribadi)','Unpaid','IJN','Sesuai Pengajuan',NULL,0,1,7,0,'Leader & Spv Dept','Form Izin Pribadi','2026-09-29 08:06:41'),
(25,'Mangkir / Alpha (Tanpa Kabar)','Pelanggaran','ALPHA','1 Hari (Per Shift)',NULL,0,1,1,0,'Full Leader (HRD Info Only)','Diinput langsung oleh Leader (Bisa direvisi)','2026-09-29 08:06:41');
/*!40000 ALTER TABLE `jenis_cuti` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `karyawan`
--

DROP TABLE IF EXISTS `karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `karyawan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'operator',
  `departemen_id` int(11) NOT NULL,
  `jabatan_id` int(11) NOT NULL,
  `current_shift` enum('Shift 1','Shift 2 (Maju)') NOT NULL DEFAULT 'Shift 1',
  `tanggal_masuk` date NOT NULL,
  `kuota_cuti` decimal(4,1) NOT NULL DEFAULT 12.0,
  `cuti_terpakai` decimal(4,1) NOT NULL DEFAULT 0.0,
  `sisa_cuti` decimal(4,1) NOT NULL DEFAULT 12.0,
  `jenis_kelamin` enum('Laki-laki','Perempuan') NOT NULL DEFAULT 'Laki-laki',
  `agama` varchar(50) NOT NULL DEFAULT 'Islam',
  `status_pernikahan` enum('Belum Menikah','Menikah','Duda','Janda') NOT NULL DEFAULT 'Belum Menikah',
  `no_hp` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status_aktif` enum('Aktif','Non-Aktif') NOT NULL DEFAULT 'Aktif',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `karyawan` WRITE;
/*!40000 ALTER TABLE `karyawan` DISABLE KEYS */;
INSERT INTO `karyawan` VALUES
(1,'ADM-001','Master Super Admin','admin@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','superadmin',8,7,'Shift 1','2020-01-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081100000001','Kantor Pusat PT. Nakakin Indonesia',NULL,'Aktif','2026-09-23 11:50:10','2026-09-23 11:50:10'),
(2,'NAK-001','Hermawan (HRD)','hermawan@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','hrd',8,6,'Shift 1','2020-03-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081234567890','Kawasan Industri KIIC, Karawang Barat',NULL,'Aktif','2026-09-23 11:50:10','2026-09-23 11:50:10'),
(3,'NAK-002','Bambang Setyo (GA)','ga@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','staff',7,2,'Shift 1','2021-02-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081234567891','Perum Resinda, Karawang Barat',NULL,'Aktif','2026-09-23 11:50:10','2026-09-23 11:50:10'),
(4,'MGR-001','Ir. Hendra Wijaya (Manager)','manager@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','manager',5,5,'Shift 1','2018-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','081399990001','Grand Taruma, Karawang Barat',NULL,'Aktif','2026-09-23 11:50:10','2026-09-23 11:50:10'),
(5,'SPV-ACC','Spv Accounting','spv.acc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',1,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000001','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:10','2026-09-23 11:50:10'),
(6,'LDR-ACC','Leader Accounting','ldr.acc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',1,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000001','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:11','2026-09-23 11:50:11'),
(7,'OP1-ACC','Operator 1 Accounting','op1.acc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',1,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000001','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:11','2026-09-23 11:50:11'),
(8,'OP2-ACC','Operator 2 Accounting','op2.acc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',1,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000001','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:11','2026-09-23 11:50:11'),
(9,'SPV-CAST','Spv Casting','spv.cast@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',2,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000002','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:11','2026-09-23 11:50:11'),
(10,'LDR-CAST','Leader Casting','ldr.cast@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',2,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000002','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:11','2026-09-23 11:50:11'),
(11,'OP1-CAST','Operator 1 Casting','op1.cast@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',2,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000002','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:12','2026-09-23 11:50:12'),
(12,'OP2-CAST','Operator 2 Casting','op2.cast@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',2,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000002','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:12','2026-09-23 11:50:12'),
(13,'SPV-CORE','Spv Core','spv.core@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',3,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000003','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:12','2026-09-23 11:50:12'),
(14,'LDR-CORE','Leader Core','ldr.core@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',3,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000003','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:12','2026-09-23 11:50:12'),
(15,'OP1-CORE','Operator 1 Core','op1.core@nakakin.co.id','$2y$12$0WfPFjakI3rOsPpez4QcbuHNWt/5pSsKKM1TiyP26o/0DTcXHNkDu','operator',3,1,'Shift 1','2023-06-01',12.0,2.0,10.0,'Laki-laki','Islam','Menikah','08160000003','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:12','2026-09-24 12:14:32'),
(16,'OP2-CORE','Operator 2 Core','op2.core@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',3,1,'Shift 1','2024-01-15',12.0,2.0,10.0,'Perempuan','Islam','Menikah','08170000003','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:12','2026-09-23 11:50:13'),
(17,'SPV-DC','Spv Diecasting','spv.dc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',4,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000004','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(18,'LDR-DC','Leader Diecasting','ldr.dc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',4,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000004','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(19,'OP1-DC','Operator 1 Diecasting','op1.dc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',4,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000004','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(20,'OP2-DC','Operator 2 Diecasting','op2.dc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',4,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000004','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(21,'SPV-ENG','Spv Engineering','spv.eng@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',5,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000005','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(22,'LDR-ENG','Leader Engineering','ldr.eng@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',5,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000005','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(23,'OP1-ENG','Operator 1 Engineering','op1.eng@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',5,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000005','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(24,'OP2-ENG','Operator 2 Engineering','op2.eng@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',5,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000005','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(25,'SPV-FET','Spv Fettling','spv.fet@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',6,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000006','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(26,'LDR-FET','Leader Fettling','ldr.fet@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',6,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000006','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(27,'OP1-FET','Operator 1 Fettling','op1.fet@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',6,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000006','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(28,'OP2-FET','Operator 2 Fettling','op2.fet@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',6,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000006','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(29,'SPV-GA','Spv GA','spv.ga@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',7,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000007','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(30,'LDR-GA','Leader GA','ldr.ga@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',7,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000007','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(31,'OP1-GA','Operator 1 GA','op1.ga@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',7,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000007','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(32,'OP2-GA','Operator 2 GA','op2.ga@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',7,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000007','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(33,'SPV-HRD','Spv HRD','spv.hrd@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',8,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000008','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(34,'LDR-HRD','Leader HRD','ldr.hrd@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',8,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000008','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(35,'OP1-HRD','Operator 1 HRD','op1.hrd@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',8,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000008','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(36,'OP2-HRD','Operator 2 HRD','op2.hrd@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',8,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000008','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(37,'SPV-MC','Spv Machining','spv.mc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',9,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000009','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(38,'LDR-MC','Leader Machining','ldr.mc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',9,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000009','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(39,'OP1-MC','Operator 1 Machining','op1.mc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',9,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000009','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(40,'OP2-MC','Operator 2 Machining','op2.mc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',9,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000009','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(41,'SPV-MKT','Spv Marketing','spv.mkt@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',10,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000010','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(42,'LDR-MKT','Leader Marketing','ldr.mkt@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',10,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000010','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(43,'OP1-MKT','Operator 1 Marketing','op1.mkt@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',10,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000010','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(44,'OP2-MKT','Operator 2 Marketing','op2.mkt@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',10,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000010','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(45,'SPV-MAINT','Spv Maintenance','spv.maint@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',11,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000011','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(46,'LDR-MAINT','Leader Maintenance','ldr.maint@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',11,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000011','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(47,'OP1-MAINT','Operator 1 Maintenance','op1.maint@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',11,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000011','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(48,'OP2-MAINT','Operator 2 Maintenance','op2.maint@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',11,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000011','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(49,'SPV-PPIC','Spv PPIC','spv.ppic@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',12,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000012','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(50,'LDR-PPIC','Leader PPIC','ldr.ppic@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',12,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000012','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(51,'OP1-PPIC','Operator 1 PPIC','op1.ppic@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',12,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000012','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(52,'OP2-PPIC','Operator 2 PPIC','op2.ppic@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',12,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000012','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(53,'SPV-PURCH','Spv Purchasing','spv.purch@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',13,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000013','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(54,'LDR-PURCH','Leader Purchasing','ldr.purch@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',13,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000013','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(55,'OP1-PURCH','Operator 1 Purchasing','op1.purch@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',13,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000013','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(56,'OP2-PURCH','Operator 2 Purchasing','op2.purch@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',13,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000013','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(57,'SPV-QC','Spv QC','spv.qc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',14,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000014','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(58,'LDR-QC','Leader QC','ldr.qc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',14,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000014','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(59,'OP1-QC','Operator 1 QC','op1.qc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',14,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000014','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(60,'OP2-QC','Operator 2 QC','op2.qc@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',14,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000014','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(61,'SPV-QCL','Spv QC Line','spv.qcl@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','supervisor',15,4,'Shift 1','2021-01-10',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08140000015','Telukjambe Timur, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(62,'LDR-QCL','Leader QC Line','ldr.qcl@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','leader',15,3,'Shift 1','2022-03-15',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08150000015','Klari, Karawang Timur',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(63,'OP1-QCL','Operator 1 QC Line','op1.qcl@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',15,1,'Shift 1','2023-06-01',12.0,0.0,12.0,'Laki-laki','Islam','Menikah','08160000015','Kosambi, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13'),
(64,'OP2-QCL','Operator 2 QC Line','op2.qcl@nakakin.co.id','$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du','operator',15,1,'Shift 1','2024-01-15',12.0,0.0,12.0,'Perempuan','Islam','Menikah','08170000015','Cikampek, Karawang',NULL,'Aktif','2026-09-23 11:50:13','2026-09-23 11:50:13');
/*!40000 ALTER TABLE `karyawan` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pengajuan_cuti`
--

DROP TABLE IF EXISTS `pengajuan_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengajuan_cuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(60) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type_id` int(11) NOT NULL,
  `shift` enum('Shift 1','Shift 2 (Maju)','Non-Shift') NOT NULL DEFAULT 'Shift 1',
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `total_hari` decimal(4,1) NOT NULL DEFAULT 1.0,
  `alasan` text NOT NULL,
  `alamat_selama_cuti` text DEFAULT NULL,
  `kontak_darurat` varchar(50) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL COMMENT 'ID User Atasan yang menyetujui/menolak',
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `catatan_atasan` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_step` varchar(30) NOT NULL DEFAULT 'pending_spv' COMMENT 'pending_spv, pending_manager, pending_hrd, approved, rejected, cancelled',
  `spv_id` int(11) DEFAULT NULL,
  `spv_at` datetime DEFAULT NULL,
  `spv_notes` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `manager_at` datetime DEFAULT NULL,
  `manager_notes` text DEFAULT NULL,
  `hrd_id` int(11) DEFAULT NULL,
  `hrd_at` datetime DEFAULT NULL,
  `hrd_notes` text DEFAULT NULL,
  `notif_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_surat` (`nomor_surat`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `jenis_cuti` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_cuti_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengajuan_cuti`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pengajuan_cuti` WRITE;
/*!40000 ALTER TABLE `pengajuan_cuti` DISABLE KEYS */;
INSERT INTO `pengajuan_cuti` VALUES
(1,'CUTI/NAK/2026/09/001',7,3,'Shift 1','2026-09-24','2026-09-25',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'rejected',2,'2026-09-23 12:34:51','uuh','','2026-09-23 11:50:13','2026-09-23 12:34:51','rejected',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(2,'CUTI/NAK/2026/09/002',11,3,'Shift 1','2026-09-25','2026-09-26',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(3,'CUTI/NAK/2026/09/003',15,3,'Shift 1','2026-09-26','2026-09-27',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'approved',2,'2026-09-24 12:12:20',NULL,'','2026-09-23 11:50:13','2026-09-24 14:39:54','approved',14,'2026-09-24 12:11:39','',4,'2026-09-24 12:12:02','',2,'2026-09-24 12:12:20','',1),
(4,'CUTI/NAK/2026/09/004',19,3,'Shift 1','2026-09-27','2026-09-28',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(5,'CUTI/NAK/2026/09/005',23,3,'Shift 1','2026-09-28','2026-09-29',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(6,'CUTI/NAK/2026/09/006',27,3,'Shift 1','2026-09-29','2026-09-30',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(7,'CUTI/NAK/2026/09/007',31,3,'Shift 1','2026-09-30','2026-10-01',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(8,'CUTI/NAK/2026/09/008',35,3,'Shift 1','2026-10-01','2026-10-02',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(9,'CUTI/NAK/2026/09/009',39,3,'Shift 1','2026-10-02','2026-10-03',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(10,'CUTI/NAK/2026/09/010',43,3,'Shift 1','2026-10-03','2026-10-04',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(11,'CUTI/NAK/2026/09/011',47,3,'Shift 1','2026-10-04','2026-10-05',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(12,'CUTI/NAK/2026/09/012',51,3,'Shift 1','2026-10-05','2026-10-06',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(13,'CUTI/NAK/2026/09/013',55,3,'Shift 1','2026-10-06','2026-10-07',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(14,'CUTI/NAK/2026/09/014',59,3,'Shift 1','2026-10-07','2026-10-08',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(15,'CUTI/NAK/2026/09/015',63,3,'Shift 1','2026-10-08','2026-10-09',NULL,NULL,2.0,'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.','Dusun Sukamaju RT 02/04, Karawang','081299887766 (Keluarga)',NULL,'pending',NULL,NULL,NULL,NULL,'2026-09-23 11:50:13','2026-09-23 11:50:13','pending_spv',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0),
(16,'CUTI/NAK/2026/09/016',16,3,'Shift 1','2026-09-01','2026-09-02',NULL,NULL,2.0,'Acara keluarga dan mudik awal bulan.','Karawang','0812345678',NULL,'approved',2,'2026-09-01 10:00:00',NULL,NULL,'2026-08-30 08:00:00','2026-09-01 10:00:00','approved',13,'2026-08-31 09:00:00','Disetujui oleh Spv',4,'2026-08-31 14:00:00','Disetujui oleh Manager',2,'2026-09-01 10:00:00','Disetujui HRD dan kuota dipotong',1),
(17,'CUTI/NAK/2026/09/017',15,3,'Shift 1','2026-10-01','2026-10-02',NULL,NULL,2.0,'Males Masuk','','',NULL,'cancelled',NULL,NULL,NULL,NULL,'2026-09-24 12:16:15','2026-09-24 14:39:54','pending_manager',14,'2026-09-24 12:16:41','',NULL,NULL,NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `pengajuan_cuti` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `pengaturan_aplikasi`
--

DROP TABLE IF EXISTS `pengaturan_aplikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengaturan_aplikasi` (
  `id` int(11) NOT NULL,
  `nama_aplikasi` varchar(150) NOT NULL DEFAULT 'Sistem Informasi Cuti Karyawan',
  `singkatan_aplikasi` varchar(50) NOT NULL DEFAULT 'E-Cuti',
  `tagline` varchar(255) NOT NULL DEFAULT 'Precision Machinery, Pumps & Tooling Manufacturing',
  `nama_perusahaan` varchar(150) NOT NULL DEFAULT 'PT. Nakakin Indonesia',
  `singkatan_perusahaan` varchar(50) NOT NULL DEFAULT 'NAKAKIN',
  `sub_singkatan_perusahaan` varchar(50) NOT NULL DEFAULT 'INDONESIA',
  `alamat_perusahaan` text DEFAULT NULL,
  `telepon` varchar(50) DEFAULT '(0267) 845-1234',
  `email_perusahaan` varchar(100) DEFAULT 'hrd@nakakin.co.id',
  `website` varchar(100) DEFAULT 'www.nakakin.co.id',
  `logo` varchar(255) DEFAULT 'Nakakin.png',
  `favicon` varchar(255) DEFAULT 'Nakakin.png',
  `prefix_nomor_surat` varchar(30) NOT NULL DEFAULT 'CUTI/NAK',
  `default_kuota_cuti` int(11) NOT NULL DEFAULT 12,
  `nama_kepala_hrd` varchar(150) NOT NULL DEFAULT 'Siti Rahmawati, S.Psi',
  `jabatan_kepala_hrd` varchar(100) NOT NULL DEFAULT 'HRD & GA Manager',
  `lokasi_surat` varchar(100) NOT NULL DEFAULT 'Karawang',
  `footer_text` varchar(255) NOT NULL DEFAULT 'Sistem Informasi Manajemen Cuti Karyawan',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengaturan_aplikasi`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `pengaturan_aplikasi` WRITE;
/*!40000 ALTER TABLE `pengaturan_aplikasi` DISABLE KEYS */;
INSERT INTO `pengaturan_aplikasi` VALUES
(1,'Nakakin Mobile','Nakakin Mobile','Precision Machinery, Pumps & Tooling Manufacturing','PT. NAKAKIN INDONESIA','NAKAKIn','INDONESIA','EJIP INDUSTRIAL PARK PLOT 5L-4 CIKARANG SELATAN, BEKASI 17550','(0267) 845-1234','hrd@nakakin.co.id','www.nakakin.co.id','Nakakin.png','Nakakin.png','CUTI/NAK',10,'Hermawan','HRD GA Manager','Bekasi','Sistem Informasi Manajemen Cuti Karyawan','2026-09-29 08:06:41');
/*!40000 ALTER TABLE `pengaturan_aplikasi` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `riwayat_kuota_cuti`
--

DROP TABLE IF EXISTS `riwayat_kuota_cuti`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_kuota_cuti` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `kuota_sebelum` int(11) NOT NULL,
  `perubahan` int(11) NOT NULL,
  `kuota_sesudah` int(11) NOT NULL,
  `tipe` enum('alokasi_tahunan','penyesuaian_hrd','bonus_lembur','potong_cuti') NOT NULL DEFAULT 'alokasi_tahunan',
  `keterangan` text NOT NULL,
  `created_by` int(11) DEFAULT NULL COMMENT 'ID User HRD/Admin yang memproses perubahan',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `riwayat_kuota_cuti_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `riwayat_kuota_cuti_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_kuota_cuti`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `riwayat_kuota_cuti` WRITE;
/*!40000 ALTER TABLE `riwayat_kuota_cuti` DISABLE KEYS */;
INSERT INTO `riwayat_kuota_cuti` VALUES
(1,16,12,-2,10,'potong_cuti','Pemotongan cuti disetujui (CUTI/NAK/2026/09/016) oleh HRD',2,'2026-09-01 10:00:00'),
(2,15,12,-2,10,'potong_cuti','Pengajuan Cuti Tahunan (Reguler HRD) (CUTI/NAK/2026/09/003) Disetujui HRD (2 hari)',2,'2026-09-24 12:12:20');
/*!40000 ALTER TABLE `riwayat_kuota_cuti` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-09-29  8:07:20
