<?php
/**
 * Team Leaves Monitoring View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Monitoring Cuti Departemen';
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

$sql = "
    SELECT lr.*, 
           e.nik, e.nama_lengkap, e.tanggal_masuk,
           p.nama_jabatan,
           d.nama_dept,
           lt.nama_cuti,
           ap.nama_lengkap as nama_atasan
    FROM pengajuan_cuti lr
    JOIN karyawan e ON lr.employee_id = e.id
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
    LEFT JOIN karyawan ap ON lr.approved_by = ap.id
";

if ($isHRD || $isManager) {
    // HRD & Plant Manager can monitor across all 15 departments
    $sql .= " ORDER BY lr.tanggal_mulai DESC";
    $stmt = $pdo->query($sql);
} else {
    // Leader / Spv monitors their own department
    $sql .= " WHERE e.departemen_id = ? ORDER BY lr.tanggal_mulai DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$deptId]);
}
$teamLeaves = $stmt->fetchAll();
?>

<div class="space-y-6">

    <!-- Table Card with Integrated Header -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-200/60 shadow-2xs">
                    <i class="fa-solid fa-users-viewfinder"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Monitoring Jadwal Cuti Departemen</h3>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block">Pantau ketersediaan dan jadwal cuti anggota tim untuk kelancaran operasional</p>
                </div>
            </div>
            <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                Total: <?= count($teamLeaves) ?> Data
            </span>
        </div>

        <div class="p-3 sm:p-5">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-8">No</th>
                            <th>Karyawan & Jabatan</th>
                            <th>Permohonan & Periode Cuti</th>
                            <th class="text-center">Total Durasi</th>
                            <th class="text-center">Status</th>
                            <th class="text-right pr-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($teamLeaves as $leave): 
                            $tglMulaiFormatted = date('d M Y', strtotime($leave['tanggal_mulai']));
                            $tglSelesaiFormatted = date('d M Y', strtotime($leave['tanggal_selesai']));
                            $periodeText = ($leave['tanggal_mulai'] === $leave['tanggal_selesai']) ? $tglMulaiFormatted : "$tglMulaiFormatted - $tglSelesaiFormatted";
                            $durasiText = ($leave['total_hari'] == 0.5) ? '0.5 Hari' : $leave['total_hari'] . ' Hari';
                        ?>
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="text-center py-2.5">
                                    <span class="w-5 h-5 rounded-md bg-slate-100 inline-flex items-center justify-center text-[10.5px] text-slate-500 font-bold">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td class="py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                            <?= strtoupper(substr($leave['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-slate-900 text-xs truncate max-w-[170px]"><?= htmlspecialchars($leave['nama_lengkap']) ?></div>
                                            <div class="text-[10.5px] text-slate-500 font-medium">
                                                NIK: <strong class="text-slate-700"><?= htmlspecialchars($leave['nik']) ?></strong> &bull; <?= htmlspecialchars($leave['nama_jabatan']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <div class="space-y-0.5">
                                        <div class="font-bold text-slate-800 text-xs"><?= htmlspecialchars($leave['nama_cuti']) ?></div>
                                        <div class="text-[10.5px] text-slate-500 flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-[10px] text-slate-400"></i>
                                            <span><?= $periodeText ?></span>
                                            <?php if (!empty($leave['shift'])): ?>
                                                <span class="text-slate-400">&bull;</span>
                                                <span class="text-slate-600 font-medium"><?= htmlspecialchars($leave['shift']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-black text-[11px] border border-blue-200/60">
                                        <?= $durasiText ?>
                                    </span>
                                </td>
                                <td class="text-center py-2.5 whitespace-nowrap">
                                    <?= getStatusBadge($leave['status']) ?>
                                </td>
                                <td class="py-2.5 text-right pr-4 whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1 justify-end">
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition flex items-center justify-center shadow-2xs" title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-[11px]"></i>
                                        </a>
                                        <?php if ($leave['status'] === 'approved'): ?>
                                            <a href="<?= BASE_URL ?>/index.php?page=leave-print&id=<?= $leave['id'] ?>" target="_blank" class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 transition flex items-center justify-center shadow-2xs" title="Cetak Surat">
                                                <i class="fa-solid fa-print text-[11px]"></i>
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
