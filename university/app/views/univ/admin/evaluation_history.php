<?php require VIEW_PATH . "/layouts/header.php"; ?>

<div class="mb-3">
  <a href="<?= BASE_URL ?>/univAdmin/alumnos/<?= $enroll['course_id'] ?>" class="btn btn-sm btn-outline-secondary">
    <i class="fa-solid fa-arrow-left"></i> Volver a Alumnos
  </a>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <h4 class="mb-1">Historial de Intentos</h4>
        <p class="text-muted mb-0">
            Alumno: <strong><?= htmlspecialchars($enroll['nombre'] . ' ' . $enroll['apellido']) ?></strong><br>
            Curso: <strong><?= htmlspecialchars($enroll['course_name']) ?></strong>
        </p>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Intento #</th>
                    <th>Fecha</th>
                    <th class="text-center">Resultado</th>
                    <th class="text-center">Puntaje</th>
                    <th class="text-center">Correctas</th>
                    <th class="text-center">Duración</th>
                    <th class="text-center">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $h): ?>
                <tr>
                    <td><strong><?= $h['numero_intento'] ?></strong></td>
                    <td><?= date('d/m/Y H:i', strtotime($h['fecha_fin'])) ?></td>
                    <td class="text-center">
                        <?php if ($h['aprobado']): ?>
                            <span class="badge bg-success">Aprobado</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Reprobado</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold"><?= $h['score'] ?>%</td>
                    <td class="text-center"><?= $h['preguntas_correctas'] ?> / <?= $h['preguntas_totales'] ?></td>
                    <td class="text-center text-muted small">
                        <?php 
                            $seg = $h['tiempo_total_segundos'];
                            echo floor($seg / 60) . 'm ' . ($seg % 60) . 's';
                        ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= BASE_URL ?>/univAdmin/evaluateDetail/<?= $h['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fa-solid fa-magnifying-glass"></i> Ver Detalle
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($history)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">No hay intentos registrados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>
