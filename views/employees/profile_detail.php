<?php
/**
 * Employee Profile Detail Screen (Exact Flutter profile_detail_screen.dart Parity)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Data Profil Karyawan';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$userId = $currentUser['id'];

// Fetch fresh employee record
$stmt = $pdo->prepare("
    SELECT k.*, d.nama_dept, d.kode_dept, j.nama_jabatan 
    FROM karyawan k
    LEFT JOIN departemen d ON k.departemen_id = d.id
    LEFT JOIN jabatan j ON k.jabatan_id = j.id
    WHERE k.id = ?
");
$stmt->execute([$userId]);
$emp = $stmt->fetch() ?: $currentUser;

$initial = strtoupper(substr($emp['nama_lengkap'] ?? 'U', 0, 1));
$masaKerja = hitungMasaKerja($emp['tanggal_masuk']);
?>

<div class="w-full max-w-lg mx-auto pb-24 space-y-5">

    <!-- Top Center Avatar & Name Card (Exact Flutter Layout) -->
    <div class="flex flex-col items-center justify-center text-center pt-2">
        <div class="w-20 h-20 rounded-full bg-blue-600 dark:bg-sky-600 text-white font-bold text-3xl flex items-center justify-center shadow-md mb-3" id="displayAvatarInitial">
            <?= $initial ?>
        </div>
        <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-tight" id="displayNamaLengkap">
            <?= htmlspecialchars($emp['nama_lengkap'] ?? '-') ?>
        </h2>
        <p class="text-xs text-slate-400 font-medium mt-1">
            <?= htmlspecialchars($emp['nik'] ?? '') ?> &bull; <?= htmlspecialchars($emp['nama_jabatan'] ?? '') ?>
        </p>
    </div>

    <!-- Group 1: DATA KEPEGAWAIAN (Locked / HRD Managed) -->
    <div class="space-y-1.5">
        <div class="px-2 flex items-center justify-between text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
            <span>DATA KEPEGAWAIAN</span>
            <span class="text-[10.5px] text-slate-400 flex items-center gap-1 font-semibold lowercase">
                <i class="fa-solid fa-lock text-[10px]"></i> resmi hrd
            </span>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs divide-y divide-slate-100 dark:divide-slate-800/80 overflow-hidden text-xs">
            
            <!-- Lama Bekerja -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">timer</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Lama Bekerja</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= $masaKerja ?></span>
            </div>

            <!-- Tanggal Masuk -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-sky-950/50 text-blue-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">calendar_month</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Tanggal Masuk</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= htmlspecialchars($emp['tanggal_masuk'] ?? '-') ?></span>
            </div>

            <!-- Departemen -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">apartment</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Departemen</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= htmlspecialchars($emp['nama_dept'] ?? '-') ?></span>
            </div>

            <!-- Jabatan -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">work</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Jabatan</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= htmlspecialchars($emp['nama_jabatan'] ?? '-') ?></span>
            </div>

            <!-- Role Hak Akses -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">label</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Role Hak Akses</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right uppercase"><?= htmlspecialchars($emp['role'] ?? 'OPERATOR') ?></span>
            </div>

        </div>
    </div>

    <!-- Group 2: HAK & KUOTA CUTI TAHUNAN (Locked / HRD Managed) -->
    <div class="space-y-1.5">
        <div class="px-2 flex items-center justify-between text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
            <span>HAK & KUOTA CUTI TAHUNAN</span>
            <span class="text-[10.5px] text-slate-400 flex items-center gap-1 font-semibold lowercase">
                <i class="fa-solid fa-lock text-[10px]"></i> resmi hrd
            </span>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs divide-y divide-slate-100 dark:divide-slate-800/80 overflow-hidden text-xs">
            
            <!-- Sisa Kuota Cuti -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-sky-950/50 text-blue-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">pie_chart</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Sisa Kuota Cuti</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= (int)($emp['sisa_cuti'] ?? 0) ?> Hari</span>
            </div>

            <!-- Cuti Terpakai -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">arrow_circle_right</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Cuti Terpakai</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= (int)($emp['cuti_terpakai'] ?? 0) ?> Hari</span>
            </div>

            <!-- Total Hak Cuti -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">shopping_bag</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Total Hak Cuti</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right"><?= (int)($emp['kuota_cuti'] ?? 12) ?> Hari</span>
            </div>

        </div>
    </div>

    <!-- Group 3: KONTAK & BIODATA (Editable by Employee) -->
    <div class="space-y-1.5">
        <div class="px-2 flex items-center justify-between text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
            <span>KONTAK & BIODATA</span>
            <button type="button" onclick="openEditBiodataModal()" class="text-[12px] font-bold text-blue-600 dark:text-sky-400 hover:underline flex items-center gap-1 cursor-pointer">
                <i class="fa-solid fa-pen-to-square"></i> Edit
            </button>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs divide-y divide-slate-100 dark:divide-slate-800/80 overflow-hidden text-xs">
            
            <!-- Email -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-sky-950/50 text-blue-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">mail</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Email</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[180px]" id="displayEmail"><?= htmlspecialchars($emp['email'] ?? '-') ?></span>
            </div>

            <!-- Nomor HP / WhatsApp -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">call</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Nomor WhatsApp / HP</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right" id="displayNoHp"><?= htmlspecialchars($emp['no_hp'] ?? '-') ?></span>
            </div>

            <!-- Alamat -->
            <div class="px-4 py-3 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">location_on</span>
                    </div>
                    <span class="text-slate-500 dark:text-slate-400 font-medium">Alamat Domisili</span>
                </div>
                <span class="font-bold text-slate-900 dark:text-white text-right truncate max-w-[180px]" id="displayAlamat"><?= htmlspecialchars($emp['alamat'] ?: '-') ?></span>
            </div>

        </div>
    </div>

    <!-- Group 4: KEAMANAN & KATA SANDI -->
    <div class="space-y-1.5">
        <div class="px-2 flex items-center justify-between text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
            <span>KEAMANAN & KATA SANDI</span>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden text-xs">
            <button type="button" onclick="openChangePasswordModal()" class="w-full px-4 py-3.5 flex items-center justify-between gap-3 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-rounded text-lg">lock</span>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 dark:text-white text-xs">Ganti Password Akun</div>
                        <div class="text-[11px] text-slate-400">Ubah kata sandi untuk login ke sistem</div>
                    </div>
                </div>
                <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
            </button>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <button type="button" onclick="openEditBiodataModal()" 
                class="w-full py-3 px-4 rounded-2xl border-2 border-blue-600 dark:border-sky-500 hover:bg-blue-50 dark:hover:bg-sky-950/40 text-blue-600 dark:text-sky-400 font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.99] cursor-pointer shadow-xs">
            <span class="material-symbols-rounded text-lg">edit</span>
            <span>Ubah Data Diri</span>
        </button>
        <button type="button" onclick="openChangePasswordModal()" 
                class="w-full py-3 px-4 rounded-2xl bg-slate-900 dark:bg-slate-800 hover:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition active:scale-[0.99] cursor-pointer shadow-xs">
            <span class="material-symbols-rounded text-lg">lock_reset</span>
            <span>Ganti Password</span>
        </button>
    </div>

</div>

<!-- Modern Edit Biodata Modal Dialog -->
<div id="modalEditBiodata" class="fixed inset-0 z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md bg-white dark:bg-[#1e293b] rounded-t-3xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 relative overflow-hidden animate-in fade-in zoom-in-95 duration-150 space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-sky-950/50 text-blue-600 dark:text-sky-400 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-rounded text-xl">person_edit</span>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight">Edit Data Pribadi</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Ubah nama, no WA, email & alamat</p>
                </div>
            </div>
            <button type="button" onclick="closeEditBiodataModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- HRD Notice Banner -->
        <div class="p-3 rounded-xl bg-blue-50/80 dark:bg-slate-900/60 border border-blue-200/70 dark:border-slate-700 text-[11px] text-blue-900 dark:text-slate-300 flex items-start gap-2.5">
            <span class="material-symbols-rounded text-blue-600 dark:text-sky-400 text-base flex-shrink-0 mt-0.5">lock</span>
            <p class="leading-relaxed">
                Data kepegawaian seperti <strong>Departemen, Jabatan, Tanggal Masuk</strong>, dan <strong>Kuota Cuti</strong> dikelola secara terpusat oleh HRD.
            </p>
        </div>

        <!-- Form -->
        <form id="formEditBiodata" onsubmit="submitEditBiodata(event)" class="space-y-4">
            
            <!-- 1. Nama Lengkap -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">person</span>
                    <input type="text" id="inputEditNama" name="nama_lengkap" value="<?= htmlspecialchars($emp['nama_lengkap'] ?? '') ?>" required
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <!-- 2. No. WhatsApp / HP -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nomor WhatsApp / HP</label>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">phone</span>
                    <input type="tel" id="inputEditNoHp" name="no_hp" value="<?= htmlspecialchars($emp['no_hp'] ?? '') ?>" required
                           placeholder="Contoh: 08123456789"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <!-- 3. Email -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email</label>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">mail</span>
                    <input type="email" id="inputEditEmail" name="email" value="<?= htmlspecialchars($emp['email'] ?? '') ?>" required
                           placeholder="email@nakakin.co.id"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-semibold focus:outline-none focus:border-blue-500 transition">
                </div>
            </div>

            <!-- 4. Alamat Domisili -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Domisili</label>
                <div class="relative">
                    <span class="material-symbols-rounded absolute left-3.5 top-3 text-slate-400 text-base pointer-events-none">location_on</span>
                    <textarea id="inputEditAlamat" name="alamat" rows="2"
                              placeholder="Alamat domisili saat ini"
                              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs font-medium focus:outline-none focus:border-blue-500 transition resize-none"><?= htmlspecialchars($emp['alamat'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditBiodataModal()" 
                        class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitEditBiodata"
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 dark:bg-sky-600 dark:hover:bg-sky-500 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-rounded text-base">save</span>
                    <span>Simpan Perubahan</span>
                </button>
            </div>

        </form>

    </div>
</div>

<!-- 2. Modern Change Password Modal Dialog -->
<div id="modalChangePassword" class="fixed inset-0 z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity">
    <div class="w-full max-w-md bg-white dark:bg-[#1e293b] rounded-t-3xl sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl p-6 relative overflow-hidden animate-in fade-in zoom-in-95 duration-150 space-y-5">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-rounded text-xl">lock_reset</span>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight">Ganti Password Akun</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Perbarui kata sandi untuk keamanan akun Anda</p>
                </div>
            </div>
            <button type="button" onclick="closeChangePasswordModal()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Form -->
        <form onsubmit="submitChangePassword(event)" class="space-y-4 text-xs">
            
            <!-- Password Saat Ini -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[11px]">
                    Password Saat Ini <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="inputCurrentPass" required 
                           placeholder="Masukkan password saat ini" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none transition pr-10">
                    <button type="button" onclick="togglePassVisibility('inputCurrentPass', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-rounded text-lg">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Password Baru -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[11px]">
                    Password Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="inputNewPass" required minlength="6" 
                           placeholder="Minimal 6 karakter" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none transition pr-10">
                    <button type="button" onclick="togglePassVisibility('inputNewPass', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-rounded text-lg">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Konfirmasi Password Baru -->
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5 text-[11px]">
                    Konfirmasi Password Baru <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" id="inputConfirmPass" required minlength="6" 
                           placeholder="Ulangi password baru Anda" 
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-amber-500 focus:outline-none transition pr-10">
                    <button type="button" onclick="togglePassVisibility('inputConfirmPass', this)" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-rounded text-lg">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeChangePasswordModal()" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Batal
                </button>
                <button type="submit" id="btnSubmitChangePass"
                        class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition shadow-md shadow-amber-500/20 flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-rounded text-base">lock_reset</span>
                    <span>Simpan Password Baru</span>
                </button>
            </div>

        </form>

    </div>
</div>

<script>
    function openEditBiodataModal() {
        const modal = document.getElementById('modalEditBiodata');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function closeEditBiodataModal() {
        const modal = document.getElementById('modalEditBiodata');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function openChangePasswordModal() {
        const modal = document.getElementById('modalChangePassword');
        if (modal) modal.classList.remove('hidden');
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('modalChangePassword');
        if (modal) {
            modal.classList.add('hidden');
            document.getElementById('inputCurrentPass').value = '';
            document.getElementById('inputNewPass').value = '';
            document.getElementById('inputConfirmPass').value = '';
        }
    }

    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('.material-symbols-rounded');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.innerText = 'visibility_off';
        } else {
            input.type = 'password';
            if (icon) icon.innerText = 'visibility';
        }
    }

    // Submit Change Password via API
    async function submitChangePassword(e) {
        e.preventDefault();
        const currentPass = document.getElementById('inputCurrentPass').value;
        const newPass = document.getElementById('inputNewPass').value;
        const confirmPass = document.getElementById('inputConfirmPass').value;

        if (newPass !== confirmPass) {
            Swal.fire({
                icon: 'error',
                title: 'Konfirmasi Salah',
                text: 'Konfirmasi password baru tidak cocok dengan password baru!'
            });
            return;
        }

        const btn = document.getElementById('btnSubmitChangePass');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';

        try {
            const response = await fetch('<?= BASE_URL ?>/api/auth/change_password.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    current_password: currentPass,
                    new_password: newPass,
                    confirm_password: confirmPass
                })
            });

            const result = await response.json();
            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (result.success) {
                closeChangePasswordModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Password Berhasil Diubah!',
                    text: 'Silakan gunakan password baru Anda untuk login selanjutnya.',
                    timer: 2500,
                    showConfirmButton: false
                });
            } else {
                let errorMsg = result.message || 'Gagal mengubah password.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Ganti Password',
                    html: errorMsg
                });
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Gagal',
                text: 'Terjadi kesalahan: ' + err.message
            });
        }
    }

    // Submit Edit Biodata via API
    async function submitEditBiodata(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitEditBiodata');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

        const nama = document.getElementById('inputEditNama').value.trim();
        const noHp = document.getElementById('inputEditNoHp').value.trim();
        const email = document.getElementById('inputEditEmail').value.trim();
        const alamat = document.getElementById('inputEditAlamat').value.trim();

        try {
            const response = await fetch('<?= BASE_URL ?>/api/auth/update_profile.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    nama_lengkap: nama,
                    no_hp: noHp,
                    email: email,
                    alamat: alamat
                })
            });

            const result = await response.json();

            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (result.success) {
                closeEditBiodataModal();

                // Update text elements live on page
                document.getElementById('displayNamaLengkap').innerText = nama;
                document.getElementById('displayEmail').innerText = email || '-';
                document.getElementById('displayNoHp').innerText = noHp || '-';
                document.getElementById('displayAlamat').innerText = alamat || '-';
                if (nama.length > 0) {
                    document.getElementById('displayAvatarInitial').innerText = nama.charAt(0).toUpperCase();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: result.message || 'Data profil Anda berhasil diperbarui!',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                let errorMsg = result.message || 'Gagal memperbarui data profil.';
                if (result.errors) {
                    errorMsg = Object.values(result.errors).join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    html: errorMsg
                });
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Gagal',
                text: 'Terjadi kesalahan koneksi ke server: ' + err.message
            });
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
