import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import 'package:intl/intl.dart';
import 'package:file_picker/file_picker.dart';
import '../config/app_theme.dart';
import '../models/leave_type_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

class LeaveRequestScreen extends StatefulWidget {
  final VoidCallback? onLeaveSubmitted;

  const LeaveRequestScreen({super.key, this.onLeaveSubmitted});

  @override
  State<LeaveRequestScreen> createState() => _LeaveRequestScreenState();
}

class _LeaveRequestScreenState extends State<LeaveRequestScreen> {
  final _formKey = GlobalKey<FormState>();
  
  List<LeaveTypeModel> _leaveTypes = [];
  LeaveTypeModel? _selectedType;
  String _selectedCategoryGroup = 'cuti';
  String _selectedShift = 'Shift 1 (Pagi)';
  DateTime? _startDate;
  DateTime? _endDate;
  double _calculatedDays = 0.0;
  
  final _alasanController = TextEditingController();
  final _alamatController = TextEditingController();
  final _kontakDaruratController = TextEditingController();

  String? _attachmentBase64;
  String? _attachmentName;
  int? _attachmentSizeBytes;
  
  bool _isLoadingTypes = true;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadLeaveTypes();
  }

  @override
  void dispose() {
    _alasanController.dispose();
    _alamatController.dispose();
    _kontakDaruratController.dispose();
    super.dispose();
  }

  Future<void> _loadLeaveTypes() async {
    setState(() {
      _isLoadingTypes = true;
      _errorMessage = null;
    });

    final res = await ApiService.getLeaveTypes();

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _leaveTypes = res.data!;
        if (_leaveTypes.isNotEmpty) {
          final cutiList = _leaveTypes.where((t) => t.isCuti).toList();
          if (cutiList.isNotEmpty) {
            _selectedType = cutiList.firstWhere(
              (t) => t.kode == 'CT' && t.eligible,
              orElse: () => cutiList.firstWhere((t) => t.eligible, orElse: () => cutiList.first),
            );
          } else {
            _selectedType = _leaveTypes.first;
          }
        }
        _isLoadingTypes = false;
      });
      _calculateDays();
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoadingTypes = false;
      });
    }
  }

  void _calculateDays() {
    if (_selectedType?.kode == 'CT-HALF' || _selectedType?.kode == 'PC-PRI' || _selectedType?.kode == 'PC-SKT' || _selectedType?.kode == 'IK-TMP') {
      setState(() {
        _calculatedDays = 0.5;
        if (_startDate != null) _endDate = _startDate;
      });
      return;
    }

    if (_startDate == null || _endDate == null) {
      setState(() {
        _calculatedDays = 0.0;
      });
      return;
    }

    if (_endDate!.isBefore(_startDate!)) {
      setState(() {
        _calculatedDays = 0.0;
      });
      return;
    }

    int days = 0;
    DateTime current = _startDate!;
    while (!current.isAfter(_endDate!)) {
      if (current.weekday != DateTime.sunday && current.weekday != DateTime.saturday) {
        days++;
      }
      current = current.add(const Duration(days: 1));
    }

    setState(() {
      _calculatedDays = (days == 0 ? 1 : days).toDouble();
    });
  }

  Future<void> _pickDateRange() async {
    final now = DateTime.now();

    if (_selectedType?.kode == 'CT-HALF' || _selectedType?.kode == 'PC-PRI' || _selectedType?.kode == 'PC-SKT' || _selectedType?.kode == 'IK-TMP') {
      final singlePicked = await showDatePicker(
        context: context,
        initialDate: _startDate ?? now,
        firstDate: now.subtract(const Duration(days: 30)),
        lastDate: now.add(const Duration(days: 365)),
      );
      if (singlePicked != null) {
        setState(() {
          _startDate = singlePicked;
          _endDate = singlePicked;
          _calculatedDays = 0.5;
        });
      }
      return;
    }

    final picked = await showDateRangePicker(
      context: context,
      firstDate: now.subtract(const Duration(days: 30)),
      lastDate: now.add(const Duration(days: 365)),
      initialDateRange: (_startDate != null && _endDate != null)
          ? DateTimeRange(start: _startDate!, end: _endDate!)
          : DateTimeRange(start: now, end: now.add(const Duration(days: 1))),
    );

    if (picked != null) {
      setState(() {
        _startDate = picked.start;
        _endDate = picked.end;
      });
      _calculateDays();
    }
  }

  Future<void> _pickAttachment() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['jpg', 'jpeg', 'png', 'pdf'],
        withData: true,
      );

      if (result != null && result.files.isNotEmpty) {
        final file = result.files.first;
        if (file.bytes != null) {
          if (file.size > 5 * 1024 * 1024) {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Ukuran file maksimal 5MB'),
                  backgroundColor: Colors.red,
                ),
              );
            }
            return;
          }

          setState(() {
            _attachmentBase64 = base64Encode(file.bytes!);
            _attachmentName = file.name;
            _attachmentSizeBytes = file.size;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal memilih file: $e')),
        );
      }
    }
  }

  void _removeAttachment() {
    setState(() {
      _attachmentBase64 = null;
      _attachmentName = null;
      _attachmentSizeBytes = null;
    });
  }

  Future<void> _submitLeave() async {
    if (!_formKey.currentState!.validate()) return;

    if (_selectedType == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Silakan pilih jenis cuti terlebih dahulu.')),
      );
      return;
    }

    if (_startDate == null || _endDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Silakan tentukan rentang tanggal cuti.')),
      );
      return;
    }

    if (_selectedType!.butuhLampiran && _attachmentBase64 == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppTheme.statusRejected,
          content: Text('Jenis cuti "${_selectedType!.namaCuti}" mewajibkan unggah dokumen/surat dokter!'),
        ),
      );
      return;
    }

    setState(() {
      _isSubmitting = true;
    });

    final res = await ApiService.submitLeave(
      leaveTypeId: _selectedType!.id,
      tanggalMulai: DateFormat('yyyy-MM-dd').format(_startDate!),
      tanggalSelesai: DateFormat('yyyy-MM-dd').format(_endDate!),
      shift: _selectedShift,
      totalHari: _calculatedDays,
      alasan: _alasanController.text.trim(),
      alamatSelamaCuti: _alamatController.text.trim(),
      kontakDarurat: _kontakDaruratController.text.trim(),
      attachmentBase64: _attachmentBase64,
      attachmentName: _attachmentName,
    );

    if (!mounted) return;

    setState(() {
      _isSubmitting = false;
    });

    if (res.success) {
      final daysText = _calculatedDays == 0.5 ? '0.5' : (_calculatedDays.truncateToDouble() == _calculatedDays ? _calculatedDays.toInt().toString() : _calculatedDays.toString());
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: AppTheme.statusApprovedBg,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded, color: AppTheme.statusApproved, size: 48),
              ),
              const SizedBox(height: 16),
              const Text(
                'Pengajuan Berhasil Dikirim!',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 8),
              Text(
                'Pengajuan cuti $daysText hari ($_selectedShift) Anda telah tercatat dan masuk ke antrean persetujuan atasan.',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary),
              ),
            ],
          ),
          actions: [
            ElevatedButton(
              onPressed: () {
                Navigator.pop(ctx);
                setState(() {
                  _startDate = null;
                  _endDate = null;
                  _calculatedDays = 0;
                  _alasanController.clear();
                  _alamatController.clear();
                  _kontakDaruratController.clear();
                  _attachmentBase64 = null;
                  _attachmentName = null;
                  _attachmentSizeBytes = null;
                });
                widget.onLeaveSubmitted?.call();
              },
              child: const Text('Lihat di Riwayat Cuti'),
            ),
          ],
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          backgroundColor: AppTheme.statusRejected,
          content: Text(res.message),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthService.currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      appBar: AppBar(
        title: Text('Form Pengajuan Cuti', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
      ),
      body: _isLoadingTypes
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 600),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Quota Indicator Banner
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: cardBg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: borderCol),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: primaryAccent.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Icon(Icons.account_balance_wallet_outlined, color: primaryAccent, size: 24),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text('Sisa Kuota Cuti Anda', style: TextStyle(fontSize: 12, color: textSub)),
                                    Text(
                                      '${user?.sisaCuti ?? 0} Hari',
                                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: primaryAccent),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Form Container
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            color: cardBg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: borderCol),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // 1. Pilihan Kategori: Cuti vs Izin
                              Text('1. Pilih Kategori Permohonan *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                              const SizedBox(height: 10),
                              Row(
                                children: [
                                  Expanded(
                                    child: InkWell(
                                      onTap: () {
                                        setState(() {
                                          _selectedCategoryGroup = 'cuti';
                                          final cutiList = _leaveTypes.where((t) => t.isCuti).toList();
                                          if (cutiList.isNotEmpty) {
                                            _selectedType = cutiList.firstWhere(
                                              (t) => t.eligible,
                                              orElse: () => cutiList.first,
                                            );
                                          }
                                        });
                                        _calculateDays();
                                      },
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        decoration: BoxDecoration(
                                          color: _selectedCategoryGroup == 'cuti'
                                              ? (isDark ? const Color(0xFF0369A1).withOpacity(0.35) : const Color(0xFFEFF6FF))
                                              : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: _selectedCategoryGroup == 'cuti' ? const Color(0xFF0284C7) : borderCol,
                                            width: _selectedCategoryGroup == 'cuti' ? 2 : 1,
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(
                                              Icons.beach_access_rounded,
                                              size: 18,
                                              color: _selectedCategoryGroup == 'cuti' ? const Color(0xFF0284C7) : textSub,
                                            ),
                                            const SizedBox(width: 8),
                                            Text(
                                              'Kategori Cuti',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13,
                                                color: _selectedCategoryGroup == 'cuti'
                                                    ? (isDark ? Colors.white : const Color(0xFF0369A1))
                                                    : textHead,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: InkWell(
                                      onTap: () {
                                        setState(() {
                                          _selectedCategoryGroup = 'izin';
                                          final izinList = _leaveTypes.where((t) => t.isIzin).toList();
                                          if (izinList.isNotEmpty) {
                                            _selectedType = izinList.firstWhere(
                                              (t) => t.eligible,
                                              orElse: () => izinList.first,
                                            );
                                          }
                                        });
                                        _calculateDays();
                                      },
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 12),
                                        decoration: BoxDecoration(
                                          color: _selectedCategoryGroup == 'izin'
                                              ? (isDark ? const Color(0xFF6D28D9).withOpacity(0.35) : const Color(0xFFF5F3FF))
                                              : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: _selectedCategoryGroup == 'izin' ? const Color(0xFF7C3AED) : borderCol,
                                            width: _selectedCategoryGroup == 'izin' ? 2 : 1,
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          children: [
                                            Icon(
                                              Icons.assignment_turned_in_rounded,
                                              size: 18,
                                              color: _selectedCategoryGroup == 'izin' ? const Color(0xFF7C3AED) : textSub,
                                            ),
                                            const SizedBox(width: 8),
                                            Text(
                                              'Kategori Izin & Sakit',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13,
                                                color: _selectedCategoryGroup == 'izin'
                                                    ? (isDark ? Colors.white : const Color(0xFF6D28D9))
                                                    : textHead,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),

                              // Dropdown Pilihan Jenis Sesuai Kategori
                              Text(
                                _selectedCategoryGroup == 'cuti' ? 'Pilih Jenis Cuti *' : 'Pilih Jenis Izin / Sakit *',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: textHead),
                              ),
                              const SizedBox(height: 8),
                              DropdownButtonFormField<LeaveTypeModel>(
                                value: (_leaveTypes.where((t) => _selectedCategoryGroup == 'cuti' ? t.isCuti : t.isIzin).contains(_selectedType))
                                    ? _selectedType
                                    : null,
                                isExpanded: true,
                                decoration: InputDecoration(
                                  prefixIcon: Icon(
                                    _selectedCategoryGroup == 'cuti' ? Icons.beach_access_outlined : Icons.assignment_outlined,
                                    size: 20,
                                  ),
                                ),
                                items: _leaveTypes
                                    .where((t) => _selectedCategoryGroup == 'cuti' ? t.isCuti : t.isIzin)
                                    .map((type) {
                                  return DropdownMenuItem(
                                    value: type,
                                    enabled: type.eligible,
                                    child: Row(
                                      children: [
                                        Expanded(
                                          child: Text(
                                            type.namaCuti,
                                            style: TextStyle(
                                              fontSize: 13,
                                              color: type.eligible ? (isDark ? Colors.white : AppTheme.textPrimary) : AppTheme.textMuted,
                                            ),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        if (type.potongKuota)
                                          Container(
                                            margin: const EdgeInsets.only(left: 6),
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: isDark ? const Color(0xFF451A03) : AppTheme.statusPendingBg,
                                              borderRadius: BorderRadius.circular(4),
                                            ),
                                            child: Text('Potong Kuota', style: TextStyle(fontSize: 10, color: isDark ? const Color(0xFFFBBF24) : AppTheme.statusPending, fontWeight: FontWeight.bold)),
                                          )
                                        else if (type.butuhLampiran)
                                          Container(
                                            margin: const EdgeInsets.only(left: 6),
                                            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                            decoration: BoxDecoration(
                                              color: isDark ? const Color(0xFF4C0519) : const Color(0xFFFFE4E6),
                                              borderRadius: BorderRadius.circular(4),
                                            ),
                                            child: const Text('Wajib Surat', style: TextStyle(fontSize: 10, color: Color(0xFFE11D48), fontWeight: FontWeight.bold)),
                                          ),
                                      ],
                                    ),
                                  );
                                }).toList(),
                                onChanged: (val) {
                                  setState(() {
                                    _selectedType = val;
                                  });
                                  _calculateDays();
                                },
                              ),
                              if (_selectedType?.deskripsi != null) ...[
                                const SizedBox(height: 6),
                                Text(
                                  _selectedType!.deskripsi!,
                                  style: TextStyle(fontSize: 12, color: textSub, fontStyle: FontStyle.italic),
                                ),
                              ],
                              const SizedBox(height: 18),

                              // 1b. Shift Kerja (PKB 2-Shift Nakakin)
                              Text('Shift Kerja Karyawan *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                              const SizedBox(height: 4),
                              Text('Pilih shift yang dijalani (Shift 2 dimulai malam Senin)', style: TextStyle(fontSize: 11, color: textSub)),
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  Expanded(
                                    child: InkWell(
                                      onTap: () => setState(() => _selectedShift = 'Shift 1 (Pagi)'),
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
                                        decoration: BoxDecoration(
                                          color: _selectedShift.contains('Shift 1')
                                              ? (isDark ? const Color(0xFF0369A1).withOpacity(0.4) : const Color(0xFFEFF6FF))
                                              : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: _selectedShift.contains('Shift 1') ? const Color(0xFF2563EB) : borderCol,
                                            width: _selectedShift.contains('Shift 1') ? 2 : 1,
                                          ),
                                        ),
                                        child: Column(
                                          children: [
                                            Text(
                                              'Shift 1 (Pagi)',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13,
                                                color: _selectedShift.contains('Shift 1')
                                                    ? (isDark ? Colors.white : const Color(0xFF1D4ED8))
                                                    : textHead,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              'Shift Pagi Pabrik',
                                              style: TextStyle(fontSize: 10, color: textSub),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: InkWell(
                                      onTap: () => setState(() => _selectedShift = 'Shift 2 (Malam)'),
                                      borderRadius: BorderRadius.circular(12),
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
                                        decoration: BoxDecoration(
                                          color: _selectedShift.contains('Shift 2')
                                              ? (isDark ? const Color(0xFF831843).withOpacity(0.4) : const Color(0xFFFFF1F2))
                                              : (isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC)),
                                          borderRadius: BorderRadius.circular(12),
                                          border: Border.all(
                                            color: _selectedShift.contains('Shift 2') ? const Color(0xFFE11D48) : borderCol,
                                            width: _selectedShift.contains('Shift 2') ? 2 : 1,
                                          ),
                                        ),
                                        child: Column(
                                          children: [
                                            Text(
                                              'Shift 2 (Malam)',
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13,
                                                color: _selectedShift.contains('Shift 2')
                                                    ? (isDark ? Colors.white : const Color(0xFFBE123C))
                                                    : textHead,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              'Shift Malam Pabrik',
                                              style: TextStyle(fontSize: 10, color: textSub),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 18),

                              // 2. Tanggal Cuti
                              Text('Rentang Tanggal Cuti *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                              const SizedBox(height: 8),
                              InkWell(
                                onTap: _pickDateRange,
                                borderRadius: BorderRadius.circular(12),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                  decoration: BoxDecoration(
                                    color: isDark ? const Color(0xFF0F172A) : Colors.white,
                                    border: Border.all(color: borderCol),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(Icons.date_range_rounded, color: primaryAccent, size: 20),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Text(
                                          (_startDate != null && _endDate != null)
                                              ? (_calculatedDays == 0.5 || _startDate == _endDate
                                                  ? '${DateFormat('dd MMM yyyy').format(_startDate!)} (0.5 Hari / 4 Jam)'
                                                  : '${DateFormat('dd MMM yyyy').format(_startDate!)} s/d ${DateFormat('dd MMM yyyy').format(_endDate!)}')
                                              : 'Pilih Tanggal Mulai dan Selesai',
                                          style: TextStyle(
                                            fontSize: 14,
                                            color: (_startDate != null) ? textHead : AppTheme.textMuted,
                                            fontWeight: (_startDate != null) ? FontWeight.w600 : FontWeight.normal,
                                          ),
                                        ),
                                      ),
                                      if (_calculatedDays > 0)
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: primaryAccent.withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: Text(
                                            _calculatedDays == 0.5 ? '0.5 Hari (4 Jam)' : '${_calculatedDays.truncateToDouble() == _calculatedDays ? _calculatedDays.toInt() : _calculatedDays} Hari Kerja',
                                            style: TextStyle(color: primaryAccent, fontWeight: FontWeight.bold, fontSize: 12),
                                          ),
                                        ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(height: 18),

                              // 3. Alasan Cuti
                              Text('Alasan Cuti *', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                              const SizedBox(height: 8),
                              TextFormField(
                                controller: _alasanController,
                                maxLines: 3,
                                decoration: const InputDecoration(
                                  hintText: 'Tuliskan keperluan/alasan pengajuan cuti secara jelas...',
                                ),
                                validator: (v) => (v == null || v.trim().isEmpty) ? 'Alasan cuti wajib diisi' : null,
                              ),
                              const SizedBox(height: 18),

                              // 4. Dokumen / Foto Lampiran (Upload)
                              Row(
                                children: [
                                  Text(
                                    'Lampiran / Surat Dokter',
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 14,
                                      color: textHead,
                                    ),
                                  ),
                                  if (_selectedType?.butuhLampiran == true)
                                    const Text(' * (Wajib)', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 13)),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                'Mendukung format JPG, PNG, atau PDF (Maks. 5MB).',
                                style: TextStyle(fontSize: 11.5, color: textSub),
                              ),
                              const SizedBox(height: 8),
                              if (_attachmentName == null)
                                InkWell(
                                  onTap: _pickAttachment,
                                  borderRadius: BorderRadius.circular(12),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                                    decoration: BoxDecoration(
                                      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                                      border: Border.all(color: borderCol, style: BorderStyle.solid),
                                      borderRadius: BorderRadius.circular(12),
                                    ),
                                    child: Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Icon(CupertinoIcons.cloud_upload, color: primaryAccent, size: 22),
                                        const SizedBox(width: 10),
                                        Text(
                                          'Pilih Berkas Lampiran / Foto',
                                          style: TextStyle(
                                            color: primaryAccent,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 13.5,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                )
                              else
                                Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: primaryAccent.withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: primaryAccent.withOpacity(0.3)),
                                  ),
                                  child: Row(
                                    children: [
                                      Icon(CupertinoIcons.doc_fill, color: primaryAccent, size: 24),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              _attachmentName!,
                                              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: textHead),
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                            ),
                                            Text(
                                              '${((_attachmentSizeBytes ?? 0) / 1024).toStringAsFixed(1)} KB',
                                              style: TextStyle(fontSize: 11, color: textSub),
                                            ),
                                          ],
                                        ),
                                      ),
                                      IconButton(
                                        icon: const Icon(CupertinoIcons.trash, color: Colors.red, size: 20),
                                        onPressed: _removeAttachment,
                                        tooltip: 'Hapus Berkas',
                                      ),
                                    ],
                                  ),
                                ),
                              const SizedBox(height: 18),

                              // 5. Alamat Selama Cuti
                              Text('Alamat Selama Cuti (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                              const SizedBox(height: 8),
                              TextFormField(
                                controller: _alamatController,
                                decoration: const InputDecoration(
                                  hintText: 'Alamat tempat tinggal/kampung selama cuti...',
                                  prefixIcon: Icon(Icons.location_on_outlined, size: 20),
                                ),
                              ),
                              const SizedBox(height: 18),

                              // 6. Kontak Darurat
                              Text('Kontak Darurat (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
                              const SizedBox(height: 8),
                              TextFormField(
                                controller: _kontakDaruratController,
                                keyboardType: TextInputType.phone,
                                decoration: const InputDecoration(
                                  hintText: 'No. HP / Telepon keluarga yang bisa dihubungi...',
                                  prefixIcon: Icon(Icons.phone_outlined, size: 20),
                                ),
                              ),
                              const SizedBox(height: 28),

                              // Submit Button
                              ElevatedButton(
                                onPressed: _isSubmitting ? null : _submitLeave,
                                child: _isSubmitting
                                    ? const SizedBox(
                                        height: 20,
                                        width: 20,
                                        child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation<Color>(Colors.white)),
                                      )
                                    : const Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Icon(Icons.send_rounded, size: 18),
                                          SizedBox(width: 8),
                                          Text('Kirim Pengajuan Cuti', style: TextStyle(fontSize: 16)),
                                        ],
                                      ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
    );
  }
}
