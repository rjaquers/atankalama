<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if(!empty($_SESSION['flash_error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <i class="fa-solid fa-exclamation-circle"></i> <?= $_SESSION['flash_error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <h3><i class="fa-solid fa-file-contract"></i> Convenios</h3>
  <div>
    <?php if(AuthService::hasPermission('contracts_create')): ?>
    <a href="<?= BASE_URL ?>/contracts/create" class="btn btn-atk">
      <i class="fa-solid fa-plus"></i> Nuevo Contrato
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4 fade-in">
  <div class="col-6 col-md">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Total</div>
            <div class="kpi-value"><?= (int)($kpis['total'] ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-file-contract fa-2x opacity-50" style="color: var(--atk-primary)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card kpi-card success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Vigentes</div>
            <div class="kpi-value"><?= (int)($kpis['vigentes'] ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-check-circle fa-2x opacity-50" style="color: var(--atk-success)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card kpi-card info">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Borradores</div>
            <div class="kpi-value"><?= (int)($kpis['borradores'] ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-pen-to-square fa-2x opacity-50" style="color: var(--atk-info)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card kpi-card warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Por Renovar</div>
            <div class="kpi-value"><?= (int)($kpis['por_renovar'] ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-clock fa-2x opacity-50" style="color: var(--atk-warning)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md">
    <div class="card kpi-card danger">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Vencidos</div>
            <div class="kpi-value"><?= (int)($kpis['vencidos'] ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-exclamation-triangle fa-2x opacity-50" style="color: var(--atk-danger)"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" action="<?= BASE_URL ?>/contracts" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label mb-1 small">Buscar</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Código, empresa..."
               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small">Estado</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="borrador" <?= ($filters['status'] ?? '') === 'borrador' ? 'selected' : '' ?>>Borrador</option>
          <option value="vigente" <?= ($filters['status'] ?? '') === 'vigente' ? 'selected' : '' ?>>Vigente</option>
          <option value="por_renovar" <?= ($filters['status'] ?? '') === 'por_renovar' ? 'selected' : '' ?>>Por Renovar</option>
          <option value="vencido" <?= ($filters['status'] ?? '') === 'vencido' ? 'selected' : '' ?>>Vencido</option>
          <option value="cancelado" <?= ($filters['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small">Tipo</label>
        <select name="contract_type" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="arriendo" <?= ($filters['contract_type'] ?? '') === 'arriendo' ? 'selected' : '' ?>>Arriendo</option>
          <option value="hospedaje" <?= ($filters['contract_type'] ?? '') === 'hospedaje' ? 'selected' : '' ?>>Hospedaje</option>
          <option value="proveedor" <?= ($filters['contract_type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small">Empresa</label>
        <select name="company_id" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php foreach($companiesSelect as $co): ?>
          <option value="<?= $co['id'] ?>" <?= (int)($filters['company_id'] ?? 0) === (int)$co['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($co['business_name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-sm btn-atk"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= BASE_URL ?>/contracts" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
      </div>
    </form>
  </div>
</div>

<!-- Tabla de contratos -->
<div class="card fade-in">
  <div class="card-body">
    <?php if(empty($contracts)): ?>
      <div class="text-center text-muted py-5">
        <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-25"></i>
        <p>No se encontraron contratos con los filtros seleccionados</p>
        <?php if(AuthService::hasPermission('contracts_create')): ?>
        <a href="<?= BASE_URL ?>/contracts/create" class="btn btn-atk mt-2">
          <i class="fa-solid fa-plus"></i> Crear primer contrato
        </a>
        <?php endif; ?>
      </div>
    <?php else: ?>
    <table id="tablaContratos" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Código</th>
          <th>Empresa</th>
          <th>Tipo</th>
          <th>Inicio</th>
          <th>Término</th>
          <th>Monto Base</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($contracts as $c): ?>
        <tr>
          <td>
            <a href="<?= BASE_URL ?>/contracts/show/<?= $c['id'] ?>" class="text-decoration-none fw-bold">
              <?= htmlspecialchars($c['code']) ?>
            </a>
          </td>
          <td>
            <a href="<?= BASE_URL ?>/companies/show/<?= $c['company_id'] ?>" class="text-decoration-none">
              <?= htmlspecialchars($c['business_name'] ?? '') ?>
            </a>
            <?php if(!empty($c['trade_name'])): ?>
              <br><small class="text-muted"><?= htmlspecialchars($c['trade_name']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <?php
              $typeIcons = ['arriendo' => 'fa-house', 'hospedaje' => 'fa-bed', 'proveedor' => 'fa-truck'];
              $typeColors = ['arriendo' => 'bg-primary', 'hospedaje' => 'bg-success', 'proveedor' => 'bg-info'];
              $icon = $typeIcons[$c['contract_type']] ?? 'fa-file';
              $color = $typeColors[$c['contract_type']] ?? 'bg-secondary';
            ?>
            <span class="badge <?= $color ?>">
              <i class="fa-solid <?= $icon ?>"></i> <?= ucfirst(htmlspecialchars($c['contract_type'])) ?>
            </span>
          </td>
          <td><?= date('d/m/Y', strtotime($c['start_date'])) ?></td>
          <td>
            <?php if($c['end_date']): ?>
              <?= date('d/m/Y', strtotime($c['end_date'])) ?>
              <?php
                $daysLeft = (strtotime($c['end_date']) - time()) / 86400;
                if ($daysLeft <= 30 && $daysLeft > 0 && $c['status'] === 'vigente'):
              ?>
                <br><small class="text-warning"><i class="fa-solid fa-clock"></i> <?= (int)$daysLeft ?> días</small>
              <?php elseif ($daysLeft <= 0 && $c['status'] === 'vigente'): ?>
                <br><small class="text-danger"><i class="fa-solid fa-exclamation-triangle"></i> Vencido</small>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-muted">Indefinido</span>
            <?php endif; ?>
          </td>
          <td class="text-end">$<?= number_format((float)$c['base_amount'], 0, ',', '.') ?></td>
          <td>
            <?php
              $statusClasses = [
                'borrador'    => 'bg-secondary',
                'vigente'     => 'bg-success',
                'por_renovar' => 'bg-warning text-dark',
                'vencido'     => 'bg-danger',
                'cancelado'   => 'bg-dark',
              ];
              $statusClass = $statusClasses[$c['status']] ?? 'bg-secondary';
              $statusLabel = ucfirst(str_replace('_', ' ', $c['status'] ?? ''));
            ?>
            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
          </td>
          <td>
            <div class="btn-group btn-group-sm">
              <a href="<?= BASE_URL ?>/contracts/show/<?= $c['id'] ?>" class="btn btn-outline-primary" title="Ver">
                <i class="fa-solid fa-eye"></i>
              </a>
              <?php if(AuthService::hasPermission('contracts_edit')): ?>
              <a href="<?= BASE_URL ?>/contracts/edit/<?= $c['id'] ?>" class="btn btn-outline-warning" title="Editar">
                <i class="fa-solid fa-pen"></i>
              </a>
              <?php endif; ?>
              <?php if(AuthService::hasPermission('contracts_delete')): ?>
              <form method="post" action="<?= BASE_URL ?>/contracts/delete/<?= $c['id'] ?>"
                    onsubmit="return confirm('¿Eliminar este contrato?')" class="d-inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaContratos').DataTable({
        order: [[0, 'desc']],
        columnDefs: [
            { orderable: false, targets: -1 }
        ]
    });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
