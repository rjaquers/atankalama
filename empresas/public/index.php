<?php
/**
 * Entry Point - Atankalama Empresas
 */
require_once '../config/config.php';
require_once '../app/core/Autoload.php';

// Definir constantes de rutas
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('VIEW_PATH', APP_PATH . '/views');

$router = new Router();
$router->dispatch();
