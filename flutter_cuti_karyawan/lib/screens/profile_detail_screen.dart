import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/auth_service.dart';

class ProfileDetailScreen extends StatelessWidget {
  const ProfileDetailScreen({super.key});

  Widget _buildIosGroup(List<Widget> items, {required bool isDark}) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: items,
      ),
    );
  }

  Widget _buildIosRow({
    required IconData icon,
    required Color iconColor,
    required String label,
    required String value,
    required bool isDark,
    bool showDivider = true,
  }) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: iconColor,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Icon(icon, size: 18, color: Colors.white),
              ),
              const SizedBox(width: 14),
              SizedBox(
                width: 110,
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 14,
                    color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  value,
                  textAlign: TextAlign.right,
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white : AppTheme.textPrimary,
                  ),
                ),
              ),
            ],
          ),
        ),
        if (showDivider)
          Divider(height: 1, indent: 52, color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textMuted;

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
          'Data Profil Karyawan',
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
            constraints: const BoxConstraints(maxWidth: 500),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Header Card
                Center(
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 40,
                        backgroundColor: AppTheme.primary,
                        child: Text(
                          (user?.namaLengkap.isNotEmpty == true)
                              ? user!.namaLengkap.substring(0, 1).toUpperCase()
                              : 'U',
                          style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                      ),
                      const SizedBox(height: 12),
                      Text(
                        user?.namaLengkap ?? '-',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${user?.nik ?? ''} • ${user?.namaJabatan ?? ''}',
                        style: TextStyle(fontSize: 13, color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Group 1: Kepegawaian & Masa Kerja
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('DATA KEPEGAWAIAN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                _buildIosGroup([
                  _buildIosRow(
                    icon: CupertinoIcons.timer,
                    iconColor: Colors.purple,
                    label: 'Lama Bekerja',
                    value: user?.lamaBekerja ?? '-',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.calendar,
                    iconColor: Colors.blue,
                    label: 'Tanggal Masuk',
                    value: user?.tanggalMasuk ?? '-',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.building_2_fill,
                    iconColor: Colors.teal,
                    label: 'Departemen',
                    value: user?.namaDept ?? '-',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.briefcase,
                    iconColor: Colors.indigo,
                    label: 'Jabatan',
                    value: user?.namaJabatan ?? '-',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.tag_fill,
                    iconColor: Colors.orange,
                    label: 'Role Hak Akses',
                    value: (user?.role ?? 'operator').toUpperCase(),
                    isDark: isDark,
                    showDivider: false,
                  ),
                ], isDark: isDark),
                const SizedBox(height: 20),

                // Group 2: Hak Kuota Cuti
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('HAK & KUOTA CUTI TAHUNAN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                _buildIosGroup([
                  _buildIosRow(
                    icon: CupertinoIcons.chart_pie_fill,
                    iconColor: AppTheme.primary,
                    label: 'Sisa Kuota Cuti',
                    value: '${user?.sisaCuti ?? 0} Hari',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.arrow_right_circle_fill,
                    iconColor: Colors.deepOrange,
                    label: 'Cuti Terpakai',
                    value: '${user?.cutiTerpakai ?? 0} Hari',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.bag_fill,
                    iconColor: Colors.green,
                    label: 'Total Hak Cuti',
                    value: '${user?.kuotaCuti ?? 12} Hari',
                    isDark: isDark,
                    showDivider: false,
                  ),
                ], isDark: isDark),
                const SizedBox(height: 20),

                // Group 3: Kontak & Biodata
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('KONTAK & BIODATA', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                _buildIosGroup([
                  _buildIosRow(
                    icon: CupertinoIcons.mail_solid,
                    iconColor: Colors.blueAccent,
                    label: 'Email',
                    value: user?.email ?? '-',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.phone_fill,
                    iconColor: Colors.green,
                    label: 'Nomor HP',
                    value: user?.noHp ?? '-',
                    isDark: isDark,
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.location_solid,
                    iconColor: Colors.redAccent,
                    label: 'Alamat',
                    value: user?.alamat ?? '-',
                    isDark: isDark,
                    showDivider: false,
                  ),
                ], isDark: isDark),
                const SizedBox(height: 30),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
