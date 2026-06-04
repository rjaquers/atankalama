<!doctype html>
<html lang="es">
<head><meta charset="utf-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 0">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)">

      <!-- Cabecera con color del tablero -->
      <tr>
        <td style="background:<?= htmlspecialchars($color ?? '#1e3a5f') ?>;padding:20px 28px">
          <span style="color:#fff;font-size:18px;font-weight:700;letter-spacing:.02em">
            &#9776; Tableros Kanban &mdash; Atankalama
          </span>
        </td>
      </tr>

      <!-- Cuerpo -->
      <tr>
        <td style="padding:28px 28px 8px">
          <p style="margin:0 0 16px;font-size:15px;color:#1e293b;font-weight:600">
            <?= htmlspecialchars($titulo_email ?? 'Notificación del sistema') ?>
          </p>
          <?= $cuerpo_html ?? '' ?>
        </td>
      </tr>

      <!-- Tarjeta info-box -->
      <?php if (!empty($tarjeta_titulo)): ?>
      <tr>
        <td style="padding:0 28px 20px">
          <div style="background:#f8fafc;border-left:4px solid <?= htmlspecialchars($color ?? '#3b82f6') ?>;border-radius:0 8px 8px 0;padding:14px 16px">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px">
              Tarjeta
            </div>
            <div style="font-size:15px;font-weight:600;color:#1e293b">
              <?= htmlspecialchars($tarjeta_titulo) ?>
            </div>
            <?php if (!empty($tablero_nombre)): ?>
            <div style="font-size:12px;color:#64748b;margin-top:4px">
              &#9724; <?= htmlspecialchars($tablero_nombre) ?>
              <?= !empty($lista_nombre) ? ' &rsaquo; ' . htmlspecialchars($lista_nombre) : '' ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($fecha_vencimiento)): ?>
            <div style="font-size:12px;color:#b45309;margin-top:6px">
              &#128197; Vence: <?= htmlspecialchars($fecha_vencimiento) ?>
            </div>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endif; ?>

      <!-- Botón CTA -->
      <?php if (!empty($url_app)): ?>
      <tr>
        <td style="padding:0 28px 28px;text-align:center">
          <a href="<?= htmlspecialchars($url_app) ?>"
             style="display:inline-block;background:<?= htmlspecialchars($color ?? '#1e3a5f') ?>;color:#fff;text-decoration:none;
                    padding:12px 28px;border-radius:8px;font-size:14px;font-weight:600">
            Ver tarea &rarr;
          </a>
        </td>
      </tr>
      <?php endif; ?>

      <!-- Footer -->
      <tr>
        <td style="background:#f8fafc;padding:14px 28px;border-top:1px solid #e2e8f0">
          <p style="margin:0;font-size:11px;color:#94a3b8">
            &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Hotel Atankalama.
            Este correo fue generado automáticamente, por favor no responder.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
