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
<div class="page-header d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0"><i class="fa-solid fa-hotel text-primary me-2"></i> Gestión de Hoteles</h3>
  <div>
    <a href="<?= BASE_URL ?>/hotels/create" class="btn btn-atk rounded-pill px-4 shadow-sm">
      <i class="fa-solid fa-plus me-1"></i> Nuevo Hotel
    </a>
  </div>
</div>

<!-- Tabla de hoteles -->
<div class="card border-0 shadow-sm rounded-4 fade-in">
  <div class="card-body p-4">
    <div class="table-responsive">
      <table id="tablaHoteles" class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th class="border-0">Cód.</th>
            <th class="border-0">Nombre del Hotel</th>
            <th class="border-0">RUT</th>
            <th class="border-0">Ciudad</th>
            <th class="border-0">Representante Legal</th>
            <th class="border-0">Estado</th>
            <th class="border-0 text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($hotels as $h): ?>
          <tr>
            <td>
              <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">
                <?= htmlspecialchars($h['code']) ?>
              </span>
            </td>
            <td>
              <div class="fw-bold text-dark"><?= htmlspecialchars($h['name']) ?></div>
              <small class="text-muted"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($h['address'] ?? '-') ?></small>
            </td>
            <td><?= htmlspecialchars($h['rut'] ?? '-') ?></td>
            <td><?= htmlspecialchars($h['city'] ?? '-') ?></td>
            <td>
              <div class="small fw-semibold"><?= htmlspecialchars($h['legal_representative'] ?? '-') ?></div>
              <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($h['representative_rut'] ?? '') ?></div>
            </td>
            <td>
              <?php if($h['active']): ?>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Activo</span>
              <?php else: ?>
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactivo</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                <a href="<?= BASE_URL ?>/hotels/edit/<?= $h['id'] ?>" class="btn btn-white btn-sm border-end" title="Editar">
                  <i class="fa-solid fa-pen text-warning"></i>
                </a>
                <?php if($h['active']): ?>
                <form method="post" action="<?= BASE_URL ?>/hotels/delete/<?= $h['id'] ?>"
                      onsubmit="return confirm('¿Está seguro de desactivar este hotel?')" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                  <button type="submit" class="btn btn-white btn-sm" title="Desactivar">
                    <i class="fa-solid fa-trash text-danger"></i>
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
</div>

<script>
$(document).ready(function() {
    $('#tablaHoteles').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        order: [[1, 'asc']],
        pageLength: 10,
        dom: '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
    });
});
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
