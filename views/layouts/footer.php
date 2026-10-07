        </main>

        <?php $footerSettings = getAppSettings(); ?>
        <!-- Modern Tailwind Footer (Desktop) -->
        <footer class="mt-auto border-t border-slate-200/80 bg-white/70 backdrop-blur py-4 px-6 sm:px-8 text-xs text-slate-500 hidden lg:flex flex-col sm:flex-row items-center justify-between gap-2 no-print">
            <div class="flex items-center gap-2">
                <span>&copy; <?= date('Y') ?></span>
                <strong class="text-slate-800 font-bold"><?= htmlspecialchars($footerSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></strong>
                <span class="text-slate-400">&bull;</span>
                <span><?= htmlspecialchars($footerSettings['footer_text'] ?: 'Nakakin Mobile - Sistem Presensi, Shift & Cuti') ?></span>
            </div>
            <div class="flex items-center gap-2 text-slate-400 font-medium">
                <?php if (!empty($footerSettings['lokasi_surat'])): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-semibold">
                        <i class="fa-solid fa-location-dot text-blue-600"></i> <?= htmlspecialchars($footerSettings['lokasi_surat']) ?>
                    </span>
                <?php endif; ?>
                <span>v2.5 Pro</span>
            </div>
        </footer>

        <!-- Flutter Material 3 Styled Mobile Bottom Navigation Bar -->
        <?php 
        $currPage = $_GET['page'] ?? 'dashboard'; 
        $canApproveNav = in_array($currentUser['role'] ?? '', ['leader', 'supervisor', 'manager', 'hrd', 'admin', 'superadmin']) || ($currentUser['level_hierarki'] ?? 1) >= 3;
        ?>
        <nav id="mobileAppBottomBar" class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] px-1 py-1.5 no-print">
            <div class="max-w-md mx-auto flex items-center justify-around">
                
                <!-- 1. Beranda (Icons.dashboard_outlined / Icons.dashboard_rounded) -->
                <?php $isHome = ($currPage === 'dashboard'); ?>
                <a href="<?= BASE_URL ?>/index.php?page=dashboard" 
                   class="flex flex-col items-center justify-center flex-1 py-0.5 transition group">
                    <?php if ($isHome): ?>
                        <div class="w-14 h-7 rounded-full bg-blue-600/15 text-blue-700 flex items-center justify-center transition">
                            <span class="material-symbols-rounded filled text-[22px] text-blue-700 leading-none">dashboard</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-900 mt-1 tracking-tight leading-none">Beranda</span>
                    <?php else: ?>
                        <div class="w-14 h-7 rounded-full flex items-center justify-center transition">
                            <span class="material-symbols-rounded text-[22px] text-slate-500 group-hover:text-slate-700 leading-none">dashboard</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500 mt-1 tracking-tight leading-none">Beranda</span>
                    <?php endif; ?>
                </a>

                <!-- 2. Ajukan (Icons.add_circle_outline_rounded / Icons.add_circle_rounded) -->
                <?php $isCreate = ($currPage === 'leave-create'); ?>
                <a href="<?= BASE_URL ?>/index.php?page=leave-create" 
                   class="flex flex-col items-center justify-center flex-1 py-0.5 transition group">
                    <?php if ($isCreate): ?>
                        <div class="w-14 h-7 rounded-full bg-blue-600/15 text-blue-700 flex items-center justify-center transition">
                            <span class="material-symbols-rounded filled text-[22px] text-blue-700 leading-none">add_circle</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-900 mt-1 tracking-tight leading-none">Ajukan</span>
                    <?php else: ?>
                        <div class="w-14 h-7 rounded-full flex items-center justify-center transition">
                            <span class="material-symbols-rounded text-[22px] text-slate-500 group-hover:text-slate-700 leading-none">add_circle</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500 mt-1 tracking-tight leading-none">Ajukan</span>
                    <?php endif; ?>
                </a>

                <!-- 3. Riwayat (Icons.history_outlined / Icons.history_rounded) -->
                <?php $isHistory = in_array($currPage, ['leaves-my', 'leave-detail']); ?>
                <a href="<?= BASE_URL ?>/index.php?page=leaves-my" 
                   class="flex flex-col items-center justify-center flex-1 py-0.5 transition group">
                    <?php if ($isHistory): ?>
                        <div class="w-14 h-7 rounded-full bg-blue-600/15 text-blue-700 flex items-center justify-center transition">
                            <span class="material-symbols-rounded filled text-[22px] text-blue-700 leading-none">history</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-900 mt-1 tracking-tight leading-none">Riwayat</span>
                    <?php else: ?>
                        <div class="w-14 h-7 rounded-full flex items-center justify-center transition">
                            <span class="material-symbols-rounded text-[22px] text-slate-500 group-hover:text-slate-700 leading-none">history</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500 mt-1 tracking-tight leading-none">Riwayat</span>
                    <?php endif; ?>
                </a>

                <!-- 4. Approval (Icons.verified_outlined / Icons.verified_rounded) -->
                <?php if ($canApproveNav): 
                    $isApproval = ($currPage === 'leave-approvals');
                ?>
                    <a href="<?= BASE_URL ?>/index.php?page=leave-approvals" 
                       class="flex flex-col items-center justify-center flex-1 py-0.5 transition group relative">
                        <?php if ($isApproval): ?>
                            <div class="w-14 h-7 rounded-full bg-blue-600/15 text-blue-700 flex items-center justify-center transition relative">
                                <span class="material-symbols-rounded filled text-[22px] text-blue-700 leading-none">verified</span>
                                <?php if (!empty($pendingApprovalCount) && $pendingApprovalCount > 0): ?>
                                    <span class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-[#FF3B30] text-white text-[9px] font-black border border-white">
                                        <?= $pendingApprovalCount > 99 ? '99+' : $pendingApprovalCount ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] font-bold text-slate-900 mt-1 tracking-tight leading-none">Approval</span>
                        <?php else: ?>
                            <div class="w-14 h-7 rounded-full flex items-center justify-center transition relative">
                                <span class="material-symbols-rounded text-[22px] text-slate-500 group-hover:text-slate-700 leading-none">verified</span>
                                <?php if (!empty($pendingApprovalCount) && $pendingApprovalCount > 0): ?>
                                    <span class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-[#FF3B30] text-white text-[9px] font-black border border-white">
                                        <?= $pendingApprovalCount > 99 ? '99+' : $pendingApprovalCount ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-[11px] font-medium text-slate-500 mt-1 tracking-tight leading-none">Approval</span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- 5. Setting (CupertinoIcons.gear / CupertinoIcons.gear_solid) -->
                <?php $isSetting = in_array($currPage, ['profile', 'settings']); ?>
                <a href="<?= BASE_URL ?>/index.php?page=profile" 
                   class="flex flex-col items-center justify-center flex-1 py-0.5 transition group">
                    <?php if ($isSetting): ?>
                        <div class="w-14 h-7 rounded-full bg-blue-600/15 text-blue-700 flex items-center justify-center transition">
                            <span class="material-symbols-rounded filled text-[22px] text-blue-700 leading-none">settings</span>
                        </div>
                        <span class="text-[11px] font-bold text-slate-900 mt-1 tracking-tight leading-none">Setting</span>
                    <?php else: ?>
                        <div class="w-14 h-7 rounded-full flex items-center justify-center transition">
                            <span class="material-symbols-rounded text-[22px] text-slate-500 group-hover:text-slate-700 leading-none">settings</span>
                        </div>
                        <span class="text-[11px] font-medium text-slate-500 mt-1 tracking-tight leading-none">Setting</span>
                    <?php endif; ?>
                </a>

            </div>
        </nav>
    </div>
</div>

<!-- Core Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<script>
    // Universal Desktop Mini-Sidebar & Mobile Drawer Toggle Handler
    const sidebar = document.getElementById('mainSidebar');
    const mainWrapper = document.getElementById('mainWrapper');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');
    const mobileBackdrop = document.getElementById('mobileBackdrop');

    function toggleSidebar() {
        if (!sidebar) return;
        const isDesktop = window.innerWidth >= 1024;

        if (isDesktop) {
            // Desktop Mini Collapsed Toggle
            const isMini = sidebar.classList.contains('sidebar-collapsed');
            if (isMini) {
                // Expand to Full Sidebar
                sidebar.classList.remove('sidebar-collapsed');
                if (mainWrapper) {
                    mainWrapper.classList.remove('sidebar-collapsed');
                }
                localStorage.setItem('nakakin_sidebar_mini', 'false');
            } else {
                // Collapse to Mini Sidebar (Shows only Logo & Avatar & Icons)
                sidebar.classList.add('sidebar-collapsed');
                if (mainWrapper) {
                    mainWrapper.classList.add('sidebar-collapsed');
                }
                localStorage.setItem('nakakin_sidebar_mini', 'true');
            }
        } else {
            // Mobile Slide-in Drawer Toggle
            const isHiddenMobile = sidebar.classList.contains('-translate-x-full');
            if (isHiddenMobile) {
                sidebar.classList.remove('-translate-x-full');
                if (mobileBackdrop) mobileBackdrop.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                if (mobileBackdrop) mobileBackdrop.classList.add('hidden');
            }
        }
    }

    function closeMobileSidebar() {
        if (sidebar && window.innerWidth < 1024) {
            sidebar.classList.add('-translate-x-full');
            if (mobileBackdrop) mobileBackdrop.classList.add('hidden');
        }
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }

    if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeMobileSidebar);
    if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMobileSidebar);

    // Apply saved desktop preference on page load
    (function initSidebarState() {
        // Clear legacy key if present
        localStorage.removeItem('nakakin_sidebar_collapsed');
        if (window.innerWidth >= 1024 && localStorage.getItem('nakakin_sidebar_mini') === 'true') {
            if (sidebar) sidebar.classList.add('sidebar-collapsed');
            if (mainWrapper) mainWrapper.classList.add('sidebar-collapsed');
        }
    })();

    // User Dropdown Toggle Handler
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }
        });
    }

    // Initialize Ultra-Modern DataTables
    $(document).ready(function() {
        if ($.fn.DataTable) {
            $('.datatable').each(function() {
                if (!$.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable({
                        dom: '<"dt-toolbar"lf><"overflow-x-auto w-full"rt><"dt-footer"ip>',
                        language: {
                            search: "",
                            searchPlaceholder: "🔍 Cari data...",
                            lengthMenu: "Tampil _MENU_ data",
                            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                            infoEmpty: "Tidak ada data",
                            infoFiltered: "(disaring dari _MAX_ data)",
                            zeroRecords: "Data tidak ditemukan",
                            paginate: {
                                first: "<i class='fa-solid fa-angles-left'></i>",
                                last: "<i class='fa-solid fa-angles-right'></i>",
                                next: "<i class='fa-solid fa-chevron-right text-[10.5px]'></i>",
                                previous: "<i class='fa-solid fa-chevron-left text-[10.5px]'></i>"
                            }
                        },
                        pageLength: 10,
                        responsive: false,
                        autoWidth: false
                    });
                }
            });
        }
    });

    // Global Dark Mode Handler & Synchronization
    function syncDarkModeUI(isDark) {
        const toggleMobile = document.getElementById('darkModeToggleMobile');
        if (toggleMobile) {
            toggleMobile.checked = isDark;
        }
        const themeLabel = document.getElementById('themeModeLabel');
        if (themeLabel) {
            themeLabel.innerText = isDark ? 'Tema Gelap Aktif' : 'Tema Terang Standar';
        }
        const iconContainer = document.getElementById('themeModeIconContainer');
        const iconEl = document.getElementById('themeModeIcon');
        if (iconContainer && iconEl) {
            if (isDark) {
                iconContainer.className = 'w-8 h-8 rounded-lg bg-indigo-950/80 text-indigo-400 flex items-center justify-center flex-shrink-0 transition';
                iconEl.innerText = 'dark_mode';
            } else {
                iconContainer.className = 'w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0 transition';
                iconEl.innerText = 'light_mode';
            }
        }
    }

    function toggleGlobalDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark');
        try {
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        } catch (e) {}
        syncDarkModeUI(isDark);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.classList.contains('dark');
        syncDarkModeUI(isDark);
    });

    // Handle Leave WhatsApp Submission Modal
    <?php if (isset($_SESSION['leave_whatsapp_modal'])): 
        $waModal = $_SESSION['leave_whatsapp_modal'];
        unset($_SESSION['leave_whatsapp_modal']);
    ?>
    document.addEventListener('DOMContentLoaded', function() {
        <?php 
            $contacts = $waModal['contacts'] ?? [];
            $hasMultiple = count($contacts) > 1;
        ?>
        Swal.fire({
            title: '',
            html: `
                <div class="text-left space-y-4">
                    <div class="flex items-center gap-3.5 pb-3.5 border-b border-slate-100">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl font-bold flex-shrink-0 shadow-sm">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 leading-snug">Permohonan Cuti Berhasil Diajukan!</h3>
                            <p class="text-xs text-blue-600 font-mono font-bold mt-0.5"><?= htmlspecialchars($waModal['nomor_surat']) ?></p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 text-xs text-slate-700 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-medium">Pemohon:</span>
                            <strong class="text-slate-900 font-bold"><?= htmlspecialchars($waModal['nama_pemohon']) ?></strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-medium">Target Atasan:</span>
                            <span class="text-emerald-700 font-extrabold flex items-center gap-1.5">
                                <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                                <?= htmlspecialchars($waModal['atasan_nama']) ?> (<?= htmlspecialchars($waModal['atasan_role']) ?>)
                            </span>
                        </div>
                    </div>

                    <?php if ($hasMultiple): ?>
                    <div class="space-y-2 pt-1">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500">Pilih Kontak Atasan untuk Dikirim WA:</label>
                        <div class="grid grid-cols-1 gap-1.5 max-h-48 overflow-y-auto pr-1">
                            <?php foreach ($contacts as $cnt): ?>
                                <a href="<?= htmlspecialchars($cnt['wa_url']) ?>" target="_blank" 
                                   class="flex items-center justify-between p-2.5 rounded-xl border <?= !empty($cnt['is_primary']) ? 'border-emerald-300 bg-emerald-50/70 text-emerald-900' : 'border-slate-200 bg-white hover:bg-slate-50 text-slate-700' ?> transition shadow-2xs group">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg <?= !empty($cnt['is_primary']) ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600' ?> flex items-center justify-center text-xs font-bold">
                                            <i class="fa-brands fa-whatsapp"></i>
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold leading-tight"><?= htmlspecialchars($cnt['nama']) ?></div>
                                            <div class="text-[10px] <?= !empty($cnt['is_primary']) ? 'text-emerald-700 font-semibold' : 'text-slate-400' ?>"><?= htmlspecialchars($cnt['label']) ?></div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold text-emerald-600 group-hover:underline flex items-center gap-1">
                                        Kirim <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Data cuti Anda telah aman tercatat di sistem. Anda dapat langsung mengirimkan pesan notifikasi WhatsApp ke atasan agar permohonan dapat segera ditinjau.
                    </p>
                    <?php endif; ?>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="fa-brands fa-whatsapp text-lg mr-1.5"></i> Kirim WA ke Atasan Utama',
            cancelButtonText: 'Selesai / Tutup',
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-3xl shadow-2xl border border-slate-200 p-6',
                confirmButton: 'px-5 py-3 rounded-2xl font-extrabold text-xs text-white shadow-lg shadow-emerald-500/30',
                cancelButton: 'px-5 py-3 rounded-2xl font-bold text-xs'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('<?= addslashes($waModal['wa_url']) ?>', '_blank');
            }
        });
    });
    <?php elseif ($flash): ?>
    // Handle Regular SweetAlert Flash Messages
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'success') ?>',
            title: '<?= $flash['type'] === 'error' ? 'Oops!' : ($flash['type'] === 'warning' ? 'Perhatian' : 'Berhasil!') ?>',
            text: '<?= addslashes($flash['message']) ?>',
            timer: 3500,
            timerProgressBar: true,
            confirmButtonColor: '#2563eb',
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-slate-100'
            }
        });
    });
    <?php endif; ?>

    // Universal Logout Confirmation Modal
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

</body>
</html>
