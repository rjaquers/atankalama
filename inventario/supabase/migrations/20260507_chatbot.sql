-- Chatbot de bodega: tabla de mensajes de conversación
CREATE TABLE IF NOT EXISTS inv_chatbot_messages (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    session_id  VARCHAR(64)  NOT NULL,
    user_id     INT          NOT NULL,
    role        ENUM('user','assistant') NOT NULL,
    content     MEDIUMTEXT   NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id, created_at),
    INDEX idx_user    (user_id,    created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
