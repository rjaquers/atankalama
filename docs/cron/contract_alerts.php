<?php
/**
 * CRON Script: Notificaciones de Vencimiento de Contratos
 * Hotel Atankalama – Sistema de Contratos
 * 
 * Se recomienda ejecutar diaramente: 
 * cron/contract_alerts.php
 */

// 1. Cargar entorno y constantes
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/core/Autoload.php';

// 2. Traer configuración de alertas (primer registro activo)
$alertConfig = (new AlertConfigModel())->getLatestActive();
if (!$alertConfig || !$alertConfig['email_enabled']) {
    die("Notificaciones desactivadas o sin configuración.");
}

$daysBefore = (int)$alertConfig['days_before']; // Ej: 30 días
$recipients = explode(',', $alertConfig['email_recipients']); // Lista extra de emails

// 3. Obtener contratos próximos a vencer
$contractModel = new ContractModel();
$expiring = $contractModel->getExpiringContracts($daysBefore);

if (empty($expiring)) {
    echo "[" . date('Y-m-d H:i:s') . "] No hay contratos próximos a vencer en los próximos {$daysBefore} días.\n";
    exit;
}

$mailService = new MailService();

foreach ($expiring as $c) {
    // Evitar enviar repetidamente (opcional: podrías registrar en doc_notificaciones si existiera la tabla)
    // Usaremos el historial para que quede constancia
    
    $subject = "⚠️ ALERTA: Contrato #{$c['code']} - {$c['business_name']} próximo a vencer";
    
    $body = "<h2>Aviso de Vencimiento de Contrato</h2>";
    $body .= "<p>El contrato <strong>{$c['code']}</strong> con la empresa <strong>{$c['business_name']}</strong> está próximo a vencer.</p>";
    $body .= "<ul>";
    $body .= "<li><strong>Fecha Vencimiento:</strong> " . date('d/m/Y', strtotime($c['end_date'])) . "</li>";
    $body .= "<li><strong>Monto Base:</strong> $" . number_format($c['base_amount'], 0, ',', '.') . "</li>";
    $body .= "<li><strong>Estado Actual:</strong> {$c['status']}</li>";
    $body .= "</ul>";
    $body .= "<p><a href='" . BASE_URL . "/contracts/show/{$c['id']}'>Ver detalle del contrato</a></p>";
    $body .= "<hr><small>Este es un correo automático generado por el Sistema de Contratos Atankalama.</small>";

    // Enviar a destinatarios adicionales
    foreach($recipients as $email) {
        $mailService->send(trim($email), $subject, $body);
    }
    
    // Registrar en el historial que se enviaron las alertas
    (new ContractHistoryModel())->add(
        $c['id'], 
        null, // null = Sistema
        'notificación_enviada', 
        "Se enviaron notificaciones de alerta de vencimiento ({$daysBefore} días de anticipación)"
    );
    
    echo "[" . date('Y-m-d H:i:s') . "] Notificación enviada para contrato {$c['code']} ({$c['business_name']})\n";
}

echo "Proceso finalizado.\n";
