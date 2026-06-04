<?php
/**
 * Servicio de Pagos.
 *
 * Centraliza la lógica de negocio para cálculos financieros, saldos y 
 * validación de estados de cobro.
 *
 * @package App\Services
 */
class PaymentService
{
    /**
     * Calcula el saldo pendiente de un contrato.
     * 
     * @param int $contractId
     * @return float
     */
    public static function getBalance($contractId)
    {
        $contractModel = new ContractModel();
        $paymentModel = new PaymentModel();

        $contract = $contractModel->getById($contractId);
        if (!$contract) return 0;

        $totalPaid = $paymentModel->getTotalPaid($contractId);
        $baseAmount = (float)$contract['base_amount'];

        return $baseAmount - $totalPaid;
    }

    /**
     * Obtiene el porcentaje de pago completado.
     * 
     * @param int $contractId
     * @return float
     */
    public static function getPaymentPercentage($contractId)
    {
        $contractModel = new ContractModel();
        $paymentModel = new PaymentModel();

        $contract = $contractModel->getById($contractId);
        if (!$contract || (float)$contract['base_amount'] <= 0) return 0;

        $totalPaid = $paymentModel->getTotalPaid($contractId);
        $baseAmount = (float)$contract['base_amount'];

        return ($totalPaid / $baseAmount) * 100;
    }

    /**
     * Verifica si un contrato tiene pagos pendientes por vencer próximamente.
     * 
     * @param int $contractId
     * @param int $days
     * @return bool
     */
    public static function hasUpcomingPayments($contractId, $days = 7)
    {
        $paymentModel = new PaymentModel();
        $pending = $paymentModel->getPendingPayments();
        
        foreach($pending as $p) {
            if ((int)$p['contract_id'] === (int)$contractId) {
                if ($p['period_end']) {
                    $diff = (strtotime($p['period_end']) - time()) / 86400;
                    if ($diff >= 0 && $diff <= $days) return true;
                }
            }
        }
        
        return false;
    }
}
