<?php
// Variables disponibles: $tablero (opcional), $tableros_nav (opcional)
$tableros_nav   = $tableros_nav  ?? [];
$tablero_actual = $tablero       ?? null;
$color_fondo    = $tablero_actual['fondo_color']  ?? '#1e3a5f';
$fondo_imagen   = $tablero_actual['fondo_imagen'] ?? '';
$body_style     = $fondo_imagen
    ? 'background:url(' . htmlspecialchars($fondo_imagen) . ') center/cover no-repeat fixed ' . htmlspecialchars($color_fondo) . ';'
    : 'background:' . htmlspecialchars($color_fondo) . ';';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $tablero_actual ? htmlspecialchars($tablero_actual['nombre']) . ' — ' : '' ?>Tableros de proyectos Kanban · Atankalama</title>

  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="<?= $color_fondo ?>">

  <link href="<?= BASE_URL ?>/assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/vendor/flatpickr/flatpickr.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="kanban-body" style="<?= $body_style ?>">

<!-- ── Navbar ──────────────────────────────────────────────────────────────── -->
<nav class="navbar navbar-dark kanban-navbar px-3" style="background:<?= $color_fondo ?>dd;">
  <a class="navbar-brand fw-bold me-3" href="<?= BASE_URL ?>/tablero">
    <i class="bi bi-layout-three-columns me-1"></i> Tableros de proyectos Kanban
  </a>

  <?php
    $ruta_actual    = trim($GLOBALS['ruta'] ?? '', '/');
    $en_planificador = str_starts_with($ruta_actual, 'planificador');
  ?>
  <?php if (!empty($tableros_nav)): ?>
  <div class="d-flex gap-1 flex-wrap me-auto">
    <?php foreach ($tableros_nav as $tn): ?>
      <?php $activo = $tablero_actual && $tablero_actual['id'] == $tn['id']; ?>
      <a href="<?= BASE_URL ?>/tablero/ver?id=<?= $tn['id'] ?>"
         class="btn btn-sm tablero-chip <?= $activo ? 'active' : '' ?>"
         style="--chip-color:<?= $tn['fondo_color'] ?>">
        <?= htmlspecialchars($tn['nombre']) ?>
      </a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/planificador"
       class="btn btn-sm tablero-chip <?= $en_planificador ? 'active' : '' ?>">
      <i class="bi bi-calendar3 me-1"></i> Planificador
    </a>
  </div>
  <?php endif; ?>

  <div class="d-flex align-items-center gap-2 ms-3">
    <span class="text-white-50 small d-none d-md-inline">
      <?= htmlspecialchars($GLOBALS['email'] ?? '') ?>
    </span>
    <a href="<?= BASE_URL ?>/tableroAdmin"
       class="btn btn-sm btn-outline-light" title="Administrar accesos">
      <i class="bi bi-shield-lock"></i>
    </a>
    <a href="<?= BASE_URL ?>/dashboard"
       class="btn btn-sm <?= str_starts_with($ruta_actual, 'dashboard') ? 'btn-light' : 'btn-outline-light' ?>"
       title="Dashboard de Indicadores">
      <i class="bi bi-speedometer2 me-1"></i>
      <span class="d-none d-md-inline">Dashboard</span>
    </a>
    <a href="<?= BASE_URL ?>/misTareas"
       class="btn btn-sm <?= str_starts_with($ruta_actual, 'misTareas') ? 'btn-light' : 'btn-outline-light' ?>"
       title="Mis Tareas">
      <i class="bi bi-person-check me-1"></i>
      <span class="d-none d-md-inline">Mis Tareas</span>
    </a>
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
