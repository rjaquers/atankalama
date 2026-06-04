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
  <h3><i class="fa-solid fa-box-open"></i> Catálogo de Servicios</h3>
  <div>
    <?php if(AuthService::hasPermission('services_manage')): ?>
    <a href="<?= BASE_URL ?>/services/create" class="btn btn-atk">
      <i class="fa-solid fa-plus"></i> Nuevo Servicio
    </a>
    <?php endif; ?>
  </div>
</div>

<div class="card fade-in">
  <div class="card-body">
    <table id="tablaServicios" class="table table-striped table-hover">
      <thead>
        <tr>
          <th style="width: 50px">#</th>
          <th>Nombre</th>
          <th>Precio Base</th>
          <th>Descripción</th>
          <th>Estado</th>
          <?php if(AuthService::hasPermission('services_manage')): ?>
          <th style="width: 120px">Acciones</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach($services as $s): ?>
        <tr class="<?= (int)$s['active'] === 0 ? 'table-secondary' : '' ?>">
          <td><?= (int)$s['id'] ?></td>
          <td class="fw-bold">
            <i class="fa-solid fa-concierge-bell text-muted"></i>
            <?= htmlspecialchars($s['name']) ?>
          </td>
          <td>
            <span class="fw-bold text-success">$<?= number_format($s['base_price'] ?? 0, 0, ',', '.') ?></span>
          </td>
          <td class="text-muted"><?= htmlspecialchars($s['description'] ?? '-') ?></td>
          <td>
            <?php if((int)$s['active'] === 1): ?>
              <span class="badge bg-success">Activo</span>
            <?php else: ?>
              <span class="badge bg-danger">Inactivo</span>
            <?php endif; ?>
          </td>
          <?php if(AuthService::hasPermission('services_manage')): ?>
          <td>
            <div class="btn-group btn-group-sm">
              <a href="<?= BASE_URL ?>/services/edit/<?= $s['id'] ?>" class="btn btn-outline-warning" title="Editar">
                <i class="fa-solid fa-pen"></i>
              </a>
              <?php if((int)$s['active'] === 1): ?>
              <form method="post" action="<?= BASE_URL ?>/services/delete/<?= $s['id'] ?>"
                    onsubmit="return confirm('¿Eliminar este servicio del catálogo?')" class="d-inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#tablaServicios').DataTable({ order: [[1, 'asc']] });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
