<?php
// Layout principal - Sistema de Contratos Atankalama
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Sistema de Contratos - Hotel Atankalama</title>
  <meta name="description" content="Sistema de gestión de contratos del Hotel Atankalama">

  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="#1a3a5c">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="mobile-web-app-capable" content="yes">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-dark navbar-expand-lg" style="background: linear-gradient(135deg, #1a3a5c 0%, #2c5f8a 100%);">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= BASE_URL ?>/dashboard">
      <i class="fa-solid fa-file-contract"></i> Contratos Atankalama
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navContratos">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navContratos">
      <?php if(!empty($_SESSION['user_id'])): ?>
      <ul class="navbar-nav me-auto">
        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/dashboard">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        </li>

        <!-- Cotizaciones -->
        <?php if(AuthService::hasPermission('contracts_view')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-file-invoice-dollar"></i> Cotizaciones
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/quotations"><i class="bi bi-list-ul"></i> Listar Cotizaciones</a></li>
            <?php if(AuthService::hasPermission('contracts_create')): ?>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/quotations/create"><i class="bi bi-plus-circle"></i> Nueva Cotización</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Contratos -->
        <?php if(AuthService::hasPermission('contracts_view')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-file-contract"></i> Contratos
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/contracts"><i class="bi bi-list-ul"></i> Listar Contratos</a></li>
            <?php if(AuthService::hasPermission('contracts_create')): ?>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/contracts/create"><i class="bi bi-plus-circle"></i> Nuevo Contrato</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Empresas -->
        <?php if(AuthService::hasPermission('companies_view')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-building"></i> Empresas
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/companies"><i class="bi bi-list-ul"></i> Listar Empresas</a></li>
            <?php if(AuthService::hasPermission('companies_create')): ?>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/companies/create"><i class="bi bi-plus-circle"></i> Nueva Empresa</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Pagos (visible para admin, cobranzas) -->
        <?php if(AuthService::hasPermission('payments_view')): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/payments">
            <i class="fa-solid fa-money-check-dollar"></i> Pagos
          </a>
        </li>
        <?php endif; ?>

        <!-- Espacios (visible para quienes tengan spaces_view) -->
        <?php if(AuthService::hasPermission('spaces_view')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-door-open"></i> Espacios
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/spaces"><i class="bi bi-list-ul"></i> Espacios</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/bookings"><i class="bi bi-calendar-check"></i> Reservas</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/bookings/calendar"><i class="bi bi-calendar3"></i> Calendario</a></li>
            <?php if(AuthService::hasPermission('bookings_create')): ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/bookings/create"><i class="bi bi-plus-circle"></i> Nueva Reserva</a></li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <!-- Reportes -->
        <?php if(AuthService::hasPermission('reports_view')): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/reports">
            <i class="fa-solid fa-chart-bar"></i> Reportes
          </a>
        </li>
        <?php endif; ?>

        <!-- Admin: Usuarios, Servicios, Plantillas, Alertas -->
        <?php if(AuthService::isAdmin()): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-gear"></i> Admin
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/users"><i class="bi bi-people"></i> Usuarios</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/hotels"><i class="bi bi-houses"></i> Hoteles</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/services"><i class="bi bi-box-seam"></i> Servicios</a></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/templates"><i class="bi bi-file-earmark-text"></i> Plantillas</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= BASE_URL ?>/alerts"><i class="bi bi-bell"></i> Config. Alertas</a></li>
          </ul>
        </li>
        <?php endif; ?>
      </ul>

      <!-- Derecha: Usuario + Inicio + Logout -->
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link text-light">
            <i class="fa-regular fa-user"></i>
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuario') ?>
            <span class="badge bg-light text-dark ms-1"><?= htmlspecialchars(ucfirst($_SESSION['role'] ?? '')) ?></span>
          </span>
        </li>
        <li class="nav-item">
          <a class="nav-link text-secondary" href="https://www.atankalama.com/login/index.php?route=dashboard">
            <i class="bi bi-house-door me-1"></i> Inicio
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="<?= BASE_URL ?>/logout">
            <i class="bi bi-power me-1"></i> Salir
          </a>
        </li>
      </ul>
      <?php else: ?>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?= BASE_URL ?>/login"><i class="fa-solid fa-lock"></i> Login</a>
        </li>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div id="rkm-network-status" class="alert alert-warning text-center m-0 rounded-0 d-none">
  <i class="fa-solid fa-wifi"></i> Modo offline: guardando localmente
</div>

<div class="container-fluid my-4 px-4">
