<?php
/**
 * connections/conec6.php
 * Conexión MySQLi robusta + helper db()
 */
declare(strict_types=1);
date_default_timezone_set('America/Santiago');

$DB_HOST = getenv('DB_HOST') ?: '127.0.0.1';
$DB_PORT = (int)(getenv('DB_PORT') ?: 3306);
$DB_NAME = getenv('DB_NAME') ?: 'cat6852_hotel_tickets';
$DB_USER = getenv('DB_USER') ?: 'cat6852_rje';
$DB_PASS = getenv('DB_PASS') ?: 'MM)[9&VYxl[W';

$mysqli = @new \mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) {
    throw new \RuntimeException('DB_CONNECT_FAILED: ('.$mysqli->connect_errno.') '.$mysqli->connect_error);
}
if (!$mysqli->set_charset('utf8mb4')) {
    throw new \RuntimeException('DB_SET_CHARSET_FAILED: '.$mysqli->error);
}

if (!isset($db)   || !($db   instanceof \mysqli)) $db   = $mysqli;
if (!isset($conn) || !($conn instanceof \mysqli)) $conn = $mysqli;

/** Devuelve una instancia válida de \mysqli o lanza excepción */
function db(): \mysqli {
    global $db, $mysqli, $conn;
    if ($db     instanceof \mysqli) return $db;
    if ($mysqli instanceof \mysqli) return $mysqli;
    if ($conn   instanceof \mysqli) return $conn;
    throw new \RuntimeException('No hay conexión MySQLi disponible (db(), conec6.php).');
}
