import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'leave_detail_screen.dart';
import 'leave_print_preview_screen.dart';

class ManageLeavesScreen extends StatefulWidget {
  const ManageLeavesScreen({super.key});

  @override
  State<ManageLeavesScreen> createState() => _ManageLeavesScreenState();
}

class _ManageLeavesScreenState extends State<ManageLeavesScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  List<LeaveModel> _allLeaves = [];
  bool _isLoading = true;
  String? _errorMessage;

  int? _selectedDeptId; // null = Semua Departemen
  final List<String> _statusFilters = ['all', 'pending', 'approved', 'rejected', 'cancelled'];
  final List<String> _tabTitles = ['Semua', 'Menunggu', 'Disetujui', 'Ditolak', 'Dibatalkan'];

  final List<Map<String, dynamic>> _departemenList = [
    {'id': null, 'nama': 'Semua Dept', 'kode': 'ALL'},
    {'id': 1, 'nama': 'Accounting', 'kode': 'ACC'},
    {'id': 2, 'nama': 'Casting', 'kode': 'CAST'},
    {'id': 3, 'nama': 'Core', 'kode': 'CORE'},
    {'id': 4, 'nama': 'Diecasting', 'kode': 'DC'},
    {'id': 5, 'nama': 'Engineering', 'kode': 'ENG'},
    {'id': 6, 'nama': 'Fettling', 'kode': 'FET'},
    {'id': 7, 'nama': 'GA', 'kode': 'GA'},
    {'id': 8, 'nama': 'HRD', 'kode': 'HRD'},
    {'id': 9, 'nama': 'Machining', 'kode': 'MC'},
    {'id': 10, 'nama': 'Marketing', 'kode': 'MKT'},
    {'id': 11, 'nama': 'Maintenance', 'kode': 'MAINT'},
    {'id': 12, 'nama': 'PPIC', 'kode': 'PPIC'},
    {'id': 13, 'nama': 'Purchasing', 'kode': 'PURCH'},
    {'id': 14, 'nama': 'QC', 'kode': 'QC'},
    {'id': 15, 'nama': 'QC Line', 'kode': 'QCL'},
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabTitles.length, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        _loadLeaves();
      }
    });
    _loadLeaves();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadLeaves() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final currentStatus = _statusFilters[_tabController.index];
    final res = await ApiService.getLeavesList(
      status: currentStatus,
      scope: 'all',
      deptId: _selectedDeptId,
      search: _searchController.text.trim(),
    );

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _allLeaves = res.data!;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  Widget _buildStatusBadge(String status, String? step) {
    Color bg;
    Color text;
    String label;

    switch (status.toLowerCase()) {
      case 'approved':
        bg = AppTheme.statusApprovedBg;
        text = AppTheme.statusApproved;
        label = 'Disetujui';
        break;
      case 'rejected':
        bg = AppTheme.statusRejectedBg;
        text = AppTheme.statusRejected;
        label = 'Ditolak';
        break;
      case 'cancelled':
        bg = AppTheme.statusCancelledBg;
        text = AppTheme.statusCancelled;
        label = 'Dibatalkan';
        break;
      default:
        bg = AppTheme.statusPendingBg;
        text = AppTheme.statusPending;
        if (step == 'pending_spv') {
          label = 'Review Spv';
        } else if (step == 'pending_manager') {
          label = 'Review Manager';
        } else if (step == 'pending_hrd') {
          label = 'Review HRD (Final)';
        } else {
          label = 'Menunggu';
        }
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: text,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  void _showDirectActionDialog(LeaveModel leave, String action) {
    final noteController = TextEditingController();
    final isApprove = action == 'approve';

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(
              isApprove ? Icons.check_circle_rounded : Icons.cancel_rounded,
              color: isApprove ? AppTheme.statusApproved : AppTheme.statusRejected,
            ),
            const SizedBox(width: 8),
            Text(isApprove ? 'Verifikasi & Setujui Cuti' : 'Tolak Pengajuan Cuti'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Pengajuan: ${leave.namaLengkap ?? "Karyawan"} (${leave.totalHari} Hari)',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
            ),
            const SizedBox(height: 4),
            Text('Jenis: ${leave.namaCuti ?? "Cuti Tahunan"}', style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
            const SizedBox(height: 12),
            TextField(
              controller: noteController,
              decoration: InputDecoration(
                labelText: isApprove ? 'Catatan HRD (Opsional)' : 'Alasan Penolakan (Wajib)',
                hintText: isApprove ? 'Disetujui sesuai prosedur' : 'Tuliskan alasan penolakan...',
                border: const OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: isApprove ? AppTheme.statusApproved : AppTheme.statusRejected,
              foregroundColor: Colors.white,
            ),
            onPressed: () async {
              final note = noteController.text.trim();
              if (!isApprove && note.isEmpty) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Alasan penolakan wajib diisi!')),
                );
                return;
              }
              Navigator.pop(ctx);

              final res = await ApiService.processApproval(
                leaveId: leave.id,
                action: action,
                notes: note,
                rejectionReason: !isApprove ? note : null,
              );

              if (mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(res.message),
                    backgroundColor: res.success ? AppTheme.statusApproved : AppTheme.statusRejected,
                  ),
                );
                _loadLeaves();
              }
            },
            child: Text(isApprove ? 'Setujui Final' : 'Tolak'),
          ),
        ],
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
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        title: Text(
          'Kelola Data Cuti',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadLeaves,
            tooltip: 'Segarkan Data',
          ),
          const SizedBox(width: 6),
        ],
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          labelColor: primaryAccent,
          unselectedLabelColor: textSub,
          indicatorColor: primaryAccent,
          indicatorWeight: 3,
          tabs: _tabTitles.map((t) => Tab(text: t)).toList(),
        ),
      ),
      body: Column(
        children: [
          // Filter Horizontal Departemen
          Container(
            height: 48,
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16),
              itemCount: _departemenList.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (ctx, idx) {
                final dept = _departemenList[idx];
                final isSelected = _selectedDeptId == dept['id'];

                return ChoiceChip(
                  label: Text(
                    dept['nama'] as String,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                      color: isSelected
                          ? Colors.white
                          : (isDark ? Colors.white70 : AppTheme.textPrimary),
                    ),
                  ),
                  selected: isSelected,
                  selectedColor: primaryAccent,
                  backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
                  side: BorderSide(
                    color: isSelected ? primaryAccent : borderCol,
                  ),
                  onSelected: (selected) {
                    if (selected) {
                      setState(() {
                        _selectedDeptId = dept['id'] as int?;
                      });
                      _loadLeaves();
                    }
                  },
                );
              },
            ),
          ),

          // Search Box
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari nama karyawan, NIK, no surat, alasan...',
                prefixIcon: const Icon(Icons.search, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          _loadLeaves();
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onSubmitted: (_) => _loadLeaves(),
            ),
          ),

          // List Leaves
          Expanded(
            child: RefreshIndicator(
              onRefresh: _loadLeaves,
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
                              ElevatedButton(onPressed: _loadLeaves, child: const Text('Coba Lagi')),
                            ],
                          ),
                        )
                      : _allLeaves.isEmpty
                          ? Center(
                              child: SingleChildScrollView(
                                physics: const AlwaysScrollableScrollPhysics(),
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(20),
                                      decoration: BoxDecoration(
                                        color: primaryAccent.withOpacity(0.1),
                                        shape: BoxShape.circle,
                                      ),
                                      child: Icon(Icons.folder_open_rounded, size: 48, color: primaryAccent),
                                    ),
                                    const SizedBox(height: 16),
                                    Text(
                                      'Tidak Ada Data Cuti Ditemukan',
                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: textHead),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      'Tidak ada pengajuan cuti yang sesuai dengan filter ini.',
                                      style: TextStyle(fontSize: 13, color: textSub),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : ListView.separated(
                              padding: const EdgeInsets.all(16),
                              itemCount: _allLeaves.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 12),
                              itemBuilder: (ctx, idx) {
                                final leave = _allLeaves[idx];
                                final canDirectApprove = leave.isPending && leave.approvalStep == 'pending_hrd';

                                return Container(
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: cardBg,
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(color: borderCol),
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withOpacity(isDark ? 0.2 : 0.04),
                                        blurRadius: 8,
                                        offset: const Offset(0, 3),
                                      ),
                                    ],
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Top Employee Info & Status
                                      Row(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          CircleAvatar(
                                            radius: 20,
                                            backgroundColor: primaryAccent.withOpacity(0.15),
                                            child: Text(
                                              (leave.namaLengkap ?? 'K').substring(0, 1).toUpperCase(),
                                              style: TextStyle(fontWeight: FontWeight.bold, color: primaryAccent, fontSize: 15),
                                            ),
                                          ),
                                          const SizedBox(width: 12),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Text(
                                                  leave.namaLengkap ?? 'Karyawan',
                                                  style: TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    fontSize: 14.5,
                                                    color: textHead,
                                                  ),
                                                ),
                                                const SizedBox(height: 2),
                                                Text(
                                                  '${leave.nik ?? "-"} • ${leave.namaDept ?? "Dept"} (${leave.namaJabatan ?? "Staff"})',
                                                  style: TextStyle(fontSize: 11.5, color: textSub),
                                                ),
                                              ],
                                            ),
                                          ),
                                          _buildStatusBadge(leave.status, leave.approvalStep),
                                        ],
                                      ),
                                      const SizedBox(height: 12),
                                      Divider(height: 1, color: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9)),
                                      const SizedBox(height: 12),

                                      // Leave Type & Duration
                                      Row(
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: (leave.potongKuota ? const Color(0xFF0284C7) : const Color(0xFF10B981)).withOpacity(0.12),
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: Text(
                                              leave.namaCuti ?? 'Cuti Tahunan',
                                              style: TextStyle(
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                                color: leave.potongKuota ? const Color(0xFF0284C7) : const Color(0xFF10B981),
                                              ),
                                            ),
                                          ),
                                          const Spacer(),
                                          Text(
                                            '${leave.totalHari} Hari Kerja',
                                            style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12.5, color: textHead),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 8),

                                      // Period Date
                                      Row(
                                        children: [
                                          Icon(CupertinoIcons.calendar, size: 14, color: textSub),
                                          const SizedBox(width: 6),
                                          Text(
                                            '${LeavePrintService.formatTanggalIndo(leave.tanggalMulai)} s/d ${LeavePrintService.formatTanggalIndo(leave.tanggalSelesai)}',
                                            style: TextStyle(fontSize: 12, color: textHead, fontWeight: FontWeight.w500),
                                          ),
                                        ],
                                      ),

                                      // Reason
                                      if (leave.alasan.isNotEmpty) ...[
                                        const SizedBox(height: 6),
                                        Text(
                                          'Alasan: ${leave.alasan}',
                                          style: TextStyle(fontSize: 12, color: textSub),
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],

                                      const SizedBox(height: 14),

                                      // Action Buttons
                                      Row(
                                        children: [
                                          // Button Cetak PDF
                                          Expanded(
                                            child: OutlinedButton.icon(
                                              style: OutlinedButton.styleFrom(
                                                padding: const EdgeInsets.symmetric(vertical: 8),
                                                side: BorderSide(color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1)),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                              icon: const Icon(CupertinoIcons.printer, size: 16),
                                              label: const Text('Cetak PDF', style: TextStyle(fontSize: 12)),
                                              onPressed: () {
                                                Navigator.push(
                                                  context,
                                                  MaterialPageRoute(
                                                    builder: (_) => LeavePrintPreviewScreen(leave: leave),
                                                  ),
                                                );
                                              },
                                            ),
                                          ),
                                          const SizedBox(width: 8),

                                          // Button Detail
                                          Expanded(
                                            child: ElevatedButton.icon(
                                              style: ElevatedButton.styleFrom(
                                                backgroundColor: primaryAccent,
                                                foregroundColor: Colors.white,
                                                padding: const EdgeInsets.symmetric(vertical: 8),
                                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                              ),
                                              icon: const Icon(CupertinoIcons.eye, size: 16),
                                              label: const Text('Detail', style: TextStyle(fontSize: 12)),
                                              onPressed: () {
                                                Navigator.push(
                                                  context,
                                                  MaterialPageRoute(
                                                    builder: (_) => LeaveDetailScreen(leaveId: leave.id),
                                                  ),
                                                ).then((_) => _loadLeaves());
                                              },
                                            ),
                                          ),

                                          // Quick Approve/Reject if pending HRD
                                          if (canDirectApprove) ...[
                                            const SizedBox(width: 8),
                                            IconButton.filled(
                                              style: IconButton.filledStyleFrom(
                                                backgroundColor: AppTheme.statusApproved,
                                              ),
                                              icon: const Icon(Icons.check, size: 18, color: Colors.white),
                                              tooltip: 'Setujui Final',
                                              onPressed: () => _showDirectActionDialog(leave, 'approve'),
                                            ),
                                            const SizedBox(width: 4),
                                            IconButton.filled(
                                              style: IconButton.filledStyleFrom(
                                                backgroundColor: AppTheme.statusRejected,
                                              ),
                                              icon: const Icon(Icons.close, size: 18, color: Colors.white),
                                              tooltip: 'Tolak',
                                              onPressed: () => _showDirectActionDialog(leave, 'reject'),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
            ),
          ),
        ],
      ),
    );
  }
}
