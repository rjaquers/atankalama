<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com                    =
  = Fecha: <?= date('Y') ?>                         =
  ===================================================
-->
<!-- Resumen: Formulario para crear productos con carga múltiple de imágenes -->

<h2>Agregar Nuevo Producto</h2>

<form action="index.php?controller=producto&action=guardar" method="POST" enctype="multipart/form-data" class="p-3 border rounded bg-light">
    <div class="row mb-3">
        <div class="col-md-6">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label>Unidad</label>
            <input type="text" name="unit" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label>Descripción</label>
        <textarea name="description" class="form-control"></textarea>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <label>Cantidad</label>
            <input type="number" name="quantity" class="form-control" value="0">
        </div>
        <div class="col-md-3">
            <label>Stock mínimo</label>
            <input type="number" name="min_stock" class="form-control" value="0">
        </div>
        <div class="col-md-3">
            <label>Categoría (ID)</label>
            <input type="number" name="category_id" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Ubicación (ID)</label>
            <input type="number" name="location_id" class="form-control">
        </div>
    </div>

    <div class="mb-3">
        <label>Estado</label>
        <select name="status" class="form-control">
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Fotos del producto</label>
        <input type="file" name="fotos[]" class="form-control" multiple accept="image/*">
        <small class="text-muted">Puedes seleccionar varias imágenes a la vez.</small>
    </div>

    <button type="submit" class="btn btn-success">Guardar Producto</button>
    <a href="index.php?controller=producto&action=listar" class="btn btn-secondary">Cancelar</a>
</form>

<!--<h2>Crear Producto</h2>-->
<!--<form method="post" action="index.php?controller=producto&action=crear">-->
<!--    <label>Nombre: <input type="text" name="nombre" required></label><br>-->
<!--    <label>Categoría: <input type="text" name="categoria"></label><br>-->
<!--    <label>Stock: <input type="number" name="stock" value="0"></label><br>-->
<!--    <label>Ubicación: <input type="text" name="ubicacion"></label><br>-->
<!--    <label>Stock mínimo: <input type="number" name="stock_minimo" value="0"></label><br>-->
<!--    <button type="submit">Guardar</button>-->
<!--</form>-->