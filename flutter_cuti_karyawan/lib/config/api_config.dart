import 'package:flutter/foundation.dart';

class ApiConfig {
  /// Ubah baseUrl sesuai environment Anda:
  /// - Untuk Flutter Web di Laragon: http://localhost/Cuti_Karyawan/api
  /// - Untuk Android Emulator: http://10.0.2.2/Cuti_Karyawan/api
  /// - Untuk HP Fisik (WiFi sama): http://[IP_KOMPUTER_ANDA]/Cuti_Karyawan/api (misal: http://192.168.1.10/Cuti_Karyawan/api)
  /// - Untuk Production Hosting: https://domainanda.com/api
  
  static String get defaultBaseUrl {
    if (kIsWeb) {
      return 'http://localhost/Cuti_Karyawan/api';
    } else if (defaultTargetPlatform == TargetPlatform.android) {
      // 10.0.2.2 adalah localhost untuk Android Emulator
      return 'http://10.0.2.2/Cuti_Karyawan/api';
    } else {
      return 'http://localhost/Cuti_Karyawan/api';
    }
  }

  // Active Base URL (bisa diganti dari setting/storage di runtime)
  static String baseUrl = defaultBaseUrl;

  // Endpoints
  static String get login => '$baseUrl/auth/login.php';
  static String get profile => '$baseUrl/auth/profile.php';
  static String get dashboardStats => '$baseUrl/dashboard/stats.php';
  
  static String get leaveTypes => '$baseUrl/leaves/types.php';
  static String get leavesList => '$baseUrl/leaves/list.php';
  static String get leaveCreate => '$baseUrl/leaves/create.php';
  static String get leaveDetail => '$baseUrl/leaves/detail.php';
  static String get leaveCancel => '$baseUrl/leaves/cancel.php';

  static String get approvalsList => '$baseUrl/approvals/list.php';
  static String get approvalAction => '$baseUrl/approvals/action.php';

  static String get publicBoard => '$baseUrl/public/board.php';
}
