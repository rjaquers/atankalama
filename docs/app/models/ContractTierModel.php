<?php
/**
 * Modelo de Escalas de Precio (Tiers).
 *
 * Gestiona las escalas de descuento por volumen de huéspedes
 * en la tabla doc_contract_tiers.
 * Cada contrato con pricing_mode = 'por_persona' puede tener
 * múltiples escalas (ej: base, 50+, 70+).
 *
 * @package App\Models
 */
class ContractTierModel extends Model
{
    /**
     * Obtiene las escalas de un contrato, ordenadas por min_guests.
     *
     * @param  int   $contractId ID del contrato
     * @return array Lista de escalas ordenadas ascendentemente
     */
    public function getByContractId($contractId)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM doc_contract_tiers
            WHERE contract_id = ?
            ORDER BY min_guests ASC
        ");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = $row;
            }
        }
        return $rows;
    }
    // Fin de la función getByContractId()

    /**
     * Busca la escala aplicable para una cantidad de huéspedes.
     *
     * Qué hace:
     * - Recorre las escalas del contrato
     * - Encuentra la que corresponde al rango del guest_count
     * - Retorna los datos del tier aplicable
     *
     * @param  int        $contractId ID del contrato
     * @param  int        $guestCount Cantidad de huéspedes
     * @return array|null Datos del tier aplicable o null si no hay coincidencia
     */
    public function findApplicableTier($contractId, $guestCount)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM doc_contract_tiers
            WHERE contract_id = ?
              AND min_guests <= ?
              AND (max_guests IS NULL OR max_guests >= ?)
            ORDER BY min_guests DESC
            LIMIT 1
        ");
        $stmt->bind_param("iii", $contractId, $guestCount, $guestCount);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función findApplicableTier()

    /**
     * Sincroniza las escalas de un contrato.
     *
     * Qué hace:
     * - Elimina todas las escalas anteriores
     * - Inserta las nuevas escalas
     *
     * @param  int   $contractId ID del contrato
     * @param  array $tiers Lista de escalas, cada una con:
     *   - min_guests       (int)
     *   - max_guests       (int|null)
     *   - price_per_person (float)
     *   - discount_percent (float)
     *   - description      (string|null)
     * @return void
     */
    public function syncTiers($contractId, $tiers)
    {
        // ----------------------------
        // Eliminar escalas previas
        // ----------------------------
        $stmt = $this->conn->prepare("DELETE FROM doc_contract_tiers WHERE contract_id = ?");
        $stmt->bind_param("i", $contractId);
        $stmt->execute();

        // ----------------------------
        // Insertar nuevas escalas
        // ----------------------------
        $stmt = $this->conn->prepare("
            INSERT INTO doc_contract_tiers(contract_id, min_guests, max_guests, price_per_person, discount_percent, description)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($tiers as $tier) {
            $maxGuests = !empty($tier['max_guests']) ? (int)$tier['max_guests'] : null;
            $desc = $tier['description'] ?? null;
            $stmt->bind_param("iiidds",
                $contractId,
                $tier['min_guests'],
                $maxGuests,
                $tier['price_per_person'],
                $tier['discount_percent'],
                $desc
            );
            $stmt->execute();
        }
    }
    // Fin de la función syncTiers()

    /**
     * Busca una escala por su ID.
     *
     * @param  int $id ID del tier
     * @return array|null Datos del tier o null
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM doc_contract_tiers WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }
    // Fin de la función getById()
}
