/**
 * Main Javascript Helpers
 * PT. Nakakin Indonesia Leave Management System
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Sidebar Toggle for Mobile & Responsive
    const sidebar = document.getElementById('appSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
            if (sidebarBackdrop) {
                sidebarBackdrop.classList.toggle('d-none');
            }
        });
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            sidebar.classList.remove('show');
            sidebarBackdrop.classList.add('d-none');
        });
    }

    // 2. Realtime Header Clock
    const clockEl = document.getElementById('liveClock');
    if (clockEl) {
        function updateClock() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            const dayName = days[now.getDay()];
            const date = String(now.getDate()).padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            clockEl.innerHTML = `<i class="fa-regular fa-clock me-1 text-primary"></i> ${dayName}, ${date} ${monthName} ${year} &bull; ${hours}:${minutes}:${seconds} WIB`;
        }
        setInterval(updateClock, 1000);
        updateClock();
    }

    // 3. Date Range Calculator for Leave Application Form
    const startDateInput = document.getElementById('tanggal_mulai');
    const endDateInput = document.getElementById('tanggal_selesai');
    const totalDaysInput = document.getElementById('total_hari');
    const totalDaysDisplay = document.getElementById('total_hari_display');

    if (startDateInput && endDateInput && totalDaysInput) {
        function calculateLeaveDays() {
            const startVal = startDateInput.value;
            const endVal = endDateInput.value;

            if (!startVal || !endVal) {
                totalDaysInput.value = 0;
                if (totalDaysDisplay) totalDaysDisplay.innerText = '0 Hari Kerja';
                return;
            }

            const start = new Date(startVal);
            const end = new Date(endVal);

            if (end < start) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal Tidak Valid',
                        text: 'Tanggal selesai cuti tidak boleh lebih awal dari tanggal mulai!'
                    });
                } else {
                    alert('Tanggal selesai cuti tidak boleh lebih awal dari tanggal mulai!');
                }
                endDateInput.value = startVal;
                totalDaysInput.value = 1;
                if (totalDaysDisplay) totalDaysDisplay.innerText = '1 Hari Kerja';
                return;
            }

            // Calculate business days (skip Sundays, or Saturday & Sunday depending on company policy)
            let count = 0;
            let curDate = new Date(start.getTime());
            while (curDate <= end) {
                const dayOfWeek = curDate.getDay();
                // 0 is Sunday. If 6 workdays (Mon-Sat), skip only Sunday (0)
                if (dayOfWeek !== 0) {
                    count++;
                }
                curDate.setDate(curDate.getDate() + 1);
            }

            // At least 1 day if start and end are same day
            if (count === 0 && startVal === endVal) {
                count = 1;
            }

            totalDaysInput.value = count;
            if (totalDaysDisplay) {
                totalDaysDisplay.innerText = count + ' Hari Kerja (Exclude Hari Minggu)';
            }
        }

        startDateInput.addEventListener('change', calculateLeaveDays);
        endDateInput.addEventListener('change', calculateLeaveDays);
    }
});

// Confirmation helper for Delete / Cancel action
function confirmDelete(url, message = 'Data yang dihapus tidak dapat dikembalikan!') {
    if (window.Swal) {
        Swal.fire({
            title: '',
            html: `
                <div class="text-left space-y-3">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-base font-bold border border-rose-200/60 shadow-2xs flex-shrink-0">
                            <i class="fa-solid fa-trash-can"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">Konfirmasi Tindakan</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Tindakan ini memerlukan persetujuan Anda</p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">${message}</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: '<i class="fa-solid fa-trash-can mr-1.5"></i> Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'modern-swal-popup',
                htmlContainer: 'modern-swal-html',
                actions: 'modern-swal-actions',
                confirmButton: 'modern-swal-confirm',
                cancelButton: 'modern-swal-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    } else {
        if (confirm(message)) {
            window.location.href = url;
        }
    }
}

// Confirmation helper for Leave Approval
function confirmApprove(leaveId, employeeName) {
    if (window.Swal) {
        Swal.fire({
            title: '',
            html: `
                <div class="text-left space-y-4">
                    <!-- Header Banner -->
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg font-bold border border-emerald-200/60 shadow-2xs flex-shrink-0">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">Setujui Permohonan Cuti</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Pemohon: <strong class="text-slate-800">${employeeName}</strong></p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 text-[11.5px] text-emerald-800 flex items-start gap-2 leading-relaxed">
                        <i class="fa-solid fa-circle-info text-emerald-600 mt-0.5 flex-shrink-0"></i>
                        <span>Apakah Anda menyetujui izin cuti untuk <b>${employeeName}</b>? Kuota cuti karyawan akan otomatis diperbarui.</span>
                    </div>

                    <!-- Input Note Group -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Catatan / Pesan Atasan</span>
                            <span class="text-[10.5px] text-slate-400 font-normal normal-case">Opsional</span>
                        </label>
                        <textarea id="swalCatatan" rows="2" 
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 text-slate-800 font-medium text-xs focus:outline-none focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition resize-none placeholder:text-slate-400" 
                            placeholder="Tuliskan pesan atau catatan tambahan untuk pemohon (opsional)..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-check mr-1.5"></i> Setujui Cuti',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'modern-swal-popup',
                htmlContainer: 'modern-swal-html',
                actions: 'modern-swal-actions',
                confirmButton: 'modern-swal-confirm',
                cancelButton: 'modern-swal-cancel'
            },
            preConfirm: () => {
                return document.getElementById('swalCatatan')?.value || '';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const note = encodeURIComponent(result.value || '');
                window.location.href = `index.php?page=leave-action&action=approve&id=${leaveId}&note=${note}`;
            }
        });
    } else {
        if (confirm(`Setujui cuti ${employeeName}?`)) {
            window.location.href = `index.php?page=leave-action&action=approve&id=${leaveId}`;
        }
    }
}

// Confirmation helper for Leave Rejection with Reason
function confirmReject(leaveId, employeeName) {
    if (window.Swal) {
        Swal.fire({
            title: '',
            html: `
                <div class="text-left space-y-4">
                    <!-- Header Banner -->
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-11 h-11 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-lg font-bold border border-rose-200/60 shadow-2xs flex-shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 leading-snug">Tolak Permohonan Cuti</h3>
                            <p class="text-[11px] text-slate-400 font-medium">Pemohon: <strong class="text-slate-800">${employeeName}</strong></p>
                        </div>
                    </div>

                    <!-- Input Reason Group -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>Alasan Penolakan <span class="text-rose-500">*</span></span>
                            <span class="text-[10.5px] text-slate-400 font-normal normal-case">Wajib diisi</span>
                        </label>
                        <textarea id="swalAlasan" rows="3" 
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/60 text-slate-800 font-medium text-xs focus:outline-none focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 transition resize-none placeholder:text-slate-400 shadow-2xs" 
                            placeholder="Tuliskan alasan penolakan secara jelas (contoh: Jadwal kerja sedang padat, mohon ajukan di tanggal lain)..."></textarea>
                        <p class="text-[10.5px] text-slate-400 mt-1.5 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info text-blue-500"></i> Alasan ini akan tercatat dalam riwayat dan dapat dilihat oleh pemohon.
                        </p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: '<i class="fa-solid fa-xmark mr-1.5"></i> Tolak Cuti',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'modern-swal-popup',
                htmlContainer: 'modern-swal-html',
                actions: 'modern-swal-actions',
                confirmButton: 'modern-swal-confirm',
                cancelButton: 'modern-swal-cancel'
            },
            preConfirm: () => {
                const reason = document.getElementById('swalAlasan')?.value.trim();
                if (!reason) {
                    Swal.showValidationMessage('Mohon isi alasan penolakan cuti terlebih dahulu!');
                    return false;
                }
                return reason;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = encodeURIComponent(result.value);
                window.location.href = `index.php?page=leave-action&action=reject&id=${leaveId}&reason=${reason}`;
            }
        });
    } else {
        const reason = prompt(`Masukkan alasan penolakan untuk ${employeeName}:`);
        if (reason) {
            window.location.href = `index.php?page=leave-action&action=reject&id=${leaveId}&reason=${encodeURIComponent(reason)}`;
        }
    }
}
