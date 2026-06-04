<!--
  = Proyecto: Sistema de Contratos Atankalama =
  = Autor: Rodrigo Jaque Escobar              =
  = Contacto: rjaquers@gmail.com              =

-->
<?php
$title = "Panel de Reportes";
include VIEW_PATH . "/layouts/header.php";
?>

<div class="content">
    <div class="container-fluid">
        <!-- Título -->
        <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
            <div>
                <h2 class="h3 fw-bold mb-1"><i class="fa-solid fa-chart-line text-primary me-2"></i> Reportes Ejecutivos</h2>
                <p class="text-muted"><i class="fa-regular fa-clock me-1"></i> Generación de datos consolidados para directoria y cobranzas.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/reports/export_contracts" class="btn btn-outline-success border-2 fw-semibold">
                    <i class="fa-solid fa-file-csv me-1"></i> Exportar Contratos (CSV)
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- KPIs Rápidos -->
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-primary text-white">
                    <small class="opacity-75 uppercase fw-bold">Total por Cobrar</small>
                    <h3 class="fw-bold mb-0 mt-1">$<?= number_format($totalPending, 0, ',', '.') ?></h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 bg-dark text-white">
                    <small class="opacity-75 uppercase fw-bold">Contratos Vigentes</small>
                    <h3 class="fw-bold mb-0 mt-1"><?= count(array_filter($contracts, fn($c) => $c['status'] === 'vigente')) ?></h3>
                </div>
            </div>
        </div>

        <!-- Tabla Resumen -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">Resumen Global de Contratos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="reportsTable" class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Código</th>
                                <th>Empresa</th>
                                <th>Tipo</th>
                                <th>Monto Base</th>
                                <th>Estado</th>
                                <th>Inicio</th>
                                <th>Término</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contracts as $c): ?>
                                <tr>
                                    <td class="fw-semibold text-primary"><?= $c['code'] ?></td>
                                    <td><?= htmlspecialchars($c['business_name']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle uppercase px-2 py-1">
                                            <?= $c['contract_type'] ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold">$<?= number_format($c['base_amount'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php 
                                        switch($c['status']) {
                                            case 'vigente': $badgeClass = 'bg-success'; break;
                                            case 'vencido': $badgeClass = 'bg-danger'; break;
                                            case 'por_renovar': $badgeClass = 'bg-warning'; break;
                                            case 'borrador': $badgeClass = 'bg-info'; break;
                                            default: $badgeClass = 'bg-secondary';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> px-2 py-1 uppercase"><?= $c['status'] ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($c['start_date'])) ?></td>
                                    <td><?= $c['end_date'] ? date('d/m/Y', strtotime($c['end_date'])) : 'Indefinido' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#reportsTable').DataTable({
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
        pageLength: 25,
        dom: 'Bfrtip',
        buttons: ['excel', 'pdf', 'print']
    });
});
</script>

<?php include VIEW_PATH . "/layouts/footer.php"; ?>
