import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/auth_service.dart';

class ProfileDetailScreen extends StatelessWidget {
  const ProfileDetailScreen({super.key});

  Widget _buildIosGroup(List<Widget> items) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
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
    bool showDivider = true,
  }) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
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
              Expanded(
                child: Text(
                  label,
                  style: const TextStyle(fontSize: 14, color: AppTheme.textSecondary),
                ),
              ),
              Text(
                value,
                style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
              ),
            ],
          ),
        ),
        if (showDivider)
          const Divider(height: 1, indent: 52, color: Color(0xFFF1F5F9)),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;

    return Scaffold(
      backgroundColor: const Color(0xFFF2F2F7), // iOS Grouped Background
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(CupertinoIcons.back, color: AppTheme.primary),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text('Data Profil Karyawan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        centerTitle: true,
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
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${user?.nik ?? ''} • ${user?.namaJabatan ?? ''}',
                        style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 24),

                // Group 1: Kepegawaian & Masa Kerja
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('DATA KEPEGAWAIAN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                _buildIosGroup([
                  _buildIosRow(
                    icon: CupertinoIcons.timer,
                    iconColor: Colors.purple,
                    label: 'Lama Bekerja',
                    value: user?.lamaBekerja ?? '-',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.calendar,
                    iconColor: Colors.blue,
                    label: 'Tanggal Masuk',
                    value: user?.tanggalMasuk ?? '-',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.building_2_fill,
                    iconColor: Colors.teal,
                    label: 'Departemen',
                    value: user?.namaDept ?? '-',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.briefcase,
                    iconColor: Colors.indigo,
                    label: 'Jabatan',
                    value: user?.namaJabatan ?? '-',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.tag_fill,
                    iconColor: Colors.orange,
                    label: 'Role Hak Akses',
                    value: (user?.role ?? 'operator').toUpperCase(),
                    showDivider: false,
                  ),
                ]),
                const SizedBox(height: 20),

                // Group 2: Hak Kuota Cuti
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('HAK & KUOTA CUTI TAHUNAN', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                _buildIosGroup([
                  _buildIosRow(
                    icon: CupertinoIcons.chart_pie_fill,
                    iconColor: AppTheme.primary,
                    label: 'Sisa Kuota Cuti',
                    value: '${user?.sisaCuti ?? 0} Hari',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.arrow_right_circle_fill,
                    iconColor: Colors.deepOrange,
                    label: 'Cuti Terpakai',
                    value: '${user?.cutiTerpakai ?? 0} Hari',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.bag_fill,
                    iconColor: Colors.green,
                    label: 'Total Hak Cuti',
                    value: '${user?.kuotaCuti ?? 12} Hari',
                    showDivider: false,
                  ),
                ]),
                const SizedBox(height: 20),

                // Group 3: Kontak & Biodata
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('KONTAK & BIODATA', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                _buildIosGroup([
                  _buildIosRow(
                    icon: CupertinoIcons.mail_solid,
                    iconColor: Colors.blueAccent,
                    label: 'Email',
                    value: user?.email ?? '-',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.phone_fill,
                    iconColor: Colors.green,
                    label: 'Nomor HP',
                    value: user?.noHp ?? '-',
                  ),
                  _buildIosRow(
                    icon: CupertinoIcons.location_solid,
                    iconColor: Colors.redAccent,
                    label: 'Alamat',
                    value: user?.alamat ?? '-',
                    showDivider: false,
                  ),
                ]),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
