import 'package:flutter/material.dart';
import 'package:flutter/cupertino.dart';
import '../config/app_theme.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';

class EditEmployeeScreen extends StatefulWidget {
  final UserModel employee;

  const EditEmployeeScreen({super.key, required this.employee});

  @override
  State<EditEmployeeScreen> createState() => _EditEmployeeScreenState();
}

class _EditEmployeeScreenState extends State<EditEmployeeScreen> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _nikController;
  late TextEditingController _namaController;
  late TextEditingController _emailController;
  late TextEditingController _noHpController;
  late TextEditingController _alamatController;
  late TextEditingController _kuotaController;
  late TextEditingController _sisaController;
  final TextEditingController _passwordController = TextEditingController();

  late DateTime _tanggalMasuk;
  late String _selectedRole;
  int? _selectedDeptId;
  int? _selectedJabatanId;
  late String _selectedGender;
  late String _selectedMarital;
  late String _selectedAgama;
  bool _statusAktif = true;
  bool _obscurePassword = true;

  List<dynamic> _departments = [];
  List<dynamic> _positions = [];
  bool _isLoadingOptions = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();
    final emp = widget.employee;
    _nikController = TextEditingController(text: emp.nik);
    _namaController = TextEditingController(text: emp.namaLengkap);
    _emailController = TextEditingController(text: emp.email);
    _noHpController = TextEditingController(text: emp.noHp ?? '');
    _alamatController = TextEditingController(text: emp.alamat ?? '');
    _kuotaController = TextEditingController(text: emp.kuotaCuti.toString());
    _sisaController = TextEditingController(text: emp.sisaCuti.toString());

    _selectedRole = emp.role.toLowerCase();
    _selectedDeptId = emp.deptId;
    _selectedJabatanId = emp.jabatanId;
    _selectedGender = emp.jenisKelamin?.isNotEmpty == true ? emp.jenisKelamin! : 'Laki-laki';
    _selectedMarital = emp.statusPernikahan?.isNotEmpty == true ? emp.statusPernikahan! : 'Belum Menikah';
    _selectedAgama = emp.agama?.isNotEmpty == true ? emp.agama! : 'Islam';
    _statusAktif = (emp.statusAktif ?? 'Aktif').toLowerCase() != 'nonaktif';

    if (emp.tanggalMasuk != null && emp.tanggalMasuk!.isNotEmpty) {
      try {
        _tanggalMasuk = DateTime.parse(emp.tanggalMasuk!);
      } catch (_) {
        _tanggalMasuk = DateTime.now();
      }
    } else {
      _tanggalMasuk = DateTime.now();
    }

    _loadOptions();
  }

  @override
  void dispose() {
    _nikController.dispose();
    _namaController.dispose();
    _emailController.dispose();
    _noHpController.dispose();
    _alamatController.dispose();
    _kuotaController.dispose();
    _sisaController.dispose();
    _passwordController.dispose();
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
          _isLoadingOptions = false;
        });
      } else {
        setState(() => _isLoadingOptions = false);
      }
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedDeptId == null || _selectedJabatanId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan pilih Departemen dan Jabatan'),
          backgroundColor: AppTheme.statusRejected,
        ),
      );
      return;
    }

    setState(() => _isSaving = true);

    final payload = {
      'id': widget.employee.id,
      'nik': _nikController.text.trim(),
      'nama_lengkap': _namaController.text.trim(),
      'email': _emailController.text.trim(),
      'role': _selectedRole,
      'departemen_id': _selectedDeptId,
      'jabatan_id': _selectedJabatanId,
      'tanggal_masuk': _tanggalMasuk.toIso8601String().split('T').first,
      'kuota_cuti': int.tryParse(_kuotaController.text.trim()) ?? 12,
      'sisa_cuti': int.tryParse(_sisaController.text.trim()) ?? 12,
      'jenis_kelamin': _selectedGender,
      'agama': _selectedAgama,
      'status_pernikahan': _selectedMarital,
      'no_hp': _noHpController.text.trim(),
      'alamat': _alamatController.text.trim(),
      'status_aktif': _statusAktif ? 'Aktif' : 'Nonaktif',
      if (_passwordController.text.trim().isNotEmpty)
        'new_password': _passwordController.text.trim(),
    };

    final res = await ApiService.updateEmployee(payload);

    if (!mounted) return;
    setState(() => _isSaving = false);

    if (res.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message.isNotEmpty ? res.message : 'Data karyawan berhasil diperbarui'),
          backgroundColor: const Color(0xFF10B981),
          behavior: SnackBarBehavior.floating,
        ),
      );
      Navigator.pop(context, true);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message.isNotEmpty ? res.message : 'Gagal memperbarui data'),
          backgroundColor: AppTheme.statusRejected,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  Future<void> _showQuickResetDialog() async {
    final newPassCtrl = TextEditingController(text: 'password123');
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.lock_reset_rounded, color: Color(0xFFFF9500)),
            SizedBox(width: 8),
            Text('Reset Password', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Reset password untuk akun ${widget.employee.namaLengkap} (${widget.employee.nik}):', style: const TextStyle(fontSize: 13)),
            const SizedBox(height: 12),
            TextField(
              controller: newPassCtrl,
              decoration: const InputDecoration(
                labelText: 'Password Baru',
                hintText: 'password123',
                prefixIcon: Icon(Icons.key_outlined, size: 20),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFFF9500),
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Reset Password'),
          ),
        ],
      ),
    );

    if (confirmed == true && mounted) {
      final res = await ApiService.resetEmployeePassword(
        widget.employee.id,
        newPassword: newPassCtrl.text.trim(),
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(res.message),
          backgroundColor: res.success ? const Color(0xFF10B981) : AppTheme.statusRejected,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF1E293B) : Colors.white;
    final borderCol = isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0);
    final textHead = isDark ? Colors.white : AppTheme.textPrimary;
    final primaryAccent = isDark ? const Color(0xFF38BDF8) : AppTheme.primary;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF121824) : const Color(0xFFF2F2F7),
      appBar: AppBar(
        title: Text('Edit Data Karyawan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: textHead)),
        actions: [
          IconButton(
            icon: const Icon(Icons.lock_reset_rounded, color: Color(0xFFFF9500)),
            tooltip: 'Reset Password',
            onPressed: _showQuickResetDialog,
          ),
        ],
      ),
      body: _isLoadingOptions
          ? const Center(child: CircularProgressIndicator())
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Section 1: Profil & Identitas
                  _buildSectionCard(
                    title: 'Informasi Utama & Role',
                    icon: Icons.badge_outlined,
                    cardBg: cardBg,
                    borderCol: borderCol,
                    textHead: textHead,
                    children: [
                      TextFormField(
                        controller: _nikController,
                        decoration: const InputDecoration(labelText: 'NIK *', prefixIcon: Icon(Icons.pin, size: 20)),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'NIK tidak boleh kosong' : null,
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _namaController,
                        decoration: const InputDecoration(labelText: 'Nama Lengkap *', prefixIcon: Icon(Icons.person, size: 20)),
                        validator: (v) => (v == null || v.trim().isEmpty) ? 'Nama lengkap tidak boleh kosong' : null,
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(labelText: 'Alamat Email *', prefixIcon: Icon(Icons.email, size: 20)),
                        validator: (v) {
                          if (v == null || v.trim().isEmpty) return 'Email tidak boleh kosong';
                          if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(v.trim())) return 'Format email tidak valid';
                          return null;
                        },
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        value: _selectedRole,
                        decoration: const InputDecoration(labelText: 'Role Sistem *', prefixIcon: Icon(Icons.security, size: 20)),
                        items: const [
                          DropdownMenuItem(value: 'operator', child: Text('Operator Produksi (Level 1)')),
                          DropdownMenuItem(value: 'staff', child: Text('Staff (Level 2)')),
                          DropdownMenuItem(value: 'leader', child: Text('Leader Tim (Level 3)')),
                          DropdownMenuItem(value: 'supervisor', child: Text('Supervisor / Spv (Level 4)')),
                          DropdownMenuItem(value: 'manager', child: Text('Plant / Dept Manager (Level 6)')),
                          DropdownMenuItem(value: 'hrd', child: Text('HRD (Level 7)')),
                          DropdownMenuItem(value: 'admin', child: Text('Super Admin (Level 8)')),
                        ],
                        onChanged: (val) {
                          if (val != null) setState(() => _selectedRole = val);
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Section 2: Departemen & Jabatan
                  _buildSectionCard(
                    title: 'Departemen & Penempatan',
                    icon: Icons.business_outlined,
                    cardBg: cardBg,
                    borderCol: borderCol,
                    textHead: textHead,
                    children: [
                      DropdownButtonFormField<int>(
                        value: _selectedDeptId,
                        decoration: const InputDecoration(labelText: 'Departemen *', prefixIcon: Icon(Icons.apartment, size: 20)),
                        items: _departments.map<DropdownMenuItem<int>>((d) {
                          return DropdownMenuItem<int>(
                            value: d['id'] as int,
                            child: Text(d['nama_dept']?.toString() ?? ''),
                          );
                        }).toList(),
                        onChanged: (val) => setState(() => _selectedDeptId = val),
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<int>(
                        value: _selectedJabatanId,
                        decoration: const InputDecoration(labelText: 'Jabatan *', prefixIcon: Icon(Icons.work_outline, size: 20)),
                        items: _positions.map<DropdownMenuItem<int>>((p) {
                          return DropdownMenuItem<int>(
                            value: p['id'] as int,
                            child: Text(p['nama_jabatan']?.toString() ?? ''),
                          );
                        }).toList(),
                        onChanged: (val) => setState(() => _selectedJabatanId = val),
                      ),
                      const SizedBox(height: 12),
                      InkWell(
                        onTap: () async {
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: _tanggalMasuk,
                            firstDate: DateTime(2000),
                            lastDate: DateTime.now().add(const Duration(days: 365)),
                          );
                          if (picked != null) setState(() => _tanggalMasuk = picked);
                        },
                        child: InputDecorator(
                          decoration: const InputDecoration(
                            labelText: 'Tanggal Masuk Kerja *',
                            prefixIcon: Icon(Icons.calendar_today, size: 20),
                          ),
                          child: Text(
                            '${_tanggalMasuk.year}-${_tanggalMasuk.month.toString().padLeft(2, '0')}-${_tanggalMasuk.day.toString().padLeft(2, '0')}',
                            style: const TextStyle(fontSize: 14),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Section 3: Kuota Cuti
                  _buildSectionCard(
                    title: 'Alokasi Kuota Cuti',
                    icon: Icons.pie_chart_outline,
                    cardBg: cardBg,
                    borderCol: borderCol,
                    textHead: textHead,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: TextFormField(
                              controller: _kuotaController,
                              keyboardType: TextInputType.number,
                              decoration: const InputDecoration(labelText: 'Jatah Tahunan', suffixText: 'Hari'),
                              validator: (v) => (int.tryParse(v ?? '') == null) ? 'Angka valid' : null,
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: TextFormField(
                              controller: _sisaController,
                              keyboardType: TextInputType.number,
                              decoration: const InputDecoration(labelText: 'Sisa Kuota', suffixText: 'Hari'),
                              validator: (v) => (int.tryParse(v ?? '') == null) ? 'Angka valid' : null,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Section 4: Biodata & Kontak
                  _buildSectionCard(
                    title: 'Biodata & Kontak',
                    icon: Icons.person_pin_outlined,
                    cardBg: cardBg,
                    borderCol: borderCol,
                    textHead: textHead,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              value: _selectedGender,
                              decoration: const InputDecoration(labelText: 'Jenis Kelamin'),
                              items: const [
                                DropdownMenuItem(value: 'Laki-laki', child: Text('Laki-laki')),
                                DropdownMenuItem(value: 'Perempuan', child: Text('Perempuan')),
                              ],
                              onChanged: (val) {
                                if (val != null) setState(() => _selectedGender = val);
                              },
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: DropdownButtonFormField<String>(
                              value: _selectedAgama,
                              decoration: const InputDecoration(labelText: 'Agama'),
                              items: const [
                                DropdownMenuItem(value: 'Islam', child: Text('Islam')),
                                DropdownMenuItem(value: 'Kristen', child: Text('Kristen')),
                                DropdownMenuItem(value: 'Katolik', child: Text('Katolik')),
                                DropdownMenuItem(value: 'Hindu', child: Text('Hindu')),
                                DropdownMenuItem(value: 'Buddha', child: Text('Buddha')),
                                DropdownMenuItem(value: 'Konghucu', child: Text('Konghucu')),
                              ],
                              onChanged: (val) {
                                if (val != null) setState(() => _selectedAgama = val);
                              },
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      DropdownButtonFormField<String>(
                        value: _selectedMarital,
                        decoration: const InputDecoration(labelText: 'Status Pernikahan'),
                        items: const [
                          DropdownMenuItem(value: 'Belum Menikah', child: Text('Belum Menikah')),
                          DropdownMenuItem(value: 'Menikah', child: Text('Menikah')),
                          DropdownMenuItem(value: 'Duda', child: Text('Duda')),
                          DropdownMenuItem(value: 'Janda', child: Text('Janda')),
                        ],
                        onChanged: (val) {
                          if (val != null) setState(() => _selectedMarital = val);
                        },
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _noHpController,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(labelText: 'Nomor WhatsApp / HP', prefixIcon: Icon(Icons.phone, size: 20)),
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _alamatController,
                        maxLines: 2,
                        decoration: const InputDecoration(labelText: 'Alamat Tinggal Lengkap', prefixIcon: Icon(Icons.location_on, size: 20)),
                      ),
                      const SizedBox(height: 12),
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        title: const Text('Status Karyawan Aktif', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
                        subtitle: Text(_statusAktif ? 'Akun dapat login & mengajukan cuti' : 'Akun dinonaktifkan', style: const TextStyle(fontSize: 12)),
                        value: _statusAktif,
                        activeColor: const Color(0xFF10B981),
                        onChanged: (v) => setState(() => _statusAktif = v),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Section 5: Ganti Password Baru (Opsional)
                  _buildSectionCard(
                    title: 'Ganti Password Akun (Opsional)',
                    icon: Icons.key_outlined,
                    cardBg: cardBg,
                    borderCol: borderCol,
                    textHead: textHead,
                    children: [
                      TextFormField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        decoration: InputDecoration(
                          labelText: 'Password Baru',
                          hintText: 'Biarkan kosong jika tidak ingin diubah',
                          prefixIcon: const Icon(Icons.lock_outline, size: 20),
                          suffixIcon: IconButton(
                            icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility, size: 20),
                            onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Submit Button
                  ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: primaryAccent,
                      foregroundColor: isDark ? const Color(0xFF0F172A) : Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    icon: _isSaving
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Icon(Icons.save_rounded, size: 20),
                    label: Text(
                      _isSaving ? 'Menyimpan Perubahan...' : 'Simpan Pembaruan Data Karyawan',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    onPressed: _isSaving ? null : _submitForm,
                  ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required Color cardBg,
    required Color borderCol,
    required Color textHead,
    required List<Widget> children,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderCol),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: AppTheme.primary),
              const SizedBox(width: 8),
              Text(
                title,
                style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold, color: textHead),
              ),
            ],
          ),
          const SizedBox(height: 14),
          ...children,
        ],
      ),
    );
  }
}
