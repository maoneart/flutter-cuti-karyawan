import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/api_service.dart';
import 'leave_detail_screen.dart';

class NotificationScreen extends StatefulWidget {
  const NotificationScreen({super.key});

  @override
  State<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends State<NotificationScreen> {
  List<dynamic> _notifications = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getNotifications();

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _notifications = res.data!['notifications'] as List<dynamic>? ?? [];
        _isLoading = false;
      });
      // Mark as read in background
      ApiService.markNotificationsRead();
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  Widget _buildNotificationIcon(String type) {
    Color bg;
    Color iconColor;
    IconData icon;

    switch (type) {
      case 'leave_approved':
        bg = AppTheme.statusApprovedBg;
        iconColor = AppTheme.statusApproved;
        icon = CupertinoIcons.checkmark_circle_fill;
        break;
      case 'leave_rejected':
        bg = AppTheme.statusRejectedBg;
        iconColor = AppTheme.statusRejected;
        icon = CupertinoIcons.xmark_circle_fill;
        break;
      case 'approval_required':
      default:
        bg = AppTheme.statusPendingBg;
        iconColor = AppTheme.statusPending;
        icon = CupertinoIcons.bell_fill;
        break;
    }

    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: bg,
        shape: BoxShape.circle,
      ),
      child: Icon(icon, color: iconColor, size: 20),
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
        leading: IconButton(
          icon: Icon(CupertinoIcons.back, color: isDark ? Colors.white : AppTheme.primary),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text('Notifikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: textHead)),
        centerTitle: true,
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        elevation: 0.5,
        actions: [
          IconButton(
            icon: Icon(CupertinoIcons.arrow_clockwise, size: 20, color: textHead),
            onPressed: _loadNotifications,
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadNotifications,
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
                        ElevatedButton(onPressed: _loadNotifications, child: const Text('Coba Lagi')),
                      ],
                    ),
                  )
                : _notifications.isEmpty
                    ? Center(
                        child: SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(20),
                                decoration: BoxDecoration(
                                  color: primaryAccent.withOpacity(0.12),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(CupertinoIcons.bell_slash, size: 48, color: primaryAccent),
                              ),
                              const SizedBox(height: 16),
                              Text('Tidak Ada Notifikasi Baru', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: textHead)),
                              const SizedBox(height: 4),
                              Text('Semua status pengajuan cuti Anda sudah terbaru.', style: TextStyle(fontSize: 13, color: textSub)),
                            ],
                          ),
                        ),
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemCount: _notifications.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 10),
                        itemBuilder: (ctx, idx) {
                          final item = _notifications[idx];
                          final leaveId = int.tryParse(item['leave_id']?.toString() ?? '0') ?? 0;
                          final isRead = item['read'] == true;

                          Color tileBg;
                          if (isDark) {
                            tileBg = isRead ? const Color(0xFF1E293B) : const Color(0xFF1E3A5F);
                          } else {
                            tileBg = isRead ? Colors.white : const Color(0xFFF0F7FF);
                          }

                          return InkWell(
                            onTap: () {
                              if (leaveId > 0) {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => LeaveDetailScreen(leaveId: leaveId),
                                  ),
                                );
                              }
                            },
                            borderRadius: BorderRadius.circular(12),
                            child: Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: tileBg,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: isRead ? borderCol : primaryAccent.withOpacity(0.4),
                                  width: isRead ? 1 : 1.5,
                                ),
                              ),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _buildNotificationIcon(item['type']?.toString() ?? ''),
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
                                                item['title']?.toString() ?? 'Pemberitahuan',
                                                style: TextStyle(
                                                  fontWeight: isRead ? FontWeight.bold : FontWeight.w800,
                                                  fontSize: 14,
                                                  color: textHead,
                                                ),
                                              ),
                                            ),
                                            if (!isRead)
                                              Container(
                                                width: 8,
                                                height: 8,
                                                decoration: const BoxDecoration(
                                                  color: Colors.red,
                                                  shape: BoxShape.circle,
                                                ),
                                              ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          item['message']?.toString() ?? '',
                                          style: TextStyle(fontSize: 12, color: textSub),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          item['time']?.toString() ?? '',
                                          style: TextStyle(fontSize: 10, color: isDark ? const Color(0xFF64748B) : AppTheme.textMuted),
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
    );
  }
}
