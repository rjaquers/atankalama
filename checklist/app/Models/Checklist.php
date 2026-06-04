<?php
namespace App\Models;

use App\Core\Model;

class Checklist extends Model
{
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM " . DB_PREFIX . "checklists WHERE estado = 'activo' ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "checklists SET estado = 'eliminado' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "checklists WHERE id = ?");
        $stmt->execute([$id]);
        $checklist = $stmt->fetch();

        if ($checklist) {
            $checklist['preguntas'] = $this->getQuestions($id);
        }
        return $checklist;
    }

    public function findByToken($token)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "checklists WHERE token_publico = ? AND modo = 'abierto' AND estado = 'activo'");
        $stmt->execute([$token]);
        $checklist = $stmt->fetch();
        if ($checklist) {
            $checklist['preguntas'] = $this->getQuestions($checklist['id']);
        }
        return $checklist;
    }

    public function create($nombre, $area, $createdBy, $modo = 'cerrado', $hotel = 'Atankalama')
    {
        $token = ($modo === 'abierto') ? bin2hex(random_bytes(32)) : null;
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "checklists (nombre, area, hotel, created_by, modo, token_publico) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $area, $hotel, $createdBy, $modo, $token]);
        return ['id' => (int) $this->db->lastInsertId(), 'token' => $token];
    }

    public function addQuestion($checklistId, $pregunta, $tipo, $min = null, $max = null, $orden = 0, $grupo = null)
    {
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "checklist_preguntas (checklist_id, grupo, pregunta, tipo_respuesta, escala_min, escala_max, orden) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$checklistId, $grupo, $pregunta, $tipo, $min, $max, $orden]);
    }

    public function update($id, $nombre, $area, $modo = 'cerrado', $hotel = 'Atankalama')
    {
        if ($modo === 'abierto') {
            $nuevoToken = bin2hex(random_bytes(32));
            $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "checklists SET nombre = ?, area = ?, hotel = ?, modo = 'abierto', token_publico = COALESCE(token_publico, ?) WHERE id = ?");
            return $stmt->execute([$nombre, $area, $hotel, $nuevoToken, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "checklists SET nombre = ?, area = ?, hotel = ?, modo = 'cerrado', token_publico = NULL WHERE id = ?");
            return $stmt->execute([$nombre, $area, $hotel, $id]);
        }
    }

    public function clearQuestions($checklistId)
    {
        $stmt = $this->db->prepare("DELETE FROM " . DB_PREFIX . "checklist_preguntas WHERE checklist_id = ?");
        return $stmt->execute([$checklistId]);
    }

    private function getQuestions($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "checklist_preguntas WHERE checklist_id = ? ORDER BY orden ASC");
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }
}
