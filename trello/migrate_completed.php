<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $db = (new Database())->connect();
    
    // 1. Añadir columna completada si no existe
    $res = $db->query("SHOW COLUMNS FROM trell_tarjetas LIKE 'completada'");
    if ($res->num_rows === 0) {
        $db->query("ALTER TABLE trell_tarjetas ADD COLUMN completada TINYINT(1) DEFAULT 0 AFTER archivada");
        echo "Columna 'completada' añadida con éxito.\n";
    } else {
        echo "La columna 'completada' ya existe.\n";
    }

    // 2. Opcional: Marcar como completadas las tareas que ya están en listas "Listo", "Finalizado", etc.
    $db->query("UPDATE trell_tarjetas t 
                JOIN trell_listas l ON t.lista_id = l.id 
                SET t.completada = 1 
                WHERE l.nombre LIKE '%Listo%' 
                   OR l.nombre LIKE '%Completado%' 
                   OR l.nombre LIKE '%Finalizado%'
                   OR l.nombre LIKE '%Terminado%'");
    echo "Tareas existentes en columnas de finalización marcadas como completadas.\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
