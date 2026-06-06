<?php
/**
 * Vista de Creación de Usuario - Atankalama Empresas
 */
Layout::header($title, $user, 'usuarios');
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>usuarios" class="text-decoration-none">Usuarios</a></li>
                    <li class="breadcrumb-item active">Nuevo Usuario</li>
                </ol>
            </nav>
            <h3>Registrar Nuevo Usuario</h3>
            <p class="text-muted">Complete el formulario para dar acceso a un nuevo miembro de su empresa.</p>
        </div>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>usuarios/store" method="POST">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="Ej: Juan Pérez" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Correo Electrónico (Login)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="nombre@empresa.com" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="form-label fw-bold">Rol del Usuario</label>
                            <select name="role" class="form-select">
                                <option value="visor">Visor (Solo consulta y exportación)</option>
                                <option value="admin">Administrador (Puede crear otros usuarios)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?= BASE_URL ?>usuarios" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa-solid fa-save me-2"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php Layout::footer(); ?>
