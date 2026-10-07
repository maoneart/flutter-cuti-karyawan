<?php
/**
 * Excel & CSV Helper Utility
 * Lightweight pure PHP parser and generator for .xlsx and .csv
 * PT. Nakakin Indonesia Leave Management System
 */

class ExcelHelper {

    /**
     * Parse uploaded file (.xlsx or .csv) into array of rows
     */
    public static function parseFile($filePath, $originalName = '') {
        $ext = strtolower(pathinfo($originalName ?: $filePath, PATHINFO_EXTENSION));

        if ($ext === 'csv' || $ext === 'txt') {
            return self::parseCsv($filePath);
        } elseif ($ext === 'xlsx') {
            return self::parseXlsx($filePath);
        } else {
            // Try CSV first, if fails try XLSX
            $rows = self::parseCsv($filePath);
            if (!empty($rows)) return $rows;
            return self::parseXlsx($filePath);
        }
    }

    /**
     * Parse CSV file with comma or semicolon delimiter detection
     */
    public static function parseCsv($filePath) {
        $rows = [];
        if (!file_exists($filePath)) return $rows;

        $content = file_get_contents($filePath);
        // Remove UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        // Detect delimiter (comma, semicolon, tab)
        $firstLine = strtok($content, "\r\n");
        $delimiters = [',', ';', "\t"];
        $chosenDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $d) {
            $cnt = substr_count($firstLine, $d);
            if ($cnt > $maxCount) {
                $maxCount = $cnt;
                $chosenDelimiter = $d;
            }
        }

        $lines = preg_split("/\r\n|\n|\r/", $content);
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') continue;
            $row = str_getcsv($line, $chosenDelimiter);
            $cleanRow = [];
            foreach ($row as $val) {
                $v = trim($val);
                // Strip ="..." Excel formula wrapper if present
                if (preg_match('/^="?(.*?)"?$/', $v, $m)) {
                    $v = $m[1];
                }
                // Convert scientific notation (e.g. 6.28123E+12) back to full digit string
                if (preg_match('/^[0-9]+(\.[0-9]+)?[eE]\+[0-9]+$/', $v)) {
                    $v = sprintf('%.0f', (float)$v);
                }
                $cleanRow[] = trim($v);
            }
            $rows[] = $cleanRow;
        }

        return $rows;
    }

    /**
     * Parse XLSX file using PHP native ZipArchive & SimpleXML
     */
    public static function parseXlsx($filePath) {
        $rows = [];
        if (!class_exists('ZipArchive') || !file_exists($filePath)) {
            return $rows;
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== TRUE) {
            return $rows;
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $str = '';
                        foreach ($si->r as $r) {
                            $str .= (string)$r->t;
                        }
                        $sharedStrings[] = $str;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Read first worksheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            // Find any sheet in xl/worksheets/
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, 'xl/worksheets/sheet') === 0) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if (!$sheetXml) return $rows;

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData)) return $rows;

        foreach ($xml->sheetData->row as $rowNode) {
            $currentRow = [];
            $maxColIdx = 0;

            foreach ($rowNode->c as $c) {
                $r = (string)$c['r']; // e.g. A1, B2
                $colLetters = preg_replace('/[0-9]/', '', $r);
                $colIdx = self::columnLetterToIndex($colLetters);

                // Fill gaps if empty cells
                while (count($currentRow) < $colIdx) {
                    $currentRow[] = '';
                }

                $val = '';
                $type = (string)$c['t'];

                if (isset($c->v)) {
                    $rawVal = (string)$c->v;
                    if ($type === 's') {
                        // Shared string
                        $idx = (int)$rawVal;
                        $val = $sharedStrings[$idx] ?? '';
                    } elseif ($type === 'b') {
                        $val = ($rawVal == '1') ? 'TRUE' : 'FALSE';
                    } else {
                        $val = $rawVal;
                    }
                } elseif (isset($c->is->t)) {
                    $val = (string)$c->is->t;
                }

                $currentRow[] = trim($val);
            }

            if (!empty(array_filter($currentRow, function($v) { return $v !== ''; }))) {
                $rows[] = $currentRow;
            }
        }

        return $rows;
    }

    private static function columnLetterToIndex($letters) {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Generate Template CSV Content
     */
    public static function generateTemplateCsv() {
        $headers = [
            'NIK',
            'Nama Lengkap',
            'Email',
            'Role (operator/staff/leader/supervisor/manager/hrd/admin)',
            'Departemen',
            'Jabatan',
            'Tanggal Masuk (YYYY-MM-DD)',
            'Kuota Cuti Default (12)',
            'Jenis Kelamin (Laki-laki/Perempuan)',
            'Agama (Islam/Kristen/Katolik/Hindu/Buddha/Konghucu)',
            'Status Pernikahan (Belum Menikah/Menikah/Duda/Janda)',
            'No HP/WhatsApp (Contoh: 6281234567891)',
            'Alamat Lengkap',
            'Status Aktif (Aktif/Nonaktif)'
        ];

        $examples = [
            [
                'OP-011',
                'Ahmad Fauzi',
                'ahmad.fauzi@nakakin.co.id',
                'operator',
                'Core',
                'Operator Produksi',
                '2024-02-01',
                '12',
                'Laki-laki',
                'Islam',
                'Belum Menikah',
                '="6281234567891"',
                'Dusun Sukamaju RT 01/02, Karawang Barat',
                'Aktif'
            ],
            [
                'STF-012',
                'Dewi Lestari',
                'dewi.lestari@nakakin.co.id',
                'staff',
                'Accounting',
                'Staff',
                '2023-11-15',
                '12',
                'Perempuan',
                'Islam',
                'Menikah',
                '="6281298765432"',
                'Perum Telukjambe Blok C-12, Karawang',
                'Aktif'
            ],
            [
                'LDR-013',
                'Rahmat Hidayat',
                'rahmat.hidayat@nakakin.co.id',
                'leader',
                'Diecasting',
                'Leader',
                '2022-05-10',
                '12',
                'Laki-laki',
                'Islam',
                'Menikah',
                '="6281345678901"',
                'Klari, Karawang Timur',
                'Aktif'
            ]
        ];

        $fp = fopen('php://temp', 'r+');
        // Add UTF-8 BOM so Excel opens it with proper encoding
        fwrite($fp, pack('H*', 'EFBBBF'));
        fputcsv($fp, $headers, ',', '"', "\\");
        foreach ($examples as $ex) {
            fputcsv($fp, $ex, ',', '"', "\\");
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);
        return $csv;
    }

    /**
     * Generate genuine Microsoft Excel .xlsx Template with Dropdown Data Validations referencing Sheet 2 (Referensi)
     */
    public static function generateXlsxTemplate() {
        if (!class_exists('ZipArchive')) {
            return self::generateTemplateCsv();
        }

        $headers = [
            'NIK',
            'Nama Lengkap',
            'Email',
            'Role',
            'Departemen',
            'Jabatan',
            'Tanggal Masuk (YYYY-MM-DD)',
            'Kuota Cuti Tahunan',
            'Jenis Kelamin',
            'Agama',
            'Status Pernikahan',
            'No HP / WhatsApp (Contoh: 6281234567891)',
            'Alamat Tinggal',
            'Status Aktif'
        ];

        $examples = [
            [
                'OP-011',
                'Ahmad Fauzi',
                'ahmad.fauzi@nakakin.co.id',
                'operator',
                'Core',
                'Operator Produksi',
                '2024-02-01',
                '12',
                'Laki-laki',
                'Islam',
                'Belum Menikah',
                '6281234567891',
                'Dusun Sukamaju RT 01/02, Karawang Barat',
                'Aktif'
            ],
            [
                'STF-012',
                'Dewi Lestari',
                'dewi.lestari@nakakin.co.id',
                'staff',
                'Accounting',
                'Staff',
                '2023-11-15',
                '12',
                'Perempuan',
                'Islam',
                'Menikah',
                '6281298765432',
                'Perum Telukjambe Blok C-12, Karawang',
                'Aktif'
            ],
            [
                'LDR-013',
                'Rahmat Hidayat',
                'rahmat.hidayat@nakakin.co.id',
                'leader',
                'Diecasting',
                'Leader',
                '2022-05-10',
                '12',
                'Laki-laki',
                'Islam',
                'Menikah',
                '6281345678901',
                'Klari, Karawang Timur',
                'Aktif'
            ]
        ];

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_tpl_');
        $zip = new ZipArchive();
        if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            return self::generateTemplateCsv();
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' .
            '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. docProps/core.xml
        $core = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            '<dc:creator>PT. Nakakin Indonesia</dc:creator>' .
            '<cp:lastModifiedBy>PT. Nakakin Indonesia</cp:lastModifiedBy>' .
            '<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created>' .
            '<dcterms:modified xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:modified>' .
            '</cp:coreProperties>';
        $zip->addFromString('docProps/core.xml', $core);

        // 4. docProps/app.xml
        $app = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' .
            '<Application>Microsoft Excel</Application>' .
            '<DocSecurity>0</DocSecurity>' .
            '<ScaleCrop>false</ScaleCrop>' .
            '<HeadingPairs><vt:vector size="2" baseType="variant"><vt:variant><vt:lpstr>Worksheets</vt:lpstr></vt:variant><vt:variant><vt:i4>2</vt:i4></vt:variant></vt:vector></HeadingPairs>' .
            '<TitlesOfParts><vt:vector size="2" baseType="lpstr"><vt:lpstr>Data Karyawan</vt:lpstr><vt:lpstr>Referensi</vt:lpstr></vt:vector></TitlesOfParts>' .
            '<Company>PT. Nakakin Indonesia</Company>' .
            '<LinksUpToDate>false</LinksUpToDate>' .
            '<SharedDoc>false</SharedDoc>' .
            '<HyperlinksChanged>false</HyperlinksChanged>' .
            '<AppVersion>16.0300</AppVersion>' .
            '</Properties>';
        $zip->addFromString('docProps/app.xml', $app);

        // 5. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 6. xl/workbook.xml
        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<fileVersion appName="xl" lastEdited="7" lowestEdited="7" rupBuild="24816"/>' .
            '<workbookPr defaultThemeVersion="166925"/>' .
            '<bookViews>' .
            '<workbookView xWindow="0" yWindow="0" windowWidth="22260" windowHeight="12525"/>' .
            '</bookViews>' .
            '<sheets>' .
            '<sheet name="Data Karyawan" sheetId="1" r:id="rId1"/>' .
            '<sheet name="Referensi" sheetId="2" r:id="rId2"/>' .
            '</sheets>' .
            '<calcPr calcId="191029"/>' .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $wb);

        // 7. xl/styles.xml (Strict OpenXML Schema)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="2">' .
            '<font><sz val="11"/><color rgb="FF000000"/><name val="Calibri"/><family val="2"/></font>' .
            '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>' .
            '</fonts>' .
            '<fills count="3">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FF0284C7"/><bgColor indexed="64"/></patternFill></fill>' .
            '</fills>' .
            '<borders count="1">' .
            '<border><left/><right/><top/><bottom/><diagonal/></border>' .
            '</borders>' .
            '<cellStyleXfs count="1">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>' .
            '</cellStyleXfs>' .
            '<cellXfs count="2">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' .
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>' .
            '</cellXfs>' .
            '<cellStyles count="1">' .
            '<cellStyle name="Normal" xfId="0" builtinId="0"/>' .
            '</cellStyles>' .
            '<dxfs count="0"/>' .
            '<tableStyles count="0" defaultTableStyle="TableStyleMedium9" defaultPivotStyle="PivotStyleLight16"/>' .
            '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // Helper to convert column index to letter (0 -> A, 1 -> B, ...)
        $colLetter = function($idx) {
            $letter = '';
            while ($idx >= 0) {
                $letter = chr($idx % 26 + 65) . $letter;
                $idx = intdiv($idx, 26) - 1;
            }
            return $letter;
        };

        // 6. xl/worksheets/sheet1.xml (Main Data sheet with Dropdowns pointing to Sheet 2 "Referensi")
        $sheet1Xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<dimension ref="A1:N500"/>' .
            '<sheetViews>' .
            '<sheetView tabSelected="1" workbookViewId="0">' .
            '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>' .
            '</sheetView>' .
            '</sheetViews>' .
            '<sheetFormatPr defaultRowHeight="20"/>' .
            '<cols>' .
            '<col min="1" max="1" width="16" customWidth="1"/>' .
            '<col min="2" max="2" width="24" customWidth="1"/>' .
            '<col min="3" max="3" width="28" customWidth="1"/>' .
            '<col min="4" max="4" width="16" customWidth="1"/>' .
            '<col min="5" max="5" width="20" customWidth="1"/>' .
            '<col min="6" max="6" width="22" customWidth="1"/>' .
            '<col min="7" max="7" width="26" customWidth="1"/>' .
            '<col min="8" max="8" width="20" customWidth="1"/>' .
            '<col min="9" max="9" width="16" customWidth="1"/>' .
            '<col min="10" max="10" width="16" customWidth="1"/>' .
            '<col min="11" max="11" width="18" customWidth="1"/>' .
            '<col min="12" max="12" width="18" customWidth="1"/>' .
            '<col min="13" max="13" width="34" customWidth="1"/>' .
            '<col min="14" max="14" width="16" customWidth="1"/>' .
            '</cols>' .
            '<sheetData>';

        // Header Row (Row 1, styled)
        $sheet1Xml .= '<row r="1" ht="26" customHeight="1">';
        foreach ($headers as $cIdx => $hText) {
            $ref = $colLetter($cIdx) . '1';
            $sheet1Xml .= '<c r="' . $ref . '" s="1" t="inlineStr"><is><t>' . htmlspecialchars($hText, ENT_XML1, 'UTF-8') . '</t></is></c>';
        }
        $sheet1Xml .= '</row>';

        // Example Rows (Row 2, 3, 4)
        $rowNum = 2;
        foreach ($examples as $rowVals) {
            $sheet1Xml .= '<row r="' . $rowNum . '" ht="20">';
            foreach ($rowVals as $cIdx => $val) {
                $ref = $colLetter($cIdx) . $rowNum;
                $sheet1Xml .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . htmlspecialchars((string)$val, ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            $sheet1Xml .= '</row>';
            $rowNum++;
        }
        $sheet1Xml .= '</sheetData>';

        // Data Validations (Dropdowns referencing Sheet 2 Referensi)
        $sheet1Xml .= '<dataValidations count="7">' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="D2:D500">' .
            '<formula1>=Referensi!$B$2:$B$8</formula1>' .
            '</dataValidation>' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="E2:E500">' .
            '<formula1>=Referensi!$A$2:$A$16</formula1>' .
            '</dataValidation>' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="F2:F500">' .
            '<formula1>=Referensi!$C$2:$C$8</formula1>' .
            '</dataValidation>' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="I2:I500">' .
            '<formula1>=Referensi!$D$2:$D$3</formula1>' .
            '</dataValidation>' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="J2:J500">' .
            '<formula1>=Referensi!$E$2:$E$7</formula1>' .
            '</dataValidation>' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="K2:K500">' .
            '<formula1>=Referensi!$F$2:$F$5</formula1>' .
            '</dataValidation>' .
            '<dataValidation type="list" allowBlank="1" showInputMessage="1" showErrorMessage="1" sqref="N2:N500">' .
            '<formula1>=Referensi!$G$2:$G$3</formula1>' .
            '</dataValidation>' .
            '</dataValidations>';

        $sheet1Xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>';
        $sheet1Xml .= '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);

        // 7. xl/worksheets/sheet2.xml (Reference Sheet holding Department, Role, Jabatan, Gender, etc.)
        $sheet2Xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<dimension ref="A1:H20"/>' .
            '<sheetViews>' .
            '<sheetView workbookViewId="0"/>' .
            '</sheetViews>' .
            '<sheetFormatPr defaultRowHeight="20"/>' .
            '<cols>' .
            '<col min="1" max="1" width="22" customWidth="1"/>' .
            '<col min="2" max="2" width="16" customWidth="1"/>' .
            '<col min="3" max="3" width="24" customWidth="1"/>' .
            '<col min="4" max="4" width="16" customWidth="1"/>' .
            '<col min="5" max="5" width="20" customWidth="1"/>' .
            '<col min="6" max="6" width="18" customWidth="1"/>' .
            '<col min="7" max="7" width="16" customWidth="1"/>' .
            '<col min="8" max="8" width="45" customWidth="1"/>' .
            '</cols>' .
            '<sheetData>' .
            '<row r="1" ht="24">' .
            '<c r="A1" s="1" t="inlineStr"><is><t>Departemen</t></is></c>' .
            '<c r="B1" s="1" t="inlineStr"><is><t>Role</t></is></c>' .
            '<c r="C1" s="1" t="inlineStr"><is><t>Jabatan</t></is></c>' .
            '<c r="D1" s="1" t="inlineStr"><is><t>Jenis Kelamin</t></is></c>' .
            '<c r="E1" s="1" t="inlineStr"><is><t>Agama</t></is></c>' .
            '<c r="F1" s="1" t="inlineStr"><is><t>Status Pernikahan</t></is></c>' .
            '<c r="G1" s="1" t="inlineStr"><is><t>Status Karyawan</t></is></c>' .
            '<c r="H1" s="1" t="inlineStr"><is><t>Petunjuk Pengisian</t></is></c>' .
            '</row>';

        $depts = [
            'Accounting', 'Casting', 'Core', 'Diecasting', 'Engineering',
            'Fettling', 'GA', 'HRD', 'Machining', 'Marketing',
            'Maintenance', 'PPIC', 'Purchasing', 'QC', 'QC Line'
        ];
        $rolesRef = ['operator', 'staff', 'leader', 'supervisor', 'manager', 'hrd', 'admin'];
        $jabsRef = ['Operator Produksi', 'Staff', 'Leader', 'Supervisor (Spv)', 'Department Manager', 'HRD', 'Super Admin'];
        $genders = ['Laki-laki', 'Perempuan'];
        $agamas = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        $nikahs = ['Belum Menikah', 'Menikah', 'Duda', 'Janda'];
        $statuss = ['Aktif', 'Nonaktif'];
        $tips = [
            '1. Kolom NIK dan Email harus unik & belum pernah terdaftar di sistem.',
            '2. Gunakan tanda panah dropdown di sheet Data Karyawan untuk memilih Role, Departemen, dan Jabatan.',
            '3. Format Tanggal Masuk: YYYY-MM-DD (Contoh: 2024-01-15).',
            '4. Kuota cuti tahunan default adalah 12 hari.',
            '5. Password awal default akun otomatis: password123.',
            '6. Simpan file lalu upload di menu Master Data Karyawan -> Import Excel.',
            '7. Sistem akan menampilkan preview validasi sebelum data dimasukkan ke database.'
        ];

        $maxRows = max(count($depts), count($rolesRef), count($jabsRef), count($genders), count($agamas), count($nikahs), count($statuss), count($tips));
        for ($r = 0; $r < $maxRows; $r++) {
            $rNum = $r + 2;
            $sheet2Xml .= '<row r="' . $rNum . '">';
            if (isset($depts[$r])) {
                $sheet2Xml .= '<c r="A' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($depts[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($rolesRef[$r])) {
                $sheet2Xml .= '<c r="B' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($rolesRef[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($jabsRef[$r])) {
                $sheet2Xml .= '<c r="C' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($jabsRef[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($genders[$r])) {
                $sheet2Xml .= '<c r="D' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($genders[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($agamas[$r])) {
                $sheet2Xml .= '<c r="E' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($agamas[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($nikahs[$r])) {
                $sheet2Xml .= '<c r="F' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($nikahs[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($statuss[$r])) {
                $sheet2Xml .= '<c r="G' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($statuss[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            if (isset($tips[$r])) {
                $sheet2Xml .= '<c r="H' . $rNum . '" t="inlineStr"><is><t>' . htmlspecialchars($tips[$r], ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            $sheet2Xml .= '</row>';
        }

        $sheet2Xml .= '</sheetData>';
        $sheet2Xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>';
        $sheet2Xml .= '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet2.xml', $sheet2Xml);

        $zip->close();
        $content = file_get_contents($tmpFile);
        @unlink($tmpFile);
        return $content;
    }

    /**
     * Validate and normalize parsed rows against database
     */
    public static function validateRows($rawRows, $pdo) {
        $result = [
            'total_rows' => 0,
            'valid_count' => 0,
            'invalid_count' => 0,
            'valid_rows' => [],
            'invalid_rows' => []
        ];

        if (empty($rawRows)) {
            return $result;
        }

        // Fetch master departments (name & code maps)
        $deptMap = [];
        $stmtDept = $pdo->query("SELECT id, LOWER(nama_dept) as nama, LOWER(kode_dept) as kode, nama_dept FROM departemen");
        while ($d = $stmtDept->fetch(PDO::FETCH_ASSOC)) {
            $deptMap[$d['nama']] = (int)$d['id'];
            $deptMap[$d['kode']] = (int)$d['id'];
        }

        // Fetch master jabatan
        $jabatanMap = [];
        $stmtJab = $pdo->query("SELECT id, LOWER(nama_jabatan) as nama, level_hierarki, nama_jabatan FROM jabatan");
        while ($j = $stmtJab->fetch(PDO::FETCH_ASSOC)) {
            $jabatanMap[$j['nama']] = [
                'id' => (int)$j['id'],
                'level' => (int)$j['level_hierarki'],
                'nama' => $j['nama_jabatan']
            ];
        }

        // Fetch existing NIK and Email
        $existingNiks = [];
        $existingEmails = [];
        $stmtEmp = $pdo->query("SELECT LOWER(nik) as nik, LOWER(email) as email FROM karyawan");
        while ($e = $stmtEmp->fetch(PDO::FETCH_ASSOC)) {
            $existingNiks[$e['nik']] = true;
            $existingEmails[$e['email']] = true;
        }

        // Determine header row
        $headerRowIdx = 0;
        $headers = array_map('strtolower', $rawRows[0]);

        // Check if first row is header
        $hasHeader = false;
        foreach ($headers as $h) {
            if (strpos($h, 'nik') !== false || strpos($h, 'nama') !== false || strpos($h, 'email') !== false) {
                $hasHeader = true;
                break;
            }
        }

        $startIdx = $hasHeader ? 1 : 0;
        $seenNiksInFile = [];
        $seenEmailsInFile = [];

        for ($i = $startIdx; $i < count($rawRows); $i++) {
            $rowNumber = $i + 1;
            $row = $rawRows[$i];

            // Skip empty rows
            $nonEmpty = array_filter($row, function($v) { return trim($v) !== ''; });
            if (empty($nonEmpty)) continue;

            $result['total_rows']++;

            $nik = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');
            $email = trim($row[2] ?? '');
            $role = strtolower(trim($row[3] ?? 'operator'));
            $deptInput = strtolower(trim($row[4] ?? ''));
            $jabatanInput = strtolower(trim($row[5] ?? ''));
            $tanggalMasuk = trim($row[6] ?? date('Y-m-d'));
            $kuotaCuti = isset($row[7]) && is_numeric(trim($row[7])) ? (int)$row[7] : 12;
            $jenisKelamin = trim($row[8] ?? 'Laki-laki');
            $agama = trim($row[9] ?? 'Islam');
            $statusPernikahan = trim($row[10] ?? 'Belum Menikah');
            $noHp = trim($row[11] ?? '');
            $alamat = trim($row[12] ?? '');
            $statusAktif = trim($row[13] ?? 'Aktif');

            $errors = [];

            // 1. Validate NIK
            if (empty($nik)) {
                $errors[] = "NIK tidak boleh kosong";
            } else {
                $nikLower = strtolower($nik);
                if (isset($existingNiks[$nikLower])) {
                    $errors[] = "NIK '$nik' sudah terdaftar di sistem";
                } elseif (isset($seenNiksInFile[$nikLower])) {
                    $errors[] = "NIK '$nik' duplikat di dalam file ini (baris {$seenNiksInFile[$nikLower]})";
                } else {
                    $seenNiksInFile[$nikLower] = $rowNumber;
                }
            }

            // 2. Validate Nama
            if (empty($nama)) {
                $errors[] = "Nama lengkap tidak boleh kosong";
            }

            // 3. Validate Email
            if (empty($email)) {
                $errors[] = "Email tidak boleh kosong";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format email '$email' tidak valid";
            } else {
                $emailLower = strtolower($email);
                if (isset($existingEmails[$emailLower])) {
                    $errors[] = "Email '$email' sudah digunakan karyawan lain";
                } elseif (isset($seenEmailsInFile[$emailLower])) {
                    $errors[] = "Email '$email' duplikat di dalam file ini";
                } else {
                    $seenEmailsInFile[$emailLower] = $rowNumber;
                }
            }

            // 4. Validate Role
            $validRoles = ['operator', 'staff', 'leader', 'supervisor', 'manager', 'hrd', 'admin', 'superadmin'];
            if (!in_array($role, $validRoles)) {
                $role = 'operator'; // default fallback
            }

            // 5. Validate Departemen
            $deptId = null;
            if (empty($deptInput)) {
                $errors[] = "Departemen wajib diisi";
            } elseif (isset($deptMap[$deptInput])) {
                $deptId = $deptMap[$deptInput];
            } else {
                // Try fuzzy match
                foreach ($deptMap as $nameKey => $idVal) {
                    if (strpos($nameKey, $deptInput) !== false || strpos($deptInput, $nameKey) !== false) {
                        $deptId = $idVal;
                        break;
                    }
                }
                if (!$deptId) {
                    $errors[] = "Departemen '$deptInput' tidak ditemukan di 15 master departemen resmi";
                }
            }

            // 6. Validate Jabatan
            $jabatanId = null;
            if (empty($jabatanInput)) {
                // Default based on role
                if ($role === 'manager') $jabatanInput = 'department manager';
                elseif ($role === 'supervisor') $jabatanInput = 'supervisor (spv)';
                elseif ($role === 'leader') $jabatanInput = 'leader';
                elseif ($role === 'staff') $jabatanInput = 'staff';
                elseif ($role === 'hrd') $jabatanInput = 'hrd';
                else $jabatanInput = 'operator produksi';
            }

            if (isset($jabatanMap[$jabatanInput])) {
                $jabatanId = $jabatanMap[$jabatanInput]['id'];
            } else {
                foreach ($jabatanMap as $nameKey => $jData) {
                    if (strpos($nameKey, $jabatanInput) !== false || strpos($jabatanInput, $nameKey) !== false) {
                        $jabatanId = $jData['id'];
                        break;
                    }
                }
                if (!$jabatanId) {
                    // Fallback to Operator Produksi
                    $jabatanId = 1;
                }
            }

            // 7. Validate Date
            if (!empty($tanggalMasuk)) {
                $parsedDate = date_parse($tanggalMasuk);
                if ($parsedDate['error_count'] > 0 || !checkdate($parsedDate['month'], $parsedDate['day'], $parsedDate['year'])) {
                    $tanggalMasuk = date('Y-m-d'); // fallback
                } else {
                    $tanggalMasuk = sprintf('%04d-%02d-%02d', $parsedDate['year'], $parsedDate['month'], $parsedDate['day']);
                }
            } else {
                $tanggalMasuk = date('Y-m-d');
            }

            // Normalize choices
            $jenisKelamin = (stripos($jenisKelamin, 'perempuan') !== false || stripos($jenisKelamin, 'wanita') !== false || stripos($jenisKelamin, 'p') === 0) ? 'Perempuan' : 'Laki-laki';
            $statusPernikahan = in_array($statusPernikahan, ['Belum Menikah', 'Menikah', 'Duda', 'Janda']) ? $statusPernikahan : 'Belum Menikah';
            $statusAktif = (stripos($statusAktif, 'non') !== false || stripos($statusAktif, 'tidak') !== false) ? 'Nonaktif' : 'Aktif';

            // Normalize No HP (mendukung format 08xxx, 628xxx, +628xxx, maupun 8xxx)
            $cleanDigits = preg_replace('/[^0-9]/', '', $noHp);
            if (!empty($cleanDigits)) {
                if (strpos($cleanDigits, '62') === 0) {
                    $noHp = '0' . substr($cleanDigits, 2);
                } elseif (strpos($cleanDigits, '8') === 0) {
                    $noHp = '0' . $cleanDigits;
                } else {
                    $noHp = $cleanDigits;
                }
            }

            $rowData = [
                'row_number' => $rowNumber,
                'nik' => $nik,
                'nama_lengkap' => $nama,
                'email' => $email,
                'role' => $role,
                'departemen_id' => $deptId,
                'departemen_input' => $deptInput,
                'jabatan_id' => $jabatanId,
                'jabatan_input' => $jabatanInput,
                'tanggal_masuk' => $tanggalMasuk,
                'kuota_cuti' => $kuotaCuti,
                'jenis_kelamin' => $jenisKelamin,
                'agama' => $agama,
                'status_pernikahan' => $statusPernikahan,
                'no_hp' => $noHp,
                'alamat' => $alamat,
                'status_aktif' => $statusAktif,
                'errors' => $errors,
                'is_valid' => empty($errors)
            ];

            if (empty($errors)) {
                $result['valid_count']++;
                $result['valid_rows'][] = $rowData;
            } else {
                $result['invalid_count']++;
                $result['invalid_rows'][] = $rowData;
            }
        }

        return $result;
    }
}
