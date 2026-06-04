<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif;">
  <div style="max-width:600px;margin:auto;border:1px solid #eee;border-radius:8px;overflow:hidden">
    <div style="background:#212529;color:#fff;padding:14px 16px">
      <strong><?= htmlspecialchars($t ?? 'Notificación') ?></strong>
    </div>
    <div style="padding:16px">
      <p style="margin-top:0"><?= nl2br(htmlspecialchars($m ?? '')) ?></p>
      <hr>
      <p style="color:#666;font-size:12px;margin:0">Enviado por Starter Kit RKM</p>
    </div>
  </div>
</body>
</html>
