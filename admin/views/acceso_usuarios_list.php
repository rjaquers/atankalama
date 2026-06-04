<?php include 'layout.php'; ?>

<!-- DataTables Buttons: CSS (solo esta vista) -->
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_ok']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_ok']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-people-fill"></i> Usuarios del Sistema</h4>
        <div class="d-flex gap-2">
            <button id="btnExcel" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
            </button>
            <a href="index.php?route=acceso/usuarios/log" class="btn btn-outline-secondary">
                <i class="bi bi-journal-text"></i> Log de ingresos
            </a>
            <a href="index.php?route=acceso/usuarios/create" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i> Nuevo usuario
            </a>
        </div>
    </div>

    <table id="tablaUsuarios" class="table table-striped table-hover align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th class="d-none d-md-table-cell">Correo</th>
                <th class="d-none">Teléfono</th>
                <th class="d-none">RUT</th>
                <th class="d-none">Perfil</th>
                <th class="d-none d-md-table-cell">Apps</th>
                <th class="d-none d-md-table-cell">Sesión</th>
                <th class="d-none">Último acceso</th>
                <th>Estado</th>
                <th class="d-none d-md-table-cell">Correo validado</th>
                <th class="d-none d-md-table-cell" title="Recibe email al registrar una novedad">Novedades</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <?php
                $esSelf        = ($u['email'] === $emailActual);
                $sesionActiva  = !empty($u['sesion_expira_en'])
                                 && strtotime($u['sesion_expira_en']) > time();
                $nombreCompleto = trim(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? ''));

                // Texto plano para exportar (sin badges ni íconos)
                if (!empty($u['forzar_logout']))  $sesionExport = 'Cerrando';
                elseif ($sesionActiva)             $sesionExport = 'Activa';
                else                               $sesionExport = 'Sin sesión';

                if (!empty($u['last_login'])) {
                    $ts   = strtotime($u['last_login']);
                    $diff = time() - $ts;
                    if ($diff < 60)        $rel = 'hace ' . $diff . 's';
                    elseif ($diff < 3600)  $rel = 'hace ' . floor($diff/60) . 'min';
                    elseif ($diff < 86400) $rel = 'hace ' . floor($diff/3600) . 'h';
                    else                   $rel = date('d/m/Y H:i', $ts);
                    $loginExport = date('d/m/Y H:i', $ts);
                } else {
                    $ts   = 0;
                    $rel  = '—';
                    $loginExport = '—';
                }
            ?>
            <tr>
                <!-- Nombre -->
                <td data-export="<?= htmlspecialchars($nombreCompleto ?: '—') ?>">
                    <?= htmlspecialchars($nombreCompleto) ?: '<span class="text-muted">—</span>' ?>
                    <?php if ($esSelf): ?>
                        <span class="badge bg-primary ms-1" title="Eres tú"><i class="bi bi-person-check"></i> Tú</span>
                    <?php endif; ?>
                </td>
                <!-- Correo -->
                <td class="d-none d-md-table-cell">
                    <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-decoration-none small">
                        <?= htmlspecialchars($u['email']) ?>
                    </a>
                </td>
                <!-- Teléfono -->
                <td class="d-none" data-export="<?= htmlspecialchars($u['telefono'] ?? '—') ?>">
                    <?php if (!empty($u['telefono'])): ?>
                        <a href="tel:<?= htmlspecialchars($u['telefono']) ?>" class="text-decoration-none">
                            <?= htmlspecialchars($u['telefono']) ?>
                        </a>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <!-- RUT -->
                <td class="d-none"><?= htmlspecialchars($u['rut'] ?? '—') ?></td>
                <!-- Perfil -->
                <td class="d-none" data-export="<?= htmlspecialchars($u['perfil'] ?? '—') ?>">
                    <?php
                        $perfil = $u['perfil'] ?? '';
                        if ($perfil === 'Administrador'):
                    ?>
                        <span class="badge bg-danger"><i class="bi bi-shield-fill"></i> Administrador</span>
                    <?php elseif ($perfil === 'Gerencia'): ?>
                        <span class="badge bg-dark"><i class="bi bi-briefcase-fill"></i> Gerencia</span>
                    <?php elseif (str_starts_with($perfil, 'Jefatura')): ?>
                        <span class="badge bg-primary"><i class="bi bi-person-badge-fill"></i> <?= htmlspecialchars($perfil) ?></span>
                    <?php elseif (!empty($perfil)): ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($perfil) ?></span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <!-- Apps -->
                <td class="d-none d-md-table-cell" data-export="<?= (int)$u['total_apps'] ?>">
                    <span class="badge bg-info text-dark"><?= $u['total_apps'] ?> app(s)</span>
                </td>
                <!-- Sesión -->
                <td class="d-none d-md-table-cell" data-export="<?= htmlspecialchars($sesionExport) ?>">
                    <?php if (!empty($u['forzar_logout'])): ?>
                        <span class="badge bg-warning text-dark" title="Cierre pendiente">
                            <i class="bi bi-clock-history"></i> Cerrando…
                        </span>
                    <?php elseif ($sesionActiva): ?>
                        <span class="badge bg-success" title="Sesión activa hasta <?= htmlspecialchars(date('H:i', strtotime($u['sesion_expira_en']))) ?>">
                            <i class="bi bi-circle-fill" style="font-size:.55rem"></i> Activa
                        </span>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border">
                            <i class="bi bi-circle" style="font-size:.55rem"></i> Sin sesión
                        </span>
                    <?php endif; ?>
                </td>
                <!-- Último acceso -->
                <td class="d-none" data-order="<?= $ts ?>" data-export="<?= htmlspecialchars($loginExport) ?>">
                    <?php if ($ts): ?>
                        <span class="text-muted small" title="<?= htmlspecialchars(date('d/m/Y H:i:s', $ts)) ?>">
                            <?= $rel ?>
                        </span>
                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <!-- Estado -->
                <td data-export="<?= $u['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?>">
                    <?php if ($u['estado'] === 'activo'): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactivo</span>
                    <?php endif; ?>
                </td>
                <!-- Correo validado -->
                <td class="d-none d-md-table-cell" data-export="<?= !empty($u['validado']) ? 'Verificado' : 'Sin verificar' ?>">
                    <?php if (!empty($u['validado'])): ?>
                        <span class="badge bg-success" title="Verificó su código correctamente">
                            <i class="bi bi-patch-check-fill"></i> Verificado
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark" title="Aún no completó la verificación">
                            <i class="bi bi-patch-exclamation-fill"></i> Sin verificar
                        </span>
                    <?php endif; ?>
                </td>
                <!-- Novedades -->
                <?php $recibeNovedades = !empty($u['recibe_novedades']); ?>
                <td class="d-none d-md-table-cell text-center"
                    data-export="<?= $recibeNovedades ? 'Sí' : 'No' ?>">
                    <form method="POST" action="index.php?route=acceso/usuarios/toggle-novedades"
                          style="display:inline">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit"
                                class="btn btn-sm <?= $recibeNovedades ? 'btn-success' : 'btn-outline-secondary' ?>"
                                title="<?= $recibeNovedades ? 'Quitar de notificaciones' : 'Agregar a notificaciones' ?>">
                            <i class="bi bi-<?= $recibeNovedades ? 'bell-fill' : 'bell-slash' ?>"></i>
                        </button>
                    </form>
                </td>
                <!-- Acciones -->
                <td class="d-flex gap-1 flex-wrap">
                    <?php if (!$esSelf): ?>
                        <a href="index.php?route=acceso/usuarios/ver-como&id=<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-dark" title="Ver como este usuario">
                            <i class="bi bi-eye-fill"></i>
                        </a>
                    <?php endif; ?>
                    <a href="index.php?route=acceso/usuarios/edit&id=<?= $u['id'] ?>"
                       class="btn btn-sm btn-outline-primary" title="Editar datos">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="index.php?route=acceso/usuarios/permisos&id=<?= $u['id'] ?>"
                       class="btn btn-sm btn-outline-warning" title="Gestionar accesos y roles">
                        <i class="bi bi-key-fill"></i> Accesos
                    </a>
                    <a href="index.php?route=acceso/usuarios/log&email=<?= urlencode($u['email']) ?>"
                       class="btn btn-sm btn-outline-secondary" title="Ver historial de ingresos">
                        <i class="bi bi-journal-text"></i>
                    </a>
                    <?php if (!$esSelf): ?>
                    <form method="POST" action="index.php?route=acceso/usuarios/cerrar-sesion"
                          onsubmit="return confirm('¿Cerrar la sesión de <?= htmlspecialchars(addslashes($nombreCompleto ?: $u['email'])) ?>?')">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit"
                                class="btn btn-sm <?= $sesionActiva ? 'btn-danger' : 'btn-outline-danger' ?>"
                                title="<?= $sesionActiva ? 'Cerrar sesión activa' : 'Sin sesión activa' ?>"
                                <?= (!$sesionActiva && empty($u['forzar_logout'])) ? 'disabled' : '' ?>>
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- DataTables Buttons JS (solo esta vista) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>

<script>
$(document).ready(function () {
    var tabla = $('#tablaUsuarios').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[7, 'asc'], [0, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [10, 11] }],
        buttons: [
            {
                extend:    'excel',
                title:     'Usuarios del Sistema · Hotel Atankalama',
                filename:  'usuarios_' + new Date().toISOString().slice(0,10),
                sheetName: 'Usuarios',
                exportOptions: {
                    // Columnas 0-10: todo excepto Acciones (índice 11)
                    columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    // Usar data-export cuando esté disponible, si no el texto del nodo
                    format: {
                        body: function (data, row, column, node) {
                            var exp = $(node).data('export');
                            return (exp !== undefined && exp !== '') ? exp : $('<div>').html(data).text().trim();
                        }
                    }
                }
            }
        ]
    });

    // Botón manual en la barra superior dispara el botón de DataTables
    $('#btnExcel').on('click', function () {
        tabla.button(0).trigger();
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
