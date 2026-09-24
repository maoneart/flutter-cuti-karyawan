<?php
/**
 * Print Official Leave Permit / Surat Izin Cuti Karyawan (4-Tier Multi-Level Approval)
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
           p.nama_jabatan, p.level_hierarki as level_karyawan,
           lt.nama_cuti, lt.potong_kuota,
           spv.nama_lengkap as spv_name, spv.nik as spv_nik, spv_j.nama_jabatan as spv_jabatan, spv_d.nama_dept as spv_dept,
           mgr.nama_lengkap as manager_name, mgr.nik as manager_nik, mgr_j.nama_jabatan as manager_jabatan, mgr_d.nama_dept as manager_dept,
           hrd.nama_lengkap as hrd_name, hrd.nik as hrd_nik, hrd_j.nama_jabatan as hrd_jabatan, hrd_d.nama_dept as hrd_dept,
           ap.nama_lengkap as nama_atasan, ap.nik as nik_atasan, pos_ap.nama_jabatan as jabatan_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan spv ON lr.spv_id = spv.id
    LEFT JOIN jabatan spv_j ON spv.jabatan_id = spv_j.id
    LEFT JOIN departemen spv_d ON spv.departemen_id = spv_d.id
    LEFT JOIN karyawan mgr ON lr.manager_id = mgr.id
    LEFT JOIN jabatan mgr_j ON mgr.jabatan_id = mgr_j.id
    LEFT JOIN departemen mgr_d ON mgr.departemen_id = mgr_d.id
    LEFT JOIN karyawan hrd ON lr.hrd_id = hrd.id
    LEFT JOIN jabatan hrd_j ON hrd.jabatan_id = hrd_j.id
    LEFT JOIN departemen hrd_d ON hrd.departemen_id = hrd_d.id
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
            max-width: 840px;
            margin: 30px auto;
            background: #ffffff;
            padding: 35px 45px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 4px;
        }
        .header-kop {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px double #000000;
            padding-bottom: 12px;
            margin-bottom: 18px;
            gap: 20px;
        }
        .header-kop .kop-text {
            text-align: left;
            flex: 1;
        }
        .header-kop h2 {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
            color: #000000;
        }
        .header-kop p {
            font-size: 11px;
            margin: 2px 0 0;
            color: #222222;
            line-height: 1.3;
        }
        .header-kop img {
            height: 60px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h3 {
            font-size: 15px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
        }
        .doc-title .doc-number {
            font-size: 11.5px;
            color: #333;
        }
        .table-data {
            width: 100%;
            font-size: 12.5px;
            margin-bottom: 16px;
        }
        .table-data td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .table-bordered-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 16px;
        }
        .table-bordered-custom th,
        .table-bordered-custom td {
            border: 1px solid #000000;
            padding: 6px 8px;
        }
        .table-bordered-custom th {
            background-color: #f8fafc;
            text-align: center;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-size: 11.5px;
            margin-top: 25px;
            border: 1px solid #000000;
        }
        .signature-table th {
            border: 1px solid #000000;
            background-color: #f8fafc;
            padding: 6px 4px;
            font-weight: bold;
            font-size: 11px;
        }
        .signature-table td {
            border: 1px solid #000000;
            padding: 8px 4px;
            width: 25%;
            vertical-align: top;
        }
        .sign-space {
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .watermark-stamp {
            border: 1.5px solid #16a34a;
            color: #16a34a;
            padding: 3px 6px;
            font-weight: bold;
            font-size: 9.5px;
            display: inline-block;
            border-radius: 3px;
            transform: rotate(-3deg);
            line-height: 1.2;
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

    <p style="font-size: 12px; margin-bottom: 10px;">Yang bertanda tangan di bawah ini, menerangkan bahwa karyawan:</p>

    <!-- Data Karyawan -->
    <table class="table-data">
        <tr>
            <td style="width: 24%;"><strong>Nama Lengkap</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 36%;"><strong><?= htmlspecialchars($leave['nama_lengkap']) ?></strong></td>
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
            <td><?= htmlspecialchars($leave['no_hp'] ?: $leave['kontak_darurat'] ?: '-') ?></td>
        </tr>
    </table>

    <p style="font-size: 12px; margin-bottom: 8px;">Mengajukan permohonan izin cuti dengan rincian sebagai berikut:</p>

    <!-- Detail Cuti Table -->
    <table class="table-bordered-custom">
        <thead>
            <tr>
                <th style="width: 25%;">Jenis Cuti</th>
                <th style="width: 28%;">Periode Tanggal Cuti</th>
                <th style="width: 15%;">Jumlah Hari</th>
                <th style="width: 32%;">Alasan / Keperluan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong><?= htmlspecialchars($leave['nama_cuti']) ?></strong></td>
                <td style="text-align: center;">
                    <?= formatTanggalIndo($leave['tanggal_mulai']) ?><br>
                    s/d <?= formatTanggalIndo($leave['tanggal_selesai']) ?>
                </td>
                <td style="text-align: center; font-weight: bold; font-size: 13px;">
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
    <div style="font-size: 11.5px; margin-top: 10px; padding: 8px 10px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
        <strong>Catatan & Keputusan:</strong> 
        <?= $leave['catatan_atasan'] ? htmlspecialchars($leave['catatan_atasan']) : 'Disetujui sesuai dengan ketentuan dan prosedur operasional ' . htmlspecialchars($appSettings['nama_perusahaan']) . '.' ?>
    </div>

    <!-- 4 Kolom Approval Table -->
    <table class="signature-table">
        <thead>
            <tr>
                <th>1. Operator / Karyawan</th>
                <th>2. Leader / Supervisor</th>
                <th>3. Manager</th>
                <th>4. HRD</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Kolom 1: Pemohon Cuti -->
                <td>
                    <div style="font-size: 10px; margin-bottom: 2px;"><?= htmlspecialchars($appSettings['lokasi_surat'] ?: 'Karawang') ?>, <?= date('d/m/Y', strtotime($leave['created_at'])) ?></div>
                    <div class="sign-space">
                        <span class="watermark-stamp"><i class="fa-solid fa-check"></i> DIGITAL SIGNED</span>
                    </div>
                    <strong>( <?= htmlspecialchars($leave['nama_lengkap']) ?> )</strong><br>
                    <span style="font-size: 10.5px; color: #333;"><?= htmlspecialchars($leave['nama_jabatan']) ?> <?= htmlspecialchars($leave['kode_dept'] ?: $leave['nama_dept']) ?></span>
                </td>

                <!-- Kolom 2: Leader / Supervisor -->
                <td>
                    <div style="font-size: 10px; margin-bottom: 2px;">
                        <?= !empty($leave['spv_at']) ? date('d/m/Y', strtotime($leave['spv_at'])) : '-' ?>
                    </div>
                    <div class="sign-space">
                        <?php if (!empty($leave['spv_id'])): ?>
                            <span class="watermark-stamp"><i class="fa-solid fa-certificate"></i> APPROVED</span>
                        <?php elseif ($leave['level_karyawan'] >= 3): ?>
                            <span class="watermark-stamp" style="border-color: #64748b; color: #64748b;"><i class="fa-solid fa-forward"></i> BYPASS</span>
                        <?php elseif ($leave['status'] === 'rejected' && empty($leave['spv_id'])): ?>
                            <span class="watermark-stamp" style="border-color: #dc2626; color: #dc2626;"><i class="fa-solid fa-times"></i> REJECTED</span>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 10.5px; font-style: italic;">(Menunggu Approval)</span>
                        <?php endif; ?>
                    </div>
                    <strong>( <?= htmlspecialchars(!empty($leave['spv_id']) ? $leave['spv_name'] : ($leave['level_karyawan'] >= 3 ? '-' : '...................................')) ?> )</strong><br>
                    <span style="font-size: 10.5px; color: #333;">
                        <?php if (!empty($leave['spv_id'])): ?>
                            <?= htmlspecialchars($leave['spv_jabatan'] ?: 'Leader / Spv') ?> <?= htmlspecialchars($leave['spv_dept'] ? $leave['spv_dept'] : '') ?>
                        <?php elseif ($leave['level_karyawan'] >= 3): ?>
                            Leader / Supervisor
                        <?php else: ?>
                            Leader / Supervisor
                        <?php endif; ?>
                    </span>
                </td>

                <!-- Kolom 3: Manager -->
                <td>
                    <div style="font-size: 10px; margin-bottom: 2px;">
                        <?= !empty($leave['manager_at']) ? date('d/m/Y', strtotime($leave['manager_at'])) : '-' ?>
                    </div>
                    <div class="sign-space">
                        <?php if (!empty($leave['manager_id'])): ?>
                            <span class="watermark-stamp"><i class="fa-solid fa-certificate"></i> APPROVED</span>
                        <?php elseif ($leave['level_karyawan'] >= 6): ?>
                            <span class="watermark-stamp" style="border-color: #64748b; color: #64748b;"><i class="fa-solid fa-forward"></i> BYPASS</span>
                        <?php elseif ($leave['status'] === 'rejected' && !empty($leave['spv_id']) && empty($leave['manager_id'])): ?>
                            <span class="watermark-stamp" style="border-color: #dc2626; color: #dc2626;"><i class="fa-solid fa-times"></i> REJECTED</span>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 10.5px; font-style: italic;">(Menunggu Approval)</span>
                        <?php endif; ?>
                    </div>
                    <strong>( <?= htmlspecialchars(!empty($leave['manager_id']) ? $leave['manager_name'] : ($leave['level_karyawan'] >= 6 ? '-' : '...................................')) ?> )</strong><br>
                    <span style="font-size: 10.5px; color: #333;">
                        <?php if (!empty($leave['manager_id'])): ?>
                            <?= htmlspecialchars($leave['manager_jabatan'] ?: 'Department Manager') ?>
                        <?php else: ?>
                            Department / Plant Manager
                        <?php endif; ?>
                    </span>
                </td>

                <!-- Kolom 4: HRD -->
                <td>
                    <div style="font-size: 10px; margin-bottom: 2px;">
                        <?= ($leave['status'] === 'approved' && (!empty($leave['hrd_at']) || !empty($leave['approved_at']))) ? date('d/m/Y', strtotime($leave['hrd_at'] ?: $leave['approved_at'])) : '-' ?>
                    </div>
                    <div class="sign-space">
                        <?php if ($leave['status'] === 'approved'): ?>
                            <span class="watermark-stamp" style="border-color: #1e3a8a; color: #1e3a8a;"><i class="fa-solid fa-stamp"></i> VERIFIED HRD</span>
                        <?php elseif ($leave['status'] === 'rejected' && (!empty($leave['manager_id']) || $leave['level_karyawan'] >= 5)): ?>
                            <span class="watermark-stamp" style="border-color: #dc2626; color: #dc2626;"><i class="fa-solid fa-times"></i> REJECTED</span>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 10.5px; font-style: italic;">(Menunggu Approval)</span>
                        <?php endif; ?>
                    </div>
                    <strong>( <?= htmlspecialchars($leave['status'] === 'approved' ? ($leave['hrd_name'] ?: $leave['nama_atasan'] ?: $appSettings['nama_kepala_hrd'] ?: 'HRD') : '...................................') ?> )</strong><br>
                    <span style="font-size: 10.5px; color: #333;">
                        <?php if ($leave['status'] === 'approved'): ?>
                            <?= htmlspecialchars($leave['hrd_jabatan'] ?: $appSettings['jabatan_kepala_hrd'] ?: 'HRD & GA Manager') ?>
                        <?php else: ?>
                            HRD
                        <?php endif; ?>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px; border-top: 1px dashed #ccc; padding-top: 8px; font-size: 10px; color: #777; text-align: center;">
        Dokumen ini diterbitkan secara elektronik oleh <?= htmlspecialchars($appSettings['nama_aplikasi']) ?> <?= htmlspecialchars($appSettings['nama_perusahaan']) ?> &bull; Sah tanpa tanda tangan basah berdasarkan verifikasi sistem.
    </div>
</div>

</body>
</html>
