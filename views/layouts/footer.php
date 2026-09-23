        </main>

        <?php $footerSettings = getAppSettings(); ?>
        <!-- Modern Tailwind Footer -->
        <footer class="mt-auto border-t border-slate-200/80 bg-white/70 backdrop-blur py-4 px-6 sm:px-8 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2 no-print">
            <div class="flex items-center gap-2">
                <span>&copy; <?= date('Y') ?></span>
                <strong class="text-slate-800 font-bold"><?= htmlspecialchars($footerSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></strong>
                <span class="text-slate-400">&bull;</span>
                <span><?= htmlspecialchars($footerSettings['footer_text'] ?: 'Sistem Informasi Manajemen Cuti Karyawan') ?></span>
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

    // Handle SweetAlert Flash Messages
    <?php if ($flash): ?>
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
</script>

</body>
</html>
