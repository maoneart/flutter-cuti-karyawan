<?php
/**
 * Login Page (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/index.php?page=dashboard');
    exit;
}

$flash = getFlash();
$appSettings = getAppSettings();
$loginLogoUrl = !empty($appSettings['logo']) ? BASE_URL . '/assets/images/' . $appSettings['logo'] : BASE_URL . '/assets/images/Nakakin.png';
$loginFavUrl = !empty($appSettings['favicon']) ? BASE_URL . '/assets/images/' . $appSettings['favicon'] : BASE_URL . '/assets/images/Nakakin.png';
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &bull; <?= htmlspecialchars($appSettings['nama_aplikasi'] ?: 'Sistem Informasi Cuti Karyawan') ?> <?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. Nakakin Indonesia') ?></title>
    
    <link rel="icon" type="image/png" href="<?= $loginFavUrl ?>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Space Grotesk"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">
</head>
<body class="h-full flex items-center justify-center p-4 bg-[#090e1a] font-sans antialiased text-slate-800 relative overflow-hidden selection:bg-rose-500 selection:text-white">

    <!-- Background Glowing Ambient Orbs -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-rose-600/25 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-blue-600/25 rounded-full blur-3xl pointer-events-none animate-pulse" style="animation-delay: 1.5s;"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-indigo-950/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md bg-white/95 backdrop-blur-2xl rounded-3xl p-7 sm:p-9 shadow-2xl border border-white/20 relative z-10 space-y-4">
        
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <a href="<?= BASE_URL ?>/index.php" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-blue-600 transition">
                <i class="fa-solid fa-arrow-left text-[11px]"></i> Papan Kehadiran Hari Ini
            </a>
            <span class="text-[10.5px] font-mono text-slate-400 font-semibold"><?= date('d M Y') ?></span>
        </div>

        <!-- Header Logo -->
        <div class="text-center mb-6">
            <div class="inline-flex p-2.5 bg-white rounded-2xl shadow-md border border-slate-100 mb-3 max-h-16">
                <img src="<?= $loginLogoUrl ?>" alt="<?= htmlspecialchars($appSettings['nama_perusahaan']) ?>" class="h-11 w-auto max-w-[180px] object-contain">
            </div>
            <h2 class="font-display text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase"><?= htmlspecialchars($appSettings['nama_perusahaan'] ?: 'PT. NAKAKIN INDONESIA') ?></h2>
            <p class="text-xs text-slate-500 font-semibold mt-0.5"><?= htmlspecialchars($appSettings['tagline'] ?: $appSettings['nama_aplikasi']) ?></p>
        </div>

        <!-- Form Login -->
        <form action="<?= BASE_URL ?>/index.php?page=login-process" method="POST" class="space-y-4">
            
            <div>
                <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    NIK atau Alamat Email
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-badge text-sm"></i>
                    </span>
                    <input type="text" name="username" id="username" required autofocus placeholder="Contoh: NAK-001 atau email"
                           class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition placeholder:text-slate-400">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Password Akun
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-sm"></i>
                    </span>
                    <input type="password" name="password" id="password" required placeholder="Masukkan password Anda"
                           class="w-full pl-11 pr-12 py-3 rounded-2xl border border-slate-200 bg-slate-50/50 text-slate-900 font-semibold text-sm focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 focus:bg-white transition placeholder:text-slate-400">
                    <button type="button" id="btnTogglePassword" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-slate-600">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-rose-600 via-red-600 to-rose-700 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-sm shadow-lg shadow-rose-600/40 transform hover:-translate-y-0.5 active:translate-y-0 transition duration-200 flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>Masuk ke Sistem Cuti</span>
            </button>
        </form>

        <!-- Quick Demo Selector -->
        <div class="mt-6 pt-5 border-t border-slate-100">
            <div class="text-center mb-2.5">
                <span class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-key text-amber-500 text-xs"></i> Akses Cepat Akun Demo (Klik untuk Isi):
                </span>
            </div>
            
            <div class="grid grid-cols-2 gap-2.5">
                <!-- 1. Akun HRD -->
                <button type="button" class="quick-login p-2.5 rounded-2xl bg-emerald-50/90 hover:bg-emerald-100 border border-emerald-200 text-emerald-900 text-xs font-bold transition flex items-center justify-between shadow-2xs group" 
                        data-user="hrd@nakakin.co.id" data-pass="password123">
                    <div class="flex items-center gap-2 text-left truncate">
                        <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-xs flex-shrink-0 shadow-2xs">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-black text-slate-900 truncate">Risma (HRD)</div>
                            <div class="text-[10px] text-emerald-700 font-mono truncate">hrd@nakakin.co.id</div>
                        </div>
                    </div>
                    <span class="text-[10px] bg-emerald-600 text-white px-2 py-0.5 rounded-md font-bold group-hover:scale-105 transition flex-shrink-0 ml-1">Pilih</span>
                </button>

                <!-- 2. Akun Manager -->
                <button type="button" class="quick-login p-2.5 rounded-2xl bg-purple-50/90 hover:bg-purple-100 border border-purple-200 text-purple-900 text-xs font-bold transition flex items-center justify-between shadow-2xs group" 
                        data-user="manager@nakakin.co.id" data-pass="password123">
                    <div class="flex items-center gap-2 text-left truncate">
                        <div class="w-7 h-7 rounded-xl bg-purple-600 text-white flex items-center justify-center text-xs flex-shrink-0 shadow-2xs">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-black text-slate-900 truncate">Akun Manager</div>
                            <div class="text-[10px] text-purple-700 font-mono truncate">manager@nakakin.co.id</div>
                        </div>
                    </div>
                    <span class="text-[10px] bg-purple-600 text-white px-2 py-0.5 rounded-md font-bold group-hover:scale-105 transition flex-shrink-0 ml-1">Pilih</span>
                </button>

                <!-- 3. Akun Leader Core -->
                <button type="button" class="quick-login p-2.5 rounded-2xl bg-sky-50/90 hover:bg-sky-100 border border-sky-200 text-sky-900 text-xs font-bold transition flex items-center justify-between shadow-2xs group" 
                        data-user="ldr.core@nakakin.co.id" data-pass="password123">
                    <div class="flex items-center gap-2 text-left truncate">
                        <div class="w-7 h-7 rounded-xl bg-sky-600 text-white flex items-center justify-center text-xs flex-shrink-0 shadow-2xs">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-black text-slate-900 truncate">Akun Leader Core</div>
                            <div class="text-[10px] text-sky-700 font-mono truncate">ldr.core@nakakin.co.id</div>
                        </div>
                    </div>
                    <span class="text-[10px] bg-sky-600 text-white px-2 py-0.5 rounded-md font-bold group-hover:scale-105 transition flex-shrink-0 ml-1">Pilih</span>
                </button>

                <!-- 4. Akun Operator 1 Core -->
                <button type="button" class="quick-login p-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200/90 border border-slate-200 text-slate-800 text-xs font-bold transition flex items-center justify-between shadow-2xs group" 
                        data-user="op1.core@nakakin.co.id" data-pass="password123">
                    <div class="flex items-center gap-2 text-left truncate">
                        <div class="w-7 h-7 rounded-xl bg-slate-700 text-white flex items-center justify-center text-xs flex-shrink-0 shadow-2xs">
                            <i class="fa-solid fa-helmet-safety"></i>
                        </div>
                        <div class="truncate">
                            <div class="text-xs font-black text-slate-900 truncate">Operator 1 Core</div>
                            <div class="text-[10px] text-slate-600 font-mono truncate">op1.core@nakakin.co.id</div>
                        </div>
                    </div>
                    <span class="text-[10px] bg-slate-700 text-white px-2 py-0.5 rounded-md font-bold group-hover:scale-105 transition flex-shrink-0 ml-1">Pilih</span>
                </button>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
    <script>
        const btnToggle = document.getElementById('btnTogglePassword');
        const passInput = document.getElementById('password');
        if (btnToggle && passInput) {
            btnToggle.addEventListener('click', function() {
                const isPassword = passInput.type === 'password';
                passInput.type = isPassword ? 'text' : 'password';
                btnToggle.innerHTML = isPassword ? '<i class="fa-regular fa-eye-slash text-slate-600"></i>' : '<i class="fa-regular fa-eye text-slate-400"></i>';
            });
        }

        document.querySelectorAll('.quick-login').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('username').value = this.getAttribute('data-user');
                document.getElementById('password').value = this.getAttribute('data-pass');
            });
        });

        <?php if ($flash): ?>
            Swal.fire({
                icon: '<?= $flash['type'] === 'error' ? 'error' : ($flash['type'] === 'warning' ? 'warning' : 'success') ?>',
                title: '<?= $flash['type'] === 'error' ? 'Oops!' : ($flash['type'] === 'warning' ? 'Perhatian' : 'Berhasil!') ?>',
                text: '<?= addslashes($flash['message']) ?>',
                confirmButtonColor: '#2563eb',
                customClass: { popup: 'rounded-2xl shadow-2xl' }
            });
        <?php endif; ?>
    </script>
</body>
</html>
