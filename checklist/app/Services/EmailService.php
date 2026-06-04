<?php

namespace App\Services;

/**
 * ===================================================
 * Servicio: EmailService
 * Proyecto: Hotel Atankalama – Sistema de Novedades
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * Gestionar el envío de correos electrónicos del sistema,
 * incluyendo códigos OTP y correos de bienvenida.
 */
class EmailService
{
    /**
     * Envía un correo electrónico con el código OTP para inicio de sesión.
     *
     * Qué hace:
     * - Configura el asunto y el cuerpo del mensaje en HTML.
     * - Define las cabeceras MIME necesarias.
     * - Llama a la función mail() nativa de PHP.
     *
     * @param string $email Dirección de correo del destinatario.
     * @param string $otp Código de acceso generado.
     * @return bool True si el correo fue aceptado para su entrega, false en caso contrario.
     */
    public static function sendOTP($email, $otp)
    {
        $subject = "Tu código de acceso - " . APP_NAME;
        $message = "
        <html>
        <head>
          <title>Código de Acceso</title>
        </head>
        <body>
          <h2>Hola,</h2>
          <p>Tu código de acceso para el sistema de checklist es:</p>
          <h1 style='color: #0d6efd; font-size: 32px; letter-spacing: 5px;'>" . $otp . "</h1>
          <p>Este código expirará en " . OTP_EXPIRATION_MINUTES . " minutos.</p>
          <p>Si no solicitaste este código, puedes ignorar este correo.</p>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";

        // Nota: El uso de mail() nativo depende de la configuración de php.ini
        // Si el servidor requiere SMTP auth directo en el script, se debería usar PHPMailer.
        return mail($email, $subject, $message, $headers);
    }
    // Fin de la función sendOTP()

    /**
     * Envía un correo de bienvenida a un nuevo usuario del sistema.
     *
     * Qué hace:
     * - Genera un mensaje HTML con instrucciones de acceso y enlace al portal.
     * - Utiliza constantes globales para la configuración del remitente.
     *
     * @param string $email Correo del nuevo usuario.
     * @return bool Resultado del envío.
     */
    public static function sendWelcomeEmail($email)
    {
        $subject = "Bienvenido al Sistema de Checklist - Atankalama";
        $loginUrl = "https://www.atankalama.com/checklist/";

        $message = "
        <html>
        <head>
          <title>Bienvenida al Sistema de Checklist</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
          <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
            <h2 style='color: #0d6efd;'>¡Bienvenido a Atankalama!</h2>
            <p>Has sido invitado a utilizar el <strong>Módulo de Checklist Hotelero</strong>.</p>
            <p>Para acceder al sistema, por favor utiliza el siguiente enlace:</p>
            <p style='text-align: center; margin: 30px 0;'>
              <a href='" . $loginUrl . "' style='background-color: #0d6efd; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Acceder al Sistema</a>
            </p>
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <p><strong>Instrucciones de acceso:</strong></p>
            <ul>
              <li>Ingresa tu cuenta de correo corporativo: <strong>" . $email . "</strong></li>
              <li>El sistema te enviará una <strong>clave de acceso (OTP)</strong> a este mismo correo cada vez que necesites iniciar sesión.</li>
              <li>Utiliza esa clave para completar el ingreso.</li>
            </ul>
            <p style='font-size: 0.9em; color: #666;'>Si tienes problemas para acceder, contacta al administrador del sistema.</p>
          </div>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">" . "\r\n";

        return mail($email, $subject, $message, $headers);
    }
    // Fin de la función sendWelcomeEmail()

    /**
     * Notifica a la lista de distribución del hotel que se creó una nueva encuesta pública.
     *
     * @param string $nombre       Nombre del checklist/encuesta.
     * @param string $area         Área asignada.
     * @param string $creadoPor    Email del usuario que la creó.
     * @param string $link         URL pública de la encuesta.
     * @return bool Resultado del envío.
     */
    public static function sendNuevaEncuesta($nombre, $area, $creadoPor, $link)
    {
        $nombre    = htmlspecialchars($nombre);
        $area      = htmlspecialchars($area);
        $creadoPor = htmlspecialchars($creadoPor);
        $link      = htmlspecialchars($link);
        $fecha     = date('d/m/Y H:i');

        $subject = "Nueva encuesta disponible: {$nombre}";

        $message = "
        <!DOCTYPE html>
        <html lang='es'>
        <body style='margin:0;padding:0;background:#f1f3f5;font-family:Arial,sans-serif;color:#333;'>
          <div style='max-width:620px;margin:30px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>
            <div style='background:#0d6efd;padding:28px 32px;'>
              <p style='margin:0;color:rgba(255,255,255,.7);font-size:12px;text-transform:uppercase;letter-spacing:1px;'>Hotel Atankalama &middot; Sistema de Checklist</p>
              <h1 style='margin:6px 0 0;color:#fff;font-size:22px;'>Nueva encuesta creada</h1>
            </div>
            <div style='padding:28px 32px;'>
              <p style='margin:0 0 20px;'>Se ha creado una nueva encuesta pública en el sistema.</p>
              <table style='width:100%;border-collapse:collapse;font-size:14px;'>
                <tr style='background:#f8f9fa;'>
                  <td style='padding:10px 14px;font-weight:bold;width:140px;border-bottom:1px solid #dee2e6;'>Encuesta</td>
                  <td style='padding:10px 14px;border-bottom:1px solid #dee2e6;'>{$nombre}</td>
                </tr>
                <tr>
                  <td style='padding:10px 14px;font-weight:bold;border-bottom:1px solid #dee2e6;'>Área</td>
                  <td style='padding:10px 14px;border-bottom:1px solid #dee2e6;'>{$area}</td>
                </tr>
                <tr style='background:#f8f9fa;'>
                  <td style='padding:10px 14px;font-weight:bold;border-bottom:1px solid #dee2e6;'>Creada por</td>
                  <td style='padding:10px 14px;border-bottom:1px solid #dee2e6;'>{$creadoPor}</td>
                </tr>
                <tr>
                  <td style='padding:10px 14px;font-weight:bold;'>Fecha</td>
                  <td style='padding:10px 14px;'>{$fecha}</td>
                </tr>
              </table>
              <div style='text-align:center;margin:28px 0;'>
                <a href='{$link}' style='background:#0d6efd;color:#fff;padding:13px 28px;text-decoration:none;border-radius:6px;font-weight:bold;font-size:15px;'>Abrir encuesta</a>
              </div>
              <p style='font-size:12px;color:#6c757d;margin:0;'>Este mensaje fue generado automáticamente. Por favor no responder este correo.</p>
            </div>
            <div style='background:#f8f9fa;padding:14px 32px;border-top:1px solid #dee2e6;font-size:11px;color:#6c757d;'>
              &copy; " . date('Y') . " Rodrigo Jaque Escobar &mdash; Hotel Atankalama &middot; Sistema de Checklist
            </div>
          </div>
        </body>
        </html>";

        $destinatarioPrincipal = 'nayrarobles@atankalama.com';
        $bcc = [
            'novedades@atankalama.com',
            'pamelawaldron@atankalama.com',
            'osvaldocampos@atankalama.com',
            'rodrigojaque@atankalama.com',
            'jorgeperez@atankalama.com',
            'valeskasegura@atankalama.com',
            'mcristinac@atankalama.com',
            'nicolas@atankalama.com',
            'diegoortiz@atankalama.com',
        ];

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "Bcc: " . implode(', ', $bcc) . "\r\n";

        return mail($destinatarioPrincipal, $subject, $message, $headers);
    }

    /**
     * Envía correo de retroalimentación a un Recepcionista con copia a gerencia.
     * @deprecated Usar sendEvaluacionColaborador()
     */
    public static function sendReceptionistEvaluation($email_evaluado, $informe)
    {
        return self::sendEvaluacionColaborador($email_evaluado, $informe);
    }

    /**
     * Envía el resultado de una evaluación al colaborador evaluado, con CC a gerencia.
     *
     * @param string $email_evaluado Correo del colaborador evaluado (de chk_usuarios).
     * @param array  $informe        Array devuelto por ReportService::getEvaluationDetail().
     * @return bool  Resultado del envío.
     */
    public static function sendEvaluacionColaborador($email_evaluado, $informe)
    {
        $nombreEvaluado  = htmlspecialchars(trim(($informe['evaluado_nombre'] ?? '') . ' ' . ($informe['evaluado_apellido'] ?? '')));
        $checklist       = htmlspecialchars($informe['checklist_nombre'] ?? '');
        $area            = htmlspecialchars($informe['area'] ?? '');
        $ejecutadoPor    = htmlspecialchars($informe['ejecutado_por'] ?? '');
        $fechaEval       = !empty($informe['fecha_fin'])
                            ? date('d/m/Y H:i', strtotime($informe['fecha_fin']))
                            : date('d/m/Y H:i', strtotime($informe['fecha_evaluacion'] ?? 'now'));

        $cumplimiento = $informe['stats']['cumplimiento'] ?? 0;
        $color = $cumplimiento >= 80 ? '#198754' : ($cumplimiento >= 50 ? '#fd7e14' : '#dc3545');

        // Construir filas de respuestas
        $htmlRespuestas = '';
        $currentGroup   = null;
        foreach ($informe['respuestas'] as $index => $res) {
            if (!empty($res['grupo']) && $res['grupo'] !== $currentGroup) {
                $htmlRespuestas .= "
                    <tr>
                      <td colspan='2' style='background:#f0f4ff;padding:10px 12px;font-weight:bold;color:#0d6efd;border-bottom:1px solid #dee2e6;'>
                        " . htmlspecialchars($res['grupo']) . "
                      </td>
                    </tr>";
                $currentGroup = $res['grupo'];
            }

            if ($res['tipo_respuesta'] === 'boolean') {
                if ($res['respuesta_boolean'] !== null && (int)$res['respuesta_boolean'] === 1) {
                    $resultado = "<span style='color:#198754;font-weight:bold;'>&#10003; SÍ</span>";
                } elseif ($res['respuesta_boolean'] !== null && (int)$res['respuesta_boolean'] === 0) {
                    $resultado = "<span style='color:#dc3545;font-weight:bold;'>&#10007; NO</span>";
                } else {
                    $resultado = "<span style='color:#6c757d;'>N/A</span>";
                }
            } elseif ($res['tipo_respuesta'] === 'numeric_scale') {
                $resultado = "<span style='color:#0d6efd;font-weight:bold;'>" . htmlspecialchars((string)$res['respuesta_numerica']) . "</span>";
            } elseif ($res['tipo_respuesta'] === 'foto') {
                $resultado = "<span style='color:#6c757d;font-style:italic;'>Foto adjunta</span>";
            } else {
                $resultado = !empty($res['respuesta_texto'])
                    ? nl2br(htmlspecialchars($res['respuesta_texto']))
                    : "<span style='color:#aaa;font-style:italic;'>Sin observación</span>";
            }

            $bg = ($index % 2 === 0) ? '#ffffff' : '#f8f9fa';
            $htmlRespuestas .= "
                <tr style='background:{$bg};'>
                  <td style='padding:9px 12px;border-bottom:1px solid #dee2e6;font-size:13px;'>" . ($index + 1) . ". " . htmlspecialchars($res['pregunta']) . "</td>
                  <td style='padding:9px 12px;border-bottom:1px solid #dee2e6;text-align:center;font-size:13px;white-space:nowrap;'>{$resultado}</td>
                </tr>";
        }

        $subject = "Evaluación completada: {$checklist} — {$fechaEval}";

        $message = "
        <!DOCTYPE html>
        <html lang='es'>
        <body style='margin:0;padding:0;background:#f1f3f5;font-family:Arial,sans-serif;color:#333;'>
          <div style='max-width:620px;margin:30px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);'>

            <!-- Cabecera -->
            <div style='background:#0d6efd;padding:28px 32px;'>
              <p style='margin:0;color:rgba(255,255,255,.7);font-size:12px;text-transform:uppercase;letter-spacing:1px;'>Hotel Atankalama · Sistema de Evaluaciones</p>
              <h1 style='margin:6px 0 0;color:#fff;font-size:22px;'>Resultados de tu evaluación</h1>
            </div>

            <!-- Saludo -->
            <div style='padding:28px 32px 0;'>
              <p style='margin:0 0 6px;'>Hola <strong>{$nombreEvaluado}</strong>,</p>
              <p style='margin:0;color:#555;font-size:14px;'>
                Se completó la evaluación <strong>{$checklist}</strong>
                " . (!empty($area) ? "— área <strong>{$area}</strong>" : "") . "
                el <strong>{$fechaEval}</strong>.
              </p>
            </div>

            <!-- Indicador de cumplimiento -->
            <div style='margin:24px 32px;background:#f8f9fa;border-radius:8px;padding:20px;text-align:center;'>
              <p style='margin:0 0 4px;font-size:12px;color:#6c757d;text-transform:uppercase;letter-spacing:1px;'>Cumplimiento total</p>
              <p style='margin:0;font-size:52px;font-weight:bold;color:{$color};line-height:1.1;'>{$cumplimiento}%</p>
              <p style='margin:6px 0 0;font-size:12px;color:#6c757d;'>
                &#10003;&nbsp;{$informe['stats']['total_si']} correctas &nbsp;·&nbsp; &#10007;&nbsp;{$informe['stats']['total_no']} incorrectas
              </p>
            </div>

            <!-- Tabla de respuestas -->
            <div style='padding:0 32px 28px;'>
              <h3 style='font-size:15px;color:#333;border-bottom:2px solid #dee2e6;padding-bottom:8px;margin-bottom:0;'>Detalle de respuestas</h3>
              <table style='width:100%;border-collapse:collapse;font-size:13px;margin-top:0;'>
                <thead>
                  <tr style='background:#e9ecef;'>
                    <th style='text-align:left;padding:10px 12px;font-size:12px;text-transform:uppercase;color:#6c757d;'>Criterio</th>
                    <th style='text-align:center;padding:10px 12px;font-size:12px;text-transform:uppercase;color:#6c757d;width:100px;'>Resultado</th>
                  </tr>
                </thead>
                <tbody>
                  {$htmlRespuestas}
                </tbody>
              </table>
            </div>

            <!-- Pie -->
            <div style='background:#f8f9fa;padding:16px 32px;border-top:1px solid #dee2e6;font-size:11px;color:#6c757d;'>
              Evaluación ejecutada por <strong>{$ejecutadoPor}</strong> · Generado automáticamente por el Sistema de Checklist Atankalama.
            </div>
          </div>
        </body>
        </html>";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . SMTP_FROM_NAME . " <" . SMTP_FROM_EMAIL . ">\r\n";
        $headers .= "Cc: rodrigojaque@atankalama.com, jorgeperez@atankalama.com, nayrarobles@atankalama.com\r\n";

        return mail($email_evaluado, $subject, $message, $headers);
    }
}
