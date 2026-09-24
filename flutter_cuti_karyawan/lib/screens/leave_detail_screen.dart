import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'leave_print_preview_screen.dart';
import '../services/leave_print_service.dart';

class LeaveDetailScreen extends StatefulWidget {
  final int leaveId;
  const LeaveDetailScreen({super.key, required this.leaveId});

  @override
  State<LeaveDetailScreen> createState() => _LeaveDetailScreenState();
}

class _LeaveDetailScreenState extends State<LeaveDetailScreen> {
  LeaveModel? _leave;
  bool _isLoading = true;
  bool _isCancelling = false;
  bool _isProcessingAction = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getLeaveDetail(widget.leaveId);

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _leave = res.data;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  bool _canUserApprove(LeaveModel leave, UserModel? user) {
    if (user == null || !leave.isPending) return false;
    if (user.id == leave.employeeId) return false; // Tidak bisa approve pengajuan diri sendiri

    final step = leave.approvalStep.toLowerCase();
    if (step == 'pending_spv') {
      // HANYA Leader / Supervisor departemen yang sama dengan pemohon
      final isDeptMatch = (user.departemenId == leave.departemenId) ||
                          (user.namaDept != null && leave.namaDept != null && user.namaDept!.trim().toLowerCase() == leave.namaDept!.trim().toLowerCase());
      return user.isSupervisor && isDeptMatch;
    } else if (step == 'pending_manager') {
      // HANYA Plant Manager (setelah disetujui Leader/Spv)
      return user.isManager;
    } else if (step == 'pending_hrd') {
      // HANYA HRD / Super Admin (setelah disetujui Plant Manager)
      return user.isAdmin;
    }
    return false;
  }

  Future<void> _handleApproveAction() async {
    final noteController = TextEditingController();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(CupertinoIcons.checkmark_seal_fill, color: Color(0xFF34C759), size: 24),
            SizedBox(width: 8),
            Text('Setujui Pengajuan', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Apakah Anda yakin ingin menyetujui pengajuan cuti ini?',
              style: TextStyle(fontSize: 13, color: isDark ? const Color(0xFFCBD5E1) : AppTheme.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: noteController,
              decoration: const InputDecoration(
                hintText: 'Catatan persetujuan (opsional)...',
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF34C759)),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Setujui', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isProcessingAction = true);

    final res = await ApiService.processApprovalAction(
      leaveId: widget.leaveId,
      action: 'approve',
      notes: noteController.text.trim(),
    );

    if (!mounted) return;

    setState(() => _isProcessingAction = false);

    if (res.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFF34C759),
          content: Text(res.message.isNotEmpty ? res.message : 'Pengajuan cuti berhasil disetujui.'),
        ),
      );
      _loadDetail();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(backgroundColor: AppTheme.statusRejected, content: Text(res.message)),
      );
    }
  }

  Future<void> _handleRejectAction() async {
    final reasonController = TextEditingController();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(CupertinoIcons.xmark_circle_fill, color: Color(0xFFFF3B30), size: 24),
            SizedBox(width: 8),
            Text('Tolak Pengajuan', style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Masukkan alasan penolakan pengajuan cuti ini:',
              style: TextStyle(fontSize: 13, color: isDark ? const Color(0xFFCBD5E1) : AppTheme.textSecondary),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: reasonController,
              decoration: const InputDecoration(
                hintText: 'Alasan penolakan (wajib)...',
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFFF3B30)),
            onPressed: () {
              if (reasonController.text.trim().isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Alasan penolakan wajib diisi.')),
                );
                return;
              }
              Navigator.pop(ctx, true);
            },
            child: const Text('Tolak Cuti', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isProcessingAction = true);

    final res = await ApiService.processApprovalAction(
      leaveId: widget.leaveId,
      action: 'reject',
      rejectionReason: reasonController.text.trim(),
    );

    if (!mounted) return;

    setState(() => _isProcessingAction = false);

    if (res.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: const Color(0xFFFF3B30),
          content: Text(res.message.isNotEmpty ? res.message : 'Pengajuan cuti telah ditolak.'),
        ),
      );
      _loadDetail();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(backgroundColor: AppTheme.statusRejected, content: Text(res.message)),
      );
    }
  }

  Future<void> _handleCancel() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Batalkan Pengajuan?'),
        content: const Text('Apakah Anda yakin ingin membatalkan pengajuan cuti ini? Tindakan ini tidak dapat diurungkan.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Tidak')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppTheme.statusRejected),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Batalkan'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() {
      _isCancelling = true;
    });

    final res = await ApiService.cancelLeave(widget.leaveId);

    if (!mounted) return;

    setState(() {
      _isCancelling = false;
    });

    if (res.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pengajuan cuti berhasil dibatalkan.')),
      );
      _loadDetail();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(backgroundColor: AppTheme.statusRejected, content: Text(res.message)),
      );
    }
  }

  Widget _buildStatusBanner(LeaveModel leave) {
    Color bg;
    Color color;
    IconData icon;
    String title;
    String desc;

    switch (leave.status.toLowerCase()) {
      case 'approved':
        bg = AppTheme.statusApprovedBg;
        color = AppTheme.statusApproved;
        icon = CupertinoIcons.checkmark_seal_fill;
        title = 'Disetujui Sepenuhnya';
        desc = 'Pengajuan cuti telah disetujui HRD & kuota telah dipotong.';
        break;
      case 'rejected':
        bg = AppTheme.statusRejectedBg;
        color = AppTheme.statusRejected;
        icon = CupertinoIcons.xmark_circle_fill;
        title = 'Pengajuan Ditolak';
        desc = leave.rejectionReason?.isNotEmpty == true
            ? 'Alasan: ${leave.rejectionReason}'
            : 'Pengajuan cuti tidak disetujui.';
        break;
      case 'cancelled':
        bg = AppTheme.statusCancelledBg;
        color = AppTheme.statusCancelled;
        icon = CupertinoIcons.minus_circle_fill;
        title = 'Dibatalkan';
        desc = 'Pengajuan cuti ini telah dibatalkan oleh pemohon.';
        break;
      default:
        bg = AppTheme.statusPendingBg;
        color = AppTheme.statusPending;
        icon = CupertinoIcons.clock_fill;
        title = leave.stepLabel;
        if (leave.approvalStep == 'pending_spv') {
          desc = 'Menunggu persetujuan dari Supervisor / Leader departemen.';
        } else if (leave.approvalStep == 'pending_manager') {
          desc = 'Menunggu persetujuan Plant Manager.';
        } else {
          desc = 'Menunggu persetujuan akhir dan pemotongan kuota oleh HRD.';
        }
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 30),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: color)),
                const SizedBox(height: 2),
                Text(desc, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineStep({
    required String title,
    required String subtitle,
    required bool isDone,
    required bool isCurrent,
    required bool isRejected,
    required bool isDark,
    required Color textHead,
    required Color textSub,
    String? note,
    String? date,
    bool isLast = false,
  }) {
    Color nodeColor;
    IconData nodeIcon;

    if (isRejected) {
      nodeColor = const Color(0xFFFF3B30);
      nodeIcon = CupertinoIcons.clear;
    } else if (isDone) {
      nodeColor = const Color(0xFF34C759);
      nodeIcon = CupertinoIcons.checkmark;
    } else if (isCurrent) {
      nodeColor = const Color(0xFFFF9500);
      nodeIcon = CupertinoIcons.time;
    } else {
      nodeColor = isDark ? const Color(0xFF64748B) : const Color(0xFFD1D1D6);
      nodeIcon = CupertinoIcons.circle;
    }

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Column(
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: BoxDecoration(
                  color: nodeColor.withOpacity(0.15),
                  shape: BoxShape.circle,
                  border: Border.all(color: nodeColor, width: 2),
                ),
                child: Icon(nodeIcon, size: 14, color: nodeColor),
              ),
              if (!isLast)
                Expanded(
                  child: Container(
                    width: 2,
                    color: isDone ? const Color(0xFF34C759) : (isDark ? const Color(0xFF334155) : const Color(0xFFE5E5EA)),
                  ),
                ),
            ],
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        title,
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: isCurrent ? const Color(0xFFFF9500) : (isRejected ? const Color(0xFFFF3B30) : textHead),
                        ),
                      ),
                      if (date != null && date.isNotEmpty)
                        Text(date, style: TextStyle(fontSize: 11, color: isDark ? const Color(0xFF64748B) : const Color(0xFF8E8E93))),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: TextStyle(fontSize: 12, color: textSub),
                  ),
                  if (note != null && note.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF2F2F7),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'Catatan: "$note"',
                        style: TextStyle(fontSize: 11.5, fontStyle: FontStyle.italic, color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3A3A3C)),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineSection(
    LeaveModel leave,
    Color cardBg,
    Color borderCol,
    Color textHead,
    Color textSub,
    bool isDark,
  ) {
    final isRejected = leave.isRejected;
    final isApproved = leave.isApproved;
    final empLevel = leave.employeeLevel;

    // Spv logic
    final spvDone = leave.spvId != null || (leave.approvalStep != 'pending_spv' && !isRejected) || isApproved;
    final spvCurrent = leave.approvalStep == 'pending_spv' && !isRejected && !isApproved;
    final spvRejected = isRejected && (leave.approvalStep == 'pending_spv' || (leave.spvId == null && leave.managerId == null && leave.hrdId == null));

    // Manager logic
    final mgrDone = leave.managerId != null || (leave.approvalStep == 'pending_hrd') || isApproved;
    final mgrCurrent = leave.approvalStep == 'pending_manager' && !isRejected && !isApproved;
    final mgrRejected = isRejected && leave.approvalStep == 'pending_manager';

    // HRD logic
    final hrdDone = isApproved;
    final hrdCurrent = leave.approvalStep == 'pending_hrd' && !isRejected && !isApproved;
    final hrdRejected = isRejected && leave.approvalStep == 'pending_hrd';

    List<Widget> timelineItems = [];
    String tierTitle = 'Hierarki Persetujuan';

    if (empLevel <= 2) {
      // 3 TAHAP: Operator & Staff -> Spv -> Manager -> HRD
      tierTitle = 'Hierarki Persetujuan (3 Tahap)';
      timelineItems = [
        _buildTimelineStep(
          title: '1. Supervisor / Leader',
          subtitle: leave.spvName != null ? 'Oleh: ${leave.spvName}' : (spvDone ? 'Disetujui' : (spvCurrent ? 'Menunggu review' : 'Antrean')),
          isDone: spvDone,
          isCurrent: spvCurrent,
          isRejected: spvRejected,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          note: leave.spvNotes,
          date: leave.spvAt,
        ),
        _buildTimelineStep(
          title: '2. Plant Manager',
          subtitle: leave.managerName != null ? 'Oleh: ${leave.managerName}' : (mgrDone ? 'Disetujui' : (mgrCurrent ? 'Menunggu review' : 'Antrean')),
          isDone: mgrDone,
          isCurrent: mgrCurrent,
          isRejected: mgrRejected,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          note: leave.managerNotes,
          date: leave.managerAt,
        ),
        _buildTimelineStep(
          title: '3. HRD (Final & Potong Kuota)',
          subtitle: leave.hrdName != null ? 'Oleh: ${leave.hrdName}' : (hrdDone ? 'Disetujui & Kuota Dipotong' : (hrdCurrent ? 'Menunggu review final' : 'Antrean')),
          isDone: hrdDone,
          isCurrent: hrdCurrent,
          isRejected: hrdRejected,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          note: leave.hrdNotes ?? leave.catatanAtasan,
          date: leave.hrdAt ?? leave.approvedAt,
          isLast: true,
        ),
      ];
    } else if (empLevel <= 4) {
      // 2 TAHAP: Leader & Supervisor -> Langsung ke Plant Manager -> HRD
      tierTitle = 'Hierarki Persetujuan (2 Tahap)';
      timelineItems = [
        _buildTimelineStep(
          title: '1. Plant Manager',
          subtitle: leave.managerName != null ? 'Oleh: ${leave.managerName}' : (mgrDone ? 'Disetujui' : (mgrCurrent ? 'Menunggu review' : 'Antrean')),
          isDone: mgrDone,
          isCurrent: mgrCurrent,
          isRejected: mgrRejected,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          note: leave.managerNotes,
          date: leave.managerAt,
        ),
        _buildTimelineStep(
          title: '2. HRD (Final & Potong Kuota)',
          subtitle: leave.hrdName != null ? 'Oleh: ${leave.hrdName}' : (hrdDone ? 'Disetujui & Kuota Dipotong' : (hrdCurrent ? 'Menunggu review final' : 'Antrean')),
          isDone: hrdDone,
          isCurrent: hrdCurrent,
          isRejected: hrdRejected,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          note: leave.hrdNotes ?? leave.catatanAtasan,
          date: leave.hrdAt ?? leave.approvedAt,
          isLast: true,
        ),
      ];
    } else if (empLevel <= 6) {
      // 1 TAHAP: Manager -> Langsung ke HRD
      tierTitle = 'Hierarki Persetujuan (1 Tahap)';
      timelineItems = [
        _buildTimelineStep(
          title: '1. HRD (Final & Potong Kuota)',
          subtitle: leave.hrdName != null ? 'Oleh: ${leave.hrdName}' : (hrdDone ? 'Disetujui & Kuota Dipotong' : (hrdCurrent ? 'Menunggu review final' : 'Antrean')),
          isDone: hrdDone,
          isCurrent: hrdCurrent,
          isRejected: hrdRejected,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          note: leave.hrdNotes ?? leave.catatanAtasan,
          date: leave.hrdAt ?? leave.approvedAt,
          isLast: true,
        ),
      ];
    } else {
      // HRD & Super Admin -> Auto-approved
      tierTitle = 'Hierarki Persetujuan (Auto-Approved)';
      timelineItems = [
        _buildTimelineStep(
          title: '1. Persetujuan HRD / Mandiri',
          subtitle: 'Disetujui Langsung & Kuota Dipotong Otomatis',
          isDone: true,
          isCurrent: false,
          isRejected: false,
          isDark: isDark,
          textHead: textHead,
          textSub: textSub,
          date: leave.approvedAt ?? leave.createdAt,
          isLast: true,
        ),
      ];
    }

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderCol),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            tierTitle,
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: textHead),
          ),
          const SizedBox(height: 16),
          ...timelineItems,
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, Color textHead, Color textSub, {IconData? icon}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 18, color: textSub),
            const SizedBox(width: 10),
          ],
          SizedBox(
            width: 130,
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
    final isOwner = _leave != null && user != null && _leave!.employeeId == user.id;
    final canApproveNow = _leave != null && _canUserApprove(_leave!, user);

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

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
          'Detail Pengajuan',
          style: TextStyle(
            color: textHead,
            fontWeight: FontWeight.bold,
            fontSize: 17,
          ),
        ),
        actions: [
          if (_leave != null)
            IconButton(
              icon: const Icon(CupertinoIcons.printer_fill, color: Color(0xFF007AFF), size: 22),
              tooltip: 'Cetak Surat Cuti',
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => LeavePrintPreviewScreen(leave: _leave!),
                  ),
                );
              },
            ),
          const SizedBox(width: 8),
        ],
      ),
      body: _isLoading
          ? const Center(child: CupertinoActivityIndicator(radius: 14))
          : _errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, size: 48, color: AppTheme.statusRejected),
                      const SizedBox(height: 12),
                      Text(_errorMessage!, style: TextStyle(color: textSub)),
                      const SizedBox(height: 12),
                      ElevatedButton(onPressed: _loadDetail, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 600),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Status Banner
                          _buildStatusBanner(_leave!),
                          const SizedBox(height: 16),

                          // Dynamic Hierarchical Approval Flow Card
                          _buildTimelineSection(_leave!, cardBg, borderCol, textHead, textSub, isDark),
                          const SizedBox(height: 16),

                          // Main Info Card
                          Container(
                            padding: const EdgeInsets.all(18),
                            decoration: BoxDecoration(
                              color: cardBg,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: borderCol),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  'Informasi Pengajuan',
                                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: textHead),
                                ),
                                Divider(height: 24, color: borderCol),
                                _buildInfoRow('Nomor Surat', _leave!.nomorSurat, textHead, textSub, icon: Icons.tag),
                                _buildInfoRow('Nama Pemohon', _leave!.namaLengkap ?? '-', textHead, textSub, icon: Icons.person_outline),
                                _buildInfoRow('Departemen', _leave!.namaDept ?? '-', textHead, textSub, icon: Icons.apartment),
                                _buildInfoRow('Jenis Cuti', _leave!.namaCuti ?? '-', textHead, textSub, icon: Icons.category_outlined),
                                _buildInfoRow(
                                  'Tgl Pelaksanaan',
                                  '${_leave!.tanggalMulai} s/d ${_leave!.tanggalSelesai}',
                                  textHead,
                                  textSub,
                                  icon: Icons.date_range,
                                ),
                                _buildInfoRow('Total Durasi', '${_leave!.totalHari} Hari Kerja', textHead, textSub, icon: Icons.timelapse),
                                _buildInfoRow('Potong Kuota', _leave!.potongKuota ? 'Ya (Potong Kuota)' : 'Tidak (Hak Khusus/Izin)', textHead, textSub, icon: Icons.pie_chart_outline),
                                Divider(height: 24, color: borderCol),
                                Text('Alasan Cuti:', style: TextStyle(fontSize: 13, color: textSub)),
                                const SizedBox(height: 6),
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(_leave!.alasan, style: TextStyle(fontSize: 13, color: textHead)),
                                ),
                                if (_leave!.alamatSelamaCuti != null && _leave!.alamatSelamaCuti!.isNotEmpty) ...[
                                  const SizedBox(height: 12),
                                  _buildInfoRow('Alamat Selama Cuti', _leave!.alamatSelamaCuti!, textHead, textSub, icon: Icons.location_on_outlined),
                                ],
                                if (_leave!.kontakDarurat != null && _leave!.kontakDarurat!.isNotEmpty) ...[
                                  const SizedBox(height: 12),
                                  _buildInfoRow('Kontak Darurat', _leave!.kontakDarurat!, textHead, textSub, icon: Icons.phone_outlined),
                                ],
                              ],
                            ),
                          ),
                          const SizedBox(height: 16),

                          // Tombol Cetak Surat Cuti
                          ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0284C7),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              elevation: 2,
                            ),
                            icon: const Icon(CupertinoIcons.printer_fill, size: 20),
                            label: const Text(
                              'Cetak Surat Cuti',
                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                            ),
                            onPressed: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => LeavePrintPreviewScreen(leave: _leave!),
                                ),
                              );
                            },
                          ),
                          const SizedBox(height: 20),

                          // Tindakan Persetujuan Card (for Approvers: Leader, Manager, HRD)
                          if (!isOwner && (user?.canApprove == true || _leave!.isPending)) ...[
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: cardBg,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(
                                  color: canApproveNow
                                      ? (isDark ? const Color(0xFF0284C7) : const Color(0xFFBAE6FD))
                                      : borderCol,
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.stretch,
                                children: [
                                  Row(
                                    children: [
                                      Icon(
                                        canApproveNow
                                            ? CupertinoIcons.checkmark_shield_fill
                                            : CupertinoIcons.lock_shield_fill,
                                        color: canApproveNow
                                            ? const Color(0xFF007AFF)
                                            : const Color(0xFF94A3B8),
                                        size: 20,
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          'Tindakan Persetujuan',
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 15,
                                            color: textHead,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: canApproveNow
                                              ? const Color(0xFF34C759).withOpacity(0.12)
                                              : (isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                                          borderRadius: BorderRadius.circular(6),
                                        ),
                                        child: Text(
                                          canApproveNow ? 'Giliran Anda' : 'Tombol Dinonaktifkan',
                                          style: TextStyle(
                                            fontSize: 11,
                                            fontWeight: FontWeight.bold,
                                            color: canApproveNow
                                                ? const Color(0xFF16A34A)
                                                : const Color(0xFF64748B),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    canApproveNow
                                        ? 'Pengajuan ini sedang menunggu tindakan dari Anda.'
                                        : (_leave!.approvalStep == 'pending_spv'
                                            ? 'Tombol dinonaktifkan: Menunggu persetujuan dari Leader / Supervisor Departemen ${_leave!.namaDept ?? ""} lebih dulu.'
                                            : (_leave!.approvalStep == 'pending_manager'
                                                ? 'Tombol dinonaktifkan: Menunggu persetujuan dari Plant Manager lebih dulu.'
                                                : (_leave!.approvalStep == 'pending_hrd'
                                                    ? 'Tombol dinonaktifkan: Pengajuan sedang menunggu persetujuan akhir HRD.'
                                                    : 'Pengajuan ini sudah selesai diproses.'))),
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      color: canApproveNow ? textSub : const Color(0xFFEF4444),
                                      height: 1.35,
                                    ),
                                  ),
                                  const SizedBox(height: 16),
                                  if (_isProcessingAction)
                                    const Center(child: CupertinoActivityIndicator(radius: 14))
                                  else
                                    Row(
                                      children: [
                                        Expanded(
                                          child: OutlinedButton.icon(
                                            style: OutlinedButton.styleFrom(
                                              foregroundColor: canApproveNow ? const Color(0xFFFF3B30) : const Color(0xFF94A3B8),
                                              side: BorderSide(
                                                color: canApproveNow ? const Color(0xFFFF3B30) : const Color(0xFFCBD5E1),
                                                width: 1.5,
                                              ),
                                              padding: const EdgeInsets.symmetric(vertical: 13),
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                            ),
                                            icon: Icon(
                                              CupertinoIcons.xmark_circle,
                                              size: 18,
                                              color: canApproveNow ? const Color(0xFFFF3B30) : const Color(0xFF94A3B8),
                                            ),
                                            label: Text(
                                              'Tolak',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 14,
                                                color: canApproveNow ? const Color(0xFFFF3B30) : const Color(0xFF94A3B8),
                                              ),
                                            ),
                                            onPressed: canApproveNow ? _handleRejectAction : null,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: ElevatedButton.icon(
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: canApproveNow
                                                  ? const Color(0xFF34C759)
                                                  : (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                                              foregroundColor: canApproveNow ? Colors.white : const Color(0xFF94A3B8),
                                              elevation: canApproveNow ? 1 : 0,
                                              padding: const EdgeInsets.symmetric(vertical: 13),
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                            ),
                                            icon: Icon(
                                              CupertinoIcons.checkmark_alt_circle,
                                              size: 18,
                                              color: canApproveNow ? Colors.white : const Color(0xFF94A3B8),
                                            ),
                                            label: Text(
                                              'Setujui',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 14,
                                                color: canApproveNow ? Colors.white : const Color(0xFF94A3B8),
                                              ),
                                            ),
                                            onPressed: canApproveNow ? _handleApproveAction : null,
                                          ),
                                        ),
                                      ],
                                    ),
                                ],
                              ),
                            ),
                            const SizedBox(height: 16),
                          ],

                          // Cancel Button (if pending and is owner)
                          if (isOwner && _leave!.isPending)
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFFFF3B30),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              onPressed: _isCancelling ? null : _handleCancel,
                              child: _isCancelling
                                  ? const CupertinoActivityIndicator(color: Colors.white)
                                  : const Text('Batalkan Pengajuan Cuti', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                            ),
                          const SizedBox(height: 24),
                        ],
                      ),
                    ),
                  ),
                ),
    );
  }
}

