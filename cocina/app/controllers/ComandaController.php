<?php

require_once __DIR__ . '/../models/ComandaModel.php';
require_once __DIR__ . '/../models/EmpresaModel.php';
require_once __DIR__ . '/../models/RecepcionModel.php';
require_once __DIR__ . '/../models/ReservaModel.php';
require_once __DIR__ . '/../models/DesayunoMasivoModel.php';

class ComandaController
{
    // ─────────────────────────────────────────────────────────
    // FORMULARIOS
    // ─────────────────────────────────────────────────────────

    /** Formulario: Cena o Colación del día */
    public function cena(): void
    {
        $empresaModel  = new EmpresaModel();
        $empresasLista = $empresaModel->listarEmpresasActivas();
        $tipoFijo      = null; // el usuario elige cena o colacion en el form
        require_once __DIR__ . '/../views/comanda/cena.php';
    }

    /** Formulario: Desayunos (multi-día) */
    public function desayuno(): void
    {
        $empresaModel  = new EmpresaModel();
        $empresasLista = $empresaModel->listarEmpresasActivas();
        require_once __DIR__ . '/../views/comanda/desayuno.php';
    }

    /** Formulario: Colación reforzada o especial */
    public function especial(): void
    {
        $empresaModel  = new EmpresaModel();
        $empresasLista = $empresaModel->listarEmpresasActivas();
        $tipoFijo      = 'colacion_especial';
        require_once __DIR__ . '/../views/comanda/cena.php'; // misma vista, tipo fijo
    }

    // ─────────────────────────────────────────────────────────
    // GUARDAR (POST)
    // ─────────────────────────────────────────────────────────

    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        // ── Tipo de servicio ─────────────────────────────────
        $tiposValidos  = ['cena', 'colacion', 'colacion_especial', 'almuerzo'];
        $tipoServicio  = in_array($_POST['tipo_servicio'] ?? '', $tiposValidos)
                         ? $_POST['tipo_servicio']
                         : 'cena';

        // ── Campos comunes ───────────────────────────────────
        $fecha            = $_POST['fecha']             ?? date('Y-m-d');
        $nombreHotel      = in_array($_POST['nombre_hotel'] ?? '', ['Atankalama', 'Atankalama Inn'])
                            ? $_POST['nombre_hotel'] : 'Atankalama';
        $cantidadPersonas = max(1, (int)($_POST['cantidad_personas'] ?? 1));
        $horaServicio     = !empty($_POST['hora_servicio']) ? $_POST['hora_servicio'] : null;
        $observaciones    = trim($_POST['observaciones'] ?? '') ?: null;
        $origen           = ($_POST['origen'] ?? '') === 'urgente' ? 'urgente' : 'programada';

        // ── Tipo de solicitante ──────────────────────────────
        $tipoSolicitante = ($_POST['tipo_solicitante'] ?? '') === 'empresa' ? 'empresa' : 'particular';
        $companyId       = null;
        $contractId      = null;
        $nombreEmpresa   = null;
        $nombreContacto  = null;

        if ($tipoSolicitante === 'empresa') {
            $companyId     = intval($_POST['company_id']     ?? 0) ?: null;
            $contractId    = intval($_POST['contract_id']    ?? 0) ?: null;
            $nombreEmpresa = trim($_POST['nombre_empresa']   ?? '') ?: null;
            $nombreContacto = trim($_POST['nombre_contacto'] ?? '') ?: null;
        } else {
            $nombreContacto = trim($_POST['nombre_contacto'] ?? '') ?: null;
        }

        // ── Guardar comanda ──────────────────────────────────
        $model     = new ComandaModel();
        $comandaId = $model->insertar(
            $fecha, $tipoServicio, $nombreHotel, $tipoSolicitante,
            $companyId, $contractId, $nombreEmpresa, $nombreContacto,
            $cantidadPersonas, $horaServicio, $observaciones,
            0, $origen
        );

        // Procesar archivos de respaldo
        $this->procesarRespaldos($comandaId);

        // ── Si es urgente: crear también en coci_ordenes ─────
        if ($origen === 'urgente') {
            $etiqueta = match($tipoServicio) {
                'almuerzo'          => 'Almuerzo',
                'colacion'          => 'Colación',
                'colacion_especial' => 'Colación especial',
                default             => 'Cena',
            };
            $solicitante = $nombreEmpresa ?: $nombreContacto ?: 'Sin nombre';

            $recepcionModel = new RecepcionModel();
            $horaEntrega    = date('Y-m-d H:i:s', time() + 20 * 60);

            $ordenId = $recepcionModel->insertarOrden(
                '000',               // habitación genérica para comandas urgentes
                'Comedor',
                "URGENTE {$etiqueta}: {$solicitante}",
                $cantidadPersonas,
                0,
                $horaEntrega,
                $tipoSolicitante,
                $companyId,
                $contractId,
                $nombreContacto,
                null
            );
            $recepcionModel->insertarDetalleOrden($ordenId, $etiqueta, 0, $cantidadPersonas);

            // Vincular orden a la comanda
            $model->actualizar(
                $comandaId,
                $cantidadPersonas, $horaServicio, $observaciones, 0,
                $nombreHotel, $nombreEmpresa, $nombreContacto
            );
        }

        header("Location: index.php?page=voucher/clientes/{$comandaId}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // GUARDAR DESAYUNOS (POST multi-día)
    // ─────────────────────────────────────────────────────────

    public function guardarDesayuno(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/desayuno');
            exit;
        }

        // Validar fechas recibidas
        $fechas = array_filter(
            array_map('trim', (array)($_POST['fechas'] ?? [])),
            fn($f) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)
        );

        if (empty($fechas)) {
            header('Location: index.php?page=comanda/desayuno&error=sin_fechas');
            exit;
        }

        // Campos comunes
        $nombreHotel      = in_array($_POST['nombre_hotel'] ?? '', ['Atankalama', 'Atankalama Inn'])
                            ? $_POST['nombre_hotel'] : 'Atankalama';
        $cantidadPersonas = max(1, (int)($_POST['cantidad_personas'] ?? 1));
        $esParaLlevar     = ($_POST['es_para_llevar'] ?? '0') === '1' ? 1 : 0;
        $observaciones    = trim($_POST['observaciones'] ?? '') ?: null;

        // Solicitante
        $tipoSolicitante = ($_POST['tipo_solicitante'] ?? '') === 'empresa' ? 'empresa' : 'particular';
        $companyId       = null;
        $contractId      = null;
        $nombreEmpresa   = null;
        $nombreContacto  = null;

        if ($tipoSolicitante === 'empresa') {
            $companyId      = intval($_POST['company_id']    ?? 0) ?: null;
            $contractId     = intval($_POST['contract_id']   ?? 0) ?: null;
            $nombreEmpresa  = trim($_POST['nombre_empresa']  ?? '') ?: null;
            $nombreContacto = trim($_POST['nombre_contacto'] ?? '') ?: null;
        } else {
            $nombreContacto = trim($_POST['nombre_contacto'] ?? '') ?: null;
        }

        $model = new ComandaModel();
        
        // Crear reserva automática si es un rango
        $reservaId = null;
        if (count($fechas) > 1) {
            $rm = new ReservaModel();
            $labelReserva = ($nombreEmpresa ?: $nombreContacto ?: 'Sin nombre') . " (Multi-Día)";
            $reservaId = $rm->crear(
                $labelReserva,
                min($fechas),
                max($fechas),
                $companyId,
                $nombreEmpresa,
                $observaciones
            );
        }

        $ids   = $model->insertarRango(
            array_values($fechas),
            'desayuno',
            $nombreHotel,
            $tipoSolicitante,
            $companyId,
            $contractId,
            $nombreEmpresa,
            $nombreContacto,
            $cantidadPersonas,
            null,
            $observaciones,
            $esParaLlevar,
            $reservaId
        );

        // Procesar archivos para cada comanda del rango
        foreach ($ids as $comandaId) {
            $this->procesarRespaldos($comandaId);
        }

        header("Location: index.php?page=voucher/clientes/{$ids[0]}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // GUARDAR MULTI-DÍA — Cenas / Colaciones / Especiales
    // ─────────────────────────────────────────────────────────

    public function guardarMulti(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=comanda/listado');
            exit;
        }

        $tiposValidos = ['cena', 'colacion', 'colacion_especial', 'almuerzo'];
        $tipoServicio = in_array($_POST['tipo_servicio'] ?? '', $tiposValidos)
                        ? $_POST['tipo_servicio'] : 'cena';

        // Validar fechas
        $fechas = array_filter(
            array_map('trim', (array)($_POST['fechas'] ?? [])),
            fn($f) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)
        );

        if (empty($fechas)) {
            $ruta = $tipoServicio === 'colacion_especial' ? 'comanda/especial' : 'comanda/cena';
            header("Location: index.php?page={$ruta}&error=sin_fechas");
            exit;
        }

        $nombreHotel      = in_array($_POST['nombre_hotel'] ?? '', ['Atankalama', 'Atankalama Inn'])
                            ? $_POST['nombre_hotel'] : 'Atankalama';
        $cantidadPersonas = max(1, (int)($_POST['cantidad_personas'] ?? 1));
        $horaServicio     = !empty($_POST['hora_servicio']) ? $_POST['hora_servicio'] : null;
        $observaciones    = trim($_POST['observaciones'] ?? '') ?: null;
        $origen           = ($_POST['origen'] ?? '') === 'urgente' ? 'urgente' : 'programada';

        $tipoSolicitante = ($_POST['tipo_solicitante'] ?? '') === 'empresa' ? 'empresa' : 'particular';
        $companyId = $contractId = null;
        $nombreEmpresa = $nombreContacto = null;

        if ($tipoSolicitante === 'empresa') {
            $companyId      = intval($_POST['company_id']    ?? 0) ?: null;
            $contractId     = intval($_POST['contract_id']   ?? 0) ?: null;
            $nombreEmpresa  = trim($_POST['nombre_empresa']  ?? '') ?: null;
            $nombreContacto = trim($_POST['nombre_contacto'] ?? '') ?: null;
        } else {
            $nombreContacto = trim($_POST['nombre_contacto'] ?? '') ?: null;
        }

        $projectId = intval($_POST['project_id'] ?? 0) ?: null;

        $model = new ComandaModel();
        $hoy   = date('Y-m-d');
        $ids   = [];

        // Crear reserva automática si es un rango
        $reservaId = null;
        if (count($fechas) > 1) {
            $rm = new ReservaModel();
            $labelReserva = ($nombreEmpresa ?: $nombreContacto ?: 'Sin nombre') . " (Multi-Día {$tipoServicio})";
            $reservaId = $rm->crear(
                $labelReserva,
                min($fechas),
                max($fechas),
                $companyId,
                $nombreEmpresa,
                $observaciones
            );
        }

        foreach (array_values($fechas) as $fecha) {
            // Urgente solo aplica a fechas de hoy
            $origenFecha = ($origen === 'urgente' && $fecha === $hoy) ? 'urgente' : 'programada';

            $comandaId = $model->insertar(
                $fecha, $tipoServicio, $nombreHotel, $tipoSolicitante,
                $companyId, $contractId, $nombreEmpresa, $nombreContacto,
                $cantidadPersonas, $horaServicio, $observaciones, 0, $origenFecha,
                null, $reservaId, $projectId
            );
            $ids[] = $comandaId;

            // Procesar archivos de respaldo para cada día
            $this->procesarRespaldos($comandaId);

            // Si urgente y es hoy, crear también orden en cola de cocina
            if ($origenFecha === 'urgente') {
                $etiqueta = match($tipoServicio) {
                    'almuerzo'          => 'Almuerzo',
                    'colacion'          => 'Colación',
                    'colacion_especial' => 'Colación especial',
                    default             => 'Cena',
                };
                $solicitante    = $nombreEmpresa ?: $nombreContacto ?: 'Sin nombre';
                $recepcionModel = new RecepcionModel();
                $horaEntrega    = date('Y-m-d H:i:s', time() + 20 * 60);
                $ordenId        = $recepcionModel->insertarOrden(
                    '000', 'Comedor',
                    "URGENTE {$etiqueta}: {$solicitante}",
                    $cantidadPersonas, 0, $horaEntrega,
                    $tipoSolicitante, $companyId, $contractId, $nombreContacto, null
                );
                $recepcionModel->insertarDetalleOrden($ordenId, $etiqueta, 0, $cantidadPersonas);
            }
        }

        header("Location: index.php?page=voucher/clientes/{$ids[0]}");
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // LISTADO
    // ─────────────────────────────────────────────────────────

    public function listado(): void
    {
        $fecha  = $_GET['fecha'] ?? date('Y-m-d');
        $model  = new ComandaModel();
        $comandas = $model->obtenerPorFecha($fecha);

        $empresaModel  = new EmpresaModel();
        $empresasLista = $empresaModel->listarEmpresasActivas();

        foreach ($comandas as &$c) {
            $c['nombre_empresa_oficial'] = null;
            if (!empty($c['company_id'])) {
                $emp = $empresaModel->obtenerEmpresa($c['company_id']);
                $c['nombre_empresa_oficial'] = $emp['business_name'] ?? null;
            }
        }
        unset($c);

        $ids           = array_column($comandas, 'id');
        $voucherCounts = $model->contarVouchersPorIds($ids);
        foreach ($comandas as &$c) {
            $c['voucher_count']    = $voucherCounts[$c['id']]['total']    ?? 0;
            $c['voucher_impresos'] = $voucherCounts[$c['id']]['impresos'] ?? 0;
        }
        unset($c);

        // Desayunos masivos para la fecha seleccionada
        $dm              = new DesayunoMasivoModel();
        $masivoAtan      = $dm->obtenerPorFechaHotel($fecha, 'Atankalama');
        $masivoInn       = $dm->obtenerPorFechaHotel($fecha, 'Atankalama Inn');
        $totalesMasivo   = $dm->totalesPorHotel($fecha);
        $totalMasivoAtan = $totalesMasivo['Atankalama']     ?? 0;
        $totalMasivoInn  = $totalesMasivo['Atankalama Inn'] ?? 0;

        require_once __DIR__ . '/../views/comanda/listado.php';
    }

    // ─────────────────────────────────────────────────────────
    // IMPRIMIR (vista limpia sin botones)
    // ─────────────────────────────────────────────────────────

    public function imprimir(): void
    {
        $fecha    = $_GET['fecha'] ?? date('Y-m-d');
        $model    = new ComandaModel();
        $comandas = $model->obtenerPorFecha($fecha);

        $empresaModel = new EmpresaModel();
        foreach ($comandas as &$c) {
            $c['nombre_empresa_oficial'] = null;
            if (!empty($c['company_id'])) {
                $emp = $empresaModel->obtenerEmpresa($c['company_id']);
                $c['nombre_empresa_oficial'] = $emp['business_name'] ?? null;
            }
        }
        unset($c);

        require_once __DIR__ . '/../views/comanda/imprimir.php';
    }

    // ─────────────────────────────────────────────────────────
    // ELIMINAR
    // ─────────────────────────────────────────────────────────

    public function eliminar(): void
    {
        $id    = intval($_POST['id'] ?? 0);
        $fecha = $_POST['fecha'] ?? date('Y-m-d');

        if ($id > 0) {
            (new ComandaModel())->eliminar($id);
        }

        header('Location: index.php?page=comanda/listado&fecha=' . urlencode($fecha));
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // AJAX: Crear empresa
    // ─────────────────────────────────────────────────────────

    public function crearEmpresaAjax(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $business_name = trim($_POST['business_name'] ?? '');
        if (empty($business_name)) {
            echo json_encode(['success' => false, 'message' => 'El nombre de la empresa es obligatorio']);
            exit;
        }

        $datos = [
            'rut'            => trim($_POST['rut'] ?? ''),
            'business_name'  => $business_name,
            'trade_name'     => trim($_POST['trade_name'] ?? ''),
            'contact_name'   => trim($_POST['contact_name'] ?? ''),
            'contact_email'  => trim($_POST['contact_email'] ?? ''),
            'contact_phone'  => trim($_POST['contact_phone'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'city'           => trim($_POST['city'] ?? ''),
            'type'           => 'cliente',
            'notes'          => 'Creado desde módulo Cocina',
        ];

        $project_name = trim($_POST['project_name'] ?? '');

        try {
            $empresaModel = new EmpresaModel();
            $id = $empresaModel->crearEmpresa($datos);

            $project_id = null;
            if ($project_name !== '') {
                $pdo = TicketsDatabase::getInstance();
                $stmt = $pdo->prepare('INSERT INTO doc_projects (company_id, name, active) VALUES (?, ?, 1)');
                $stmt->execute([$id, $project_name]);
                $project_id = (int) $pdo->lastInsertId();
            }

            echo json_encode([
                'success'       => true,
                'id'            => $id,
                'business_name' => $business_name,
                'contact_name'  => $datos['contact_name'],
                'project_id'    => $project_id,
                'project_name'  => $project_name,
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al crear empresa: ' . $e->getMessage()]);
        }
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // GESTIÓN DE RESPALDOS (Archivos)
    // ─────────────────────────────────────────────────────────

    private function procesarRespaldos(int $comandaId): void
    {
        if (empty($_FILES['respaldos']['name'][0])) {
            return;
        }

        $model = new ComandaModel();
        $files = $_FILES['respaldos'];
        
        // Estructura de carpetas: uploads/comandos/YYYY/MM/DD
        $subPath    = 'uploads/comandos/' . date('Y/m/d');
        $uploadBase = __DIR__ . '/../../public/';
        $targetDir  = $uploadBase . $subPath;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        foreach ($files['name'] as $i => $originalName) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmpPath = $files['tmp_name'][$i];
            $ext     = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $baseName = 'comanda_' . $comandaId . '_' . uniqid();
            
            $esImagen = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            
            if ($esImagen) {
                // Conversión a WebP
                $newFileName = $baseName . '.webp';
                $finalPath   = $targetDir . '/' . $newFileName;
                
                if ($this->convertToWebp($tmpPath, $finalPath, $ext)) {
                    $model->registrarRespaldo($comandaId, $subPath . '/' . $newFileName, 'image/webp');
                }
            } else {
                // Otros archivos (PDF, MSG, etc.)
                $newFileName = $baseName . '.' . $ext;
                $finalPath   = $targetDir . '/' . $newFileName;
                
                if (move_uploaded_file($tmpPath, $finalPath)) {
                    $mimeType = $files['type'][$i];
                    $model->registrarRespaldo($comandaId, $subPath . '/' . $newFileName, $mimeType);
                }
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // AJAX: Proyectos por empresa
    // ─────────────────────────────────────────────────────────

    public function proyectosEmpresa(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $companyId = (int)($_GET['company_id'] ?? 0);
        if (!$companyId) { echo json_encode([]); exit; }
        $proyectos = (new EmpresaModel())->listarProyectosPorEmpresa($companyId);
        echo json_encode($proyectos);
        exit;
    }

    private function convertToWebp(string $source, string $destination, string $ext): bool
    {
        try {
            $image = match ($ext) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($source),
                'png'         => @imagecreatefrompng($source),
                'gif'         => @imagecreatefromgif($source),
                'webp'        => @imagecreatefromwebp($source),
                default       => false,
            };

            if (!$image) return false;

            // Mantener transparencia si es PNG o WebP original
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);

            $success = imagewebp($image, $destination, 80); // 80% calidad
            imagedestroy($image);
            
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }
}
