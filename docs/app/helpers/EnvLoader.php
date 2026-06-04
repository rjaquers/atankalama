<?php
/**
 * ===================================================
 * Helper: EnvLoader
 * Proyecto: Hotel Atankalama – Sistema de Contratos
 * PHP: 7.4 compatible
 * ===================================================
 *
 * Responsabilidad:
 * Parsea el archivo .env y define las variables como
 * constantes PHP accesibles en todo el sistema.
 * Permite centralizar la configuración en un solo archivo
 * (.env) eliminando la duplicación con config.php.
 *
 * Uso:
 *   EnvLoader::load(__DIR__ . '/../../.env');
 *   echo DB_HOST; // valor del .env
 */
class EnvLoader
{
    /**
     * Carga y parsea un archivo .env definiendo sus valores como constantes PHP.
     *
     * Qué hace:
     * - Lee el archivo línea por línea
     * - Ignora líneas vacías y comentarios (#)
     * - Separa clave=valor y limpia comillas
     * - Define cada par como constante PHP (si no existe ya)
     * - También almacena en $_ENV y putenv() para compatibilidad
     *
     * @param  string $path Ruta absoluta al archivo .env
     * @return void
     *
     * @throws RuntimeException Si el archivo .env no existe
     */
    public static function load($path)
    {
        // ===============================
        // VALIDAR QUE EL ARCHIVO EXISTE
        // ===============================
        if (!file_exists($path)) {
            throw new RuntimeException("Archivo .env no encontrado: " . $path);
        }

        // ===============================
        // LEER Y PARSEAR LÍNEA POR LÍNEA
        // ===============================
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // ----------------------------
            // Ignorar comentarios y líneas vacías
            // ----------------------------
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            // ----------------------------
            // Separar clave = valor
            // ----------------------------
            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key   = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // ----------------------------
            // Limpiar comillas (simples o dobles)
            // ----------------------------
            $value = self::cleanQuotes($value);

            // ----------------------------
            // Definir como constante si no existe
            // ----------------------------
            if (!defined($key)) {
                define($key, $value);
            }

            // ----------------------------
            // También almacenar en $_ENV y putenv
            // ----------------------------
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
    // Fin de la función load()

    /**
     * Limpia comillas simples o dobles de un valor.
     *
     * @param  string $value Valor a limpiar
     * @return string Valor sin comillas envolventes
     */
    private static function cleanQuotes($value)
    {
        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last  = $value[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                return substr($value, 1, $len - 2);
            }
        }
        return $value;
    }
    // Fin de la función cleanQuotes()

    /**
     * Obtiene el valor de una variable de entorno.
     *
     * Busca primero en las constantes definidas, luego en $_ENV,
     * y finalmente en getenv(). Retorna el valor por defecto si
     * no se encuentra en ninguno.
     *
     * @param  string $key     Nombre de la variable
     * @param  mixed  $default Valor por defecto si no existe
     * @return mixed  Valor de la variable o el default
     */
    public static function get($key, $default = null)
    {
        if (defined($key)) {
            return constant($key);
        }
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        $env = getenv($key);
        return $env !== false ? $env : $default;
    }
    // Fin de la función get()
}
