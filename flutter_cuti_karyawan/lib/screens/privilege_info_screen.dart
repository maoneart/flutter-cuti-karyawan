import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

class PrivilegeInfoScreen extends StatefulWidget {
  final int initialTabIndex;
  const PrivilegeInfoScreen({super.key, this.initialTabIndex = 0});

  @override
  State<PrivilegeInfoScreen> createState() => _PrivilegeInfoScreenState();
}

class _PrivilegeInfoScreenState extends State<PrivilegeInfoScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  bool _canManage = false;
  bool _isLoading = true;
  String? _errorMessage;

  // Matrix data from API
  Map<String, dynamic> _matrix = {};
  Map<String, dynamic> _myPermissions = {};

  // Selected role for matrix configuration
  String _selectedRole = 'operator';

  // Toggle loading lock to prevent double taps
  final Set<String> _updatingKeys = {};

  final List<Map<String, String>> _rolesList = [
    {'key': 'operator', 'label': 'Operator', 'level': 'Level 1', 'desc': 'Karyawan operator lini produksi'},
    {'key': 'staff', 'label': 'Staff', 'level': 'Level 2', 'desc': 'Staff administrasi & operasional'},
    {'key': 'leader', 'label': 'Leader', 'level': 'Level 3', 'desc': 'Leader regu seksi departemen'},
    {'key': 'supervisor', 'label': 'Supervisor', 'level': 'Level 4', 'desc': 'Supervisor pengendali operasional'},
    {'key': 'manager', 'label': 'Manager', 'level': 'Level 6', 'desc': 'Plant Manager operasional pabrik'},
    {'key': 'hrd', 'label': 'HRD & Admin', 'level': 'Level 7', 'desc': 'Pengelola SDM & master data cuti'},
    {'key': 'superadmin', 'label': 'Super Admin', 'level': 'Level 8', 'desc': 'Akses penuh seluruh konfigurasi sistem'},
  ];

  bool get _isSuperUser {
    final user = AuthService.currentUser;
    if (user == null) return false;
    return user.isSuperAdmin;
  }

  @override
  void initState() {
    super.initState();
    _canManage = _isSuperUser;
    final tabIndex = (widget.initialTabIndex == 1 && _canManage) ? 1 : 0;
    _tabController = TabController(length: _canManage ? 2 : 1, initialIndex: tabIndex, vsync: this);
    _fetchPermissions();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchPermissions() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getPermissionsMatrix();

    if (!mounted) return;

    if (res.success && res.data != null) {
      final data = res.data!;
      final serverRole = data['current_user_role']?.toString();
      final matrixData = (data['matrix'] as Map<String, dynamic>?) ?? {};

      // Sync role dynamically if changed on webbase backend
      if (serverRole != null && AuthService.currentUser != null && AuthService.currentUser!.role.toLowerCase() != serverRole.toLowerCase()) {
        final updatedUser = AuthService.currentUser!.copyWith(
          role: serverRole,
          levelHierarki: int.tryParse(data['current_user_level']?.toString() ?? '') ?? AuthService.currentUser!.levelHierarki,
        );
        await AuthService.updateUser(updatedUser);
      }

      // Check if user is Super User (strictly exclude HRD)
      final bool serverIsSuperAdmin = (data['is_superadmin'] == true) && (serverRole?.toLowerCase() != 'hrd');
      final bool newCanManage = _isSuperUser && (serverRole?.toLowerCase() != 'hrd');

      setState(() {
        if (newCanManage != _canManage) {
          final oldController = _tabController;
          _canManage = newCanManage;
          _tabController = TabController(length: _canManage ? 2 : 1, vsync: this);
          oldController.dispose();
        }
        _isLoading = false;
        _myPermissions = (data['my_permissions'] as Map<String, dynamic>?) ?? {};
        _matrix = matrixData;
      });
    } else {
      setState(() {
        _isLoading = false;
        _errorMessage = res.message;
      });
    }
  }

  Future<void> _handleTogglePermission(String permissionKey, bool currentValue) async {
    if (_selectedRole == 'superadmin') {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Hak akses Super Admin terkunci permanen demi integritas sistem.'),
          backgroundColor: Colors.orange,
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    final newValue = !currentValue;
    final updateKey = '$_selectedRole:$permissionKey';

    if (_updatingKeys.contains(updateKey)) return;

    setState(() {
      _updatingKeys.add(updateKey);
      // Optimistic update
      if (_matrix.containsKey(permissionKey) && _matrix[permissionKey]['roles'] is Map) {
        _matrix[permissionKey]['roles'][_selectedRole] = newValue;
      }
    });

    final res = await ApiService.updatePermission(
      role: _selectedRole,
      permissionKey: permissionKey,
      isGranted: newValue,
    );

    if (!mounted) return;

    setState(() {
      _updatingKeys.remove(updateKey);
    });

    if (res.success) {
      final permName = _matrix[permissionKey]?['name'] ?? permissionKey;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              Icon(
                newValue ? Icons.check_circle_rounded : Icons.remove_circle_outline_rounded,
                color: Colors.white,
                size: 18,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  '$permName untuk ${_selectedRole.toUpperCase()} ${newValue ? "diaktifkan" : "dinonaktifkan"}.',
                  style: const TextStyle(fontSize: 12.5),
                ),
              ),
            ],
          ),
          backgroundColor: newValue ? const Color(0xFF059669) : const Color(0xFF475569),
          duration: const Duration(seconds: 2),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    } else {
      // Revert optimistic update
      setState(() {
        if (_matrix.containsKey(permissionKey) && _matrix[permissionKey]['roles'] is Map) {
          _matrix[permissionKey]['roles'][_selectedRole] = currentValue;
        }
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: Colors.red.shade700,
          duration: const Duration(seconds: 3),
        ),
      );
    }
  }

  IconData _getPermIcon(String key) {
    switch (key) {
      case 'leave_request':
        return CupertinoIcons.calendar_badge_plus;
      case 'view_public_board':
        return CupertinoIcons.calendar;
      case 'approval_tier1':
        return CupertinoIcons.checkmark_seal_fill;
      case 'approval_tier2':
        return CupertinoIcons.briefcase_fill;
      case 'approval_tier3':
        return CupertinoIcons.person_crop_circle_badge_checkmark;
      case 'manage_employees':
        return CupertinoIcons.person_2_fill;
      case 'manage_quotas':
        return CupertinoIcons.slider_horizontal_3;
      case 'view_reports':
        return CupertinoIcons.doc_text_fill;
      case 'manage_settings':
        return CupertinoIcons.gear_alt_fill;
      default:
        return CupertinoIcons.shield_fill;
    }
  }

  Color _getRoleColor(String role) {
    switch (role.toLowerCase()) {
      case 'superadmin':
        return const Color(0xFFE11D48); // Rose
      case 'hrd':
      case 'admin':
        return const Color(0xFF059669); // Emerald
      case 'manager':
        return const Color(0xFF7C3AED); // Purple
      case 'supervisor':
        return const Color(0xFF2563EB); // Blue
      case 'leader':
        return const Color(0xFF0284C7); // Cyan
      case 'staff':
        return const Color(0xFF475569); // Slate
      default:
        return const Color(0xFF64748B); // Operator
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final user = AuthService.currentUser;
    final canManage = _canManage;

    final bgCol = isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC);
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);

    return Scaffold(
      backgroundColor: bgCol,
      appBar: AppBar(
        title: const Text('Hak Akses & Privilege'),
        elevation: 0,
        actions: [
          IconButton(
            tooltip: 'Segarkan Data',
            icon: const Icon(CupertinoIcons.arrow_clockwise, size: 20),
            onPressed: _fetchPermissions,
          ),
        ],
        bottom: canManage
            ? TabBar(
                controller: _tabController,
                indicatorColor: const Color(0xFF6366F1),
                indicatorWeight: 3,
                labelColor: isDark ? Colors.white : const Color(0xFF4F46E5),
                unselectedLabelColor: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                labelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                tabs: const [
                  Tab(
                    icon: Icon(CupertinoIcons.person_crop_circle_fill_badge_checkmark, size: 18),
                    text: 'Wewenang Saya',
                  ),
                  Tab(
                    icon: Icon(CupertinoIcons.slider_horizontal_3, size: 18),
                    text: 'Matriks & Setting Role',
                  ),
                ],
              )
            : null,
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CupertinoActivityIndicator(radius: 14),
                  SizedBox(height: 12),
                  Text('Memuat hak akses & matriks role...', style: TextStyle(fontSize: 13, color: Colors.grey)),
                ],
              ),
            )
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 48),
                        const SizedBox(height: 16),
                        Text('Gagal Memuat Data', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead)),
                        const SizedBox(height: 8),
                        Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(fontSize: 13, color: Colors.grey)),
                        const SizedBox(height: 16),
                        ElevatedButton.icon(
                          onPressed: _fetchPermissions,
                          icon: const Icon(CupertinoIcons.arrow_clockwise, size: 16),
                          label: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              : canManage
                  ? TabBarView(
                      controller: _tabController,
                      children: [
                        _buildMyPermissionsTab(user, isDark, cardBg, textHead, borderCol),
                        _buildMatrixSettingTab(isDark, cardBg, textHead, borderCol),
                      ],
                    )
                  : _buildMyPermissionsTab(user, isDark, cardBg, textHead, borderCol),
    );
  }

  /// TAB 1: User's Own Permissions View
  Widget _buildMyPermissionsTab(UserModel? user, bool isDark, Color cardBg, Color textHead, Color borderCol) {
    final role = user?.role.toLowerCase() ?? 'operator';
    final roleColor = _getRoleColor(role);

    // Fallback static list if matrix not yet available
    final List<Map<String, dynamic>> defaultPrivs = [
      {'key': 'leave_request', 'name': 'Pengajuan Cuti Pribadi', 'desc': 'Mengajukan cuti mandiri, upload surat sakit/lampiran & cek sisa kuota', 'icon': CupertinoIcons.calendar_badge_plus},
      {'key': 'view_public_board', 'name': 'Papan Informasi & Kalender Bersama', 'desc': 'Melihat jadwal cuti rekan kerja di departemen dan statistik pabrik', 'icon': CupertinoIcons.calendar},
      {'key': 'approval_tier1', 'name': 'Approval Tier 1 (Leader / Spv)', 'desc': 'Verifikasi & tinjauan cuti operator/staff departemen yang sama', 'icon': CupertinoIcons.checkmark_seal_fill},
      {'key': 'approval_tier2', 'name': 'Approval Tier 2 (Plant Manager)', 'desc': 'Persetujuan operasional tingkat manajerial lintas seluruh departemen', 'icon': CupertinoIcons.briefcase_fill},
      {'key': 'approval_tier3', 'name': 'Approval Tier 3 / Final (HRD)', 'desc': 'Persetujuan akhir resmi dan pemotongan otomatis kuota cuti', 'icon': CupertinoIcons.person_crop_circle_badge_checkmark},
      {'key': 'manage_employees', 'name': 'Kelola Master Data Karyawan', 'desc': 'Tambah, edit, hapus, reset password, dan import data karyawan', 'icon': CupertinoIcons.person_2_fill},
      {'key': 'manage_quotas', 'name': 'Penyesuaian & Alokasi Kuota', 'desc': 'Penyesuaian sisa cuti individual maupun alokasi kuota massal', 'icon': CupertinoIcons.slider_horizontal_3},
      {'key': 'view_reports', 'name': 'Rekap Laporan & Cetak Cuti', 'desc': 'Filter analitik rekapitulasi kehadiran dan cetak dokumen resmi format PDF', 'icon': CupertinoIcons.doc_text_fill},
      {'key': 'manage_settings', 'name': 'Pengaturan Sistem & Privilege', 'desc': 'Ubah logo perusahaan, kop surat, nomor registrasi & matriks role', 'icon': CupertinoIcons.gear_alt_fill},
    ];

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Header Profile Card
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
                      border: Border.all(color: roleColor.withOpacity(0.5)),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      'Level ${user?.levelHierarki ?? 1} - ${user?.role.toUpperCase() ?? "OPERATOR"}',
                      style: TextStyle(color: roleColor, fontSize: 11, fontWeight: FontWeight.bold),
                    ),
                  ),
                  const Icon(CupertinoIcons.shield_fill, color: Colors.amber, size: 24),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                user?.namaLengkap ?? 'Pengguna',
                style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 4),
              Text(
                '${user?.namaJabatan ?? "-"} • ${user?.namaDept ?? "-"}',
                style: TextStyle(color: Colors.white.withOpacity(0.7), fontSize: 13),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),

        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 8),
          child: Text(
            'HAK AKSES AKTIF ANDA',
            style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, letterSpacing: 0.8, color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary),
          ),
        ),

        // List permissions
        ...defaultPrivs.map((item) {
          final key = item['key'] as String;
          final bool isGranted = _myPermissions.containsKey(key)
              ? (_myPermissions[key] == true || _myPermissions[key] == 1)
              : false;

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isGranted
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
                    color: isGranted
                        ? (isDark ? const Color(0xFF0F766E).withOpacity(0.3) : const Color(0xFFD1FAE5))
                        : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    _getPermIcon(key),
                    size: 20,
                    color: isGranted ? const Color(0xFF059669) : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
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
                              item['name'] as String,
                              style: TextStyle(
                                fontSize: 13.5,
                                fontWeight: FontWeight.bold,
                                color: isGranted ? textHead : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: isGranted ? const Color(0xFF059669).withOpacity(0.12) : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              isGranted ? 'Diberikan' : 'Terkunci',
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: isGranted ? const Color(0xFF059669) : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        item['desc'] as String,
                        style: TextStyle(fontSize: 12, color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary, height: 1.3),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }

  /// TAB 2: Interactive Matrix Role Setting
  Widget _buildMatrixSettingTab(bool isDark, Color cardBg, Color textHead, Color borderCol) {
    final currentRoleMeta = _rolesList.firstWhere(
      (r) => r['key'] == _selectedRole,
      orElse: () => _rolesList.first,
    );
    final roleColor = _getRoleColor(_selectedRole);

    // Group permissions by category
    final Map<String, List<MapEntry<String, dynamic>>> grouped = {};
    for (final entry in _matrix.entries) {
      final category = (entry.value['category'] as String?) ?? 'Lainnya';
      grouped.putIfAbsent(category, () => []).add(entry);
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        // Role Selector Horizontal Pills
        Padding(
          padding: const EdgeInsets.only(left: 4, bottom: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('PILIH ROLE UNTUK DIKONFIGURASI', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B))),
              if (_selectedRole == 'superadmin')
                const Text('🔒 Terkunci', style: TextStyle(fontSize: 11, color: Colors.orange, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
        SizedBox(
          height: 42,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: _rolesList.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (context, index) {
              final r = _rolesList[index];
              final isSelected = r['key'] == _selectedRole;
              final col = _getRoleColor(r['key']!);

              return ChoiceChip(
                selected: isSelected,
                label: Text(r['label']!),
                labelStyle: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.bold,
                  color: isSelected ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
                ),
                selectedColor: col,
                backgroundColor: isDark ? const Color(0xFF1E293B) : const Color(0xFFF1F5F9),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                  side: BorderSide(color: isSelected ? col : borderCol),
                ),
                onSelected: (val) {
                  if (val) {
                    setState(() {
                      _selectedRole = r['key']!;
                    });
                  }
                },
              );
            },
          ),
        ),
        const SizedBox(height: 14),

        // Selected Role Summary Banner
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: roleColor.withOpacity(0.08),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: roleColor.withOpacity(0.25)),
          ),
          child: Row(
            children: [
              CircleAvatar(
                radius: 18,
                backgroundColor: roleColor.withOpacity(0.2),
                child: Icon(CupertinoIcons.person_crop_circle_fill, color: roleColor, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${currentRoleMeta['label']} (${currentRoleMeta['level']})',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: roleColor),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      currentRoleMeta['desc']!,
                      style: TextStyle(fontSize: 11.5, color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 18),

        // Categories & Switches
        if (grouped.isEmpty)
          const Padding(
            padding: EdgeInsets.all(24),
            child: Center(
              child: Text('Data matriks belum tersedia.', style: TextStyle(color: Colors.grey)),
            ),
          )
        else
          ...grouped.entries.map((group) {
            final categoryName = group.key;
            final items = group.value;

            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.only(left: 4, top: 8, bottom: 8),
                  child: Row(
                    children: [
                      Container(
                        width: 4,
                        height: 14,
                        decoration: BoxDecoration(
                          color: const Color(0xFF6366F1),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Text(
                        categoryName.toUpperCase(),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.7,
                          color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  margin: const EdgeInsets.only(bottom: 14),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    children: items.asMap().entries.map((entry) {
                      final idx = entry.key;
                      final permKey = entry.value.key;
                      final permData = entry.value.value as Map<String, dynamic>;
                      final permName = permData['name'] as String? ?? permKey;
                      final permDesc = permData['description'] as String? ?? '';
                      final rolesMap = (permData['roles'] as Map<String, dynamic>?) ?? {};
                      final bool isGranted = rolesMap[_selectedRole] == true || rolesMap[_selectedRole] == 1;

                      final updateKey = '$_selectedRole:$permKey';
                      final isUpdating = _updatingKeys.contains(updateKey);
                      final isSuperAdmin = _selectedRole == 'superadmin';

                      return Column(
                        children: [
                          Padding(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: isGranted
                                        ? const Color(0xFF6366F1).withOpacity(0.12)
                                        : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Icon(
                                    _getPermIcon(permKey),
                                    size: 20,
                                    color: isGranted ? const Color(0xFF6366F1) : (isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8)),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        permName,
                                        style: TextStyle(
                                          fontSize: 13.5,
                                          fontWeight: FontWeight.w600,
                                          color: textHead,
                                        ),
                                      ),
                                      if (permDesc.isNotEmpty) ...[
                                        const SizedBox(height: 2),
                                        Text(
                                          permDesc,
                                          style: TextStyle(
                                            fontSize: 11.5,
                                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                            height: 1.25,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 10),
                                if (isUpdating)
                                  const SizedBox(
                                    width: 32,
                                    height: 32,
                                    child: Center(
                                      child: CupertinoActivityIndicator(radius: 9),
                                    ),
                                  )
                                else
                                  Transform.scale(
                                    scale: 0.85,
                                    child: CupertinoSwitch(
                                      value: isGranted,
                                      activeColor: const Color(0xFF4F46E5),
                                      onChanged: isSuperAdmin
                                          ? null
                                          : (val) => _handleTogglePermission(permKey, isGranted),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          if (idx < items.length - 1)
                            Divider(height: 1, indent: 56, endIndent: 14, color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                        ],
                      );
                    }).toList(),
                  ),
                ),
              ],
            );
          }),
        const SizedBox(height: 20),
      ],
    );
  }
}
