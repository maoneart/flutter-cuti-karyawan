<?php
/**
 * Create Leave Application View (Compact & Ultra-Organized Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Ajukan Cuti Baru';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$leaveTypes = $pdo->query("SELECT * FROM jenis_cuti ORDER BY id ASC")->fetchAll();

$allEmployees = [];
if ($currentUser['role'] === 'admin') {
    $stmtAll = $pdo->query("
        SELECT e.*, d.nama_dept, p.nama_jabatan 
        FROM karyawan e
        JOIN departemen d ON e.departemen_id = d.id
        JOIN jabatan p ON e.jabatan_id = p.id
        WHERE e.status_aktif = 'Aktif'
        ORDER BY d.nama_dept ASC, e.nama_lengkap ASC
    ");
    $allEmployees = $stmtAll->fetchAll();
}

$typeIcons = [
    'CT'        => 'fa-solid fa-umbrella-beach text-blue-600 bg-blue-50',
    'CT-HALF'   => 'fa-solid fa-hourglass-half text-amber-600 bg-amber-50',
    'PC-PRI'    => 'fa-solid fa-person-walking-arrow-right text-orange-600 bg-orange-50',
    'PC-SKT'    => 'fa-solid fa-hospital-user text-red-600 bg-red-50',
    'IK-TMP'    => 'fa-solid fa-door-open text-yellow-600 bg-yellow-50',
    'T'         => 'fa-solid fa-clock text-amber-700 bg-amber-50',
    'SD'        => 'fa-solid fa-file-medical text-emerald-600 bg-emerald-50',
    'ST'        => 'fa-solid fa-head-side-cough text-amber-600 bg-amber-50',
    'CH'        => 'fa-solid fa-droplet text-rose-600 bg-rose-50',
    'CML'       => 'fa-solid fa-baby text-indigo-600 bg-indigo-50',
    'CKG'       => 'fa-solid fa-heart-crack text-pink-600 bg-pink-50',
    'CK-NIK'    => 'fa-solid fa-ring text-violet-600 bg-violet-50',
    'CK-ANK'    => 'fa-solid fa-children text-blue-600 bg-blue-50',
    'CK-KHT'    => 'fa-solid fa-child text-teal-600 bg-teal-50',
    'CK-IMS'    => 'fa-solid fa-person-pregnant text-pink-600 bg-pink-50',
    'CK-DK1'    => 'fa-solid fa-ribbon text-slate-700 bg-slate-100',
    'CK-DK2'    => 'fa-solid fa-house-chimney-crack text-slate-600 bg-slate-100',
    'CK-HAJ'    => 'fa-solid fa-kaaba text-emerald-700 bg-emerald-50',
    'DISP-SP'   => 'fa-solid fa-hand-fist text-red-700 bg-red-50',
    'DISP-DNS'  => 'fa-solid fa-plane-departure text-sky-600 bg-sky-50',
    'DISP-BNC'  => 'fa-solid fa-house-flood-water text-cyan-700 bg-cyan-50',
    'DISP-STD'  => 'fa-solid fa-graduation-cap text-indigo-700 bg-indigo-50',
    'DISP-NGR'  => 'fa-solid fa-landmark text-slate-800 bg-slate-100',
    'IJN'       => 'fa-solid fa-calendar-xmark text-purple-600 bg-purple-50',
    'ALPHA'     => 'fa-solid fa-triangle-exclamation text-rose-700 bg-rose-50',
];

$usedPercent = $currentUser['kuota_cuti'] > 0 ? round(($currentUser['cuti_terpakai'] / $currentUser['kuota_cuti']) * 100) : 0;
?>

<div class="w-full space-y-5">

    <!-- Top Bento User Balance Card -->
    <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200/80 shadow-soft">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-lg flex items-center justify-center shadow-md shadow-blue-600/20 flex-shrink-0">
                    <?= strtoupper(substr($currentUser['nama_lengkap'], 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight"><?= htmlspecialchars($currentUser['nama_lengkap']) ?></h3>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-mono font-bold"><?= htmlspecialchars($currentUser['nik']) ?></span>
                    </div>
                    <div class="text-[11.5px] text-slate-500 font-medium mt-0.5 flex items-center gap-2 flex-wrap">
                        <span><i class="fa-solid fa-building text-blue-600 mr-1"></i> <?= htmlspecialchars($currentUser['nama_dept']) ?></span>
                        <span class="text-slate-300">&bull;</span>
                        <span><?= htmlspecialchars($currentUser['nama_jabatan']) ?></span>
                        <span class="text-slate-300">&bull;</span>
                        <span>Masa Kerja: <strong class="text-slate-700"><?= hitungMasaKerja($currentUser['tanggal_masuk']) ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Balance Progress Widget -->
            <div class="bg-slate-50 rounded-2xl p-3.5 border border-slate-200/70 min-w-[240px]">
                <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                    <span class="text-slate-600">Hak Cuti Tahunan <?= date('Y') ?></span>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-[11px] font-black">Sisa: <?= $currentUser['sisa_cuti'] ?> Hari</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all" style="width: <?= $usedPercent ?>%"></div>
                </div>
                <div class="flex items-center justify-between text-[10.5px] text-slate-400 font-medium mt-1">
                    <span>Terpakai: <strong class="text-slate-700"><?= $currentUser['cuti_terpakai'] ?> Hari</strong></span>
                    <span>Total Kuota: <strong class="text-slate-700"><?= $currentUser['kuota_cuti'] ?> Hari</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold shadow-2xs">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Formulir Permohonan Cuti & Izin</h2>
                    <p class="text-xs text-slate-500">Isi data permohonan dengan lengkap dan benar.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Cuti
            </a>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=leave-submit" method="POST" enctype="multipart/form-data" id="leaveForm" class="p-5 sm:p-7">
            
            <?php if ($currentUser['role'] === 'admin'): ?>
                <!-- ADMIN HRD SPECIAL: TARGET APPLICANT SELECTOR -->
                <div class="mb-5 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200/60 pb-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-user-gear text-blue-600"></i>
                            <span class="text-xs font-extrabold text-slate-900">Target Pemohon Cuti</span>
                        </div>

                        <!-- Segmented Switcher Pill -->
                        <div class="inline-flex rounded-xl bg-slate-200/80 p-0.5 border border-slate-200 text-xs font-semibold">
                            <button type="button" id="btnModeSelf" onclick="setApplicantMode('self')" 
                                    class="px-3 py-1 rounded-lg transition bg-white text-blue-700 font-bold shadow-2xs flex items-center gap-1">
                                <i class="fa-solid fa-user text-[11px]"></i> Diri Sendiri
                            </button>
                            <button type="button" id="btnModeOther" onclick="setApplicantMode('other')" 
                                    class="px-3 py-1 rounded-lg transition text-slate-600 hover:text-slate-900 flex items-center gap-1">
                                <i class="fa-solid fa-users text-[11px]"></i> Karyawan Lain
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="target_employee_id" id="target_employee_id" value="<?= $currentUser['id'] ?>">

                    <div id="otherEmployeeSearchWrap" class="hidden space-y-2">
                        <div id="selectedEmpBanner" class="hidden p-3 rounded-xl bg-white border border-blue-200 shadow-2xs flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-2.5">
                                <div id="selAvatar" class="w-8 h-8 rounded-lg bg-blue-600 text-white font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    A
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <strong id="selNama" class="text-slate-900 text-xs font-bold">Nama Karyawan</strong>
                                        <span id="selNik" class="px-1.5 py-0.2 rounded bg-slate-100 font-mono text-[10px] text-slate-600">NIK</span>
                                    </div>
                                    <div id="selMeta" class="text-[11px] text-slate-500">Dept &bull; Jabatan</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span id="selSisaCuti" class="px-2 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-xs">Sisa: 12 Hari</span>
                                <button type="button" onclick="clearSelectedEmployee()" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">Ganti</button>
                            </div>
                        </div>

                        <div id="searchBoxWrap" class="relative">
                            <input type="text" id="empSearchKeyword" oninput="handleEmpLiveSearch(this.value)" 
                                   placeholder="Ketik nama atau NIK karyawan..." 
                                   class="w-full pl-9 pr-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 font-semibold text-xs focus:outline-none focus:border-blue-500 transition shadow-2xs">
                            <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            <div id="empSearchResults" class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl border border-slate-200 shadow-xl z-50 max-h-56 overflow-y-auto hidden divide-y divide-slate-100"></div>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer select-none text-xs">
                        <input type="checkbox" name="direct_approve" id="direct_approve_toggle" value="1" checked class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <span class="font-bold text-slate-700">Verifikasi & Setujui Langsung oleh HRD</span>
                    </label>
                </div>
            <?php endif; ?>

            <!-- Compact 2-Column Responsive Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- LEFT COLUMN (Cols 7): Primary Request Details -->
                <div class="lg:col-span-7 space-y-5">
                    
                    <!-- 1. Kategori & Jenis Permohonan -->
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            1. Jenis Permohonan <span class="text-rose-500">*</span>
                        </label>

                        <!-- Compact Category Tabs -->
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" id="btnGroupCuti" onclick="switchCategoryGroup('cuti')"
                                    class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 font-bold text-xs cursor-pointer border-blue-600 bg-blue-50 text-blue-700 shadow-2xs transition">
                                <i class="fa-solid fa-umbrella-beach text-blue-600"></i>
                                <span>🌴 Kategori Cuti</span>
                            </button>
                            <button type="button" id="btnGroupIzin" onclick="switchCategoryGroup('izin')"
                                    class="flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 font-bold text-xs cursor-pointer border-slate-200 bg-white text-slate-600 hover:border-indigo-400 hover:text-indigo-600 shadow-2xs transition">
                                <i class="fa-solid fa-file-signature text-indigo-500"></i>
                                <span>📋 Kategori Izin & Sakit</span>
                            </button>
                        </div>

                        <!-- Dropdown Select -->
                        <div class="relative">
                            <select id="leave_type_select" onchange="handleDropdownChange(this.value)"
                                    class="w-full px-3.5 py-2.5 rounded-xl border-2 border-slate-200 bg-white text-slate-900 font-bold text-xs sm:text-sm focus:outline-none focus:border-blue-600 transition appearance-none cursor-pointer">
                                <!-- Dynamic options injected via JS -->
                            </select>
                            <i class="fa-solid fa-chevron-down absolute right-3.5 top-3.5 text-slate-400 pointer-events-none text-xs"></i>
                        </div>

                        <input type="hidden" name="leave_type_id" id="leave_type_id" required>

                        <!-- Dynamic Mini Info Preview Card -->
                        <div id="typeInfoCard" class="p-3 rounded-xl bg-white border border-blue-100 flex items-start gap-3 text-xs shadow-2xs">
                            <div id="typeInfoIcon" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between flex-wrap gap-1">
                                    <strong id="typeInfoName" class="text-slate-900 font-bold">Cuti Tahunan Penuh</strong>
                                    <span id="typeInfoBadge" class="px-2 py-0.2 rounded text-[10.5px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200">Potong Kuota</span>
                                </div>
                                <div id="typeInfoDesc" class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                    Maksimal durasi sesuai sisa kuota cuti tahunan reguler karyawan.
                                </div>
                            </div>
                        </div>

                        <!-- Doctor Note Alert -->
                        <div id="doctorNoteAlert" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start gap-2.5 hidden">
                            <i class="fa-solid fa-hospital-user text-rose-600 mt-0.5 text-sm flex-shrink-0"></i>
                            <div class="leading-relaxed text-[11.5px]">
                                <strong>Wajib Surat Dokter:</strong> Anda wajib melampirkan foto / scan Surat Dokter asli di form sebelah kanan agar tidak memotong cuti/gaji.
                            </div>
                        </div>
                    </div>

                    <!-- 2. Jadwal Shift & Periode Tanggal -->
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            2. Jadwal Shift & Periode Tanggal <span class="text-rose-500">*</span>
                        </label>

                        <!-- Shift Radio Toggle -->
                        <div class="grid grid-cols-2 gap-2">
                            <label class="relative flex items-center gap-2 p-2.5 rounded-xl border-2 border-slate-200 bg-white hover:border-blue-400 cursor-pointer transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="radio" name="shift" value="Shift 1 (Pagi)" checked class="w-3.5 h-3.5 text-blue-600">
                                <span class="font-bold text-xs text-slate-900">☀️ Shift 1 (Pagi)</span>
                            </label>
                            <label class="relative flex items-center gap-2 p-2.5 rounded-xl border-2 border-slate-200 bg-white hover:border-indigo-400 cursor-pointer transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50">
                                <input type="radio" name="shift" value="Shift 2 (Malam)" class="w-3.5 h-3.5 text-indigo-600">
                                <span class="font-bold text-xs text-slate-900">🌙 Shift 2 (Malam)</span>
                            </label>
                        </div>

                        <!-- Tanggal Mulai & Tanggal Selesai -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                            <div>
                                <label for="tanggal_mulai" class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Mulai Cuti</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                                       class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 font-semibold text-xs focus:outline-none focus:border-blue-500 transition shadow-2xs">
                            </div>

                            <div>
                                <label for="tanggal_selesai" class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Selesai Cuti</label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                                       class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 font-semibold text-xs focus:outline-none focus:border-blue-500 transition shadow-2xs">
                            </div>
                        </div>

                        <!-- Live Duration Pill -->
                        <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-center justify-between gap-2">
                            <div class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-calculator text-blue-600 text-xs"></i>
                                <span>Total Durasi Hari:</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="hidden" name="total_hari" id="total_hari" value="0">
                                <span id="total_hari_display" class="px-3 py-1 rounded-lg bg-blue-600 text-white font-extrabold text-xs shadow-sm">
                                    0 Hari Kerja
                                </span>
                            </div>
                        </div>

                        <div id="quotaWarning" class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2 hidden">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600 flex-shrink-0"></i>
                            <span>Jumlah hari melebihi sisa kuota (Sisa: <?= $currentUser['sisa_cuti'] ?> hari).</span>
                        </div>
                    </div>

                    <!-- 3. Alasan / Keperluan Cuti -->
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-2">
                        <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            3. Alasan / Keperluan Cuti <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="alasan" id="alasan" rows="2" required placeholder="Tuliskan alasan permohonan cuti secara jelas..."
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-900 font-medium text-xs focus:outline-none focus:border-blue-500 transition placeholder:text-slate-400"></textarea>
                    </div>

                </div>

                <!-- RIGHT COLUMN (Cols 5): Emergency Contact, Attachment & Submission -->
                <div class="lg:col-span-5 space-y-5">
                    
                    <!-- 4. Alamat & Kontak Darurat -->
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            4. Kontak Darurat & Alamat
                        </label>

                        <div>
                            <label for="kontak_darurat" class="block text-[11px] font-bold text-slate-600 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="kontak_darurat" id="kontak_darurat" placeholder="081234567890" value="<?= htmlspecialchars($currentUser['no_hp'] ?? '') ?>"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 font-medium text-xs focus:outline-none focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label for="alamat_selama_cuti" class="block text-[11px] font-bold text-slate-600 mb-1">Alamat Selama Cuti</label>
                            <input type="text" name="alamat_selama_cuti" id="alamat_selama_cuti" placeholder="Rumah sendiri / luar kota" value="<?= htmlspecialchars($currentUser['alamat'] ?? '') ?>"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-900 font-medium text-xs focus:outline-none focus:border-blue-500 transition">
                        </div>
                    </div>

                    <!-- 5. Dokumen Lampiran (Surat Dokter / Pendukung) -->
                    <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-200/80 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                5. Dokumen Lampiran <span id="lampiranReqLabel" class="text-slate-400 font-normal normal-case">(Opsional)</span>
                            </label>
                            <span id="mandatoryTag" class="px-2 py-0.2 rounded-md bg-rose-100 text-rose-700 text-[10px] font-extrabold hidden">
                                WAJIB
                            </span>
                        </div>

                        <div id="dropZoneBox" onclick="document.getElementById('attachment').click();" 
                             class="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl p-4 text-center bg-white cursor-pointer transition">
                            <div id="dropIconWrap" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base mx-auto mb-1.5 transition">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div class="text-xs font-bold text-slate-800" id="fileUploadLabel">Upload Surat Dokter / Bukti Pendukung</div>
                            <p class="text-[10.5px] text-slate-400 mt-0.5">Format JPG, PNG, atau PDF (Max 5MB)</p>
                            <input type="file" name="attachment" id="attachment" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>

                    <!-- 6. Routing Info & Action Buttons -->
                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-slate-900 text-white flex items-center gap-2.5 text-xs">
                            <i class="fa-solid fa-shield-halved text-blue-400 text-sm flex-shrink-0"></i>
                            <div class="leading-tight text-[11px] text-slate-300">
                                Permohonan diteruskan ke <strong>Atasan <?= htmlspecialchars($currentUser['nama_dept']) ?></strong>.
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5">
                            <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="w-1/3 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center transition">
                                Batal
                            </a>
                            <button type="submit" id="submitLeaveBtn" class="w-2/3 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-black text-xs sm:text-sm shadow-md shadow-rose-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                                <span>Kirim Permohonan</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </form>
    </div>

</div>

<!-- Data types stored as JSON for instant client-side lookup -->
<script>
const leaveTypesData = <?= json_encode(array_map(function($t) use ($typeIcons) {
    $k = strtoupper($t['kode']);
    $n = strtolower($t['nama_cuti']);
    $isCuti = (strpos($k, 'CT') === 0 || strpos($k, 'CK') === 0 || in_array($k, ['CH', 'CML', 'DISP-NGR']) || strpos($n, 'cuti') !== false);
    return [
        'id' => (int)$t['id'],
        'nama_cuti' => $t['nama_cuti'],
        'kategori' => $isCuti ? 'cuti' : 'izin',
        'kode' => $t['kode'],
        'potong_kuota' => (int)$t['potong_kuota'],
        'butuh_lampiran' => (int)$t['butuh_lampiran'],
        'max_hari' => $t['max_hari_default'],
        'durasi_maks' => $t['durasi_maksimal'] ?? ($t['max_hari_default'] . ' Hari'),
        'deskripsi' => $t['deskripsi'] ?? $t['catatan_khusus'] ?? '',
        'icon' => $typeIcons[$t['kode']] ?? 'fa-solid fa-calendar-check text-blue-600 bg-blue-50'
    ];
}, $leaveTypes)) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('leave_type_id_input') || document.getElementById('leave_type_select');
    const inputType = document.getElementById('leave_type_id');
    const btnCuti = document.getElementById('btnGroupCuti');
    const btnIzin = document.getElementById('btnGroupIzin');
    const typeInfoCard = document.getElementById('typeInfoCard');
    const typeInfoIcon = document.getElementById('typeInfoIcon');
    const typeInfoName = document.getElementById('typeInfoName');
    const typeInfoBadge = document.getElementById('typeInfoBadge');
    const typeInfoDesc = document.getElementById('typeInfoDesc');
    const docAlert = document.getElementById('doctorNoteAlert');
    const lampiranReq = document.getElementById('lampiranReqLabel');
    const mandatoryTag = document.getElementById('mandatoryTag');
    const attachmentInput = document.getElementById('attachment');
    const fileLabel = document.getElementById('fileUploadLabel');
    const dropZone = document.getElementById('dropZoneBox');
    const dropIconWrap = document.getElementById('dropIconWrap');
    const quotaWarning = document.getElementById('quotaWarning');
    const totalDaysInput = document.getElementById('total_hari');
    const totalDaysDisplay = document.getElementById('total_hari_display');
    const startDateInput = document.getElementById('tanggal_mulai');
    const endDateInput = document.getElementById('tanggal_selesai');
    let userSisaCuti = <?= (float)$currentUser['sisa_cuti'] ?>;
    const leaveForm = document.getElementById('leaveForm');

    let currentGroup = 'cuti';
    let currentNeedsAttach = 0;

    // File input preview
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileLabel.innerHTML = `<span class="text-emerald-600 font-extrabold text-xs block"><i class="fa-solid fa-file-circle-check mr-1"></i> ${this.files[0].name} (${(this.files[0].size / 1024).toFixed(1)} KB)</span><span class="text-[10.5px] text-slate-400">File lampiran siap dikirim</span>`;
                dropZone.classList.remove('border-rose-400', 'bg-rose-50/40');
                dropZone.classList.add('border-emerald-500', 'bg-emerald-50/30');
            } else {
                fileLabel.innerText = 'Upload Surat Dokter / Bukti Pendukung';
                dropZone.classList.remove('border-emerald-500', 'bg-emerald-50/30');
            }
        });
    }

    // Populate dropdown based on category
    function populateDropdown(group) {
        if (!typeSelect) return;
        typeSelect.innerHTML = '';
        const filtered = leaveTypesData.filter(t => t.kategori === group);

        filtered.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            const potongLabel = t.potong_kuota === 1 ? ' [Potong Kuota]' : '';
            const docLabel = t.butuh_lampiran === 1 ? ' [Wajib Surat Dokter]' : '';
            opt.textContent = `${t.nama_cuti} (Max: ${t.durasi_maks})${potongLabel}${docLabel}`;
            typeSelect.appendChild(opt);
        });

        if (filtered.length > 0) {
            selectLeaveTypeById(filtered[0].id);
        }
    }

    // Switch Category Group (Cuti vs Izin)
    window.switchCategoryGroup = function(group) {
        currentGroup = group;
        if (group === 'cuti') {
            btnCuti.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 font-bold text-xs cursor-pointer border-blue-600 bg-blue-50 text-blue-700 shadow-2xs transition';
            btnIzin.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 font-bold text-xs cursor-pointer border-slate-200 bg-white text-slate-600 hover:border-indigo-400 hover:text-indigo-600 shadow-2xs transition';
        } else {
            btnIzin.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 font-bold text-xs cursor-pointer border-indigo-600 bg-indigo-50 text-indigo-700 shadow-2xs transition';
            btnCuti.className = 'flex items-center justify-center gap-2 py-2 px-3 rounded-xl border-2 font-bold text-xs cursor-pointer border-slate-200 bg-white text-slate-600 hover:border-blue-400 hover:text-blue-600 shadow-2xs transition';
        }
        populateDropdown(group);
    };

    window.handleDropdownChange = function(typeId) {
        selectLeaveTypeById(parseInt(typeId));
    };

    function selectLeaveTypeById(id) {
        const item = leaveTypesData.find(t => t.id === id);
        if (!item) return;

        inputType.value = item.id;
        typeSelect.value = item.id;
        currentNeedsAttach = item.butuh_lampiran;

        // Update Mini Info Card
        typeInfoName.textContent = item.nama_cuti;
        typeInfoDesc.textContent = item.deskripsi ? item.deskripsi : `Durasi maksimal: ${item.durasi_maks}`;

        if (item.butuh_lampiran === 1) {
            typeInfoBadge.className = 'px-2 py-0.2 rounded text-[10.5px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200';
            typeInfoBadge.textContent = 'Wajib Surat Dokter';
        } else if (item.potong_kuota === 1) {
            typeInfoBadge.className = 'px-2 py-0.2 rounded text-[10.5px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200';
            typeInfoBadge.textContent = 'Potong Kuota';
        } else {
            typeInfoBadge.className = 'px-2 py-0.2 rounded text-[10.5px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200';
            typeInfoBadge.textContent = 'Cuti Khusus';
        }

        // Half-day logic
        const isHalfDay = ['CT-HALF', 'PC-PRI', 'PC-SKT', 'IK-TMP', 'T'].includes(item.kode);
        if (isHalfDay) {
            if (startDateInput.value) {
                endDateInput.value = startDateInput.value;
                endDateInput.min = startDateInput.value;
            }
            endDateInput.readOnly = true;
            endDateInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            totalDaysInput.value = 0.5;
            if (totalDaysDisplay) totalDaysDisplay.innerText = '0.5 Hari (Setengah Hari / 4 Jam)';
        } else {
            endDateInput.readOnly = false;
            endDateInput.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            if (startDateInput.value) {
                endDateInput.min = startDateInput.value;
                if (!endDateInput.value || endDateInput.value < startDateInput.value) {
                    endDateInput.value = startDateInput.value;
                }
                startDateInput.dispatchEvent(new Event('input'));
            }
        }

        // Mandatory Attachment Alert
        if (item.butuh_lampiran === 1) {
            docAlert.classList.remove('hidden');
            lampiranReq.innerHTML = '<strong class="text-rose-600 font-extrabold">(WAJIB DILAMPIRKAN)</strong>';
            mandatoryTag.classList.remove('hidden');
            attachmentInput.required = true;
            dropZone.classList.add('border-rose-400', 'bg-rose-50/30');
            dropIconWrap.classList.remove('bg-blue-50', 'text-blue-600');
            dropIconWrap.classList.add('bg-rose-100', 'text-rose-600');
        } else {
            docAlert.classList.add('hidden');
            lampiranReq.innerHTML = '<span class="text-slate-400 font-normal">(Opsional)</span>';
            mandatoryTag.classList.add('hidden');
            attachmentInput.required = false;
            dropZone.classList.remove('border-rose-400', 'bg-rose-50/30');
            dropIconWrap.classList.remove('bg-rose-100', 'text-rose-600');
            dropIconWrap.classList.add('bg-blue-50', 'text-blue-600');
        }

        checkQuota(item.potong_kuota);
    }

    function checkQuota(potong) {
        const totalHari = parseFloat(totalDaysInput.value || 0);
        if (potong === 1 && totalHari > userSisaCuti) {
            quotaWarning.classList.remove('hidden');
        } else {
            quotaWarning.classList.add('hidden');
        }
    }

    ['input', 'change'].forEach(evt => {
        startDateInput.addEventListener(evt, () => {
            const curType = leaveTypesData.find(t => t.id === parseInt(inputType.value));
            const potong = curType ? curType.potong_kuota : 0;
            setTimeout(() => checkQuota(potong), 50);
        });
        endDateInput.addEventListener(evt, () => {
            const curType = leaveTypesData.find(t => t.id === parseInt(inputType.value));
            const potong = curType ? curType.potong_kuota : 0;
            setTimeout(() => checkQuota(potong), 50);
        });
    });

    // Initialize with Cuti group
    switchCategoryGroup('cuti');

    // Admin On-Behalf Handling
    const allEmployeesList = <?= json_encode(array_map(function($e) {
        return [
            'id' => (int)$e['id'],
            'nik' => $e['nik'],
            'nama' => $e['nama_lengkap'],
            'dept' => $e['nama_dept'],
            'jabatan' => $e['nama_jabatan'],
            'sisa_cuti' => (float)$e['sisa_cuti'],
            'kuota_cuti' => (float)$e['kuota_cuti'],
            'alamat' => $e['alamat'] ?? '',
            'no_hp' => $e['no_hp'] ?? ''
        ];
    }, $allEmployees)) ?>;

    const currentUserId = <?= (int)$currentUser['id'] ?>;
    const currentUserQuota = <?= (int)$currentUser['sisa_cuti'] ?>;

    window.setApplicantMode = function(mode) {
        const btnSelf = document.getElementById('btnModeSelf');
        const btnOther = document.getElementById('btnModeOther');
        const searchWrap = document.getElementById('otherEmployeeSearchWrap');
        const targetInput = document.getElementById('target_employee_id');

        if (mode === 'self') {
            btnSelf.className = 'px-3 py-1 rounded-lg transition bg-white text-blue-700 font-bold shadow-2xs flex items-center gap-1';
            btnOther.className = 'px-3 py-1 rounded-lg transition text-slate-600 hover:text-slate-900 flex items-center gap-1';
            searchWrap.classList.add('hidden');
            targetInput.value = currentUserId;
            userSisaCuti = currentUserQuota;
        } else {
            btnOther.className = 'px-3 py-1 rounded-lg transition bg-white text-blue-700 font-bold shadow-2xs flex items-center gap-1';
            btnSelf.className = 'px-3 py-1 rounded-lg transition text-slate-600 hover:text-slate-900 flex items-center gap-1';
            searchWrap.classList.remove('hidden');
            const searchInput = document.getElementById('empSearchKeyword');
            if (searchInput) searchInput.focus();
        }
        const curType = leaveTypesData.find(t => t.id === parseInt(inputType.value));
        const potong = curType ? curType.potong_kuota : 0;
        checkQuota(potong);
    };

    window.handleEmpLiveSearch = function(keyword) {
        const resBox = document.getElementById('empSearchResults');
        const kw = keyword.trim().toLowerCase();

        if (kw.length === 0) {
            resBox.classList.add('hidden');
            resBox.innerHTML = '';
            return;
        }

        const matches = allEmployeesList.filter(e => 
            e.nama.toLowerCase().includes(kw) || 
            e.nik.toLowerCase().includes(kw) || 
            e.dept.toLowerCase().includes(kw) ||
            e.jabatan.toLowerCase().includes(kw)
        );

        if (matches.length === 0) {
            resBox.innerHTML = '<div class="p-3 text-center text-xs text-slate-500 font-medium">Tidak ada karyawan yang cocok</div>';
            resBox.classList.remove('hidden');
            return;
        }

        let html = '';
        matches.slice(0, 8).forEach(emp => {
            html += `
                <div onclick='selectEmployee(${JSON.stringify(emp)})' 
                     class="p-2.5 hover:bg-blue-50/70 cursor-pointer flex items-center justify-between gap-2 transition text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-md bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                            ${emp.nama.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="text-slate-900 font-bold text-xs">${escapeHtml(emp.nama)}</div>
                            <div class="text-[10px] text-slate-500 font-mono">${escapeHtml(emp.nik)} &bull; ${escapeHtml(emp.dept)}</div>
                        </div>
                    </div>
                    <span class="px-2 py-0.2 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                        Sisa: ${emp.sisa_cuti}
                    </span>
                </div>
            `;
        });

        resBox.innerHTML = html;
        resBox.classList.remove('hidden');
    };

    window.selectEmployee = function(emp) {
        document.getElementById('target_employee_id').value = emp.id;
        document.getElementById('selAvatar').textContent = emp.nama.charAt(0).toUpperCase();
        document.getElementById('selNama').textContent = emp.nama;
        document.getElementById('selNik').textContent = 'NIK: ' + emp.nik;
        document.getElementById('selMeta').innerHTML = `${escapeHtml(emp.jabatan)} &bull; <span class="text-blue-600 font-semibold">${escapeHtml(emp.dept)}</span>`;
        document.getElementById('selSisaCuti').textContent = `Sisa: ${emp.sisa_cuti} Hari`;

        document.getElementById('searchBoxWrap').classList.add('hidden');
        document.getElementById('selectedEmpBanner').classList.remove('hidden');
        document.getElementById('empSearchResults').classList.add('hidden');

        userSisaCuti = emp.sisa_cuti;
        const curType = leaveTypesData.find(t => t.id === parseInt(inputType.value));
        const potong = curType ? curType.potong_kuota : 0;
        checkQuota(potong);
    };

    window.clearSelectedEmployee = function() {
        document.getElementById('selectedEmpBanner').classList.add('hidden');
        document.getElementById('searchBoxWrap').classList.remove('hidden');
        const searchInput = document.getElementById('empSearchKeyword');
        searchInput.value = '';
        searchInput.focus();
        document.getElementById('empSearchResults').classList.add('hidden');
    };

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        const searchBox = document.getElementById('searchBoxWrap');
        const resBox = document.getElementById('empSearchResults');
        if (searchBox && resBox && !searchBox.contains(e.target)) {
            resBox.classList.add('hidden');
        }
    });

    // Form submit validation check
    if (leaveForm) {
        leaveForm.addEventListener('submit', function(e) {
            if (currentNeedsAttach === 1 && (!attachmentInput.files || attachmentInput.files.length === 0)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Surat Dokter Wajib Dilampirkan!',
                    text: 'Untuk jenis cuti ini, Anda wajib mengunggah foto / scan Surat Keterangan Dokter asli.',
                    confirmButtonColor: '#e11d48',
                    confirmButtonText: 'Saya Mengerti'
                });
                dropZone.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
