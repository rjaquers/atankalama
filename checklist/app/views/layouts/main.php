<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistema Checklist' ?> - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/public/favicon.png">
    <style>
        :root {
            --accent-color: #4361ee;
            --primary-bg: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--primary-bg);
        }

        /* Navbar */
        .topnav {
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 0 1.5rem;
        }

        .topnav .navbar-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--accent-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .topnav .nav-link {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 1rem 0.75rem;
            border-bottom: 2px solid transparent;
            border-radius: 0;
            transition: color 0.2s, border-color 0.2s;
        }

        .topnav .nav-link:hover,
        .topnav .nav-link.active {
            color: var(--accent-color);
            border-bottom-color: var(--accent-color);
        }

        .topnav .dropdown-item.active,
        .topnav .dropdown-item:active {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--accent-color);
        }

        .topnav .dropdown-menu {
            border: none;
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
            border-radius: 10px;
        }

        /* Content */
        #content {
            padding: 2rem;
            min-height: calc(100vh - 57px);
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,.075);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>

<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg topnav">
        <div class="container-fluid px-0">

            <!-- Brand -->
            <a class="navbar-brand me-4" href="<?= BASE_URL ?>/dashboard">
                <i class="bi bi-hospital"></i> CheckList Atankalama
            </a>

            <!-- Mobile toggle -->
            <button class="navbar-toggler border-0 d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#topNavLinks">
                <i class="bi bi-list fs-4"></i>
            </button>

            <!-- Links -->
            <div class="collapse navbar-collapse" id="topNavLinks">
                <ul class="navbar-nav me-auto align-items-lg-stretch">
                    <li class="nav-item">
                        <a class="nav-link <?= ($active ?? '') == 'dashboard' ? 'active' : '' ?>" href="<?= BASE_URL ?>/dashboard">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active ?? '') == 'reportes' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reportes">
                            <i class="bi bi-graph-up-arrow me-1"></i> Reportes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active ?? '') == 'encuestas' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reportes/encuestas">
                            <i class="bi bi-bar-chart-line me-1"></i> Encuestas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active ?? '') == 'evaluaciones' ? 'active' : '' ?>" href="<?= BASE_URL ?>/evaluaciones">
                            <i class="bi bi-plus-circle me-1"></i> Iniciar Evaluación
                        </a>
                    </li>

                    <?php if (\App\Middleware\AuthMiddleware::isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array(($active ?? ''), ['checklists','logs','areas','usuarios']) ? 'active' : '' ?>"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-shield-lock me-1"></i> Administración
                        </a>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?= ($active ?? '') == 'checklists' ? 'active' : '' ?>" href="<?= BASE_URL ?>/checklists">
                                    <i class="bi bi-list-check me-2"></i> Checklist / Encuestas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($active ?? '') == 'logs' ? 'active' : '' ?>" href="<?= BASE_URL ?>/reportes/logs">
                                    <i class="bi bi-clock-history me-2"></i> Log Auditoría
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($active ?? '') == 'areas' ? 'active' : '' ?>" href="<?= BASE_URL ?>/areas">
                                    <i class="bi bi-diagram-3 me-2"></i> Áreas / Deptos
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= ($active ?? '') == 'usuarios' ? 'active' : '' ?>" href="<?= BASE_URL ?>/usuarios">
                                    <i class="bi bi-people me-2"></i> Usuarios
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>

                <!-- Right side -->
                <ul class="navbar-nav ms-auto align-items-lg-center">
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
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div id="content">
        <?= $content ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
