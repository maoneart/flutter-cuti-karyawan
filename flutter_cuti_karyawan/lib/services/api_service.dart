import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../models/user_model.dart';
import '../models/leave_model.dart';
import '../models/leave_type_model.dart';
import '../models/dashboard_stats_model.dart';
import 'auth_service.dart';

class ApiResponse<T> {
  final bool success;
  final String message;
  final T? data;
  final Map<String, dynamic>? errors;

  ApiResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });
}

class ApiService {
  static const Duration timeoutDuration = Duration(seconds: 15);

  static Map<String, String> _getHeaders({bool withAuth = true}) {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (withAuth && AuthService.token != null) {
      headers['Authorization'] = 'Bearer ${AuthService.token}';
    }
    return headers;
  }

  /// 1. Login User
  static Future<ApiResponse<UserModel>> login(String username, String password) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.login),
            headers: _getHeaders(withAuth: false),
            body: jsonEncode({
              'username': username,
              'password': password,
            }),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final data = body['data'] as Map<String, dynamic>;
        final token = data['token'] as String;
        final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
        
        await AuthService.saveSession(token, user);
        return ApiResponse(success: true, message: body['message'] ?? 'Login berhasil', data: user);
      } else {
        return ApiResponse(
          success: false,
          message: body['message'] ?? 'Login gagal. Periksa username dan password.',
          errors: body['errors'] as Map<String, dynamic>?,
        );
      }
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Koneksi ke server gagal. Pastikan Apache/Laragon aktif dan URL API benar (${ApiConfig.baseUrl}).',
      );
    }
  }

  /// 2. Get Dashboard Stats
  static Future<ApiResponse<DashboardStatsModel>> getDashboardStats() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConfig.dashboardStats),
            headers: _getHeaders(),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final stats = DashboardStatsModel.fromJson(body['data'] as Map<String, dynamic>);
        return ApiResponse(success: true, message: body['message'] ?? 'Sukses', data: stats);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat statistik');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 3. Get Leave Types
  static Future<ApiResponse<List<LeaveTypeModel>>> getLeaveTypes() async {
    try {
      final response = await http
          .get(
            Uri.parse(ApiConfig.leaveTypes),
            headers: _getHeaders(),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final list = (body['data'] as List<dynamic>)
            .map((e) => LeaveTypeModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return ApiResponse(success: true, message: 'Sukses', data: list);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat jenis cuti');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 4. Get Leaves List (My Leaves or Team)
  static Future<ApiResponse<List<LeaveModel>>> getLeavesList({
    String status = 'all',
    String scope = 'my',
    String search = '',
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.leavesList).replace(queryParameters: {
        'status': status,
        'scope': scope,
        if (search.isNotEmpty) 'search': search,
      });

      final response = await http
          .get(uri, headers: _getHeaders())
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final list = (body['data'] as List<dynamic>)
            .map((e) => LeaveModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return ApiResponse(success: true, message: 'Sukses', data: list);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat riwayat cuti');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 5. Submit Leave Request
  static Future<ApiResponse<Map<String, dynamic>>> submitLeave({
    required int leaveTypeId,
    required String tanggalMulai,
    required String tanggalSelesai,
    required String alasan,
    String? alamatSelamaCuti,
    String? kontakDarurat,
    String? attachmentBase64,
    String? attachmentName,
  }) async {
    try {
      final payload = {
        'leave_type_id': leaveTypeId,
        'tanggal_mulai': tanggalMulai,
        'tanggal_selesai': tanggalSelesai,
        'alasan': alasan,
        'alamat_selama_cuti': alamatSelamaCuti ?? '',
        'kontak_darurat': kontakDarurat ?? '',
        if (attachmentBase64 != null) 'attachment_base64': attachmentBase64,
        if (attachmentName != null) 'attachment_name': attachmentName,
      };

      final response = await http
          .post(
            Uri.parse(ApiConfig.leaveCreate),
            headers: _getHeaders(),
            body: jsonEncode(payload),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if ((response.statusCode == 200 || response.statusCode == 201) && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: body['message'] ?? 'Pengajuan cuti berhasil dikirim!',
          data: body['data'] as Map<String, dynamic>?,
        );
      } else {
        return ApiResponse(
          success: false,
          message: body['message'] ?? 'Gagal mengajukan cuti.',
          errors: body['errors'] as Map<String, dynamic>?,
        );
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Koneksi bermasalah saat mengirim pengajuan cuti');
    }
  }

  /// 6. Get Leave Detail
  static Future<ApiResponse<LeaveModel>> getLeaveDetail(int leaveId) async {
    try {
      final uri = Uri.parse('${ApiConfig.leaveDetail}?id=$leaveId');
      final response = await http.get(uri, headers: _getHeaders()).timeout(timeoutDuration);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final leave = LeaveModel.fromJson(body['data'] as Map<String, dynamic>);
        return ApiResponse(success: true, message: 'Sukses', data: leave);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat detail cuti');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 7. Cancel Leave
  static Future<ApiResponse<void>> cancelLeave(int leaveId) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.leaveCancel),
            headers: _getHeaders(),
            body: jsonEncode({'id': leaveId}),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? 'Status pembatalan cuti diproses',
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal membatalkan cuti');
    }
  }

  /// 8. Get Approvals List
  static Future<ApiResponse<List<LeaveModel>>> getApprovalsList({
    String status = 'pending',
    String search = '',
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.approvalsList).replace(queryParameters: {
        'status': status,
        if (search.isNotEmpty) 'search': search,
      });

      final response = await http.get(uri, headers: _getHeaders()).timeout(timeoutDuration);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final list = (body['data'] as List<dynamic>)
            .map((e) => LeaveModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return ApiResponse(success: true, message: 'Sukses', data: list);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat persetujuan cuti');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 9. Process Approval Action (Approve / Reject)
  static Future<ApiResponse<void>> processApprovalAction({
    required int leaveId,
    required String action, // 'approve' or 'reject'
    String notes = '',
    String rejectionReason = '',
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.approvalAction),
            headers: _getHeaders(),
            body: jsonEncode({
              'id': leaveId,
              'action': action,
              'notes': notes,
              'rejection_reason': rejectionReason,
            }),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? 'Persetujuan cuti diproses',
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal memproses persetujuan cuti');
    }
  }

  /// 10. Get Public Board
  static Future<ApiResponse<Map<String, dynamic>>> getPublicBoard({int? deptId}) async {
    try {
      final uri = Uri.parse(ApiConfig.publicBoard).replace(queryParameters: {
        if (deptId != null && deptId > 0) 'dept_id': deptId.toString(),
      });

      final response = await http.get(uri, headers: _getHeaders(withAuth: false)).timeout(timeoutDuration);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(success: true, message: 'Sukses', data: body['data'] as Map<String, dynamic>?);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat papan informasi');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }
}
