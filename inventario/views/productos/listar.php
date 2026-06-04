<h2>Listado de Productos</h2>
<a href='index.php?controller=producto&action=guardar' class='btn btn-primary'>Nuevo Producto..</a>
<!--<a href='index.php?controller=producto&action=crear' class='btn btn-primary'>Nuevo Producto</a>-->

<table border="1">
    <tr>
        <th>acciones</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>Stock</th>
        <th>Ubicación</th>
    </tr>
    <?php foreach ($productos as $p): ?>
    <tr>
        <td>  <a href="index.php?controller=producto&action=editar&id=<?= $p['producto_id'] ?>">Editar</a>  </td>
        <td><?= htmlspecialchars($p['nombre']) ?></td>
        <td><?= htmlspecialchars($p['categoria']) ?></td>
        <td><?= $p['stock'] ?></td>
        <td><?= htmlspecialchars($p['ubicacion']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
