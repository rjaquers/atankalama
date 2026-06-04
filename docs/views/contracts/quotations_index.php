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
  <h3><i class="fa-solid fa-file-invoice-dollar"></i> Cotizaciones / Propuestas</h3>
  <div>
    <?php if(AuthService::hasPermission('contracts_create')): ?>
    <a href="<?= BASE_URL ?>/quotations/create" class="btn btn-atk">
      <i class="fa-solid fa-plus"></i> Nueva Cotización
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3 shadow-sm border-0">
  <div class="card-body py-2">
    <form method="get" action="<?= BASE_URL ?>/quotations" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label mb-1 small fw-bold">Buscar</label>
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Empresa, código..."
               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label mb-1 small fw-bold">Estado</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Todas</option>
          <option value="quotation_draft" <?= ($filters['status'] ?? '') === 'quotation_draft' ? 'selected' : '' ?>>Borrador</option>
          <option value="quotation_sent" <?= ($filters['status'] ?? '') === 'quotation_sent' ? 'selected' : '' ?>>Enviada</option>
          <option value="quotation_approved" <?= ($filters['status'] ?? '') === 'quotation_approved' ? 'selected' : '' ?>>Aprobada</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label mb-1 small fw-bold">Empresa</label>
        <select name="company_id" class="form-select form-select-sm">
          <option value="">Todas</option>
          <?php foreach($companiesSelect as $co): ?>
          <option value="<?= $co['id'] ?>" <?= (int)($filters['company_id'] ?? 0) === (int)$co['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($co['business_name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <button type="submit" class="btn btn-sm btn-atk"><i class="fa-solid fa-filter"></i> Filtrar</button>
        <a href="<?= BASE_URL ?>/quotations" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eraser"></i> Limpiar</a>
      </div>
    </form>
  </div>
</div>

<!-- Tabla de cotizaciones -->
<div class="card shadow-sm border-0">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="bg-light">
        <tr>
          <th>Código / Versión</th>
          <th>Empresa</th>
          <th>Tipo</th>
          <th>Fecha Inicio</th>
          <th>Estado</th>
          <th>Vendedor</th>
          <th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($quotations)): ?>
          <tr>
            <td colspan="7" class="text-center py-4 text-muted">No se encontraron cotizaciones.</td>
          </tr>
        <?php else: ?>
          <?php foreach($quotations as $q): ?>
          <tr>
            <td>
              <span class="fw-bold"><?= htmlspecialchars($q['code']) ?></span>
              <span class="badge bg-light text-dark border ms-1">v<?= (int)$q['version_number'] ?></span>
            </td>
            <td>
              <div class="fw-bold text-atk"><?= htmlspecialchars($q['business_name']) ?></div>
              <small class="text-muted"><?= htmlspecialchars($q['company_rut'] ?? '') ?></small>
            </td>
            <td>
              <?php
                $types = ['arriendo' => '🏠 Arriendo', 'hospedaje' => '🛏️ Hospedaje', 'proveedor' => '🚚 Prov.'];
                echo $types[$q['contract_type']] ?? $q['contract_type'];
              ?>
            </td>
            <td><?= !empty($q['start_date']) ? date('d/m/Y', strtotime($q['start_date'])) : '-' ?></td>
            <td>
              <?php
                $badges = [
                  'quotation_draft'    => 'bg-secondary',
                  'quotation_sent'     => 'bg-info',
                  'quotation_approved' => 'bg-success'
                ];
                $labels = [
                  'quotation_draft'    => 'Borrador',
                  'quotation_sent'     => 'Enviada',
                  'quotation_approved' => 'Aprobada'
                ];
              ?>
              <span class="badge <?= $badges[$q['status']] ?? 'bg-light text-dark' ?>">
                <?= $labels[$q['status']] ?? $q['status'] ?>
              </span>
            </td>
            <td><small><?= htmlspecialchars($q['created_by_name'] ?? 'N/A') ?></small></td>
            <td class="text-end">
              <div class="btn-group">
                <a href="<?= BASE_URL ?>/quotations/show/<?= $q['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver">
                  <i class="fa-solid fa-eye"></i>
                </a>
                <a href="<?= BASE_URL ?>/quotations/edit/<?= $q['id'] ?>" class="btn btn-sm btn-outline-atk" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </a>
                <a href="<?= BASE_URL ?>/quotations/createVersion/<?= $q['id'] ?>" class="btn btn-sm btn-outline-success" title="Copiar Cotización" onclick="return confirm('¿Desea crear una copia de esta cotización?')">
                  <i class="fa-solid fa-copy"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
