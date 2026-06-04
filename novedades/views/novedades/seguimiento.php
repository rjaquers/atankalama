<!--
  ===================================================
  = Proyecto: Hotel Atankalama - Sistema de Cocina  =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com                    =
  = Fecha: <?= date('Y') ?>                         =
  ===================================================

  Resumen:
  Vista de seguimiento de una novedad marcada como tarea.
  Permite revisar el detalle, el estado y agregar comentarios
  mientras la tarea esté pendiente.
-->

<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-4">

    <a href="index.php?route=novedades/list" class="btn btn-sm btn-outline-secondary mb-3">
        ← Volver al historial
    </a>

    <div class="card shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center"
            style="background:#6f42c1;color:white">

            <div>
                <strong><?= htmlspecialchars($novedad['recepcionista']) ?></strong>
                <span class="ms-2 small">
                    📅 <?= date('d-m-Y H:i', strtotime($novedad['fecha_registro'])) ?>
                </span>
            </div>

            <?php if ((int) $novedad['seguimiento_estado'] === 1): ?>
                <span class="badge bg-warning text-dark">
                    ⏳ Pendiente
                </span>
            <?php else: ?>
                <span class="badge bg-success">
                    ✔ Realizada
                </span>
            <?php endif; ?>

        </div>

        <div class="card-body">

            <div class="mb-3">
                <span class="badge bg-info"><i class="bi bi-geo-alt"></i>
                    <?= htmlspecialchars($novedad['area']) ?></span>
                <span class="badge" style="background-color: #673ab7; color: white;"><i class="bi bi-building"></i>
                    Hotel:
                    <?= htmlspecialchars($novedad['hotel']) ?></span>
                <?php if (!empty($novedad['tipo_seguimiento'])): ?>
                    <span class="badge bg-secondary ms-1"><i class="bi bi-tools"></i> Tipo:
                        <?= ucfirst(htmlspecialchars($novedad['tipo_seguimiento'])) ?></span>
                <?php endif; ?>
                <?php if (!empty($novedad['flexkeeping_id'])): ?>
                    <span class="badge ms-1" style="background-color: #3f51b5; color: white; border: 1px solid #303f9f;">
                        <i class="bi bi-hash"></i> Nro Id Flexkeeping = <?= htmlspecialchars($novedad['flexkeeping_id']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <h5>Detalle de la solicitud</h5>
            <p class="border rounded p-3 bg-light">
                <?= nl2br(htmlspecialchars($novedad['detalle'])) ?>
            </p>

            <hr>

            <h5>Seguimiento</h5>

            <?php if (empty($comentarios)): ?>
                <div class="alert alert-info">
                    Aún no hay comentarios de seguimiento.
                </div>
            <?php else: ?>
                <ul class="list-group mb-3">
                    <?php foreach ($comentarios as $c): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($c['autor']) ?></strong>
                                <small class="text-muted">
                                    <?= date('d-m-Y H:i', strtotime($c['creado_at'])) ?>
                                </small>
                            </div>
                            <div class="mt-1">
                                <?= nl2br(htmlspecialchars($c['comentario'])) ?>
                            </div>
                        </li>

                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <div class='mb-2'>

                <?php if (!empty($archivos)): ?>
                    <hr>
                    <h6>Evidencia adjunta</h6>

                    <div class="mt-2">
                        <?php foreach ($archivos as $a): ?>
                            <?php
                            $ruta = '../uploads/' . date('Y_m_d', strtotime($novedad['fecha_registro'])) . '/novedad_' . $novedad['id'] . '/' . $a['archivo'];
                            ?>

                            <?php if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $a['archivo'])): ?>
                                <a href="<?= $ruta ?>" target="_blank">
                                    <img src="<?= $ruta ?>" class="img-thumbnail me-2 mb-2" style="width:120px">
                                </a>
                            <?php else: ?>
                                <a href="<?= $ruta ?>" target="_blank" class="d-block">
                                    📎 <?= htmlspecialchars($a['archivo']) ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>


            </div>

            <?php if ((int) $novedad['seguimiento_estado'] === 1): ?>
                <hr>

                <form method='POST' action='index.php?route=novedades/comentario/agregar' enctype='multipart/form-data'>
                    <input type="hidden" name="novedad_id" value="<?= (int) $novedad['id'] ?>">



                    <div class='mb-3'>
                        <label class='form-label'>Responsable / Recepcionista</label>
                        <select name='autor' class='form-select' required>
                            <?php foreach ($recepcionistas as $r): ?>
                                <option value="<?= htmlspecialchars($r['nombre']) ?>">
                                    <?= htmlspecialchars($r['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comentario de seguimiento</label>
                        <textarea name="comentario" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class='mb-3'>
                        <label class='form-label'>
                            Evidencia (foto o archivo)
                        </label>
                        <input type='file' name='archivos[]' class='form-control' multiple accept='image/*,.pdf,.doc,.docx'>
                        <small class='text-muted'>
                            Puedes adjuntar imágenes o documentos como respaldo.
                        </small>
                    </div>


                    <button type="submit" class="btn btn-primary">
                        ➕ Agregar comentario
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-success mt-3">
                    Esta tarea ya fue marcada como realizada y no admite nuevos comentarios.
                </div>
            <?php endif; ?>


            <?php if ((int) $novedad['seguimiento_estado'] === 1): ?>
                <hr>

                <form method="POST" action="index.php?route=novedades/seguimiento/cerrar"
                    onsubmit="return confirm('¿Confirmas que esta tarea fue realizada y deseas cerrarla?');">

                    <input type="hidden" name="novedad_id" value="<?= (int) $novedad['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label">Responsable que cierra</label>
                        <select name="autor" class="form-select" required>
                            <?php foreach ($recepcionistas as $r): ?>
                                <option value="<?= htmlspecialchars($r['nombre']) ?>">
                                    <?= htmlspecialchars($r['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comentario final de cierre</label>
                        <textarea name="comentario" class="form-control" rows="3" required
                            placeholder="Describe brevemente cómo se resolvió la solicitud"></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        ✔ Marcar tarea como realizada
                    </button>
                </form>
            <?php endif; ?>

        </div>


    </div>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>