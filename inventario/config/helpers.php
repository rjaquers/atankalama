<?php

function isLoggedIn() {
    return true;
}

function isAdmin() {
    return true;
}

function requireLogin() {
    // Login system removed
    return;
}

function requireAdmin() {
    // Admin system removed
    return;
}

function redirect($page) {
    header("Location: index.php?page=" . $page);
    exit();
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function showAlert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>';
}

/**
 * Guarda eventos de autenticación en /logs/auth.log
 * @param string $message  Mensaje a registrar
 * @param string $level    Nivel del evento: INFO, WARNING, ERROR
 */
function authLog($message, $level = 'INFO')
{
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/auth.log';

    // Crear carpeta si no existe
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $date = date('Y-m-d H:i:s');
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $entry = "[$date] [$level] [IP:$ip] $message" . PHP_EOL;

    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}


?>