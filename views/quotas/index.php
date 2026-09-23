<?php
/**
 * Quota Management View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Kelola Jatah Kuota Cuti Karyawan';
require_once __DIR__ . '/../layouts/header.php';

requireRole('admin');

$pdo = getDbConnection();

// Fetch departments & positions for bulk filter
$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();
$positions = $pdo->query("SELECT * FROM jabatan ORDER BY level_hierarki ASC")->fetchAll();

$stmtEmp = $pdo->query("
    SELECT e.*, d.nama_dept, p.nama_jabatan 
    FROM karyawan e
    JOIN departemen d ON e.departemen_id = d.id
    JOIN jabatan p ON e.jabatan_id = p.id
    WHERE e.status_aktif = 'Aktif'
    ORDER BY d.nama_dept ASC, e.nama_lengkap ASC
");
$employees = $stmtEmp->fetchAll();

$stmtLogs = $pdo->query("
    SELECT qh.*, e.nama_lengkap, e.nik, admin.nama_lengkap as nama_admin
    FROM riwayat_kuota_cuti qh
    JOIN karyawan e ON qh.employee_id = e.id
    LEFT JOIN karyawan admin ON qh.created_by = admin.id
    ORDER BY qh.created_at DESC
    LIMIT 50
");
$quotaLogs = $stmtLogs->fetchAll();
?>

<div class="space-y-6">

    <!-- Hero Guidance Banner for HRD -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-3xl p-6 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-300 text-xs font-semibold backdrop-blur-md">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Manajemen Kuota Cuti Skala Besar (400 - 500+ Karyawan)</span>
                </div>
                <h2 class="text-lg font-black text-white">Alokasi Cepat Akhir Tahun & Penyesuaian Fleksibel</h2>
                <p class="text-xs text-slate-300 max-w-2xl leading-relaxed">
                    Gunakan <strong>Alokasi Kuota Massal (Bulk)</strong> untuk membagikan jatah cuti tahunan ke ratusan karyawan sekaligus dalam 1 detik. Untuk jabatan tertentu (seperti Manager/Atasan) atau kasus khusus, gunakan fitur <strong>Ubah Kuota</strong> di tabel di bawah.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="button" onclick="openBulkModal()" 
                        class="px-5 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-xs shadow-lg shadow-blue-600/30 transition transform active:scale-95 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-rotate text-sm"></i>
                    <span>Alokasi Kuota Massal (Bulk)</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Employee Quota Table (8 Cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200/80 shadow-[0_10px_30px_-5px_rgba(0,0,0,0.04)] overflow-hidden">
            <div class="p-5 sm:px-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/40">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-sm font-bold border border-purple-200/60 shadow-2xs">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Daftar Saldo Kuota Seluruh Karyawan (<?= count($employees) ?> Aktif)</h3>
                        <p class="text-[11px] text-slate-400 font-medium hidden sm:block">Cari nama/jabatan karyawan untuk penyesuaian individual secara manual</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" onclick="openManualAdjustModal()" 
                            class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 font-extrabold text-xs shadow-2xs transition flex items-center gap-1.5">
                        <i class="fa-solid fa-plus text-blue-600"></i>
                        <span>Input Penyesuaian</span>
                    </button>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs datatable">
                        <thead>
                            <tr>
                                <th class="min-w-[180px]">Karyawan</th>
                                <th class="min-w-[130px]">Departemen</th>
                                <th class="min-w-[120px]">Jabatan</th>
                                <th class="min-w-[90px] text-center">Total Kuota</th>
                                <th class="min-w-[80px] text-center">Terpakai</th>
                                <th class="min-w-[100px] text-center">Sisa Saldo</th>
                                <th class="min-w-[110px] text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employees as $emp): ?>
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xs flex items-center justify-center flex-shrink-0 shadow-2xs">
                                                <?= strtoupper(substr($emp['nama_lengkap'], 0, 1)) ?>
                                            </div>
                                            <div>
                                                <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($emp['nama_lengkap']) ?></div>
                                                <div class="text-[11px] text-slate-400 font-mono">NIK: <strong class="text-slate-700"><?= htmlspecialchars($emp['nik']) ?></strong></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-extrabold text-slate-900 text-xs"><?= htmlspecialchars($emp['nama_dept']) ?></div>
                                    </td>
                                    <td class="font-semibold text-slate-700">
                                        <?= htmlspecialchars($emp['nama_jabatan']) ?>
                                    </td>
                                    <td class="text-center font-black text-slate-800 text-xs"><?= $emp['kuota_cuti'] ?> Hari</td>
                                    <td class="text-center font-black text-amber-600 text-xs"><?= $emp['cuti_terpakai'] ?> Hari</td>
                                    <td class="text-center whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 border border-blue-200/80 font-black text-xs shadow-2xs"><?= $emp['sisa_cuti'] ?> Hari</span>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <button type="button" 
                                                onclick='openEditQuotaData(<?= json_encode([
                                                    "id" => $emp["id"],
                                                    "nama" => $emp["nama_lengkap"],
                                                    "nik" => $emp["nik"],
                                                    "dept" => $emp["nama_dept"],
                                                    "jabatan" => $emp["nama_jabatan"],
                                                    "kuota_cuti" => $emp["kuota_cuti"],
                                                    "cuti_terpakai" => $emp["cuti_terpakai"],
                                                    "sisa_cuti" => $emp["sisa_cuti"]
                                                ]) ?>)' 
                                                class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 font-bold text-xs transition shadow-2xs flex items-center gap-1.5">
                                            <i class="fa-solid fa-sliders"></i> Ubah Kuota
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- History Log (4 Cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-blue-600"></i> Riwayat Audit Kuota
                </h3>
            </div>
            <div class="p-4 flex-1 overflow-y-auto max-h-[600px] space-y-3">
                <?php if (empty($quotaLogs)): ?>
                    <div class="text-center py-8 text-slate-400 text-xs">Belum ada riwayat penyesuaian kuota.</div>
                <?php else: ?>
                    <?php foreach ($quotaLogs as $log): ?>
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                            <div class="flex items-center justify-between">
                                <strong class="text-xs font-extrabold text-slate-900 truncate"><?= htmlspecialchars($log['nama_lengkap']) ?></strong>
                                <span class="px-2 py-0.5 rounded-full text-[10.5px] font-extrabold <?= $log['perubahan'] >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                    <?= $log['perubahan'] >= 0 ? '+' . $log['perubahan'] : $log['perubahan'] ?> Hari
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 font-medium"><?= htmlspecialchars($log['keterangan']) ?></p>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-200/60 font-mono">
                                <span>Saldo: <?= $log['kuota_sebelum'] ?> &rarr; <strong class="text-slate-700"><?= $log['kuota_sesudah'] ?></strong></span>
                                <span><?= date('d/m H:i', strtotime($log['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<!-- MODAL 1: Individual Quota Setting (Penetapan Plafon Hak Cuti Tahunan) -->
<div id="modalAdjustQuota" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm hidden">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
        <form action="<?= BASE_URL ?>/index.php?page=quota-adjust" method="POST">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-sliders"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Penetapan Hak Jatah Cuti Tahunan</h3>
                        <p class="text-[11px] text-slate-400">Atur plafon resmi hak cuti tahunan (1 tahun sekali)</p>
                    </div>
                </div>
                <button type="button" onclick="closeAdjustModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                
                <div class="p-3 rounded-2xl bg-amber-50 text-amber-900 border border-amber-200 text-[11px] leading-relaxed">
                    <i class="fa-solid fa-circle-info text-amber-600 mr-1"></i>
                    <strong>Ketentuan:</strong> Jatah cuti tahunan dialokasikan 1 tahun sekali. Pemotongan saldo cuti hanya berjalan secara otomatis melalui sistem saat pengajuan cuti karyawan telah disetujui atasan.
                </div>

                <!-- Target Employee Info Card (Active when opened from row) -->
                <div id="targetEmpCard" class="hidden p-4 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">Karyawan Terpilih</span>
                            <div id="dispEmpName" class="text-sm font-black text-slate-900"></div>
                            <div id="dispEmpMeta" class="text-xs text-slate-500 font-medium"></div>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Sisa Cuti Saat Ini</span>
                            <div id="dispEmpSisa" class="text-base font-black text-blue-700"></div>
                        </div>
                    </div>
                </div>

                <!-- Dropdown Select (Used when opened from manual input button) -->
                <div id="targetEmpSelectGroup">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Karyawan <span class="text-rose-500">*</span></label>
                    <select name="employee_id" id="emp_select" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="">-- Cari & Pilih Karyawan --</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nik']) ?> - <?= htmlspecialchars($emp['nama_lengkap']) ?> (<?= htmlspecialchars($emp['nama_jabatan']) ?> - <?= htmlspecialchars($emp['nama_dept']) ?>) [Sisa: <?= $emp['sisa_cuti'] ?>h]</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Total Quota Field -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Total Jatah Hak Cuti Tahunan (Hari) <span class="text-rose-500">*</span></label>
                    <input type="number" name="total_kuota_baru" id="input_total_kuota_baru" value="12" min="1" max="50" required placeholder="Contoh: 12 (Standar) atau 14 (Manager)" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-black text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                    <p class="text-[10.5px] text-slate-400 mt-1">Sisa saldo cuti akan disesuaikan otomatis dari (Total Kuota Baru &minus; Cuti yang Sudah Terpakai).</p>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Kategori / Dasar Alokasi <span class="text-rose-500">*</span></label>
                    <select name="tipe" id="input_tipe" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="alokasi_tahunan">Alokasi / Penetapan Plafon Cuti Tahunan Resmi</option>
                        <option value="penyesuaian_hrd">Penyesuaian Level Jabatan (Manager / Atasan)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan / Catatan Audit <span class="text-rose-500">*</span></label>
                    <textarea name="keterangan" id="input_keterangan" rows="2" required placeholder="Contoh: Penetapan jatah tahunan posisi Manager (14 hari)" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15"></textarea>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAdjustModal()" class="px-4 py-2 rounded-xl bg-white border border-slate-200 font-bold text-slate-600 text-xs">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm">Simpan Penetapan Kuota</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: BULK ALLOCATION (Alokasi Massal untuk Ratusan Karyawan) -->
<div id="modalBatchQuota" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm hidden">
    <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
        <form action="<?= BASE_URL ?>/index.php?page=quota-batch-reset" method="POST">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-blue-600 to-indigo-600 text-white">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white/20 text-white flex items-center justify-center font-bold">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-white">Alokasi Kuota Massal (Bulk Generator)</h3>
                        <p class="text-[11px] text-blue-100">Solusi alokasi cuti serentak untuk 400 - 500+ karyawan</p>
                    </div>
                </div>
                <button type="button" onclick="document.getElementById('modalBatchQuota').classList.add('hidden')" class="text-white/80 hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                
                <div class="p-3.5 rounded-2xl bg-blue-50 text-blue-900 border border-blue-200/80 leading-relaxed">
                    <strong class="block font-bold mb-0.5"><i class="fa-solid fa-bolt text-amber-500 mr-1"></i> Proses Instan & Otomatis:</strong>
                    Sistem akan memproses seluruh karyawan yang terpilih secara massal dan mencatat riwayat audit secara otomatis.
                </div>

                <!-- Target Scope Filter -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Target Karyawan <span class="text-rose-500">*</span></label>
                    <select name="target_scope" id="bulk_target_scope" onchange="handleScopeChange(this.value)" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-bold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <option value="all">🌟 Seluruh Karyawan Aktif (Semua Divisi)</option>
                        <option value="department">🏢 Per Departemen Tertentu</option>
                        <option value="position">💼 Per Jabatan Tertentu (Misal: Semua Manager / Supervisor)</option>
                    </select>
                </div>

                <!-- Sub-select for Department (Conditional) -->
                <div id="scopeDeptBox" class="hidden">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Departemen Target <span class="text-rose-500">*</span></label>
                    <select name="target_id" id="bulk_dept_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nama_dept']) ?> (<?= htmlspecialchars($dept['kode_dept']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sub-select for Position (Conditional) -->
                <div id="scopePositionBox" class="hidden">
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Pilih Jabatan Target <span class="text-rose-500">*</span></label>
                    <select name="target_id" id="bulk_pos_id" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                        <?php foreach ($positions as $pos): ?>
                            <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['nama_jabatan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Action Type: Reset Tahunan vs Tambah Saldo -->
                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipe Aksi Alokasi</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-slate-200 hover:border-blue-300 cursor-pointer bg-slate-50/50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 transition">
                            <input type="radio" name="batch_action" value="reset" checked class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <strong class="block text-slate-800 text-xs">Reset Baru Tahunan</strong>
                                <span class="text-[10px] text-slate-400">Set kuota baru & nolkan terpakai</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border border-slate-200 hover:border-blue-300 cursor-pointer bg-slate-50/50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 transition">
                            <input type="radio" name="batch_action" value="add" class="text-blue-600 focus:ring-blue-500">
                            <div>
                                <strong class="block text-slate-800 text-xs">Tambah Saldo (+N Hari)</strong>
                                <span class="text-[10px] text-slate-400">Tambahkan ke saldo yang ada</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jumlah Kuota (Hari) <span class="text-rose-500">*</span></label>
                    <input type="number" name="default_quota" value="12" min="1" max="50" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-black text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Keterangan Catatan Audit</label>
                    <input type="text" name="batch_keterangan" value="Alokasi Jatah Cuti Tahunan <?= date('Y') ?>" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-medium text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('modalBatchQuota').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-white border border-slate-200 font-bold text-slate-600 text-xs">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold text-xs shadow-md shadow-blue-600/30">Terapkan Massal Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
function openBulkModal() {
    document.getElementById('modalBatchQuota').classList.remove('hidden');
}

function handleScopeChange(scope) {
    const deptBox = document.getElementById('scopeDeptBox');
    const posBox = document.getElementById('scopePositionBox');
    const deptSelect = document.getElementById('bulk_dept_id');
    const posSelect = document.getElementById('bulk_pos_id');

    deptBox.classList.add('hidden');
    posBox.classList.add('hidden');
    deptSelect.disabled = true;
    posSelect.disabled = true;

    if (scope === 'department') {
        deptBox.classList.remove('hidden');
        deptSelect.disabled = false;
    } else if (scope === 'position') {
        posBox.classList.remove('hidden');
        posSelect.disabled = false;
    }
}

function openManualAdjustModal() {
    document.getElementById('targetEmpCard').classList.add('hidden');
    document.getElementById('targetEmpSelectGroup').classList.remove('hidden');
    document.getElementById('emp_select').disabled = false;
    document.getElementById('emp_select').value = '';
    document.getElementById('input_keterangan').value = '';
    document.getElementById('modalAdjustQuota').classList.remove('hidden');
}

function openEditQuotaData(emp) {
    document.getElementById('targetEmpSelectGroup').classList.add('hidden');
    document.getElementById('emp_select').disabled = true;
    
    // Inject hidden input if not exists
    let hiddenInput = document.getElementById('hidden_emp_id');
    if (!hiddenInput) {
        hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'employee_id';
        hiddenInput.id = 'hidden_emp_id';
        document.querySelector('#modalAdjustQuota form').appendChild(hiddenInput);
    }
    hiddenInput.value = emp.id;

    // Display Card Info
    document.getElementById('dispEmpName').textContent = emp.nama;
    document.getElementById('dispEmpMeta').textContent = `NIK: ${emp.nik} | ${emp.jabatan} - ${emp.dept}`;
    document.getElementById('dispEmpSisa').textContent = `${emp.sisa_cuti} Hari`;
    document.getElementById('targetEmpCard').classList.remove('hidden');

    // Default note
    document.getElementById('input_keterangan').value = `Penyesuaian kuota posisi ${emp.jabatan}`;
    document.getElementById('input_total_kuota_baru').value = Math.max(12, emp.kuota_cuti);

    document.getElementById('modalAdjustQuota').classList.remove('hidden');
}

function closeAdjustModal() {
    document.getElementById('modalAdjustQuota').classList.add('hidden');
    const hiddenInput = document.getElementById('hidden_emp_id');
    if (hiddenInput) hiddenInput.remove();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
