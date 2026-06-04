<?php include __DIR__ . '/../layout.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white text-center py-3">
                    <i class="bi bi-shield-lock fs-3"></i>
                    <h5 class="mb-0 mt-1">Acceso restringido</h5>
                    <small class="text-white-50">Ingresa tu correo para recibir un código de acceso</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="index.php?route=auth/send-otp">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" class="form-control form-control-lg"
                                   id="email" name="email"
                                   placeholder="tu@correo.com"
                                   autofocus required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">
                                <i class="bi bi-send"></i> Enviar código
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center text-muted small">
                    Solo usuarios autorizados tienen acceso.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../helpers/cierre.php'; ?>
