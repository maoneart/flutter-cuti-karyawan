import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import '../models/leave_model.dart';

class LeavePrintService {
  static const pdfBlack = PdfColor.fromInt(0xFF000000);
  static const pdfBluePrimary = PdfColor.fromInt(0xFF1E3A8A);
  static const pdfSuccessGreen = PdfColor.fromInt(0xFF16A34A);
  static const pdfDangerRed = PdfColor.fromInt(0xFFDC2626);
  static const pdfBorderGrey = PdfColor.fromInt(0xFF000000);
  static const pdfBgLight = PdfColor.fromInt(0xFFF8FAFC);
  static const pdfBgRowAlt = PdfColor.fromInt(0xFFFAFAFA);
  static const pdfTextDark = PdfColor.fromInt(0xFF111827);
  static const pdfTextMuted = PdfColor.fromInt(0xFF4B5563);

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

  /// Format tanggal singkat (dd/mm/yyyy)
  static String formatTanggalSingkat(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '-';
    try {
      final date = DateTime.parse(dateStr);
      final d = date.day.toString().padLeft(2, '0');
      final m = date.month.toString().padLeft(2, '0');
      return '$d/$m/${date.year}';
    } catch (_) {
      return dateStr;
    }
  }

  /// Hitung masa kerja karyawan dari tanggal masuk
  static String hitungMasaKerja(String? tglMasukStr) {
    if (tglMasukStr == null || tglMasukStr.isEmpty) return '-';
    try {
      final tglMasuk = DateTime.parse(tglMasukStr);
      final now = DateTime.now();
      int years = now.year - tglMasuk.year;
      int months = now.month - tglMasuk.month;
      int days = now.day - tglMasuk.day;
      if (days < 0) {
        months -= 1;
      }
      if (months < 0) {
        years -= 1;
        months += 12;
      }
      if (years <= 0 && months <= 0) return 'Kurang dari 1 bulan';
      if (years <= 0) return '$months Bulan';
      if (months <= 0) return '$years Tahun';
      return '$years Thn $months Bln';
    } catch (_) {
      return '-';
    }
  }

  /// Generate PDF Dokumen Surat Izin Cuti A4 (1:1 Sesuai Versi Web Base)
  static Future<Uint8List> generateLeavePdf(LeaveModel leave, {PdfPageFormat pageFormat = PdfPageFormat.a4}) async {
    final pdf = pw.Document();

    final isApproved = leave.isApproved;
    final isRejected = leave.isRejected;

    final formattedStartDate = formatTanggalIndo(leave.tanggalMulai);
    final formattedEndDate = formatTanggalIndo(leave.tanggalSelesai);
    final formattedCreatedDate = formatTanggalIndo(leave.createdAt);
    final masaKerjaText = hitungMasaKerja(leave.tanggalMasuk);

    // Muat Logo Perusahaan dari Asset
    pw.MemoryImage? logoImage;
    try {
      final byteData = await rootBundle.load('assets/images/Nakakin.png');
      final bytes = byteData.buffer.asUint8List();
      logoImage = pw.MemoryImage(bytes);
    } catch (_) {
      logoImage = null;
    }

    pdf.addPage(
      pw.Page(
        pageFormat: pageFormat,
        margin: const pw.EdgeInsets.symmetric(horizontal: 36, vertical: 28),
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              // 1. KOP SURAT PERUSAHAAN (Teks Kiri, Logo Kanan)
              pw.Row(
                crossAxisAlignment: pw.CrossAxisAlignment.center,
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
                            fontSize: 16,
                            color: pdfBlack,
                            letterSpacing: 0.5,
                          ),
                        ),
                        pw.SizedBox(height: 2),
                        pw.Text(
                          'MANUFACTURER OF ALUMINUM DIE CASTING & MACHINING',
                          style: pw.TextStyle(
                            fontWeight: pw.FontWeight.bold,
                            fontSize: 8.5,
                            color: pdfBlack,
                          ),
                        ),
                        pw.SizedBox(height: 2),
                        pw.Text(
                          'Kawasan Industri KIIC, Jl. Maligi VI Lot L-4, Telukjambe Barat, Karawang 41361',
                          style: const pw.TextStyle(fontSize: 8, color: pdfTextDark),
                        ),
                        pw.Text(
                          'Telp: (0267) 863-1234 • Email: hrd@nakakin.co.id • Website: www.nakakin.co.id',
                          style: const pw.TextStyle(fontSize: 8, color: pdfTextDark),
                        ),
                      ],
                    ),
                  ),
                  if (logoImage != null)
                    pw.Container(
                      width: 90,
                      height: 52,
                      child: pw.Image(logoImage, fit: pw.BoxFit.contain),
                    ),
                ],
              ),
              pw.SizedBox(height: 6),

              // Garis Ganda Pembatas Kop Surat (Double Border Style)
              pw.Container(height: 1.8, color: pdfBlack),
              pw.SizedBox(height: 1.2),
              pw.Container(height: 0.6, color: pdfBlack),
              pw.SizedBox(height: 14),

              // 2. JUDUL DOKUMEN & NOMOR SURAT
              pw.Center(
                child: pw.Column(
                  children: [
                    pw.Text(
                      'FORMULIR PERMOHONAN & SURAT IZIN CUTI',
                      style: pw.TextStyle(
                        fontWeight: pw.FontWeight.bold,
                        fontSize: 13,
                        decoration: pw.TextDecoration.underline,
                      ),
                    ),
                    pw.SizedBox(height: 3),
                    pw.Text(
                      'Nomor: ${leave.nomorSurat}',
                      style: const pw.TextStyle(fontSize: 9.5, color: pdfTextDark),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 12),

              // Kalimat Pembuka
              pw.Text(
                'Yang bertanda tangan di bawah ini, menerangkan bahwa karyawan:',
                style: const pw.TextStyle(fontSize: 8.5, color: pdfTextDark),
              ),
              pw.SizedBox(height: 6),

              // 3. TABEL DATA KARYAWAN (2 Kolom Data)
              pw.Table(
                columnWidths: {
                  0: const pw.FlexColumnWidth(2.3),
                  1: const pw.FlexColumnWidth(0.2),
                  2: const pw.FlexColumnWidth(3.8),
                  3: const pw.FlexColumnWidth(2.0),
                  4: const pw.FlexColumnWidth(0.2),
                  5: const pw.FlexColumnWidth(3.0),
                },
                children: [
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('Nama Lengkap', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text(leave.namaLengkap ?? '-', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('Tanggal Masuk', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text(formatTanggalIndo(leave.tanggalMasuk), style: const pw.TextStyle(fontSize: 8.5)),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('N I K', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text(leave.nik ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('Masa Kerja', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text(masaKerjaText, style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('Departemen / Bagian', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('${leave.namaDept ?? "-"} (${leave.kodeCuti ?? "-"})', style: const pw.TextStyle(fontSize: 8.5)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('Sisa Hak Cuti', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('${leave.sisaCuti ?? 12} Hari', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('Jabatan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text(leave.namaJabatan ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text('No. Handphone / WA', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5)),
                      ),
                      pw.Padding(padding: const pw.EdgeInsets.symmetric(vertical: 2), child: pw.Text(':', style: const pw.TextStyle(fontSize: 8.5))),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 2),
                        child: pw.Text((leave.noHp?.isNotEmpty == true ? leave.noHp : leave.kontakDarurat) ?? '-', style: const pw.TextStyle(fontSize: 8.5)),
                      ),
                    ],
                  ),
                ],
              ),
              pw.SizedBox(height: 10),

              // Kalimat Pengantar Detail Cuti
              pw.Text(
                'Mengajukan permohonan izin cuti dengan rincian sebagai berikut:',
                style: const pw.TextStyle(fontSize: 8.5, color: pdfTextDark),
              ),
              pw.SizedBox(height: 6),

              // 4. TABEL DETAIL PERMOHONAN CUTI (Sama Persis Web Base)
              pw.Table(
                border: pw.TableBorder.all(color: pdfBlack, width: 0.7),
                columnWidths: {
                  0: const pw.FlexColumnWidth(2.5),
                  1: const pw.FlexColumnWidth(2.8),
                  2: const pw.FlexColumnWidth(1.5),
                  3: const pw.FlexColumnWidth(3.2),
                },
                children: [
                  pw.TableRow(
                    decoration: const pw.BoxDecoration(color: pdfBgLight),
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(5),
                        child: pw.Text('Jenis Cuti', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(5),
                        child: pw.Text('Periode Tanggal Cuti', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(5),
                        child: pw.Text('Jumlah Hari', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(5),
                        child: pw.Text('Alasan / Keperluan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 8.5), textAlign: pw.TextAlign.center),
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
                          style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 9),
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
                      pw.Container(
                        color: pdfBgRowAlt,
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.start,
                          children: [
                            pw.RichText(
                              text: pw.TextSpan(
                                children: [
                                  pw.TextSpan(
                                    text: 'Alamat / Kontak Selama Cuti: ',
                                    style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5, color: pdfBlack),
                                  ),
                                  pw.TextSpan(
                                    text: '${(leave.alamatSelamaCuti != null && leave.alamatSelamaCuti!.isNotEmpty) ? leave.alamatSelamaCuti! : (leave.alamat ?? "Di alamat tempat tinggal terdaftar")} (Kontak Darurat: ${(leave.kontakDarurat != null && leave.kontakDarurat!.isNotEmpty) ? leave.kontakDarurat! : (leave.noHp ?? "-")})',
                                    style: const pw.TextStyle(fontSize: 7.5, color: pdfTextDark),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              pw.SizedBox(height: 8),

              // 5. KOTAK KEPUTUSAN & CATATAN ATASAN
              pw.Container(
                width: double.infinity,
                padding: const pw.EdgeInsets.all(6),
                decoration: pw.BoxDecoration(
                  color: pdfBgLight,
                  border: pw.Border.all(color: const PdfColor.fromInt(0xFFE2E8F0), width: 0.8),
                ),
                child: pw.RichText(
                  text: pw.TextSpan(
                    children: [
                      pw.TextSpan(
                        text: 'Catatan & Keputusan: ',
                        style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5, color: pdfBlack),
                      ),
                      pw.TextSpan(
                        text: leave.catatanAtasan?.isNotEmpty == true
                            ? leave.catatanAtasan!
                            : 'Disetujui sesuai dengan ketentuan dan prosedur operasional PT. Nakakin Indonesia.',
                        style: const pw.TextStyle(fontSize: 7.5, color: pdfTextDark),
                      ),
                    ],
                  ),
                ),
              ),
              pw.SizedBox(height: 14),

              // 6. LEMBAR TANDA TANGAN (4 KOLOM APPROVAL BERIKAN BORDER HITAM 1:1 SESUAI WEB BASE)
              pw.Table(
                border: pw.TableBorder.all(color: pdfBlack, width: 0.7),
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
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 2),
                        child: pw.Center(
                          child: pw.Text('1. Operator / Karyawan', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 2),
                        child: pw.Center(
                          child: pw.Text('2. Leader / Supervisor', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 2),
                        child: pw.Center(
                          child: pw.Text('3. Manager', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7.5)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 2),
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
                        padding: const pw.EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text('Karawang, ${formatTanggalSingkat(leave.createdAt)}', style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark)),
                            pw.SizedBox(height: 6),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(color: pdfSuccessGreen, width: 1),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                              ),
                              child: pw.Text(
                                'DIGITAL SIGNED',
                                style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfSuccessGreen),
                              ),
                            ),
                            pw.SizedBox(height: 6),
                            pw.Text('( ${leave.namaLengkap ?? "Pemohon"} )', style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7), textAlign: pw.TextAlign.center),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              '${leave.namaJabatan ?? "Karyawan"} ${leave.namaDept ?? ""}',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),

                      // 2. Kolom Leader / Supervisor
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text(
                              leave.spvAt != null ? formatTanggalSingkat(leave.spvAt) : '-',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
                            ),
                            pw.SizedBox(height: 6),
                            if (leave.spvId != null)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfSuccessGreen, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'APPROVED',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfSuccessGreen),
                                ),
                              )
                            else if (leave.employeeLevel >= 3)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfTextMuted, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'BYPASS',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfTextMuted),
                                ),
                              )
                            else if (isRejected && leave.spvId == null)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfDangerRed, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'REJECTED',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfDangerRed),
                                ),
                              )
                            else
                              pw.Padding(
                                padding: const pw.EdgeInsets.symmetric(vertical: 3),
                                child: pw.Text(
                                  '(Menunggu Approval)',
                                  style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted, fontStyle: pw.FontStyle.italic),
                                ),
                              ),
                            pw.SizedBox(height: 6),
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
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),

                      // 3. Kolom Manager
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text(
                              leave.managerAt != null ? formatTanggalSingkat(leave.managerAt) : '-',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
                            ),
                            pw.SizedBox(height: 6),
                            if (leave.managerId != null)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfSuccessGreen, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'APPROVED',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfSuccessGreen),
                                ),
                              )
                            else if (leave.employeeLevel >= 6)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfTextMuted, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'BYPASS',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfTextMuted),
                                ),
                              )
                            else if (isRejected && leave.spvId != null && leave.managerId == null)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfDangerRed, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'REJECTED',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfDangerRed),
                                ),
                              )
                            else
                              pw.Padding(
                                padding: const pw.EdgeInsets.symmetric(vertical: 3),
                                child: pw.Text(
                                  '(Menunggu Approval)',
                                  style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted, fontStyle: pw.FontStyle.italic),
                                ),
                              ),
                            pw.SizedBox(height: 6),
                            pw.Text(
                              '( ${leave.managerId != null ? (leave.managerName ?? "Manager") : (leave.employeeLevel >= 6 ? "-" : "...................")} )',
                              style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7),
                              textAlign: pw.TextAlign.center,
                            ),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              leave.managerId != null
                                  ? (leave.managerJabatan ?? "Department Manager")
                                  : 'Department / Plant Manager',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
                              textAlign: pw.TextAlign.center,
                            ),
                          ],
                        ),
                      ),

                      // 4. Kolom HRD
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 6, horizontal: 4),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text(
                              (isApproved && (leave.hrdAt != null || leave.approvedAt != null))
                                  ? formatTanggalSingkat(leave.hrdAt ?? leave.approvedAt)
                                  : '-',
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
                            ),
                            pw.SizedBox(height: 6),
                            if (isApproved)
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfBluePrimary, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'VERIFIED HRD',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfBluePrimary),
                                ),
                              )
                            else if (isRejected && (leave.managerId != null || leave.employeeLevel >= 5))
                              pw.Container(
                                padding: const pw.EdgeInsets.symmetric(horizontal: 5, vertical: 3),
                                decoration: pw.BoxDecoration(
                                  border: pw.Border.all(color: pdfDangerRed, width: 1),
                                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(2)),
                                ),
                                child: pw.Text(
                                  'REJECTED',
                                  style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 6.5, color: pdfDangerRed),
                                ),
                              )
                            else
                              pw.Padding(
                                padding: const pw.EdgeInsets.symmetric(vertical: 3),
                                child: pw.Text(
                                  '(Menunggu Approval)',
                                  style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted, fontStyle: pw.FontStyle.italic),
                                ),
                              ),
                            pw.SizedBox(height: 6),
                            pw.Text(
                              '( ${isApproved ? (leave.hrdName ?? leave.approverName ?? "Siti Rahmawati, S.Psi") : "..................."} )',
                              style: pw.TextStyle(fontWeight: pw.FontWeight.bold, fontSize: 7),
                              textAlign: pw.TextAlign.center,
                            ),
                            pw.SizedBox(height: 1),
                            pw.Text(
                              isApproved ? (leave.hrdJabatan ?? "HRD & GA Manager") : "HRD",
                              style: const pw.TextStyle(fontSize: 6.5, color: pdfTextDark),
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

              // 7. FOOTER RESMI DOKUMEN ELEKTRONIK (Garis Putus-putus)
              pw.Container(
                padding: const pw.EdgeInsets.only(top: 6),
                decoration: const pw.BoxDecoration(
                  border: pw.Border(top: pw.BorderSide(color: PdfColor.fromInt(0xFFCCCCCC), width: 0.6, style: pw.BorderStyle.dashed)),
                ),
                child: pw.Center(
                  child: pw.Text(
                    'Dokumen ini diterbitkan secara elektronik oleh Aplikasi E-Cuti PT. Nakakin Indonesia • Sah tanpa tanda tangan basah berdasarkan verifikasi sistem.',
                    style: const pw.TextStyle(fontSize: 6.5, color: pdfTextMuted),
                    textAlign: pw.TextAlign.center,
                  ),
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