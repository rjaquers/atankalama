-- ============================================================
-- SISTEMA CHAT INTERNO - HOTEL ATANKALAMA
-- Prefijo: chat_
-- Motor: InnoDB | Charset: utf8mb4
-- Versión: 1.0 | Fecha: 2026-04-03
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-04:00"; -- Chile

-- ============================================================
-- 1. ÁREAS (Departamentos del hotel)
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_areas` (
  `id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(100) NOT NULL COMMENT 'Ej: Cocina, Recepción, Mantención',
  `descripcion` text DEFAULT NULL,
  `color`       varchar(7) NOT NULL DEFAULT '#3B82F6' COMMENT 'Color HEX para UI',
  `icono`       varchar(50) DEFAULT NULL COMMENT 'Nombre de icono (ej: utensils, wrench)',
  `estado`      enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Departamentos/áreas del hotel';

-- Datos iniciales
INSERT INTO `chat_areas` (`nombre`, `descripcion`, `color`, `icono`) VALUES
('Recepción',    'Área de recepción y atención al cliente', '#3B82F6', 'concierge-bell'),
('Cocina',       'Área de cocina y gastronomía',            '#F59E0B', 'utensils'),
('Mantención',   'Área de mantenimiento e infraestructura', '#6B7280', 'wrench'),
('Ventas',       'Área de ventas y reservas',               '#10B981', 'trending-up'),
('Informática',  'Área de sistemas y tecnología',           '#8B5CF6', 'monitor'),
('Housekeeping', 'Área de aseo y habitaciones',             '#EC4899', 'bed');


-- ============================================================
-- 2. ROLES
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_roles` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`      varchar(50) NOT NULL COMMENT 'Ej: Administrador, Jefe de Área, Operador',
  `descripcion` text DEFAULT NULL,
  `created_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Roles del sistema';

INSERT INTO `chat_roles` (`nombre`, `descripcion`) VALUES
('Administrador', 'Acceso total al sistema'),
('Jefe de Área',  'Gestión de su área y sus operadores'),
('Operador',      'Uso básico: chat, tareas propias, mantención');


-- ============================================================
-- 3. PERMISOS
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_permisos` (
  `id`     int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: crear_usuarios, ver_tareas_todas',
  `grupo`  varchar(50) DEFAULT NULL COMMENT 'Agrupación: usuarios, tareas, mantencion, chat',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Permisos disponibles en el sistema';

INSERT INTO `chat_permisos` (`nombre`, `grupo`) VALUES
('crear_usuarios',          'usuarios'),
('editar_usuarios',         'usuarios'),
('desactivar_usuarios',     'usuarios'),
('ver_todos_usuarios',      'usuarios'),
('crear_areas',             'areas'),
('editar_areas',            'areas'),
('crear_tareas',            'tareas'),
('asignar_tareas',          'tareas'),
('ver_todas_tareas',        'tareas'),
('cerrar_cualquier_tarea',  'tareas'),
('crear_mantencion',        'mantencion'),
('asignar_mantencion',      'mantencion'),
('ver_toda_mantencion',     'mantencion'),
('cerrar_cualquier_mantencion', 'mantencion'),
('ver_todos_chats',         'chat'),
('eliminar_mensajes',       'chat');


-- ============================================================
-- 4. RELACIÓN ROLES - PERMISOS
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_rol_permisos` (
  `id`          int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rol_id`      int(10) UNSIGNED NOT NULL,
  `permiso_id`  int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rol_permiso` (`rol_id`, `permiso_id`),
  CONSTRAINT `fk_rp_rol`    FOREIGN KEY (`rol_id`)     REFERENCES `chat_roles`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `chat_permisos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Asignación de permisos por rol';

-- Administrador tiene todos los permisos
INSERT INTO `chat_rol_permisos` (`rol_id`, `permiso_id`)
SELECT 1, id FROM `chat_permisos`;

-- Jefe de Área: tareas de su área, mantención, chat
INSERT INTO `chat_rol_permisos` (`rol_id`, `permiso_id`)
SELECT 2, id FROM `chat_permisos`
WHERE `nombre` IN ('crear_tareas','asignar_tareas','ver_todas_tareas',
                   'crear_mantencion','asignar_mantencion','ver_toda_mantencion');


-- ============================================================
-- 5. USUARIOS
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_usuarios` (
  `id`            int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nombre`        varchar(120) NOT NULL,
  `email`         varchar(150) NOT NULL COMMENT 'Correo institucional — no editable por el usuario',
  `password_hash` varchar(255) NOT NULL,
  `rol_id`        int(10) UNSIGNED NOT NULL DEFAULT 3 COMMENT '3=Operador',
  `area_id`       int(11) UNSIGNED DEFAULT NULL,
  `es_jefe`       tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Jefe de área',
  `foto_perfil`   varchar(255) DEFAULT NULL COMMENT 'Ruta relativa al archivo WebP',
  `fcm_token`     varchar(255) DEFAULT NULL COMMENT 'Token Firebase para push notifications',
  `estado`        tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `otp_code`      varchar(6) DEFAULT NULL COMMENT 'Código OTP de 6 dígitos',
  `otp_expires`   datetime DEFAULT NULL COMMENT 'Expiración del OTP',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `created_at`    datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  CONSTRAINT `fk_usr_rol`  FOREIGN KEY (`rol_id`)  REFERENCES `chat_roles` (`id`),
  CONSTRAINT `fk_usr_area` FOREIGN KEY (`area_id`) REFERENCES `chat_areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Usuarios del sistema de chat';


-- ============================================================
-- 6. SESIONES (tokens JWT / acceso)
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_sesiones` (
  `id`         int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `token`      varchar(512) NOT NULL COMMENT 'JWT token',
  `dispositivo` varchar(100) DEFAULT NULL COMMENT 'iOS, Android, Web',
  `ip`         varchar(45) DEFAULT NULL,
  `activa`     tinyint(1) NOT NULL DEFAULT 1,
  `expira_en`  datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token` (`token`(64)),
  CONSTRAINT `fk_ses_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Sesiones activas de usuarios';


-- ============================================================
-- 7. CONVERSACIONES
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_conversaciones` (
  `id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipo`        enum('individual','grupo','area','sistema') NOT NULL DEFAULT 'individual',
  `nombre`      varchar(100) DEFAULT NULL COMMENT 'Nombre del grupo o área',
  `foto`        varchar(255) DEFAULT NULL COMMENT 'Foto del grupo (WebP)',
  `area_id`     int(11) UNSIGNED DEFAULT NULL COMMENT 'Si es conversación de área',
  `creado_por`  int(10) UNSIGNED NOT NULL,
  `created_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Actualizado con cada mensaje nuevo',
  PRIMARY KEY (`id`),
  KEY `idx_area` (`area_id`),
  CONSTRAINT `fk_conv_area`    FOREIGN KEY (`area_id`)    REFERENCES `chat_areas`    (`id`),
  CONSTRAINT `fk_conv_creador` FOREIGN KEY (`creado_por`) REFERENCES `chat_usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Conversaciones (1a1, grupos, por área)';


-- ============================================================
-- 8. PARTICIPANTES DE CONVERSACIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_participantes` (
  `id`               int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversacion_id`  int(11) UNSIGNED NOT NULL,
  `usuario_id`       int(10) UNSIGNED NOT NULL,
  `archivada`        tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=conversación archivada por este usuario',
  `silenciada`       tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=sin notificaciones',
  `ultimo_leido_id`  int(11) UNSIGNED DEFAULT NULL COMMENT 'ID del último mensaje leído',
  `joined_at`        timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_conv_usr` (`conversacion_id`, `usuario_id`),
  CONSTRAINT `fk_part_conv` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_part_usr`  FOREIGN KEY (`usuario_id`)      REFERENCES `chat_usuarios`       (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Miembros de cada conversación';


-- ============================================================
-- 9. MENSAJES
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_mensajes` (
  `id`              int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `conversacion_id` int(11) UNSIGNED NOT NULL,
  `usuario_id`      int(10) UNSIGNED NOT NULL COMMENT 'Autor del mensaje',
  `tipo`            enum('texto','imagen','archivo','sistema') NOT NULL DEFAULT 'texto',
  `contenido`       text DEFAULT NULL COMMENT 'Texto del mensaje',
  `archivo_ruta`    varchar(255) DEFAULT NULL COMMENT 'Ruta WebP u otro archivo',
  `archivo_nombre`  varchar(150) DEFAULT NULL COMMENT 'Nombre original del archivo',
  `guardado`        tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=mensaje guardado/destacado',
  `eliminado`       tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=eliminado (soft delete)',
  `created_at`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_conv_msg` (`conversacion_id`, `created_at`),
  CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_msg_usr`  FOREIGN KEY (`usuario_id`)      REFERENCES `chat_usuarios`       (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Mensajes de chat';


-- ============================================================
-- 10. ESTADO DE LECTURA DE MENSAJES
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_mensaje_lecturas` (
  `id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mensaje_id`  int(11) UNSIGNED NOT NULL,
  `usuario_id`  int(10) UNSIGNED NOT NULL,
  `leido_at`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_msg_usr` (`mensaje_id`, `usuario_id`),
  CONSTRAINT `fk_lect_msg` FOREIGN KEY (`mensaje_id`) REFERENCES `chat_mensajes`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_lect_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Control de mensajes leídos por usuario';


-- ============================================================
-- 11. TAREAS
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_tareas` (
  `id`              int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo`          varchar(200) NOT NULL,
  `descripcion`     text DEFAULT NULL,
  `tipo`            enum('abierta','dirigida') NOT NULL DEFAULT 'abierta' COMMENT 'Abierta=para el área; Dirigida=a persona específica',
  `area_id`         int(11) UNSIGNED DEFAULT NULL,
  `asignado_a`      int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario responsable',
  `creado_por`      int(10) UNSIGNED NOT NULL,
  `prioridad`       enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `estado`          enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_limite`    date DEFAULT NULL,
  `fecha_completada` datetime DEFAULT NULL,
  `foto_cierre`     varchar(255) DEFAULT NULL COMMENT 'Foto obligatoria al completar (WebP)',
  `nota_cierre`     text DEFAULT NULL COMMENT 'Observación al completar',
  `created_at`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tarea_estado`     (`estado`),
  KEY `idx_tarea_asignado`   (`asignado_a`),
  KEY `idx_tarea_area`       (`area_id`),
  CONSTRAINT `fk_tar_area`   FOREIGN KEY (`area_id`)    REFERENCES `chat_areas`    (`id`),
  CONSTRAINT `fk_tar_asig`   FOREIGN KEY (`asignado_a`) REFERENCES `chat_usuarios` (`id`),
  CONSTRAINT `fk_tar_creador` FOREIGN KEY (`creado_por`) REFERENCES `chat_usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro y seguimiento de tareas';


-- ============================================================
-- 12. ARCHIVOS ADJUNTOS DE TAREAS
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_tarea_archivos` (
  `id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tarea_id`    int(11) UNSIGNED NOT NULL,
  `ruta`        varchar(255) NOT NULL COMMENT 'Ruta WebP en el servidor',
  `nombre_orig` varchar(150) DEFAULT NULL COMMENT 'Nombre original antes de convertir',
  `subido_por`  int(10) UNSIGNED NOT NULL,
  `created_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tarch_tarea` FOREIGN KEY (`tarea_id`)   REFERENCES `chat_tareas`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tarch_usr`   FOREIGN KEY (`subido_por`) REFERENCES `chat_usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Fotos adjuntas a tareas';


-- ============================================================
-- 13. COMENTARIOS DE TAREAS
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_tarea_comentarios` (
  `id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tarea_id`    int(11) UNSIGNED NOT NULL,
  `usuario_id`  int(10) UNSIGNED NOT NULL,
  `comentario`  text NOT NULL,
  `created_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tcom_tarea` FOREIGN KEY (`tarea_id`)   REFERENCES `chat_tareas`   (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tcom_usr`   FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Comentarios y seguimiento de tareas';


-- ============================================================
-- 14. MANTENCIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_mantencion` (
  `id`               int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `titulo`           varchar(200) NOT NULL,
  `descripcion`      text DEFAULT NULL,
  `ubicacion`        varchar(150) DEFAULT NULL COMMENT 'Ej: Habitación 203, Piscina, Cocina',
  `tipo`             enum('correctiva','preventiva','emergencia') NOT NULL DEFAULT 'correctiva',
  `area_id`          int(11) UNSIGNED DEFAULT NULL,
  `asignado_a`       int(10) UNSIGNED DEFAULT NULL,
  `creado_por`       int(10) UNSIGNED NOT NULL,
  `prioridad`        enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `estado`           enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_programada` date DEFAULT NULL COMMENT 'Para mantenciones preventivas',
  `fecha_completada` datetime DEFAULT NULL,
  `foto_cierre`      varchar(255) DEFAULT NULL COMMENT 'Foto obligatoria al completar (WebP)',
  `nota_cierre`      text DEFAULT NULL,
  `costo_estimado`   decimal(10,2) DEFAULT NULL,
  `costo_real`       decimal(10,2) DEFAULT NULL,
  `created_at`       timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mant_estado`   (`estado`),
  KEY `idx_mant_tipo`     (`tipo`),
  KEY `idx_mant_area`     (`area_id`),
  KEY `idx_mant_asignado` (`asignado_a`),
  CONSTRAINT `fk_mant_area`    FOREIGN KEY (`area_id`)    REFERENCES `chat_areas`    (`id`),
  CONSTRAINT `fk_mant_asig`    FOREIGN KEY (`asignado_a`) REFERENCES `chat_usuarios` (`id`),
  CONSTRAINT `fk_mant_creador` FOREIGN KEY (`creado_por`) REFERENCES `chat_usuarios` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de actividades de mantención';


-- ============================================================
-- 15. ARCHIVOS ADJUNTOS DE MANTENCIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_mantencion_archivos` (
  `id`           int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mantencion_id` int(11) UNSIGNED NOT NULL,
  `ruta`         varchar(255) NOT NULL COMMENT 'Ruta WebP en el servidor',
  `nombre_orig`  varchar(150) DEFAULT NULL,
  `momento`      enum('antes','durante','despues','cierre') NOT NULL DEFAULT 'durante' COMMENT 'Momento en que se tomó la foto',
  `subido_por`   int(10) UNSIGNED NOT NULL,
  `created_at`   timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_march_mant` FOREIGN KEY (`mantencion_id`) REFERENCES `chat_mantencion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_march_usr`  FOREIGN KEY (`subido_por`)    REFERENCES `chat_usuarios`   (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Fotos adjuntas a mantenciones';


-- ============================================================
-- 16. COMENTARIOS DE MANTENCIÓN
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_mantencion_comentarios` (
  `id`            int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mantencion_id` int(11) UNSIGNED NOT NULL,
  `usuario_id`    int(10) UNSIGNED NOT NULL,
  `comentario`    text NOT NULL,
  `created_at`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_mcom_mant` FOREIGN KEY (`mantencion_id`) REFERENCES `chat_mantencion` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mcom_usr`  FOREIGN KEY (`usuario_id`)    REFERENCES `chat_usuarios`   (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Comentarios y seguimiento de mantenciones';


-- ============================================================
-- 17. NOTIFICACIONES PUSH (historial)
-- ============================================================
CREATE TABLE IF NOT EXISTS `chat_notificaciones` (
  `id`          int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario_id`  int(10) UNSIGNED NOT NULL COMMENT 'Destinatario',
  `tipo`        enum('mensaje','tarea','mantencion','sistema') NOT NULL,
  `titulo`      varchar(100) NOT NULL,
  `cuerpo`      varchar(255) NOT NULL,
  `referencia_tipo` varchar(30) DEFAULT NULL COMMENT 'mensaje, tarea, mantencion',
  `referencia_id`   int(11) UNSIGNED DEFAULT NULL COMMENT 'ID del objeto relacionado',
  `leida`       tinyint(1) NOT NULL DEFAULT 0,
  `enviada_fcm` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=enviada a Firebase exitosamente',
  `created_at`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_usr`   (`usuario_id`, `leida`),
  CONSTRAINT `fk_notif_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historial de notificaciones push';


-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;
