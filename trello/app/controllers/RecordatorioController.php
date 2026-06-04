<?php
/**
 * RecordatorioController — endpoint para el cron job de vencimientos.
 *
 * Llamar vía cron una vez al día (recomendado: 08:00 AM):
 *   curl -s "https://www.atankalama.com/trello/public/recordatorio/ejecutar?token=CRON_TOKEN" > /dev/null
 *
 * El token está definido en config/config.php como CRON_TOKEN.
 * La ruta debe estar en las rutasPublicas de AccesoBootstrap (ya registrada en index.php).
 */
class RecordatorioController extends Controller
{
    private TarjetaModel $tarjetaModelo;

    public function __construct()
    {
        $this->tarjetaModelo = new TarjetaModel();
    }

    // GET /recordatorio/ejecutar?token=XXX
    public function ejecutar(): void
    {
        // Validar token
        $token = trim($_GET['token'] ?? '');
        if (!hash_equals(CRON_TOKEN, $token)) {
            http_response_code(403);
            die('Acceso denegado');
        }

        $enviados  = 0;
        $errores   = 0;
        $log       = [];

        // Tarjetas que vencen mañana
        $mañana = $this->tarjetaModelo->proximasAVencer(1);

        // Agrupar por usuario para no mandar múltiples correos si tiene varias tareas mañana
        $porUsuario = [];
        foreach ($mañana as $row) {
            $porUsuario[$row['usuario_id']]['datos'] = [
                'nombre'   => $row['usuario_nombre'] . ' ' . $row['usuario_apellido'],
                'email'    => $row['usuario_email'],
            ];
            $porUsuario[$row['usuario_id']]['tarjetas'][] = $row;
        }

        foreach ($porUsuario as $uid => $info) {
            try {
                $ok = $this->enviarRecordatorio($info['datos'], $info['tarjetas']);
                if ($ok) {
                    $enviados++;
                    $log[] = "OK → {$info['datos']['email']} (" . count($info['tarjetas']) . " tarea/s)";
                } else {
                    $errores++;
                    $log[] = "FAIL → {$info['datos']['email']}";
                }
            } catch (\Throwable $e) {
                $errores++;
                $log[] = "ERROR → {$info['datos']['email']}: " . $e->getMessage();
                app_log("RECORDATORIO ERROR uid=$uid: " . $e->getMessage());
            }
        }

        // Tarjetas que vencen hoy (segunda pasada, solo si hay)
        $hoy = $this->tarjetaModelo->proximasAVencer(0);
        $porUsuarioHoy = [];
        foreach ($hoy as $row) {
            $porUsuarioHoy[$row['usuario_id']]['datos'] = [
                'nombre' => $row['usuario_nombre'] . ' ' . $row['usuario_apellido'],
                'email'  => $row['usuario_email'],
            ];
            $porUsuarioHoy[$row['usuario_id']]['tarjetas'][] = $row;
        }
        foreach ($porUsuarioHoy as $uid => $info) {
            try {
                $ok = $this->enviarRecordatorio($info['datos'], $info['tarjetas'], true);
                if ($ok) { $enviados++; $log[] = "HOY OK → {$info['datos']['email']}"; }
                else     { $errores++; $log[] = "HOY FAIL → {$info['datos']['email']}"; }
            } catch (\Throwable $e) {
                $errores++;
                app_log("RECORDATORIO HOY ERROR uid=$uid: " . $e->getMessage());
            }
        }

        app_log("RECORDATORIO ejecutado: $enviados OK, $errores errores.");

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'       => true,
            'enviados' => $enviados,
            'errores'  => $errores,
            'detalle'  => $log,
            'timestamp'=> date('Y-m-d H:i:s'),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function enviarRecordatorio(array $usuario, array $tarjetas, bool $esHoy = false): bool
    {
        $cuantas = count($tarjetas);
        $cuando  = $esHoy ? 'HOY' : 'mañana';

        $asunto = $cuantas === 1
            ? "⏰ Tu tarea vence $cuando: {$tarjetas[0]['titulo']}"
            : "⏰ Tienes $cuantas tareas que vencen $cuando";

        $items_html = '';
        foreach ($tarjetas as $t) {
            $fv = $t['fecha_vencimiento']
                ? date('d/m/Y H:i', strtotime($t['fecha_vencimiento']))
                : '';
            $items_html .= '
            <tr>
              <td style="padding:10px 0;border-bottom:1px solid #f1f5f9;vertical-align:top">
                <div style="font-size:14px;font-weight:600;color:#1e293b">'
                    . htmlspecialchars($t['titulo']) . '</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px">
                  &#9724; ' . htmlspecialchars($t['tablero_nombre'])
                  . ' &rsaquo; ' . htmlspecialchars($t['lista_nombre']) . '
                </div>
                <div style="font-size:12px;color:#b45309;margin-top:2px">&#128197; ' . $fv . '</div>
              </td>
            </tr>';
        }

        $cuerpo_html = '
        <p style="font-size:14px;color:#475569;margin:0 0 16px">
            Hola <strong>' . htmlspecialchars($usuario['nombre']) . '</strong>,<br>
            tienes <strong>' . $cuantas . ' tarea' . ($cuantas !== 1 ? 's' : '') . '</strong>
            que vence' . ($cuantas !== 1 ? 'n' : '') . ' <strong>' . $cuando . '</strong>.
        </p>
        <table width="100%" cellpadding="0" cellspacing="0">' . $items_html . '</table>';

        $color = $esHoy ? '#ef4444' : '#f59e0b';

        $html = $this->renderEmail([
            'titulo_email' => $asunto,
            'cuerpo_html'  => $cuerpo_html,
            'color'        => $color,
            'url_app'      => BASE_URL . '/misTareas',
        ]);

        $mail = new MailService();
        return $mail->send($usuario['email'], $asunto, $html);
    }

    private function renderEmail(array $vars): string
    {
        extract($vars);
        ob_start();
        include VIEW_PATH . '/emails/kanban_notif.php';
        return ob_get_clean();
    }
}
