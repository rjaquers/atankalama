<?php
/**
 * Copyright © Rodrigo Jaque Escobar. Todos los derechos reservados.
 *
 * Modelo de mensajes del chatbot de bodega.
 * Persiste la conversación en inv_chatbot_messages y gestiona la sesión por usuario.
 */

require_once 'config/database.php';

class ChatbotMessage
{
    private $conn;

    public function __construct()
    {
        $db         = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Obtiene o crea un session_id para el usuario.
     * Reutiliza la sesión si hubo actividad en las últimas 2 horas.
     */
    public function getOrCreateSessionId(int $userId): string
    {
        if (!empty($_SESSION['chatbot_session_id'])) {
            $sid  = $_SESSION['chatbot_session_id'];
            $stmt = $this->conn->prepare(
                'SELECT MAX(created_at) AS last_at FROM inv_chatbot_messages WHERE session_id = ?'
            );
            $stmt->execute([$sid]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['last_at'] && strtotime($row['last_at']) > time() - 7200) {
                return $sid;
            }
        }

        $sid = bin2hex(random_bytes(32));
        $_SESSION['chatbot_session_id'] = $sid;
        return $sid;
    }

    /**
     * Guarda un turno de conversación.
     * $content puede ser string (texto plano) o array (bloques de contenido).
     */
    public function saveTurn(string $sessionId, int $userId, string $role, $content): void
    {
        $stored = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $stmt   = $this->conn->prepare(
            'INSERT INTO inv_chatbot_messages (session_id, user_id, role, content) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$sessionId, $userId, $role, $stored]);
    }

    /**
     * Recupera los últimos N mensajes en formato Messages API para enviar a Claude.
     * Retorna array de {role, content} alternando user/assistant.
     */
    public function getRecentMessages(string $sessionId, int $limit = 30): array
    {
        $stmt = $this->conn->prepare(
            'SELECT role, content FROM inv_chatbot_messages
             WHERE session_id = ?
             ORDER BY id DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $sessionId);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));

        return array_map(function ($row) {
            $content = json_decode($row['content'], true);
            if ($content === null) {
                $content = $row['content'];
            }
            return ['role' => $row['role'], 'content' => $content];
        }, $rows);
    }

    /**
     * Retorna mensajes simplificados para renderizar en la vista HTML inicial.
     * Filtra los tool_result y extrae solo texto visible.
     */
    public function getDisplayMessages(string $sessionId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT role, content, created_at FROM inv_chatbot_messages
             WHERE session_id = ?
             ORDER BY id ASC'
        );
        $stmt->execute([$sessionId]);

        $display = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $content = json_decode($row['content'], true);

            if ($row['role'] === 'user') {
                // Saltar bloques tool_result (no son texto del usuario)
                if (is_array($content) && !empty($content) && ($content[0]['type'] ?? '') === 'tool_result') {
                    continue;
                }
                $text = $this->extractText($content);
            } elseif ($row['role'] === 'assistant') {
                $text = $this->extractText($content);
            } else {
                continue;
            }

            if ($text !== '') {
                $display[] = [
                    'role'  => $row['role'],
                    'text'  => $text,
                    'fecha' => $row['created_at'],
                ];
            }
        }

        return $display;
    }

    private function extractText($content): string
    {
        if (is_string($content)) return $content;
        if (!is_array($content)) return '';

        $text = '';
        foreach ($content as $block) {
            if (is_string($block)) {
                $text .= $block;
            } elseif (($block['type'] ?? '') === 'text') {
                $text .= $block['text'] ?? '';
            }
        }
        return $text;
    }
}
