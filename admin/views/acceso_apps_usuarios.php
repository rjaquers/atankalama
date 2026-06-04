<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?route=acceso/apps/list">Aplicaciones</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($app['nombre']) ?></li>
            <li class="breadcrumb-item active" aria-current="page">Usuarios con acceso</li>
        </ol>
    </nav>

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
        <h4>
            <i class="bi bi-people-fill"></i> Usuarios con acceso a: 
            <span class="text-primary"><?= htmlspecialchars($app['nombre']) ?></span>
        </h4>
        <a href="index.php?route=acceso/apps/list" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver al listado
        </a>
    </div>

    <form action="index.php?route=acceso/apps/usuarios/quitar" method="POST" id="formQuitarAcceso">
        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tablaUsuariosApp" class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="checkTodos">
                                </th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Perfil Sistema</th>
                                <th>Rol en App</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="usuario_ids[]" value="<?= $u['id'] ?>" class="form-check-input check-usuario">
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($u['apellido'] . ', ' . $u['nombre']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($u['perfil']) ?></span></td>
                                <td>
                                    <?php if ($u['rol_nombre']): ?>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($u['rol_nombre']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small"><em>Sin rol asignado</em></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($u['estado'] === 'activo'): ?>
                                        <span class="badge bg-success">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <button type="button" class="btn btn-danger" id="btnQuitarAcceso" disabled>
                    <i class="bi bi-person-dash"></i> Quitar acceso a seleccionados
                </button>
                <span class="text-muted align-self-center">
                    Total usuarios: <strong><?= count($usuarios) ?></strong>
                </span>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function () {
    const table = $('#tablaUsuariosApp').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[1, 'asc']],
        pageLength: 50,
        columnDefs: [{ orderable: false, targets: 0 }]
    });

    // Manejo de checkbox "seleccionar todos"
    $('#checkTodos').on('click', function() {
        const rows = table.rows({ 'search': 'applied' }).nodes();
        $('input[type="checkbox"]', rows).prop('checked', this.checked);
        actualizarBoton();
    });

    // Manejo de checkboxes individuales (incluso en otras páginas del DataTable)
    $('#tablaUsuariosApp tbody').on('change', 'input[type="checkbox"]', function() {
        if (!this.checked) {
            const el = $('#checkTodos').get(0);
            if (el && el.checked && ('indeterminate' in el)) {
                el.checked = false;
            }
        }
        actualizarBoton();
    });

    function actualizarBoton() {
        const rows = table.rows().nodes();
        const anyChecked = $('input.check-usuario:checked', rows).length > 0;
        $('#btnQuitarAcceso').prop('disabled', !anyChecked);
    }

    $('#btnQuitarAcceso').on('click', function() {
        const rows = table.rows().nodes();
        const checked = $('input.check-usuario:checked', rows);
        const count = checked.length;
        
        if (confirm(`¿Está seguro que desea quitar el acceso a ${count} usuario(s)? Esta acción también eliminará sus roles asignados en esta aplicación.`)) {
            // Asegurarnos de que todos los ids seleccionados se envíen, incluso los que no están en el DOM actual
            const hiddenInputs = [];
            checked.each(function() {
                hiddenInputs.push($('<input>').attr('type', 'hidden').attr('name', 'usuario_ids[]').val($(this).val()));
            });
            
            // Limpiar los checkboxes actuales del form (para evitar duplicados si están en el DOM)
            $('#formQuitarAcceso input[name="usuario_ids[]"]').remove();
            
            // Agregar los inputs ocultos
            $('#formQuitarAcceso').append(hiddenInputs);
            $('#formQuitarAcceso').submit();
        }
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
