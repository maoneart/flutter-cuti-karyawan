import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../config/api_config.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

class ServerSettingScreen extends StatefulWidget {
  const ServerSettingScreen({super.key});

  @override
  State<ServerSettingScreen> createState() => _ServerSettingScreenState();
}

class _ServerSettingScreenState extends State<ServerSettingScreen> {
  late TextEditingController _urlController;
  bool _isTesting = false;
  String? _testResult;
  bool? _testSuccess;

  @override
  void initState() {
    super.initState();
    _urlController = TextEditingController(text: ApiConfig.baseUrl);
  }

  @override
  void dispose() {
    _urlController.dispose();
    super.dispose();
  }

  Future<void> _handleTestPing() async {
    final url = _urlController.text.trim();
    if (url.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Masukkan URL server terlebih dahulu')),
      );
      return;
    }

    setState(() {
      _isTesting = true;
      _testResult = null;
      _testSuccess = null;
    });

    final res = await ApiService.testConnection(url);

    if (!mounted) return;

    setState(() {
      _isTesting = false;
      _testSuccess = res['success'] == true;
      _testResult = res['message']?.toString();
    });
  }

  Future<void> _handleSave() async {
    final newUrl = _urlController.text.trim();
    if (newUrl.isEmpty) return;

    await AuthService.setCustomBaseUrl(newUrl);
    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Server berhasil diatur ke: $newUrl')),
    );
    Navigator.pop(context);
  }

  void _setPreset(String url) {
    setState(() {
      _urlController.text = url;
      _testResult = null;
      _testSuccess = null;
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textMuted;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
        centerTitle: true,
        leading: CupertinoButton(
          padding: EdgeInsets.zero,
          onPressed: () => Navigator.pop(context),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(CupertinoIcons.chevron_back, color: Color(0xFF007AFF), size: 28),
              Text(
                'Kembali',
                style: TextStyle(
                  color: Color(0xFF007AFF),
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
        leadingWidth: 95,
        title: Text(
          'Konfigurasi Server API',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17, color: textHead),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 500),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('ALAMAT SERVER API', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                Container(
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'URL Backend API:',
                        style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: textHead),
                      ),
                      const SizedBox(height: 8),
                      TextFormField(
                        controller: _urlController,
                        style: TextStyle(fontSize: 14, color: isDark ? Colors.white : AppTheme.textPrimary),
                        decoration: InputDecoration(
                          hintText: 'http://172.16.0.107/Cuti_Karyawan/api',
                          prefixIcon: const Icon(Icons.dns_rounded, size: 20),
                          suffixIcon: IconButton(
                            icon: const Icon(Icons.clear, size: 18),
                            onPressed: () => _urlController.clear(),
                          ),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Ping Test Button
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: _isTesting ? null : _handleTestPing,
                              icon: _isTesting
                                  ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                                  : const Icon(Icons.wifi_tethering_rounded, size: 18),
                              label: Text(_isTesting ? 'Menguji...' : 'Uji Koneksi (Ping)'),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: ElevatedButton.icon(
                              style: ElevatedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 12),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                              ),
                              onPressed: _handleSave,
                              icon: const Icon(Icons.save_rounded, size: 18),
                              label: const Text('Simpan Server'),
                            ),
                          ),
                        ],
                      ),

                      // Test Result Banner
                      if (_testResult != null) ...[
                        const SizedBox(height: 14),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: _testSuccess == true ? AppTheme.statusApprovedBg : AppTheme.statusRejectedBg,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: (_testSuccess == true ? AppTheme.statusApproved : AppTheme.statusRejected).withOpacity(0.3),
                            ),
                          ),
                          child: Row(
                            children: [
                              Icon(
                                _testSuccess == true ? Icons.check_circle_rounded : Icons.cancel_rounded,
                                color: _testSuccess == true ? AppTheme.statusApproved : AppTheme.statusRejected,
                                size: 20,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _testResult!,
                                  style: TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w600,
                                    color: _testSuccess == true ? AppTheme.statusApproved : AppTheme.statusRejected,
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
                const SizedBox(height: 24),

                // Presets
                Padding(
                  padding: const EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('PRESET SERVER CEPAT', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
                ),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: cardBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: borderCol),
                  ),
                  child: Column(
                    children: [
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const CircleAvatar(
                          radius: 16,
                          backgroundColor: Color(0xFF6366F1),
                          child: Icon(Icons.cloud_done_rounded, size: 16, color: Colors.white),
                        ),
                        title: const Text('Cloud Hosting (maoneart.my.id)', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold)),
                        subtitle: const Text('https://maoneart.my.id/api', style: TextStyle(fontSize: 11)),
                        onTap: () => _setPreset('https://maoneart.my.id/api'),
                      ),
                      const Divider(height: 16),
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const CircleAvatar(
                          radius: 16,
                          backgroundColor: Color(0xFF0284C7),
                          child: Icon(Icons.wifi, size: 16, color: Colors.white),
                        ),
                        title: const Text('WiFi Lokal Kantor (172.16.0.107)', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold)),
                        subtitle: const Text('http://172.16.0.107/Cuti_Karyawan/api', style: TextStyle(fontSize: 11)),
                        onTap: () => _setPreset('http://172.16.0.107/Cuti_Karyawan/api'),
                      ),
                      const Divider(height: 16),
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const CircleAvatar(
                          radius: 16,
                          backgroundColor: Color(0xFF059669),
                          child: Icon(Icons.computer, size: 16, color: Colors.white),
                        ),
                        title: const Text('Localhost (Emulator / Dev PC)', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold)),
                        subtitle: const Text('http://localhost/Cuti_Karyawan/api', style: TextStyle(fontSize: 11)),
                        onTap: () => _setPreset('http://localhost/Cuti_Karyawan/api'),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
