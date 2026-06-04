<?php
require_once __DIR__.'/../models/ColacionLote.php';  // <- luego modelos
require_once __DIR__.'/../models/ColacionVoucher.php';

class ColacionController
{
    private function db(): mysqli {
        global $mysqli, $db;
        if ($db instanceof mysqli) return $db;
        if ($mysqli instanceof mysqli) return $mysqli;
        throw new RuntimeException('Sin conexión MySQLi disponible');
    }



    public function crear()
    {
        global $db;
        $empresas = $db->query('SELECT id, business_name AS nombre FROM doc_companies WHERE active=1 ORDER BY business_name')->fetch_all(MYSQLI_ASSOC);
        $tipos    = $db->query('SELECT id, nombre FROM colacion_servicio_tipo ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
        $adds     = $db->query('SELECT * FROM colacion_adicional WHERE activo = 1 ORDER BY tipo, nombre')->fetch_all(MYSQLI_ASSOC);

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $excelCount = isset($_SESSION['excel_mapped']) && is_array($_SESSION['excel_mapped'])
                ? count($_SESSION['excel_mapped'])
                : 0;

        include __DIR__.'/../views/colaciones/lote_form.php'; // en la vista: muestra $excelCount y un checkbox "usar_excel"
    }



    /*    public function crear()
        {
            // cargar combos: empresas, tipos, adicionales
            global $db;
            $empresas = $db->query('SELECT `id`, `business_name` AS `nombre` FROM `doc_companies` WHERE `active`=1 ORDER BY `business_name`')->fetch_all(MYSQLI_ASSOC);
            $tipos = $db->query('SELECT `id`, `nombre` FROM `colacion_servicio_tipo` ORDER BY `nombre`')->fetch_all(MYSQLI_ASSOC);
            $adds = $db->query('SELECT `id`, `nombre` FROM `colacion_adicional` ORDER BY `nombre`')->fetch_all(MYSQLI_ASSOC);

            include __DIR__.'/../views/colaciones/lote_form.php';
        }*/
    public function guardar()
    {
        global $db;
        // ---------------------------------------------------------
        // 1) Parámetros básicos del formulario
        // ---------------------------------------------------------
        $empresa_id = (int)($_POST['empresa_id'] ?? 0);
        $fecha      = trim($_POST['fecha_servicio'] ?? date('Y-m-d'));
        $fecha_fin_servicio = trim($_POST['fecha_fin_servicio'] ?? date('Y-m-d'));

        // ---------------------------------------------------------
        // 2) SERVICIOS: aceptar ambos formularios
        //    FORM NUEVO  → servicios[]
        //    FORM ANTIGUO → servicio_tipo_id
        // ---------------------------------------------------------
        if (isset($_POST['servicios'])) {
            // Nuevo formato (checkbox múltiple)
            $servicios_raw = (array)$_POST['servicios'];
            $servicios = array_map('intval', $servicios_raw);

            // Servicio principal → primero del array
            $servicio_tipo_id = $servicios[0] ?? 0;

        } elseif (isset($_POST['servicio_tipo_id'])) {
            // Formulario antiguo
            $servicio_tipo_id = (int)$_POST['servicio_tipo_id'];

            // Para mantener compatibilidad se genera array compatible
            $servicios = [$servicio_tipo_id];

        } else {
            // No llegó nada
            $servicio_tipo_id = 0;
            $servicios = [];
        }

        // Validación CRÍTICA para evitar errores de FOREIGN KEY
        if ($servicio_tipo_id <= 0) {
            http_response_code(400);
            exit('Debe seleccionar un servicio principal válido.');
        }

        // Convertimos los servicios adicionales en JSON (para el modelo)
        $servicios_adicionales = json_encode($servicios);


        // ---------------------------------------------------------
        // 3) Otros parámetros
        // ---------------------------------------------------------
        $cantidad   = (int)($_POST['cantidad'] ?? 0);
        $adds       = isset($_POST['adicionales']) ? array_map('intval', (array)$_POST['adicionales']) : [];
        $obs        = trim((string)($_POST['observaciones'] ?? ''));
        $excel       = (int)($_POST['excel'] ?? 0);
        $user_id    = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        // ---------------------------------------------------------
        // 4) Si viene desde Excel, tomamos cantidad y huéspedes
        // ---------------------------------------------------------
        $from_upload_id = (int)($_POST['from_upload_id'] ?? 0);
        $guests = [];

        if ($from_upload_id > 0) {
            $stmt = $db->prepare(
                    'SELECT fila_nro, rut, nombre, habitacion
               FROM excel_upload_item
              WHERE upload_id = ?
              ORDER BY fila_nro ASC'
            );
            if (!$stmt) {
                throw new \RuntimeException('DB_PREPARE_FAILED: '.$db->error);
            }

            $stmt->bind_param('i', $from_upload_id);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($r = $res->fetch_assoc()) {
                $idx = (int)$r['fila_nro'];
                $guests[$idx] = [
                        'rut'        => (string)$r['rut'],
                        'nombre'     => (string)$r['nombre'],
                        'habitacion' => $r['habitacion'] !== null ? (string)$r['habitacion'] : null,
                ];
            }
            $stmt->close();

            if (!empty($guests)) {
                $cantidad = count($guests);
            }
        }

        // ---------------------------------------------------------
        // 5) Validaciones mínimas
        // ---------------------------------------------------------
        if ($empresa_id <= 0) {
            http_response_code(400);
            exit('Parámetros inválidos: empresa es obligatoria.');
        }

        if ($from_upload_id <= 0 && $cantidad <= 0) {
            http_response_code(400);
            exit('Cantidad inválida. Si no viene Excel, debes indicar cantidad > 0.');
        }

        $obs = ($obs !== '') ? $obs : null;

        // ---------------------------------------------------------
        // 6) Transacción
        // ---------------------------------------------------------
        $db->begin_transaction();

        try {
            $loteM = new ColacionLote($db);
            $vchM  = new ColacionVoucher($db);

            // ------------------------
            // Crear lote
            // ------------------------
            $data_lote = [
                    'empresa_id'            => $empresa_id,
                    'fecha_servicio'        => $fecha,
                    'fecha_fin_servicio'    => $fecha_fin_servicio,
                    'servicio_tipo_id'      => $servicio_tipo_id,
                    'servicios_adicionales' => $servicios_adicionales,
                    'cantidad'              => $cantidad,
                    'observaciones'         => $obs,
                    'creado_por'            => $user_id,
                    'excel'                 => $excel,
                    'from_upload_id'        => $from_upload_id
            ];

            $lote_id = $loteM->crear($data_lote, $adds, $from_upload_id);

            // ------------------------
            // Generar vouchers
            // ------------------------
            $vchM->generarDesdeLote($lote_id, $fecha, $cantidad, $guests);

            $db->commit();

            // Redirección final
            header('Location: /custodia/colaciones/lotes');
            exit;

        } catch (\Throwable $e) {
            $db->rollback();
            http_response_code(500);
            exit('Error guardando: ' . $e->getMessage());
        }
    }






    public function guardarunoxuno() {
        global $db;

        $id = (int)($_POST['id'] ?? 0);
        $lote_id = (int)($_POST['lote_id'] ?? 0);

        $datos = [
                'guest_rut'       => trim($_POST['guest_rut']),
                'guest_nombre'    => trim($_POST['guest_nombre']),
                'guest_habitacion'=> trim($_POST['guest_habitacion']),
        ];

        if ($id > 0) {
            // update
            $db->update('colacion_voucher', $datos, ['id' => $id]);
        } else {
            // insert
            $datos['lote_id'] = $lote_id;
            $db->insert('colacion_voucher', $datos);
        }

        echo json_encode(['ok'=>1]);
    }
    public function eliminar() {
        global $db;
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $db->delete('colacion_voucher', ['id' => $id]);
        }

        echo json_encode(['ok'=>1]);
    }





    public function crearDesdeExcel()
    {
        global $db;

        $from_upload_id = isset($_GET['from_upload_id']) ? (int)$_GET['from_upload_id'] : 0;
        if ($from_upload_id <= 0) {
            http_response_code(400);
            echo 'from_upload_id inválido';
            return;
        }

        // Verifica que exista la carga
        $stmt = $db->prepare('SELECT id FROM excel_upload WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $from_upload_id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$exists) {
            http_response_code(404);
            echo 'Carga Excel no encontrada';
            return;
        }

        // Cuenta filas de la carga
        $stmt = $db->prepare('SELECT COUNT(*) AS c FROM excel_upload_item WHERE upload_id=?');
        $stmt->bind_param('i', $from_upload_id);
        $stmt->execute();
        $excelCount = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
        $stmt->close();

        // Combos
        $empresas = $db->query('SELECT id, business_name AS nombre FROM doc_companies WHERE active=1 ORDER BY business_name')->fetch_all(MYSQLI_ASSOC);
        $tipos    = $db->query('SELECT id, nombre FROM colacion_servicio_tipo ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);
        $adds     = $db->query('SELECT id, nombre FROM colacion_adicional ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);

        // Render
        $from_upload_id = (int)$from_upload_id; // para la vista
        include __DIR__.'/../views/colaciones/lote_form_from_excel.php';
    }


    public function index()
    {
        $db = $this->db();

        $empresa_id = isset($_GET['empresa_id']) && $_GET['empresa_id'] !== ''
                ? (int)$_GET['empresa_id']
                : null;

        $fecha = $_GET['fecha'] ?? null;

        // Modelo correcto
        $loteM = new ColacionLote($db);

        // 1) Obtener registros
        $rows = $loteM->listar($empresa_id, $fecha);

        // =========================================================
        // 2) Agregar nombres de servicios adicionales a cada fila
        // =========================================================
        foreach ($rows as &$r) {
            $r['servicios_adicionales_nombre'] =
                    $loteM->obtenerNombresAdicionalesDeLote((int)$r['id']);
        }
        unset($r);

        // 3) Listado empresas para filtro
        $empresas = $db->query('SELECT id, business_name AS nombre FROM doc_companies WHERE active=1 ORDER BY business_name')
                ->fetch_all(MYSQLI_ASSOC);

        // 4) Enviar datos a la vista
        include __DIR__.'/../views/colaciones/lote_index.php';
    }


    public function imprimir($lote_id)
    {
        $db   = $this->db();
        $loteM = new ColacionLote($db);
        $vchM  = new ColacionVoucher($db);

        $lote = $loteM->obtener((int)$lote_id);
        if (!$lote) { http_response_code(404); exit('Lote no encontrado'); }

        $adics = $loteM->obtenerAdicionales($lote['id']);

        // USAR el que hace JOIN con excel_upload_item
        $vchs  = $vchM->listarPorLoteConHuesped($lote['id']);

        $reimp   = isset($_GET['reimp']) && $_GET['reimp'] == '1';
        $user_id = $_SESSION['user_id'] ?? null;
        $vchM->registrarImpresionLote($lote['id'], $reimp ? 'reimpresion' : 'impresion', 1, $user_id);

        include __DIR__.'/../views/colaciones/print_lote_80mm.php';
    }





    public function reimprimir($lote_id)
    {
        $_GET['reimp'] = '1';
        $this->imprimir($lote_id);
    }
// Atajo con URL dedicada si prefieres rutas separadas
//    public function reimprimir($lote_id)
//    {
//        $_GET['reimp'] = '1';
//        $this->imprimir($lote_id);
//    }

    /** GET /colaciones/vouchers/scan?d=... */
    public function scanVoucher(): void
    {
        // --- Helpers locales (por si faltan) ---
        if (!function_exists('h')) {
            function h(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
        }
        if (!function_exists('client_ip')) {
            function client_ip(): ?string {
                foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'] as $h) {
                    $v = trim((string)($_SERVER[$h] ?? '')); if ($v) return explode(',', $v)[0];
                }
                return null;
            }
        }
        $wantsJson = (isset($_GET['format']) && $_GET['format'] === 'json')
                || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $d = isset($_GET['d']) ? trim((string)$_GET['d']) : '';
        if ($d === '') {
            if ($wantsJson) {
                http_response_code(400);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok'=>false,'status'=>'error','msg'=>'Falta parámetro d']);
                return;
            }
            http_response_code(400);
            echo 'Falta parámetro d.';
            return;
        }

        require_once __DIR__ . '/../models/ColacionVoucher.php';
        $model = new ColacionVoucher();

        // 1) Modo corto: d = codigo_publico
        $voucher = $model->obtenerPorCodigo($d);

        // 2) Compat: si no lo halla y existe qr_unpack, intenta formato firmado antiguo
        if (!$voucher && function_exists('qr_unpack')) {
            $res = qr_unpack($d, 2); // [id, codigo_publico]
            if (($res['ok'] ?? false) === true) {
                [$idStr, $pub] = $res['parts'];
                $row = $model->obtenerPorId((int)$idStr);
                if ($row && ($row['codigo_publico'] ?? '') === $pub) {
                    $voucher = $row;
                }
            }
        }

        if (!$voucher) {
            if ($wantsJson) {
                http_response_code(404);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok'=>false,'status'=>'error','msg'=>'Voucher no encontrado']);
                return;
            }
            http_response_code(404);
            echo 'Voucher no encontrado.';
            return;
        }

        // Incrementa contador de scans (diagnóstico; errores ignorables)
        try { $model->incrementarScan((int)$voucher['id']); } catch (\Throwable $e) {}

        $changed = false;
        $status  = 'warn';
        $msg     = 'Este voucher ya fue procesado.';

        if (($voucher['estado'] ?? 'pendiente') === 'pendiente') {
            $ip = client_ip();
            $ok = $model->marcarUsado((int)$voucher['id'], $ip);
            if ($ok) {
                $changed = true;
                $status  = 'ok';
                $msg     = 'Voucher marcado como USADO.';
                // recargar datos
                $voucher = $model->obtenerPorId((int)$voucher['id']);
            } else {
                // Otra transacción lo cambió en el microsegundo; recarga para informar
                $voucher = $model->obtenerPorId((int)$voucher['id']);
                $status  = (($voucher['estado'] ?? '') === 'usado') ? 'warn' : 'error';
                $msg     = (($voucher['estado'] ?? '') === 'usado') ? 'Este voucher ya fue procesado.' : 'No fue posible procesar el voucher.';
            }
        }

        // --- RESPUESTA JSON ---
        if ($wantsJson) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                                     'ok'     => ($status === 'ok'),
                                     'status' => $status,            // ok | warn | error
                                     'msg'    => $msg,
                                     'voucher'=> [
                                             'codigo_publico' => $voucher['codigo_publico'] ?? null,
                                             'lote_id'        => isset($voucher['lote_id']) ? (int)$voucher['lote_id'] : null,
                                             'numero_en_lote' => isset($voucher['numero_en_lote']) ? (int)$voucher['numero_en_lote'] : null,
                                             'estado'         => $voucher['estado'] ?? null,
                                             'scan_count'     => isset($voucher['scan_count']) ? (int)$voucher['scan_count'] : 0,
                                         // Huésped (si existe)
                                             'guest_nombre'   => $voucher['guest_nombre'] ?? null,
                                             'guest_rut'      => $voucher['guest_rut'] ?? null,
                                             'guest_hab'      => $voucher['guest_hab'] ?? null,
                                         // Trazas útiles
                                             'usado_en'       => $voucher['usado_en'] ?? null,
                                     ],
                             ]);
            return;
        }

        // --- RESPUESTA HTML (móvil / navegador) ---
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '/custodia';
        http_response_code(200);
        ?>
        <!doctype html>
        <html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Voucher <?=h($voucher['codigo_publico'] ?? '')?></title>
        <style>
            body{font-family:system-ui,Arial;margin:20px}
            .ok{background:#ecffef;color:#115e26;border:1px solid #bfe3c7;padding:10px;border-radius:10px}
            .warn{background:#fff4e5;color:#7a4d00;border:1px solid #ffd6a8;padding:10px;border-radius:10px}
            .error{background:#ffecec;color:#b10000;border:1px solid #f5c2c7;padding:10px;border-radius:10px}
            .meta{margin-top:10px}
            .badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #bbb;background:#f6f6f6}
            .kv{margin:2px 0}
            a.btn{display:inline-block;margin-top:10px;padding:8px 12px;border-radius:8px;border:1px solid #ccc;text-decoration:none}
        </style>
        <body>
        <h3>Voucher <?=h($voucher['codigo_publico'] ?? '')?></h3>
        <div class="<?=h($status)?>"><?=h($msg)?></div>
        <div class="meta">
            <div class="kv">Estado: <span class="badge"><?=h($voucher['estado'] ?? '')?></span></div>
            <?php if (!empty($voucher['guest_nombre']) || !empty($voucher['guest_rut']) || !empty($voucher['guest_hab'])): ?>
                <div class="kv"><strong><?=h($voucher['guest_nombre'] ?? '')?></strong></div>
                <?php if (!empty($voucher['guest_rut'])): ?><div class="kv">RUT: <?=h($voucher['guest_rut'])?></div><?php endif; ?>
                <?php if (!empty($voucher['guest_hab'])): ?><div class="kv">Hab.: <?=h($voucher['guest_hab'])?></div><?php endif; ?>
            <?php endif; ?>
            <div class="kv">Lote: #<?= (int)($voucher['lote_id'] ?? 0) ?> — N°: <?= (int)($voucher['numero_en_lote'] ?? 0) ?></div>
            <div class="kv">Scans: <?= (int)($voucher['scan_count'] ?? 0) ?></div>
            <?php if (!empty($voucher['usado_en'])): ?><div class="kv">Usado en: <?=h($voucher['usado_en'])?></div><?php endif; ?>
        </div>
        <a class="btn" href="<?= h($base) ?>/colaciones/lotes">Volver</a>
        </body></html>
        <?php
    }





    private function renderScanView(array $voucher, bool $changed, string $msg, int $status = 200): void
    {
        http_response_code($status);
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        ?>
        <!doctype html>
        <html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Voucher <?=h($voucher['codigo_publico'] ?? '')?></title>
        <style>
            body{font-family:system-ui,Arial;margin:20px}
            .ok{background:#ecffef;color:#115e26;border:1px solid #bfe3c7;padding:10px;border-radius:10px}
            .warn{background:#fff4e5;color:#7a4d00;border:1px solid #ffd6a8;padding:10px;border-radius:10px}
            .err{background:#ffecec;color:#b10000;border:1px solid #f5c2c7;padding:10px;border-radius:10px}
            .meta{margin-top:10px}
            .badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #bbb;background:#f6f6f6}
            a.btn{display:inline-block;margin-top:10px;padding:8px 12px;border-radius:8px;border:1px solid #ccc;text-decoration:none}
        </style>
        <body>
        <h3>Voucher <?=h($voucher['codigo_publico'] ?? '')?></h3>
        <div class="<?= $changed ? 'ok' : (($voucher['estado'] ?? '')==='usado' ? 'warn' : 'err') ?>">
            <?=h($msg)?>
        </div>
        <div class="meta">
            <div>Estado: <span class="badge"><?=h($voucher['estado'] ?? '')?></span></div>
            <?php if (!empty($voucher['usado_en'])): ?>
                <div>Usado en: <?=h($voucher['usado_en'])?></div>
            <?php endif; ?>
            <div>Lote: #<?= (int)($voucher['lote_id'] ?? 0) ?> — N° en lote: <?= (int)($voucher['numero_en_lote'] ?? 0) ?></div>
            <div>Scans: <?= (int)($voucher['scan_count'] ?? 0) ?></div>
        </div>
        <a class="btn" href="<?= $base ?>/colaciones/lotes">Volver</a>
        </body></html>
        <?php
    }



    private function renderScanView_respaldo(array $voucher, bool $changed, string $msg, int $status = 200): void
    {
        http_response_code($status);
        // Vista mínima para móvil (puedes mover a views/colaciones/scan_voucher.php si prefieres)
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        ?>
        <!doctype html>
        <html lang="es">
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Voucher <?=h($voucher['codigo_publico'] ?? '')?></title>
        <style>
            body{font-family:system-ui,Arial;margin:20px}
            .ok{background:#ecffef;color:#115e26;border:1px solid #bfe3c7;padding:10px;border-radius:10px}
            .warn{background:#fff4e5;color:#7a4d00;border:1px solid #ffd6a8;padding:10px;border-radius:10px}
            .err{background:#ffecec;color:#b10000;border:1px solid #f5c2c7;padding:10px;border-radius:10px}
            .meta{margin-top:10px}
            .badge{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid #bbb;background:#f6f6f6}
            a.btn{display:inline-block;margin-top:10px;padding:8px 12px;border-radius:8px;border:1px solid #ccc;text-decoration:none}
        </style>
        <body>
        <h3>Voucher <?=h($voucher['codigo_publico'] ?? '')?></h3>
        <div class="<?= $changed ? 'ok' : (($voucher['estado'] ?? '')==='usado' ? 'warn' : 'err') ?>">
            <?=h($msg)?>
        </div>
        <div class="meta">
            <div>Estado: <span class="badge"><?=h($voucher['estado'] ?? '')?></span></div>
            <?php if (!empty($voucher['usado_en'])): ?>
                <div>Usado en: <?=h($voucher['usado_en'])?></div>
            <?php endif; ?>
            <div>Lote: #<?= (int)($voucher['lote_id'] ?? 0) ?> — N° en lote: <?= (int)($voucher['numero_en_lote'] ?? 0) ?></div>
            <div>Scans: <?= (int)($voucher['scan_count'] ?? 0) ?></div>
        </div>
        <a class="btn" href="<?= $base ?>/colaciones/lotes">Volver</a>
        </body>
        </html>
        <?php
    }



    /**
     * Valida formato y dígito verificador de un RUT chileno
     * Retorna true si es válido, false si no lo es.
     */
    private function validarRut($rut)
    {
        $rut = strtolower(trim($rut));
        $rut = str_replace(['.', ','], '', $rut);

        if (strpos($rut, '-') === false) {
            return false;
        }

        [$cuerpo, $dv] = explode('-', $rut);

        if (!ctype_digit($cuerpo)) {
            return false;
        }

        $factor = 2;
        $suma = 0;

        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
            $suma += $factor * $cuerpo[$i];
            $factor = $factor == 7 ? 2 : $factor + 1;
        }

        $resto = $suma % 11;
        $dv_calculado = 11 - $resto;

        if ($dv_calculado == 11) {
            $dv_calculado = '0';
        } elseif ($dv_calculado == 10) {
            $dv_calculado = 'k';
        } else {
            $dv_calculado = (string)$dv_calculado;
        }

        return $dv === $dv_calculado;
    }

    public function buscar()
    {
        require_once __DIR__ . '/../models/ImpresionesModel.php';
        $db = $this->db();
        $rut = $_GET['rut'] ?? null;
        $resultado = [];
        $vouchers  = [];

        // 0) Validación de RUT
        if ($rut && ! $this->validarRut($rut)) {
            $resultado = [
                    'rut' => $rut,
                    'nombre' => 'Formato inválido',
                    'estado' => 'RUT inválido',
                    'servicios' => '---'

            ];

            include __DIR__.'/../views/colaciones/buscar_servicio.php';

            return;
        }

        // 1) Consulta principal
        if ($rut) {
            $sql = '
            SELECT
                l.id AS lote_id,
                v.codigo_publico,
                v.id AS voucher_id,
                l.servicio_tipo_id,
                l.servicios_adicionales,
                GROUP_CONCAT(la.adicional_id ORDER BY la.adicional_id SEPARATOR \',\') AS adicionales_relacional,
                v.guest_rut,
                v.guest_nombre,
                v.guest_habitacion
            FROM colacion_voucher AS v
            INNER JOIN colacion_lote AS l ON l.id = v.lote_id
            LEFT JOIN colacion_lote_adicional AS la ON la.lote_id = l.id
            WHERE v.guest_rut = ?
              AND l.fecha_servicio <= CURDATE()
              AND l.fecha_fin_servicio >= CURDATE()
            GROUP BY v.id
            LIMIT 1
        ';

            $stmt = $db->prepare($sql);
            $stmt->bind_param('s', $rut);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            // --- Sin servicio vigente
            if (! $row) {
                $resultado = [
                        'rut' => $rut,
                        'nombre' => 'No encontrado o sin servicio vigente',
                        'estado' => 'No autorizado',
                        'servicios' => 'Sin servicios activos',
                        'lote_id' => null,
                ];
                include __DIR__.'/../views/colaciones/buscar_servicio.php';
                return;
            }

            // --- Modelos
            $loteM = new ColacionLote($db);
            $voucherM = new ColacionVoucher($db);

            // --- Servicios adicionales: fuente primaria = tabla relacional colacion_lote_adicional
            // Fallback al campo CSV legacy (servicios_adicionales) para lotes antiguos
            $fuenteAdicionales = $row['adicionales_relacional'] ?? null;
            if ($fuenteAdicionales === null || $fuenteAdicionales === '') {
                $fuenteAdicionales = $row['servicios_adicionales'] ?? null;
            }

            $idsServicios = ($fuenteAdicionales !== null && $fuenteAdicionales !== '')
                ? array_values(array_filter(array_map('intval', explode(',', $fuenteAdicionales))))
                : [];

            // Agregar servicio principal a la lista si no estuviera
            if (! in_array((int)$row['servicio_tipo_id'], $idsServicios)) {
                $idsServicios[] = (int)$row['servicio_tipo_id'];
            }

            // --- Variables para clasificación
            $serviciosDisponibles = [];
            $serviciosFueraHorario = [];
            $serviciosDetalles = []; // nombres de todos

            $hora_actual_ts = strtotime(date('H:i:s'));

            foreach ($idsServicios as $sid) {
                // Obtener nombre + horario
                $info = $loteM->obtenerInfoServicio($sid);

                if ($info === null) {
                    continue;
                }

                $serviciosDetalles[] = $info['nombre'];

                // Comparación numérica de timestamps para evitar dependencia del formato HH:MM
                $inicio_ts = strtotime($info['hora_inicio']);
                $fin_ts    = strtotime($info['hora_fin']);

                if ($hora_actual_ts >= $inicio_ts && $hora_actual_ts <= $fin_ts) {
                    $serviciosDisponibles[] = $info;
                } else {
                    $serviciosFueraHorario[] = $info;
                }
            }

            // --- CASO: Ningún servicio disponible ahora
            if (empty($serviciosDisponibles)) {
                $mensaje = 'Servicios fuera de horario:';
                foreach ($serviciosFueraHorario as $s) {
                    $mensaje .= "- {$s['nombre']} ({$s['hora_inicio']}–{$s['hora_fin']})";
                }

                $resultado = [
                        'rut' => $row['guest_rut'],
                        'nombre' => $row['guest_nombre'],
                        'estado' => 'Fuera de horario',
                        'servicios' => implode(', ', $serviciosDetalles),
                        'mensaje' => $mensaje,
                        'permitir_impresion' => false,
                        'lote_id' => $row['lote_id'],
                        'voucher_id' => $row['voucher_id'],


                ];




                include __DIR__.'/../views/colaciones/buscar_servicio.php';

                return;
            }

            // --- CASO: Hay servicios en horario → generar voucher SOLO para esos
            // --- Generar vouchers disponibles
            $vouchers = [];

            foreach ($serviciosDisponibles as $svc) {

                // OBTENER voucher existente
                $voucherExistente = $voucherM->obtenerVoucherDeLotePorRutYServicio(
                        $row['lote_id'],
                        $row['guest_rut'],
                        $svc['id']
                );

                if (!$voucherExistente) {
                    continue; // esto no debería ocurrir
                }

                $vouchers[] = [
                        'servicio'      => $svc['nombre'],
                        'servicio_id'   => $svc['id'],
                        'codigo_publico2'=> $voucherExistente['codigo_publico'],
                        'id'=> $voucherExistente['id'],
                        'url'           => '/custodia/colaciones/voucher/imprimir?codigo='
                                . urlencode($voucherExistente['codigo_publico'])
                ];



            }




// ======================================================
//  ENRIQUECER CADA SERVICIO CON LA INFORMACIÓN DE IMPRESIÓN
// ======================================================
            $impModel = new ImpresionesModel($db);

            foreach ($vouchers as &$v) {
                $v['impreso'] = $impModel->fueImpresoHoy($row['guest_rut'], (int)$v['servicio_id']);
            }
            unset($v);


            // --- Resultado final
            $resultado = [
                    'rut' => $row['guest_rut'],
                    'nombre' => $row['guest_nombre'],
                    'estado' => 'Autorizado ahora',
                    'servicios' => implode(', ', $serviciosDetalles),
                    'permitir_impresion' => true,
                    'vouchers' => $vouchers,
                    'lote_id' => $row['lote_id'],
                    'voucher_id' => $row['voucher_id']

            ];
        }

        include __DIR__.'/../views/colaciones/buscar_servicio.php';
    }


    //fin buscar

    public function personas($lote_id)
    {
        $lote_id = (int)$lote_id;

        $db = $this->db();
        $model = new ColacionLote($db);

        $lote = $model->obtener($lote_id);

        // Personas del lote
        $personas = $model->obtenerPersonasPorLote($lote_id);

        // Servicios asociados
        $serviciosRaw = $model->obtenerServiciosDeLote($lote_id);

        $idsServicios = array_merge([$serviciosRaw['principal']], $serviciosRaw['adicionales']);
        $nombresServicios = $model->obtenerNombresServicios($idsServicios);

        // Todos los tipos de servicio disponibles (para el formulario de edición)
        $todosServicios = $db->query('SELECT id, nombre FROM colacion_servicio_tipo ORDER BY nombre')->fetch_all(MYSQLI_ASSOC);

        // Reporte de impresiones por persona y servicio
        require_once __DIR__ . '/../models/ImpresionesModel.php';
        $impModel  = new ImpresionesModel($db);
        $reporteImpresiones = $impModel->obtenerReporteDelLote($lote_id);

        include __DIR__ . '/../views/colaciones/personas.php';
    }

    /**
     * Exporta el reporte de impresiones de un lote a Excel (.xlsx) con 3 hojas.
     */
    public function exportarImpresiones(int $lote_id): void
    {
        $db     = $this->db();
        $loteM  = new ColacionLote($db);
        $lote   = $loteM->obtener($lote_id);

        if (!$lote) {
            http_response_code(404);
            exit('Lote no encontrado.');
        }

        require_once __DIR__ . '/../models/ImpresionesModel.php';
        $impModel = new ImpresionesModel($db);

        $resumen  = $impModel->obtenerReporteDelLote($lote_id);
        $detalle  = $impModel->obtenerDetalleTotemDelLote($lote_id);
        $auditoria = $impModel->obtenerAuditoriaDelLote($lote_id);

        // Servicios del lote para columnas del resumen
        $serviciosRaw     = $loteM->obtenerServiciosDeLote($lote_id);
        $idsServicios     = array_filter(array_merge([$serviciosRaw['principal']], $serviciosRaw['adicionales']));
        $nombresServicios = $loteM->obtenerNombresServicios($idsServicios);

        // ── PhpSpreadsheet ──────────────────────────────────────────────
        require_once __DIR__ . '/../vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Reporte Impresiones Lote '.$lote_id)
            ->setCreator('Custodia')
            ->setDescription('Exportado el '.date('d/m/Y H:i'));

        // ── Estilos reutilizables ────────────────────────────────────────
        $styleHeader = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $styleBorder = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ];

        // ════════════════════════════════════════════════════════════════
        // HOJA 1 — RESUMEN POR PERSONA
        // ════════════════════════════════════════════════════════════════
        $ws1 = $spreadsheet->getActiveSheet();
        $ws1->setTitle('Resumen por persona');

        // Título general
        $ws1->setCellValue('A1', 'Reporte de impresiones — Lote '.$lote_id.' ('.$lote['empresa'].' / '.date('d/m/Y', strtotime($lote['fecha_servicio'])).' – '.date('d/m/Y', strtotime($lote['fecha_fin_servicio'])).')');
        $ws1->mergeCells('A1:'. \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(3 + count($idsServicios)) .'1');
        $ws1->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);
        $ws1->getRowDimension(1)->setRowHeight(22);

        // Cabeceras
        $colIdx = 1;
        $ws1->setCellValueByColumnAndRow($colIdx++, 2, 'RUT');
        $ws1->setCellValueByColumnAndRow($colIdx++, 2, 'Nombre');
        $ws1->setCellValueByColumnAndRow($colIdx++, 2, 'Habitación');
        foreach ($idsServicios as $sid) {
            $ws1->setCellValueByColumnAndRow($colIdx++, 2, $nombresServicios[$sid] ?? 'Servicio '.$sid);
        }
        $ws1->setCellValueByColumnAndRow($colIdx, 2, 'TOTAL');

        $lastColResumen = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
        $ws1->getStyle('A2:'.$lastColResumen.'2')->applyFromArray($styleHeader);

        // Datos
        $fila = 3;
        foreach ($resumen as $r) {
            $colIdx = 1;
            $total  = 0;
            $ws1->setCellValueByColumnAndRow($colIdx++, $fila, $r['rut']);
            $ws1->setCellValueByColumnAndRow($colIdx++, $fila, $r['nombre']);
            $ws1->setCellValueByColumnAndRow($colIdx++, $fila, $r['habitacion'] ?? '');
            foreach ($idsServicios as $sid) {
                $cnt = $r['servicios'][$sid]['total'] ?? 0;
                $ws1->setCellValueByColumnAndRow($colIdx++, $fila, $cnt);
                $total += $cnt;
            }
            $ws1->setCellValueByColumnAndRow($colIdx, $fila, $total);
            // Resaltar filas sin ninguna impresión
            if ($total === 0) {
                $ws1->getStyle('A'.$fila.':'.$lastColResumen.$fila)
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF2CC');
            }
            $fila++;
        }

        // Borde general y autowidth
        $ws1->getStyle('A2:'.$lastColResumen.($fila - 1))->applyFromArray($styleBorder);
        foreach (range(1, $colIdx) as $c) {
            $ws1->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // ════════════════════════════════════════════════════════════════
        // HOJA 2 — DETALLE TÓTEM (cada impresión individual)
        // ════════════════════════════════════════════════════════════════
        $ws2 = $spreadsheet->createSheet();
        $ws2->setTitle('Detalle tótem');

        $headers2 = ['RUT', 'Nombre', 'Habitación', 'Servicio', 'Fecha impresión', 'N° impresión del día'];
        foreach ($headers2 as $ci => $h) {
            $ws2->setCellValueByColumnAndRow($ci + 1, 1, $h);
        }
        $ws2->getStyle('A1:F1')->applyFromArray($styleHeader);

        $fila = 2;
        foreach ($detalle as $d) {
            $ws2->setCellValueByColumnAndRow(1, $fila, $d['rut']);
            $ws2->setCellValueByColumnAndRow(2, $fila, $d['nombre']);
            $ws2->setCellValueByColumnAndRow(3, $fila, $d['habitacion'] ?? '');
            $ws2->setCellValueByColumnAndRow(4, $fila, $d['servicio']);
            $ws2->setCellValueByColumnAndRow(5, $fila, $d['fecha_impresion']);
            $ws2->setCellValueByColumnAndRow(6, $fila, (int)$d['nro_impresion']);
            $fila++;
        }

        $ws2->getStyle('A1:F'.max(1, $fila - 1))->applyFromArray($styleBorder);
        foreach (range(1, 6) as $c) {
            $ws2->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // ════════════════════════════════════════════════════════════════
        // HOJA 3 — AUDITORÍA (IP + user-agent + tipo)
        // ════════════════════════════════════════════════════════════════
        $ws3 = $spreadsheet->createSheet();
        $ws3->setTitle('Auditoría');

        $headers3 = ['RUT', 'Nombre', 'Habitación', 'Servicio', 'Fecha impresión', 'Tipo', 'IP', 'User Agent'];
        foreach ($headers3 as $ci => $h) {
            $ws3->setCellValueByColumnAndRow($ci + 1, 1, $h);
        }
        $ws3->getStyle('A1:H1')->applyFromArray($styleHeader);

        $fila = 2;
        foreach ($auditoria as $a) {
            $ws3->setCellValueByColumnAndRow(1, $fila, $a['rut']);
            $ws3->setCellValueByColumnAndRow(2, $fila, $a['nombre']);
            $ws3->setCellValueByColumnAndRow(3, $fila, $a['habitacion'] ?? '');
            $ws3->setCellValueByColumnAndRow(4, $fila, $a['servicio']);
            $ws3->setCellValueByColumnAndRow(5, $fila, $a['fecha_impresion']);
            $ws3->setCellValueByColumnAndRow(6, $fila, $a['tipo']);
            $ws3->setCellValueByColumnAndRow(7, $fila, $a['ip'] ?? '');
            $ws3->setCellValueByColumnAndRow(8, $fila, $a['user_agent'] ?? '');
            $fila++;
        }

        $ws3->getStyle('A1:H'.max(1, $fila - 1))->applyFromArray($styleBorder);
        foreach (range(1, 8) as $c) {
            $ws3->getColumnDimensionByColumn($c)->setAutoSize(true);
        }

        // ── Activar hoja 1 y enviar archivo ─────────────────────────────
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'reporte_impresiones_lote'.$lote_id.'_'.date('Ymd_His').'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    /**
     * Actualiza el servicio principal y los servicios adicionales de un lote
     */
    public function actualizarServicios(): void
    {
        $db = $this->db();

        $lote_id  = (int)($_POST['lote_id'] ?? 0);
        $servicios = array_values(array_unique(array_map('intval', $_POST['servicios'] ?? [])));

        if (empty($servicios)) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'error' => 'Debe seleccionar al menos un servicio']);
            return;
        }

        // Mantener el principal actual si sigue marcado; si no, usar el primero marcado
        $row = $db->query("SELECT servicio_tipo_id FROM colacion_lote WHERE id = $lote_id LIMIT 1")->fetch_assoc();
        $principalActual = (int)($row['servicio_tipo_id'] ?? 0);

        $principal   = in_array($principalActual, $servicios) ? $principalActual : $servicios[0];
        $adicionales = array_values(array_filter($servicios, fn($id) => $id !== $principal));

        $adicionales_csv = empty($adicionales) ? null : implode(',', $adicionales);

        $stmt = $db->prepare('UPDATE colacion_lote SET servicio_tipo_id = ?, servicios_adicionales = ? WHERE id = ?');
        $stmt->bind_param('isi', $principal, $adicionales_csv, $lote_id);
        $stmt->execute();

        header('Content-Type: application/json');
        echo json_encode(['ok' => true, 'lote_id' => $lote_id]);
    }


    /**
     * Muestra formulario de edición de un lote
     */
    /**
     * Muestra formulario de edición de un lote
     */
    public function form(int $id): void
    {
        $db = $this->db();

        // Modelo correcto (como el resto del controller)
        $loteM = new ColacionLote($db);

        $lote = $loteM->obtener($id);

        if (! $lote) {
            http_response_code(404);
            echo 'Lote no encontrado';
            return;
        }

        // Cargar combos necesarios (MISMO contrato que crear())
        $empresas = $db->query(
                'SELECT id, business_name AS nombre FROM doc_companies WHERE active=1 ORDER BY business_name'
        )->fetch_all(MYSQLI_ASSOC);

        $tipos = $db->query(
                'SELECT id, nombre FROM colacion_servicio_tipo ORDER BY nombre'
        )->fetch_all(MYSQLI_ASSOC);

        $adds = $db->query(
                'SELECT * FROM colacion_adicional WHERE activo = 1 ORDER BY tipo, nombre'
        )->fetch_all(MYSQLI_ASSOC);

        // Modo edición
        $modo = 'editar';

        require __DIR__ . '/../views/colaciones/lote_form.php';
    }
// Fin de la función form()


    /**
     * Actualiza fechas de servicio de un lote
     */
    public function actualizarFechas(): void
    {
        $db = $this->db();

        $lote_id = (int)($_POST['lote_id'] ?? 0);
        $inicio  = $_POST['fecha_servicio'] ?? '';
        $fin     = $_POST['fecha_fin_servicio'] ?? '';

        if ($lote_id <= 0 || ! $inicio || ! $fin) {
            http_response_code(400);
            echo json_encode(['ok' => 0, 'msg' => 'Datos incompletos']);
            return;
        }

        $stmt = $db->prepare(
                'UPDATE colacion_lote
         SET fecha_servicio = ?, fecha_fin_servicio = ?
         WHERE id = ?'
        );

        $stmt->bind_param('ssi', $inicio, $fin, $lote_id);
        $ok = $stmt->execute();

        echo json_encode(['ok' => $ok ? 1 : 0]);
    }
// Fin de la función actualizarFechas()



}
