<?php
/**
 * Modelo para la gestión de cursos de la Universidad Atankalama.
 * Sigue el esquema de la tabla `univ_courses`.
 */
class UnivCourseModel extends Model
{
    /**
     * Obtener todos los cursos activos.
     */
    public function getAll($soloActivos = true)
    {
        $sql = "SELECT * FROM univ_courses";
        if ($soloActivos) {
            $sql .= " WHERE activo = 1";
        }
        $sql .= " ORDER BY fecha_creacion DESC";
        
        $res = $this->conn->query($sql);
        $courses = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $courses[] = $row;
            }
        }
        return $courses;
    }

    /**
     * Obtener un curso por su ID.
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM univ_courses WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Crear un nuevo curso.
     */
    public function create($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO univ_courses (nombre, descripcion, tipo, creditos, min_score_to_approve, total_preguntas_examen, tiempo_limite_minutos, max_intentos, vigencia_meses, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'] ?? '';
        $tipo = $data['tipo'] ?? 'opcional';
        $creditos = (int)($data['creditos'] ?? 0);
        $min_score = (int)($data['min_score_to_approve'] ?? 70);
        $total_preguntas = (int)($data['total_preguntas_examen'] ?? 10);
        $tiempo_limite = (int)($data['tiempo_limite_minutos'] ?? 15);
        $max_intentos = (int)($data['max_intentos'] ?? 3);
        $vigencia = isset($data['vigencia_meses']) ? (int)$data['vigencia_meses'] : null;
        $activo = (int)($data['activo'] ?? 1);

        $stmt->bind_param("ssisiiiiii", 
            $nombre, 
            $descripcion, 
            $tipo, 
            $creditos, 
            $min_score, 
            $total_preguntas, 
            $tiempo_limite, 
            $max_intentos, 
            $vigencia, 
            $activo
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Actualizar un curso existente.
     */
    public function update($id, $data)
    {
        $stmt = $this->conn->prepare("UPDATE univ_courses SET nombre = ?, descripcion = ?, tipo = ?, creditos = ?, min_score_to_approve = ?, total_preguntas_examen = ?, tiempo_limite_minutos = ?, max_intentos = ?, vigencia_meses = ?, activo = ? WHERE id = ?");
        
        $nombre = $data['nombre'];
        $descripcion = $data['descripcion'] ?? '';
        $tipo = $data['tipo'] ?? 'opcional';
        $creditos = (int)($data['creditos'] ?? 0);
        $min_score = (int)($data['min_score_to_approve'] ?? 70);
        $total_preguntas = (int)($data['total_preguntas_examen'] ?? 10);
        $tiempo_limite = (int)($data['tiempo_limite_minutos'] ?? 15);
        $max_intentos = (int)($data['max_intentos'] ?? 3);
        $vigencia = isset($data['vigencia_meses']) ? (int)$data['vigencia_meses'] : null;
        $activo = (int)($data['activo'] ?? 1);

        $stmt->bind_param("ssisiiiiiii", 
            $nombre, 
            $descripcion, 
            $tipo, 
            $creditos, 
            $min_score, 
            $total_preguntas, 
            $tiempo_limite, 
            $max_intentos, 
            $vigencia, 
            $activo,
            $id
        );
        
        return $stmt->execute();
    }

    /**
     * Eliminar un curso (o marcar como inactivo).
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM univ_courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
