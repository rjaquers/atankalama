<?php
/**
 * Diagnóstico de Librerías para Excel
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Diagnóstico de Librerías</h3>";

// Rutas a probar
$paths = [
    'Cocina Vendor' => dirname(dirname(dirname(dirname(__FILE__)))) . '/cocina/vendor/autoload.php',
    'Docs Vendor' => dirname(dirname(dirname(dirname(__FILE__)))) . '/docs/vendor/autoload.php',
    'Admin Vendor' => dirname(dirname(dirname(dirname(__FILE__)))) . '/admin/vendor/autoload.php',
];

foreach ($paths as $name => $path) {
    echo "<b>Probando $name:</b> $path <br>";
    if (file_exists($path)) {
        echo "✅ Existe. Cargando...<br>";
        require_once $path;
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            echo "⭐ <b>¡ÉXITO! PhpSpreadsheet cargado correctamente desde $name.</b><br>";
        } else {
            echo "❌ Cargado, pero la clase Spreadsheet no existe.<br>";
        }
    } else {
        echo "❌ No existe.<br>";
    }
    echo "<hr>";
}
