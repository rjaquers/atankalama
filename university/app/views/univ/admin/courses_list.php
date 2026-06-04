<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
  <h3 class="m-0 fs-4"><i class="fa-solid fa-graduation-cap"></i> Gestión de Cursos</h3>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= BASE_URL ?>/index.php?route=univAdmin/asignaciones" class="btn btn-outline-primary btn-sm flex-fill">
      <i class="fa-solid fa-user-graduate"></i> Asignar
    </a>
    <a href="<?= BASE_URL ?>/univAdmin/create" class="btn btn-primary btn-sm flex-fill">
      <i class="fa-solid fa-plus"></i> Nuevo Curso
    </a>
  </div>
</div>

<!-- Vista de Escritorio: Tabla (Oculta en móviles) -->
<div class="card shadow-sm d-none d-lg-block">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="coursesTable">
        <thead class="table-light">
          <tr>
            <th class="ps-3">ID</th>
            <th>Nombre</th>
            <th>Tipo</th>
            <th>Créditos</th>
            <th>Estado</th>
            <th>Versión</th>
            <th class="text-end pe-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($courses as $c): ?>
            <tr>
              <td class="ps-3"><?= $c['id'] ?></td>
              <td>
                <div class="fw-bold"><?= htmlspecialchars($c['nombre']) ?></div>
                <div class="text-muted small text-truncate" style="max-width: 250px;"><?= htmlspecialchars($c['descripcion']) ?></div>
              </td>
              <td>
                <?php
                $badge = 'bg-secondary';
                if ($c['tipo'] == 'obligatorio_legal') $badge = 'bg-danger';
                if ($c['tipo'] == 'obligatorio_area') $badge = 'bg-warning text-dark';
                ?>
                <span class="badge <?= $badge ?>"><?= htmlspecialchars($c['tipo']) ?></span>
              </td>
              <td><?= (int)$c['creditos'] ?></td>
              <td>
                <?php if ($c['activo']): ?>
                  <span class="badge bg-success">Activo</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Inactivo</span>
                <?php endif; ?>
              </td>
              <td>v<?= $c['version'] ?></td>
              <td class="text-end pe-3">
                <div class="btn-group">
                  <a href="<?= BASE_URL ?>/univAdmin/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline-dark" title="Editar Info">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/univAdmin/pages/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Gestionar Páginas">
                    <i class="fa-solid fa-file-lines"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/univAdmin/questions/<?= $c['id'] ?>" class="btn btn-sm btn-outline-info" title="Banco de Preguntas">
                    <i class="fa-solid fa-circle-question"></i>
                  </a>
                  <a href="<?= BASE_URL ?>/univAdmin/alumnos/<?= $c['id'] ?>" class="btn btn-sm btn-outline-success" title="Asignar Alumnos">
                    <i class="fa-solid fa-user-graduate"></i>
                  </a>
                  <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?= $c['id'] ?>)" title="Eliminar">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Vista Móvil: Cards (Oculta en pantallas grandes) -->
<div class="d-lg-none">
  <?php foreach ($courses as $c): ?>
    <div class="card mb-3 shadow-sm border-0">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge bg-light text-dark border small">ID: <?= $c['id'] ?></span>
            <div class="d-flex gap-1">
                <span class="badge <?= $c['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $c['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
                <span class="badge bg-info text-dark">v<?= $c['version'] ?></span>
            </div>
        </div>
        
        <h5 class="fw-bold mb-1"><?= htmlspecialchars($c['nombre']) ?></h5>
        <div class="small text-muted mb-3"><?= htmlspecialchars($c['descripcion']) ?></div>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <?php
            $badge = 'bg-secondary';
            if ($c['tipo'] == 'obligatorio_legal') $badge = 'bg-danger';
            if ($c['tipo'] == 'obligatorio_area') $badge = 'bg-warning text-dark';
            ?>
            <span class="badge <?= $badge ?>"><?= htmlspecialchars($c['tipo']) ?></span>
            <span class="fw-bold small"><?= (int)$c['creditos'] ?> créditos</span>
        </div>

        <div class="border-top pt-3 mt-2">
            <div class="row g-2">
                <div class="col-6">
                    <a href="<?= BASE_URL ?>/univAdmin/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline-dark w-100 py-2">
                        <i class="fa-solid fa-pen"></i> Info
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?= BASE_URL ?>/univAdmin/pages/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary w-100 py-2">
                        <i class="fa-solid fa-file-lines"></i> Páginas
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?= BASE_URL ?>/univAdmin/questions/<?= $c['id'] ?>" class="btn btn-sm btn-outline-info w-100 py-2">
                        <i class="fa-solid fa-circle-question"></i> Banco
                    </a>
                </div>
                <div class="col-6">
                    <a href="<?= BASE_URL ?>/univAdmin/alumnos/<?= $c['id'] ?>" class="btn btn-sm btn-outline-success w-100 py-2">
                        <i class="fa-solid fa-user-graduate"></i> Alumnos
                    </a>
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 py-2" onclick="confirmDelete(<?= $c['id'] ?>)">
                        <i class="fa-solid fa-trash"></i> Eliminar Curso
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (empty($courses)): ?>
  <div class="card bg-light border-0 py-5 text-center shadow-sm">
    <i class="fa-solid fa-book-open fa-3x text-muted mb-3"></i>
    <h5>No hay cursos creados todavía.</h5>
    <p class="text-muted small">Haz clic en "Nuevo Curso" para comenzar.</p>
  </div>
<?php endif; ?>

<script>
function confirmDelete(id) {
    if (confirm('¿Estás seguro de eliminar este curso? Se perderán las páginas y preguntas asociadas.')) {
        window.location.href = '<?= BASE_URL ?>/univAdmin/delete/' + id;
    }
}
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
