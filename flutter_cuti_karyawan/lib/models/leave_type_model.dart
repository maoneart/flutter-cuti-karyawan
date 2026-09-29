class LeaveTypeModel {
  final int id;
  final String namaCuti;
  final String? kategori;
  final String kode;
  final String? deskripsi;
  final bool potongKuota;
  final double maxHariDefault;
  final bool butuhLampiran;
  final bool eligible;
  final String? eligibilityMessage;

  LeaveTypeModel({
    required this.id,
    required this.namaCuti,
    this.kategori,
    required this.kode,
    this.deskripsi,
    required this.potongKuota,
    required this.maxHariDefault,
    required this.butuhLampiran,
    this.eligible = true,
    this.eligibilityMessage,
  });

  bool get isCuti {
    final k = kode.toUpperCase();
    final n = namaCuti.toLowerCase();
    if (k.startsWith('CT') || k.startsWith('CK') || k == 'CH' || k == 'CML' || k == 'DISP-NGR' || n.contains('cuti') || n.contains('nikah') || n.contains('khitan') || n.contains('haji') || n.contains('duka')) {
      return true;
    }
    return false;
  }

  bool get isIzin => !isCuti;

  factory LeaveTypeModel.fromJson(Map<String, dynamic> json) {
    return LeaveTypeModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      namaCuti: json['nama_cuti']?.toString() ?? '',
      kategori: json['kategori']?.toString(),
      kode: json['kode']?.toString() ?? '',
      deskripsi: json['deskripsi']?.toString(),
      potongKuota: (json['potong_kuota']?.toString() == '1' || json['potong_kuota'] == true),
      maxHariDefault: double.tryParse(json['max_hari_default']?.toString() ?? '12') ?? 12.0,
      butuhLampiran: (json['butuh_lampiran']?.toString() == '1' || json['butuh_lampiran'] == true),
      eligible: json['eligible'] != false,
      eligibilityMessage: json['eligibility_message']?.toString(),
    );
  }
}
