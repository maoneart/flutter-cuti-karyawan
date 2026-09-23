import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/auth_service.dart';
import 'login_screen.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Future<void> _handleLogout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Keluar dari Akun?'),
        content: const Text('Apakah Anda yakin ingin keluar dari sistem?'),
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

  void _showServerSettingsDialog() {
    final serverController = TextEditingController(text: ApiConfig.baseUrl);
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.settings_ethernet, color: AppTheme.primary),
            SizedBox(width: 8),
            Text('Konfigurasi Server API', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: serverController,
              decoration: const InputDecoration(
                labelText: 'API Base URL',
                hintText: 'http://192.168.1.10/Cuti_Karyawan/api',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            onPressed: () async {
              final newUrl = serverController.text.trim();
              if (newUrl.isNotEmpty) {
                await AuthService.setCustomBaseUrl(newUrl);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Server diatur ke: $newUrl')),
                  );
                }
              }
              Navigator.pop(ctx);
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  Widget _buildProfileItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppTheme.primary.withOpacity(0.08),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: AppTheme.primary, size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                const SizedBox(height: 2),
                Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      appBar: AppBar(
        title: Text('Profil Pengguna', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 600),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Avatar & Name Card
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    children: [
                      CircleAvatar(
                        radius: 36,
                        backgroundColor: primaryAccent,
                        child: Text(
                          (user?.namaLengkap.isNotEmpty == true)
                              ? user!.namaLengkap.substring(0, 1).toUpperCase()
                              : 'U',
                          style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Colors.white),
                        ),
                      ),
                      const SizedBox(height: 14),
                      Text(
                        user?.namaLengkap ?? 'Karyawan',
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${user?.nik ?? ''} • ${user?.namaJabatan ?? ''}',
                        style: TextStyle(fontSize: 13, color: textSub),
                      ),
                      const SizedBox(height: 10),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(
                          color: primaryAccent.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Text(
                          (user?.role ?? 'operator').toUpperCase(),
                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: primaryAccent),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Detail Info Card
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Informasi Karyawan',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      Divider(height: 20, color: borderCol),
                      _buildProfileItem(icon: Icons.email_outlined, label: 'Email Perusahaan', value: user?.email ?? '-'),
                      _buildProfileItem(icon: Icons.apartment_outlined, label: 'Departemen', value: user?.namaDept ?? '-'),
                      _buildProfileItem(icon: Icons.work_outline, label: 'Jabatan', value: user?.namaJabatan ?? '-'),
                      _buildProfileItem(icon: Icons.phone_outlined, label: 'Nomor WhatsApp / HP', value: user?.noHp ?? '-'),
                      _buildProfileItem(icon: Icons.pie_chart_outline, label: 'Sisa Kuota Cuti', value: '${user?.sisaCuti ?? 0} Hari (Dari Kuota ${user?.kuotaCuti ?? 12} Hari)'),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Settings & Server Config
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Pengaturan Koneksi',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      Divider(height: 20, color: borderCol),
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: Icon(Icons.dns_rounded, color: primaryAccent),
                        title: Text('API Endpoint Base URL', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: textHead)),
                        subtitle: Text(ApiConfig.baseUrl, style: TextStyle(fontSize: 12, color: textSub)),
                        trailing: Icon(Icons.edit, size: 18, color: textSub),
                        onTap: _showServerSettingsDialog,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Logout Button
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppTheme.statusRejected,
                    side: const BorderSide(color: AppTheme.statusRejected, width: 1.5),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  onPressed: _handleLogout,
                  icon: const Icon(Icons.logout_rounded, size: 20),
                  label: const Text('Keluar dari Akun', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
