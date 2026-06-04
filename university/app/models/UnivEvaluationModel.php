<?php
/**
 * Modelo para gestionar los intentos de evaluación y sus respuestas detalladas.
 */
class UnivEvaluationModel extends Model
{
    /**
     * Registrar un nuevo intento de examen.
     */
    public function createAttempt($data)
    {
        $stmt = $this->conn->prepare("INSERT INTO univ_evaluations 
            (enrollment_id, numero_intento, score, preguntas_correctas, preguntas_totales, aprobado, tiempo_total_segundos, fecha_inicio, fecha_fin, ip_origen) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("iiiiiiisss", 
            $data['enrollment_id'], 
            $data['numero_intento'], 
            $data['score'], 
            $data['preguntas_correctas'], 
            $data['preguntas_totales'], 
            $data['aprobado'], 
            $data['tiempo_total_segundos'], 
            $data['fecha_inicio'], 
            $data['fecha_fin'], 
            $data['ip_origen']
        );
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Guardar el detalle de una respuesta dada en un examen.
     */
    public function saveAnswer($evaluationId, $questionId, $optionId, $esCorrecta)
    {
        $stmt = $this->conn->prepare("INSERT INTO univ_evaluation_answers 
            (evaluation_id, question_id, option_id_elegida, es_correcta) 
            VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $evaluationId, $questionId, $optionId, $esCorrecta);
        return $stmt->execute();
    }

    /**
     * Obtener el historial de intentos de un enrollment.
     */
    public function getHistoryByEnrollment($enrollId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM univ_evaluations WHERE enrollment_id = ? ORDER BY numero_intento DESC");
        $stmt->bind_param("i", $enrollId);
        $stmt->execute();
        $res = $stmt->get_result();
        $history = [];
        while ($row = $res->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }

    /**
     * Obtener un intento por su ID.
     */
    public function getById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM univ_evaluations WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtener el detalle de respuestas de un examen.
     */
    public function getDetails($evaluationId)
    {
        $sql = "SELECT a.*, q.texto_pregunta, 
                       oe.texto_opcion as respuesta_elegida,
                       oc.texto_opcion as respuesta_correcta
                FROM univ_evaluation_answers a
                JOIN univ_questions q ON a.question_id = q.id
                LEFT JOIN univ_options oe ON a.option_id_elegida = oe.id
                LEFT JOIN univ_options oc ON q.id = oc.question_id AND oc.es_correcta = 1
                WHERE a.evaluation_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $evaluationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
