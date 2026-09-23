import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../services/api_service.dart';
import 'leave_detail_screen.dart';

class LeaveHistoryScreen extends StatefulWidget {
  const LeaveHistoryScreen({super.key});

  @override
  State<LeaveHistoryScreen> createState() => _LeaveHistoryScreenState();
}

class _LeaveHistoryScreenState extends State<LeaveHistoryScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  List<LeaveModel> _leaves = [];
  bool _isLoading = true;
  String? _errorMessage;

  final List<String> _statusFilters = ['all', 'pending', 'approved', 'rejected', 'cancelled'];
  final List<String> _tabTitles = ['Semua', 'Menunggu', 'Disetujui', 'Ditolak', 'Dibatalkan'];

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
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final currentStatus = _statusFilters[_tabController.index];
    final res = await ApiService.getLeavesList(
      status: currentStatus,
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

  Widget _buildStatusBadge(String status) {
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
        label = 'Menunggu';
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
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      appBar: AppBar(
        title: Text('Riwayat Cuti Saya', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
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
          // Search Box
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Cari nomor surat atau alasan cuti...',
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
                                return InkWell(
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (_) => LeaveDetailScreen(leaveId: leave.id),
                                      ),
                                    ).then((_) => _loadLeaves());
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
                                            _buildStatusBadge(leave.status),
                                          ],
                                        ),
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
