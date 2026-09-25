import 'dart:convert';
import 'dart:io';
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
  static const Duration timeoutDuration = Duration(seconds: 12);

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

  /// Test server connectivity
  static Future<Map<String, dynamic>> testConnection(String customUrl) async {
    final cleanUrl = customUrl.trim().replaceAll(RegExp(r'/+$'), '');
    final testEndpoint = '$cleanUrl/public/board.php';
    final stopwatch = Stopwatch()..start();

    try {
      final response = await http
          .get(Uri.parse(testEndpoint))
          .timeout(const Duration(seconds: 7));
      stopwatch.stop();

      if (response.statusCode == 200) {
        return {
          'success': true,
          'message': 'Koneksi Berhasil! (Response ${stopwatch.elapsedMilliseconds}ms)',
          'status': response.statusCode,
        };
      } else {
        return {
          'success': false,
          'message': 'Server terhubung tapi mengembalikan status HTTP ${response.statusCode}',
          'status': response.statusCode,
        };
      }
    } on SocketException catch (e) {
      stopwatch.stop();
      return {
        'success': false,
        'message': 'Gagal terhubung ke host (${e.message}). Pastikan IP benar dan Firewall mengizinkan port 80.',
      };
    } on http.ClientException catch (e) {
      stopwatch.stop();
      return {
        'success': false,
        'message': 'Client HTTP error: ${e.message}',
      };
    } catch (e) {
      stopwatch.stop();
      return {
        'success': false,
        'message': 'Koneksi timeout / gagal: $e',
      };
    }
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
    } on SocketException catch (e) {
      return ApiResponse(
        success: false,
        message: 'Koneksi gagal (${e.message}). Periksa apakah IP Laptop (${ApiConfig.baseUrl}) dapat dijangkau dan Firewall aktif.',
      );
    } catch (e) {
      return ApiResponse(
        success: false,
        message: 'Koneksi ke server gagal ($e). Pastikan Apache/Laragon aktif dan URL API benar (${ApiConfig.baseUrl}).',
      );
    }
  }

  /// 2. Change Password
  static Future<ApiResponse<void>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.changePassword),
            headers: _getHeaders(),
            body: jsonEncode({
              'current_password': currentPassword,
              'new_password': newPassword,
              'confirm_password': confirmPassword,
            }),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? 'Password diproses',
        errors: body['errors'] as Map<String, dynamic>?,
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal mengubah password');
    }
  }

  /// 3. Get Notifications
  static Future<ApiResponse<Map<String, dynamic>>> getNotifications() async {
    try {
      final response = await http
          .get(Uri.parse(ApiConfig.notifications), headers: _getHeaders())
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: 'Sukses',
          data: body['data'] as Map<String, dynamic>?,
        );
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat notifikasi');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server notifikasi');
    }
  }

  /// 4. Mark Notifications as Read
  static Future<void> markNotificationsRead() async {
    try {
      await http.post(Uri.parse(ApiConfig.notifications), headers: _getHeaders()).timeout(timeoutDuration);
    } catch (_) {}
  }

  /// 5. Get Dashboard Stats
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

  /// 6. Get Leave Types
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

  /// 7. Get Leaves List
  static Future<ApiResponse<List<LeaveModel>>> getLeavesList({
    String status = 'all',
    String scope = 'my',
    String search = '',
    int? deptId,
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.leavesList).replace(queryParameters: {
        'status': status,
        'scope': scope,
        if (search.isNotEmpty) 'search': search,
        if (deptId != null && deptId > 0) 'dept_id': deptId.toString(),
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

  /// 8. Submit Leave Request
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

  /// 9. Get Leave Detail
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

  /// 10. Cancel Leave
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

  /// 11. Get Approvals List
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

  /// 12. Process Approval Action (Approve / Reject)
  static Future<ApiResponse<void>> processApprovalAction({
    required int leaveId,
    required String action,
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

  /// Alias for processApprovalAction
  static Future<ApiResponse<void>> processApproval({
    required int leaveId,
    required String action,
    String? notes,
    String? rejectionReason,
  }) => processApprovalAction(
    leaveId: leaveId,
    action: action,
    notes: notes ?? '',
    rejectionReason: rejectionReason ?? '',
  );

  /// 13. Get Public Board
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

  /// 14. Get Employee Form Options (Departments & Positions)
  static Future<ApiResponse<Map<String, dynamic>>> getEmployeeOptions() async {
    try {
      final response = await http
          .get(Uri.parse(ApiConfig.employeeOptions), headers: _getHeaders())
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: 'Sukses',
          data: body['data'] as Map<String, dynamic>?,
        );
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat opsi karyawan');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 15. Create Employee
  static Future<ApiResponse<Map<String, dynamic>>> createEmployee(Map<String, dynamic> data) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.employeeCreate),
            headers: _getHeaders(),
            body: jsonEncode(data),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? (response.statusCode == 200 ? 'Karyawan berhasil dibuat' : 'Gagal membuat karyawan'),
        data: body['data'] as Map<String, dynamic>?,
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal mengirim data karyawan');
    }
  }

  /// 16. Get Employees List
  static Future<ApiResponse<List<UserModel>>> getEmployeesList({
    int? deptId,
    String search = '',
    String role = '',
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.employeesList).replace(queryParameters: {
        if (deptId != null && deptId > 0) 'dept_id': deptId.toString(),
        if (search.isNotEmpty) 'search': search,
        if (role.isNotEmpty) 'role': role,
      });

      final response = await http.get(uri, headers: _getHeaders()).timeout(timeoutDuration);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        final list = (body['data'] as List<dynamic>)
            .map((e) => UserModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return ApiResponse(success: true, message: 'Sukses', data: list);
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat daftar karyawan');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 17. Preview Employee Excel Import
  static Future<ApiResponse<Map<String, dynamic>>> previewEmployeeImport({
    List<int>? fileBytes,
    String? filePath,
    required String fileName,
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.employeeImportPreview);
      final request = http.MultipartRequest('POST', uri);

      final token = AuthService.token;
      if (token != null && token.isNotEmpty) {
        request.headers['Authorization'] = 'Bearer $token';
      }

      if (fileBytes != null && fileBytes.isNotEmpty) {
        request.files.add(http.MultipartFile.fromBytes(
          'file',
          fileBytes,
          filename: fileName,
        ));
      } else if (filePath != null && filePath.isNotEmpty) {
        request.files.add(await http.MultipartFile.fromPath(
          'file',
          filePath,
          filename: fileName,
        ));
      } else {
        return ApiResponse(success: false, message: 'File tidak dapat dibaca');
      }

      final streamedResponse = await request.send().timeout(timeoutDuration);
      final response = await http.Response.fromStream(streamedResponse);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: body['message'] ?? 'Validasi berhasil',
          data: body['data'] as Map<String, dynamic>?,
        );
      } else {
        return ApiResponse(
          success: false,
          message: body['message'] ?? 'Gagal memvalidasi file Excel',
        );
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal mengunggah file ke server');
    }
  }

  /// 18. Commit Employee Excel Import
  static Future<ApiResponse<Map<String, dynamic>>> commitEmployeeImport(
    List<dynamic> validRows,
  ) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.employeeImportCommit),
            headers: _getHeaders(),
            body: jsonEncode({'valid_rows': validRows}),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: body['message'] ?? 'Data karyawan berhasil diimpor',
          data: body['data'] as Map<String, dynamic>?,
        );
      } else {
        return ApiResponse(
          success: false,
          message: body['message'] ?? 'Gagal menyimpan data karyawan',
        );
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 19. Update Employee Data
  static Future<ApiResponse<Map<String, dynamic>>> updateEmployee(Map<String, dynamic> data) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.employeeUpdate),
            headers: _getHeaders(),
            body: jsonEncode(data),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? (response.statusCode == 200 ? 'Data karyawan berhasil diperbarui' : 'Gagal memperbarui karyawan'),
        data: body['data'] as Map<String, dynamic>?,
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal memperbarui data karyawan');
    }
  }

  /// 20. Reset Employee Password
  static Future<ApiResponse<void>> resetEmployeePassword(int id, {String? newPassword}) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.employeeResetPassword),
            headers: _getHeaders(),
            body: jsonEncode({
              'id': id,
              if (newPassword != null && newPassword.isNotEmpty) 'new_password': newPassword,
            }),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? (response.statusCode == 200 ? 'Password berhasil direset' : 'Gagal mereset password'),
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal mereset password karyawan');
    }
  }

  /// 21. Delete Employee
  static Future<ApiResponse<void>> deleteEmployee(int id) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.employeeDelete),
            headers: _getHeaders(),
            body: jsonEncode({'id': id}),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? (response.statusCode == 200 ? 'Data karyawan berhasil dihapus' : 'Gagal menghapus karyawan'),
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghapus data karyawan');
    }
  }

  /// 22. Get Quotas List & Summary
  static Future<ApiResponse<Map<String, dynamic>>> getQuotasList({
    int? deptId,
    String search = '',
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.quotasList).replace(queryParameters: {
        if (deptId != null && deptId > 0) 'dept_id': deptId.toString(),
        if (search.isNotEmpty) 'search': search,
      });

      final response = await http.get(uri, headers: _getHeaders()).timeout(timeoutDuration);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: 'Sukses',
          data: body['data'] as Map<String, dynamic>?,
        );
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat data jatah kuota');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal menghubungi server');
    }
  }

  /// 23. Adjust Individual Employee Quota
  static Future<ApiResponse<Map<String, dynamic>>> adjustQuota({
    required int employeeId,
    required String mode,
    int? totalKuota,
    int? delta,
    required String keterangan,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.quotaAdjust),
            headers: _getHeaders(),
            body: jsonEncode({
              'employee_id': employeeId,
              'mode': mode,
              if (totalKuota != null) 'total_kuota': totalKuota,
              if (delta != null) 'delta': delta,
              'keterangan': keterangan,
            }),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? 'Penyesuaian kuota diproses',
        data: body['data'] as Map<String, dynamic>?,
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal memproses penyesuaian kuota');
    }
  }

  /// 24. Bulk Quota Allocation
  static Future<ApiResponse<Map<String, dynamic>>> bulkAllocateQuota({
    required String targetScope,
    int targetId = 0,
    required String batchAction,
    required int quotaAmount,
    required String keterangan,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.quotaBulk),
            headers: _getHeaders(),
            body: jsonEncode({
              'target_scope': targetScope,
              'target_id': targetId,
              'batch_action': batchAction,
              'quota_amount': quotaAmount,
              'keterangan': keterangan,
            }),
          )
          .timeout(timeoutDuration);

      final body = jsonDecode(response.body) as Map<String, dynamic>;
      return ApiResponse(
        success: body['success'] == true,
        message: body['message'] ?? 'Alokasi massal diproses',
        data: body['data'] as Map<String, dynamic>?,
      );
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal memproses alokasi massal');
    }
  }

  /// 25. Get Quota Mutation Logs
  static Future<ApiResponse<List<dynamic>>> getQuotaLogs({
    int? employeeId,
    int limit = 50,
  }) async {
    try {
      final uri = Uri.parse(ApiConfig.quotaLogs).replace(queryParameters: {
        if (employeeId != null && employeeId > 0) 'employee_id': employeeId.toString(),
        'limit': limit.toString(),
      });

      final response = await http.get(uri, headers: _getHeaders()).timeout(timeoutDuration);
      final body = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && body['success'] == true) {
        return ApiResponse(
          success: true,
          message: 'Sukses',
          data: body['data'] as List<dynamic>?,
        );
      } else {
        return ApiResponse(success: false, message: body['message'] ?? 'Gagal memuat riwayat mutasi kuota');
      }
    } catch (e) {
      return ApiResponse(success: false, message: 'Gagal memuat riwayat mutasi kuota');
    }
  }
}

