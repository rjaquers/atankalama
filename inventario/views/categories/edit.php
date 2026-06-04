Plantilla páginas  <!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Editar Categorías';
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
        <a href='index.php?page=categories' class='btn btn-secondary'>
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
                                <div class='mb-3'>
                                    <label for='name' class='form-label'>Nombre de la Categoría *</label>
                                    <input type='text' class='form-control' id='name' name='name' required
                                           value="<?php echo htmlspecialchars($category['name']); ?>">
                                </div>

                                <div class='mb-3'>
                                    <label for='description' class='form-label'>Descripción</label>
                                    <textarea class='form-control' id='description' name='description' rows='3'><?php echo htmlspecialchars($category['description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="active" class="form-label">Estado *</label>
                                    <select class="form-control" id="active" name="active" required>
                                        <option value="1" <?php echo $category['active'] == 1 ? 'selected' : ''; ?>>Activa</option>
                                        <option value="0" <?php echo $category['active'] == 0 ? 'selected' : ''; ?>>Inactiva</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="index.php?page=categories" class="btn btn-secondary me-md-2">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Actualizar Categoría
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


