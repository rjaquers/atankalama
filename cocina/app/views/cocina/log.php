<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='pro-body'>
    <div class='container'>
        <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom"
            style="border-color: var(--color-border) !important;">
            <h2 class="mb-0 fw-bold"><i class="bi bi-terminal me-2" style="color: var(--color-cta)"></i>Log de Correos
            </h2>
            <a href="index.php?page=cocina/index" class="btn btn-pro-action px-3" style="width: auto;"><i
                    class="bi bi-arrow-left me-1"></i>Volver</a>
        </div>

        <div class="pro-card border-0 mb-4">
            <div class="card-header bg-transparent py-3 px-4" style="border-bottom: 1px solid var(--color-border);">
                <h5 class="fw-bold mb-0" style="color: var(--color-primary);"><i class="bi bi-card-text me-2"
                        style="color: var(--color-cta)"></i>Registro de Sistema</h5>
            </div>
            <div class="card-body p-0">
                <pre class="m-0 p-4"
                    style='background: var(--color-background); color: #10b981; border: none; overflow-x: auto; font-family: "Courier New", Courier, monospace; font-size: 0.9rem; line-height: 1.5; border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;'>
<?php foreach ($lineas as $linea): ?>
        <?= htmlspecialchars($linea) ?>
<?php endforeach; ?>
</pre>
            </div>
        </div>
    </div>
    <!--footer-->
    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
    <!--footer-->