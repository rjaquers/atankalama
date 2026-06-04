<?php

require_once __DIR__ . '/../config/db.php';

class VoucherModel
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    // CLIENTES NOMINALES
    // ─────────────────────────────────────────────────────────

    public function obtenerClientesPorComanda(int $comandaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_voucher_clientes
             WHERE comanda_id = ? ORDER BY nombre ASC"
        );
        $stmt->execute([$comandaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertarCliente(int $comandaId, ?string $rut, string $nombre, ?string $empresa): int
    {
        $codigo = $this->generarCodigo();
        $stmt   = $this->conn->prepare(
            "INSERT INTO coci_voucher_clientes (comanda_id, rut, nombre, empresa, codigo)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $comandaId,
            $rut ? $this->normalizarRut($rut) : null,
            $nombre,
            $empresa,
            $codigo,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function obtenerClientePorId(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_voucher_clientes WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function eliminarCliente(int $id): void
    {
        $this->conn->prepare("DELETE FROM coci_voucher_clientes WHERE id = ?")
            ->execute([$id]);
    }

    public function actualizarCliente(int $id, ?string $rut, string $nombre, ?string $empresa): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE coci_voucher_clientes 
             SET rut = ?, nombre = ?, empresa = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $rut ? $this->normalizarRut($rut) : null,
            $nombre,
            $empresa,
            $id
        ]);
    }

    /**
     * Copia todos los clientes de una comanda a las comandas futuras de la misma reserva.
     */
    public function propagarVouchers(int $comandaOrigenId, int $reservaId, string $fechaActual): int
    {
        $clientes = $this->obtenerClientesPorComanda($comandaOrigenId);
        if (empty($clientes)) return 0;

        $stmt = $this->conn->prepare(
            "SELECT id FROM coci_comandas
             WHERE reserva_id = ? AND fecha > ? AND id != ?"
        );
        $stmt->execute([$reservaId, $fechaActual, $comandaOrigenId]);
        $comandasDestino = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($comandasDestino)) return 0;

        // Preparar INSERT una sola vez y reutilizar en cada iteración
        $insertStmt = $this->conn->prepare(
            "INSERT INTO coci_voucher_clientes (comanda_id, rut, nombre, empresa, codigo)
             VALUES (?, ?, ?, ?, ?)"
        );

        foreach ($comandasDestino as $destinoId) {
            $this->eliminarClientesPorComanda((int)$destinoId);
            foreach ($clientes as $c) {
                $insertStmt->execute([
                    $destinoId,
                    $c['rut'],
                    $c['nombre'],
                    $c['empresa'],
                    $this->generarCodigo(),
                ]);
            }
        }

        return count($comandasDestino);
    }

    public function eliminarClientesPorComanda(int $comandaId): void
    {
        $this->conn->prepare("DELETE FROM coci_voucher_clientes WHERE comanda_id = ?")
            ->execute([$comandaId]);
    }

    /**
     * Actualiza el cliente equivalente (mismo RUT o mismo nombre) en las comandas
     * futuras de la misma reserva. Solo toca registros no impresos.
     */
    public function propagarEdicionCliente(
        int $reservaId,
        string $fechaActual,
        ?string $rutAnterior,
        string $nombreAnterior,
        ?string $rutNuevo,
        string $nombreNuevo,
        ?string $empresa
    ): int {
        $stmt = $this->conn->prepare(
            "SELECT id FROM coci_comandas WHERE reserva_id = ? AND fecha > ?"
        );
        $stmt->execute([$reservaId, $fechaActual]);
        $comandasDestino = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($comandasDestino)) return 0;

        $rutAntNorm = $rutAnterior ? $this->normalizarRut($rutAnterior) : null;
        $count = 0;

        foreach ($comandasDestino as $destId) {
            if ($rutAntNorm) {
                $find = $this->conn->prepare(
                    "SELECT id FROM coci_voucher_clientes
                     WHERE comanda_id = ? AND rut = ? AND veces_impreso = 0 LIMIT 1"
                );
                $find->execute([$destId, $rutAntNorm]);
            } else {
                $find = $this->conn->prepare(
                    "SELECT id FROM coci_voucher_clientes
                     WHERE comanda_id = ? AND nombre = ? AND veces_impreso = 0 LIMIT 1"
                );
                $find->execute([$destId, $nombreAnterior]);
            }
            $clienteId = $find->fetchColumn();
            if ($clienteId) {
                $this->actualizarCliente((int)$clienteId, $rutNuevo, $nombreNuevo, $empresa);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Elimina el cliente equivalente (mismo RUT o mismo nombre) en las comandas
     * futuras de la misma reserva. Solo toca registros no impresos.
     */
    public function propagarEliminacionCliente(
        int $reservaId,
        string $fechaActual,
        ?string $rut,
        string $nombre
    ): int {
        $stmt = $this->conn->prepare(
            "SELECT id FROM coci_comandas WHERE reserva_id = ? AND fecha > ?"
        );
        $stmt->execute([$reservaId, $fechaActual]);
        $comandasDestino = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($comandasDestino)) return 0;

        $rutNorm = $rut ? $this->normalizarRut($rut) : null;
        $count = 0;

        foreach ($comandasDestino as $destId) {
            if ($rutNorm) {
                $find = $this->conn->prepare(
                    "SELECT id FROM coci_voucher_clientes
                     WHERE comanda_id = ? AND rut = ? AND veces_impreso = 0 LIMIT 1"
                );
                $find->execute([$destId, $rutNorm]);
            } else {
                $find = $this->conn->prepare(
                    "SELECT id FROM coci_voucher_clientes
                     WHERE comanda_id = ? AND nombre = ? AND veces_impreso = 0 LIMIT 1"
                );
                $find->execute([$destId, $nombre]);
            }
            $clienteId = $find->fetchColumn();
            if ($clienteId) {
                $this->eliminarCliente((int)$clienteId);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Importa clientes desde un archivo Excel (.xlsx/.xls).
     * Espera columnas: A=RUT, B=Nombre. (C=Empresa es opcional y se ignora si se provee $defaultEmpresa).
     * Retorna [ 'insertados' => N, 'omitidos' => M, 'errores' => [...] ]
     */
    public function importarDesdeExcel(int $comandaId, string $archivoTmp, ?string $defaultEmpresa = null): array
    {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $result = ['insertados' => 0, 'omitidos' => 0, 'errores' => []];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($archivoTmp);
            $hoja        = $spreadsheet->getActiveSheet();
            $filas       = $hoja->toArray(null, true, true, true);
        } catch (\Exception $e) {
            $result['errores'][] = 'No se pudo leer el archivo: ' . $e->getMessage();
            return $result;
        }

        $primera = true;
        foreach ($filas as $num => $fila) {
            if ($primera) { $primera = false; continue; } // saltar cabecera

            $rut     = trim((string)($fila['A'] ?? ''));
            $nombre  = trim((string)($fila['B'] ?? ''));
            $empresa = $defaultEmpresa ?: trim((string)($fila['C'] ?? ''));

            if ($nombre === '') {
                $result['omitidos']++;
                continue;
            }

            try {
                $this->insertarCliente($comandaId, $rut ?: null, $nombre, $empresa ?: null);
                $result['insertados']++;
            } catch (\Exception $e) {
                $result['errores'][] = "Fila {$num}: {$e->getMessage()}";
            }
        }

        return $result;
    }

    // ─────────────────────────────────────────────────────────
    // VOUCHERS GENÉRICOS (sin nombre)
    // ─────────────────────────────────────────────────────────

    public function obtenerGenericosPorComanda(int $comandaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM coci_vouchers_genericos
             WHERE comanda_id = ? ORDER BY numero ASC"
        );
        $stmt->execute([$comandaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Regenera los vouchers genéricos de una comanda (borra los anteriores). */
    public function generarVouchersGenericos(int $comandaId, int $cantidad): void
    {
        $this->conn->prepare("DELETE FROM coci_vouchers_genericos WHERE comanda_id = ?")
            ->execute([$comandaId]);

        $stmt = $this->conn->prepare(
            "INSERT INTO coci_vouchers_genericos (comanda_id, codigo, numero) VALUES (?, ?, ?)"
        );
        for ($i = 1; $i <= $cantidad; $i++) {
            $stmt->execute([$comandaId, $this->generarCodigo(), $i]);
        }
    }

    /** Agrega N vouchers genéricos a los existentes (sin borrar los anteriores). */
    public function agregarVouchersGenericos(int $comandaId, int $cantidad): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(MAX(numero), 0) FROM coci_vouchers_genericos WHERE comanda_id = ?"
        );
        $stmt->execute([$comandaId]);
        $maxNumero = (int)$stmt->fetchColumn();

        $stmt = $this->conn->prepare(
            "INSERT INTO coci_vouchers_genericos (comanda_id, codigo, numero) VALUES (?, ?, ?)"
        );
        for ($i = 1; $i <= $cantidad; $i++) {
            $stmt->execute([$comandaId, $this->generarCodigo(), $maxNumero + $i]);
        }
        return $maxNumero + 1; // primer número agregado
    }

    // ─────────────────────────────────────────────────────────
    // BÚSQUEDAS (kiosko y QR)
    // ─────────────────────────────────────────────────────────

    /**
     * Busca vouchers nominales por RUT.
     * Solo retorna comandas del día de hoy.
     */
    public function buscarPorRut(string $rut): array
    {
        $rutNorm = $this->normalizarRut($rut);
        $stmt    = $this->conn->prepare(
            "SELECT vc.id, vc.codigo, vc.nombre, vc.empresa, vc.rut, vc.canjeado, vc.canjeado_en, vc.impreso, vc.veces_impreso,
                    c.id AS comanda_id, c.tipo_servicio, c.fecha, c.hora_servicio,
                    c.nombre_hotel, c.nombre_empresa, c.cantidad_personas, c.observaciones
             FROM coci_voucher_clientes vc
             JOIN coci_comandas c ON c.id = vc.comanda_id
             WHERE vc.rut = ? AND c.fecha = CURDATE()
             ORDER BY c.hora_servicio ASC"
        );
        $stmt->execute([$rutNorm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Busca un voucher nominal por código único (para QR). */
    public function buscarNominalPorCodigo(string $codigo): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT vc.*, c.tipo_servicio, c.fecha, c.hora_servicio,
                    c.nombre_hotel, c.nombre_empresa, c.cantidad_personas, c.observaciones
             FROM coci_voucher_clientes vc
             JOIN coci_comandas c ON c.id = vc.comanda_id
             WHERE vc.codigo = ? LIMIT 1"
        );
        $stmt->execute([$codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Busca un voucher genérico por código único (para QR). */
    public function buscarGenericoPorCodigo(string $codigo): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT vg.*, c.tipo_servicio, c.fecha, c.hora_servicio,
                    c.nombre_hotel, c.nombre_empresa, c.cantidad_personas, c.observaciones
             FROM coci_vouchers_genericos vg
             JOIN coci_comandas c ON c.id = vg.comanda_id
             WHERE vg.codigo = ? LIMIT 1"
        );
        $stmt->execute([$codigo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /** Marca un voucher nominal como canjeado (solo si no lo estaba ya). */
    public function marcarNominalCanjeado(int $id): void
    {
        $this->conn->prepare(
            "UPDATE coci_voucher_clientes
             SET canjeado = 1, canjeado_en = NOW()
             WHERE id = ? AND canjeado = 0"
        )->execute([$id]);
    }

    /** Marca un voucher genérico como canjeado. */
    public function marcarGenericoCanjeado(int $id): void
    {
        $this->conn->prepare(
            "UPDATE coci_vouchers_genericos
             SET canjeado = 1, canjeado_en = NOW()
             WHERE id = ? AND canjeado = 0"
        )->execute([$id]);
    }

    /** Marca un voucher individual como impreso (por código único). */
    public function marcarImpresoPorCodigo(string $codigo): void
    {
        $this->conn->prepare(
            "UPDATE coci_voucher_clientes 
             SET impreso = 1, impreso_en = NOW(), veces_impreso = veces_impreso + 1
             WHERE codigo = ?"
        )->execute([$codigo]);

        $this->conn->prepare(
            "UPDATE coci_vouchers_genericos 
             SET impreso = 1, impreso_en = NOW(), veces_impreso = veces_impreso + 1
             WHERE codigo = ?"
        )->execute([$codigo]);
    }

    /** Resetea el contador de impresiones de un cliente nominal a cero. */
    public function resetearImpresiones(int $id): void
    {
        $this->conn->prepare(
            "UPDATE coci_voucher_clientes
             SET impreso = 0, impreso_en = NULL, veces_impreso = 0
             WHERE id = ?"
        )->execute([$id]);
    }

    /** Marca todos los vouchers de una comanda como impresos. */
    public function marcarVouchersImpresos(int $comandaId): void
    {
        $this->conn->prepare(
            "UPDATE coci_voucher_clientes SET impreso = 1, impreso_en = NOW() 
             WHERE comanda_id = ? AND impreso = 0"
        )->execute([$comandaId]);

        $this->conn->prepare(
            "UPDATE coci_vouchers_genericos SET impreso = 1, impreso_en = NOW() 
             WHERE comanda_id = ? AND impreso = 0"
        )->execute([$comandaId]);
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

    private function generarCodigo(): string
    {
        return strtoupper(bin2hex(random_bytes(8))); // 16 chars hex
    }

    public function normalizarRut(string $rut): string
    {
        $rut = strtoupper(str_replace(['.', ' '], '', trim($rut)));
        if (strpos($rut, '-') === false && strlen($rut) > 1) {
            $rut = substr($rut, 0, -1) . '-' . substr($rut, -1);
        }
        return $rut;
    }

    /** Etiqueta legible del tipo de servicio. */
    public static function etiquetaServicio(string $tipo): string
    {
        return match ($tipo) {
            'almuerzo'          => 'Almuerzo',
            'cena'              => 'Cena',
            'colacion'          => 'Colación',
            'colacion_especial' => 'Colación Especial',
            'desayuno'          => 'Desayuno',
            default             => ucfirst($tipo),
        };
    }

    /** Color Bootstrap del tipo de servicio. */
    public static function colorServicio(string $tipo): string
    {
        return match ($tipo) {
            'almuerzo'          => 'warning',
            'cena'              => 'primary',
            'colacion'          => 'success',
            'colacion_especial' => 'dark',
            'desayuno'          => 'info',
            default             => 'secondary',
        };
    }
}
