<?php



class Novedad
{
    private $pdo;

    /**
     * Constructor del modelo Novedad
     * - Obtiene conexión única desde Database::getConnection()
     * - Asigna PDO a $this->pdo
     *
     * @throws RuntimeException si no hay conexión
     */
    public function __construct()
    {
        $this->pdo = Database::getConnection();

        if (!$this->pdo) {
            throw new RuntimeException('No se pudo establecer conexión PDO.');
        }
    }
    // Fin del constructor


    /**
     * Guarda una nueva novedad en la base de datos.
     *
     * @param array $data Datos necesarios para guardar la novedad:
     *  - recepcionista_id (int)
     *  - area (string)
     *  - detalle (string)
     *  - hotel (string)
     *  - requiere_seguimiento (int 0|1)
     *  - seguimiento_estado (int)
     *  - nivel_importancia (int 1-10)
     *  - nivel_sugerido (int 1-10)
     *  - score_calculado (int)
     *  - detalle_calculo (string JSON)
     *
     * @return int ID insertado
     *
     * @throws InvalidArgumentException si faltan datos obligatorios
     */
    public function guardar(array $data): int
    {
        $recepcionistaId = (int) ($data['recepcionista_id'] ?? 0);
        $area = trim((string) ($data['area'] ?? ''));
        $detalle = trim((string) ($data['detalle'] ?? ''));
        $hotel = trim((string) ($data['hotel'] ?? ''));

        $requiere = (int) ($data['requiere_seguimiento'] ?? 0);
        $estado = (int) ($data['seguimiento_estado'] ?? 0);

        $tipoSeguimiento = $data['tipo_seguimiento'] ?? null;
        $flexkeepingId = $data['flexkeeping_id'] ?? null;

        $nivelImportancia = (int) ($data['nivel_importancia'] ?? 0);
        $nivelSugerido = (int) ($data['nivel_sugerido'] ?? 0);
        $scoreCalculado = (int) ($data['score_calculado'] ?? 0);
        $detalleCalculo = (string) ($data['detalle_calculo'] ?? '');
        $tipoNovedad = trim((string) ($data['tipo_novedad'] ?? 'Otro'));

        // =========================
        // VALIDACIONES OBLIGATORIAS
        // =========================

        if ($recepcionistaId <= 0 || $area === '' || $detalle === '' || $hotel === '') {
            throw new InvalidArgumentException('Datos incompletos para guardar la novedad.');
        }

        if ($nivelImportancia < 1 || $nivelImportancia > 10) {
            throw new InvalidArgumentException('Nivel de importancia inválido.');
        }

        if ($nivelSugerido < 1 || $nivelSugerido > 10) {
            throw new InvalidArgumentException('Nivel sugerido inválido.');
        }

        if ($scoreCalculado < 1) {
            throw new InvalidArgumentException('Score calculado inválido.');
        }

        if ($detalleCalculo === '') {
            throw new InvalidArgumentException('Detalle de cálculo vacío.');
        }

        if ($requiere !== 0 && $requiere !== 1) {
            $requiere = 0;
        }

        // Normalizar estado según requiere
        if ($requiere === 0) {
            $estado = 0;
        } else {
            if (!in_array($estado, [1, 2], true)) {
                $estado = 1; // pendiente
            }
        }

        // =========================
        // INSERT
        // =========================

        $sql = '
        INSERT INTO `nov_novedades`
            (
                `recepcionista_id`,
                `area`,
                `detalle`,
                `hotel`,
                `requiere_seguimiento`,
                `seguimiento_estado`,
                `tipo_seguimiento`,
                `flexkeeping_id`,
                `nivel_importancia`,
                `nivel_sugerido`,
                `score_calculado`,
                `detalle_calculo`,
                `tipo_novedad`,
                `fecha_registro`
            )
        VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $recepcionistaId,
            $area,
            $detalle,
            $hotel,
            $requiere,
            $estado,
            $tipoSeguimiento,
            $flexkeepingId,
            $nivelImportancia,
            $nivelSugerido,
            $scoreCalculado,
            $detalleCalculo,
            $tipoNovedad
        ]);

        return (int) $this->pdo->lastInsertId();
    }
    // Fin de la función guardar()


    /**
     * Lista novedades registradas en un día específico.
     *
     * @param string|null $fecha Fecha a consultar (Y-m-d). Si es null se usa hoy.
     * @return array Listado de novedades del día
     */
    public function listarPorDia($fecha = null)
    {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        $sql = 'SELECT `n`.*, `r`.`nombre` AS `recepcionista`
                FROM `nov_novedades` AS `n`
                JOIN `nov_recepcionistas` AS `r` ON `r`.`id` = `n`.`recepcionista_id`
                WHERE DATE(`n`.`fecha_registro`) = ?
                ORDER BY `n`.`fecha_registro` DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fecha]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fin de la función listarPorDia()


    /**
     * Alias de buscar() para una fecha específica.
     *
     * @param string $fecha Y-m-d
     * @param string $hotel
     * @param string $keyword
     * @param mixed $minImportancia (Obsoleto)
     * @param int $soloCriticas (0|1)
     * @return array
     */
    public function listarPorFecha($fecha, $hotel = '', $keyword = '', $minImportancia = null, $soloCriticas = 0)
    {
        // minImportancia ya no se aplica por decisión del proyecto (puedes mantenerlo si lo necesitas)
        return $this->buscar([
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'hotel' => $hotel,
            'keyword' => $keyword,
            'solo_criticas' => (int) $soloCriticas,
            'solo_pendientes' => 0,
        ]);
    }
    // Fin de la función listarPorFecha()
    // Fin de la función listarPorFecha()

    /**
     * Alias de buscar() para búsqueda con seguimiento.
     */
    public function listarPorFechaConSeguimiento(string $fecha, string $hotel = '', string $keyword = '', int $soloPendientes = 0, $minImportancia = null, $soloCriticas = 0): array
    {
        return $this->buscar([
            'fecha_inicio' => $fecha,
            'fecha_fin' => $fecha,
            'hotel' => $hotel,
            'keyword' => $keyword,
            'solo_criticas' => (int) $soloCriticas,
            'solo_pendientes' => (int) $soloPendientes,
        ]);
    }
    // Fin de la función listarPorFechaConSeguimiento()
    // Fin de la función listarPorFechaConSeguimiento()






    /**
     * Registra un archivo adjunto vinculado a una novedad.
     *
     * @param int $novedad_id
     * @param string $archivo Nombre del archivo en disco
     * @param string $tipo MIME type
     */
    public function guardarArchivo($novedad_id, $archivo, $tipo)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO nov_archivos (novedad_id, archivo, tipo) VALUES (?, ?, ?)'
        );
        $stmt->execute([(int) $novedad_id, $archivo, $tipo]);
    }
    // Fin de la función guardarArchivo()

    /**
     * Lista todos los archivos adjuntos de una novedad.
     *
     * @param int $novedad_id
     * @return array
     */
    public function listarArchivos($novedad_id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM nov_archivos WHERE novedad_id = ?');
        $stmt->execute([(int) $novedad_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Fin de la función listarArchivos()

    /**
     * Obtiene los datos completos de una novedad por su ID.
     *
     * @param int $id
     * @return array|false
     */
    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare(
            '
            SELECT n.*, r.nombre AS recepcionista
            FROM nov_novedades n
            JOIN nov_recepcionistas r ON n.recepcionista_id = r.id
            WHERE n.id = ?
            '
        );
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Fin de la función obtenerPorId()

    /**
     * Marca una novedad como seguimiento cerrado.
     *
     * @param int $id
     * @return void
     */
    public function cerrarSeguimiento(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE nov_novedades
         SET seguimiento_estado = 2
         WHERE id = ? AND requiere_seguimiento = 1'
        );
        $stmt->execute([$id]);
    }
    // Fin de la función cerrarSeguimiento()

    /**
     * Busca novedades usando filtros combinables y SQL seguro.
     *
     * Qué hace:
     * - Unifica búsqueda por fecha o rango, hotel, keyword, críticas y pendientes.
     * - Construye WHERE dinámico y usa prepared statements.
     *
     * Parámetros (array $filtros):
     * - fecha_inicio (YYYY-MM-DD) obligatorio
     * - fecha_fin (YYYY-MM-DD) opcional (si no viene, se asume igual a inicio)
     * - hotel (string) opcional
     * - keyword (string) opcional (busca en detalle)
     * - solo_criticas (int 0|1) opcional (>= 8)
     * - solo_pendientes (int 0|1) opcional (requiere seguimiento y estado pendiente)
     *
     * Retorna:
     * - array de filas
     *
     * Variables usadas:
     * - $this->pdo
     */
    public function buscar(array $filtros): array
    {
        $fechaInicio = trim((string) ($filtros['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($filtros['fecha_fin'] ?? $fechaInicio));
        $hotel = trim((string) ($filtros['hotel'] ?? ''));
        $keyword = trim((string) ($filtros['keyword'] ?? ''));
        $soloCriticas = (int) ($filtros['solo_criticas'] ?? 0);
        $soloPendientes = (int) ($filtros['solo_pendientes'] ?? 0);
        $tipoNovedad = trim((string) ($filtros['tipo_novedad'] ?? ''));
        $area = trim((string) ($filtros['area'] ?? ''));

        // ----------------------------
        // Validaciones defensivas
        // ----------------------------
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $fechaInicio = date('Y-m-d');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $fechaFin = $fechaInicio;
        }
        if ($fechaInicio > $fechaFin) {
            $tmp = $fechaInicio;
            $fechaInicio = $fechaFin;
            $fechaFin = $tmp;
        }

        if ($soloCriticas !== 1) {
            $soloCriticas = 0;
        }
        if ($soloPendientes !== 1) {
            $soloPendientes = 0;
        }

        // ----------------------------
        // SQL base
        // ----------------------------
        $sql = '
        SELECT n.*, r.nombre AS recepcionista_nombre, r.nombre AS recepcionista
        FROM nov_novedades n
        LEFT JOIN nov_recepcionistas r ON r.id = n.recepcionista_id
        WHERE DATE(n.fecha_registro) BETWEEN ? AND ?
    ';

        $params = [$fechaInicio, $fechaFin];

        // Hotel exacto
        if ($hotel !== '') {
            $sql .= ' AND n.hotel = ?';
            $params[] = $hotel;
        }

        // Keyword en detalle (case-insensitive)
        if ($keyword !== '') {
            $sql .= ' AND LOWER(n.detalle) LIKE LOWER(?)';
            $params[] = '%' . $keyword . '%';
        }

        // Solo críticas (>=8)
        if ($soloCriticas === 1) {
            $sql .= ' AND n.nivel_importancia >= 8';
        }

        // Solo pendientes (requiere seguimiento y pendiente)
        if ($soloPendientes === 1) {
            $sql .= ' AND n.requiere_seguimiento = 1 AND n.seguimiento_estado = 1';
        }

        // Departamento involucrado
        if ($tipoNovedad !== '') {
            $sql .= ' AND n.tipo_novedad = ?';
            $params[] = $tipoNovedad;
        }

        // Área del hotel
        if ($area !== '') {
            $sql .= ' AND n.area = ?';
            $params[] = $area;
        }

        $sql .= ' ORDER BY n.fecha_registro ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    // Fin de la función buscar()

}
