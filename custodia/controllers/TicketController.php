<?php
// controllers/TicketController.php
declare(strict_types=1);

require_once __DIR__ . '/../models/Ticket.php';

class TicketController
{
    private Ticket $ticket;

    public function __construct()
    {
        $this->ticket = new Ticket();
    }

    public function crear(): void
    {

        include __DIR__ . '/../views/tickets/form.php';
    }


    public function listarVista(): void
    {
        // Reutiliza la lógica de index() para obtener datos
        $data = $this->index();

        // Expone variables tal como las espera la vista
        $rows   = $data['rows'];
        $total  = $data['total'];
        $page   = $data['page'];
        $pages  = $data['pages'];
        $params = $data['params'];

        // Renderiza la vista existente
        include __DIR__ . '/../views/tickets/listar.php';
    }


    /**
     * Lee filtros desde $_GET, llama al modelo y devuelve datos + paginación
     */
    public function index(): array
    {
        $q      = isset($_GET['q'])      ? trim((string)$_GET['q']) : '';
        $status = isset($_GET['status']) ? trim((string)$_GET['status']) : '';
        $mode   = isset($_GET['mode'])   ? trim((string)$_GET['mode']) : '';
        $from   = isset($_GET['from'])   ? trim((string)$_GET['from']) : '';
        $to     = isset($_GET['to'])     ? trim((string)$_GET['to']) : '';

        $page   = isset($_GET['page'])   ? max(1, (int)$_GET['page']) : 1;
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $filters = [
            'q'      => $q ?: null,
            'status' => $status ?: null,
            'mode'   => $mode ?: null,
            'from'   => $from ?: null,
            'to'     => $to ?: null,
            'limit'  => $limit,
            'offset' => $offset,
        ];

        $rows  = $this->ticket->listar($filters);
        $total = $this->ticket->contar($filters);
        $pages = (int)ceil(max(1, $total) / $limit);

        return [
            'rows'   => $rows,
            'total'  => $total,
            'page'   => $page,
            'pages'  => $pages,
            'limit'  => $limit,
            'params' => compact('q','status','mode','from','to'),
        ];
    }


// controllers/TicketController.php
    public function imprimirVista(int $id): void
    {
        $ticket = $this->ticket->obtenerPorId($id);
        if (!is_array($ticket)) {
            http_response_code(404);
            echo 'Ticket no encontrado.';
            return;
        }

        // Sumar impresión aquí (la vista no volverá a sumar si detecta este flag)
        $this->ticket->incrementarImpresion($id);
        $incremented = true;

        // URL a la que quieres volver después de imprimir
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        $listar_url = $base . '/tickets/custodia/listar';

        // Exponer variables que usa la vista
        include __DIR__ . '/../views/tickets/imprimir.php';
    }

    // controllers/TicketController.php (añade estos métodos a tu clase existente)

    public function nuevo(): void
    {
        // Solo renderiza el formulario
        include __DIR__ . '/../views/tickets/nuevo.php';
    }

    public function guardar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo 'Método no permitido';
            return;
        }

        // Sanitizar inputs
        $mode          = isset($_POST['mode']) && $_POST['mode'] === 'perdido' ? 'perdido' : 'custodia';
        $guest_name    = trim((string)($_POST['guest_name'] ?? '')) ?: null;
        $item_type     = trim((string)($_POST['item_type'] ?? '')) ?: null;
        $location_label= trim((string)($_POST['location_label'] ?? '')) ?: null;
        $notes         = trim((string)($_POST['notes'] ?? '')) ?: null;

        // Insertar
        try {
            $id = $this->ticket->crear([
                                           'mode'           => $mode,
                                           'guest_name'     => $guest_name,
                                           'item_type'      => $item_type,
                                           'location_label' => $location_label,
                                           'notes'          => $notes,
                                           // status/created_at/public_code/ip/user_agent se setean en el modelo
                                       ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo 'Error al guardar: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
            return;
        }

        // Redirige directo a imprimir
        $base = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '';
        header('Location: ' . $base . '/tickets/custodia/imprimir/' . (int)$id);
        exit;
    }





}
