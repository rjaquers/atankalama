<?php
class ReferenciaController extends Controller
{
    private int $usuario_id;
    private ReferenciaModel $modelo;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->modelo        = new ReferenciaModel();
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        $this->usuario_id = $uid;
    }

    // POST /referencia/crear  { tarjeta_id, tablero_destino_id, lista_destino_id, mensaje }
    public function crear(): void
    {
        $d                  = $this->input();
        $tarjeta_id         = (int)($d['tarjeta_id']         ?? 0);
        $tablero_destino_id = (int)($d['tablero_destino_id'] ?? 0);
        $lista_destino_id   = (int)($d['lista_destino_id']   ?? 0);
        $mensaje            = trim($d['mensaje'] ?? '');

        if (!$tarjeta_id || !$tablero_destino_id || !$lista_destino_id) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }

        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $id = $this->modelo->crear($tarjeta_id, $tablero_destino_id, $lista_destino_id, $mensaje);
        if ($id === -1) {
            $this->json(['ok' => false, 'error' => 'Ya existe una referencia a ese tablero']);
            return;
        }
        $this->json(['ok' => true, 'id' => $id]);
    }

    // POST /referencia/eliminar  { id }
    public function eliminar(): void
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) $this->json(['ok' => false], 400);

        $tarjeta_id = $this->modelo->tarjetaDe($id);
        if (!$tarjeta_id) $this->json(['ok' => false, 'error' => 'No encontrado'], 404);

        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $this->modelo->eliminar($id);
        $this->json(['ok' => true]);
    }

    // GET /referencia/listas?tablero_id=ID o /referencia/listas/ID
    public function listas(string $tablero_id = ''): void
    {
        $tid  = (int)($tablero_id ?: ($_GET['tablero_id'] ?? 0));
        if (!$tid) {
            $this->json(['ok' => false, 'error' => 'ID de tablero requerido'], 400);
        }
        $listas = $this->tableroModelo->listasDelTablero($tid);
        $this->json(['ok' => true, 'listas' => $listas]);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
