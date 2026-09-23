import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:url_launcher/url_launcher.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/auth_service.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  Future<void> _openGuideDoc(BuildContext context, String role) async {
    final guideUrl = '${ApiConfig.baseUrl}/docs/panduan.php?role=$role';
    final uri = Uri.parse(guideUrl);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (context.mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Membuka browser ke: $guideUrl')),
          );
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membuka tautan panduan: $e')),
        );
      }
    }
  }

  void _showGuideSelectionModal(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => Container(
        decoration: BoxDecoration(
          color: cardBg,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
          border: Border.all(color: borderCol),
        ),
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: borderCol,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'Pusat Buku Panduan & Tutorial (PDF)',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: textHead),
            ),
            const SizedBox(height: 4),
            Text(
              'Pilih buku panduan operasional sesuai peran jabatan:',
              style: TextStyle(fontSize: 12.5, color: textSub),
            ),
            const SizedBox(height: 18),

            _buildGuideTile(
              ctx,
              icon: CupertinoIcons.person_fill,
              color: const Color(0xFF0284C7),
              title: '1. Panduan Operator & Staff',
              desc: 'Alur pengajuan cuti, syarat lampiran surat dokter, dan lacak status.',
              role: 'operator',
              textHead: textHead,
              textSub: textSub,
              borderCol: borderCol,
              isDark: isDark,
            ),
            const SizedBox(height: 10),

            _buildGuideTile(
              ctx,
              icon: CupertinoIcons.person_2_fill,
              color: const Color(0xFF16A34A),
              title: '2. Panduan Leader & Supervisor',
              desc: 'Review tahap 1 tim departemen, tolak/setujui, dan pengajuan pribadi.',
              role: 'leader',
              textHead: textHead,
              textSub: textSub,
              borderCol: borderCol,
              isDark: isDark,
            ),
            const SizedBox(height: 10),

            _buildGuideTile(
              ctx,
              icon: CupertinoIcons.briefcase_fill,
              color: const Color(0xFFD97706),
              title: '3. Panduan Plant Manager',
              desc: 'Persetujuan tahap 2 lintas 15 departemen & monitoring produksi.',
              role: 'manager',
              textHead: textHead,
              textSub: textSub,
              borderCol: borderCol,
              isDark: isDark,
            ),
            const SizedBox(height: 10),

            _buildGuideTile(
              ctx,
              icon: CupertinoIcons.shield_lefthalf_fill,
              color: const Color(0xFF7C3AED),
              title: '4. Panduan HRD & Super Admin',
              desc: 'Persetujuan final tahap 3, pemotongan kuota, dan kelola karyawan.',
              role: 'hrd',
              textHead: textHead,
              textSub: textSub,
              borderCol: borderCol,
              isDark: isDark,
            ),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }

  Widget _buildGuideTile(
    BuildContext context, {
    required IconData icon,
    required Color color,
    required String title,
    required String desc,
    required String role,
    required Color textHead,
    required Color textSub,
    required Color borderCol,
    required bool isDark,
  }) {
    return InkWell(
      onTap: () {
        Navigator.pop(context);
        _openGuideDoc(context, role);
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: borderCol),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: color.withOpacity(0.15),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: textHead)),
                  const SizedBox(height: 2),
                  Text(desc, style: TextStyle(fontSize: 11.5, color: textSub), maxLines: 2, overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Icon(CupertinoIcons.arrow_down_doc_fill, size: 18, color: color),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : const Color(0xFF0F172A);
    final textSub = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

    String defaultRole = 'operator';
    if (user?.isAdmin == true) {
      defaultRole = 'hrd';
    } else if (user?.isManager == true) {
      defaultRole = 'manager';
    } else if (user?.isSupervisor == true) {
      defaultRole = 'leader';
    }

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
              Text(
                'Kembali',
                style: TextStyle(
                  color: Color(0xFF007AFF),
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
        leadingWidth: 95,
        title: Text(
          'Tentang Aplikasi',
          style: TextStyle(
            color: textHead,
            fontWeight: FontWeight.bold,
            fontSize: 17,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 550),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // 1. App Identity Card
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0284C7).withOpacity(0.12),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(CupertinoIcons.calendar_today, size: 44, color: Color(0xFF0284C7)),
                      ),
                      const SizedBox(height: 14),
                      Text(
                        'E-Cuti PT. Nakakin Indonesia',
                        style: TextStyle(
                          fontSize: 19,
                          fontWeight: FontWeight.bold,
                          color: textHead,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Versi 2.0.0 (Release Multiplatform)',
                        style: TextStyle(fontSize: 12.5, color: textSub),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          'Sistem Persetujuan Cuti Hierarki 3-Tier',
                          style: TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white70 : const Color(0xFF475569),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // 2. Download 4 User Manuals Banner
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF0284C7), Color(0xFF0369A1)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF0284C7).withOpacity(0.25),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Row(
                        children: [
                          Icon(CupertinoIcons.doc_text_fill, color: Colors.white, size: 26),
                          SizedBox(width: 10),
                          Text(
                            'Buku Panduan & Tutorial (PDF)',
                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Tersedia 4 buku panduan terpisah untuk Operator, Leader/Spv, Plant Manager, dan HRD/Admin.',
                        style: TextStyle(color: Colors.white70, fontSize: 12.5),
                      ),
                      const SizedBox(height: 14),
                      Row(
                        children: [
                          Expanded(
                            child: ElevatedButton.icon(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.white,
                                foregroundColor: const Color(0xFF0284C7),
                                elevation: 0,
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: () => _openGuideDoc(context, defaultRole),
                              icon: const Icon(CupertinoIcons.arrow_down_doc_fill, size: 18),
                              label: Text('Panduan Saya (${user?.role.toUpperCase() ?? "PDF"})', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            ),
                          ),
                          const SizedBox(width: 8),
                          ElevatedButton(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.white.withOpacity(0.2),
                              foregroundColor: Colors.white,
                              elevation: 0,
                              padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            onPressed: () => _showGuideSelectionModal(context),
                            child: const Text('Semua (4 PDF)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // 3. Quick How-To Steps
                Padding(
                  padding: const EdgeInsets.only(left: 6, bottom: 8),
                  child: Text(
                    'PANDUAN SINGKAT OPERASIONAL',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    children: [
                      _buildStepItem(
                        number: '1',
                        title: 'Operator & Staff (3 Tahap)',
                        desc: 'Pengajuan disetujui Leader/Spv ➔ Plant Manager ➔ HRD Final. Sisa kuota terpotong setelah HRD menyetujui.',
                        isDark: isDark,
                      ),
                      const Divider(height: 20),
                      _buildStepItem(
                        number: '2',
                        title: 'Leader & Supervisor (2 Tahap)',
                        desc: 'Bypass Spv, langsung disetujui Plant Manager ➔ HRD Final. Leader memproses cuti operator timnya.',
                        isDark: isDark,
                      ),
                      const Divider(height: 20),
                      _buildStepItem(
                        number: '3',
                        title: 'Plant Manager (15 Departemen)',
                        desc: 'Persetujuan tahap 2 mencakup seluruh 15 departemen pabrik sebelum diteruskan ke HRD.',
                        isDark: isDark,
                      ),
                      const Divider(height: 20),
                      _buildStepItem(
                        number: '4',
                        title: 'HRD & Super Admin (Final & Kuota)',
                        desc: 'Persetujuan akhir, monitoring seluruh pengajuan aktif, dan manajemen data pegawai.',
                        isDark: isDark,
                        isLast: true,
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

  Widget _buildStepItem({
    required String number,
    required String title,
    required String desc,
    required bool isDark,
    bool isLast = false,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        CircleAvatar(
          radius: 13,
          backgroundColor: const Color(0xFF007AFF),
          child: Text(
            number,
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                desc,
                style: TextStyle(
                  fontSize: 12,
                  color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
