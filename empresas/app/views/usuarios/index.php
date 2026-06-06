<?php
/**
 * Vista de Usuarios - Atankalama Empresas
 */
Layout::header($title, $user, 'usuarios');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Gestión de Usuarios</h3>
    <a href="<?= BASE_URL ?>usuarios/create" class="btn btn-primary">
        <i class="fa-solid fa-user-plus me-2"></i> Nuevo Usuario
    </a>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-check-circle me-2"></i> <?= htmlspecialchars($_GET['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nombre</th>
                        <th>Email / Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No hay usuarios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($usuarios as $u): ?>
                            <tr>
                                <td class="ps-4 fw-medium"><?= htmlspecialchars($u['name']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= $u['role'] == 'admin' ? 'Administrador' : 'Visor' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($u['status']): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3">Activo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group">
                                        <a href="<?= BASE_URL ?>usuarios/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <?php if($u['id'] != $user['id']): ?>
                                            <a href="<?= BASE_URL ?>usuarios/delete/<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('¿Está seguro de eliminar este usuario? (Soft Delete)')" title="Eliminar">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php Layout::footer(); ?>
