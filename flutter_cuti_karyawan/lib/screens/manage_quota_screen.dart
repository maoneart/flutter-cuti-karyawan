import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import '../config/app_theme.dart';
import '../services/api_service.dart';

class ManageQuotaScreen extends StatefulWidget {
  const ManageQuotaScreen({super.key});

  @override
  State<ManageQuotaScreen> createState() => _ManageQuotaScreenState();
}

class _ManageQuotaScreenState extends State<ManageQuotaScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  bool _isLoading = true;
  String? _errorMessage;

  Map<String, dynamic>? _summary;
  List<dynamic> _employees = [];
  List<dynamic> _departments = [];
  List<dynamic> _positions = [];
  List<dynamic> _quotaLogs = [];
  bool _isLoadingLogs = false;

  int? _selectedDeptId; // null = Semua Departemen

  // Bulk form controllers
  String _bulkScope = 'all'; // 'all', 'department', 'position'
  int _bulkTargetId = 0;
  String _bulkAction = 'reset'; // 'reset', 'add'
  final TextEditingController _bulkAmountController = TextEditingController(text: '12');
  final TextEditingController _bulkNotesController = TextEditingController(text: 'Alokasi Jatah Kuota Cuti Tahunan Massal');
  bool _isSubmittingBulk = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _tabController.addListener(() {
      if (_tabController.index == 2 && _quotaLogs.isEmpty && !_isLoadingLogs) {
        _loadQuotaLogs();
      }
    });
    _loadQuotaData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    _bulkAmountController.dispose();
    _bulkNotesController.dispose();
    super.dispose();
  }

  Future<void> _loadQuotaData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final res = await ApiService.getQuotasList(
      deptId: _selectedDeptId,
      search: _searchController.text.trim(),
    );

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _summary = res.data!['summary'] as Map<String, dynamic>?;
        _employees = res.data!['employees'] as List<dynamic>? ?? [];
        _departments = res.data!['departments'] as List<dynamic>? ?? [];
        _positions = res.data!['positions'] as List<dynamic>? ?? [];
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res.message;
        _isLoading = false;
      });
    }
  }

  Future<void> _loadQuotaLogs() async {
    if (!mounted) return;
    setState(() {
      _isLoadingLogs = true;
    });

    final res = await ApiService.getQuotaLogs(limit: 50);

    if (!mounted) return;

    if (res.success && res.data != null) {
      setState(() {
        _quotaLogs = res.data!;
        _isLoadingLogs = false;
      });
    } else {
      setState(() {
        _isLoadingLogs = false;
      });
    }
  }

  void _showAdjustModal(Map<String, dynamic> emp) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final totalController = TextEditingController(text: '${emp['kuota_cuti'] ?? 12}');
    final notesController = TextEditingController(text: 'Penetapan Hak Jatah Cuti Tahunan');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            final used = emp['cuti_terpakai'] ?? 0;
            final currentTotal = int.tryParse(totalController.text) ?? 12;
            final calculatedSisa = (currentTotal - used) < 0 ? 0 : (currentTotal - used);

            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white24 : Colors.grey[300],
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF4F46E5).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(CupertinoIcons.slider_horizontal_3, color: Color(0xFF4F46E5), size: 24),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Ubah Hak Jatah Cuti',
                                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                              ),
                              Text(
                                '${emp['nama_lengkap']} (${emp['nik']})',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          Column(
                            children: [
                              Text('Dept / Jabatan', style: TextStyle(fontSize: 11, color: isDark ? const Color(0xFF94A3B8) : Colors.grey[600])),
                              const SizedBox(height: 2),
                              Text('${emp['nama_dept']} • ${emp['nama_jabatan']}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            ],
                          ),
                          Container(width: 1, height: 28, color: Colors.grey[400]),
                          Column(
                            children: [
                              Text('Cuti Terpakai', style: TextStyle(fontSize: 11, color: isDark ? const Color(0xFF94A3B8) : Colors.grey[600])),
                              const SizedBox(height: 2),
                              Text('$used Hari', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12, color: Color(0xFFE11D48))),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      'Total Kuota Cuti Tahunan Baru',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: totalController,
                      keyboardType: TextInputType.number,
                      onChanged: (_) => setModalState(() {}),
                      decoration: InputDecoration(
                        suffixText: 'Hari',
                        prefixIcon: const Icon(CupertinoIcons.calendar_today, size: 20),
                        hintText: 'Contoh: 12',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF0284C7).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: const Color(0xFF0284C7).withOpacity(0.3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.info_outline, color: Color(0xFF0284C7), size: 20),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Sisa saldo cuti baru akan otomatis menjadi $calculatedSisa Hari (Total $currentTotal - Terpakai $used).',
                              style: const TextStyle(fontSize: 12, color: Color(0xFF0284C7), fontWeight: FontWeight.w600),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      'Keterangan / Alasan Perubahan',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: notesController,
                      decoration: InputDecoration(
                        hintText: 'Misal: Penetapan hak kuota manajerial...',
                        prefixIcon: const Icon(CupertinoIcons.pencil, size: 20),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton.icon(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4F46E5),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        icon: const Icon(Icons.save_rounded),
                        label: const Text('Simpan Perubahan Kuota', style: TextStyle(fontWeight: FontWeight.bold)),
                        onPressed: () async {
                          final newTotal = int.tryParse(totalController.text);
                          if (newTotal == null || newTotal < 0) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Masukkan angka kuota yang valid!')),
                            );
                            return;
                          }

                          Navigator.pop(ctx);
                          final res = await ApiService.adjustQuota(
                            employeeId: emp['id'],
                            mode: 'set_total',
                            totalKuota: newTotal,
                            keterangan: notesController.text.trim(),
                          );

                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(res.message),
                                backgroundColor: res.success ? Colors.green : Colors.red,
                              ),
                            );
                            if (res.success) {
                              _loadQuotaData();
                              _loadQuotaLogs();
                            }
                          }
                        },
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  void _showDeltaAdjustModal(Map<String, dynamic> emp) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final deltaController = TextEditingController(text: '1');
    final notesController = TextEditingController(text: 'Penyesuaian reward / kompensasi');
    bool isAdd = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (ctx, setModalState) {
            final currentSisa = emp['sisa_cuti'] ?? 0;
            final amount = int.tryParse(deltaController.text) ?? 1;
            final deltaVal = isAdd ? amount : -amount;
            final newSisa = (currentSisa + deltaVal) < 0 ? 0 : (currentSisa + deltaVal);

            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(ctx).viewInsets.bottom + 24,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white24 : Colors.grey[300],
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFF059669).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(CupertinoIcons.plus_slash_minus, color: Color(0xFF059669), size: 24),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Tambah / Kurang Saldo Hari',
                                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                              ),
                              Text(
                                '${emp['nama_lengkap']} (Sisa saat ini: $currentSisa Hari)',
                                style: TextStyle(
                                  fontSize: 12.5,
                                  color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: ChoiceChip(
                            label: const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.add_circle, color: Color(0xFF059669), size: 16),
                                SizedBox(width: 6),
                                Text('Tambah (+ Hari)', style: TextStyle(fontWeight: FontWeight.bold)),
                              ],
                            ),
                            selected: isAdd,
                            onSelected: (val) => setModalState(() => isAdd = true),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: ChoiceChip(
                            label: const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.remove_circle, color: Color(0xFFE11D48), size: 16),
                                SizedBox(width: 6),
                                Text('Kurang (- Hari)', style: TextStyle(fontWeight: FontWeight.bold)),
                              ],
                            ),
                            selected: !isAdd,
                            onSelected: (val) => setModalState(() => isAdd = false),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Text(
                      'Jumlah Hari',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: deltaController,
                      keyboardType: TextInputType.number,
                      onChanged: (_) => setModalState(() {}),
                      decoration: InputDecoration(
                        suffixText: 'Hari',
                        hintText: '1',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: (isAdd ? const Color(0xFF059669) : const Color(0xFFE11D48)).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: (isAdd ? const Color(0xFF059669) : const Color(0xFFE11D48)).withOpacity(0.3)),
                      ),
                      child: Text(
                        'Hasil penyesuaian: Sisa saldo akan menjadi $newSisa Hari.',
                        style: TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.bold,
                          color: isAdd ? const Color(0xFF059669) : const Color(0xFFE11D48),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      'Keterangan / Alasan',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : AppTheme.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    TextField(
                      controller: notesController,
                      decoration: InputDecoration(
                        hintText: 'Misal: Kompensasi lembur / reward...',
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isAdd ? const Color(0xFF059669) : const Color(0xFFE11D48),
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        onPressed: () async {
                          final val = int.tryParse(deltaController.text);
                          if (val == null || val <= 0) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Masukkan jumlah hari yang valid (> 0)!')),
                            );
                            return;
                          }

                          Navigator.pop(ctx);
                          final res = await ApiService.adjustQuota(
                            employeeId: emp['id'],
                            mode: 'delta',
                            delta: isAdd ? val : -val,
                            keterangan: notesController.text.trim(),
                          );

                          if (mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(res.message),
                                backgroundColor: res.success ? Colors.green : Colors.red,
                              ),
                            );
                            if (res.success) {
                              _loadQuotaData();
                              _loadQuotaLogs();
                            }
                          }
                        },
                        child: Text(
                          isAdd ? 'Tambahkan Hari Cuti' : 'Kurangkan Hari Cuti',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Future<void> _submitBulkAllocation() async {
    final amount = int.tryParse(_bulkAmountController.text);
    if (amount == null || amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Jumlah hari kuota harus lebih dari 0!')),
      );
      return;
    }

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.orange),
            SizedBox(width: 8),
            Text('Konfirmasi Alokasi Massal'),
          ],
        ),
        content: Text(
          _bulkAction == 'reset'
              ? 'Tindakan ini akan me-RESET hak kuota tahunan menjadi $amount Hari dan mengosongkan Cuti Terpakai untuk target yang dipilih. Lanjutkan?'
              : 'Tindakan ini akan MENAMBAHKAN +$amount Hari ke saldo kuota untuk seluruh target yang dipilih. Lanjutkan?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('Batal')),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF4F46E5), foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Ya, Jalankan'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isSubmittingBulk = true);

    final res = await ApiService.bulkAllocateQuota(
      targetScope: _bulkScope,
      targetId: _bulkTargetId,
      batchAction: _bulkAction,
      quotaAmount: amount,
      keterangan: _bulkNotesController.text.trim(),
    );

    if (!mounted) return;
    setState(() => _isSubmittingBulk = false);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(res.message),
        backgroundColor: res.success ? Colors.green : Colors.red,
      ),
    );

    if (res.success) {
      _loadQuotaData();
      _loadQuotaLogs();
      _tabController.animateTo(0);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final textSub = isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Kelola Jatah Cuti', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () {
              _loadQuotaData();
              _loadQuotaLogs();
            },
            tooltip: 'Refresh Data',
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: isDark ? const Color(0xFF38BDF8) : const Color(0xFF4F46E5),
          labelColor: isDark ? const Color(0xFF38BDF8) : const Color(0xFF4F46E5),
          unselectedLabelColor: isDark ? const Color(0xFF94A3B8) : Colors.grey[600],
          tabs: const [
            Tab(icon: Icon(CupertinoIcons.person_3_fill, size: 18), text: 'Daftar Saldo'),
            Tab(icon: Icon(CupertinoIcons.arrows_2_flat, size: 18), text: 'Alokasi Massal'),
            Tab(icon: Icon(CupertinoIcons.time, size: 18), text: 'Riwayat Mutasi'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // TAB 1: Daftar Saldo Karyawan
          _buildEmployeeListTab(isDark, cardBg, borderCol, textHead, textSub),

          // TAB 2: Alokasi Massal (Bulk)
          _buildBulkAllocationTab(isDark, cardBg, borderCol, textHead, textSub),

          // TAB 3: Riwayat Mutasi Kuota (Logs)
          _buildQuotaLogsTab(isDark, cardBg, borderCol, textHead, textSub),
        ],
      ),
    );
  }

  Widget _buildEmployeeListTab(bool isDark, Color cardBg, Color borderCol, Color textHead, Color textSub) {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 12),
              Text(_errorMessage!, textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton(onPressed: _loadQuotaData, child: const Text('Coba Lagi')),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadQuotaData,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // 1. Quota Summary Cards
          if (_summary != null)
            Row(
              children: [
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF4F46E5).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFF4F46E5).withOpacity(0.25)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Total Hak Kuota', style: TextStyle(fontSize: 11, color: Color(0xFF4F46E5), fontWeight: FontWeight.w600)),
                        const SizedBox(height: 4),
                        Text('${_summary!['total_kuota']} Hari', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF4F46E5))),
                        Text('${_summary!['total_karyawan']} Pegawai Aktif', style: TextStyle(fontSize: 10.5, color: textSub)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE11D48).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE11D48).withOpacity(0.25)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Total Terpakai', style: TextStyle(fontSize: 11, color: Color(0xFFE11D48), fontWeight: FontWeight.w600)),
                        const SizedBox(height: 4),
                        Text('${_summary!['total_terpakai']} Hari', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFFE11D48))),
                        Text('Cuti Diambil', style: TextStyle(fontSize: 10.5, color: textSub)),
                      ],
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF059669).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFF059669).withOpacity(0.25)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Sisa Saldo', style: TextStyle(fontSize: 11, color: Color(0xFF059669), fontWeight: FontWeight.w600)),
                        const SizedBox(height: 4),
                        Text('${_summary!['total_sisa']} Hari', style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF059669))),
                        Text('Tersedia', style: TextStyle(fontSize: 10.5, color: textSub)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          const SizedBox(height: 14),

          // 2. Search Field
          TextField(
            controller: _searchController,
            decoration: InputDecoration(
              hintText: 'Cari nama karyawan, NIK, jabatan...',
              prefixIcon: const Icon(CupertinoIcons.search, size: 20),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, size: 18),
                      onPressed: () {
                        _searchController.clear();
                        _loadQuotaData();
                      },
                    )
                  : null,
              filled: true,
              fillColor: cardBg,
              contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(color: borderCol),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(color: borderCol),
              ),
            ),
            onSubmitted: (_) => _loadQuotaData(),
          ),
          const SizedBox(height: 10),

          // 3. Department Chips Filter
          SizedBox(
            height: 38,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: [
                Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: FilterChip(
                    label: const Text('Semua Dept', style: TextStyle(fontSize: 11.5)),
                    selected: _selectedDeptId == null,
                    onSelected: (val) {
                      setState(() => _selectedDeptId = null);
                      _loadQuotaData();
                    },
                  ),
                ),
                ..._departments.map((dept) {
                  final isSelected = _selectedDeptId == dept['id'];
                  return Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: FilterChip(
                      label: Text(dept['kode_dept'] ?? dept['nama_dept'], style: const TextStyle(fontSize: 11.5)),
                      selected: isSelected,
                      onSelected: (val) {
                        setState(() => _selectedDeptId = val ? dept['id'] : null);
                        _loadQuotaData();
                      },
                    ),
                  );
                }),
              ],
            ),
          ),
          const SizedBox(height: 12),

          // 4. Employee List Cards
          if (_employees.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              alignment: Alignment.center,
              child: Column(
                children: [
                  Icon(CupertinoIcons.person_3, size: 48, color: textSub.withOpacity(0.5)),
                  const SizedBox(height: 12),
                  Text('Tidak ada karyawan ditemukan', style: TextStyle(color: textSub, fontWeight: FontWeight.bold)),
                ],
              ),
            )
          else
            ..._employees.map((emp) {
              return Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: borderCol),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.02),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        CircleAvatar(
                          radius: 20,
                          backgroundColor: const Color(0xFF4F46E5).withOpacity(0.12),
                          child: Text(
                            (emp['nama_lengkap'] as String? ?? 'U').substring(0, 1).toUpperCase(),
                            style: const TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.bold, fontSize: 16),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                emp['nama_lengkap'] ?? '-',
                                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14.5, color: textHead),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${emp['nik']} • ${emp['nama_jabatan']}',
                                style: TextStyle(fontSize: 12, color: textSub),
                              ),
                              Text(
                                'Dept: ${emp['nama_dept']}',
                                style: TextStyle(fontSize: 11.5, color: textSub),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFF059669).withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFF059669).withOpacity(0.3)),
                          ),
                          child: Text(
                            'Sisa: ${emp['sisa_cuti']} Hari',
                            style: const TextStyle(color: Color(0xFF059669), fontWeight: FontWeight.bold, fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          Text('Hak Total: ${emp['kuota_cuti']} Hari', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600)),
                          const Text('•', style: TextStyle(color: Colors.grey)),
                          Text('Terpakai: ${emp['cuti_terpakai']} Hari', style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: Color(0xFFE11D48))),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            style: OutlinedButton.styleFrom(
                              foregroundColor: const Color(0xFF4F46E5),
                              side: const BorderSide(color: Color(0xFF4F46E5)),
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            icon: const Icon(CupertinoIcons.slider_horizontal_3, size: 16),
                            label: const Text('Ubah Hak Kuota', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                            onPressed: () => _showAdjustModal(emp),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF059669),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            ),
                            icon: const Icon(CupertinoIcons.plus_slash_minus, size: 16),
                            label: const Text('Sesuaikan (+/-)', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                            onPressed: () => _showDeltaAdjustModal(emp),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              );
            }),
        ],
      ),
    );
  }

  Widget _buildBulkAllocationTab(bool isDark, Color cardBg, Color borderCol, Color textHead, Color textSub) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Guidance Banner
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1E1B4B), Color(0xFF312E81)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Row(
              children: [
                Icon(CupertinoIcons.wand_stars, color: Color(0xFF818CF8), size: 28),
                SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Alokasi Cepat Skala Besar',
                        style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Bagikan jatah cuti tahunan ke ratusan karyawan sekaligus dalam 1 detik.',
                        style: TextStyle(color: Colors.white70, fontSize: 11.5),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          Text('1. Target Penerima Alokasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            value: _bulkScope,
            decoration: InputDecoration(
              filled: true,
              fillColor: cardBg,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: borderCol)),
            ),
            items: const [
              DropdownMenuItem(value: 'all', child: Text('Seluruh Karyawan Aktif')),
              DropdownMenuItem(value: 'department', child: Text('Per Departemen Tertentu')),
              DropdownMenuItem(value: 'position', child: Text('Per Jabatan Tertentu')),
            ],
            onChanged: (val) {
              if (val != null) {
                setState(() {
                  _bulkScope = val;
                  _bulkTargetId = 0;
                });
              }
            },
          ),
          const SizedBox(height: 12),

          if (_bulkScope == 'department') ...[
            Text('Pilih Departemen Target', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
            const SizedBox(height: 6),
            DropdownButtonFormField<int>(
              value: _bulkTargetId == 0 ? (_departments.isNotEmpty ? _departments[0]['id'] : 0) : _bulkTargetId,
              decoration: InputDecoration(
                filled: true,
                fillColor: cardBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
              items: _departments.map<DropdownMenuItem<int>>((d) {
                return DropdownMenuItem<int>(
                  value: d['id'] as int,
                  child: Text('${d['nama_dept']} (${d['kode_dept']})'),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null) setState(() => _bulkTargetId = val);
              },
            ),
            const SizedBox(height: 16),
          ],

          if (_bulkScope == 'position') ...[
            Text('Pilih Jabatan Target', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: textSub)),
            const SizedBox(height: 6),
            DropdownButtonFormField<int>(
              value: _bulkTargetId == 0 ? (_positions.isNotEmpty ? _positions[0]['id'] : 0) : _bulkTargetId,
              decoration: InputDecoration(
                filled: true,
                fillColor: cardBg,
                border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
              items: _positions.map<DropdownMenuItem<int>>((p) {
                return DropdownMenuItem<int>(
                  value: p['id'] as int,
                  child: Text('${p['nama_jabatan']} (Level ${p['level_hierarki']})'),
                );
              }).toList(),
              onChanged: (val) {
                if (val != null) setState(() => _bulkTargetId = val);
              },
            ),
            const SizedBox(height: 16),
          ],

          Text('2. Mode Alokasi Kuota', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: ChoiceChip(
                  label: const Text('Reset Tahunan Baru', style: TextStyle(fontWeight: FontWeight.bold)),
                  selected: _bulkAction == 'reset',
                  onSelected: (val) => setState(() => _bulkAction = 'reset'),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: ChoiceChip(
                  label: const Text('Tambah (+ Hari)', style: TextStyle(fontWeight: FontWeight.bold)),
                  selected: _bulkAction == 'add',
                  onSelected: (val) => setState(() => _bulkAction = 'add'),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          Text('3. Jumlah Hari Kuota', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
          const SizedBox(height: 8),
          TextField(
            controller: _bulkAmountController,
            keyboardType: TextInputType.number,
            decoration: InputDecoration(
              suffixText: 'Hari',
              filled: true,
              fillColor: cardBg,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 16),

          Text('4. Keterangan Mutasi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: textHead)),
          const SizedBox(height: 8),
          TextField(
            controller: _bulkNotesController,
            decoration: InputDecoration(
              hintText: 'Misal: Alokasi Kuota Tahunan 2026...',
              filled: true,
              fillColor: cardBg,
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
            ),
          ),
          const SizedBox(height: 24),

          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4F46E5),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              icon: _isSubmittingBulk ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Icon(Icons.flash_on_rounded),
              label: Text(_isSubmittingBulk ? 'Memproses Alokasi...' : 'Eksekusi Alokasi Massal', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              onPressed: _isSubmittingBulk ? null : _submitBulkAllocation,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuotaLogsTab(bool isDark, Color cardBg, Color borderCol, Color textHead, Color textSub) {
    if (_isLoadingLogs) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_quotaLogs.isEmpty) {
      return RefreshIndicator(
        onRefresh: _loadQuotaLogs,
        child: ListView(
          padding: const EdgeInsets.all(32),
          children: [
            Center(
              child: Column(
                children: [
                  Icon(CupertinoIcons.doc_text_search, size: 48, color: textSub.withOpacity(0.5)),
                  const SizedBox(height: 12),
                  Text('Belum ada riwayat mutasi kuota tercatat.', style: TextStyle(color: textSub, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  ElevatedButton(onPressed: _loadQuotaLogs, child: const Text('Muat Ulang')),
                ],
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadQuotaLogs,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _quotaLogs.length,
        itemBuilder: (ctx, i) {
          final log = _quotaLogs[i];
          final perubahan = log['perubahan'] ?? 0;
          final isPositive = perubahan >= 0;

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: borderCol),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: (isPositive ? const Color(0xFF059669) : const Color(0xFFE11D48)).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    isPositive ? Icons.arrow_upward_rounded : Icons.arrow_downward_rounded,
                    color: isPositive ? const Color(0xFF059669) : const Color(0xFFE11D48),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            log['nama_lengkap'] ?? '-',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13.5, color: textHead),
                          ),
                          Text(
                            '${isPositive ? "+" : ""}$perubahan Hari',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: isPositive ? const Color(0xFF059669) : const Color(0xFFE11D48),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${log['nama_dept']} • ${log['nama_jabatan']}',
                        style: TextStyle(fontSize: 11.5, color: textSub),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        log['keterangan'] ?? '-',
                        style: TextStyle(fontSize: 12, color: textHead),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            'Saldo: ${log['kuota_sebelum']} ➔ ${log['kuota_sesudah']} Hari',
                            style: TextStyle(fontSize: 11, color: isDark ? const Color(0xFF38BDF8) : const Color(0xFF0284C7), fontWeight: FontWeight.w600),
                          ),
                          Text(
                            log['created_at'] != null ? log['created_at'].toString().substring(0, 16) : '',
                            style: TextStyle(fontSize: 10.5, color: textSub),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
