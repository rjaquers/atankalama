<?php
/**
 * Controller de Pagos.
 *
 * Gestiona el registro y seguimiento de pagos parciales asociados a contratos.
 * Permite registrar nuevos pagos, ver el historial y anular registros.
 * Requiere permisos: payments_view, payments_register, payments_void.
 *
 * @package App\Controllers
 */
class PaymentsController extends Controller
{
    /**
     * Lista todos los pagos con filtros opcionales.
     * Permiso requerido: payments_view
     */
    public function index()
    {
        PermissionMiddleware::check('payments_view');

        $paymentModel = new PaymentModel();
        
        $filters = [
            'status' => $_GET['status'] ?? null
        ];

        // Obtener pagos pendientes para el dashboard de cobranzas
        $pendingPayments = $paymentModel->getPendingPayments();
        $totalPending = $paymentModel->getTotalPendingAmount();
        $countPending = $paymentModel->countPending();

        $this->view('payments/index', compact('pendingPayments', 'totalPending', 'countPending', 'filters'));
    }

    /**
     * Muestra formulario para registrar un nuevo pago.
     * Permiso requerido: payments_register
     */
    public function create()
    {
        PermissionMiddleware::check('payments_register');

        $contractId = isset($_GET['contract_id']) ? (int)$_GET['contract_id'] : null;
        
        if (!$contractId) {
            $_SESSION['flash_error'] = 'Debe seleccionar un contrato para registrar un pago.';
            $this->redirect('/contracts');
            return;
        }

        $contractModel = new ContractModel();
        $contract = $contractModel->getById($contractId);

        if (!$contract) {
            $_SESSION['flash_error'] = 'Contrato no encontrado.';
            $this->redirect('/contracts');
            return;
        }

        $payment = [];
        $isEdit = false;

        $tierModel = new ContractTierModel();
        $tiers = $tierModel->getByContractId($contractId);

        $serviceModel = new ServiceModel();
        $availableServices = $serviceModel->getAll();

        $this->view('payments/form', compact('contract', 'payment', 'isEdit', 'tiers', 'availableServices'));
    }

    /**
     * Guarda un nuevo registro de pago.
     * Permiso requerido: payments_register
     */
    public function store()
    {
        PermissionMiddleware::check('payments_register');
        csrf_verify();

        $contractId = (int)$_POST['contract_id'];
        
        $data = [
            'contract_id'      => $contractId,
            'amount'           => (float)$_POST['amount'],
            'payment_date'     => $_POST['payment_date'],
            'payment_method'   => $_POST['payment_method'],
            'reference_number' => trim($_POST['reference_number'] ?? ''),
            'period_type'      => $_POST['period_type'],
            'period_start'     => $_POST['period_start'] ?: null,
            'period_end'       => $_POST['period_end'] ?: null,
            'status'           => $_POST['status'] ?? 'pagado',
            'notes'            => trim($_POST['notes'] ?? '')
        ];

        // Validaciones básicas
        if ($data['amount'] <= 0) {
            $_SESSION['flash_error'] = 'El monto del pago debe ser mayor a cero.';
            $this->redirect('/payments/create?contract_id=' . $contractId);
            return;
        }

        $paymentModel = new PaymentModel();
        $paymentId = $paymentModel->create($data, AuthService::userId());

        if ($paymentId) {
            // Procesar adjunto de pago si se envió
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] !== UPLOAD_ERR_NO_FILE) {
                // Obtener compañía del contrato
                $contractModel = new ContractModel();
                $contract = $contractModel->getById($contractId);
                
                if ($contract) {
                    $uploadService = new FileUploadService();
                    $uploadService->uploadAttachment(
                        $_FILES['payment_proof'], 
                        $contractId, 
                        $contract['company_id'], 
                        'comprobante_pago', 
                        AuthService::userId(),
                        $paymentId
                    );
                }
            }

            // Registrar en el historial del contrato
            (new ContractHistoryModel())->add(
                $contractId, 
                AuthService::userId(), 
                'pago_registrado', 
                "Se registró un pago de $" . number_format($data['amount'], 0, ',', '.') . " (Método: {$data['payment_method']})"
            );

            (new AuditModel())->add(AuthService::userId(), 'pagos', 'crear', "Pago registrado (ID: {$paymentId}) para contrato ID: {$contractId}");

            $_SESSION['flash_success'] = 'Pago registrado exitosamente.';
            $this->redirect('/contracts/show/' . $contractId);
        } else {
            $_SESSION['flash_error'] = 'Error al registrar el pago.';
            $this->redirect('/payments/create?contract_id=' . $contractId);
        }
    }

    /**
     * Guarda un nuevo registro de deuda / cobro pendiente.
     * Permiso requerido: payments_register
     */
    public function storeDebt()
    {
        PermissionMiddleware::check('payments_register');
        csrf_verify();

        $contractId = (int)$_POST['contract_id'];
        
        $data = [
            'contract_id'      => $contractId,
            'amount'           => (float)$_POST['amount'],
            'payment_date'     => $_POST['payment_date'] ?? date('Y-m-d'), // Actúa como due_date
            'payment_method'   => 'transferencia', // default
            'reference_number' => '',
            'period_type'      => $_POST['period_type'] ?? 'otro',
            'period_start'     => !empty($_POST['period_start']) ? $_POST['period_start'] : null,
            'period_end'       => !empty($_POST['period_end']) ? $_POST['period_end'] : null,
            'status'           => 'pendiente', // ESTO ES CLAVE PARA LA DEUDA
            'notes'            => trim($_POST['notes'] ?? '')
        ];

        // Validaciones básicas
        if ($data['amount'] <= 0) {
            $_SESSION['flash_error'] = 'El monto a cobrar debe ser mayor a cero.';
            $this->redirect('/contracts/show/' . $contractId);
            return;
        }

        $paymentModel = new PaymentModel();
        $paymentId = $paymentModel->create($data, AuthService::userId());

        if ($paymentId) {
            // Registrar en el historial del contrato
            (new ContractHistoryModel())->add(
                $contractId, 
                AuthService::userId(), 
                'cargo_generado', 
                "Se registró un nuevo cargo por $" . number_format($data['amount'], 0, ',', '.')
            );

            (new AuditModel())->add(AuthService::userId(), 'pagos', 'crear_deuda', "Deuda generada (ID: {$paymentId}) para contrato ID: {$contractId}");

            $_SESSION['flash_success'] = 'Registro de cobro generado exitosamente.';
        } else {
            $_SESSION['flash_error'] = 'Error al registrar el cobro.';
        }

        $this->redirect('/contracts/show/' . $contractId);
    }

    /**
     * Guarda un cargo adicional por servicio como movimiento independiente.
     * Permiso requerido: payments_register
     */
    public function storeServiceCharge()
    {
        PermissionMiddleware::check('payments_register');
        csrf_verify();

        $contractId = (int)$_POST['contract_id'];
        $serviceName = trim($_POST['service_name'] ?? '');
        $serviceDate = $_POST['service_date'] ?? date('Y-m-d');
        $serviceQty = (int)($_POST['service_qty'] ?? 1);
        $servicePrice = (float)($_POST['service_price'] ?? 0);
        $serviceNotes = trim($_POST['service_notes'] ?? '');

        // Validaciones
        if (empty($serviceName)) {
            $_SESSION['flash_error'] = 'Debe seleccionar un tipo de cargo.';
            $this->redirect('/contracts/show/' . $contractId);
            return;
        }

        $totalAmount = $serviceQty * $servicePrice;
        if ($totalAmount <= 0) {
            $_SESSION['flash_error'] = 'El monto total del cargo debe ser mayor a cero.';
            $this->redirect('/contracts/show/' . $contractId);
            return;
        }

        // Construir nota descriptiva
        $noteStr = "Cargo por servicio: {$serviceName}";
        $noteStr .= " ({$serviceQty} x $" . number_format($servicePrice, 0, ',', '.') . ")";
        $noteStr .= "\nFecha del servicio: " . date('d/m/Y', strtotime($serviceDate));
        if (!empty($serviceNotes)) {
            $noteStr .= "\nObs: {$serviceNotes}";
        }

        $data = [
            'contract_id'      => $contractId,
            'amount'           => $totalAmount,
            'payment_date'     => $serviceDate,
            'payment_method'   => 'transferencia',
            'reference_number' => '',
            'period_type'      => 'servicio',
            'period_start'     => null,
            'period_end'       => null,
            'status'           => 'pendiente',
            'notes'            => $noteStr
        ];

        $paymentModel = new PaymentModel();
        $paymentId = $paymentModel->create($data, AuthService::userId());

        if ($paymentId) {
            (new ContractHistoryModel())->add(
                $contractId,
                AuthService::userId(),
                'cargo_generado',
                "Cargo por servicio: {$serviceName} — $" . number_format($totalAmount, 0, ',', '.') . " (Fecha: " . date('d/m/Y', strtotime($serviceDate)) . ")"
            );

            (new AuditModel())->add(AuthService::userId(), 'pagos', 'crear_cargo_servicio', "Cargo de servicio (ID: {$paymentId}) para contrato ID: {$contractId}");

            $_SESSION['flash_success'] = "Cargo por \"{$serviceName}\" registrado exitosamente ($" . number_format($totalAmount, 0, ',', '.') . ").";
        } else {
            $_SESSION['flash_error'] = 'Error al registrar el cargo de servicio.';
        }

        $this->redirect('/contracts/show/' . $contractId);
    }

    /**
     * Formulario de edición de pago.
     * Permiso requerido: payments_register
     */
    public function edit($id)
    {
        PermissionMiddleware::check('payments_register');

        $paymentModel = new PaymentModel();
        $payment = $paymentModel->getById((int)$id);

        if (!$payment) {
            $_SESSION['flash_error'] = 'Pago no encontrado.';
            $this->redirect('/contracts');
            return;
        }

        $contractModel = new ContractModel();
        $contract = $contractModel->getById($payment['contract_id']);

        $isEdit = true;

        $tierModel = new ContractTierModel();
        $tiers = $tierModel->getByContractId($payment['contract_id']);

        $serviceModel = new ServiceModel();
        $availableServices = $serviceModel->getAll();

        $this->view('payments/form', compact('contract', 'payment', 'isEdit', 'tiers', 'availableServices'));
    }

    /**
     * Actualiza un pago.
     * Permiso requerido: payments_register
     */
    public function update($id)
    {
        PermissionMiddleware::check('payments_register');
        csrf_verify();

        $paymentModel = new PaymentModel();
        $payment = $paymentModel->getById((int)$id);

        if (!$payment) {
            $_SESSION['flash_error'] = 'Pago no encontrado.';
            $this->redirect('/contracts');
            return;
        }

        $data = [
            'amount'           => (float)$_POST['amount'],
            'payment_date'     => $_POST['payment_date'],
            'payment_method'   => $_POST['payment_method'],
            'reference_number' => trim($_POST['reference_number'] ?? ''),
            'period_type'      => $_POST['period_type'],
            'period_start'     => $_POST['period_start'] ?: null,
            'period_end'       => $_POST['period_end'] ?: null,
            'status'           => $_POST['status'] ?? 'pagado',
            'notes'            => trim($_POST['notes'] ?? '')
        ];

        if ($paymentModel->update((int)$id, $data)) {
            // Procesar adjunto de pago si se envió
            if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] !== UPLOAD_ERR_NO_FILE) {
                $contractModel = new ContractModel();
                $contract = $contractModel->getById($payment['contract_id']);
                
                if ($contract) {
                    $uploadService = new FileUploadService();
                    $uploadService->uploadAttachment(
                        $_FILES['payment_proof'], 
                        $payment['contract_id'], 
                        $contract['company_id'], 
                        'comprobante_pago', 
                        AuthService::userId(),
                        $id
                    );
                }
            }
            
            (new AuditModel())->add(AuthService::userId(), 'pagos', 'editar', "Pago editado (ID: {$id})");
            $_SESSION['flash_success'] = 'Pago actualizado correctamente.';
            $this->redirect('/contracts/show/' . $payment['contract_id']);
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar el pago.';
            $this->redirect('/payments/edit/' . $id);
        }
    }

    /**
     * Anula un pago.
     * Permiso requerido: payments_void
     */
    public function void($id)
    {
        PermissionMiddleware::check('payments_void');
        csrf_verify();

        $paymentModel = new PaymentModel();
        $payment = $paymentModel->getById((int)$id);

        if (!$payment) {
            $_SESSION['flash_error'] = 'Pago no encontrado.';
            $this->redirect('/contracts');
            return;
        }

        if ($paymentModel->void((int)$id)) {
            // Historial del contrato
            (new ContractHistoryModel())->add(
                $payment['contract_id'], 
                AuthService::userId(), 
                'pago_anulado', 
                "Se anuló el pago de $" . number_format($payment['amount'], 0, ',', '.') . " (Ref: {$payment['reference_number']})"
            );

            (new AuditModel())->add(AuthService::userId(), 'pagos', 'anular', "Pago anulado (ID: {$id})");

            $_SESSION['flash_success'] = 'Pago anulado correctamente.';
        } else {
            $_SESSION['flash_error'] = 'Error al anular el pago.';
        }

        $this->redirect('/contracts/show/' . $payment['contract_id']);
    }
}
