<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
/**
 * Resumen de la página:
 * - GET  : Lee la ruta del archivo subida en el paso anterior (SESSION['excel_file']),
 *          detecta si es CSV/XLSX, parsea, muestra vista previa (primeras 50 filas)
 *          y permite mapear columnas: id, rut, nombre, habitacion (opcional).
 * - POST : (action=confirm) Abre conexión MySQLi, inserta cabecera en excel_upload
 *          e inserta cada fila en excel_upload_item con los mapeos elegidos.
 * - Diseño: navbar simple + tabla preview.
 * - Debug opcional: añade ?debug=1 en POST para probar SELECT 1 (diagnóstico).
 */

// ---------------------------------------------------------------------
// 0) Arranque básico y helpers
// ---------------------------------------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si tienes Composer/autoload, lo intentamos (para PhpSpreadsheet si suben XLSX)
@require_once __DIR__.'/../../vendor/autoload.php';

// No abrimos conec6.php aquí (GET); se carga en POST cuando se graba.
require_once __DIR__.'/../../connections/config.php'; // url(), h(), BASE_URL (si la usas)

// Helpers mínimos si no vinieran de config.php:
if (! function_exists('h')) {
    function h(?string $v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}
if (! function_exists('url')) {
    // Construye URL relativa a BASE_URL (fallback). Ajusta BASE_URL si difiere.
    define('BASE_URL', defined('BASE_URL') ? BASE_URL : '/custodia');
    function url(string $path = '/'): string
    {
        if ($path === '' || $path[0] !== '/') {
            $path = '/'.$path;
        }

        return rtrim(BASE_URL, '/').$path;
    }
}

// Archivo de log de esta vista (para parse/insert)
$logFile = __DIR__.'/../../logs/excel_preview.log';

// ---------------------------------------------------------------------
// 1) Cargar la ruta del archivo desde sesión (puesto por excel_import_form.php)
// ---------------------------------------------------------------------
$errors = [];
$rows = [];   // matriz con datos crudos (sin encabezado si se detecta)
$headers = [];   // encabezados detectados o generados
$file = $_SESSION['excel_file'] ?? '';

if ($file === '' || ! file_exists($file)) {
    $errors[] = 'No hay archivo cargado o la ruta es inválida. Vuelve a Importar.';
}

// ---------------------------------------------------------------------
// 2) Parseo del archivo SOLO si existe (GET y también POST para validar)
//     - CSV/TXT: autodetecta delimitador ; o ,
//     - XLSX/XLS: requiere PhpSpreadsheet (si no está, pide subir CSV)
// ---------------------------------------------------------------------
if (! $errors) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    try {
        if (in_array($ext, ['csv', 'txt'], true)) {
            $fp = @fopen($file, 'r');
            if (! $fp) {
                $errors[] = 'No se pudo abrir el CSV.';
            } else {
                // Detecta delimitador de la 1ª línea
                $firstLine = fgets($fp);
                if ($firstLine === false) {
                    $errors[] = 'El archivo CSV está vacío.';
                } else {
                    $delim = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
                    rewind($fp);

                    // Quita BOM si existe
                    $bom = fread($fp, 3);
                    if ($bom !== "\xEF\xBB\xBF") {
                        rewind($fp);
                    }

                    // Lee todas las filas
                    while (($data = fgetcsv($fp, 0, $delim)) !== false) {
                        $rows[] = array_map(static fn($v) => trim((string)$v), $data);
                    }
                    fclose($fp);

                    if ($rows) {
                        // Heurística: si la 1ª fila parece encabezado (mucho texto)
                        $first = $rows[0];
                        $textCells = 0;
                        foreach ($first as $c) {
                            if (! is_numeric($c)) {
                                $textCells++;
                            }
                        }
                        if ($textCells >= max(1, (int)floor(count($first) / 2))) {
                            $headers = array_map('trim', $first);
                            $rows = array_slice($rows, 1);
                        } else {
                            // Sin encabezados: generamos col_1..n
                            $headers = [];
                            for ($i = 0; $i < count($first); $i++) {
                                $headers[] = 'col_'.($i + 1);
                            }
                        }
                    } else {
                        $errors[] = 'No se pudieron leer filas del CSV.';
                    }
                }
            }
        } elseif (in_array($ext, ['xlsx', 'xls'], true)) {
            if (! class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                $errors[] = 'PhpSpreadsheet no está disponible en el servidor. Sube CSV o instala la librería.';
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
                $spreadsheet = $reader->load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $raw = $sheet->toArray(null, true, true, true); // keys A,B,C...

                // Normaliza a índices 0..n
                $tmp = [];
                foreach ($raw as $r) {
                    $tmp[] = array_values($r);
                }

                if ($tmp) {
                    $first = $tmp[0];
                    $textCells = 0;
                    foreach ($first as $c) {
                        if (! is_numeric($c)) {
                            $textCells++;
                        }
                    }
                    if ($textCells >= max(1, (int)floor(count($first) / 2))) {
                        $headers = array_map('trim', $first);
                        $rows = array_slice($tmp, 1);
                    } else {
                        $headers = [];
                        for ($i = 0; $i < count($first); $i++) {
                            $headers[] = 'col_'.($i + 1);
                        }
                        $rows = $tmp;
                    }
                } else {
                    $errors[] = 'La hoja XLSX está vacía.';
                }
            }
        } else {
            $errors[] = 'Extensión no soportada. Usa CSV o XLSX.';
        }
    } catch (\Throwable $e) {
        $errors[] = 'Error al parsear archivo: '.$e->getMessage();
        @error_log('[PARSE ERR] '.$e->getMessage().PHP_EOL, 3, $logFile);
    }
}

// ---------------------------------------------------------------------
// 3) Heurística de mapeos por nombre de columna (para precargar selects)
//     - Busca patrones comunes en encabezados: id, rut, nombre, habitación
// ---------------------------------------------------------------------
$mapGuess = ['id' => '', 'rut' => '', 'nombre' => '', 'habitacion' => ''];
if ($headers) {
    foreach ($headers as $i => $hName) {
        $hn = mb_strtolower($hName, 'UTF-8');
        if ($mapGuess['id'] === '' && preg_match('/\b(id|folio|codigo)\b/u', $hn)) {
            $mapGuess['id'] = (string)$i;
        }
        if ($mapGuess['rut'] === '' && preg_match('/\b(rut|dni|doc)\b/u', $hn)) {
            $mapGuess['rut'] = (string)$i;
        }
        if ($mapGuess['nombre'] === '' && preg_match('/\b(nombre|name)\b/u', $hn)) {
            $mapGuess['nombre'] = (string)$i;
        }
        if ($mapGuess['habitacion'] === '' && preg_match('/\b(habit|hab|room)\b/u', $hn)) {
            $mapGuess['habitacion'] = (string)$i;
        }
    }
}

// ---------------------------------------------------------------------
// 4) Helper de conexión: devuelve instancia mysqli sin ambigüedad
//     - Acepta $db / $mysqli / $conn
//     - Requiere conec6.php si aún no está cargado
// ---------------------------------------------------------------------
if (! function_exists('db_or_fail')) {
    function db_or_fail(): mysqli
    {
        if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof mysqli) {
            return $GLOBALS['db'];
        }
        if (isset($GLOBALS['mysqli']) && $GLOBALS['mysqli'] instanceof mysqli) {
            return $GLOBALS['db'] = $GLOBALS['mysqli'];
        }
        if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
            return $GLOBALS['db'] = $GLOBALS['conn'];
        }
        $path = __DIR__.'/../../connections/conec6.php';
        if (is_file($path)) {
            require_once $path;
            if (isset($GLOBALS['db']) && $GLOBALS['db'] instanceof mysqli) {
                return $GLOBALS['db'];
            }
            if (isset($GLOBALS['mysqli']) && $GLOBALS['mysqli'] instanceof mysqli) {
                return $GLOBALS['db'] = $GLOBALS['mysqli'];
            }
            if (isset($GLOBALS['conn']) && $GLOBALS['conn'] instanceof mysqli) {
                return $GLOBALS['db'] = $GLOBALS['conn'];
            }
        }
        throw new RuntimeException('DB no disponible tras incluir connections/conec6.php');
    }
}

// ---------------------------------------------------------------------
// 5) Si es POST (action=confirm): validar mapeos y guardar en BD
// ---------------------------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'confirm') {
    // 5.1) Valida mapeos obligatorios
    $m_id = isset($_POST['map_id']) ? (int)$_POST['map_id'] : -1;
    $m_rut = isset($_POST['map_rut']) ? (int)$_POST['map_rut'] : -1;
    $m_nom = isset($_POST['map_nombre']) ? (int)$_POST['map_nombre'] : -1;
    $m_hab = isset($_POST['map_habitacion']) ? (int)$_POST['map_habitacion'] : -1; // opcional

    if ($m_id < 0 || $m_rut < 0 || $m_nom < 0) {
        http_response_code(400);
        echo "<!doctype html><meta charset='utf-8'>
              <body style='font-family:system-ui,Arial;padding:16px;color:#900'>
              <h3>Faltan mapeos obligatorios</h3>
              <div>Debes mapear: ID, RUT y Nombre.</div>
              <div style='margin-top:10px'><a href='".h(url('/colaciones/excel/preview'))."'>Volver</a></div>
              </body>";
        exit;
    }

    // 5.2) Abrir BD (solo en POST). Smoke test + charset.
    try {
        require_once __DIR__.'/../../connections/conec6.php'; // expone $db / $mysqli / $conn
        $db = db_or_fail();
        @$db->set_charset('utf8mb4');

        // Debug opcional del POST: /preview?debug=1
        if (isset($_GET['debug']) && $_GET['debug'] === '1') {
            header('Content-Type: text/plain; charset=utf-8');
            $res = $db->query('SELECT 1 AS one');
            echo 'SELECT 1: '.($res ? 'OK' : 'FAIL').PHP_EOL;
            if ($res) {
                $row = $res->fetch_assoc();
                echo 'one='.($row['one'] ?? 'null').PHP_EOL;
            }
            exit;
        }

        // 5.3) Insertar cabecera en excel_upload
        $original_name = basename((string)($_SESSION['excel_original_name'] ?? $file));
        $stored_path = $file;
        $total_rows = count($rows);

        $db->begin_transaction();

        $stmtUp = $db->prepare('INSERT INTO `excel_upload` (`original_filename`, `stored_path`, `total_rows`) VALUES (?, ?, ?)');
        if (! $stmtUp) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$db->error);
        }
        $stmtUp->bind_param('ssi', $original_name, $stored_path, $total_rows);
        if (! $stmtUp->execute()) {
            throw new RuntimeException('INSERT excel_upload: '.$stmtUp->error);
        }
        $upload_id = (int)$db->insert_id;
        $stmtUp->close();

        // 5.4) Insertar filas en excel_upload_item
        $stmtIt = $db->prepare(
                'INSERT INTO `excel_upload_item` (`upload_id`, `fila_nro`, `id_archivo`, `rut`, `nombre`, `habitacion`)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        if (! $stmtIt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$db->error);
        }

        $fila_nro = 0;
        foreach ($rows as $r) {
            $fila_nro++;
            $id_archivo = trim((string)($r[$m_id] ?? ''));
            $rut = trim((string)($r[$m_rut] ?? ''));
            $nombre = trim((string)($r[$m_nom] ?? ''));
            $habitacion = ($m_hab >= 0 && isset($r[$m_hab])) ? trim((string)$r[$m_hab]) : null;

            // Evita filas totalmente vacías
            if ($id_archivo === '' && $rut === '' && $nombre === '' && ($habitacion ?? '') === '') {
                continue;
            }

            $stmtIt->bind_param('iissss', $upload_id, $fila_nro, $id_archivo, $rut, $nombre, $habitacion);
            if (! $stmtIt->execute()) {
                throw new RuntimeException('INSERT item fila '.$fila_nro.': '.$stmtIt->error);
            }
        }
        $stmtIt->close();

        $db->commit();

        // 5.5) Respuesta de éxito con CTA
        echo "<!doctype html><meta charset='utf-8'><body style='font-family:system-ui,Arial;padding:16px'>";
        echo '<h3>Datos guardados correctamente</h3>';
        echo '<div>Upload ID: <strong>'.(int)$upload_id.'</strong></div>';
        echo '<div>Archivo: '.h($original_name).'</div>';
        echo '<div>Filas insertadas: <strong>'.(int)$total_rows.'</strong></div>';
        echo "<div style='margin-top:12px'>";
        //echo "  <a href='".h(
        //                url('/colaciones/lotes/crear')
        //        ).'?from_upload_id='.$upload_id."' "."style='display:inline-block;padding:10px 14px;border:1px solid #0b2950;border-radius:8px;background:#0b2950;color:#fff;text-decoration:none'>".'Crear lote desde esta carga</a> ';

        echo "<a href='".h(url('/colaciones/lotes/crear-desde-excel')).'?from_upload_id='.$upload_id."' class='btn btn-primary'>Crear lote desde esta carga</a>";

        echo "  <a href='".h(
                        url('/colaciones/excel/import')
                )."' "."style='display:inline-block;padding:10px 14px;border:1px solid #475569;border-radius:8px;background:#475569;color:#fff;text-decoration:none;margin-left:8px'>".'Subir otro archivo</a>';
        echo '</div></body>';
        exit;
    } catch (\Throwable $e) {
        @error_log('[CONFIRM ERR] '.$e->getMessage().PHP_EOL, 3, $logFile);
        http_response_code(500);
        echo "<!doctype html><meta charset='utf-8'><body style='font-family:system-ui,Arial;padding:16px;color:#900'>";
        echo '<h3>Error insertando los datos</h3>';
        echo '<div>'.h($e->getMessage()).'</div>';
        echo "<div style='margin-top:10px'><a href='".h(url('/colaciones/excel/preview'))."'>Volver</a></div>";
        echo '</body>';
        exit;
    }
}

// ---------------------------------------------------------------------
// 6) Render de la VISTA (GET): navbar + mapeo + preview
// ---------------------------------------------------------------------
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Vista Previa — Excel/CSV</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #0b2950;
            color: #f1f5f9
        }

        .navbar .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 700
        }

        .navbar img {
            height: 28px
        }

        .navbar a {
            color: #f1f5f9;
            text-decoration: none;
            margin: 0 8px
        }

        .navbar a:hover {
            text-decoration: underline
        }

        .container {
            max-width: 1100px;
            margin: 16px auto;
            padding: 0 12px
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 13px
        }

        th {
            background: #f8fafc;
            text-align: left
        }

        .errors {
            background: #ffecec;
            border: 1px solid #f5c2c7;
            color: #900;
            padding: 10px;
            border-radius: 8px;
            margin: 12px 0
        }

        .muted {
            color: #64748b;
            font-size: 13px
        }

        select, button {
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 8px
        }

        .grid {
            display: grid;
            gap: 10px
        }

        .grid.cols-4 {
            grid-template-columns:repeat(4, 1fr)
        }

        .row {
            display: flex;
            gap: 10px;
            align-items: center
        }

        .actions {
            margin-top: 12px
        }

        .btn {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #0b2950;
            background: #0b2950;
            color: #fff;
            cursor: pointer
        }

        .btn.secondary {
            border-color: #475569;
            background: #475569
        }
    </style>
</head>
<body>
<div class="navbar">
    <div class="brand">
        <img src="https://www.atankalama.com/custodia/img/Logo-Atankalama.png" alt="Atankalama">
        <span>SisColaciones · Vista previa Excel/CSV</span>
    </div>
    <div>
        <a href="<?=h(url('/colaciones/excel/import'))?>">Subir otro</a>
        <a href="<?=h(url('/colaciones/lotes'))?>">Lotes</a>
        <a href="<?=h(url('/empresas/listar'))?>">Empresas</a>
    </div>
</div>

<div class="container">
    <?php if ($errors): ?>
        <div class="errors">
            <?php foreach ($errors as $e): ?>
                <div>• <?=h($e)?></div><?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="row" style="justify-content:space-between">
                <div><strong>Archivo:</strong> <?=h(basename($file))?></div>
                <div class="muted">Filas: <?=(int)count($rows)?> · Columnas: <?=(int)count($headers)?></div>
            </div>

            <h3 style="margin-top:10px">Mapeo de columnas</h3>
            <form method="post" action="<?=h(url('/colaciones/excel/preview'))?>">
                <input type="hidden" name="action" value="confirm">
                <div class="grid cols-4">
                    <?php
                    $opts = '';
                    foreach ($headers as $i => $hName) {
                        $opts .= '<option value="'.(int)$i.'">'.h($hName).' (col '.($i + 1).')</option>';
                    }
                    $sel = function (string $name, $guess) use ($opts) {
                        $html = '<select name="'.$name.'" required><option value="">-- Seleccionar --</option>'.$opts.'</select>';
                        if ($guess !== '') {
                            $html .= '<script>document.currentScript.previousElementSibling.value="'.(int)$guess.'";</script>';
                        }

                        return $html;
                    };
                    ?>
                    <div><label>ID (del archivo)</label><?=$sel('map_id', $mapGuess['id'])?></div>
                    <div><label>RUT</label><?=$sel('map_rut', $mapGuess['rut'])?></div>
                    <div><label>Nombre</label><?=$sel('map_nombre', $mapGuess['nombre'])?></div>
                    <div><label>Habitación (opcional)</label><?=$sel('map_habitacion', $mapGuess['habitacion'])?></div>
                </div>

                <div class="actions">
                    <button class="btn" type="submit">Crear lote desde este Excel</button>
                    <a class="btn secondary" href="<?=h(url('/colaciones/excel/import'))?>">Volver</a>
                </div>
            </form>

            <h3 style="margin-top:18px">Vista previa (primeras 50 filas)</h3>
            <div style="max-height:420px;overflow:auto;border:1px solid #e5e7eb;border-radius:8px">
                <table>
                    <thead>
                    <tr>
                        <?php foreach ($headers as $hName): ?>
                            <th><?=h($hName)?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $max = min(50, count($rows));
                    for ($r = 0; $r < $max; $r++):
                        echo '<tr>';
                        $row = $rows[$r];
                        for ($c = 0; $c < count($headers); $c++) {
                            $val = $row[$c] ?? '';
                            echo '<td>'.h((string)$val).'</td>';
                        }
                        echo '</tr>';
                    endfor;
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="muted" style="margin-top:6px">Mostrando <?=(int)$max?> de <?=(int)count($rows)?> filas.</div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
