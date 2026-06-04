<?php
$fv         = $tarjeta['fecha_vencimiento']
            ? date('Y-m-d', strtotime($tarjeta['fecha_vencimiento']))
            : '';
$tarjeta_id = (int)$tarjeta['id'];
?>

<div class="modal-header py-2"
     style="border-bottom:3px solid <?= $tarjeta['fondo_color'] ?>;background:<?= $tarjeta['fondo_color'] ?>18;">
  <div>
    <div class="text-muted" style="font-size:.72rem">#<?= $tarjeta['numero'] ?></div>
    <h6 class="modal-title mb-0 fw-bold"><?= htmlspecialchars($tarjeta['titulo']) ?></h6>
    <div class="text-muted" style="font-size:.75rem;margin-top:2px">
      <i class="bi bi-layout-three-columns me-1"></i>
      <?= htmlspecialchars($tarjeta['tablero_nombre']) ?>
      <i class="bi bi-chevron-right mx-1" style="font-size:.6rem"></i>
      <?= htmlspecialchars($tarjeta['lista_nombre']) ?>
    </div>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body p-0">
  <div class="row g-0">

    <!-- ── Columna principal ─────────────────────────────── -->
    <div class="col-md-8 p-3" style="border-right:1px solid #dee2e6;">

      <?php if ($puede_editar): ?>
      <form id="form-tarjeta" data-id="<?= $tarjeta_id ?>">

        <div class="mb-2">
          <label class="modal-label">Título</label>
          <input type="text" class="form-control form-control-sm" name="titulo"
                 value="<?= htmlspecialchars($tarjeta['titulo']) ?>" required>
        </div>

        <div class="mb-2">
          <label class="modal-label">Descripción</label>
          <textarea class="form-control form-control-sm" name="descripcion" rows="3"
                    placeholder="Agrega más detalle..."><?= htmlspecialchars($tarjeta['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
          <label class="modal-label">Fecha de vencimiento</label>
          <input type="text" class="form-control form-control-sm flatpickr-date" name="fecha_vencimiento"
                 value="<?= $fv ?>" placeholder="Sin fecha — clic para asignar">
        </div>

        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" name="completada" id="chk-completada"
                 <?= (isset($tarjeta['completada']) && $tarjeta['completada']) ? 'checked' : '' ?>>
          <label class="form-check-label small fw-bold text-success" for="chk-completada">
            <i class="bi bi-check-circle-fill me-1"></i> Tarea terminada
          </label>
        </div>

      </form>
      <?php else: ?>
        <p class="fw-semibold"><?= htmlspecialchars($tarjeta['titulo']) ?></p>
        <?php if ($tarjeta['descripcion']): ?>
          <p class="text-muted small" style="white-space:pre-wrap"><?= htmlspecialchars($tarjeta['descripcion']) ?></p>
        <?php endif; ?>
        <?php if ($tarjeta['fecha_vencimiento']): ?>
          <p class="small text-muted"><i class="bi bi-clock me-1"></i>Vence: <?= date('d/m/Y', strtotime($tarjeta['fecha_vencimiento'])) ?></p>
        <?php endif; ?>
      <?php endif; ?>

      <!-- ── Checklists ──────────────────────────────────── -->
      <div id="checklists-wrap">

        <?php foreach ($checklists as $cl):
          $total = count($cl['items']);
          $ok    = count(array_filter($cl['items'], fn($i) => $i['completado']));
          $pct   = $total ? round($ok / $total * 100) : 0;
        ?>
        <div class="cl-block" id="cl-<?= $cl['id'] ?>">

          <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="fw-semibold small">
              <i class="bi bi-check2-square me-1"></i><?= htmlspecialchars($cl['titulo']) ?>
            </span>
            <?php if ($puede_editar): ?>
              <button class="btn btn-link btn-sm text-muted p-0 btn-del-cl"
                      data-id="<?= $cl['id'] ?>">Eliminar</button>
            <?php endif; ?>
          </div>

          <?php if ($total > 0): ?>
          <div class="d-flex align-items-center gap-2 mb-2">
            <small class="text-muted" id="cl-pct-<?= $cl['id'] ?>"><?= $pct ?>%</small>
            <div class="flex-grow-1" style="height:6px;background:#e9ecef;border-radius:3px;overflow:hidden">
              <div id="cl-bar-<?= $cl['id'] ?>"
                   style="width:<?= $pct ?>%;height:100%;background:<?= $pct>=100?'#22c55e':'#3b82f6' ?>;border-radius:3px;transition:width .2s">
              </div>
            </div>
          </div>
          <?php endif; ?>

          <ul class="list-unstyled mb-1"
              id="cl-items-<?= $cl['id'] ?>" data-ok="<?= $ok ?>" data-total="<?= $total ?>">
            <?php foreach ($cl['items'] as $item):
              $item_fecha = !empty($item['fecha_vencimiento']) ? date('d/m', strtotime($item['fecha_vencimiento'])) : '';
              $pri_color = [
                'baja' => '#94a3b8',
                'normal' => '#3b82f6',
                'alta' => '#ef4444'
              ][$item['prioridad'] ?? 'normal'] ?? '#3b82f6';
              $resp = !empty($item['responsable_nombre']) ? mb_strtoupper(mb_substr($item['responsable_nombre'],0,1).mb_substr($item['responsable_apellido'],0,1)) : '';
            ?>
            <li class="d-flex align-items-start gap-2 mb-1" id="ci-<?= $item['id'] ?>">
              <?php if ($puede_editar): ?>
                <input type="checkbox" class="form-check-input mt-1 ck-toggle"
                       data-id="<?= $item['id'] ?>" data-cl="<?= $cl['id'] ?>"
                       <?= $item['completado'] ? 'checked' : '' ?>>
              <?php else: ?>
                <input type="checkbox" class="form-check-input mt-1"
                       <?= $item['completado'] ? 'checked' : '' ?> disabled>
              <?php endif; ?>
              
              <div class="flex-grow-1 min-w-0">
                <span class="small <?= $item['completado'] ? 'text-decoration-line-through text-muted' : '' ?>"
                      id="ci-text-<?= $item['id'] ?>">
                  <?= htmlspecialchars($item['texto']) ?>
                </span>
                <div class="d-flex align-items-center gap-2 mt-0">
                  <?php if ($item_fecha): ?>
                    <span class="text-muted" style="font-size:.65rem">
                      <i class="bi bi-clock me-1"></i><?= $item_fecha ?>
                    </span>
                  <?php endif; ?>
                  <span class="badge p-0" style="width:12px;height:4px;background:<?= $pri_color ?>;margin-top:2px" title="Prioridad: <?= $item['prioridad'] ?>"></span>
                  <?php if ($resp): ?>
                    <span class="badge bg-light text-dark border" style="font-size:.6rem;padding:2px 4px" title="Responsable: <?= htmlspecialchars($item['responsable_nombre'].' '.$item['responsable_apellido']) ?>">
                      <?= $resp ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>

              <?php if ($puede_editar): ?>
                <button class="btn btn-link btn-sm text-muted p-0 btn-del-item"
                        data-id="<?= $item['id'] ?>" data-cl="<?= $cl['id'] ?>"
                        title="Eliminar"><i class="bi bi-x"></i></button>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>

          <?php if ($puede_editar): ?>
          <div class="cl-add-wrap">
            <div class="cl-add-form d-none" id="cl-form-<?= $cl['id'] ?>">
              <input type="text" class="form-control form-control-sm mb-1"
                     placeholder="Texto del elemento...">
              <div class="row g-1 mb-1">
                <div class="col-4">
                  <input type="text" class="form-control form-control-sm flatpickr-date" 
                         placeholder="Vencimiento" id="cl-date-<?= $cl['id'] ?>">
                </div>
                <div class="col-4">
                  <select class="form-select form-select-sm" id="cl-pri-<?= $cl['id'] ?>">
                    <option value="baja">Baja</option>
                    <option value="normal" selected>Normal</option>
                    <option value="alta">Alta</option>
                  </select>
                </div>
                <div class="col-4">
                  <select class="form-select form-select-sm" id="cl-resp-<?= $cl['id'] ?>">
                    <option value="">— Responsable —</option>
                    <?php foreach ($miembros_tablero as $u): ?>
                      <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="d-flex gap-1">
                <button class="btn btn-primary btn-sm btn-add-item"
                        data-cl="<?= $cl['id'] ?>">Agregar</button>
                <button class="btn btn-outline-secondary btn-sm btn-cancel-item"
                        data-cl="<?= $cl['id'] ?>"><i class="bi bi-x"></i></button>
              </div>
            </div>
            <button class="btn btn-link btn-sm text-muted p-0 btn-show-item"
                    data-cl="<?= $cl['id'] ?>">
              <i class="bi bi-plus me-1"></i>Agregar elemento
            </button>
          </div>
          <?php endif; ?>

        </div><!-- /cl-block -->
        <?php endforeach; ?>

      </div><!-- /checklists-wrap -->

      <?php if ($puede_editar): ?>
      <div class="mt-2">
        <div id="new-cl-form" class="d-none mb-2">
          <input type="text" class="form-control form-control-sm mb-1" id="new-cl-titulo"
                 placeholder="Nombre del checklist...">
          <div class="d-flex gap-1">
            <button class="btn btn-primary btn-sm" id="btn-do-cl">Agregar</button>
            <button class="btn btn-outline-secondary btn-sm" id="btn-cancel-cl">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>
        <button class="btn btn-outline-secondary btn-sm w-100" id="btn-show-cl">
          <i class="bi bi-plus me-1"></i>Agregar checklist
        </button>
      </div>
      <?php endif; ?>

      <!-- ── Comentarios ────────────────────────────────── -->
      <div class="mt-4 pt-3" style="border-top: 1px solid #eee;">
        <p class="modal-label mb-2"><i class="bi bi-chat-left-text me-1"></i>Comentarios</p>
        
        <div class="mb-3">
          <div class="d-flex gap-2">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:.75rem">
              <?= mb_strtoupper(mb_substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-grow-1">
              <textarea class="form-control form-control-sm" id="new-comment-text" rows="2" placeholder="Escribe un comentario o respuesta..."></textarea>
              <div class="d-flex justify-content-end mt-1">
                <button class="btn btn-primary btn-sm px-3" id="btn-save-comment">Enviar</button>
              </div>
            </div>
          </div>
        </div>

        <div id="comments-thread">
          <?php foreach ($comentarios as $c): 
            $c_ini = mb_strtoupper(mb_substr($c['nombre'],0,1).mb_substr($c['apellido'],0,1));
          ?>
            <div class="d-flex gap-2 mb-3" id="comment-<?= $c['id'] ?>">
              <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;font-size:.75rem">
                <?= $c_ini ?>
              </div>
              <div class="flex-grow-1 bg-light p-2 rounded" style="position:relative">
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="fw-bold small"><?= htmlspecialchars($c['nombre'].' '.$c['apellido']) ?></span>
                  <span class="text-muted" style="font-size:.65rem"><?= date('d/m H:i', strtotime($c['created_at'])) ?></span>
                </div>
                <div class="small text-dark" style="white-space:pre-wrap"><?= htmlspecialchars($c['comentario']) ?></div>
                <?php if ($c['usuario_id'] == $usuario_id): ?>
                  <button class="btn btn-link btn-sm text-muted p-0 btn-del-comment" data-id="<?= $c['id'] ?>" style="position:absolute;top:0;right:-25px" title="Eliminar">
                    <i class="bi bi-x"></i>
                  </button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /col-md-8 -->

    <!-- ── Sidebar ─────────────────────────────────────── -->
    <div class="col-md-4 p-3 bg-light">

      <!-- Etiquetas -->
      <p class="modal-label mb-2"><i class="bi bi-tag me-1"></i>Etiquetas</p>
      <div class="d-flex flex-wrap gap-1 mb-3" id="etiquetas-lista">
        <?php foreach ($etiquetas_tablero as $e):
          $sel = isset($etiquetas_tarjeta[$e['id']]);
        ?>
          <button class="btn-etq <?= $sel ? 'etq-on' : '' ?>"
                  style="background:<?= htmlspecialchars($e['color']) ?>"
                  data-id="<?= $e['id'] ?>"
                  data-color="<?= htmlspecialchars($e['color']) ?>"
                  data-nombre="<?= htmlspecialchars($e['nombre']) ?>"
                  title="<?= htmlspecialchars($e['nombre']) ?>"
                  <?= $puede_editar ? '' : 'disabled' ?>>
            <?= htmlspecialchars($e['nombre']) ?>
            <?php if ($sel): ?><i class="bi bi-check-lg ms-1"></i><?php endif; ?>
          </button>
        <?php endforeach; ?>
        <?php if (empty($etiquetas_tablero)): ?>
          <span class="text-muted small">Sin etiquetas configuradas</span>
        <?php endif; ?>
      </div>

      <!-- Miembros -->
      <p class="modal-label mb-2"><i class="bi bi-people me-1"></i>Miembros</p>
      <div class="d-flex flex-wrap gap-1" id="miembros-lista">
        <?php
          $avatar_colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899','#06b6d4'];
          foreach ($miembros_tablero as $idx => $u):
            $sel   = isset($miembros_tarjeta[$u['id']]);
            $color = $avatar_colors[$idx % count($avatar_colors)];
            $ini   = mb_strtoupper(mb_substr($u['nombre'],0,1).mb_substr($u['apellido'],0,1));
        ?>
          <button class="btn-avatar <?= $sel ? 'avatar-on' : '' ?>"
                  style="background:<?= $color ?>"
                  data-id="<?= $u['id'] ?>"
                  title="<?= htmlspecialchars($u['nombre'].' '.$u['apellido']) ?>"
                  <?= $puede_editar ? '' : 'disabled' ?>>
            <?= $ini ?>
          </button>
        <?php endforeach; ?>
        <?php if (empty($miembros_tablero)): ?>
          <span class="text-muted small">Sin miembros</span>
        <?php endif; ?>
      </div>

      <!-- Adjuntos -->
      <p class="modal-label mt-3 mb-2"><i class="bi bi-paperclip me-1"></i>Adjuntos</p>
      <div id="adjuntos-lista" class="mb-2">
        <?php foreach ($adjuntos as $a):
          $url = BASE_URL . '/' . $a['ruta'];
        ?>
        <div class="adjunto-item d-flex align-items-center gap-2 mb-1" id="adj-<?= $a['id'] ?>">
          <?php if ($a['tipo'] === 'imagen'): ?>
            <a href="<?= htmlspecialchars($url) ?>" target="_blank">
              <img src="<?= htmlspecialchars($url) ?>" alt="" class="adj-thumb">
            </a>
          <?php else: ?>
            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="text-danger">
              <i class="bi bi-file-earmark-pdf fs-4"></i>
            </a>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div class="small text-truncate" title="<?= htmlspecialchars($a['nombre_original']) ?>">
              <?= htmlspecialchars($a['nombre_original']) ?>
            </div>
            <div class="text-muted" style="font-size:.68rem">
              <?= htmlspecialchars($a['subido_por_nombre']) ?>
            </div>
          </div>
          <?php if ($puede_editar): ?>
            <button class="btn btn-link btn-sm text-muted p-0 btn-del-adj"
                    data-id="<?= $a['id'] ?>" title="Eliminar">
              <i class="bi bi-x"></i>
            </button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($adjuntos)): ?>
          <p class="text-muted small mb-0">Sin adjuntos</p>
        <?php endif; ?>
      </div>

      <?php if ($puede_editar): ?>
      <form id="adjunto-form" class="mt-1">
        <input type="hidden" name="tarjeta_id" value="<?= $tarjeta_id ?>">
        <div class="d-flex gap-1">
          <input type="file" class="form-control form-control-sm" name="archivo"
                 accept="image/*,application/pdf" required>
          <button type="submit" class="btn btn-outline-secondary btn-sm flex-shrink-0">
            <i class="bi bi-upload"></i>
          </button>
        </div>
      </form>
      <?php endif; ?>

      <!-- Referencias -->
      <p class="modal-label mt-3 mb-2"><i class="bi bi-arrow-left-right me-1"></i>Compartir en área</p>
      <div id="refs-lista" class="mb-2">
        <?php foreach ($referencias as $r): ?>
        <div class="d-flex align-items-center gap-2 mb-1" id="ref-<?= $r['id'] ?>">
          <span class="badge" style="background:<?= htmlspecialchars($r['tablero_destino_nombre'][0] ?? '#999') ?>22;color:#333;font-size:.68rem">
            <?= htmlspecialchars($r['tablero_destino_nombre']) ?>
            <span class="text-muted"> › </span><?= htmlspecialchars($r['lista_destino_nombre']) ?>
          </span>
          <?php if ($puede_editar): ?>
            <button class="btn btn-link btn-sm text-muted p-0 btn-del-ref" data-id="<?= $r['id'] ?>" title="Quitar">
              <i class="bi bi-x"></i>
            </button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (empty($referencias)): ?>
          <p class="text-muted small mb-0">No compartida</p>
        <?php endif; ?>
      </div>

      <?php if ($puede_editar && !empty($tableros_otros)): ?>
      <div id="ref-form-wrap">
        <div id="ref-form" class="d-none mb-1">
          <select class="form-select form-select-sm mb-1" id="ref-tablero">
            <option value="">— Tablero destino —</option>
            <?php foreach ($tableros_otros as $tb): ?>
              <option value="<?= $tb['id'] ?>"><?= htmlspecialchars($tb['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
          <select class="form-select form-select-sm mb-1 d-none" id="ref-lista">
            <option value="">— Lista —</option>
          </select>
          <div class="d-flex gap-1">
            <button class="btn btn-primary btn-sm" id="btn-do-ref">Compartir</button>
            <button class="btn btn-outline-secondary btn-sm" id="btn-cancel-ref">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>
        <button class="btn btn-outline-secondary btn-sm w-100" id="btn-show-ref">
          <i class="bi bi-plus me-1"></i>Compartir en otro tablero
        </button>
      </div>
      <?php endif; ?>

    </div><!-- /sidebar -->

  </div><!-- /row -->
</div><!-- /modal-body -->

<div class="modal-footer d-flex justify-content-between py-2">
  <?php if ($puede_editar): ?>
    <button class="btn btn-outline-danger btn-sm btn-archivar" data-id="<?= $tarjeta_id ?>">
      <i class="bi bi-archive me-1"></i> Archivar
    </button>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      <button class="btn btn-primary btn-sm btn-guardar" data-id="<?= $tarjeta_id ?>">
        <i class="bi bi-check-lg me-1"></i> Guardar
      </button>
    </div>
  <?php else: ?>
    <div></div>
    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
  <?php endif; ?>
</div>
