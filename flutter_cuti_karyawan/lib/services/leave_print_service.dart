import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import '../models/leave_model.dart';

class LeavePrintService {
  static const pdfBluePrimary = PdfColor.fromInt(0xFF1E3A8A);
  static const pdfBlueHeader = PdfColor.fromInt(0xFFEFF6FF);
  static const pdfSuccessGreen = PdfColor.fromInt(0xFF15803D);
  static const pdfDangerRed = PdfColor.fromInt(0xFFDC2626);
  static const pdfWarningAmber = PdfColor.fromInt(0xFFD97706);
  static const pdfBorderGrey = PdfColor.fromInt(0xFFCBD5E1);
  static const pdfBgLight = PdfColor.fromInt(0xFFF8FAFC);
  static const pdfTextDark = PdfColor.fromInt(0xFF0F172A);
  static const pdfTextMuted = PdfColor.fromInt(0xFF475569);

  /// Format tanggal Indonesia
  static String formatTanggalIndo(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '-';
    try {
      final date = DateTime.parse(dateStr);
      const bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
      ];
      return '${date.day} ${bulan[date.month - 1]} ${date.year}';
    } catch (_) {
      return dateStr;
    }
  }

  /// Generate PDF Dokumen Surat Izin Cuti A4
  static Future<Uint8List> generateLeavePdf(LeaveModel leave, {PdfPageFormat pageFormat = PdfPageFormat.a4}) async {
    final pdf = pw.Document();

    final isApproved = leave.isApproved;
    final isRejected = leave.isRejected;

    final formattedStartDate = formatTanggalIndo(leave.tanggalMulai);
    final formattedEndDate = formatTanggalIndo(leave.tanggalSelesai);
    final formattedCreatedDate = formatTanggalIndo(leave.createdAt);
    final now = DateTime.now();
    const bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    final printDate = '${now.day} ${bulan[now.month - 1]} ${now.year}';

    pdf.addPage(
      pw.Page(
        pageFormat: pageFormat,
        margin: const pw.EdgeInsets.all(36),
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              // 1. KOP SURAT PERUSAHAAN
              pw.Row(
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                children: [
                  pw.Expanded(
                    child: pw.Column(
                      crossAxisAlignment: pw.CrossAxisAlignment.start,
                      children: [
                        pw.Text(
                          'PT. NAKAKIN INDONESIA',
                          style: pw.TextStyle(
                            fontWeight: pw.FontWeight.bold,
                            fontSize: 15,
                            color: pdfBluePrimary,
                          ),
                        ),
                        pw.SizedBox(height: 2),
                        pw.Text(
                          'MANUFACTURER OF ALUMINUM DIE CASTING & MACHINING',
                          style: pw.TextStyle(
                            fontWeight: pw.FontWeight.bold,
                            fontSize: 8.5,
                            color: pdfTextDark,
                          ),
                        ),
                        pw.SizedBox(height: 2),
                        pw.Text(
                          'Kawasan Industri KIIC, Jl. Maligi VI Lot L-4, Telukjambe Barat, Karawang 41361',
                          style: const pw.TextStyle(fontSize: 8, color: pdfTextMuted),
                        ),
                        pw.Text(
                          'Telp: (0267) 863-1234 • Email: hrd@nakakin.co.id • Website: www.nakakin.co.id',
                          style: const pw.TextStyle(fontSize: 8, color: pdfTextMuted),
                        ),
                      ],
                    ),
                  ),
                  pw.Container(
                    padding: const pw.EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: pw.BoxDecoration(
                      border: pw.Border.all(color: pdfBluePrimary, width: 1.5),
                      borderRadius: const pw.BorderRadius.all(pw.Radius.circular(4)),
                    ),
                    child: pw.Text(
                      'NAKAKIN',
                      style: pw.TextStyle(
                        fontWeight: pw.FontWeight.bold,
                        fontSize: 13,
                        color: pdfBluePrimary,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ),
                ],
              ),
              pw.SizedBox(height: 6),
              // Double divider lines
              pw.Container(height: 2, color: pdfBluePrimary),
              pw.SizedBox(height: 1.5),
              pw.Container(height: 0.75, color: pdfBluePrimary),
              pw.SizedBox(height: 12),

              // 2. JUDUL DOKUMEN & NOMOR SURAT
              pw.Center(
                child: pw.Column(
                  children: [
                    pw.Text(
                      'FORMULIR PERMOHONAN & SURAT IZIN CUTI',
                      style: pw.TextStyle(
                        fontWeight: pw.FontWeight.bold,
                        fontSize: 13,
                        color: pdfTextDark,
                        decoration: pw.TextDecoration.underline,
                      ),
                    ),
                    pw.SizedBox(height: 3),
                    pw.Text(
                      'Nomor: ${leave.nomorSurat.isNotEmpty ? leave.nomorSurat : "-"}',
                      style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9.5, color: pdfTextMuted),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 12),

              pw.Text(
                'Yang bertanda tangan di bawah ini, menerangkan bahwa karyawan:',
                style: const pw.TextStyle(fontSize: 9),
              ),
              pw.SizedBox(height: 6),

              // 3. TABEL IDENTITAS KARYAWAN
              pw.Container(
                padding: const pw.EdgeInsets.all(8),
                decoration: pw.BoxDecoration(
                  color: pdfBgLight,
                  border: pw.Border.all(color: pdfBorderGrey),
                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(4)),
                ),
                child: pw.Table(
                  columnWidths: {
                    0: const pw.FlexColumnWidth(2.5),
                    1: const pw.FlexColumnWidth(0.3),
                    2: const pw.FlexColumnWidth(4.5),
                    3: const pw.FlexColumnWidth(2.2),
                    4: const pw.FlexColumnWidth(0.3),
                    5: const pw.FlexColumnWidth(3.5),
                  },
                  children: [
                    pw.TableRow(
                      children: [
                        pw.Text('Nama Lengkap', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                        pw.Text(':', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text(leave.namaLengkap ?? '-', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5, color: pdfBluePrimary)),
                        pw.Text('Departemen', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                        pw.Text(':', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text(leave.namaDept ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                      ],
                    ),
                    pw.TableRow(
                      children: [
                        pw.Text('N I K', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                        pw.Text(':', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text(leave.nik ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text('Jabatan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                        pw.Text(':', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text(leave.namaJabatan ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                      ],
                    ),
                    pw.TableRow(
                      children: [
                        pw.Text('Tgl Pengajuan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                        pw.Text(':', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text(formattedCreatedDate, style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text('No. Handphone / WA', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                        pw.Text(':', style: const pw.TextStyle(fontSize: 8.5)),
                        pw.Text((leave.noHp?.isNotEmpty == true ? leave.noHp : leave.kontakDarurat) ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                      ],
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 10),

              pw.Text(
                'Mengajukan permohonan izin cuti kerja dengan rincian data sebagai berikut:',
                style: const pw.TextStyle(fontSize: 9),
              ),
              pw.SizedBox(height: 6),

              // 4. TABEL DETAIL PERMOHONAN CUTI
              pw.Table(
                border: pw.TableBorder.all(color: pdfBorderGrey, width: 0.8),
                columnWidths: {
                  0: const pw.FlexColumnWidth(2.5),
                  1: const pw.FlexColumnWidth(3.5),
                  2: const pw.FlexColumnWidth(1.8),
                  3: const pw.FlexColumnWidth(4.2),
                },
                children: [
                  pw.TableRow(
                    decoration: const pw.BoxDecoration(color: pdfBlueHeader),
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Jenis Cuti', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5, color: pdfBluePrimary)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Periode Tanggal Cuti', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5, color: pdfBluePrimary), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Jumlah Hari', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5, color: pdfBluePrimary), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Alasan / Keperluan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5, color: pdfBluePrimary)),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(leave.namaCuti ?? 'Cuti Tahunan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          '$formattedStartDate\ns/d $formattedEndDate',
                          style: const pw.TextStyle(fontSize: 8),
                          textAlign: pw.TextAlign.center,
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          '${leave.totalHari} Hari Kerja',
                          style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5, color: pdfBluePrimary),
                          textAlign: pw.TextAlign.center,
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          leave.alasan.isNotEmpty ? leave.alasan : '-',
                          style: const pw.TextStyle(fontSize: 8),
                        ),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Alamat Selama Cuti', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          '${(leave.alamatSelamaCuti != null && leave.alamatSelamaCuti!.isNotEmpty) ? leave.alamatSelamaCuti! : (leave.alamat ?? "Di alamat tempat tinggal terdaftar")}'
                          '${(leave.kontakDarurat != null && leave.kontakDarurat!.isNotEmpty) ? " (Darurat: ${leave.kontakDarurat})" : ""}',
                          style: const pw.TextStyle(fontSize: 8),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Status Approval', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          isApproved ? 'DISETUJUI (APPROVED)' : (isRejected ? 'DITOLAK (REJECTED)' : 'PROSES REVIEW (PENDING)'),
                          style: pw.TextStyle(
                            fontWeight: pw.FontWeight.bold,
                            fontSize: 8,
                            color: isApproved ? pdfSuccessGreen : (isRejected ? pdfDangerRed : pdfWarningAmber),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              pw.SizedBox(height: 10),

              // 5. CATATAN / KEPUTUSAN ATASAN
              pw.Container(
                width: double.infinity,
                padding: const pw.EdgeInsets.all(7),
                decoration: pw.BoxDecoration(
                  color: pdfBgLight,
                  border: pw.Border.all(color: pdfBorderGrey),
                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(4)),
                ),
                child: pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text('Catatan & Keputusan Atasan:', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8)),
                    pw.SizedBox(height: 2),
                    pw.Text(
                      (leave.catatanAtasan != null && leave.catatanAtasan!.isNotEmpty)
                          ? leave.catatanAtasan!
                          : (isApproved
                              ? 'Disetujui sesuai dengan ketentuan dan prosedur ketenagakerjaan PT. Nakakin Indonesia.'
                              : (isRejected
                                  ? 'Pengajuan tidak dapat disetujui: ${leave.rejectionReason ?? "Kebutuhan operasional pabrik."}'
                                  : 'Sedang dalam proses peninjauan persetujuan hierarki atasan.')),
                      style: const pw.TextStyle(fontStyle: pw.FontStyle.italic, fontSize: 7.5, color: pdfTextDark),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 18),

              // 6. LEMBAR TANDA TANGAN (4 KOLOM APPROVAL)
              pw.Table(
                border: pw.TableBorder.all(color: pdfBorderGrey, width: 0.6),
                columnWidths: {
                  0: const pw.FlexColumnWidth(1),
                  1: const pw.FlexColumnWidth(1),
                  2: const pw.FlexColumnWidth(1),
                  3: const pw.FlexColumnWidth(1),
                },
                children: [
                  // Row Header Jabatan
                  pw.TableRow(
                    decoration: const pw.BoxDecoration(color: pdfBgLight),
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 4),
                        child: pw.Center(
                          child: pw.Text('1. Operator / Karyawan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 4),
                        child: pw.Center(
                          child: pw.Text('2. Leader / Supervisor', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 4),
                        child: pw.Center(
                          child: pw.Text('3. Manager', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 4),
                        child: pw.Center(
                          child: pw.Text('4. HRD', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                    ],
                  ),
                  // Row Isi Status & Nama Approver
                  pw.TableRow(
                    children: [
                      // 1. Kolom Pemohon
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text('Karawang, $formattedCreatedDate', style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted)),
                            pw.SizedBox(height: 8),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(color: pdfBluePrimary, width: 0.8),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2.5)),
                              ),
                              child: pw.Text(
                                'DIGITAL SIGNED',
                                style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfBluePrimary),
                              ),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Text('( ${leave.namaLengkap ?? "Pemohon"} )', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7), textAlign: pw.TextAlign.center),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              '${leave.namaJabatan ?? "Karyawan"}${leave.namaDept != null ? " (${leave.namaDept})" : ""}',
                              style: const pw.TextStyle(fontSize: 6, color: pdfTextMuted),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),

                      // 2. Kolom Leader / Supervisor
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text(
                              leave.spvAt != null ? formatTanggalIndo(leave.spvAt) : '-',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(
                                  color: leave.spvId != null
                                      ? pdfSuccessGreen
                                      : (leave.employeeLevel >= 3
                                          ? pdfBorderGrey
                                          : (isRejected && leave.spvId == null ? pdfDangerRed : pdfBorderGrey)),
                                  width: 0.8,
                                ),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2.5)),
                              ),
                              child: pw.Text(
                                leave.spvId != null
                                    ? 'APPROVED'
                                    : (leave.employeeLevel >= 3
                                        ? 'BYPASS'
                                        : (isRejected && leave.spvId == null ? 'REJECTED' : 'MENUNGGU')),
                                style: pw.TextStyle(
                                  fontWeight: pw.FontWeight.bold,
                                  fontSize: 6.5,
                                  color: leave.spvId != null
                                      ? pdfSuccessGreen
                                      : (leave.employeeLevel >= 3
                                          ? pdfTextMuted
                                          : (isRejected && leave.spvId == null ? pdfDangerRed : pdfTextMuted)),
                                ),
                              ),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Text(
                              '( ${leave.spvId != null ? (leave.spvName ?? "Leader / Spv") : (leave.employeeLevel >= 3 ? "-" : "...................")} )',
                              style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7),
                              textAlign: pw.TextAlign.center,
                            ),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              leave.spvId != null
                                  ? '${leave.spvJabatan ?? "Leader / Supervisor"}${leave.spvDept != null ? " (${leave.spvDept})" : ""}'
                                  : 'Leader / Supervisor',
                              style: const pw.TextStyle(fontSize: 6, color: pdfTextMuted),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),

                      // 3. Kolom Manager
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text(
                              leave.managerAt != null ? formatTanggalIndo(leave.managerAt) : '-',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(
                                  color: leave.managerId != null
                                      ? pdfSuccessGreen
                                      : (leave.employeeLevel >= 6
                                          ? pdfBorderGrey
                                          : (isRejected && leave.spvId != null && leave.managerId == null ? pdfDangerRed : pdfBorderGrey)),
                                  width: 0.8,
                                ),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2.5)),
                              ),
                              child: pw.Text(
                                leave.managerId != null
                                    ? 'APPROVED'
                                    : (leave.employeeLevel >= 6
                                        ? 'BYPASS'
                                        : (isRejected && leave.spvId != null && leave.managerId == null ? 'REJECTED' : 'MENUNGGU')),
                                style: pw.TextStyle(
                                  fontWeight: pw.FontWeight.bold,
                                  fontSize: 6.5,
                                  color: leave.managerId != null
                                      ? pdfSuccessGreen
                                      : (leave.employeeLevel >= 6
                                          ? pdfTextMuted
                                          : (isRejected && leave.spvId != null && leave.managerId == null ? pdfDangerRed : pdfTextMuted)),
                                ),
                              ),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Text(
                              '( ${leave.managerId != null ? (leave.managerName ?? "Manager") : (leave.employeeLevel >= 6 ? "-" : "...................")} )',
                              style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7),
                              textAlign: pw.TextAlign.center,
                            ),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              leave.managerId != null
                                  ? (leave.managerJabatan ?? "Department Manager")
                                  : 'Plant / Dept Manager',
                              style: const pw.TextStyle(fontSize: 6, color: pdfTextMuted),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),

                      // 4. Kolom HRD
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text(
                              (isApproved && (leave.hrdAt != null || leave.approvedAt != null))
                                  ? formatTanggalIndo(leave.hrdAt ?? leave.approvedAt)
                                  : '-',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(
                                  color: isApproved
                                      ? pdfBluePrimary
                                      : (isRejected && (leave.managerId != null || leave.employeeLevel >= 5) ? pdfDangerRed : pdfBorderGrey),
                                  width: 0.8,
                                ),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2.5)),
                              ),
                              child: pw.Text(
                                isApproved
                                    ? 'VERIFIED HRD'
                                    : (isRejected && (leave.managerId != null || leave.employeeLevel >= 5) ? 'REJECTED' : 'MENUNGGU'),
                                style: pw.TextStyle(
                                  fontWeight: pw.FontWeight.bold,
                                  fontSize: 6.5,
                                  color: isApproved
                                      ? pdfBluePrimary
                                      : (isRejected && (leave.managerId != null || leave.employeeLevel >= 5) ? pdfDangerRed : pdfTextMuted),
                                ),
                              ),
                            ),
                            pw.SizedBox(height: 8),
                            pw.Text(
                              '( ${isApproved ? (leave.hrdName ?? leave.approverName ?? "HRD Department") : "..................."} )',
                              style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7),
                              textAlign: pw.TextAlign.center,
                            ),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              isApproved ? (leave.hrdJabatan ?? "HRD & GA Manager") : "HRD Department",
                              style: const pw.TextStyle(fontSize: 6, color: pdfTextMuted),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              pw.Spacer(),

              // 7. FOOTER ELEKTRONIK
              pw.Container(
                padding: const pw.EdgeInsets.only(top: 8),
                decoration: const pw.BoxDecoration(
                  border: pw.Border(top: pw.BorderSide(color: pdfBorderGrey, width: 0.5)),
                ),
                child: pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    pw.Text(
                      'Dicetak melalui Aplikasi E-Cuti PT. Nakakin Indonesia pada $printDate',
                      style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted),
                    ),
                    pw.Text(
                      'Dokumen elektronik sah tanpa tanda tangan basah.',
                      style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted),
                    ),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  /// Buka dialog Print langsung menggunakan fitur printing OS
  static Future<void> printLeave(BuildContext context, LeaveModel leave) async {
    try {
      final safeNum = leave.nomorSurat.replaceAll('/', '_');
      await Printing.layoutPdf(
        name: 'Surat_Cuti_$safeNum',
        onLayout: (PdfPageFormat format) => generateLeavePdf(leave, pageFormat: format),
      );
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mencetak dokumen: $e')),
        );
      }
    }
  }

  /// Bagikan dokumen PDF ke aplikasi lain (Share Sheet / WA / Email / Drive)
  static Future<void> shareLeave(BuildContext context, LeaveModel leave) async {
    try {
      final safeNum = leave.nomorSurat.replaceAll('/', '_');
      final pdfBytes = await generateLeavePdf(leave);
      await Printing.sharePdf(
        bytes: pdfBytes,
        filename: 'Surat_Cuti_$safeNum.pdf',
      );
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membagikan dokumen: $e')),
        );
      }
    }
  }
}