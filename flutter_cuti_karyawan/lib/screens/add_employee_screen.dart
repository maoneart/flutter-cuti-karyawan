import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../services/api_service.dart';

class AddEmployeeScreen extends StatefulWidget {
  const AddEmployeeScreen({super.key});

  @override
  State<AddEmployeeScreen> createState() => _AddEmployeeScreenState();
}

class _AddEmployeeScreenState extends State<AddEmployeeScreen> {
  final _formKey = GlobalKey<FormState>();

  final _nikController = TextEditingController();
  final _namaController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController(text: 'password123');
  final _noHpController = TextEditingController();
  final _alamatController = TextEditingController();
  final _kuotaController = TextEditingController(text: '12');

  DateTime _tanggalMasuk = DateTime.now();
  String _selectedRole = 'operator';
  int? _selectedDeptId;
  int? _selectedJabatanId;
  String _selectedGender = 'Laki-laki';
  String _selectedMarital = 'Belum Menikah';

  List<dynamic> _departments = [];
  List<dynamic> _positions = [];
  bool _isLoadingOptions = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    _loadOptions();
  }

  @override
  void dispose() {
    _nikController.dispose();
    _namaController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _noHpController.dispose();
    _alamatController.dispose();
    _kuotaController.dispose();
    super.dispose();
  }

  Future<void> _loadOptions() async {
    setState(() => _isLoadingOptions = true);
    final res = await ApiService.getEmployeeOptions();
    if (mounted) {
      if (res.success && res.data != null) {
        setState(() {
          _departments = res.data!['departments'] ?? [];
          _positions = res.data!['positions'] ?? [];
          if (_departments.isNotEmpty) {
            _selectedDeptId = _departments.first['id'] as int?;
          }
          if (_positions.isNotEmpty) {
            _selectedJabatanId = _positions.first['id'] as int?;
          }
          _isLoadingOptions = false;
        });
      } else {
        setState(() => _isLoadingOptions = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res.message),
            backgroundColor: Colors.red.shade700,
          ),
        );
      }
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedDeptId == null || _selectedJabatanId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih Departemen dan Jabatan'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);

    final payload = {
      'nik': _nikController.text.trim(),
      'nama_lengkap': _namaController.text.trim(),
      'email': _emailController.text.trim(),
      'password': _passwordController.text.trim(),
      'role': _selectedRole,
      'departemen_id': _selectedDeptId,
      'jabatan_id': _selectedJabatanId,
      'tanggal_masuk': _tanggalMasuk.toIso8601String().substring(0, 10),
      'kuota_cuti': int.tryParse(_kuotaController.text.trim()) ?? 12,
      'jenis_kelamin': _selectedGender,
      'status_pernikahan': _selectedMarital,
      'no_hp': _noHpController.text.trim(),
      'alamat': _alamatController.text.trim(),
    };

    final res = await ApiService.createEmployee(payload);

    if (mounted) {
      setState(() => _isSaving = false);
      if (res.success) {
        showCupertinoDialog(
          context: context,
          builder: (ctx) => CupertinoAlertDialog(
            title: const Text('Berhasil Ditambahkan'),
            content: Text(res.message),
            actions: [
              CupertinoDialogAction(
                isDefaultAction: true,
                child: const Text('OK'),
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pop(context, true);
                },
              ),
            ],
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res.message),
            backgroundColor: Colors.red.shade700,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textHead = isDark ? Colors.white : const Color(0xFF0F172A);
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE5E5EA);

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
          'Tambah Karyawan',
          style: TextStyle(
            color: textHead,
            fontWeight: FontWeight.bold,
            fontSize: 17,
          ),
        ),
      ),
      body: _isLoadingOptions
          ? const Center(child: CupertinoActivityIndicator(radius: 14))
          : SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Center(
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 550),
                  child: Form(
                    key: _formKey,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildSectionHeader('DATA PEKERJAAN & PENEMPATAN', isDark),
                        _buildGroupedCard([
                          _buildTextField(
                            controller: _nikController,
                            label: 'NIK Karyawan',
                            placeholder: 'Contoh: NAK-055',
                            isRequired: true,
                            isDark: isDark,
                          ),
                          _buildDropdownRow<int>(
                            label: 'Departemen',
                            value: _selectedDeptId,
                            items: _departments.map((d) {
                              return DropdownMenuItem<int>(
                                value: d['id'] as int,
                                child: Text('${d['nama_dept']} (${d['kode_dept']})'),
                              );
                            }).toList(),
                            onChanged: (v) => setState(() => _selectedDeptId = v),
                            isDark: isDark,
                          ),
                          _buildDropdownRow<int>(
                            label: 'Jabatan Kerja',
                            value: _selectedJabatanId,
                            items: _positions.map((p) {
                              return DropdownMenuItem<int>(
                                value: p['id'] as int,
                                child: Text(p['nama_jabatan'].toString()),
                              );
                            }).toList(),
                            onChanged: (v) => setState(() => _selectedJabatanId = v),
                            isDark: isDark,
                          ),
                          _buildDropdownRow<String>(
                            label: 'Hak Akses (Role)',
                            value: _selectedRole,
                            items: const [
                              DropdownMenuItem(value: 'operator', child: Text('Operator Produksi')),
                              DropdownMenuItem(value: 'staff', child: Text('Staff')),
                              DropdownMenuItem(value: 'leader', child: Text('Leader')),
                              DropdownMenuItem(value: 'supervisor', child: Text('Supervisor (Spv)')),
                              DropdownMenuItem(value: 'manager', child: Text('Manager')),
                              DropdownMenuItem(value: 'hrd', child: Text('HRD')),
                              DropdownMenuItem(value: 'superadmin', child: Text('Super Admin (Sistem)')),
                            ],
                            onChanged: (v) => setState(() => _selectedRole = v ?? 'operator'),
                            isDark: isDark,
                          ),
                          _buildDatePickerRow(
                            label: 'Tanggal Masuk (Join Date)',
                            date: _tanggalMasuk,
                            onTap: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: _tanggalMasuk,
                                firstDate: DateTime(1990),
                                lastDate: DateTime(2035),
                              );
                              if (picked != null) {
                                setState(() => _tanggalMasuk = picked);
                              }
                            },
                            isDark: isDark,
                          ),
                          _buildTextField(
                            controller: _kuotaController,
                            label: 'Jatah Kuota Cuti Awal (Hari)',
                            placeholder: '12',
                            keyboardType: TextInputType.number,
                            isRequired: true,
                            isDark: isDark,
                          ),
                        ], cardBg: cardBg, borderCol: borderCol),

                        const SizedBox(height: 20),
                        _buildSectionHeader('PROFIL PRIBADI & AKUN LOGIN', isDark),
                        _buildGroupedCard([
                          _buildTextField(
                            controller: _namaController,
                            label: 'Nama Lengkap Sesuai KTP',
                            placeholder: 'Contoh: Budi Santoso',
                            isRequired: true,
                            isDark: isDark,
                          ),
                          _buildTextField(
                            controller: _emailController,
                            label: 'Email Perusahaan',
                            placeholder: 'budi@nakakin.co.id',
                            keyboardType: TextInputType.emailAddress,
                            isRequired: true,
                            isDark: isDark,
                          ),
                          _buildTextField(
                            controller: _passwordController,
                            label: 'Password Akun',
                            placeholder: 'Default: password123',
                            isRequired: true,
                            isDark: isDark,
                          ),
                          _buildDropdownRow<String>(
                            label: 'Jenis Kelamin',
                            value: _selectedGender,
                            items: const [
                              DropdownMenuItem(value: 'Laki-laki', child: Text('Laki-laki')),
                              DropdownMenuItem(value: 'Perempuan', child: Text('Perempuan')),
                            ],
                            onChanged: (v) => setState(() => _selectedGender = v ?? 'Laki-laki'),
                            isDark: isDark,
                          ),
                          _buildDropdownRow<String>(
                            label: 'Status Pernikahan',
                            value: _selectedMarital,
                            items: const [
                              DropdownMenuItem(value: 'Belum Menikah', child: Text('Belum Menikah')),
                              DropdownMenuItem(value: 'Menikah', child: Text('Menikah')),
                              DropdownMenuItem(value: 'Duda', child: Text('Duda')),
                              DropdownMenuItem(value: 'Janda', child: Text('Janda')),
                            ],
                            onChanged: (v) => setState(() => _selectedMarital = v ?? 'Belum Menikah'),
                            isDark: isDark,
                          ),
                          _buildTextField(
                            controller: _noHpController,
                            label: 'No. Handphone / WhatsApp',
                            placeholder: 'Contoh: 081234567890',
                            keyboardType: TextInputType.phone,
                            isDark: isDark,
                          ),
                          _buildTextField(
                            controller: _alamatController,
                            label: 'Alamat Domisili',
                            placeholder: 'Alamat tempat tinggal di Karawang/sekitarnya...',
                            maxLines: 2,
                            isDark: isDark,
                          ),
                        ], cardBg: cardBg, borderCol: borderCol),

                        const SizedBox(height: 28),
                        SizedBox(
                          width: double.infinity,
                          height: 50,
                          child: ElevatedButton(
                            onPressed: _isSaving ? null : _submitForm,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF007AFF),
                              foregroundColor: Colors.white,
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(14),
                              ),
                            ),
                            child: _isSaving
                                ? const CupertinoActivityIndicator(color: Colors.white)
                                : const Text(
                                    'Simpan Data Karyawan',
                                    style: TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                          ),
                        ),
                        const SizedBox(height: 40),
                      ],
                    ),
                  ),
                ),
              ),
            ),
    );
  }

  Widget _buildSectionHeader(String title, bool isDark) {
    return Padding(
      padding: const EdgeInsets.only(left: 14, bottom: 8, top: 8),
      child: Text(
        title,
        style: TextStyle(
          color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF6C6C70),
          fontSize: 12.5,
          fontWeight: FontWeight.w600,
          letterSpacing: -0.2,
        ),
      ),
    );
  }

  Widget _buildGroupedCard(List<Widget> children, {required Color cardBg, required Color borderCol}) {
    return Container(
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderCol),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: children,
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String placeholder,
    bool isRequired = false,
    TextInputType keyboardType = TextInputType.text,
    int maxLines = 1,
    required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                ),
              ),
              if (isRequired)
                const Text(' *', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold)),
            ],
          ),
          const SizedBox(height: 6),
          TextFormField(
            controller: controller,
            keyboardType: keyboardType,
            maxLines: maxLines,
            style: TextStyle(fontSize: 14.5, color: isDark ? Colors.white : const Color(0xFF1C1C1E)),
            decoration: InputDecoration(
              hintText: placeholder,
              hintStyle: TextStyle(color: isDark ? const Color(0xFF64748B) : const Color(0xFFC7C7CC), fontSize: 13.5),
              isDense: true,
              filled: true,
              fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: Color(0xFF007AFF), width: 1.5),
              ),
            ),
            validator: isRequired
                ? (val) {
                    if (val == null || val.trim().isEmpty) {
                      return '$label wajib diisi';
                    }
                    return null;
                  }
                : null,
          ),
        ],
      ),
    );
  }

  Widget _buildDropdownRow<T>({
    required String label,
    required T? value,
    required List<DropdownMenuItem<T>> items,
    required ValueChanged<T?> onChanged,
    required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
            ),
          ),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
            ),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<T>(
                value: value,
                isExpanded: true,
                items: items,
                onChanged: onChanged,
                icon: const Icon(CupertinoIcons.chevron_down, size: 16, color: Color(0xFF8E8E93)),
                style: TextStyle(fontSize: 14, color: isDark ? Colors.white : const Color(0xFF1C1C1E)),
                dropdownColor: isDark ? const Color(0xFF1E293B) : Colors.white,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDatePickerRow({
    required String label,
    required DateTime date,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
            ),
          ),
          const SizedBox(height: 6),
          InkWell(
            onTap: onTap,
            borderRadius: BorderRadius.circular(10),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}',
                    style: TextStyle(
                      fontSize: 14.5,
                      color: isDark ? Colors.white : const Color(0xFF1C1C1E),
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const Icon(CupertinoIcons.calendar, size: 18, color: Color(0xFF007AFF)),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
