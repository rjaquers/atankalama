<?php if (!empty($productos)): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Total $</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($productos as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['producto']) ?></td>
                <td><?= $item['cantidad_total'] ?></td>
                <td>$ <?= number_format($item['total'], 0, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No se encontraron productos para esta fecha.</p>
<?php endif; ?>
