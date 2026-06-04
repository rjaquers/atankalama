<?php
namespace App\Core;

class Logger
{
    private $db;
    private $logFile;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logFile = __DIR__ . '/../../logs/system.log';
    }

    public function log($nivel, $modulo, $mensaje, $contexto = [], $user_email = null)
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $contexto_json = json_encode($contexto);

        // 1. Guardar en Base de Datos
        try {
            $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "system_logs (nivel, modulo, mensaje, contexto_json, user_email, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nivel, $modulo, $mensaje, $contexto_json, $user_email, $ip]);
        } catch (\Exception $e) {
            // Si falla la DB, al menos intentamos el archivo
        }

        // 2. Guardar en Archivo
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$nivel] [$modulo] $mensaje | Context: $contexto_json | IP: $ip | User: $user_email" . PHP_EOL;

        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        if (is_writable($logDir) || (!file_exists($this->logFile) && is_writable($logDir)) || (file_exists($this->logFile) && is_writable($this->logFile))) {
            @file_put_contents($this->logFile, $logEntry, FILE_APPEND);
        }
    }

    public static function info($modulo, $mensaje, $contexto = [], $user_email = null)
    {
        (new self())->log('INFO', $modulo, $mensaje, $contexto, $user_email);
    }

    public static function error($modulo, $mensaje, $contexto = [], $user_email = null)
    {
        (new self())->log('ERROR', $modulo, $mensaje, $contexto, $user_email);
    }

    public static function warning($modulo, $mensaje, $contexto = [], $user_email = null)
    {
        (new self())->log('WARNING', $modulo, $mensaje, $contexto, $user_email);
    }

    public static function security($modulo, $mensaje, $contexto = [], $user_email = null)
    {
        (new self())->log('SECURITY', $modulo, $mensaje, $contexto, $user_email);
    }
}
