<?php
require_once 'config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 🔧 Desactivar ONLY_FULL_GROUP_BY solo para esta sesión
            $this->pdo->exec("SET SESSION sql_mode = (SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''))");

        } catch (PDOException $e) {
            throw new \RuntimeException('Error de conexión BD: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance->pdo; // devuelve instancia PDO
    }
}

// Conexión a la BD compartida (cat6852_hotel_tickets) para empresas y contratos.
// En producción reutiliza acceso_pdo() del archivo compartido para no duplicar credenciales.
class TicketsDatabase {
    private static $pdo = null;

    public static function getInstance(): PDO {
        if (self::$pdo === null) {
            $sharedFile = $_SERVER['DOCUMENT_ROOT'] . '/shared/acceso_db.php';
            if (file_exists($sharedFile)) {
                require_once $sharedFile;
                self::$pdo = acceso_pdo();
            } else {
                // Fallback local: credenciales de config.php
                try {
                    $dsn = 'mysql:host=' . TICKETS_DB_HOST . ';dbname=' . TICKETS_DB_NAME . ';charset=utf8mb4';
                    self::$pdo = new PDO($dsn, TICKETS_DB_USER, TICKETS_DB_PASS, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                } catch (PDOException $e) {
                    die('Error de conexión a BD tickets: ' . $e->getMessage());
                }
            }
        }
        return self::$pdo;
    }
}
