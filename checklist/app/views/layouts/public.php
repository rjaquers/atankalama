<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Encuesta' ?> - <?= APP_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/favicon.png">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .public-header {
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 1rem 0;
        }
        .public-footer {
            border-top: 1px solid #dee2e6;
            padding: 1.5rem 0;
            margin-top: 3rem;
            background: #ffffff;
        }
    </style>
</head>
<body>
    <header class="public-header">
        <div class="container">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-hospital text-primary fs-4"></i>
                <span class="fw-bold text-primary">Atankalama</span>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <?= $content ?>
    </main>

    <footer class="public-footer">
        <div class="container text-center">
            <small class="text-muted">
                &copy; <?= date('Y') ?> Atankalama &mdash; Todos los derechos reservados
            </small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
