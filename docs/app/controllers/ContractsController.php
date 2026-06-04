<?php
/**
 * Controller de Contratos.
 *
 * Gestiona las acciones CRUD para contratos de arriendo/hospedaje
 * y proveedores. Incluye lógica de multi-hotel, servicios, escalas
 * de precio (tiers) y cambio de estado.
 * Requiere permisos: contracts_view, contracts_create, contracts_edit, contracts_delete.
 *
 * @package App\Controllers
 */
class ContractsController extends Controller
{
    /**
     * Lista todos los contratos activos con filtros.
     * Permiso requerido: contracts_view
     */
    public function index()
    {
        PermissionMiddleware::check('contracts_view');

        // ===============================
        // RECOGER FILTROS
        // ===============================
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['company_id'])) {
            $filters['company_id'] = (int)$_GET['company_id'];
        }
        if (!empty($_GET['contract_type'])) {
            $filters['contract_type'] = $_GET['contract_type'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        // ===============================
        // OBTENER DATOS
        // ===============================
        $contractModel = new ContractModel();

        // Excluir siempre las cotizaciones — solo aparecen en /quotations
        $filters['status_not_in'] = ['quotation_draft', 'quotation_sent', 'quotation_approved'];

        // Seguridad (IDOR): Vendedores solo ven sus propios contratos
        if (!AuthService::isAdmin()) {
            $filters['created_by'] = AuthService::userId();
        }

        $contracts = $contractModel->getAll($filters);

        // KPIs
        $kpis = [
            'total'       => $contractModel->countByStatus(),
            'vigentes'    => $contractModel->countByStatus('vigente'),
            'borradores'  => $contractModel->countByStatus('borrador'),
            'por_renovar' => $contractModel->countByStatus('por_renovar'),
            'vencidos'    => $contractModel->countByStatus('vencido'),
        ];

        // Empresas para filtro dropdown
        $companyModel = new CompanyModel();
        $companiesSelect = $companyModel->getForSelect();

        $this->view('contracts/index', compact('contracts', 'filters', 'kpis', 'companiesSelect'));
    }
    // Fin de la función index()

    /**
     * Muestra formulario de creación de contrato.
     * Permiso requerido: contracts_create
     */
    public function create()
    {
        PermissionMiddleware::check('contracts_create');

        $contract = [];
        $isEdit = false;

        // Datos para selects del formulario
        $formData = $this->getFormSelectData();

        // Preseleccionar empresa si viene de la vista de empresa
        $preselectedCompanyId = !empty($_GET['company_id']) ? (int)$_GET['company_id'] : null;

        $selectedHotels = [];
        $selectedServices = [];
        $tiers = [];

        $this->view('contracts/form', compact(
            'contract', 'isEdit', 'formData',
            'preselectedCompanyId', 'selectedHotels', 'selectedServices', 'tiers'
        ));
    }
    // Fin de la función create()

    /**
     * Guarda un nuevo contrato con hoteles, servicios y escalas.
     * Permiso requerido: contracts_create
     */
    public function store()
    {
        PermissionMiddleware::check('contracts_create');
        csrf_verify();

        // ===============================
        // RECOGER Y VALIDAR DATOS
        // ===============================
        $data = $this->collectFormData();
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $this->redirect('/contracts/create');
            return;
        }

        // ===============================
        // GUARDAR CONTRATO
        // ===============================
        $contractModel = new ContractModel();
        $contractId = $contractModel->create($data, AuthService::userId());

        if (!$contractId) {
            $_SESSION['flash_error'] = 'Error al crear el contrato';
            $this->redirect('/contracts/create');
            return;
        }

        // ===============================
        // SINCRONIZAR RELACIONES
        // ===============================
        $this->syncRelations($contractId, $data);

        // ===============================
        // HISTORIAL + AUDITORÍA
        // ===============================
        (new ContractHistoryModel())->add($contractId, AuthService::userId(), 'creado', "Contrato creado");
        (new AuditModel())->add(AuthService::userId(), 'contratos', 'crear', "Contrato creado (ID: {$contractId})");

        $_SESSION['flash_success'] = 'Contrato creado exitosamente';
        $this->redirect('/contracts/show/' . $contractId);
    }
    // Fin de la función store()

    /**
     * Muestra el detalle completo de un contrato.
     * Permiso requerido: contracts_view
     *
     * @param int $id ID del contrato
     */
    public function show($id)
    {
        PermissionMiddleware::check('contracts_view');

        $contractModel = new ContractModel();
        $contract = $contractModel->getById($id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado';
            $this->redirect('/contracts');
            return;
        }

        // Seguridad (IDOR): Solo admin o el creador pueden ver
        if (!AuthService::isAdmin() && (int)$contract['created_by'] !== AuthService::userId()) {
            http_response_code(403);
            die("No tiene permiso para ver este contrato");
        }

        $companyModel = new CompanyModel();
        // DATOS RELACIONADOS
        // ===============================
        $hotels   = (new HotelModel())->getByContractId((int)$id);
        $services = (new ServiceModel())->getByContractId((int)$id);
        $tiers    = (new ContractTierModel())->getByContractId((int)$id);
        $payments = (new PaymentModel())->getByContractId((int)$id);
        $history  = (new ContractHistoryModel())->getByContractId((int)$id, 0);
        $attachments = (new ContractAttachmentModel())->getByContractId((int)$id);
        $notes = (new ContractNoteModel())->getByContractId((int)$id);
        $availableServices = (new ServiceModel())->getAll();

        // Servicios de alimentación (cocina)
        $filtrosServicio = [
            'tipo_servicio' => $_GET['tipo_servicio'] ?? '',
            'cobrado'       => $_GET['cobrado']       ?? '',
            'fecha_desde'   => $_GET['fecha_desde']   ?? '',
            'fecha_hasta'   => $_GET['fecha_hasta']   ?? '',
            'sin_contrato'  => !empty($_GET['sin_contrato']),
        ];

        try {
            $cocinaModel = new CocinaServicioModel();
            $comandas    = $cocinaModel->getByCompany((int)$contract['company_id'], $filtrosServicio);
            $resumenComandas = $cocinaModel->resumenByCompany((int)$contract['company_id']);
        } catch (Exception $e) {
            $comandas = [];
            $resumenComandas = ['total' => 0, 'cobrado' => 0, 'pendiente' => 0, 'total_personas' => 0, 'sin_contrato' => 0];
        }

        // Calcular saldo
        $totalPaid = (new PaymentModel())->getTotalPaid((int)$id);
        $totalCharged = (new PaymentModel())->getTotalCharged((int)$id);
        $saldo = $totalCharged - $totalPaid;

        $this->view('contracts/show', compact(
            'contract', 'hotels', 'services', 'tiers',
            'payments', 'history', 'totalPaid', 'totalCharged', 'saldo', 'attachments', 'availableServices', 'notes',
            'comandas', 'resumenComandas', 'filtrosServicio'
        ));
    }
    // Fin de la función show()

    /**
     * Muestra formulario de edición de contrato.
     * Permiso requerido: contracts_edit
     *
     * @param int $id ID del contrato
     */
    public function edit($id)
    {
        PermissionMiddleware::check('contracts_edit');

        $contractModel = new ContractModel();
        $contract = $contractModel->getById($id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado';
            $this->redirect('/contracts');
            return;
        }

        // Seguridad (IDOR)
        if (!AuthService::isAdmin() && (int)$contract['created_by'] !== AuthService::userId()) {
            http_response_code(403);
            die("No tiene permiso para editar este contrato");
        }

        $isEdit = true;
        $formData = $this->getFormSelectData();
        $preselectedCompanyId = $contract['company_id'];

        // Obtener relaciones actuales
        $selectedHotels = array_column((new HotelModel())->getByContractId((int)$id), 'id');
        $selectedServices = array_column((new ServiceModel())->getByContractId((int)$id), 'id');
        $tiers = (new ContractTierModel())->getByContractId((int)$id);

        $this->view('contracts/form', compact(
            'contract', 'isEdit', 'formData',
            'preselectedCompanyId', 'selectedHotels', 'selectedServices', 'tiers'
        ));
    }
    // Fin de la función edit()

    /**
     * Actualiza un contrato existente.
     * Permiso requerido: contracts_edit
     *
     * @param int $id ID del contrato
     */
    public function update($id)
    {
        PermissionMiddleware::check('contracts_edit');
        csrf_verify();

        $contractModel = new ContractModel();
        $contract = $contractModel->getById((int)$id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado';
            $this->redirect('/contracts');
            return;
        }

        // Seguridad (IDOR)
        if (!AuthService::isAdmin() && (int)$contract['created_by'] !== AuthService::userId()) {
            http_response_code(403);
            die("No tiene permiso para editar este contrato");
        }

        // ===============================
        // RECOGER Y VALIDAR DATOS
        // ===============================
        $data = $this->collectFormData();
        $errors = $this->validateFormData($data);

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $this->redirect('/contracts/edit/' . $id);
            return;
        }

        // ===============================
        // ACTUALIZAR CONTRATO
        // ===============================
        if (!$contractModel->update((int)$id, $data)) {
            $_SESSION['flash_error'] = 'Error al actualizar el contrato';
            $this->redirect('/contracts/edit/' . $id);
            return;
        }

        // ===============================
        // SINCRONIZAR RELACIONES
        // ===============================
        $this->syncRelations((int)$id, $data);

        // ===============================
        // HISTORIAL + AUDITORÍA
        // ===============================
        (new ContractHistoryModel())->add((int)$id, AuthService::userId(), 'editado', "Contrato editado");
        (new AuditModel())->add(AuthService::userId(), 'contratos', 'editar', "Contrato editado: {$contract['code']} (ID: {$id})");

        $_SESSION['flash_success'] = 'Contrato actualizado exitosamente';
        $this->redirect('/contracts/show/' . $id);
    }
    // Fin de la función update()

    /**
     * Soft delete de un contrato.
     * Permiso requerido: contracts_delete
     *
     * @param int $id ID del contrato
     */
    public function delete($id)
    {
        PermissionMiddleware::check('contracts_delete');
        csrf_verify();

        $contractModel = new ContractModel();
        $contract = $contractModel->getById((int)$id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado';
            $this->redirect('/contracts');
            return;
        }

        // Seguridad (IDOR)
        if (!AuthService::isAdmin() && (int)$contract['created_by'] !== AuthService::userId()) {
            http_response_code(403);
            die("No tiene permiso para eliminar este contrato");
        }

        if ($contractModel->delete((int)$id)) {
            (new ContractHistoryModel())->add((int)$id, AuthService::userId(), 'eliminado', "Contrato eliminado");
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'eliminar', "Contrato eliminado: {$contract['code']} (ID: {$id})");
            $_SESSION['flash_success'] = 'Contrato eliminado exitosamente';
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar el contrato';
        }

        $this->redirect('/contracts');
    }
    // Fin de la función delete()

    /**
     * Cambia el estado de un contrato (vigente, vencido, cancelado, etc.).
     * Permiso requerido: contracts_edit
     *
     * @param int $id ID del contrato
     */
    public function changeStatus($id)
    {
        PermissionMiddleware::check('contracts_edit');
        csrf_verify();

        $contractModel = new ContractModel();
        $contract = $contractModel->getById((int)$id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado';
            $this->redirect('/contracts');
            return;
        }

        $newStatus = trim($_POST['status'] ?? '');
        $validStatuses = ['borrador', 'vigente', 'por_renovar', 'vencido', 'cancelado'];

        if (!in_array($newStatus, $validStatuses)) {
            $_SESSION['flash_error'] = 'Estado inválido';
            $this->redirect('/contracts/show/' . $id);
            return;
        }

        if ($contractModel->changeStatus((int)$id, $newStatus)) {
            $statusLabels = [
                'borrador'    => 'Borrador',
                'vigente'     => 'Vigente',
                'por_renovar' => 'Por renovar',
                'vencido'     => 'Vencido',
                'cancelado'   => 'Cancelado',
            ];
            $label = $statusLabels[$newStatus] ?? $newStatus;

            (new ContractHistoryModel())->add((int)$id, AuthService::userId(), 'cambio_estado', "Estado cambiado a: {$label}");
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'cambio_estado', "Estado de contrato {$contract['code']} cambiado a: {$label}");
            $_SESSION['flash_success'] = "Estado actualizado a: {$label}";
        } else {
            $_SESSION['flash_error'] = 'Error al cambiar el estado';
        }

        $this->redirect('/contracts/show/' . $id);
    }
    // Fin de la función changeStatus()

    /**
     * Sube un archivo adjunto al contrato.
     * Permiso requerido: contracts_edit
     *
     * @param int $id ID del contrato
     */
    public function uploadAttachment($id)
    {
        PermissionMiddleware::check('attachments_upload');
        csrf_verify();

        $contractModel = new ContractModel();
        $contract = $contractModel->getById((int)$id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado';
            $this->redirect('/contracts');
            return;
        }

        if (empty($_FILES['attachment']) || $_FILES['attachment']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash_error'] = 'No se seleccionó ningún archivo';
            $this->redirect('/contracts/show/' . $id);
            return;
        }

        $category = $_POST['category'] ?? 'otro';
        $uploader = new FileUploadService();
        $result = $uploader->uploadAttachment(
            $_FILES['attachment'], 
            (int)$id, 
            (int)$contract['company_id'], 
            $category, 
            AuthService::userId()
        );

        if ($result['status']) {
            (new ContractHistoryModel())->add((int)$id, AuthService::userId(), 'archivo_subido', "Archivo subido: {$result['data']['original_name']} (Categoría: {$category})");
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'upload', "Archivo subido a contrato {$contract['code']}: {$result['data']['original_name']}");
            $_SESSION['flash_success'] = 'Archivo subido correctamente';
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        $this->redirect('/contracts/show/' . $id);
    }
    // Fin de la función uploadAttachment()

    /**
     * Elimina un adjunto (soft delete).
     * Permiso requerido: contracts_edit
     *
     * @param int $id ID del adjunto
     */
    public function deleteAttachment($id)
    {
        PermissionMiddleware::check('attachments_delete');
        csrf_verify();

        $attachmentModel = new ContractAttachmentModel();
        $attachment = $attachmentModel->getById((int)$id);

        if (!$attachment) {
            $_SESSION['flash_error'] = 'Archivo no encontrado';
            $this->redirect('/contracts');
            return;
        }

        if ($attachmentModel->delete((int)$id)) {
            (new ContractHistoryModel())->add((int)$attachment['contract_id'], AuthService::userId(), 'archivo_eliminado', "Archivo eliminado: {$attachment['original_name']}");
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'delete_upload', "Archivo eliminado de contrato ID: {$attachment['contract_id']}");
            $_SESSION['flash_success'] = 'Archivo eliminado correctamente';
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar el archivo';
        }

        $this->redirect('/contracts/show/' . $attachment['contract_id']);
    }
    // Fin de la función deleteAttachment()

    /**
     * Agrega una observación al contrato.
     * Permiso requerido: contracts_view
     *
     * @param int $id ID del contrato
     */
    public function saveNote($id)
    {
        PermissionMiddleware::check('contracts_view');
        csrf_verify();

        $note = trim($_POST['note'] ?? '');
        if (empty($note)) {
            $_SESSION['flash_error'] = 'La nota no puede estar vacía';
            $this->redirect('/contracts/show/' . $id);
            return;
        }

        $noteModel = new ContractNoteModel();
        if ($noteModel->create((int)$id, AuthService::userId(), $note)) {
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'add_note', "Nota agregada en contrato ID: {$id}");
            $_SESSION['flash_success'] = 'Observación guardada';
        } else {
            $_SESSION['flash_error'] = 'Error al guardar observación';
        }

        $this->redirect('/contracts/show/' . $id);
    }

    /**
     * Elimina una observación.
     * Permiso requerido: contracts_edit (por seguridad solo edit)
     *
     * @param int $id ID de la nota
     */
    public function deleteNote($id)
    {
        PermissionMiddleware::check('contracts_edit');
        csrf_verify();

        $noteModel = new ContractNoteModel();
        // Necesitamos el contract_id para volver a redirigir
        $conn = (new Database())->connect();
        $res = $conn->query("SELECT contract_id FROM doc_contract_notes WHERE id = " . (int)$id);
        $note = $res->fetch_assoc();

        if ($note && $noteModel->delete((int)$id)) {
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'delete_note', "Nota eliminada ID: {$id}");
            $_SESSION['flash_success'] = 'Observación eliminada';
            $this->redirect('/contracts/show/' . $note['contract_id']);
        } else {
            $_SESSION['flash_error'] = 'Error al eliminar observación';
            $this->redirect('/contracts');
        }
    }

    /**
     * Genera el PDF oficial del contrato basado en su plantilla.
     * Permiso requerido: contracts_view
     *
     * @param int $id ID del contrato
     */
    public function generatePdf($id)
    {
        PermissionMiddleware::check('contracts_view');

        $pdfService = new PdfGeneratorService();
        $result = $pdfService->generateContractPdf((int)$id);

        if ($result['status']) {
            (new ContractHistoryModel())->add((int)$id, AuthService::userId(), 'pdf_generado', "Se generó un nuevo PDF del contrato");
            (new AuditModel())->add(AuthService::userId(), 'contratos', 'pdf', "PDF generado para contrato ID: {$id}");
            $_SESSION['flash_success'] = 'PDF generado correctamente';
        } else {
            $_SESSION['flash_error'] = $result['message'];
        }

        $this->redirect('/contracts/show/' . $id);
    }
    // Fin de la función generatePdf()

    /**
     * Descarga/visualiza el PDF generado de un contrato.
     * Sirve el archivo a través de PHP ya que el directorio uploads
     * tiene acceso directo denegado por .htaccess.
     * Permiso requerido: contracts_view
     *
     * @param int $id ID del contrato
     */
    public function downloadPdf($id)
    {
        PermissionMiddleware::check('contracts_view');

        $contractModel = new ContractModel();
        $contract = $contractModel->getById((int)$id);

        if (!$contract || empty($contract['generated_pdf_path'])) {
            $_SESSION['flash_error'] = 'PDF no encontrado para este contrato.';
            $this->redirect('/contracts');
            return;
        }

        $filePath = UPLOAD_BASE_PATH . '/' . $contract['generated_pdf_path'];

        if (!file_exists($filePath)) {
            $_SESSION['flash_error'] = 'El archivo PDF no existe en el servidor.';
            $this->redirect('/contracts/show/' . $id);
            return;
        }

        // Servir el archivo como PDF inline (se abre en el navegador)
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($filePath);
        exit;
    }
    // Fin de la función downloadPdf()

    // ========================================================
    // MÉTODOS PRIVADOS AUXILIARES
    // ========================================================

    /**
     * Recopila los datos del formulario de contrato.
     *
     * @return array Datos sanitizados del formulario
     */
    private function collectFormData()
    {
        return [
            'company_id'        => (int)($_POST['company_id'] ?? 0),
            'template_id'       => !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null,
            'contract_type'     => trim($_POST['contract_type'] ?? ''),
            'pricing_mode'      => trim($_POST['pricing_mode'] ?? 'grupo'),
            'duration_type'     => trim($_POST['duration_type'] ?? 'indefinido'),
            'start_date'        => trim($_POST['start_date'] ?? ''),
            'end_date'          => trim($_POST['end_date'] ?? ''),
            'base_amount'       => (float)($_POST['base_amount'] ?? 0),
            'base_guests'       => !empty($_POST['base_guests']) ? (int)$_POST['base_guests'] : null,
            'payment_frequency' => trim($_POST['payment_frequency'] ?? 'mensual'),
            'status'            => trim($_POST['status'] ?? 'borrador'),
            'notes'             => trim($_POST['notes'] ?? ''),
            // Relaciones (no se guardan directamente en doc_contracts)
            'hotel_ids'         => $_POST['hotel_ids'] ?? [],
            'service_ids'       => $_POST['service_ids'] ?? [],
            'tiers'             => $_POST['tiers'] ?? [],
        ];
    }
    // Fin de la función collectFormData()

    /**
     * Valida los datos del formulario.
     *
     * @param  array $data Datos del formulario
     * @return array Lista de errores (vacía si no hay errores)
     */
    private function validateFormData($data)
    {
        $errors = [];

        if (empty($data['company_id'])) {
            $errors[] = 'Debe seleccionar una empresa';
        }
        if (empty($data['contract_type'])) {
            $errors[] = 'Debe seleccionar un tipo de contrato';
        }
        if (empty($data['start_date'])) {
            $errors[] = 'La fecha de inicio es obligatoria';
        }
        if (empty($data['hotel_ids'])) {
            $errors[] = 'Debe seleccionar al menos un hotel';
        }
        if ($data['duration_type'] === 'plazo_fijo' && empty($data['end_date'])) {
            $errors[] = 'Los contratos a plazo fijo requieren fecha de término';
        }

        return $errors;
    }
    // Fin de la función validateFormData()

    /**
     * Sincroniza las relaciones N:M del contrato.
     * (hoteles, servicios, escalas de precio)
     *
     * @param int   $contractId ID del contrato
     * @param array $data       Datos del formulario con hotel_ids, service_ids, tiers
     */
    private function syncRelations($contractId, $data)
    {
        $contractModel = new ContractModel();

        // Sincronizar hoteles
        if (!empty($data['hotel_ids'])) {
            $contractModel->syncHotels($contractId, $data['hotel_ids']);
        }

        // Sincronizar servicios
        $serviceIds = $data['service_ids'] ?? [];
        $contractModel->syncServices($contractId, $serviceIds);

        // Sincronizar tiers (solo si pricing_mode = por_persona)
        if ($data['pricing_mode'] === 'por_persona' && !empty($data['tiers'])) {
            $tiersData = [];
            foreach ($data['tiers'] as $tier) {
                if (!empty($tier['min_guests']) && !empty($tier['price_per_person'])) {
                    $tiersData[] = [
                        'min_guests'       => (int)$tier['min_guests'],
                        'max_guests'       => !empty($tier['max_guests']) ? (int)$tier['max_guests'] : null,
                        'price_per_person' => (float)$tier['price_per_person'],
                        'discount_percent' => (float)($tier['discount_percent'] ?? 0),
                        'description'      => $tier['description'] ?? null,
                    ];
                }
            }
            if (!empty($tiersData)) {
                (new ContractTierModel())->syncTiers($contractId, $tiersData);
            }
        }
    }
    // Fin de la función syncRelations()

    /**
     * Obtiene los datos necesarios para los selects del formulario.
     *
     * @return array Datos para dropdowns:
     *   - companies  => Lista de empresas (id, business_name)
     *   - hotels     => Lista de hoteles activos
     *   - services   => Lista de servicios activos
     *   - templates  => Lista de plantillas activas
     */
    private function getFormSelectData()
    {
        return [
            'companies' => (new CompanyModel())->getForSelect(),
            'hotels'    => (new HotelModel())->getAll(),
            'services'  => (new ServiceModel())->getAll(),
            'templates' => (new ContractTemplateModel())->getForSelect(),
        ];
    }
    // Fin de la función getFormSelectData()
}
