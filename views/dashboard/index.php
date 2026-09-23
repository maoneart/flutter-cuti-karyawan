<?php
/**
 * Dashboard View (Tailwind CSS Bento Grid Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Dashboard Utama';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$userId = $currentUser['id'];
$userRole = $currentUser['role'];
$deptId = $currentUser['departemen_id'];

// 1. Personal Stats
$stmtPersonal = $pdo->prepare("
    SELECT 
        COUNT(*) as total_pengajuan,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as total_approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as total_rejected
    FROM pengajuan_cuti 
    WHERE employee_id = ?
");
$stmtPersonal->execute([$userId]);
$myStats = $stmtPersonal->fetch();

// 2. Personal Recent Leaves
$stmtMyLeaves = $pdo->prepare("
    SELECT lr.*, lt.nama_cuti, lt.potong_kuota
    FROM pengajuan_cuti lr
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    WHERE lr.employee_id = ?
    ORDER BY lr.created_at DESC
    LIMIT 5
");
$stmtMyLeaves->execute([$userId]);
$myRecentLeaves = $stmtMyLeaves->fetchAll();

// 3. Pending Approvals for Atasan
$deptPendingLeaves = [];
if (in_array($userRole, ['atasan', 'admin'])) {
    if ($userRole === 'admin') {
        $stmtDeptPending = $pdo->query("
            SELECT lr.*, e.nama_lengkap, e.nik, d.nama_dept, p.nama_jabatan, lt.nama_cuti
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan p ON e.jabatan_id = p.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            WHERE lr.status = 'pending'
            ORDER BY lr.created_at ASC
            LIMIT 5
        ");
    } else {
        $stmtDeptPending = $pdo->prepare("
            SELECT lr.*, e.nama_lengkap, e.nik, d.nama_dept, p.nama_jabatan, lt.nama_cuti
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan p ON e.jabatan_id = p.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            WHERE lr.status = 'pending' AND e.departemen_id = ? AND e.id != ?
            ORDER BY lr.created_at ASC
            LIMIT 5
        ");
        $stmtDeptPending->execute([$deptId, $userId]);
    }
    $deptPendingLeaves = $stmtDeptPending->fetchAll();
}

// 4. Admin HRD Overview Metrics
if ($userRole === 'admin') {
    $totalKaryawan = $pdo->query("SELECT COUNT(*) FROM karyawan WHERE status_aktif = 'Aktif'")->fetchColumn();
    $totalCutiBulanIni = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())")->fetchColumn();
    $totalPendingAll = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'pending'")->fetchColumn();
    $cutiHariIni = $pdo->query("SELECT COUNT(*) FROM pengajuan_cuti WHERE status = 'approved' AND CURRENT_DATE() BETWEEN tanggal_mulai AND tanggal_selesai")->fetchColumn();

    $stmtChartDept = $pdo->query("
        SELECT d.nama_dept, COUNT(lr.id) as total_cuti
        FROM departemen d
        LEFT JOIN karyawan e ON d.id = e.departemen_id
        LEFT JOIN pengajuan_cuti lr ON e.id = lr.employee_id AND lr.status = 'approved'
        GROUP BY d.id, d.nama_dept
    ");
    $chartDeptData = $stmtChartDept->fetchAll();
}
?>

<div class="space-y-6">

    <!-- Hero Bento Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#090e1a] via-[#1e1b4b] to-[#1e3a8a] text-white p-6 sm:p-8 shadow-xl border border-slate-800">
        <!-- Background Ambient Glow -->
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-rose-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/10 text-xs font-semibold text-blue-200">
                    <i class="fa-solid fa-building text-rose-400"></i>
                    <span><?= htmlspecialchars($currentUser['nama_dept']) ?></span>
                    <span class="text-white/40">&bull;</span>
                    <span><?= htmlspecialchars($currentUser['nama_jabatan']) ?></span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black font-display tracking-tight text-white">
                    Selamat Datang, <?= htmlspecialchars($currentUser['nama_lengkap']) ?>! 👋
                </h2>
                <p class="text-slate-300 text-xs sm:text-sm max-w-xl font-medium leading-relaxed">
                    <?= htmlspecialchars($appSettings['nama_aplikasi']) ?> <?= htmlspecialchars($appSettings['nama_perusahaan']) ?>. Pantau jatah kuota cuti tahunan dan ajukan permohonan izin dengan cepat dan mudah.
                </p>
            </div>
            
            <div class="flex-shrink-0">
                <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
                   class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-sm shadow-lg shadow-rose-600/40 hover:shadow-rose-600/60 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-200">
                    <i class="fa-solid fa-calendar-plus text-base"></i>
                    <span>Ajukan Cuti Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Personal Stats Bento Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Sisa Kuota Cuti -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sisa Kuota Cuti</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                <?= (int)$currentUser['sisa_cuti'] ?> <span class="text-sm font-semibold text-slate-400">Hari</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span>Total: <?= (int)$currentUser['kuota_cuti'] ?> Hari</span>
                <span class="text-emerald-600 font-bold">Siap Pakai</span>
            </div>
        </div>

        <!-- Cuti Terpakai -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Cuti Terpakai</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-plane-departure"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                <?= (int)$currentUser['cuti_terpakai'] ?> <span class="text-sm font-semibold text-slate-400">Hari</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span>Periode: <?= date('Y') ?></span>
                <span class="text-amber-600 font-bold"><?= (int)$currentUser['cuti_terpakai'] ?> hari dipakai</span>
            </div>
        </div>

        <!-- Masa Kerja -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Masa Kerja</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
            </div>
            <div class="text-lg font-black text-purple-700 leading-snug truncate" title="<?= hitungMasaKerja($currentUser['tanggal_masuk']) ?>">
                <?= hitungMasaKerja($currentUser['tanggal_masuk']) ?>
            </div>
            <div class="mt-2 text-xs text-slate-500 font-medium pt-2 border-t border-slate-100 truncate">
                Join: <?= formatTanggalIndo($currentUser['tanggal_masuk']) ?>
            </div>
        </div>

        <!-- Total Pengajuan -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft hover:shadow-md transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pengajuan</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg group-hover:scale-110 transition">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="text-3xl font-black text-slate-900 tracking-tight">
                <?= (int)$myStats['total_pengajuan'] ?> <span class="text-sm font-semibold text-slate-400">Kali</span>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span class="text-emerald-600 font-bold"><?= (int)$myStats['total_approved'] ?> Disetujui</span>
                <span class="text-amber-600 font-bold"><?= (int)$myStats['total_pending'] ?> Pending</span>
            </div>
        </div>
    </div>

    <!-- HRD Quick Metrics (If Admin) -->
    <?php if ($userRole === 'admin'): ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border-l-4 border-rose-600 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Total Karyawan</div>
                    <div class="text-xl font-black text-slate-900"><?= $totalKaryawan ?> Orang</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border-l-4 border-blue-600 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Pengajuan Bulan Ini</div>
                    <div class="text-xl font-black text-blue-600"><?= $totalCutiBulanIni ?> Pengajuan</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border-l-4 border-amber-500 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Menunggu Approval</div>
                    <div class="text-xl font-black text-amber-600"><?= $totalPendingAll ?> Pengajuan</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-hourglass-start"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-4 border-l-4 border-emerald-600 border-y border-r border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Sedang Cuti Hari Ini</div>
                    <div class="text-xl font-black text-emerald-600"><?= $cutiHariIni ?> Orang</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-clock"></i>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ATASAN / ADMIN APPROVAL QUEUE CARD -->
    <?php if (in_array($userRole, ['atasan', 'admin'])): ?>
        <div class="bg-white rounded-3xl border border-amber-200 shadow-soft overflow-hidden">
            <div class="p-5 sm:px-6 bg-gradient-to-r from-amber-50/80 to-orange-50/80 border-b border-amber-200/80 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900">
                            Pengajuan Cuti Menunggu Persetujuan Anda
                        </h3>
                        <p class="text-xs text-slate-500">Khusus anggota tim Departemen <?= htmlspecialchars($currentUser['nama_dept']) ?></p>
                    </div>
                </div>
                <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-xs font-bold text-slate-700 shadow-sm transition">
                    Lihat Semua (<?= count($deptPendingLeaves) ?>) &rarr;
                </a>
            </div>

            <div class="p-0">
                <?php if (empty($deptPendingLeaves)): ?>
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2 block"></i>
                        <p class="text-xs font-semibold text-slate-600">Semua permohonan cuti untuk departemen Anda sudah diproses!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3.5">Karyawan</th>
                                    <th class="px-4 py-3.5">Departemen</th>
                                    <th class="px-4 py-3.5">Jenis Cuti</th>
                                    <th class="px-4 py-3.5">Tanggal</th>
                                    <th class="px-4 py-3.5">Alasan</th>
                                    <th class="px-5 py-3.5 text-left">Aksi Persetujuan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($deptPendingLeaves as $req): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 font-extrabold flex items-center justify-center text-xs">
                                                    <?= strtoupper(substr($req['nama_lengkap'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-900"><?= htmlspecialchars($req['nama_lengkap']) ?></div>
                                                    <div class="text-[11px] text-slate-400">NIK: <?= htmlspecialchars($req['nik']) ?> &bull; <?= htmlspecialchars($req['nama_jabatan']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($req['nama_dept']) ?></div>
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <?= renderLeaveTypeDisplay($req['nama_cuti'], $req['potong_kuota'] ?? null) ?>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-semibold text-slate-700"><?= formatTanggalIndo($req['tanggal_mulai']) ?></div>
                                            <div class="text-blue-600 font-bold"><?= $req['total_hari'] ?> Hari Kerja</div>
                                        </td>
                                        <td class="px-4 py-3.5 max-w-xs truncate text-slate-600" title="<?= htmlspecialchars($req['alasan']) ?>">
                                            <?= htmlspecialchars($req['alasan']) ?>
                                        </td>
                                        <td class="px-5 py-3.5 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <button type="button" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1" onclick="confirmApprove(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>')">
                                                    <i class="fa-solid fa-check"></i> Approve
                                                </button>
                                                <button type="button" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition flex items-center gap-1" onclick="confirmReject(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>')">
                                                    <i class="fa-solid fa-xmark"></i> Reject
                                                </button>
                                                <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $req['id'] ?>" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center justify-center shadow-2xs" title="Lihat Detail">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Two Columns: Chart & Personal Leave History -->
    <div class="grid grid-cols-1 <?= $userRole === 'admin' ? 'lg:grid-cols-2' : '' ?> gap-6">
        
        <?php if ($userRole === 'admin'): ?>
            <!-- Department Leaves Chart -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-blue-600"></i> Pengajuan Cuti per Departemen
                    </h3>
                    <span class="text-xs font-semibold text-slate-400">Tahun <?= date('Y') ?></span>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <canvas id="deptLeaveChart" height="200"></canvas>
                </div>
            </div>
        <?php endif; ?>

        <!-- My Recent Leaves -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Pengajuan Cuti Saya
                </h3>
                <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua &rarr;</a>
            </div>

            <div class="p-0">
                <?php if (empty($myRecentLeaves)): ?>
                    <div class="text-center py-8 text-slate-400">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block"></i>
                        <p class="text-xs font-medium">Anda belum pernah mengajukan cuti.</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-extrabold uppercase tracking-wider border-b border-slate-100">
                                <tr>
                                    <th class="px-5 py-3">No. Surat</th>
                                    <th class="px-4 py-3">Jenis Cuti</th>
                                    <th class="px-4 py-3">Tanggal</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-5 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($myRecentLeaves as $leave): ?>
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="px-5 py-3 font-mono font-bold text-slate-900"><?= htmlspecialchars($leave['nomor_surat']) ?></td>
                                        <td class="px-4 py-3 whitespace-nowrap"><?= renderLeaveTypeDisplay($leave['nama_cuti'], $leave['potong_kuota'] ?? null) ?></td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-slate-800"><?= formatTanggalIndo($leave['tanggal_mulai']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= $leave['total_hari'] ?> Hari</div>
                                        </td>
                                        <td class="px-4 py-3"><?= getStatusBadge($leave['status']) ?></td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <div class="flex items-center gap-1.5">
                                                <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold flex items-center justify-center transition shadow-2xs" title="Lihat Detail">
                                                    <i class="fa-solid fa-eye text-xs"></i>
                                                </a>
                                                <?php if ($leave['status'] === 'approved'): ?>
                                                    <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 font-bold flex items-center justify-center transition shadow-2xs" title="Cetak Surat">
                                                        <i class="fa-solid fa-print text-xs"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php if ($userRole === 'admin' && !empty($chartDeptData)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('deptLeaveChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($chartDeptData, 'nama_dept')) ?>,
                datasets: [{
                    label: 'Cuti Disetujui',
                    data: <?= json_encode(array_column($chartDeptData, 'total_cuti')) ?>,
                    backgroundColor: '#3b82f6',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
