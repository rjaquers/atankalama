<?php
/**
 * Modelo para la gestión del banco de preguntas y alternativas.
 */
class UnivQuestionModel extends Model
{
    /**
     * Obtener preguntas de un curso con sus opciones.
     */
    public function getByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM univ_questions WHERE course_id = ? AND activa = 1");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $res = $stmt->get_result();
        $questions = [];
        if ($res) {
            while ($q = $res->fetch_assoc()) {
                $q['options'] = $this->getOptions($q['id']);
                $questions[] = $q;
            }
        }
        return $questions;
    }

    /**
     * Obtener alternativas de una pregunta.
     */
    public function getOptions($questionId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM univ_options WHERE question_id = ?");
        $stmt->bind_param("i", $questionId);
        $stmt->execute();
        $res = $stmt->get_result();
        $options = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $options[] = $row;
            }
        }
        return $options;
    }

    /**
     * Guardar pregunta y sus opciones.
     */
    public function save($data)
    {
        $this->conn->begin_transaction();
        try {
            if (isset($data['id']) && $data['id'] > 0) {
                $stmt = $this->conn->prepare("UPDATE univ_questions SET texto_pregunta = ? WHERE id = ?");
                $stmt->bind_param("si", $data['texto_pregunta'], $data['id']);
                $stmt->execute();
                $questionId = $data['id'];
                
                // Limpiar opciones anteriores para simplificar (o actualizar una a una)
                $this->conn->query("DELETE FROM univ_options WHERE question_id = $questionId");
            } else {
                $stmt = $this->conn->prepare("INSERT INTO univ_questions (course_id, texto_pregunta) VALUES (?, ?)");
                $stmt->bind_param("is", $data['course_id'], $data['texto_pregunta']);
                $stmt->execute();
                $questionId = $this->conn->insert_id;
            }

            // Insertar nuevas opciones
            foreach ($data['options'] as $opt) {
                $stmtOpt = $this->conn->prepare("INSERT INTO univ_options (question_id, texto_opcion, es_correcta) VALUES (?, ?, ?)");
                $es_correcta = $opt['es_correcta'] ? 1 : 0;
                $stmtOpt->bind_param("isi", $questionId, $opt['texto_opcion'], $es_correcta);
                $stmtOpt->execute();
            }

            $this->conn->commit();
            return $questionId;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }

    /**
     * Eliminar pregunta (lógico o físico según prefieras, aquí físico por CASCADE).
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM univ_questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
