-- ============================================================
-- PRIMER USUARIO ADMINISTRADOR — Chat Interno Atankalama
-- Ejecutar UNA SOLA VEZ en la base de datos cat6852_hotel_tickets
-- ============================================================
-- NOTA: El acceso es por OTP (código al correo), no por contraseña.
--       El campo password_hash es requerido por la tabla pero no se usa.
-- ============================================================

INSERT INTO `chat_usuarios`
  (`nombre`, `email`, `password_hash`, `rol_id`, `area_id`, `es_jefe`, `estado`, `created_at`)
VALUES (
  'Rodrigo Jaque',
  'rjaquers@gmail.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  1,      -- rol_id 1 = Administrador
  NULL,   -- sin área por defecto (puede asignarse luego desde el panel)
  0,
  1,      -- estado activo
  NOW()
);

-- Verificar que se insertó correctamente:
SELECT id, nombre, email, rol_id, estado FROM chat_usuarios WHERE email = 'rjaquers@gmail.com';
