<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso — SISColaciones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600&family=Playfair+Display+SC:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* ── Tokens ── */
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
            --color-info-bg:      #F0FDFA;
            --color-info-text:    #0F766E;
            --color-info-border:  #99F6E4;
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

        /* Patrón de fondo sutil */
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

        /* Cabecera */
        .card-header {
            text-align: center;
            margin-bottom: 32px;
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
            letter-spacing: .02em;
        }

        .card-subtitle {
            font-size: 14px;
            color: var(--color-muted);
            margin-top: 4px;
        }

        /* Alertas */
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

        .alert-info {
            background: var(--color-info-bg);
            color: var(--color-info-text);
            border: 1px solid var(--color-info-border);
        }

        /* Formulario */
        .field {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 6px;
        }

        input[type="email"] {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            border: 1.5px solid var(--color-border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 16px;
            color: var(--color-text);
            background: #fff;
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }

        input[type="email"]::placeholder { color: var(--color-muted); font-size: 14px; }

        input[type="email"]:focus {
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

        /* Pie */
        .card-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 13px;
            color: var(--color-muted);
        }

        /* Reducir movimiento */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
</head>
<body>

<div class="card" role="main">
    <div class="card-header">
        <div class="app-icon" aria-hidden="true">
            <!-- Icono: ticket/voucher -->
            <svg viewBox="0 0 24 24">
                <path d="M2 9a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v2a2 2 0 0 0 0 4v2a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-2a2 2 0 0 0 0-4V9z"/>
                <line x1="9" y1="8" x2="9" y2="16"/>
            </svg>
        </div>
        <h1 class="card-title">SISColaciones</h1>
        <p class="card-subtitle">Sistema de gestión de colaciones</p>
    </div>

    <?php if (!empty($error)): ?>
        <?php $isInfo = (strpos($error, 'recibirás') !== false); ?>
        <div class="alert <?= $isInfo ? 'alert-info' : 'alert-error' ?>" role="alert" aria-live="polite">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <?php if ($isInfo): ?>
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                <?php else: ?>
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                <?php endif; ?>
            </svg>
            <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="<?= url('/auth/login') ?>" novalidate>
        <div class="field">
            <label for="email">Correo electrónico</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="tucorreo@atankalama.com"
                autocomplete="email"
                inputmode="email"
                required
                autofocus
            >
        </div>

        <button type="submit" class="btn-primary" id="submitBtn">
            <span class="spinner" id="spinner" aria-hidden="true"></span>
            <svg id="btnIcon" viewBox="0 0 24 24">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
            </svg>
            <span id="btnText">Enviar código</span>
        </button>
    </form>

    <div class="card-footer">
        Solo personal autorizado de Atankalama
    </div>
</div>

<script>
    (function () {
        var form   = document.getElementById('loginForm');
        var btn    = document.getElementById('submitBtn');
        var spinner = document.getElementById('spinner');
        var icon   = document.getElementById('btnIcon');
        var text   = document.getElementById('btnText');

        form.addEventListener('submit', function (e) {
            var email = document.getElementById('email').value.trim();
            if (!email) { e.preventDefault(); return; }

            // Estado loading
            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display    = 'none';
            text.textContent      = 'Enviando…';
        });
    })();
</script>
</body>
</html>
