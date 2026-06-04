<?php
/**
 * Migración: Agregar columna respuesta_foto a chk_evaluacion_respuestas
 *
 * Ejecutar una sola vez:
 *   php scripts/migrate_foto.php
 *
 * O acceder via navegador (solo en desarrollo):
 *   http://checklist.local:8080/scripts/migrate_foto.php
 */

require_once __DIR__ . '/../config/config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Verificar si la columna ya existe
    $stmt = $pdo->query("SHOW COLUMNS FROM chk_evaluacion_respuestas LIKE 'respuesta_foto'");
    if ($stmt->rowCount() > 0) {
        echo "OK: La columna 'respuesta_foto' ya existe. No se requiere migración.\n";
        exit(0);
    }

    $pdo->exec("ALTER TABLE chk_evaluacion_respuestas ADD COLUMN respuesta_foto TEXT NULL DEFAULT NULL");
    echo "OK: Columna 'respuesta_foto' agregada correctamente a chk_evaluacion_respuestas.\n";

} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
