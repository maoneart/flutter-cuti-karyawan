-- ========================================================
-- Database: db_cuti_nakakin
-- PT. Nakakin Indonesia Leave Management System
-- Complete Production Schema & Seeded Master Data
-- Generated: 2026-09-23 07:59:13
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_cuti_nakakin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_cuti_nakakin`;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for `departemen`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `departemen`;
CREATE TABLE `departemen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_dept` varchar(20) NOT NULL,
  `nama_dept` varchar(100) NOT NULL,
  `deskripsi` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_dept` (`kode_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `departemen`

INSERT INTO `departemen` (`id`, `kode_dept`, `nama_dept`, `deskripsi`, `created_at`) VALUES
('1', 'ACC', 'Accounting', 'Accounting & Finance Department', '2026-09-23 11:50:09'),
('2', 'CAST', 'Casting', 'Casting & Foundry Department', '2026-09-23 11:50:09'),
('3', 'CORE', 'Core', 'Core Production Department', '2026-09-23 11:50:09'),
('4', 'DC', 'Diecasting', 'Die Casting Production Department', '2026-09-23 11:50:09'),
('5', 'ENG', 'Engineering', 'Engineering & Technical Department', '2026-09-23 11:50:09'),
('6', 'FET', 'Fettling', 'Fettling & Finishing Department', '2026-09-23 11:50:09'),
('7', 'GA', 'GA', 'General Affairs Department', '2026-09-23 11:50:09'),
('8', 'HRD', 'HRD', 'Human Resources Department', '2026-09-23 11:50:09'),
('9', 'MC', 'Machining', 'Machining Production Department', '2026-09-23 11:50:09'),
('10', 'MKT', 'Marketing', 'Sales & Marketing Department', '2026-09-23 11:50:09'),
('11', 'MAINT', 'Maintenance', 'Machine & Utility Maintenance Department', '2026-09-23 11:50:09'),
('12', 'PPIC', 'PPIC', 'Production Planning & Inventory Control', '2026-09-23 11:50:09'),
('13', 'PURCH', 'Purchasing', 'Purchasing & Procurement Department', '2026-09-23 11:50:09'),
('14', 'QC', 'QC', 'Quality Control Department', '2026-09-23 11:50:09'),
('15', 'QCL', 'QC Line', 'Quality Control Line Inspection Department', '2026-09-23 11:50:09');

-- --------------------------------------------------------
-- Table structure for `jabatan`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `jabatan`;
CREATE TABLE `jabatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  `level_hierarki` int NOT NULL DEFAULT '1' COMMENT '1: Operator, 2: Staff, 3: Leader, 4: Supervisor, 5: Assistant Manager, 6: Manager, 7: HRD Admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `jabatan`

INSERT INTO `jabatan` (`id`, `nama_jabatan`, `level_hierarki`, `created_at`) VALUES
('1', 'Operator Produksi', '1', '2026-09-23 11:50:09'),
('2', 'Staff', '2', '2026-09-23 11:50:09'),
('3', 'Leader', '3', '2026-09-23 11:50:09'),
('4', 'Supervisor (Spv)', '4', '2026-09-23 11:50:09'),
('5', 'Department Manager', '6', '2026-09-23 11:50:09'),
('6', 'HRD', '7', '2026-09-23 11:50:09'),
('7', 'Super Admin', '8', '2026-09-23 11:50:09');

-- --------------------------------------------------------
-- Table structure for `jenis_cuti`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `jenis_cuti`;
CREATE TABLE `jenis_cuti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_cuti` varchar(100) NOT NULL,
  `kode` varchar(20) NOT NULL,
  `deskripsi` text,
  `potong_kuota` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Memotong jatah cuti tahunan, 0 = Tidak potong jatah cuti tahunan',
  `max_hari_default` int NOT NULL DEFAULT '12',
  `butuh_lampiran` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for `jenis_cuti`

INSERT INTO `jenis_cuti` (`id`, `nama_cuti`, `kode`, `deskripsi`, `potong_kuota`, `max_hari_default`, `butuh_lampiran`, `created_at`) VALUES
('1', 'Sakit Surat Dokter (Tidak Potong Gaji)', 'SSD', 'Izin sakit resmi dengan melampirkan surat keterangan dokter.', '0', '14', '1', '2026-09-23 11:50:09'),
('2', 'Sakit Tanpa Surat Dokter (Potong Jatah/Gaji)', 'STSD', 'Izin sakit tanpa surat dokter.', '1', '3', '0', '2026-09-23 11:50:09'),
('3', 'Cuti Tahunan (Reguler HRD)', 'CT', 'Hak cuti tahunan yang dialokasikan oleh HRD.', '1', '12', '0', '2026-09-23 11:50:09'),
('4', 'Cuti Haid', 'CH', 'Cuti haid/menstruasi bagi karyawati.', '1', '2', '0', '2026-09-23 11:50:09'),
('5', 'Cuti Melahirkan / Bersalin (3 Bulan)', 'CML', 'Hak cuti bersalin bagi karyawati (3 bulan / 90 hari).', '0', '90', '1', '2026-09-23 11:50:09'),
('6', 'Cuti Khusus (Keluarga Meninggal)', 'CKH', 'Cuti duka cita karena anggota keluarga inti meninggal.', '0', '2', '0', '2026-09-23 11:50:09'),
('7', 'Ijin Tidak Masuk (Potong Gaji)', 'IJN', 'Izin tidak masuk kerja untuk keperluan mendesak.', '0', '7', '0', '2026-09-23 11:50:10');

-- --------------------------------------------------------
-- Table structure for `karyawan`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `karyawan`;
CREATE TABLE `karyawan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'operator',
  `departemen_id` int NOT NULL,
  `jabatan_id` int NOT NULL,
  `tanggal_masuk` date NOT NULL,
  `kuota_cuti` int NOT NULL DEFAULT '12',
  `cuti_terpakai` int NOT NULL DEFAULT '0',
  `sisa_cuti` int NOT NULL DEFAULT '12',
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

-- Dumping data for `karyawan`

INSERT INTO `karyawan` (`id`, `nik`, `nama_lengkap`, `email`, `password`, `role`, `departemen_id`, `jabatan_id`, `tanggal_masuk`, `kuota_cuti`, `cuti_terpakai`, `sisa_cuti`, `jenis_kelamin`, `agama`, `status_pernikahan`, `no_hp`, `alamat`, `foto`, `status_aktif`, `created_at`, `updated_at`) VALUES
('1', 'ADM-001', 'Master Super Admin', 'admin@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'superadmin', '8', '7', '2020-01-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '081100000001', 'Kantor Pusat PT. Nakakin Indonesia', NULL, 'Aktif', '2026-09-23 11:50:10', '2026-09-23 11:50:10'),
('2', 'NAK-001', 'Hermawan (HRD)', 'hermawan@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'hrd', '8', '6', '2020-03-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '081234567890', 'Kawasan Industri KIIC, Karawang Barat', NULL, 'Aktif', '2026-09-23 11:50:10', '2026-09-23 11:50:10'),
('3', 'NAK-002', 'Bambang Setyo (GA)', 'ga@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'staff', '7', '2', '2021-02-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '081234567891', 'Perum Resinda, Karawang Barat', NULL, 'Aktif', '2026-09-23 11:50:10', '2026-09-23 11:50:10'),
('4', 'MGR-001', 'Ir. Hendra Wijaya (Manager)', 'manager@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'manager', '5', '5', '2018-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '081399990001', 'Grand Taruma, Karawang Barat', NULL, 'Aktif', '2026-09-23 11:50:10', '2026-09-23 11:50:10'),
('5', 'SPV-ACC', 'Spv Accounting', 'spv.acc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '1', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000001', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:10', '2026-09-23 11:50:10'),
('6', 'LDR-ACC', 'Leader Accounting', 'ldr.acc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '1', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000001', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:11', '2026-09-23 11:50:11'),
('7', 'OP1-ACC', 'Operator 1 Accounting', 'op1.acc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '1', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000001', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:11', '2026-09-23 11:50:11'),
('8', 'OP2-ACC', 'Operator 2 Accounting', 'op2.acc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '1', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000001', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:11', '2026-09-23 11:50:11'),
('9', 'SPV-CAST', 'Spv Casting', 'spv.cast@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '2', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000002', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:11', '2026-09-23 11:50:11'),
('10', 'LDR-CAST', 'Leader Casting', 'ldr.cast@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '2', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000002', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:11', '2026-09-23 11:50:11'),
('11', 'OP1-CAST', 'Operator 1 Casting', 'op1.cast@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '2', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000002', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:12', '2026-09-23 11:50:12'),
('12', 'OP2-CAST', 'Operator 2 Casting', 'op2.cast@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '2', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000002', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:12', '2026-09-23 11:50:12'),
('13', 'SPV-CORE', 'Spv Core', 'spv.core@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '3', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000003', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:12', '2026-09-23 11:50:12'),
('14', 'LDR-CORE', 'Leader Core', 'ldr.core@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '3', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000003', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:12', '2026-09-23 11:50:12'),
('15', 'OP1-CORE', 'Operator 1 Core', 'op1.core@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '3', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000003', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:12', '2026-09-23 11:50:12'),
('16', 'OP2-CORE', 'Operator 2 Core', 'op2.core@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '3', '1', '2024-01-15', '12', '2', '10', 'Perempuan', 'Islam', 'Menikah', '08170000003', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:12', '2026-09-23 11:50:13'),
('17', 'SPV-DC', 'Spv Diecasting', 'spv.dc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '4', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000004', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('18', 'LDR-DC', 'Leader Diecasting', 'ldr.dc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '4', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000004', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('19', 'OP1-DC', 'Operator 1 Diecasting', 'op1.dc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '4', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000004', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('20', 'OP2-DC', 'Operator 2 Diecasting', 'op2.dc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '4', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000004', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('21', 'SPV-ENG', 'Spv Engineering', 'spv.eng@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '5', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000005', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('22', 'LDR-ENG', 'Leader Engineering', 'ldr.eng@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '5', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000005', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('23', 'OP1-ENG', 'Operator 1 Engineering', 'op1.eng@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '5', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000005', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('24', 'OP2-ENG', 'Operator 2 Engineering', 'op2.eng@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '5', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000005', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('25', 'SPV-FET', 'Spv Fettling', 'spv.fet@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '6', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000006', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('26', 'LDR-FET', 'Leader Fettling', 'ldr.fet@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '6', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000006', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('27', 'OP1-FET', 'Operator 1 Fettling', 'op1.fet@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '6', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000006', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('28', 'OP2-FET', 'Operator 2 Fettling', 'op2.fet@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '6', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000006', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('29', 'SPV-GA', 'Spv GA', 'spv.ga@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '7', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000007', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('30', 'LDR-GA', 'Leader GA', 'ldr.ga@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '7', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000007', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('31', 'OP1-GA', 'Operator 1 GA', 'op1.ga@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '7', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000007', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('32', 'OP2-GA', 'Operator 2 GA', 'op2.ga@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '7', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000007', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('33', 'SPV-HRD', 'Spv HRD', 'spv.hrd@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '8', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000008', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('34', 'LDR-HRD', 'Leader HRD', 'ldr.hrd@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '8', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000008', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('35', 'OP1-HRD', 'Operator 1 HRD', 'op1.hrd@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '8', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000008', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('36', 'OP2-HRD', 'Operator 2 HRD', 'op2.hrd@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '8', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000008', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('37', 'SPV-MC', 'Spv Machining', 'spv.mc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '9', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000009', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('38', 'LDR-MC', 'Leader Machining', 'ldr.mc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '9', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000009', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('39', 'OP1-MC', 'Operator 1 Machining', 'op1.mc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '9', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000009', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('40', 'OP2-MC', 'Operator 2 Machining', 'op2.mc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '9', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000009', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('41', 'SPV-MKT', 'Spv Marketing', 'spv.mkt@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '10', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000010', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('42', 'LDR-MKT', 'Leader Marketing', 'ldr.mkt@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '10', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000010', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('43', 'OP1-MKT', 'Operator 1 Marketing', 'op1.mkt@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '10', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000010', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('44', 'OP2-MKT', 'Operator 2 Marketing', 'op2.mkt@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '10', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000010', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('45', 'SPV-MAINT', 'Spv Maintenance', 'spv.maint@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '11', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000011', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('46', 'LDR-MAINT', 'Leader Maintenance', 'ldr.maint@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '11', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000011', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('47', 'OP1-MAINT', 'Operator 1 Maintenance', 'op1.maint@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '11', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000011', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('48', 'OP2-MAINT', 'Operator 2 Maintenance', 'op2.maint@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '11', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000011', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('49', 'SPV-PPIC', 'Spv PPIC', 'spv.ppic@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '12', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000012', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('50', 'LDR-PPIC', 'Leader PPIC', 'ldr.ppic@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '12', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000012', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('51', 'OP1-PPIC', 'Operator 1 PPIC', 'op1.ppic@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '12', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000012', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('52', 'OP2-PPIC', 'Operator 2 PPIC', 'op2.ppic@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '12', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000012', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('53', 'SPV-PURCH', 'Spv Purchasing', 'spv.purch@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '13', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000013', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('54', 'LDR-PURCH', 'Leader Purchasing', 'ldr.purch@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '13', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000013', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('55', 'OP1-PURCH', 'Operator 1 Purchasing', 'op1.purch@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '13', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000013', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('56', 'OP2-PURCH', 'Operator 2 Purchasing', 'op2.purch@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '13', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000013', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('57', 'SPV-QC', 'Spv QC', 'spv.qc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '14', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000014', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('58', 'LDR-QC', 'Leader QC', 'ldr.qc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '14', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000014', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('59', 'OP1-QC', 'Operator 1 QC', 'op1.qc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '14', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000014', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('60', 'OP2-QC', 'Operator 2 QC', 'op2.qc@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '14', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000014', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('61', 'SPV-QCL', 'Spv QC Line', 'spv.qcl@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'supervisor', '15', '4', '2021-01-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08140000015', 'Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('62', 'LDR-QCL', 'Leader QC Line', 'ldr.qcl@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'leader', '15', '3', '2022-03-15', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08150000015', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('63', 'OP1-QCL', 'Operator 1 QC Line', 'op1.qcl@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '15', '1', '2023-06-01', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '08160000015', 'Kosambi, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13'),
('64', 'OP2-QCL', 'Operator 2 QC Line', 'op2.qcl@nakakin.co.id', '$2y$10$0LpiNefCcpbSzBBjUsa65eHSx9LrWK3twmdsVjKwndsJZOfZAA/du', 'operator', '15', '1', '2024-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Menikah', '08170000015', 'Cikampek, Karawang', NULL, 'Aktif', '2026-09-23 11:50:13', '2026-09-23 11:50:13');

-- --------------------------------------------------------
-- Table structure for `pengajuan_cuti`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pengajuan_cuti`;
CREATE TABLE `pengajuan_cuti` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(60) NOT NULL,
  `employee_id` int NOT NULL,
  `leave_type_id` int NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `total_hari` int NOT NULL,
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

-- Dumping data for `pengajuan_cuti`

INSERT INTO `pengajuan_cuti` (`id`, `nomor_surat`, `employee_id`, `leave_type_id`, `tanggal_mulai`, `tanggal_selesai`, `total_hari`, `alasan`, `alamat_selama_cuti`, `kontak_darurat`, `attachment`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `catatan_atasan`, `created_at`, `updated_at`, `approval_step`, `spv_id`, `spv_at`, `spv_notes`, `manager_id`, `manager_at`, `manager_notes`, `hrd_id`, `hrd_at`, `hrd_notes`, `notif_read`) VALUES
('1', 'CUTI/NAK/2026/09/001', '7', '3', '2026-09-24', '2026-09-25', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'rejected', '2', '2026-09-23 12:34:51', 'uuh', '', '2026-09-23 11:50:13', '2026-09-23 12:34:51', 'rejected', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('2', 'CUTI/NAK/2026/09/002', '11', '3', '2026-09-25', '2026-09-26', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('3', 'CUTI/NAK/2026/09/003', '15', '3', '2026-09-26', '2026-09-27', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('4', 'CUTI/NAK/2026/09/004', '19', '3', '2026-09-27', '2026-09-28', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('5', 'CUTI/NAK/2026/09/005', '23', '3', '2026-09-28', '2026-09-29', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('6', 'CUTI/NAK/2026/09/006', '27', '3', '2026-09-29', '2026-09-30', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('7', 'CUTI/NAK/2026/09/007', '31', '3', '2026-09-30', '2026-10-01', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('8', 'CUTI/NAK/2026/09/008', '35', '3', '2026-10-01', '2026-10-02', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('9', 'CUTI/NAK/2026/09/009', '39', '3', '2026-10-02', '2026-10-03', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('10', 'CUTI/NAK/2026/09/010', '43', '3', '2026-10-03', '2026-10-04', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('11', 'CUTI/NAK/2026/09/011', '47', '3', '2026-10-04', '2026-10-05', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('12', 'CUTI/NAK/2026/09/012', '51', '3', '2026-10-05', '2026-10-06', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('13', 'CUTI/NAK/2026/09/013', '55', '3', '2026-10-06', '2026-10-07', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('14', 'CUTI/NAK/2026/09/014', '59', '3', '2026-10-07', '2026-10-08', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('15', 'CUTI/NAK/2026/09/015', '63', '3', '2026-10-08', '2026-10-09', '2', 'Keperluan keluarga mendesak dan acara syukuran di kampung halaman.', 'Dusun Sukamaju RT 02/04, Karawang', '081299887766 (Keluarga)', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-23 11:50:13', '2026-09-23 11:50:13', 'pending_spv', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0'),
('16', 'CUTI/NAK/2026/09/016', '16', '3', '2026-09-01', '2026-09-02', '2', 'Acara keluarga dan mudik awal bulan.', 'Karawang', '0812345678', NULL, 'approved', '2', '2026-09-01 10:00:00', NULL, NULL, '2026-08-30 08:00:00', '2026-09-01 10:00:00', 'approved', '13', '2026-08-31 09:00:00', 'Disetujui oleh Spv', '4', '2026-08-31 14:00:00', 'Disetujui oleh Manager', '2', '2026-09-01 10:00:00', 'Disetujui HRD dan kuota dipotong', '1');

-- --------------------------------------------------------
-- Table structure for `riwayat_kuota_cuti`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `riwayat_kuota_cuti`;
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

-- Dumping data for `riwayat_kuota_cuti`

INSERT INTO `riwayat_kuota_cuti` (`id`, `employee_id`, `kuota_sebelum`, `perubahan`, `kuota_sesudah`, `tipe`, `keterangan`, `created_by`, `created_at`) VALUES
('1', '16', '12', '-2', '10', 'potong_cuti', 'Pemotongan cuti disetujui (CUTI/NAK/2026/09/016) oleh HRD', '2', '2026-09-01 10:00:00');

-- --------------------------------------------------------
-- Table structure for `pengaturan_aplikasi`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pengaturan_aplikasi`;
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

-- Dumping data for `pengaturan_aplikasi`

INSERT INTO `pengaturan_aplikasi` (`id`, `nama_aplikasi`, `singkatan_aplikasi`, `tagline`, `nama_perusahaan`, `singkatan_perusahaan`, `sub_singkatan_perusahaan`, `alamat_perusahaan`, `telepon`, `email_perusahaan`, `website`, `logo`, `favicon`, `prefix_nomor_surat`, `default_kuota_cuti`, `nama_kepala_hrd`, `jabatan_kepala_hrd`, `lokasi_surat`, `footer_text`, `updated_at`) VALUES
('1', 'Sistem Informasi Cuti Karyawan', 'FORM CUTI ONLINE', 'Manufacturing', 'PT. NAKAKIN INDONESIA', 'NAKAKIn', 'INDONESIA', 'EJIP INDUSTRIAL PARK PLOT 5L-4 CIKARANG SELATAN, BEKASI 17550', '(0267) 845-1234', 'hrd@nakakin.co.id', 'www.nakakin.co.id', 'Nakakin.png', 'Nakakin.png', 'CUTI/NAK', '10', 'Hermawan', 'HRD GA Manager', 'Bekasi', 'Sistem Informasi Manajemen Cuti Karyawan', '2026-09-23 07:51:18');

SET FOREIGN_KEY_CHECKS = 1;
