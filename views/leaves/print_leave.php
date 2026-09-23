<?php
/**
 * Print Official Leave Permit / Surat Izin Cuti Karyawan
 * PT. Nakakin Indonesia
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../config/session.php';

requireLogin();

$id = (int)($_GET['id'] ?? 0);
$pdo = getDbConnection();

$stmt = $pdo->prepare("
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.email, e.no_hp, e.tanggal_masuk, e.kuota_cuti, e.sisa_cuti, e.cuti_terpakai, e.alamat as alamat_karyawan,
           d.nama_dept, d.kode_dept,
           p.nama_jabatan,
           lt.nama_cuti, lt.potong_kuota,
           ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan, pos_ap.nama_jabatan as jabatan_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    LEFT JOIN jabatan pos_ap ON ap.jabatan_id = pos_ap.id
    WHERE lr.id = ?
");
$stmt->execute([$id]);
$leave = $stmt->fetch();

if (!$leave) {
    die('Data permohonan cuti tidak ditemukan!');
}

$appSettings = getAppSettings($pdo);
$printLogoUrl = !empty($appSettings['logo']) ? BASE_URL . '/assets/images/' . $appSettings['logo'] : BASE_URL . '/assets/images/Nakakin.png';
$printFavUrl = !empty($appSettings['favicon']) ? BASE_URL . '/assets/images/' . $appSettings['favicon'] : BASE_URL . '/assets/images/Nakakin.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin Cuti - <?= htmlspecialchars($leave['nomor_surat']) ?> - <?= htmlspecialchars($appSettings['nama_perusahaan']) ?></title>
    
    <link rel="icon" type="image/png" href="<?= $printFavUrl ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
        }
        .print-container {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            padding: 40px 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .header-kop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #000000;
            padding-bottom: 14px;
            margin-bottom: 20px;
            gap: 20px;
        }
        .header-kop .kop-text {
            text-align: left;
            flex: 1;
        }
        .header-kop h2 {
            font-size: 22px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
            color: #000000;
        }
        .header-kop p {
            font-size: 11.5px;
            margin: 2px 0 0;
            color: #222222;
            line-height: 1.35;
        }
        .header-kop img {
            height: 65px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 25px;
        }
        .doc-title h3 {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
        }
        .doc-title .doc-number {
            font-size: 12px;
            color: #333;
        }
        .table-data {
            width: 100%;
            font-size: 13px;
            margin-bottom: 20px;
        }
        .table-data td {
            padding: 6px 8px;
            vertical-align: top;
        }
        .table-bordered-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-bottom: 20px;
        }
        .table-bordered-custom th,
        .table-bordered-custom td {
            border: 1px solid #000000;
            padding: 8px 10px;
        }
        .table-bordered-custom th {
            background-color: #f8fafc;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            text-align: center;
            font-size: 12.5px;
            margin-top: 35px;
        }
        .signature-table td {
            padding: 10px;
            width: 33.33%;
            vertical-align: top;
        }
        .sign-space {
            height: 70px;
        }
        .watermark-stamp {
            border: 2px solid #16a34a;
            color: #16a34a;
            padding: 4px 10px;
            font-weight: bold;
            font-size: 11px;
            display: inline-block;
            border-radius: 4px;
            transform: rotate(-5deg);
        }
        @media print {
            body {
                background: #ffffff !important;
            }
            .print-container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container text-center my-3 no-print">
    <button onclick="window.print()" class="btn btn-primary px-4 fw-bold shadow-sm me-2">
        <i class="fa-solid fa-print me-1"></i> Cetak / Simpan PDF
    </button>
    <button onclick="window.close()" class="btn btn-outline-secondary px-3">
        <i class="fa-solid fa-times me-1"></i> Tutup
    </button>
</div>

<div class="print-container">
    <!-- Header KOP Perusahaan (Teks Kiri, Logo Kanan) -->
    <div class="header-kop">
        <div class="kop-text">
            <h2><?= strtoupper(htmlspecialchars($appSettings['nama_perusahaan'])) ?></h2>
            <?php if (!empty($appSettings['tagline'])): ?>
                <p><strong><?= htmlspecialchars($appSettings['tagline']) ?></strong></p>
            <?php endif; ?>
            <?php if (!empty($appSettings['alamat_perusahaan'])): ?>
                <p><?= htmlspecialchars($appSettings['alamat_perusahaan']) ?></p>
            <?php endif; ?>
            <p>
                <?php if (!empty($appSettings['telepon'])): ?>Telp: <?= htmlspecialchars($appSettings['telepon']) ?> &bull; <?php endif; ?>
                <?php if (!empty($appSettings['email_perusahaan'])): ?>Email: <?= htmlspecialchars($appSettings['email_perusahaan']) ?> &bull; <?php endif; ?>
                <?php if (!empty($appSettings['website'])): ?>Website: <?= htmlspecialchars($appSettings['website']) ?><?php endif; ?>
            </p>
        </div>
        <img src="<?= $printLogoUrl ?>" alt="<?= htmlspecialchars($appSettings['nama_perusahaan']) ?>">
    </div>

    <!-- Title -->
    <div class="doc-title">
        <h3>FORMULIR PERMOHONAN & SURAT IZIN CUTI</h3>
        <div class="doc-number">Nomor: <strong><?= htmlspecialchars($leave['nomor_surat']) ?></strong></div>
    </div>

    <p style="font-size: 13px; margin-bottom: 12px;">Yang bertanda tangan di bawah ini, menerangkan bahwa karyawan:</p>

    <!-- Data Karyawan -->
    <table class="table-data">
        <tr>
            <td style="width: 25%;"><strong>Nama Lengkap</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 35%;"><strong><?= htmlspecialchars($leave['nama_lengkap']) ?></strong></td>
            <td style="width: 18%;"><strong>Tanggal Masuk</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 18%;"><?= formatTanggalIndo($leave['tanggal_masuk']) ?></td>
        </tr>
        <tr>
            <td><strong>N I K</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($leave['nik']) ?></td>
            <td><strong>Masa Kerja</strong></td>
            <td>:</td>
            <td><strong><?= hitungMasaKerja($leave['tanggal_masuk']) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Departemen / Bagian</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($leave['nama_dept']) ?> (<?= htmlspecialchars($leave['kode_dept']) ?>)</td>
            <td><strong>Sisa Hak Cuti</strong></td>
            <td>:</td>
            <td><strong style="color: #000;"><?= $leave['sisa_cuti'] ?> Hari</strong></td>
        </tr>
        <tr>
            <td><strong>Jabatan</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($leave['nama_jabatan']) ?></td>
            <td><strong>No. Handphone / WA</strong></td>
            <td>:</td>
            <td><?= htmlspecialchars($leave['no_hp'] ?: '-') ?></td>
        </tr>
    </table>

    <p style="font-size: 13px; margin-bottom: 10px;">Mengajukan permohonan izin cuti dengan rincian sebagai berikut:</p>

    <!-- Detail Cuti Table -->
    <table class="table-bordered-custom">
        <thead>
            <tr>
                <th style="width: 25%;">Jenis Cuti</th>
                <th style="width: 30%;">Periode Tanggal Cuti</th>
                <th style="width: 15%;">Jumlah Hari</th>
                <th style="width: 30%;">Alasan / Keperluan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong><?= htmlspecialchars($leave['nama_cuti']) ?></strong></td>
                <td style="text-align: center;">
                    <?= formatTanggalIndo($leave['tanggal_mulai']) ?><br>
                    s/d <?= formatTanggalIndo($leave['tanggal_selesai']) ?>
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 14px;">
                    <?= $leave['total_hari'] ?> Hari Kerja
                </td>
                <td><?= nl2br(htmlspecialchars($leave['alasan'])) ?></td>
            </tr>
            <tr>
                <td colspan="4" style="background-color: #fafafa;">
                    <strong>Alamat / Kontak Selama Cuti:</strong> <?= htmlspecialchars($leave['alamat_selama_cuti'] ?: $leave['alamat_karyawan'] ?: '-') ?> 
                    (Kontak Darurat: <?= htmlspecialchars($leave['kontak_darurat'] ?: $leave['no_hp'] ?: '-') ?>)
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Status Box -->
    <div style="font-size: 12px; margin-top: 15px; padding: 10px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
        <strong>Catatan & Keputusan Atasan:</strong> 
        <?= $leave['catatan_atasan'] ? htmlspecialchars($leave['catatan_atasan']) : 'Disetujui sesuai dengan ketentuan dan prosedur ketenagakerjaan ' . htmlspecialchars($appSettings['nama_perusahaan']) . '.' ?>
    </div>

    <!-- Signatures Table -->
    <table class="signature-table">
        <tr>
            <td>
                <?= htmlspecialchars($appSettings['lokasi_surat'] ?: 'Karawang') ?>, <?= date('d M Y', strtotime($leave['created_at'])) ?><br>
                <strong>Pemohon Cuti</strong>
                <div class="sign-space d-flex align-items-center justify-content-center">
                    <span class="watermark-stamp"><i class="fa-solid fa-check"></i> DIGITAL SIGNED</span>
                </div>
                <strong>( <?= htmlspecialchars($leave['nama_lengkap']) ?> )</strong><br>
                <span style="font-size: 11px;">NIK: <?= htmlspecialchars($leave['nik']) ?></span>
            </td>
            <td>
                Mengetahui & Menyetujui,<br>
                <strong>Atasan Langsung (<?= htmlspecialchars($leave['nama_dept']) ?>)</strong>
                <div class="sign-space d-flex align-items-center justify-content-center">
                    <?php if ($leave['approved_by']): ?>
                        <span class="watermark-stamp"><i class="fa-solid fa-certificate"></i> APPROVED BY ATASAN</span>
                    <?php else: ?>
                        <span style="color: #999;">(Menunggu Approval)</span>
                    <?php endif; ?>
                </div>
                <strong>( <?= htmlspecialchars($leave['nama_atasan'] ?: '..........................................') ?> )</strong><br>
                <span style="font-size: 11px;"><?= htmlspecialchars($leave['jabatan_atasan'] ?: 'Leader / Spv / Manager') ?></span>
            </td>
            <td>
                Diverifikasi Oleh,<br>
                <strong>HRD Department</strong>
                <div class="sign-space d-flex align-items-center justify-content-center">
                    <span class="watermark-stamp" style="border-color: #1e3a8a; color: #1e3a8a;"><i class="fa-solid fa-stamp"></i> VERIFIED HRD</span>
                </div>
                <strong>( <?= htmlspecialchars($appSettings['nama_kepala_hrd'] ?: 'Siti Rahmawati, S.Psi') ?> )</strong><br>
                <span style="font-size: 11px;"><?= htmlspecialchars($appSettings['jabatan_kepala_hrd'] ?: 'HRD & GA Manager') ?></span>
            </td>
        </tr>
    </table>

    <div style="margin-top: 35px; border-top: 1px dashed #ccc; padding-top: 8px; font-size: 10px; color: #777; text-align: center;">
        Dokumen ini diterbitkan secara elektronik oleh <?= htmlspecialchars($appSettings['nama_aplikasi']) ?> <?= htmlspecialchars($appSettings['nama_perusahaan']) ?> &bull; Sah tanpa tanda tangan basah berdasarkan verifikasi sistem.
    </div>
</div>

</body>
</html>
