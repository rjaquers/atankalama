<?php
class ComentarioController extends Controller
{
    private int $usuario_id;
    private ComentarioModel $modelo;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        app_log("INICIANDO ComentarioController para email: [" . ($email ?? 'NULL') . "]");
        $this->modelo        = new ComentarioModel();
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        
        if (!$uid) {
            app_log("ERROR: Usuario no autenticado en ComentarioController");
            $this->json(['ok' => false, 'error' => 'No autenticado'], 200); // 200 para ver el JSON
        }
        $this->usuario_id = $uid;
    }

    // POST /comentario/crear { tarjeta_id, comentario }
    public function crear(): void
    {
        try {
            $d = $this->input();
            $tid = (int)($d['tarjeta_id'] ?? 0);
            $txt = trim($d['comentario']  ?? '');

            app_log("Comentario: Intentando crear. Tarjeta=$tid, Usuario=" . $this->usuario_id);

            if (!$tid || !$txt) {
                $this->json(['ok' => false, 'error' => 'Datos incompletos'], 200);
            }

            $id = $this->modelo->crear($tid, $this->usuario_id, $txt);
            app_log("Comentario: Creado con ID=$id");

            $this->json([
                'ok' => true,
                'comentario' => [
                    'id' => $id,
                    'texto' => $txt,
                    'fecha' => date('d/m H:i'),
                    'usuario' => $_SESSION['user_name'] ?? 'Usuario'
                ]
            ], 200);
        } catch (Exception $e) {
            app_log("ERROR FATAL en ComentarioController: " . $e->getMessage());
            $this->json(['ok' => false, 'error' => $e->getMessage()], 200);
        }
    }

    // POST /comentario/eliminar { id }
    public function eliminar(): void
    {
        $d  = $this->input();
        $id = (int)($d['id'] ?? 0);
        if (!$id) $this->json(['ok' => false], 400);

        $ok = $this->modelo->eliminar($id, $this->usuario_id);
        $this->json(['ok' => $ok]);
    }

    private function input(): array
    {
        // Soporte para FormData (por el cambio que hicimos por el firewall)
        if (!empty($_POST)) return $_POST;
        
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : [];
    }
}
