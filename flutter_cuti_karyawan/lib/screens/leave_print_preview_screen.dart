import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:pdf/pdf.dart';
import 'package:printing/printing.dart';
import '../config/app_theme.dart';
import '../models/leave_model.dart';
import '../services/leave_print_service.dart';

class LeavePrintPreviewScreen extends StatelessWidget {
  final LeaveModel leave;

  const LeavePrintPreviewScreen({super.key, required this.leave});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryAccent = isDark ? AppTheme.darkPrimary : AppTheme.primary;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBg : AppTheme.background,
      appBar: AppBar(
        title: Text(
          'Surat Izin Cuti',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: isDark ? Colors.white : AppTheme.primaryDark,
          ),
        ),
        centerTitle: false,
        leading: IconButton(
          icon: Icon(
            CupertinoIcons.chevron_back,
            color: isDark ? Colors.white : AppTheme.primaryDark,
          ),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          IconButton(
            icon: Icon(
              CupertinoIcons.share,
              color: isDark ? Colors.white : AppTheme.primaryDark,
            ),
            tooltip: 'Bagikan PDF',
            onPressed: () => LeavePrintService.shareLeave(context, leave),
          ),
          IconButton(
            icon: Icon(
              CupertinoIcons.printer,
              color: isDark ? Colors.white : AppTheme.primaryDark,
            ),
            tooltip: 'Cetak Dokumen',
            onPressed: () => LeavePrintService.printLeave(context, leave),
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Top Info Bar
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkSurface : Colors.white,
                border: Border(
                  bottom: BorderSide(
                    color: isDark ? AppTheme.darkBorder : const Color(0xFFE2E8F0),
                    width: 1,
                  ),
                ),
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: primaryAccent.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      CupertinoIcons.doc_text_fill,
                      size: 20,
                      color: primaryAccent,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          leave.nomorSurat.isNotEmpty ? leave.nomorSurat : 'Surat Cuti',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: isDark ? Colors.white : AppTheme.textPrimary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${leave.namaLengkap ?? "Karyawan"} • ${leave.namaCuti ?? "Cuti"} (${leave.totalHari} Hari)',
                          style: TextStyle(
                            fontSize: 11.5,
                            color: isDark ? const Color(0xFF94A3B8) : AppTheme.textSecondary,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            // PDF Viewer Area
            Expanded(
              child: PdfPreview(
                maxPageWidth: 700,
                build: (format) => LeavePrintService.generateLeavePdf(leave, pageFormat: format),
                canChangeOrientation: false,
                canChangePageFormat: false,
                canDebug: false,
                previewPageMargin: const EdgeInsets.all(16),
                loadingWidget: const Center(
                  child: CupertinoActivityIndicator(radius: 16),
                ),
                pdfFileName: 'Surat_Cuti_${leave.nomorSurat.replaceAll("/", "_")}.pdf',
                actions: [
                  PdfPreviewAction(
                    icon: const Icon(CupertinoIcons.printer_fill),
                    onPressed: (ctx, build, format) async {
                      await LeavePrintService.printLeave(context, leave);
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
