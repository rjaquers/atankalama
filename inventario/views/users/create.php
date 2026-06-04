<?php 
$page_title = 'Crear Usuario';
include 'views/layout/header.php'; 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h2>
    <a href="index.php?page=users" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Nombre de Usuario *</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               placeholder="usuario123">
                        <div class="form-text">Solo letras, números y guiones bajos</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required
                               placeholder="Juan Pérez">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" id="password" name="password" required
                               minlength="6">
                        <div class="form-text">Mínimo 6 caracteres</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol *</label>
                        <select class="form-control" id="role" name="role" required>
                            <option value="">Seleccionar rol</option>
                            <option value="user">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                        <div class="form-text">
                            <strong>Usuario:</strong> Puede ver productos y registrar consumos<br>
                            <strong>Administrador:</strong> Acceso completo al sistema
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=users" class="btn btn-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-2"></i>Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>