<?php
namespace App\Models;

use App\Core\Model;

class Evaluation extends Model
{
    public function create($checklistId, $nombre, $apellido, $ejecutadoPor, $fechaInicio = null, $fechaFin = null)
    {
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "evaluaciones (checklist_id, evaluado_nombre, evaluado_apellido, ejecutado_por, fecha_inicio, fecha_fin, activo) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$checklistId, $nombre, $apellido, $ejecutadoPor, $fechaInicio, $fechaFin]);
        return $this->db->lastInsertId();
    }

    public function saveResponse($evaluacionId, $preguntaId, $respuesta, $foto = null)
    {
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "evaluacion_respuestas (evaluacion_id, pregunta_id, respuesta_boolean, respuesta_texto, respuesta_numerica, respuesta_foto) VALUES (?, ?, ?, ?, ?, ?)");

        $bool = $txt = $num = null;

        // Determinar el tipo de respuesta basado en el valor
        if ($respuesta === '') {
            // No hacer nada, se quedan como null
        } elseif ($respuesta === '1' || $respuesta === '0' || is_bool($respuesta)) {
            $bool = ($respuesta === '1' || $respuesta === true) ? 1 : 0;
        } elseif (is_numeric($respuesta)) {
            $num = $respuesta;
        } else {
            $txt = $respuesta;
        }

        return $stmt->execute([$evaluacionId, $preguntaId, $bool, $txt, $num, $foto]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "evaluaciones SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
