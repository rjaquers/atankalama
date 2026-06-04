<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Gestión de Categorías';
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
        <a href='index.php' class='btn btn-secondary'>
            <i class='fas fa-arrow-left me-2'></i>Volver
        </a>
        <a href='index.php?page=categories&action=create' class='btn btn-success'>
            <i class='fas fa-plus me-2'></i>Nueva Categoría
        </a>
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
        <div class='row justify-content-center'>
            <div class='card'>
                <div class='card-body'>
                    <div class='table-responsive'>
                        <table class='table table-hover'>
                            <thead class='table-dark'>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($category['description']): ?>
                                            <small><?php echo htmlspecialchars($category['description']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $product_count = (new Category())->getProductCount($category['id']);
                                        ?>
                                        <span class="badge bg-info"><?php echo $product_count; ?> productos</span>
                                    </td>
                                    <td>
                                        <?php if ($category['active']): ?>
                                            <span class="badge bg-success">Activa</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo formatDate($category['created_at']); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="index.php?page=categories&action=edit&id=<?php echo $category['id']; ?>"
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <?php
                                            $product_count = (new Category())->getProductCount($category['id']);
                                            if ($product_count == 0):
                                                ?>
                                                <a href="index.php?page=categories&action=delete&id=<?php echo $category['id']; ?>"
                                                   class="btn btn-danger btn-sm delete-confirm">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-danger btn-sm" disabled title="No se puede eliminar: tiene productos asignados">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
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








