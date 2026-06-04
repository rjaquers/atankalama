<?php
/**
 * Cargador simple de archivos .env para entornos sin Composer.
 */
function loadEnv($path)
{
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Dividir por el primer signo =
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);

            // Quitar comillas si existen
            $value = trim($value, '"\'');

            // Definir como constante si no existe y poner en $_ENV
            if (!defined($key)) {
                define($key, $value);
            }
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
    return true;
}
