<?php
class MiembroController extends Controller
{
    private int $usuario_id;
    private TarjetaMiembroModel $modelo;
    private TarjetaModel $tarjetaModelo;
    private TableroModel $tableroModelo;

    public function __construct()
    {
        global $email;
        $this->modelo        = new TarjetaMiembroModel();
        $this->tarjetaModelo = new TarjetaModel();
        $this->tableroModelo = new TableroModel();
        $uid = $this->tableroModelo->usuarioId($email ?? '');
        if (!$uid) $this->json(['ok' => false, 'error' => 'No autenticado'], 401);
        $this->usuario_id = $uid;
    }

    // POST /miembro/toggle  { tarjeta_id, usuario_id }
    public function toggle(): void
    {
        $d          = $this->input();
        $tarjeta_id = (int)($d['tarjeta_id'] ?? 0);
        $uid        = (int)($d['usuario_id'] ?? 0);
        if (!$tarjeta_id || !$uid) $this->json(['ok' => false], 400);

        $tablero_id = $this->tarjetaModelo->tableroDeTarjeta($tarjeta_id);
        if (!$tablero_id || !$this->tableroModelo->puedeEditar($tablero_id, $this->usuario_id)) {
            $this->json(['ok' => false, 'error' => 'Sin permiso'], 403);
        }
        $action = $this->modelo->toggle($tarjeta_id, $uid);

        if ($action === 'added') {
            $this->notificarAsignacion($tarjeta_id, $uid);
        }

        $this->json(['ok' => true, 'action' => $action]);
    }

    private function notificarAsignacion(int $tarjeta_id, int $uid): void
    {
        try {
            $destinatario = $this->modelo->datosUsuario($uid);
            if (!$destinatario) return;

            $tarjeta = $this->tarjetaModelo->porId($tarjeta_id);
            if (!$tarjeta) return;

            $fv = $tarjeta['fecha_vencimiento']
                ? date('d/m/Y', strtotime($tarjeta['fecha_vencimiento']))
                : null;

            $nombre = $destinatario['nombre'] . ' ' . $destinatario['apellido'];
            $asunto = '📋 Te asignaron una tarea: ' . $tarjeta['titulo'];

            $cuerpo_html = '<p style="font-size:14px;color:#475569;margin:0 0 12px">
                Hola <strong>' . htmlspecialchars($nombre) . '</strong>,<br>
                se te acaba de asignar la siguiente tarea en los Tableros Kanban de Atankalama.
            </p>';

            $html = $this->renderEmail([
                'titulo_email'   => $asunto,
                'cuerpo_html'    => $cuerpo_html,
                'tarjeta_titulo' => $tarjeta['titulo'],
                'tablero_nombre' => $tarjeta['tablero_nombre'],
                'lista_nombre'   => $tarjeta['lista_nombre'],
                'fecha_vencimiento' => $fv,
                'color'          => $tarjeta['fondo_color'],
                'url_app'        => BASE_URL . '/misTareas',
            ]);

            $mail = new MailService();
            $mail->send($destinatario['email'], $asunto, $html);

            app_log("NOTIF asignación enviada a {$destinatario['email']} para tarjeta $tarjeta_id");
        } catch (\Throwable $e) {
            app_log("NOTIF asignación ERROR: " . $e->getMessage());
        }
    }

    private function renderEmail(array $vars): string
    {
        extract($vars);
        ob_start();
        include VIEW_PATH . '/emails/kanban_notif.php';
        return ob_get_clean();
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        return $raw ? (json_decode($raw, true) ?? []) : $_POST;
    }
}
