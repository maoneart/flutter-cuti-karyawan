<?php
/**
 * API & Web Endpoint: Buku Panduan Penggunaan Sistem E-Cuti PT. Nakakin Indonesia
 * Mendukung 4 Panduan Berbeda Sesuai Role:
 * - GET /api/docs/panduan.php?role=operator (Panduan Operator & Staff)
 * - GET /api/docs/panduan.php?role=leader   (Panduan Leader & Supervisor)
 * - GET /api/docs/panduan.php?role=manager  (Panduan Plant Manager)
 * - GET /api/docs/panduan.php?role=hrd      (Panduan HRD & Super Admin)
 */

$role = strtolower(trim($_GET['role'] ?? 'operator'));
$autoPrint = isset($_GET['print']) && $_GET['print'] == '1';

// Configure content based on role
switch ($role) {
    case 'leader':
    case 'supervisor':
    case 'spv':
        $roleKey = 'leader';
        $docTitle = "Buku Panduan E-Cuti: Leader & Supervisor Departemen";
        $roleName = "Leader & Supervisor";
        $badgeColor = "#34c759";
        $levelDesc = "Level 3 - 4 (Hierarki Persetujuan Tahap 1 & Pengajuan Cuti Khusus)";
        break;

    case 'manager':
        $roleKey = 'manager';
        $docTitle = "Buku Panduan E-Cuti: Plant / Operational Manager";
        $roleName = "Plant Manager";
        $badgeColor = "#ff9500";
        $levelDesc = "Level 5 - 6 (Persetujuan Tahap 2 Lintas 15 Departemen)";
        break;

    case 'hrd':
    case 'admin':
    case 'superadmin':
        $roleKey = 'hrd';
        $docTitle = "Buku Panduan E-Cuti: HRD & Super Admin";
        $roleName = "HRD & Super Admin";
        $badgeColor = "#007aff";
        $levelDesc = "Level 7 - 8 (Persetujuan Final Tahap 3, Pemotongan Kuota & Manajemen Karyawan)";
        break;

    case 'operator':
    case 'staff':
    default:
        $roleKey = 'operator';
        $docTitle = "Buku Panduan E-Cuti: Operator Produksi & Staff";
        $roleName = "Operator & Staff";
        $badgeColor = "#64748b";
        $levelDesc = "Level 1 - 2 (Pengajuan Cuti Online & Pelacakan Status Realtime)";
        break;
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($docTitle) ?> - PT. Nakakin Indonesia</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 20mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #1e293b;
            background: #f8fafc;
            margin: 0;
            padding: 30px 20px;
        }
        .container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px 48px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 20px;
            margin-bottom: 28px;
        }
        .header-logo h1 {
            font-size: 20px;
            margin: 0 0 4px;
            color: #0f172a;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header-logo p {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }
        .role-badge {
            display: inline-block;
            background: <?= $badgeColor ?>;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 20px;
            text-transform: uppercase;
        }
        .nav-roles {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }
        .nav-roles a {
            text-decoration: none;
            font-size: 12px;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            color: #475569;
            background: #f1f5f9;
            transition: all 0.2s;
        }
        .nav-roles a.active {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
        }
        h2 {
            font-size: 16px;
            color: #0f172a;
            border-left: 4px solid #0284c7;
            padding-left: 12px;
            margin-top: 32px;
            margin-bottom: 16px;
        }
        .card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            margin-bottom: 16px;
        }
        .card-title {
            font-weight: bold;
            font-size: 14.5px;
            color: #0f172a;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .number-badge {
            background: #0284c7;
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
        }
        ol, ul {
            margin: 8px 0;
            padding-left: 24px;
            font-size: 13.5px;
            color: #334155;
        }
        li { margin-bottom: 6px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
        }
        th {
            background: #f1f5f9;
            font-weight: bold;
            color: #0f172a;
        }
        .alert {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            color: #1e40af;
            margin: 16px 0;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0284c7;
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            background: #0369a1;
            transform: translateY(-1px);
        }
        .mobile-sticky-bar {
            display: none;
        }
        @media (max-width: 640px) {
            body { padding: 12px 10px 80px 10px; }
            .container { padding: 20px 16px; border-radius: 12px; }
            .header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .header-logo h1 { font-size: 17px; }
            .btn-print.top-btn { display: none; }
            .mobile-sticky-bar {
                display: block;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-top: 1px solid #cbd5e1;
                padding: 12px 20px;
                box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
                z-index: 9999;
                text-align: center;
            }
            .mobile-sticky-bar .btn-print {
                width: 100%;
                justify-content: center;
                padding: 12px;
                font-size: 14px;
            }
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .container { box-shadow: none; border: none; padding: 0; }
            .nav-roles, .btn-print, .mobile-sticky-bar { display: none !important; }
        }
    </style>
</head>
<body>
    <!-- Mobile Sticky Action Bar -->
    <div class="mobile-sticky-bar">
        <button class="btn-print" onclick="window.print()">📥 Unduh / Simpan PDF (<?= htmlspecialchars($roleName) ?>)</button>
    </div>

    <div class="container">
        <!-- Top Action Bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
            <div class="nav-roles" style="margin-bottom: 0;">
                <span style="font-size: 12px; font-weight: bold; padding: 6px 0; margin-right: 6px;">Pilih Panduan:</span>
                <a href="?role=operator" class="<?= $roleKey === 'operator' ? 'active' : '' ?>">1. Operator & Staff</a>
                <a href="?role=leader" class="<?= $roleKey === 'leader' ? 'active' : '' ?>">2. Leader & Spv</a>
                <a href="?role=manager" class="<?= $roleKey === 'manager' ? 'active' : '' ?>">3. Plant Manager</a>
                <a href="?role=hrd" class="<?= $roleKey === 'hrd' ? 'active' : '' ?>">4. HRD & Admin</a>
            </div>
            <button class="btn-print top-btn" onclick="window.print()">📥 Cetak / Simpan PDF</button>
        </div>

        <!-- Document Header -->
        <div class="header">
            <div class="header-logo">
                <h1>PT. NAKAKIN INDONESIA</h1>
                <p>Sistem Informasi Pengajuan &amp; Persetujuan Cuti Digital (E-Cuti)</p>
            </div>
            <div>
                <span class="role-badge"><?= htmlspecialchars($roleName) ?></span>
            </div>
        </div>

        <div style="background: #f1f5f9; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px;">
            <strong style="color: #0f172a; font-size: 14px;"><?= htmlspecialchars($docTitle) ?></strong>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;"><?= htmlspecialchars($levelDesc) ?></div>
        </div>

        <?php if ($roleKey === 'operator'): ?>
            <!-- PANDUAN 1: OPERATOR & STAFF -->
            <h2>A. Alur Pengajuan Cuti (3 Tahap Persetujuan)</h2>
            <div class="alert">
                Sebagai <strong>Operator / Staff (Level 1–2)</strong>, pengajuan cuti Anda akan melewati 3 tahap verifikasi: <strong>Leader/Spv $\rightarrow$ Plant Manager $\rightarrow$ HRD Final</strong>.
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">1</span> Login &amp; Cek Kuota Cuti</div>
                <ul>
                    <li>Buka aplikasi <strong>E-Cuti</strong> di ponsel atau browser Anda.</li>
                    <li>Masukkan Email/NIK Anda dan kata sandi (default: <code>password123</code>).</li>
                    <li>Di halaman <strong>Dashboard</strong>, Anda dapat melihat <strong>Sisa Kuota Cuti</strong> dan masa kerja Anda.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">2</span> Membuat Pengajuan Cuti Baru</div>
                <ul>
                    <li>Pilih menu <strong>"Ajukan"</strong> di bar navigasi bawah.</li>
                    <li>Pilih <strong>Jenis Cuti</strong>:
                        <ul>
                            <li><strong>Cuti Tahunan (Reguler HRD):</strong> Mengurangi kuota tahunan.</li>
                            <li><strong>Sakit Surat Dokter:</strong> Wajib mengunggah foto surat keterangan dokter.</li>
                            <li><strong>Cuti Melahirkan / Izin Khusus:</strong> Hak khusus sesuai regulasi perusahaan.</li>
                        </ul>
                    </li>
                    <li>Tentukan <strong>Tanggal Mulai</strong> dan <strong>Tanggal Selesai</strong>.</li>
                    <li>Tuliskan <strong>Alasan Cuti</strong>, Alamat selama cuti, dan No. HP darurat.</li>
                    <li>Tekan tombol <strong>"Kirim Pengajuan Cuti"</strong>.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">3</span> Melacak Status Persetujuan &amp; Pembatalan</div>
                <ul>
                    <li>Buka menu <strong>"Riwayat"</strong> untuk melihat daftar cuti Anda.</li>
                    <li>Klik pada salah satu pengajuan untuk melihat <strong>Timeline Persetujuan 3 Tahap</strong>:
                        <ul>
                            <li>🟢 <strong>1. Supervisor / Leader</strong>: Sedang/Sudah diverifikasi atasan departemen.</li>
                            <li>🟡 <strong>2. Plant Manager</strong>: Sedang/Sudah disetujui Plant Manager.</li>
                            <li>🔵 <strong>3. HRD (Final)</strong>: Persetujuan akhir HRD dan pemotongan kuota cuti.</li>
                        </ul>
                    </li>
                    <li>Jika pengajuan masih berstatus <em>Menunggu (Pending)</em> dan ada perubahan rencana, Anda dapat menekan tombol merah <strong>"Batalkan Pengajuan Cuti"</strong>.</li>
                </ul>
            </div>

        <?php elseif ($roleKey === 'leader'): ?>
            <!-- PANDUAN 2: LEADER & SUPERVISOR -->
            <h2>A. Tanggung Jawab Persetujuan (Tahap 1 Departemen)</h2>
            <div class="alert">
                Sebagai <strong>Leader &amp; Supervisor (Level 3–4)</strong>, Anda berwenang memproses persetujuan tahap pertama bagi seluruh <strong>Operator &amp; Staff di departemen Anda</strong>.
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">1</span> Menerima Notifikasi Pengajuan Masuk</div>
                <ul>
                    <li>Saat Operator di departemen Anda mengajukan cuti, Anda akan menerima notifikasi dengan ikon lonceng berwarna merah di Dashboard.</li>
                    <li>Tekan ikon <strong>Lonceng Notifikasi</strong> atau buka tab <strong>"Persetujuan"</strong>.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">2</span> Memproses Persetujuan / Penolakan Langsung</div>
                <ul>
                    <li>Klik pada pengajuan cuti yang ingin ditinjau.</li>
                    <li>Di bagian bawah halaman <strong>Detail Pengajuan</strong>, terdapat kartu <strong>Tindakan Persetujuan</strong>:
                        <ul>
                            <li>🟢 <strong>Tombol [Setujui]</strong>: Menyetujui cuti operator dan meneruskannya ke tahap <strong>Plant Manager</strong>. Anda dapat menambahkan catatan opsional.</li>
                            <li>🔴 <strong>Tombol [Tolak]</strong>: Menolak pengajuan cuti dengan wajib mengisi alasan penolakan.</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">3</span> Alur Saat Leader / Supervisor Mengajukan Cuti Sendiri</div>
                <ul>
                    <li>Ketika Anda (Leader/Spv) mengajukan cuti pribadi, sistem secara otomatis <strong>mem-bypass tahap Spv</strong> dan langsung mengirimkan pengajuan ke <strong>Plant Manager (Tahap 1)</strong>, kemudian ke <strong>HRD (Tahap 2)</strong>.</li>
                </ul>
            </div>

        <?php elseif ($roleKey === 'manager'): ?>
            <!-- PANDUAN 3: PLANT MANAGER -->
            <h2>A. Wewenang Persetujuan Plant Manager (Tahap 2 Lintas 15 Departemen)</h2>
            <div class="alert">
                Sebagai <strong>Plant / Operational Manager (Level 5–6)</strong>, Anda memiliki wewenang operasional penuh untuk menyetujui pengajuan cuti dari <strong>seluruh 15 departemen perusahaan</strong> setelah disetujui Leader/Spv.
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">1</span> Notifikasi &amp; Daftar Antrean Manager</div>
                <ul>
                    <li>Semua pengajuan cuti yang telah di-ACC oleh Leader/Supervisor di 15 departemen akan otomatis masuk ke antrean Anda (<code>pending_manager</code>).</li>
                    <li>Pengajuan dari <strong>Leader &amp; Supervisor</strong> langsung masuk ke antrean Anda tanpa melalui tahap Spv.</li>
                    <li>Jumlah antrean persetujuan dapat dipantau langsung pada badge lonceng dan menu Persetujuan.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">2</span> Memberikan Persetujuan Operasional</div>
                <ul>
                    <li>Buka menu <strong>"Persetujuan"</strong> atau klik notifikasi pengajuan.</li>
                    <li>Periksa alasan, jadwal tanggal pelaksanaan, dan beban kerja departemen terkait.</li>
                    <li>Tekan 🟢 <strong>[Setujui]</strong> untuk meneruskan pengajuan ke <strong>HRD</strong> untuk validasi kuota dan persetujuan final.</li>
                    <li>Tekan 🔴 <strong>[Tolak]</strong> jika jadwal cuti bentrok dengan jadwal produksi mendesak.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">3</span> Monitoring Riwayat Cuti Seluruh Karyawan</div>
                <ul>
                    <li>Di menu <strong>"Riwayat"</strong>, gunakan tombol switch <strong>[Semua Karyawan]</strong> untuk memantau status cuti seluruh tim dari ke-15 departemen.</li>
                </ul>
            </div>

        <?php elseif ($roleKey === 'hrd'): ?>
            <!-- PANDUAN 4: HRD & SUPER ADMIN -->
            <h2>A. Wewenang Final HRD &amp; Manajemen Kuota (Tahap 3 Final)</h2>
            <div class="alert">
                Sebagai <strong>HRD &amp; Super Admin (Level 7–8)</strong>, Anda memegang persetujuan final yang secara otomatis <strong>memotong saldo kuota cuti tahunan</strong> dan memiliki hak monitoring penuh ke seluruh 15 departemen.
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">1</span> Monitoring Realtime &amp; Persetujuan Final</div>
                <ul>
                    <li><strong>Live Monitoring:</strong> Lonceng notifikasi dan dashboard HRD menampilkan seluruh permohonan cuti aktif di 15 departemen (baik di level Spv, Manager, maupun Final HRD).</li>
                    <li><strong>Persetujuan Final:</strong> Saat pengajuan telah disetujui Plant Manager (<code>pending_hrd</code>), tombol 🟢 <strong>[Setujui]</strong> akan aktif bagi HRD.</li>
                    <li>Ketika HRD menekan <strong>Setujui</strong>, sistem otomatis:
                        <ul>
                            <li>Mengubah status menjadi <code>approved</code>.</li>
                            <li>Memotong kuota cuti karyawan secara realtime di database.</li>
                            <li>Mencatat mutasi pengurangan di tabel riwayat kuota (audit log).</li>
                        </ul>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">2</span> Manajemen &amp; Pendaftaran Karyawan Baru</div>
                <ul>
                    <li>Buka menu <strong>"Data Karyawan"</strong> di Dashboard atau menu Pengaturan.</li>
                    <li>Anda dapat melihat daftar seluruh pegawai, mencari berdasarkan nama/NIK, dan memfilter per departemen.</li>
                    <li>Tekan tombol <strong>"+ Tambah Karyawan"</strong> untuk mendaftarkan karyawan baru dengan menentukan NIK, Nama, Departemen, Jabatan/Role, dan Kuota Cuti Awal.</li>
                </ul>
            </div>

            <div class="card">
                <div class="card-title"><span class="number-badge">3</span> Papan Kehadiran Live (Public Board)</div>
                <ul>
                    <li>Akses menu <strong>"Papan Live"</strong> di Dashboard untuk menampilkan display monitor/TV operasional yang menunjukkan siapa saja karyawan yang sedang cuti/izin hari ini di tiap departemen.</li>
                </ul>
            </div>
        <?php endif; ?>

        <h2>B. Ringkasan Matriks Alur Hierarki Persetujuan (3-Tier)</h2>
        <table>
            <thead>
                <tr>
                    <th>Jabatan Pemohon</th>
                    <th>Jumlah Tahap</th>
                    <th>Tahap 1</th>
                    <th>Tahap 2</th>
                    <th>Tahap 3 (Final)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Operator / Staff</strong> (Level 1–2)</td>
                    <td><span style="font-weight: bold; color: #0284c7;">3 Tahap</span></td>
                    <td>Leader / Supervisor Dept</td>
                    <td>Plant Manager</td>
                    <td>HRD (Potong Kuota)</td>
                </tr>
                <tr>
                    <td><strong>Leader / Supervisor</strong> (Level 3–4)</td>
                    <td><span style="font-weight: bold; color: #0284c7;">2 Tahap</span></td>
                    <td>Plant Manager</td>
                    <td>HRD (Potong Kuota)</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td><strong>Plant Manager</strong> (Level 5–6)</td>
                    <td><span style="font-weight: bold; color: #0284c7;">1 Tahap</span></td>
                    <td>HRD (Potong Kuota)</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
                <tr>
                    <td><strong>HRD / Super Admin</strong> (Level 7–8)</td>
                    <td><span style="font-weight: bold; color: #16a34a;">Auto</span></td>
                    <td>Auto-Approved</td>
                    <td>-</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            &copy; 2026 PT. Nakakin Indonesia &bull; Kawasan Industri KIIC, Karawang Barat &bull; Versi Sistem: 2.0.0
        </div>
    </div>

    <?php if ($autoPrint): ?>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 600);
        });
    </script>
    <?php endif; ?>
</body>
</html>
