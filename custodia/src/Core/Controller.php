<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Controlador base de la aplicación.
 *
 * - Expone $this->db (mysqli) para los controladores hijos.
 * - Ofrece helpers sencillos para renderizar vistas y devolver JSON.
 *
 * Mantiene compatibilidad con el código actual:
 * si no se inyecta explícitamente la conexión, intenta usar la función global db()
 * definida en connections/conec6.php.
 */
abstract class Controller
{
    /** @var \mysqli */
    protected \mysqli $db;

    public function __construct(?\mysqli $db = null)
    {
        if ($db instanceof \mysqli) {
            $this->db = $db;
            return;
        }

        if (function_exists('db')) {
            /** @var \mysqli $conn */
            $conn = db();
            $this->db = $conn;
            return;
        }

        throw new \RuntimeException('No se pudo obtener la conexión MySQLi en Controller.');
    }

    /**
     * Renderiza una vista PHP.
     *
     * @param string $view  Ruta relativa dentro de /views, sin ".php" (ej: "colaciones/lote_index")
     * @param array  $data  Variables a extraer dentro de la vista
     */
    protected function view(string $view, array $data = []): void
    {
        if (!empty($data)) {
            extract($data, EXTR_SKIP);
        }

        $basePath = \defined('ROOT_PATH')
            ? ROOT_PATH
            : \dirname(__DIR__, 2);

        $file = $basePath . '/views/' . $view . '.php';

        if (!\is_file($file)) {
            throw new \RuntimeException('Vista no encontrada: ' . $file);
        }

        require $file;
    }

    /**
     * Envía una respuesta JSON estandarizada.
     *
     * @param array $payload
     * @param int   $status
     */
    protected function json(array $payload, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }

        echo json_encode($payload);
    }
}

