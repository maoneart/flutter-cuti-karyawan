import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/auth_service.dart';
import '../services/theme_service.dart';
import 'profile_detail_screen.dart';
import 'change_password_screen.dart';
import 'server_setting_screen.dart';
import 'add_employee_screen.dart';
import 'about_screen.dart';
import 'login_screen.dart';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  Future<void> _handleLogout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Keluar dari Akun?'),
        content: const Text('Apakah Anda yakin ingin keluar dari sistem E-Cuti?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.statusRejected),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Keluar'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    await AuthService.logout();

    if (!mounted) return;

    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  }

  Widget _buildIosGroup(List<Widget> children, {required bool isDark}) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: children,
      ),
    );
  }

  Widget _buildIosTile({
    required IconData icon,
    required Color iconColor,
    required String title,
    required bool isDark,
    String? subtitle,
    Widget? trailing,
    VoidCallback? onTap,
    bool showDivider = true,
  }) {
    return Column(
      children: [
        ListTile(
          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
          leading: Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: iconColor,
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, size: 20, color: Colors.white),
          ),
          title: Text(
            title,
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: isDark ? Colors.white : AppTheme.textPrimary,
            ),
          ),
          subtitle: subtitle != null
              ? Text(
                  subtitle,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                  ),
                )
              : null,
          trailing: trailing ?? Icon(
            CupertinoIcons.chevron_forward,
            size: 18,
            color: isDark ? const Color(0xFF64748B) : AppTheme.textMuted,
          ),
          onTap: onTap,
        ),
        if (showDivider)
          Divider(
            height: 1,
            indent: 56,
            color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
          ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textMuted;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        title: Text(
          'Pengaturan',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead),
        ),
        centerTitle: false,
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        elevation: 0.5,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 500),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. iOS Profile Header Tile (Tapping opens full profile)
                InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const ProfileDetailScreen()),
                    ).then((_) => setState(() {}));
                  },
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: borderCol),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 28,
                          backgroundColor: AppTheme.primary,
                          child: Text(
                            (user?.namaLengkap.isNotEmpty == true)
                                ? user!.namaLengkap.substring(0, 1).toUpperCase()
                                : 'U',
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white),
                          ),
                        ),
                        const SizedBox(width: 14),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                user?.namaLengkap ?? 'Karyawan',
                                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${user?.nik ?? ''} • ${user?.namaJabatan ?? ''}',
                                style: TextStyle(fontSize: 12, color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary),
                              ),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'Masa Kerja: ${user?.lamaBekerja ?? '-'}',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF0284C7)),
                                ),
                              ),
                            ],
                          ),
                        ),
                        Icon(
                          CupertinoIcons.chevron_forward,
                          size: 18,
                          color: isDark ? const Color(0xFF64748B) : AppTheme.textMuted,
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Group 1: Akun & Keamanan
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('KEAMANAN & TAMPILAN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                _buildIosGroup([
                  // Dark Mode Switch
                  _buildIosTile(
                    icon: isDark ? CupertinoIcons.moon_fill : CupertinoIcons.sun_max_fill,
                    iconColor: isDark ? const Color(0xFF6366F1) : const Color(0xFFF59E0B),
                    title: 'Mode Gelap (Dark Mode)',
                    subtitle: isDark ? 'Tema Gelap Aktif' : 'Tema Terang Aktif',
                    isDark: isDark,
                    trailing: CupertinoSwitch(
                      value: isDark,
                      activeColor: const Color(0xFF007AFF),
                      onChanged: (val) {
                        ThemeService.setDarkMode(val);
                      },
                    ),
                  ),
                  _buildIosTile(
                    icon: CupertinoIcons.lock_shield_fill,
                    iconColor: Colors.orange,
                    title: 'Ganti Password',
                    subtitle: 'Ubah kata sandi akun Anda',
                    isDark: isDark,
                    showDivider: false,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
                      );
                    },
                  ),
                ], isDark: isDark),
                const SizedBox(height: 20),

                // Group 2: HRD Admin (if admin)
                if (user?.role == 'admin' || (user?.levelHierarki ?? 0) >= 7) ...[
                  Padding(
                    padding: const EdgeInsets.only(left: 8, bottom: 8),
                    child: Text('ADMINISTRASI HRD', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                  ),
                  _buildIosGroup([
                    _buildIosTile(
                      icon: CupertinoIcons.person_badge_plus,
                      iconColor: const Color(0xFFE11D48),
                      title: 'Tambah Karyawan Baru',
                      subtitle: 'Daftarkan pegawai & buat akun login',
                      isDark: isDark,
                      showDivider: false,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AddEmployeeScreen()),
                        );
                      },
                    ),
                  ], isDark: isDark),
                  const SizedBox(height: 20),
                ],

                // Group 3: Koneksi Server
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('JARINGAN & SISTEM', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                _buildIosGroup([
                  _buildIosTile(
                    icon: CupertinoIcons.antenna_radiowaves_left_right,
                    iconColor: Colors.green,
                    title: 'Ganti Server API',
                    subtitle: ApiConfig.baseUrl,
                    isDark: isDark,
                    showDivider: false,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ServerSettingScreen()),
                      ).then((_) => setState(() {}));
                    },
                  ),
                ], isDark: isDark),
                const SizedBox(height: 20),

                // Group 4: Informasi & Panduan
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('BANTUAN & PANDUAN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                _buildIosGroup([
                  _buildIosTile(
                    icon: CupertinoIcons.info_circle_fill,
                    iconColor: const Color(0xFF0284C7),
                    title: 'Tentang Aplikasi & Panduan',
                    subtitle: 'Buku panduan PDF, alur approval, & info rilis',
                    isDark: isDark,
                    showDivider: false,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const AboutScreen()),
                      );
                    },
                  ),
                ], isDark: isDark),
                const SizedBox(height: 24),

                // Group 5: Logout Button
                InkWell(
                  onTap: _handleLogout,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    decoration: BoxDecoration(
                      color: cardBg,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: borderCol),
                    ),
                    child: const Center(
                      child: Text(
                        'Keluar dari Akun',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFFFF3B30),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
