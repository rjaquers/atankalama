<!DOCTYPE html>
<html lang='es'>
<head>
    <?php
    $page_title = 'Dashboard';
    include 'views/layout/header.php'; // SOLO metadatos y links (sin <body> ni <nav>)
    ?>
</head>
<body>
<?php include 'views/layout/navbar.php'; ?>
<?php
$page_title = 'Eventos de Consumo';
include 'views/layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-shopping-cart me-2"></i>Eventos de Consumo</h2>
    <a href="index.php?page=consumption&action=create" class="btn btn-success">
        <i class="fas fa-plus me-2"></i>Registrar Consumo
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Ubicación del Consumo</th>
                        <th>Usuario</th>
                        <th>Descripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td>
                            <small><?php echo formatDate($event['event_date']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($event['product_name']); ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-warning">
                                <?php echo $event['quantity_consumed']; ?> <?php echo htmlspecialchars($event['unit']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($event['consumption_location']): ?>
                                <small><?php echo htmlspecialchars($event['consumption_location']); ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($event['user_name']); ?></small>
                        </td>
                        <td>
                            <?php if ($event['description']): ?>
                                <small><?php echo htmlspecialchars($event['description']); ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($events)): ?>
        <div class="text-center py-4">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay eventos de consumo registrados</h5>
            <p class="text-muted">Los eventos de consumo aparecerán aquí una vez que se registren.</p>
            <a href="index.php?page=consumption&action=create" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Registrar Primer Consumo
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>