<?php require VIEW_PATH . "/layouts/header.php"; ?>

<!-- Mensajes flash -->
<?php if(!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<!-- Page Header -->
<div class="page-header">
  <h3>
    <i class="fa-solid fa-building"></i> <?= htmlspecialchars($company['business_name']) ?>
    <?php if($company['type'] === 'cliente'): ?>
      <span class="badge bg-success ms-2">Cliente</span>
    <?php else: ?>
      <span class="badge bg-info ms-2">Proveedor</span>
    <?php endif; ?>
  </h3>
  <div class="d-flex gap-2">
    <?php if(AuthService::hasPermission('companies_edit')): ?>
    <a href="<?= BASE_URL ?>/companies/edit/<?= $company['id'] ?>" class="btn btn-warning">
      <i class="fa-solid fa-pen"></i> Editar
    </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/companies" class="btn btn-outline-secondary">
      <i class="fa-solid fa-arrow-left"></i> Volver
    </a>
  </div>
</div>

<div class="row g-4 fade-in">
  <!-- Datos de la empresa -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-info-circle text-primary"></i> Información General</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width: 40%">RUT:</td>
            <td class="fw-bold"><?= htmlspecialchars($company['rut'] ?: 'No registrado') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Razón Social:</td>
            <td class="fw-bold"><?= htmlspecialchars($company['business_name']) ?></td>
          </tr>
          <tr>
            <td class="text-muted">Nombre Fantasía:</td>
            <td><?= htmlspecialchars($company['trade_name'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Dirección:</td>
            <td><?= htmlspecialchars($company['address'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Ciudad:</td>
            <td><?= htmlspecialchars($company['city'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Registrada:</td>
            <td><small class="text-muted"><?= date('d/m/Y H:i', strtotime($company['created_at'])) ?></small></td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <!-- Contacto -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fa-solid fa-user text-primary"></i> Contacto Principal</h6>
      </div>
      <div class="card-body">
        <table class="table table-borderless mb-0">
          <tr>
            <td class="text-muted" style="width: 40%">Nombre:</td>
            <td class="fw-bold"><?= htmlspecialchars($company['contact_name'] ?: '-') ?></td>
          </tr>
          <tr>
            <td class="text-muted">Email:</td>
            <td>
              <?php if(!empty($company['contact_email'])): ?>
                <a href="mailto:<?= htmlspecialchars($company['contact_email']) ?>">
                  <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($company['contact_email']) ?>
                </a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <td class="text-muted">Teléfono:</td>
            <td>
              <?php if(!empty($company['contact_phone'])): ?>
                <a href="tel:<?= htmlspecialchars($company['contact_phone']) ?>">
                  <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($company['contact_phone']) ?>
                </a>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        </table>

        <?php if(!empty($company['notes'])): ?>
        <hr>
        <h6 class="text-muted"><i class="fa-solid fa-sticky-note"></i> Notas</h6>
        <p class="mb-0"><?= nl2br(htmlspecialchars($company['notes'])) ?></p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Contratos de esta empresa -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0"><i class="fa-solid fa-file-contract text-primary"></i> Contratos (<?= count($contracts) ?>)</h6>
    <?php if(AuthService::hasPermission('contracts_create')): ?>
    <a href="<?= BASE_URL ?>/contracts/create?company_id=<?= $company['id'] ?>" class="btn btn-sm btn-atk">
      <i class="fa-solid fa-plus"></i> Nuevo Contrato
    </a>
    <?php endif; ?>
  </div>
  <div class="card-body">
    <?php if(empty($contracts)): ?>
      <div class="text-center text-muted py-4">
        <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-25"></i>
        <p>No hay contratos registrados para esta empresa</p>
      </div>
    <?php else: ?>
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Código</th>
          <th>Tipo</th>
          <th>Inicio</th>
          <th>Término</th>
          <th class="text-end">Saldo Pendiente</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($contracts as $c): ?>
        <tr>
          <td><a href="<?= BASE_URL ?>/contracts/show/<?= $c['id'] ?>" class="fw-bold text-decoration-none"><?= htmlspecialchars($c['code']) ?></a></td>
          <td><?= ucfirst(htmlspecialchars($c['contract_type'])) ?></td>
          <td><?= date('d/m/Y', strtotime($c['start_date'])) ?></td>
          <td><?= $c['end_date'] ? date('d/m/Y', strtotime($c['end_date'])) : 'Indefinido' ?></td>
          <td class="text-end fw-bold text-danger">$<?= number_format((float)$c['saldo'], 0, ',', '.') ?></td>
          <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_', ' ', $c['status'])) ?></span></td>
          <td>
            <a href="<?= BASE_URL ?>/contracts/show/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver">
              <i class="fa-solid fa-eye"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>


<!-- Servicios de Alimentación -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h6 class="mb-0">
        <i class="fa-solid fa-utensils text-primary"></i>
        Servicios de Alimentación
        <?php if($resumenServicios['total'] > 0): ?>
          <span class="badge bg-secondary ms-1"><?= (int)$resumenServicios['total'] ?></span>
        <?php endif; ?>
      </h6>
    </div>
    <div class="d-flex gap-2 flex-wrap small">
      <span class="badge bg-success"><i class="fa-solid fa-check"></i> Cobrado: <?= (int)$resumenServicios['cobrado'] ?></span>
      <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock"></i> Pendiente: <?= (int)$resumenServicios['pendiente'] ?></span>
      <span class="badge bg-info text-dark"><i class="fa-solid fa-users"></i> Personas: <?= (int)$resumenServicios['total_personas'] ?></span>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card-body border-bottom pb-3">
    <form method="GET" action="" class="row g-2 align-items-end">
      <input type="hidden" name="url" value="companies/show/<?= (int)$company['id'] ?>">

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Tipo</label>
        <select name="tipo_servicio" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="desayuno"          <?= ($filtrosServicio['tipo_servicio'] === 'desayuno')          ? 'selected' : '' ?>>Desayuno</option>
          <option value="cena"              <?= ($filtrosServicio['tipo_servicio'] === 'cena')              ? 'selected' : '' ?>>Cena</option>
          <option value="colacion"          <?= ($filtrosServicio['tipo_servicio'] === 'colacion')          ? 'selected' : '' ?>>Colación</option>
          <option value="colacion_especial" <?= ($filtrosServicio['tipo_servicio'] === 'colacion_especial') ? 'selected' : '' ?>>Col. Especial</option>
        </select>
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Estado cobro</label>
        <select name="cobrado" class="form-select form-select-sm">
          <option value="">Todos</option>
          <option value="0" <?= ($filtrosServicio['cobrado'] === '0') ? 'selected' : '' ?>>Pendiente</option>
          <option value="1" <?= ($filtrosServicio['cobrado'] === '1') ? 'selected' : '' ?>>Cobrado</option>
        </select>
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Desde</label>
        <input type="date" name="fecha_desde" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filtrosServicio['fecha_desde']) ?>">
      </div>

      <div class="col-sm-6 col-md-2">
        <label class="form-label small text-muted mb-1">Hasta</label>
        <input type="date" name="fecha_hasta" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filtrosServicio['fecha_hasta']) ?>">
      </div>

      <div class="col-sm-6 col-md-2 d-flex align-items-end gap-1">
        <div class="form-check mb-0 ms-1">
          <input class="form-check-input" type="checkbox" name="sin_contrato" id="sinContrato" value="1"
                 <?= !empty($filtrosServicio['sin_contrato']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="sinContrato">Sin contrato</label>
        </div>
      </div>

      <div class="col-sm-6 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-fill">
          <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        <?php
          $queryParams = $_GET;
          unset($queryParams['url']);
          $exportUrl = BASE_URL . "/companies/exportAlimentacion/" . (int)$company['id'] . "?" . http_build_query($queryParams);
        ?>
        <a href="<?= $exportUrl ?>" class="btn btn-sm btn-success" title="Exportar detalle CSV">
          <i class="fa-solid fa-file-csv"></i>
        </a>
        <button type="button" class="btn btn-sm btn-success"
                title="Exportar resumen por hotel"
                data-bs-toggle="modal" data-bs-target="#modalResumenHoteles">
          <i class="fa-solid fa-file-excel"></i> Resumen
        </button>
        <a href="?url=companies/show/<?= (int)$company['id'] ?>" class="btn btn-sm btn-outline-secondary">
          <i class="fa-solid fa-xmark"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Tabla de servicios -->
  <div class="card-body p-0">
    <?php if(empty($serviciosAlimentacion)): ?>
      <div class="text-center text-muted py-4">
        <i class="fa-solid fa-utensils fa-3x mb-3 opacity-25"></i>
        <p>No hay servicios de alimentación registrados para esta empresa</p>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0" id="tablaServicios">
        <thead class="table-light">
          <tr>
            <th title="ID del servicio — clic para ordenar">ID</th>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Hotel</th>
            <th class="text-center">Pers.</th>
            <th>Hora</th>
            <th>Contacto</th>
            <th>Observaciones</th>
            <th class="text-center">Contrato</th>
            <th class="text-center">Cobrado</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($serviciosAlimentacion as $srv): ?>
          <tr id="fila-<?= (int)$srv['id'] ?>">
            <td class="text-nowrap" data-order="<?= (int)$srv['id'] ?>">
              <span class="text-muted small"><?= (int)$srv['id'] ?></span>
              <a href="https://www.atankalama.com/cocina/public/index.php?page=voucher/clientes/<?= (int)$srv['id'] ?>"
                 target="_blank" rel="noopener"
                 class="btn btn-sm btn-outline-info ms-1 py-0 px-1" title="Ver voucher en cocina">
                <i class="fa-solid fa-eye"></i>
              </a>
            </td>
            <td class="text-nowrap" data-order="<?= htmlspecialchars($srv['fecha']) ?>"><?= date('d/m/Y', strtotime($srv['fecha'])) ?></td>
            <td>
              <?php
                $tipoBadge = [
                    'desayuno'          => 'bg-info text-dark',
                    'cena'              => 'bg-primary',
                    'colacion'          => 'bg-warning text-dark',
                    'colacion_especial' => 'bg-danger',
                ];
                $tipoLabel = [
                    'desayuno'          => 'Desayuno',
                    'cena'              => 'Cena',
                    'colacion'          => 'Colación',
                    'colacion_especial' => 'Col. Especial',
                ];
                $badgeClass = $tipoBadge[$srv['tipo_servicio']] ?? 'bg-secondary';
                $tipoTexto  = $tipoLabel[$srv['tipo_servicio']]  ?? $srv['tipo_servicio'];
              ?>
              <span class="badge <?= $badgeClass ?>"><?= $tipoTexto ?></span>
            </td>
            <td class="small"><?= htmlspecialchars($srv['nombre_hotel']) ?></td>
            <td class="text-center fw-bold"><?= (int)$srv['cantidad_personas'] ?></td>
            <td class="text-nowrap small"><?= $srv['hora_servicio'] ? substr($srv['hora_servicio'], 0, 5) : '-' ?></td>
            <td class="small"><?= htmlspecialchars($srv['nombre_contacto'] ?? '-') ?></td>
            <td class="small text-muted" style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                title="<?= htmlspecialchars($srv['observaciones'] ?? '') ?>">
              <?= htmlspecialchars($srv['observaciones'] ?? '-') ?>
            </td>
            <td class="text-center">
              <?php if($srv['contract_id']): ?>
                <span class="badge bg-success" title="Contrato #<?= (int)$srv['contract_id'] ?>">
                  <i class="fa-solid fa-file-contract"></i>
                </span>
              <?php else: ?>
                <span class="badge bg-light text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="text-center">
              <button type="button"
                      class="btn btn-sm btn-cobrado <?= $srv['cobrado'] ? 'btn-success' : 'btn-outline-secondary' ?>"
                      data-id="<?= (int)$srv['id'] ?>"
                      data-cobrado="<?= (int)$srv['cobrado'] ?>"
                      title="<?= $srv['cobrado'] ? 'Cobrado el ' . ($srv['cobrado_at'] ? date('d/m/Y', strtotime($srv['cobrado_at'])) : '?') . ' — clic para revertir' : 'Marcar como cobrado' ?>">
                <i class="fa-solid <?= $srv['cobrado'] ? 'fa-check-circle' : 'fa-circle' ?>"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Desayunos Masivos -->
<div class="card mt-4 fade-in">
  <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h6 class="mb-0">
        <i class="fa-solid fa-table text-warning"></i>
        Desayunos Masivos
        <?php if((int)$resumenMasivos['total_registros'] > 0): ?>
          <span class="badge bg-secondary ms-1"><?= (int)$resumenMasivos['total_registros'] ?></span>
        <?php endif; ?>
      </h6>
    </div>
    <div class="d-flex gap-2 flex-wrap small">
      <span class="badge bg-primary"><i class="fa-solid fa-building"></i> Atankalama: <?= (int)$resumenMasivos['atan'] ?> PAX</span>
      <span class="badge bg-info text-dark"><i class="fa-solid fa-building"></i> Inn: <?= (int)$resumenMasivos['inn'] ?> PAX</span>
      <span class="badge bg-dark"><i class="fa-solid fa-users"></i> Total: <?= (int)$resumenMasivos['total_pax'] ?> PAX</span>
    </div>
  </div>

  <!-- Filtro de fechas -->
  <div class="card-body border-bottom pb-3">
    <form method="GET" action="" class="row g-2 align-items-end">
      <input type="hidden" name="url" value="companies/show/<?= (int)$company['id'] ?>">
      <?php foreach (['tipo_servicio','cobrado','sin_contrato'] as $k): ?>
        <?php if(!empty($filtrosServicio[$k])): ?>
          <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($filtrosServicio[$k]) ?>">
        <?php endif; ?>
      <?php endforeach; ?>

      <div class="col-sm-6 col-md-3">
        <label class="form-label small text-muted mb-1">Desde</label>
        <input type="date" name="fecha_desde" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filtrosMasivo['fecha_desde']) ?>">
      </div>
      <div class="col-sm-6 col-md-3">
        <label class="form-label small text-muted mb-1">Hasta</label>
        <input type="date" name="fecha_hasta" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filtrosMasivo['fecha_hasta']) ?>">
      </div>
      <div class="col-sm-6 col-md-2 d-flex gap-1">
        <button type="submit" class="btn btn-sm btn-primary flex-fill">
          <i class="fa-solid fa-filter"></i> Filtrar
        </button>
        <a href="?url=companies/show/<?= (int)$company['id'] ?>" class="btn btn-sm btn-outline-secondary">
          <i class="fa-solid fa-xmark"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Tabla de masivos -->
  <div class="card-body p-0">
    <?php if(empty($desayunosMasivos)): ?>
      <div class="text-center text-muted py-4">
        <i class="fa-solid fa-table fa-3x mb-3 opacity-25"></i>
        <p>No hay desayunos masivos registrados para esta empresa<?= ($filtrosMasivo['fecha_desde'] || $filtrosMasivo['fecha_hasta']) ? ' en el rango seleccionado' : '' ?>.</p>
        <a href="https://www.atankalama.com/cocina/public/index.php?page=desayuno/tablero"
           target="_blank" class="btn btn-sm btn-outline-warning">
          <i class="fa-solid fa-external-link-alt me-1"></i>Ir al tablero de desayunos
        </a>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover table-sm mb-0" id="tablaMasivos">
        <thead class="table-light">
          <tr>
            <th>Fecha</th>
            <th>Hotel</th>
            <th>Proyecto</th>
            <th class="text-center">PAX</th>
            <th>Observaciones</th>
            <th>Registrado por</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($desayunosMasivos as $dm): ?>
          <tr>
            <td class="text-nowrap" data-order="<?= htmlspecialchars($dm['fecha']) ?>"><?= date('d/m/Y', strtotime($dm['fecha'])) ?></td>
            <td class="small"><?= htmlspecialchars($dm['nombre_hotel']) ?></td>
            <td class="small text-muted"><?= htmlspecialchars($dm['nombre_proyecto'] ?: '—') ?></td>
            <td class="text-center fw-bold"><?= (int)$dm['cantidad'] ?></td>
            <td class="small text-muted" style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"
                title="<?= htmlspecialchars($dm['observaciones'] ?? '') ?>">
              <?= htmlspecialchars($dm['observaciones'] ?: '—') ?>
            </td>
            <td class="small text-muted"><?= htmlspecialchars($dm['registrado_por']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal: Resumen por Hotel -->
<div class="modal fade" id="modalResumenHoteles" tabindex="-1" aria-labelledby="modalResumenHotelesLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title" id="modalResumenHotelesLabel">
          <i class="fa-solid fa-file-excel text-success"></i> Resumen por Hotel
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formResumenHoteles" method="GET" action="<?= BASE_URL ?>">
        <input type="hidden" name="url" value="companies/exportResumenHoteles/<?= (int)$company['id'] ?>">
        <div class="modal-body">
          <p class="text-muted small mb-3">
            Genera un Excel con una hoja por hotel.<br>
            Filas: tipos de servicio &mdash; Columnas: días.
          </p>
          <div class="mb-3">
            <label class="form-label small fw-bold">Desde</label>
            <input type="date" name="fecha_desde" id="resumenDesde" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filtrosServicio['fecha_desde'] ?: date('Y-m-01')) ?>" required>
          </div>
          <div class="mb-0">
            <label class="form-label small fw-bold">Hasta</label>
            <input type="date" name="fecha_hasta" id="resumenHasta" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($filtrosServicio['fecha_hasta'] ?: date('Y-m-t')) ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-sm btn-success">
            <i class="fa-solid fa-download"></i> Exportar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require VIEW_PATH . "/layouts/footer.php"; ?>

<script>
$(function () {
  if ($('#tablaServicios').length) {
    $('#tablaServicios').DataTable({
      order: [[0, 'desc']],
      columnDefs: [
        { orderable: false, targets: [7, 8, 9] }
      ],
      pageLength: 25,
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-CL.json'
      }
    });
  }

  if ($('#tablaMasivos').length) {
    $('#tablaMasivos').DataTable({
      order: [[0, 'desc']],
      columnDefs: [
        { orderable: false, targets: [4, 5] }
      ],
      pageLength: 25,
      language: {
        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-CL.json'
      }
    });
  }

  const baseUrl = '<?= rtrim(BASE_URL, '/') ?>';

  document.querySelectorAll('.btn-cobrado').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const id = parseInt(this.dataset.id, 10);
      btn.disabled = true;

      fetch(baseUrl + '?url=companies/toggleCobrado/' + id, {
        method : 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (!data.success) { alert('Error al actualizar'); btn.disabled = false; return; }

        const cobrado = data.cobrado;
        btn.dataset.cobrado = cobrado;

        if (cobrado) {
          btn.classList.replace('btn-outline-secondary', 'btn-success');
          btn.querySelector('i').classList.replace('fa-circle', 'fa-check-circle');
          const fecha = data.cobrado_at ? new Date(data.cobrado_at).toLocaleDateString('es-CL') : '';
          btn.title = 'Cobrado' + (fecha ? ' el ' + fecha : '') + ' — clic para revertir';
        } else {
          btn.classList.replace('btn-success', 'btn-outline-secondary');
          btn.querySelector('i').classList.replace('fa-check-circle', 'fa-circle');
          btn.title = 'Marcar como cobrado';
        }
        btn.disabled = false;
      })
      .catch(function () { alert('Error de red'); btn.disabled = false; });
    });
  });
});
</script>
