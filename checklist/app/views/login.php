<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checklist y Encuestas - Acceso</title>
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/favicon.png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/public/favicon.png">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #00d2ff 0%, #3a7bd5 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.2);
            --text-main: #2d3436;
            --text-muted: #636e72;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #0f172a;
            background-image: 
                radial-gradient(at 0% 0%, hsla(197, 100%, 49%, 0.15) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(215, 100%, 50%, 0.15) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(180, 100%, 50%, 0.1) 0, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .login-container {
            position: relative;
            z-index: 1;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            border-radius: 24px;
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-wrapper {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            margin-bottom: 1rem;
            filter: drop-shadow(0 10px 15px rgba(58, 123, 213, 0.3));
        }

        .brand-name {
            font-weight: 600;
            font-size: 1.75rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .brand-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 400;
        }

        h4 {
            color: var(--text-main);
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-main);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e2e8f0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: #3a7bd5;
            box-shadow: 0 0 0 4px rgba(58, 123, 213, 0.1);
        }

        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(58, 123, 213, 0.4);
            background: var(--primary-gradient);
        }

        .btn-success {
            background: linear-gradient(135deg, #00b09b 0%, #96c93d 100%);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .footer-logo {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0.3;
            transition: opacity 0.3s ease;
        }

        .footer-logo:hover {
            opacity: 1;
        }

        .footer-logo img {
            max-width: 100px;
            filter: grayscale(100%) invert(100%);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-wrapper">
                <div class="logo-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 3H14.82C14.4 1.84 13.3 1 12 1C10.7 1 9.6 1.84 9.18 3H5C3.9 3 3 3.9 3 5V19C3 20.1 3.9 21 5 21H19C20.1 21 21 20.1 21 19V5C21 3.9 20.1 3 19 3ZM12 3C12.55 3 13 3.45 13 4C13 4.55 12.55 5 12 5C11.45 5 11 4.55 11 4C11 3.45 11.45 3 12 3ZM11 17L7 13L8.41 11.59L11 14.17L15.59 9.58L17 11L11 17Z" fill="url(#paint0_linear)"/>
                        <defs>
                            <linearGradient id="paint0_linear" x1="3" y1="1" x2="21" y2="21" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#00D2FF"/>
                                <stop offset="1" stop-color="#3A7BD5"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="brand-name">Checklist</div>
                <div class="brand-subtitle">y Encuestas</div>
            </div>

            <h4 class="text-center">Iniciar Sesión</h4>

            <div id="emailSection">
                <div class="mb-4">
                    <label class="form-label">Correo Corporativo</label>
                    <input type="email" id="email" class="form-control" placeholder="usuario<?= ALLOWED_DOMAIN ?>" required autocomplete="email">
                </div>
                <button class="btn btn-primary w-100" onclick="requestOTP()">
                    Enviar Código OTP <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>

            <div id="otpSection" class="d-none">
                <div class="alert alert-info py-2 small">
                    <i class="bi bi-info-circle me-2"></i> Código enviado a su correo.
                </div>
                <div class="mb-4 text-center">
                    <label class="form-label d-block text-start">Código de 6 dígitos</label>
                    <input type="text" id="otp" class="form-control text-center fs-3 fw-bold" maxlength="6" pattern="[0-9]*" inputmode="numeric">
                </div>
                <button class="btn btn-success w-100 mb-3" onclick="verifyOTP()">
                    Verificar e Ingresar
                </button>
                <button class="btn btn-link w-100 btn-sm text-decoration-none text-muted" onclick="location.reload()">
                    <i class="bi bi-arrow-left me-1"></i> Cambiar correo
                </button>
            </div>
        </div>

        <div class="footer-logo">
            <img src="<?= BASE_URL ?>/public/assets/img/logo.png" alt="Atankalama Logo" title="Plataforma Atankalama">
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';

        async function requestOTP() {
            const email = document.getElementById('email').value;
            if (!email) {
                alert("Por favor ingrese su correo");
                return;
            }

            const btn = document.querySelector('#emailSection button');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';

            try {
                const res = await fetch(`${BASE_URL}/login/request`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}`
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    btn.disabled = false;
                    btn.innerHTML = 'Enviar Código OTP <i class="bi bi-arrow-right ms-2"></i>';
                    alert("Error en el servidor. Verifique la base de datos.");
                    return;
                }

                if (res.ok) {
                    document.getElementById('emailSection').classList.add('d-none');
                    document.getElementById('otpSection').classList.remove('d-none');
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Enviar Código OTP <i class="bi bi-arrow-right ms-2"></i>';
                    alert(data.error || 'Error desconocido');
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = 'Enviar Código OTP <i class="bi bi-arrow-right ms-2"></i>';
                alert("Error de conexión");
            }
        }

        async function verifyOTP() {
            const email = document.getElementById('email').value;
            const otp = document.getElementById('otp').value;

            if (otp.length < 6) {
                alert("Ingrese el código completo");
                return;
            }

            const btn = document.querySelector('#otpSection .btn-success');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Verificando...';

            try {
                const res = await fetch(`${BASE_URL}/login/verify`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}`
                });
                const data = await res.json();
                if (data.redirect) {
                    window.location.href = data.redirect.startsWith('http') ? data.redirect : BASE_URL + data.redirect;
                } else {
                    btn.disabled = false;
                    btn.innerHTML = 'Verificar e Ingresar';
                    alert(data.error || 'Código incorrecto');
                }
            } catch (err) {
                btn.disabled = false;
                btn.innerHTML = 'Verificar e Ingresar';
                alert("Error de conexión");
            }
        }
    </script>
</body>

</html>