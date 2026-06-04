<h2>Historial de Movimientos</h2>
<table border="1">
    <tr>
        <th>Producto</th>
        <th>Tipo</th>
        <th>Cantidad</th>
        <th>Responsable</th>
        <th>Justificación</th>
        <th>Fecha</th>
    </tr>
    <?php foreach ($movimientos as $m): ?>
    <tr>
        <td><?= htmlspecialchars($m['producto']) ?></td>
        <td><?= htmlspecialchars($m['tipo']) ?></td>
        <td><?= $m['cantidad'] ?></td>
        <td><?= htmlspecialchars($m['responsable']) ?></td>
        <td><?= htmlspecialchars($m['justificacion']) ?></td>
        <td><?= $m['fecha_movimiento'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>