<?php
/**
 * API / Endpoint Panduan Penggunaan Sistem E-Cuti PT. Nakakin Indonesia
 * GET /api/docs/panduan.php
 */

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="Buku_Panduan_E-Cuti_PT_Nakakin_Indonesia.pdf"');

// Generate an HTML or printable guide / PDF stream
$guideHtml = <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Panduan Penggunaan E-Cuti PT. Nakakin Indonesia</title>
    <style>
        body { font-family: 'Segoe UI', Helvetica, Arial, sans-serif; line-height: 1.6; color: #1e293b; padding: 40px; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #0284c7; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #0f172a; margin: 0 0 8px; font-size: 24px; }
        .header p { color: #64748b; margin: 0; font-size: 14px; }
        h2 { color: #0284c7; border-left: 4px solid #0284c7; padding-left: 10px; margin-top: 30px; font-size: 18px; }
        .step { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .step-title { font-weight: bold; color: #0f172a; margin-bottom: 6px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; }
        .badge-blue { background: #e0f2fe; color: #0369a1; }
        .badge-green { background: #dcfce7; color: #15803d; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 13px; }
        th { background: #f1f5f9; font-weight: bold; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BUKU PANDUAN PENGOPERASIAN E-CUTI</h1>
        <p>PT. Nakakin Indonesia &bull; Sistem Pengajuan &amp; Persetujuan Cuti Digital</p>
    </div>

    <h2>1. Alur Pengajuan Cuti (Bagi Operator / Karyawan)</h2>
    <div class="step">
        <div class="step-title">Langkah 1: Mengajukan Cuti Baru</div>
        <p>Buka menu <strong>"Ajukan"</strong> di navbar bawah. Pilih <em>Jenis Cuti</em> (Tahunan, Sakit, Melahirkan, Izin Khusus, dll), tentukan <em>Rentang Tanggal</em>, masukkan <em>Alasan Cuti</em>, dan unggah <em>Dokumen/Surat Dokter</em> jika diperlukan.</p>
    </div>
    <div class="step">
        <div class="step-title">Langkah 2: Melacak Status &amp; Notifikasi</div>
        <p>Pantau status cuti di tab <strong>"Riwayat"</strong> atau klik ikon <strong>Lonceng Notifikasi</strong> di atas Dashboard. Status cuti akan bergerak melalui tahapan verifikasi.</p>
    </div>

    <h2>2. Alur Persetujuan Hierarki 3-Tingkat (3-Tier Approval)</h2>
    <table>
        <tr>
            <th>Posisi Pemohon</th>
            <th>Tahap 1</th>
            <th>Tahap 2</th>
            <th>Tahap 3 (Final)</th>
        </tr>
        <tr>
            <td><strong>Operator / Staff</strong></td>
            <td><span class="badge badge-blue">Supervisor / Leader</span></td>
            <td><span class="badge badge-amber">Manager Dept</span></td>
            <td><span class="badge badge-green">HRD / Admin</span></td>
        </tr>
        <tr>
            <td><strong>Leader / Spv</strong></td>
            <td>-</td>
            <td><span class="badge badge-amber">Manager Dept</span></td>
            <td><span class="badge badge-green">HRD / Admin</span></td>
        </tr>
        <tr>
            <td><strong>Manager</strong></td>
            <td>-</td>
            <td>-</td>
            <td><span class="badge badge-green">HRD / Admin</span></td>
        </tr>
    </table>

    <h2>3. Menu Pengaturan &amp; Fitur Tambahan</h2>
    <div class="step">
        <div class="step-title">Fitur Ganti Server &amp; Pengaturan Tema</div>
        <ul>
            <li><strong>Tema Gelap/Terang:</strong> Aktifkan tombol Switch Mode Gelap di menu Pengaturan untuk kenyamanan visual.</li>
            <li><strong>Ganti Server API:</strong> Jika berpindah jaringan (WiFi lokal ke kuota internet/Cloudflare), ubah alamat server di menu Pengaturan lalu lakukan <em>Uji Koneksi (Ping)</em>.</li>
            <li><strong>Pendaftaran Karyawan:</strong> Khusus akun HRD, dapat mendaftarkan pegawai baru melalui tombol <em>+ Karyawan</em> di Dashboard.</li>
        </ul>
    </div>

    <div class="footer">
        &copy; 2026 PT. Nakakin Indonesia. Hak Cipta Dilindungi Undang-Undang.
    </div>
</body>
</html>
HTML;

echo $guideHtml;
