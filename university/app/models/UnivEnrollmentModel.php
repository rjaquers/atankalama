<?php
/**
 * Modelo para gestionar las inscripciones (enrollments) y el progreso de los alumnos.
 */
class UnivEnrollmentModel extends Model
{
    /**
     * Obtener los cursos asignados a un usuario con su estado actual.
     */
    public function getByUser($userId)
    {
        $sql = "SELECT e.*, c.nombre, c.tipo, c.creditos, c.total_preguntas_examen, c.descripcion
                FROM univ_enrollments e
                JOIN univ_courses c ON e.course_id = c.id
                WHERE e.user_id = ?
                ORDER BY e.status DESC, e.fecha_asignacion DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $enrollments = [];
        while ($row = $res->fetch_assoc()) {
            $enrollments[] = $row;
        }
        return $enrollments;
    }

    /**
     * Obtener un enrollment específico por ID, validando el usuario.
     */
    public function getByIdAndUser($enrollId, $userId)
    {
        $stmt = $this->conn->prepare("SELECT e.*, c.nombre, c.total_preguntas_examen, c.min_score_to_approve 
                                      FROM univ_enrollments e 
                                      JOIN univ_courses c ON e.course_id = c.id 
                                      WHERE e.id = ? AND e.user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $enrollId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Actualizar el progreso (página actual).
     */
    public function updateProgress($enrollId, $pageNumber)
    {
        // Si es la primera vez que entra, marcamos fecha_inicio
        $stmt = $this->conn->prepare("UPDATE univ_enrollments SET 
                                      pagina_actual = ?, 
                                      status = 'en_progreso',
                                      fecha_inicio = IFNULL(fecha_inicio, CURRENT_TIMESTAMP)
                                      WHERE id = ?");
        $stmt->bind_param("ii", $pageNumber, $enrollId);
        return $stmt->execute();
    }

    /**
     * Crear una inscripción automática si no existe.
     */
    public function autoEnroll($userId, $courseId, $version)
    {
        // Verificar si ya existe un enrollment activo para este ciclo
        $stmt = $this->conn->prepare("SELECT id FROM univ_enrollments WHERE user_id = ? AND course_id = ? AND status IN ('asignado', 'en_progreso')");
        $stmt->bind_param("ii", $userId, $courseId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) return true;

        $stmt = $this->conn->prepare("INSERT INTO univ_enrollments (user_id, course_id, course_version, status) VALUES (?, ?, ?, 'asignado')");
        $stmt->bind_param("iii", $userId, $courseId, $version);
        return $stmt->execute();
    }
}
