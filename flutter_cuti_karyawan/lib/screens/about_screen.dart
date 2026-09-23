import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:url_launcher/url_launcher.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';

class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  Future<void> _openGuideDoc(BuildContext context) async {
    final guideUrl = '${ApiConfig.baseUrl}/docs/panduan.php';
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

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : const Color(0xFF0F172A);
    final textSub = isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B);

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
                        'E-Cuti Karyawan',
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'Versi 1.0.0 (Production Release)',
                        style: TextStyle(fontSize: 13, color: textSub),
                      ),
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0284C7).withOpacity(0.08),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'PT. Nakakin Indonesia',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF0284C7)),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // 2. Download / View PDF Guide Button
                Container(
                  padding: const EdgeInsets.all(18),
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
                            'Buku Panduan Pengoperasian',
                            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      const Text(
                        'Pelajari alur pengajuan cuti, syarat lampiran, mekanisme approval 3-tingkat, dan notifikasi.',
                        style: TextStyle(color: Colors.white70, fontSize: 12.5),
                      ),
                      const SizedBox(height: 14),
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.white,
                            foregroundColor: const Color(0xFF0284C7),
                            elevation: 0,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          ),
                          onPressed: () => _openGuideDoc(context),
                          icon: const Icon(CupertinoIcons.arrow_down_doc_fill, size: 18),
                          label: const Text('Buka / Unduh Panduan (PDF)', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
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
                        title: 'Pengajuan Cuti & Lampiran',
                        desc: 'Pilih jenis cuti, tentukan tanggal, tulis alasan, dan sertakan foto surat dokter/lampiran jika wajib.',
                        isDark: isDark,
                      ),
                      const Divider(height: 20),
                      _buildStepItem(
                        number: '2',
                        title: 'Persetujuan 3 Tingkat (3-Tier)',
                        desc: 'Operator disetujui Leader/Spv ➔ Manager Dept ➔ HRD Final. Sisa kuota terpotong setelah HRD menyetujui.',
                        isDark: isDark,
                      ),
                      const Divider(height: 20),
                      _buildStepItem(
                        number: '3',
                        title: 'Lonceng Notifikasi & Papan Live',
                        desc: 'Cek lonceng di atas dashboard untuk pemberitahuan persetujuan dan lihat status tim pada Papan Live.',
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
                  fontSize: 14,
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
