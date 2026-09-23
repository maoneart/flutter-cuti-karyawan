import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/api_service.dart';

class PublicBoardScreen extends StatefulWidget {
  const PublicBoardScreen({super.key});

  @override
  State<PublicBoardScreen> createState() => _PublicBoardScreenState();
}

class _PublicBoardScreenState extends State<PublicBoardScreen> {
  bool _isLoading = true;
  String? _errorMessage;

  String _todayFormatted = '';
  Map<String, dynamic> _summary = {};
  List<dynamic> _departments = [];
  List<dynamic> _leavesToday = [];
  int? _selectedDeptId;

  @override
  void initState() {
    super.initState();
    _loadBoard();
  }

  Future<void> _loadBoard() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getPublicBoard(deptId: _selectedDeptId);

    if (!mounted) return;

    if (res.success && res.data != null) {
      final data = res.data!;
      setState(() {
        _todayFormatted = data['today_formatted']?.toString() ?? '';
        _summary = (data['summary'] as Map<String, dynamic>?) ?? {};
        _departments = (data['departments'] as List<dynamic>?) ?? [];
        _leavesToday = (data['leaves_today'] as List<dynamic>?) ?? [];
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.background,
      appBar: AppBar(
        title: const Text('Papan Kehadiran & Cuti Hari Ini'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _loadBoard,
            tooltip: 'Segarkan',
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _loadBoard,
        color: AppTheme.primary,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppTheme.statusRejected),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, style: const TextStyle(color: AppTheme.textSecondary)),
                        const SizedBox(height: 12),
                        ElevatedButton(onPressed: _loadBoard, child: const Text('Coba Lagi')),
                      ],
                    ),
                  )
                : SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Live Clock / Date Card
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: AppTheme.primaryDark,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.12),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.calendar_today_rounded, color: Colors.white, size: 22),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('STATUS KEHADIRAN LIVE', style: TextStyle(color: Colors.white70, fontSize: 11, fontWeight: FontWeight.bold, letterSpacing: 1)),
                                    const SizedBox(height: 2),
                                    Text(
                                      _todayFormatted.isNotEmpty ? _todayFormatted : 'Hari Ini',
                                      style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Stats Summary Row
                        Row(
                          children: [
                            Expanded(
                              child: _buildStatBox(
                                label: 'Hadir',
                                value: '${_summary['total_hadir'] ?? 0}',
                                color: AppTheme.statusApproved,
                                icon: Icons.check_circle_outline_rounded,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _buildStatBox(
                                label: 'Cuti / Izin',
                                value: '${_summary['total_cuti_izin'] ?? 0}',
                                color: AppTheme.statusPending,
                                icon: Icons.beach_access_rounded,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _buildStatBox(
                                label: '% Kehadiran',
                                value: '${_summary['tingkat_kehadiran'] ?? 100}%',
                                color: AppTheme.primary,
                                icon: Icons.pie_chart_outline_rounded,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 24),

                        // Department Filter Chips
                        SingleChildScrollView(
                          scrollDirection: Axis.horizontal,
                          child: Row(
                            children: [
                              Padding(
                                padding: const EdgeInsets.only(right: 8),
                                child: ChoiceChip(
                                  label: const Text('Semua Dept'),
                                  selected: _selectedDeptId == null,
                                  onSelected: (val) {
                                    setState(() {
                                      _selectedDeptId = null;
                                    });
                                    _loadBoard();
                                  },
                                ),
                              ),
                              ..._departments.map((d) {
                                final id = int.tryParse(d['id']?.toString() ?? '0') ?? 0;
                                final name = d['kode_dept']?.toString() ?? d['nama_dept']?.toString() ?? '';
                                return Padding(
                                  padding: const EdgeInsets.only(right: 8),
                                  child: ChoiceChip(
                                    label: Text(name),
                                    selected: _selectedDeptId == id,
                                    onSelected: (val) {
                                      setState(() {
                                        _selectedDeptId = val ? id : null;
                                      });
                                      _loadBoard();
                                    },
                                  ),
                                );
                              }),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // List of On Leave Employees
                        Text(
                          'Karyawan Cuti Hari Ini (${_leavesToday.length})',
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                        ),
                        const SizedBox(height: 12),

                        if (_leavesToday.isEmpty)
                          Container(
                            padding: const EdgeInsets.all(28),
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: const Column(
                              children: [
                                Icon(Icons.sentiment_satisfied_alt_rounded, size: 48, color: AppTheme.statusApproved),
                                SizedBox(height: 12),
                                Text(
                                  'Semua Karyawan Hadir Lengkap!',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: AppTheme.textPrimary),
                                ),
                                SizedBox(height: 4),
                                Text(
                                  'Tidak ada catatan cuti/izin aktif untuk hari ini.',
                                  style: TextStyle(fontSize: 13, color: AppTheme.textSecondary),
                                ),
                              ],
                            ),
                          )
                        else
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: _leavesToday.length,
                            separatorBuilder: (_, __) => const SizedBox(height: 12),
                            itemBuilder: (ctx, idx) {
                              final item = _leavesToday[idx];
                              return Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    CircleAvatar(
                                      radius: 22,
                                      backgroundColor: AppTheme.primaryLight.withOpacity(0.12),
                                      child: Text(
                                        (item['nama_lengkap']?.toString().isNotEmpty == true)
                                            ? item['nama_lengkap'].toString().substring(0, 1).toUpperCase()
                                            : 'K',
                                        style: const TextStyle(fontWeight: FontWeight.bold, color: AppTheme.primaryLight),
                                      ),
                                    ),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                            children: [
                                              Expanded(
                                                child: Text(
                                                  item['nama_lengkap']?.toString() ?? '-',
                                                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(
                                                  color: AppTheme.statusPendingBg,
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  item['kode_cuti']?.toString() ?? 'CUTI',
                                                  style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: AppTheme.statusPending),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            '${item['nik'] ?? ''} • ${item['nama_dept'] ?? ''}',
                                            style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
                                          ),
                                          const SizedBox(height: 6),
                                          Text(
                                            '${item['tanggal_mulai']} s/d ${item['tanggal_selesai']} (${item['total_hari']} hari)',
                                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.primary),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            'Alasan: ${item['alasan'] ?? '-'}',
                                            style: const TextStyle(fontSize: 12, color: AppTheme.textMuted),
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ],
                                      ),
                                    ),
                                  ],
                                ),
                              );
                            },
                          ),
                      ],
                    ),
                  ),
      ),
    );
  }

  Widget _buildStatBox({
    required String label,
    required String value,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Icon(icon, size: 20, color: color),
          const SizedBox(height: 6),
          Text(
            value,
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: color),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
