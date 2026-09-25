<?php
/**
 * Application & Company Settings View
 * PT. Nakakin Indonesia / White-label System Configuration
 */

require_once __DIR__ . '/../layouts/header.php';

$appSettings = getAppSettings($pdo);
$logoPath = !empty($appSettings['logo']) ? BASE_URL . '/assets/images/' . $appSettings['logo'] : BASE_URL . '/assets/images/Nakakin.png';
$faviconPath = !empty($appSettings['favicon']) ? BASE_URL . '/assets/images/' . $appSettings['favicon'] : BASE_URL . '/assets/images/Nakakin.png';
?>

<div class="w-full space-y-7">

    <!-- Top Bento Page Header Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-soft relative overflow-hidden">
        <!-- Ambient Glow -->
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-0 right-1/3 w-40 h-40 bg-rose-600/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 relative z-10">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-500 to-indigo-600 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-blue-500/30 flex-shrink-0">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">Pengaturan Aplikasi & Perusahaan</h2>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-400/30 text-xs font-bold font-mono">White-label Ready</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">Ubah nama sistem, identitas perusahaan, logo, serta format surat perizinan cuti tanpa perlu mengubah kode sumber.</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="<?= BASE_URL ?>/index.php?page=dashboard" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-1.5 backdrop-blur-md">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-px">
        <button type="button" onclick="switchSettingTab('general')" id="tabBtnGeneral" class="px-5 py-3 rounded-2xl text-xs font-black transition-all duration-200 flex items-center gap-2 bg-blue-600 text-white shadow-md shadow-blue-500/20">
            <i class="fa-solid fa-building"></i> Identitas & Kop Surat
        </button>
        <button type="button" onclick="switchSettingTab('privileges')" id="tabBtnPrivileges" class="px-5 py-3 rounded-2xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all duration-200 flex items-center gap-2">
            <i class="fa-solid fa-shield-halved"></i> Matriks Hak Akses & Privilege Role
            <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black">Baru</span>
        </button>
    </div>

    <!-- TAB 1: Main Settings Form -->
    <div id="tabContentGeneral">
    <form action="<?= BASE_URL ?>/index.php?page=settings-update" method="POST" enctype="multipart/form-data" id="formSettings" class="space-y-6">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left & Middle: Input Forms (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Identitas Perusahaan -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft p-6 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-building"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">1. Identitas & Profil Perusahaan</h3>
                            <p class="text-xs text-slate-500">Informasi resmi perusahaan yang akan tertera pada kop surat dan footer sistem.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Resmi Perusahaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_perusahaan" id="inNamaPerusahaan" required 
                                   value="<?= htmlspecialchars($appSettings['nama_perusahaan']) ?>"
                                   oninput="updateLivePreview()"
                                   placeholder="Contoh: PT. Nakakin Indonesia" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Singkatan Utama Brand <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="singkatan_perusahaan" id="inSingkatanPerusahaan" required 
                                   value="<?= htmlspecialchars($appSettings['singkatan_perusahaan']) ?>"
                                   oninput="updateLivePreview()"
                                   placeholder="Contoh: NAKAKIN" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition uppercase">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Sub-Singkatan / Wilayah
                            </label>
                            <input type="text" name="sub_singkatan_perusahaan" id="inSubSingkatan" 
                                   value="<?= htmlspecialchars($appSettings['sub_singkatan_perusahaan'] ?? '') ?>"
                                   oninput="updateLivePreview()"
                                   placeholder="Contoh: INDONESIA / JAKARTA" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition uppercase">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Tagline / Bidang Usaha Perusahaan
                            </label>
                            <input type="text" name="tagline" id="inTagline" 
                                   value="<?= htmlspecialchars($appSettings['tagline'] ?? '') ?>"
                                   oninput="updateLivePreview()"
                                   placeholder="Contoh: Precision Machinery, Pumps & Tooling Manufacturing" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Lengkap Pabrik / Kantor
                            </label>
                            <textarea name="alamat_perusahaan" id="inAlamat" rows="2" 
                                      oninput="updateLivePreview()"
                                      placeholder="Alamat kantor yang dicetak pada kop surat..."
                                      class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition resize-none"><?= htmlspecialchars($appSettings['alamat_perusahaan'] ?? '') ?></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                No. Telepon / Hotline
                            </label>
                            <input type="text" name="telepon" id="inTelepon" 
                                   value="<?= htmlspecialchars($appSettings['telepon'] ?? '') ?>"
                                   placeholder="(0267) 845-XXXX" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Email Resmi HRD / Perusahaan
                            </label>
                            <input type="email" name="email_perusahaan" id="inEmail" 
                                   value="<?= htmlspecialchars($appSettings['email_perusahaan'] ?? '') ?>"
                                   placeholder="hrd@perusahaan.co.id" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Website Resmi Perusahaan
                            </label>
                            <input type="text" name="website" id="inWebsite" 
                                   value="<?= htmlspecialchars($appSettings['website'] ?? '') ?>"
                                   placeholder="www.perusahaan.co.id" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- 2. Pengaturan Branding & Logo Aplikasi -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft p-6 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-palette"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">2. Branding & Logo Aplikasi</h3>
                            <p class="text-xs text-slate-500">Kustomisasi judul sistem di browser, badge sidebar, dan logo resmi.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Judul / Nama Aplikasi <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="nama_aplikasi" id="inNamaAplikasi" required 
                                   value="<?= htmlspecialchars($appSettings['nama_aplikasi']) ?>"
                                   oninput="updateLivePreview()"
                                   placeholder="Contoh: Sistem Informasi Cuti Karyawan" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Badge Singkat Sidebar <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="singkatan_aplikasi" id="inSingkatanAplikasi" required 
                                   value="<?= htmlspecialchars($appSettings['singkatan_aplikasi']) ?>"
                                   oninput="updateLivePreview()"
                                   placeholder="Contoh: FORM CUTI ONLINE / E-CUTI" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition uppercase">
                        </div>

                        <!-- Upload Logo Baru -->
                        <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 p-2 flex items-center justify-center shadow-2xs flex-shrink-0">
                                    <img id="logoPreviewImg" src="<?= $logoPath ?>" alt="Logo Perusahaan" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="flex-1 space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-800">
                                        Upload File Logo Perusahaan (Header, Login & Kop Surat)
                                    </label>
                                    <input type="file" name="logo" id="inLogoFile" accept="image/png, image/jpeg, image/webp, image/svg+xml"
                                           onchange="previewLogoFile(this)"
                                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer file:transition">
                                    <p class="text-[11px] text-slate-400">Direkomendasikan format PNG transparan atau SVG (Maks. 2MB).</p>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Favicon -->
                        <div class="sm:col-span-2 p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 p-1.5 flex items-center justify-center shadow-2xs flex-shrink-0">
                                    <img id="favPreviewImg" src="<?= $faviconPath ?>" alt="Favicon" class="max-h-full max-w-full object-contain">
                                </div>
                                <div class="flex-1 space-y-1.5">
                                    <label class="block text-xs font-bold text-slate-800">
                                        Upload Favicon (Ikon Tab Browser)
                                    </label>
                                    <input type="file" name="favicon" id="inFavFile" accept="image/x-icon, image/png, image/jpeg, image/webp"
                                           onchange="previewFavFile(this)"
                                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-700 file:text-white hover:file:bg-slate-800 file:cursor-pointer file:transition">
                                    <p class="text-[11px] text-slate-400">Ikon kecil tab browser (ICO / PNG rasio 1:1, misal 64x64 px).</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 3. Format Surat & Standard HRD -->
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft p-6 sm:p-7 space-y-5">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">3. Administrasi Surat Cuti & Pejabat HRD</h3>
                            <p class="text-xs text-slate-500">Atur penomoran surat izin dan penandatangan resmi berkas cetak.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Prefix Kode Nomor Surat <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="prefix_nomor_surat" id="inPrefixSurat" required 
                                   value="<?= htmlspecialchars($appSettings['prefix_nomor_surat']) ?>"
                                   placeholder="Contoh: CUTI/NAK atau HRD/CUTI" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-mono font-bold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition uppercase">
                            <p class="text-[11px] text-slate-400 mt-1">Hasil: <span class="font-mono text-blue-600 font-semibold"><?= htmlspecialchars($appSettings['prefix_nomor_surat']) ?>/<?= date('Y/m') ?>/001</span></p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jatah Cuti Tahunan Default (Hari) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="default_kuota_cuti" min="1" max="100" required 
                                   value="<?= (int)$appSettings['default_kuota_cuti'] ?>"
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-bold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Kepala HRD / Penandatangan
                            </label>
                            <input type="text" name="nama_kepala_hrd" id="inNamaHrd" 
                                   value="<?= htmlspecialchars($appSettings['nama_kepala_hrd'] ?? '') ?>"
                                   placeholder="Contoh: Siti Rahmawati, S.Psi" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Jabatan Kepala HRD
                            </label>
                            <input type="text" name="jabatan_kepala_hrd" id="inJabatanHrd" 
                                   value="<?= htmlspecialchars($appSettings['jabatan_kepala_hrd'] ?? '') ?>"
                                   placeholder="Contoh: HRD & GA Manager" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kota / Lokasi Cetak Surat
                            </label>
                            <input type="text" name="lokasi_surat" id="inLokasi" 
                                   value="<?= htmlspecialchars($appSettings['lokasi_surat'] ?? 'Karawang') ?>"
                                   placeholder="Contoh: Karawang / Jakarta" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Teks Footer / Hak Cipta
                            </label>
                            <input type="text" name="footer_text" id="inFooterText" 
                                   value="<?= htmlspecialchars($appSettings['footer_text'] ?? '') ?>"
                                   placeholder="Sistem Informasi Manajemen Cuti Karyawan" 
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-xs focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition">
                        </div>
                    </div>
                </div>

                <!-- Submit Action Button -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="reset" class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        <i class="fa-solid fa-arrows-rotate mr-1"></i> Reset Nilai
                    </button>
                    <button type="submit" class="px-7 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 hover:from-blue-500 hover:to-indigo-500 text-white font-extrabold text-sm shadow-lg shadow-blue-600/30 transform hover:-translate-y-0.5 active:translate-y-0 transition flex items-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Semua Pengaturan
                    </button>
                </div>

            </div>

            <!-- Right: Interactive Live Simulation Preview Card (1 Col) -->
            <div class="space-y-6">
                
                <div class="bg-slate-900 rounded-3xl p-6 border border-slate-800 shadow-xl text-white space-y-5 sticky top-24">
                    <div class="flex items-center gap-2 text-xs font-black text-rose-400 uppercase tracking-wider border-b border-slate-800 pb-3">
                        <i class="fa-solid fa-eye text-sm animate-pulse"></i>
                        <span>Live Simulation Preview</span>
                    </div>

                    <!-- 1. Simulated Sidebar Brand -->
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Preview Sidebar Logo & Brand:</span>
                        <div class="p-3.5 rounded-2xl bg-[#090e1a] border border-slate-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-rose-500 via-red-600 to-rose-700 p-0.5 shadow-md flex items-center justify-center flex-shrink-0">
                                <div class="w-full h-full bg-[#0a0f1d] rounded-[14px] flex items-center justify-center overflow-hidden">
                                    <img id="simSidebarLogo" src="<?= $logoPath ?>" alt="Logo" class="max-h-7 max-w-7 object-contain">
                                </div>
                            </div>
                            <div class="overflow-hidden">
                                <div class="flex items-center gap-1.5">
                                    <span id="simBrand" class="font-display font-black text-white text-[13px] tracking-wide uppercase leading-tight truncate">
                                        <?= htmlspecialchars($appSettings['singkatan_perusahaan']) ?>
                                    </span>
                                    <span id="simSubBrand" class="text-[8.5px] font-black px-1.5 py-0.2 rounded bg-rose-950 text-rose-400 border border-rose-800 uppercase tracking-widest flex-shrink-0">
                                        <?= htmlspecialchars($appSettings['sub_singkatan_perusahaan'] ?? 'ID') ?>
                                    </span>
                                </div>
                                <div class="mt-1">
                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-rose-500/20 border border-rose-500/35 text-[9px] font-black text-rose-300 tracking-wider uppercase">
                                        <span class="w-1 h-1 rounded-full bg-rose-500 animate-pulse"></span>
                                        <span id="simAppBadge"><?= htmlspecialchars($appSettings['singkatan_aplikasi']) ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Simulated Login Header Card -->
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Preview Login Screen:</span>
                        <div class="p-4 rounded-2xl bg-white text-slate-900 border border-slate-200 text-center space-y-2 shadow-sm">
                            <div class="inline-flex p-2 bg-slate-50 rounded-xl border border-slate-100">
                                <img id="simLoginLogo" src="<?= $logoPath ?>" alt="Logo" class="h-7 w-auto object-contain">
                            </div>
                            <h4 id="simLoginCompany" class="font-display font-black text-sm text-slate-900 tracking-tight leading-tight">
                                <?= strtoupper(htmlspecialchars($appSettings['nama_perusahaan'])) ?>
                            </h4>
                            <p id="simLoginApp" class="text-[11px] text-slate-500 font-semibold">
                                <?= htmlspecialchars($appSettings['nama_aplikasi']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- 3. Simulated Kop Surat Print -->
                    <div class="space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Preview Kop Cetak Surat:</span>
                        <div class="p-3.5 rounded-2xl bg-slate-50 text-slate-900 border border-slate-200 text-xs space-y-1 font-serif">
                            <div class="flex items-center justify-between border-b-2 border-slate-900 pb-2">
                                <div>
                                    <div id="simKopTitle" class="font-bold text-xs uppercase"><?= htmlspecialchars($appSettings['nama_perusahaan']) ?></div>
                                    <div id="simKopTagline" class="text-[10px] italic text-slate-600"><?= htmlspecialchars($appSettings['tagline'] ?? '') ?></div>
                                    <div id="simKopAddr" class="text-[9px] text-slate-500 line-clamp-1"><?= htmlspecialchars($appSettings['alamat_perusahaan'] ?? '') ?></div>
                                </div>
                                <img id="simKopLogo" src="<?= $logoPath ?>" alt="Logo" class="h-8 w-auto object-contain flex-shrink-0 ml-2">
                            </div>
                            <div class="text-[10px] text-center pt-1 font-sans font-bold text-blue-700">
                                No. Surat: <?= htmlspecialchars($appSettings['prefix_nomor_surat']) ?>/<?= date('Y/m') ?>/001
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-800/80 border border-slate-700/60 text-[11px] text-slate-300 space-y-1">
                        <div class="font-bold text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-blue-400"></i> Siap Dijual & Di-White-label
                        </div>
                        <p class="text-slate-400 text-[10.5px] leading-relaxed">
                            Cukup ganti form di atas untuk langsung mengalihkan sistem ini ke klien/perusahaan baru tanpa perlu *touch* satu baris pun kode pemrograman.
                        </p>
                    </div>

                </div>

            </div>

        </div>

    </form>
    </div>
    <!-- END TAB 1 -->

    <!-- TAB 2: Role Permissions & Privilege Matrix -->
    <div id="tabContentPrivileges" class="hidden space-y-6">
        
        <!-- Info Banner -->
        <div class="bg-gradient-to-r from-indigo-900/90 via-slate-900 to-slate-950 rounded-3xl p-6 text-white border border-indigo-500/20 shadow-soft flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <h3 class="text-base font-black tracking-tight">Matriks Hak Akses & Privilege Role (Multi-Tier Architecture)</h3>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed">
                    Atur wewenang dan batasan akses setiap peran (*role*) dalam sistem E-Cuti secara dinamis. Perubahan langsung tersinkronisasi ke Webbase & Aplikasi Mobile Android.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <button type="submit" form="formPermissionsMatrix" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Semua Hak Akses
                </button>
            </div>
        </div>

        <!-- Matrix Form -->
        <form action="<?= BASE_URL ?>/index.php?page=settings-permissions-update" method="POST" id="formPermissionsMatrix">
            
            <?php
            // Prepare Matrix Data
            require_once __DIR__ . '/../../models/Permission.php';
            $matrixRows = Permission::getAllMatrix($pdo);

            // Group by category and key
            $permsByKey = [];
            foreach ($matrixRows as $m) {
                $permsByKey[$m['permission_key']]['name'] = $m['permission_name'];
                $permsByKey[$m['permission_key']]['category'] = $m['category'];
                $permsByKey[$m['permission_key']]['desc'] = $m['description'];
                $permsByKey[$m['permission_key']]['roles'][$m['role']] = (int)$m['is_granted'] === 1;
            }

            $categories = [
                'Cuti & Kehadiran' => ['icon' => 'fa-calendar-days', 'color' => 'text-blue-500', 'bg' => 'bg-blue-50'],
                'Persetujuan (Approval)' => ['icon' => 'fa-stamp', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-50'],
                'Manajemen HRD' => ['icon' => 'fa-users-gear', 'color' => 'text-violet-500', 'bg' => 'bg-violet-50'],
                'Laporan & Dokumen' => ['icon' => 'fa-file-lines', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50'],
                'Sistem & Konfigurasi' => ['icon' => 'fa-sliders', 'color' => 'text-rose-500', 'bg' => 'bg-rose-50']
            ];

            $rolesList = [
                'operator' => ['label' => 'Operator', 'sub' => 'Level 1', 'badge' => 'bg-slate-100 text-slate-700'],
                'staff' => ['label' => 'Staff', 'sub' => 'Level 2', 'badge' => 'bg-slate-100 text-slate-700'],
                'leader' => ['label' => 'Leader', 'sub' => 'Level 3', 'badge' => 'bg-sky-100 text-sky-800'],
                'supervisor' => ['label' => 'Supervisor', 'sub' => 'Level 4', 'badge' => 'bg-blue-100 text-blue-800'],
                'manager' => ['label' => 'Plant Mgr', 'sub' => 'Level 6', 'badge' => 'bg-purple-100 text-purple-800'],
                'hrd' => ['label' => 'HRD', 'sub' => 'Level 7', 'badge' => 'bg-emerald-100 text-emerald-800'],
                'superadmin' => ['label' => 'Super Admin', 'sub' => 'Level 8', 'badge' => 'bg-rose-100 text-rose-800'],
            ];
            ?>

            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-black uppercase tracking-wider text-slate-600">
                                <th class="p-4 sm:p-5 min-w-[280px]">Fitur & Modul Hak Akses</th>
                                <?php foreach ($rolesList as $rKey => $rInfo): ?>
                                    <th class="p-3 text-center min-w-[95px]">
                                        <div class="flex flex-col items-center">
                                            <span class="px-2 py-0.5 rounded-lg <?= $rInfo['badge'] ?> text-[10px] font-black">
                                                <?= $rInfo['label'] ?>
                                            </span>
                                            <span class="text-[9px] text-slate-400 font-mono mt-0.5"><?= $rInfo['sub'] ?></span>
                                        </div>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            <?php foreach ($categories as $catName => $catMeta): ?>
                                <!-- Category Section Header -->
                                <tr class="bg-slate-50/40">
                                    <td colspan="8" class="px-5 py-3 border-y border-slate-200/60">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-lg <?= $catMeta['bg'] ?> <?= $catMeta['color'] ?> flex items-center justify-center text-xs">
                                                <i class="fa-solid <?= $catMeta['icon'] ?>"></i>
                                            </div>
                                            <span class="font-extrabold text-slate-800 tracking-tight text-xs uppercase"><?= $catName ?></span>
                                        </div>
                                    </td>
                                </tr>

                                <?php foreach ($permsByKey as $pKey => $pData): ?>
                                    <?php if ($pData['category'] !== $catName) continue; ?>
                                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                                        <td class="p-4 sm:p-5">
                                            <div class="font-bold text-slate-800 group-hover:text-indigo-600 transition text-xs">
                                                <?= htmlspecialchars($pData['name']) ?>
                                            </div>
                                            <div class="text-[10.5px] text-slate-400 mt-0.5 leading-relaxed">
                                                <?= htmlspecialchars($pData['desc']) ?>
                                            </div>
                                        </td>
                                        
                                        <?php foreach ($rolesList as $rKey => $rInfo): ?>
                                            <?php 
                                            $isGranted = !empty($pData['roles'][$rKey]);
                                            $isLocked = ($rKey === 'superadmin');
                                            ?>
                                            <td class="p-3 text-center align-middle">
                                                <div class="flex items-center justify-center">
                                                    <label class="relative inline-flex items-center cursor-pointer <?= $isLocked ? 'opacity-80 cursor-not-allowed' : '' ?>">
                                                        <input type="checkbox" 
                                                               name="matrix[<?= $rKey ?>][<?= $pKey ?>]" 
                                                               value="1" 
                                                               <?= $isGranted ? 'checked' : '' ?> 
                                                               <?= $isLocked ? 'disabled' : '' ?>
                                                               onchange="togglePermAjax('<?= $rKey ?>', '<?= $pKey ?>', this.checked)"
                                                               class="sr-only peer">
                                                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                                    </label>
                                                    <?php if ($isLocked): ?>
                                                        <input type="hidden" name="matrix[superadmin][<?= $pKey ?>]" value="1">
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Action Bar -->
                <div class="p-4 sm:p-5 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="text-xs text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span>Toggle switch otomatis tersimpan secara instan (Live AJAX). Anda juga bisa klik tombol simpan di kanan.</span>
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Matriks Hak Akses
                    </button>
                </div>
            </div>

        </form>

    </div>
    <!-- END TAB 2 -->

</div>

<script>
function switchSettingTab(tab) {
    const tabGeneral = document.getElementById('tabContentGeneral');
    const tabPrivileges = document.getElementById('tabContentPrivileges');
    const btnGeneral = document.getElementById('tabBtnGeneral');
    const btnPrivileges = document.getElementById('tabBtnPrivileges');

    if (tab === 'privileges') {
        tabGeneral.classList.add('hidden');
        tabPrivileges.classList.remove('hidden');

        btnPrivileges.className = "px-5 py-3 rounded-2xl text-xs font-black transition-all duration-200 flex items-center gap-2 bg-indigo-600 text-white shadow-md shadow-indigo-500/20";
        btnGeneral.className = "px-5 py-3 rounded-2xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all duration-200 flex items-center gap-2";
        window.location.hash = 'tab-privileges';
    } else {
        tabPrivileges.classList.add('hidden');
        tabGeneral.classList.remove('hidden');

        btnGeneral.className = "px-5 py-3 rounded-2xl text-xs font-black transition-all duration-200 flex items-center gap-2 bg-blue-600 text-white shadow-md shadow-blue-500/20";
        btnPrivileges.className = "px-5 py-3 rounded-2xl text-xs font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-all duration-200 flex items-center gap-2";
        window.location.hash = 'tab-general';
    }
}

// Auto open tab if URL hash is tab-privileges
if (window.location.hash === '#tab-privileges') {
    switchSettingTab('privileges');
}

function togglePermAjax(role, permKey, isChecked) {
    const formData = new FormData();
    formData.append('ajax', '1');
    formData.append('role', role);
    formData.append('permission_key', permKey);
    formData.append('is_granted', isChecked ? '1' : '0');

    fetch('<?= BASE_URL ?>/index.php?page=settings-permissions-update', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log(`[Permission Synced] ${role} -> ${permKey}: ${isChecked}`);
        } else {
            alert(data.message || 'Gagal mengubah hak akses');
        }
    })
    .catch(err => {
        console.error('AJAX Error:', err);
    });
}

function updateLivePreview() {
    const namaPerusahaan = document.getElementById('inNamaPerusahaan').value || 'PT. Nama Perusahaan';
    const singkatanPerusahaan = document.getElementById('inSingkatanPerusahaan').value || 'BRAND';
    const subSingkatan = document.getElementById('inSubSingkatan').value || '';
    const tagline = document.getElementById('inTagline').value || '';
    const alamat = document.getElementById('inAlamat').value || '';
    const namaAplikasi = document.getElementById('inNamaAplikasi').value || 'Sistem Informasi Cuti';
    const singkatanAplikasi = document.getElementById('inSingkatanAplikasi').value || 'E-CUTI';

    // Update Sidebar simulation
    document.getElementById('simBrand').textContent = singkatanPerusahaan.toUpperCase();
    document.getElementById('simSubBrand').textContent = subSingkatan.toUpperCase() || 'ID';
    document.getElementById('simAppBadge').textContent = singkatanAplikasi.toUpperCase();

    // Update Login simulation
    document.getElementById('simLoginCompany').textContent = namaPerusahaan.toUpperCase();
    document.getElementById('simLoginApp').textContent = namaAplikasi;

    // Update Kop simulation
    document.getElementById('simKopTitle').textContent = namaPerusahaan.toUpperCase();
    document.getElementById('simKopTagline').textContent = tagline;
    document.getElementById('simKopAddr').textContent = alamat;
}

function previewLogoFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const dataUrl = e.target.result;
            document.getElementById('logoPreviewImg').src = dataUrl;
            document.getElementById('simSidebarLogo').src = dataUrl;
            document.getElementById('simLoginLogo').src = dataUrl;
            document.getElementById('simKopLogo').src = dataUrl;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFavFile(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('favPreviewImg').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
