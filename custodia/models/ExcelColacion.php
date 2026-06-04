<?php
declare(strict_types=1);

class ExcelColacion
{
    /**
     * Carga un archivo subido (CSV o XLSX), retorna:
     * [
     *   'headers' => string[],
     *   'rows'    => string[][]
     * ]
     * - Convierte a UTF-8.
     * - Quita filas totalmente vacías.
     */
    public static function loadFromUpload(array $file): array
    {
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se recibió archivo o hubo error de subida.');
        }

        $tmp = $file['tmp_name'];
        $name = (string)($file['name'] ?? 'archivo');

        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($ext === 'csv') {
            return self::parseCSV($tmp);
        }

        if ($ext === 'xlsx') {
            if (class_exists('SimpleXLSX')) {
                return self::parseXLSX($tmp);
            }
            // Sin lector XLSX: sugerir CSV o instalar lector
            throw new RuntimeException('Para XLSX necesitas incluir SimpleXLSX. Alternativa: sube CSV.');
        }

        // Intento heurístico: si el contenido parece CSV
        if (self::looksLikeCSV($tmp)) {
            return self::parseCSV($tmp);
        }

        throw new RuntimeException('Formato no soportado. Usa CSV o instala un lector XLSX.');
    }

    /** CSV parser robusto (delimitador auto: ; , \t |) */
    private static function parseCSV(string $path): array
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('No se pudo leer el CSV.');
        }

        // Normalizar saltos de línea y encoding → UTF-8
        $contents = str_replace(["\r\n", "\r"], "\n", $contents);
        $enc = mb_detect_encoding($contents, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8';
        if ($enc !== 'UTF-8') {
            $contents = mb_convert_encoding($contents, 'UTF-8', $enc);
        }

        // Detectar delimitador
        $firstLine = strtok($contents, "\n");
        $delim = self::detectDelimiter($firstLine);

        // Volver a recorrer completo con puntero temporal
        $fh = fopen('php://memory', 'r+');
        fwrite($fh, $contents);
        rewind($fh);

        $headers = [];
        $rows = [];
        $lineN = 0;

        while (($cols = fgetcsv($fh, 0, $delim)) !== false) {
            // Trim básico
            $cols = array_map(function ($v) {
                $v = (string)$v;
                // remover BOM residual
                if (strpos($v, "\xEF\xBB\xBF") === 0) {
                    $v = substr($v, 3);
                }

                return trim($v);
            }, $cols);

            // Filas totalmente vacías → saltar
            $allEmpty = true;
            foreach ($cols as $c) {
                if ($c !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            if ($lineN === 0) {
                $headers = self::normalizeHeaders($cols);
            } else {
                // Igualar largo a cantidad de headers
                if (count($cols) < count($headers)) {
                    $cols = array_pad($cols, count($headers), '');
                } elseif (count($cols) > count($headers)) {
                    $cols = array_slice($cols, 0, count($headers));
                }
                $rows[] = $cols;
            }
            $lineN++;
        }

        fclose($fh);

        if (! $headers) {
            throw new RuntimeException('No se encontraron cabeceras en el CSV.');
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** XLSX parser usando SimpleXLSX si está disponible. */
    private static function parseXLSX(string $path): array
    {
        // @phpstan-ignore-next-line
        $xlsx = \SimpleXLSX::parse($path);
        if (! $xlsx) {
            // @phpstan-ignore-next-line
            throw new RuntimeException('No se pudo leer XLSX: '.\SimpleXLSX::parseError());
        }
        $sheet = $xlsx->rows(); // primera hoja
        if (! $sheet || ! isset($sheet[0])) {
            throw new RuntimeException('XLSX vacío o sin cabeceras.');
        }

        $headers = self::normalizeHeaders($sheet[0]);
        $rows = [];

        for ($i = 1; $i < count($sheet); $i++) {
            $cols = array_map(function ($v) {
                $v = (string)$v;

                return trim($v);
            }, $sheet[$i]);

            // Igualar tamaño
            if (count($cols) < count($headers)) {
                $cols = array_pad($cols, count($headers), '');
            } elseif (count($cols) > count($headers)) {
                $cols = array_slice($cols, 0, count($headers));
            }

            // Saltar filas totalmente vacías
            $allEmpty = true;
            foreach ($cols as $c) {
                if ($c !== '') {
                    $allEmpty = false;
                    break;
                }
            }
            if ($allEmpty) {
                continue;
            }

            $rows[] = $cols;
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /** Detección simple de delimitador a partir de la primera línea. */
    private static function detectDelimiter(string $line): string
    {
        $candidates = [',', ';', "\t", '|'];
        $bestDelim = ',';
        $bestCount = 0;

        foreach ($candidates as $d) {
            $cnt = substr_count($line, $d);
            if ($cnt > $bestCount) {
                $bestCount = $cnt;
                $bestDelim = $d;
            }
        }

        return $bestDelim;
    }

    /** Heurística básica: ¿parece CSV (tiene ; , | o \t)? */
    private static function looksLikeCSV(string $path): bool
    {
        $sample = file_get_contents($path, false, null, 0, 4096);
        if ($sample === false) {
            return false;
        }

        return (strpos($sample, ',') !== false) || (strpos($sample, ';') !== false) || (strpos($sample, "\t") !== false) || (strpos($sample, '|') !== false);
    }

    /** Normaliza cabeceras a minúsculas, sin acentos, sin espacios -> underscores. */
    private static function normalizeHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $h) {
            $h = (string)$h;
            $h = self::toAscii($h);
            $h = strtolower($h);
            $h = preg_replace('/[^a-z0-9]+/i', '_', $h) ?: '';
            $h = trim($h, '_');
            if ($h === '') {
                $h = 'col_'.(count($out) + 1);
            }
            $out[] = $h;
        }

        return $out;
    }

    /** Convierte a ASCII básico (quita tildes). */
    private static function toAscii(string $s): string
    {
        $enc = mb_detect_encoding($s, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true) ?: 'UTF-8';
        if ($enc !== 'UTF-8') {
            $s = mb_convert_encoding($s, 'UTF-8', $enc);
        }
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);

        return $s ?: '';
    }

    /**
     * Aplica un mapeo de columnas a la forma estándar:
     * $map = ['rut' => 'columna_rut', 'nombre' => 'columna_nombre', 'habitacion' => 'columna_hab', 'id' => 'columna_id' (opcional)]
     * Retorna un array de filas: [['rut'=>..., 'nombre'=>..., 'habitacion'=>..., 'id'=>...], ...]
     */
    public static function mapRows(array $headers, array $rows, array $map): array
    {
        // Hash header->index
        $pos = [];
        foreach ($headers as $i => $h) {
            $pos[$h] = $i;
        }

        $out = [];
        foreach ($rows as $r) {
            $item = [
                'rut' => self::valRut(self::getCol($r, $pos, $map['rut'] ?? null)),
                'nombre' => self::valNombre(self::getCol($r, $pos, $map['nombre'] ?? null)),
                'habitacion' => self::valHabitacion(self::getCol($r, $pos, $map['habitacion'] ?? null)),
                'id' => self::valId(self::getCol($r, $pos, $map['id'] ?? null)),
            ];

            // Filtra filas completamente vacías (al menos rut o nombre)
            if ($item['rut'] !== null || $item['nombre'] !== null) {
                $out[] = $item;
            }
        }

        return $out;
    }

    private static function getCol(array $row, array $pos, ?string $colName): ?string
    {
        if ($colName === null) {
            return null;
        }
        if (! isset($pos[$colName])) {
            return null;
        }
        $v = $row[$pos[$colName]] ?? '';
        $v = is_string($v) ? trim($v) : (string)$v;

        return ($v === '') ? null : $v;
    }

    /** Valida/sanea RUT libre: permite dígitos/letras y separadores básicos, retorna string o null. */
    private static function valRut(?string $rut): ?string
    {
        if ($rut === null) {
            return null;
        }
        $rut = strtoupper($rut);
        // Permite dígitos/letras y - . /
        $rut = preg_replace('/[^0-9A-Z\-\.\/]/', '', $rut);
        $rut = trim($rut);

        return $rut !== '' ? $rut : null;
    }

    private static function valNombre(?string $nombre): ?string
    {
        if ($nombre === null) {
            return null;
        }
        $nombre = trim(preg_replace('/\s+/', ' ', $nombre));

        return $nombre !== '' ? $nombre : null;
    }

    private static function valHabitacion(?string $hab): ?string
    {
        if ($hab === null) {
            return null;
        }
        $hab = trim((string)$hab);

        return $hab !== '' ? $hab : null;
    }

    private static function valId(?string $id): ?string
    {
        if ($id === null) {
            return null;
        }
        $id = trim((string)$id);

        return $id !== '' ? $id : null;
    }
}

