<?php
/**
 * Servicio de Contratos.
 *
 * Orquestador de lógica de negocio para contratos: creación multi-entidad,
 * lógica de cambio de estados y cálculos de fechas/renovaciones.
 *
 * @package App\Services
 */
class ContractService
{
    /**
     * Crea un contrato completo con sus relaciones (hoteles, servicios, tiers).
     * 
     * @param array $data       Datos base del contrato
     * @param array $hotelIds   IDs de hoteles vinculados
     * @param array $serviceIds IDs de servicios vinculados
     * @param array $tiers      Datos de las escalas de precio
     * @param int   $userId     ID del usuario creador
     * @return int|bool         ID del contrato creado o false
     */
    public function createFullContract($data, $hotelIds, $serviceIds, $tiers, $userId)
    {
        $contractModel = new ContractModel();
        
        // 1. Crear el contrato (genera el código automático)
        $contractId = $contractModel->create($data, $userId);
        if (!$contractId) return false;

        // 2. Vincular Hoteles
        if (!empty($hotelIds)) {
            $contractModel->syncHotels($contractId, $hotelIds);
        }

        // 3. Vincular Servicios
        if (!empty($serviceIds)) {
            $contractModel->syncServices($contractId, $serviceIds);
        }

        // 4. Crear Tiers (solo si el modo es por_persona)
        if ($data['pricing_mode'] === 'por_persona' && !empty($tiers)) {
            $tiersModel = new ContractTierModel();
            $processedTiers = [];
            foreach ($tiers as $t) {
                if (!empty($t['min_guests']) && !empty($t['price_per_person'])) {
                    $processedTiers[] = [
                        'min_guests'       => (int)$t['min_guests'],
                        'max_guests'       => !empty($t['max_guests']) ? (int)$t['max_guests'] : null,
                        'price_per_person' => (float)$t['price_per_person'],
                        'discount_percent' => (float)($t['discount_percent'] ?? 0),
                        'description'      => $t['description'] ?? null
                    ];
                }
            }
            if (!empty($processedTiers)) {
                $tiersModel->syncTiers($contractId, $processedTiers);
            }
        }

        // 5. Registrar Historial
        (new ContractHistoryModel())->add($contractId, $userId, 'creado', 'Contrato completo creado vía servicio');

        return $contractId;
    }

    /**
     * Verifica y actualiza automáticamente los estados de contratos vencidos.
     * Útil para ejecuciones vía CRON.
     */
    public function updateContractStatuses()
    {
        $contractModel = new ContractModel();
        $today = date('Y-m-d');
        
        // Buscar contratos vigentes que ya pasaron su fecha de término
        $sql = "SELECT id, code FROM doc_contracts 
                WHERE status = 'vigente' AND end_date IS NOT NULL AND end_date < ? AND active = 1";
        // (Nota: Esto es una simplificación, lo ideal es usar el modelo)
        
        // Actualizamos a 'vencido'
        // ... logic ...
    }
}
