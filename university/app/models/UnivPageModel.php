<?php
/**
 * Modelo para la gestión de las páginas (contenido) de los cursos.
 */
class UnivPageModel extends Model
{
    /**
     * Obtener todas las páginas de un curso ordenadas.
     */
    public function getByCourse($courseId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM univ_pages WHERE course_id = ? ORDER BY orden ASC");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $res = $stmt->get_result();
        $pages = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $pages[] = $row;
            }
        }
        return $pages;
    }

    /**
     * Guardar o actualizar una página.
     */
    public function save($data)
    {
        if (isset($data['id']) && $data['id'] > 0) {
            $stmt = $this->conn->prepare("UPDATE univ_pages SET titulo = ?, tipo = ?, contenido = ?, tiempo_minimo_segundos = ? WHERE id = ?");
            $stmt->bind_param("sssii", $data['titulo'], $data['tipo'], $data['contenido'], $data['tiempo_minimo_segundos'], $data['id']);
        } else {
            $stmt = $this->conn->prepare("INSERT INTO univ_pages (course_id, orden, titulo, tipo, contenido, tiempo_minimo_segundos) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssi", $data['course_id'], $data['orden'], $data['titulo'], $data['tipo'], $data['contenido'], $data['tiempo_minimo_segundos']);
        }
        return $stmt->execute();
    }

    /**
     * Eliminar una página.
     */
    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM univ_pages WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Reordenar páginas (recibe array de id => orden).
     */
    public function reorder($orders)
    {
        foreach ($orders as $id => $order) {
            $stmt = $this->conn->prepare("UPDATE univ_pages SET orden = ? WHERE id = ?");
            $stmt->bind_param("ii", $order, $id);
            $stmt->execute();
        }
        return true;
    }
}
