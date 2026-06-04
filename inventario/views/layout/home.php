<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com                    =
  = Fecha: <?= date('Y') ?>                         =
  ===================================================
-->
<?php
/**
 * Layout maestro del sistema
 *
 * Responsabilidad:
 * - Cargar estructura HTML base
 * - Incluir header, navbar y footer
 * - Renderizar la vista final indicada por el controlador
 *
 * Variables esperadas:
 * @var string $page_title  Título de la página
 * @var string $view        Ruta de la vista a cargar
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php
    $page_title = $page_title ?? 'Sistema de Inventario';
    include __DIR__ . '/header.php'; // solo <meta>, <link>, <title>
    ?>
</head>

<body>

<main class="container-fluid p-0 min-vh-100">

    <!-- Navbar -->
    <?php include __DIR__ . '/navbar.php'; ?>

    <!-- Contenido dinámico -->
    <section class="container py-3 px-2">
        <?php
        if (isset($view) && file_exists($view)) {
            include $view;
        } else {
            echo "<div class='alert alert-danger'>Vista no definida</div>";
        }
        ?>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . '/footer.php'; ?>

</main>

</body>
</html>
