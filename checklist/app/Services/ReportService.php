<?php
namespace App\Services;

use App\Core\Database;
use PDO;

class ReportService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Reporte 1 – Historial por Usuario
     */
    public function getUserHistory($email)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, c.nombre as checklist_nombre 
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            WHERE e.ejecutado_por = ? AND e.activo = 1
            ORDER BY e.fecha_evaluacion DESC
        ");
        $stmt->execute([$email]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener todas las evaluaciones para el listado principal
     */
    public function getAllEvaluations()
    {
        $stmt = $this->db->query("
            SELECT e.*, c.nombre as checklist_nombre, c.area
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            WHERE e.activo = 1
            ORDER BY e.fecha_evaluacion DESC
            LIMIT 1000
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener el detalle completo de una evaluación con sus respuestas
     */
    public function getEvaluationDetail($id)
    {
        // Info básica y checklist
        $stmt = $this->db->prepare("
            SELECT e.*, c.nombre as checklist_nombre, c.area
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            WHERE e.id = ? AND e.activo = 1
        ");
        $stmt->execute([$id]);
        $evaluation = $stmt->fetch();

        if ($evaluation) {
            // Obtener preguntas y respuestas vinculadas
            $stmt = $this->db->prepare("
                SELECT cp.pregunta, cp.tipo_respuesta, cp.grupo,
                       er.respuesta_boolean, er.respuesta_texto, er.respuesta_numerica, er.respuesta_foto
                FROM " . DB_PREFIX . "checklist_preguntas cp
                JOIN " . DB_PREFIX . "evaluacion_respuestas er ON cp.id = er.pregunta_id
                WHERE er.evaluacion_id = ?
                ORDER BY cp.orden ASC
            ");
            $stmt->execute([$id]);
            $respuestas = $stmt->fetchAll();
            $evaluation['respuestas'] = $respuestas;

            // Calcular estadísticas de cumplimiento
            $total_si = 0;
            $total_no = 0;
            $suma_numerica = 0;
            $cont_numerica = 0;

            foreach ($respuestas as $res) {
                if ($res['tipo_respuesta'] === 'boolean') {
                    if ($res['respuesta_boolean'] !== null && (int) $res['respuesta_boolean'] === 1)
                        $total_si++;
                    elseif ($res['respuesta_boolean'] !== null && (int) $res['respuesta_boolean'] === 0)
                        $total_no++;
                } elseif ($res['tipo_respuesta'] === 'numeric_scale' && $res['respuesta_numerica'] !== null) {
                    $suma_numerica += $res['respuesta_numerica'];
                    $cont_numerica++;
                }
            }

            $total_preguntas_sn = $total_si + $total_no;
            $cumplimiento = $total_preguntas_sn > 0 ? round(($total_si / $total_preguntas_sn) * 100, 1) : 0;
            $promedio_numerico = $cont_numerica > 0 ? round($suma_numerica / $cont_numerica, 1) : 0;

            $evaluation['stats'] = [
                'total_si' => $total_si,
                'total_no' => $total_no,
                'cumplimiento' => $cumplimiento,
                'promedio_numerico' => $promedio_numerico,
                'total_respuestas' => count($respuestas)
            ];
        }

        return $evaluation;
    }

    /**
     * Reporte 4 – Actividad del Sistema (Audit Logs)
     */
    public function getSystemActivity($filters = [], $limit = 100)
    {
        $whereChunks = [];
        $params = [];

        if (!empty($filters['startDate'])) {
            $whereChunks[] = "created_at >= ?";
            $params[] = $filters['startDate'] . " 00:00:00";
        }
        if (!empty($filters['endDate'])) {
            $whereChunks[] = "created_at <= ?";
            $params[] = $filters['endDate'] . " 23:59:59";
        }
        if (!empty($filters['user'])) {
            $whereChunks[] = "user_email LIKE ?";
            $params[] = "%" . $filters['user'] . "%";
        }
        if (!empty($filters['modulo'])) {
            $whereChunks[] = "modulo = ?";
            $params[] = $filters['modulo'];
        }
        if (!empty($filters['nivel'])) {
            $whereChunks[] = "nivel = ?";
            $params[] = $filters['nivel'];
        }

        $where = !empty($whereChunks) ? " WHERE " . implode(" AND ", $whereChunks) : "";

        $sql = "SELECT * FROM " . DB_PREFIX . "system_logs $where ORDER BY created_at DESC LIMIT " . (int) $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getLogModules()
    {
        $stmt = $this->db->query("SELECT DISTINCT modulo FROM " . DB_PREFIX . "system_logs ORDER BY modulo ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obtener estadísticas de encuestas abiertas (modo = 'abierto')
     */
    public function getSurveyStats($filters = [])
    {
        $whereBase = ["c.modo = 'abierto'", "e.activo = 1"];
        $params = [];

        if (!empty($filters['startDate'])) {
            $whereBase[] = "e.fecha_evaluacion >= ?";
            $params[] = $filters['startDate'] . " 00:00:00";
        }
        if (!empty($filters['endDate'])) {
            $whereBase[] = "e.fecha_evaluacion <= ?";
            $params[] = $filters['endDate'] . " 23:59:59";
        }
        if (!empty($filters['checklist_id'])) {
            $whereBase[] = "c.id = ?";
            $params[] = (int) $filters['checklist_id'];
        }

        $where = " WHERE " . implode(" AND ", $whereBase);

        // 1. Totales generales
        $stmtTotales = $this->db->prepare("
            SELECT COUNT(DISTINCT e.id) as total_respuestas,
                   COUNT(DISTINCT c.id) as total_checklists,
                   SUM(CASE WHEN DATE(e.fecha_evaluacion) >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) as respuestas_mes
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            $where
        ");
        $stmtTotales->execute($params);
        $totales = $stmtTotales->fetch();

        // 2. Respuestas por checklist
        $stmtPorChecklist = $this->db->prepare("
            SELECT c.id, c.nombre, c.area,
                   COUNT(DISTINCT e.id) as total_respuestas,
                   MAX(e.fecha_evaluacion) as ultima_respuesta
            FROM " . DB_PREFIX . "checklists c
            LEFT JOIN " . DB_PREFIX . "evaluaciones e ON c.id = e.checklist_id AND e.activo = 1
            WHERE c.modo = 'abierto' AND c.estado = 'activo'
            GROUP BY c.id, c.nombre, c.area
            ORDER BY total_respuestas DESC
        ");
        $stmtPorChecklist->execute();
        $porChecklist = $stmtPorChecklist->fetchAll();

        // 3. Tendencia últimos 30 días
        $paramsT = [];
        $whereT = ["c.modo = 'abierto'", "e.activo = 1", "e.fecha_evaluacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)"];
        if (!empty($filters['checklist_id'])) {
            $whereT[] = "c.id = ?";
            $paramsT[] = (int) $filters['checklist_id'];
        }
        $stmtTendencia = $this->db->prepare("
            SELECT DATE(e.fecha_evaluacion) as dia, COUNT(*) as total
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            WHERE " . implode(" AND ", $whereT) . "
            GROUP BY dia ORDER BY dia ASC
        ");
        $stmtTendencia->execute($paramsT);
        $tendencia = $stmtTendencia->fetchAll();

        // 4. Desglose por pregunta (solo si se filtra por checklist)
        $porPregunta = [];
        if (!empty($filters['checklist_id'])) {
            $stmtPreg = $this->db->prepare("
                SELECT cp.pregunta, cp.tipo_respuesta, cp.grupo, cp.orden,
                       COUNT(er.id) as total,
                       SUM(CASE WHEN er.respuesta_boolean = 1 THEN 1 ELSE 0 END) as total_si,
                       SUM(CASE WHEN er.respuesta_boolean = 0 THEN 1 ELSE 0 END) as total_no,
                       AVG(er.respuesta_numerica) as promedio_num,
                       MIN(er.respuesta_numerica) as min_num,
                       MAX(er.respuesta_numerica) as max_num
                FROM " . DB_PREFIX . "checklist_preguntas cp
                LEFT JOIN " . DB_PREFIX . "evaluacion_respuestas er ON cp.id = er.pregunta_id
                LEFT JOIN " . DB_PREFIX . "evaluaciones e ON er.evaluacion_id = e.id AND e.activo = 1
                WHERE cp.checklist_id = ?
                GROUP BY cp.id, cp.pregunta, cp.tipo_respuesta, cp.grupo, cp.orden
                ORDER BY cp.orden ASC
            ");
            $stmtPreg->execute([(int) $filters['checklist_id']]);
            $porPregunta = $stmtPreg->fetchAll();

            // Respuestas de texto recientes
            $stmtTexto = $this->db->prepare("
                SELECT cp.pregunta, er.respuesta_texto, e.fecha_evaluacion
                FROM " . DB_PREFIX . "checklist_preguntas cp
                JOIN " . DB_PREFIX . "evaluacion_respuestas er ON cp.id = er.pregunta_id
                JOIN " . DB_PREFIX . "evaluaciones e ON er.evaluacion_id = e.id AND e.activo = 1
                WHERE cp.checklist_id = ? AND cp.tipo_respuesta = 'text' AND er.respuesta_texto IS NOT NULL AND er.respuesta_texto != ''
                ORDER BY e.fecha_evaluacion DESC LIMIT 50
            ");
            $stmtTexto->execute([(int) $filters['checklist_id']]);
            $respuestasTexto = $stmtTexto->fetchAll();

            // Agrupar textos por pregunta
            $textosPorPregunta = [];
            foreach ($respuestasTexto as $row) {
                $textosPorPregunta[$row['pregunta']][] = [
                    'texto' => $row['respuesta_texto'],
                    'fecha' => $row['fecha_evaluacion']
                ];
            }
            foreach ($porPregunta as &$p) {
                $p['respuestas_texto'] = $textosPorPregunta[$p['pregunta']] ?? [];
            }
            unset($p);
        }

        return [
            'totales'        => $totales,
            'por_checklist'  => $porChecklist,
            'tendencia'      => $tendencia,
            'por_pregunta'   => $porPregunta,
        ];
    }

    /**
     * Obtener datos de encuestas para exportar a Excel.
     * Retorna preguntas (columnas) y evaluaciones (filas) con sus respuestas.
     */
    public function getSurveyExportData($filters = [])
    {
        $whereBase = ["c.modo = 'abierto'", "e.activo = 1"];
        $params = [];

        if (!empty($filters['startDate'])) {
            $whereBase[] = "e.fecha_evaluacion >= ?";
            $params[] = $filters['startDate'] . " 00:00:00";
        }
        if (!empty($filters['endDate'])) {
            $whereBase[] = "e.fecha_evaluacion <= ?";
            $params[] = $filters['endDate'] . " 23:59:59";
        }
        if (!empty($filters['checklist_id'])) {
            $whereBase[] = "c.id = ?";
            $params[] = (int) $filters['checklist_id'];
        }

        $where = implode(" AND ", $whereBase);

        // Obtener preguntas del checklist (si hay filtro), ordenadas
        $preguntas = [];
        if (!empty($filters['checklist_id'])) {
            $stmtP = $this->db->prepare("
                SELECT id, pregunta, tipo_respuesta, grupo, orden
                FROM " . DB_PREFIX . "checklist_preguntas
                WHERE checklist_id = ?
                ORDER BY orden ASC
            ");
            $stmtP->execute([(int) $filters['checklist_id']]);
            $preguntas = $stmtP->fetchAll();
        }

        // Obtener todas las respuestas en una sola consulta
        $stmt = $this->db->prepare("
            SELECT
                e.id            AS eval_id,
                e.fecha_evaluacion,
                c.nombre        AS checklist_nombre,
                c.area,
                cp.id           AS pregunta_id,
                cp.pregunta,
                cp.tipo_respuesta,
                cp.orden,
                er.respuesta_boolean,
                er.respuesta_numerica,
                er.respuesta_texto
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            LEFT JOIN " . DB_PREFIX . "checklist_preguntas cp ON cp.checklist_id = c.id
            LEFT JOIN " . DB_PREFIX . "evaluacion_respuestas er
                   ON er.evaluacion_id = e.id AND er.pregunta_id = cp.id
            WHERE $where
            ORDER BY e.fecha_evaluacion DESC, cp.orden ASC
        ");
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        // Pivotar: una fila por evaluación, columnas por pregunta
        $evaluaciones = [];
        foreach ($filas as $f) {
            $eid = $f['eval_id'];
            if (!isset($evaluaciones[$eid])) {
                $evaluaciones[$eid] = [
                    'id'                 => $eid,
                    'fecha_evaluacion'   => $f['fecha_evaluacion'],
                    'checklist_nombre'   => $f['checklist_nombre'],
                    'area'               => $f['area'],
                    'respuestas'         => [],
                ];
            }
            if ($f['pregunta_id']) {
                $evaluaciones[$eid]['respuestas'][$f['pregunta_id']] = [
                    'boolean'  => $f['respuesta_boolean'],
                    'numerica' => $f['respuesta_numerica'],
                    'texto'    => $f['respuesta_texto'],
                    'tipo'     => $f['tipo_respuesta'],
                ];
            }
        }

        return [
            'preguntas'    => $preguntas,
            'evaluaciones' => array_values($evaluaciones),
        ];
    }

    /**
     * Obtener todos los checklists abiertos (para filtro)
     */
    public function getOpenChecklists()
    {
        $stmt = $this->db->query("SELECT id, nombre, area FROM " . DB_PREFIX . "checklists WHERE modo = 'abierto' AND estado = 'activo' ORDER BY nombre ASC");
        return $stmt->fetchAll();
    }

    /**
     * Resumen mensual ejecutivo: controles por hotel y defectos
     */
    public function getMonthlySummary($year, $month)
    {
        $start = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $end   = date('Y-m-t 23:59:59', mktime(0, 0, 0, $month, 1, $year));

        $stmtControles = $this->db->prepare("
            SELECT
                CASE
                    WHEN e.evaluado_nombre IN ('Atankalama', 'Atankalama Inn') THEN e.evaluado_nombre
                    ELSE c.hotel
                END AS hotel,
                COUNT(DISTINCT e.id) AS total
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            WHERE e.activo = 1
              AND e.fecha_evaluacion >= ?
              AND e.fecha_evaluacion <= ?
            GROUP BY hotel
        ");
        $stmtControles->execute([$start, $end]);
        $rows = $stmtControles->fetchAll();

        $controlesPorHotel = [];
        $totalControles = 0;
        foreach ($rows as $r) {
            $controlesPorHotel[$r['hotel']] = (int)$r['total'];
            $totalControles += (int)$r['total'];
        }

        $stmtDefectos = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM " . DB_PREFIX . "evaluacion_respuestas er
            JOIN " . DB_PREFIX . "evaluaciones e ON er.evaluacion_id = e.id
            WHERE er.respuesta_boolean = 0
              AND e.activo = 1
              AND e.fecha_evaluacion >= ?
              AND e.fecha_evaluacion <= ?
        ");
        $stmtDefectos->execute([$start, $end]);
        $totalDefectos = (int)$stmtDefectos->fetchColumn();

        $pctDefectos = $totalControles > 0
            ? round(($totalDefectos / max(1, $totalControles * 10)) * 100, 1)
            : 0;

        return [
            'por_hotel'      => $controlesPorHotel,
            'total_controles'=> $totalControles,
            'total_defectos' => $totalDefectos,
        ];
    }

    /**
     * Obtener estadísticas globales agrupadas
     */
    public function getGlobalStats($filters = [], $limit = 10, $page = 1)
    {
        $whereChunks = ["er.respuesta_boolean IS NOT NULL", "e.activo = 1"];
        $params = [];

        if (!empty($filters['startDate'])) {
            $whereChunks[] = "e.fecha_evaluacion >= ?";
            $params[] = $filters['startDate'] . " 00:00:00";
        }
        if (!empty($filters['endDate'])) {
            $whereChunks[] = "e.fecha_evaluacion <= ?";
            $params[] = $filters['endDate'] . " 23:59:59";
        }
        if (!empty($filters['area'])) {
            $whereChunks[] = "c.area = ?";
            $params[] = $filters['area'];
        }
        if (!empty($filters['persona'])) {
            $whereChunks[] = "(e.evaluado_nombre LIKE ? OR e.evaluado_apellido LIKE ?)";
            $params[] = "%" . $filters['persona'] . "%";
            $params[] = "%" . $filters['persona'] . "%";
        }
        
        if (!empty($filters['tipo'])) {
            if ($filters['tipo'] === 'habitaciones') {
                $whereChunks[] = "e.evaluado_nombre IN ('Atankalama', 'Atankalama Inn')";
            } elseif ($filters['tipo'] === 'personas') {
                $whereChunks[] = "e.evaluado_nombre NOT IN ('Atankalama', 'Atankalama Inn')";
            }
        }

        if (!empty($filters['hotel'])) {
            if (($filters['tipo'] ?? '') === 'habitaciones') {
                $whereChunks[] = "e.evaluado_nombre = ?";
            } else {
                $whereChunks[] = "c.hotel = ?";
            }
            $params[] = $filters['hotel'];
        }

        $where = " WHERE " . implode(" AND ", $whereChunks);

        // 1. Cumplimiento por Área
        $stmtAreas = $this->db->prepare("
            SELECT c.area, 
                   COUNT(er.id) as total_respuestas,
                   SUM(CASE WHEN er.respuesta_boolean = 1 THEN 1 ELSE 0 END) as total_si,
                   SUM(CASE WHEN er.respuesta_boolean = 0 THEN 1 ELSE 0 END) as total_no
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            JOIN " . DB_PREFIX . "evaluacion_respuestas er ON e.id = er.evaluacion_id
            $where
            GROUP BY c.area
        ");
        $stmtAreas->execute($params);
        $statsArea = $stmtAreas->fetchAll();

        // 2. Cumplimiento por Persona (Evaluado)
        $stmtPersona = $this->db->prepare("
            SELECT e.evaluado_nombre, e.evaluado_apellido,
                   COUNT(er.id) as total_respuestas,
                   SUM(CASE WHEN er.respuesta_boolean = 1 THEN 1 ELSE 0 END) as total_si,
                   SUM(CASE WHEN er.respuesta_boolean = 0 THEN 1 ELSE 0 END) as total_no
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "evaluacion_respuestas er ON e.id = er.evaluacion_id
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            $where
            GROUP BY e.evaluado_nombre, e.evaluado_apellido
            ORDER BY total_respuestas DESC
            LIMIT " . (int)$limit . "
        ");
        $stmtPersona->execute($params);
        $statsPersona = $stmtPersona->fetchAll();

        // 3. Cumplimiento por Mes
        $stmtMes = $this->db->prepare("
            SELECT DATE_FORMAT(e.fecha_evaluacion, '%Y-%m') as mes,
                   COUNT(DISTINCT e.id) as total_evaluaciones,
                   COUNT(er.id) as total_respuestas,
                   SUM(CASE WHEN er.respuesta_boolean = 1 THEN 1 ELSE 0 END) as total_si,
                   SUM(CASE WHEN er.respuesta_boolean = 0 THEN 1 ELSE 0 END) as total_no
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "evaluacion_respuestas er ON e.id = er.evaluacion_id
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            $where
            GROUP BY mes
            ORDER BY mes ASC
        ");
        $stmtMes->execute($params);
        $statsMes = $stmtMes->fetchAll();

        // 4. Detalle por Evaluación (ID) — paginado
        $perPage = (int)$limit;
        $offset  = (max(1, (int)$page) - 1) * $perPage;

        $stmtEvaCount = $this->db->prepare("
            SELECT COUNT(DISTINCT e.id) as total
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "evaluacion_respuestas er ON e.id = er.evaluacion_id
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            $where
        ");
        $stmtEvaCount->execute($params);
        $totalEvaluaciones = (int)$stmtEvaCount->fetchColumn();

        $stmtEva = $this->db->prepare("
            SELECT e.id as evaluacion_id, e.evaluado_nombre, e.evaluado_apellido, c.nombre as checklist_nombre, e.fecha_evaluacion,
                   COUNT(er.id) as total_respuestas,
                   SUM(CASE WHEN er.respuesta_boolean = 1 THEN 1 ELSE 0 END) as total_si,
                   SUM(CASE WHEN er.respuesta_boolean = 0 THEN 1 ELSE 0 END) as total_no
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "evaluacion_respuestas er ON e.id = er.evaluacion_id
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            $where
            GROUP BY e.id, e.evaluado_nombre, e.evaluado_apellido, c.nombre, e.fecha_evaluacion
            ORDER BY e.fecha_evaluacion DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmtEva->execute($params);
        $statsEva = $stmtEva->fetchAll();

        // 5. Cantidad de Checklists por Tipo
        $stmtTipo = $this->db->prepare("
            SELECT c.nombre as checklist_nombre,
                   COUNT(DISTINCT e.id) as total_creados
            FROM " . DB_PREFIX . "evaluaciones e
            JOIN " . DB_PREFIX . "evaluacion_respuestas er ON e.id = er.evaluacion_id
            JOIN " . DB_PREFIX . "checklists c ON e.checklist_id = c.id
            $where
            GROUP BY c.id, c.nombre
            ORDER BY total_creados DESC
        ");
        $stmtTipo->execute($params);
        $statsTipo = $stmtTipo->fetchAll();

        return [
            'por_area'           => $statsArea,
            'por_persona'        => $statsPersona,
            'por_mes'            => $statsMes,
            'por_evaluacion'     => $statsEva,
            'total_evaluaciones' => $totalEvaluaciones,
            'por_tipo'           => $statsTipo,
        ];
    }
}
