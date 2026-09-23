<?php
/**
 * Create Leave Application View (Tailwind CSS Edition)
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
    'SSD'  => 'fa-solid fa-file-medical text-emerald-600 bg-emerald-50',
    'STSD' => 'fa-solid fa-head-side-cough text-amber-600 bg-amber-50',
    'CT'   => 'fa-solid fa-umbrella-beach text-blue-600 bg-blue-50',
    'CH'   => 'fa-solid fa-droplet text-rose-600 bg-rose-50',
    'CML'  => 'fa-solid fa-baby text-indigo-600 bg-indigo-50',
    'CKH'  => 'fa-solid fa-ribbon text-slate-700 bg-slate-100',
    'IJN'  => 'fa-solid fa-calendar-xmark text-purple-600 bg-purple-50',
];

$usedPercent = $currentUser['kuota_cuti'] > 0 ? round(($currentUser['cuti_terpakai'] / $currentUser['kuota_cuti']) * 100) : 0;
?>

<div class="w-full space-y-6">

    <!-- Top Bento User Balance Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-soft">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-xl flex items-center justify-center shadow-lg shadow-blue-600/30 flex-shrink-0">
                    <?= strtoupper(substr($currentUser['nama_lengkap'], 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-lg font-extrabold text-slate-900 leading-tight"><?= htmlspecialchars($currentUser['nama_lengkap']) ?></h3>
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-xs font-mono font-bold"><?= htmlspecialchars($currentUser['nik']) ?></span>
                    </div>
                    <div class="text-xs text-slate-500 font-medium mt-1 flex items-center gap-2 flex-wrap">
                        <span><i class="fa-solid fa-building text-blue-600 mr-1"></i> <?= htmlspecialchars($currentUser['nama_dept']) ?></span>
                        <span class="text-slate-300">&bull;</span>
                        <span><i class="fa-solid fa-briefcase text-slate-400 mr-1"></i> <?= htmlspecialchars($currentUser['nama_jabatan']) ?></span>
                        <span class="text-slate-300">&bull;</span>
                        <span><i class="fa-solid fa-hourglass-half text-amber-500 mr-1"></i> Masa Kerja: <strong class="text-slate-700"><?= hitungMasaKerja($currentUser['tanggal_masuk']) ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Balance Progress Widget -->
            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/60 min-w-[240px]">
                <div class="flex items-center justify-between text-xs font-bold mb-2">
                    <span class="text-slate-600">Hak Cuti Tahunan <?= date('Y') ?></span>
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-600 text-white text-xs font-black">Sisa: <?= (int)$currentUser['sisa_cuti'] ?> Hari</span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-400 to-amber-500 h-2.5 rounded-full transition-all" style="width: <?= $usedPercent ?>%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium mt-1.5">
                    <span>Terpakai: <strong class="text-slate-700"><?= (int)$currentUser['cuti_terpakai'] ?> Hari</strong></span>
                    <span>Total Kuota: <strong class="text-slate-700"><?= (int)$currentUser['kuota_cuti'] ?> Hari</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-6 sm:p-7 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Formulir Permohonan Cuti Karyawan</h2>
                    <p class="text-xs text-slate-500">Pilih jenis cuti dan lengkapi data permohonan izin Anda.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Riwayat
            </a>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=leave-submit" method="POST" enctype="multipart/form-data" id="leaveForm" class="p-6 sm:p-8 space-y-6">
            
            <?php if ($currentUser['role'] === 'admin'): ?>
                <!-- ADMIN HRD SPECIAL: ELEGANT LIGHT TARGET APPLICANT SELECTOR -->
                <div class="p-5 rounded-2xl bg-slate-50/80 border border-slate-200/90 space-y-4">
                    
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200/60 pb-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs shadow-2xs">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                            <div>
                                <h3 class="text-xs font-extrabold text-slate-900">Target Pemohon Cuti</h3>
                                <p class="text-[11px] text-slate-500">Ajukan cuti pribadi atau input atas nama karyawan absen/mangkir</p>
                            </div>
                        </div>

                        <!-- Segmented Switcher Pill -->
                        <div class="inline-flex rounded-xl bg-slate-200/70 p-1 border border-slate-200 text-xs font-semibold">
                            <button type="button" id="btnModeSelf" onclick="setApplicantMode('self')" 
                                    class="px-3 py-1.5 rounded-lg transition bg-white text-blue-700 font-bold shadow-2xs flex items-center gap-1.5">
                                <i class="fa-solid fa-user"></i> Diri Sendiri
                            </button>
                            <button type="button" id="btnModeOther" onclick="setApplicantMode('other')" 
                                    class="px-3 py-1.5 rounded-lg transition text-slate-600 hover:text-slate-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-users"></i> Atas Nama Karyawan Lain
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="target_employee_id" id="target_employee_id" value="<?= $currentUser['id'] ?>">

                    <!-- Search Input Box (Visible in 'other' mode) -->
                    <div id="otherEmployeeSearchWrap" class="hidden space-y-3">
                        
                        <!-- Selected Employee Banner (when selected) -->
                        <div id="selectedEmpBanner" class="hidden p-3.5 rounded-2xl bg-white border border-blue-200 shadow-2xs flex items-center justify-between gap-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div id="selAvatar" class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black text-sm flex items-center justify-center shadow-2xs flex-shrink-0">
                                    A
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <strong id="selNama" class="text-slate-900 text-xs font-extrabold">Nama Karyawan</strong>
                                        <span id="selNik" class="px-2 py-0.5 rounded-md bg-slate-100 font-mono text-[10.5px] text-slate-600 font-bold">NIK</span>
                                    </div>
                                    <div id="selMeta" class="text-[11px] text-slate-500 mt-0.5 font-medium">Departemen &bull; Jabatan</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span id="selSisaCuti" class="px-3 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200/80 font-black text-xs">
                                    Sisa: 12 Hari
                                </span>
                                <button type="button" onclick="clearSelectedEmployee()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-1">
                                    <i class="fa-solid fa-arrows-rotate text-blue-600"></i> Ganti
                                </button>
                            </div>
                        </div>

                        <!-- Live Search Input Box -->
                        <div id="searchBoxWrap" class="relative">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                <i class="fa-solid fa-magnifying-glass text-blue-600 mr-1"></i> Cari Karyawan (Ketik Nama / NIK / Departemen)
                            </label>
                            <div class="relative">
                                <input type="text" id="empSearchKeyword" oninput="handleEmpLiveSearch(this.value)" 
                                       placeholder="Ketik nama atau NIK karyawan (contoh: Budi / NAK-011 / Hendra)..." 
                                       class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition shadow-2xs">
                                <i class="fa-solid fa-search absolute left-3.5 top-3 text-slate-400 text-sm"></i>
                            </div>

                            <!-- Live Results Container Dropdown -->
                            <div id="empSearchResults" class="absolute left-0 right-0 top-full mt-1.5 bg-white rounded-2xl border border-slate-200 shadow-xl z-50 max-h-64 overflow-y-auto hidden divide-y divide-slate-100">
                                <!-- Dynamic results injected via JS -->
                            </div>
                        </div>
                    </div>

                    <!-- Direct Approval Checkbox -->
                    <div class="flex items-center justify-between pt-2 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="direct_approve" id="direct_approve_toggle" value="1" checked class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            <span class="font-bold text-slate-700">Verifikasi & Setujui Langsung oleh HRD (Berdasarkan Rekap Absensi)</span>
                        </label>
                    </div>

                </div>
            <?php endif; ?>

            <!-- 1. Pilihan Jenis Cuti -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                    1. Pilih Kategori / Jenis Cuti <span class="text-rose-500">*</span>
                </label>
                
                <input type="hidden" name="leave_type_id" id="leave_type_id" required>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <?php foreach ($leaveTypes as $type): 
                        $iconClass = $typeIcons[$type['kode']] ?? 'fa-solid fa-calendar-check text-blue-600 bg-blue-50';
                    ?>
                        <div class="leave-type-card relative flex items-start gap-3 p-4 rounded-2xl border-2 border-slate-200/80 bg-white hover:border-blue-400 hover:bg-blue-50/30 cursor-pointer transition transform active:scale-98"
                             data-id="<?= $type['id'] ?>"
                             data-potong="<?= $type['potong_kuota'] ?>"
                             data-attachment="<?= $type['butuh_lampiran'] ?>"
                             data-kode="<?= $type['kode'] ?>"
                             data-desc="<?= htmlspecialchars($type['deskripsi']) ?>">
                            
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0 <?= $iconClass ?>">
                                <i class="<?= explode(' ', $iconClass)[0] ?> <?= explode(' ', $iconClass)[1] ?>"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-extrabold text-xs text-slate-900 truncate"><?= htmlspecialchars($type['nama_cuti']) ?></div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    <?php if ($type['butuh_lampiran']): ?>
                                        <span class="text-rose-600 font-extrabold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">Wajib Surat Dokter</span>
                                    <?php elseif ($type['potong_kuota']): ?>
                                        <span class="text-blue-600 font-bold">Potong Kuota</span>
                                    <?php else: ?>
                                        <span class="text-emerald-600 font-bold">Cuti Khusus</span>
                                    <?php endif; ?>
                                    &bull; Max <?= $type['max_hari_default'] ?> Hari
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Info description of selected type -->
                <div id="typeSelectedBanner" class="mt-3 p-3.5 rounded-2xl bg-blue-50/80 border border-blue-200/80 text-xs text-blue-900 flex items-start gap-2.5 hidden">
                    <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 text-sm"></i>
                    <div id="typeSelectedText" class="leading-relaxed"></div>
                </div>

                <!-- Medical Attachment Requirement Warning -->
                <div id="doctorNoteAlert" class="mt-3 p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-xs text-rose-900 flex items-start gap-3 hidden animate-pulse">
                    <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center text-base flex-shrink-0">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div>
                        <strong class="font-extrabold text-rose-800 text-sm block mb-0.5">Wajib Melampirkan Surat Keterangan Dokter!</strong>
                        <p class="text-rose-700 leading-relaxed font-medium">
                            Untuk permohonan izin <strong>Sakit Surat Dokter</strong> / <strong>Cuti Melahirkan</strong>, Anda <u>wajib mengunggah foto / scan Surat Keterangan Dokter/Bidan asli</u> di bagian formulir bawah agar tidak dikenakan pemotongan jatah cuti atau gaji.
                        </p>
                    </div>
                </div>
            </div>

            <!-- 2. Periode Tanggal Cuti -->
            <div class="pt-4 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">
                    2. Periode Tanggal Cuti <span class="text-rose-500">*</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Mulai Cuti</label>
                        <div class="relative">
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" required min="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                        </div>
                    </div>

                    <div>
                        <label for="tanggal_selesai" class="block text-xs font-semibold text-slate-600 mb-1.5">Tanggal Selesai Cuti</label>
                        <div class="relative">
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" required min="<?= date('Y-m-d') ?>"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                        </div>
                    </div>
                </div>

                <!-- Live Duration Calculator Card -->
                <div class="mt-4 p-4 rounded-2xl bg-gradient-to-r from-slate-50 to-blue-50/50 border border-slate-200/80 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <div class="text-xs font-extrabold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-calculator text-blue-600"></i>
                            <span>Total Durasi Hari Kerja Cuti</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Dihitung otomatis (Hari Minggu otomatis dilewati).</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="total_hari" id="total_hari" value="0">
                        <span id="total_hari_display" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-extrabold text-sm shadow-md shadow-blue-600/30">
                            0 Hari Kerja
                        </span>
                    </div>
                </div>

                <div id="quotaWarning" class="mt-3 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center gap-2 hidden">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                    <span><strong>Perhatian:</strong> Jumlah hari cuti melebihi sisa kuota cuti tahunan Anda (Sisa: <?= $currentUser['sisa_cuti'] ?> hari).</span>
                </div>
            </div>

            <!-- 3. Alasan / Keperluan Cuti -->
            <div class="pt-4 border-t border-slate-100">
                <label for="alasan" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    3. Alasan / Keperluan Cuti <span class="text-rose-500">*</span>
                </label>
                <textarea name="alasan" id="alasan" rows="3" required placeholder="Tuliskan keterangan dan alasan keperluan pengajuan cuti secara jelas..."
                          class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition placeholder:text-slate-400"></textarea>
            </div>

            <!-- 4. Alamat & Kontak Darurat -->
            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="alamat_selama_cuti" class="block text-xs font-semibold text-slate-600 mb-1.5">Alamat Selama Cuti</label>
                    <input type="text" name="alamat_selama_cuti" id="alamat_selama_cuti" placeholder="Contoh: Rumah sendiri / Kampung halaman" value="<?= htmlspecialchars($currentUser['alamat'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                </div>
                <div>
                    <label for="kontak_darurat" class="block text-xs font-semibold text-slate-600 mb-1.5">No. HP / WhatsApp Darurat</label>
                    <input type="text" name="kontak_darurat" id="kontak_darurat" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($currentUser['no_hp'] ?? '') ?>"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                </div>
            </div>

            <!-- 5. Upload Lampiran (Surat Dokter / Bukti) -->
            <div class="pt-4 border-t border-slate-100" id="uploadAttachmentSection">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        5. Dokumen Lampiran / Surat Dokter <span id="lampiranReqLabel" class="text-rose-600 font-bold text-xs">(Wajib untuk Sakit Surat Dokter)</span>
                    </label>
                    <span id="mandatoryTag" class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[10.5px] font-extrabold hidden">
                        WAJIB DIUNGGAH
                    </span>
                </div>

                <div id="dropZoneBox" onclick="document.getElementById('attachment').click();" 
                     class="group border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-3xl p-6 text-center bg-slate-50/50 hover:bg-blue-50/30 cursor-pointer transition">
                    <div id="dropIconWrap" class="w-12 h-12 rounded-2xl bg-blue-100/80 text-blue-600 flex items-center justify-center text-xl mx-auto mb-2 group-hover:scale-110 transition">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div class="text-xs font-bold text-slate-800" id="fileUploadLabel">Klik untuk unggah Surat Keterangan Dokter atau dokumen pendukung</div>
                    <p class="text-[11px] text-slate-400 mt-1">Mendukung format JPG, PNG, atau PDF (Maksimal 5MB)</p>
                    <input type="file" name="attachment" id="attachment" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>

            <!-- Approval Routing Alert -->
            <div class="p-4 rounded-2xl bg-slate-900 text-white flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-600/30 text-blue-400 flex items-center justify-center text-lg flex-shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="text-xs">
                    <span class="font-bold text-slate-100">Alur Persetujuan Resmi (Department Workflow):</span>
                    <p class="text-slate-300 mt-0.5">Permohonan cuti ini akan secara otomatis diteruskan kepada <strong>Atasan Departemen <?= htmlspecialchars($currentUser['nama_dept']) ?></strong> untuk persetujuan.</p>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= BASE_URL ?>/index.php?page=leaves-my" class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Batal
                </a>
                <button type="submit" id="submitLeaveBtn" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-black text-sm shadow-lg shadow-rose-600/40 hover:shadow-rose-600/60 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Kirim Permohonan Cuti</span>
                </button>
            </div>

        </form>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.leave-type-card');
    const inputType = document.getElementById('leave_type_id');
    const banner = document.getElementById('typeSelectedBanner');
    const bannerText = document.getElementById('typeSelectedText');
    const docAlert = document.getElementById('doctorNoteAlert');
    const lampiranReq = document.getElementById('lampiranReqLabel');
    const mandatoryTag = document.getElementById('mandatoryTag');
    const attachmentInput = document.getElementById('attachment');
    const fileLabel = document.getElementById('fileUploadLabel');
    const dropZone = document.getElementById('dropZoneBox');
    const dropIconWrap = document.getElementById('dropIconWrap');
    const quotaWarning = document.getElementById('quotaWarning');
    const totalDaysInput = document.getElementById('total_hari');
    const userSisaCuti = <?= (int)$currentUser['sisa_cuti'] ?>;
    const leaveForm = document.getElementById('leaveForm');

    let currentNeedsAttach = 0;

    // File input preview
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileLabel.innerHTML = `<span class="text-emerald-600 font-extrabold text-sm block"><i class="fa-solid fa-file-circle-check mr-1.5"></i> ${this.files[0].name} (${(this.files[0].size / 1024).toFixed(1)} KB)</span><span class="text-[11px] text-slate-500">File surat dokter siap dikirim</span>`;
                dropZone.classList.remove('border-rose-400', 'bg-rose-50/40');
                dropZone.classList.add('border-emerald-500', 'bg-emerald-50/30');
            } else {
                fileLabel.innerText = 'Klik untuk unggah Surat Keterangan Dokter atau dokumen pendukung';
                dropZone.classList.remove('border-emerald-500', 'bg-emerald-50/30');
            }
        });
    }

    // Leave Type Cards Interaction
    cards.forEach((card, idx) => {
        if (idx === 0) selectCard(card);
        card.addEventListener('click', () => selectCard(card));
    });

    function selectCard(selectedCard) {
        cards.forEach(c => {
            c.classList.remove('border-blue-600', 'bg-blue-50/60', 'ring-2', 'ring-blue-600/30');
            c.classList.add('border-slate-200/80', 'bg-white');
        });

        selectedCard.classList.remove('border-slate-200/80', 'bg-white');
        selectedCard.classList.add('border-blue-600', 'bg-blue-50/60', 'ring-2', 'ring-blue-600/30');

        const id = selectedCard.getAttribute('data-id');
        const desc = selectedCard.getAttribute('data-desc');
        const potong = parseInt(selectedCard.getAttribute('data-potong') || 0);
        const needsAttach = parseInt(selectedCard.getAttribute('data-attachment') || 0);
        currentNeedsAttach = needsAttach;

        inputType.value = id;

        if (desc) {
            bannerText.innerHTML = `<strong>Keterangan:</strong> ${desc}`;
            banner.classList.remove('hidden');
        } else {
            banner.classList.add('hidden');
        }

        // Handle mandatory medical document requirement
        if (needsAttach === 1) {
            docAlert.classList.remove('hidden');
            lampiranReq.innerHTML = '<strong class="text-rose-600 font-extrabold">(WAJIB DILAMPIRKAN)</strong>';
            mandatoryTag.classList.remove('hidden');
            attachmentInput.required = true;
            dropZone.classList.add('border-rose-400', 'bg-rose-50/30');
            dropIconWrap.classList.remove('bg-blue-100/80', 'text-blue-600');
            dropIconWrap.classList.add('bg-rose-100', 'text-rose-600');
        } else {
            docAlert.classList.add('hidden');
            lampiranReq.innerHTML = '<span class="text-slate-400 font-medium">(Opsional)</span>';
            mandatoryTag.classList.add('hidden');
            attachmentInput.required = false;
            dropZone.classList.remove('border-rose-400', 'bg-rose-50/30');
            dropIconWrap.classList.remove('bg-rose-100', 'text-rose-600');
            dropIconWrap.classList.add('bg-blue-100/80', 'text-blue-600');
        }

        checkQuota(potong);
    }

    function checkQuota(potong) {
        const totalHari = parseInt(totalDaysInput.value || 0);
        if (potong === 1 && totalHari > userSisaCuti) {
            quotaWarning.classList.remove('hidden');
        } else {
            quotaWarning.classList.add('hidden');
        }
    }

    document.getElementById('tanggal_mulai').addEventListener('change', () => {
        const sel = document.querySelector('.leave-type-card.border-blue-600');
        const potong = sel ? parseInt(sel.getAttribute('data-potong') || 0) : 0;
        setTimeout(() => checkQuota(potong), 150);
    });

    document.getElementById('tanggal_selesai').addEventListener('change', () => {
        const sel = document.querySelector('.leave-type-card.border-blue-600');
        const potong = sel ? parseInt(sel.getAttribute('data-potong') || 0) : 0;
        setTimeout(() => checkQuota(potong), 150);
    });

    const allEmployeesList = <?= json_encode(array_map(function($e) {
        return [
            'id' => (int)$e['id'],
            'nik' => $e['nik'],
            'nama' => $e['nama_lengkap'],
            'dept' => $e['nama_dept'],
            'jabatan' => $e['nama_jabatan'],
            'sisa_cuti' => (int)$e['sisa_cuti'],
            'kuota_cuti' => (int)$e['kuota_cuti'],
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
            btnSelf.className = 'px-3 py-1.5 rounded-lg transition bg-white text-blue-700 font-bold shadow-2xs flex items-center gap-1.5';
            btnOther.className = 'px-3 py-1.5 rounded-lg transition text-slate-600 hover:text-slate-900 flex items-center gap-1.5';
            searchWrap.classList.add('hidden');
            targetInput.value = currentUserId;
            userSisaCuti = currentUserQuota;
        } else {
            btnOther.className = 'px-3 py-1.5 rounded-lg transition bg-white text-blue-700 font-bold shadow-2xs flex items-center gap-1.5';
            btnSelf.className = 'px-3 py-1.5 rounded-lg transition text-slate-600 hover:text-slate-900 flex items-center gap-1.5';
            searchWrap.classList.remove('hidden');
            const searchInput = document.getElementById('empSearchKeyword');
            if (searchInput) searchInput.focus();
        }
        const sel = document.querySelector('.leave-type-card.border-blue-600');
        const potong = sel ? parseInt(sel.getAttribute('data-potong') || 0) : 0;
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
            resBox.innerHTML = '<div class="p-4 text-center text-xs text-slate-500 font-medium">Tidak ada karyawan yang cocok dengan pencarian "<strong>' + escapeHtml(keyword) + '</strong>"</div>';
            resBox.classList.remove('hidden');
            return;
        }

        let html = '';
        matches.slice(0, 10).forEach(emp => {
            html += `
                <div onclick='selectEmployee(${JSON.stringify(emp)})' 
                     class="p-3 hover:bg-blue-50/70 cursor-pointer flex items-center justify-between gap-3 transition">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-black text-xs flex items-center justify-center flex-shrink-0">
                            ${emp.nama.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="text-slate-900 font-bold text-xs">${escapeHtml(emp.nama)}</div>
                            <div class="text-[10.5px] text-slate-500 font-mono">NIK: <strong class="text-slate-700">${escapeHtml(emp.nik)}</strong> &bull; ${escapeHtml(emp.jabatan)} (<span class="text-blue-600 font-semibold">${escapeHtml(emp.dept)}</span>)</div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 text-right">
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10.5px] font-black border border-emerald-200">
                            Sisa: ${emp.sisa_cuti} Hari
                        </span>
                    </div>
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
        document.getElementById('selSisaCuti').textContent = `Sisa Cuti: ${emp.sisa_cuti} Hari`;

        document.getElementById('searchBoxWrap').classList.add('hidden');
        document.getElementById('selectedEmpBanner').classList.remove('hidden');
        document.getElementById('empSearchResults').classList.add('hidden');

        userSisaCuti = emp.sisa_cuti;

        const sel = document.querySelector('.leave-type-card.border-blue-600');
        const potong = sel ? parseInt(sel.getAttribute('data-potong') || 0) : 0;
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
                    text: 'Untuk jenis cuti ini, Anda wajib mengunggah foto / scan Surat Keterangan Dokter asli. Jika tidak ada surat dokter, silakan pilih opsi Sakit Tanpa Surat Dokter.',
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
