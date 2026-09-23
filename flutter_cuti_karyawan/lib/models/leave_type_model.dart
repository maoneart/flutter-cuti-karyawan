class LeaveTypeModel {
  final int id;
  final String namaCuti;
  final String kode;
  final String? deskripsi;
  final bool potongKuota;
  final int maxHariDefault;
  final bool butuhLampiran;
  final bool eligible;
  final String? eligibilityMessage;

  LeaveTypeModel({
    required this.id,
    required this.namaCuti,
    required this.kode,
    this.deskripsi,
    required this.potongKuota,
    required this.maxHariDefault,
    required this.butuhLampiran,
    this.eligible = true,
    this.eligibilityMessage,
  });

  factory LeaveTypeModel.fromJson(Map<String, dynamic> json) {
    return LeaveTypeModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      namaCuti: json['nama_cuti']?.toString() ?? '',
      kode: json['kode']?.toString() ?? '',
      deskripsi: json['deskripsi']?.toString(),
      potongKuota: (json['potong_kuota']?.toString() == '1' || json['potong_kuota'] == true),
      maxHariDefault: int.tryParse(json['max_hari_default']?.toString() ?? '12') ?? 12,
      butuhLampiran: (json['butuh_lampiran']?.toString() == '1' || json['butuh_lampiran'] == true),
      eligible: json['eligible'] != false,
      eligibilityMessage: json['eligibility_message']?.toString(),
    );
  }
}
