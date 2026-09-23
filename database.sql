-- ========================================================
-- Database: db_cuti_nakakin
-- Aplikasi Cuti Karyawan & Papan Kehadiran Live
-- Generated on: 2026-09-18 06:53:25
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_cuti_nakakin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_cuti_nakakin`;

DROP TABLE IF EXISTS `riwayat_kuota_cuti`;
DROP TABLE IF EXISTS `pengajuan_cuti`;
DROP TABLE IF EXISTS `karyawan`;
DROP TABLE IF EXISTS `jenis_cuti`;
DROP TABLE IF EXISTS `jabatan`;
DROP TABLE IF EXISTS `departemen`;
DROP TABLE IF EXISTS `pengaturan_aplikasi`;

-- --------------------------------------------------------
-- Table structure for `departemen`
-- --------------------------------------------------------
CREATE TABLE `departemen` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kode_dept` varchar(20) NOT NULL,
  `nama_dept` varchar(100) NOT NULL,
  `deskripsi` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kode_dept` (`kode_dept`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `departemen`
INSERT INTO `departemen` (`id`, `kode_dept`, `nama_dept`, `deskripsi`, `created_at`) VALUES
('1', 'HRD-GA', 'HRD & General Affairs', 'Human Resources & General Affairs Department', '2026-09-18 13:04:38'),
('2', 'QC', 'Quality Control', 'Quality Control & Inspection Department', '2026-09-18 13:04:38'),
('3', 'PROD-MC', 'Machining', 'Machining & Tooling Production Department', '2026-09-18 13:04:38'),
('4', 'PROD-CAST', 'Casting', 'Casting & Foundry Department', '2026-09-18 13:04:38'),
('5', 'PROD-ASSY', 'Assembly & Produksi', 'Assembly & Final Production Department', '2026-09-18 13:04:38'),
('6', 'MAINT', 'Maintenance', 'Facility & Machine Maintenance Department', '2026-09-18 13:04:38'),
('7', 'PPIC-WHS', 'PPIC & Warehouse', 'Production Planning & Warehouse Inventory', '2026-09-18 13:04:38'),
('8', 'FIN-ACC', 'Finance & Accounting', 'Finance, Tax & Accounting Department', '2026-09-18 13:04:38');

-- --------------------------------------------------------
-- Table structure for `jabatan`
-- --------------------------------------------------------
CREATE TABLE `jabatan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_jabatan` varchar(100) NOT NULL,
  `level_hierarki` int NOT NULL DEFAULT '1' COMMENT '1: Operator, 2: Staff, 3: Leader, 4: Supervisor, 5: Assistant Manager, 6: Manager, 7: HRD Admin',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `jabatan`
INSERT INTO `jabatan` (`id`, `nama_jabatan`, `level_hierarki`, `created_at`) VALUES
('1', 'Operator Produksi', '1', '2026-09-18 13:04:38'),
('2', 'Staff Administrasi / Teknis', '2', '2026-09-18 13:04:38'),
('3', 'Leader (Group Leader)', '3', '2026-09-18 13:04:38'),
('4', 'Supervisor (Spv)', '4', '2026-09-18 13:04:38'),
('5', 'Assistant Manager (Asmen)', '5', '2026-09-18 13:04:38'),
('6', 'Department Manager', '6', '2026-09-18 13:04:38'),
('7', 'HRD / Admin', '7', '2026-09-18 13:04:38');

-- --------------------------------------------------------
-- Table structure for `jenis_cuti`
-- --------------------------------------------------------
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

-- Dumping data for table `jenis_cuti`
INSERT INTO `jenis_cuti` (`id`, `nama_cuti`, `kode`, `deskripsi`, `potong_kuota`, `max_hari_default`, `butuh_lampiran`, `created_at`) VALUES
('1', 'Sakit Surat Dokter (Tidak Potong Gaji)', 'SSD', 'Izin sakit resmi dengan melampirkan surat keterangan dokter. Tidak memotong cuti tahunan dan tidak potong gaji.', '0', '14', '1', '2026-09-18 13:04:39'),
('2', 'Sakit Tanpa Surat Dokter (Potong Jatah/Gaji)', 'STSD', 'Izin tidak masuk karena sakit tanpa surat dokter. Memotong jatah cuti tahunan, atau potong gaji jika jatah cuti habis.', '1', '3', '0', '2026-09-18 13:04:39'),
('3', 'Cuti Tahunan (Reguler HRD)', 'CT', 'Hak cuti tahunan yang dialokasikan oleh HRD (mengurangi sisa kuota cuti tahunan).', '1', '12', '0', '2026-09-18 13:04:39'),
('4', 'Cuti Haid (Mengurangi Cuti Tahunan)', 'CH', 'Cuti haid/menstruasi bagi karyawati (mengurangi jatah kuota cuti tahunan).', '1', '2', '0', '2026-09-18 13:04:39'),
('5', 'Cuti Melahirkan / Bersalin (3 Bulan)', 'CML', 'Hak cuti bersalin bagi karyawati sesuai ketentuan UU Ketenagakerjaan (3 bulan / 90 hari, upah penuh tanpa potong cuti tahunan).', '0', '90', '1', '2026-09-18 13:04:39'),
('6', 'Cuti Khusus (Keluarga Meninggal - 2 Hari)', 'CKH', 'Cuti duka cita karena anggota keluarga inti meninggal dunia (2 hari kerja tanpa memotong cuti tahunan).', '0', '2', '0', '2026-09-18 13:04:39'),
('7', 'Ijin Tidak Masuk (Potong Gaji)', 'IJN', 'Izin tidak masuk kerja untuk keperluan pribadi mendesak (potong gaji tanpa mengurangi jatah cuti tahunan).', '0', '7', '0', '2026-09-18 13:04:39');

-- --------------------------------------------------------
-- Table structure for `karyawan`
-- --------------------------------------------------------
CREATE TABLE `karyawan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nik` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','atasan','staff','operator') NOT NULL DEFAULT 'operator',
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `karyawan`
INSERT INTO `karyawan` (`id`, `nik`, `nama_lengkap`, `email`, `password`, `role`, `departemen_id`, `jabatan_id`, `tanggal_masuk`, `kuota_cuti`, `cuti_terpakai`, `sisa_cuti`, `jenis_kelamin`, `agama`, `status_pernikahan`, `no_hp`, `alamat`, `foto`, `status_aktif`, `created_at`, `updated_at`) VALUES
('1', 'NAK-001', 'Hermawan', 'hrd@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'admin', '1', '7', '2020-03-01', '12', '2', '10', 'Laki-laki', 'Islam', 'Menikah', '082122365620', 'Kawasan Industri KIIC, Karawang Barat', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:52:10'),
('2', 'NAK-010', 'Hendra Gunawan (Atasan QC)', 'hendra.qc@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'atasan', '2', '4', '2019-06-15', '12', '1', '11', 'Laki-laki', 'Islam', 'Menikah', '081311223344', 'Perumahan Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('3', 'NAK-011', 'Budi Santoso', 'budi.santoso@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'operator', '2', '1', '2022-01-10', '12', '3', '9', 'Laki-laki', 'Islam', 'Menikah', '085712345678', 'Desa Sukaluyu, Telukjambe Timur, Karawang', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('4', 'NAK-012', 'Rian Pratama', 'rian.pratama@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'operator', '2', '1', '2023-05-20', '12', '0', '12', 'Laki-laki', 'Islam', 'Belum Menikah', '085888999111', 'Kos Griya Indah, Galuh Mas, Karawang', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('5', 'NAK-020', 'Bambang Sutrisno (Atasan Machining)', 'bambang.mc@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'atasan', '3', '5', '2018-02-01', '12', '2', '10', 'Laki-laki', 'Islam', 'Menikah', '081234567890', 'Grand Taruma, Karawang Barat', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('6', 'NAK-021', 'Ahmad Fauzi', 'ahmad.fauzi@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'operator', '3', '1', '2021-08-16', '12', '4', '8', 'Laki-laki', 'Islam', 'Menikah', '087765432109', 'Cariu, Cikampek, Karawang', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('7', 'NAK-030', 'Dedi Supriyadi (Leader Produksi)', 'dedi.prod@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'atasan', '5', '3', '2020-11-05', '12', '1', '11', 'Laki-laki', 'Islam', 'Menikah', '089612345678', 'Klari, Karawang Timur', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('8', 'NAK-031', 'Dani Kurniawan', 'dani.kurniawan@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'operator', '5', '1', '2022-09-01', '12', '2', '10', 'Laki-laki', 'Islam', 'Belum Menikah', '081299887766', 'Kos Asri, Klari, Karawang', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('9', 'NAK-032', 'Nurul Aini', 'nurul.aini@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'operator', '5', '1', '2023-02-14', '12', '1', '11', 'Perempuan', 'Islam', 'Belum Menikah', '082133445566', 'Kondangjaya, Karawang Timur', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('10', 'NAK-040', 'Agus Setiawan (Spv Maint)', 'agus.maint@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'atasan', '6', '4', '2017-04-10', '12', '0', '12', 'Laki-laki', 'Islam', 'Menikah', '081399881122', 'Kosambi, Klari, Karawang', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('11', 'NAK-041', 'Eko Wahyudi', 'eko.wahyudi@nakakin.co.id', '$2y$10$TCDhyxsSDQxxpPlMpLdpS.sYTxtK3NQvadX63gWUZlNQcroxYQdny', 'staff', '6', '2', '2021-04-01', '12', '2', '10', 'Laki-laki', 'Islam', 'Menikah', '081277665544', 'Perum Resinda, Karawang Barat', NULL, 'Aktif', '2026-09-18 13:04:39', '2026-09-18 13:04:39'),
('12', 'NAK-051', 'Dewi Lestari, S.E', 'dewi.lestari@nakakin.co.id', '$2y$10$hvnop8gawcGjCLQvXHfi0uZJ7u1xH94LVETwerA2CMkKIoDHp5ud.', 'operator', '8', '2', '2023-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Belum Menikah', '081298765432', 'Karawang Barat', NULL, 'Aktif', '2026-09-18 13:51:48', '2026-09-18 13:51:48'),
('13', 'NAK-052', 'Siti Nurhaliza', 'siti.nurhaliza@nakakin.co.id', '$2y$10$hvnop8gawcGjCLQvXHfi0uZJ7u1xH94LVETwerA2CMkKIoDHp5ud.', 'operator', '2', '1', '2023-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Belum Menikah', '081298765432', 'Karawang Barat', NULL, 'Aktif', '2026-09-18 13:51:48', '2026-09-18 13:51:48'),
('14', 'NAK-053', 'Anisa Rahmadani', 'anisa.rahmadani@nakakin.co.id', '$2y$10$hvnop8gawcGjCLQvXHfi0uZJ7u1xH94LVETwerA2CMkKIoDHp5ud.', 'operator', '5', '1', '2023-01-15', '12', '0', '12', 'Perempuan', 'Islam', 'Belum Menikah', '081298765432', 'Karawang Barat', NULL, 'Aktif', '2026-09-18 13:51:49', '2026-09-18 13:51:49');

-- --------------------------------------------------------
-- Table structure for `pengajuan_cuti`
-- --------------------------------------------------------
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
  `approved_by` int DEFAULT NULL COMMENT 'ID User Atasan final yang menyetujui/menolak',
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text,
  `catatan_atasan` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_surat` (`nomor_surat`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `pengajuan_cuti_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_cuti_ibfk_2` FOREIGN KEY (`leave_type_id`) REFERENCES `jenis_cuti` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_cuti_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `karyawan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `pengajuan_cuti`
INSERT INTO `pengajuan_cuti` (`id`, `nomor_surat`, `employee_id`, `leave_type_id`, `tanggal_mulai`, `tanggal_selesai`, `total_hari`, `alasan`, `alamat_selama_cuti`, `kontak_darurat`, `attachment`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `catatan_atasan`, `created_at`, `updated_at`) VALUES
('1', 'CUTI/NAK/2026/08/001', '3', '1', '2026-08-10', '2026-08-12', '3', 'Acara silaturahmi keluarga di kampung halaman Ciamis', 'Jl. Raya Ciamis No. 45, Jawa Barat', '081234560000 (Ayah)', NULL, 'approved', '2', '2026-08-08 14:20:00', NULL, 'Disetujui. Pastikan pekerjaan shift diserahterimakan kepada rekan QC.', '2026-08-08 09:15:00', '2026-09-18 13:04:39'),
('2', 'CUTI/NAK/2026/09/002', '3', '1', '2026-09-25', '2026-09-26', '2', 'Mengurus perpanjangan administrasi berkas kependudukan di kampung', 'Desa Sukaluyu, Telukjambe', '085712345678', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-17 11:30:00', '2026-09-18 13:04:39'),
('3', 'CUTI/NAK/2026/09/003', '6', '1', '2026-09-28', '2026-09-29', '2', 'Menghadiri wisuda adik kandung di Bandung', 'Jl. Dipatiukur No. 12, Bandung', '087765432109', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-18 08:00:00', '2026-09-18 13:04:39'),
('4', 'CUTI/NAK/2026/09/004', '8', '1', '2026-09-30', '2026-09-30', '1', 'Keperluan keluarga mendesak', 'Klari, Karawang Timur', '081299887766', NULL, 'pending', NULL, NULL, NULL, NULL, '2026-09-18 08:30:00', '2026-09-18 13:04:39'),
('7', 'CUTI/NAK/2026/08/010', '9', '5', '2026-08-01', '2026-10-31', '90', 'Cuti Melahirkan / Bersalin anak pertama sesuai rekomendasi dokter spesialis kandungan.', NULL, NULL, NULL, 'approved', '7', '2026-07-28 10:00:00', NULL, 'Disetujui. Selamat atas kelahiran putranya, semoga sehat selalu.', '2026-09-18 13:52:10', '2026-09-18 13:52:10'),
('8', 'CUTI/NAK/2026/09/011', '12', '1', '2026-09-17', '2026-09-19', '3', 'Izin sakit tipes ringan dan flu berat, rawat jalan di rumah dengan surat keterangan dokter RS Hermina.', NULL, NULL, NULL, 'approved', '1', '2026-09-17 08:30:00', NULL, 'Disetujui. Istirahat yang cukup sampai pulih kembali.', '2026-09-18 13:52:10', '2026-09-18 13:52:10'),
('9', 'CUTI/NAK/2026/09/012', '13', '3', '2026-09-18', '2026-09-19', '2', 'Keperluan mendesak mengurus berkas keluarga di dinas kependudukan daerah.', NULL, NULL, NULL, 'approved', '2', '2026-09-16 14:00:00', NULL, 'Disetujui. Tugas shift QC diserahterimakan kepada rekan regu.', '2026-09-18 13:52:11', '2026-09-18 13:52:11'),
('10', 'CUTI/NAK/2026/09/013', '3', '3', '2026-09-17', '2026-09-19', '3', 'Menghadiri acara pernikahan adik kandung di Ciamis Jawa Barat.', NULL, NULL, NULL, 'approved', '2', '2026-09-15 11:20:00', NULL, 'Disetujui. Selamat untuk adiknya dan kembali bekerja tepat waktu.', '2026-09-18 13:52:11', '2026-09-18 13:52:11');

-- --------------------------------------------------------
-- Table structure for `riwayat_kuota_cuti`
-- --------------------------------------------------------
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table `riwayat_kuota_cuti`
INSERT INTO `riwayat_kuota_cuti` (`id`, `employee_id`, `kuota_sebelum`, `perubahan`, `kuota_sesudah`, `tipe`, `keterangan`, `created_by`, `created_at`) VALUES
('1', '3', '0', '12', '12', 'alokasi_tahunan', 'Alokasi Jatah Cuti Tahunan 2026 oleh HRD', '1', '2026-01-01 08:00:00'),
('2', '3', '12', '-3', '9', 'potong_cuti', 'Pengajuan Cuti CUTI/NAK/2026/08/001 Disetujui (3 hari)', '2', '2026-08-08 14:20:00'),
('3', '6', '0', '12', '12', 'alokasi_tahunan', 'Alokasi Jatah Cuti Tahunan 2026 oleh HRD', '1', '2026-01-01 08:00:00'),
('4', '6', '12', '-4', '8', 'potong_cuti', 'Penggunaan cuti tahunan periode sebelumnya', '1', '2026-05-10 10:00:00');

-- --------------------------------------------------------
-- Table structure for `pengaturan_aplikasi`
-- --------------------------------------------------------
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

-- Dumping data for table `pengaturan_aplikasi`
INSERT INTO `pengaturan_aplikasi` (`id`, `nama_aplikasi`, `singkatan_aplikasi`, `tagline`, `nama_perusahaan`, `singkatan_perusahaan`, `sub_singkatan_perusahaan`, `alamat_perusahaan`, `telepon`, `email_perusahaan`, `website`, `logo`, `favicon`, `prefix_nomor_surat`, `default_kuota_cuti`, `nama_kepala_hrd`, `jabatan_kepala_hrd`, `lokasi_surat`, `footer_text`, `updated_at`) VALUES
('1', 'Sistem Informasi Cuti Karyawan', 'FORM CUTI ONLINE', 'asasasadadasdasdad', 'PT. Mayora Teknik', 'Mayora', 'Teknik', 'safasfasfasfasfasf', '(0267) 845-1234', 'hrd@nakakin.co.id', 'www.nakakin.co.id', 'Nakakin.png', 'Nakakin.png', 'CUTI/NAK', '10', 'Hermawan', 'HRD GA Manager', 'Bekasi', 'Sistem Informasi Manajemen Cuti Karyawan', '2026-09-18 13:37:00');

