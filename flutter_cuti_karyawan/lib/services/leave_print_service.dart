import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import '../models/leave_model.dart';

class LeavePrintService {
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

    final fontRegular = pw.Font.helvetica();
    final fontBold = pw.Font.helveticaBold();
    final fontOblique = pw.Font.helveticaOblique();

    final isApproved = leave.isApproved;
    final isRejected = leave.isRejected;

    final formattedStartDate = formatTanggalIndo(leave.tanggalMulai);
    final formattedEndDate = formatTanggalIndo(leave.tanggalSelesai);
    final formattedCreatedDate = formatTanggalIndo(leave.createdAt);
    final printDate = DateFormat('dd MMMM yyyy', 'id_ID').format(DateTime.now());

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
                            font: fontBold,
                            fontSize: 15,
                            color: PdfColors.blue900,
                          ),
                        ),
                        pw.SizedBox(height: 2),
                        pw.Text(
                          'MANUFACTURER OF ALUMINUM DIE CASTING & MACHINING',
                          style: pw.TextStyle(
                            font: fontBold,
                            fontSize: 8.5,
                            color: PdfColors.grey800,
                          ),
                        ),
                        pw.SizedBox(height: 2),
                        pw.Text(
                          'Kawasan Industri KIIC, Jl. Maligi VI Lot L-4, Telukjambe Barat, Karawang 41361',
                          style: pw.TextStyle(font: fontRegular, fontSize: 8, color: PdfColors.grey700),
                        ),
                        pw.Text(
                          'Telp: (0267) 863-1234 • Email: hrd@nakakin.co.id • Website: www.nakakin.co.id',
                          style: pw.TextStyle(font: fontRegular, fontSize: 8, color: PdfColors.grey700),
                        ),
                      ],
                    ),
                  ),
                  pw.Container(
                    padding: const pw.EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: pw.BoxDecoration(
                      border: pw.Border.all(color: PdfColors.blue900, width: 1.5),
                      borderRadius: const pw.BorderRadius.all(pw.Radius.circular(4)),
                    ),
                    child: pw.Text(
                      'NAKAKIN',
                      style: pw.TextStyle(
                        font: fontBold,
                        fontSize: 13,
                        color: PdfColors.blue900,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ),
                ],
              ),
              pw.SizedBox(height: 6),
              // Double divider lines
              pw.Container(height: 2, color: PdfColors.blue900),
              pw.SizedBox(height: 1.5),
              pw.Container(height: 0.75, color: PdfColors.blue900),
              pw.SizedBox(height: 12),

              // 2. JUDUL DOKUMEN & NOMOR SURAT
              pw.Center(
                child: pw.Column(
                  children: [
                    pw.Text(
                      'FORMULIR PERMOHONAN & SURAT IZIN CUTI',
                      style: pw.TextStyle(
                        font: fontBold,
                        fontSize: 13,
                        color: PdfColors.black,
                        decoration: pw.TextDecoration.underline,
                      ),
                    ),
                    pw.SizedBox(height: 3),
                    pw.Text(
                      'Nomor: ${leave.nomorSurat.isNotEmpty ? leave.nomorSurat : "-"}',
                      style: pw.TextStyle(font: fontBold, fontSize: 9.5, color: PdfColors.grey800),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 12),

              pw.Text(
                'Yang bertanda tangan di bawah ini, menerangkan bahwa karyawan:',
                style: pw.TextStyle(font: fontRegular, fontSize: 9),
              ),
              pw.SizedBox(height: 6),

              // 3. TABEL IDENTITAS KARYAWAN
              pw.Container(
                padding: const pw.EdgeInsets.all(8),
                decoration: pw.BoxDecoration(
                  color: PdfColors.grey100,
                  border: pw.Border.all(color: PdfColors.grey300),
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
                        pw.Text('Nama Lengkap', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                        pw.Text(':', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text(leave.namaLengkap ?? '-', style: pw.TextStyle(font: fontBold, fontSize: 8.5, color: PdfColors.blue900)),
                        pw.Text('Departemen', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                        pw.Text(':', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text(leave.namaDept ?? '-', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                      ],
                    ),
                    pw.TableRow(
                      children: [
                        pw.Text('N I K', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                        pw.Text(':', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text(leave.nik ?? '-', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text('Jabatan', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                        pw.Text(':', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text(leave.namaJabatan ?? '-', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                      ],
                    ),
                    pw.TableRow(
                      children: [
                        pw.Text('Tgl Pengajuan', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                        pw.Text(':', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text(formattedCreatedDate, style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text('No. HP / Kontak', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                        pw.Text(':', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                        pw.Text(leave.kontakDarurat ?? '-', style: pw.TextStyle(font: fontRegular, fontSize: 8.5)),
                      ],
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 10),

              pw.Text(
                'Mengajukan permohonan izin cuti kerja dengan rincian data sebagai berikut:',
                style: pw.TextStyle(font: fontRegular, fontSize: 9),
              ),
              pw.SizedBox(height: 6),

              // 4. TABEL DETAIL PERMOHONAN CUTI
              pw.Table(
                border: pw.TableBorder.all(color: PdfColors.grey400, width: 0.8),
                columnWidths: {
                  0: const pw.FlexColumnWidth(2.5),
                  1: const pw.FlexColumnWidth(3.5),
                  2: const pw.FlexColumnWidth(1.8),
                  3: const pw.FlexColumnWidth(4.2),
                },
                children: [
                  pw.TableRow(
                    decoration: const pw.BoxDecoration(color: PdfColors.blue50),
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Jenis Cuti', style: pw.TextStyle(font: fontBold, fontSize: 8.5, color: PdfColors.blue900)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Periode Tanggal Cuti', style: pw.TextStyle(font: fontBold, fontSize: 8.5, color: PdfColors.blue900), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Jumlah Hari', style: pw.TextStyle(font: fontBold, fontSize: 8.5, color: PdfColors.blue900), textAlign: pw.TextAlign.center),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Alasan / Keperluan', style: pw.TextStyle(font: fontBold, fontSize: 8.5, color: PdfColors.blue900)),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(leave.namaCuti ?? 'Cuti Tahunan', style: pw.TextStyle(font: fontBold, fontSize: 8.5)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          '$formattedStartDate\ns/d $formattedEndDate',
                          style: pw.TextStyle(font: fontRegular, fontSize: 8),
                          textAlign: pw.TextAlign.center,
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          '${leave.totalHari} Hari Kerja',
                          style: pw.TextStyle(font: fontBold, fontSize: 8.5, color: PdfColors.blue900),
                          textAlign: pw.TextAlign.center,
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          leave.alasan.isNotEmpty ? leave.alasan : '-',
                          style: pw.TextStyle(font: fontRegular, fontSize: 8),
                        ),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Alamat Selama Cuti', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          (leave.alamatSelamaCuti != null && leave.alamatSelamaCuti!.isNotEmpty) ? leave.alamatSelamaCuti! : 'Di alamat tempat tinggal terdaftar',
                          style: pw.TextStyle(font: fontRegular, fontSize: 8),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text('Status Approval', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(6),
                        child: pw.Text(
                          isApproved ? 'DISETUJUI (APPROVED)' : (isRejected ? 'DITOLAK (REJECTED)' : 'PROSES REVIEW (PENDING)'),
                          style: pw.TextStyle(
                            font: fontBold,
                            fontSize: 8,
                            color: isApproved ? PdfColors.green700 : (isRejected ? PdfColors.red700 : PdfColors.orange700),
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
                  color: PdfColors.grey50,
                  border: pw.Border.all(color: PdfColors.grey300),
                  borderRadius: const pw.BorderRadius.all(pw.Radius.circular(4)),
                ),
                child: pw.Column(
                  crossAxisAlignment: pw.CrossAxisAlignment.start,
                  children: [
                    pw.Text('Catatan & Keputusan Atasan:', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                    pw.SizedBox(height: 2),
                    pw.Text(
                      (leave.catatanAtasan != null && leave.catatanAtasan!.isNotEmpty)
                          ? leave.catatanAtasan!
                          : (isApproved
                              ? 'Disetujui sesuai dengan ketentuan dan prosedur ketenagakerjaan PT. Nakakin Indonesia.'
                              : (isRejected
                                  ? 'Pengajuan tidak dapat disetujui: ${leave.rejectionReason ?? "Kebutuhan operasional pabrik."}'
                                  : 'Sedang dalam proses peninjauan persetujuan hierarki atasan.')),
                      style: pw.TextStyle(font: fontOblique, fontSize: 7.5, color: PdfColors.grey800),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 18),

              // 6. LEMBAR TANDA TANGAN (3 KOLOM)
              pw.Table(
                border: pw.TableBorder.all(color: PdfColors.grey400, width: 0.6),
                columnWidths: {
                  0: const pw.FlexColumnWidth(1),
                  1: const pw.FlexColumnWidth(1),
                  2: const pw.FlexColumnWidth(1),
                },
                children: [
                  pw.TableRow(
                    decoration: const pw.BoxDecoration(color: PdfColors.grey100),
                    children: [
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 6),
                        child: pw.Center(
                          child: pw.Text('Pemohon Cuti', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 6),
                        child: pw.Center(
                          child: pw.Text('Mengetahui / Atasan Langsung', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                        ),
                      ),
                      pw.Padding(
                        padding: const pw.EdgeInsets.symmetric(vertical: 4, horizontal: 6),
                        child: pw.Center(
                          child: pw.Text('Diverifikasi HRD Department', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                        ),
                      ),
                    ],
                  ),
                  pw.TableRow(
                    children: [
                      // Kolom Pemohon
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(8),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text('Karawang, $formattedCreatedDate', style: pw.TextStyle(font: fontRegular, fontSize: 7)),
                            pw.SizedBox(height: 10),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(color: PdfColors.blue700, width: 1),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(3)),
                              ),
                              child: pw.Text(
                                'DIGITAL SIGNED',
                                style: pw.TextStyle(font: fontBold, fontSize: 7, color: PdfColors.blue700),
                              ),
                            ),
                            pw.SizedBox(height: 10),
                            pw.Text('( ${leave.namaLengkap ?? "Pemohon"} )', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                            pw.Text('NIK: ${leave.nik ?? "-"}', style: pw.TextStyle(font: fontRegular, fontSize: 7)),
                          ],
                        ),
                      ),

                      // Kolom Atasan Langsung
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(8),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text('Menyetujui,', style: pw.TextStyle(font: fontRegular, fontSize: 7)),
                            pw.SizedBox(height: 10),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(
                                  color: (leave.spvId != null || leave.managerId != null || isApproved) ? PdfColors.green700 : PdfColors.grey500,
                                  width: 1,
                                ),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(3)),
                              ),
                              child: pw.Text(
                                (leave.spvId != null || leave.managerId != null || isApproved) ? 'APPROVED BY ATASAN' : 'MENUNGGU APPROVAL',
                                style: pw.TextStyle(
                                  font: fontBold,
                                  fontSize: 7,
                                  color: (leave.spvId != null || leave.managerId != null || isApproved) ? PdfColors.green700 : PdfColors.grey600,
                                ),
                              ),
                            ),
                            pw.SizedBox(height: 10),
                            pw.Text('( ${leave.spvName ?? leave.managerName ?? "..........................."} )', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                            pw.Text('Leader / Spv / Manager Dept', style: pw.TextStyle(font: fontRegular, fontSize: 7)),
                          ],
                        ),
                      ),

                      // Kolom HRD
                      pw.Padding(
                        padding: const pw.EdgeInsets.all(8),
                        child: pw.Column(
                          crossAxisAlignment: pw.CrossAxisAlignment.center,
                          children: [
                            pw.Text('Persetujuan Final,', style: pw.TextStyle(font: fontRegular, fontSize: 7)),
                            pw.SizedBox(height: 10),
                            pw.Container(
                              padding: const pw.EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: pw.BoxDecoration(
                                border: pw.Border.all(
                                  color: isApproved ? PdfColors.blue900 : PdfColors.grey500,
                                  width: 1,
                                ),
                                borderRadius: const pw.BorderRadius.all(pw.Radius.circular(3)),
                              ),
                              child: pw.Text(
                                isApproved ? 'VERIFIED HRD' : 'MENUNGGU VERIFIKASI',
                                style: pw.TextStyle(
                                  font: fontBold,
                                  fontSize: 7,
                                  color: isApproved ? PdfColors.blue900 : PdfColors.grey600,
                                ),
                              ),
                            ),
                            pw.SizedBox(height: 10),
                            pw.Text('( ${leave.hrdName ?? leave.approverName ?? "HRD & GA Department"} )', style: pw.TextStyle(font: fontBold, fontSize: 8)),
                            pw.Text('HRD & GA Manager', style: pw.TextStyle(font: fontRegular, fontSize: 7)),
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
                  border: pw.Border(top: pw.BorderSide(color: PdfColors.grey300, width: 0.5)),
                ),
                child: pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                  children: [
                    pw.Text(
                      'Dicetak melalui Aplikasi E-Cuti PT. Nakakin Indonesia pada $printDate',
                      style: pw.TextStyle(font: fontRegular, fontSize: 6.5, color: PdfColors.grey600),
                    ),
                    pw.Text(
                      'Dokumen elektronik sah tanpa tanda tangan basah.',
                      style: pw.TextStyle(font: fontRegular, fontSize: 6.5, color: PdfColors.grey600),
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
