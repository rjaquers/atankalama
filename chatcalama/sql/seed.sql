INSERT IGNORE INTO roles(name) VALUES ('admin'),('user');

INSERT IGNORE INTO users(name,email,password_hash,role,status)
VALUES ('Administrador','admin@rkm.local','$2b$10$RdYB/pkxcZnRADgPRH0g1O8J5QOcwau2W8wOMPulSAfbA6unJJIdW','admin',1);

-- Permisos base (ejemplos)
INSERT IGNORE INTO permissions(name) VALUES
('dashboard_view'),
('notifications_view');

-- Asociar permisos al rol admin
INSERT IGNORE INTO role_permissions(role_id, permission_id)
SELECT r.id, p.id
FROM roles r, permissions p
WHERE r.name='admin' AND p.name IN ('dashboard_view','notifications_view');
