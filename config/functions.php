<?php
/**
 * Helper Functions
 * PT. Nakakin Indonesia Leave Management System
 */

// Function to calculate exact length of employment (Masa Kerja)
function hitungMasaKerja($tanggal_masuk) {
    if (!$tanggal_masuk || $tanggal_masuk === '0000-00-00') {
        return '-';
    }
    
    $start = new DateTime($tanggal_masuk);
    $now = new DateTime();
    
    if ($start > $now) {
        return 'Baru Bergabung';
    }
    
    $diff = $start->diff($now);
    
    $parts = [];
    if ($diff->y > 0) {
        $parts[] = $diff->y . ' Tahun';
    }
    if ($diff->m > 0) {
        $parts[] = $diff->m . ' Bulan';
    }
    if ($diff->d > 0 || empty($parts)) {
        $parts[] = $diff->d . ' Hari';
    }
    
    return implode(' ', $parts);
}

// Format date to Indonesian Format
function formatTanggalIndo($tanggal) {
    if (!$tanggal || $tanggal === '0000-00-00') return '-';
    
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $time = strtotime($tanggal);
    $tgl = date('d', $time);
    $bln = $bulan[(int)date('m', $time)];
    $thn = date('Y', $time);
    
    return "$tgl $bln $thn";
}

// Format date time to Indonesian Format
function formatDateTimeIndo($datetime) {
    if (!$datetime) return '-';
    $time = strtotime($datetime);
    return formatTanggalIndo(date('Y-m-d', $time)) . ' ' . date('H:i', $time) . ' WIB';
}

// Status Badges HTML (Tailwind CSS Ultra-Modern Theme)
function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'approved':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 shadow-2xs whitespace-nowrap"><i class="fa-solid fa-circle-check text-emerald-500 text-xs"></i> Disetujui</span>';
        case 'pending':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-amber-50 text-amber-800 border border-amber-300/80 shadow-2xs whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Menunggu</span>';
        case 'rejected':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs whitespace-nowrap"><i class="fa-solid fa-circle-xmark text-rose-500 text-xs"></i> Ditolak</span>';
        case 'cancelled':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-slate-100 text-slate-600 border border-slate-200 whitespace-nowrap"><i class="fa-solid fa-ban text-slate-400 text-xs"></i> Dibatalkan</span>';
        default:
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap">' . htmlspecialchars(ucfirst($status)) . '</span>';
    }
}

// Role Badges HTML (Tailwind CSS Ultra-Modern Theme)
function getRoleBadge($role) {
    switch (strtolower($role)) {
        case 'admin':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs whitespace-nowrap"><i class="fa-solid fa-user-shield text-rose-500"></i> HRD Admin</span>';
        case 'atasan':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs whitespace-nowrap"><i class="fa-solid fa-user-tie text-blue-500"></i> Atasan / Leader</span>';
        case 'staff':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-2xs whitespace-nowrap"><i class="fa-solid fa-user-gear text-indigo-500"></i> Staff</span>';
        case 'operator':
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-slate-100 text-slate-700 border border-slate-200 whitespace-nowrap"><i class="fa-solid fa-helmet-safety text-slate-500"></i> Operator</span>';
        default:
            return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-slate-100 text-slate-700 whitespace-nowrap">' . htmlspecialchars(ucfirst($role)) . '</span>';
    }
}

// Get Application & Company Dynamic Settings
function getAppSettings($pdo = null) {
    static $cachedSettings = null;
    if ($cachedSettings !== null) {
        return $cachedSettings;
    }

    $defaultSettings = [
        'id' => 1,
        'nama_aplikasi' => 'Sistem Informasi Cuti Karyawan',
        'singkatan_aplikasi' => 'FORM CUTI ONLINE',
        'tagline' => 'Precision Machinery, Pumps & Tooling Manufacturing',
        'nama_perusahaan' => 'PT. Nakakin Indonesia',
        'singkatan_perusahaan' => 'NAKAKIN',
        'sub_singkatan_perusahaan' => 'INDONESIA',
        'alamat_perusahaan' => 'Kawasan Industri KIIC, Jl. Maligi Raya Lot Q-2A, Sukaluyu, Telukjambe Timur, Karawang, Jawa Barat 41361',
        'telepon' => '(0267) 845-1234',
        'email_perusahaan' => 'hrd@nakakin.co.id',
        'website' => 'www.nakakin.co.id',
        'logo' => 'Nakakin.png',
        'favicon' => 'Nakakin.png',
        'prefix_nomor_surat' => 'CUTI/NAK',
        'default_kuota_cuti' => 12,
        'nama_kepala_hrd' => 'Siti Rahmawati, S.Psi',
        'jabatan_kepala_hrd' => 'HRD & GA Manager',
        'lokasi_surat' => 'Karawang',
        'footer_text' => 'Sistem Informasi Manajemen Cuti Karyawan'
    ];

    try {
        if (!$pdo) {
            $pdo = getDbConnection();
        }
        $stmt = $pdo->query("SELECT * FROM pengaturan_aplikasi WHERE id = 1 LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($settings) {
            $cachedSettings = array_merge($defaultSettings, array_filter($settings, function($v) {
                return $v !== null && $v !== '';
            }));
            return $cachedSettings;
        }
    } catch (Exception $e) {
        // Fallback to defaults if table doesn't exist yet
    }

    $cachedSettings = $defaultSettings;
    return $cachedSettings;
}

// Generate unique Leave Letter Number
function generateNomorSurat($pdo) {
    $settings = getAppSettings($pdo);
    $prefixCode = !empty($settings['prefix_nomor_surat']) ? trim($settings['prefix_nomor_surat'], '/') : 'CUTI/NAK';

    $year = date('Y');
    $month = date('m');
    $prefix = "{$prefixCode}/{$year}/{$month}/";
    
    // Find highest existing sequence number for this prefix
    $stmt = $pdo->prepare("SELECT nomor_surat FROM pengajuan_cuti WHERE nomor_surat LIKE ? ORDER BY id DESC");
    $stmt->execute([$prefix . '%']);
    $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $maxSeq = 0;
    foreach ($existing as $no) {
        $parts = explode('/', $no);
        $last = (int)end($parts);
        if ($last > $maxSeq) {
            $maxSeq = $last;
        }
    }
    
    $nextSeq = $maxSeq + 1;
    $nomorSurat = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    
    // Safety check loop to guarantee uniqueness
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM pengajuan_cuti WHERE nomor_surat = ?");
    while (true) {
        $stmtCheck->execute([$nomorSurat]);
        if ($stmtCheck->fetchColumn() == 0) {
            break;
        }
        $nextSeq++;
        $nomorSurat = $prefix . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);
    }
    
    return $nomorSurat;
}

// Render Clean 2-Line Leave Type Badge & Title
function renderLeaveTypeDisplay($namaCuti, $potongKuota = null) {
    if (!$namaCuti) return '-';
    
    // Extract main title and parenthesis text if any
    $title = $namaCuti;
    $subtitle = '';
    
    if (preg_match('/^(.*?)\s*\((.*?)\)$/', $namaCuti, $matches)) {
        $title = trim($matches[1]);
        $subtitle = trim($matches[2]);
    }
    
    // Custom color & icon themes for all 7 standard leave types
    $badgeStyle = 'bg-slate-100 text-slate-700 border-slate-200/80';
    $icon = 'fa-solid fa-calendar-check text-blue-600';
    
    if (stripos($title, 'Sakit Surat Dokter') !== false) {
        $badgeStyle = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
        $icon = 'fa-solid fa-file-medical text-emerald-600';
    } elseif (stripos($title, 'Sakit') !== false) {
        $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-200/80';
        $icon = 'fa-solid fa-notes-medical text-amber-600';
    } elseif (stripos($title, 'Melahirkan') !== false) {
        $badgeStyle = 'bg-rose-50 text-rose-700 border-rose-200/80';
        $icon = 'fa-solid fa-baby text-rose-600';
    } elseif (stripos($title, 'Haid') !== false) {
        $badgeStyle = 'bg-purple-50 text-purple-700 border-purple-200/80';
        $icon = 'fa-solid fa-person-dress text-purple-600';
    } elseif (stripos($title, 'Khusus') !== false) {
        $badgeStyle = 'bg-indigo-50 text-indigo-700 border-indigo-200/80';
        $icon = 'fa-solid fa-ribbon text-indigo-600';
    } elseif (stripos($title, 'Ijin') !== false) {
        $badgeStyle = 'bg-orange-50 text-orange-700 border-orange-200/80';
        $icon = 'fa-solid fa-calendar-xmark text-orange-600';
    } elseif (stripos($title, 'Tahunan') !== false) {
        $badgeStyle = 'bg-blue-50 text-blue-700 border-blue-200/80';
        $icon = 'fa-solid fa-calendar-days text-blue-600';
    }
    
    $out = '<div class="flex flex-col gap-1 whitespace-nowrap">';
    $out .= '<div class="font-extrabold text-slate-900 text-xs flex items-center gap-1.5">';
    $out .= '<i class="' . $icon . ' text-xs flex-shrink-0"></i>';
    $out .= '<span>' . htmlspecialchars($title) . '</span>';
    $out .= '</div>';
    
    if ($subtitle) {
        $out .= '<div>';
        $out .= '<span class="inline-flex items-center px-2 py-0.5 rounded-md border text-[10.5px] font-bold ' . $badgeStyle . '">';
        $out .= htmlspecialchars($subtitle);
        $out .= '</span>';
        $out .= '</div>';
    }
    $out .= '</div>';
    
    return $out;
}

// Set Flash Message for UI notification
function setFlash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

// Get Flash Message
function getFlash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Helper to sanitize input
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate WhatsApp Notification Data & URL for a Leave Request
 */
function getLeaveWhatsAppNotificationData($leaveId, $pdo = null) {
    if (!$pdo) {
        $pdo = getDbConnection();
    }

    $stmt = $pdo->prepare("
        SELECT lr.*, 
               e.nik, e.nama_lengkap, e.role as emp_role, e.departemen_id,
               d.nama_dept,
               p.nama_jabatan, p.level_hierarki,
               lt.nama_cuti, lt.kode as kode_cuti
        FROM pengajuan_cuti lr
        JOIN karyawan e ON lr.employee_id = e.id
        JOIN departemen d ON e.departemen_id = d.id
        JOIN jabatan p ON e.jabatan_id = p.id
        JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
        WHERE lr.id = ?
    ");
    $stmt->execute([$leaveId]);
    $leave = $stmt->fetch();
    if (!$leave) return null;

    $empHierarki = (int)($leave['level_hierarki'] ?? 1);
    $empRole = strtolower($leave['emp_role'] ?? '');

    $targetAtasan = null;
    $atasanRoleText = 'Atasan / Leader';

    if ($empHierarki <= 2 || in_array($empRole, ['operator', 'staff'])) {
        // Cari Leader atau Supervisor di Departemen yang sama
        $stmtAtasan = $pdo->prepare("
            SELECT id, nama_lengkap, no_hp, role 
            FROM karyawan 
            WHERE departemen_id = ? AND role IN ('supervisor', 'leader') AND status_aktif = 'Aktif' AND no_hp IS NOT NULL AND no_hp != ''
            ORDER BY FIELD(role, 'leader', 'supervisor') ASC LIMIT 1
        ");
        $stmtAtasan->execute([$leave['departemen_id']]);
        $targetAtasan = $stmtAtasan->fetch();
        $atasanRoleText = 'Leader / Supervisor Departemen';
    } elseif ($empHierarki <= 4 || in_array($empRole, ['leader', 'supervisor'])) {
        // Cari Plant / Dept Manager
        $stmtAtasan = $pdo->prepare("
            SELECT id, nama_lengkap, no_hp, role 
            FROM karyawan 
            WHERE role = 'manager' AND status_aktif = 'Aktif' AND no_hp IS NOT NULL AND no_hp != ''
            LIMIT 1
        ");
        $stmtAtasan->execute();
        $targetAtasan = $stmtAtasan->fetch();
        $atasanRoleText = 'Plant Manager';
    } else {
        // Cari HRD
        $stmtAtasan = $pdo->prepare("
            SELECT id, nama_lengkap, no_hp, role 
            FROM karyawan 
            WHERE role IN ('hrd', 'superadmin') AND status_aktif = 'Aktif' AND no_hp IS NOT NULL AND no_hp != ''
            ORDER BY FIELD(role, 'hrd', 'superadmin') ASC LIMIT 1
        ");
        $stmtAtasan->execute();
        $targetAtasan = $stmtAtasan->fetch();
        $atasanRoleText = 'HRD Manager';
    }

    $rawPhone = $targetAtasan['no_hp'] ?? '';
    $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
    if (strpos($cleanPhone, '0') === 0) {
        $cleanPhone = '62' . substr($cleanPhone, 1);
    } elseif (strpos($cleanPhone, '8') === 0) {
        $cleanPhone = '628' . substr($cleanPhone, 1);
    }

    $atasanNama = $targetAtasan['nama_lengkap'] ?? $atasanRoleText;
    $tglMulaiIndo = formatTanggalIndo($leave['tanggal_mulai']);
    $tglSelesaiIndo = formatTanggalIndo($leave['tanggal_selesai']);
    $periodeText = ($leave['tanggal_mulai'] === $leave['tanggal_selesai']) 
        ? $tglMulaiIndo 
        : "$tglMulaiIndo s/d $tglSelesaiIndo";

    $daysText = $leave['total_hari'] == 0.5 ? '0.5 Hari (Setengah Hari)' : $leave['total_hari'] . ' Hari Kerja';

    $msg = "Halo Bapak/Ibu {$atasanNama},\n\n"
         . "Saya mengajukan permohonan cuti/izin kerja melalui sistem *Nakakin Mobile / Web Cuti*:\n\n"
         . "📋 *DETAIL PENGAJUAN CUTI*\n"
         . "• *No. Surat:* {$leave['nomor_surat']}\n"
         . "• *Nama:* {$leave['nama_lengkap']} (NIK: {$leave['nik']})\n"
         . "• *Dept / Shift:* {$leave['nama_dept']} ({$leave['shift']})\n"
         . "• *Jenis Permohonan:* {$leave['nama_cuti']}\n"
         . "• *Periode:* {$periodeText} ({$daysText})\n"
         . "• *Alasan:* {$leave['alasan']}\n\n"
         . "Mohon kesediaan Bapak/Ibu untuk memeriksa dan memproses persetujuan permohonan ini.\n"
         . "Terima kasih.";

    $waUrl = !empty($cleanPhone) 
        ? "https://api.whatsapp.com/send?phone={$cleanPhone}&text=" . rawurlencode($msg)
        : "https://api.whatsapp.com/send?text=" . rawurlencode($msg);

    return [
        'leave_id' => (int)$leave['id'],
        'nomor_surat' => $leave['nomor_surat'],
        'nama_pemohon' => $leave['nama_lengkap'],
        'atasan_nama' => $atasanNama,
        'atasan_role' => $atasanRoleText,
        'atasan_phone' => $cleanPhone,
        'has_phone' => !empty($cleanPhone),
        'message_text' => $msg,
        'wa_url' => $waUrl
    ];
}

