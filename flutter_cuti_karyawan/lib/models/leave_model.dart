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
  final int? approvedBy;
  final String? approverName;
  final String? approvedAt;
  final String? rejectionReason;
  final String? catatanAtasan;
  final String createdAt;
  
  // Joins
  final String? namaLengkap;
  final String? nik;
  final String? namaDept;
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
    this.approvedBy,
    this.approverName,
    this.approvedAt,
    this.rejectionReason,
    this.catatanAtasan,
    required this.createdAt,
    this.namaLengkap,
    this.nik,
    this.namaDept,
    this.namaCuti,
    this.kodeCuti,
    this.potongKuota = true,
    this.butuhLampiran = false,
  });

  bool get isPending => status.toLowerCase() == 'pending';
  bool get isApproved => status.toLowerCase() == 'approved';
  bool get isRejected => status.toLowerCase() == 'rejected';
  bool get isCancelled => status.toLowerCase() == 'cancelled';

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
      approvedBy: json['approved_by'] != null ? int.tryParse(json['approved_by'].toString()) : null,
      approverName: json['approver_name']?.toString(),
      approvedAt: json['approved_at']?.toString(),
      rejectionReason: json['rejection_reason']?.toString(),
      catatanAtasan: json['catatan_atasan']?.toString(),
      createdAt: json['created_at']?.toString() ?? '',
      namaLengkap: json['nama_lengkap']?.toString(),
      nik: json['nik']?.toString(),
      namaDept: json['nama_dept']?.toString(),
      namaCuti: json['nama_cuti']?.toString(),
      kodeCuti: json['kode_cuti']?.toString(),
      potongKuota: (json['potong_kuota']?.toString() == '1' || json['potong_kuota'] == true),
      butuhLampiran: (json['butuh_lampiran']?.toString() == '1' || json['butuh_lampiran'] == true),
    );
  }
}
