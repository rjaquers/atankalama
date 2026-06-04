-- Registro de chatCalama en el sistema de autenticación centralizado del hotel
-- Ejecutar una sola vez en cat6852_hotel_tickets
-- Regla: auth-otp

INSERT IGNORE INTO `chk_apps` (`slug`, `nombre`, `descripcion`, `estado`)
VALUES ('chat', 'Sistema de Chat', 'Chat interno y gestión de tareas Hotel Atankalama', 1);

-- Dar acceso a todos los usuarios activos existentes
INSERT IGNORE INTO `chk_usuario_apps` (`usuario_id`, `app_id`)
SELECT u.id, a.id
FROM `chk_usuarios` u
CROSS JOIN `chk_apps` a
WHERE a.slug = 'chat'
  AND u.estado = 'activo';
