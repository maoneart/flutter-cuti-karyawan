<?php
/**
 * Leave Approvals View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Persetujuan & Monitoring Cuti';
require_once __DIR__ . '/../layouts/header.php';

$userRole = strtolower($currentUser['role'] ?? '');
$userLevel = (int)($currentUser['level_hierarki'] ?? 1);
$isHRD = in_array($userRole, ['admin', 'superadmin', 'hrd']) || $userLevel >= 7;
$isManager = $userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6);
$isSpv = in_array($userRole, ['supervisor', 'leader', 'atasan']) || ($userLevel >= 3 && $userLevel <= 4);
$isApprover = $isHRD || $isManager || $isSpv;

if (!$isApprover) {
    echo '<div class="p-6 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 font-bold text-sm">Akses ditolak! Halaman ini hanya untuk Leader, Manager, dan HRD.</div>';
    require_once __DIR__ . '/../layouts/footer.php';
    exit;
}

$pdo = getDbConnection();
$deptId = $currentUser['departemen_id'];
$statusFilter = cleanInput($_GET['status'] ?? 'pending');

$sql = "
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.email, e.no_hp, e.sisa_cuti, e.kuota_cuti, e.tanggal_masuk, e.departemen_id,
           d.nama_dept, d.kode_dept,
           p.nama_jabatan, p.level_hierarki as employee_level,
           lt.nama_cuti, lt.potong_kuota,
           ap.nama_lengkap as nama_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
    WHERE 1=1
";

$params = [];

if ($isHRD) {
    // HRD can view and monitor all requests across all 15 departments
    if ($statusFilter === 'pending') {
        $sql .= " AND lr.status = 'pending' ";
    } elseif ($statusFilter !== 'all') {
        $sql .= " AND lr.status = ? ";
        $params[] = $statusFilter;
    }
} elseif ($isManager) {
    // Manager can view across all departments, excluding self
    $sql .= " AND lr.employee_id != ? ";
    $params[] = $currentUser['id'];
    if ($statusFilter === 'pending') {
        $sql .= " AND lr.status = 'pending' ";
    } elseif ($statusFilter !== 'all') {
        $sql .= " AND lr.status = ? ";
        $params[] = $statusFilter;
    }
} else {
    // Leader / Spv for their own department
    $sql .= " AND e.departemen_id = ? AND e.id != ? ";
    $params[] = $deptId;
    $params[] = $currentUser['id'];
    if ($statusFilter === 'pending') {
        $sql .= " AND lr.status = 'pending' ";
    } elseif ($statusFilter !== 'all') {
        $sql .= " AND lr.status = ? ";
        $params[] = $statusFilter;
    }
}

$sql .= " ORDER BY CASE 
            WHEN lr.approval_step = 'pending_hrd' AND " . ($isHRD ? "1=1" : "1=0") . " THEN 1 
            WHEN lr.approval_step = 'pending_manager' AND " . ($isManager ? "1=1" : "1=0") . " THEN 1 
            WHEN lr.approval_step = 'pending_spv' AND " . ($isSpv ? "1=1" : "1=0") . " THEN 1 
            WHEN lr.status = 'pending' THEN 2 
            ELSE 3 
          END, lr.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();
?>

<div class="space-y-6">

    <!-- Table Card with Integrated Filter Header -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-sm font-bold border border-amber-200/60 shadow-2xs">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">
                        <?= $isHRD ? 'Persetujuan & Monitoring Cuti (Seluruh 15 Departemen)' : ($isManager ? 'Persetujuan Cuti Plant Manager' : 'Permohonan Cuti Anggota Tim (' . htmlspecialchars($currentUser['nama_dept'] ?? 'Departemen') . ')') ?>
                    </h3>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block">
                        <?= $isHRD ? 'Pantau seluruh tahapan cuti karyawan dan lakukan verifikasi final ketika giliran approval HRD tiba' : 'Verifikasi dan berikan persetujuan izin cuti untuk anggota tim Anda' ?>
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap">
                <!-- Filter Status Pills -->
                <div class="inline-flex p-1 bg-white border border-slate-200/80 rounded-2xl shadow-2xs text-xs font-bold gap-1">
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=pending" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                        Menunggu
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=approved" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'approved' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                        Disetujui
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=rejected" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'rejected' ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                        Ditolak
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals&status=all" 
                       class="px-3 py-1 rounded-xl transition <?= $statusFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
                        Semua
                    </a>
                </div>

                <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                    Total: <?= count($requests) ?> Data
                </span>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-12">No</th>
                            <th class="min-w-[170px]">Karyawan Pemohon</th>
                            <th class="min-w-[150px]">Departemen / Jabatan</th>
                            <th class="min-w-[180px]">Jenis Cuti</th>
                            <th class="min-w-[160px]">Tanggal & Hari</th>
                            <th class="min-w-[110px]">Sisa Kuota</th>
                            <th class="min-w-[130px] text-center">Tahapan / Status</th>
                            <th class="min-w-[150px] text-left">Aksi Persetujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($requests as $req): 
                            $step = $req['approval_step'] ?? 'pending_spv';
                            $canApproveRow = false;
                            $stepBadge = '';

                            if ($req['status'] === 'pending') {
                                if ($isHRD && $step === 'pending_hrd') {
                                    $canApproveRow = true;
                                } elseif ($isManager && $step === 'pending_manager') {
                                    $canApproveRow = true;
                                } elseif ($isSpv && $step === 'pending_spv' && (int)$req['departemen_id'] === (int)$deptId && (int)$req['employee_id'] !== (int)$currentUser['id']) {
                                    $canApproveRow = true;
                                }

                                if ($step === 'pending_spv') {
                                    $stepBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold"><i class="fa-solid fa-clock"></i> Review Leader</span>';
                                } elseif ($step === 'pending_manager') {
                                    $stepBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold"><i class="fa-solid fa-clock"></i> Review Manager</span>';
                                } elseif ($step === 'pending_hrd') {
                                    $stepBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 border border-purple-200 text-[10px] font-bold"><i class="fa-solid fa-clock"></i> Menunggu HRD</span>';
                                }
                            } else {
                                $stepBadge = getStatusBadge($req['status']);
                            }
                        ?>
                            <tr>
                                <td class="text-center">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 inline-flex items-center justify-center text-[11px] text-slate-600 font-black">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                            <?= strtoupper(substr($req['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($req['nama_lengkap']) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono">NIK: <strong class="text-slate-700"><?= htmlspecialchars($req['nik']) ?></strong></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($req['nama_dept']) ?></div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5"><?= htmlspecialchars($req['nama_jabatan']) ?></div>
                                </td>
                                <td class="whitespace-nowrap">
                                    <?= renderLeaveTypeDisplay($req['nama_cuti'], $req['potong_kuota']) ?>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-slate-800"><?= formatTanggalIndo($req['tanggal_mulai']) ?> s/d <?= formatTanggalIndo($req['tanggal_selesai']) ?></div>
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-black text-[11px] mt-1 inline-block border border-blue-200/60"><?= $req['total_hari'] ?> Hari Kerja</span>
                                </td>
                                <td>
                                    <div class="font-black text-slate-900 text-xs"><?= $req['sisa_cuti'] ?> Hari</div>
                                    <div class="text-[10px] text-slate-400 font-medium">Masa: <?= hitungMasaKerja($req['tanggal_masuk']) ?></div>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <?= $stepBadge ?>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <?php if ($canApproveRow): ?>
                                            <button type="button" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition transform active:scale-95 flex items-center gap-1" onclick="confirmApprove(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>')" title="Setujui Cuti">
                                                <i class="fa-solid fa-check"></i> Setuju
                                            </button>
                                            <button type="button" class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-md shadow-rose-600/20 transition transform active:scale-95 flex items-center gap-1" onclick="confirmReject(<?= $req['id'] ?>, '<?= addslashes(htmlspecialchars($req['nama_lengkap'])) ?>')" title="Tolak Cuti">
                                                <i class="fa-solid fa-xmark"></i> Tolak
                                            </button>
                                        <?php elseif ($req['status'] === 'pending'): ?>
                                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-500 font-bold text-[11px] border border-slate-200 flex items-center gap-1" title="Menunggu giliran approval">
                                                <i class="fa-solid fa-hourglass-start text-slate-400"></i> Pantau
                                            </span>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $req['id'] ?>" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center justify-center shadow-2xs" title="Lihat Detail & Bukti">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
                                        <?php if ($req['status'] === 'approved'): ?>
                                            <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $req['id'] ?>" target="_blank" class="w-8 h-8 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition flex items-center justify-center shadow-2xs" title="Cetak Surat">
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
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
