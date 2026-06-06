<?php
/**
 * Controller de Cotizaciones.
 *
 * Gestiona el flujo comercial de propuestas antes de convertirse en contratos.
 * Soporta versionado, servicios con precios específicos y UF.
 */
class QuotationsController extends Controller
{
    public function index()
    {
        PermissionMiddleware::check('contracts_view');

        $filters = $_GET;

        // Si no se selecciona un estado específico, limitamos a estados de cotización
        if (empty($filters['status'])) {
            $filters['status_in'] = ['quotation_draft', 'quotation_sent', 'quotation_approved'];
        }

        // Seguridad (IDOR): Vendedores solo ven sus propias cotizaciones
        if (!AuthService::isAdmin()) {
            $filters['created_by'] = AuthService::userId();
        }

        $contractModel = new ContractModel();
        $quotations = $contractModel->getAll($filters);

        $companyModel = new CompanyModel();
        $companiesSelect = $companyModel->getForSelect();

        $this->view('contracts/quotations_index', compact('quotations', 'filters', 'companiesSelect'));
    }

    public function create()
    {
        PermissionMiddleware::check('contracts_create');

        $contract = [];
        $isEdit = false;
        $isQuotation = true;

        $companyModel = new CompanyModel();
        $hotelModel = new HotelModel();
        $serviceModel = new ServiceModel();
        $templateModel = new ContractTemplateModel();

        $formData = [
            'companies' => $companyModel->getForSelect(),
            'hotels'    => $hotelModel->getAll(),
            'services'  => $serviceModel->getAll(),
            'templates' => $templateModel->getForSelect(),
        ];

        $preselectedCompanyId = $_GET['company_id'] ?? null;
        $selectedHotels = [];
        $selectedServices = [];
        $services = [];
        $tiers = [];

        $this->view('contracts/form', compact(
            'contract', 'isEdit', 'isQuotation', 'formData',
            'preselectedCompanyId', 'selectedHotels', 'selectedServices', 'services', 'tiers'
        ));
    }

    public function store()
    {
        PermissionMiddleware::check('contracts_create');
        csrf_verify();

        $data = $this->collectFormData();
        // Forzar estado de cotización
        $data['status'] = 'quotation_draft';

        $contractModel = new ContractModel();
        $id = $contractModel->create($data, AuthService::userId());

        if ($id) {
            $this->syncRelations($id, $data);

            // Subida de archivos adjuntos
            if (!empty($_FILES['attachments']['name'][0])) {
                $uploadService = new FileUploadService();
                foreach ($_FILES['attachments']['name'] as $i => $name) {
                    if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name'     => $_FILES['attachments']['name'][$i],
                            'type'     => $_FILES['attachments']['type'][$i],
                            'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                            'error'    => $_FILES['attachments']['error'][$i],
                            'size'     => $_FILES['attachments']['size'][$i]
                        ];
                        $uploadService->uploadAttachment($file, $id, $data['company_id'], 'cotizacion_adjunto', AuthService::userId());
                    }
                }
            }

            $_SESSION['flash_success'] = 'Cotización creada correctamente';
            $this->redirect('/quotations/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al crear la cotización';
            $this->redirect('/quotations/create');
        }
    }

    public function show($id)
    {
        PermissionMiddleware::check('contracts_view');
        
        $contractModel = new ContractModel();
        $quotation = $contractModel->getById($id);

        if (!$quotation) {
            $this->redirect('/quotations');
            return;
        }

        // Seguridad (IDOR)
        if (!AuthService::isAdmin() && (int)$quotation['created_by'] !== AuthService::userId()) {
            http_response_code(403);
            die("No tienes permiso para ver esta cotización");
        }

        $serviceModel = new ServiceModel();
        $services = $serviceModel->getByContractId($id);

        $hotelModel = new HotelModel();
        $hotels = $hotelModel->getByContractId($id);

        $attachments = (new ContractAttachmentModel())->getByContractId($id);

        // Cargar historial de acciones
        $historyModel = new ContractHistoryModel();
        $history = $historyModel->getByContractId($id);

        // Contactos adicionales de la empresa
        $contacts = (new ContactModel())->getByCompany((int) $quotation['company_id']);

        $this->view('contracts/quotation_show', compact('quotation', 'services', 'hotels', 'attachments', 'history', 'contacts'));
    }

    /**
     * Genera y guarda físicamente el PDF de la cotización en el servidor.
     */
    public function generatePdf($id)
    {
        PermissionMiddleware::check('contracts_edit');

        $pdfService = new PdfGeneratorService();
        $result = $pdfService->saveQuotationPdf($id);

        if ($result['status']) {
            (new ContractHistoryModel())->add($id, AuthService::userId(), 'pdf_generado', "Se generó una versión física del PDF: " . basename($result['file_path']));
            $_SESSION['flash_success'] = 'PDF generado y guardado correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Error al generar PDF: ' . $result['message'];
        }

        $this->redirect('/quotations/show/' . $id);
    }

    public function edit($id)
    {
        PermissionMiddleware::check('contracts_edit');

        $contractModel = new ContractModel();
        $contract = $contractModel->getById($id);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Cotización no encontrada';
            $this->redirect('/quotations');
            return;
        }

        // Seguridad (IDOR)
        if (!AuthService::isAdmin() && (int)$contract['created_by'] !== AuthService::userId()) {
            http_response_code(403);
            die("No tiene permiso para editar esta cotización");
        }

        $isEdit = true;
        $isQuotation = true;

        $companyModel = new CompanyModel();
        $hotelModel = new HotelModel();
        $serviceModel = new ServiceModel();
        $templateModel = new ContractTemplateModel();

        $formData = [
            'companies' => $companyModel->getForSelect(),
            'hotels'    => $hotelModel->getAll(),
            'services'  => $serviceModel->getAll(),
            'templates' => $templateModel->getForSelect(),
        ];

        $preselectedCompanyId = $contract['company_id'];
        
        // Obtener relaciones actuales
        $selectedHotels   = array_column((new HotelModel())->getByContractId($id), 'id');
        $services         = $serviceModel->getByContractId($id);
        $selectedServices = array_column($services, 'id');
        $tiers            = (new ContractTierModel())->getByContractId($id);
        $attachments      = (new ContractAttachmentModel())->getByContractId($id);

        $this->view('contracts/form', compact(
            'contract', 'isEdit', 'isQuotation', 'formData',
            'preselectedCompanyId', 'selectedHotels', 'selectedServices', 'services', 'tiers', 'attachments'
        ));
    }

    public function update($id)
    {
        PermissionMiddleware::check('contracts_edit');
        csrf_verify();

        $contractModel = new ContractModel();
        $contract = $contractModel->getById($id);

        if (!$contract || (!AuthService::isAdmin() && (int)$contract['created_by'] !== AuthService::userId())) {
            http_response_code(403);
            die("No autorizado");
        }

        $data = $this->collectFormData();
        $data['status'] = $contract['status']; // preservar estado actual — update() siempre escribe status

        if ($contractModel->update($id, $data)) {
            $this->syncRelations($id, $data);

            // Subida de nuevos archivos adjuntos si los hay
            if (!empty($_FILES['attachments']['name'][0])) {
                $uploadService = new FileUploadService();
                foreach ($_FILES['attachments']['name'] as $i => $name) {
                    if ($_FILES['attachments']['error'][$i] === UPLOAD_ERR_OK) {
                        $file = [
                            'name'     => $_FILES['attachments']['name'][$i],
                            'type'     => $_FILES['attachments']['type'][$i],
                            'tmp_name' => $_FILES['attachments']['tmp_name'][$i],
                            'error'    => $_FILES['attachments']['error'][$i],
                            'size'     => $_FILES['attachments']['size'][$i]
                        ];
                        $uploadService->uploadAttachment($file, $id, $data['company_id'], 'cotizacion_adjunto', AuthService::userId());
                    }
                }
            }

            $_SESSION['flash_success'] = 'Cotización actualizada correctamente';
            $this->redirect('/quotations/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar cotización';
            $this->redirect('/quotations/edit/' . $id);
        }
    }

    /**
     * Crea una copia de la cotización actual.
     */
    public function createVersion($id)
    {
        PermissionMiddleware::check('contracts_create');
        
        $contractModel = new ContractModel();
        $quotation = $contractModel->getById($id);

        if (!$quotation || (!AuthService::isAdmin() && (int)$quotation['created_by'] !== AuthService::userId())) {
            $this->redirect('/quotations');
            return;
        }

        $newId = $contractModel->createVersion($id);
        if ($newId) {
            $_SESSION['flash_success'] = 'Copia de cotización creada correctamente';
            $this->redirect('/quotations/edit/' . $newId);
        } else {
            $_SESSION['flash_error'] = 'Error al copiar cotización';
            $this->redirect('/quotations/show/' . $id);
        }
    }

    /**
     * Aprueba la cotización y la prepara para ser contrato.
     */
    public function approve($id)
    {
        PermissionMiddleware::check('contracts_edit');
        
        $contractModel = new ContractModel();
        $quotation = $contractModel->getById($id);

        if (!$quotation || (!AuthService::isAdmin() && (int)$quotation['created_by'] !== AuthService::userId())) {
            $this->redirect('/quotations');
            return;
        }

        // Cambiar estado a aprobada usando el modelo
        if ($contractModel->changeStatus($id, 'quotation_approved')) {
            (new ContractHistoryModel())->add($id, AuthService::userId(), 'aprobado', "Cotización aprobada por el cliente");
            $_SESSION['flash_success'] = 'Cotización aprobada. Ahora puede completar los datos finales para convertirla en contrato.';
            $this->redirect('/contracts/edit/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al aprobar cotización';
            $this->redirect('/quotations/show/' . $id);
        }
    }

    /**
     * Envía la cotización por correo con el PDF adjunto.
     * POST ?url=quotations/sendEmail/{id}
     */
    public function sendEmail($id)
    {
        PermissionMiddleware::check('contracts_edit');
        csrf_verify();

        $contractModel = new ContractModel();
        $quotation     = $contractModel->getById($id);

        if (!$quotation || (!AuthService::isAdmin() && (int)$quotation['created_by'] !== AuthService::userId())) {
            $_SESSION['flash_error'] = 'Cotización no encontrada o sin permiso.';
            $this->redirect('/quotations');
            return;
        }

        // Generar PDF si no existe o el archivo ya no está en disco
        $pdfPath = null;
        if (!empty($quotation['generated_pdf_path'])) {
            $rel     = $quotation['generated_pdf_path'];
            $pdfPath = APP_ROOT . '/public' . (strpos($rel, '/uploads/') === 0 ? $rel : '/uploads/contracts/' . $rel);
        }

        if (empty($pdfPath) || !file_exists($pdfPath)) {
            $pdfResult = (new PdfGeneratorService())->saveQuotationPdf($id);
            if ($pdfResult['status']) {
                $pdfPath  = $pdfResult['file_path'];
                $quotation = $contractModel->getById($id);
            }
        }

        $recipients = array_unique(array_filter(array_map('trim', (array)($_POST['recipients'] ?? []))));
        $subject    = trim($_POST['subject'] ?? '') ?: 'Propuesta Comercial — ' . htmlspecialchars($quotation['business_name']);
        $msgPlain   = trim($_POST['message'] ?? '');
        $msgHtml    = nl2br(htmlspecialchars($msgPlain));

        if (empty($recipients)) {
            $_SESSION['flash_error'] = 'Selecciona al menos un destinatario.';
            $this->redirect('/quotations/show/' . $id);
            return;
        }

        $body = "
            <div style='font-family:Arial,sans-serif;font-size:15px;color:#333;max-width:600px;margin:0 auto;'>
              <p>{$msgHtml}</p>
              <hr style='border:none;border-top:1px solid #ddd;margin:24px 0;'>
              <p style='font-size:12px;color:#888;'>
                Atankalama — Sistema de Contratos<br>
                Cotización: <strong>{$quotation['code']}</strong>
              </p>
            </div>";

        $pdfName     = 'Cotizacion_' . $quotation['code'] . '.pdf';
        $mailService = new MailService();
        $enviados    = 0;
        $fallidos    = [];

        foreach ($recipients as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
            $ok = $mailService->sendWithAttachment($email, $subject, $body, $pdfPath, $pdfName);
            $ok ? $enviados++ : $fallidos[] = $email;
        }

        if ($enviados > 0) {
            // Marcar como enviada si estaba en borrador
            if ($quotation['status'] === 'quotation_draft') {
                $contractModel->changeStatus($id, 'quotation_sent');
            }
            $lista = implode(', ', $recipients);
            (new ContractHistoryModel())->add($id, AuthService::userId(), 'email_enviado', "Correo enviado a: {$lista}");
            $_SESSION['flash_success'] = "Correo enviado a {$enviados} destinatario(s).";
        } else {
            $_SESSION['flash_error'] = 'No se pudo enviar el correo. Revisa la configuración SMTP.';
        }

        $this->redirect('/quotations/show/' . $id);
    }

    private function collectFormData()
    {
        return [
            'company_id'        => (int)($_POST['company_id'] ?? 0),
            'template_id'       => !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null,
            'contract_type'     => trim($_POST['contract_type'] ?? 'hospedaje'),
            'duration_type'     => trim($_POST['duration_type'] ?? 'indefinido'),
            'start_date'        => $_POST['start_date'] ?? date('Y-m-d'),
            'end_date'          => $_POST['end_date'] ?? null,
            'pricing_mode'      => trim($_POST['pricing_mode'] ?? 'grupo'),
            'base_amount'       => (float)($_POST['base_amount'] ?? 0),
            'payment_frequency' => trim($_POST['payment_frequency'] ?? 'mensual'),
            'notes'             => trim($_POST['notes'] ?? ''),
            'hotel_ids'         => $_POST['hotel_ids'] ?? [],
            'service_ids'       => $_POST['service_ids'] ?? [],
            'service_prices'    => $_POST['service_prices'] ?? [],
            'service_currencies'=> $_POST['service_currencies'] ?? [],
            'service_billings'  => $_POST['service_billings'] ?? [],
            'service_notes'     => $_POST['service_notes'] ?? [],
        ];
    }

    private function syncRelations($id, $data)
    {
        $contractModel = new ContractModel();
        
        if (!empty($data['hotel_ids'])) {
            $contractModel->syncHotels($id, $data['hotel_ids']);
        }

        if (!empty($data['service_ids'])) {
            $details = [
                'price'    => $data['service_prices'] ?? [],
                'currency' => $data['service_currencies'] ?? [],
                'billing'  => $data['service_billings'] ?? [],
                'notes'    => $data['service_notes'] ?? [],
            ];
            $contractModel->syncServices($id, $data['service_ids'], $details);
        }
    }
}
