<?php
/**
 * Escritor XLSX liviano sin dependencias externas.
 * Usa ZipArchive (incluido en PHP core) y genera Open XML válido.
 * Soporta múltiples hojas, fila de cabecera con fondo naranja+negrita,
 * strings (vía tabla compartida) y números.
 */
class XlsxWriter
{
    private array $sheets            = [];
    private array $sharedStrings     = [];
    private array $sharedStringsMap  = [];

    /**
     * Agrega una hoja al libro.
     *
     * @param string $name      Nombre de la hoja (se trunca a 31 chars)
     * @param array  $rows      Filas: array de arrays; cada celda es string|int|float|null
     * @param int    $headerRow Número de fila (1-based) que recibe estilo de cabecera
     */
    public function addSheet(string $name, array $rows, int $headerRow = 1): void
    {
        $name = substr(preg_replace('/[\/\\\\?\*\[\]:]+/', '', $name), 0, 31) ?: 'Hoja';

        // Pre-registrar strings en la tabla compartida
        foreach ($rows as $row) {
            foreach ($row as $cell) {
                if (is_string($cell) && $cell !== '') {
                    $this->getStringIndex($cell);
                }
            }
        }

        $this->sheets[] = ['name' => $name, 'rows' => $rows, 'headerRow' => $headerRow];
    }

    /** Envía el archivo al navegador y termina la ejecución. */
    public function download(string $filename): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml',        $this->buildContentTypes());
        $zip->addFromString('_rels/.rels',                $this->buildRels());
        $zip->addFromString('xl/workbook.xml',            $this->buildWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRels());
        $zip->addFromString('xl/styles.xml',              $this->buildStyles());
        $zip->addFromString('xl/sharedStrings.xml',       $this->buildSharedStrings());

        foreach ($this->sheets as $i => $sheetDef) {
            $zip->addFromString(
                'xl/worksheets/sheet' . ($i + 1) . '.xml',
                $this->buildWorksheet($sheetDef)
            );
        }

        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . filesize($tmp));
        header('Cache-Control: max-age=0');

        readfile($tmp);
        unlink($tmp);
        exit;
    }

    // ── Privados ────────────────────────────────────────────────────────────────

    private function getStringIndex(string $str): int
    {
        if (!isset($this->sharedStringsMap[$str])) {
            $this->sharedStringsMap[$str] = count($this->sharedStrings);
            $this->sharedStrings[]        = $str;
        }
        return $this->sharedStringsMap[$str];
    }

    private static function colLetter(int $col): string
    {
        $letter = '';
        while ($col > 0) {
            $col--;
            $letter = chr(65 + ($col % 26)) . $letter;
            $col    = (int)($col / 26);
        }
        return $letter;
    }

    private function buildContentTypes(): string
    {
        $overrides = '';
        foreach ($this->sheets as $i => $_) {
            $n = $i + 1;
            $overrides .= "<Override PartName=\"/xl/worksheets/sheet{$n}.xml\""
                . " ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml"  ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml"'
            .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml"'
            .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml"'
            .   ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . $overrides
            . '</Types>';
    }

    private function buildRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            .   ' Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function buildWorkbook(): string
    {
        $sheets = '';
        foreach ($this->sheets as $i => $s) {
            $n    = $i + 1;
            $name = htmlspecialchars($s['name'], ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $sheets .= "<sheet name=\"{$name}\" sheetId=\"{$n}\" r:id=\"rId{$n}\"/>";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            .   ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . $sheets . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRels(): string
    {
        $rels = '';
        foreach ($this->sheets as $i => $_) {
            $n = $i + 1;
            $rels .= "<Relationship Id=\"rId{$n}\""
                .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                .   " Target=\"worksheets/sheet{$n}.xml\"/>";
        }
        $nSheets  = count($this->sheets);
        $rels .= "<Relationship Id=\"rId" . ($nSheets + 1) . "\""
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
            .   ' Target="sharedStrings.xml"/>';
        $rels .= "<Relationship Id=\"rId" . ($nSheets + 2) . "\""
            .   ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            .   ' Target="styles.xml"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    private function buildStyles(): string
    {
        // Estilo 0: normal  |  Estilo 1: fondo naranja #FFC000 + negrita
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><sz val="11"/><name val="Calibri"/><b/></font>'
            . '</fonts>'
            . '<fills count="3">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid">'
            .     '<fgColor rgb="FFFFC000"/><bgColor indexed="64"/>'
            .   '</patternFill></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '</cellXfs>'
            . '</styleSheet>';
    }

    private function buildSharedStrings(): string
    {
        $count = count($this->sharedStrings);
        $items = '';
        foreach ($this->sharedStrings as $str) {
            $escaped = htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $items  .= "<si><t xml:space=\"preserve\">{$escaped}</t></si>";
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\""
            . " count=\"{$count}\" uniqueCount=\"{$count}\">"
            . $items
            . '</sst>';
    }

    private function buildWorksheet(array $sheetDef): string
    {
        $rows      = $sheetDef['rows'];
        $headerRow = $sheetDef['headerRow'];

        // Calcular número máximo de columnas
        $maxCols = 0;
        foreach ($rows as $row) {
            $maxCols = max($maxCols, count($row));
        }

        // Definición de anchos de columna
        $colDefs = '';
        if ($maxCols > 0) {
            $colDefs  = '<cols>';
            $colDefs .= '<col min="1" max="1" width="22" customWidth="1"/>';
            if ($maxCols > 1) {
                $colDefs .= '<col min="2" max="' . $maxCols . '" width="10" customWidth="1"/>';
            }
            $colDefs .= '</cols>';
        }

        $sheetData = '<sheetData>';
        foreach ($rows as $rowIdx => $row) {
            $rowNum   = $rowIdx + 1;
            $isHeader = ($rowNum === $headerRow);

            $sheetData .= "<row r=\"{$rowNum}\">";
            foreach ($row as $colIdx => $cell) {
                $colNum  = $colIdx + 1;
                $cellRef = self::colLetter($colNum) . $rowNum;
                $sAttr   = $isHeader ? ' s="1"' : '';

                if ($cell === null || $cell === '') {
                    if ($isHeader) {
                        $sheetData .= "<c r=\"{$cellRef}\" s=\"1\"/>";
                    }
                } elseif (is_int($cell) || is_float($cell)) {
                    $sheetData .= "<c r=\"{$cellRef}\"{$sAttr}><v>{$cell}</v></c>";
                } else {
                    $idx        = $this->getStringIndex((string)$cell);
                    $sheetData .= "<c r=\"{$cellRef}\" t=\"s\"{$sAttr}><v>{$idx}</v></c>";
                }
            }
            $sheetData .= '</row>';
        }
        $sheetData .= '</sheetData>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $colDefs
            . $sheetData
            . '</worksheet>';
    }
}
