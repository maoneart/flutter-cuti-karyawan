class LeaveModel {
  final int id;
  final String nomorSurat;
  final int employeeId;
  final int leaveTypeId;
  final String tanggalMulai;
  final String tanggalSelesai;
  final int totalHari;
  final String alasan;
  final String? alamatSelamaCuti;
  final String? kontakDarurat;
  final String? attachment;
  final String? attachmentUrl;
  final String status; // pending, approved, rejected, cancelled
  final String approvalStep; // pending_spv, pending_manager, pending_hrd, approved, rejected
  final int? approvedBy;
  final String? approverName;
  final String? approvedAt;
  final String? rejectionReason;
  final String? catatanAtasan;
  final String createdAt;
  
  // 3-Tier Approval Flow Info
  final int? spvId;
  final String? spvName;
  final String? spvAt;
  final String? spvNotes;

  final int? managerId;
  final String? managerName;
  final String? managerAt;
  final String? managerNotes;

  final int? hrdId;
  final String? hrdName;
  final String? hrdAt;
  final String? hrdNotes;

  // Joins & Employee Info
  final String? namaLengkap;
  final String? nik;
  final int? departemenId;
  final String? namaDept;
  final String? namaJabatan;
  final int employeeLevel;
  final String? employeeRole;
  final String? namaCuti;
  final String? kodeCuti;
  final bool potongKuota;
  final bool butuhLampiran;

  LeaveModel({
    required this.id,
    required this.nomorSurat,
    required this.employeeId,
    required this.leaveTypeId,
    required this.tanggalMulai,
    required this.tanggalSelesai,
    required this.totalHari,
    required this.alasan,
    this.alamatSelamaCuti,
    this.kontakDarurat,
    this.attachment,
    this.attachmentUrl,
    required this.status,
    this.approvalStep = 'pending_spv',
    this.approvedBy,
    this.approverName,
    this.approvedAt,
    this.rejectionReason,
    this.catatanAtasan,
    required this.createdAt,
    this.spvId,
    this.spvName,
    this.spvAt,
    this.spvNotes,
    this.managerId,
    this.managerName,
    this.managerAt,
    this.managerNotes,
    this.hrdId,
    this.hrdName,
    this.hrdAt,
    this.hrdNotes,
    this.namaLengkap,
    this.nik,
    this.departemenId,
    this.namaDept,
    this.namaJabatan,
    this.employeeLevel = 1,
    this.employeeRole,
    this.namaCuti,
    this.kodeCuti,
    this.potongKuota = true,
    this.butuhLampiran = false,
  });

  bool get isPending => status.toLowerCase() == 'pending';
  bool get isApproved => status.toLowerCase() == 'approved';
  bool get isRejected => status.toLowerCase() == 'rejected';
  bool get isCancelled => status.toLowerCase() == 'cancelled';

  String get stepLabel {
    if (isApproved) return 'Disetujui Sepenuhnya';
    if (isRejected) return 'Ditolak';
    if (isCancelled) return 'Dibatalkan';
    
    switch (approvalStep.toLowerCase()) {
      case 'pending_spv':
        return 'Tahap 1: Review Spv / Leader';
      case 'pending_manager':
        return employeeLevel <= 2 ? 'Tahap 2: Review Manager' : 'Tahap 1: Review Manager';
      case 'pending_hrd':
        return employeeLevel <= 2 ? 'Tahap 3: Review HRD Final' : (employeeLevel <= 4 ? 'Tahap 2: Review HRD Final' : 'Tahap 1: Review HRD Final');
      default:
        return 'Menunggu Persetujuan';
    }
  }

  factory LeaveModel.fromJson(Map<String, dynamic> json) {
    return LeaveModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nomorSurat: json['nomor_surat']?.toString() ?? '',
      employeeId: int.tryParse(json['employee_id']?.toString() ?? '0') ?? 0,
      leaveTypeId: int.tryParse(json['leave_type_id']?.toString() ?? '0') ?? 0,
      tanggalMulai: json['tanggal_mulai']?.toString() ?? '',
      tanggalSelesai: json['tanggal_selesai']?.toString() ?? '',
      totalHari: int.tryParse(json['total_hari']?.toString() ?? '1') ?? 1,
      alasan: json['alasan']?.toString() ?? '',
      alamatSelamaCuti: json['alamat_selama_cuti']?.toString(),
      kontakDarurat: json['kontak_darurat']?.toString(),
      attachment: json['attachment']?.toString(),
      attachmentUrl: json['attachment_url']?.toString(),
      status: json['status']?.toString() ?? 'pending',
      approvalStep: json['approval_step']?.toString() ?? 'pending_spv',
      approvedBy: json['approved_by'] != null ? int.tryParse(json['approved_by'].toString()) : null,
      approverName: json['approver_name']?.toString(),
      approvedAt: json['approved_at']?.toString(),
      rejectionReason: json['rejection_reason']?.toString(),
      catatanAtasan: json['catatan_atasan']?.toString(),
      createdAt: json['created_at']?.toString() ?? '',
      spvId: json['spv_id'] != null ? int.tryParse(json['spv_id'].toString()) : null,
      spvName: json['spv_name']?.toString(),
      spvAt: json['spv_at']?.toString(),
      spvNotes: json['spv_notes']?.toString(),
      managerId: json['manager_id'] != null ? int.tryParse(json['manager_id'].toString()) : null,
      managerName: json['manager_name']?.toString(),
      managerAt: json['manager_at']?.toString(),
      managerNotes: json['manager_notes']?.toString(),
      hrdId: json['hrd_id'] != null ? int.tryParse(json['hrd_id'].toString()) : null,
      hrdName: json['hrd_name']?.toString(),
      hrdAt: json['hrd_at']?.toString(),
      hrdNotes: json['hrd_notes']?.toString(),
      namaLengkap: json['nama_lengkap']?.toString(),
      nik: json['nik']?.toString(),
      departemenId: json['departemen_id'] != null ? int.tryParse(json['departemen_id'].toString()) : null,
      namaDept: json['nama_dept']?.toString(),
      namaJabatan: json['nama_jabatan']?.toString(),
      employeeLevel: int.tryParse(json['employee_level']?.toString() ?? json['level_hierarki']?.toString() ?? '1') ?? 1,
      employeeRole: json['role']?.toString() ?? json['employee_role']?.toString(),
      namaCuti: json['nama_cuti']?.toString(),
      kodeCuti: json['kode_cuti']?.toString(),
      potongKuota: (json['potong_kuota']?.toString() == '1' || json['potong_kuota'] == true),
      butuhLampiran: (json['butuh_lampiran']?.toString() == '1' || json['butuh_lampiran'] == true),
    );
  }
}
