<?php
// Layout principal
$_ruta_actual  = trim($_GET['route'] ?? 'univ/index', '/');
$_es_admin     = str_starts_with($_ruta_actual, 'univAdmin');
$_es_alumno    = str_starts_with($_ruta_actual, 'univ') && !$_es_admin;
$_perfil       = $_SESSION['perfil'] ?? '';
$_puede_admin  = in_array($_perfil, ['Administrador', 'RRHH', 'Gerencia']);
$_titulo_pagina = $_es_admin ? 'Admin — Universidad Atankalama' : 'Universidad Atankalama';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= $_titulo_pagina ?></title>

  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="#212529">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark <?= $_es_admin ? 'bg-primary' : 'bg-dark' ?> navbar-expand-lg">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= BASE_URL ?>/index.php?route=univ/index">
      <i class="fa-solid fa-graduation-cap"></i>
      <?= $_es_admin ? '<span class="badge bg-warning text-dark me-1" style="font-size:.65rem;vertical-align:middle;">ADMIN</span>' : '' ?>
      Universidad Atankalama
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navUniv">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navUniv">
      <ul class="navbar-nav ms-auto align-items-center gap-1">
        <?php if(univ_email()): ?>

          <?php if ($_es_admin): ?>
            <!-- En modo admin: botón para ver como alumno -->
            <li class="nav-item">
              <a class="btn btn-sm btn-outline-light me-2"
                 href="<?= BASE_URL ?>/index.php?route=univ/index"
                 title="Ver los cursos tal como los ve un alumno">
                <i class="bi bi-person-video3 me-1"></i> Ver como Alumno
              </a>
            </li>
          <?php elseif ($_puede_admin): ?>
            <!-- En modo alumno: botón para volver al panel admin -->
            <li class="nav-item">
              <a class="btn btn-sm btn-outline-warning me-2"
                 href="<?= BASE_URL ?>/index.php?route=univAdmin/index"
                 title="Volver al panel de administración">
                <i class="bi bi-gear-fill me-1"></i> Panel Admin
              </a>
            </li>
          <?php endif; ?>

          <li class="nav-item">
            <a class="nav-link <?= $_es_alumno ? 'active fw-bold' : 'text-white-50' ?>" 
               href="<?= BASE_URL ?>/index.php?route=univ/index">
              <i class="fa-solid fa-book-open me-1"></i> Mis Cursos
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-white-50"
               href="https://www.atankalama.com/login/index.php?route=dashboard">
              <i class="bi bi-house-door me-1"></i> Inicio
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout">
              <i class="bi bi-power me-1"></i> Salir
            </a>
          </li>

        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="https://www.atankalama.com/login/index.php?route=auth/login">
              <i class="fa-solid fa-lock"></i> Ingresar
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div id="rkm-network-status" class="alert alert-warning text-center m-0 rounded-0 d-none">
  <i class="fa-solid fa-wifi"></i> Modo offline: guardando localmente
</div>

<div class="container my-4">
