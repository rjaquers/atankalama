<?php
/**
 * Autoload - Atankalama Empresas
 */
spl_autoload_register(function ($class) {
    $paths = [
        '../app/core/',
        '../app/controllers/',
        '../app/models/',
        '../app/helpers/',
        '../config/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
