<?php
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../app/core/Autoload.php";

$module = $argv[1] ?? null;
if (!$module) {
    echo "Uso: php tools/build_crud.php <modulo>\n";
    exit(1);
}

$builder = new CrudBuilder();
if ($builder->build($module)) {
    echo "OK: módulo generado -> $module\n";
} else {
    echo "ERROR: no se pudo generar\n";
    exit(1);
}
