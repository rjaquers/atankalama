<?php
/**
 * Layout Helper - Atankalama Empresas
 * Genera el encabezado y navegación común
 */
class Layout
{
    public static function header($title, $user, $activeTab = 'dashboard')
    {
        $baseUrl = BASE_URL;
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?= $title ?> - Atankalama Empresas</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
            <style>
                body { background-color: #f8f9fa; }
                .navbar-dark { background-color: #2c3e50; }
                .navbar-brand { font-weight: bold; }
                .nav-link.active { font-weight: bold; border-bottom: 2px solid #fff; }
                .card { border: none; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
                .filter-btn.active { background-color: #0056b3; color: white; }
            </style>
        </head>
        <body>
            <nav class="navbar navbar-expand-lg navbar-dark sticky-top mb-4">
                <div class="container">
                    <a class="navbar-brand d-flex align-items-center" href="<?= $baseUrl ?>dashboard">
                        <i class="fa-solid fa-hotel fa-2x me-3"></i>
                        <div>
                            <div class="lh-1">ATANKALAMA</div>
                            <small class="text-white-50 fw-normal" style="font-size: 0.65rem; letter-spacing: 1px;">PORTAL DEL CLIENTE</small>
                        </div>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav me-auto">
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab == 'dashboard' ? 'active' : '' ?>" href="<?= $baseUrl ?>dashboard">
                                    <i class="fa-solid fa-gauge me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab == 'alimentacion' ? 'active' : '' ?>" href="<?= $baseUrl ?>alimentacion">
                                    <i class="fa-solid fa-utensils me-1"></i> Alimentación
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab == 'servicios' ? 'active' : '' ?>" href="<?= $baseUrl ?>servicios">
                                    <i class="fa-solid fa-concierge-bell me-1"></i> Servicios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $activeTab == 'usuarios' ? 'active' : '' ?>" href="<?= $baseUrl ?>usuarios">
                                    <i class="fa-solid fa-users me-1"></i> Usuarios
                                </a>
                            </li>
                        </ul>
                        <div class="navbar-nav align-items-center">
                            <span class="navbar-text me-3 d-none d-lg-block">
                                <i class="fa-solid fa-building me-1"></i> <?= htmlspecialchars($user['company_name']) ?>
                            </span>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-white" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-user-circle me-1"></i> <?= htmlspecialchars($user['name']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>login/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión</a></li>
                                </ul>
                            </li>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="container mb-5">
        <?php
    }

    public static function footer()
    {
        ?>
            </div>
            <footer class="footer mt-auto py-3 bg-white border-top text-center text-muted">
                <div class="container">
                    <span class="small">© <?= date('Y') ?> Hotel Atankalama - Portal de Clientes | <b>v1.0.0</b></span>
                    <div class="mt-1">
                        <small style="font-size: 0.7rem;">La información mostrada está sujeta a la <a href="#" onclick="alert('La información se presenta con fines informativos y de gestión interna. El sistema protege los datos sensibles mediante enmascaramiento de RUT.'); return false;" class="text-decoration-none">restricción de responsabilidad</a>.</small>
                    </div>
                </div>
            </footer>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>
        <?php
    }
}
