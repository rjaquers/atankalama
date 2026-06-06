<?php
class TableroController extends Controller
{
    private int $usuario_id;
    private TableroModel $modelo;

    public function __construct()
    {
        global $email;
        require_once APP_PATH . '/helpers/kanban.php';
        $this->modelo = new TableroModel();
        $uid = $this->modelo->usuarioId($email ?? '');
        if (!$uid) {
            $this->redirect('/logout');
        }
        $this->usuario_id = $uid;
    }

    public function index(): void
    {
        $tableros = $this->modelo->todos();
        if (empty($tableros)) {
            $this->view('tablero/index', [
                'tableros_nav' => [],
                'tablero'      => null,
                'listas'       => [],
                'puede_editar' => false,
            ]);
            return;
        }
        $this->redirect('/tablero/ver?id=' . $tableros[0]['id']);
    }

    // GET /tablero/ver?id=ID o /tablero/ver/ID
    public function ver(string $id = ''): void
    {
        $id = (int)($id ?: ($_GET['id'] ?? 0));
        if (!$id) { $this->redirect('/tablero'); }

        $tablero = $this->modelo->porId($id);
        if (!$tablero) {
            $this->redirect('/tablero');
        }

        $listas = $this->modelo->listasConTarjetas((int)$id);

        // Inyectar referencias apuntando a este tablero en las listas correspondientes
        $refModelo = new ReferenciaModel();
        $refs = $refModelo->porTableroDestino((int)$id);
        $refs_por_lista = [];
        foreach ($refs as $r) {
            $refs_por_lista[$r['lista_destino_id']][] = $r;
        }
        foreach ($listas as &$lista) {
            $lista['referencias'] = $refs_por_lista[$lista['id']] ?? [];
        }

        $this->view('tablero/index', [
            'tablero'      => $tablero,
            'listas'       => $listas,
            'tableros_nav' => $this->modelo->todos(),
            'puede_editar' => $this->modelo->puedeEditar((int)$id, $this->usuario_id),
        ]);
    }

    // POST /tablero/fondo  { id, fondo_color, fondo_imagen }
    public function fondo(): void
    {
        $d            = $this->input();
        $id           = (int)($d['id']           ?? 0);
        $fondo_color  = trim($d['fondo_color']   ?? '');
        $fondo_imagen = trim($d['fondo_imagen']  ?? '');

        app_log("TableroController::fondo - ID: $id, Color: $fondo_color, Imagen: $fondo_imagen");

        if (!$id) {
            app_log("TableroController::fondo - Error: ID requerido");
            $this->json(['ok' => false, 'error' => 'ID requerido'], 400);
        }

        $puede_editar = $this->modelo->puedeEditar($id, $this->usuario_id);
        app_log("TableroController::fondo - Permiso puedeEditar(ID: $id, UID: {$this->usuario_id}): " . ($puede_editar ? 'SI' : 'NO'));

        if (!$puede_editar) {
            app_log("TableroController::fondo - Error: Sin permiso (Usuario: {$this->usuario_id} en Tablero: $id)");
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $fondo_color)) $fondo_color = '#1e3a5f';
        if ($fondo_imagen && preg_match('/^https:\/\/images\.unsplash\.com\//', $fondo_imagen)) {
            $local_path = $this->descargarImagen($fondo_imagen);
            if ($local_path) {
                $fondo_imagen = $local_path;
            }
        }

        $ok = $this->modelo->actualizarFondo($id, $fondo_color, $fondo_imagen);
        app_log("TableroController::fondo - Resultado: " . ($ok ? 'OK' : 'FALLÓ'));
        $this->json(['ok' => $ok]);
    }

    private function descargarImagen(string $url): ?string
    {
        $dir = PUBLIC_PATH . '/uploads/trello/backgrounds';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Obtener un nombre de archivo único basado en la URL (sin los parámetros de query si es posible)
        // O simplemente un hash MD5 de la URL completa para evitar duplicados.
        $filename = md5($url) . '.webp';
        $full_path = $dir . '/' . $filename;
        $relative_path = BASE_URL . '/uploads/trello/backgrounds/' . $filename;

        // Si ya existe, no descargamos de nuevo
        if (file_exists($full_path)) {
            return $relative_path;
        }

        // Descargar usando file_get_contents (si allow_url_fopen está activo) o cURL
        try {
            $content = @file_get_contents($url);
            if ($content === false) {
                app_log("descargarImagen - Error al descargar: $url");
                return null;
            }
            if (file_put_contents($full_path, $content)) {
                app_log("descargarImagen - Guardado local: $filename");
                return $relative_path;
            }
        } catch (Exception $e) {
            app_log("descargarImagen - Excepción: " . $e->getMessage());
        }

        return null;
    }

    // GET /tablero/miembros?id=ID
    public function miembros(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) $this->json(['ok' => false, 'error' => 'ID requerido'], 400);

        $mbrModelo = new TarjetaMiembroModel();
        $miembros = $mbrModelo->usuariosTablero($id);
        
        $this->json(['ok' => true, 'miembros' => $miembros]);
    }

    // GET /tablero/archivadas?id=ID
    public function archivadas(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) $this->json(['ok' => false, 'error' => 'Tablero ID requerido'], 400);

        if (!$this->modelo->puedeEditar($id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin acceso'], 403);
        }

        $tarjetaModelo = new TarjetaModel();
        $archivadas = $tarjetaModelo->archivadasPorTablero($id);
        $this->json(['ok' => true, 'archivadas' => $archivadas]);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
