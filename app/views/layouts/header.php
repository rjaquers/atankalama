<?php
// Variables disponibles: $tablero (opcional), $tableros_nav (opcional)
$tableros_nav  = $tableros_nav  ?? [];
$tablero_actual = $tablero      ?? null;
$color_fondo   = $tablero_actual['fondo_color'] ?? '#1e3a5f';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $tablero_actual ? htmlspecialchars($tablero_actual['nombre']) . ' — ' : '' ?>Trello · Atankalama</title>

  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="<?= $color_fondo ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="kanban-body">

<!-- ── Navbar ──────────────────────────────────────────────────────────────── -->
<nav class="navbar navbar-dark kanban-navbar px-3" style="background:<?= $color_fondo ?>dd;">
  <a class="navbar-brand fw-bold me-3" href="<?= BASE_URL ?>/tablero">
    <i class="bi bi-kanban-fill me-1"></i> Trello
  </a>

  <?php if (!empty($tableros_nav)): ?>
  <div class="d-flex gap-1 flex-wrap me-auto">
    <?php foreach ($tableros_nav as $tn): ?>
      <?php $activo = $tablero_actual && $tablero_actual['id'] == $tn['id']; ?>
      <a href="<?= BASE_URL ?>/tablero/ver/<?= $tn['id'] ?>"
         class="btn btn-sm tablero-chip <?= $activo ? 'active' : '' ?>"
         style="--chip-color:<?= $tn['fondo_color'] ?>">
        <?= htmlspecialchars($tn['nombre']) ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="d-flex align-items-center gap-2 ms-3">
    <span class="text-white-50 small d-none d-md-inline">
      <?= htmlspecialchars($GLOBALS['email'] ?? '') ?>
    </span>
    <a href="https://www.atankalama.com/login/index.php?route=dashboard"
       class="btn btn-sm btn-outline-light" title="Inicio">
      <i class="bi bi-house-door"></i>
    </a>
    <a href="<?= BASE_URL ?>/logout"
       class="btn btn-sm btn-outline-light" title="Salir">
      <i class="bi bi-power"></i>
    </a>
  </div>
</nav>
