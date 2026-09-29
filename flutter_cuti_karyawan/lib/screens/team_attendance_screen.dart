import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import '../config/app_theme.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

class TeamAttendanceScreen extends StatefulWidget {
  const TeamAttendanceScreen({super.key});

  @override
  State<TeamAttendanceScreen> createState() => _TeamAttendanceScreenState();
}

class _TeamAttendanceScreenState extends State<TeamAttendanceScreen> {
  DateTime _selectedDate = DateTime.now();
  bool _isLoading = true;
  String? _errorMessage;

  Map<String, dynamic>? _department;
  bool _isLeaderOrSpv = false;
  bool _canManageShift = false;
  List<dynamic> _members = [];
  List<dynamic> _revisionTypes = [];

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    final res = await ApiService.getTeamAttendance(tanggal: dateStr);

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _department = res.data!['department'] as Map<String, dynamic>?;
        _isLeaderOrSpv = res.data!['is_leader_or_spv'] == true;
        _canManageShift = res.data!['can_manage_shift'] == true;
        _members = (res.data!['members'] as List<dynamic>?) ?? [];
        _revisionTypes = (res.data!['revision_types'] as List<dynamic>?) ?? [];
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 60)),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );

    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
      _loadData();
    }
  }

  Future<void> _toggleShift(int employeeId, String currentShift, String empName) async {
    final newShift = currentShift.contains('Shift 1') ? 'Shift 2 (Malam)' : 'Shift 1 (Pagi)';

    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Ubah Jadwal Shift', style: TextStyle(fontWeight: FontWeight.bold)),
        content: Text('Ubah shift untuk $empName menjadi $newShift?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: newShift.contains('Shift 2') ? const Color(0xFFE11D48) : const Color(0xFF2563EB),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: Text('Ubah ke $newShift'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final res = await ApiService.updateEmployeeShift(employeeId: employeeId, shift: newShift);
    if (!mounted) return;

    if (res.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message), backgroundColor: Colors.green),
      );
      _loadData();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message), backgroundColor: Colors.red),
      );
    }
  }

  Future<void> _confirmRollingShift() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: const [
            Icon(Icons.swap_horiz_rounded, color: Color(0xFFE11D48)),
            SizedBox(width: 8),
            Text('Rolling Shift Mingguan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
          ],
        ),
        content: const Text(
          'Tindakan ini akan menukar Shift 1 (Pagi) ke Shift 2 (Malam) dan sebaliknya untuk seluruh operator di departemen ini.\n\nLanjutkan rolling shift mingguan?',
          style: TextStyle(fontSize: 13),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE11D48),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Rolling Shift'),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final res = await ApiService.rollDepartmentShift();
    if (!mounted) return;

    if (res.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message), backgroundColor: Colors.green),
      );
      _loadData();
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(res.message), backgroundColor: Colors.red),
      );
    }
  }

  void _showMangkirDialog(Map<String, dynamic> member) {
    final noteController = TextEditingController(text: 'Mangkir tanpa kabar saat jam shift masuk.');
    String currentShift = member['current_shift']?.toString() ?? 'Shift 1';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheetState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final sheetBg = isDark ? const Color(0xFF1E293B) : Colors.white;

          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
            child: Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: sheetBg,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFF1F2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.person_off_rounded, color: Color(0xFFE11D48)),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Catat Mangkir (Alpha / AWOL)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            Text(member['nama_lengkap'] ?? '', style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text('Shift Kerja:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? Colors.white70 : AppTheme.textPrimary)),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Expanded(
                        child: ChoiceChip(
                          label: const Center(child: Text('Shift 1 (Pagi)')),
                          selected: currentShift.contains('Shift 1'),
                          onSelected: (val) {
                            if (val) setSheetState(() => currentShift = 'Shift 1 (Pagi)');
                          },
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: ChoiceChip(
                          label: const Center(child: Text('Shift 2 (Malam)')),
                          selected: currentShift.contains('Shift 2'),
                          onSelected: (val) {
                            if (val) setSheetState(() => currentShift = 'Shift 2 (Malam)');
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  Text('Catatan / Keterangan Leader:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? Colors.white70 : AppTheme.textPrimary)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: noteController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: 'Contoh: Tidak ada kabar saat jam masuk shift...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: () => Navigator.pop(ctx),
                          child: const Text('Batal'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFE11D48),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: () async {
                            Navigator.pop(ctx);
                            final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
                            final res = await ApiService.recordMangkir(
                              employeeId: (member['id'] as num).toInt(),
                              tanggal: dateStr,
                              shift: currentShift,
                              keterangan: noteController.text.trim(),
                            );
                            if (!mounted) return;
                            if (res.success) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(res.message), backgroundColor: Colors.green),
                              );
                              _loadData();
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(res.message), backgroundColor: Colors.red),
                              );
                            }
                          },
                          child: const Text('Catat Mangkir'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  void _showRevisiDialog(Map<String, dynamic> member) {
    String selectedKode = 'SD';
    final noteController = TextEditingController(text: 'Karyawan menyerahkan Surat Dokter asli.');
    String? attachmentBase64;
    String? attachmentName;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setSheetState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final sheetBg = isDark ? const Color(0xFF1E293B) : Colors.white;

          return Padding(
            padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
            child: Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: sheetBg,
                borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF3C7),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.edit_calendar_rounded, color: Color(0xFFD97706)),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Revisi Status Mangkir', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                            Text(member['nama_lengkap'] ?? '', style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary)),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text('Ubah Ke Status / Jenis Izin:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? Colors.white70 : AppTheme.textPrimary)),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<String>(
                    value: selectedKode,
                    isExpanded: true,
                    decoration: InputDecoration(
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    ),
                    items: _revisionTypes.map((t) {
                      final kode = t['kode']?.toString() ?? '';
                      final nama = t['nama_cuti']?.toString() ?? '';
                      return DropdownMenuItem(
                        value: kode,
                        child: Text('$kode - $nama', style: const TextStyle(fontSize: 13), overflow: TextOverflow.ellipsis),
                      );
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) {
                        setSheetState(() {
                          selectedKode = val;
                          if (val == 'SD') {
                            noteController.text = 'Karyawan menyerahkan Surat Dokter asli.';
                          } else if (val == 'CT-HALF') {
                            noteController.text = 'Disepakati potong Cuti Tahunan 0.5 Hari (Setengah Hari).';
                          } else if (val == 'CT') {
                            noteController.text = 'Disepakati potong Cuti Tahunan 1 Hari.';
                          }
                        });
                      }
                    },
                  ),
                  const SizedBox(height: 14),
                  Text('Alasan Revisi:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: isDark ? Colors.white70 : AppTheme.textPrimary)),
                  const SizedBox(height: 6),
                  TextField(
                    controller: noteController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      hintText: 'Alasan revisi...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),
                  const SizedBox(height: 14),
                  InkWell(
                    onTap: () async {
                      final result = await FilePicker.platform.pickFiles(
                        type: FileType.custom,
                        allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
                        withData: true,
                      );
                      if (result != null && result.files.isNotEmpty) {
                        final f = result.files.first;
                        if (f.bytes != null) {
                          setSheetState(() {
                            attachmentBase64 = base64Encode(f.bytes!);
                            attachmentName = f.name;
                          });
                        }
                      }
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                        borderRadius: BorderRadius.circular(12),
                        color: attachmentName != null ? Colors.green.withOpacity(0.08) : Colors.transparent,
                      ),
                      child: Row(
                        children: [
                          Icon(attachmentName != null ? Icons.check_circle : Icons.upload_file, color: attachmentName != null ? Colors.green : AppTheme.primary),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              attachmentName ?? 'Unggah Surat Dokter / Bukti (Opsional)',
                              style: TextStyle(fontSize: 12, color: attachmentName != null ? Colors.green : AppTheme.textSecondary),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton(
                          style: OutlinedButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: () => Navigator.pop(ctx),
                          child: const Text('Batal'),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: ElevatedButton(
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFD97706),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                          onPressed: () async {
                            Navigator.pop(ctx);
                            final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
                            final res = await ApiService.reviseAttendance(
                              absensiId: member['absensi_id'] != null ? (member['absensi_id'] as num).toInt() : null,
                              employeeId: (member['id'] as num).toInt(),
                              tanggal: dateStr,
                              direvisiMenjadi: selectedKode,
                              alasanRevisi: noteController.text.trim(),
                              buktiBase64: attachmentBase64,
                              buktiName: attachmentName,
                            );
                            if (!mounted) return;
                            if (res.success) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(res.message), backgroundColor: Colors.green),
                              );
                              _loadData();
                            } else {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text(res.message), backgroundColor: Colors.red),
                              );
                            }
                          },
                          child: const Text('Simpan Revisi'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    final mangkirCount = _members.where((m) => m['is_mangkir'] == true).length;
    final shift1Count = _members.where((m) => (m['current_shift']?.toString() ?? '').contains('Shift 1')).length;
    final shift2Count = _members.where((m) => (m['current_shift']?.toString() ?? '').contains('Shift 2')).length;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Tim & Absensi Shift', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 17)),
            Text(
              _department?['nama_dept'] ?? 'Departemen Anda',
              style: const TextStyle(fontSize: 11, color: AppTheme.textSecondary),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan',
            onPressed: _loadData,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.error_outline_rounded, size: 48, color: Colors.red),
                        const SizedBox(height: 12),
                        Text(_errorMessage!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton(onPressed: _loadData, child: const Text('Coba Lagi')),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadData,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Date Selector & Rolling Shift Header
                      Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: cardBg,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: borderCol),
                        ),
                        child: Row(
                          children: [
                            InkWell(
                              onTap: _selectDate,
                              borderRadius: BorderRadius.circular(10),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                                decoration: BoxDecoration(
                                  color: AppTheme.primary.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.calendar_month_rounded, size: 18, color: AppTheme.primary),
                                    const SizedBox(width: 8),
                                    Text(
                                      DateFormat('dd MMMM yyyy').format(_selectedDate),
                                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: AppTheme.primary),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            const Spacer(),
                            if (_canManageShift)
                              ElevatedButton.icon(
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF0F172A),
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                                icon: const Icon(Icons.swap_horiz_rounded, size: 16),
                                label: const Text('Rolling Shift', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                                onPressed: _confirmRollingShift,
                              ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),

                      // Quick Stats Ribbon
                      Row(
                        children: [
                          Expanded(
                            child: _buildStatCard('Total Anggota', '${_members.length}', Icons.groups_rounded, Colors.blue, cardBg, borderCol, textHead, textSub),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _buildStatCard('Shift 1 (Pagi)', '$shift1Count', Icons.wb_sunny_rounded, Colors.indigo, cardBg, borderCol, textHead, textSub),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _buildStatCard('Shift 2 (Malam)', '$shift2Count', Icons.nightlight_round, Colors.purple, cardBg, borderCol, textHead, textSub),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _buildStatCard('Mangkir', '$mangkirCount', Icons.person_off_rounded, const Color(0xFFE11D48), cardBg, borderCol, textHead, textSub),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      Text(
                        'Daftar Anggota & Roster Shift',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: textHead),
                      ),
                      const SizedBox(height: 8),

                      if (_members.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(32),
                          decoration: BoxDecoration(
                            color: cardBg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: borderCol),
                          ),
                          child: const Center(
                            child: Text('Tidak ada data anggota di departemen ini.'),
                          ),
                        )
                      else
                        ..._members.map((m) => _buildMemberCard(m, cardBg, borderCol, textHead, textSub, isDark)),
                    ],
                  ),
                ),
    );
  }

  Widget _buildStatCard(String label, String value, IconData icon, Color color, Color bg, Color border, Color textHead, Color textSub) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: border),
      ),
      child: Column(
        children: [
          Icon(icon, size: 18, color: color),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: textHead)),
          Text(label, style: TextStyle(fontSize: 9.5, color: textSub), textAlign: TextAlign.center, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  Widget _buildMemberCard(dynamic m, Color cardBg, Color borderCol, Color textHead, Color textSub, bool isDark) {
    final member = m as Map<String, dynamic>;
    final empId = (member['id'] as num).toInt();
    final name = member['nama_lengkap']?.toString() ?? '';
    final nik = member['nik']?.toString() ?? '';
    final jabatan = member['nama_jabatan']?.toString() ?? '';
    final currentShift = member['current_shift']?.toString() ?? 'Shift 1';
    final sisaCuti = member['sisa_cuti'] != null ? (member['sisa_cuti'] as num).toDouble() : 0.0;
    final statusKehadiran = member['status_kehadiran']?.toString() ?? 'Hadir';
    final isMangkir = member['is_mangkir'] == true;
    final isRevised = member['is_revised'] == true;
    final catatan = member['catatan']?.toString() ?? '';

    final isShift2 = currentShift.contains('Shift 2');

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isMangkir ? const Color(0xFFFCA5A5) : borderCol,
          width: isMangkir ? 1.5 : 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar
              CircleAvatar(
                radius: 20,
                backgroundColor: isShift2 ? const Color(0xFF831843).withOpacity(0.15) : const Color(0xFF0369A1).withOpacity(0.15),
                child: Text(
                  name.isNotEmpty ? name[0].toUpperCase() : '?',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    color: isShift2 ? const Color(0xFFBE123C) : const Color(0xFF1D4ED8),
                  ),
                ),
              ),
              const SizedBox(width: 12),

              // Name & details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(name, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                    const SizedBox(height: 2),
                    Text('$nik • $jabatan', style: TextStyle(fontSize: 11, color: textSub)),
                    const SizedBox(height: 4),
                    Text(
                      'Sisa Cuti: ${sisaCuti == 0.5 ? "0.5" : sisaCuti.toInt()} Hari',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF059669)),
                    ),
                  ],
                ),
              ),

              // Shift badge & Toggle
              InkWell(
                onTap: _canManageShift ? () => _toggleShift(empId, currentShift, name) : null,
                borderRadius: BorderRadius.circular(10),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: isShift2 ? const Color(0xFFFFF1F2) : const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(
                      color: isShift2 ? const Color(0xFFFDA4AF) : const Color(0xFFBFDBFE),
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        isShift2 ? Icons.nightlight_round : Icons.wb_sunny_rounded,
                        size: 13,
                        color: isShift2 ? const Color(0xFFE11D48) : const Color(0xFF2563EB),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        currentShift.contains('Shift 2') ? 'Shift 2 (Malam)' : 'Shift 1 (Pagi)',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: isShift2 ? const Color(0xFFBE123C) : const Color(0xFF1D4ED8),
                        ),
                      ),
                      if (_canManageShift) ...[
                        const SizedBox(width: 4),
                        const Icon(Icons.sync_alt_rounded, size: 12, color: AppTheme.textSecondary),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 10),
          const Divider(height: 1),
          const SizedBox(height: 10),

          // Attendance status row & action buttons
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: isMangkir
                      ? const Color(0xFFFFF1F2)
                      : (isRevised
                          ? const Color(0xFFFEF3C7)
                          : (statusKehadiran.contains('Cuti') ? const Color(0xFFEFF6FF) : const Color(0xFFECFDF5))),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  statusKehadiran,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isMangkir
                        ? const Color(0xFFE11D48)
                        : (isRevised
                            ? const Color(0xFFD97706)
                            : (statusKehadiran.contains('Cuti') ? const Color(0xFF2563EB) : const Color(0xFF059669))),
                  ),
                ),
              ),
              const Spacer(),

              // Action buttons (MaoneArt Glassmorphism standard)
              if (_isLeaderOrSpv || _canManageShift) ...[
                if (isMangkir)
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFD97706),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      visualDensity: VisualDensity.compact,
                    ),
                    icon: const Icon(Icons.edit_note_rounded, size: 14),
                    label: const Text('Revisi Mangkir', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    onPressed: () => _showRevisiDialog(member),
                  )
                else if (!statusKehadiran.contains('Cuti') && !isRevised)
                  OutlinedButton.icon(
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFFE11D48),
                      side: const BorderSide(color: Color(0xFFFDA4AF)),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                      visualDensity: VisualDensity.compact,
                    ),
                    icon: const Icon(Icons.person_off_rounded, size: 14),
                    label: const Text('Tandai Mangkir', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                    onPressed: () => _showMangkirDialog(member),
                  ),
              ],
            ],
          ),

          if (catatan.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text('Ket: $catatan', style: TextStyle(fontSize: 11, color: textSub, fontStyle: FontStyle.italic)),
          ],
        ],
      ),
    );
  }
}
