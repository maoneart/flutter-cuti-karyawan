import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/theme_service.dart';
import 'main_navigation_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usernameController = TextEditingController(text: 'hrd@nakakin.co.id');
  final _passwordController = TextEditingController(text: 'password123');

  bool _obscurePassword = true;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.login(
      _usernameController.text.trim(),
      _passwordController.text,
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });

    if (res.success && res.data != null) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      setState(() {
        _errorMessage = res.message;
      });
    }
  }

  void _showServerSettingsDialog() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final serverController = TextEditingController(text: ApiConfig.baseUrl);
    bool isTesting = false;
    String? testResult;
    bool? testSuccess;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          backgroundColor: isDark ? AppTheme.darkSurface : Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: BorderSide(
              color: isDark ? AppTheme.darkBorder : const Color(0xFFE2E8F0),
            ),
          ),
          title: Row(
            children: [
              Icon(Icons.settings_ethernet, color: isDark ? AppTheme.darkPrimary : AppTheme.primary),
              const SizedBox(width: 8),
              Text(
                'Konfigurasi Server API',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : AppTheme.textPrimary,
                ),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Masukkan URL backend API laptop / server Anda:',
                  style: TextStyle(
                    fontSize: 13,
                    color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: serverController,
                  style: TextStyle(color: isDark ? Colors.white : AppTheme.textPrimary),
                  decoration: InputDecoration(
                    labelText: 'API Base URL',
                    hintText: 'http://172.16.0.107/Cuti_Karyawan/api',
                    prefixIcon: Icon(Icons.link, size: 20, color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary),
                  ),
                ),
                const SizedBox(height: 10),

                // Test Connection Button
                OutlinedButton.icon(
                  style: OutlinedButton.styleFrom(
                    foregroundColor: isDark ? AppTheme.darkPrimary : AppTheme.primary,
                    side: BorderSide(color: isDark ? AppTheme.darkBorder : const Color(0xFFCBD5E1)),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  onPressed: isTesting
                      ? null
                      : () async {
                          setDialogState(() {
                            isTesting = true;
                            testResult = null;
                            testSuccess = null;
                          });

                          final res = await ApiService.testConnection(serverController.text);

                          setDialogState(() {
                            isTesting = false;
                            testSuccess = res['success'] == true;
                            testResult = res['message']?.toString();
                          });
                        },
                  icon: isTesting
                      ? const SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Icon(Icons.network_check_rounded, size: 16),
                  label: const Text('Tes Koneksi / Ping Server', style: TextStyle(fontSize: 12)),
                ),

                if (testResult != null) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: (testSuccess == true)
                          ? (isDark ? const Color(0xFF065F46).withOpacity(0.3) : AppTheme.statusApprovedBg)
                          : (isDark ? const Color(0xFF991B1B).withOpacity(0.3) : AppTheme.statusRejectedBg),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: (testSuccess == true) ? AppTheme.statusApproved : AppTheme.statusRejected,
                        width: 1,
                      ),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(
                          (testSuccess == true) ? Icons.check_circle : Icons.error_outline,
                          size: 16,
                          color: (testSuccess == true) ? AppTheme.statusApproved : AppTheme.statusRejected,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            testResult!,
                            style: TextStyle(
                              fontSize: 12,
                              color: (testSuccess == true) ? AppTheme.statusApproved : AppTheme.statusRejected,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: Text(
                'Batal',
                style: TextStyle(color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary),
              ),
            ),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: isDark ? const Color(0xFF0284C7) : AppTheme.primary,
                foregroundColor: Colors.white,
              ),
              onPressed: () async {
                final newUrl = serverController.text.trim();
                if (newUrl.isNotEmpty) {
                  await AuthService.setCustomBaseUrl(newUrl);
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('Server diatur ke: $newUrl')),
                    );
                  }
                }
                Navigator.pop(ctx);
              },
              child: const Text('Simpan'),
            ),
          ],
        ),
      ),
    );
  }

  void _setDemoAccount(String username, String password) {
    setState(() {
      _usernameController.text = username;
      _passwordController.text = password;
      _errorMessage = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryAccent = isDark ? AppTheme.darkPrimary : AppTheme.primary;
    final cardBg = isDark ? AppTheme.darkSurface : Colors.white;
    final borderCol = isDark ? AppTheme.darkBorder : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.background,
      body: SafeArea(
        child: Stack(
          children: [
            Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 24),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 440),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Top Logo & Header
                        Center(
                          child: Column(
                            children: [
                              Container(
                                width: 72,
                                height: 72,
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(18),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(isDark ? 0.3 : 0.08),
                                      blurRadius: 14,
                                      offset: const Offset(0, 4),
                                    ),
                                  ],
                                ),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(18),
                                  child: Image.asset(
                                    'assets/images/Icon.png',
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => Icon(
                                      Icons.business_center_rounded,
                                      size: 40,
                                      color: primaryAccent,
                                    ),
                                  ),
                                ),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'PT. NAKAKIN INDONESIA',
                                style: TextStyle(
                                  fontSize: 22,
                                  fontWeight: FontWeight.w800,
                                  color: isDark ? Colors.white : AppTheme.primaryDark,
                                  letterSpacing: 0.5,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Sistem Informasi Cuti Karyawan',
                                style: TextStyle(
                                  fontSize: 14,
                                  color: textSub,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 32),

                        // Login Card
                        Container(
                          padding: const EdgeInsets.all(28),
                          decoration: BoxDecoration(
                            color: cardBg,
                            borderRadius: BorderRadius.circular(20),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(isDark ? 0.25 : 0.04),
                                blurRadius: 20,
                                offset: const Offset(0, 8),
                              ),
                            ],
                            border: Border.all(color: borderCol),
                          ),
                          child: Form(
                            key: _formKey,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Text(
                                  'Masuk ke Akun',
                                  style: TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.bold,
                                    color: textHead,
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Silakan masukkan NIK atau Email terdaftar',
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: textSub,
                                  ),
                                ),
                                const SizedBox(height: 20),

                                // Error Box
                                if (_errorMessage != null) ...[
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: isDark ? AppTheme.statusRejected.withOpacity(0.18) : AppTheme.statusRejectedBg,
                                      borderRadius: BorderRadius.circular(10),
                                      border: Border.all(color: AppTheme.statusRejected.withOpacity(0.4)),
                                    ),
                                    child: Row(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Icon(Icons.error_outline, color: AppTheme.statusRejected, size: 20),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            _errorMessage!,
                                            style: const TextStyle(
                                              color: AppTheme.statusRejected,
                                              fontSize: 12,
                                              fontWeight: FontWeight.w500,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(height: 16),
                                ],

                                // Username / NIK
                                TextFormField(
                                  controller: _usernameController,
                                  style: TextStyle(color: textHead, fontSize: 14),
                                  decoration: InputDecoration(
                                    labelText: 'NIK / Email / Username',
                                    labelStyle: TextStyle(color: textSub),
                                    prefixIcon: Icon(Icons.person_outline, size: 20, color: textSub),
                                  ),
                                  validator: (v) => (v == null || v.trim().isEmpty) ? 'NIK/Email wajib diisi' : null,
                                ),
                                const SizedBox(height: 16),

                                // Password
                                TextFormField(
                                  controller: _passwordController,
                                  obscureText: _obscurePassword,
                                  style: TextStyle(color: textHead, fontSize: 14),
                                  decoration: InputDecoration(
                                    labelText: 'Password',
                                    labelStyle: TextStyle(color: textSub),
                                    prefixIcon: Icon(Icons.lock_outline, size: 20, color: textSub),
                                    suffixIcon: IconButton(
                                      icon: Icon(
                                        _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                        size: 20,
                                        color: textSub,
                                      ),
                                      onPressed: () {
                                        setState(() {
                                          _obscurePassword = !_obscurePassword;
                                        });
                                      },
                                    ),
                                  ),
                                  validator: (v) => (v == null || v.isEmpty) ? 'Password wajib diisi' : null,
                                ),
                                const SizedBox(height: 24),

                                // Submit Button
                                ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: isDark ? const Color(0xFF0284C7) : AppTheme.primary,
                                    foregroundColor: Colors.white,
                                  ),
                                  onPressed: _isLoading ? null : _handleLogin,
                                  child: _isLoading
                                      ? const SizedBox(
                                          height: 20,
                                          width: 20,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2.5,
                                            valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                          ),
                                        )
                                      : const Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Text('Masuk Sekarang', style: TextStyle(fontSize: 16)),
                                            SizedBox(width: 8),
                                            Icon(Icons.arrow_forward_rounded, size: 18),
                                          ],
                                        ),
                                ),
                              ],
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Demo Accounts Quick Select
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: cardBg,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: borderCol),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Icon(Icons.info_outline, size: 16, color: primaryAccent),
                                  const SizedBox(width: 6),
                                  Text(
                                    'Akun Demo Testing (Klik Cepat):',
                                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textHead),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: [
                                  ActionChip(
                                    backgroundColor: isDark ? const Color(0xFF831843) : const Color(0xFFFCE7F3),
                                    side: BorderSide(color: borderCol),
                                    label: Text('Super Admin', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFFF472B6) : const Color(0xFFBE185D))),
                                    avatar: const CircleAvatar(backgroundColor: Color(0xFFDB2777), child: Text('S', style: TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold))),
                                    onPressed: () => _setDemoAccount('admin@nakakin.co.id', 'password123'),
                                  ),
                                  ActionChip(
                                    backgroundColor: isDark ? const Color(0xFF064E3B) : const Color(0xFFD1FAE5),
                                    side: BorderSide(color: borderCol),
                                    label: Text('Risma (HRD)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFF10B981) : const Color(0xFF065F46))),
                                    avatar: const CircleAvatar(backgroundColor: Color(0xFF059669), child: Text('H', style: TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold))),
                                    onPressed: () => _setDemoAccount('hrd@nakakin.co.id', 'password123'),
                                  ),
                                  ActionChip(
                                    backgroundColor: isDark ? const Color(0xFF4C1D95) : const Color(0xFFEDE9FE),
                                    side: BorderSide(color: borderCol),
                                    label: Text('Akun Manager', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFFDDD6FE) : const Color(0xFF5B21B6))),
                                    avatar: const CircleAvatar(backgroundColor: Color(0xFF7C3AED), child: Text('M', style: TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold))),
                                    onPressed: () => _setDemoAccount('manager@nakakin.co.id', 'password123'),
                                  ),
                                  ActionChip(
                                    backgroundColor: isDark ? const Color(0xFF0C4A6E) : const Color(0xFFE0F2FE),
                                    side: BorderSide(color: borderCol),
                                    label: Text('Leader Core', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFFBAE6FD) : const Color(0xFF0369A1))),
                                    avatar: const CircleAvatar(backgroundColor: Color(0xFF0284C7), child: Text('L', style: TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold))),
                                    onPressed: () => _setDemoAccount('ldr.core@nakakin.co.id', 'password123'),
                                  ),
                                  ActionChip(
                                    backgroundColor: isDark ? const Color(0xFF334155) : const Color(0xFFF1F5F9),
                                    side: BorderSide(color: borderCol),
                                    label: Text('Operator 1 Core', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textHead)),
                                    avatar: const CircleAvatar(backgroundColor: Color(0xFF475569), child: Text('O', style: TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.bold))),
                                    onPressed: () => _setDemoAccount('op1.core@nakakin.co.id', 'password123'),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Bottom Server Config Link
                        Center(
                          child: TextButton.icon(
                            onPressed: _showServerSettingsDialog,
                            icon: Icon(Icons.settings, size: 16, color: textSub),
                            label: Text(
                              'Server API: ${ApiConfig.baseUrl}',
                              style: TextStyle(fontSize: 12, color: textSub),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),

            // Top Right Floating Theme Switcher (Highest Z-Index)
            Positioned(
              top: 16,
              right: 16,
              child: Material(
                color: isDark ? AppTheme.darkSurface : Colors.white,
                shape: const CircleBorder(),
                elevation: 3,
                shadowColor: Colors.black26,
                child: InkWell(
                  customBorder: const CircleBorder(),
                  onTap: () {
                    ThemeService.toggleTheme();
                  },
                  child: Padding(
                    padding: const EdgeInsets.all(10),
                    child: Icon(
                      isDark ? CupertinoIcons.sun_max_fill : CupertinoIcons.moon_fill,
                      color: isDark ? const Color(0xFFFBBF24) : AppTheme.primary,
                      size: 22,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
