<?php
/**
 * Script de Emergencia - Crear tabla emp_project_assignments
 */
require_once '../config/config.php';
require_once '../app/core/Autoload.php';
require_once '../config/Database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Instalador de Módulo de Proyectos</h3>";

try {
    $db = Database::getConnection();
    
    $sql = "CREATE TABLE IF NOT EXISTS emp_project_assignments (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        company_id INT(10) UNSIGNED NOT NULL,
        project_id INT(10) UNSIGNED NOT NULL,
        employee_rut VARCHAR(20) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_assignment_rut (employee_rut),
        KEY idx_assignment_dates (start_date, end_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
    $db->exec($sql);

    echo "<p style='color: green;'>✅ ¡ÉXITO! La tabla 'emp_project_assignments' ha sido creada correctamente.</p>";
    echo "<p><a href='../alimentacion'>👉 Volver a Alimentación</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
