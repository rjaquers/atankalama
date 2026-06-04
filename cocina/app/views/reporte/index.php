<h1>Cocina Atankalama</h1>
<h2>Resumen de Órdenes - Últimos Días</h2>
<table border='1' cellpadding='8' cellspacing='0'>
    <th>
        <th>Fecha</th>
        <th>Total de Ordenes</th>
        <th>Ventas Totales</th>
    </th>
    <?php foreach ($datos as $fila): ?>
        <tr>
            <td></td>
            <td align='center'><?= date('d-m-Y', strtotime($fila['fecha'])) ?></td>
            <td align='center'><?= $fila['total_ordenes'] ?></td>
            <td align='center'>$ <?= number_format($fila['total_ventas'], 0, ',', '.') ?></td>
        </tr>
    <?php endforeach; ?>
</table>


