<?php
/**
 * AccesoLog — Registro centralizado de accesos a las apps del hotel.
 *
 * Escribe una línea por login exitoso en /logs/accesos.log.
 * Todas las apps comparten el mismo archivo vía AccesoBootstrap.
 *
 * Formato de cada línea (pipe-separated):
 *   fecha | app_slug | app_nombre | email | ip | user_agent
 *
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 */
class AccesoLog
{
    private static function ruta(): string
    {
        return rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/logs/accesos.log';
    }

    /**
     * Registrar un ingreso exitoso.
     * Llamado automáticamente por AccesoBootstrap tras verificar OTP o TOTP.
     */
    public static function registrar(string $appSlug, string $appNombre, string $email): void
    {
        $fecha = date('Y-m-d H:i:s');
        $ip    = $_SERVER['REMOTE_ADDR']     ?? '-';
        $ua    = substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 250);

        // Sanitizar pipes en user_agent para no romper el formato
        $ua = str_replace('|', '/', $ua);

        $linea = implode('|', [$fecha, $appSlug, $appNombre, $email, $ip, $ua]) . PHP_EOL;

        $dir = dirname(self::ruta());
        if (!is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        file_put_contents(self::ruta(), $linea, FILE_APPEND | LOCK_EX);
    }

    /**
     * Leer el log y devolver array asociativo, más recientes primero.
     *
     * @param  int         $limite     Máximo de líneas a devolver
     * @param  string|null $filtroApp  Filtrar por app_slug exacto
     * @param  string|null $filtroEmail Filtrar por fragmento de email
     * @param  string|null $filtroFecha Filtrar por fecha YYYY-MM-DD
     * @return array<int, array{fecha:string, app_slug:string, app_nombre:string, email:string, ip:string, user_agent:string}>
     */
    public static function leer(
        int     $limite      = 1000,
        ?string $filtroApp   = null,
        ?string $filtroEmail = null,
        ?string $filtroFecha = null
    ): array {
        $ruta = self::ruta();
        if (!file_exists($ruta)) {
            return [];
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lineas === false || $lineas === []) {
            return [];
        }

        // Más recientes primero
        $lineas = array_reverse($lineas);

        $registros = [];
        foreach ($lineas as $linea) {
            $p = explode('|', $linea, 6);
            if (count($p) < 5) {
                continue;
            }

            $reg = [
                'fecha'      => $p[0],
                'app_slug'   => $p[1],
                'app_nombre' => $p[2],
                'email'      => $p[3],
                'ip'         => $p[4],
                'user_agent' => $p[5] ?? '',
            ];

            if ($filtroApp   && $reg['app_slug'] !== $filtroApp) {
                continue;
            }
            if ($filtroEmail && stripos($reg['email'], $filtroEmail) === false) {
                continue;
            }
            if ($filtroFecha && !str_starts_with($reg['fecha'], $filtroFecha)) {
                continue;
            }

            $registros[] = $reg;

            if (count($registros) >= $limite) {
                break;
            }
        }

        return $registros;
    }

    /** Lista de apps únicas presentes en el log (para el filtro). */
    public static function apps(): array
    {
        $ruta = self::ruta();
        if (!file_exists($ruta)) {
            return [];
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lineas) {
            return [];
        }

        $apps = [];
        foreach ($lineas as $linea) {
            $p = explode('|', $linea, 4);
            if (count($p) >= 3 && !isset($apps[$p[1]])) {
                $apps[$p[1]] = $p[2]; // slug => nombre
            }
        }

        asort($apps);
        return $apps;
    }
}
