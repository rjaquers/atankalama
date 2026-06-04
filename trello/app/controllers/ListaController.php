<?php
class ListaController extends Controller
{
    private int $usuario_id;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        $this->usuario_id = $uid;
    }

    // POST /lista/crear  { tablero_id, nombre }
    public function crear(): void
    {
        $d          = $this->input();
        $tablero_id = (int)($d['tablero_id'] ?? 0);
        $nombre     = trim($d['nombre'] ?? '');

        if (!$tablero_id || $nombre === '') {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }
        if (mb_strlen($nombre) > 60) {
            $this->json(['ok' => false, 'error' => 'El nombre no puede superar 60 caracteres']);
        }
        if (!$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso de edición en este tablero'], 403);
        }

        $lista = $this->tableroModelo->crearLista($tablero_id, $nombre);
        if ($lista) {
            $this->json(['ok' => true, 'lista' => $lista]);
        } else {
            $this->json(['ok' => false, 'error' => 'Error al crear la columna']);
        }
    }

    // POST /lista/eliminar  { lista_id }
    public function eliminar(): void
    {
        $d        = $this->input();
        $lista_id = (int)($d['lista_id'] ?? 0);

        if (!$lista_id) $this->json(['ok' => false, 'error' => 'ID de lista requerido'], 400);

        $tablero_id = $this->tableroModelo->tablerodeLista($lista_id);
        if (!$tablero_id) $this->json(['ok' => false, 'error' => 'Lista no encontrada'], 404);

        if (!$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso de edición en este tablero'], 403);
        }

        $result = $this->tableroModelo->eliminarLista($lista_id);
        $this->json($result);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
