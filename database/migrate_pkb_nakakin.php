<?php
/**
 * Migration Script: Master Cuti PKB 25 Jenis, Shift Maju, Absensi Mangkir, & Rebranding Nakakin Mobile
 */
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

echo "Starting migration...\n";

// 1. Update jenis_cuti structure
$pdo->exec("
ALTER TABLE jenis_cuti 
MODIFY COLUMN potong_kuota TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS kategori VARCHAR(50) NOT NULL DEFAULT 'Cuti Reguler' AFTER nama_cuti,
ADD COLUMN IF NOT EXISTS durasi_maksimal VARCHAR(50) DEFAULT '1 Hari' AFTER kode,
ADD COLUMN IF NOT EXISTS potong_gaji TINYINT(1) NOT NULL DEFAULT 0 AFTER potong_kuota,
ADD COLUMN IF NOT EXISTS wewenang_approval VARCHAR(100) DEFAULT 'Leader & Spv Dept' AFTER butuh_lampiran,
ADD COLUMN IF NOT EXISTS catatan_khusus TEXT NULL AFTER wewenang_approval;
");
echo "1. jenis_cuti columns updated.\n";

// 2. Update karyawan structure (DECIMAL 4,1 and current_shift)
$pdo->exec("
ALTER TABLE karyawan 
MODIFY COLUMN kuota_cuti DECIMAL(4,1) NOT NULL DEFAULT 12.0,
MODIFY COLUMN cuti_terpakai DECIMAL(4,1) NOT NULL DEFAULT 0.0,
MODIFY COLUMN sisa_cuti DECIMAL(4,1) NOT NULL DEFAULT 12.0,
MODIFY COLUMN current_shift ENUM('Shift 1', 'Shift 1 (Pagi)', 'Shift 2 (Malam)', 'Shift 2 (Maju)') NOT NULL DEFAULT 'Shift 1 (Pagi)';
UPDATE karyawan SET current_shift = 'Shift 2 (Malam)' WHERE current_shift LIKE '%Shift 2%';
UPDATE karyawan SET current_shift = 'Shift 1 (Pagi)' WHERE current_shift LIKE '%Shift 1%';
");
echo "2. karyawan columns updated.\n";

// 3. Update pengajuan_cuti structure
$pdo->exec("
ALTER TABLE pengajuan_cuti 
MODIFY COLUMN total_hari DECIMAL(4,1) NOT NULL DEFAULT 1.0,
MODIFY COLUMN shift ENUM('Shift 1', 'Shift 1 (Pagi)', 'Shift 2 (Malam)', 'Shift 2 (Maju)', 'Non-Shift') NOT NULL DEFAULT 'Shift 1 (Pagi)';
UPDATE pengajuan_cuti SET shift = 'Shift 2 (Malam)' WHERE shift LIKE '%Shift 2%';
UPDATE pengajuan_cuti SET shift = 'Shift 1 (Pagi)' WHERE shift LIKE '%Shift 1%';
");
echo "3. pengajuan_cuti columns updated.\n";

// 4. Create absensi_shift table
$pdo->exec("
CREATE TABLE IF NOT EXISTS absensi_shift (
  id INT NOT NULL AUTO_INCREMENT,
  employee_id INT NOT NULL,
  leader_id INT NOT NULL,
  tanggal DATE NOT NULL,
  shift ENUM('Shift 1', 'Shift 1 (Pagi)', 'Shift 2 (Malam)', 'Shift 2 (Maju)') NOT NULL DEFAULT 'Shift 1 (Pagi)',
  status VARCHAR(30) NOT NULL DEFAULT 'mangkir',
  keterangan_mangkir TEXT NULL,
  status_revisi ENUM('original', 'revised') NOT NULL DEFAULT 'original',
  direvisi_menjadi VARCHAR(50) NULL,
  alasan_revisi TEXT NULL,
  bukti_lampiran VARCHAR(255) NULL,
  direvisi_pada DATETIME NULL,
  hrd_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_emp (employee_id),
  KEY idx_leader (leader_id),
  KEY idx_tgl (tanggal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE absensi_shift MODIFY COLUMN shift ENUM('Shift 1', 'Shift 1 (Pagi)', 'Shift 2 (Malam)', 'Shift 2 (Maju)') NOT NULL DEFAULT 'Shift 1 (Pagi)';
UPDATE absensi_shift SET shift = 'Shift 2 (Malam)' WHERE shift LIKE '%Shift 2%';
UPDATE absensi_shift SET shift = 'Shift 1 (Pagi)' WHERE shift LIKE '%Shift 1%';
");
echo "4. absensi_shift table updated.\n";

// 5. Sync 25 Jenis Cuti
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE jenis_cuti;");

$items = [
    [1, 'Cuti Tahunan Penuh', 'Cuti Reguler', 'CT', 'Sesuai Sisa', 1, 0, 12, 0, 'Leader -> Mgr -> HRD', 'Kuota tahunan reguler'],
    [2, 'Cuti Setengah Hari (Pagi / Siang)', 'Cuti Reguler', 'CT-HALF', '0.5 Hari (4 Jam)', 1, 0, 1, 0, 'Leader & Spv Dept', '4 jam = 1/2 hari'],
    [3, 'Izin Pulang Cepat (PC) - Pribadi', 'Jam-Jaman', 'PC-PRI', 'Sisa Jam Shift', 0, 1, 1, 0, 'Leader & Spv Dept', '4 jam = 1/2 hari'],
    [4, 'Izin Pulang Cepat (PC) - Sakit Klinik', 'Jam-Jaman', 'PC-SKT', 'Sisa Jam Shift', 0, 1, 1, 1, 'Dokter Klinik & Leader', '4 jam = 1/2 hari'],
    [5, 'Izin Keluar Sementara (Kembali Masuk)', 'Jam-Jaman', 'IK-TMP', '1 - 3 Jam', 0, 1, 1, 0, 'Leader & Spv Dept', '4 jam = 1/2 hari'],
    [6, 'Izin Datang Terlambat', 'Jam-Jaman', 'T', '1 - 2 Jam', 0, 1, 1, 0, 'Leader & Spv Dept', 'tidak boleh diawal masuk'],
    [7, 'Sakit Surat Dokter (SD)', 'Medis', 'SD', 'Sesuai Surat', 0, 0, 14, 1, 'Leader & HRD', 'Surat Keterangan Dokter Resmi'],
    [8, 'Sakit Tanpa Surat Dokter (ST)', 'Medis', 'ST', 'Maks 1 Hari', 1, 1, 1, 0, 'Leader & Spv Dept', 'Potong cuti, jika kuota 0 potong gaji'],
    [9, 'Cuti Haid (Karyawati, H1 & H2)', 'Normatif', 'CH', '2 Hari', 0, 0, 2, 1, 'Leader & Spv Dept', 'Wajib Surat Dokter'],
    [10, 'Cuti Melahirkan / Bersalin', 'Normatif', 'CML', '90 Hari (3 Bln)', 0, 0, 90, 1, 'Leader -> Mgr -> HRD', 'Surat Bidan / RS / HPL'],
    [11, 'Cuti Keguguran Kandungan', 'Normatif', 'CKG', '45 Hari (1.5 Bln)', 0, 0, 45, 1, 'Leader -> Mgr -> HRD', 'Surat Dokter Kandungan (Obgyn)'],
    [12, 'Pekerja Menikah', 'Cuti Khusus', 'CK-NIK', '3 Hari', 0, 0, 3, 1, 'Leader -> Mgr -> HRD', 'Surat Nikah/akta Nikah'],
    [13, 'Menikahkan Anak Sah', 'Cuti Khusus', 'CK-ANK', '2 Hari', 0, 0, 2, 1, 'Leader -> Mgr -> HRD', 'Undangan Pernikahan'],
    [14, 'Khitanan / Baptis Anak', 'Cuti Khusus', 'CK-KHT', '2 Hari', 0, 0, 2, 1, 'Leader -> Mgr -> HRD', 'Keterangan Khitan/Baptis'],
    [15, 'Istri Melahirkan / Keguguran', 'Cuti Khusus', 'CK-IMS', '2 Hari', 0, 0, 2, 1, 'Leader -> Mgr -> HRD', 'Surat Keterangan RS/Klinik'],
    [16, 'Duka Cita (Keluarga Inti Meninggal)', 'Cuti Khusus', 'CK-DK1', '2 Hari', 0, 0, 2, 1, 'Leader -> Mgr -> HRD', 'Surat Kematian (Suami/Istri/Anak/Ortu/Mertua)'],
    [17, 'Duka Cita (Keluarga Serumah Meninggal)', 'Cuti Khusus', 'CK-DK2', '1 Hari', 0, 0, 1, 1, 'Leader -> Mgr -> HRD', 'Surat Kematian & Ket. RT'],
    [18, 'Ibadah Haji (Pertama Kali)', 'Cuti Khusus', 'CK-HAJ', '40 Hari', 0, 0, 40, 1, 'Leader -> Mgr -> HRD', 'Porsi Haji Kemenag'],
    [19, 'Dispensasi Serikat Pekerja (PUK/Serikat)', 'Dispensasi', 'DISP-SP', 'Sesuai Agenda', 0, 0, 30, 1, 'Leader & Spv Dept (CC HRD)', 'Surat Mandat / Undangan Resmi Serikat'],
    [20, 'Tugas Perusahaan / Dinas Luar', 'Dispensasi', 'DISP-DNS', 'Sesuai Tugas', 0, 0, 30, 1, 'Manager Dept', 'Surat Perintah Perjalanan Dinas (SPPD)'],
    [21, 'Bencana Alam / Force Majeure', 'Dispensasi', 'DISP-BNC', '1 - 2 Hari', 0, 0, 2, 1, 'Leader & Spv Dept', 'Foto lokasi & Surat RT/RW (Banjir/Kebakaran)'],
    [22, 'Pendidikan / Ujian Akhir / Wisuda', 'Izin Khusus', 'DISP-STD', '1 - 2 Hari', 0, 0, 2, 1, 'Leader & Spv Dept', 'masuknya ke ijin'],
    [23, 'Panggilan Negara / Pengadilan / Donor', 'Cuti Khusus', 'DISP-NGR', 'Sesuai Acara', 0, 0, 7, 1, 'Leader & Spv Dept', 'jika casenya wajib militer dan pemilu masuknya ka kecuti khusus'],
    [24, 'Izin Tidak Masuk (Keperluan Pribadi)', 'Unpaid', 'IJN', 'Sesuai Pengajuan', 0, 1, 7, 0, 'Leader & Spv Dept', 'Form Izin Pribadi'],
    [25, 'Mangkir / Alpha (Tanpa Kabar)', 'Pelanggaran', 'ALPHA', '1 Hari (Per Shift)', 0, 1, 1, 0, 'Full Leader (HRD Info Only)', 'Diinput langsung oleh Leader (Bisa direvisi)']
];

$stmt = $pdo->prepare("
INSERT INTO jenis_cuti (id, nama_cuti, kategori, kode, durasi_maksimal, potong_kuota, potong_gaji, max_hari_default, butuh_lampiran, wewenang_approval, catatan_khusus)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($items as $item) {
    $stmt->execute($item);
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "5. 25 Jenis Cuti synced successfully.\n";

// 6. Update pengaturan_aplikasi to Nakakin Mobile
$pdo->exec("
UPDATE pengaturan_aplikasi 
SET nama_aplikasi = 'Nakakin Mobile', 
    singkatan_aplikasi = 'Nakakin Mobile', 
    tagline = 'Precision Machinery, Pumps & Tooling Manufacturing'
WHERE id = 1;
");
echo "6. pengaturan_aplikasi updated to Nakakin Mobile.\n";

echo "ALL MIGRATIONS FINISHED SUCCESSFULLY!\n";
