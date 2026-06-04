<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center py-3">
                    <i class="bi bi-key fs-3"></i>
                    <h5 class="mb-0 mt-1">Ingresa tu código</h5>
                    <small class="text-white-50">
                        Enviamos un código de 6 dígitos a<br>
                        <strong><?= htmlspecialchars($email) ?></strong>
                    </small>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?route=auth/verify-code">
                        <div class="mb-3">
                            <label for="code" class="form-label">Código de 6 dígitos</label>
                            <input type="text" class="form-control form-control-lg text-center"
                                   id="code" name="code"
                                   maxlength="6" pattern="\d{6}"
                                   placeholder="_ _ _ _ _ _"
                                   autocomplete="one-time-code"
                                   autofocus required>
                        </div>

                        <div class="d-grid mb-2">
                            <button type="submit" class="btn btn-dark btn-lg">
                                <i class="bi bi-unlock"></i> Verificar código
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="index.php?route=auth/request" class="text-muted small">
                            <i class="bi bi-arrow-left"></i> Volver / usar otro correo
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center text-muted small">
                    El código expira en 10 minutos.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-formatear: solo dígitos
document.getElementById('code').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});
// Auto-submit al completar 6 dígitos
document.getElementById('code').addEventListener('input', function () {
    if (this.value.length === 6) this.closest('form').submit();
});
</script>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
