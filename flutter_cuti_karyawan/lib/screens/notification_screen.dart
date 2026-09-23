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
    return Scaffold(
      backgroundColor: const Color(0xFFF2F2F7),
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(CupertinoIcons.back, color: AppTheme.primary),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text('Notifikasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0.5,
        actions: [
          IconButton(
            icon: const Icon(CupertinoIcons.arrow_clockwise, size: 20),
            onPressed: _loadNotifications,
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadNotifications,
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
                                  color: AppTheme.primary.withOpacity(0.08),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(CupertinoIcons.bell_slash, size: 48, color: AppTheme.primary),
                              ),
                              const SizedBox(height: 16),
                              const Text('Tidak Ada Notifikasi Baru', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                              const SizedBox(height: 4),
                              const Text('Semua status pengajuan cuti Anda sudah terbaru.', style: TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
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
                                color: isRead ? Colors.white : const Color(0xFFF0F7FF),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: isRead ? const Color(0xFFE2E8F0) : AppTheme.primaryLight.withOpacity(0.3),
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
                                                  color: AppTheme.textPrimary,
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
                                          style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                                        ),
                                        const SizedBox(height: 6),
                                        Text(
                                          item['time']?.toString() ?? '',
                                          style: const TextStyle(fontSize: 10, color: AppTheme.textMuted),
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
