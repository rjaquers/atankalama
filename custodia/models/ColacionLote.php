<?php



class ColacionLote
{
    /** @var mysqli */
    private $db;

    private $table = 'colacion_lote';

    public function __construct($db = null)
    {
        if ($db instanceof mysqli) {
            $this->db = $db;
        } else {
            // fallback al global si no se inyectó
          //  global $db as $globalDb; // (si tu editor no soporta "as", usa línea siguiente)
            global $db; $globalDb = $db;
            if ($globalDb instanceof mysqli) {
                $this->db = $globalDb;
            } else {
                throw new RuntimeException('No hay conexión MySQLi válida en ColacionLote.');
            }
        }
    }

    //public function crear(array $data): int
    //{
    //    /**
    //     * 1) Validar servicio principal (obligatorio, entero, debe existir)
    //     */
    //    if (! isset($data['servicio_tipo_id']) || ! is_numeric($data['servicio_tipo_id'])) {
    //        throw new \RuntimeException('servicio_tipo_id inválido o no recibido');
    //    }
    //
    //    $servicio_tipo_id = (int)$data['servicio_tipo_id'];
    //    if ($servicio_tipo_id <= 0) {
    //        throw new \RuntimeException('servicio_tipo_id no puede ser 0');
    //    }
    //
    //    /**
    //     * 2) Servicios adicionales (checkbox múltiple)
    //     *    → Se guardan como JSON
    //     */
    //    $servicios_adicionales = json_encode($data['servicios_adicionales'] ?? []);
    //
    //
    //    /**
    //     * Servicios adicionales (checkbox múltiple)
    //     * Guardar como "2,3,5"
    //     */
    //
    //
    //    if (!empty($data['servicios_adicionales'])) {
    //        // Garantiza arreglo
    //        $tmp = (array)$data['servicios_adicionales'];
    //        // Filtra solo números válidos
    //        $tmp = array_filter($tmp, fn($v) => is_numeric($v));
    //        // Convierte a texto CSV
    //        if (!empty($tmp)) {
    //            $servicios_adicionales = implode(',', $tmp);
    //        }
    //    }
    //
    //
    //    $servicios_adicionales = trim($servicios_adicionales, '"[]"');
    //
    //    //print_r($servicios_adicionales); die();
    //
    //
    //    /**
    //     * 3) Preparar SQL
    //     */
    //    $sql = 'INSERT INTO colacion_lote
    //        (empresa_id, fecha_servicio, fecha_fin_servicio, servicios_adicionales,
    //         servicio_tipo_id, cantidad, observaciones, from_upload_id, creado_por, excel)
    //        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    //
    //    $stmt = $this->db->prepare($sql);
    //    if (! $stmt) {
    //        throw new \RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
    //    }
    //
    //    /**
    //     * 4) Normalización de datos
    //     */
    //    $empresa_id = (int)($data['empresa_id'] ?? 0);
    //    $fecha_servicio = (string)($data['fecha_servicio'] ?? '');
    //    $fecha_fin_servicio = (string)($data['fecha_fin_servicio'] ?? '');
    //    $cantidad = (int)($data['cantidad'] ?? 0);
    //    $observaciones = $data['observaciones'] ?? null;
    //    if ($observaciones === '') {
    //        $observaciones = null;
    //    }
    //    $from_upload_id = isset($data['from_upload_id']) && $data['from_upload_id'] !== '' ? (int)$data['from_upload_id'] : null;
    //    $creado_por = isset($data['creado_por']) && $data['creado_por'] !== '' ? (int)$data['creado_por'] : null;
    //    $excel = isset($data['excel']) && $data['excel'] !== '' ? (int)$data['excel'] : null;
    //
    //    /**
    //     * 5) Bind_param: tipos correctos
    //     *  i s s s i i s i i
    //     */
    //    $stmt->bind_param(
    //        'isssiisiii',
    //        $empresa_id,
    //        $fecha_servicio,
    //        $fecha_fin_servicio,
    //        $servicios_adicionales,
    //        $servicio_tipo_id,
    //        $cantidad,
    //        $observaciones,
    //        $from_upload_id,
    //        $creado_por,
    //        $excel
    //    );
    //
    //    /**
    //     * 6) Ejecutar y manejar error
    //     */
    //    if (! $stmt->execute()) {
    //        $err = $stmt->error;
    //        $stmt->close();
    //        throw new \RuntimeException('Error creando lote x4: '.$err);
    //    }
    //
    //    /**
    //     * 7) Obtener ID
    //     */
    //    $id = (int)$this->db->insert_id;
    //    $stmt->close();
    //
    //    return $id;
    //}

    /**
     * Crea un lote y registra sus adicionales en colacion_lote_adicional.
     *
     * @param array $data  Datos del lote (empresa_id, fechas, servicio_tipo_id, etc.)
     * @param array $adds  IDs de adicionales seleccionados en el formulario (adicionales[])
     * @param int   $from_upload_id  (opcional) id de carga excel, por compatibilidad con tu controller
     *
     * @return int ID del lote creado
     */
    public function crear(array $data, array $adds = [], int $from_upload_id = 0): int
    {
        // 1) Validar servicio principal
        if (!isset($data['servicio_tipo_id']) || !is_numeric($data['servicio_tipo_id'])) {
            throw new RuntimeException('servicio_tipo_id inválido o no recibido');
        }

        $servicio_tipo_id = (int)$data['servicio_tipo_id'];
        if ($servicio_tipo_id <= 0) {
            throw new RuntimeException('servicio_tipo_id no puede ser 0');
        }

        // 2) Normalizar adicionales (IDs únicos, enteros, >0)
        $adds = array_values(array_unique(array_filter(array_map('intval', (array)$adds), fn($v) => $v > 0)));

        // 3) Campo legacy servicios_adicionales (RECOMENDADO: dejar NULL)
        // Si quieres mantener un "cache" CSV por compatibilidad, descomenta el implode.
        $servicios_adicionales = null;
        // $servicios_adicionales = !empty($adds) ? implode(',', $adds) : null;

        // 4) Preparar SQL insert lote
        $sql = 'INSERT INTO colacion_lote
            (empresa_id, fecha_servicio, fecha_fin_servicio, servicios_adicionales,
             servicio_tipo_id, cantidad, observaciones, from_upload_id, creado_por, excel)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        }

        // 5) Normalización de datos
        $empresa_id        = (int)($data['empresa_id'] ?? 0);
        $fecha_servicio    = (string)($data['fecha_servicio'] ?? '');
        $fecha_fin_servicio= (string)($data['fecha_fin_servicio'] ?? '');
        $cantidad          = (int)($data['cantidad'] ?? 0);

        $observaciones = $data['observaciones'] ?? null;
        if ($observaciones === '') { $observaciones = null; }

        $from_upload_id_db = isset($data['from_upload_id']) && $data['from_upload_id'] !== '' ? (int)$data['from_upload_id'] : null;
        $creado_por        = isset($data['creado_por']) && $data['creado_por'] !== '' ? (int)$data['creado_por'] : null;
        $excel             = isset($data['excel']) && $data['excel'] !== '' ? (int)$data['excel'] : 0;

        // 6) bind_param (nota: servicios_adicionales puede ser null)
        $stmt->bind_param(
            'isssiisiii',
            $empresa_id,
            $fecha_servicio,
            $fecha_fin_servicio,
            $servicios_adicionales,
            $servicio_tipo_id,
            $cantidad,
            $observaciones,
            $from_upload_id_db,
            $creado_por,
            $excel
        );

        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new RuntimeException('Error creando lote: '.$err);
        }

        $lote_id = (int)$this->db->insert_id;
        $stmt->close();

        // 7) Insertar adicionales en tabla relacional
        $this->guardarAdicionales($lote_id, $adds);

        return $lote_id;
    }
    // Fin de la función crear()

    /**
     * Inserta los adicionales del lote en colacion_lote_adicional.
     *
     * @param int   $lote_id ID del lote recién creado
     * @param int[] $adds    IDs de adicionales a insertar
     * @return void
     */
    private function guardarAdicionales(int $lote_id, array $adds): void
    {
        if ($lote_id <= 0 || empty($adds)) {
            return;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO colacion_lote_adicional (lote_id, adicional_id) VALUES (?, ?)'
        );
        if (!$stmt) {
            throw new RuntimeException('DB_PREPARE_FAILED (adicionales): '.$this->db->error);
        }

        foreach ($adds as $adicional_id) {
            $adicional_id = (int)$adicional_id;
            if ($adicional_id <= 0) {
                continue;
            }
            $stmt->bind_param('ii', $lote_id, $adicional_id);
            if (!$stmt->execute()) {
                $err = $stmt->error;
                $stmt->close();
                throw new RuntimeException('Error insertando adicional: '.$err);
            }
        }

        $stmt->close();
    }


    public function listar($empresa_id = null, $fecha = null): array
    {
        $sql = "SELECT l.*, e.business_name AS empresa, t.nombre AS servicio
                FROM {$this->table} l
                JOIN doc_companies e ON e.id = l.empresa_id
                JOIN colacion_servicio_tipo t ON t.id = l.servicio_tipo_id
                WHERE 1=1";
        $types = '';
        $params = [];
        if ($empresa_id) {
            $sql .= ' AND l.empresa_id=?';
            $types .= 'i';
            $params[] = $empresa_id;
        }
        if ($fecha) {
            $sql .= ' AND l.fecha_servicio=?';
            $types .= 's';
            $params[] = $fecha;
        }
        $sql .= ' ORDER BY l.fecha_fin_servicio DESC, l.creado_en DESC limit 50';

        $stmt = $this->db->prepare($sql);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows ?? [];
    }

    public function obtener($id): ?array
    {
        $stmt = $this->db->prepare(
            "
            SELECT l.*, e.business_name AS empresa, t.nombre AS servicio
            FROM {$this->table} l
            JOIN doc_companies e ON e.id = l.empresa_id
            JOIN colacion_servicio_tipo t ON t.id = l.servicio_tipo_id
            WHERE l.id=?
        "
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $r ?: null;
    }

    public function obtenerAdicionales($lote_id): array
    {
        $stmt = $this->db->prepare(
            '
            SELECT a.id, a.nombre
            FROM colacion_lote_adicional la
            JOIN colacion_adicional a ON a.id = la.adicional_id
            WHERE la.lote_id=?
        '
        );
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $res ?? [];
    }




    public function obtenerPorId($id)
    {
        $sql = 'SELECT 
                l.*,
                e.business_name AS empresa,
                s.nombre AS servicio
            FROM colacion_lote AS l
            LEFT JOIN doc_companies AS e ON e.id = l.empresa_id
            LEFT JOIn colacion_servicio_tipo AS s ON s.id = l.servicio_tipo_id
            WHERE l.id = ?
            LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }






    public function obtenerPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM colacion_voucher WHERE codigo_publico=?');
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        $stmt->bind_param('s', $codigo);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc() ?: null;
        $stmt->close();
        return $row;
    }

    public function incrementarScan(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE colacion_voucher SET scan_count = scan_count + 1 WHERE id=?');
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    /** Devuelve true si pasó de pendiente→usado */
    public function marcarUsado(int $id, ?string $ip): bool
    {
        $now = (new DateTime('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
        $sql = "UPDATE colacion_voucher
                SET estado='usado', usado_en=?, usado_por_ip=INET6_ATON(?)
                WHERE id=? AND estado='pendiente'";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error);
        $stmt->bind_param('ssi', $now, $ip, $id);
        $stmt->execute();
        $aff = $stmt->affected_rows;
        $stmt->close();
        return $aff > 0;
    }

    public function generarDesdeLote($lote_id, $fecha_servicio, $cantidad, array $guests = []): void
    {
        // Si hay huéspedes: insert con columnas guest_*
        if (!empty($guests)) {
            $sql = "
            INSERT INTO {$this->table}
                (lote_id, numero_en_lote, codigo_publico, guest_rut, guest_nombre, guest_habitacion)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) { throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error); }

            for ($i = 1; $i <= (int)$cantidad; $i++) {
                $g = $guests[$i-1] ?? ['rut'=>null,'nombre'=>null,'habitacion'=>null];
                $code = $this->generarCodigoPublico($fecha_servicio);

                $rut  = $g['rut'] ?? null;
                $nom  = $g['nombre'] ?? null;
                $hab  = $g['habitacion'] ?? null;

                $stmt->bind_param('iissss', $lote_id, $i, $code, $rut, $nom, $hab);
                if (!$stmt->execute()) {
                    throw new RuntimeException('INSERT voucher #'.$i.': '.$stmt->error);
                }
            }
            $stmt->close();
            return;
        }

        // Sin huéspedes (comportamiento previo)
        $sql = "INSERT INTO {$this->table} (lote_id, numero_en_lote, codigo_publico) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) { throw new RuntimeException('DB_PREPARE_FAILED: '.$this->db->error); }

        for ($i = 1; $i <= (int)$cantidad; $i++) {
            $code = $this->generarCodigoPublico($fecha_servicio);
            $stmt->bind_param('iis', $lote_id, $i, $code);
            if (!$stmt->execute()) {
                throw new RuntimeException('INSERT voucher #'.$i.': '.$stmt->error);
            }
        }
        $stmt->close();
    }


    public function obtenerNombreServicios($csv_ids)
    {
        if (empty($csv_ids)) {
            return 'Sin datos';
        }

        // "1,3" → [1,3]
        $ids = array_filter(array_map('intval', explode(',', $csv_ids)));

        if (empty($ids)) {
            return 'Sin datos';
        }

        // placeholders → ?, ?, ?
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $sql = "SELECT nombre FROM colacion_servicio_tipo WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);

        $types = str_repeat('i', count($ids));
        $stmt->bind_param($types, ...$ids);

        $stmt->execute();
        $res = $stmt->get_result();

        $nombres = [];
        while ($row = $res->fetch_assoc()) {
            $nombres[] = $row['nombre'];
        }

        return $nombres ? implode(', ', $nombres) : 'Sin datos';
    }

    public function obtenerHorarioServicio($servicio_id)
    {
        $sql = 'SELECT hora_inicio, hora_fin 
            FROM colacion_servicio_tipo 
            WHERE id = ?
            LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $servicio_id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerInfoServicio($id)
    {
        $sql = 'SELECT nombre, hora_inicio, hora_fin , id
            FROM colacion_servicio_tipo 
            WHERE id = ?
            LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function obtenerServiciosDeLote($lote_id)
    {
        $sql = '
        SELECT servicio_tipo_id, servicios_adicionales
        FROM colacion_lote
        WHERE id = ?
        LIMIT 1
    ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (! $res) {
            return ['principal' => null, 'adicionales' => []];
        }

        // Servicio principal
        $principal = (int)$res['servicio_tipo_id'];

        // Servicios adicionales (cadena CSV: "2,3,1")
        $adicionales = [];
        if (! empty($res['servicios_adicionales'])) {
            $adicionales = array_filter(array_map('intval', explode(',', $res['servicios_adicionales'])));
        }

        return [
            'principal' => $principal,
            'adicionales' => $adicionales
        ];
    }

    public function obtenerNombresServicios(array $ids)
    {
        if (empty($ids)) return [];

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $sql = "SELECT id, nombre FROM colacion_servicio_tipo WHERE id IN ($placeholders)";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $res = $stmt->get_result();

        $nombres = [];
        while ($row = $res->fetch_assoc()) {
            $nombres[$row['id']] = $row['nombre'];
        }

        return $nombres;
    }

    public function obtenerPersonasPorLote($lote_id)
    {
        $sql = '
        SELECT 
            id,
            lote_id,
            numero_en_lote,
            codigo_publico,
            guest_rut,
            guest_nombre,
            guest_habitacion,
            estado,
            creado_en
        FROM colacion_voucher
        WHERE lote_id = ?
        ORDER BY guest_nombre ASC, id ASC
    ';

        $stmt = $this->db->prepare($sql);
        if (! $stmt) {
            throw new RuntimeException('Error en prepare(): '.$this->db->error);
        }

        $stmt->bind_param('i', $lote_id);
        $stmt->execute();

        $result = $stmt->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);
    }


    /**
     * Obtiene los nombres de los servicios adicionales de un lote
     *
     * @param int $lote_id
     * @return string
     */
    public function obtenerNombresAdicionalesDeLote(int $lote_id): string
    {
        $sql = '
        SELECT a.nombre
        FROM colacion_lote_adicional la
        INNER JOIN colacion_adicional a ON a.id = la.adicional_id
        WHERE la.lote_id = ?
          AND a.activo = 1
        ORDER BY a.tipo, a.nombre
    ';

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('i', $lote_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $nombres = [];
        while ($row = $res->fetch_assoc()) {
            $nombres[] = $row['nombre'];
        }
        $stmt->close();

        return $nombres ? implode(', ', $nombres) : '—';
    }
// Fin de la función obtenerNombresAdicionalesDeLote()

}
