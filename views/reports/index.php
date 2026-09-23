<?php
/**
 * Reports & Analytics View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Laporan & Rekapitulasi Cuti';
require_once __DIR__ . '/../layouts/header.php';

requireRole('admin');

$pdo = getDbConnection();

$startDate = cleanInput($_GET['start_date'] ?? date('Y-01-01'));
$endDate = cleanInput($_GET['end_date'] ?? date('Y-12-31'));
$deptId = (int)($_GET['dept_id'] ?? 0);
$typeId = (int)($_GET['type_id'] ?? 0);
$status = cleanInput($_GET['status'] ?? 'all');

$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();
$leaveTypes = $pdo->query("SELECT * FROM jenis_cuti ORDER BY id ASC")->fetchAll();

$sql = "
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.tanggal_masuk,
           d.nama_dept, d.kode_dept,
           p.nama_jabatan,
           lt.nama_cuti, lt.potong_kuota,
           ap.nama_lengkap as nama_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    WHERE lr.tanggal_mulai >= ? AND lr.tanggal_selesai <= ?
";

$params = [$startDate, $endDate];

if ($deptId > 0) {
    $sql .= " AND e.departemen_id = ? ";
    $params[] = $deptId;
}

if ($typeId > 0) {
    $sql .= " AND lr.leave_type_id = ? ";
    $params[] = $typeId;
}

if ($status !== 'all') {
    $sql .= " AND lr.status = ? ";
    $params[] = $status;
}

$sql .= " ORDER BY lr.tanggal_mulai DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll();

$totalDaysApproved = 0;
$countApproved = 0;
$countPending = 0;
$countRejected = 0;

foreach ($reports as $r) {
    if ($r['status'] === 'approved') {
        $countApproved++;
        $totalDaysApproved += $r['total_hari'];
    } elseif ($r['status'] === 'pending') {
        $countPending++;
    } elseif ($r['status'] === 'rejected') {
        $countRejected++;
    }
}
?>

<div class="space-y-6">

    <!-- Filter Card with Integrated Export Buttons -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft no-print">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-200/60 shadow-2xs">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Filter Rekapitulasi Cuti Karyawan</h3>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block">Pilih parameter periode tanggal, departemen, jenis cuti, dan status pengajuan</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= BASE_URL ?>/index.php?page=report-export&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&dept_id=<?= $deptId ?>&type_id=<?= $typeId ?>&status=<?= $status ?>" 
                   class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export Excel</span>
                </a>
                <button onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-extrabold text-xs shadow-2xs transition flex items-center gap-1.5">
                    <i class="fa-solid fa-print text-slate-500"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>

        <form method="GET" action="<?= BASE_URL ?>/index.php" class="space-y-4">
            <input type="hidden" name="page" value="reports">
            
            <!-- Quick Preset Year Buttons -->
            <div class="flex items-center gap-2 flex-wrap text-xs pb-1 border-b border-slate-100">
                <span class="text-slate-400 font-bold text-[11px]"><i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Periode Cepat:</span>
                <button type="button" onclick="setFilterYear(<?= date('Y') ?>)" class="px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] border border-blue-200 transition">
                    Tahun Ini (<?= date('Y') ?>)
                </button>
                <button type="button" onclick="setFilterYear(<?= date('Y') - 1 ?>)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] border border-slate-200 transition">
                    Tahun Lalu (<?= date('Y') - 1 ?>)
                </button>
                <button type="button" onclick="setFilterYear(<?= date('Y') - 2 ?>)" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] border border-slate-200 transition">
                    Tahun <?= date('Y') - 2 ?>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" id="filter_start_date" name="start_date" value="<?= $startDate ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
                    <input type="date" id="filter_end_date" name="end_date" value="<?= $endDate ?>" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Departemen</label>
                    <select name="dept_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="0">Semua Departemen</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= $d['id'] ?>" <?= $deptId === (int)$d['id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['nama_dept']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Jenis Cuti</label>
                    <select name="type_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="0">Semua Jenis</option>
                        <?php foreach ($leaveTypes as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $typeId === (int)$t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['nama_cuti']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <a href="<?= BASE_URL ?>/index.php?page=reports" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">Reset</a>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm">Terapkan Filter</button>
            </div>
        </form>
    </div>

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase">Total Pengajuan</span>
                <div class="text-2xl font-black text-slate-900"><?= count($reports) ?></div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base"><i class="fa-solid fa-list-check"></i></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase">Total Hari Disetujui</span>
                <div class="text-2xl font-black text-emerald-600"><?= $totalDaysApproved ?> <span class="text-xs text-slate-400">Hari</span></div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base"><i class="fa-solid fa-calendar-check"></i></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase">Pending Approval</span>
                <div class="text-2xl font-black text-amber-600"><?= $countPending ?></div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base"><i class="fa-solid fa-hourglass-start"></i></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase">Ditolak</span>
                <div class="text-2xl font-black text-rose-600"><?= $countRejected ?></div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base"><i class="fa-solid fa-ban"></i></div>
        </div>
    </div>

    <!-- Report Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/40">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold border border-blue-100">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <h3 class="text-sm font-extrabold text-slate-900">Data Rekapitulasi Penggunaan Cuti</h3>
            </div>
            <span class="px-3.5 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                Periode: <?= formatTanggalIndo($startDate) ?> - <?= formatTanggalIndo($endDate) ?>
            </span>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-12">No</th>
                            <th class="min-w-[150px]">No. Surat</th>
                            <th class="min-w-[180px]">Karyawan & NIK</th>
                            <th class="min-w-[140px]">Departemen</th>
                            <th class="min-w-[200px]">Jenis Cuti</th>
                            <th class="min-w-[160px]">Periode Tanggal</th>
                            <th class="min-w-[80px] text-center">Hari</th>
                            <th class="min-w-[120px] text-center">Status</th>
                            <th class="min-w-[140px]">Pemeriksa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($reports as $r): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 inline-flex items-center justify-center text-[11px] text-slate-600 font-black">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap font-mono font-black text-blue-600">
                                    <span class="bg-blue-50/80 px-2 py-0.5 rounded-lg border border-blue-100"><?= htmlspecialchars($r['nomor_surat']) ?></span>
                                </td>
                                <td>
                                    <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($r['nama_lengkap']) ?></div>
                                    <div class="text-[10.5px] text-slate-400 font-mono">NIK: <strong class="text-slate-700"><?= htmlspecialchars($r['nik']) ?></strong> &bull; <?= htmlspecialchars($r['nama_jabatan']) ?></div>
                                </td>
                                <td>
                                    <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($r['nama_dept']) ?></div>
                                </td>
                                <td class="whitespace-nowrap"><?= renderLeaveTypeDisplay($r['nama_cuti'], $r['potong_kuota'] ?? null) ?></td>
                                <td class="whitespace-nowrap font-bold text-slate-800">
                                    <?= formatTanggalIndo($r['tanggal_mulai']) ?> s/d <?= formatTanggalIndo($r['tanggal_selesai']) ?>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border border-blue-200/80 font-black text-xs shadow-2xs"><?= $r['total_hari'] ?> Hari</span>
                                </td>
                                <td class="text-center whitespace-nowrap"><?= getStatusBadge($r['status']) ?></td>
                                <td class="text-slate-700 font-semibold whitespace-nowrap"><?= htmlspecialchars($r['nama_atasan'] ?: '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
function setFilterYear(year) {
    document.getElementById('filter_start_date').value = year + '-01-01';
    document.getElementById('filter_end_date').value = year + '-12-31';
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
