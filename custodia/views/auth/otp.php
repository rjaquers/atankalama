<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación — SISColaciones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600&family=Playfair+Display+SC:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* ── Tokens (idénticos a login.php) ── */
        :root {
            --color-primary:      #0D9488;
            --color-primary-dark: #0F766E;
            --color-primary-light:#CCFBF1;
            --color-accent:       #EA580C;
            --color-bg:           #F0FDFA;
            --color-card:         #FFFFFF;
            --color-text:         #134E4A;
            --color-muted:        #64748B;
            --color-border:       #99F6E4;
            --color-error-bg:     #FEF2F2;
            --color-error-text:   #991B1B;
            --color-error-border: #FECACA;
            --radius:             12px;
            --shadow:             0 4px 24px rgba(13,148,136,.10);
            --transition:         150ms ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Karla', system-ui, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            background: var(--color-bg);
            color: var(--color-text);
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(13,148,136,.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(234,88,12,.06) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 1;
            background: var(--color-card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--color-border);
            width: 100%;
            max-width: 420px;
            padding: 40px 36px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .app-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: var(--color-primary);
            border-radius: 16px;
            margin-bottom: 16px;
        }

        .app-icon svg {
            width: 28px;
            height: 28px;
            color: #fff;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .card-title {
            font-family: 'Playfair Display SC', serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--color-text);
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--color-muted);
            margin-top: 6px;
            line-height: 1.5;
        }

        /* Pasos de progreso */
        .steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
        }

        .step-dot {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .step-dot.done {
            background: var(--color-primary);
            color: #fff;
        }

        .step-dot.active {
            background: var(--color-primary-light);
            color: var(--color-primary);
            border: 2px solid var(--color-primary);
        }

        .step-label {
            font-size: 11px;
            color: var(--color-muted);
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: var(--color-border);
            margin: 0 8px;
            margin-bottom: 16px;
            max-width: 60px;
        }

        /* Alerta */
        .alert {
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            margin-top: 1px;
        }

        .alert-error {
            background: var(--color-error-bg);
            color: var(--color-error-text);
            border: 1px solid var(--color-error-border);
        }

        /* Campo OTP */
        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 8px;
        }

        .otp-hint {
            font-size: 13px;
            color: var(--color-muted);
            margin-top: 6px;
        }

        input[type="text"]#code {
            width: 100%;
            height: 64px;
            padding: 0 20px;
            border: 2px solid var(--color-border);
            border-radius: 10px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: .35em;
            text-align: center;
            color: var(--color-text);
            background: #fff;
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
            caret-color: var(--color-primary);
        }

        input[type="text"]#code:focus {
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(13,148,136,.15);
        }

        /* Botón */
        .btn-primary {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 48px;
            background: var(--color-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--transition), transform var(--transition), opacity var(--transition);
        }

        .btn-primary:hover { background: var(--color-primary-dark); }
        .btn-primary:active { transform: scale(.98); }
        .btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }

        .btn-primary svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        /* Acciones secundarias */
        .secondary-actions {
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            font-size: 14px;
        }

        .secondary-actions a {
            color: var(--color-primary);
            text-decoration: none;
            font-weight: 500;
            transition: color var(--transition);
        }

        .secondary-actions a:hover { color: var(--color-primary-dark); text-decoration: underline; }

        .secondary-actions .sep {
            color: var(--color-border);
            font-size: 18px;
            line-height: 1;
        }

        .secondary-actions .cancel {
            color: var(--color-muted);
        }

        /* Temporizador */
        .timer {
            text-align: center;
            font-size: 13px;
            color: var(--color-muted);
            margin-top: 12px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
</head>
<body>

<div class="card" role="main">
    <div class="card-header">
        <div class="app-icon" aria-hidden="true">
            <!-- Icono: escudo / verificación -->
            <svg viewBox="0 0 24 24">
                <path d="M12 2l7 4v6c0 4.41-3.13 8.54-7 9-3.87-.46-7-4.59-7-9V6l7-4z"/>
                <polyline points="9 12 11 14 15 10"/>
            </svg>
        </div>
        <h1 class="card-title">Verificación</h1>
        <p class="card-subtitle">
            Ingresa el código de 6 dígitos<br>enviado a tu correo
        </p>
    </div>

    <!-- Progreso del flujo -->
    <div class="steps" aria-label="Paso 2 de 2">
        <div class="step">
            <div class="step-dot done" aria-hidden="true">✓</div>
            <span class="step-label">Correo</span>
        </div>
        <div class="step-line" aria-hidden="true"></div>
        <div class="step">
            <div class="step-dot active" aria-hidden="true">2</div>
            <span class="step-label">Código</span>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert" aria-live="polite">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <form id="otpForm" method="POST" action="<?= url('/auth/otp') ?>" novalidate>
        <div class="field">
            <label for="code">Código de verificación</label>
            <input
                type="text"
                id="code"
                name="code"
                maxlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="000000"
                required
                autofocus
            >
            <p class="otp-hint">El código expira en 10 minutos.</p>
        </div>

        <button type="submit" class="btn-primary" id="submitBtn">
            <span class="spinner" id="spinner" aria-hidden="true"></span>
            <svg id="btnIcon" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span id="btnText">Verificar acceso</span>
        </button>
    </form>

    <div class="secondary-actions">
        <a href="<?= url('/auth/otp/reenviar') ?>">Reenviar código</a>
        <span class="sep" aria-hidden="true">·</span>
        <a href="<?= url('/auth/logout') ?>" class="cancel">Cancelar</a>
    </div>

    <div class="timer" id="timer" aria-live="polite"></div>
</div>

<script>
    (function () {
        // Auto-submit al ingresar 6 dígitos
        var input  = document.getElementById('code');
        var form   = document.getElementById('otpForm');
        var btn    = document.getElementById('submitBtn');
        var spinner = document.getElementById('spinner');
        var icon   = document.getElementById('btnIcon');
        var text   = document.getElementById('btnText');

        // Solo permitir dígitos
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            if (this.value.length === 6) {
                triggerSubmit();
            }
        });

        form.addEventListener('submit', function () {
            triggerSubmit();
        });

        function triggerSubmit() {
            if (btn.disabled) return;
            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display    = 'none';
            text.textContent      = 'Verificando…';
            form.submit();
        }

        // Temporizador regresivo de 10 minutos
        var timerEl = document.getElementById('timer');
        var seconds = 600;

        function updateTimer() {
            var m = Math.floor(seconds / 60);
            var s = seconds % 60;
            timerEl.textContent = 'Expira en ' + m + ':' + (s < 10 ? '0' : '') + s;
            if (seconds <= 0) {
                timerEl.textContent = 'El código expiró. Solicita uno nuevo.';
                clearInterval(interval);
            }
            seconds--;
        }

        updateTimer();
        var interval = setInterval(updateTimer, 1000);
    })();
</script>
</body>
</html>
