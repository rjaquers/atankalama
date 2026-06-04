<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:500px;">
    <h4 class="mb-4"><i class="bi bi-plus-circle me-2"></i> Nuevo Perfil</h4>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="index.php?route=acceso/perfiles/store">
                <div class="mb-3">
                    <label class="form-label">Nombre del Perfil</label>
                    <input type="text" name="nombre" class="form-control" required maxlength="100" 
                           placeholder="Ej: Recepcionista" autofocus>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                    <a href="index.php?route=acceso/perfiles/list" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../helpers/cierre.php'; ?>
