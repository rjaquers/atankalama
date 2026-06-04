<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com                   =
  = Fecha: <?= date('Y') ?>                        =
  ===================================================
-->
<?php
/**
 * Página:
 * Listado de productos SIN stock.
 */
?>
<br>
<br>
<div class="card">
    <div class="card-header bg-danger text-white">
        <h5 class="mb-0">
            <i class="fas fa-times-circle"></i>
            Productos Sin Stock
        </h5>
    </div>

    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="alert alert-success">
                No existen productos sin stock actualmente.
            </div>
        <?php else: ?>
            <table class="table table-striped table-hover datatable-export">
                <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Ubicación</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Mínimo</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= $p['category'] ?? 'Sin categoría' ?></td>
                        <td><?= $p['location'] ?? 'Sin ubicación' ?></td>
                        <td class="text-center text-danger fw-bold">0</td>
                        <td class="text-center"><?= $p['min_stock'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
