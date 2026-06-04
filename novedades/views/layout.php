<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_layout_email = $_SESSION['nov_admin_email'] ?? null;
$_layout_exp   = $_SESSION['nov_admin_expires'] ?? 0;
if ($_layout_email && time() > $_layout_exp) {
    $_layout_email = null;
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Sistema de Novedades · Hotel Atankalama</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <!-- Tipografía -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <!-- DataTables -->
    <link href='https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css' rel='stylesheet'>
    <!-- Bootstrap Icons -->
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css' rel='stylesheet'>

    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <link href='/novedades/assets/css/style.css' rel='stylesheet'>
</head>
<body>

<?php
$adminAuth    = !empty($_layout_email);
$_routeActual = $_GET['route'] ?? '';

$_layout_nombre  = '';
$_layout_inicial = '?';
if ($adminAuth) {
    try {
        $stmtH = acceso_pdo()->prepare(
            "SELECT nombre, apellido FROM chk_usuarios WHERE email = ? LIMIT 1"
        );
        $stmtH->execute([$_layout_email]);
        $row = $stmtH->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $_layout_nombre  = htmlspecialchars(trim($row['nombre'] . ' ' . $row['apellido']));
            $_layout_inicial = htmlspecialchars(mb_strtoupper(mb_substr($row['nombre'], 0, 1)));
        } else {
            $_layout_nombre  = htmlspecialchars($_layout_email);
            $_layout_inicial = htmlspecialchars(mb_strtoupper(mb_substr($_layout_email, 0, 1)));
        }
    } catch (\Throwable $e) {
        $_layout_nombre  = htmlspecialchars($_layout_email);
        $_layout_inicial = '?';
    }
}

$_rutasGestion = [
    'empresas/list','empresas/create','empresas/edit',
    'encargados/list','encargados/create','encargados/edit',
    'recepcionistas/list','recepcionistas/create','recepcionistas/edit',
];
?>

<!-- ── Top Navbar ───────────────────────────────────────────────── -->
<nav class="app-navbar navbar navbar-expand-md navbar-dark">
    <div class="container-fluid px-3 px-md-4">

        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="index.php?route=dashboard">
            <i class="bi bi-journal-text me-2"></i>Novedades
        </a>

        <!-- Toggler móvil -->
        <button class="navbar-toggler border-0 shadow-none" type="button"
                data-bs-toggle="collapse" data-bs-target="#appNavMenu"
                aria-controls="appNavMenu" aria-expanded="false" aria-label="Menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="appNavMenu">

            <!-- ── Izquierda ─────────────────────────────────── -->
            <ul class="navbar-nav me-auto mb-2 mb-md-0">

                <li class="nav-item">
                    <a class="nav-link <?= $_routeActual === 'dashboard' ? 'active' : '' ?>"
                       href="index.php?route=dashboard">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $_routeActual === 'novedades/form' ? 'active' : '' ?>"
                       href="index.php?route=novedades/form">
                        <i class="bi bi-plus-circle me-1"></i>Nueva Novedad
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $_routeActual === 'novedades/list' ? 'active' : '' ?>"
                       href="index.php?route=novedades/list">
                        <i class="bi bi-list-check me-1"></i>Historial
                    </a>
                </li>

                <!-- Gestión (dropdown) -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($_routeActual, $_rutasGestion) ? 'active' : '' ?>"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid me-1"></i>Gestión
                        <?php if ($adminAuth): ?>
                            <i class="bi bi-shield-fill-check ms-1 text-success" style="font-size:.65rem;vertical-align:middle;"></i>
                        <?php else: ?>
                            <i class="bi bi-shield-lock ms-1 text-warning" style="font-size:.65rem;vertical-align:middle;"></i>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark border-0 shadow">
                        <li>
                            <a class="dropdown-item" href="index.php?route=empresas/list">
                                <i class="bi bi-building me-2"></i>Empresas
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="index.php?route=encargados/list">
                                <i class="bi bi-person-badge me-2"></i>Encargados
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="index.php?route=recepcionistas/list">
                                <i class="bi bi-people me-2"></i>Personal
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>

            <!-- ── Derecha ───────────────────────────────────── -->
            <ul class="navbar-nav align-items-md-center gap-md-1">

                <li class="nav-item">
                    <a class="nav-link <?= $_routeActual === 'version' ? 'active' : '' ?>"
                       href="index.php?route=version">
                        <i class="bi bi-code-branch me-1"></i>Versiones
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                       href="https://www.atankalama.com/login/index.php?route=dashboard">
                        <i class="bi bi-house me-1"></i>Inicio
                    </a>
                </li>

                <?php if ($adminAuth): ?>
                <!-- Usuario autenticado: avatar + dropdown -->
                <li class="nav-item dropdown ms-md-2">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 py-1"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="nav-avatar"><?= $_layout_inicial ?></span>
                        <span class="d-none d-lg-inline small"><?= $_layout_nombre ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                        <li>
                            <span class="dropdown-item-text small text-muted py-1">
                                <?= htmlspecialchars($_layout_email) ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item" href="https://www.atankalama.com/admin/public/">
                                <i class="bi bi-shield-lock me-2 text-secondary"></i>Panel Admin
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?route=auth/logout">
                                <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </li>

                <?php else: ?>
                <!-- Sin sesión: acceso directo a salir -->
                <li class="nav-item ms-md-1">
                    <a class="nav-link text-danger-emphasis" href="index.php?route=auth/logout">
                        <i class="bi bi-box-arrow-right me-1"></i>Salir
                    </a>
                </li>
                <?php endif; ?>

            </ul>
        </div><!-- /collapse -->
    </div><!-- /container -->
</nav>

<div class="wrapper">
    <main class="content">
