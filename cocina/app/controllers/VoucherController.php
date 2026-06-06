<?php

require_once __DIR__ . '/../models/VoucherModel.php';
require_once __DIR__ . '/../models/ComandaModel.php';
require_once __DIR__ . '/../models/EmpresaModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/CambioLogModel.php';
require_once __DIR__ . '/../models/ImpresionLogModel.php';
require_once __DIR__ . '/../config/db.php';

class VoucherController
{
    // ─────────────────────────────────────────────────────────
    // GESTIÓN DE CLIENTES DE UNA COMANDA
    // ─────────────────────────────────────────────────────────

    public function clientes(string $id = '0'): void
    {
        $comandaId = (int) $id;
        $model     = new VoucherModel();
        $cm        = new ComandaModel();

        $comanda   = $cm->obtenerPorId($comandaId);
        if (!$comanda) {
            echo '<div class="alert alert-danger m-4">Comanda no encontrada.</div>';
            return;
        }

        $clientes      = $model->obtenerClientesPorComanda($comandaId);
        $genericos     = $model->obtenerGenericosPorComanda($comandaId);
        $reserva       = (new ReservaModel())->obtenerPorComanda($comandaId);
        $respaldos     = $cm->obtenerRespaldos($comandaId);

        // Detectar si hay comandas futuras en la misma reserva para ofrecer propagación
        $comandasFuturas = 0;
        if ($reserva && !empty($reserva['id'])) {
            $stmt = (Database::getInstance())->prepare(
                "SELECT COUNT(*) FROM coci_comandas WHERE reserva_id = ? AND fecha > ?"
            );
            $stmt->execute([$reserva['id'], $comanda['fecha']]);
            $comandasFuturas = (int)$stmt->fetchColumn();
        }

        $cl      = new CambioLogModel();
        $cambios = $reserva
            ? $cl->obtenerPorReserva((int) $reserva['id'])
            : $cl->obtenerPorComanda($comandaId);

        $ok           = $_GET['ok']           ?? null;
        $errorImport  = $_GET['error_import'] ?? null;
        $insertados   = (int)($_GET['insertados']  ?? 0);
        $propagados   = (int)($_GET['propagados']  ?? 0);

        require_once __DIR__ . '/../views/voucher/clientes.php';
    }

    // ─────────────────────────────────────────────────────────
    // CREAR EMPRESA (modal)
    // ─────────────────────────────────────────────────────────

    public function crearEmpresa(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'mensaje' => 'Método no permitido.']);
            return;
        }

        $businessName = trim($_POST['business_name'] ?? '');
        if ($businessName === '') {
            echo json_encode(['status' => 'error', 'mensaje' => 'La razón social es obligatoria.']);
            return;
        }

        $id = (new EmpresaModel())->crearEmpresa([
            'rut'           => trim($_POST['rut']           ?? ''),
            'business_name' => $businessName,
            'trade_name'    => trim($_POST['trade_name']    ?? ''),
            'contact_name'  => trim($_POST['contact_name']  ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'address'       => trim($_POST['address']       ?? ''),
            'city'          => trim($_POST['city']          ?? ''),
            'type'          => trim($_POST['type']          ?? 'cliente'),
            'notes'         => trim($_POST['notes']         ?? ''),
        ]);

        echo json_encode([
            'status'        => 'ok',
            'id'            => $id,
            'business_name' => $businessName,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // AGREGAR UN CLIENTE MANUAL
    // ─────────────────────────────────────────────────────────

    public function agregar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $comandaId = (int)($_POST['comanda_id'] ?? 0);
        $rut       = trim($_POST['rut']    ?? '') ?: null;
        $nombre    = trim($_POST['nombre'] ?? '');
        
        $cm      = new ComandaModel();
        $comanda = $cm->obtenerPorId($comandaId);
        $empresa = $comanda['nombre_empresa'] ?? null;

        if ($nombre !== '' && $comandaId > 0) {
            $vm = new VoucherModel();
            $vm->insertarCliente($comandaId, $rut, $nombre, $empresa);

            (new CambioLogModel())->registrar(
                $comandaId,
                'coci_voucher_clientes',
                'voucher_agregado',
                null,
                $nombre . ($rut ? " ({$rut})" : ''),
                AccesoBootstrap::email() ?? 'sistema',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $comanda['reserva_id'] ?? null
            );

            // Propagación si se solicita
            $propagados = 0;
            if (isset($_POST['propagar']) && $_POST['propagar'] === '1' && !empty($comanda['reserva_id'])) {
                $propagados = $vm->propagarVouchers($comandaId, (int)$comanda['reserva_id'], $comanda['fecha']);
            }
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=agregado&propagados={$propagados}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // EDITAR UN CLIENTE
    // ─────────────────────────────────────────────────────────

    public function editar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $id        = (int)($_POST['id']         ?? 0);
        $comandaId = (int)($_POST['comanda_id'] ?? 0);
        $rut       = trim($_POST['rut']    ?? '') ?: null;
        $nombre    = trim($_POST['nombre'] ?? '');

        $propagados = 0;

        if ($id > 0 && $nombre !== '') {
            $vm = new VoucherModel();
            $cm = new ComandaModel();
            $comanda = $cm->obtenerPorId($comandaId);
            $empresa = $comanda['nombre_empresa'] ?? null;

            $clienteAnterior = $vm->obtenerClientePorId($id);

            $vm->actualizarCliente($id, $rut, $nombre, $empresa);

            // Registrar en el log
            $cambios = [];
            if (($clienteAnterior['nombre'] ?? '') !== $nombre) {
                $cambios[] = "Nombre: {$clienteAnterior['nombre']} -> {$nombre}";
            }
            if (($clienteAnterior['rut'] ?? '') !== $vm->normalizarRut($rut ?? '')) {
                $cambios[] = "RUT: " . ($clienteAnterior['rut'] ?: '—') . " -> " . ($rut ?: '—');
            }

            if (!empty($cambios)) {
                (new CambioLogModel())->registrar(
                    $comandaId,
                    'coci_voucher_clientes',
                    'voucher_editado',
                    implode(' | ', $cambios),
                    $nombre,
                    AccesoBootstrap::email() ?? 'sistema',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $comanda['reserva_id'] ?? null
                );
            }

            // Propagación a días siguientes
            if (($_POST['propagar'] ?? '') === '1' && !empty($comanda['reserva_id'])) {
                $propagados = $vm->propagarEdicionCliente(
                    (int)$comanda['reserva_id'],
                    $comanda['fecha'],
                    $clienteAnterior['rut'] ?? null,
                    $clienteAnterior['nombre'] ?? $nombre,
                    $rut,
                    $nombre,
                    $empresa
                );
            }
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=editado&propagados={$propagados}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // RESETEAR IMPRESIONES DE UN CLIENTE
    // ─────────────────────────────────────────────────────────

    public function resetearImpresiones(): void
    {
        $id        = (int)($_POST['id']         ?? 0);
        $comandaId = (int)($_POST['comanda_id'] ?? 0);

        if ($id > 0) {
            (new VoucherModel())->resetearImpresiones($id);
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=impresiones_reseteadas");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // ELIMINAR UN CLIENTE
    // ─────────────────────────────────────────────────────────

    public function eliminar(): void
    {
        $id        = (int)($_POST['id']         ?? 0);
        $comandaId = (int)($_POST['comanda_id'] ?? 0);

        $propagados = 0;

        if ($id > 0) {
            $vm      = new VoucherModel();
            $cliente = $vm->obtenerClientePorId($id);
            $vm->eliminarCliente($id);

            if ($cliente) {
                $comanda = (new ComandaModel())->obtenerPorId($comandaId);
                (new CambioLogModel())->registrar(
                    $comandaId,
                    'coci_voucher_clientes',
                    'voucher_eliminado',
                    $cliente['nombre'] . ($cliente['rut'] ? " ({$cliente['rut']})" : ''),
                    null,
                    AccesoBootstrap::email() ?? 'sistema',
                    $_SERVER['REMOTE_ADDR'] ?? null,
                    $comanda['reserva_id'] ?? null
                );

                // Propagación a días siguientes
                if (($_POST['propagar'] ?? '') === '1' && !empty($comanda['reserva_id'])) {
                    $propagados = $vm->propagarEliminacionCliente(
                        (int)$comanda['reserva_id'],
                        $comanda['fecha'],
                        $cliente['rut'] ?? null,
                        $cliente['nombre']
                    );
                }
            }
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=eliminado&propagados={$propagados}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // IMPORTAR EXCEL
    // ─────────────────────────────────────────────────────────

    public function importar(): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $comandaId = (int)($_POST['comanda_id'] ?? 0);

        if ($comandaId === 0) {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        if (empty($_FILES['archivo']['tmp_name']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            header("Location: index.php?page=voucher/clientes/{$comandaId}&error_import=upload");
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls', 'ods'])) {
            header("Location: index.php?page=voucher/clientes/{$comandaId}&error_import=formato");
            exit;
        }

        $cm      = new ComandaModel();
        $comanda = $cm->obtenerPorId($comandaId);
        
        if (!$comanda) {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $empresa = $comanda['nombre_empresa'] ?? null;

        $model  = new VoucherModel();
        $result = $model->importarDesdeExcel($comandaId, $_FILES['archivo']['tmp_name'], $empresa);

        $propagados = 0;
        if ($result['insertados'] > 0) {
            (new CambioLogModel())->registrar(
                $comandaId,
                'coci_voucher_clientes',
                'vouchers_importados',
                null,
                $result['insertados'] . ' clientes desde Excel',
                AccesoBootstrap::email() ?? 'sistema',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $comanda['reserva_id'] ?? null
            );

            // Propagación si se solicita
            if (isset($_POST['propagar']) && $_POST['propagar'] === '1' && !empty($comanda['reserva_id'])) {
                $propagados = $model->propagarVouchers($comandaId, (int)$comanda['reserva_id'], $comanda['fecha']);
            }
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=importado&insertados={$result['insertados']}&propagados={$propagados}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // GENERAR VOUCHERS GENÉRICOS
    // ─────────────────────────────────────────────────────────

    public function generarGenericos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $comandaId = (int)($_POST['comanda_id'] ?? 0);
        $cantidad  = max(1, (int)($_POST['cantidad'] ?? 1));

        if ($comandaId > 0) {
            (new VoucherModel())->generarVouchersGenericos($comandaId, $cantidad);

            $comanda = (new ComandaModel())->obtenerPorId($comandaId);
            (new CambioLogModel())->registrar(
                $comandaId,
                'coci_vouchers_genericos',
                'vouchers_genericos',
                null,
                $cantidad . ' vouchers genéricos',
                AccesoBootstrap::email() ?? 'sistema',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $comanda['reserva_id'] ?? null
            );
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=genericos");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // IMPRIMIR UN SOLO VOUCHER NOMINAL
    // ─────────────────────────────────────────────────────────

    public function imprimirUno(string $codigo = ''): void
    {
        $model   = new VoucherModel();
        $cliente = $model->buscarNominalPorCodigo($codigo);

        if (!$cliente) {
            echo '<div class="alert alert-danger m-4">Voucher no encontrado.</div>';
            return;
        }

        $cm      = new ComandaModel();
        $comanda = $cm->obtenerPorId((int)$cliente['comanda_id']);

        if (!$comanda) {
            echo '<div class="alert alert-danger m-4">Comanda no encontrada.</div>';
            return;
        }

        require_once __DIR__ . '/../views/voucher/imprimir_uno.php';
    }

    // ─────────────────────────────────────────────────────────
    // AGREGAR VOUCHERS GENÉRICOS (sin borrar los existentes)
    // ─────────────────────────────────────────────────────────

    public function agregarGenericos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $comandaId = (int)($_POST['comanda_id'] ?? 0);
        $cantidad  = max(1, (int)($_POST['cantidad'] ?? 1));

        if ($comandaId > 0) {
            $vm        = new VoucherModel();
            $desde     = $vm->agregarVouchersGenericos($comandaId, $cantidad);

            $comanda = (new ComandaModel())->obtenerPorId($comandaId);
            (new CambioLogModel())->registrar(
                $comandaId,
                'coci_vouchers_genericos',
                'vouchers_genericos',
                null,
                $cantidad . ' vouchers genéricos agregados (desde #' . $desde . ')',
                AccesoBootstrap::email() ?? 'sistema',
                $_SERVER['REMOTE_ADDR'] ?? null,
                $comanda['reserva_id'] ?? null
            );
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}&ok=genericos_agregados");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // VISTA DE IMPRESIÓN (lista completa de vouchers)
    // ─────────────────────────────────────────────────────────

    public function imprimir(string $id = '0'): void
    {
        $comandaId = (int) $id;
        $model     = new VoucherModel();
        $cm        = new ComandaModel();

        $comanda   = $cm->obtenerPorId($comandaId);
        if (!$comanda) {
            echo '<div class="alert alert-danger m-4">Comanda no encontrada.</div>';
            return;
        }

        $clientes  = $model->obtenerClientesPorComanda($comandaId);
        $genericos = $model->obtenerGenericosPorComanda($comandaId);

        $projectName = null;
        if (!empty($comanda['project_id'])) {
            $stmt = TicketsDatabase::getInstance()->prepare(
                'SELECT name FROM doc_projects WHERE id = ? LIMIT 1'
            );
            $stmt->execute([(int)$comanda['project_id']]);
            $projectName = $stmt->fetchColumn() ?: null;
        }

        require_once __DIR__ . '/../views/voucher/imprimir.php';
    }

    // ─────────────────────────────────────────────────────────
    // PANTALLA KIOSKO (pública)
    // ─────────────────────────────────────────────────────────

    public function kiosko(): void
    {
        require_once __DIR__ . '/../views/voucher/kiosko.php';
    }

    // ─────────────────────────────────────────────────────────
    // BÚSQUEDA POR RUT — AJAX (pública)
    // ─────────────────────────────────────────────────────────

    public function buscar(): void
    {
        // Descartar cualquier output previo (notices/warnings de PHP o de la carga de archivos)
        while (ob_get_level() > 0) ob_end_clean();

        header('Content-Type: application/json; charset=utf-8');

        try {
            $rut = trim($_POST['rut'] ?? '');
            if ($rut === '') {
                echo json_encode(['status' => 'error', 'mensaje' => 'Ingrese su RUT.']);
                exit;
            }

            $model    = new VoucherModel();
            $vouchers = $model->buscarPorRut($rut);
        } catch (\Throwable $e) {
            error_log('VoucherController::buscar — ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
            exit;
        }

        if (empty($vouchers)) {
            echo json_encode([
                'status'  => 'not_found',
                'mensaje' => 'No hay vouchers para su RUT en el día de hoy.',
            ]);
            exit;
        }

        // Preparar datos para el frontend
        $items = [];
        $baseUrl = BASE_URL;
        // Si no termina en public/, lo agregamos para que los links QR funcionen correctamente
        if (strpos($baseUrl, 'public/') === false) {
            $baseUrl .= 'public/';
        }

        foreach ($vouchers as $v) {
            // Regla: Desayuno no se muestra en el Kiosko (usuario final)
            if ($v['tipo_servicio'] === 'desayuno') continue;

            $urlVoucher = $baseUrl . 'index.php?page=voucher/ver/' . $v['codigo'];
            $horaActual = date('H:i');
            $permitido  = false;
            $mensaje    = '';

            // Reglas de horario
            if ($v['tipo_servicio'] === 'almuerzo') {
                if ($horaActual >= '12:00' && $horaActual <= '15:00') {
                    $permitido = true;
                } else {
                    $mensaje = 'Disponible de 12:00 a 15:00 hrs';
                }
            } elseif (in_array($v['tipo_servicio'], ['cena', 'colacion_especial'])) {
                if ($horaActual >= '16:30' && $horaActual <= '23:30') {
                    $permitido = true;
                } else {
                    $mensaje = 'Disponible de 16:30 a 23:30 hrs';
                }
            } else {
                // Otros servicios (ej: colacion estándar)
                $permitido = true;
            }

            $items[] = [
                'codigo'          => $v['codigo'],
                'nombre'          => $v['nombre'],
                'rut'             => $v['rut'] ?? '',
                'empresa'         => $v['empresa'] ?? '',
                'observaciones'   => $v['observaciones'] ?? '',
                'tipo_servicio'   => $v['tipo_servicio'],
                'etiqueta'        => VoucherModel::etiquetaServicio($v['tipo_servicio']),
                'color'           => VoucherModel::colorServicio($v['tipo_servicio']),
                'fecha'           => date('d/m/Y', strtotime($v['fecha'])),
                'fecha_texto'     => $this->diaNombreCompleto($v['fecha']),
                'hora'            => $v['hora_servicio'] ? substr($v['hora_servicio'], 0, 5) . ' hrs' : '—',
                'hotel'           => $v['nombre_hotel'],
                'canjeado'        => (bool)$v['canjeado'],
                'impreso'         => (bool)$v['impreso'],
                'veces_impreso'   => (int)($v['veces_impreso'] ?? 0),
                'permitido'       => $permitido,
                'mensaje_horario' => $mensaje,
                'url_voucher'     => $urlVoucher,
            ];
        }

        $json = json_encode(['status' => 'ok', 'vouchers' => $items], JSON_UNESCAPED_UNICODE);
        echo ($json !== false) ? $json : json_encode(['status' => 'error', 'mensaje' => 'Error al codificar la respuesta.']);
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // VER VOUCHER POR CÓDIGO QR (pública)
    // ─────────────────────────────────────────────────────────

    public function ver(string $codigo = ''): void
    {
        $model   = new VoucherModel();
        $voucher = $model->buscarNominalPorCodigo($codigo);
        $tipo    = 'nominal';

        if (!$voucher) {
            $voucher = $model->buscarGenericoPorCodigo($codigo);
            $tipo    = 'generico';
        }

        if (!$voucher) {
            $this->errorVoucher('Voucher no encontrado o código inválido.');
            return;
        }

        // Marcar como canjeado al primer escaneo
        if ($tipo === 'nominal') {
            $model->marcarNominalCanjeado($voucher['id']);
        } else {
            $model->marcarGenericoCanjeado($voucher['id']);
        }

        require_once __DIR__ . '/../views/voucher/ver.php';
    }

    // ─────────────────────────────────────────────────────────
    // REGISTRAR IMPRESIÓN INDIVIDUAL — AJAX (pública, kiosko)
    // ─────────────────────────────────────────────────────────

    public function registrarImpresion(): void
    {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        $codigo = trim($_POST['codigo'] ?? '');
        if ($codigo === '') {
            echo json_encode(['status' => 'error', 'mensaje' => 'Código requerido.']);
            exit;
        }

        try {
            (new VoucherModel())->marcarImpresoPorCodigo($codigo);
            echo json_encode(['status' => 'ok']);
        } catch (\Throwable $e) {
            error_log('VoucherController::registrarImpresion — ' . $e->getMessage());
            echo json_encode(['status' => 'error', 'mensaje' => $e->getMessage()]);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // MARCAR COMO IMPRESOS (AJAX)
    // ─────────────────────────────────────────────────────────

    public function marcarImpresos(): void
    {
        header('Content-Type: application/json');
        $comandaId = (int)($_POST['comanda_id'] ?? 0);
        if ($comandaId > 0) {
            $vm = new VoucherModel();
            $vm->marcarVouchersImpresos($comandaId);

            $comanda   = (new ComandaModel())->obtenerPorId($comandaId);
            $nominales = count($vm->obtenerClientesPorComanda($comandaId));
            $genericos = count($vm->obtenerGenericosPorComanda($comandaId));
            (new ImpresionLogModel())->registrar(
                $comandaId,
                AccesoBootstrap::email() ?? 'sistema',
                $nominales,
                $genericos,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $comanda['reserva_id'] ?? null
            );

            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'mensaje' => 'ID de comanda no válido.']);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // GUARDAR EDICIÓN DE COMANDA
    // ─────────────────────────────────────────────────────────

    public function guardarEdicionComanda(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $cm        = new ComandaModel();
        $cl        = new CambioLogModel();
        $comandaAnt = $cm->obtenerPorId($id);

        if (!$comandaAnt) {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $cantidadPersonas = max(1, (int)($_POST['cantidad_personas'] ?? 1));
        $horaServicio     = !empty($_POST['hora_servicio']) ? $_POST['hora_servicio'] . ':00' : null;
        $observaciones    = trim($_POST['observaciones'] ?? '') ?: null;
        $esParaLlevar     = (int)($_POST['es_para_llevar'] ?? 0);
        $nombreHotel      = trim($_POST['nombre_hotel'] ?? 'Atankalama');
        $nombreEmpresa    = trim($_POST['nombre_empresa'] ?? '') ?: null;
        $nombreContacto   = trim($_POST['nombre_contacto'] ?? '') ?: null;

        $cm->actualizar(
            $id,
            $cantidadPersonas,
            $horaServicio,
            $observaciones,
            $esParaLlevar,
            $nombreHotel,
            $nombreEmpresa,
            $nombreContacto
        );

        // Registro de cambios
        $campos = [
            'cantidad_personas' => $cantidadPersonas,
            'hora_servicio'     => $horaServicio,
            'observaciones'     => $observaciones,
            'es_para_llevar'    => $esParaLlevar,
            'nombre_hotel'      => $nombreHotel,
            'nombre_empresa'    => $nombreEmpresa,
            'nombre_contacto'   => $nombreContacto,
        ];

        $email = AccesoBootstrap::email() ?? 'sistema';
        $ip    = $_SERVER['REMOTE_ADDR'] ?? null;

        foreach ($campos as $campo => $valorNuevo) {
            $valorAnt = $comandaAnt[$campo] ?? null;
            
            // Normalizar para comparación
            if ($campo === 'es_para_llevar') {
                $valorAnt   = (int)$valorAnt;
                $valorNuevo = (int)$valorNuevo;
            }
            if ($campo === 'cantidad_personas') {
                $valorAnt   = (int)$valorAnt;
                $valorNuevo = (int)$valorNuevo;
            }
            
            if ($valorAnt != $valorNuevo) {
                $valAntStr = (string)$valorAnt;
                $valNewStr = (string)$valorNuevo;

                if ($campo === 'es_para_llevar') {
                    $valAntStr = (int)$valorAnt === 1 ? 'Sí' : 'No';
                    $valNewStr = (int)$valorNuevo === 1 ? 'Sí' : 'No';
                }

                $cl->registrar(
                    $id,
                    'coci_comandas',
                    $campo,
                    $valAntStr,
                    $valNewStr,
                    $email,
                    $ip,
                    (int)($comandaAnt['reserva_id'] ?? null) ?: null
                );
            }
        }

        $redir = $_POST['redir'] ?? "voucher/clientes/{$id}";
        header("Location: index.php?page={$redir}&ok=comanda_editada");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    private function diaNombreCompleto(string $fecha): string
    {
        $dias  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio',
                  'agosto','septiembre','octubre','noviembre','diciembre'];
        $ts = strtotime($fecha);
        return $dias[date('w', $ts)] . ' ' . date('j', $ts) . ' de '
             . $meses[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
    }

    private function errorVoucher(string $msg): void
    {
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
              <title>Voucher no encontrado</title></head>
              <body style="font-family:sans-serif;text-align:center;padding:60px;">
              <h2>⚠️ ' . htmlspecialchars($msg) . '</h2>
              <p><a href="javascript:history.back()">Volver</a></p>
              </body></html>';
    }
}
