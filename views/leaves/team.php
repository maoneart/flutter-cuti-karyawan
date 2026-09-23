<?php
/**
 * Team Leaves Monitoring View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Monitoring Cuti Departemen';
require_once __DIR__ . '/../layouts/header.php';

if (!in_array($currentUser['role'], ['atasan', 'admin'])) {
    setFlash('error', 'Akses ditolak!');
    header('Location: ' . BASE_URL . '/index.php?page=dashboard');
    exit;
}

$pdo = getDbConnection();
$deptId = $currentUser['departemen_id'];

$stmt = $pdo->prepare("
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
    WHERE " . ($currentUser['role'] === 'admin' ? "1=1" : "e.departemen_id = ?") . "
    ORDER BY lr.tanggal_mulai DESC
");
if ($currentUser['role'] === 'admin') {
    $stmt->execute();
} else {
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

        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-12">No</th>
                            <th class="min-w-[170px]">Karyawan</th>
                            <th class="min-w-[130px]">Jabatan</th>
                            <th class="min-w-[200px]">Jenis Cuti</th>
                            <th class="min-w-[140px]">Mulai</th>
                            <th class="min-w-[140px]">Selesai</th>
                            <th class="min-w-[90px] text-center">Hari</th>
                            <th class="min-w-[120px] text-center">Status</th>
                            <th class="min-w-[80px] text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($teamLeaves as $leave): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 inline-flex items-center justify-center text-[11px] text-slate-600 font-black">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                            <?= strtoupper(substr($leave['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($leave['nama_lengkap']) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono">NIK: <strong class="text-slate-700"><?= htmlspecialchars($leave['nik']) ?></strong></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-slate-800 text-xs"><?= htmlspecialchars($leave['nama_jabatan']) ?></div>
                                </td>
                                <td class="whitespace-nowrap"><?= renderLeaveTypeDisplay($leave['nama_cuti'], $leave['potong_kuota'] ?? null) ?></td>
                                <td class="whitespace-nowrap font-bold text-slate-800"><?= formatTanggalIndo($leave['tanggal_mulai']) ?></td>
                                <td class="whitespace-nowrap font-bold text-slate-800"><?= formatTanggalIndo($leave['tanggal_selesai']) ?></td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border border-blue-200/80 font-black text-xs shadow-2xs"><?= $leave['total_hari'] ?> Hari</span>
                                </td>
                                <td class="text-center whitespace-nowrap"><?= getStatusBadge($leave['status']) ?></td>
                                <td class="whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <a href="<?= BASE_URL ?>/index.php?page=leave-detail&id=<?= $leave['id'] ?>" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold flex items-center justify-center transition shadow-2xs" title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>
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
