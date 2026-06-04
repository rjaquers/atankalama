<?php
/**
 * Script para ejecutar el reporte diario desde Cron.
 * Uso: php /ruta/al/proyecto/app/cron/reporte_diario.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/CocinaController.php';

echo "Iniciando envío de reporte diario vía CLI...\n";

$controller = new CocinaController();
$controller->enviarReporteDiario();

echo "Ejecución finalizada.\n";
