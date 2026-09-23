import 'leave_model.dart';

class DashboardStatsModel {
  final int sisaCuti;
  final int kuotaCuti;
  final int cutiTerpakai;
  final int totalPengajuan;
  final int pendingCount;
  final int approvedCount;
  final int rejectedCount;
  final int cancelledCount;
  final int pendingApprovalsCount;
  final int todayOnLeaveCount;
  final List<LeaveModel> recentLeaves;

  DashboardStatsModel({
    required this.sisaCuti,
    required this.kuotaCuti,
    required this.cutiTerpakai,
    required this.totalPengajuan,
    required this.pendingCount,
    required this.approvedCount,
    required this.rejectedCount,
    required this.cancelledCount,
    required this.pendingApprovalsCount,
    required this.todayOnLeaveCount,
    required this.recentLeaves,
  });

  factory DashboardStatsModel.fromJson(Map<String, dynamic> json) {
    final counters = json['counters'] as Map<String, dynamic>? ?? {};
    final recentLeavesList = (json['recent_leaves'] as List<dynamic>? ?? [])
        .map((e) => LeaveModel.fromJson(e as Map<String, dynamic>))
        .toList();

    return DashboardStatsModel(
      sisaCuti: int.tryParse(counters['sisa_cuti']?.toString() ?? '0') ?? 0,
      kuotaCuti: int.tryParse(counters['kuota_cuti']?.toString() ?? '0') ?? 0,
      cutiTerpakai: int.tryParse(counters['cuti_terpakai']?.toString() ?? '0') ?? 0,
      totalPengajuan: int.tryParse(counters['total_pengajuan']?.toString() ?? '0') ?? 0,
      pendingCount: int.tryParse(counters['pending_count']?.toString() ?? '0') ?? 0,
      approvedCount: int.tryParse(counters['approved_count']?.toString() ?? '0') ?? 0,
      rejectedCount: int.tryParse(counters['rejected_count']?.toString() ?? '0') ?? 0,
      cancelledCount: int.tryParse(counters['cancelled_count']?.toString() ?? '0') ?? 0,
      pendingApprovalsCount: int.tryParse(counters['pending_approvals_count']?.toString() ?? '0') ?? 0,
      todayOnLeaveCount: int.tryParse(counters['today_on_leave_count']?.toString() ?? '0') ?? 0,
      recentLeaves: recentLeavesList,
    );
  }
}
