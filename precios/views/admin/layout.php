<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($tituloPagina ?? 'Admin Precios') ?> — Atankalama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f8f5f0; }
        .navbar-brand { font-weight: 700; letter-spacing: 1px; }
        .navbar { background: linear-gradient(90deg, #b3541a 0%, #e76f51 100%) !important; }
        .nav-link, .navbar-brand, .navbar-text { color: #fff !important; }
        .nav-link:hover { color: #ffe5b4 !important; }
        .nav-link.active { background: rgba(255,255,255,0.2); border-radius: 6px; }
        .card { border: none; box-shadow: 0 2px 12px rgba(60,40,10,.10); border-radius: 14px; }
        .table th { background: linear-gradient(90deg,#ffe5b4,#e9c46a); color: #b3541a; font-weight: 600; }
        .precio-celda { cursor: pointer; transition: background .15s; }
        .precio-celda:hover { background: #fff3cd; }
        .badge-activo   { background-color: #2a9d8f; }
        .badge-inactivo { background-color: #adb5bd; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php?page=precios/lista">
            <i class="bi bi-tags-fill me-1"></i> Precios Atankalama
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= (str_starts_with($page,'precios')) ? 'active' : '' ?>"
                       href="index.php?page=precios/lista">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Grilla de precios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (str_starts_with($page,'categorias')) ? 'active' : '' ?>"
                       href="index.php?page=categorias/lista">
                        <i class="bi bi-columns me-1"></i>Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (str_starts_with($page,'tipos')) ? 'active' : '' ?>"
                       href="index.php?page=tipos/lista">
                        <i class="bi bi-list-ul me-1"></i>Tipos de hab.
                    </a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($email) ?>
            </span>
            <a href="index.php?page=auth/logout" class="btn btn-outline-light btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Salir
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <?php if (!empty($mensajeExito)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($mensajeExito) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($mensajeError)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($mensajeError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?= $contenido ?>
</div>

<footer class="text-center text-muted small py-3 border-top mt-4">
    &copy; <?= date('Y') ?> Rodrigo Jaque Escobar &mdash; Todos los derechos reservados.<br>
    Se concede uso operacional de esta aplicación. El código fuente y la aplicación
    permanecen como propiedad exclusiva del autor.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $scriptExtra ?? '' ?>
</body>
</html>
