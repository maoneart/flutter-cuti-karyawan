import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:url_launcher/url_launcher.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/auth_service.dart';

class UserGuideScreen extends StatelessWidget {
  const UserGuideScreen({super.key});

  static Future<void> downloadRolePdf(BuildContext context, String roleKey) async {
    final pdfUrl = '${ApiConfig.baseUrl}/docs/panduan.php?role=$roleKey';
    final uri = Uri.parse(pdfUrl);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Membuka browser ke: $pdfUrl')),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mengunduh dokumen panduan: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    // Detect user role categorization
    final userRole = (user?.role ?? 'operator').toLowerCase();
    final userLevel = user?.levelHierarki ?? 1;

    String roleCategory = 'operator';
    String roleTitle = 'Operator & Staff';
    String roleSubtitle = 'Panduan Pengajuan Cuti, Lampiran Surat Dokter, & Lacak Kuota';
    Color roleColor = const Color(0xFF0284C7); // Sky blue
    IconData roleIcon = CupertinoIcons.person_fill;

    if (userRole == 'hrd' || userRole == 'admin' || userRole == 'superadmin' || userLevel >= 7) {
      roleCategory = 'hrd';
      roleTitle = 'HRD & Super Admin';
      roleSubtitle = 'Panduan Persetujuan Akhir Tier 3, Pemotongan Kuota, & Kelola Pegawai';
      roleColor = const Color(0xFF7C3AED); // Purple
      roleIcon = CupertinoIcons.shield_lefthalf_fill;
    } else if (userRole == 'manager' || userLevel == 6) {
      roleCategory = 'manager';
      roleTitle = 'Plant Manager';
      roleSubtitle = 'Panduan Persetujuan Tier 2 Cuti Lintas 15 Departemen & Monitoring Produksi';
      roleColor = const Color(0xFFD97706); // Amber
      roleIcon = CupertinoIcons.briefcase_fill;
    } else if (userRole == 'leader' || userRole == 'supervisor' || userLevel >= 3) {
      roleCategory = 'leader';
      roleTitle = 'Leader & Supervisor';
      roleSubtitle = 'Panduan Persetujuan Tier 1 Tim Departemen & Pengajuan Cuti Pribadi';
      roleColor = const Color(0xFF16A34A); // Emerald
      roleIcon = CupertinoIcons.person_2_fill;
    }

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        title: Text('Panduan Aplikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
        centerTitle: true,
        actions: [
          IconButton(
            icon: const Icon(CupertinoIcons.arrow_down_doc_fill, size: 20),
            tooltip: 'Unduh Dokumen PDF',
            onPressed: () => downloadRolePdf(context, roleCategory),
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. Role-Specific Hero Header
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [roleColor, roleColor.withOpacity(0.8)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: roleColor.withOpacity(0.3),
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
                                    'PANDUAN RESMI PERAN: ${user?.namaJabatan.toUpperCase() ?? roleTitle.toUpperCase()}',
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
                                  'Halo, ${user?.namaLengkap ?? "Pengguna"} 👋',
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
                        style: const TextStyle(color: Colors.white, fontSize: 13, height: 1.4),
                      ),
                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: roleColor,
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: () => downloadRolePdf(context, roleCategory),
                          icon: const Icon(CupertinoIcons.arrow_down_circle_fill, size: 18),
                          label: Text(
                            'Unduh PDF Panduan ($roleTitle)',
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // 2. Role-Specific Step-by-Step Sections
                Padding(
                  padding: const EdgeInsets.only(left: 4, bottom: 10),
                  child: Text(
                    'LANGKAH PENGOPERASIAN APLIKASI',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: textSub,
                      letterSpacing: 0.5,
                    ),
                  ),
                ),

                if (roleCategory == 'operator') ..._buildOperatorSteps(cardBg, borderCol, textHead, textSub, isDark),
                if (roleCategory == 'leader') ..._buildLeaderSteps(cardBg, borderCol, textHead, textSub, isDark),
                if (roleCategory == 'manager') ..._buildManagerSteps(cardBg, borderCol, textHead, textSub, isDark),
                if (roleCategory == 'hrd') ..._buildHrdSteps(cardBg, borderCol, textHead, textSub, isDark),

                const SizedBox(height: 24),

                // 3. Footer Help Card
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
                              'Butuh Bantuan Lebih Lanjut?',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: textHead),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              'Hubungi tim HRD / GA PT. Nakakin Indonesia untuk kendala data akun atau kuota cuti.',
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
  // 1. OPERATOR & STAFF STEPS
  // ==========================================
  List<Widget> _buildOperatorSteps(Color cardBg, Color borderCol, Color textHead, Color textSub, bool isDark) {
    return [
      _buildStepCard(
        number: '1',
        title: 'Memeriksa Dashboard & Sisa Kuota Cuti',
        desc: 'Di halaman Dashboard, pantau sisa kuota cuti tahunan, total hari terpakai, dan masa kerja Anda. Sisa kuota akan berkurang secara otomatis hanya setelah cuti disetujui tuntas oleh HRD.',
        icon: CupertinoIcons.gauge,
        iconColor: const Color(0xFF0284C7),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '2',
        title: 'Mengajukan Permohonan Cuti Baru',
        desc: 'Klik tombol "+ Ajukan Cuti" di menu bawah atau Dashboard:\n• Pilih Jenis Cuti (Cuti Tahunan, Cuti Sakit, dll).\n• Tentukan Tanggal Mulai dan Tanggal Selesai.\n• Tulis Alasan yang jelas dan lengkap.\n• Jika Sakit: Wajib lampirkan Foto / Dokumen Surat Dokter.',
        icon: CupertinoIcons.calendar_badge_plus,
        iconColor: const Color(0xFF10B981),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '3',
        title: 'Memahami Alur Persetujuan 3-Tier',
        desc: 'Pengajuan Anda akan melewati 3 tahap berurutan:\n1. Tier 1: Leader / Supervisor Departemen Anda.\n2. Tier 2: Plant Manager (lintas pabrik).\n3. Tier 3: HRD (Persetujuan Final & potong kuota).\nJika salah satu tahap menolak, pengajuan langsung berstatus Ditolak.',
        icon: CupertinoIcons.arrow_branch,
        iconColor: const Color(0xFFF59E0B),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '4',
        title: 'Melihat Riwayat & Status Pengajuan',
        desc: 'Buka menu "Riwayat" untuk memantau status secara real-time. Anda dapat melihat tahapan mana yang sedang memproses pengajuan Anda melalui timeline visual bertingkat.',
        icon: CupertinoIcons.clock_fill,
        iconColor: const Color(0xFF6366F1),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '5',
        title: 'Papan Kehadiran Bersama (Papan Live)',
        desc: 'Gunakan fitur "Papan Live" di Dashboard untuk melihat jadwal cuti seluruh rekan kerja hari ini agar memudahkan koordinasi jadwal kerja tim.',
        icon: CupertinoIcons.tv,
        iconColor: const Color(0xFFEC4899),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
    ];
  }

  // ==========================================
  // 2. LEADER & SUPERVISOR STEPS
  // ==========================================
  List<Widget> _buildLeaderSteps(Color cardBg, Color borderCol, Color textHead, Color textSub, bool isDark) {
    return [
      _buildStepCard(
        number: '1',
        title: 'Dashboard & Notifikasi Pengajuan Tim',
        desc: 'Dashboard Anda menampilkan ringkasan pengajuan cuti yang masuk dari anggota tim dalam 1 departemen Anda. Badge lonceng merah akan menandai jumlah pengajuan yang menunggu tindakan.',
        icon: CupertinoIcons.bell_fill,
        iconColor: const Color(0xFF16A34A),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '2',
        title: 'Melakukan Review & Approval (Tier 1)',
        desc: 'Buka menu "Persetujuan" atau klik detail cuti tim:\n• Tombol Setujui dan Tolak hanya AKTIF pada pengajuan yang berstatus "Menunggu Review Spv".\n• Tekan Setujui untuk meneruskan ke Plant Manager (Tier 2).\n• Tekan Tolak jika tidak disetujui (proses akan berhenti permanen).',
        icon: CupertinoIcons.checkmark_seal_fill,
        iconColor: const Color(0xFF0284C7),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '3',
        title: 'Pengajuan Cuti Pribadi (Bypass Tier 1)',
        desc: 'Ketika Anda sebagai Leader / Supervisor mengajukan cuti pribadi, sistem secara otomatis melompati (bypass) tahap Tier 1 dan langsung dialihkan ke Plant Manager (Tier 2).',
        icon: CupertinoIcons.person_crop_circle_badge_checkmark,
        iconColor: const Color(0xFFF59E0B),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '4',
        title: 'Monitoring Jadwal Cuti Anggota Departemen',
        desc: 'Periksa menu Papan Live dan Riwayat Departemen untuk memastikan kecukupan tenaga kerja di line produksi Anda sebelum menyetujui pengajuan bawahan.',
        icon: CupertinoIcons.person_3_fill,
        iconColor: const Color(0xFF8B5CF6),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
    ];
  }

  // ==========================================
  // 3. PLANT MANAGER STEPS
  // ==========================================
  List<Widget> _buildManagerSteps(Color cardBg, Color borderCol, Color textHead, Color textSub, bool isDark) {
    return [
      _buildStepCard(
        number: '1',
        title: 'Dashboard Pabrik (Lintas 15 Departemen)',
        desc: 'Plant Manager memiliki wewenang memantau status kehadiran karyawan dan pengajuan cuti yang telah disetujui Leader dari seluruh 15 departemen di pabrik.',
        icon: CupertinoIcons.building_2_fill,
        iconColor: const Color(0xFFD97706),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '2',
        title: 'Approval Tahap 2 (Plant Manager)',
        desc: 'Buka menu "Persetujuan":\n• Pengajuan yang masuk adalah pengajuan yang telah disetujui Leader Departemen (status: pending_manager).\n• Tombol aksi hanya aktif pada giliran Anda.\n• Persetujuan Anda akan meneruskan berkas ke tahap akhir (HRD).',
        icon: CupertinoIcons.slider_horizontal_3,
        iconColor: const Color(0xFF0284C7),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '3',
        title: 'Pengawasan Produksi & Papan Kehadiran',
        desc: 'Gunakan Papan Live Kehadiran untuk mengevaluasi dampak pengajuan cuti terhadap kelancaran target operasional pabrik sebelum memberikan persetujuan.',
        icon: CupertinoIcons.chart_bar_alt_fill,
        iconColor: const Color(0xFF10B981),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '4',
        title: 'Pengajuan Cuti Pribadi Manager',
        desc: 'Pengajuan cuti pribadi Plant Manager otomatis melewati Tier 1 & Tier 2, dan langsung masuk ke meja HRD untuk persetujuan final.',
        icon: CupertinoIcons.briefcase_fill,
        iconColor: const Color(0xFF6366F1),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
    ];
  }

  // ==========================================
  // 4. HRD & SUPER ADMIN STEPS
  // ==========================================
  List<Widget> _buildHrdSteps(Color cardBg, Color borderCol, Color textHead, Color textSub, bool isDark) {
    return [
      _buildStepCard(
        number: '1',
        title: 'Persetujuan Final (Tier 3) & Potong Kuota',
        desc: 'HRD memproses verifikasi akhir (status: pending_hrd) setelah pengajuan disetujui Leader dan Plant Manager. Ketika HRD menekan "Setujui", sisa kuota cuti karyawan resmi terpotong di database.',
        icon: CupertinoIcons.checkmark_shield_fill,
        iconColor: const Color(0xFF7C3AED),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '2',
        title: 'Akses Menu List Karyawan',
        desc: 'Melalui tombol "List Karyawan" di Dashboard atau Pengaturan, HRD dapat melihat seluruh data pegawai, NIK, Departemen, nomor kontak, serta sisa dan kuota cuti yang dimiliki.',
        icon: CupertinoIcons.person_3_fill,
        iconColor: const Color(0xFF0284C7),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '3',
        title: 'Pendaftaran Karyawan Baru (+ Tambah Karyawan)',
        desc: 'Di dalam halaman List Karyawan, tekan tombol "+ Tambah Karyawan" untuk mendaftarkan akun baru dengan mengatur NIK, Nama, Departemen, Jabatan/Role, dan Kuota Cuti Awal.',
        icon: CupertinoIcons.person_badge_plus,
        iconColor: const Color(0xFF10B981),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
      const SizedBox(height: 12),
      _buildStepCard(
        number: '4',
        title: 'Pengaturan Sistem Web & Master Data',
        desc: 'Melalui panel Web Admin, HRD & Super Admin dapat mengatur Kop Surat resmi, Logo Perusahaan, Master Departemen, Master Jabatan, dan Master Jenis Cuti.',
        icon: CupertinoIcons.gear_alt_fill,
        iconColor: const Color(0xFFF59E0B),
        cardBg: cardBg,
        borderCol: borderCol,
        textHead: textHead,
        textSub: textSub,
      ),
    ];
  }

  // ==========================================
  // HELPER WIDGETS
  // ==========================================
  Widget _buildStepCard({
    required String number,
    required String title,
    required String desc,
    required IconData icon,
    required Color iconColor,
    required Color cardBg,
    required Color borderCol,
    required Color textHead,
    required Color textSub,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderCol),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.12),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: iconColor, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: iconColor.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        'LANGKAH $number',
                        style: TextStyle(
                          fontSize: 10.5,
                          fontWeight: FontWeight.bold,
                          color: iconColor,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.bold,
                    color: textHead,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  desc,
                  style: TextStyle(
                    fontSize: 12.5,
                    color: textSub,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
