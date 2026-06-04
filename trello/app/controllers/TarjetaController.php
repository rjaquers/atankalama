<?php
class TarjetaController extends Controller
{
    private int $usuario_id;
    private TarjetaModel $modelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->modelo       = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        
        app_log("CONSTRUCTOR TarjetaController: Email detectado [" . ($email ?? 'NULL') . "], UID resultante [" . ($uid ?? 'NULL') . "]");
        
        if (!$uid) {
            app_log("ERROR: Usuario no autenticado o no encontrado en chk_usuarios: " . ($email ?? 'NULL'));
            $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        }
        $this->usuario_id = $uid;
    }

    // GET /tarjeta/modal?id=ID o /tarjeta/modal/ID
    public function modal(string $id = ''): void
    {
        $id = (int)($id ?: ($_GET['id'] ?? 0));
        if (!$id) { http_response_code(400); die('ID de tarjeta requerido'); }

        $tarjeta = $this->modelo->porId($id);
        if (!$tarjeta) { http_response_code(404); exit; }

        $puede_editar = $this->tableroModelo->puedeEditar(
            $tarjeta['tablero_id'], $this->usuario_id
        );

        $clModelo    = new ChecklistModel();
        $etqModelo   = new EtiquetaModel();
        $mbrModelo   = new TarjetaMiembroModel();
        $adjModelo   = new AdjuntoModel();
        $refModelo   = new ReferenciaModel();
        $comModelo   = new ComentarioModel();

        $checklists        = $clModelo->porTarjeta((int)$id);
        $etiquetas_tablero = $etqModelo->porTablero($tarjeta['tablero_id']);
        $etiquetas_tarjeta = array_column($etqModelo->porTarjeta((int)$id), null, 'id');
        $miembros_tablero  = $mbrModelo->usuariosTablero($tarjeta['tablero_id']);
        $miembros_tarjeta  = array_column($mbrModelo->porTarjeta((int)$id), null, 'id');
        $adjuntos          = $adjModelo->porTarjeta((int)$id);
        $referencias       = $refModelo->porTarjeta((int)$id);
        $comentarios       = $comModelo->porTarjeta((int)$id);
        $tableros_otros    = array_filter(
            $this->tableroModelo->todos(),
            fn($t) => $t['id'] != $tarjeta['tablero_id']
        );

        $usuario_id = $this->usuario_id;
        $this->view('tarjeta/_modal', compact(
            'tarjeta', 'puede_editar', 'checklists',
            'etiquetas_tablero', 'etiquetas_tarjeta',
            'miembros_tablero', 'miembros_tarjeta',
            'adjuntos', 'referencias', 'tableros_otros',
            'comentarios', 'usuario_id'
        ));
    }

    // POST /tarjeta/crear
    public function crear(): void
    {
        $d = $this->input();
        $lista_id   = (int)($d['lista_id']   ?? 0);
        $tablero_id = (int)($d['tablero_id'] ?? 0);
        $titulo     = trim($d['titulo']      ?? '');

        app_log("SOLICITUD crear tarjeta: Tablero=$tablero_id, Lista=$lista_id, Titulo=$titulo por Usuario=" . $this->usuario_id);

        if (!$titulo || !$lista_id || !$tablero_id) {
            app_log("ERROR: Datos incompletos al crear tarjeta.");
            $this->json(['ok' => false, 'error' => 'Datos incompletos']);
        }
        
        if (!$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            app_log("ERROR: El usuario " . $this->usuario_id . " NO tiene permiso para editar en el tablero " . $tablero_id);
            // Devolvemos 200 en lugar de 403 para evitar que el servidor intercepte con una página HTML de error
            $this->json([
                'ok' => false, 
                'error' => 'Sin permiso de edición en este tablero (ID Tablero: '.$tablero_id.', ID Usuario: '.$this->usuario_id.')',
                'debug_uid' => $this->usuario_id,
                'debug_tid' => $tablero_id
            ], 200);
        }

        $tarjeta = $this->modelo->crear($lista_id, $tablero_id, $titulo, $this->usuario_id);
        if ($tarjeta) {
            $this->json(['ok' => true, 'tarjeta' => $tarjeta]);
        } else {
            app_log("ERROR: Falló la creación en el modelo para tablero $tablero_id.");
            $this->json(['ok' => false, 'error' => 'Error al crear']);
        }
    }

    // POST /tarjeta/guardar
    public function guardar(): void
    {
        $d                = $this->input();
        $id               = (int)($d['id']               ?? 0);
        $titulo           = trim($d['titulo']             ?? '');
        $descripcion      = trim($d['descripcion']        ?? '');
        $fecha_vencimiento = $d['fecha_vencimiento']      ?? '';
        $completada       = (int)($d['completada']        ?? 0);

        if (!$id || !$titulo) $this->json(['ok' => false, 'error' => 'Datos incompletos']);

        $tablero_id = $this->modelo->tableroDeTarjeta($id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $ok = $this->modelo->actualizar($id, $titulo, $descripcion, $fecha_vencimiento ?: null, $completada);
        $this->json(['ok' => $ok]);
    }

    // POST /tarjeta/archivar
    public function archivar(): void
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) $this->json(['ok' => false]);

        $tablero_id = $this->modelo->tableroDeTarjeta($id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $ok = $this->modelo->archivar($id);
        $this->json(['ok' => $ok]);
    }

    public function desarchivar(): void
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) $this->json(['ok' => false]);

        $tablero_id = $this->modelo->tableroDeTarjeta($id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $ok = $this->modelo->desarchivar($id);
        $this->json(['ok' => $ok]);
    }

    // POST /tarjeta/mover
    public function mover(): void
    {
        $d        = $this->input();
        $id       = (int)($d['id']       ?? 0);
        $lista_id = (int)($d['lista_id'] ?? 0);
        $prev_id  = isset($d['prev_id']) && $d['prev_id'] !== null ? (int)$d['prev_id'] : null;
        $next_id  = isset($d['next_id']) && $d['next_id'] !== null ? (int)$d['next_id'] : null;

        if (!$id || !$lista_id) $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);

        $tablero_id = $this->modelo->tableroDeTarjeta($id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }

        $ok = $this->modelo->mover($id, $lista_id, $prev_id, $next_id);
        $this->json(['ok' => $ok]);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
