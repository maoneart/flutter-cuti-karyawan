import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'leave_detail_screen.dart';
import 'leave_print_preview_screen.dart';

class LeaveHistoryScreen extends StatefulWidget {
  const LeaveHistoryScreen({super.key});

  @override
  State<LeaveHistoryScreen> createState() => LeaveHistoryScreenState();
}

class LeaveHistoryScreenState extends State<LeaveHistoryScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  List<LeaveModel> _leaves = [];
  bool _isLoading = true;
  String? _errorMessage;
  String _selectedScope = 'my'; // 'my' or 'all'/'team'

  final List<String> _statusFilters = ['all', 'pending', 'approved', 'rejected', 'cancelled'];
  final List<String> _tabTitles = ['Semua', 'Menunggu', 'Disetujui', 'Ditolak', 'Dibatalkan'];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabTitles.length, vsync: this);
    _tabController.addListener(() {
      if (!_tabController.indexIsChanging) {
        loadLeaves();
      }
    });
    loadLeaves();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Future<void> loadLeaves() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final currentStatus = _statusFilters[_tabController.index];
    final res = await ApiService.getLeavesList(
      status: currentStatus,
      scope: _selectedScope,
      search: _searchController.text.trim(),
    );

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _leaves = res.data!;
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
          label = 'Menunggu Spv';
        } else if (step == 'pending_manager') {
          label = 'Menunggu Manager';
        } else if (step == 'pending_hrd') {
          label = 'Menunggu HRD';
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

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final canApprove = user?.canApprove ?? false;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    final teamLabel = (user?.isAdmin == true || user?.isManager == true) ? 'Semua Karyawan' : 'Tim Departemen';

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        title: Text(
          _selectedScope == 'my' ? 'Riwayat Cuti Saya' : 'Monitoring $teamLabel',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: loadLeaves,
            tooltip: 'Segarkan Riwayat Cuti',
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
          // Scope Switcher if User can Approve / HRD / Manager / Superadmin
          if (canApprove) ...[
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
              child: Container(
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1E293B) : const Color(0xFFE2E8F0),
                  borderRadius: BorderRadius.circular(12),
                ),
                padding: const EdgeInsets.all(4),
                child: Row(
                  children: [
                    Expanded(
                      child: InkWell(
                        onTap: () {
                          if (_selectedScope != 'my') {
                            setState(() => _selectedScope = 'my');
                            loadLeaves();
                          }
                        },
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          decoration: BoxDecoration(
                            color: _selectedScope == 'my'
                                ? (isDark ? const Color(0xFF334155) : Colors.white)
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: _selectedScope == 'my'
                                ? [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 4, offset: const Offset(0, 2))]
                                : null,
                          ),
                          alignment: Alignment.center,
                          child: Text(
                            'Cuti Saya',
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: _selectedScope == 'my' ? FontWeight.bold : FontWeight.w500,
                              color: _selectedScope == 'my' ? primaryAccent : textSub,
                            ),
                          ),
                        ),
                      ),
                    ),
                    Expanded(
                      child: InkWell(
                        onTap: () {
                          if (_selectedScope != 'all') {
                            setState(() => _selectedScope = 'all');
                            loadLeaves();
                          }
                        },
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          padding: const EdgeInsets.symmetric(vertical: 8),
                          decoration: BoxDecoration(
                            color: _selectedScope == 'all'
                                ? (isDark ? const Color(0xFF334155) : Colors.white)
                                : Colors.transparent,
                            borderRadius: BorderRadius.circular(10),
                            boxShadow: _selectedScope == 'all'
                                ? [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 4, offset: const Offset(0, 2))]
                                : null,
                          ),
                          alignment: Alignment.center,
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                CupertinoIcons.eye_fill,
                                size: 14,
                                color: _selectedScope == 'all' ? primaryAccent : textSub,
                              ),
                              const SizedBox(width: 6),
                              Text(
                                teamLabel,
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: _selectedScope == 'all' ? FontWeight.bold : FontWeight.w500,
                                  color: _selectedScope == 'all' ? primaryAccent : textSub,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],

          // Search Box
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: _selectedScope == 'my'
                    ? 'Cari nomor surat atau alasan cuti...'
                    : 'Cari nama karyawan, departemen, alasan...',
                prefixIcon: const Icon(Icons.search, size: 20),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          loadLeaves();
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onSubmitted: (_) => loadLeaves(),
            ),
          ),

          // List Leaves
          Expanded(
            child: RefreshIndicator(
              onRefresh: loadLeaves,
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
                              ElevatedButton(onPressed: loadLeaves, child: const Text('Coba Lagi')),
                            ],
                          ),
                        )
                      : _leaves.isEmpty
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
                                      child: Icon(Icons.description_outlined, size: 48, color: primaryAccent),
                                    ),
                                    const SizedBox(height: 16),
                                    Text(
                                      'Tidak Ada Data Pengajuan Cuti',
                                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: textHead),
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      'Belum ada pengajuan pada kategori ini.',
                                      style: TextStyle(fontSize: 13, color: textSub),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : ListView.separated(
                              padding: const EdgeInsets.all(16),
                              itemCount: _leaves.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 12),
                              itemBuilder: (ctx, idx) {
                                final leave = _leaves[idx];
                                final isOtherEmployee = _selectedScope != 'my';

                                return InkWell(
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (_) => LeaveDetailScreen(leaveId: leave.id),
                                      ),
                                    ).then((_) => loadLeaves());
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
                                        // Header: ID/Nomor Surat & Status Badge
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              leave.nomorSurat,
                                              style: TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.bold,
                                                color: primaryAccent,
                                              ),
                                            ),
                                            _buildStatusBadge(leave.status, leave.approvalStep),
                                          ],
                                        ),

                                        // Employee Name & Dept if viewing team/company
                                        if (isOtherEmployee && leave.namaLengkap != null) ...[
                                          const SizedBox(height: 8),
                                          Row(
                                            children: [
                                              Container(
                                                padding: const EdgeInsets.all(6),
                                                decoration: BoxDecoration(
                                                  color: primaryAccent.withOpacity(0.12),
                                                  shape: BoxShape.circle,
                                                ),
                                                child: Icon(CupertinoIcons.person_fill, size: 14, color: primaryAccent),
                                              ),
                                              const SizedBox(width: 8),
                                              Expanded(
                                                child: Column(
                                                  crossAxisAlignment: CrossAxisAlignment.start,
                                                  children: [
                                                    Text(
                                                      leave.namaLengkap!,
                                                      style: TextStyle(
                                                        fontWeight: FontWeight.bold,
                                                        fontSize: 14,
                                                        color: textHead,
                                                      ),
                                                    ),
                                                    Text(
                                                      '${leave.namaDept ?? "-"} • ${leave.namaJabatan ?? "-"}',
                                                      style: TextStyle(fontSize: 11.5, color: textSub),
                                                    ),
                                                  ],
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],

                                        const SizedBox(height: 8),
                                        Text(
                                          leave.namaCuti ?? 'Cuti',
                                          style: TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.bold,
                                            color: textHead,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        Row(
                                          children: [
                                            const Icon(Icons.date_range, size: 14, color: AppTheme.textMuted),
                                            const SizedBox(width: 4),
                                            Text(
                                              '${leave.tanggalMulai} s/d ${leave.tanggalSelesai}',
                                              style: TextStyle(fontSize: 12, color: textSub),
                                            ),
                                            const SizedBox(width: 8),
                                            Container(
                                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                              decoration: BoxDecoration(
                                                color: isDark ? const Color(0xFF334155) : AppTheme.surfaceVariant,
                                                borderRadius: BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                '${leave.totalHari} Hari',
                                                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: textHead),
                                              ),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 8),
                                        Text(
                                          leave.alasan,
                                          style: TextStyle(fontSize: 13, color: textSub),
                                          maxLines: 2,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 10),
                                        Divider(height: 1, thickness: 0.8, color: borderCol),
                                        const SizedBox(height: 6),
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              'Ketuk untuk lihat detail',
                                              style: TextStyle(fontSize: 11.5, color: textSub.withOpacity(0.8)),
                                            ),
                                            TextButton.icon(
                                              style: TextButton.styleFrom(
                                                foregroundColor: const Color(0xFF0284C7),
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                                visualDensity: VisualDensity.compact,
                                              ),
                                              icon: const Icon(CupertinoIcons.printer_fill, size: 14),
                                              label: const Text(
                                                'Cetak Surat',
                                                style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                                              ),
                                              onPressed: () {
                                                Navigator.push(
                                                  context,
                                                  MaterialPageRoute(
                                                    builder: (_) => LeavePrintPreviewScreen(leave: leave),
                                                  ),
                                                );
                                              },
                                            ),
                                          ],
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
    );
  }
}

