import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

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

  Widget _buildStatusBanner(String status) {
    Color bg;
    Color color;
    IconData icon;
    String title;
    String desc;

    switch (status.toLowerCase()) {
      case 'approved':
        bg = AppTheme.statusApprovedBg;
        color = AppTheme.statusApproved;
        icon = Icons.check_circle_rounded;
        title = 'Disetujui';
        desc = 'Pengajuan cuti telah disetujui oleh atasan.';
        break;
      case 'rejected':
        bg = AppTheme.statusRejectedBg;
        color = AppTheme.statusRejected;
        icon = Icons.cancel_rounded;
        title = 'Ditolak';
        desc = 'Pengajuan cuti ditolak oleh atasan.';
        break;
      case 'cancelled':
        bg = AppTheme.statusCancelledBg;
        color = AppTheme.statusCancelled;
        icon = Icons.remove_circle_outline_rounded;
        title = 'Dibatalkan';
        desc = 'Pengajuan cuti ini telah dibatalkan.';
        break;
      default:
        bg = AppTheme.statusPendingBg;
        color = AppTheme.statusPending;
        icon = Icons.hourglass_top_rounded;
        title = 'Menunggu Persetujuan';
        desc = 'Pengajuan cuti sedang dalam antrean review atasan.';
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
          Icon(icon, color: color, size: 32),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: color)),
                const SizedBox(height: 2),
                Text(desc, style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoRow(String label, String value, {IconData? icon}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 18, color: AppTheme.textMuted),
            const SizedBox(width: 10),
          ],
          SizedBox(
            width: 130,
            child: Text(label, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: AppTheme.textPrimary),
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

    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Detail Pengajuan Cuti'),
      ),
      body: _isLoading
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
                      ElevatedButton(onPressed: _loadDetail, child: const Text('Coba Lagi')),
                    ],
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 600),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Status Banner
                          _buildStatusBanner(_leave!.status),
                          const SizedBox(height: 20),

                          // Main Info Card
                          Container(
                            padding: const EdgeInsets.all(20),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Informasi Pengajuan',
                                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                                ),
                                const Divider(height: 24, color: Color(0xFFE2E8F0)),
                                _buildInfoRow('Nomor Surat', _leave!.nomorSurat, icon: Icons.tag),
                                _buildInfoRow('Nama Pemohon', _leave!.namaLengkap ?? '-', icon: Icons.person_outline),
                                _buildInfoRow('Departemen', _leave!.namaDept ?? '-', icon: Icons.apartment),
                                _buildInfoRow('Jenis Cuti', _leave!.namaCuti ?? '-', icon: Icons.category_outlined),
                                _buildInfoRow(
                                  'Tgl Pelaksanaan',
                                  '${_leave!.tanggalMulai} s/d ${_leave!.tanggalSelesai}',
                                  icon: Icons.date_range,
                                ),
                                _buildInfoRow('Total Durasi', '${_leave!.totalHari} Hari Kerja', icon: Icons.timelapse),
                                _buildInfoRow('Potong Kuota', _leave!.potongKuota ? 'Ya (Potong Kuota)' : 'Tidak (Hak Khusus/Izin)', icon: Icons.pie_chart_outline),
                                const Divider(height: 24, color: Color(0xFFE2E8F0)),
                                const Text('Alasan Cuti:', style: TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
                                const SizedBox(height: 6),
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: AppTheme.surfaceVariant,
                                    borderRadius: BorderRadius.circular(10),
                                  ),
                                  child: Text(_leave!.alasan, style: const TextStyle(fontSize: 13, color: AppTheme.textPrimary)),
                                ),
                                if (_leave!.alamatSelamaCuti != null && _leave!.alamatSelamaCuti!.isNotEmpty) ...[
                                  const SizedBox(height: 12),
                                  _buildInfoRow('Alamat Selama Cuti', _leave!.alamatSelamaCuti!, icon: Icons.location_on_outlined),
                                ],
                                if (_leave!.kontakDarurat != null && _leave!.kontakDarurat!.isNotEmpty) ...[
                                  _buildInfoRow('Kontak Darurat', _leave!.kontakDarurat!, icon: Icons.phone_outlined),
                                ],
                              ],
                            ),
                          ),
                          const SizedBox(height: 20),

                          // Approval / Feedback Notes
                          if (_leave!.approverName != null || _leave!.catatanAtasan != null || _leave!.rejectionReason != null) ...[
                            Container(
                              padding: const EdgeInsets.all(20),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(16),
                                border: Border.all(color: const Color(0xFFE2E8F0)),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Catatan Persetujuan / Review',
                                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                                  ),
                                  const Divider(height: 24, color: Color(0xFFE2E8F0)),
                                  if (_leave!.approverName != null)
                                    _buildInfoRow('Diverifikasi Oleh', _leave!.approverName!, icon: Icons.verified_user_outlined),
                                  if (_leave!.approvedAt != null)
                                    _buildInfoRow('Waktu Keputusan', _leave!.approvedAt!, icon: Icons.access_time),
                                  if (_leave!.rejectionReason != null && _leave!.rejectionReason!.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    const Text('Alasan Penolakan:', style: TextStyle(fontSize: 13, color: AppTheme.statusRejected, fontWeight: FontWeight.bold)),
                                    const SizedBox(height: 4),
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: AppTheme.statusRejectedBg,
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: Text(_leave!.rejectionReason!, style: const TextStyle(fontSize: 13, color: AppTheme.statusRejected)),
                                    ),
                                  ],
                                  if (_leave!.catatanAtasan != null && _leave!.catatanAtasan!.isNotEmpty) ...[
                                    const SizedBox(height: 8),
                                    const Text('Catatan Atasan:', style: TextStyle(fontSize: 13, color: AppTheme.textSecondary, fontWeight: FontWeight.bold)),
                                    const SizedBox(height: 4),
                                    Container(
                                      width: double.infinity,
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: AppTheme.surfaceVariant,
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: Text(_leave!.catatanAtasan!, style: const TextStyle(fontSize: 13, color: AppTheme.textPrimary)),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                            const SizedBox(height: 20),
                          ],

                          // Cancel Button (if pending and user is owner)
                          if (isOwner && _leave!.isPending) ...[
                            OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: AppTheme.statusRejected,
                                side: const BorderSide(color: AppTheme.statusRejected),
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              ),
                              onPressed: _isCancelling ? null : _handleCancel,
                              icon: _isCancelling
                                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                                  : const Icon(Icons.cancel_outlined, size: 18),
                              label: const Text('Batalkan Pengajuan Ini', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),
                ),
    );
  }
}
