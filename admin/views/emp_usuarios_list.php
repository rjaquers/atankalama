<?php include 'layout.php'; ?>

<div class="container-fluid mt-4">

    <?php if (!empty($_SESSION['flash_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['flash_ok'] ?>
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

    <!-- Cabecera -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">
                <i class="bi bi-people-fill"></i>
                Usuarios — <?= htmlspecialchars($empresa['business_name']) ?>
            </h4>
            <small class="text-muted">
                Personas con acceso al portal de la empresa en
                <a href="https://www.atankalama.com/empresas/" target="_blank" class="text-muted">atankalama.com/empresas</a>
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php?route=doc_companies/list" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Volver a Empresas
            </a>
            <a href="index.php?route=emp/usuarios/create&company=<?= $empresa['id'] ?>" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i> Nuevo usuario
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <table id="tablaEmpUsuarios" class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <?php
                        $ts  = !empty($u['last_login']) ? strtotime($u['last_login']) : 0;
                        $diff = $ts ? time() - $ts : 0;
                        if (!$ts)            $rel = '—';
                        elseif ($diff < 60)  $rel = 'hace ' . $diff . 's';
                        elseif ($diff < 3600)$rel = 'hace ' . floor($diff / 60) . 'min';
                        elseif ($diff < 86400)$rel = 'hace ' . floor($diff / 3600) . 'h';
                        else                 $rel = date('d/m/Y H:i', $ts);
                    ?>
                    <tr>
                        <!-- Nombre -->
                        <td><?= htmlspecialchars($u['name']) ?></td>

                        <!-- Correo -->
                        <td>
                            <a href="mailto:<?= htmlspecialchars($u['email']) ?>" class="text-decoration-none small">
                                <?= htmlspecialchars($u['email']) ?>
                            </a>
                        </td>

                        <!-- Rol -->
                        <td>
                            <?php if ($u['role'] === 'admin'): ?>
                                <span class="badge bg-danger"><i class="bi bi-shield-fill"></i> Admin</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-eye-fill"></i> Visor</span>
                            <?php endif; ?>
                        </td>

                        <!-- Estado -->
                        <td>
                            <?php if ($u['status']): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <!-- Último acceso -->
                        <td data-order="<?= $ts ?>">
                            <span class="text-muted small" title="<?= $ts ? htmlspecialchars(date('d/m/Y H:i:s', $ts)) : '' ?>">
                                <?= $rel ?>
                            </span>
                        </td>

                        <!-- Creado -->
                        <td class="small text-muted">
                            <?= $u['created_at'] ? date('d/m/Y', strtotime($u['created_at'])) : '—' ?>
                        </td>

                        <!-- Acciones -->
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="index.php?route=emp/usuarios/edit&id=<?= $u['id'] ?>&company=<?= $empresa['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                <form method="POST" action="index.php?route=emp/usuarios/delete"
                                      onsubmit="return confirm('¿Eliminar el acceso de <?= htmlspecialchars(addslashes($u['name'])) ?>?')">
                                    <input type="hidden" name="id"         value="<?= $u['id'] ?>">
                                    <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar acceso">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($usuarios)): ?>
                <p class="text-center text-muted py-4">
                    <i class="bi bi-people fs-2 d-block mb-2"></i>
                    Esta empresa aún no tiene usuarios con acceso.
                    <a href="index.php?route=emp/usuarios/create&company=<?= $empresa['id'] ?>">Crear el primero</a>.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $('#tablaEmpUsuarios').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json' },
        order: [[0, 'asc']],
        pageLength: 25,
        columnDefs: [{ orderable: false, targets: [6] }]
    });
});
</script>

<?php include '../helpers/cierre.php'; ?>
