<?php
/**
 * Controller de Reservas de Espacios.
 *
 * Gestiona el CRUD de reservas, validación de disponibilidad,
 * calendario, cancelación y cambios de estado.
 *
 * @package App\Controllers
 */
class BookingsController extends Controller
{
    /**
     * Lista todas las reservas con filtros.
     * Permiso requerido: bookings_view
     */
    public function index()
    {
        PermissionMiddleware::check('bookings_view');

        $filters = [];
        foreach (['booking_status', 'charge_status', 'space_id', 'company_id', 'date_from', 'date_to'] as $f) {
            if (!empty($_GET[$f])) {
                $filters[$f] = $_GET[$f];
            }
        }

        $bookings = (new SpaceBookingModel())->getAll($filters);
        $spaces = (new SpaceModel())->getActive();
        $this->view('bookings/index', compact('bookings', 'spaces', 'filters'));
    }
    // Fin de la función index()

    /**
     * Muestra formulario de creación de reserva.
     * Permiso requerido: bookings_create
     */
    public function create()
    {
        PermissionMiddleware::check('bookings_create');

        $spaces = (new SpaceModel())->getAll(['active' => 1]);
        $extras = (new SpaceExtraModel())->getActive();
        $companies = (new CompanyModel())->getAll(['active' => 1]);
        $contracts = []; // Se cargarán por AJAX según empresa

        $selectedSpace = !empty($_GET['space_id']) ? (new SpaceModel())->getById((int)$_GET['space_id']) : null;
        $booking = null;

        $this->view('bookings/form', compact('spaces', 'extras', 'companies', 'contracts', 'booking', 'selectedSpace'));
    }
    // Fin de la función create()
    
    /**
     * Muestra formulario de edición de reserva.
     * Permiso requerido: bookings_edit
     */
    public function edit($id)
    {
        PermissionMiddleware::check('bookings_edit');

        $bookingModel = new SpaceBookingModel();
        $booking = $bookingModel->getById((int)$id);
        if (!$booking) {
            $_SESSION['flash_error'] = 'Reserva no encontrada';
            $this->redirect('/bookings');
            return;
        }

        $spaces = (new SpaceModel())->getAll(['active' => 1]);
        $extras = (new SpaceExtraModel())->getActive();
        $companies = (new CompanyModel())->getAll(['active' => 1]);
        
        // Cargar contratos de la empresa de la reserva
        $contracts = [];
        if ($booking['company_id']) {
            $contracts = (new ContractModel())->getAll([
                'company_id' => $booking['company_id'],
                'status'     => 'vigente'
            ]);
        }

        $selectedSpace = null;
        $items = (new SpaceBookingItemModel())->getByBookingId((int)$id);
        $this->view('bookings/form', compact('spaces', 'extras', 'companies', 'contracts', 'booking', 'selectedSpace', 'items'));
    }
    // Fin de la función edit()

    /**
     * Registra un pago/abono directo para una reserva.
     * Permiso requerido: bookings_edit
     */
    public function addPayment($id)
    {
        PermissionMiddleware::check('bookings_edit');
        csrf_verify();

        $bookingId = (int)$id;
        $data = [
            'booking_id'      => $bookingId,
            'amount'          => (float)($_POST['amount'] ?? 0),
            'payment_date'    => $_POST['payment_date'] ?? date('Y-m-d'),
            'payment_method'  => $_POST['payment_method'] ?? 'transferencia',
            'reference_number' => $_POST['reference_number'] ?? null,
            'notes'           => $_POST['notes'] ?? null,
            'receipt_path'    => null
        ];

        if ($data['amount'] <= 0) {
            $_SESSION['flash_error'] = 'El monto debe ser mayor a cero';
            $this->redirect('/bookings/show/' . $bookingId);
            return;
        }

        $userId = AuthService::userId();
        $ok = (new BookingPaymentModel())->create($data, $userId);

        if ($ok) {
            (new SpaceBookingHistoryModel())->add($bookingId, $userId, 'pago_registrado', "Abono de $" . number_format($data['amount'], 0, ',', '.') . " registrado.");
            $_SESSION['flash_success'] = 'Pago registrado correctamente';
        } else {
            $_SESSION['flash_error'] = 'Error al registrar el pago';
        }

        $this->redirect('/bookings/show/' . $bookingId);
    }
    // Fin de la función addPayment()

    /**
     * Guarda una nueva reserva.
     * Permiso requerido: bookings_create
     */
    public function store()
    {
        PermissionMiddleware::check('bookings_create');
        csrf_verify();

        $data = $this->collectFormData();

        // ===============================
        // VALIDACIONES
        // ===============================
        if (empty($data['space_id'])) {
            $_SESSION['flash_error'] = 'Debe seleccionar un espacio';
            $this->redirect('/bookings/create');
            return;
        }
        if (empty($data['start_datetime']) || empty($data['end_datetime'])) {
            $_SESSION['flash_error'] = 'Debe indicar fecha/hora de inicio y fin';
            $this->redirect('/bookings/create');
            return;
        }
        if ($data['start_datetime'] >= $data['end_datetime']) {
            $_SESSION['flash_error'] = 'La fecha de fin debe ser posterior a la de inicio';
            $this->redirect('/bookings/create');
            return;
        }

        // Verificar traslape
        $bookingModel = new SpaceBookingModel();
        $overlap = $bookingModel->checkOverlap($data['space_id'], $data['start_datetime'], $data['end_datetime']);
        if ($overlap) {
            $_SESSION['flash_error'] = "Conflicto: el espacio ya tiene una reserva ({$overlap['folio']}) " .
                "del " . date('d/m/Y H:i', strtotime($overlap['start_datetime'])) .
                " al " . date('d/m/Y H:i', strtotime($overlap['end_datetime']));
            $this->redirect('/bookings/create');
            return;
        }

        // Verificar bloqueo
        $block = $bookingModel->checkBlockOverlap($data['space_id'], $data['start_datetime'], $data['end_datetime']);
        if ($block) {
            $_SESSION['flash_error'] = "El espacio está bloqueado en ese horario por: " . htmlspecialchars($block['reason']);
            $this->redirect('/bookings/create');
            return;
        }

        // Gratuidad requiere permiso
        if ($data['is_free'] && !AuthService::hasPermission('bookings_free')) {
            $_SESSION['flash_error'] = 'No tiene permiso para crear reservas gratuitas';
            $this->redirect('/bookings/create');
            return;
        }
        if ($data['is_free'] && empty($data['free_reason'])) {
            $_SESSION['flash_error'] = 'Debe indicar el motivo de gratuidad';
            $this->redirect('/bookings/create');
            return;
        }

        // Generar folio
        $data['folio'] = $bookingModel->generateFolio();

        // Calcular totales (base, sin extras aún)
        $data['total_price'] = $data['base_price'] + $data['surcharge'] - $data['discount'];
        if ($data['is_free']) {
            $data['total_price'] = 0;
        }

        $userId = AuthService::userId();
        $id = $bookingModel->create($data, $userId);

        if ($id) {
            // Guardar ítems (extras seleccionados) y sumar al total
            $extrasTotal = $this->saveBookingItems($id);
            if ($extrasTotal > 0 && !$data['is_free']) {
                $data['total_price'] += $extrasTotal;
                // Actualizar total_price en la BD con extras incluidos
                $bookingModel->updateTotalPrice($id, $data['total_price']);
            }

            // Sincronizar finanzas si la reserva se crea como confirmada
            if ($data['booking_status'] === 'confirmada') {
                $bookingModel->syncFinance($id, $userId);
            }

            // Historial
            (new SpaceBookingHistoryModel())->add($id, $userId, 'creado', "Reserva creada: {$data['folio']}");
            (new AuditModel())->add($userId, 'reservas_espacios', 'crear', "Reserva {$data['folio']} creada");

            $_SESSION['flash_success'] = "Reserva {$data['folio']} creada exitosamente";
            $this->redirect('/bookings/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al crear la reserva';
            $this->redirect('/bookings/create');
        }
    }
    // Fin de la función store()

    /**
     * Actualiza una reserva existente.
     * Permiso requerido: bookings_edit
     */
    public function update($id)
    {
        PermissionMiddleware::check('bookings_edit');
        csrf_verify();

        $data = $this->collectFormData();
        $id = (int)$id;

        // Validaciones básicas
        if (empty($data['space_id']) || empty($data['start_datetime']) || empty($data['end_datetime'])) {
            $_SESSION['flash_error'] = 'Faltan datos obligatorios';
            $this->redirect('/bookings/edit/' . $id);
            return;
        }

        $bookingModel = new SpaceBookingModel();
        
        // Verificar traslape excluyendo actual
        $overlap = $bookingModel->checkOverlap($data['space_id'], $data['start_datetime'], $data['end_datetime'], $id);
        if ($overlap) {
            $_SESSION['flash_error'] = "Conflicto: el espacio ya tiene una reserva ({$overlap['folio']})";
            $this->redirect('/bookings/edit/' . $id);
            return;
        }

        // Recalcular total (base, sin extras aún)
        $data['total_price'] = $data['base_price'] + $data['surcharge'] - $data['discount'];
        if ($data['is_free']) $data['total_price'] = 0;

        $userId = AuthService::userId();
        if ($bookingModel->update($id, $data, $userId)) {
            // Re-sincronizar ítems (extras): borrar y re-insertar
            (new SpaceBookingItemModel())->deleteByBookingId($id);
            $extrasTotal = $this->saveBookingItems($id);

            // Sumar extras al total y actualizar en la BD
            if ($extrasTotal > 0 && !$data['is_free']) {
                $data['total_price'] += $extrasTotal;
                $bookingModel->updateTotalPrice($id, $data['total_price']);
            }

            // Sincronizar finanzas si la reserva está confirmada
            if ($data['booking_status'] === 'confirmada') {
                $bookingModel->syncFinance($id, $userId);
            }

            (new SpaceBookingHistoryModel())->add($id, $userId, 'editado', "Reserva actualizada por el usuario");
            $_SESSION['flash_success'] = 'Reserva actualizada correctamente';
            $this->redirect('/bookings/show/' . $id);
        } else {
            $_SESSION['flash_error'] = 'Error al actualizar la reserva';
            $this->redirect('/bookings/edit/' . $id);
        }
    }
    // Fin de la función update()

    /**
     * Muestra detalle de una reserva.
     * Permiso requerido: bookings_view
     */
    public function show($id)
    {
        PermissionMiddleware::check('bookings_view');

        $booking = (new SpaceBookingModel())->getById((int)$id);
        if (!$booking) {
            $_SESSION['flash_error'] = 'Reserva no encontrada';
            $this->redirect('/bookings');
            return;
        }

        $items = (new SpaceBookingItemModel())->getByBookingId((int)$id);
        $history = (new SpaceBookingHistoryModel())->getByBookingId((int)$id);
        
        $paymentInfo = [];
        if (!empty($booking['contract_id'])) {
            // Pagos en el contrato vinculados a esta reserva
            $paymentInfo = (new PaymentModel())->conn->query("
                SELECT * FROM doc_contract_payments 
                WHERE booking_id = " . (int)$id . " AND active = 1
            ")->fetch_all(MYSQLI_ASSOC);
        } else {
            // Pagos directos de la reserva
            $paymentInfo = (new BookingPaymentModel())->getByBookingId((int)$id);
        }

        $this->view('bookings/show', compact('booking', 'items', 'history', 'paymentInfo'));
    }
    // Fin de la función show()

    /**
     * Cancela una reserva.
     * Permiso requerido: bookings_cancel
     */
    public function cancel($id)
    {
        PermissionMiddleware::check('bookings_cancel');
        csrf_verify();

        $reason = trim($_POST['cancel_reason'] ?? '');
        if (empty($reason)) {
            $_SESSION['flash_error'] = 'Debe indicar el motivo de cancelación';
            $this->redirect('/bookings/show/' . $id);
            return;
        }

        $userId = AuthService::userId();
        $bookingModel = new SpaceBookingModel();
        $booking = $bookingModel->getById((int)$id);

        if (!$booking) {
            $_SESSION['flash_error'] = 'Reserva no encontrada';
            $this->redirect('/bookings');
            return;
        }

        if ($bookingModel->cancel((int)$id, $reason, $userId)) {
            (new SpaceBookingHistoryModel())->add((int)$id, $userId, 'cancelado', "Motivo: {$reason}");
            (new AuditModel())->add($userId, 'reservas_espacios', 'cancelar', "Reserva {$booking['folio']} cancelada");
            $_SESSION['flash_success'] = 'Reserva cancelada';
        } else {
            $_SESSION['flash_error'] = 'Error al cancelar la reserva';
        }
        $this->redirect('/bookings/show/' . $id);
    }
    // Fin de la función cancel()

    /**
     * Cambia el estado de una reserva.
     * Permiso requerido: bookings_edit
     */
    public function changeStatus($id)
    {
        PermissionMiddleware::check('bookings_edit');
        csrf_verify();

        $status = $_POST['booking_status'] ?? '';
        $validStatuses = ['borrador', 'confirmada', 'en_uso', 'finalizada', 'no_asistio'];
        if (!in_array($status, $validStatuses)) {
            $_SESSION['flash_error'] = 'Estado invalido';
            $this->redirect('/bookings/show/' . $id);
            return;
        }

        $userId = AuthService::userId();
        $bookingModel = new SpaceBookingModel();

        if ($bookingModel->changeStatus((int)$id, $status, $userId)) {
            (new SpaceBookingHistoryModel())->add((int)$id, $userId, 'cambio_estado', "Nuevo estado: {$status}");
            $_SESSION['flash_success'] = 'Estado actualizado a: ' . ucfirst(str_replace('_', ' ', $status));
        } else {
            $_SESSION['flash_error'] = 'Error al cambiar estado';
        }
        $this->redirect('/bookings/show/' . $id);
    }
    // Fin de la función changeStatus()

    /**
     * Vista de calendario.
     * Permiso requerido: bookings_view
     */
    public function calendar()
    {
        PermissionMiddleware::check('bookings_view');

        $spaces = (new SpaceModel())->getAll(['active' => 1]);
        $this->view('bookings/calendar', compact('spaces'));
    }
    // Fin de la función calendar()

    /**
     * API JSON: retorna reservas para el calendario.
     * Permiso requerido: bookings_view
     */
    public function calendarData()
    {
        PermissionMiddleware::check('bookings_view');

        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $spaceId = !empty($_GET['space_id']) ? (int)$_GET['space_id'] : null;

        $bookings = (new SpaceBookingModel())->getByDateRange($start, $end, $spaceId);

        $statusColors = [
            'borrador' => '#6c757d', 'confirmada' => '#198754',
            'en_uso' => '#0dcaf0', 'finalizada' => '#212529',
            'cancelada' => '#dc3545', 'no_asistio' => '#ffc107'
        ];

        $events = [];
        foreach ($bookings as $b) {
            $status = $b['booking_status'];
            $color = $b['calendar_color'] ?? '#198754';
            $borderColor = $statusColors[$status] ?? '#6c757d';
            
            // Si está finalizada, bajamos un poco la intensidad para diferenciarla visualmente
            $opacity = ($status === 'finalizada') ? '0.7' : '1';

            $events[] = [
                'id'    => $b['id'],
                'title' => ($status === 'borrador' ? '📝 ' : '') . $b['space_name'] . ' — ' . ($b['company_name'] ?? $b['client_name'] ?? 'Sin cliente'),
                'start' => $b['start_datetime'],
                'end'   => $b['end_datetime'],
                'backgroundColor' => $color,
                'borderColor'     => $borderColor,
                'textColor'       => '#ffffff',
                'className'       => ($status === 'finalizada') ? 'fc-event-finalized' : '',
                'url'   => BASE_URL . '/bookings/show/' . $b['id'],
                'extendedProps' => [
                    'folio'  => $b['folio'],
                    'space'  => $b['space_name'],
                    'status' => $status,
                ]
            ];
        }

        // También incluir bloqueos
        $blockModel = new SpaceBlockModel();
        $blockRows = $blockModel->getByDateRange($start, $end);
        foreach ($blockRows as $row) {
            $events[] = [
                'id'    => 'block-' . $row['id'],
                'title' => '🚫 ' . $row['space_name'] . ' - ' . $row['reason'],
                'start' => $row['start_datetime'],
                'end'   => $row['end_datetime'],
                'color' => '#adb5bd',
                'display' => 'background',
            ];
        }

        $this->json($events);
    }
    // Fin de la función calendarData()

    /**
     * API JSON: verifica disponibilidad.
     */
    public function checkAvailability()
    {
        PermissionMiddleware::check('bookings_view');

        $spaceId = (int)($_GET['space_id'] ?? 0);
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $excludeId = !empty($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : null;

        if (!$spaceId || !$start || !$end) {
            $this->json(['available' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $bookingModel = new SpaceBookingModel();
        $overlap = $bookingModel->checkOverlap($spaceId, $start, $end, $excludeId);
        $block = $bookingModel->checkBlockOverlap($spaceId, $start, $end);

        if ($overlap) {
            $this->json([
                'available' => false,
                'conflict' => 'reserva',
                'folio' => $overlap['folio'],
                'message' => "Reserva {$overlap['folio']} ocupa este horario"
            ]);
        } elseif ($block) {
            $this->json([
                'available' => false,
                'conflict' => 'bloqueo',
                'message' => "Bloqueado: {$block['reason']}"
            ]);
        } else {
            $this->json(['available' => true, 'message' => 'Disponible']);
        }
    }
    // Fin de la función checkAvailability()

    /**
     * API JSON: retorna contratos vigentes de una empresa.
     */
    public function getContractsByCompany()
    {
        PermissionMiddleware::checkAny(['bookings_view', 'bookings_create']);

        $companyId = (int)($_GET['company_id'] ?? 0);
        if (!$companyId) {
            $this->json([]);
            return;
        }

        $contracts = (new ContractModel())->getAll([
            'company_id' => $companyId,
            'status'     => 'vigente'
        ]);

        $res = [];
        foreach ($contracts as $c) {
            $res[] = [
                'id'   => $c['id'],
                'code' => $c['code'] . ' (' . ucfirst($c['contract_type']) . ')'
            ];
        }

        $this->json($res);
    }
    // Fin de la función getContractsByCompany()

    // ===================================
    // HELPERS
    // ===================================

    /**
     * Recopila datos del formulario de reserva.
     */
    private function collectFormData()
    {
        $startDate = $_POST['start_date'] ?? '';
        $startTime = $_POST['start_time'] ?? '08:00';
        $endDate = $_POST['end_date'] ?? $startDate;
        $endTime = $_POST['end_time'] ?? '18:00';

        return [
            'space_id'       => (int)($_POST['space_id'] ?? 0),
            'company_id'     => !empty($_POST['company_id']) ? (int)$_POST['company_id'] : null,
            'contract_id'    => !empty($_POST['contract_id']) ? (int)$_POST['contract_id'] : null,
            'client_name'    => trim($_POST['client_name'] ?? ''),
            'booking_mode'   => $_POST['booking_mode'] ?? 'por_hora',
            'start_datetime' => "{$startDate} {$startTime}:00",
            'end_datetime'   => "{$endDate} {$endTime}:00",
            'qty_hours'      => !empty($_POST['qty_hours']) ? (float)$_POST['qty_hours'] : null,
            'qty_days'       => !empty($_POST['qty_days']) ? (int)$_POST['qty_days'] : null,
            'qty_months'     => !empty($_POST['qty_months']) ? (int)$_POST['qty_months'] : null,
            'base_price'     => (float)($_POST['base_price'] ?? 0),
            'discount'       => (float)($_POST['discount'] ?? 0),
            'surcharge'      => (float)($_POST['surcharge'] ?? 0),
            'total_price'    => 0,
            'is_free'        => (int)($_POST['is_free'] ?? 0),
            'free_reason'    => trim($_POST['free_reason'] ?? ''),
            'booking_status' => $_POST['booking_status'] ?? 'confirmada',
            'notes_client'   => trim($_POST['notes_client'] ?? ''),
            'notes_internal' => trim($_POST['notes_internal'] ?? ''),
            'origin'         => trim($_POST['origin'] ?? ''),
        ];
    }
    // Fin de la función collectFormData()

    /**
     * Guarda los ítems (extras) de una reserva.
     *
     * @param  int $bookingId ID de la reserva
     * @return float Total acumulado de extras
     */
    private function saveBookingItems($bookingId)
    {
        $itemModel = new SpaceBookingItemModel();
        $totalExtras = 0.0;

        // Extras seleccionados
        $extraIds = $_POST['extra_ids'] ?? [];
        $extraQtys = $_POST['extra_qtys'] ?? [];

        if (!is_array($extraIds)) return $totalExtras;

        $extraModel = new SpaceExtraModel();
        foreach ($extraIds as $i => $extraId) {
            $extra = $extraModel->getById((int)$extraId);
            if (!$extra) continue;

            $qty = (float)($extraQtys[$i] ?? 1);
            $subtotal = $qty * (float)$extra['unit_price'];
            $totalExtras += $subtotal;

            $itemModel->create([
                'booking_id'  => $bookingId,
                'item_type'   => 'extra',
                'extra_id'    => (int)$extraId,
                'description' => $extra['name'],
                'quantity'    => $qty,
                'unit'        => $extra['charge_type'],
                'unit_price'  => (float)$extra['unit_price'],
                'subtotal'    => $subtotal,
            ]);
        }

        return $totalExtras;
    }
    // Fin de la función saveBookingItems()
}
