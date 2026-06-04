<!DOCTYPE html>
<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com                    =
  = Fecha: <?= date('Y') ?>                         =
  ===================================================
-->
 <html lang='es'>
<head>
    <?php
    $page_title = 'Detalles del Producto';
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
        <p class='mb-3 mb-md-0'><i class='fas fa-box me-2'></i><?=htmlspecialchars($product['name']);?></p>

        <a href='index.php?page=products' class='btn btn-secondary'>
            <i class='fas fa-arrow-left me-2'></i>Volver
        </a>
        <?php if (isAdmin()): ?>
            <a href="index.php?page=products&action=edit&id=<?= $product['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
        <?php endif; ?>
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
        <div class='row g-3'>
            <!-- Columna izquierda -->
            <div class='col-lg-8'>
                <!-- Información principal -->
                <div class='card mb-4 shadow-sm'>
                    <div class='card-header'>
                        <h5 class='card-title mb-0'><i class='fas fa-info-circle me-2'></i>Información del Producto</h5>
                    </div>
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-md-6 mb-3'>
                                <h6>Nombre:</h6>
                                <p><?=htmlspecialchars($product['name']);?></p>

                                <h6>Categoría:</h6>
                                <p><span class="badge bg-primary"><?=htmlspecialchars($product['category_name']);?></span></p>

                                <h6>Ubicación:</h6>
                                <p><?=htmlspecialchars($product['location_name']);?></p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <h6>Stock Actual:</h6>
                                <p>
                                    <?php if ($product['quantity'] == 0): ?>
                                        <span class="badge bg-danger fs-6">Sin stock</span>
                                    <?php elseif ($product['quantity'] <= $product['min_stock']): ?>
                                        <span class="badge bg-warning fs-6">
                                    <?=$product['quantity'];?> <?=htmlspecialchars($product['unit']);?> (Bajo)
                                </span>
                                    <?php else: ?>
                                        <span class="badge bg-success fs-6">
                                    <?=$product['quantity'];?> <?=htmlspecialchars($product['unit']);?>
                                </span>
                                    <?php endif; ?>
                                </p>

                                <h6>Stock Mínimo:</h6>
                                <p><?=$product['min_stock'];?> <?=htmlspecialchars($product['unit']);?></p>

                                <h6>Estado:</h6>
                                <p>
                                    <?php if ($product['status'] === 'active'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactivo</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($product['description']): ?>
                            <h6>Descripción:</h6>
                            <p><?=nl2br(htmlspecialchars($product['description']));?></p>
                        <?php endif; ?>

                        <hr>

                        <div class="row text-center">
                            <div class="col-4">
                                <h6 class="text-muted">Creado</h6>
                                <p><?=formatDate($product['created_at']);?></p>
                            </div>
                            <div class="col-4">
                                <h6 class="text-muted">Actualizado</h6>
                                <p><?=formatDate($product['updated_at']);?></p>
                            </div>
                            <div class="col-4">
                                <h6 class="text-muted">Unidad</h6>
                                <p><?=htmlspecialchars($product['unit']);?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Galería de imágenes -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-images me-2"></i>Galería de Imágenes</h5>
                    </div>
                    <div class="card-body">

                        <?php if (!empty($images)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($images as $img): ?>
                                    <?php
                                    $main = $img['file_path'] ?? '';
                                    $thumb = $img['thumb_path'] ?? $main; // fallback: si no hay thumb, usa la grande
                                    ?>

                                    <a href="<?= htmlspecialchars($main) ?>" target="_blank" title="Ver imagen completa">
                                        <img src="<?= htmlspecialchars($thumb) ?>"
                                             alt="Imagen producto"
                                             width="50"
                                             height="50"
                                             style="object-fit: cover; border-radius:4px; border:1px solid #ddd;">
                                    </a>

                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Este producto no tiene imágenes asociadas.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fas fa-history me-2"></i>Historial de Movimientos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (! empty($logs)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($logs as $log): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1">
                                                <i class="<?=[
                                                        'CREATE' => 'fas fa-plus text-success',
                                                        'UPDATE' => 'fas fa-edit text-primary',
                                                        'DELETE' => 'fas fa-trash text-danger',
                                                        'STOCK_UPDATE' => 'fas fa-box text-info',
                                                        'CONSUMPTION' => 'fas fa-shopping-cart text-warning'
                                                ][$log['action']] ?? 'fas fa-info';?> me-2"></i>
                                                <?=htmlspecialchars($log['action']);?>
                                            </h6>
                                            <small><?=formatDate($log['timestamp']);?></small>
                                        </div>
                                        <small class="text-muted">Por: <?=htmlspecialchars($log['full_name']);?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No hay movimientos registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Columna derecha -->
            <div class="col-lg-4">
                <!-- Acciones rápidas -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php?page=products&action=update_stock&id=<?=$product['id'];?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Actualizar Stock
                            </a>


                                <a href='index.php?page=stock_entry' class='btn btn-primary '>
                                    <i class='fas fa-arrow-up me-2'></i>Agregar a Inventario
                                </a>


                            <a href="index.php?page=consumption&action=create" class="btn btn-info">
                                <i class="fas fa-shopping-cart me-2"></i>Registrar Consumo
                            </a>
                            <?php if (isAdmin()): ?>
                                <a href="index.php?page=products&action=edit&id=<?=$product['id'];?>" class="btn btn-primary">
                                    <i class="fas fa-pencil-alt me-2"></i>Editar Producto
                                </a>
                                <a href="index.php?page=products&action=delete&id=<?=$product['id'];?>" class="btn btn-danger delete-confirm">
                                    <i class="fas fa-trash me-2"></i>Eliminar Producto
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Estado de stock -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-chart-pie me-2"></i>Estado del Stock</h6>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $stock_percentage = $product['min_stock'] > 0 ? ($product['quantity'] / $product['min_stock']) * 100 : 100;
                        $status_color = 'success';
                        $status_text = 'Stock Normal';

                        if ($product['quantity'] == 0) {
                            $status_color = 'danger';
                            $status_text = 'Sin Stock';
                            $stock_percentage = 0;
                        } elseif ($product['quantity'] <= $product['min_stock']) {
                            $status_color = 'warning';
                            $status_text = 'Stock Bajo';
                        }
                        ?>
                        <h4 class="text-<?=$status_color;?>"><?=$status_text;?></h4>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-<?=$status_color;?>"
                                 style="width: <?=min($stock_percentage, 100);?>%">
                            </div>
                        </div>
                        <p><strong><?=$product['quantity'];?></strong> de <strong><?=$product['min_stock'];?></strong> mínimo</p>
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
