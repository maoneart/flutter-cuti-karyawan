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
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      appBar: AppBar(
        title: Text('Papan Kehadiran Hari Ini', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
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
        color: primaryAccent,
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _errorMessage != null
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, size: 48, color: AppTheme.statusRejected),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, style: TextStyle(color: textSub)),
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
                            gradient: LinearGradient(
                              colors: isDark
                                  ? [const Color(0xFF1E3A8A), const Color(0xFF0F172A)]
                                  : [AppTheme.primaryDark, AppTheme.primary],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
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
                                color: isDark ? const Color(0xFF34D399) : AppTheme.statusApproved,
                                icon: Icons.check_circle_outline_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _buildStatBox(
                                label: 'Cuti / Izin',
                                value: '${_summary['total_cuti_izin'] ?? 0}',
                                color: isDark ? const Color(0xFFFBBF24) : AppTheme.statusPending,
                                icon: Icons.beach_access_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _buildStatBox(
                                label: '% Kehadiran',
                                value: '${_summary['tingkat_kehadiran'] ?? 100}%',
                                color: primaryAccent,
                                icon: Icons.pie_chart_outline_rounded,
                                cardBg: cardBg,
                                borderCol: borderCol,
                                textSub: textSub,
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
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: textHead),
                        ),
                        const SizedBox(height: 12),

                        if (_leavesToday.isEmpty)
                          Container(
                            padding: const EdgeInsets.all(28),
                            width: double.infinity,
                            decoration: BoxDecoration(
                              color: cardBg,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: borderCol),
                            ),
                            child: Column(
                              children: [
                                const Icon(Icons.sentiment_satisfied_alt_rounded, size: 48, color: AppTheme.statusApproved),
                                const SizedBox(height: 12),
                                Text(
                                  'Semua Karyawan Hadir Lengkap!',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: textHead),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Tidak ada catatan cuti/izin aktif untuk hari ini.',
                                  style: TextStyle(fontSize: 13, color: textSub),
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
                                  color: cardBg,
                                  borderRadius: BorderRadius.circular(16),
                                  border: Border.all(color: borderCol),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    CircleAvatar(
                                      radius: 22,
                                      backgroundColor: primaryAccent.withOpacity(0.12),
                                      child: Text(
                                        (item['nama_lengkap']?.toString().isNotEmpty == true)
                                            ? item['nama_lengkap'].toString().substring(0, 1).toUpperCase()
                                            : 'K',
                                        style: TextStyle(fontWeight: FontWeight.bold, color: primaryAccent),
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
                                                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: textHead),
                                                ),
                                              ),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                decoration: BoxDecoration(
                                                  color: isDark ? const Color(0xFF451A03) : AppTheme.statusPendingBg,
                                                  borderRadius: BorderRadius.circular(6),
                                                ),
                                                child: Text(
                                                  item['kode_cuti']?.toString() ?? 'CUTI',
                                                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: isDark ? const Color(0xFFFBBF24) : AppTheme.statusPending),
                                                ),
                                              ),
                                            ],
                                          ),
                                          const SizedBox(height: 2),
                                          Text(
                                            '${item['nik'] ?? ''} • ${item['nama_dept'] ?? ''}',
                                            style: TextStyle(fontSize: 12, color: textSub),
                                          ),
                                          const SizedBox(height: 6),
                                          Text(
                                            '${item['tanggal_mulai']} s/d ${item['tanggal_selesai']} (${item['total_hari']} hari)',
                                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: primaryAccent),
                                          ),
                                          const SizedBox(height: 4),
                                          Text(
                                            'Alasan: ${item['alasan'] ?? '-'}',
                                            style: TextStyle(fontSize: 12, color: textSub.withOpacity(0.8)),
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
    required Color cardBg,
    required Color borderCol,
    required Color textSub,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 10),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: borderCol),
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
            style: TextStyle(fontSize: 11, color: textSub),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}
