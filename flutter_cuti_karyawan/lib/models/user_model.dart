class UserModel {
  final int id;
  final String nik;
  final String namaLengkap;
  final String email;
  final String role;
  final int departemenId;
  final String namaDept;
  final String? kodeDept;
  final int jabatanId;
  final String namaJabatan;
  final int levelHierarki;
  final String? tanggalMasuk;
  final int kuotaCuti;
  final int cutiTerpakai;
  final int sisaCuti;
  final String? jenisKelamin;
  final String? noHp;
  final String? alamat;
  final String? foto;

  UserModel({
    required this.id,
    required this.nik,
    required this.namaLengkap,
    required this.email,
    required this.role,
    required this.departemenId,
    required this.namaDept,
    this.kodeDept,
    required this.jabatanId,
    required this.namaJabatan,
    required this.levelHierarki,
    this.tanggalMasuk,
    required this.kuotaCuti,
    required this.cutiTerpakai,
    required this.sisaCuti,
    this.jenisKelamin,
    this.noHp,
    this.alamat,
    this.foto,
  });

  bool get isAdmin => role == 'admin' || role == 'superadmin' || role == 'hrd' || levelHierarki >= 7;
  bool get isManager => role == 'manager' || (levelHierarki >= 5 && levelHierarki <= 6);
  bool get isSupervisor => role == 'supervisor' || role == 'leader' || (levelHierarki >= 3 && levelHierarki <= 4);
  bool get canApprove => isAdmin || isManager || isSupervisor || role == 'atasan';

  /// Kalkulasi masa/lama bekerja dari tanggal masuk
  String get lamaBekerja {
    if (tanggalMasuk == null || tanggalMasuk!.isEmpty) {
      return '-';
    }
    try {
      final masuk = DateTime.parse(tanggalMasuk!);
      final now = DateTime.now();
      
      int years = now.year - masuk.year;
      int months = now.month - masuk.month;
      
      if (now.day < masuk.day) {
        months--;
      }
      if (months < 0) {
        years--;
        months += 12;
      }
      
      if (years < 0) {
        return 'Baru Bergabung';
      }
      
      if (years > 0 && months > 0) {
        return '$years Tahun $months Bulan';
      } else if (years > 0) {
        return '$years Tahun';
      } else if (months > 0) {
        return '$months Bulan';
      } else {
        return '< 1 Bulan';
      }
    } catch (e) {
      return '-';
    }
  }

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      nik: json['nik']?.toString() ?? '',
      namaLengkap: json['nama_lengkap']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      role: json['role']?.toString() ?? 'operator',
      departemenId: int.tryParse(json['departemen_id']?.toString() ?? '0') ?? 0,
      namaDept: json['nama_dept']?.toString() ?? '-',
      kodeDept: json['kode_dept']?.toString(),
      jabatanId: int.tryParse(json['jabatan_id']?.toString() ?? '0') ?? 0,
      namaJabatan: json['nama_jabatan']?.toString() ?? '-',
      levelHierarki: int.tryParse(json['level_hierarki']?.toString() ?? '1') ?? 1,
      tanggalMasuk: json['tanggal_masuk']?.toString(),
      kuotaCuti: int.tryParse(json['kuota_cuti']?.toString() ?? '0') ?? 0,
      cutiTerpakai: int.tryParse(json['cuti_terpakai']?.toString() ?? '0') ?? 0,
      sisaCuti: int.tryParse(json['sisa_cuti']?.toString() ?? '0') ?? 0,
      jenisKelamin: json['jenis_kelamin']?.toString(),
      noHp: json['no_hp']?.toString(),
      alamat: json['alamat']?.toString(),
      foto: json['foto']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nik': nik,
      'nama_lengkap': namaLengkap,
      'email': email,
      'role': role,
      'departemen_id': departemenId,
      'nama_dept': namaDept,
      'kode_dept': kodeDept,
      'jabatan_id': jabatanId,
      'nama_jabatan': namaJabatan,
      'level_hierarki': levelHierarki,
      'tanggal_masuk': tanggalMasuk,
      'kuota_cuti': kuotaCuti,
      'cuti_terpakai': cutiTerpakai,
      'sisa_cuti': sisaCuti,
      'jenis_kelamin': jenisKelamin,
      'no_hp': noHp,
      'alamat': alamat,
      'foto': foto,
    };
  }
}
