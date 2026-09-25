import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';

class PrivilegeInfoScreen extends StatelessWidget {
  const PrivilegeInfoScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final user = AuthService.currentUser;

    final role = user?.role.toLowerCase() ?? 'operator';
    final level = user?.levelHierarki ?? 1;

    // Privilege definitions for display
    final List<Map<String, dynamic>> privileges = [
      {
        'title': 'Pengajuan Cuti Pribadi',
        'desc': 'Mengajukan cuti mandiri, upload surat sakit/lampiran & pantau sisa kuota',
        'icon': CupertinoIcons.calendar_badge_plus,
        'active': true,
      },
      {
        'title': 'Papan Informasi & Kalender Bersama',
        'desc': 'Melihat jadwal cuti rekan kerja di departemen dan statistik cuti pabrik',
        'icon': CupertinoIcons.calendar,
        'active': true,
      },
      {
        'title': 'Approval Tier 1 (Leader / Supervisor)',
        'desc': 'Verifikasi & tinjauan cuti operator/staff di departemen yang sama',
        'icon': CupertinoIcons.checkmark_seal_fill,
        'active': role == 'leader' || role == 'supervisor' || role == 'superadmin' || role == 'admin' || level >= 3 && level <= 4,
      },
      {
        'title': 'Approval Tier 2 (Plant / Operational Manager)',
        'desc': 'Persetujuan operasional tingkat manajerial lintas seluruh 15 departemen',
        'icon': CupertinoIcons.briefcase_fill,
        'active': role == 'manager' || role == 'superadmin' || role == 'admin' || level == 6,
      },
      {
        'title': 'Approval Tier 3 / Final (HRD)',
        'desc': 'Persetujuan resmi akhir dan eksekusi pemotongan kuota cuti tahunan karyawan',
        'icon': CupertinoIcons.person_crop_circle_badge_checkmark,
        'active': role == 'hrd' || role == 'superadmin' || role == 'admin' || level >= 7,
      },
      {
        'title': 'Kelola Master Data Karyawan',
        'desc': 'Tambah, ubah, reset password, dan kelola akun karyawan',
        'icon': CupertinoIcons.person_2_fill,
        'active': role == 'hrd' || role == 'superadmin' || role == 'admin' || level >= 7,
      },
      {
        'title': 'Penyesuaian & Alokasi Kuota Cuti',
        'desc': 'Penyesuaian saldo kuota cuti individual maupun alokasi kuota massal',
        'icon': CupertinoIcons.slider_horizontal_3,
        'active': role == 'hrd' || role == 'superadmin' || role == 'admin' || level >= 7,
      },
      {
        'title': 'Rekap Laporan & Cetak Surat Cuti',
        'desc': 'Melihat rekapitulasi kehadiran departemen dan cetak surat resmi format PDF',
        'icon': CupertinoIcons.doc_text_fill,
        'active': role == 'leader' || role == 'supervisor' || role == 'manager' || role == 'hrd' || role == 'superadmin' || role == 'admin' || level >= 3,
      },
      {
        'title': 'Pengaturan Sistem & Kop Surat',
        'desc': 'Ubah logo perusahaan, kop surat, nomor registrasi, dan matriks hak akses',
        'icon': CupertinoIcons.gear_alt_fill,
        'active': role == 'hrd' || role == 'superadmin' || role == 'admin' || level >= 7,
      },
    ];

    Color roleColor;
    String roleBadge;
    if (level >= 8) {
      roleColor = const Color(0xFFE11D48);
      roleBadge = 'Level 8 - Super Admin';
    } else if (level == 7) {
      roleColor = const Color(0xFF059669);
      roleBadge = 'Level 7 - HRD & Admin';
    } else if (level == 6) {
      roleColor = const Color(0xFF7C3AED);
      roleBadge = 'Level 6 - Plant Manager';
    } else if (level == 4) {
      roleColor = const Color(0xFF2563EB);
      roleBadge = 'Level 4 - Supervisor (Spv)';
    } else if (level == 3) {
      roleColor = const Color(0xFF0284C7);
      roleBadge = 'Level 3 - Leader Departemen';
    } else if (level == 2) {
      roleColor = const Color(0xFF475569);
      roleBadge = 'Level 2 - Staff';
    } else {
      roleColor = const Color(0xFF64748B);
      roleBadge = 'Level 1 - Operator Produksi';
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Hak Akses & Privilege'),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Header Card
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: isDark
                    ? [const Color(0xFF1E293B), const Color(0xFF0F172A)]
                    : [const Color(0xFF0F172A), const Color(0xFF1E293B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.15),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                )
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: roleColor.withOpacity(0.2),
                        border: Border.all(color: roleColor.withOpacity(0.4)),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        roleBadge,
                        style: TextStyle(
                          color: roleColor,
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    const Icon(CupertinoIcons.shield_lefthalf_fill, color: Colors.amber, size: 24),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  user?.namaLengkap ?? 'Pengguna',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '${user?.namaJabatan ?? "-"} • ${user?.namaDept ?? "-"}',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.7),
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Section Title
          Padding(
            padding: const EdgeInsets.only(left: 4, bottom: 8),
            child: Text(
              'DAFTAR HAK AKSES SISTEM',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.8,
                color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
              ),
            ),
          ),

          // Privileges List
          ...privileges.map((item) {
            final active = item['active'] as bool;
            return Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E293B) : Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: active
                      ? (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0))
                      : (isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9)),
                ),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: active
                          ? (isDark ? const Color(0xFF0F766E).withOpacity(0.3) : const Color(0xFFD1FAE5))
                          : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      item['icon'] as IconData,
                      size: 20,
                      color: active
                          ? const Color(0xFF059669)
                          : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Flexible(
                              child: Text(
                                item['title'] as String,
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: active
                                      ? (isDark ? Colors.white : AppTheme.textPrimary)
                                      : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
                                ),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: active
                                    ? const Color(0xFF059669).withOpacity(0.12)
                                    : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                active ? 'Aktif' : 'Tidak Aktif',
                                style: TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold,
                                  color: active
                                      ? const Color(0xFF059669)
                                      : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          item['desc'] as String,
                          style: TextStyle(
                            fontSize: 12,
                            color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                            height: 1.3,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }
}
