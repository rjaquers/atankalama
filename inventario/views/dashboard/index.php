<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Dashboard';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>

<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <!-- Acciones rápidas -->
    <br>
    <div class='card shadow-sm'>
        <div class='card-header bg-primary text-white'>
            <i class='fas fa-bolt me-2'></i>Acciones Rápidas
        </div>
        <div class='card-body'>
<!-- Buscado -->
            <div class='card mb-4'>
                <div class='card-body position-relative'>
                    <label class='form-label fw-semibold'>
                        <i class='fas fa-search me-1'></i> Buscar producto
                    </label>

                    <input type='text'
                           id='product-search'
                           class='form-control'
                           placeholder='Escribe nombre o descripción del producto...'
                           autocomplete='off'>

                    <div id='product-results'
                         class='list-group position-absolute w-100 d-none'
                         style='z-index:1050;'></div>
                </div>

            </div>
<!-- Fin buscador -->
            <div class='row g-2 text-center'>
                <div class='col-6 col-md-3'>
                    <a href='index.php?page=consumption&action=create' class='btn btn-primary w-100'>
                        <i class='fas fa-plus-circle me-2'></i>Registrar Consumo
                    </a>
                </div>
                <div class='col-6 col-md-3'>
                <a href='index.php?page=stock_entry' class='btn btn-primary w-100'>
                    <i class='fas fa-arrow-up me-2'></i>Ingresar Inventario
                </a>
                </div>

                <div class='col-6 col-md-3'>
                    <a href='index.php?page=products&action=create' class='btn btn-success w-100'>
                        <i class='fas fa-box me-2'></i>Nuevo Producto
                    </a>
                </div>
                <div class='col-6 col-md-3'>
                    <a href='index.php?page=categories&action=create' class='btn btn-info w-100'>
                        <i class='fas fa-tags me-2'></i>Nueva Categoría
                    </a>
                </div>
                <div class='col-6 col-md-3'>
                    <a href='index.php?page=locations&action=create' class='btn btn-warning w-100'>
                        <i class='fas fa-map-marker-alt me-2'></i>Nueva Ubicación
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas estadísticas -->
    <?php include 'views/dashboard/cards.php'; ?>



    <?php include 'views/layout/footer.php'; ?>
</main>

</body>
</html>


