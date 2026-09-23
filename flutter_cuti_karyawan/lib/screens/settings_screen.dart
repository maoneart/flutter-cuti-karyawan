import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/auth_service.dart';
import 'profile_detail_screen.dart';
import 'change_password_screen.dart';
import 'server_setting_screen.dart';
import 'add_employee_screen.dart';
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

  Widget _buildIosGroup(List<Widget> children) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
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
    String? subtitle,
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
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
          ),
          subtitle: subtitle != null
              ? Text(subtitle, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary))
              : null,
          trailing: const Icon(CupertinoIcons.chevron_forward, size: 18, color: AppTheme.textMuted),
          onTap: onTap,
        ),
        if (showDivider)
          const Divider(height: 1, indent: 56, color: Color(0xFFF1F5F9)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;

    return Scaffold(
      backgroundColor: const Color(0xFFF2F2F7), // iOS Grouped Background
      appBar: AppBar(
        title: const Text('Pengaturan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        centerTitle: false,
        backgroundColor: Colors.white,
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
                // 1. iOS Profile Header Tile
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
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 30,
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
                                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${user?.nik ?? ''} • ${user?.namaJabatan ?? ''}',
                                style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                              ),
                              const SizedBox(height: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withOpacity(0.08),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'Masa Kerja: ${user?.lamaBekerja ?? '-'}',
                                  style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: AppTheme.primary),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const Icon(CupertinoIcons.chevron_forward, size: 18, color: AppTheme.textMuted),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Group 1: Profil & Akun
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('AKUN & PROFIL', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                _buildIosGroup([
                  _buildIosTile(
                    icon: CupertinoIcons.person_crop_circle_fill,
                    iconColor: Colors.blue,
                    title: 'Data Profil Lengkap',
                    subtitle: 'Biodata, lama bekerja, & kuota cuti',
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ProfileDetailScreen()),
                      );
                    },
                  ),
                  _buildIosTile(
                    icon: CupertinoIcons.lock_shield_fill,
                    iconColor: Colors.orange,
                    title: 'Ganti Password',
                    subtitle: 'Ubah kata sandi akun Anda',
                    showDivider: false,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ChangePasswordScreen()),
                      );
                    },
                  ),
                ]),
                const SizedBox(height: 20),

                if (user?.role == 'admin' || (user?.levelHierarki ?? 0) >= 7) ...[
                  const Padding(
                    padding: EdgeInsets.only(left: 8, bottom: 8),
                    child: Text('ADMINISTRASI HRD', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                  ),
                  _buildIosGroup([
                    _buildIosTile(
                      icon: CupertinoIcons.person_badge_plus,
                      iconColor: const Color(0xFFE11D48),
                      title: 'Tambah Karyawan Baru',
                      subtitle: 'Daftarkan pegawai & buat akun login',
                      showDivider: false,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => const AddEmployeeScreen()),
                        );
                      },
                    ),
                  ]),
                  const SizedBox(height: 20),
                ],

                // Group 2: Koneksi Server
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('JARINGAN & SISTEM', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                _buildIosGroup([
                  _buildIosTile(
                    icon: CupertinoIcons.antenna_radiowaves_left_right,
                    iconColor: Colors.green,
                    title: 'Ganti Server API',
                    subtitle: ApiConfig.baseUrl,
                    showDivider: false,
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const ServerSettingScreen()),
                      ).then((_) => setState(() {}));
                    },
                  ),
                ]),
                const SizedBox(height: 20),

                // Group 3: Informasi Aplikasi
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('INFORMASI APLIKASI', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                _buildIosGroup([
                  const Padding(
                    padding: EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Aplikasi', style: TextStyle(fontSize: 14, color: AppTheme.textSecondary)),
                            Text('E-Cuti Mobile v1.0.0', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                          ],
                        ),
                        Divider(height: 20, color: Color(0xFFF1F5F9)),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Perusahaan', style: TextStyle(fontSize: 14, color: AppTheme.textSecondary)),
                            Text('PT. Nakakin Indonesia', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                          ],
                        ),
                      ],
                    ),
                  ),
                ]),
                const SizedBox(height: 24),

                // Group 4: Logout Button
                InkWell(
                  onTap: _handleLogout,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: const Center(
                      child: Text(
                        'Keluar dari Akun',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.statusRejected,
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 32),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
