<?php
declare(strict_types=1);

/**
 * auth_require()
 * Verificar sesión activa usando AccesoBootstrap.
 * Llamar al inicio de cada ruta protegida.
 */
function auth_require(): void
{
    if (AccesoBootstrap::email() !== null) {
        return;
    }

    $redirect = ltrim(current_path(), '/');
    header(
        'Location: ' . rtrim(BASE_URL, '/') . '/index.php?route=auth/login'
        . ($redirect ? '&redirect=' . urlencode($redirect) : '')
    );
    exit;
}
