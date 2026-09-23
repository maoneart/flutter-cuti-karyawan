<?php
/**
 * Edit Employee View (Tailwind CSS Edition)
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

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-6 sm:p-7 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <div>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">Edit Data: <?= htmlspecialchars($emp['nama_lengkap']) ?></h2>
                    <p class="text-xs text-slate-500">Perbarui data kepegawaian atau profil akun karyawan.</p>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?page=employees" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>

        <form action="<?= BASE_URL ?>/index.php?page=employee-update" method="POST" class="p-6 sm:p-8 space-y-6 text-xs">
            <input type="hidden" name="id" value="<?= $emp['id'] ?>">

            <!-- Section 1: Data Pekerjaan -->
            <div>
                <h4 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                    <i class="fa-solid fa-briefcase"></i> 1. Penempatan & Data Pekerjaan
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">NIK Karyawan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nik" value="<?= htmlspecialchars($emp['nik']) ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Departemen <span class="text-rose-500">*</span></label>
                        <select name="departemen_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" <?= $emp['departemen_id'] == $dept['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['nama_dept']) ?> (<?= htmlspecialchars($dept['kode_dept']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan Kerja <span class="text-rose-500">*</span></label>
                        <select name="jabatan_id" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <?php foreach ($positions as $pos): ?>
                                <option value="<?= $pos['id'] ?>" <?= $emp['jabatan_id'] == $pos['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pos['nama_jabatan']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Hak Akses (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="operator" <?= $emp['role'] === 'operator' ? 'selected' : '' ?>>Operator (Karyawan Produksi)</option>
                            <option value="staff" <?= $emp['role'] === 'staff' ? 'selected' : '' ?>>Staff (Administrasi / Teknis)</option>
                            <option value="atasan" <?= $emp['role'] === 'atasan' ? 'selected' : '' ?>>Atasan (Leader / Spv / Asmen / Manager)</option>
                            <option value="admin" <?= $emp['role'] === 'admin' ? 'selected' : '' ?>>HRD / Super Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Masuk (Join Date) <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_masuk" value="<?= $emp['tanggal_masuk'] ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Kepegawaian</label>
                        <select name="status_aktif" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Aktif" <?= $emp['status_aktif'] === 'Aktif' ? 'selected' : '' ?>>Aktif Bekerja</option>
                            <option value="Non-Aktif" <?= $emp['status_aktif'] === 'Non-Aktif' ? 'selected' : '' ?>>Non-Aktif / Resign</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Total Kuota Cuti (Hari)</label>
                        <input type="number" name="kuota_cuti" value="<?= $emp['kuota_cuti'] ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Sisa Kuota Cuti (Hari)</label>
                        <input type="number" name="sisa_cuti" value="<?= $emp['sisa_cuti'] ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-bold focus:outline-none focus:ring-4 focus:ring-blue-500/15 transition">
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
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($emp['nama_lengkap']) ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Perusahaan <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="<?= htmlspecialchars($emp['email']) ?>" required
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ganti Password (Opsional)</label>
                        <input type="password" name="password" placeholder="Kosongkan jika tidak ganti password"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">No. Handphone / WhatsApp</label>
                        <input type="text" name="no_hp" value="<?= htmlspecialchars($emp['no_hp'] ?? '') ?>"
                               class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15 focus:border-blue-500 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select name="jenis_kelamin" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Laki-laki" <?= $emp['jenis_kelamin'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= $emp['jenis_kelamin'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Status Pernikahan <span class="text-rose-500">*</span></label>
                        <select name="status_pernikahan" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white font-semibold focus:outline-none focus:ring-4 focus:ring-blue-500/15">
                            <option value="Belum Menikah" <?= $emp['status_pernikahan'] === 'Belum Menikah' ? 'selected' : '' ?>>Belum Menikah</option>
                            <option value="Menikah" <?= $emp['status_pernikahan'] === 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                            <option value="Duda" <?= $emp['status_pernikahan'] === 'Duda' ? 'selected' : '' ?>>Duda</option>
                            <option value="Janda" <?= $emp['status_pernikahan'] === 'Janda' ? 'selected' : '' ?>>Janda</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Domisili</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-3 rounded-2xl border border-slate-200 bg-white text-slate-900 font-medium focus:outline-none focus:ring-4 focus:ring-blue-500/15"><?= htmlspecialchars($emp['alamat'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="<?= BASE_URL ?>/index.php?page=employees" class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition">
                    Batal
                </a>
                <button type="submit" class="px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold shadow-md shadow-blue-600/30 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan Data
                </button>
            </div>

        </form>
    </div>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
