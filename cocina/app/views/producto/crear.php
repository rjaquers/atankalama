<!DOCTYPE html>
<html lang='es'>

<head>
    <?php include(ROOT_PATH . '../public/static/templates/head.php'); ?>
</head>

<body class='bg-light'>
    <?php include(ROOT_PATH . '../public/static/templates/menu.php'); ?>

    <div class='container my-5'>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h2 class="h4 mb-0 text-gray-800">Crear Nuevo Producto</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="index.php?page=producto/crear" method="POST">
                            <div class="mb-3">
                                <label for="nombre" class="form-label text-muted fw-semibold">Nombre del
                                    Producto</label>
                                <input type="text" class="form-control form-control-lg" id="nombre" name="nombre"
                                    required placeholder="Ej. Desayuno Continental">
                            </div>

                            <div class="mb-4">
                                <label for="precio" class="form-label text-muted fw-semibold">Precio (CLP)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light text-muted border-end-0">$</span>
                                    <input type="number" class="form-control border-start-0 ps-0" id="precio"
                                        name="precio" required min="0" step="1" placeholder="Ej. 10500">
                                </div>
                            </div>

                            <div class="mb-4 bg-light p-3 rounded border">
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" checked
                                        value="1" style="height: 1.5em; width: 3em;">
                                    <label class="form-check-label ms-2 mt-1" for="activo">Producto Activo (Visible para
                                        pedidos)</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between pt-3">
                                <a href="index.php?page=producto/index"
                                    class="btn btn-outline-secondary btn-lg">Cancelar</a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">Guardar Producto</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include(ROOT_PATH . '../public/static/templates/footer.php'); ?>
</body>

</html>