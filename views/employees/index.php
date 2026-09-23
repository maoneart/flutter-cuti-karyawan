<?php
/**
 * Master Data Employees View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Master Data Karyawan';
require_once __DIR__ . '/../layouts/header.php';

requireRole('admin');

$pdo = getDbConnection();
$deptFilter = (int)($_GET['dept_id'] ?? 0);
$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();

$sql = "
    SELECT e.*, d.nama_dept, d.kode_dept, p.nama_jabatan, p.level_hierarki
    FROM karyawan e
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    WHERE 1=1
";

$params = [];
if ($deptFilter > 0) {
    $sql .= " AND e.departemen_id = ? ";
    $params[] = $deptFilter;
}
$sql .= " ORDER BY d.nama_dept ASC, p.level_hierarki DESC, e.nama_lengkap ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$employees = $stmt->fetchAll();
?>

<div class="space-y-6">

    <!-- Department Filter Tabs -->
    <div class="bg-white rounded-2xl p-3.5 border border-slate-200/80 shadow-sm flex items-center gap-1.5 flex-wrap text-xs">
        <span class="font-bold text-slate-400 mr-2 flex items-center gap-1"><i class="fa-solid fa-filter"></i> Divisi:</span>
        <a href="<?= BASE_URL ?>/index.php?page=employees" 
           class="px-3 py-1.5 rounded-xl font-bold transition <?= $deptFilter === 0 ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
            Semua Departemen
        </a>
        <?php foreach ($departments as $dept): ?>
            <a href="<?= BASE_URL ?>/index.php?page=employees&dept_id=<?= $dept['id'] ?>" 
               class="px-3 py-1.5 rounded-xl font-bold transition <?= $deptFilter === (int)$dept['id'] ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                <?= htmlspecialchars($dept['nama_dept']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Table Card with Integrated Action Header -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
        <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold border border-blue-200/60 shadow-2xs">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Direktori Master Data Karyawan</h3>
                    <p class="text-[11px] text-slate-400 font-medium hidden sm:block">Kelola seluruh data karyawan, jabatan, masa kerja, dan kuota cuti</p>
                </div>
            </div>
            <div class="flex items-center gap-2.5">
                <span class="px-3.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                    Total: <?= count($employees) ?> Orang
                </span>
                <a href="<?= BASE_URL ?>/index.php?page=employee-create" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-md shadow-rose-600/30 transition transform active:scale-95">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Tambah Karyawan</span>
                </a>
            </div>
        </div>

        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs datatable">
                    <thead>
                        <tr>
                            <th class="text-center w-12">No</th>
                            <th class="min-w-[200px]">Karyawan</th>
                            <th class="min-w-[150px]">Departemen & Jabatan</th>
                            <th class="min-w-[130px]">Role Akses</th>
                            <th class="min-w-[140px]">Masa Kerja</th>
                            <th class="min-w-[110px]">Status Nikah</th>
                            <th class="min-w-[120px]">Sisa Cuti</th>
                            <th class="min-w-[100px] text-center">Status</th>
                            <th class="min-w-[100px] text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($employees as $emp): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 inline-flex items-center justify-center text-[11px] text-slate-600 font-black">
                                        <?= $no++ ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                            <?= strtoupper(substr($emp['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($emp['nama_lengkap']) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono">NIK: <strong class="text-slate-700"><?= htmlspecialchars($emp['nik']) ?></strong> &bull; <?= htmlspecialchars($emp['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($emp['nama_dept']) ?></div>
                                    <div class="text-[11px] text-slate-500 font-medium mt-0.5"><?= htmlspecialchars($emp['nama_jabatan']) ?></div>
                                </td>
                                <td class="whitespace-nowrap"><?= getRoleBadge($emp['role']) ?></td>
                                <td class="whitespace-nowrap">
                                    <div class="font-bold text-slate-800"><?= hitungMasaKerja($emp['tanggal_masuk']) ?></div>
                                    <div class="text-[10.5px] text-slate-400 font-medium">Join: <?= date('d/m/Y', strtotime($emp['tanggal_masuk'])) ?></div>
                                </td>
                                <td class="whitespace-nowrap font-medium text-slate-700">
                                    <?= htmlspecialchars($emp['status_pernikahan']) ?>
                                </td>
                                <td class="whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border border-blue-200/80 font-black text-xs shadow-2xs"><?= $emp['sisa_cuti'] ?> / <?= $emp['kuota_cuti'] ?> Hari</span>
                                </td>
                                <td class="text-center whitespace-nowrap">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-black <?= $emp['status_aktif'] === 'Aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs' : 'bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs' ?>">
                                        <?= htmlspecialchars($emp['status_aktif']) ?>
                                    </span>
                                </td>
                                <td class="whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <a href="<?= BASE_URL ?>/index.php?page=employee-edit&id=<?= $emp['id'] ?>" class="w-8 h-8 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold flex items-center justify-center transition shadow-2xs" title="Edit Data">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>
                                        <?php if ($emp['id'] != $currentUser['id']): ?>
                                            <button type="button" class="w-8 h-8 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold flex items-center justify-center transition shadow-2xs" onclick="confirmDelete('<?= BASE_URL ?>/index.php?page=employee-delete&id=<?= $emp['id'] ?>', 'Hapus karyawan <?= addslashes(htmlspecialchars($emp['nama_lengkap'])) ?>?')" title="Hapus">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
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
