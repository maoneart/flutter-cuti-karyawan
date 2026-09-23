import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:url_launcher/url_launcher.dart';
import '../config/api_config.dart';
import '../config/app_theme.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'add_employee_screen.dart';
import 'edit_employee_screen.dart';

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

  Future<void> _resetPassword(UserModel emp) async {
    final newPassCtrl = TextEditingController(text: 'password123');
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.lock_reset_rounded, color: Color(0xFFFF9500)),
            SizedBox(width: 8),
            Text('Reset Password', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Reset password akun ${emp.namaLengkap} (${emp.nik}):', style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 12),
            TextField(
              controller: newPassCtrl,
              decoration: const InputDecoration(
                labelText: 'Password Baru',
                hintText: 'password123',
                prefixIcon: Icon(Icons.key_outlined, size: 20),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFFF9500),
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Reset Password'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final res = await ApiService.resetEmployeePassword(
        emp.id,
        newPassword: newPassCtrl.text.trim(),
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: res.success ? const Color(0xFF10B981) : AppTheme.statusRejected,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _deleteEmployee(UserModel emp) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: AppTheme.statusRejected),
            SizedBox(width: 8),
            Text('Hapus Karyawan', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Text('Apakah Anda yakin ingin menghapus data karyawan ${emp.namaLengkap} (${emp.nik})? Tindakan ini tidak dapat dibatalkan.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.statusRejected,
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Hapus'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final res = await ApiService.deleteEmployee(emp.id);
      if (!mounted) return;
      if (res.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res.message),
            backgroundColor: const Color(0xFF10B981),
            behavior: SnackBarBehavior.floating,
          ),
        );
        _loadEmployees();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res.message),
            backgroundColor: AppTheme.statusRejected,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _showEmployeeDetailModal(UserModel emp) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;
    final canManage = AuthService.currentUser?.isAdmin == true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
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

            // Action Buttons for HRD / Super Admin
            if (canManage) ...[
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: primaryAccent,
                        foregroundColor: isDark ? const Color(0xFF0F172A) : Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: const Icon(Icons.edit_outlined, size: 18),
                      label: const Text('Edit Data', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      onPressed: () {
                        Navigator.pop(ctx);
                        Navigator.push(
                          context,
                          MaterialPageRoute(builder: (_) => EditEmployeeScreen(employee: emp)),
                        ).then((val) {
                          if (val == true) _loadEmployees();
                        });
                      },
                    ),
                  ),
                  const SizedBox(width: 8),
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFFF9500)),
                      foregroundColor: const Color(0xFFFF9500),
                      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    icon: const Icon(Icons.lock_reset_rounded, size: 18),
                    label: const Text('Reset Pass', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12.5)),
                    onPressed: () {
                      Navigator.pop(ctx);
                      _resetPassword(emp);
                    },
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    style: IconButton.styleFrom(
                      foregroundColor: AppTheme.statusRejected,
                      backgroundColor: AppTheme.statusRejected.withOpacity(0.1),
                    ),
                    icon: const Icon(Icons.delete_outline, size: 20),
                    tooltip: 'Hapus Karyawan',
                    onPressed: () {
                      Navigator.pop(ctx);
                      _deleteEmployee(emp);
                    },
                  ),
                ],
              ),
              const SizedBox(height: 10),
            ],

            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                style: OutlinedButton.styleFrom(
                  side: BorderSide(color: borderCol),
                  foregroundColor: textSub,
                  padding: const EdgeInsets.symmetric(vertical: 12),
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

  Future<void> _downloadTemplate() async {
    final uri = Uri.parse(ApiConfig.employeeTemplate);
    try {
      final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (!launched && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Buka browser untuk unduh template: $uri'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal membuka URL template: $e'),
            backgroundColor: AppTheme.statusRejected,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  Future<void> _pickAndImportExcel() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['xlsx', 'csv', 'xls'],
        withData: true,
      );

      if (result == null || result.files.isEmpty) return;
      final file = result.files.first;

      if (!mounted) return;

      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const Center(
          child: Card(
            child: Padding(
              padding: EdgeInsets.all(20),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Memvalidasi data Excel...', style: TextStyle(fontWeight: FontWeight.w600)),
                ],
              ),
            ),
          ),
        ),
      );

      final res = await ApiService.previewEmployeeImport(
        fileBytes: file.bytes,
        filePath: file.path,
        fileName: file.name,
      );

      if (!mounted) return;
      Navigator.of(context, rootNavigator: true).pop(); // Close loading

      if (res.success && res.data != null) {
        _showImportPreviewModal(res.data!);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res.message.isNotEmpty ? res.message : 'Gagal memproses file'),
            backgroundColor: AppTheme.statusRejected,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Error memilih file: $e'),
            backgroundColor: AppTheme.statusRejected,
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    }
  }

  void _showImportPreviewModal(Map<String, dynamic> data) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    final summary = data['summary'] as Map<String, dynamic>? ?? {};
    final int total = (summary['total'] as num?)?.toInt() ?? 0;
    final int validCount = (summary['valid_count'] as num?)?.toInt() ?? 0;
    final int invalidCount = (summary['invalid_count'] as num?)?.toInt() ?? 0;

    final List<dynamic> validRows = data['valid_rows'] as List<dynamic>? ?? [];
    final List<dynamic> invalidRows = data['invalid_rows'] as List<dynamic>? ?? [];

    bool isSubmitting = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalCtx) => StatefulBuilder(
        builder: (ctx, setModalState) => Container(
          height: MediaQuery.of(context).size.height * 0.85,
          decoration: BoxDecoration(
            color: cardBg,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
            border: Border.all(color: borderCol),
          ),
          padding: const EdgeInsets.all(20),
          child: Column(
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
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFF10B981).withOpacity(0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.table_view_rounded, color: Color(0xFF10B981), size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Hasil Validasi Import Excel', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: textHead)),
                        Text('Pastikan data sudah sesuai sebelum disimpan', style: TextStyle(fontSize: 12, color: textSub)),
                      ],
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, size: 20),
                    onPressed: () => Navigator.pop(modalCtx),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Summary Stats Row
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: borderCol.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Text('$total', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textHead)),
                          Text('Total Baris', style: TextStyle(fontSize: 11, color: textSub)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withOpacity(0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Text('$validCount', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF10B981))),
                          const Text('Siap Diimport', style: TextStyle(fontSize: 11, color: Color(0xFF10B981), fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
                      decoration: BoxDecoration(
                        color: invalidCount > 0 ? const Color(0xFFEF4444).withOpacity(0.15) : borderCol.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        children: [
                          Text('$invalidCount', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: invalidCount > 0 ? const Color(0xFFEF4444) : textSub)),
                          Text('Tidak Valid', style: TextStyle(fontSize: 11, color: invalidCount > 0 ? const Color(0xFFEF4444) : textSub, fontWeight: FontWeight.w600)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Body Content (Tabs or list of errors & preview)
              Expanded(
                child: ListView(
                  children: [
                    if (invalidRows.isNotEmpty) ...[
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: const Color(0xFFEF4444).withOpacity(0.08),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: const Color(0xFFEF4444).withOpacity(0.3)),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                const Icon(Icons.warning_amber_rounded, color: Color(0xFFEF4444), size: 18),
                                const SizedBox(width: 8),
                                Text(
                                  'Ditemukan $invalidCount Baris Tidak Valid (Dilewati):',
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFFEF4444)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            ...invalidRows.map((inv) {
                              final errors = (inv['errors'] as List<dynamic>?)?.join(', ') ?? 'Data tidak valid';
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 6),
                                child: Text(
                                  '• Baris ${inv['row_number']}: [NIK: ${inv['nik'] ?? '-'}] - $errors',
                                  style: const TextStyle(fontSize: 12, color: Color(0xFFEF4444)),
                                ),
                              );
                            }),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                    ],

                    if (validRows.isNotEmpty) ...[
                      Text(
                        'Preview Karyawan Valid ($validCount):',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: textHead),
                      ),
                      const SizedBox(height: 8),
                      ...validRows.map((v) {
                        return Container(
                          margin: const EdgeInsets.only(bottom: 8),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: borderCol.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: borderCol),
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                radius: 18,
                                backgroundColor: primaryAccent.withOpacity(0.15),
                                child: Text(
                                  (v['nama_lengkap'] ?? '?')[0].toUpperCase(),
                                  style: TextStyle(fontWeight: FontWeight.bold, color: primaryAccent, fontSize: 14),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      v['nama_lengkap'] ?? '',
                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: textHead),
                                    ),
                                    Text(
                                      '${v['nik']} • ${v['nama_dept']} • ${v['nama_jabatan']}',
                                      style: TextStyle(fontSize: 11.5, color: textSub),
                                    ),
                                    Text(
                                      '${v['email']} | Kuota: ${v['kuota_cuti'] ?? 12} Hari',
                                      style: TextStyle(fontSize: 11, color: textSub),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        );
                      }),
                    ] else ...[
                      const Center(
                        child: Padding(
                          padding: EdgeInsets.symmetric(vertical: 30),
                          child: Text(
                            'Tidak ada baris valid yang dapat diimport.',
                            style: TextStyle(color: AppTheme.statusRejected, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 12),
              // Bottom Action Button
              if (validCount > 0)
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF10B981),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    icon: isSubmitting
                        ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.check_circle_outline, size: 20),
                    label: Text(
                      isSubmitting ? 'Menyimpan...' : 'Simpan $validCount Karyawan ke Database',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5),
                    ),
                    onPressed: isSubmitting
                        ? null
                        : () async {
                            setModalState(() => isSubmitting = true);
                            final commitRes = await ApiService.commitEmployeeImport(validRows);
                            setModalState(() => isSubmitting = false);

                            if (!mounted) return;
                            if (commitRes.success) {
                              Navigator.pop(modalCtx);
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(commitRes.message.isNotEmpty ? commitRes.message : 'Berhasil mengimpor $validCount karyawan!'),
                                  backgroundColor: const Color(0xFF10B981),
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                              _loadEmployees();
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(commitRes.message.isNotEmpty ? commitRes.message : 'Gagal menyimpan data import'),
                                  backgroundColor: AppTheme.statusRejected,
                                  behavior: SnackBarBehavior.floating,
                                ),
                              );
                            }
                          },
                  ),
                ),
            ],
          ),
        ),
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
          if (canAdd) ...[
            PopupMenuButton<String>(
              icon: const Icon(Icons.more_vert),
              tooltip: 'Opsi Karyawan',
              onSelected: (val) {
                if (val == 'add') {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const AddEmployeeScreen()),
                  ).then((_) => _loadEmployees());
                } else if (val == 'import') {
                  _pickAndImportExcel();
                } else if (val == 'template') {
                  _downloadTemplate();
                }
              },
              itemBuilder: (ctx) => [
                const PopupMenuItem(
                  value: 'add',
                  child: Row(
                    children: [
                      Icon(CupertinoIcons.person_add_solid, size: 18, color: Color(0xFF007AFF)),
                      SizedBox(width: 10),
                      Text('Tambah Manual'),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'import',
                  child: Row(
                    children: [
                      Icon(Icons.file_upload_outlined, size: 18, color: Color(0xFF10B981)),
                      SizedBox(width: 10),
                      Text('Import Data Excel'),
                    ],
                  ),
                ),
                const PopupMenuItem(
                  value: 'template',
                  child: Row(
                    children: [
                      Icon(Icons.download_rounded, size: 18, color: Color(0xFF6366F1)),
                      SizedBox(width: 10),
                      Text('Unduh Template Excel'),
                    ],
                  ),
                ),
              ],
            ),
          ],
          const SizedBox(width: 8),
        ],
      ),
      body: Column(
        children: [
          if (canAdd)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              color: isDark ? const Color(0xFF1A2234) : Colors.white,
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        side: const BorderSide(color: Color(0xFF10B981)),
                        foregroundColor: const Color(0xFF10B981),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: const Icon(Icons.file_upload_outlined, size: 16),
                      label: const Text('Import Excel', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold)),
                      onPressed: _pickAndImportExcel,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: OutlinedButton.icon(
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 8),
                        side: BorderSide(color: primaryAccent),
                        foregroundColor: primaryAccent,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                      icon: const Icon(Icons.download_rounded, size: 16),
                      label: const Text('Template', style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.bold)),
                      onPressed: _downloadTemplate,
                    ),
                  ),
                ],
              ),
            ),
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
