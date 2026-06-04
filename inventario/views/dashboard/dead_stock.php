<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =
  ===================================================
-->

<!--
Resumen:
Listado gerencial de productos sin movimiento en los
últimos 30 días para detectar sobrestock y capital detenido.
-->
<br>
<br>
<div class="card shadow-sm">

    <div class='card-header bg-danger text-white'>
        <h5 class='mb-0'>
            <i class='fas fa-times-circle'></i>
            Productos Sin Movimiento
        </h5>
    </div>
    <div class="card-body">
        <table class='table table-striped table-hover datatable-export'>
            <thead class="table-light">
            <tr>
                <th>Producto</th>
                <th>Stock</th>
                <th>Última Actualización</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($deadStock)): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No existen productos sin movimiento.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($deadStock as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= (int)$p['quantity'] ?></td>
                        <td><?= htmlspecialchars($p['updated_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
