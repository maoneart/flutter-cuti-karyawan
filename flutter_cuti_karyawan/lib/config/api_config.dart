import 'package:flutter/foundation.dart';

class ApiConfig {
  static const String serverIp = '172.16.0.107';

  static String get defaultBaseUrl {
    if (kIsWeb) {
      return 'http://localhost/Cuti_Karyawan/api';
    } else if (defaultTargetPlatform == TargetPlatform.android) {
      return 'http://$serverIp/Cuti_Karyawan/api';
    } else {
      return 'http://localhost/Cuti_Karyawan/api';
    }
  }

  // Active Base URL
  static String _baseUrl = defaultBaseUrl;

  static String get baseUrl => _baseUrl;
  static set baseUrl(String url) {
    _baseUrl = url.trim().replaceAll(RegExp(r'/+$'), '');
  }

  // Endpoints
  static String get login => '$baseUrl/auth/login.php';
  static String get profile => '$baseUrl/auth/profile.php';
  static String get changePassword => '$baseUrl/auth/change_password.php';
  static String get dashboardStats => '$baseUrl/dashboard/stats.php';
  static String get notifications => '$baseUrl/notifications/list.php';
  
  static String get leaveTypes => '$baseUrl/leaves/types.php';
  static String get leavesList => '$baseUrl/leaves/list.php';
  static String get leaveCreate => '$baseUrl/leaves/create.php';
  static String get leaveDetail => '$baseUrl/leaves/detail.php';
  static String get leaveCancel => '$baseUrl/leaves/cancel.php';

  static String get approvalsList => '$baseUrl/approvals/list.php';
  static String get approvalAction => '$baseUrl/approvals/action.php';

  static String get publicBoard => '$baseUrl/public/board.php';

  static String get employeeOptions => '$baseUrl/employees/options.php';
  static String get employeeCreate => '$baseUrl/employees/create.php';
  static String get employeesList => '$baseUrl/employees/list.php';
}
