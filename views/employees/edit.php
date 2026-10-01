<?php
/**
 * Edit Employee View (Tailwind CSS Edition with HRD Password Reset)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Edit Data Karyawan';
require_once __DIR__ . '/../layouts/header.php';

requireRole('admin');

$id = (int)($_GET['id'] ?? 0);
$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT * FROM karyawan WHERE id = ?");
$stmt->execute([$id]);
$emp = $stmt->fetch();

if (!$emp) {
    setFlash('error', 'Data karyawan tidak ditemukan!');
    header('Location: ' . BASE_URL . '/index.php?page=employees');
    exit;
}

$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();
$positions = $pdo->query("SELECT * FROM jabatan ORDER BY level_hierarki ASC")->fetchAll();
?>

<div class="w-full space-y-6">

    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-soft overflow-hidden">
        <div class="p-6 sm:p-7 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/40 dark:bg-slate-800/40">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg font-bold border border-blue-100 dark:border-blue-900">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white">Edit Data: <?= htmlspecialchars($emp['nama_lengkap']) ?></h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui data kepegawaian, profil, atau reset password akun karyawan.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=employees" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold transition flex items-center gap-1.5 border border-transparent dark:border-slate-700">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=employee-update" method="POST" id="editEmployeeForm" class="p-6 sm:p-8 space-y-8 text-xs">
            <input type="hidden" name="id" value="<?= $emp['id'] ?>">

            <!-- Section 1: Data Pekerjaan -->
            <div>
                <h4 class="text-xs font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-briefcase"></i>
                    </span>
                    <span>1. Penempatan & Data Kepegawaian</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">NIK Karyawan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" id="empNik" value="<?= htmlspecialchars($emp['nik']) ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Departemen <span class="text-rose-500">*</span></label>
                        <select name="departemen_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $emp['departemen_id'] == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['nama_dept']) ?> (<?= htmlspecialchars($dept['kode_dept']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Jabatan Kerja <span class="text-rose-500">*</span></label>
                        <select name="jabatan_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos['id'] ?>" <?= $emp['jabatan_id'] == $pos['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pos['nama_jabatan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="operator" <?= $emp['role'] === 'operator' ? 'selected' : '' ?>>Operator (Karyawan Produksi)</option>
                            <option value="staff" <?= $emp['role'] === 'staff' ? 'selected' : '' ?>>Staff (Administrasi / Teknis)</option>
                            <option value="leader" <?= $emp['role'] === 'leader' ? 'selected' : '' ?>>Leader</option>
                            <option value="supervisor" <?= $emp['role'] === 'supervisor' ? 'selected' : '' ?>>Supervisor</option>
                            <option value="manager" <?= $emp['role'] === 'manager' ? 'selected' : '' ?>>Plant Manager</option>
                            <option value="hrd" <?= $emp['role'] === 'hrd' ? 'selected' : '' ?>>HRD</option>
                            <option value="superadmin" <?= $emp['role'] === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Tanggal Masuk (Join Date) <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_masuk" value="<?= $emp['tanggal_masuk'] ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Status Kepegawaian</label>
                        <select name="status_aktif" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Aktif" <?= $emp['status_aktif'] === 'Aktif' ? 'selected' : '' ?>>Aktif Bekerja</option>
                            <option value="Non-Aktif" <?= $emp['status_aktif'] === 'Non-Aktif' ? 'selected' : '' ?>>Non-Aktif / Resign</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Total Kuota Cuti (Hari)</label>
                        <input type="number" name="kuota_cuti" value="<?= $emp['kuota_cuti'] ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Sisa Kuota Cuti (Hari)</label>
                        <input type="number" name="sisa_cuti" value="<?= $emp['sisa_cuti'] ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 transition">
                    </div>
                </div>
            </div>

            <!-- Section 2: Data Pribadi & Kontak -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                <h4 class="text-xs font-extrabold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <span>2. Profil Pribadi & Kontak</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($emp['nama_lengkap']) ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Email Akun / Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">No. Handphone / WhatsApp</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($emp['no_hp'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Laki-laki" <?= $emp['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $emp['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Status Pernikahan <span class="text-rose-500">*</span></label>
                        <select name="status_pernikahan" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Belum Menikah" <?= $emp['status_pernikahan'] === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                            <option value="Menikah" <?= $emp['status_pernikahan'] === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                            <option value="Duda" <?= $emp['status_pernikahan'] === 'Duda' ? 'selected' : '' ?>>Duda</option>
                            <option value="Janda" <?= $emp['status_pernikahan'] === 'Janda' ? 'selected' : '' ?>>Janda</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Alamat Domisili</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/15"><?= htmlspecialchars($emp['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Reset & Ganti Password Akun (Khusus HRD / Super Admin) -->
            <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                <div class="p-5 rounded-3xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-900/60 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-base shadow-sm">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-amber-900 dark:text-amber-300 uppercase tracking-wider">
                                    3. Reset / Ganti Password Akun Karyawan
                                </h4>
                                <p class="text-[11px] text-amber-700 dark:text-amber-400/90 mt-0.5">
                                    Jika karyawan lupa password akunnya, HRD/Super Admin dapat mereset atau memasukkan password baru di bawah ini.
                                </p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-800 dark:text-amber-300 font-extrabold text-[10.5px] border border-amber-300/60 dark:border-amber-700">
                            Wewenang HRD
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Password Baru <span class="text-slate-400 font-normal">(Kosongkan jika tidak diubah)</span>
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="newPasswordInput" placeholder="Masukkan password baru karyawan"
                                       class="w-full pl-4 pr-11 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white font-semibold focus:outline-none focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition">
                                <button type="button" onclick="togglePasswordVisibility('newPasswordInput', this)" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 transition">
                                    <i class="fa-solid fa-eye text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Tombol Cepat Reset Password
                            </label>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="setPasswordPreset('password123')" 
                                        class="px-3 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-slate-700 dark:text-slate-200 font-bold text-[11px] border border-amber-200 dark:border-amber-800 shadow-2xs transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-rotate-left text-amber-600 dark:text-amber-400"></i> Set Default (<code class="text-amber-600 dark:text-amber-400">password123</code>)
                                </button>
                                <button type="button" onclick="setPasswordPreset('<?= addslashes(htmlspecialchars($emp['nik'])) ?>')" 
                                        class="px-3 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-slate-700 dark:text-slate-200 font-bold text-[11px] border border-amber-200 dark:border-amber-800 shadow-2xs transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-id-badge text-blue-600 dark:text-blue-400"></i> Gunakan NIK
                                </button>
                                <button type="button" onclick="generateRandomPassword()" 
                                        class="px-3 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-amber-100 dark:hover:bg-amber-900/60 text-slate-700 dark:text-slate-200 font-bold text-[11px] border border-amber-200 dark:border-amber-800 shadow-2xs transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-dice text-emerald-600 dark:text-emerald-400"></i> Acak Password
                                </button>
                                <button type="button" onclick="copyPasswordToClipboard()" 
                                        class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] shadow-2xs transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-copy"></i> Salin Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="passwordAlertBadge" class="hidden p-3 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 font-bold text-[11px] flex items-center justify-between">
                        <span id="passwordAlertText"></span>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400">Klik "Simpan Perubahan" untuk menerapkan</span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                <a href="<?= BASE_URL ?>/index.php?page=employees" class="px-5 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-extrabold shadow-md shadow-blue-600/30 transition transform hover:-translate-y-0.5 flex items-center gap-2">
                    <i class="fa-solid fa-save"></i> Simpan Perubahan Data & Password
                </button>
            </div>

        </form>
    </div>

</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function setPasswordPreset(passwordValue) {
    const input = document.getElementById('newPasswordInput');
    input.value = passwordValue;
    input.type = 'text'; // Show it so HRD can confirm the value
    
    showPasswordNotice('Password telah diset ke: "' + passwordValue + '"');
}

function generateRandomPassword() {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%';
    let result = '';
    for (let i = 0; i < 8; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    setPasswordPreset(result);
}

function showPasswordNotice(msg) {
    const badge = document.getElementById('passwordAlertBadge');
    const text = document.getElementById('passwordAlertText');
    if (badge && text) {
        text.innerText = msg;
        badge.classList.remove('hidden');
    }
}

function copyPasswordToClipboard() {
    const input = document.getElementById('newPasswordInput');
    if (!input.value) {
        Swal.fire({
            icon: 'info',
            title: 'Password Masih Kosong',
            text: 'Silakan ketik atau pilih tombol preset password terlebih dahulu.',
            confirmButtonColor: '#3b82f6',
            customClass: { popup: 'modern-swal-popup' }
        });
        return;
    }

    navigator.clipboard.writeText(input.value).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'Password "' + input.value + '" berhasil disalin ke clipboard. Anda dapat membagikannya kepada karyawan.',
            timer: 2000,
            showConfirmButton: false,
            customClass: { popup: 'modern-swal-popup' }
        });
    }).catch(() => {
        input.select();
        document.execCommand('copy');
        alert('Password tersalin ke clipboard: ' + input.value);
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
