<?php
/**
 * Settings & User Profile Screen (Synced with Flutter Mobile settings_screen.dart & Desktop)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Profil & Pengaturan Akun';
require_once __DIR__ . '/../layouts/header.php';

$pdo = getDbConnection();
$emp = getCurrentUser();
$appSettings = getAppSettings($pdo);

$userRole = strtolower($emp['role'] ?? 'operator');
$userLevel = (int)($emp['level_hierarki'] ?? 1);
$isSuperAdmin = ($userRole === 'superadmin' || $userRole === 'admin' || $userLevel >= 8);
$isAdmin = ($userRole === 'admin' || $userRole === 'hrd' || $userLevel >= 7);
$isManager = ($userRole === 'manager' || ($userLevel >= 5 && $userLevel <= 6));
$isLeaderOrSpv = ($userRole === 'supervisor' || $userRole === 'leader' || ($userLevel >= 3 && $userLevel <= 4));

$initial = strtoupper(substr($emp['nama_lengkap'] ?? 'U', 0, 1));
$masaKerja = hitungMasaKerja($emp['tanggal_masuk']);
?>

<div class="w-full space-y-4 sm:space-y-6">

    <!-- ========================================================================= -->
    <!-- 1. MOBILE VIEW (Screen < 1024px) - EXACT FLUTTER SETTINGS SCREEN         -->
    <!-- ========================================================================= -->
    <div class="block lg:hidden space-y-4 max-w-lg mx-auto pb-24">
        
        <!-- 1. iOS Profile Header Tile (Tapping opens full Profile Detail Screen) -->
        <a href="<?= BASE_URL ?>/index.php?page=profile-detail" 
           class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs flex items-center justify-between gap-3 cursor-pointer active:scale-[0.99] transition block">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-14 h-14 rounded-full bg-blue-600 text-white font-bold text-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                    <?= $initial ?>
                </div>
                <div class="min-w-0">
                    <h3 class="text-base font-bold text-slate-900 leading-tight truncate">
                        <?= htmlspecialchars($emp['nama_lengkap']) ?>
                    </h3>
                    <p class="text-xs text-slate-500 truncate mt-0.5">
                        <?= htmlspecialchars($emp['nik']) ?> &bull; <?= htmlspecialchars($emp['nama_jabatan']) ?>
                    </p>
                    <div class="mt-1.5">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-blue-50 text-[#0284C7] font-semibold text-[11px]">
                            Masa Kerja: <?= $masaKerja ?>
                        </span>
                    </div>
                </div>
            </div>
            <span class="material-symbols-rounded text-slate-400 text-lg flex-shrink-0">chevron_right</span>
        </a>

        <!-- Group 1: KEAMANAN & TAMPILAN -->
        <div class="space-y-1.5">
            <div class="px-2 text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
                KEAMANAN & TAMPILAN
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                
                <!-- Dark Mode Switch -->
                <div class="p-3.5 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div id="themeModeIconContainer" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 transition">
                            <span class="material-symbols-rounded text-lg" id="themeModeIcon">light_mode</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 leading-tight">Mode Tampilan</div>
                            <div class="text-[11px] text-slate-400 mt-0.5" id="themeModeLabel">Tema Terang Standar</div>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="darkModeToggleMobile" class="sr-only peer" onchange="toggleGlobalDarkMode()">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <!-- Ganti Password -->
                <div onclick="openChangePasswordModal()" class="p-3.5 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50/70 active:bg-slate-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-rounded text-lg">lock</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 leading-tight">Ganti Password</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Ubah kata sandi akun Anda</div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                </div>

                <!-- Hak Akses & Privilege -->
                <div onclick="openPrivilegeModal()" class="p-3.5 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50/70 active:bg-slate-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-rounded text-lg">shield</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 leading-tight">Hak Akses & Privilege</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Wewenang akun & modul sistem (<?= strtoupper($emp['role'] ?? 'STAFF') ?>)</div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                </div>

            </div>
        </div>

        <!-- Group: KONFIGURASI SUPER ADMIN (if Super Admin) -->
        <?php if ($isSuperAdmin): ?>
            <div class="space-y-1.5">
                <div class="px-2 text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
                    KONFIGURASI SUPER ADMIN
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                    <a href="<?= BASE_URL ?>/index.php?page=settings" class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/70 active:bg-slate-100 transition block">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-rounded text-lg">tune</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 leading-tight">Matriks Hak Akses & Privilege Role</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Konfigurasi wewenang 7 role karyawan secara dinamis</div>
                            </div>
                        </div>
                        <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Group 2: ADMINISTRASI HRD (if Admin/HRD) -->
        <?php if ($isAdmin): ?>
            <div class="space-y-1.5">
                <div class="px-2 text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
                    ADMINISTRASI HRD
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                    <a href="<?= BASE_URL ?>/index.php?page=employees" class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/70 active:bg-slate-100 transition block">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center flex-shrink-0">
                                <span class="material-symbols-rounded text-lg">group</span>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900 leading-tight">List Karyawan</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">Daftar pegawai & tambah karyawan baru</div>
                            </div>
                        </div>
                        <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Group 3: JARINGAN & SISTEM -->
        <div class="space-y-1.5">
            <div class="px-2 text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
                JARINGAN & SISTEM
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <div onclick="openServerInfoModal()" class="p-3.5 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50/70 active:bg-slate-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-rounded text-lg">settings_ethernet</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 leading-tight">Koneksi Server Database</div>
                            <div class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>MySQL Database Live Connected</span>
                            </div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                </div>
            </div>
        </div>

        <!-- Group 4: BANTUAN & PANDUAN -->
        <div class="space-y-1.5">
            <div class="px-2 text-[11.5px] font-bold text-slate-400 tracking-wider uppercase">
                BANTUAN & PANDUAN
            </div>
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs divide-y divide-slate-100 overflow-hidden">
                <!-- User Guide PDF -->
                <a href="<?= BASE_URL ?>/api/docs/panduan.php?role=<?= urlencode($userRole) ?>" target="_blank" 
                   class="p-3.5 flex items-center justify-between gap-3 hover:bg-slate-50/70 active:bg-slate-100 transition block">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-rounded text-lg">menu_book</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 leading-tight">Buku Panduan (<?= htmlspecialchars($emp['nama_jabatan'] ?? 'Peran') ?>)</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Panduan interaktif & unduh PDF sesuai role</div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                </a>

                <!-- About App -->
                <div onclick="openAboutAppModal()" class="p-3.5 flex items-center justify-between gap-3 cursor-pointer hover:bg-slate-50/70 active:bg-slate-100 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0">
                            <span class="material-symbols-rounded text-lg">info</span>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 leading-tight">Tentang Aplikasi</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">Informasi versi rilis v2.5 Pro & pengembang</div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-slate-400 text-lg">chevron_right</span>
                </div>
            </div>
        </div>

        <!-- Group 5: Logout Button -->
        <div class="pt-2">
            <button type="button" onclick="confirmLogout()" 
                    class="w-full py-3.5 rounded-2xl bg-white border border-slate-200/90 text-[#FF3B30] font-bold text-sm shadow-2xs active:scale-[0.99] transition text-center flex items-center justify-center gap-2">
                <span>Keluar dari Akun</span>
            </button>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- 2. DESKTOP VIEW (Screen >= 1024px) - FULL BENTO PROFILE & SETTINGS        -->
    <!-- ========================================================================= -->
    <div class="hidden lg:block space-y-6">
        <div class="grid grid-cols-12 gap-6">
            
            <!-- Profile Bento Left (4 Cols) -->
            <div class="col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft text-center flex flex-col items-center">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-blue-700 to-indigo-600 text-white font-black text-3xl flex items-center justify-center shadow-lg shadow-blue-600/30 mb-4">
                    <?= $initial ?>
                </div>
                <h3 class="text-base font-extrabold text-slate-900 leading-tight"><?= htmlspecialchars($emp['nama_lengkap']) ?></h3>
                <div class="mt-1 mb-3"><?= getRoleBadge($emp['role']) ?></div>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-mono font-bold text-xs">NIK: <?= htmlspecialchars($emp['nik']) ?></span>

                <div class="w-full mt-6 pt-4 border-t border-slate-100 space-y-3 text-xs text-left">
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Departemen</span>
                        <strong class="text-slate-800 font-bold"><?= htmlspecialchars($emp['nama_dept']) ?></strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Jabatan</span>
                        <strong class="text-slate-800 font-bold"><?= htmlspecialchars($emp['nama_jabatan']) ?></strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Masa Kerja</span>
                        <strong class="text-purple-600 font-bold"><?= $masaKerja ?></strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Jatah Kuota <?= date('Y') ?></span>
                        <strong class="text-slate-800 font-bold"><?= $emp['kuota_cuti'] ?> Hari</strong>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-400 font-medium">Terpakai Tahun Ini</span>
                        <strong class="text-amber-600 font-bold"><?= $emp['cuti_terpakai'] ?> Hari</strong>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400 font-medium">Sisa Saldo Cuti</span>
                        <strong class="text-blue-600 font-black text-sm"><?= $emp['sisa_cuti'] ?> Hari</strong>
                    </div>
                </div>
            </div>

            <!-- Settings Form Right (8 Cols) -->
            <div class="col-span-8 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-soft">
                <h3 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                    <span class="material-symbols-rounded text-blue-600 text-lg">edit</span>
                    <span>Pengaturan Kontak & Password Akun</span>
                </h3>

                <form action="<?= BASE_URL ?>/index.php?page=profile-update" method="POST" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Akun <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($emp['nama_lengkap']) ?>" required
                               placeholder="Masukkan nama lengkap Anda..."
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" required
                                   placeholder="nama@perusahaan.co.id"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Handphone / WhatsApp <span class="text-rose-500">*</span></label>
                            <input type="text" name="no_hp" value="<?= htmlspecialchars($emp['no_hp'] ?? '') ?>" required
                                   placeholder="081234567890"
                                   class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Domisili</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat tempat tinggal saat ini..." class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/15"><?= htmlspecialchars($emp['alamat'] ?? '') ?></textarea>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <span class="block font-bold text-slate-700 uppercase tracking-wider">Ganti Password Login (Opsional)</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-slate-500 mb-1">Password Baru</label>
                                <input type="password" name="new_password" placeholder="Kosongkan jika tidak diubah"
                                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="block text-slate-500 mb-1">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" placeholder="Ulangi password baru"
                                       class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 text-right">
                        <button type="submit" class="px-7 py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs shadow-md shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

<!-- 1. Modal Detail Profil Lengkap (Mobile) -->
<div id="profileDetailModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white dark:bg-[#1e293b] rounded-3xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-rounded text-blue-600 dark:text-sky-400 text-lg">badge</span>
                <span>Detail Profil Karyawan</span>
            </h3>
            <button type="button" onclick="closeModal('profileDetailModal')" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition">
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>

        <div class="space-y-3 text-xs">
            <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-100 dark:border-slate-700">
                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-lg flex items-center justify-center shadow-md">
                    <?= $initial ?>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm"><?= htmlspecialchars($emp['nama_lengkap']) ?></h4>
                    <p class="text-slate-500 dark:text-slate-400"><?= htmlspecialchars($emp['nik']) ?> &bull; <?= htmlspecialchars($emp['nama_jabatan']) ?></p>
                </div>
            </div>

            <div class="space-y-2 divide-y divide-slate-100 dark:divide-slate-800">
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Departemen</span>
                    <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($emp['nama_dept']) ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Tanggal Masuk</span>
                    <strong class="text-slate-800 dark:text-slate-200"><?= date('d M Y', strtotime($emp['tanggal_masuk'])) ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Masa Kerja</span>
                    <strong class="text-purple-600 dark:text-purple-400"><?= $masaKerja ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Sisa Kuota Cuti</span>
                    <strong class="text-blue-600 dark:text-sky-400 font-bold"><?= $emp['sisa_cuti'] ?> Hari</strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">No. WhatsApp</span>
                    <strong class="text-slate-800 dark:text-slate-200"><?= htmlspecialchars($emp['no_hp'] ?: '-') ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Email</span>
                    <strong class="text-slate-800 dark:text-slate-200 truncate max-w-[180px]"><?= htmlspecialchars($emp['email']) ?></strong>
                </div>
                <div class="flex justify-between py-1.5">
                    <span class="text-slate-400">Alamat</span>
                    <strong class="text-slate-800 dark:text-slate-200 text-right max-w-[180px]"><?= htmlspecialchars($emp['alamat'] ?: '-') ?></strong>
                </div>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" onclick="closeModal('profileDetailModal')" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- 2. Modal Ganti Password -->
<div id="changePasswordModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white dark:bg-[#1e293b] rounded-3xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-rounded text-orange-600 dark:text-amber-400 text-lg">lock</span>
                <span>Ganti Password Akun</span>
            </h3>
            <button type="button" onclick="closeModal('changePasswordModal')" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition">
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=profile-update" method="POST" class="space-y-3 text-xs">
            <input type="hidden" name="nama_lengkap" value="<?= htmlspecialchars($emp['nama_lengkap']) ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($emp['email']) ?>">
            <input type="hidden" name="no_hp" value="<?= htmlspecialchars($emp['no_hp'] ?? '') ?>">

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Password Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                <input type="password" name="confirm_password" required minlength="6" placeholder="Ulangi password baru" 
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeModal('changePasswordModal')" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white font-bold shadow-md transition">
                    Simpan Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Hak Akses & Privilege -->
<div id="privilegeModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white dark:bg-[#1e293b] rounded-3xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-3.5">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-rounded text-indigo-600 dark:text-indigo-400 text-lg">shield</span>
                <span>Hak Akses Akun</span>
            </h3>
            <button type="button" onclick="closeModal('privilegeModal')" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition">
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>

        <div class="space-y-2 text-xs">
            <div class="p-3.5 rounded-2xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900">
                <div class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider">Role Terdaftar</div>
                <div class="text-base font-extrabold text-indigo-950 dark:text-indigo-200 mt-0.5"><?= strtoupper($emp['role'] ?? 'OPERATOR') ?></div>
                <div class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">Tingkat Hierarki: <strong>Level <?= $userLevel ?></strong></div>
            </div>

            <div class="space-y-2 text-slate-600 dark:text-slate-300 pt-1">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-emerald-600 dark:text-emerald-400 text-base flex-shrink-0">check_circle</span>
                    <span>Pengajuan cuti pribadi & upload lampiran medis</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="material-symbols-rounded text-emerald-600 dark:text-emerald-400 text-base flex-shrink-0">check_circle</span>
                    <span>Monitoring kalender & sisa kuota cuti tahunan</span>
                </div>
                <?php if ($isLeaderOrSpv): ?>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-rounded text-blue-600 dark:text-sky-400 text-base flex-shrink-0">verified</span>
                        <span>Approval Tier 1 pengajuan cuti anggota departemen</span>
                    </div>
                <?php endif; ?>
                <?php if ($isManager): ?>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-rounded text-blue-600 dark:text-sky-400 text-base flex-shrink-0">verified</span>
                        <span>Approval Tier 2 lintas 15 departemen produksi</span>
                    </div>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-rounded text-purple-600 dark:text-purple-400 text-base flex-shrink-0">verified</span>
                        <span>Approval Final Tier 3 HRD & pemotongan kuota cuti resmi</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="material-symbols-rounded text-purple-600 dark:text-purple-400 text-base flex-shrink-0">verified</span>
                        <span>Manajemen data master karyawan & laporan rekapitulasi</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" onclick="closeModal('privilegeModal')" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- 4. Modal Tentang Aplikasi -->
<div id="aboutAppModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white dark:bg-[#1e293b] rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4 text-center">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-sky-950/60 text-blue-600 dark:text-sky-400 flex items-center justify-center mx-auto mb-1 shadow-md">
            <span class="material-symbols-rounded text-3xl">corporate_fare</span>
        </div>
        <div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white"><?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></h3>
            <p class="text-xs text-blue-600 dark:text-sky-400 font-bold mt-0.5">E-Cuti Karyawan v2.5 Pro</p>
            <p class="text-[11.5px] text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                Sistem Manajemen Permohonan & Persetujuan Cuti Bertingkat (Operator &bull; Leader &bull; Manager &bull; HRD) terintegrasi Webbase & Mobile Flutter.
            </p>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" onclick="closeModal('aboutAppModal')" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- 5. Modal Info Server -->
<div id="serverInfoModal" class="hidden fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-white dark:bg-[#1e293b] rounded-3xl p-5 sm:p-6 border border-slate-200 dark:border-slate-800 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <span class="material-symbols-rounded text-emerald-600 dark:text-emerald-400 text-lg">database</span>
                <span>Status Jaringan & Database</span>
            </h3>
            <button type="button" onclick="closeModal('serverInfoModal')" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white flex items-center justify-center transition">
                <span class="material-symbols-rounded text-base">close</span>
            </button>
        </div>
        <div class="space-y-2.5 text-xs">
            <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 space-y-1 border border-emerald-100 dark:border-emerald-900">
                <div class="font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Status Server: Online & Terhubung</span>
                </div>
                <div class="text-[11px] text-emerald-700 dark:text-emerald-400">Semua transaksi permohonan cuti dan approval tersimpan langsung ke database perusahaan.</div>
            </div>
            <div class="space-y-1.5 text-slate-600 dark:text-slate-300 pt-1">
                <div>URL Sistem: <strong class="text-slate-900 dark:text-white font-mono"><?= BASE_URL ?></strong></div>
                <div>Protokol: <strong class="text-slate-900 dark:text-white font-mono"><?= isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'HTTPS Secure' : 'HTTP Local/Intranet' ?></strong></div>
            </div>
        </div>
        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
            <button type="button" onclick="closeModal('serverInfoModal')" class="w-full py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
function openProfileDetailModal() {
    document.getElementById('profileDetailModal')?.classList.remove('hidden');
}

function openChangePasswordModal() {
    document.getElementById('changePasswordModal')?.classList.remove('hidden');
}

function openPrivilegeModal() {
    document.getElementById('privilegeModal')?.classList.remove('hidden');
}

function openAboutAppModal() {
    document.getElementById('aboutAppModal')?.classList.remove('hidden');
}

function openServerInfoModal() {
    document.getElementById('serverInfoModal')?.classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id)?.classList.add('hidden');
}

function toggleWebTheme(isDark) {
    // Dark mode toggle handler
    if (isDark) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
}

function confirmLogout() {
    Swal.fire({
        title: 'Keluar dari Akun?',
        text: 'Apakah Anda yakin ingin keluar dari sistem E-Cuti?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fa-solid fa-arrow-right-from-bracket mr-1.5"></i> Ya, Keluar',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= BASE_URL ?>/index.php?page=logout';
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
