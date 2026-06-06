<?php
/**
 * Script para habilitar Soft Delete
 */
require_once '../config/config.php';
require_once '../app/core/Autoload.php';
require_once '../config/Database.php';

echo "<h3>Habilitando Soft Delete</h3>";

try {
    $db = Database::getConnection();
    
    // 1. Agregar columna deleted_at
    $db->exec("ALTER TABLE emp_users ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL AFTER status");

    echo "<p style='color: green;'>✅ Columna 'deleted_at' añadida con éxito.</p>";
    echo "<p><a href='../usuarios'>Volver a Usuarios</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
