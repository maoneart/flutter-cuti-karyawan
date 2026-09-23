import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../services/api_service.dart';
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
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Persetujuan Cuti Anggota', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
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
        color: AppTheme.primary,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppTheme.statusRejected),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, style: const TextStyle(color: AppTheme.textSecondary)),
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
                                  color: AppTheme.statusApproved.withOpacity(0.08),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.done_all_rounded, size: 48, color: AppTheme.statusApproved),
                              ),
                              const SizedBox(height: 16),
                              const Text(
                                'Semua Bersih!',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                              ),
                              const SizedBox(height: 6),
                              const Text(
                                'Tidak ada pengajuan cuti yang memerlukan persetujuan.',
                                style: TextStyle(fontSize: 13, color: AppTheme.textSecondary),
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
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.02),
                                    blurRadius: 8,
                                    offset: const Offset(0, 2),
                                  ),
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Top Header
                                  Row(
                                    children: [
                                      CircleAvatar(
                                        radius: 20,
                                        backgroundColor: AppTheme.primary.withOpacity(0.1),
                                        child: Text(
                                          (leave.namaLengkap?.isNotEmpty == true)
                                              ? leave.namaLengkap!.substring(0, 1).toUpperCase()
                                              : 'K',
                                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primary),
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              leave.namaLengkap ?? '-',
                                              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                                            ),
                                            Text(
                                              '${leave.nik ?? ''} • ${leave.namaDept ?? ''}',
                                              style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                                            ),
                                          ],
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: AppTheme.primary.withOpacity(0.08),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          leave.kodeCuti ?? 'CUTI',
                                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppTheme.primary),
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

                                  const Divider(height: 20, color: Color(0xFFF1F5F9)),

                                  // Details
                                  Row(
                                    children: [
                                      const Icon(Icons.date_range, size: 16, color: AppTheme.textMuted),
                                      const SizedBox(width: 6),
                                      Expanded(
                                        child: Text(
                                          '${leave.tanggalMulai} s/d ${leave.tanggalSelesai}',
                                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
                                        ),
                                      ),
                                      Text(
                                        '${leave.totalHari} Hari',
                                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Container(
                                    width: double.infinity,
                                    padding: const EdgeInsets.all(10),
                                    decoration: BoxDecoration(
                                      color: AppTheme.surfaceVariant,
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Text(
                                      'Alasan: ${leave.alasan}',
                                      style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                                    ),
                                  ),

                                  // Actions (if pending)
                                  if (leave.isPending) ...[
                                    const SizedBox(height: 14),
                                    Row(
                                      children: [
                                        Expanded(
                                          child: OutlinedButton.icon(
                                            style: OutlinedButton.styleFrom(
                                              foregroundColor: AppTheme.statusRejected,
                                              side: const BorderSide(color: AppTheme.statusRejected),
                                              padding: const EdgeInsets.symmetric(vertical: 10),
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                            ),
                                            onPressed: () => _showApprovalDialog(leave, false),
                                            icon: const Icon(Icons.close_rounded, size: 16),
                                            label: const Text('Tolak', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                          ),
                                        ),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: ElevatedButton.icon(
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: AppTheme.statusApproved,
                                              padding: const EdgeInsets.symmetric(vertical: 10),
                                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                            ),
                                            onPressed: () => _showApprovalDialog(leave, true),
                                            icon: const Icon(Icons.check_rounded, size: 16),
                                            label: const Text('Setujui', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                                          ),
                                        ),
                                      ],
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
