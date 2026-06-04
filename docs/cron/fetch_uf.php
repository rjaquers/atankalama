<?php
/**
 * Script para obtener el valor de la UF del día.
 * Debe ejecutarse vía Cron diariamente.
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/app/core/Model.php';

class UfFetcher extends Model
{
    public function fetchAndStore()
    {
        $apiUrl = 'https://mindicador.cl/api/uf';
        
        $json = @file_get_contents($apiUrl);
        if (!$json) {
            echo "Error: No se pudo conectar con la API de UF.\n";
            return false;
        }

        $data = json_decode($json);
        if (!$data || !isset($data->serie[0])) {
            echo "Error: Respuesta de API inválida.\n";
            return false;
        }

        $todayVal = $data->serie[0]->valor;
        $todayDate = date('Y-m-d', strtotime($data->serie[0]->fecha));

        $stmt = $this->conn->prepare("INSERT INTO doc_uf_values (date, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->bind_param("sdd", $todayDate, $todayVal, $todayVal);
        
        if ($stmt->execute()) {
            echo "UF registrada correctamente: $todayDate -> $todayVal\n";
            return true;
        } else {
            echo "Error al guardar en DB: " . $this->conn->error . "\n";
            return false;
        }
    }
}

$fetcher = new UfFetcher();
$fetcher->fetchAndStore();
