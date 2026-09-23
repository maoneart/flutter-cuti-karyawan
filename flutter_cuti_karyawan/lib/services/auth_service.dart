import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../config/api_config.dart';

class AuthService {
  static const String keyToken = 'auth_token';
  static const String keyUser = 'auth_user';
  static const String keyCustomBaseUrl = 'custom_base_url';

  static UserModel? _currentUser;
  static String? _token;

  static UserModel? get currentUser => _currentUser;
  static String? get token => _token;
  static bool get isLoggedIn => _token != null && _currentUser != null;

  /// Load persisted session from SharedPreferences
  static Future<bool> init() async {
    final prefs = await SharedPreferences.getInstance();
    
    // Load custom base url if set
    final customUrl = prefs.getString(keyCustomBaseUrl);
    if (customUrl != null && customUrl.isNotEmpty) {
      ApiConfig.baseUrl = customUrl;
    }

    _token = prefs.getString(keyToken);
    final userJsonStr = prefs.getString(keyUser);
    
    if (_token != null && userJsonStr != null) {
      try {
        final userMap = jsonDecode(userJsonStr) as Map<String, dynamic>;
        _currentUser = UserModel.fromJson(userMap);
        return true;
      } catch (e) {
        await logout();
        return false;
      }
    }
    return false;
  }

  /// Save session on successful login
  static Future<void> saveSession(String token, UserModel user) async {
    _token = token;
    _currentUser = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(keyToken, token);
    await prefs.setString(keyUser, jsonEncode(user.toJson()));
  }

  /// Update user profile data locally (e.g. after quota deduction)
  static Future<void> updateUser(UserModel user) async {
    _currentUser = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(keyUser, jsonEncode(user.toJson()));
  }

  /// Update Base URL
  static Future<void> setCustomBaseUrl(String url) async {
    ApiConfig.baseUrl = url;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(keyCustomBaseUrl, url);
  }

  /// Clear session
  static Future<void> logout() async {
    _token = null;
    _currentUser = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(keyToken);
    await prefs.remove(keyUser);
  }
}
