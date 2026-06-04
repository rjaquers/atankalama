<?php
// Detectar sección activa
function navActive(string $segment): string {
    $url = $_SERVER['REQUEST_URI'] ?? '';
    return strpos($url, '/' . $segment) !== false ? 'active' : '';
}
$userFoto    = $_SESSION['user_foto']   ?? '';
$userNombre  = $_SESSION['user_nombre'] ?? 'Usuario';
$userArea    = $_SESSION['user_area']   ?? '';
$userAreaId  = (int)($_SESSION['user_area_id'] ?? 0);
$userRol     = $_SESSION['user_rol']    ?? '';
$userInicial = strtoupper(mb_substr($userNombre, 0, 1, 'UTF-8'));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Chat') ?> — Atankalama</title>
  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="#0f172a">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
  <script>window._BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body>

<!-- ========================================
     SIDEBAR — desktop (lg+)
     ======================================== -->
<aside class="sidebar d-none d-lg-flex flex-column">

  <div class="sidebar-logo">
    <i class="bi bi-chat-dots-fill text-primary fs-4"></i>
    <div>
      <div class="fw-bold text-white lh-1">Chat Interno</div>
      <small style="font-size:11px;color:#64748b">Hotel Atankalama</small>
    </div>
  </div>

  <div class="sidebar-user">
    <div class="user-avatar">
      <?php if ($userFoto): ?>
        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($userFoto) ?>" alt="foto">
      <?php else: ?>
        <?= $userInicial ?>
      <?php endif; ?>
    </div>
    <div class="overflow-hidden">
      <div class="fw-semibold text-white text-truncate" style="font-size:13px"><?= htmlspecialchars($userNombre) ?></div>
      <small style="color:#64748b;font-size:11px"><?= htmlspecialchars($userArea ?: $userRol) ?></small>
    </div>
  </div>

  <nav class="sidebar-nav flex-grow-1">
    <a href="<?= BASE_URL ?>/dashboard" class="sidebar-link <?= navActive('dashboard') ?>">
      <i class="bi bi-speedometer2"></i> Inicio
    </a>
    <a href="<?= BASE_URL ?>/chat" class="sidebar-link <?= navActive('chat') ?>">
      <i class="bi bi-chat-dots-fill"></i> Chat
    </a>
    <?php if ($userAreaId > 0): ?>
    <a href="<?= BASE_URL ?>/chat/grupoArea/<?= $userAreaId ?>" class="sidebar-link" title="Chat de <?= htmlspecialchars($userArea) ?>">
      <i class="bi bi-people-fill"></i> Chat <?= htmlspecialchars($userArea) ?>
    </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/tareas" class="sidebar-link <?= navActive('tareas') ?>">
      <i class="bi bi-clipboard-check-fill"></i> Tareas
    </a>
    <a href="<?= BASE_URL ?>/mantencion" class="sidebar-link <?= navActive('mantencion') ?>">
      <i class="bi bi-wrench-adjustable-circle-fill"></i> Mantención
    </a>
    <a href="<?= BASE_URL ?>/temperaturas" class="sidebar-link <?= navActive('temperaturas') ?>">
      <i class="bi bi-thermometer-half"></i> Temperaturas
    </a>

    <?php if (in_array($userRol, ['Administrador', 'Jefe de Área'], true)): ?>
    <hr class="sidebar-divider">
    <small class="sidebar-section-label">Administración</small>
    <?php if ($userRol === 'Administrador'): ?>
    <a href="<?= BASE_URL ?>/usuarios" class="sidebar-link <?= navActive('usuarios') ?>">
      <i class="bi bi-people-fill"></i> Usuarios
    </a>
    <a href="<?= BASE_URL ?>/areas" class="sidebar-link <?= navActive('areas') ?>">
      <i class="bi bi-building"></i> Áreas
    </a>
    <?php endif; ?>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/perfil" class="sidebar-link <?= navActive('perfil') ?>">
      <i class="bi bi-person-circle"></i> Mi Perfil
    </a>
    <hr class="sidebar-divider">
    <a href="https://www.atankalama.com/login/index.php?route=dashboard" class="sidebar-link" style="color:#94a3b8">
      <i class="bi bi-house-door"></i> Inicio
    </a>
    <a href="<?= BASE_URL ?>/logout" class="sidebar-link" style="color:#ef4444">
      <i class="bi bi-power"></i> Salir
    </a>
  </div>
</aside>

<!-- ========================================
     MOBILE: Header superior
     ======================================== -->
<header class="mobile-header d-flex d-lg-none">
  <div class="d-flex align-items-center gap-2">
    <i class="bi bi-chat-dots-fill text-primary fs-5"></i>
    <span class="fw-bold" style="font-size:15px">Chat Atankalama</span>
  </div>
  <div class="d-flex align-items-center gap-3">
    <a href="<?= BASE_URL ?>/chat" class="text-dark fs-5 position-relative">
      <i class="bi bi-chat-dots"></i>
    </a>
    <a href="<?= BASE_URL ?>/perfil" class="text-decoration-none d-flex flex-column align-items-center" style="gap:2px;color:#64748b;font-size:10px">
      <?php if ($userFoto): ?>
        <div class="user-avatar-sm">
          <img src="<?= BASE_URL ?>/<?= htmlspecialchars($userFoto) ?>" alt="foto" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
        </div>
      <?php else: ?>
        <i class="bi bi-person-circle" style="font-size:22px;color:#334155"></i>
      <?php endif; ?>
      <span>Perfil</span>
    </a>
  </div>
</header>

<!-- ========================================
     CONTENIDO PRINCIPAL
     ======================================== -->
<main class="main-content">
