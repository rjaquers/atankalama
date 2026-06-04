<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Crear Ubicación';
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
    </div>
    <!-- Acciones rápidas -->
    <br>


    <!-- Principals -->
    <div class="row   mb-12">
        <!-- Alertas -->
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
                                               placeholder='Ej: Habitación 102, Bodega Lencería'>
                                    </div>
                                </div>

                                <div class='col-md-4'>
                                    <div class='mb-3'>
                                        <label for='zone' class='form-label'>Zona</label>
                                        <select class='form-control' id='zone' name='zone'>
                                            <option value=''>Seleccionar zona</option>
                                            <option value='Piso 1'>Piso 1</option>
                                            <option value='Piso 2'>Piso 2</option>
                                            <option value='Piso 3'>Piso 3</option>
                                            <option value='Piso 4'>Piso 4</option>
                                            <option value='Piso 5'>Piso 5</option>
                                            <option value='Bodega'>Bodega</option>
                                            <option value='Sótano'>Sótano</option>
                                            <option value='Recepción'>Recepción</option>
                                            <option value='Cocina'>Cocina</option>
                                            <option value='Restaurante'>Restaurante</option>
                                            <option value='Spa'>Spa</option>
                                            <option value='Gimnasio'>Gimnasio</option>
                                            <option value='Piscina'>Piscina</option>
                                            <option value='Juku'>Juku</option>
                                            <option value='Atankalama'>Atankalama</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class='mb-3'>
                                <label for='description' class='form-label'>Descripción</label>
                                <textarea class='form-control' id='description' name='description' rows='3'
                                          placeholder='Descripción opcional de la ubicación'></textarea>
                            </div>

                            <div class='d-grid gap-2 d-md-flex justify-content-md-end'>
                                <a href='index.php?page=locations' class='btn btn-secondary me-md-2'>Cancelar</a>
                                <button type='submit' class='btn btn-success'>
                                    <i class='fas fa-save me-2'></i>Crear Ubicación
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <?php include 'views/layout/footer.php'; ?>
</main>


<script>
    document.getElementById('zone').addEventListener('change', function() {
        const customOption = this.querySelector('option[value="custom"]');
        if (customOption) customOption.remove();
    });

    document.getElementById('zone').addEventListener('input', function() {
        if (!Array.from(this.options).find(option => option.value === this.value)) {
            const customOption = document.createElement('option');
            customOption.value = 'custom';
            customOption.textContent = 'Personalizado: ' + this.value;
            customOption.selected = true;
            this.appendChild(customOption);
        }
    });
</script>

</body>
</html>

