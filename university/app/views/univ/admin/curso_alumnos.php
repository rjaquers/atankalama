<?php require VIEW_PATH . "/layouts/header.php"; ?>

<?php
$perfilActivo  = htmlspecialchars($_GET['perfil'] ?? '');
$tipoBadge     = ['obligatorio_legal' => 'danger', 'obligatorio_area' => 'warning text-dark', 'opcional' => 'secondary'];
$tipoLabel     = ['obligatorio_legal' => 'Legal', 'obligatorio_area' => 'Área', 'opcional' => 'Opcional'];
$badge         = $tipoBadge[$course['tipo']] ?? 'secondary';
$redirectBack  = '/univAdmin/alumnos/' . $course['id'] . ($perfilActivo !== '' ? '?perfil=' . urlencode($_GET['perfil'] ?? '') : '');

// Contadores
$contAsignado = $contProgreso = $contAprobado = $contReprobado = 0;
foreach ($enrollments as $s) {
    if ($s === 'asignado')    $contAsignado++;
    elseif ($s === 'en_progreso') $contProgreso++;
    elseif ($s === 'aprobado')    $contAprobado++;
    elseif ($s === 'reprobado')   $contReprobado++;
}
?>

<style>
.sortable { cursor: pointer; position: relative; }
.sortable:after {
  content: '\f0dc';
  font-family: 'Font Awesome 6 Free';
  font-weight: 900;
  position: absolute;
  right: 10px;
  color: #ccc;
  font-size: 0.8em;
}
.sortable.asc:after { content: '\f0de'; color: #fff; }
.sortable.desc:after { content: '\f0dd'; color: #fff; }
</style>

<!-- Encabezado -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <a href="<?= BASE_URL ?>/index.php?route=univAdmin/index" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver a Cursos
  </a>
  <div class="text-center">
    <h5 class="m-0 fw-bold"><?= htmlspecialchars($course['nombre']) ?></h5>
    <span class="badge bg-<?= $badge ?> me-1"><?= $tipoLabel[$course['tipo']] ?? $course['tipo'] ?></span>
    <span class="badge bg-light text-dark border"><?= (int)$course['creditos'] ?> crédito(s)</span>
    <?php if (!$course['activo']): ?>
      <span class="badge bg-secondary ms-1">Inactivo</span>
    <?php endif; ?>
  </div>
  <a href="<?= BASE_URL ?>/index.php?route=univAdmin/asignaciones" class="btn btn-sm btn-outline-primary">
    <i class="fa-solid fa-table"></i> Vista por alumno
  </a>
</div>

<!-- Estadísticas -->
<div class="row g-2 mb-3">
  <div class="col-6 col-md-3">
    <div class="card border-info text-center py-2">
      <div class="fw-bold fs-5 text-info"><?= $contAsignado ?></div>
      <div class="text-muted small">Asignado(s)</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-primary text-center py-2">
      <div class="fw-bold fs-5 text-primary"><?= $contProgreso ?></div>
      <div class="text-muted small">En progreso</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-success text-center py-2">
      <div class="fw-bold fs-5 text-success"><?= $contAprobado ?></div>
      <div class="text-muted small">Aprobado(s)</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-danger text-center py-2">
      <div class="fw-bold fs-5 text-danger"><?= $contReprobado ?></div>
      <div class="text-muted small">Reprobado(s)</div>
    </div>
  </div>
</div>

<!-- Filtros -->
<div class="card mb-3 shadow-sm">
  <div class="card-body py-2">
    <div class="row g-2 align-items-center">
      <div class="col-md-5">
        <input type="text" id="filtroAlumno" class="form-control form-control-sm"
               placeholder="Buscar por nombre o email..." oninput="filtrar()">
      </div>
      <div class="col-md-4">
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
          <th class="sortable" onclick="sortTable(0)">Alumno</th>
          <th class="sortable" onclick="sortTable(1)">Perfil</th>
          <th class="sortable text-center" onclick="sortTable(2)">Estado</th>
          <th class="sortable text-center" onclick="sortTable(3)">Puntaje</th>
          <th class="text-center" style="width:120px;">Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u):
          $status = $enrollments[$u['id']] ?? 'ninguno';
          $maxScore = $scores[$u['id']] ?? null;
        ?>
        <tr class="fila-alumno"
            data-nombre="<?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>"
            data-search="<?= strtolower($u['nombre'] . ' ' . $u['apellido'] . ' ' . $u['email']) ?>"
            data-perfil="<?= htmlspecialchars($u['perfil']) ?>"
            data-status="<?= $status ?>"
            data-score="<?= (int)$maxScore ?>">

          <td>
            <div class="fw-semibold"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
          </td>

          <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($u['perfil']) ?></span></td>

          <td class="text-center">
            <?php if ($status === 'asignado'): ?>
              <span class="badge bg-info text-dark">Asignado</span>
            <?php elseif ($status === 'en_progreso'): ?>
              <span class="badge bg-primary">En progreso</span>
            <?php elseif ($status === 'aprobado'): ?>
              <span class="badge bg-success">Aprobado</span>
            <?php elseif ($status === 'reprobado'): ?>
              <span class="badge bg-danger">Reprobado</span>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>

            <?php 
              // Buscar el enrollment_id para el link de historia
              $enrollId = 0;
              $db = (new Database())->connect();
              $stmtE = $db->prepare("SELECT id FROM univ_enrollments WHERE user_id = ? AND course_id = ? AND status IN ('asignado','en_progreso','aprobado','reprobado') LIMIT 1");
              $stmtE->bind_param("ii", $u['id'], $course['id']);
              $stmtE->execute();
              $enrollId = $stmtE->get_result()->fetch_assoc()['id'] ?? 0;
            ?>

            <?php if ($enrollId > 0 && in_array($status, ['en_progreso', 'aprobado', 'reprobado'])): ?>
              <div class="mt-1">
                <a href="<?= BASE_URL ?>/univAdmin/evaluateHistory/<?= $enrollId ?>" class="text-decoration-none small" title="Ver historial de intentos">
                  <i class="fa-solid fa-clock-rotate-left"></i> Historial
                </a>
              </div>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($maxScore !== null): ?>
              <span class="fw-bold fs-5 <?= $maxScore >= $course['min_score_to_approve'] ? 'text-success' : 'text-danger' ?>">
                <?= $maxScore ?>%
              </span>
            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($status === 'en_progreso'): ?>
              <span class="text-muted small">En curso</span>

            <?php elseif ($status === 'asignado'): ?>
              <form method="POST" action="<?= BASE_URL ?>/index.php?route=univAdmin/desasignar"
                    onsubmit="return confirm('¿Quitar este curso al alumno?')">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <input type="hidden" name="redirect_back" value="<?= htmlspecialchars($redirectBack) ?>">
                <button class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size:.8rem;">
                  <i class="bi bi-x-lg"></i> Quitar
                </button>
              </form>

            <?php elseif ($status === null): ?>
              <form method="POST" action="<?= BASE_URL ?>/index.php?route=univAdmin/asignar">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                <input type="hidden" name="redirect_back" value="<?= htmlspecialchars($redirectBack) ?>">
                <button class="btn btn-outline-success btn-sm py-0 px-2" style="font-size:.8rem;">
                  <i class="bi bi-plus-lg"></i> Asignar
                </button>
              </form>

            <?php else: ?>
              <span class="text-muted small">—</span>
            <?php endif; ?>
          </td>
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

    // Actualizar redirect_back con el perfil actual para que el redirect lo preserve
    const backs = document.querySelectorAll('input[name="redirect_back"]');
    const base  = '/univAdmin/alumnos/<?= $course['id'] ?>';
    backs.forEach(function (el) {
        el.value = perfil !== '' ? base + '?perfil=' + encodeURIComponent(perfil) : base;
    });

    let visible = 0;
    document.querySelectorAll('.fila-alumno').forEach(function (tr) {
        const okNombre = tr.dataset.search.includes(txt);
        const okPerfil = !perfil || tr.dataset.perfil === perfil;
        const show = okNombre && okPerfil;
        tr.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('conteo').innerText = visible;
}

function sortTable(n) {
    const table = document.getElementById("tablaAlumnos");
    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);
    const th = table.querySelectorAll("th")[n];
    const isAsc = th.classList.contains("asc");
    
    // Quitar clases de otros headers
    table.querySelectorAll("th").forEach(h => h.classList.remove("asc", "desc"));
    
    // Determinar dirección
    const direction = isAsc ? -1 : 1;
    th.classList.add(isAsc ? "desc" : "asc");

    rows.sort((a, b) => {
        let valA, valB;
        if (n === 0) { // Alumno
            valA = a.dataset.nombre.toLowerCase();
            valB = b.dataset.nombre.toLowerCase();
        } else if (n === 1) { // Perfil
            valA = a.dataset.perfil.toLowerCase();
            valB = b.dataset.perfil.toLowerCase();
        } else if (n === 2) { // Estado
            valA = a.dataset.status;
            valB = b.dataset.status;
        } else if (n === 3) { // Puntaje
            valA = parseInt(a.dataset.score) || 0;
            valB = parseInt(b.dataset.score) || 0;
        }
        
        if (typeof valA === 'string') {
            if (valA < valB) return -1 * direction;
            if (valA > valB) return 1 * direction;
            return 0;
        } else {
            return (valA - valB) * direction;
        }
    });

    // Reinsertar filas ordenadas
    rows.forEach(row => tbody.appendChild(row));
}
<?php if (!empty($_GET['perfil'])): ?>
document.addEventListener('DOMContentLoaded', filtrar);
<?php endif; ?>
</script>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
