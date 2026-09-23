import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../config/app_theme.dart';
import '../models/dashboard_stats_model.dart';
import '../models/leave_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'leave_detail_screen.dart';
import 'notification_screen.dart';
import 'public_board_screen.dart';
import 'add_employee_screen.dart';
import 'employee_list_screen.dart';
import 'about_screen.dart';

class DashboardScreen extends StatefulWidget {
  final Function(int)? onNavigateToTab;
  const DashboardScreen({super.key, this.onNavigateToTab});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  bool _isLoading = true;
  DashboardStatsModel? _stats;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  Future<void> _loadStats() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getDashboardStats();

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _stats = res.data;
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  Widget _buildStatusBadge(LeaveModel leave) {
    Color bg;
    Color text;
    String label;

    switch (leave.status.toLowerCase()) {
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
        if (leave.approvalStep == 'pending_spv') {
          label = 'Review Spv';
        } else if (leave.approvalStep == 'pending_manager') {
          label = 'Review Manager';
        } else if (leave.approvalStep == 'pending_hrd') {
          label = 'Review HRD';
        } else {
          label = 'Menunggu';
        }
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: text,
          fontSize: 10.5,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final bellCount = _stats?.bellNotificationCount ?? 0;
    final isAdmin = user?.role == 'admin' || (user?.levelHierarki ?? 0) >= 7;

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    return Scaffold(
      appBar: AppBar(
        title: Text('Dashboard', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
        actions: [
          // 1. Smart Bell Notification with Red Badge
          Stack(
            alignment: Alignment.center,
            children: [
              IconButton(
                icon: const Icon(CupertinoIcons.bell_fill, size: 22),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const NotificationScreen()),
                  ).then((_) => _loadStats());
                },
                tooltip: 'Notifikasi',
              ),
              if (bellCount > 0)
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: Color(0xFFFF3B30), // iOS Red
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                    child: Text(
                      bellCount > 99 ? '99+' : '$bellCount',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9.5,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadStats,
            tooltip: 'Segarkan Data',
          ),
          const SizedBox(width: 6),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadStats,
        color: isDark ? const Color(0xFF38BDF8) : AppTheme.primary,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.cloud_off_rounded, size: 56, color: AppTheme.statusRejected),
                          const SizedBox(height: 16),
                          Text(
                            _errorMessage!,
                            textAlign: TextAlign.center,
                            style: TextStyle(fontSize: 14, color: textSub),
                          ),
                          const SizedBox(height: 16),
                          ElevatedButton.icon(
                            onPressed: _loadStats,
                            icon: const Icon(Icons.refresh),
                            label: const Text('Coba Lagi'),
                          ),
                        ],
                      ),
                    ),
                  )
                : SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // 1. User Header Banner
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            gradient: LinearGradient(
                              colors: isDark
                                  ? [const Color(0xFF1E3A8A), const Color(0xFF0F172A)]
                                  : [AppTheme.primaryDark, AppTheme.primary],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: (isDark ? Colors.black : AppTheme.primary).withOpacity(0.25),
                                blurRadius: 16,
                                offset: const Offset(0, 8),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  CircleAvatar(
                                    radius: 26,
                                    backgroundColor: Colors.white.withOpacity(0.2),
                                    child: Text(
                                      (user?.namaLengkap.isNotEmpty == true)
                                          ? user!.namaLengkap.substring(0, 1).toUpperCase()
                                          : 'U',
                                      style: const TextStyle(
                                        fontSize: 22,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          user?.namaLengkap ?? 'Karyawan',
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 17,
                                            fontWeight: FontWeight.bold,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                        const SizedBox(height: 3),
                                        Text(
                                          '${user?.nik ?? ''} • ${user?.namaJabatan ?? ''}',
                                          style: TextStyle(
                                            color: Colors.white.withOpacity(0.85),
                                            fontSize: 12.5,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withOpacity(0.2),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Text(
                                      (user?.role ?? 'operator').toUpperCase(),
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                                decoration: BoxDecoration(
                                  color: Colors.black.withOpacity(0.25),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        const Icon(Icons.apartment_rounded, color: Colors.white70, size: 16),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            'Dept: ${user?.namaDept ?? '-'}',
                                            style: const TextStyle(color: Colors.white, fontSize: 12),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Row(
                                      children: [
                                        const Icon(Icons.timer_outlined, color: Colors.white70, size: 16),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            'Masa Kerja: ${user?.lamaBekerja ?? '-'}',
                                            style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // 2. Feature Action Cards (Papan Kehadiran Live & Tambah Karyawan HRD)
                        Row(
                          children: [
                            Expanded(
                              child: InkWell(
                                onTap: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(builder: (_) => const PublicBoardScreen()),
                                  );
                                },
                                borderRadius: BorderRadius.circular(14),
                                child: Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    gradient: const LinearGradient(
                                      colors: [Color(0xFF0284C7), Color(0xFF0EA5E9)],
                                      begin: Alignment.topLeft,
                                      end: Alignment.bottomRight,
                                    ),
                                    borderRadius: BorderRadius.circular(14),
                                    boxShadow: [
                                      BoxShadow(
                                        color: const Color(0xFF0284C7).withOpacity(0.2),
                                        blurRadius: 10,
                                        offset: const Offset(0, 4),
                                      ),
                                    ],
                                  ),
                                  child: const Row(
                                    children: [
                                      Icon(CupertinoIcons.tv, color: Colors.white, size: 24),
                                      SizedBox(width: 10),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              'Papan Live',
                                              style: TextStyle(
                                                color: Colors.white,
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13.5,
                                              ),
                                            ),
                                            Text(
                                              'Status Cuti Tim',
                                              style: TextStyle(color: Colors.white70, fontSize: 11),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                            if (isAdmin) ...[
                              const SizedBox(width: 12),
                              Expanded(
                                child: InkWell(
                                  onTap: () {
                                    Navigator.push(
                                      context,
                                      MaterialPageRoute(builder: (_) => const EmployeeListScreen()),
                                    ).then((_) => _loadStats());
                                  },
                                  borderRadius: BorderRadius.circular(14),
                                  child: Container(
                                    padding: const EdgeInsets.all(14),
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        colors: [Color(0xFFE11D48), Color(0xFFF43F5E)],
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                      ),
                                      borderRadius: BorderRadius.circular(14),
                                      boxShadow: [
                                        BoxShadow(
                                          color: const Color(0xFFE11D48).withOpacity(0.2),
                                          blurRadius: 10,
                                          offset: const Offset(0, 4),
                                        ),
                                      ],
                                    ),
                                    child: const Row(
                                      children: [
                                        Icon(CupertinoIcons.person_3_fill, color: Colors.white, size: 24),
                                        SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'Data Karyawan',
                                                style: TextStyle(
                                                  color: Colors.white,
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 13.5,
                                                ),
                                              ),
                                              Text(
                                                'Daftar & Tambah',
                                                style: TextStyle(color: Colors.white70, fontSize: 11),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                        const SizedBox(height: 12),

                        // Quick Tutorial & PDF Guide Banner
                        InkWell(
                          onTap: () => AboutScreen.showGuideSelectionModal(context),
                          borderRadius: BorderRadius.circular(14),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                            decoration: BoxDecoration(
                              color: cardBg,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(color: borderCol),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0284C7).withOpacity(0.12),
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: const Icon(CupertinoIcons.book_fill, color: Color(0xFF0284C7), size: 20),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Buku Panduan & Unduh PDF',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 13.5,
                                          color: textHead,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Tutorial alur pengajuan cuti (4 PDF Per Role)',
                                        style: TextStyle(fontSize: 11.5, color: textSub),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0284C7),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Icon(CupertinoIcons.arrow_down_doc_fill, color: Colors.white, size: 14),
                                      SizedBox(width: 4),
                                      Text('Unduh PDF', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // 3. Quota Balances
                        Text(
                          'Ringkasan Kuota Cuti',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Expanded(
                              child: _buildQuotaCard(
                                title: 'Sisa Kuota Cuti',
                                value: '${_stats?.sisaCuti ?? user?.sisaCuti ?? 0}',
                                subtitle: 'Hari Tersedia',
                                color: isDark ? const Color(0xFF38BDF8) : AppTheme.primary,
                                icon: Icons.beach_access_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: _buildQuotaCard(
                                title: 'Cuti Terpakai',
                                value: '${_stats?.cutiTerpakai ?? user?.cutiTerpakai ?? 0}',
                                subtitle: 'Hari Diambil',
                                color: isDark ? const Color(0xFF2DD4BF) : AppTheme.secondary,
                                icon: Icons.event_busy_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // 4. Status Counters
                        Row(
                          children: [
                            Expanded(
                              child: _buildMiniCounter(
                                title: 'Menunggu',
                                count: _stats?.pendingCount ?? 0,
                                color: isDark ? const Color(0xFFFBBF24) : AppTheme.statusPending,
                                icon: Icons.hourglass_top_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: _buildMiniCounter(
                                title: 'Disetujui',
                                count: _stats?.approvedCount ?? 0,
                                color: isDark ? const Color(0xFF34D399) : AppTheme.statusApproved,
                                icon: Icons.check_circle_outline_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: _buildMiniCounter(
                                title: 'Ditolak',
                                count: _stats?.rejectedCount ?? 0,
                                color: isDark ? const Color(0xFFF87171) : AppTheme.statusRejected,
                                icon: Icons.cancel_outlined,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),

                        // Quick Approval Banner for Approvers
                        if ((user?.canApprove ?? false) && (_stats?.pendingApprovalsCount ?? 0) > 0) ...[
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: isDark ? const Color(0xFF451A03) : AppTheme.statusPendingBg,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppTheme.statusPending.withOpacity(0.4)),
                            ),
                            child: Row(
                              children: [
                                Icon(Icons.notifications_active_rounded, color: isDark ? const Color(0xFFFBBF24) : AppTheme.statusPending, size: 28),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Ada ${_stats?.pendingApprovalsCount} Pengajuan Menunggu!',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 14,
                                          color: textHead,
                                        ),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Perlu tindakan persetujuan hierarki dari Anda.',
                                        style: TextStyle(fontSize: 12, color: textSub),
                                      ),
                                    ],
                                  ),
                                ),
                                ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppTheme.statusPending,
                                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                  ),
                                  onPressed: () {
                                    if (widget.onNavigateToTab != null) {
                                      widget.onNavigateToTab!(3); // Navigate to approval tab
                                    }
                                  },
                                  child: const Text('Periksa', style: TextStyle(fontSize: 12, color: Colors.white)),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 24),
                        ],

                        // 5. Recent Leaves
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Pengajuan Terakhir Saya',
                              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                            ),
                            TextButton(
                              onPressed: () {
                                if (widget.onNavigateToTab != null) {
                                  widget.onNavigateToTab!(2); // Navigate to history tab
                                }
                              },
                              child: Text(
                                'Lihat Semua',
                                style: TextStyle(fontSize: 13, color: isDark ? const Color(0xFF38BDF8) : AppTheme.primary),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),

                        if (_stats?.recentLeaves.isEmpty ?? true)
                          Container(
                            padding: const EdgeInsets.all(24),
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: cardBg,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: borderCol),
                            ),
                            child: Column(
                              children: [
                                Icon(Icons.inbox_outlined, size: 40, color: textSub),
                                const SizedBox(height: 8),
                                Text(
                                  'Belum ada riwayat pengajuan cuti.',
                                  style: TextStyle(color: textSub, fontSize: 13),
                                ),
                              ],
                            ),
                          )
                        else
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _stats!.recentLeaves.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 10),
                            itemBuilder: (ctx, idx) {
                              final leave = _stats!.recentLeaves[idx];
                              return _buildLeaveCard(leave, cardBg, borderCol, textHead, textSub, isDark);
                            },
                          ),
                      ],
                    ),
                  ),
      ),
    );
  }

  Widget _buildQuotaCard({
    required String title,
    required String value,
    required String subtitle,
    required Color color,
    required IconData icon,
    required Color cardBg,
    required Color borderCol,
    required Color textSub,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderCol),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: color, size: 24),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(fontSize: 12, color: textSub),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w800,
                    color: color,
                  ),
                ),
                Text(
                  subtitle,
                  style: TextStyle(fontSize: 11, color: textSub.withOpacity(0.8)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMiniCounter({
    required String title,
    required int count,
    required Color color,
    required IconData icon,
    required Color cardBg,
    required Color borderCol,
    required Color textSub,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: borderCol),
      ),
      child: Column(
        children: [
          Icon(icon, size: 18, color: color),
          const SizedBox(height: 4),
          Text(
            '$count',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          Text(
            title,
            style: TextStyle(fontSize: 11, color: textSub),
          ),
        ],
      ),
    );
  }

  Widget _buildLeaveCard(
    LeaveModel leave,
    Color cardBg,
    Color borderCol,
    Color textHead,
    Color textSub,
    bool isDark,
  ) {
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return InkWell(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (_) => LeaveDetailScreen(leaveId: leave.id),
          ),
        ).then((_) => _loadStats());
      },
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: cardBg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: borderCol),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: primaryAccent.withOpacity(0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(Icons.event_note_rounded, color: primaryAccent, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          leave.namaCuti ?? 'Cuti',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: textHead,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      _buildStatusBadge(leave),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${leave.tanggalMulai} s/d ${leave.tanggalSelesai} (${leave.totalHari} hari)',
                    style: TextStyle(fontSize: 12, color: textSub),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    leave.alasan,
                    style: TextStyle(fontSize: 12, color: textSub.withOpacity(0.8)),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
