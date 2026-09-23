import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../config/app_theme.dart';
import '../services/auth_service.dart';

class UserGuideScreen extends StatefulWidget {
  const UserGuideScreen({super.key});

  @override
  State<UserGuideScreen> createState() => _UserGuideScreenState();
}

class _UserGuideScreenState extends State<UserGuideScreen> {
  // Store expanded state for tutorial cards
  final Set<int> _expandedSteps = {0}; // First step expanded by default
  String _searchQuery = '';

  void _toggleStep(int index) {
    setState(() {
      if (_expandedSteps.contains(index)) {
        _expandedSteps.remove(index);
      } else {
        _expandedSteps.add(index);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    // Detect user role & hierarchy
    final userRole = (user?.role ?? 'operator').toLowerCase();
    final userLevel = user?.levelHierarki ?? 1;

    String roleCategory = 'operator';
    String roleTitle = 'Operator & Staff';
    String roleSubtitle = 'Tutorial langkah pengajuan cuti, upload surat dokter, cek kuota, dan lacak status.';
    Color roleColor = const Color(0xFF0284C7); // Sky blue
    IconData roleIcon = CupertinoIcons.person_fill;

    if (userRole == 'hrd' || userRole == 'admin' || userRole == 'superadmin' || userLevel >= 7) {
      roleCategory = 'hrd';
      roleTitle = 'HRD & Super Admin';
      roleSubtitle = 'Tutorial persetujuan final Tier 3, pemotongan kuota cuti resmi, dan pengelolaan data karyawan.';
      roleColor = const Color(0xFF7C3AED); // Purple
      roleIcon = CupertinoIcons.shield_lefthalf_fill;
    } else if (userRole == 'manager' || userLevel == 6) {
      roleCategory = 'manager';
      roleTitle = 'Plant Manager';
      roleSubtitle = 'Tutorial persetujuan Tier 2 lintas 15 departemen pabrik & pengawasan lini produksi.';
      roleColor = const Color(0xFFD97706); // Amber
      roleIcon = CupertinoIcons.briefcase_fill;
    } else if (userRole == 'leader' || userRole == 'supervisor' || userLevel >= 3) {
      roleCategory = 'leader';
      roleTitle = 'Leader & Supervisor';
      roleSubtitle = 'Tutorial persetujuan Tier 1 tim departemen, tolak/setujui, dan alur cuti pribadi.';
      roleColor = const Color(0xFF16A34A); // Emerald
      roleIcon = CupertinoIcons.person_2_fill;
    }

    final steps = _getStepsForRole(roleCategory);
    final filteredSteps = _searchQuery.isEmpty
        ? steps
        : steps.where((s) => s.title.toLowerCase().contains(_searchQuery.toLowerCase()) || s.desc.toLowerCase().contains(_searchQuery.toLowerCase())).toList();

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
        centerTitle: true,
        leading: CupertinoButton(
          padding: EdgeInsets.zero,
          onPressed: () => Navigator.pop(context),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(CupertinoIcons.chevron_back, color: Color(0xFF007AFF), size: 28),
              Text('Kembali', style: TextStyle(color: Color(0xFF007AFF), fontSize: 16, fontWeight: FontWeight.w500)),
            ],
          ),
        ),
        leadingWidth: 95,
        title: Text(
          'Panduan Aplikasi',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: textHead),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. Role Hero Header Card (Internal Page Style)
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [roleColor, roleColor.withOpacity(0.85)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: roleColor.withOpacity(0.28),
                        blurRadius: 14,
                        offset: const Offset(0, 5),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(10),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.2),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Icon(roleIcon, color: Colors.white, size: 24),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.25),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    'PANDUAN KHUSUS: ${user?.namaJabatan.toUpperCase() ?? roleTitle.toUpperCase()}',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontWeight: FontWeight.w800,
                                      fontSize: 10.5,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Hai, ${user?.namaLengkap ?? "Pengguna"} 👋',
                                  style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        roleSubtitle,
                        style: const TextStyle(color: Colors.white, fontSize: 12.5, height: 1.4),
                      ),
                      const SizedBox(height: 14),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.18),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Row(
                          children: [
                            const Icon(CupertinoIcons.checkmark_shield_fill, color: Colors.white, size: 16),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Departemen: ${user?.namaDept ?? "-"} (Level ${user?.levelHierarki ?? 1})',
                                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // 2. Search Tutorial Box
                TextField(
                  onChanged: (val) => setState(() => _searchQuery = val),
                  decoration: InputDecoration(
                    hintText: 'Cari langkah panduan...',
                    prefixIcon: const Icon(Icons.search, size: 20),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                  ),
                ),
                const SizedBox(height: 16),

                // 3. Tutorial Accordion List Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'DAFTAR TUTORIAL (${filteredSteps.length} LANGKAH)',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: textSub,
                        letterSpacing: 0.5,
                      ),
                    ),
                    TextButton(
                      onPressed: () {
                        setState(() {
                          if (_expandedSteps.length == filteredSteps.length) {
                            _expandedSteps.clear();
                          } else {
                            _expandedSteps.addAll(List.generate(filteredSteps.length, (i) => i));
                          }
                        });
                      },
                      child: Text(
                        _expandedSteps.length == filteredSteps.length ? 'Tutup Semua' : 'Buka Semua',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: roleColor),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),

                // 4. Tutorial Cards (Expandable Accordion)
                if (filteredSteps.isEmpty)
                  Container(
                    padding: const EdgeInsets.all(24),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: borderCol),
                    ),
                    child: Center(
                      child: Text('Tidak ditemukan tutorial dengan kata kunci "$_searchQuery"', style: TextStyle(color: textSub)),
                    ),
                  )
                else
                  ...List.generate(filteredSteps.length, (index) {
                    final item = filteredSteps[index];
                    final isExpanded = _expandedSteps.contains(index);

                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: InkWell(
                        onTap: () => _toggleStep(index),
                        borderRadius: BorderRadius.circular(16),
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 200),
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: cardBg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isExpanded ? roleColor.withOpacity(0.5) : borderCol,
                              width: isExpanded ? 1.5 : 1.0,
                            ),
                            boxShadow: isExpanded
                                ? [
                                    BoxShadow(
                                      color: roleColor.withOpacity(0.08),
                                      blurRadius: 10,
                                      offset: const Offset(0, 3),
                                    ),
                                  ]
                                : null,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Step Header Row
                              Row(
                                children: [
                                  Container(
                                    width: 34,
                                    height: 34,
                                    decoration: BoxDecoration(
                                      color: isExpanded ? roleColor : roleColor.withOpacity(0.12),
                                      shape: BoxShape.circle,
                                    ),
                                    alignment: Alignment.center,
                                    child: Text(
                                      '${index + 1}',
                                      style: TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                        color: isExpanded ? Colors.white : roleColor,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          item.title,
                                          style: TextStyle(
                                            fontSize: 14.5,
                                            fontWeight: FontWeight.bold,
                                            color: textHead,
                                          ),
                                        ),
                                        if (!isExpanded) ...[
                                          const SizedBox(height: 2),
                                          Text(
                                            item.shortSummary,
                                            style: TextStyle(fontSize: 11.5, color: textSub),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Icon(
                                    isExpanded ? CupertinoIcons.chevron_up : CupertinoIcons.chevron_down,
                                    size: 18,
                                    color: textSub,
                                  ),
                                ],
                              ),

                              // Expanded Content
                              if (isExpanded) ...[
                                Divider(height: 24, color: borderCol),
                                Text(
                                  item.desc,
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF334155),
                                    height: 1.5,
                                  ),
                                ),
                                if (item.tips != null && item.tips!.isNotEmpty) ...[
                                  const SizedBox(height: 12),
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: borderCol),
                                    ),
                                    child: Row(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Icon(CupertinoIcons.lightbulb_fill, color: Color(0xFFF59E0B), size: 18),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            item.tips!,
                                            style: TextStyle(fontSize: 11.5, color: textSub, height: 1.4),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ],
                            ],
                          ),
                        ),
                      ),
                    );
                  }),

                const SizedBox(height: 20),

                // 5. Help Footer
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderCol),
                  ),
                  child: Row(
                    children: [
                      const Icon(CupertinoIcons.question_circle_fill, color: Color(0xFF0284C7), size: 28),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Ada Pertanyaan Lain?',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: textHead),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Silakan koordinasi langsung dengan HRD / GA PT. Nakakin Indonesia jika membutuhkan penyesuaian kuota atau data.',
                              style: TextStyle(fontSize: 11.5, color: textSub, height: 1.3),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 30),
              ],
            ),
          ),
        ),
      ),
    );
  }

  // ==========================================
  // TUTORIAL DATA BUILDERS
  // ==========================================
  List<_GuideStep> _getStepsForRole(String roleCategory) {
    switch (roleCategory) {
      case 'leader':
        return [
          _GuideStep(
            title: 'Memantau Dashboard & Notifikasi Pending Tim',
            shortSummary: 'Melihat ringkasan cuti tim & badge merah lonceng notifikasi.',
            desc: 'Pada layar Dashboard, Anda dapat melihat total pengajuan cuti yang masuk dari anggota di 1 departemen Anda.\n\n• Lonceng Notifikasi: Menampilkan badge angka merah yang menandakan jumlah pengajuan bawahan yang menunggu tindakan persetujuan Anda.\n• Kartu Status Tim: Memberikan ringkasan berapa anggota yang sedang cuti hari ini.',
            tips: 'Periksa lonceng notifikasi secara berkala agar pengajuan operator/staff segera diproses.',
          ),
          _GuideStep(
            title: 'Membuka Menu Persetujuan (Tier 1)',
            shortSummary: 'Akses menu Persetujuan di navigasi bawah.',
            desc: 'Untuk memproses cuti tim:\n1. Buka menu tab "Persetujuan" di bar navigasi bawah.\n2. Daftar permohonan anggota satu departemen akan tampil terurut dari yang paling awal diajukan.\n3. Anda dapat memfilter status: "Semua", "Menunggu", "Disetujui", atau "Ditolak".',
          ),
          _GuideStep(
            title: 'Membuka Detail Pengajuan & Lampiran Surat Dokter',
            shortSummary: 'Cek alasan, durasi tanggal, dan file surat dokter.',
            desc: 'Ketuk pada kartu permohonan untuk membuka Halaman Detail:\n• Anda dapat memeriksa NIK, nama lengkap, departemen, dan jabatan pemohon.\n• Periksa tanggal mulai s/d selesai dan alasan pengajuan.\n• Jika jenis cuti adalah Cuti Sakit: Anda dapat melihat dan mengklik lampiran Surat Dokter untuk memastikan keabsahannya.',
          ),
          _GuideStep(
            title: 'Menyetujui Pengajuan (Lolos ke Plant Manager)',
            shortSummary: 'Tombol "Setujui" meneruskan berkas ke Tier 2.',
            desc: 'Jika permohonan disetujui:\n1. Pastikan tombol hijau "Setujui" aktif (hanya aktif saat status "Menunggu Review Spv").\n2. Tekan tombol "Setujui".\n3. Pengajuan akan beralih status ke tahap Tier 2 ("Menunggu Review Manager") dan diteruskan ke Plant Manager.',
            tips: 'Tombol Setujui hanya aktif jika giliran Anda tiba. Jika sudah disetujui, tombol otomatis dinonaktifkan.',
          ),
          _GuideStep(
            title: 'Menolak Pengajuan (Proses Selesai Ditolak)',
            shortSummary: 'Tombol "Tolak" menghentikan proses secara permanen.',
            desc: 'Jika permohonan tidak dapat diizinkan:\n1. Tekan tombol merah "Tolak".\n2. Konfirmasi tindakan penolakan.\n3. Pengajuan langsung berstatus "Ditolak" (rejected) dan proses approval selesai seketika tanpa diteruskan ke Plant Manager atau HRD.',
            tips: 'Penolakan bersifat final. Pemohon harus membuat pengajuan baru jika ingin mengajukan kembali.',
          ),
          _GuideStep(
            title: 'Mengajukan Cuti Pribadi (Bypass Tier 1)',
            shortSummary: 'Pengajuan pribadi Leader langsung dialihkan ke Tier 2.',
            desc: 'Sebagai Leader / Supervisor, ketika Anda mengajukan cuti pribadi melalui menu "+ Ajukan Cuti":\n• Sistem secara otomatis melompati (bypass) tahap Leader/Supervisor.\n• Pengajuan Anda langsung masuk ke tahap Tier 2 (Plant Manager) dan dilanjutkan ke HRD.',
          ),
          _GuideStep(
            title: 'Papan Kehadiran Live & Kalender Departemen',
            shortSummary: 'Monitoring ketersediaan orang sebelum approval.',
            desc: 'Gunakan fitur "Papan Live" di Dashboard untuk mengecek siapa saja yang sedang cuti pada tanggal yang sama, guna memastikan kebutuhan personil di line produksi tetap tercukupi.',
          ),
        ];

      case 'manager':
        return [
          _GuideStep(
            title: 'Dashboard Pabrik Lintas 15 Departemen',
            shortSummary: 'Monitoring status kehadiran di seluruh area pabrik.',
            desc: 'Plant Manager memiliki akses pemantauan kehadiran dan pengajuan cuti yang mencakup seluruh 15 departemen di PT. Nakakin Indonesia (Casting, Machining, Engineering, QC, GA, PPIC, dll).',
          ),
          _GuideStep(
            title: 'Melakukan Approval Tahap 2 (Plant Manager)',
            shortSummary: 'Memproses berkas yang telah disetujui Leader Departemen.',
            desc: 'Alur persetujuan Plant Manager:\n1. Buka menu tab "Persetujuan".\n2. Pengajuan yang membutuhkan tindakan Anda adalah pengajuan yang berstatus "Menunggu Review Manager" (telah disetujui Leader departemen terkait).\n3. Tombol Setujui dan Tolak hanya aktif saat giliran Anda tiba.',
            tips: 'Jika pengajuan masih berstatus pending_spv, tombol akan nonaktif dengan keterangan menunggu Leader.',
          ),
          _GuideStep(
            title: 'Menyetujui / Menolak Berkas Pabrik',
            shortSummary: 'Persetujuan meneruskan ke HRD; penolakan menghentikan alur.',
            desc: '• Setujui: Berkas beralih ke tahap Tier 3 ("Menunggu Review HRD") untuk finalisasi pemotongan kuota.\n• Tolak: Berkas langsung berstatus "Ditolak" dan proses selesai seketika.',
          ),
          _GuideStep(
            title: 'Pengawasan Produksi via Papan Live',
            shortSummary: 'Mencegah kekurangan tenaga kerja di line krusial.',
            desc: 'Buka menu "Papan Live" untuk mengevaluasi dampak cuti massal terhadap target output produksi pabrik.',
          ),
          _GuideStep(
            title: 'Pengajuan Cuti Pribadi Manager',
            shortSummary: 'Bypass langsung ke HRD.',
            desc: 'Pengajuan cuti pribadi Plant Manager secara otomatis melewati Tier 1 & Tier 2, dan langsung masuk ke HRD (Tier 3 Final).',
          ),
        ];

      case 'hrd':
        return [
          _GuideStep(
            title: 'Persetujuan Final (Tier 3) & Pemotongan Kuota Resmi',
            shortSummary: 'Eksekusi pemotongan sisa kuota cuti di database.',
            desc: 'HRD memproses tahap akhir (status: pending_hrd) setelah pengajuan disetujui oleh Leader Departemen dan Plant Manager.\n\n• Ketika HRD menekan tombol "Setujui", status pengajuan menjadi "Disetujui" (approved) dan sisa kuota cuti tahunan karyawan otomatis terpotong di database.',
            tips: 'Sisa kuota cuti HANYA berkurang jika HRD telah menyetujui pengajuan di tahap final ini.',
          ),
          _GuideStep(
            title: 'Mengakses Menu List Karyawan',
            shortSummary: 'Melihat seluruh database pegawai PT. Nakakin Indonesia.',
            desc: '1. Di halaman Dashboard atau Pengaturan, klik tombol "List Karyawan".\n2. Anda dapat melihat daftar seluruh karyawan dari 15 departemen.\n3. Tersedia fitur Pencarian Cepat (Nama, NIK, Email) dan Filter per Departemen.\n4. Klik salah satu karyawan untuk melihat detail masa kerja, kuota cuti, dan nomor kontak.',
          ),
          _GuideStep(
            title: 'Menambah Karyawan Baru (+ Tambah Karyawan)',
            shortSummary: 'Pendaftaran akun pegawai dan jatah kuota cuti awal.',
            desc: 'Untuk mendaftarkan pegawai baru:\n1. Buka halaman "List Karyawan".\n2. Tekan tombol "+ Tambah Karyawan" (di sudut kanan atas atau tombol melayang).\n3. Isi NIK, Nama Lengkap, Email, Password, Departemen, Jabatan/Role, dan Kuota Cuti Awal.\n4. Tekan "Simpan Data Karyawan". Akun baru siap digunakan login.',
          ),
          _GuideStep(
            title: 'Pengaturan Web, Logo & Kop Surat Perusahaan',
            shortSummary: 'Pengelolaan master data di panel Webbase Admin.',
            desc: 'Melalui browser di komputer kantor, HRD & Super Admin dapat login ke panel web untuk mengatur Logo Perusahaan, Template Kop Surat Cuti, Master Departemen, dan Master Jabatan.',
          ),
        ];

      default: // operator / staff
        return [
          _GuideStep(
            title: 'Memeriksa Dashboard & Sisa Kuota Cuti',
            shortSummary: 'Melihat sisa jatah cuti tahunan & masa kerja.',
            desc: 'Pada layar Dashboard, Anda dapat melihat:\n• Sisa Kuota Cuti: Jumlah hari cuti tahunan yang masih dapat Anda gunakan.\n• Cuti Terpakai: Total hari cuti yang sudah Anda ambil pada tahun berjalan.\n• Masa Kerja: Durasi lama Anda bekerja di PT. Nakakin Indonesia.\n• Status Pengajuan Terkini: Ringkasan pengajuan terakhir Anda.',
            tips: 'Sisa kuota cuti Anda baru akan berkurang setelah permohonan disetujui final oleh HRD.',
          ),
          _GuideStep(
            title: 'Membuka Form Pengajuan Cuti Baru',
            shortSummary: 'Klik tombol "+ Ajukan Cuti" di menu bawah atau Dashboard.',
            desc: 'Untuk membuat permohonan cuti:\n1. Tekan tombol "+ Ajukan Cuti" pada bar navigasi bawah atau tombol cepat di Dashboard.\n2. Anda akan diarahkan ke formulir pengajuan cuti resmi.',
          ),
          _GuideStep(
            title: 'Mengisi Tanggal & Memilih Jenis Cuti',
            shortSummary: 'Pilih jenis cuti dan tentukan rentang tanggal.',
            desc: 'Langkah pengisian formulir:\n• Pilih Jenis Cuti: Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, Cuti Menikah, atau Izin Penting.\n• Pilih Tanggal Mulai dan Tanggal Selesai cuti.\n• Sistem akan otomatis menghitung durasi hari kerja cuti yang Anda ajukan.',
          ),
          _GuideStep(
            title: 'Mengisi Alasan Permohonan Cuti',
            shortSummary: 'Tuliskan alasan yang jelas dan dapat dipertanggungjawabkan.',
            desc: 'Ketikkan alasan keperluan cuti pada kolom teks yang tersedia secara jelas dan sopan agar memudahkan atasan Anda dalam memberikan persetujuan.',
          ),
          _GuideStep(
            title: 'Upload Lampiran Surat Dokter (Jika Cuti Sakit)',
            shortSummary: 'Wajib melampirkan foto/dokumen untuk cuti sakit.',
            desc: 'Khusus untuk Jenis Cuti Sakit:\n1. Klik tombol "Pilih Lampiran / Dokumen".\n2. Ambil foto surat dokter menggunakan kamera HP atau pilih file dari galeri/penyimpanan.\n3. Format yang didukung: JPG, PNG, dan PDF.',
            tips: 'Pastikan foto surat dokter terbaca dengan jelas agar tidak ditolak oleh atasan/HRD.',
          ),
          _GuideStep(
            title: 'Mengirimkan Pengajuan Cuti',
            shortSummary: 'Tekan tombol "Kirim Pengajuan Cuti".',
            desc: 'Periksa kembali seluruh data formulir, lalu tekan tombol "Kirim Pengajuan Cuti". Berkas Anda otomatis terkirim dan langsung masuk ke antrean review Leader departemen Anda.',
          ),
          _GuideStep(
            title: 'Melihat Status & Timeline Approval Bertingkat',
            shortSummary: 'Pantau proses approval secara transparan di menu Riwayat.',
            desc: 'Buka menu "Riwayat" di navigasi bawah untuk melihat status pengajuan Anda:\n• "Review Spv": Sedang menunggu persetujuan Leader/Supervisor departemen.\n• "Review Manager": Telah disetujui Leader, sedang menunggu Plant Manager.\n• "Review HRD": Sedang menunggu persetujuan akhir HRD.\n• "Disetujui": Pengajuan selesai dan disetujui seluruhnya.\n• "Ditolak": Pengajuan ditolak oleh atasan.',
          ),
          _GuideStep(
            title: 'Membatalkan Pengajuan Cuti',
            shortSummary: 'Batalkan permohonan selama belum disetujui.',
            desc: 'Jika Anda ingin membatalkan cuti yang terlanjur diajukan:\n1. Buka detail pengajuan di menu Riwayat.\n2. Tekan tombol "Batalkan Cuti" di bagian bawah layar.\n3. Tombol ini hanya tersedia jika status pengajuan masih dalam antrean (pending).',
          ),
          _GuideStep(
            title: 'Melihat Papan Kehadiran Live (Papan Live)',
            shortSummary: 'Mengecek jadwal cuti rekan kerja hari ini.',
            desc: 'Klik tombol "Papan Live" di Dashboard untuk melihat siapa saja rekan kerja di pabrik yang sedang cuti hari ini agar memudahkan koordinasi operasional tim Anda.',
          ),
        ];
    }
  }
}

class _GuideStep {
  final String title;
  final String shortSummary;
  final String desc;
  final String? tips;

  _GuideStep({
    required this.title,
    required this.shortSummary,
    required this.desc,
    this.tips,
  });
}
