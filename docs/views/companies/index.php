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
    <i class="fa-solid fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <h3><i class="fa-solid fa-building"></i> Empresas</h3>
  <div>
    <?php if(AuthService::hasPermission('companies_create')): ?>
    <a href="<?= BASE_URL ?>/companies/create" class="btn btn-atk">
      <i class="fa-solid fa-plus"></i> Nueva Empresa
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4 fade-in">
  <div class="col-12 col-md-4">
    <div class="card kpi-card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Total Empresas</div>
            <div class="kpi-value"><?= (int)($totalClientes ?? 0) + (int)($totalProveedores ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-building fa-2x opacity-50" style="color: var(--atk-primary)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card kpi-card success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Clientes</div>
            <div class="kpi-value"><?= (int)($totalClientes ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-handshake fa-2x opacity-50" style="color: var(--atk-success)"></i>
        </div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="card kpi-card info">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label">Proveedores</div>
            <div class="kpi-value"><?= (int)($totalProveedores ?? 0) ?></div>
          </div>
          <i class="fa-solid fa-truck fa-2x opacity-50" style="color: var(--atk-info)"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3">
  <div class="card-body py-2">
    <form method="get" action="<?= BASE_URL ?>/companies" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label mb-1 small">Buscar</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Nombre, RUT..."
               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1 small">Tipo</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="cliente" <?= ($filters['type'] ?? '') === 'cliente' ? 'selected' : '' ?>>Cliente</option>
          <option value="proveedor" <?= ($filters['type'] ?? '') === 'proveedor' ? 'selected' : '' ?>>Proveedor</option>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-sm btn-atk"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= BASE_URL ?>/companies" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
      </div>
    </form>
  </div>
</div>

<!-- Tabla de empresas -->
<div class="card fade-in">
  <div class="card-body">
    <table id="tablaEmpresas" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>RUT</th>
          <th>Razón Social</th>
          <th>Nombre Fantasía</th>
          <th>Tipo</th>
          <th>Contacto</th>
          <th>Ciudad</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($companies as $c): ?>
        <tr>
          <td><?= htmlspecialchars($c['rut'] ?? '-') ?></td>
          <td>
            <a href="<?= BASE_URL ?>/companies/show/<?= $c['id'] ?>" class="text-decoration-none fw-bold">
              <?= htmlspecialchars($c['business_name']) ?>
            </a>
          </td>
          <td><?= htmlspecialchars($c['trade_name'] ?? '-') ?></td>
          <td>
            <?php if($c['type'] === 'cliente'): ?>
              <span class="badge bg-success"><i class="fa-solid fa-handshake"></i> Cliente</span>
            <?php else: ?>
              <span class="badge bg-info"><i class="fa-solid fa-truck"></i> Proveedor</span>
            <?php endif; ?>
          </td>
          <td>
            <small>
              <?= htmlspecialchars($c['contact_name'] ?? '') ?><br>
              <span class="text-muted"><?= htmlspecialchars($c['contact_email'] ?? '') ?></span>
            </small>
          </td>
          <td><?= htmlspecialchars($c['city'] ?? '-') ?></td>
          <td>
            <div class="btn-group btn-group-sm">
              <a href="<?= BASE_URL ?>/companies/show/<?= $c['id'] ?>" class="btn btn-outline-primary" title="Ver">
                <i class="fa-solid fa-eye"></i>
              </a>
              <?php if(AuthService::hasPermission('companies_edit')): ?>
              <a href="<?= BASE_URL ?>/companies/edit/<?= $c['id'] ?>" class="btn btn-outline-warning" title="Editar">
                <i class="fa-solid fa-pen"></i>
              </a>
              <?php endif; ?>
              <?php if(AuthService::hasPermission('companies_delete')): ?>
              <form method="post" action="<?= BASE_URL ?>/companies/delete/<?= $c['id'] ?>"
                    onsubmit="return confirm('¿Eliminar esta empresa?')" class="d-inline">
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
  </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaEmpresas').DataTable({
        order: [[1, 'asc']]
    });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
