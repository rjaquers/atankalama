<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <a href="<?= BASE_URL ?>/index.php?route=univAdmin/index" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver a Cursos
  </a>
  <h4 class="m-0"><i class="fa-solid fa-user-graduate me-2"></i>Asignación de Cursos a Alumnos</h4>
  <span></span>
</div>

<!-- Filtro por alumno -->
<div class="card mb-3 shadow-sm">
  <div class="card-body py-2">
    <div class="row g-2 align-items-center">
      <div class="col-md-5">
        <input type="text" id="filtroAlumno" class="form-control form-control-sm"
               placeholder="Buscar alumno por nombre o email..." oninput="filtrar()">
      </div>
      <div class="col-md-4">
        <?php $perfilActivo = htmlspecialchars($_GET['perfil'] ?? ''); ?>
        <select id="filtroPerfil" class="form-select form-select-sm" onchange="filtrar()">
          <option value="">Todos los perfiles</option>
          <?php
            $perfiles = array_unique(array_column($usuarios, 'perfil'));
            sort($perfiles);
            foreach ($perfiles as $p): ?>
            <option value="<?= htmlspecialchars($p) ?>"
              <?= ($p === ($_GET['perfil'] ?? '')) ? 'selected' : '' ?>>
              <?= htmlspecialchars($p) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 text-muted small">
        <span id="conteo"><?= count($usuarios) ?></span> alumno(s) visible(s)
      </div>
    </div>
  </div>
</div>

<!-- Tabla -->
<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="tablaAlumnos">
      <thead class="table-dark">
        <tr>
          <th>Alumno</th>
          <th>Perfil</th>
          <?php foreach ($courses as $c): ?>
            <th class="text-center" style="min-width:110px; font-size:.78rem; line-height:1.2;">
              <?= htmlspecialchars($c['nombre']) ?>
              <div class="badge bg-<?= $c['tipo'] === 'obligatorio_legal' ? 'danger' : ($c['tipo'] === 'obligatorio_area' ? 'warning text-dark' : 'secondary') ?> mt-1 d-block">
                <?= $c['tipo'] === 'obligatorio_legal' ? 'Legal' : ($c['tipo'] === 'obligatorio_area' ? 'Área' : 'Opt.') ?>
              </div>
            </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
        <tr class="fila-alumno"
            data-nombre="<?= strtolower($u['nombre'] . ' ' . $u['apellido'] . ' ' . $u['email']) ?>"
            data-perfil="<?= htmlspecialchars($u['perfil']) ?>">
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
          </td>
          <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($u['perfil']) ?></span></td>

          <?php foreach ($courses as $c): ?>
            <?php $status = $enrollments[$u['id']][$c['id']] ?? null; ?>
            <td class="text-center">
              <?php if ($status === 'en_progreso'): ?>
                <span class="badge bg-primary d-block mb-1">En progreso</span>
                <small class="text-muted">no se puede quitar</small>

              <?php elseif ($status === 'aprobado'): ?>
                <span class="badge bg-success d-block">Aprobado</span>

              <?php elseif ($status === 'reprobado'): ?>
                <span class="badge bg-danger d-block">Reprobado</span>

              <?php elseif ($status === 'asignado'): ?>
                <span class="badge bg-info text-dark d-block mb-1">Asignado</span>
                <form method="POST" action="<?= BASE_URL ?>/index.php?route=univAdmin/desasignar"
                      onsubmit="return confirm('¿Quitar este curso al alumno?')">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="perfil_filtro" value="<?= $perfilActivo ?>">
                  <button class="btn btn-outline-danger btn-sm py-0 px-1" style="font-size:.7rem;">
                    <i class="bi bi-x-lg"></i> Quitar
                  </button>
                </form>

              <?php else: ?>
                <form method="POST" action="<?= BASE_URL ?>/index.php?route=univAdmin/asignar">
                  <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                  <input type="hidden" name="perfil_filtro" value="<?= $perfilActivo ?>">
                  <button class="btn btn-outline-success btn-sm py-0 px-1" style="font-size:.7rem;">
                    <i class="bi bi-plus-lg"></i> Asignar
                  </button>
                </form>

              <?php endif; ?>
            </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function filtrar() {
    const txt    = document.getElementById('filtroAlumno').value.toLowerCase();
    const perfil = document.getElementById('filtroPerfil').value;
    let visible  = 0;
    document.querySelectorAll('.fila-alumno').forEach(function (tr) {
        const okNombre = tr.dataset.nombre.includes(txt);
        const okPerfil = !perfil || tr.dataset.perfil === perfil;
        const show = okNombre && okPerfil;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('conteo').innerText = visible;
}
// Restaurar filtro al volver del redirect
<?php if (!empty($_GET['perfil'])): ?>
document.addEventListener('DOMContentLoaded', filtrar);
<?php endif; ?>
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
