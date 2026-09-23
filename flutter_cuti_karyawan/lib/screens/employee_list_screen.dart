import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'add_employee_screen.dart';

class EmployeeListScreen extends StatefulWidget {
  const EmployeeListScreen({super.key});

  @override
  State<EmployeeListScreen> createState() => _EmployeeListScreenState();
}

class _EmployeeListScreenState extends State<EmployeeListScreen> {
  final TextEditingController _searchController = TextEditingController();
  List<UserModel> _employees = [];
  List<dynamic> _departments = [];
  bool _isLoading = true;
  String? _errorMessage;

  int? _selectedDeptId;
  String _selectedRole = '';

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    // Load departments for filter
    final optRes = await ApiService.getEmployeeOptions();
    if (mounted && optRes.success && optRes.data != null) {
      setState(() {
        _departments = optRes.data!['departments'] ?? [];
      });
    }
    _loadEmployees();
  }

  Future<void> _loadEmployees() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getEmployeesList(
      deptId: _selectedDeptId,
      role: _selectedRole,
      search: _searchController.text.trim(),
    );

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _employees = res.data!;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  Color _getRoleColor(String role) {
    switch (role.toLowerCase()) {
      case 'superadmin':
        return Colors.purple;
      case 'hrd':
      case 'admin':
        return const Color(0xFF007AFF);
      case 'manager':
        return const Color(0xFFFF9500);
      case 'supervisor':
      case 'leader':
        return const Color(0xFF34C759);
      case 'staff':
        return const Color(0xFF5856D6);
      default:
        return const Color(0xFF64748B);
    }
  }

  void _showEmployeeDetailModal(UserModel emp) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    showModalBottomSheet(
      context: context,
      isScrollable: true,
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
            Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: primaryAccent.withOpacity(0.15),
                  child: Text(
                    emp.namaLengkap.isNotEmpty ? emp.namaLengkap[0].toUpperCase() : '?',
                    style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: primaryAccent),
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        emp.namaLengkap,
                        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${emp.nik} • ${emp.namaDept}',
                        style: TextStyle(fontSize: 13, color: textSub),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: _getRoleColor(emp.role).withOpacity(0.12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          emp.namaJabatan,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: _getRoleColor(emp.role),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            Divider(height: 32, color: borderCol),
            _buildDetailRow('Email', emp.email, textHead, textSub, icon: Icons.email_outlined),
            _buildDetailRow('No. HP', emp.noHp ?? '-', textHead, textSub, icon: Icons.phone_outlined),
            _buildDetailRow('Tanggal Masuk', emp.tanggalMasuk ?? '-', textHead, textSub, icon: Icons.calendar_today_outlined),
            _buildDetailRow('Masa Kerja', emp.lamaBekerja, textHead, textSub, icon: Icons.work_history_outlined),
            _buildDetailRow('Sisa Kuota Cuti', '${emp.sisaCuti} dari ${emp.kuotaCuti} Hari', textHead, textSub, icon: Icons.pie_chart_outline),
            _buildDetailRow('Cuti Terpakai', '${emp.cutiTerpakai} Hari', textHead, textSub, icon: Icons.check_circle_outline),
            if (emp.alamat != null && emp.alamat!.isNotEmpty)
              _buildDetailRow('Alamat', emp.alamat!, textHead, textSub, icon: Icons.location_on_outlined),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryAccent,
                  foregroundColor: isDark ? const Color(0xFF0F172A) : Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Tutup', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value, Color textHead, Color textSub, {IconData? icon}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 16, color: textSub),
            const SizedBox(width: 8),
          ],
          SizedBox(
            width: 120,
            child: Text(label, style: TextStyle(fontSize: 13, color: textSub)),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: textHead),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final canAdd = user?.isAdmin == true;

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        title: Text('Daftar Karyawan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
        actions: [
          if (canAdd)
            IconButton(
              icon: const Icon(CupertinoIcons.person_add_solid, size: 22),
              tooltip: 'Tambah Karyawan Baru',
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const AddEmployeeScreen()),
                ).then((_) => _loadEmployees());
              },
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          // Search Box
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari nama, NIK, email, departemen...',
                prefixIcon: const Icon(Icons.search, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          _loadEmployees();
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onSubmitted: (_) => _loadEmployees(),
            ),
          ),

          // Department Filter Chips
          if (_departments.isNotEmpty)
            SizedBox(
              height: 40,
              child: ListView(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 16),
                children: [
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      label: const Text('Semua Dept'),
                      selected: _selectedDeptId == null,
                      onSelected: (val) {
                        setState(() => _selectedDeptId = null);
                        _loadEmployees();
                      },
                      selectedColor: primaryAccent.withOpacity(0.2),
                      checkmarkColor: primaryAccent,
                    ),
                  ),
                  ..._departments.map((d) {
                    final isSel = _selectedDeptId == d['id'];
                    return Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: FilterChip(
                        label: Text(d['nama_dept']?.toString() ?? ''),
                        selected: isSel,
                        onSelected: (val) {
                          setState(() => _selectedDeptId = val ? d['id'] as int? : null);
                          _loadEmployees();
                        },
                        selectedColor: primaryAccent.withOpacity(0.2),
                        checkmarkColor: primaryAccent,
                      ),
                    );
                  }),
                ],
              ),
            ),
          const SizedBox(height: 8),

          // Employees List
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadEmployees,
              color: primaryAccent,
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _errorMessage != null
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.error_outline, size: 48, color: AppTheme.statusRejected),
                              const SizedBox(height: 12),
                              Text(_errorMessage!, style: TextStyle(color: textSub)),
                              const SizedBox(height: 12),
                              ElevatedButton(onPressed: _loadEmployees, child: const Text('Coba Lagi')),
                            ],
                          ),
                        )
                      : _employees.isEmpty
                          ? Center(
                              child: SingleChildScrollView(
                                physics: const AlwaysScrollableScrollPhysics(),
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(CupertinoIcons.person_3, size: 54, color: textSub.withOpacity(0.5)),
                                    const SizedBox(height: 12),
                                    Text('Tidak Ada Data Karyawan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: textHead)),
                                    const SizedBox(height: 4),
                                    Text('Tidak ditemukan karyawan dengan filter yang dipilih.', style: TextStyle(fontSize: 13, color: textSub)),
                                  ],
                                ),
                              ),
                            )
                          : ListView.separated(
                              padding: const EdgeInsets.all(16),
                              itemCount: _employees.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 10),
                              itemBuilder: (ctx, idx) {
                                final emp = _employees[idx];
                                final roleCol = _getRoleColor(emp.role);

                                return InkWell(
                                  onTap: () => _showEmployeeDetailModal(emp),
                                  borderRadius: BorderRadius.circular(16),
                                  child: Container(
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      color: cardBg,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: borderCol),
                                    ),
                                    child: Row(
                                      children: [
                                        CircleAvatar(
                                          radius: 24,
                                          backgroundColor: roleCol.withOpacity(0.12),
                                          child: Text(
                                            emp.namaLengkap.isNotEmpty ? emp.namaLengkap[0].toUpperCase() : '?',
                                            style: TextStyle(fontWeight: FontWeight.bold, color: roleCol, fontSize: 18),
                                          ),
                                        ),
                                        const SizedBox(width: 14),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Row(
                                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                                children: [
                                                  Expanded(
                                                    child: Text(
                                                      emp.namaLengkap,
                                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5, color: textHead),
                                                      maxLines: 1,
                                                      overflow: TextOverflow.ellipsis,
                                                    ),
                                                  ),
                                                  Container(
                                                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                                                    decoration: BoxDecoration(
                                                      color: roleCol.withOpacity(0.12),
                                                      borderRadius: BorderRadius.circular(6),
                                                    ),
                                                    child: Text(
                                                      emp.namaJabatan,
                                                      style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: roleCol),
                                                    ),
                                                  ),
                                                ],
                                              ),
                                              const SizedBox(height: 3),
                                              Text(
                                                '${emp.nik} • Dept: ${emp.namaDept}',
                                                style: TextStyle(fontSize: 12, color: textSub),
                                              ),
                                              const SizedBox(height: 6),
                                              Row(
                                                children: [
                                                  Icon(Icons.pie_chart_outline, size: 13, color: textSub),
                                                  const SizedBox(width: 4),
                                                  Text(
                                                    'Sisa Cuti: ${emp.sisaCuti} Hari',
                                                    style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: emp.sisaCuti > 0 ? const Color(0xFF34C759) : const Color(0xFFFF3B30)),
                                                  ),
                                                  if (emp.noHp != null && emp.noHp!.isNotEmpty) ...[
                                                    const SizedBox(width: 12),
                                                    Icon(Icons.phone_outlined, size: 13, color: textSub),
                                                    const SizedBox(width: 4),
                                                    Text(emp.noHp!, style: TextStyle(fontSize: 11.5, color: textSub)),
                                                  ],
                                                ],
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
            ),
          ),
        ],
      ),
      floatingActionButton: canAdd
          ? FloatingActionButton.extended(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const AddEmployeeScreen()),
                ).then((_) => _loadEmployees());
              },
              backgroundColor: primaryAccent,
              foregroundColor: isDark ? const Color(0xFF0F172A) : Colors.white,
              icon: const Icon(CupertinoIcons.add),
              label: const Text('Tambah Karyawan', style: TextStyle(fontWeight: FontWeight.bold)),
            )
          : null,
    );
  }
}
