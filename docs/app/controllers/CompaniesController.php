<?php
/**
 * Controller de Empresas.
 *
 * Gestiona las acciones CRUD para empresas clientes y proveedores.
 * Requiere permisos: companies_view, companies_create, companies_edit, companies_delete.
 *
 * @package App\Controllers
 */
class CompaniesController extends Controller
{
    /**
     * Lista todas las empresas activas.
     * Permiso requerido: companies_view
     */
    public function index()
    {
        PermissionMiddleware::check('companies_view');

        $model = new CompanyModel();
        $filters = [];

        if (!empty($_GET['type'])) {
            $filters['type'] = $_GET['type'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $companies = $model->getAll($filters);
        $totalClientes = $model->count('cliente');
        $totalProveedores = $model->count('proveedor');

        $this->view('companies/index', compact('companies', 'totalClientes', 'totalProveedores', 'filters'));
    }
    // Fin de la función index()

    /**
     * Muestra formulario de creación.
     * Permiso requerido: companies_create
     */
    public function create()
    {
        PermissionMiddleware::check('companies_create');

        $company = [];
        $isEdit = false;
        $this->view('companies/form', compact('company', 'isEdit'));
    }
    // Fin de la función create()

    /**
     * Guarda una nueva empresa.
     * Permiso requerido: companies_create
     */
    public function store()
    {
        PermissionMiddleware::check('companies_create');
        csrf_verify();

        // ===============================
        // RECOGER Y VALIDAR DATOS
        // ===============================
        $data = $this->collectFormData();

        if (empty($data['business_name'])) {
            $_SESSION['flash_error'] = 'La razón social es obligatoria';
            $this->redirect('/companies/create');
            return;
        }

        // ===============================
        // GUARDAR EN BASE DE DATOS
        // ===============================
        $model = new CompanyModel();
        $id = $model->create($data, AuthService::userId());

        if ($id) {
            (new AuditModel())->add(AuthService::userId(), 'empresas', 'crear', "Empresa creada: {$data['business_name']}");
            $_SESSION['flash_success'] = 'Empresa creada exitosamente';
            $this->redirect('/companies/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al crear la empresa';
            $this->redirect('/companies/create');
        }
    }
    // Fin de la función store()

    /**
     * Muestra el detalle de una empresa.
     * Permiso requerido: companies_view
     *
     * @param int $id ID de la empresa
     */
    public function show($id)
    {
        PermissionMiddleware::check('companies_view');

        $model = new CompanyModel();
        $company = $model->getById((int)$id);

        if (!$company) {
            $_SESSION['flash_error'] = 'Empresa no encontrada';
            $this->redirect('/companies');
            return;
        }

        // Obtener contratos de esta empresa
        $contractModel = new ContractModel();
        $contracts = $contractModel->getAll(['company_id' => (int)$id]);

        $paymentModel = new PaymentModel();
        foreach ($contracts as &$c) {
            $charged = $paymentModel->getTotalCharged((int)$c['id']);
            $paid = $paymentModel->getTotalPaid((int)$c['id']);
            $c['saldo'] = $charged - $paid;
        }
        unset($c);

        // Servicios de alimentación (cocina)
        $filtrosServicio = [
            'tipo_servicio' => $_GET['tipo_servicio'] ?? '',
            'cobrado'       => $_GET['cobrado']       ?? '',
            'fecha_desde'   => $_GET['fecha_desde']   ?? '',
            'fecha_hasta'   => $_GET['fecha_hasta']   ?? '',
            'sin_contrato'  => !empty($_GET['sin_contrato']),
        ];

        $filtrosMasivo = [
            'fecha_desde' => $filtrosServicio['fecha_desde'],
            'fecha_hasta' => $filtrosServicio['fecha_hasta'],
        ];

        try {
            $cocinaModel           = new CocinaServicioModel();
            $serviciosAlimentacion = $cocinaModel->getByCompany((int)$id, $filtrosServicio);
            $resumenServicios      = $cocinaModel->resumenByCompany((int)$id);
            $desayunosMasivos      = $cocinaModel->getMasivosByCompany((int)$id, $filtrosMasivo);
            $resumenMasivos        = $cocinaModel->resumenMasivosByCompany((int)$id, $filtrosMasivo);
        } catch (Exception $e) {
            $serviciosAlimentacion = [];
            $resumenServicios      = ['total' => 0, 'cobrado' => 0, 'pendiente' => 0, 'total_personas' => 0, 'sin_contrato' => 0];
            $desayunosMasivos      = [];
            $resumenMasivos        = ['total_registros' => 0, 'total_pax' => 0, 'atan' => 0, 'inn' => 0];
        }

        $this->view('companies/show', compact('company', 'contracts', 'serviciosAlimentacion', 'resumenServicios', 'filtrosServicio', 'desayunosMasivos', 'resumenMasivos', 'filtrosMasivo'));
    }
    // Fin de la función show()

    /**
     * Exporta los servicios de alimentación a formato CSV (Excel).
     * Incluye los nombres de las personas asociadas si están disponibles.
     *
     * @param int $id ID de la empresa
     */
    public function exportAlimentacion($id)
    {
        PermissionMiddleware::check('companies_view');

        $model = new CompanyModel();
        $company = $model->getById((int)$id);

        if (!$company) {
            die("Empresa no encontrada");
        }

        // Filtros (mismos que en show)
        $filters = [
            'tipo_servicio' => $_GET['tipo_servicio'] ?? '',
            'cobrado'       => $_GET['cobrado']       ?? '',
            'fecha_desde'   => $_GET['fecha_desde']   ?? '',
            'fecha_hasta'   => $_GET['fecha_hasta']   ?? '',
            'sin_contrato'  => !empty($_GET['sin_contrato']),
        ];

        $cocinaModel = new CocinaServicioModel();
        $servicios = $cocinaModel->getByCompany((int)$id, $filters);

        // Obtener nombres de huéspedes (batch)
        $colacionModel = new ColacionModel();
        $guestBatch = $colacionModel->getNamesBatch((int)$id, $company['business_name'], $filters['fecha_desde'], $filters['fecha_hasta']);

        // Mapeo de tipos para el batch
        $tipoMapping = [
            'desayuno'          => 1,
            'colacion'          => 2,
            'cena'              => 3,
            'colacion_especial' => 4
        ];

        // Preparar CSV
        $filename = "alimentacion_" . strtolower(str_replace(' ', '_', $company['business_name'])) . "_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        // BOM para Excel (UTF-8)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, [
            'Fecha',
            'Tipo de Servicio',
            'Hotel',
            'Cant. Personas',
            'Hora',
            'Contacto',
            'Observaciones',
            'Contrato ID',
            'Estado Cobro',
            'Nombres de Personas'
        ], ';');

        foreach ($servicios as $s) {
            $fecha = $s['fecha'];
            $tipoId = $tipoMapping[$s['tipo_servicio']] ?? 0;
            $nombres = $guestBatch[$fecha][$tipoId] ?? [];
            $nombresStr = !empty($nombres) ? implode(', ', $nombres) : 'No registrados';

            fputcsv($output, [
                date('d/m/Y', strtotime($s['fecha'])),
                ucfirst($s['tipo_servicio']),
                $s['nombre_hotel'],
                $s['cantidad_personas'],
                $s['hora_servicio'] ? substr($s['hora_servicio'], 0, 5) : '-',
                $s['nombre_contacto'] ?? '-',
                $s['observaciones'] ?? '-',
                $s['contract_id'] ?? '-',
                $s['cobrado'] ? 'Cobrado' : 'Pendiente',
                $nombresStr
            ], ';');
        }

        fclose($output);
        exit;
    }
    // Fin de la función exportAlimentacion()

    /**
     * Alterna el estado cobrado de un servicio de alimentación (AJAX).
     * Permiso requerido: companies_view
     *
     * @param int $id ID de coci_comandas
     */
    public function toggleCobrado($id)
    {
        PermissionMiddleware::check('companies_view');
        header('Content-Type: application/json; charset=utf-8');

        $id = (int)$id;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        try {
            $model = new CocinaServicioModel();
            $model->toggleCobrado($id);
            $estado = $model->getEstadoCobrado($id);
            echo json_encode([
                'success'    => true,
                'cobrado'    => (int)($estado['cobrado'] ?? 0),
                'cobrado_at' => $estado['cobrado_at'] ?? null,
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
        exit;
    }
    // Fin de la función toggleCobrado()

    /**
     * Muestra formulario de edición.
     * Permiso requerido: companies_edit
     *
     * @param int $id ID de la empresa
     */
    public function edit($id)
    {
        PermissionMiddleware::check('companies_edit');

        $model = new CompanyModel();
        $company = $model->getById((int)$id);

        if (!$company) {
            $_SESSION['flash_error'] = 'Empresa no encontrada';
            $this->redirect('/companies');
            return;
        }

        $isEdit = true;
        $this->view('companies/form', compact('company', 'isEdit'));
    }
    // Fin de la función edit()

    /**
     * Actualiza una empresa existente.
     * Permiso requerido: companies_edit
     *
     * @param int $id ID de la empresa
     */
    public function update($id)
    {
        PermissionMiddleware::check('companies_edit');
        csrf_verify();

        $model = new CompanyModel();
        $company = $model->getById((int)$id);

        if (!$company) {
            $_SESSION['flash_error'] = 'Empresa no encontrada';
            $this->redirect('/companies');
            return;
        }

        $data = $this->collectFormData();

        if (empty($data['business_name'])) {
            $_SESSION['flash_error'] = 'La razón social es obligatoria';
            $this->redirect('/companies/edit/' . $id);
            return;
        }

        if ($model->update((int)$id, $data)) {
            (new AuditModel())->add(AuthService::userId(), 'empresas', 'editar', "Empresa editada: {$data['business_name']} (ID: {$id})");
            $_SESSION['flash_success'] = 'Empresa actualizada exitosamente';
            $this->redirect('/companies/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar la empresa';
            $this->redirect('/companies/edit/' . $id);
        }
    }
    // Fin de la función update()

    /**
     * Soft delete de una empresa.
     * Permiso requerido: companies_delete
     *
     * @param int $id ID de la empresa
     */
    public function delete($id)
    {
        PermissionMiddleware::check('companies_delete');
        csrf_verify();

        $model = new CompanyModel();
        $company = $model->getById((int)$id);

        if (!$company) {
            $_SESSION['flash_error'] = 'Empresa no encontrada';
            $this->redirect('/companies');
            return;
        }

        if ($model->delete((int)$id)) {
            (new AuditModel())->add(AuthService::userId(), 'empresas', 'eliminar', "Empresa eliminada: {$company['business_name']} (ID: {$id})");
            $_SESSION['flash_success'] = 'Empresa eliminada exitosamente';
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar la empresa';
        }

        $this->redirect('/companies');
    }
    // Fin de la función delete()

    /**
     * Exporta resumen de alimentación por hotel: una hoja Excel por hotel,
     * filas = tipos de servicio, columnas = días, valores = total de personas.
     * Rango de fechas requerido via GET (fecha_desde, fecha_hasta).
     *
     * @param int $id ID de la empresa
     */
    public function exportResumenHoteles($id)
    {
        PermissionMiddleware::check('companies_view');

        $id      = (int)$id;
        $model   = new CompanyModel();
        $company = $model->getById($id);

        if (!$company) {
            die("Empresa no encontrada");
        }

        $fechaDesde = trim($_GET['fecha_desde'] ?? '');
        $fechaHasta = trim($_GET['fecha_hasta'] ?? '');

        if (!$fechaDesde || !$fechaHasta || $fechaDesde > $fechaHasta) {
            $_SESSION['flash_error'] = 'Debe indicar un rango de fechas válido para exportar el resumen.';
            $this->redirect('/companies/show/' . $id);
            return;
        }

        $cocinaModel = new CocinaServicioModel();
        $filas       = $cocinaModel->getResumenPorHotelFecha($id, $fechaDesde, $fechaHasta);

        if (empty($filas)) {
            $_SESSION['flash_error'] = 'No hay servicios de alimentación en el rango de fechas indicado.';
            $this->redirect('/companies/show/' . $id);
            return;
        }

        // Estructurar datos: [hotel][fecha][tipo] = total
        $porHotel   = [];
        $fechasSet  = [];
        $tiposSet   = [];

        foreach ($filas as $f) {
            $hotel = $f['nombre_hotel'];
            $fecha = $f['fecha'];
            $tipo  = $f['tipo_servicio'];

            $porHotel[$hotel][$fecha][$tipo] = (int)$f['total'];
            $fechasSet[$fecha] = true;
            $tiposSet[$tipo]   = true;
        }

        ksort($fechasSet);
        $fechas = array_keys($fechasSet);

        // Orden preferido de tipos de servicio
        $tiposOrden = ['desayuno', 'colacion', 'colacion_especial', 'cena'];
        $tiposLabel = [
            'desayuno'          => 'Desayuno',
            'cena'              => 'Cena',
            'colacion'          => 'Colación',
            'colacion_especial' => 'Colación Especial',
        ];
        $tiposPresentes  = array_keys($tiposSet);
        $tiposOrdenados  = array_merge(
            array_filter($tiposOrden, function ($t) use ($tiposPresentes) { return in_array($t, $tiposPresentes); }),
            array_filter($tiposPresentes, function ($t) use ($tiposOrden) { return !in_array($t, $tiposOrden); })
        );

        // Generar Excel con XlsxWriter (nativo PHP, sin dependencias externas)
        $xlsx = new XlsxWriter();

        foreach ($porHotel as $hotelNombre => $hotelData) {
            // Fila 1: [Nombre hotel] [dd-mm-yy] [dd-mm-yy] ...
            $cabecera = [mb_strtoupper($hotelNombre)];
            foreach ($fechas as $fecha) {
                $cabecera[] = date('d-m-y', strtotime($fecha));
            }

            $sheetRows = [$cabecera];

            // Filas de datos: [Tipo servicio] [total|null] [total|null] ...
            foreach ($tiposOrdenados as $tipo) {
                $label = $tiposLabel[$tipo] ?? ucfirst(str_replace('_', ' ', $tipo));
                $fila  = [$label];
                foreach ($fechas as $fecha) {
                    $val    = $hotelData[$fecha][$tipo] ?? 0;
                    $fila[] = $val > 0 ? (int)$val : null;
                }
                $sheetRows[] = $fila;
            }

            $xlsx->addSheet($hotelNombre, $sheetRows, 1);
        }

        $nombreArchivo = 'resumen_'
            . strtolower(preg_replace('/\s+/', '_', $company['business_name']))
            . '_' . date('Ymd') . '.xlsx';

        $xlsx->download($nombreArchivo);
    }

    /**
     * Recopila los datos del formulario de empresa.
     *
     * @return array Datos sanitizados del formulario
     */
    private function collectFormData()
    {
        return [
            'rut'           => trim($_POST['rut'] ?? ''),
            'business_name' => trim($_POST['business_name'] ?? ''),
            'trade_name'    => trim($_POST['trade_name'] ?? ''),
            'contact_name'  => trim($_POST['contact_name'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'address'       => trim($_POST['address'] ?? ''),
            'city'          => trim($_POST['city'] ?? ''),
            'type'          => trim($_POST['type'] ?? 'cliente'),
            'notes'         => trim($_POST['notes'] ?? ''),
        ];
    }
    // Fin de la función collectFormData()
}
