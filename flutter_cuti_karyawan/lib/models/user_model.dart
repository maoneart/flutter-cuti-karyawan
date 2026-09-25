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
  final String? agama;
  final String? statusPernikahan;
  final String? noHp;
  final String? alamat;
  final String? statusAktif;
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
    this.agama,
    this.statusPernikahan,
    this.noHp,
    this.alamat,
    this.statusAktif,
    this.foto,
  });

  int get deptId => departemenId;
  bool get isAdmin => role == 'admin' || role == 'superadmin' || role == 'hrd' || levelHierarki >= 7;
  bool get isManager => role == 'manager' || (levelHierarki >= 5 && levelHierarki <= 6);
  bool get isSupervisor => role == 'supervisor' || role == 'leader' || (levelHierarki >= 3 && levelHierarki <= 4);
  bool get canApprove => isAdmin || isManager || isSupervisor || role == 'atasan';

  /// Super Admin: Hanya superadmin atau level >= 8 (HRD role 'hrd' level 7 BUKAN superadmin)
  bool get isSuperAdmin {
    final r = role.toLowerCase().trim();
    if (r == 'hrd') return false;
    return r == 'superadmin' || r == 'admin' || levelHierarki >= 8;
  }

  UserModel copyWith({
    int? id,
    String? nik,
    String? namaLengkap,
    String? email,
    String? role,
    int? departemenId,
    String? namaDept,
    String? kodeDept,
    int? jabatanId,
    String? namaJabatan,
    int? levelHierarki,
    String? tanggalMasuk,
    int? kuotaCuti,
    int? cutiTerpakai,
    int? sisaCuti,
    String? jenisKelamin,
    String? agama,
    String? statusPernikahan,
    String? noHp,
    String? alamat,
    String? statusAktif,
    String? foto,
  }) {
    return UserModel(
      id: id ?? this.id,
      nik: nik ?? this.nik,
      namaLengkap: namaLengkap ?? this.namaLengkap,
      email: email ?? this.email,
      role: role ?? this.role,
      departemenId: departemenId ?? this.departemenId,
      namaDept: namaDept ?? this.namaDept,
      kodeDept: kodeDept ?? this.kodeDept,
      jabatanId: jabatanId ?? this.jabatanId,
      namaJabatan: namaJabatan ?? this.namaJabatan,
      levelHierarki: levelHierarki ?? this.levelHierarki,
      tanggalMasuk: tanggalMasuk ?? this.tanggalMasuk,
      kuotaCuti: kuotaCuti ?? this.kuotaCuti,
      cutiTerpakai: cutiTerpakai ?? this.cutiTerpakai,
      sisaCuti: sisaCuti ?? this.sisaCuti,
      jenisKelamin: jenisKelamin ?? this.jenisKelamin,
      agama: agama ?? this.agama,
      statusPernikahan: statusPernikahan ?? this.statusPernikahan,
      noHp: noHp ?? this.noHp,
      alamat: alamat ?? this.alamat,
      statusAktif: statusAktif ?? this.statusAktif,
      foto: foto ?? this.foto,
    );
  }

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
      agama: json['agama']?.toString(),
      statusPernikahan: json['status_pernikahan']?.toString(),
      noHp: json['no_hp']?.toString(),
      alamat: json['alamat']?.toString(),
      statusAktif: json['status_aktif']?.toString() ?? 'Aktif',
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
      'agama': agama,
      'status_pernikahan': statusPernikahan,
      'no_hp': noHp,
      'alamat': alamat,
      'status_aktif': statusAktif,
      'foto': foto,
    };
  }
}
