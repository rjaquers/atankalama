<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Token extends Model
{
    public function create($email, $token, $ip, $userAgent)
    {
        $expires = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRATION_MINUTES . ' minutes'));
        $stmt = $this->db->prepare("INSERT INTO " . DB_PREFIX . "login_tokens (email, token, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$email, $token, $expires, $ip, $userAgent]);
    }

    public function findValid($email, $token)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . DB_PREFIX . "login_tokens WHERE email = ? AND token = ? AND used = 0 AND expires_at > NOW() AND attempts < ?");
        $stmt->execute([$email, $token, OTP_MAX_ATTEMPTS]);
        return $stmt->fetch();
    }

    public function markAsUsed($id)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "login_tokens SET used = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function incrementAttempts($id)
    {
        $stmt = $this->db->prepare("UPDATE " . DB_PREFIX . "login_tokens SET attempts = attempts + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
