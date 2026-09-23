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
    return Scaffold(
      backgroundColor: const Color(0xFFF2F2F7),
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(CupertinoIcons.back, color: AppTheme.primary),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text('Konfigurasi Server API', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0.5,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 500),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('KONEKSI API BACKEND', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      TextFormField(
                        controller: _urlController,
                        decoration: const InputDecoration(
                          labelText: 'API Base URL',
                          hintText: 'http://172.16.0.107/Cuti_Karyawan/api',
                          prefixIcon: Icon(Icons.dns_rounded, size: 20),
                        ),
                      ),
                      const SizedBox(height: 14),

                      // Test Ping Button
                      OutlinedButton.icon(
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                        ),
                        onPressed: _isTesting ? null : _handleTestPing,
                        icon: _isTesting
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                            : const Icon(Icons.network_check_rounded, size: 18),
                        label: const Text('Uji Ping & Koneksi Server', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                      ),

                      if (_testResult != null) ...[
                        const SizedBox(height: 12),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: (_testSuccess == true) ? AppTheme.statusApprovedBg : AppTheme.statusRejectedBg,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: (_testSuccess == true) ? AppTheme.statusApproved : AppTheme.statusRejected,
                            ),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                (_testSuccess == true) ? Icons.check_circle : Icons.error_outline,
                                size: 18,
                                color: (_testSuccess == true) ? AppTheme.statusApproved : AppTheme.statusRejected,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  _testResult!,
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: (_testSuccess == true) ? AppTheme.statusApproved : AppTheme.statusRejected,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 20),

                      // Save Button
                      ElevatedButton(
                        onPressed: _handleSave,
                        child: const Text('Simpan & Terapkan', style: TextStyle(fontSize: 15)),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Presets
                const Padding(
                  padding: EdgeInsets.only(left: 8, bottom: 8),
                  child: Text('PILIHAN CEPAT (PRESETS)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppTheme.textMuted)),
                ),
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Column(
                    children: [
                      ListTile(
                        leading: const CircleAvatar(backgroundColor: Color(0xFFE0E7FF), child: Icon(Icons.laptop, color: AppTheme.primary, size: 20)),
                        title: const Text('IP Laptop Saat Ini (172.16.0.107)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        subtitle: const Text('http://172.16.0.107/Cuti_Karyawan/api', style: TextStyle(fontSize: 11)),
                        onTap: () => _setPreset('http://172.16.0.107/Cuti_Karyawan/api'),
                      ),
                      const Divider(height: 1, indent: 56, color: Color(0xFFF1F5F9)),
                      ListTile(
                        leading: const CircleAvatar(backgroundColor: Color(0xFFDCFCE7), child: Icon(Icons.cloud_done, color: Colors.green, size: 20)),
                        title: const Text('Custom Domain / Hosting', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                        subtitle: const Text('https://domainanda.com/api', style: TextStyle(fontSize: 11)),
                        onTap: () => _setPreset('https://domainanda.com/api'),
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
