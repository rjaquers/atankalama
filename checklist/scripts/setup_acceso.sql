-- ============================================================
-- Setup AccesoBootstrap para app: checklist
-- Ejecutar en: cat6852_hotel_tickets
-- ============================================================

-- 1. Registrar la app (si no existe)
INSERT IGNORE INTO chk_apps (slug, nombre, descripcion, estado)
VALUES ('checklist', 'Sistema de Checklists', 'Gestión de checklists del hotel', 'activo');

-- 2. Dar acceso a todos los usuarios activos existentes
INSERT IGNORE INTO chk_usuario_apps (usuario_id, app_id)
SELECT u.id, a.id
FROM chk_usuarios u
CROSS JOIN chk_apps a
WHERE a.slug = 'checklist'
  AND u.estado = 'activo';

-- 3. Registrar secciones (todas como 'publica' — control de admin queda en la app)
SET @app_id = (SELECT id FROM chk_apps WHERE slug = 'checklist' LIMIT 1);

INSERT IGNORE INTO acc_secciones (app_id, slug, nombre, tipo, estado) VALUES
(@app_id, 'dashboard',                   'Panel principal',        'publica', 'activo'),
(@app_id, 'checklists',                  'Checklists',             'publica', 'activo'),
(@app_id, 'checklists/nuevo',            'Nuevo checklist',        'publica', 'activo'),
(@app_id, 'checklists/editar',           'Editar checklist',       'publica', 'activo'),
(@app_id, 'evaluaciones',                'Evaluaciones',           'publica', 'activo'),
(@app_id, 'evaluaciones/ejecutar',       'Ejecutar evaluación',    'publica', 'activo'),
(@app_id, 'api/evaluaciones/guardar',    'API guardar evaluación', 'publica', 'activo'),
(@app_id, 'reportes',                    'Reportes',               'publica', 'activo'),
(@app_id, 'reportes/stats',              'Estadísticas',           'publica', 'activo'),
(@app_id, 'reportes/ver',                'Ver reporte',            'publica', 'activo'),
(@app_id, 'reportes/logs',               'Log de auditoría',       'publica', 'activo'),
(@app_id, 'reportes/encuestas',          'Encuestas',              'publica', 'activo'),
(@app_id, 'reportes/encuestas/exportar', 'Exportar encuestas',     'publica', 'activo'),
(@app_id, 'usuarios',                    'Gestión de usuarios',    'publica', 'activo'),
(@app_id, 'usuarios/guardar',            'Guardar usuario',        'publica', 'activo'),
(@app_id, 'usuarios/actualizar',         'Actualizar usuario',     'publica', 'activo'),
(@app_id, 'usuarios/eliminar',           'Eliminar usuario',       'publica', 'activo'),
(@app_id, 'areas',                       'Áreas / Departamentos',  'publica', 'activo'),
(@app_id, 'areas/guardar',               'Guardar área',           'publica', 'activo'),
(@app_id, 'areas/eliminar',              'Eliminar área',          'publica', 'activo'),
(@app_id, 'api/checklists/guardar',      'API guardar checklist',  'publica', 'activo'),
(@app_id, 'api/checklists/actualizar',   'API actualizar checklist','publica','activo'),
(@app_id, 'api/checklists/eliminar',     'API eliminar checklist', 'publica', 'activo'),
(@app_id, 'api/reportes/eliminar',       'API eliminar reporte',   'publica', 'activo');

-- ============================================================
-- Verificación
-- ============================================================
SELECT a.slug, COUNT(s.id) AS secciones
FROM chk_apps a
LEFT JOIN acc_secciones s ON s.app_id = a.id
WHERE a.slug = 'checklist'
GROUP BY a.slug;

SELECT COUNT(*) AS usuarios_con_acceso
FROM chk_usuario_apps ua
JOIN chk_apps a ON a.id = ua.app_id
WHERE a.slug = 'checklist';
