<?php
class TableroAdminController extends Controller
{
    private int $usuario_id;
    private TableroModel $modelo;

    public function __construct()
    {
        global $email;
        $this->modelo = new TableroModel();
        $uid = $this->modelo->usuarioId($email ?? '');
        if (!$uid) $this->redirect('/logout');
        $this->usuario_id = $uid;
    }

    // GET /tableroAdmin — lista todos los tableros
    public function index(): void
    {
        $tableros      = $this->modelo->todosConConteoMiembros();
        $tableros_nav  = $this->modelo->todos();
        $areas         = $this->modelo->areasDisponibles();
        $this->view('tableroAdmin/index', compact('tableros', 'tableros_nav', 'areas'));
    }

    // POST /tableroAdmin/crear  { nombre, fondo_color, area_id }
    public function crear(): void
    {
        $d           = $this->input();
        $nombre      = trim($d['nombre'] ?? '');
        $fondo_color = trim($d['fondo_color'] ?? '#3b82f6');
        $area_id     = (int)($d['area_id'] ?? 0);

        if (!$nombre || !$area_id) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }

        $id = $this->modelo->crearTablero($nombre, $fondo_color, $area_id);
        $this->json(['ok' => true, 'id' => $id]);
    }

    // POST /tableroAdmin/actualizar  { id, nombre, fondo_color, area_id }
    public function actualizar(): void
    {
        $d           = $this->input();
        $id          = (int)($d['id']          ?? 0);
        $nombre      = trim($d['nombre']        ?? '');
        $fondo_color = trim($d['fondo_color']   ?? '#3b82f6');
        $area_id     = (int)($d['area_id']      ?? 0);

        if (!$id || !$nombre || !$area_id) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $fondo_color)) {
            $fondo_color = '#3b82f6';
        }

        $ok = $this->modelo->actualizarTablero($id, $nombre, $fondo_color, $area_id);
        $this->json(['ok' => $ok, 'nombre' => $nombre, 'fondo_color' => $fondo_color]);
    }

    // POST /tableroAdmin/eliminar  { id }
    public function eliminar(): void
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);

        if (!$id) {
            $this->json(['ok' => false, 'error' => 'ID requerido'], 400);
        }

        $ok = $this->modelo->eliminarTablero($id);
        $this->json(['ok' => $ok]);
    }

    // GET /tableroAdmin/miembros?id=ID o /tableroAdmin/miembros/ID
    public function miembros(string $id = ''): void
    {
        $id = (int)($id ?: ($_GET['id'] ?? 0));
        if (!$id) $this->redirect('/tableroAdmin');

        $tablero = $this->modelo->porId($id);
        if (!$tablero) $this->redirect('/tableroAdmin');

        $miembros          = $this->modelo->miembrosTablero($id);
        $usuarios_disponibles = $this->modelo->usuariosDisponibles($id);
        $tableros_nav      = $this->modelo->todos();

        $this->view('tableroAdmin/miembros', compact(
            'tablero', 'miembros', 'usuarios_disponibles', 'tableros_nav'
        ));
    }

    // POST /tableroAdmin/asignar  { tablero_id, usuario_id, puede_editar }
    public function asignar(): void
    {
        $d           = $this->input();
        $tablero_id  = (int)($d['tablero_id']  ?? 0);
        $usuario_id  = (int)($d['usuario_id']  ?? 0);
        $puede_editar = (bool)($d['puede_editar'] ?? false);

        if (!$tablero_id || !$usuario_id) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }

        $this->modelo->asignarUsuario($tablero_id, $usuario_id, $puede_editar);
        $this->json(['ok' => true]);
    }

    // POST /tableroAdmin/revocar  { tablero_id, usuario_id }
    public function revocar(): void
    {
        $d          = $this->input();
        $tablero_id = (int)($d['tablero_id'] ?? 0);
        $usuario_id = (int)($d['usuario_id'] ?? 0);

        if (!$tablero_id || !$usuario_id) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }

        $this->modelo->revocarUsuario($tablero_id, $usuario_id);
        $this->json(['ok' => true]);
    }

    // POST /tableroAdmin/toggleEditar  { tablero_id, usuario_id }
    public function toggleEditar(): void
    {
        $d          = $this->input();
        $tablero_id = (int)($d['tablero_id'] ?? 0);
        $usuario_id = (int)($d['usuario_id'] ?? 0);

        if (!$tablero_id || !$usuario_id) {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }

        $nuevo = $this->modelo->togglePuedeEditar($tablero_id, $usuario_id);
        $this->json(['ok' => true, 'puede_editar' => $nuevo]);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
