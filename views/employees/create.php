<?php
/**
 * Create Employee View (Tailwind CSS Edition)
 * PT. Nakakin Indonesia Leave Management System
 */

$pageTitle = 'Tambah Karyawan Baru';
require_once __DIR__ . '/../layouts/header.php';

requireRole('admin');

$pdo = getDbConnection();
$departments = $pdo->query("SELECT * FROM departemen ORDER BY nama_dept ASC")->fetchAll();
$positions = $pdo->query("SELECT * FROM jabatan ORDER BY level_hierarki ASC")->fetchAll();
$appSettings = getAppSettings($pdo);
?>

<div class="w-full space-y-6">

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-6 sm:p-7 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Pendaftaran Karyawan Baru</h2>
                    <p class="text-xs text-slate-500">Isi data profil dan penempatan kerja karyawan <?= htmlspecialchars($appSettings['nama_perusahaan']) ?>.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=employees" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=employee-save" method="POST" class="p-6 sm:p-8 space-y-6 text-xs">
            
            <!-- Section 1: Data Pekerjaan -->
            <div>
                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <i class="fa-solid fa-briefcase"></i> 1. Penempatan & Data Pekerjaan
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIK Karyawan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" required placeholder="Contoh: NAK-055"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Departemen <span class="text-rose-500">*</span></label>
                        <select name="departemen_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="">-- Pilih Departemen --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['nama_dept']) ?> (<?= htmlspecialchars($dept['kode_dept']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan Kerja <span class="text-rose-500">*</span></label>
                        <select name="jabatan_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="">-- Pilih Jabatan --</option>
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos['id'] ?>"><?= htmlspecialchars($pos['nama_jabatan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="operator">Operator (Karyawan Produksi)</option>
                            <option value="staff">Staff (Administrasi / Teknis)</option>
                            <option value="atasan">Atasan (Leader / Spv / Asmen / Manager)</option>
                            <option value="admin">HRD / Super Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Masuk (Join Date) <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_masuk" value="<?= date('Y-m-d') ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jatah Kuota Cuti Awal <span class="text-rose-500">*</span></label>
                        <input type="number" name="kuota_cuti" value="12" min="0" max="30" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                </div>
            </div>

            <!-- Section 2: Data Pribadi & Akun -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <i class="fa-solid fa-user"></i> 2. Profil Pribadi & Akun Login
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap Sesuai KTP <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" required placeholder="Contoh: Budi Santoso"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" required placeholder="Contoh: budi@nakakin.co.id"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password Akun <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" value="password123" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                        <span class="text-[10.5px] text-slate-400 mt-1 block">Default: <code>password123</code></span>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Handphone / WhatsApp</label>
                        <input type="text" name="no_hp" placeholder="Contoh: 081234567890"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Pernikahan <span class="text-rose-500">*</span></label>
                        <select name="status_pernikahan" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Belum Menikah">Belum Menikah</option>
                            <option value="Menikah">Menikah</option>
                            <option value="Duda">Duda</option>
                            <option value="Janda">Janda</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Domisili / Tempat Tinggal</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat lengkap tempat tinggal karyawan di Karawang/sekitarnya..."
                                  class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/15"></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= BASE_URL ?>/index.php?page=employees" class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-extrabold shadow-md shadow-rose-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Data Karyawan
                </button>
            </div>

        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
