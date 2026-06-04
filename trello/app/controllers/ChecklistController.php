<?php
class ChecklistController extends Controller
{
    private int $usuario_id;
    private ChecklistModel $modelo;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->modelo        = new ChecklistModel();
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        $this->usuario_id = $uid;
    }

    // POST /checklist/crear  { tarjeta_id, titulo }
    public function crear(): void
    {
        $d          = $this->input();
        $tarjeta_id = (int)($d['tarjeta_id'] ?? 0);
        $titulo     = trim($d['titulo'] ?? 'Lista de verificación');
        if (!$tarjeta_id || !$this->puedeEditar($tarjeta_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $id = $this->modelo->crearChecklist($tarjeta_id, $titulo);
        $this->json(['ok' => true, 'id' => $id, 'titulo' => $titulo]);
    }

    // POST /checklist/addItem  { checklist_id, texto, fecha, prioridad, responsable_id }
    public function addItem(): void
    {
        $d              = $this->input();
        $checklist_id   = (int)($d['checklist_id'] ?? 0);
        $texto          = trim($d['texto'] ?? '');
        $fecha          = !empty($d['fecha']) ? $d['fecha'] : null;
        $prioridad      = !empty($d['prioridad']) ? $d['prioridad'] : 'normal';
        $responsable_id = !empty($d['responsable_id']) ? (int)$d['responsable_id'] : null;

        if (!$checklist_id || !$texto) $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);

        $tarjeta_id = $this->modelo->tarjetaDe($checklist_id);
        if (!$tarjeta_id || !$this->puedeEditar($tarjeta_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $id = $this->modelo->agregarItem($checklist_id, $texto, $fecha, $prioridad, $responsable_id);
        $this->json([
            'ok' => true, 
            'id' => $id, 
            'texto' => $texto, 
            'fecha' => $fecha, 
            'prioridad' => $prioridad,
            'responsable_id' => $responsable_id
        ]);
    }

    // POST /checklist/updateItem  { item_id, data }
    public function updateItem(): void
    {
        $d       = $this->input();
        $item_id = (int)($d['item_id'] ?? 0);
        $data    = $d['data'] ?? [];

        if (!$item_id || empty($data)) $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);

        $tarjeta_id = $this->modelo->tarjetaDeItem($item_id);
        if (!$tarjeta_id || !$this->puedeEditar($tarjeta_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $this->modelo->actualizarItem($item_id, $data);
        $this->json(['ok' => true]);
    }

    // POST /checklist/toggle  { item_id }
    public function toggle(): void
    {
        $d       = $this->input();
        $item_id = (int)($d['item_id'] ?? 0);
        if (!$item_id) $this->json(['ok' => false], 400);

        $tarjeta_id = $this->modelo->tarjetaDeItem($item_id);
        if (!$tarjeta_id || !$this->puedeEditar($tarjeta_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $this->modelo->toggleItem($item_id);
        $this->json(['ok' => true]);
    }

    // POST /checklist/eliminarItem  { item_id }
    public function eliminarItem(): void
    {
        $d       = $this->input();
        $item_id = (int)($d['item_id'] ?? 0);
        if (!$item_id) $this->json(['ok' => false], 400);

        $tarjeta_id = $this->modelo->tarjetaDeItem($item_id);
        if (!$tarjeta_id || !$this->puedeEditar($tarjeta_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $this->modelo->eliminarItem($item_id);
        $this->json(['ok' => true]);
    }

    // POST /checklist/eliminar  { id }
    public function eliminar(): void
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) $this->json(['ok' => false], 400);

        $tarjeta_id = $this->modelo->tarjetaDe($id);
        if (!$tarjeta_id || !$this->puedeEditar($tarjeta_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $this->modelo->eliminarChecklist($id);
        $this->json(['ok' => true]);
    }

    private function puedeEditar(int $tarjeta_id): bool
    {
        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        return $tablero_id && $this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
