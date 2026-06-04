Plantilla páginas  <!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Editar Ubicación';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>

<main class="container py-3 px-2 bg-gradient text-dark" style='background: linear-gradient(135deg, #5c6bc0 0%, #3949ab 100%); min-height: 100vh;'>
    <!-- Navbar (fuera del <head>) -->
    <?php include 'views/layout/navbar.php'; ?>
    <br>
    <div class='d-flex justify-content-between align-items-center mb-4'>
        <h2><i class='fas fa-plus me-2'></i><?=$page_title;?></h2>
        <a href='index.php?page=locations' class='btn btn-secondary'>
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
                        <div class='card-body'>
                            <form method='POST'>
                                <div class='row'>
                                    <div class='col-md-8'>
                                        <div class='mb-3'>
                                            <label for='name' class='form-label'>Nombre de la Ubicación *</label>
                                            <input type='text' class='form-control' id='name' name='name' required
                                                   value="<?php echo htmlspecialchars($location['name']); ?>">
                                        </div>
                                    </div>

                                    <div class='col-md-4'>
                                        <div class='mb-3'>
                                            <label for='zone' class='form-label'>Zona</label>
                                            <select class='form-control' id='zone' name='zone'>
                                                <option value=''>Sin zona</option>
                                                <option value='Piso 1' <?php echo $location['zone'] === 'Piso 1' ? 'selected' : ''; ?>>Piso 1</option>
                                                <option value="Piso 2" <?php echo $location['zone'] === 'Piso 2' ? 'selected' : ''; ?>>Piso 2</option>
                                                <option value="Piso 3" <?php echo $location['zone'] === 'Piso 3' ? 'selected' : ''; ?>>Piso 3</option>
                                                <option value="Piso 4" <?php echo $location['zone'] === 'Piso 4' ? 'selected' : ''; ?>>Piso 4</option>
                                                <option value="Piso 5" <?php echo $location['zone'] === 'Piso 5' ? 'selected' : ''; ?>>Piso 5</option>
                                                <option value="Bodega" <?php echo $location['zone'] === 'Bodega' ? 'selected' : ''; ?>>Bodega</option>
                                                <option value="Sótano" <?php echo $location['zone'] === 'Sótano' ? 'selected' : ''; ?>>Sótano</option>
                                                <option value="Recepción" <?php echo $location['zone'] === 'Recepción' ? 'selected' : ''; ?>>Recepción</option>
                                                <option value="Cocina" <?php echo $location['zone'] === 'Cocina' ? 'selected' : ''; ?>>Cocina</option>
                                                <option value="Restaurante" <?php echo $location['zone'] === 'Restaurante' ? 'selected' : ''; ?>>Restaurante</option>
                                                <option value="Spa" <?php echo $location['zone'] === 'Spa' ? 'selected' : ''; ?>>Spa</option>
                                                <option value="Gimnasio" <?php echo $location['zone'] === 'Gimnasio' ? 'selected' : ''; ?>>Gimnasio</option>
                                                <option value="Piscina" <?php echo $location['zone'] === 'Piscina' ? 'selected' : ''; ?>>Piscina</option>
                                                <option value='Juku' <?php echo $location['zone'] === 'Juku' ? 'selected' : ''; ?>>Juku</option>
                                                <option value='Atankalama' <?php echo $location['zone'] === 'Atankalama' ? 'selected' : ''; ?>>Atankalama</option>

                                                <?php if ($location['zone'] && ! in_array($location['zone'],
                                                                                          [
                                                                                                  'Piso 1',
                                                                                                  'Piso 2',
                                                                                                  'Piso 3',
                                                                                                  'Piso 4',
                                                                                                  'Piso 5',
                                                                                                  'Bodega',
                                                                                                  'Sótano',
                                                                                                  'Recepción',
                                                                                                  'Cocina',
                                                                                                  'Restaurante',
                                                                                                  'Spa',
                                                                                                  'Gimnasio',
                                                                                                  'Piscina',
                                                                                                  'Juku',
                                                                                                  'Atankalama'
                                                                                          ]
                                                        )): ?>
                                                    <option value="<?php echo htmlspecialchars($location['zone']); ?>" selected>
                                                        <?php echo htmlspecialchars($location['zone']); ?>
                                                    </option>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($location['description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="active" class="form-label">Estado *</label>
                                    <select class="form-control" id="active" name="active" required>
                                        <option value="1" <?php echo $location['active'] == 1 ? 'selected' : ''; ?>>Activa</option>
                                        <option value="0" <?php echo $location['active'] == 0 ? 'selected' : ''; ?>>Inactiva</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php?page=locations" class="btn btn-secondary me-md-2">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Actualizar Ubicación
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
