<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$_layout_email = $_SESSION['adm_admin_email'] ?? null;
$_layout_exp   = $_SESSION['adm_admin_expires'] ?? 0;
if ($_layout_email && time() > $_layout_exp) {
    $_layout_email = null;
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title>Administración · Hotel Atankalama</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>

    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css' rel='stylesheet'>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <link href='/novedades/assets/css/style.css' rel='stylesheet'>
    <style>
        /* Ajustes para pasar de sidebar a menú superior */
        body { background-color: #f4f7f6; }
        .wrapper { display: block !important; }
        .sidebar { display: none !important; }
        .topbar-mobile { display: none !important; }
        .content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.08); }
        .bg-desert { background-color: #8d6e63 !important; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; }
        .nav-link { font-weight: 500; }
        .dropdown-item.active, .dropdown-item:active { background-color: #8d6e63; }
        footer { margin-left: 0 !important; }
    </style>
</head>
<body>

<?php
$rutasAcceso = [
    'acceso/usuarios/list','acceso/usuarios/create','acceso/usuarios/edit','acceso/usuarios/permisos',
    'acceso/apps/list','acceso/apps/create','acceso/apps/edit',
    'acceso/roles/list','acceso/roles/create','acceso/roles/edit','acceso/roles/secciones',
    'acceso/secciones/list','acceso/secciones/create','acceso/secciones/edit',
    'acceso/perfiles/list','acceso/perfiles/create','acceso/perfiles/edit',
];
$adminAuth    = !empty($_layout_email);
$_routeActual = $_GET['route'] ?? '';

$_layout_nombre = '';
if ($adminAuth) {
    try {
        $stmtH = acceso_pdo()->prepare(
            "SELECT CONCAT(nombre, ' ', apellido) FROM chk_usuarios WHERE email = ? LIMIT 1"
        );
        $stmtH->execute([$_layout_email]);
        $_layout_nombre = htmlspecialchars($stmtH->fetchColumn() ?: $_layout_email);
    } catch (\Throwable $e) {
        $_layout_nombre = htmlspecialchars($_layout_email);
    }
}
?>

<!-- ── Navbar Superior ──────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-dark bg-desert sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?route=acceso/usuarios/list">
            <i class="bi bi-shield-lock me-2"></i> Administración
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= in_array($_routeActual, $rutasAcceso) ? 'active' : '' ?>"
                       href="#" id="accesoDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Control de Acceso
                    </a>
                    <ul class="dropdown-menu shadow" aria-labelledby="accesoDropdown">
                        <li>
                            <a class="dropdown-item <?= in_array($_routeActual, ['acceso/usuarios/list','acceso/usuarios/create','acceso/usuarios/edit','acceso/usuarios/permisos']) ? 'active' : '' ?>"
                               href="index.php?route=acceso/usuarios/list">
                                <i class="bi bi-people-fill me-2 opacity-75"></i> Usuarios
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= in_array($_routeActual, ['acceso/apps/list','acceso/apps/create','acceso/apps/edit']) ? 'active' : '' ?>"
                               href="index.php?route=acceso/apps/list">
                                <i class="bi bi-grid-fill me-2 opacity-75"></i> Aplicaciones
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= in_array($_routeActual, ['acceso/roles/list','acceso/roles/create','acceso/roles/edit','acceso/roles/secciones']) ? 'active' : '' ?>"
                               href="index.php?route=acceso/roles/list">
                                <i class="bi bi-person-badge-fill me-2 opacity-75"></i> Roles
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= in_array($_routeActual, ['acceso/secciones/list','acceso/secciones/create','acceso/secciones/edit']) ? 'active' : '' ?>"
                               href="index.php?route=acceso/secciones/list">
                                <i class="bi bi-link-45deg me-2 opacity-75"></i> Secciones
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item <?= in_array($_routeActual, ['acceso/perfiles/list','acceso/perfiles/create','acceso/perfiles/edit']) ? 'active' : '' ?>"
                               href="index.php?route=acceso/perfiles/list">
                                <i class="bi bi-person-badge-fill me-2 opacity-75"></i> Perfiles
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($_routeActual, 'doc_companies/') ? 'active' : '' ?>"
                       href="index.php?route=doc_companies/list">
                        <i class="bi bi-building me-1"></i> Empresas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_starts_with($_routeActual, 'chk/areas/') ? 'active' : '' ?>"
                       href="index.php?route=chk/areas/list">
                        <i class="bi bi-diagram-3-fill me-1"></i> Áreas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $_routeActual === 'version' ? 'active' : '' ?>"
                       href="index.php?route=version">
                        <i class="bi bi-code-branch me-1"></i> Versiones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://www.atankalama.com/login/">
                        <i class="bi bi-house me-1"></i> Inicio
                    </a>
                </li>
            </ul>

            <?php if ($adminAuth): ?>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> <span class="small"><?= $_layout_nombre ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item text-danger" href="index.php?route=auth/logout">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (!empty($_SESSION['admin_impersonator_email'])): ?>
    <div class="alert alert-dark border-0 rounded-0 mb-0 py-2 d-flex align-items-center justify-content-between px-4">
        <div class="small">
            <i class="bi bi-eye-fill me-2"></i>
            Estás viendo el sistema como <strong><?= htmlspecialchars($_layout_email) ?></strong>
        </div>
        <a href="index.php?route=acceso/usuarios/salir-ver-como" class="btn btn-sm btn-light py-0 fw-bold">
            <i class="bi bi-box-arrow-left me-1"></i> Volver a mi perfil
        </a>
    </div>
<?php endif; ?>

<div class='wrapper'>
    <!-- Main content -->
    <main class='content'>
