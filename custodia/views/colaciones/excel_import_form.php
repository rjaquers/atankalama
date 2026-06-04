<?php include __DIR__.'/../../includes/inc_proyect.php'; ?>
/**
 * Resumen:
 * - Form para subir Excel/CSV (name="excel").
 * - Procesa POST, mueve a /uploads/tmp y redirige a /colaciones/excel/preview.
 * - Soporta ?debug=1 para diagnóstico en pantalla.
 */

declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__.'/../../connections/conec6.php';
require_once __DIR__.'/../../connections/config.php';

if (!function_exists('h')) {
    function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

/* ===== Config de subida / debug siempre disponibles ===== */
$destDir = __DIR__ . '/../../uploads/tmp';
$logFile = __DIR__ . '/../../logs/upload_excel.log';
$errors  = [];
$infos   = [];
$debug   = isset($_GET['debug']) && $_GET['debug'] === '1';

/* ===== POST: subir archivo ===== */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {

    if (!isset($_FILES['excel'])) {
        $errors[] = 'No llegó el campo de archivo (excel). Verifica name="excel" y enctype="multipart/form-data".';
    } else {
        $f = $_FILES['excel'];

        // Mapa de errores PHP
        $errMap = [
                UPLOAD_ERR_OK         => 'OK',
                UPLOAD_ERR_INI_SIZE   => 'El archivo excede upload_max_filesize.',
                UPLOAD_ERR_FORM_SIZE  => 'El archivo excede MAX_FILE_SIZE del formulario.',
                UPLOAD_ERR_PARTIAL    => 'Archivo subido parcialmente.',
                UPLOAD_ERR_NO_FILE    => 'No se subió archivo.',
                UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal en el servidor.',
                UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en disco.',
                UPLOAD_ERR_EXTENSION  => 'Extensión bloqueada por la configuración.',
        ];

        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Error de subida: ' . ($errMap[$f['error']] ?? ('Código '.$f['error']));
        } elseif (!is_uploaded_file($f['tmp_name'] ?? '')) {
            $errors[] = 'tmp_name no es un archivo subido válido (is_uploaded_file=false).';
        } elseif (!filesize($f['tmp_name'])) {
            $errors[] = 'tmp_name sin contenido (size=0).';
        } else {
            // Asegurar carpeta destino
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            if (!is_dir($destDir)) {
                $errors[] = 'No se pudo crear la carpeta destino: ' . $destDir;
            } elseif (!is_writable($destDir)) {
                @chmod($destDir, 0775);
                if (!is_writable($destDir)) {
                    $errors[] = 'La carpeta destino no es escribible: ' . $destDir;
                }
            }

            // Mover si todo ok
            if (empty($errors)) {
                $origName = $f['name'] ?? 'archivo';
                $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, ['csv','xlsx','xls','txt'], true)) {
                    $errors[] = 'Extensión no soportada. Usa CSV/XLSX/XLS.';
                } else {
                    $dest = rtrim($destDir, '/\\') . '/excel_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                    if (!@move_uploaded_file($f['tmp_name'], $dest)) {
                        $errors[] = 'move_uploaded_file() falló (posible bloqueo mod_security/permisos).';
                    } elseif (!file_exists($dest)) {
                        $errors[] = 'El archivo movido no existe en destino (post-move check).';
                    } else {
                        // OK: guardar referencia en sesión y redirigir a preview
                        if (!is_dir(dirname($logFile))) @mkdir(dirname($logFile), 0775, true);
                        @error_log('[UPLOAD OK] '.$dest.PHP_EOL, 3, $logFile);

                        $_SESSION['excel_file'] = $dest;
                        header('Location: ' . url('/colaciones/excel/preview'));
                        exit;
                    }
                }
            }
        }
    }

    // Debug de POST
    if ($debug) {
        $infos[] = 'request_method='.($_SERVER['REQUEST_METHOD'] ?? '');
        $infos[] = 'CONTENT_LENGTH='.($_SERVER['CONTENT_LENGTH'] ?? '(n/a)');
        $infos[] = 'file_uploads='.(ini_get('file_uploads') ? 'On' : 'Off');
        $infos[] = 'tmp_name=' . h($_FILES['excel']['tmp_name'] ?? '(sin tmp)');
        $infos[] = 'name=' . h($_FILES['excel']['name'] ?? '(sin nombre)');
        $infos[] = 'error=' . (string)($_FILES['excel']['error'] ?? 'N/A');
        $infos[] = 'is_uploaded_file=' . (isset($_FILES['excel']['tmp_name']) && is_uploaded_file($_FILES['excel']['tmp_name']) ? 'sí' : 'no');
    }

    // Log de errores si hubo
    if (!empty($errors)) {
        if (!is_dir(dirname($logFile))) @mkdir(dirname($logFile), 0775, true);
        foreach ($errors as $e) { @error_log('[UPLOAD ERR] '.$e.PHP_EOL, 3, $logFile); }
    }
}

/* ===== Debug siempre visible si se pide ===== */
if ($debug) {
    $infos[] = 'DEBUG ON';
    $infos[] = 'destDir: ' . $destDir;
    $infos[] = 'exists(destDir): ' . (is_dir($destDir) ? 'yes' : 'no');
    $infos[] = 'is_writable(destDir): ' . (is_writable($destDir) ? 'yes' : 'no');
    $infos[] = 'upload_max_filesize: ' . ini_get('upload_max_filesize');
    $infos[] = 'post_max_size: ' . ini_get('post_max_size');
    $infos[] = 'max_file_uploads: ' . ini_get('max_file_uploads');
    $infos[] = 'request_method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Importar Excel/CSV — SisColaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .navbar{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#0b2950;color:#f1f5f9}
        .navbar .brand{display:flex;align-items:center;gap:8px;font-weight:700}
        .navbar img{height:28px}
        .navbar a{color:#f1f5f9;text-decoration:none;margin:0 8px}
        .navbar a:hover{text-decoration:underline}
        .container{max-width:900px;margin:16px auto;padding:0 12px}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px}
        label{display:block;font-size:14px;color:#334155;margin-bottom:6px}
        input[type=file],select{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px}
        button{padding:10px 14px;border-radius:8px;border:1px solid #0b2950;background:#0b2950;color:#fff;cursor:pointer}
        button:hover{filter:brightness(1.05)}
        .errors{background:#ffecec;border:1px solid #f5c2c7;color:#900;padding:10px;border-radius:8px;margin-bottom:12px}
        .infos{background:#ecfeff;border:1px solid #a5f3fc;color:#075985;padding:10px;border-radius:8px;margin-bottom:12px}
        .muted{color:#64748b;font-size:13px}
        code{background:#f1f5f9;padding:0 4px;border-radius:4px}
    </style>
</head>
<body>
<div class="navbar">
    <div class="brand">
        <img src="https://www.atankalama.com/custodia/img/Logo-Atankalama.png" alt="Atankalama">
        <span>SisColaciones · Importar Excel/CSV</span>
    </div>
    <div>
        <a href="<?= h(url('/colaciones/excel/import?debug=1')) ?>">Debug</a>
        <a href="<?= h(url('/tickets/custodia/listar')) ?>">SISCustodia</a>
        <a href="<?= h(url('/colaciones/lotes')) ?>">Lotes</a>
        <a href="<?= h(url('/empresas/listar')) ?>">Empresas</a>
    </div>
</div>

<div class="container">
    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $e): ?><div>• <?= h($e) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($debug && !empty($infos)): ?>
        <div class="infos">
            <?php foreach ($infos as $i): ?><div>▪ <?= h($i) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2>Subir archivo Excel/CSV</h2>
        <p class="muted">Estructura esperada: <code>id</code>, <code>rut</code>, <code>nombre</code>, <code>habitacion</code> (opcional).</p>

        <form method="post" enctype="multipart/form-data" action="<?= h(url('/colaciones/excel/import')) ?>">
            <label for="excel">Archivo (.csv, .xlsx)</label>
            <input type="file" id="excel" name="excel" required
                   accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel">
            <div style="margin-top:12px">
                <button type="submit">Previsualizar</button>
            </div>
        </form>

        <p class="muted" style="margin-top:12px">
            Si tienes problemas, usa <a href="<?= h(url('/colaciones/excel/import?debug=1')) ?>">Debug</a> y revisa permisos de <code>/uploads/tmp</code>.
        </p>
    </div>
</div>
</body>
</html>
