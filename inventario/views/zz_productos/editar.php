<h2>Editar Producto</h2>
<form method='post' action='index.php?controller=producto&action=editar'>
    <input type='hidden' name='producto_id' value="<?=$producto['producto_id']?>">
    <label>Nombre: <input type='text' name='nombre' value="<?=htmlspecialchars($producto['nombre'])?>" required></label><br>
    <label>Categoría: <input type='text' name='categoria' value="<?=htmlspecialchars($producto['categoria'])?>"></label><br>
    <label>Stock: <input type='number' name='stock' value="<?=$producto['stock']?>"></label><br>
    <label>Ubicación: <input type='text' name='ubicacion' value="<?=htmlspecialchars($producto['ubicacion'])?>"></label><br>
    <label>Stock mínimo: <input type='number' name='stock_minimo' value="<?=$producto['stock_minimo']?>"></label><br>
    <button type='submit'>Actualizar</button>
</form>
