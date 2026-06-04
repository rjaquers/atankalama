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
  <h3><i class="fa-solid fa-users"></i> Usuarios del Sistema</h3>
  <div>
    <a href="<?= BASE_URL ?>/users/create" class="btn btn-atk">
      <i class="fa-solid fa-plus"></i> Nuevo Usuario
    </a>
  </div>
</div>

<!-- Resumen por roles -->
<div class="row g-3 mb-4 fade-in">
  <?php
    $roleCounts = [];
    foreach($users as $u) {
      $r = $u['role_name'] ?? 'sin_rol';
      $roleCounts[$r] = ($roleCounts[$r] ?? 0) + 1;
    }
    $roleIcons = [
      'admin' => ['fa-shield-halved', ''],
      'vendedor' => ['fa-user-tie', 'success'],
      'cobranzas' => ['fa-money-bill-wave', 'warning'],
      'recepcion' => ['fa-concierge-bell', 'info'],
    ];
  ?>
  <?php foreach($roleIcons as $roleName => $iconData): ?>
  <div class="col-6 col-md-3">
    <div class="card kpi-card <?= $iconData[1] ?>">
      <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="kpi-label"><?= ucfirst($roleName) ?></div>
            <div class="kpi-value" style="font-size: 1.5rem"><?= (int)($roleCounts[$roleName] ?? 0) ?></div>
          </div>
          <i class="fa-solid <?= $iconData[0] ?> fa-lg opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Tabla de usuarios -->
<div class="card fade-in">
  <div class="card-body">
    <table id="tablaUsuarios" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $u): ?>
        <tr class="<?= (int)$u['status'] === 0 ? 'table-secondary' : '' ?>">
          <td><?= (int)$u['id'] ?></td>
          <td>
            <i class="fa-solid fa-user-circle text-muted"></i>
            <?= htmlspecialchars($u['name']) ?>
          </td>
          <td>
            <a href="mailto:<?= htmlspecialchars($u['email']) ?>"><?= htmlspecialchars($u['email']) ?></a>
          </td>
          <td>
            <?php
              $roleClass = [
                'admin' => 'bg-dark',
                'vendedor' => 'bg-success',
                'cobranzas' => 'bg-warning text-dark',
                'recepcion' => 'bg-info',
              ];
              $cls = $roleClass[$u['role_name'] ?? ''] ?? 'bg-secondary';
            ?>
            <span class="badge <?= $cls ?>"><?= ucfirst(htmlspecialchars($u['role_name'] ?? 'N/A')) ?></span>
          </td>
          <td>
            <?php if((int)$u['status'] === 1): ?>
              <span class="badge bg-success"><i class="fa-solid fa-check"></i> Activo</span>
            <?php else: ?>
              <span class="badge bg-danger"><i class="fa-solid fa-ban"></i> Inactivo</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="btn-group btn-group-sm">
              <a href="<?= BASE_URL ?>/users/edit/<?= $u['id'] ?>" class="btn btn-outline-warning" title="Editar">
                <i class="fa-solid fa-pen"></i>
              </a>
              <?php if((int)$u['id'] !== AuthService::userId()): ?>
              <form method="post" action="<?= BASE_URL ?>/users/toggle/<?= $u['id'] ?>"
                    onsubmit="return confirm('<?= (int)$u['status'] === 1 ? '¿Desactivar este usuario?' : '¿Activar este usuario?' ?>')"
                    class="d-inline">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit" class="btn btn-outline-<?= (int)$u['status'] === 1 ? 'danger' : 'success' ?>"
                        title="<?= (int)$u['status'] === 1 ? 'Desactivar' : 'Activar' ?>">
                  <i class="fa-solid <?= (int)$u['status'] === 1 ? 'fa-ban' : 'fa-check' ?>"></i>
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
    $('#tablaUsuarios').DataTable({
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
