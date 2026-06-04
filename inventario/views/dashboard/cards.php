
<div class='row g-3 mb-3 text-center'>
        <div class='col-6 col-md-3'>
            <div class='card border-primary shadow-sm h-100'>
                <div class='card-body'>
                    <i class='fas fa-boxes fa-2x text-primary mb-2'></i>
                    <h6 class='text-uppercase text-primary small'>Total Productos</h6>
                    <h4 class='fw-bold text-dark'><?= $totalProductos ?? "Sin datos" ?></h4>
</div>
</div>
</div>

<div class="col-6 col-md-3">
    <div class="card border-success shadow-sm h-100">
        <div class="card-body">
            <i class="fas fa-warehouse fa-2x text-success mb-2"></i>
            <h6 class="text-uppercase text-success small">Items en Stock</h6>
            <h4 class="fw-bold text-dark"><?=$itemsStock ?? "Sin datos"?></h4>
        </div>
    </div>
</div>

<div class="col-6 col-md-3">
    <div class="card border-warning shadow-sm h-100">
        <a href='index.php?page=products&action=low_stock' class='text-decoration-none'>
        <div class="card-body">
            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
            <h6 class="text-uppercase text-warning small">Stock Bajo</h6>
            <h4 class="fw-bold text-dark"> <?=$lowStockCount?></h4>
        </div>
        </a>
    </div>
</div>

<div class="col-6 col-md-3">
    <div class="card border-danger shadow-sm h-100">

        <a href='index.php?page=products&action=sin_stock' class='text-decoration-none'>
            <div class='card-body'>
                <i class='fas fa-times-circle fa-2x text-danger mb-2'></i>
                <h6 class='text-uppercase text-danger small'>Sin Stock</h6>
                <h4 class='fw-bold text-dark'><?=$sinStock?></h4>
            </div>
        </a>




    </div>
</div>


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
    Dashboard gerencial con indicadores clave de salud del inventario,
    consumo crítico y productos sin movimiento para toma de decisiones.
    -->

    <div class="row g-3 mb-4 text-center">


        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h6>Stock OK</h6>
                    <h3 class="text-success"><?= $inventoryHealth['ok'] ?></h3>
                </div>
            </div>
        </div>



        <div class="col-md-3">
            <a href='index.php?page=dashboard&action=dead_stock'
               class='text-decoration-none'>
                <div class='card shadow-sm border-warning h-100'>
                    <div class='card-body text-center'>
                        <i class='fas fa-pause-circle fa-2x text-warning mb-2'></i>
                        <h6 class='text-uppercase text-warning small'>
                            Sin Movimiento (30d)
                        </h6>
                        <h4 class='fw-bold text-dark'>
                            <?=$inventoryHealth['no_movement']?>
                        </h4>
                    </div>
                </div>
            </a>

        </div>
    </div>

    <hr>
<div class="row">
    <div class="col-6">
        <h5>Top Productos Más Consumidos (30 días)</h5>
        <table class='table table-sm table-striped'>
            <thead>
            <tr>
                <th>Producto</th>
                <th>Total Consumido</th>
                <th>Stock Actual</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($topConsumed as $p): ?>
                <tr>
                    <td><?=htmlspecialchars($p['name'])?></td>
                    <td><?=$p['total_consumed']?></td>
                    <td><?=$p['current_stock']?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>


    <hr>




</div>