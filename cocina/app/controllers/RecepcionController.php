<?php
require_once __DIR__ . '/../models/RecepcionModel.php';
require_once __DIR__ . '/../models/EmpresaModel.php';

class RecepcionController
{

    // Mostrar formulario de nueva solicitud (combinado — mantenido por compatibilidad)
    public function index()
    {
        require_once __DIR__ . '/../models/ProductoModel.php';
        $productoModel   = new ProductoModel();
        $productosLista  = $productoModel->obtenerActivos();

        $empresaModel    = new EmpresaModel();
        $empresasLista   = $empresaModel->listarEmpresasActivas();

        require_once __DIR__ . '/../views/recepcion/index.php';
    }

    // Formulario exclusivo para órdenes de Particulares / Huéspedes
    public function particular()
    {
        require_once __DIR__ . '/../models/ProductoModel.php';
        $productoModel  = new ProductoModel();
        $productosLista = $productoModel->obtenerActivos();

        require_once __DIR__ . '/../views/recepcion/particular.php';
    }

    // Formulario exclusivo para órdenes de Empresa / Organización
    public function empresa()
    {
        require_once __DIR__ . '/../models/ProductoModel.php';
        $productoModel  = new ProductoModel();
        $productosLista = $productoModel->obtenerActivos();

        $empresaModel  = new EmpresaModel();
        $empresasLista = $empresaModel->listarEmpresasActivas();

        require_once __DIR__ . '/../views/recepcion/empresa.php';
    }

    public function listado()
    {
        $model   = new RecepcionModel();
        $ordenes = $model->obtenerOrdenes();

        // Enriquecer órdenes de empresa con nombre de la compañía
        $empresaModel = new EmpresaModel();
        foreach ($ordenes as &$o) {
            $o['nombre_empresa'] = null;
            if (!empty($o['company_id'])) {
                $emp = $empresaModel->obtenerEmpresa($o['company_id']);
                $o['nombre_empresa'] = $emp ? $emp['business_name'] : null;
            }
        }
        unset($o);

        require_once __DIR__ . '/../views/recepcion/listado.php';
    }

    // AJAX: devuelve contratos de una empresa en JSON
    public function contratosEmpresa()
    {
        header('Content-Type: application/json; charset=utf-8');
        $company_id = intval($_GET['company_id'] ?? 0);
        if (!$company_id) {
            echo json_encode([]);
            exit;
        }
        $empresaModel = new EmpresaModel();
        $contratos    = $empresaModel->listarContratosPorEmpresa($company_id);
        echo json_encode($contratos);
        exit;
    }

    public function crear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=recepcion/index');
            exit;
        }

        // ── Hora de entrega ──────────────────────────────────────────────────
        $horaUsuario      = $_POST['hora_entrega'] ?? '';
        $ahora            = new DateTime();
        $minutosAdicionales = 20;

        if ($horaUsuario) {
            $horaEntrega  = new DateTime($horaUsuario);
            $ahoraMas20   = clone $ahora;
            $ahoraMas20->modify("+{$minutosAdicionales} minutes");
            if ($horaEntrega < $ahoraMas20) {
                $horaEntrega = $ahoraMas20;
            }
        } else {
            $horaEntrega  = $ahora->modify("+{$minutosAdicionales} minutes");
        }
        $fechaHoraEntrega = $horaEntrega->format('Y-m-d H:i:s');

        // ── Campos comunes ───────────────────────────────────────────────────
        $habitacion        = trim($_POST['habitacion']);
        $lugar             = $_POST['lugar'];
        $cantidad_personas = max(1, intval($_POST['cantidad_personas']));
        $pagado            = isset($_POST['pagado']) ? 1 : 0;
        $voucher           = !empty($_POST['boucher']) ? trim($_POST['boucher']) : null;

        // ── Tipo de solicitante ──────────────────────────────────────────────
        $tipo_solicitante  = in_array($_POST['tipo_solicitante'] ?? '', ['particular', 'empresa'])
                             ? $_POST['tipo_solicitante']
                             : 'particular';

        $nombre_huesped    = null;
        $company_id        = null;
        $contract_id       = null;
        $nombre_contacto   = null;
        $observaciones     = null;

        if ($tipo_solicitante === 'empresa') {
            $company_id      = intval($_POST['company_id'] ?? 0) ?: null;
            $contract_id     = intval($_POST['contract_id'] ?? 0) ?: null;
            $nombre_contacto = !empty($_POST['nombre_contacto']) ? trim($_POST['nombre_contacto']) : null;
            $observaciones   = !empty($_POST['observaciones'])   ? trim($_POST['observaciones'])   : null;
            $nombre_huesped  = $nombre_contacto;
        } else {
            $nombre_huesped  = !empty($_POST['nombre_huesped']) ? trim($_POST['nombre_huesped']) : null;
            $observaciones   = !empty($_POST['observaciones'])   ? trim($_POST['observaciones'])   : null;
        }

        // ── Productos (Nuevo formato: [nombre => ['precio' => X, 'cantidad' => Y]]) ──
        $productos_post = $_POST['productos'] ?? [];
        $otros_desc     = trim($_POST['otros_desc'] ?? '');
        $otros_precio   = floatval($_POST['otros_precio'] ?? 0);

        $detalles = [];
        $total    = 0;

        foreach ($productos_post as $nombre => $datos) {
            $cantidad = intval($datos['cantidad'] ?? 0);
            if ($cantidad <= 0) continue;
            
            $precio     = floatval($datos['precio'] ?? 0);
            $detalles[] = [
                'producto' => $nombre, 
                'precio'   => $precio, 
                'cantidad' => $cantidad
            ];
            $total += ($precio * $cantidad);
        }

        if ($otros_desc && $otros_precio > 0) {
            $detalles[] = ['producto' => $otros_desc, 'precio' => $otros_precio, 'cantidad' => 1];
            $total      += $otros_precio;
        }

        // ── Guardar orden ────────────────────────────────────────────────────
        $model    = new RecepcionModel();
        $orden_id = $model->insertarOrden(
            $habitacion, $lugar, $nombre_huesped, $cantidad_personas, $total, $fechaHoraEntrega,
            $tipo_solicitante, $company_id, $contract_id, $nombre_contacto, $observaciones,
            $pagado, $voucher
        );

        foreach ($detalles as $detalle) {
            $model->insertarDetalleOrden($orden_id, $detalle['producto'], $detalle['precio'], $detalle['cantidad']);
        }

        // ── Archivo de respaldo (WhatsApp / correo) ──────────────────────────
        if (!empty($_FILES['archivo_respaldo']['tmp_name'])) {
            $file      = $_FILES['archivo_respaldo'];
            $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];

            if (in_array($ext, $permitidos) && $file['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/uploads/respaldos/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $nombreArchivo = 'respaldo_' . $orden_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($file['tmp_name'], $uploadDir . $nombreArchivo)) {
                    $model->actualizarArchivoRespaldo($orden_id, 'uploads/respaldos/' . $nombreArchivo);
                }
            }
        }

        header('Location: index.php?page=recepcion/imprimir&id=' . $orden_id);
        exit;
    }

    public function imprimir()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=recepcion/index');
            exit;
        }

        $model  = new RecepcionModel();
        $orden  = $model->obtenerOrdenPorId($id);
        if (!$orden) {
            header('Location: index.php?page=recepcion/index');
            exit;
        }

        $detalles      = $model->obtenerDetallesOrden($id);
        $empresa       = null;
        $contrato      = null;

        if (!empty($orden['company_id'])) {
            $empresaModel = new EmpresaModel();
            $empresa      = $empresaModel->obtenerEmpresa($orden['company_id']);
            if (!empty($orden['contract_id'])) {
                $contratos = $empresaModel->listarContratosPorEmpresa($orden['company_id']);
                foreach ($contratos as $c) {
                    if ($c['id'] == $orden['contract_id']) {
                        $contrato = $c;
                        break;
                    }
                }
            }
        }

        require_once __DIR__ . '/../views/recepcion/imprimir.php';
    }
}
