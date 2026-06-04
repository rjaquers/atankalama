<!--
  = Proyecto: Starter Kit RKM =
  = Autor: Rodrigo Jaque Escobar                    =
  = Contacto: rjaquers@gmail.com.                   =
  = Fecha: <?= date('Y') ?>                  =

-->
<?php
// Autoload simple sin Composer (PHP 7.4+)

spl_autoload_register(function ($class) {

    $paths = [
        APP_PATH . "/core/",
        APP_PATH . "/controllers/",
        APP_PATH . "/controllers/api/",
        APP_PATH . "/models/",
        APP_PATH . "/services/",
        APP_PATH . "/middleware/",
        APP_PATH . "/helpers/",
        APP_PATH . "/generators/",
        APP_PATH . "/reports/",
    ];

    foreach ($paths as $path) {
        $file = $path . $class . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
