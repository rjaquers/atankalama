<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Actualizar Stock';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>
<br>
<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <br>
    <div class='d-flex justify-content-between align-items-center mb-4'>
        <h2><i class='fas fa-plus me-2'></i><?=$page_title;?></h2>
        <a href='index.php?page=products' class='btn btn-secondary'>
            <i class='fas fa-arrow-left me-2'></i>Volver
        </a>
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
        <div class='row justify-content-center'>
            <div class='row justify-content-center'>
                <div class='col-lg-6'>
                    <div class='card'>
                        <div class='card-header'>
                            <h5 class='card-title mb-0'>
                                <i class='fas fa-box me-2'></i><?php echo htmlspecialchars($product['name']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4 text-center">
                                    <h6 class="text-muted">Stock Actual</h6>
                                    <h3 class="text-primary"><?php echo $product['quantity']; ?></h3>
                                    <small><?php echo htmlspecialchars($product['unit']); ?></small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6 class="text-muted">Stock Mínimo</h6>
                                    <h3 class="text-warning"><?php echo $product['min_stock']; ?></h3>
                                    <small><?php echo htmlspecialchars($product['unit']); ?></small>
                                </div>
                                <div class="col-md-4 text-center">
                                    <h6 class="text-muted">Ubicación</h6>
                                    <p class="mb-0"><?php echo htmlspecialchars($product['location_name']); ?></p>
                                </div>
                            </div>

                            <hr>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Nueva Cantidad *</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="quantity" name="quantity"
                                               value="<?php echo $product['quantity']; ?>" min="0" required>
                                        <span class="input-group-text"><?php echo htmlspecialchars($product['unit']); ?></span>
                                    </div>
                                    <div class="form-text">
                                        Ingrese la nueva cantidad total en stock
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Nota:</strong> Esta acción actualizará el stock total del producto y será registrada en el historial de movimientos.
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php?page=products" class="btn btn-secondary me-md-2">Cancelar</a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>Actualizar Stock
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php include 'views/layout/footer.php'; ?>
</main>
<!--//Adicionales de la págona-->

</body>
</html>









