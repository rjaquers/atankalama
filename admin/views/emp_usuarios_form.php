<?php include 'layout.php'; ?>

<div class="container mt-4" style="max-width:640px">

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="index.php?route=emp/usuarios/list&company=<?= $empresa['id'] ?>"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Nuevo usuario</h4>
            <small class="text-muted"><?= htmlspecialchars($empresa['business_name']) ?></small>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="index.php?route=emp/usuarios/store" id="formNuevoUsuario">
                <input type="hidden" name="company_id" value="<?= $empresa['id'] ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Ej: Juan Pérez" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo electrónico <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="usuario@empresa.com" required>
                </div>

                <!-- ── Contraseña con generador ── -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Contraseña inicial <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="password" name="password" id="inputPassword"
                               class="form-control font-monospace"
                               placeholder="Escribe o genera una contraseña segura"
                               required minlength="8" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" id="btnToggle"
                                title="Mostrar / ocultar contraseña">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="btnGenerar">
                            <i class="bi bi-arrow-clockwise me-1"></i>Generar
                        </button>
                    </div>

                    <!-- Alerta de copia — oculta hasta que se genere una contraseña -->
                    <div id="alertaCopiar" class="alert alert-warning d-flex align-items-start gap-2 py-2 mt-2 mb-0 d-none">
                        <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                        <div>
                            <strong>Copia la contraseña antes de guardar.</strong>
                            Una vez creado el usuario no podrás recuperarla.
                            <button type="button" class="btn btn-sm btn-warning ms-2 py-0" id="btnCopiar">
                                <i class="bi bi-clipboard me-1"></i>Copiar
                            </button>
                        </div>
                    </div>

                    <div class="form-text">El usuario podrá cambiarla desde la app.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Rol</label>
                    <select name="role" class="form-select">
                        <option value="visor">Visor — solo consultar información</option>
                        <option value="admin">Admin — puede gestionar usuarios de la empresa</option>
                    </select>
                </div>

                <!-- ── Comentarios / Notas internas ── -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-chat-left-text me-1 text-secondary"></i>Comentarios internos
                        <span class="text-muted fw-normal">(opcional)</span>
                    </label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Ej: Contacto principal de faena Norte. Reemplaza a Pedro García."
                              style="resize:vertical;"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
                    <div class="form-text">Visible solo para administradores. No se muestra al usuario.</div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php?route=emp/usuarios/list&company=<?= $empresa['id'] ?>"
                       class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-person-check-fill me-1"></i>Crear usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    // ── Generador de contraseña segura ───────────────────────────
    function generarPassword() {
        const upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';   // sin I, O
        const lower   = 'abcdefghjkmnpqrstuvwxyz';    // sin i, l, o
        const digits  = '23456789';                    // sin 0, 1
        const symbols = '@#$%&*!';
        const all     = upper + lower + digits + symbols;

        const len = 14;
        const arr = new Uint8Array(len);
        crypto.getRandomValues(arr);

        const pwd = [
            upper[arr[0]  % upper.length],
            upper[arr[1]  % upper.length],
            lower[arr[2]  % lower.length],
            lower[arr[3]  % lower.length],
            digits[arr[4] % digits.length],
            digits[arr[5] % digits.length],
            symbols[arr[6] % symbols.length],
        ];
        for (let i = 7; i < len; i++) pwd.push(all[arr[i] % all.length]);

        // Fisher-Yates shuffle
        const rnd = new Uint32Array(pwd.length);
        crypto.getRandomValues(rnd);
        for (let i = pwd.length - 1; i > 0; i--) {
            const j = rnd[i] % (i + 1);
            [pwd[i], pwd[j]] = [pwd[j], pwd[i]];
        }
        return pwd.join('');
    }

    const input        = document.getElementById('inputPassword');
    const btnToggle    = document.getElementById('btnToggle');
    const btnGenerar   = document.getElementById('btnGenerar');
    const btnCopiar    = document.getElementById('btnCopiar');
    const eyeIcon      = document.getElementById('eyeIcon');
    const alertaCopiar = document.getElementById('alertaCopiar');

    // Generar contraseña
    btnGenerar.addEventListener('click', function () {
        input.value = generarPassword();
        input.type  = 'text';
        eyeIcon.className = 'bi bi-eye-slash';
        alertaCopiar.classList.remove('d-none');
        input.classList.add('border-warning');
    });

    // Mostrar / ocultar
    btnToggle.addEventListener('click', function () {
        const visible = input.type === 'text';
        input.type        = visible ? 'password' : 'text';
        eyeIcon.className = visible ? 'bi bi-eye' : 'bi bi-eye-slash';
    });

    // Copiar al portapapeles
    btnCopiar.addEventListener('click', function () {
        navigator.clipboard.writeText(input.value).then(() => {
            const orig = btnCopiar.innerHTML;
            btnCopiar.innerHTML = '<i class="bi bi-check2 me-1"></i>¡Copiada!';
            btnCopiar.classList.replace('btn-warning', 'btn-success');
            setTimeout(() => {
                btnCopiar.innerHTML = orig;
                btnCopiar.classList.replace('btn-success', 'btn-warning');
            }, 2000);
        });
    });

    // Advertir si el formulario se envía sin haber copiado
    document.getElementById('formNuevoUsuario').addEventListener('submit', function (e) {
        if (!alertaCopiar.classList.contains('d-none')) {
            // La alerta está visible → hay contraseña generada
            // Solo un recordatorio si el usuario no copió
        }
    });
})();
</script>

<?php include '../helpers/cierre.php'; ?>
