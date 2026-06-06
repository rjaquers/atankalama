<?php
/**
 * Vista de Edición de Usuario - Atankalama Empresas
 */
Layout::header($title, $user, 'usuarios');
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>usuarios" class="text-decoration-none">Usuarios</a></li>
                    <li class="breadcrumb-item active">Editar Usuario</li>
                </ol>
            </nav>
            <h3>Editar Usuario</h3>
            <p class="text-muted">Modifique los datos del usuario. Deje la contraseña en blanco si no desea cambiarla.</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>usuarios/update/<?= $usuario['id'] ?>" method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($usuario['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Correo Electrónico (Login)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nueva Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Opcional">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Rol del Usuario</label>
                            <select name="role" class="form-select">
                                <option value="visor" <?= $usuario['role'] == 'visor' ? 'selected' : '' ?>>Visor (Solo consulta)</option>
                                <option value="admin" <?= $usuario['role'] == 'admin' ? 'selected' : '' ?>>Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Estado de Cuenta</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" <?= $usuario['status'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="statusSwitch">Cuenta Activa</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end border-top pt-3">
                        <a href="<?= BASE_URL ?>usuarios" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php Layout::footer(); ?>
