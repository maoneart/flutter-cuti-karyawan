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
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-black border border-slate-200/60 shadow-2xs">
                    Total: <?= count($employees) ?> Orang
                </span>
                <a href="<?= BASE_URL ?>/index.php?page=employee-template" 
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-700 font-extrabold text-xs transition transform active:scale-95 shadow-2xs"
                   title="Unduh format template Excel untuk pengisian data karyawan secara massal">
                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                    <span>Template Excel</span>
                </a>
                <button type="button" onclick="openImportModal()"
                   class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 text-indigo-700 font-extrabold text-xs transition transform active:scale-95 shadow-2xs">
                    <i class="fa-solid fa-file-import text-indigo-600"></i>
                    <span>Import Excel</span>
                </button>
                <a href="<?= BASE_URL ?>/index.php?page=employee-create" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-xs shadow-md shadow-rose-600/30 transition transform active:scale-95">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>+ Tambah Karyawan</span>
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

<!-- Modern Modal Import Excel Karyawan -->
<div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm transition-opacity flex items-center justify-center p-4">
    <div class="relative w-full max-w-2xl rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 space-y-5 animate-scaleUp max-h-[90vh] flex flex-col">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg font-bold border border-indigo-200/60 shadow-2xs">
                    <i class="fa-solid fa-file-import"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Import Data Karyawan Massal</h3>
                    <p class="text-xs text-slate-400">Unggah file Excel (.xlsx / .csv) untuk memasukkan banyak karyawan sekaligus</p>
                </div>
            </div>
            <button type="button" onclick="closeImportModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Step 1: Upload Dropzone -->
        <div id="uploadSection" class="space-y-4">
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 text-xs text-blue-800 space-y-1">
                <strong class="font-bold flex items-center gap-1.5"><i class="fa-solid fa-circle-info"></i> Petunjuk Import:</strong>
                <p>1. Unduh template resmi terlebih dahulu agar struktur kolom sesuai.</p>
                <p>2. Pastikan NIK & Email belum terdaftar dan Departemen sesuai salah satu dari 15 departemen resmi.</p>
                <p>3. Password awal untuk akun hasil import otomatis di-set ke: <code class="bg-blue-100 px-1.5 py-0.5 rounded font-mono font-bold">password123</code></p>
            </div>

            <div class="border-2 border-dashed border-slate-200 hover:border-indigo-500 rounded-3xl p-8 text-center transition cursor-pointer bg-slate-50/50 hover:bg-indigo-50/30 group" id="dropzoneBox" onclick="document.getElementById('excelFileInput').click()">
                <input type="file" id="excelFileInput" accept=".xlsx, .csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv" class="hidden" onchange="handleFileSelected(this)">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl mx-auto mb-3 group-hover:scale-110 transition shadow-sm">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="text-sm font-extrabold text-slate-800 mb-1" id="dropzoneTitle">Klik atau seret file Excel/CSV ke sini</div>
                <p class="text-xs text-slate-400" id="dropzoneSubtitle">Mendukung format .xlsx dan .csv (Maks. 10MB)</p>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="<?= BASE_URL ?>/index.php?page=employee-template" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                    <i class="fa-solid fa-download"></i> Unduh Template Excel
                </a>
                <button type="button" id="btnProcessPreview" onclick="uploadAndPreview()" disabled class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-200 disabled:text-slate-400 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass"></i> Periksa & Validasi
                </button>
            </div>
        </div>

        <!-- Step 2: Validation Preview (Hidden by default) -->
        <div id="previewSection" class="hidden space-y-4 overflow-y-auto flex-1 custom-scrollbar pr-1">
            
            <!-- Summary Metric Cards -->
            <div class="grid grid-cols-3 gap-3">
                <div class="p-3.5 rounded-2xl bg-slate-100 border border-slate-200/80 text-center">
                    <div class="text-[11px] font-bold text-slate-500 uppercase">Total Baris</div>
                    <div class="text-xl font-black text-slate-900" id="previewTotalCount">0</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200/80 text-center">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase">Valid (Siap Simpan)</div>
                    <div class="text-xl font-black text-emerald-600" id="previewValidCount">0</div>
                </div>
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200/80 text-center">
                    <div class="text-[11px] font-bold text-rose-600 uppercase">Error (Gagal)</div>
                    <div class="text-xl font-black text-rose-600" id="previewInvalidCount">0</div>
                </div>
            </div>

            <!-- Error List Box (if any) -->
            <div id="errorListBox" class="hidden space-y-2">
                <div class="text-xs font-extrabold text-rose-700 flex items-center gap-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i> Baris dengan Kesalahan (Tidak akan disimpan):
                </div>
                <div class="max-h-36 overflow-y-auto bg-rose-50/70 border border-rose-200 rounded-2xl p-3 text-xs text-rose-900 space-y-1.5 font-medium" id="errorListContent">
                </div>
            </div>

            <!-- Valid Rows Preview Table -->
            <div id="validTableBox" class="space-y-2">
                <div class="text-xs font-extrabold text-slate-800 flex items-center justify-between">
                    <span>Daftar Karyawan Valid:</span>
                    <span class="text-[11px] text-slate-400 font-normal">Hanya data valid yang akan dimasukkan ke database</span>
                </div>
                <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-2xl">
                    <table class="w-full text-left text-[11px]">
                        <thead class="bg-slate-100 text-slate-600 font-bold sticky top-0">
                            <tr>
                                <th class="p-2.5">Baris</th>
                                <th class="p-2.5">NIK</th>
                                <th class="p-2.5">Nama Lengkap</th>
                                <th class="p-2.5">Departemen</th>
                                <th class="p-2.5">Role</th>
                            </tr>
                        </thead>
                        <tbody id="validTableBody" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Commit Actions -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" onclick="resetImportModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    &larr; Pilih File Lain
                </button>
                <button type="button" id="btnCommitImport" onclick="commitImport()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Simpan Data Valid
                </button>
            </div>

        </div>

    </div>
</div>

<script>
let selectedImportFile = null;
let currentValidRows = [];

function openImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
    resetImportModal();
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

function resetImportModal() {
    selectedImportFile = null;
    currentValidRows = [];
    document.getElementById('excelFileInput').value = '';
    document.getElementById('dropzoneTitle').innerText = 'Klik atau seret file Excel/CSV ke sini';
    document.getElementById('dropzoneSubtitle').innerText = 'Mendukung format .xlsx dan .csv (Maks. 10MB)';
    document.getElementById('btnProcessPreview').disabled = true;
    document.getElementById('uploadSection').classList.remove('hidden');
    document.getElementById('previewSection').classList.add('hidden');
}

function handleFileSelected(input) {
    if (input.files && input.files[0]) {
        selectedImportFile = input.files[0];
        document.getElementById('dropzoneTitle').innerText = selectedImportFile.name;
        document.getElementById('dropzoneSubtitle').innerText = (selectedImportFile.size / 1024).toFixed(1) + ' KB - Siap diperiksa';
        document.getElementById('btnProcessPreview').disabled = false;
    }
}

function uploadAndPreview() {
    if (!selectedImportFile) return;

    const btn = document.getElementById('btnProcessPreview');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memvalidasi Data...';

    const formData = new FormData();
    formData.append('file', selectedImportFile);

    fetch('<?= BASE_URL ?>/index.php?page=employee-import-preview', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Periksa & Validasi';

        if (!res.success) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: res.message || 'File tidak dapat diproses'
            });
            return;
        }

        const data = res.data;
        currentValidRows = data.valid_rows || [];

        // Show Preview Section
        document.getElementById('uploadSection').classList.add('hidden');
        document.getElementById('previewSection').classList.remove('hidden');

        document.getElementById('previewTotalCount').innerText = data.total_rows;
        document.getElementById('previewValidCount').innerText = data.valid_count;
        document.getElementById('previewInvalidCount').innerText = data.invalid_count;

        // Render Errors
        const errorBox = document.getElementById('errorListBox');
        const errorContent = document.getElementById('errorListContent');
        if (data.invalid_count > 0 && data.invalid_rows) {
            errorBox.classList.remove('hidden');
            let errHtml = '';
            data.invalid_rows.forEach(r => {
                errHtml += `<div class="p-2 rounded-lg bg-rose-100/60 border border-rose-200 flex items-start gap-2">
                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-mono text-[10px] font-bold">Baris ${r.row_number}</span>
                    <div class="flex-1">
                        <strong>${r.nama_lengkap || r.nik || 'Data'}</strong>: ${r.errors.join(' &bull; ')}
                    </div>
                </div>`;
            });
            errorContent.innerHTML = errHtml;
        } else {
            errorBox.classList.add('hidden');
            errorContent.innerHTML = '';
        }

        // Render Valid Table
        const tbody = document.getElementById('validTableBody');
        if (currentValidRows.length > 0) {
            let rowHtml = '';
            currentValidRows.forEach(r => {
                rowHtml += `<tr class="hover:bg-slate-50">
                    <td class="p-2.5 font-mono text-slate-500">${r.row_number}</td>
                    <td class="p-2.5 font-bold text-slate-900">${r.nik}</td>
                    <td class="p-2.5 font-medium">${r.nama_lengkap}</td>
                    <td class="p-2.5 text-slate-600">${r.departemen_input || '-'}</td>
                    <td class="p-2.5"><span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-bold uppercase text-[10px]">${r.role}</span></td>
                </tr>`;
            });
            tbody.innerHTML = rowHtml;
            document.getElementById('btnCommitImport').disabled = false;
        } else {
            tbody.innerHTML = '<tr><td colspan="5" class="p-4 text-center text-slate-400 font-semibold">Tidak ada data valid untuk diimpor. Harap perbaiki file Excel Anda.</td></tr>';
            document.getElementById('btnCommitImport').disabled = true;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Periksa & Validasi';
        Swal.fire({
            icon: 'error',
            title: 'Kesalahan Sistem',
            text: 'Gagal mengirim file ke server: ' + err.message
        });
    });
}

function commitImport() {
    if (!currentValidRows || currentValidRows.length === 0) return;

    Swal.fire({
        title: 'Konfirmasi Simpan',
        text: `Simpan ${currentValidRows.length} data karyawan valid ke database?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan Semua',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#059669'
    }).then(result => {
        if (result.isConfirmed) {
            const btn = document.getElementById('btnCommitImport');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

            fetch('<?= BASE_URL ?>/index.php?page=employee-import-commit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ valid_rows: currentValidRows })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Berhasil!',
                        text: res.message || 'Data karyawan telah disimpan.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan Data Valid';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: res.message || 'Terjadi kesalahan saat menyimpan'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-check"></i> Simpan Data Valid';
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Jaringan',
                    text: err.message
                });
            });
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

