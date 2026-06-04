<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Editar Producto';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>

<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <br>   <br>
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
        <div class='row'>
            <div class='col-lg-8'>
                <div class='card'>
                    <div class='card-body'>
                        <form method='POST'>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <div class='mb-3'>
                                        <label for='name' class='form-label'>Nombre del Producto *</label>
                                        <input type='text' class='form-control' id='name' name='name'
                                               value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                </div>

                                <div class='col-md-6'>
                                    <div class='mb-3'>
                                        <label for='unit' class='form-label'>Unidad de Medida *</label>
                                        <select class='form-control' id='unit' name='unit' required>
                                            <option value=''>Seleccionar unidad</option>
                                            <option value='unidad' <?php echo $product['unit'] === 'unidad' ? 'selected' : ''; ?>>Unidad</option>
                                            <option value="juego" <?php echo $product['unit'] === 'juego' ? 'selected' : ''; ?>>Juego</option>
                                            <option value="rollo" <?php echo $product['unit'] === 'rollo' ? 'selected' : ''; ?>>Rollo</option>
                                            <option value="caja" <?php echo $product['unit'] === 'caja' ? 'selected' : ''; ?>>Caja</option>
                                            <option value="litro" <?php echo $product['unit'] === 'litro' ? 'selected' : ''; ?>>Litro</option>
                                            <option value="kilogramo" <?php echo $product['unit'] === 'kilogramo' ? 'selected' : ''; ?>>Kilogramo</option>
                                            <option value="paquete" <?php echo $product['unit'] === 'paquete' ? 'selected' : ''; ?>>Paquete</option>
                                            <option value="botella" <?php echo $product['unit'] === 'botella' ? 'selected' : ''; ?>>Botella</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Cantidad Actual *</label>
                                        <input type="number" class="form-control" id="quantity" name="quantity"
                                               value="<?php echo $product['quantity']; ?>" min="0" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="min_stock" class="form-label">Stock Mínimo *</label>
                                        <input type="number" class="form-control" id="min_stock" name="min_stock"
                                               value="<?php echo $product['min_stock']; ?>" min="0" required>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Categoría *</label>
                                        <select class="form-control" id="category_id" name="category_id" required>
                                            <option value="">Seleccionar categoría</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>"
                                                        <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Estado *</label>
                                        <select class="form-control" id="status" name="status" required>
                                            <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
                                            <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class='row'>
                                <div class='col-md-6'>
                                    <div class='mb-3'>
                                        <label for='codigoBarra' class='form-label'>Código de Barra</label>
                                        <input type='text' class='form-control' id='codigoBarra' name='codigoBarra'
                                               value="<?php echo htmlspecialchars($product['codigoBarra'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class='col-md-6'>
                                    <div class='mb-3'>
                                        <label for='vencimiento' class='form-label'>Vencimiento</label>
                                        <input type='date' class='form-control' id='vencimiento' name='vencimiento'
                                               value="<?php echo htmlspecialchars($product['vencimiento'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class='row'>
                                <!-- Ubicación -->
                                <div class='col-md-12 mb-3'>
                                    <label for='location_id' class='form-label'>Ubicación *</label>
                                    <select class='form-control' id='location_id' name='location_id' required>
                                        <option value=''>Seleccionar ubicación</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?=$location['id'];?>"
                                                    <?=$location['id'] == $product['location_id'] ? 'selected' : '';?>>
                                                <?=htmlspecialchars($location['name']);?>
                                                <?php if ($location['zone']): ?>
                                                    (<?=htmlspecialchars($location['zone']);?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php?page=products" class="btn btn-secondary me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Actualizar Producto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class='col-lg-4'>
                <div class='card mb-4'>
                    <div class='card-header'>
                        <h6 class='card-title mb-0'><i class='fas fa-images me-2'></i>Fotos del Producto</h6>
                    </div>
                    <div class='card-body'>
                        <?php foreach ($images as $img): ?>
                            <div class="position-relative">
                                <?php
                                // Miniatura (si existe) o fallback a imagen grande
                                $thumb = $img['thumb_path'] ?? $img['file_path'];

                                // Imagen grande
                                $full  = $img['file_path'];
                                ?>

                                <!-- Miniatura clickeable -->
                                <a href="<?= htmlspecialchars($full) ?>"
                                   target="_blank"
                                   title="Ver imagen completa">

                                    <img src="<?= htmlspecialchars($thumb) ?>"
                                         class="rounded border"
                                         style="width:100px;height:100px;object-fit:cover;">
                                </a>

                                <!-- Botón eliminar -->
                                <a href="index.php?page=products&action=deleteImage&id=<?= $img['id'] ?>&product_id=<?= $product['id'] ?>"
                                   class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                   title="Eliminar imagen"
                                   onclick="return confirm('¿Eliminar esta imagen?');">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>


                        <form action="index.php?page=products&action=addImage&id=<?=$product['id']?>"
                              method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="fotos" class="form-label">Agregar nuevas imágenes</label>
                                <input type="file" name="fotos[]" id="fotos" class="form-control" multiple accept="image/*">
                                <small class="text-muted">Selecciona una o más imágenes para añadir al producto.</small>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-upload me-2"></i>Subir Imágenes
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Información del Producto</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Creado:</strong> <?=formatDate($product['created_at']);?></p>
                        <p><strong>Última actualización:</strong> <?=formatDate($product['updated_at']);?></p>

                        <hr>

                        <a href="index.php?page=products&action=view&id=<?=$product['id'];?>"
                           class="btn btn-info btn-sm w-100">
                            <i class="fas fa-eye me-2"></i>Ver Historial Completo
                        </a>
                    </div>
                </div>
            </div>


            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Información del Producto</h6>
                </div>
                <div class="card-body">
                    <p><strong>Creado:</strong> <?=formatDate($product['created_at']);?></p>
                    <p><strong>Última actualización:</strong> <?=formatDate($product['updated_at']);?></p>

                    <hr>

                    <a href="index.php?page=products&action=view&id=<?=$product['id'];?>"
                       class="btn btn-info btn-sm w-100">
                        <i class="fas fa-eye me-2"></i>Ver Historial Completo
                    </a>
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




