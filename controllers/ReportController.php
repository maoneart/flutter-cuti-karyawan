<?php
/**
 * Report Controller
 * PT. Nakakin Indonesia Leave Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/functions.php';
require_once __DIR__ . '/../config/session.php';

class ReportController {
    
    // Export Leave Data to CSV / Excel
    public function exportExcel() {
        requireRole('admin');

        $pdo = getDbConnection();

        $startDate = cleanInput($_GET['start_date'] ?? date('Y-01-01'));
        $endDate = cleanInput($_GET['end_date'] ?? date('Y-12-31'));
        $deptId = (int)($_GET['dept_id'] ?? 0);
        $typeId = (int)($_GET['type_id'] ?? 0);
        $status = cleanInput($_GET['status'] ?? 'all');

        $sql = "
            SELECT lr.nomor_surat, e.nik, e.nama_lengkap, d.nama_dept, p.nama_jabatan,
                   lt.nama_cuti, lr.tanggal_mulai, lr.tanggal_selesai, lr.total_hari,
                   lr.alasan, lr.status, ap.nama_lengkap as nama_atasan, lr.created_at
            FROM pengajuan_cuti lr
            JOIN karyawan e ON lr.employee_id = e.id
            JOIN departemen d ON e.departemen_id = d.id
            JOIN jabatan p ON e.jabatan_id = p.id
            JOIN jenis_cuti lt ON lr.leave_type_id = lt.id
            LEFT JOIN karyawan ap ON lr.approved_by = ap.id
            WHERE lr.tanggal_mulai >= ? AND lr.tanggal_selesai <= ?
        ";

        $params = [$startDate, $endDate];

        if ($deptId > 0) {
            $sql .= " AND e.departemen_id = ? ";
            $params[] = $deptId;
        }

        if ($typeId > 0) {
            $sql .= " AND lr.leave_type_id = ? ";
            $params[] = $typeId;
        }

        if ($status !== 'all') {
            $sql .= " AND lr.status = ? ";
            $params[] = $status;
        }

        $sql .= " ORDER BY lr.tanggal_mulai DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        $filename = 'Rekap_Cuti_Nakakin_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel Indonesian compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Company Header in CSV
        fputcsv($output, ['REKAPITULASI DATA CUTI KARYAWAN - PT. NAKAKIN INDONESIA']);
        fputcsv($output, ['Periode:', $startDate . ' s/d ' . $endDate]);
        fputcsv($output, ['Diekspor pada:', date('d-m-Y H:i:s') . ' WIB']);
        fputcsv($output, []); // blank line

        // Table Header
        fputcsv($output, [
            'No',
            'No. Surat Cuti',
            'NIK',
            'Nama Karyawan',
            'Departemen',
            'Jabatan',
            'Jenis Cuti',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Total Hari Kerja',
            'Alasan / Keperluan',
            'Status',
            'Disetujui Oleh',
            'Waktu Pengajuan'
        ]);

        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['nomor_surat'],
                $row['nik'],
                $row['nama_lengkap'],
                $row['nama_dept'],
                $row['nama_jabatan'],
                $row['nama_cuti'],
                $row['tanggal_mulai'],
                $row['tanggal_selesai'],
                $row['total_hari'],
                $row['alasan'],
                strtoupper($row['status']),
                $row['nama_atasan'] ?: '-',
                $row['created_at']
            ]);
        }

        fclose($output);
        exit;
    }
}
