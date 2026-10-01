import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

class ProfileDetailScreen extends StatefulWidget {
  const ProfileDetailScreen({super.key});

  @override
  State<ProfileDetailScreen> createState() => _ProfileDetailScreenState();
}

class _ProfileDetailScreenState extends State<ProfileDetailScreen> {
  bool _isUpdating = false;

  void _openEditProfileModal() {
    final user = AuthService.currentUser;
    final nameController = TextEditingController(text: user?.namaLengkap ?? '');
    final phoneController = TextEditingController(text: user?.noHp ?? '');
    final emailController = TextEditingController(text: user?.email ?? '');
    final addressController = TextEditingController(text: user?.alamat ?? '');
    final formKey = GlobalKey<FormState>();

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final fieldBg = isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: EdgeInsets.only(
                top: 20,
                left: 20,
                right: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 24,
              ),
              decoration: BoxDecoration(
                color: cardBg,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
                border: Border.all(color: borderCol),
              ),
              child: SingleChildScrollView(
                child: Form(
                  key: formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Drag Handle
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

                      // Header
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFF38BDF8).withOpacity(0.15),
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Icon(CupertinoIcons.pencil_ellipsis_rectangle, color: Color(0xFF0284C7), size: 22),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Edit Data Pribadi',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                                ),
                                Text(
                                  'Ubah nama, no WA, email & alamat',
                                  style: TextStyle(fontSize: 12, color: textSub),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      // Notice Banner HRD
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF0F172A) : const Color(0xFFEFF6FF),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFBFDBFE)),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(CupertinoIcons.lock_shield_fill, size: 18, color: Color(0xFF2563EB)),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                'Data kepegawaian (Departemen, Jabatan, Tanggal Masuk, Kuota Cuti) dikelola langsung oleh HRD.',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF1E40AF),
                                  height: 1.4,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 18),

                      // 1. Nama Lengkap
                      Text('Nama Lengkap', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textHead)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: nameController,
                        style: TextStyle(fontSize: 14, color: textHead, fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          prefixIcon: const Icon(CupertinoIcons.person_fill, size: 18),
                          hintText: 'Nama lengkap Anda',
                          filled: true,
                          fillColor: fieldBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama lengkap wajib diisi' : null,
                      ),
                      const SizedBox(height: 14),

                      // 2. No. WhatsApp / HP
                      Text('Nomor WhatsApp / HP', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textHead)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: phoneController,
                        keyboardType: TextInputType.phone,
                        style: TextStyle(fontSize: 14, color: textHead, fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          prefixIcon: const Icon(CupertinoIcons.phone_fill, size: 18),
                          hintText: 'Contoh: 08123456789',
                          filled: true,
                          fillColor: fieldBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                        ),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Nomor WhatsApp wajib diisi' : null,
                      ),
                      const SizedBox(height: 14),

                      // 3. Email
                      Text('Alamat Email', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textHead)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: emailController,
                        keyboardType: TextInputType.emailAddress,
                        style: TextStyle(fontSize: 14, color: textHead, fontWeight: FontWeight.w600),
                        decoration: InputDecoration(
                          prefixIcon: const Icon(CupertinoIcons.mail_solid, size: 18),
                          hintText: 'email@nakakin.co.id',
                          filled: true,
                          fillColor: fieldBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                        ),
                        validator: (v) {
                          if (v == null || v.trim().isEmpty) return 'Email wajib diisi';
                          if (!v.contains('@')) return 'Format email tidak valid';
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),

                      // 4. Alamat
                      Text('Alamat Domisili', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textHead)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: addressController,
                        maxLines: 2,
                        style: TextStyle(fontSize: 14, color: textHead, fontWeight: FontWeight.w500),
                        decoration: InputDecoration(
                          prefixIcon: const Icon(CupertinoIcons.location_solid, size: 18),
                          hintText: 'Alamat tempat tinggal saat ini',
                          filled: true,
                          fillColor: fieldBg,
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
                        ),
                      ),
                      const SizedBox(height: 22),

                      // Submit Button
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF0284C7),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          elevation: 0,
                        ),
                        onPressed: _isUpdating
                            ? null
                            : () async {
                                if (!formKey.currentState!.validate()) return;
                                setModalState(() => _isUpdating = true);
                                setState(() => _isUpdating = true);

                                final res = await ApiService.updateProfile(
                                  namaLengkap: nameController.text.trim(),
                                  email: emailController.text.trim(),
                                  noHp: phoneController.text.trim(),
                                  alamat: addressController.text.trim(),
                                );

                                setModalState(() => _isUpdating = false);
                                setState(() => _isUpdating = false);

                                if (mounted) {
                                  Navigator.pop(ctx);
                                  if (res.success) {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Row(
                                          children: [
                                            const Icon(Icons.check_circle_rounded, color: Colors.white),
                                            const SizedBox(width: 10),
                                            Expanded(child: Text(res.message)),
                                          ],
                                        ),
                                        backgroundColor: const Color(0xFF059669),
                                        behavior: SnackBarBehavior.floating,
                                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                      ),
                                    );
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(res.message),
                                        backgroundColor: const Color(0xFFDC2626),
                                        behavior: SnackBarBehavior.floating,
                                      ),
                                    );
                                  }
                                }
                              },
                        child: _isUpdating
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(CupertinoIcons.checkmark_alt, size: 18),
                                  SizedBox(width: 8),
                                  Text('Simpan Perubahan', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
                                ],
                              ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildIosGroup(List<Widget> items, {required bool isDark}) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(14),
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
                    fontSize: 13.5,
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
                    fontSize: 13.5,
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
        actions: [
          IconButton(
            icon: const Icon(CupertinoIcons.pencil_ellipsis_rectangle, color: Color(0xFF007AFF)),
            tooltip: 'Edit Profil',
            onPressed: _openEditProfileModal,
          ),
          const SizedBox(width: 4),
        ],
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

                // Group 1: Kepegawaian & Masa Kerja (Locked - HRD Managed)
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('DATA KEPEGAWAIAN', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: textSub)),
                      Row(
                        children: [
                          Icon(CupertinoIcons.lock_fill, size: 12, color: textSub),
                          const SizedBox(width: 4),
                          Text('Resmi HRD', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: textSub)),
                        ],
                      ),
                    ],
                  ),
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

                // Group 2: Hak Kuota Cuti (Locked - HRD Managed)
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('HAK & KUOTA CUTI TAHUNAN', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: textSub)),
                      Row(
                        children: [
                          Icon(CupertinoIcons.lock_fill, size: 12, color: textSub),
                          const SizedBox(width: 4),
                          Text('Resmi HRD', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: textSub)),
                        ],
                      ),
                    ],
                  ),
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

                // Group 3: Kontak & Biodata (Editable)
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text('KONTAK & BIODATA', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: textSub)),
                      InkWell(
                        onTap: _openEditProfileModal,
                        borderRadius: BorderRadius.circular(4),
                        child: const Padding(
                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Text('Edit', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF007AFF))),
                        ),
                      ),
                    ],
                  ),
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
                    label: 'Nomor HP / WA',
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
                const SizedBox(height: 20),

                // Full Width Button to Edit Profile
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF007AFF),
                      side: const BorderSide(color: Color(0xFF007AFF)),
                      padding: const EdgeInsets.symmetric(vertical: 13),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    icon: const Icon(CupertinoIcons.pencil, size: 16),
                    label: const Text('Ubah Data Diri & Kontak', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5)),
                    onPressed: _openEditProfileModal,
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
}
