<?php
class NotificationModel extends Model
{
    public function insert($userId, $title, $message, $channel, $status = 'sent')
    {
        $stmt = $this->conn->prepare("
            INSERT INTO notifications(user_id, title, message, channel, status)
            VALUES(?,?,?,?,?)
        ");
        $stmt->bind_param("issss", $userId, $title, $message, $channel, $status);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    public function latestForUser($userId, $limit = 10)
    {
        $limit = (int)$limit;
        $stmt = $this->conn->prepare("
            SELECT id, title, message, channel, status, created_at
            FROM notifications
            WHERE user_id IS NULL OR user_id = ?
            ORDER BY id DESC
            LIMIT $limit
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = [];
        if ($res) while ($row = $res->fetch_assoc()) $out[] = $row;
        return $out;
    }
}
