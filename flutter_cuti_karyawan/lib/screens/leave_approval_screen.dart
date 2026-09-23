import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'leave_detail_screen.dart';

class LeaveApprovalScreen extends StatefulWidget {
  const LeaveApprovalScreen({super.key});

  @override
  State<LeaveApprovalScreen> createState() => _LeaveApprovalScreenState();
}

class _LeaveApprovalScreenState extends State<LeaveApprovalScreen> {
  List<LeaveModel> _approvals = [];
  bool _isLoading = true;
  String? _errorMessage;
  String _currentFilter = 'pending'; // 'pending' or 'all'

  @override
  void initState() {
    super.initState();
    _loadApprovals();
  }

  bool _canApproveItem(LeaveModel leave, UserModel? user) {
    if (user == null || !leave.isPending) return false;
    if (user.id == leave.employeeId) return false;

    final step = leave.approvalStep.toLowerCase();
    if (step == 'pending_spv') {
      return user.isSupervisor && user.departemenId == leave.departemenId;
    } else if (step == 'pending_manager') {
      return user.isManager;
    } else if (step == 'pending_hrd') {
      return user.isAdmin;
    }
    return false;
  }

  Future<void> _loadApprovals() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getApprovalsList(status: _currentFilter);

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _approvals = res.data!;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  void _showApprovalDialog(LeaveModel leave, bool isApprove) {
    final notesController = TextEditingController();
    final formKey = GlobalKey<FormState>();
    bool isProcessing = false;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Row(
            children: [
              Icon(
                isApprove ? Icons.check_circle_outline_rounded : Icons.highlight_off_rounded,
                color: isApprove ? AppTheme.statusApproved : AppTheme.statusRejected,
              ),
              const SizedBox(width: 8),
              Text(
                isApprove ? 'Setujui Pengajuan' : 'Tolak Pengajuan',
                style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          content: Form(
            key: formKey,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Pemohon: ${leave.namaLengkap} (${leave.nik})',
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                ),
                Text(
                  'Durasi: ${leave.totalHari} hari (${leave.tanggalMulai} s/d ${leave.tanggalSelesai})',
                  style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                ),
                const SizedBox(height: 14),
                TextFormField(
                  controller: notesController,
                  maxLines: 3,
                  decoration: InputDecoration(
                    labelText: isApprove ? 'Catatan Tambahan (Opsional)' : 'Alasan Penolakan (Wajib) *',
                    hintText: isApprove ? 'Misal: Disetujui, pekerjaan didelegasikan...' : 'Tuliskan alasan penolakan...',
                  ),
                  validator: (v) {
                    if (!isApprove && (v == null || v.trim().isEmpty)) {
                      return 'Alasan penolakan wajib diisi';
                    }
                    return null;
                  },
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: isProcessing ? null : () => Navigator.pop(ctx),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: isApprove ? AppTheme.statusApproved : AppTheme.statusRejected,
              ),
              onPressed: isProcessing
                  ? null
                  : () async {
                      if (!formKey.currentState!.validate()) return;
                      setDialogState(() {
                        isProcessing = true;
                      });

                      final res = await ApiService.processApprovalAction(
                        leaveId: leave.id,
                        action: isApprove ? 'approve' : 'reject',
                        notes: notesController.text.trim(),
                        rejectionReason: notesController.text.trim(),
                      );

                      if (!mounted) return;

                      Navigator.pop(ctx);
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          backgroundColor: res.success ? AppTheme.statusApproved : AppTheme.statusRejected,
                          content: Text(res.message),
                        ),
                      );

                      if (res.success) {
                        _loadApprovals();
                      }
                    },
              child: isProcessing
                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                  : Text(isApprove ? 'Ya, Setujui' : 'Tolak Cuti'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      appBar: AppBar(
        title: Text('Persetujuan Cuti Anggota', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.filter_list_rounded),
            tooltip: 'Filter Status',
            onSelected: (val) {
              setState(() {
                _currentFilter = val;
              });
              _loadApprovals();
            },
            itemBuilder: (ctx) => const [
              PopupMenuItem(value: 'pending', child: Text('Hanya Menunggu (Pending)')),
              PopupMenuItem(value: 'all', child: Text('Semua Riwayat Pengajuan')),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadApprovals,
            tooltip: 'Segarkan',
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadApprovals,
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
                        ElevatedButton(onPressed: _loadApprovals, child: const Text('Coba Lagi')),
                      ],
                    ),
                  )
                : _approvals.isEmpty
                    ? Center(
                        child: SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(20),
                                decoration: BoxDecoration(
                                  color: AppTheme.statusApproved.withOpacity(0.12),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.done_all_rounded, size: 48, color: AppTheme.statusApproved),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'Semua Bersih!',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                'Tidak ada pengajuan cuti yang memerlukan persetujuan.',
                                style: TextStyle(fontSize: 13, color: textSub),
                              ),
                            ],
                          ),
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _approvals.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 14),
                        itemBuilder: (ctx, idx) {
                          final leave = _approvals[idx];
                          return InkWell(
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => LeaveDetailScreen(leaveId: leave.id),
                                ),
                              ).then((_) => _loadApprovals());
                            },
                            borderRadius: BorderRadius.circular(16),
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: cardBg,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: borderCol),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Top Header
                                  Row(
                                    children: [
                                      CircleAvatar(
                                        radius: 20,
                                        backgroundColor: primaryAccent.withOpacity(0.12),
                                        child: Text(
                                          (leave.namaLengkap?.isNotEmpty == true)
                                              ? leave.namaLengkap!.substring(0, 1).toUpperCase()
                                              : 'K',
                                          style: TextStyle(fontWeight: FontWeight.bold, color: primaryAccent),
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              leave.namaLengkap ?? '-',
                                              style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: textHead),
                                            ),
                                            Text(
                                              '${leave.nik ?? ''} • ${leave.namaDept ?? ''}',
                                              style: TextStyle(fontSize: 12, color: textSub),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: primaryAccent.withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          leave.kodeCuti ?? 'CUTI',
                                          style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: primaryAccent),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 10),

                                  // Stage Chip
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFFF9500).withOpacity(0.12),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        const Icon(CupertinoIcons.layers_alt_fill, size: 14, color: Color(0xFFFF9500)),
                                        const SizedBox(width: 6),
                                        Text(
                                          leave.stepLabel,
                                          style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: Color(0xFFFF9500)),
                                        ),
                                      ],
                                    ),
                                  ),

                                  Divider(height: 20, color: borderCol),

                                  // Details
                                  Row(
                                    children: [
                                      const Icon(Icons.date_range, size: 16, color: AppTheme.textMuted),
                                      const SizedBox(width: 6),
                                      Expanded(
                                        child: Text(
                                          '${leave.tanggalMulai} s/d ${leave.tanggalSelesai}',
                                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: textHead),
                                        ),
                                      ),
                                      Text(
                                        '${leave.totalHari} Hari',
                                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: primaryAccent),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: isDark ? const Color(0xFF0F172A) : AppTheme.surfaceVariant,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      'Alasan: ${leave.alasan}',
                                      style: TextStyle(fontSize: 12, color: textSub),
                                    ),
                                  ),

                                  // Actions (if pending)
                                  if (leave.isPending) ...[
                                    const SizedBox(height: 14),
                                    Builder(
                                      builder: (context) {
                                        final currentUser = AuthService.currentUser;
                                        final canApproveThis = _canApproveItem(leave, currentUser);

                                        return Column(
                                          crossAxisAlignment: CrossAxisAlignment.stretch,
                                          children: [
                                            Row(
                                              children: [
                                                Expanded(
                                                  child: OutlinedButton.icon(
                                                    style: OutlinedButton.styleFrom(
                                                      foregroundColor: canApproveThis ? AppTheme.statusRejected : const Color(0xFF94A3B8),
                                                      side: BorderSide(
                                                        color: canApproveThis ? AppTheme.statusRejected : const Color(0xFFCBD5E1),
                                                      ),
                                                      padding: const EdgeInsets.symmetric(vertical: 10),
                                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                    ),
                                                    onPressed: canApproveThis ? () => _showApprovalDialog(leave, false) : null,
                                                    icon: Icon(
                                                      Icons.close_rounded,
                                                      size: 16,
                                                      color: canApproveThis ? AppTheme.statusRejected : const Color(0xFF94A3B8),
                                                    ),
                                                    label: Text(
                                                      'Tolak',
                                                      style: TextStyle(
                                                        fontSize: 13,
                                                        fontWeight: FontWeight.bold,
                                                        color: canApproveThis ? AppTheme.statusRejected : const Color(0xFF94A3B8),
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                                const SizedBox(width: 10),
                                                Expanded(
                                                  child: ElevatedButton.icon(
                                                    style: ElevatedButton.styleFrom(
                                                      backgroundColor: canApproveThis
                                                          ? AppTheme.statusApproved
                                                          : (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                                                      elevation: canApproveThis ? 1 : 0,
                                                      padding: const EdgeInsets.symmetric(vertical: 10),
                                                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                                    ),
                                                    onPressed: canApproveThis ? () => _showApprovalDialog(leave, true) : null,
                                                    icon: Icon(
                                                      Icons.check_rounded,
                                                      size: 16,
                                                      color: canApproveThis ? Colors.white : const Color(0xFF94A3B8),
                                                    ),
                                                    label: Text(
                                                      'Setujui',
                                                      style: TextStyle(
                                                        fontSize: 13,
                                                        fontWeight: FontWeight.bold,
                                                        color: canApproveThis ? Colors.white : const Color(0xFF94A3B8),
                                                      ),
                                                    ),
                                                  ),
                                                ),
                                              ],
                                            ),
                                            if (!canApproveThis) ...[
                                              const SizedBox(height: 6),
                                              Center(
                                                child: Text(
                                                  leave.approvalStep == 'pending_spv'
                                                      ? 'Menunggu persetujuan Leader/Spv lebih dulu'
                                                      : (leave.approvalStep == 'pending_manager'
                                                          ? 'Menunggu persetujuan Plant Manager lebih dulu'
                                                          : 'Menunggu persetujuan akhir HRD'),
                                                  style: const TextStyle(
                                                    fontSize: 11,
                                                    color: Color(0xFFEF4444),
                                                    fontWeight: FontWeight.w500,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ],
                                        );
                                      },
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        },
                      ),
      ),
    );
  }
}
