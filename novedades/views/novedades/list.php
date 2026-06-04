<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-6">
    <div class="row">
        <div class="col-12">
            <h3 class='mb-3 text-primary'>
                <i class='bi bi-list-check'></i> Historial de Novedades
            </h3>



            <?php if (isset($_GET['fecha_inicio']) && isset($_GET['fecha_fin'])): ?>
                <div class="alert alert-light border mb-3">
                    <strong>Resultados:</strong>
                    <?= date('d-m-Y', strtotime($_GET['fecha_inicio'])) ?>
                    hasta <?= date('d-m-Y', strtotime($_GET['fecha_fin'])) ?>
                    <?php if (!empty($_GET['hotel'])): ?>
                        | <b>Hotel:</b> <?= htmlspecialchars($_GET['hotel']) ?>
                    <?php endif; ?>
                    <?php if (!empty($_GET['tipo_novedad'])): ?>
                        | <b>Departamento:</b> <?= htmlspecialchars($_GET['tipo_novedad']) ?>
                    <?php endif; ?>
                    <?php if (!empty($_GET['area'])): ?>
                        | <b>Área:</b> <?= htmlspecialchars($_GET['area']) ?>
                    <?php endif; ?>
                    <?php if (!empty($_GET['keyword'])): ?>
                        | <b>Palabra:</b> <?= htmlspecialchars($_GET['keyword']) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class='card shadow-sm p-3 mb-4'>
                <form method='get' action='index.php' class='row g-2 align-items-end'>

                    <input type='hidden' name='route' value='novedades/list'>

                    <!-- Fecha -->
                    <div class='col-auto'>
                        <label class='form-label small mb-1'>Fecha</label>
                        <input type='date' name='fecha' class='form-control form-control-sm'
                            value="<?= htmlspecialchars($_GET['fecha'] ?? date('Y-m-d')) ?>">
                    </div>

                    <!-- Pendientes -->
                    <div class='col-auto form-check mt-4'>
                        <input class='form-check-input' type='checkbox' name='solo_pendientes' value='1'
                            <?= isset($_GET['solo_pendientes']) ? 'checked' : '' ?>>
                        <label class="form-check-label small">
                            Pendientes
                        </label>
                    </div>

                    <!-- Críticas -->
                    <div class="col-auto form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="solo_criticas" value="1"
                            <?= isset($_GET['solo_criticas']) ? 'checked' : '' ?>>
                        <label class="form-check-label small">
                            Críticas (≥ 8)
                        </label>
                    </div>

                    <!-- Importancia mínima -->
                    <div class="col-auto">
                        <label class="form-label small mb-1">Importancia</label>
                        <select name="min_importancia" class="form-select form-select-sm">
                            <option value="">Todas</option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_GET['min_importancia']) && $_GET['min_importancia'] == $i) ? 'selected' : '' ?>>
                                    ≥ <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- Botón buscar -->
                    <div class="col-auto mt-4">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>

                    </div>
                    <!-- Botón limpiar -->
                    <div class='col-auto mt-4'>
                        <a href='index.php?route=novedades/list' class='btn btn-sm btn-outline-secondary'>
                            <i class='bi bi-x-circle'></i> Limpiar
                        </a>
                    </div>


                    <!-- Exportaciones -->
                    <?php
                    $hoy              = date('Y-m-d');
                    $hace7            = date('Y-m-d', strtotime('-6 days'));
                    $hace15           = date('Y-m-d', strtotime('-14 days'));
                    $mesPasadoInicio  = date('Y-m-01', strtotime('first day of last month'));
                    $mesPasadoFin     = date('Y-m-t',  strtotime('last day of last month'));
                    $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                              'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    $mesPasadoLabel = $meses[(int)date('n', strtotime('last month')) - 1]
                                    . ' ' . date('Y', strtotime('last month'));
                    ?>
                    <div class="col-auto mt-4 d-flex gap-1">

                        <!-- Dropdown Excel por período -->
                        <div class="btn-group">
                            <button type="button"
                                    class="btn btn-sm btn-outline-success dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false"
                                    title="Descargar Excel por período">
                                <i class="bi bi-file-earmark-excel me-1"></i>Excel
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><h6 class="dropdown-header">Descargar período</h6></li>
                                <li>
                                    <a class="dropdown-item" href="index.php?route=novedades/export&format=excel&fecha_inicio=<?= $hace7 ?>&fecha_fin=<?= $hoy ?>">
                                        <i class="bi bi-calendar-week me-2 text-success"></i>Últimos 7 días
                                        <small class="text-muted d-block"><?= date('d/m', strtotime($hace7)) ?> – <?= date('d/m/Y', strtotime($hoy)) ?></small>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="index.php?route=novedades/export&format=excel&fecha_inicio=<?= $hace15 ?>&fecha_fin=<?= $hoy ?>">
                                        <i class="bi bi-calendar2-range me-2 text-success"></i>Últimos 15 días
                                        <small class="text-muted d-block"><?= date('d/m', strtotime($hace15)) ?> – <?= date('d/m/Y', strtotime($hoy)) ?></small>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="index.php?route=novedades/export&format=excel&fecha_inicio=<?= $mesPasadoInicio ?>&fecha_fin=<?= $mesPasadoFin ?>">
                                        <i class="bi bi-calendar-month me-2 text-success"></i>Mes pasado
                                        <small class="text-muted d-block"><?= date('d/m', strtotime($mesPasadoInicio)) ?> – <?= date('d/m/Y', strtotime($mesPasadoFin)) ?></small>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- PDF filtro actual -->
                        <a href="index.php?route=novedades/export&format=pdf" target="_blank"
                            class="btn btn-sm btn-outline-danger" title="Exportar PDF">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </a>
                    </div>

                    <!-- KPI -->
                    <div class="col text-end mt-4">
                        <span class="fw-semibold text-muted small">
                            📌 <?= count($novedades) ?> novedades
                        </span>
                    </div>

                </form>
            </div>



        </div>
        <div class="col-4">
            <div class='d-flex justify-content-between align-items-center mb-3'>

                <button class='btn btn-outline-primary' data-bs-toggle='modal' data-bs-target='#modalBusqueda'>
                    <i class='bi bi-search'></i> Buscar por rango
                </button>
            </div>
        </div>




    </div>


    <?php if (empty($novedades)): ?>
        <div class="alert alert-info mt-3">
            No hay novedades registradas para esta fecha.
        </div>
    <?php else: ?>
        <p class="fw-bold mb-4">
            📌 Listado de novedades <?= count($novedades) ?> | fecha
            <?= date('d-m-Y', strtotime($_GET['fecha'] ?? date('Y-m-d'))) ?>
        </p>

        <div class="timeline d-flex flex-column">
            <?php
            // Paleta de colores (puedes ajustarla a los colores corporativos)
            $colores = [
                '#0288d1',
                '#009688',
                '#8e44ad',
                '#d35400',
                '#2c3e50',
                '#27ae60',
                '#c0392b',
                '#f39c12'
            ];
            $mapaColores = [];
            $i = 0;

            foreach ($novedades as $n):


                $nombre = $n['recepcionista'];
                if (!isset($mapaColores[$nombre])) {
                    $mapaColores[$nombre] = $colores[$i % count($colores)];
                    $i++;
                }
                $colorFondo = $mapaColores[$nombre];
                ?>


                <?php
                $nivel = (int) $n['nivel_importancia'];

                if ($nivel >= 8) {
                    $claseImportancia = 'imp-alta';
                } elseif ($nivel >= 5) {
                    $claseImportancia = 'imp-media';
                } else {
                    $claseImportancia = 'imp-baja';
                }
                ?>

                <!--Para enviar por WhatsApp-->
                <?php $hotel = htmlspecialchars($n['hotel']);
                $area = htmlspecialchars($n['area']);
                $detalle = strip_tags($n['detalle']);
                $nombre = htmlspecialchars($n['recepcionista_nombre'] ?? 'Recepción');

                // ⚠️ Número centralizado (luego puede venir de config o BD)
                $whatsappNumero = '56961405440';

                // Mensaje WhatsApp (URL-safe)
                $mensajeWhatsApp = urlencode(
                    "Hola, se ha registrado una nueva novedad en el hotel $hotel.\n" .
                    "Área: $area\n" .
                    "Recepcionista: $nombre\n" .
                    "Detalle: $detalle"
                );

                // Link WhatsApp
                $linkWhatsApp = "https://wa.me/$whatsappNumero?text=$mensajeWhatsApp";


                //Preparo el correo:
                $hotel = $n['hotel'];
                $area = $n['area'];
                $detalle = trim($detalle); // el que ya formateaste
                $fecha = date('d-m-Y H:i', strtotime($n['fecha_registro']));
                $nombre = $n['recepcionista_nombre'] ?? 'Recepción';

                // Asunto
                $emailSubject = urlencode("Novedad registrada – $hotel");

                // Cuerpo
                $emailBody = urlencode(
                    "Se ha registrado una nueva novedad en el sistema.\n\n" .
                    "Hotel: $hotel\n" .
                    "Área: $area\n" .
                    "Fecha: $fecha\n" .
                    "Recepcionista: $nombre\n\n" .
                    "Detalle:\n$detalle\n\n" .
                    "—\nSistema de Novedades Hotel Atankalama"
                );

                // Link mailto
                $linkCorreo = "mailto:?subject=$emailSubject&body=$emailBody";



                ?>


                <!--Css para compartir tarjeta-->
                <!--            fin css-->
                <div class="timeline-nodes d-flex justify-content-between align-items-start mb-5">



                    <!-- tarjeta -->
                    <div class='col-5 timeline-content p-3 bg-white position-relative'>
                        <h3 class='text-white rounded-top p-2' style="background:<?= $colorFondo ?>">
                            <?= $n['id']; ?> | <?= htmlspecialchars($nombre) ?> /
                            <small class="small">
                                📅 <?= date('d-m-Y H:i', strtotime($n['fecha_registro'])) ?>
                            </small>

                        </h3>


                        <div class="mb-2 d-flex flex-wrap align-items-center gap-1">
                            <span class='badge bg-info'>
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($n['area']) ?>
                            </span>
                            <span class="badge" style="background-color: #673ab7; color: white;">
                                <i class="bi bi-building"></i> Hotel: <?= htmlspecialchars($n['hotel']) ?>
                            </span>
                            <span class='badge' style="background-color: #8b5cf6; color: #fff;">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($n['tipo_novedad'] ?? 'Otro') ?>
                            </span>

                            <?php if (!empty($n['requiere_seguimiento'])): ?>
                                <?php if ((int) $n['seguimiento_estado'] === 1): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-hourglass-split"></i> Pendiente
                                    </span>
                                <?php elseif ((int) $n['seguimiento_estado'] === 2): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Realizada
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($n['tipo_seguimiento'])): ?>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-tools"></i> <?= ucfirst(htmlspecialchars($n['tipo_seguimiento'])) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!empty($n['flexkeeping_id'])): ?>
                                    <span class="badge" style="background-color: #3f51b5; color: white;">
                                        <i class="bi bi-hash"></i> ID: <?= htmlspecialchars($n['flexkeeping_id']) ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="importancia-chip <?= $claseImportancia ?>" title="Nivel de importancia: <?= $nivel ?>">
                            <?= $nivel ?>
                        </div>

                        <hr>

                        <?php
                        $detalleRaw = trim($n['detalle']);
                        $detalle = mb_strtolower($detalleRaw, 'UTF-8');
                        $detalle = preg_replace_callback(
                            '/(^|\n)([a-záéíóúñ])/u',
                            fn($m) => $m[1] . mb_strtoupper($m[2], 'UTF-8'),
                            $detalle
                        );
                        ?>

                        <div class="novedad-detalle mb-3">
                            <?= nl2br(htmlspecialchars($detalle)) ?>
                        </div>

                        <?php
                        $archivos = $model->listarArchivos($n['id']);
                        if (!empty($archivos)): ?>
                            <div class="novedad-adjuntos mt-2 d-flex flex-wrap">
                                <?php foreach ($archivos as $a): ?>
                                    <?php
                                    $ruta = '../uploads/' . date('Y_m_d', strtotime($n['fecha_registro'])) . '/novedad_' . $n['id'] . '/' . $a['archivo'];
                                    ?>
                                    <div class="me-2 mb-2">
                                        <?php if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $a['archivo'])): ?>
                                            <a href="<?= $ruta ?>" target="_blank">
                                                <img src="<?= $ruta ?>" alt="" class="img-thumbnail"
                                                    style="width:100px; height:100px; object-fit: cover;">
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= $ruta ?>" target="_blank"
                                                class="btn btn-sm btn-outline-secondary d-flex align-items-center">
                                                <i class="bi bi-file-earmark-text me-1"></i> <?= htmlspecialchars($a['archivo']) ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <hr>
                        <div class='novedad-actions'>

                            <!-- Compartir por WhatsApp -->
                            <a href="<?= $linkWhatsApp ?>" target='_blank'
                                title='Compartir esta novedad con detalle y fecha por WhatsApp' class='action-icon whatsapp'
                                onclick='event.stopPropagation();'>
                                <i class='bi bi-whatsapp'></i>
                            </a>

                            <!-- Activar tarea o reparación link Flexkeeping -->
                            <?php if (!empty($n['flexkeeping_id'])):
                                $tipoFlex = (strpos(mb_strtolower($n['tipo_seguimiento'] ?? ''), 'repara') !== false) ? 'repair' : 'task';
                                ?>
                                <a href="https://app.flexkeeping.com/assignment/<?= $tipoFlex ?>?filters=submitted&id=<?= htmlspecialchars($n['flexkeeping_id']) ?>"
                                    target='_blank' title='Abrir en Flexkeeping' class='action-icon flexkeeping'
                                    onclick='event.stopPropagation();'>
                                    <img src='https://www.atankalama.com/novedades/assets/img/logo_flexkeeping.png'
                                        alt='Flexkeeping' height='22'>
                                </a>
                            <?php endif; ?>



                            <!--                            Compartir por correo-->
                            <a href="<?= $linkCorreo ?>" title='Compartir por correo electrónico' class='action-icon email'
                                onclick='event.stopPropagation();'>
                                <i class='bi bi-envelope'></i>
                            </a>

                            <!-- Botón para adjuntar evidencia -->
                            <button class='btn btn-sm btn-outline-primary btn-evidencia' data-bs-toggle='modal'
                                data-bs-target='#modalAdjunto' data-novedad-id="<?= (int) $n['id'] ?>" data-bs-toggle='tooltip'
                                data-bs-placement='top' title='Agregar evidencia (foto o archivo)'>

                                <i class='bi bi-paperclip me-1'></i>
                                Evidencia
                            </button>

                            <?php if (!empty($n['requiere_seguimiento'])): ?>
                                <a href="index.php?route=novedades/seguimiento&id=<?= (int) $n['id'] ?>"
                                    class="btn btn-sm btn-outline-warning" title="Gestionar seguimiento de esta tarea"
                                    onclick="event.stopPropagation();">
                                    <i class="bi bi-list-task"></i>
                                    Seguimiento
                                </a>
                            <?php endif; ?>


                        </div>
                        <!--Dentro de la tarjeta-->



                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!--Modal:-->
<!-- Modal de búsqueda -->
<div class='modal fade' id='modalBusqueda' tabindex='-1' aria-labelledby='modalBusquedaLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>

            <form id='formBusqueda' action='index.php' method='get'>
                <input type='hidden' name='route' value='novedades/list'>

                <div class='modal-header bg-primary text-white'>
                    <h5 class='modal-title' id='modalBusquedaLabel'>
                        <i class='bi bi-calendar-range'></i> Buscar por rango de fechas
                    </h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                </div>

                <div class='modal-body'>

                    <!-- Keyword -->
                    <div class='mb-3'>
                        <label for='keyword' class='form-label'>Palabra(s) clave</label>
                        <input type='text' class='form-control' id='keyword' name='keyword'
                            value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                            placeholder='Ej: notebook, huésped, reparación...'>
                    </div>

                    <!-- Fecha Inicio -->
                    <div class='mb-3'>
                        <label for='fecha_inicio' class='form-label'>Fecha inicio</label>
                        <input type='date' class='form-control' id='fecha_inicio' name='fecha_inicio'
                            value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>" required>
                    </div>

                    <!-- Fecha Fin -->
                    <div class='mb-3'>
                        <label for='fecha_fin' class='form-label'>Fecha fin</label>
                        <input type='date' class='form-control' id='fecha_fin' name='fecha_fin'
                            value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>" required>
                    </div>

                    <!-- Hotel -->
                    <div class='mb-3'>
                        <label for='hotel' class='form-label'>Hotel</label>
                        <select name='hotel' id='hotel' class='form-select'>
                            <option value=''>Todos</option>
                            <option value='Atankalama' <?= (($_GET['hotel'] ?? '') === 'Atankalama') ? 'selected' : '' ?>>
                                Atankalama
                            </option>
                            <option value='Atankalama Inn' <?= (($_GET['hotel'] ?? '') === 'Atankalama Inn') ? 'selected' : '' ?>>
                                Atankalama Inn
                            </option>
                        </select>
                    </div>

                    <!-- Departamento involucrado -->
                    <div class='mb-3'>
                        <label for='tipo_novedad' class='form-label'>Departamento involucrado</label>
                        <select name='tipo_novedad' id='tipo_novedad' class='form-select'>
                            <option value=''>Todos</option>
                            <?php foreach (['RRHH' => 'Personal', 'Aseo' => 'Aseo', 'Cocina' => 'Cocina', 'Servicio' => 'Servicio', 'Tecnología' => 'Tecnologia', 'Mantenimiento' => 'mantenimiento', 'Otro' => 'Otro'] as $label => $val): ?>
                                <option value='<?= $val ?>' <?= (($_GET['tipo_novedad'] ?? '') === $val) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Área -->
                    <div class='mb-3'>
                        <label for='area' class='form-label'>Área</label>
                        <select name='area' id='area' class='form-select'>
                            <option value=''>Todas</option>
                            <?php foreach (['Recepción' => 'recepcion', 'Estacionamiento' => 'estacionamiento', 'Comedor' => 'comedor', 'Cocina' => 'cocina', 'Piscina' => 'piscina', 'Habitación' => 'habitacion', 'Pasillos' => 'Pasillos', 'Jardines' => 'Jardines', 'Otros' => 'otros'] as $label => $val): ?>
                                <option value='<?= $val ?>' <?= (($_GET['area'] ?? '') === $val) ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Solo críticas -->
                    <div class='form-check mb-2'>
                        <input class='form-check-input' type='checkbox' name='solo_criticas' value='1'
                            id='soloCriticasModal' <?= isset($_GET['solo_criticas']) ? 'checked' : '' ?>>
                        <label class='form-check-label' for='soloCriticasModal'>
                            Mostrar solo críticas (≥ 8)
                        </label>
                    </div>

                    <!-- Solo pendientes -->
                    <div class='form-check mb-3'>
                        <input class='form-check-input' type='checkbox' name='solo_pendientes' value='1'
                            id='soloPendientesModal' <?= isset($_GET['solo_pendientes']) ? 'checked' : '' ?>>
                        <label class='form-check-label' for='soloPendientesModal'>
                            Mostrar solo tareas pendientes
                        </label>
                    </div>

                    <div class='alert alert-info small mb-0'>
                        🔎 El rango máximo de búsqueda es de <b>30 días corridos</b>.
                    </div>

                </div>

                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>
                        Cancelar
                    </button>

                    <button type='submit' class='btn btn-primary'>
                        <i class='bi bi-search'></i> Buscar
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>


<!--Modal adjuntar evidencia-->
<!-- Modal para adjuntar evidencia -->
<div class='modal fade' id='modalAdjunto' tabindex='-1' aria-labelledby='modalAdjuntoLabel' aria-hidden='true'>
    <div class='modal-dialog'>
        <div class='modal-content'>
            <form id='formAdjunto' method='POST' action='index.php?route=novedades/agregarAdjunto'
                enctype='multipart/form-data'>
                <input type='hidden' name='novedad_id' id='modal_novedad_id'>

                <div class='modal-header bg-primary text-white'>
                    <h5 class='modal-title' id='modalAdjuntoLabel'>
                        <i class='bi bi-paperclip'></i> Agregar evidencia
                    </h5>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                </div>

                <div class='modal-body'>
                    <div class='mb-3'>
                        <label for='archivosAdjuntos' class='form-label'>Selecciona archivo(s):</label>
                        <input type='file' class='form-control' name='archivos[]' id='archivosAdjuntos' multiple
                            required>
                    </div>
                    <small class='text-muted'>
                        Puedes subir imágenes o documentos PDF. Tamaño máximo 5 MB cada uno.
                    </small>
                </div>

                <div class='modal-footer'>
                    <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancelar</button>
                    <button type='submit' class='btn btn-primary'>
                        <i class='bi bi-upload'></i> Subir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Pasar ID de la novedad al modal
    document.getElementById('modalAdjunto').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const novedadId = button.getAttribute('data-novedad-id');
        document.getElementById('modal_novedad_id').value = novedadId;
    });
</script>

<!-- Estilos específicos del Historial (Timeline) -->
<link href='/novedades/assets/css/novedades_list.css' rel='stylesheet'>


<!--Vaidación del modal-->
<script>
    document.getElementById('formBusqueda').addEventListener('submit', function (e) {
        const inicio = new Date(document.getElementById('fecha_inicio').value);
        const fin = new Date(document.getElementById('fecha_fin').value);

        if (!inicio || !fin) return;

        const diff = (fin - inicio) / (1000 * 60 * 60 * 24);

        if (diff < 0) {
            alert('⚠️ La fecha final no puede ser anterior a la inicial.');
            e.preventDefault();
            return;
        }

        if (diff > 30) {
            alert('⚠️ El rango máximo de búsqueda es de 30 días corridos.');
            e.preventDefault();
            return;
        }
    });

</script>


<!--Alertas de subida de archivos-->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const status = params.get('status');

        if (status === 'ok') {
            Swal.fire({
                icon: 'success',
                title: '¡Evidencia subida correctamente!',
                text: 'Los archivos fueron guardados con éxito.',
                confirmButtonColor: '#0288d1',
                confirmButtonText: 'Aceptar',
                timer: 3000
            });
        } else if (status === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Error al subir los archivos',
                text: 'Ocurrió un problema al intentar guardar la evidencia. Inténtalo nuevamente.',
                confirmButtonColor: '#d33',
                confirmButtonText: 'Cerrar'
            });
        }
    });
</script>


<?php include __DIR__ . '/../../helpers/cierre.php'; ?>