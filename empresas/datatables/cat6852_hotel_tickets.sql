-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-06-2026 a las 14:20:06
-- Versión del servidor: 10.11.14-MariaDB-cll-lve
-- Versión de PHP: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cat6852_hotel_tickets`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_perfiles`
--

CREATE TABLE `acc_perfiles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_roles`
--

CREATE TABLE `acc_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `app_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → chk_apps.id',
  `nombre` varchar(80) NOT NULL COMMENT 'Ej: Administrador, Recepcionista, Jefe de Área',
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles definidos por aplicación';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_rol_secciones`
--

CREATE TABLE `acc_rol_secciones` (
  `rol_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → acc_roles.id',
  `seccion_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → acc_secciones.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Secciones habilitadas por rol';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_secciones`
--

CREATE TABLE `acc_secciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `app_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → chk_apps.id',
  `slug` varchar(150) NOT NULL COMMENT 'Ruta tal como aparece en el router',
  `nombre` varchar(150) NOT NULL COMMENT 'Etiqueta legible para la UI',
  `tipo` enum('publica','restringida') NOT NULL DEFAULT 'restringida',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `origen` enum('auto','manual') NOT NULL DEFAULT 'auto' COMMENT 'auto=detectado del router; manual=agregado desde UI',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Secciones o rutas registradas por aplicación';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acc_usuario_roles`
--

CREATE TABLE `acc_usuario_roles` (
  `usuario_id` int(11) NOT NULL COMMENT 'FK → chk_usuarios.id',
  `rol_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → acc_roles.id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rol asignado a cada usuario por aplicación';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos_incidencias`
--

CREATE TABLE `archivos_incidencias` (
  `id` int(11) NOT NULL,
  `incidencia_id` int(11) NOT NULL,
  `comentario_id` int(11) DEFAULT NULL,
  `testigo_id` int(11) DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(100) NOT NULL,
  `tamano_archivo` int(11) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo_evidencia` enum('general','testigo','comentario') DEFAULT 'general',
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `module` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_areas`
--

CREATE TABLE `chat_areas` (
  `id` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: Cocina, Recepción, Mantención',
  `descripcion` text DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#3B82F6' COMMENT 'Color HEX para UI',
  `icono` varchar(50) DEFAULT NULL COMMENT 'Nombre de icono (ej: utensils, wrench)',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Departamentos/áreas del hotel';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_conversaciones`
--

CREATE TABLE `chat_conversaciones` (
  `id` int(11) UNSIGNED NOT NULL,
  `tipo` enum('individual','grupo','area','sistema') NOT NULL DEFAULT 'individual',
  `nombre` varchar(100) DEFAULT NULL COMMENT 'Nombre del grupo o área',
  `foto` varchar(255) DEFAULT NULL COMMENT 'Foto del grupo (WebP)',
  `area_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'Si es conversación de área',
  `creado_por` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Actualizado con cada mensaje nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Conversaciones (1a1, grupos, por área)';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mantencion`
--

CREATE TABLE `chat_mantencion` (
  `id` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ubicacion` varchar(150) DEFAULT NULL COMMENT 'Ej: Habitación 203, Piscina, Cocina',
  `tipo` enum('correctiva','preventiva','emergencia') NOT NULL DEFAULT 'correctiva',
  `area_id` int(11) UNSIGNED DEFAULT NULL,
  `asignado_a` int(10) UNSIGNED DEFAULT NULL,
  `creado_por` int(10) UNSIGNED NOT NULL,
  `prioridad` enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `estado` enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_programada` date DEFAULT NULL COMMENT 'Para mantenciones preventivas',
  `fecha_completada` datetime DEFAULT NULL,
  `foto_cierre` varchar(255) DEFAULT NULL COMMENT 'Foto obligatoria al completar (WebP)',
  `nota_cierre` text DEFAULT NULL,
  `costo_estimado` decimal(10,2) DEFAULT NULL,
  `costo_real` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro de actividades de mantención';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mantencion_archivos`
--

CREATE TABLE `chat_mantencion_archivos` (
  `id` int(11) UNSIGNED NOT NULL,
  `mantencion_id` int(11) UNSIGNED NOT NULL,
  `ruta` varchar(255) NOT NULL COMMENT 'Ruta WebP en el servidor',
  `nombre_orig` varchar(150) DEFAULT NULL,
  `momento` enum('antes','durante','despues','cierre') NOT NULL DEFAULT 'durante' COMMENT 'Momento en que se tomó la foto',
  `subido_por` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Fotos adjuntas a mantenciones';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mantencion_comentarios`
--

CREATE TABLE `chat_mantencion_comentarios` (
  `id` int(11) UNSIGNED NOT NULL,
  `mantencion_id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `comentario` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Comentarios y seguimiento de mantenciones';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mensajes`
--

CREATE TABLE `chat_mensajes` (
  `id` int(11) UNSIGNED NOT NULL,
  `conversacion_id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'Autor del mensaje',
  `tipo` enum('texto','imagen','archivo','sistema') NOT NULL DEFAULT 'texto',
  `contenido` text DEFAULT NULL COMMENT 'Texto del mensaje',
  `archivo_ruta` varchar(255) DEFAULT NULL COMMENT 'Ruta WebP u otro archivo',
  `archivo_nombre` varchar(150) DEFAULT NULL COMMENT 'Nombre original del archivo',
  `guardado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=mensaje guardado/destacado',
  `eliminado` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=eliminado (soft delete)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Mensajes de chat';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_mensaje_lecturas`
--

CREATE TABLE `chat_mensaje_lecturas` (
  `id` int(11) UNSIGNED NOT NULL,
  `mensaje_id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `leido_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Control de mensajes leídos por usuario';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_notificaciones`
--

CREATE TABLE `chat_notificaciones` (
  `id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL COMMENT 'Destinatario',
  `tipo` enum('mensaje','tarea','mantencion','sistema') NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `cuerpo` varchar(255) NOT NULL,
  `referencia_tipo` varchar(30) DEFAULT NULL COMMENT 'mensaje, tarea, mantencion',
  `referencia_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID del objeto relacionado',
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `enviada_fcm` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=enviada a Firebase exitosamente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Historial de notificaciones push';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_participantes`
--

CREATE TABLE `chat_participantes` (
  `id` int(11) UNSIGNED NOT NULL,
  `conversacion_id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `archivada` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=conversación archivada por este usuario',
  `silenciada` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=sin notificaciones',
  `ultimo_leido_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'ID del último mensaje leído',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Miembros de cada conversación';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_permisos`
--

CREATE TABLE `chat_permisos` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Ej: crear_usuarios, ver_tareas_todas',
  `grupo` varchar(50) DEFAULT NULL COMMENT 'Agrupación: usuarios, tareas, mantencion, chat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Permisos disponibles en el sistema';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_roles`
--

CREATE TABLE `chat_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: Administrador, Jefe de Área, Operador',
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Roles del sistema';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_rol_permisos`
--

CREATE TABLE `chat_rol_permisos` (
  `id` int(10) UNSIGNED NOT NULL,
  `rol_id` int(10) UNSIGNED NOT NULL,
  `permiso_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Asignación de permisos por rol';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_sesiones`
--

CREATE TABLE `chat_sesiones` (
  `id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(512) NOT NULL COMMENT 'JWT token',
  `dispositivo` varchar(100) DEFAULT NULL COMMENT 'iOS, Android, Web',
  `ip` varchar(45) DEFAULT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `expira_en` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sesiones activas de usuarios';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_tareas`
--

CREATE TABLE `chat_tareas` (
  `id` int(11) UNSIGNED NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('abierta','dirigida') NOT NULL DEFAULT 'abierta' COMMENT 'Abierta=para el área;   \r\n  Dirigida=a persona específica',
  `area_id` int(11) UNSIGNED DEFAULT NULL,
  `asignado_a` int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario responsable',
  `creado_por` int(10) UNSIGNED NOT NULL,
  `prioridad` enum('baja','media','alta','urgente') NOT NULL DEFAULT 'media',
  `estado` enum('pendiente','en_proceso','completada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_limite` date DEFAULT NULL,
  `fecha_completada` datetime DEFAULT NULL,
  `foto_cierre` varchar(255) DEFAULT NULL COMMENT 'Foto obligatoria al completar (WebP)',
  `nota_cierre` text DEFAULT NULL COMMENT 'Observación al completar',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Registro y seguimiento de tareas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_tarea_archivos`
--

CREATE TABLE `chat_tarea_archivos` (
  `id` int(11) UNSIGNED NOT NULL,
  `tarea_id` int(11) UNSIGNED NOT NULL,
  `ruta` varchar(255) NOT NULL COMMENT 'Ruta WebP en el servidor',
  `nombre_orig` varchar(150) DEFAULT NULL COMMENT 'Nombre original antes de convertir',
  `subido_por` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Fotos adjuntas a tareas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_tarea_comentarios`
--

CREATE TABLE `chat_tarea_comentarios` (
  `id` int(11) UNSIGNED NOT NULL,
  `tarea_id` int(11) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `comentario` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Comentarios y seguimiento de tareas';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_usuarios`
--

CREATE TABLE `chat_usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL COMMENT 'Correo institucional — no editable por el usuario',
  `password_hash` varchar(255) NOT NULL,
  `rol_id` int(10) UNSIGNED NOT NULL DEFAULT 3 COMMENT '3=Operador',
  `area_id` int(11) UNSIGNED DEFAULT NULL,
  `es_jefe` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=Jefe de área',
  `foto_perfil` varchar(255) DEFAULT NULL COMMENT 'Ruta relativa al archivo WebP',
  `fcm_token` varchar(255) DEFAULT NULL COMMENT 'Token Firebase para push notifications',
  `estado` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activo, 0=inactivo',
  `otp_code` varchar(6) DEFAULT NULL COMMENT 'Código OTP de 6 dígitos',
  `otp_expires` datetime DEFAULT NULL COMMENT 'Expiración del OTP',
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Usuarios del sistema de chat';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_apps`
--

CREATE TABLE `chk_apps` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(50) NOT NULL COMMENT 'Identificador único, ej: novedades, checklist, chat',
  `session_prefix` varchar(20) DEFAULT NULL COMMENT 'Prefijo de sesiÃ³n PHP (ej: nov, coc)',
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre legible',
  `descripcion` varchar(255) DEFAULT NULL,
  `url_inicio` varchar(500) DEFAULT NULL COMMENT 'URL de la página de inicio/usuario de la app',
  `url_admin` varchar(500) DEFAULT NULL COMMENT 'URL del panel de administración de la app',
  `icono` varchar(50) DEFAULT NULL COMMENT 'Clase Bootstrap Icons (ej: bi-journal-text)',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de sistemas/aplicaciones del hotel';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_areas`
--

CREATE TABLE `chk_areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_checklists`
--

CREATE TABLE `chk_checklists` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `area` varchar(100) NOT NULL,
  `hotel` varchar(50) NOT NULL DEFAULT 'Atankalama',
  `modo` enum('cerrado','abierto') NOT NULL DEFAULT 'cerrado',
  `token_publico` varchar(64) DEFAULT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `estado` enum('activo','inactivo','eliminado') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_checklist_preguntas`
--

CREATE TABLE `chk_checklist_preguntas` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `grupo` varchar(255) DEFAULT NULL,
  `pregunta` text NOT NULL,
  `tipo_respuesta` enum('boolean','text','numeric_scale','foto') NOT NULL,
  `escala_min` int(11) DEFAULT NULL,
  `escala_max` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_evaluaciones`
--

CREATE TABLE `chk_evaluaciones` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `evaluado_nombre` varchar(100) NOT NULL,
  `evaluado_apellido` varchar(100) NOT NULL,
  `ejecutado_por` varchar(255) DEFAULT NULL,
  `fecha_evaluacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL COMMENT 'hora inicio evaluación',
  `fecha_fin` datetime DEFAULT NULL COMMENT 'hora final evaluación',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_evaluacion_respuestas`
--

CREATE TABLE `chk_evaluacion_respuestas` (
  `id` int(11) NOT NULL,
  `evaluacion_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta_boolean` tinyint(1) DEFAULT NULL,
  `respuesta_texto` text DEFAULT NULL,
  `respuesta_numerica` decimal(10,2) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `respuesta_foto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_login_log`
--

CREATE TABLE `chk_login_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `app_slug` varchar(50) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log de ingresos exitosos al sistema';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_login_tokens`
--

CREATE TABLE `chk_login_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` char(6) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_report_cache`
--

CREATE TABLE `chk_report_cache` (
  `id` int(11) NOT NULL,
  `tipo_reporte` varchar(100) NOT NULL,
  `parametros_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `resultado_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `generado_por` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_system_logs`
--

CREATE TABLE `chk_system_logs` (
  `id` int(11) NOT NULL,
  `nivel` enum('INFO','WARNING','ERROR','SECURITY','CRITICAL') NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `contexto_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_usuarios`
--

CREATE TABLE `chk_usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `rut` varchar(12) DEFAULT NULL,
  `perfil` varchar(100) DEFAULT 'Operador',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `forzar_logout` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si = 1, AccesoBootstrap cierra la sesión en el próximo request',
  `sesion_expira_en` datetime DEFAULT NULL COMMENT 'Expiración de la sesión activa; NULL = sin sesión',
  `totp_secret` varchar(32) DEFAULT NULL,
  `totp_habilitado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `validado` tinyint(1) NOT NULL,
  `recibe_novedades` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Si 1, recibe email al registrarse una nueva novedad'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chk_usuario_apps`
--

CREATE TABLE `chk_usuario_apps` (
  `usuario_id` int(11) NOT NULL,
  `app_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Qué usuario tiene acceso a qué aplicación';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_comandas`
--

CREATE TABLE `coci_comandas` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_servicio` enum('cena','colacion','colacion_especial','desayuno') NOT NULL,
  `nombre_hotel` varchar(100) NOT NULL DEFAULT 'Atankalama',
  `tipo_solicitante` enum('particular','empresa') NOT NULL DEFAULT 'empresa',
  `company_id` int(11) DEFAULT NULL,
  `contract_id` int(11) DEFAULT NULL,
  `nombre_empresa` varchar(200) DEFAULT NULL,
  `nombre_contacto` varchar(100) DEFAULT NULL,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `hora_servicio` time DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `es_para_llevar` tinyint(1) NOT NULL DEFAULT 0,
  `origen` enum('programada','urgente') NOT NULL DEFAULT 'programada',
  `orden_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coc_otp`
--

CREATE TABLE `coc_otp` (
  `id` int(10) UNSIGNED NOT NULL,
  `recep_id` int(10) UNSIGNED NOT NULL,
  `code` char(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_acceso`
--

CREATE TABLE `codigos_acceso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `codigo` varchar(6) NOT NULL,
  `usado` tinyint(1) DEFAULT 0,
  `expira_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_adicional`
--

CREATE TABLE `colacion_adicional` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` tinyint(1) NOT NULL DEFAULT 2 COMMENT '1=Principal, 2=Adicional,\r\n3=Opcional',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_impresiones`
--

CREATE TABLE `colacion_impresiones` (
  `id` int(10) UNSIGNED NOT NULL,
  `rut` varchar(20) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_impresion` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `copia` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_impresion_log`
--

CREATE TABLE `colacion_impresion_log` (
  `id` bigint(20) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `voucher_id` bigint(20) DEFAULT NULL,
  `accion` enum('impresion','reimpresion') NOT NULL,
  `copias` int(11) NOT NULL DEFAULT 1,
  `usuario_id` int(11) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_lote`
--

CREATE TABLE `colacion_lote` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `fecha_servicio` date NOT NULL,
  `fecha_fin_servicio` date DEFAULT NULL,
  `servicio_tipo_id` int(11) NOT NULL,
  `servicios_adicionales` varchar(20) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `from_upload_id` int(11) DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp(),
  `actualizado` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `excel` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_lote_adicional`
--

CREATE TABLE `colacion_lote_adicional` (
  `lote_id` int(11) NOT NULL,
  `adicional_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_lote_servicio`
--

CREATE TABLE `colacion_lote_servicio` (
  `id` int(10) UNSIGNED NOT NULL,
  `lote_id` int(11) NOT NULL,
  `servicio_tipo_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_servicio_tipo`
--

CREATE TABLE `colacion_servicio_tipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `hora_inicio` time NOT NULL DEFAULT '00:00:00',
  `hora_fin` time NOT NULL DEFAULT '23:59:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_voucher`
--

CREATE TABLE `colacion_voucher` (
  `id` bigint(20) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `numero_en_lote` int(11) NOT NULL,
  `codigo_publico` varchar(32) NOT NULL,
  `guest_rut` varchar(32) DEFAULT NULL,
  `guest_nombre` varchar(150) DEFAULT NULL,
  `guest_habitacion` varchar(50) DEFAULT NULL,
  `estado` enum('pendiente','usado','anulado') NOT NULL DEFAULT 'pendiente',
  `usado_en` timestamp NULL DEFAULT NULL,
  `usado_por_ip` varbinary(16) DEFAULT NULL,
  `scan_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `impreso_count` int(11) NOT NULL DEFAULT 0,
  `ultimo_impreso_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `colacion_voucher_impresiones`
--

CREATE TABLE `colacion_voucher_impresiones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rut` varchar(15) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_impresion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios_incidencias`
--

CREATE TABLE `comentarios_incidencias` (
  `id` int(11) NOT NULL,
  `incidencia_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `menciones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_alert_config`
--

CREATE TABLE `doc_alert_config` (
  `id` int(10) UNSIGNED NOT NULL,
  `days_before` int(10) UNSIGNED NOT NULL,
  `email_enabled` tinyint(4) NOT NULL DEFAULT 1,
  `email_recipients` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_companies`
--

CREATE TABLE `doc_companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `business_name` varchar(200) NOT NULL,
  `trade_name` varchar(200) DEFAULT NULL,
  `contact_name` varchar(150) DEFAULT NULL,
  `contact_email` varchar(200) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `type` enum('cliente','proveedor') NOT NULL DEFAULT 'cliente',
  `notes` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contracts`
--

CREATE TABLE `doc_contracts` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `version_number` int(10) UNSIGNED DEFAULT 1,
  `code` varchar(50) NOT NULL,
  `company_id` int(10) UNSIGNED NOT NULL,
  `template_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_type` enum('arriendo','hospedaje','proveedor') NOT NULL DEFAULT 'hospedaje',
  `pricing_mode` enum('grupo','por_persona') NOT NULL DEFAULT 'grupo',
  `duration_type` enum('indefinido','plazo_fijo','por_temporada') NOT NULL DEFAULT 'plazo_fijo',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `base_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `base_guests` int(10) UNSIGNED DEFAULT NULL,
  `payment_frequency` enum('semanal','quincenal','mensual','otro') NOT NULL DEFAULT 'mensual',
  `status` enum('vigente','vencido','borrador','finalizado','cancelado','quotation_draft','quotation_sent','quotation_approved') DEFAULT 'borrador',
  `notes` text DEFAULT NULL,
  `custom_data` text DEFAULT NULL,
  `generated_pdf_path` varchar(500) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_attachments`
--

CREATE TABLE `doc_contract_attachments` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `file_path` varchar(500) NOT NULL,
  `category` enum('contrato_firmado','evidencia_cobro','comprobante_pago','foto','email','otro') NOT NULL DEFAULT 'otro',
  `description` text DEFAULT NULL,
  `payment_id` int(10) UNSIGNED DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_history`
--

CREATE TABLE `doc_contract_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_hotels`
--

CREATE TABLE `doc_contract_hotels` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `hotel_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_notes`
--

CREATE TABLE `doc_contract_notes` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `note` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_payments`
--

CREATE TABLE `doc_contract_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('transferencia','efectivo','cheque','tarjeta','otro') NOT NULL DEFAULT 'transferencia',
  `reference_number` varchar(100) DEFAULT NULL,
  `period_type` enum('semanal','quincenal','mensual','anual','servicio','otro') NOT NULL DEFAULT 'mensual',
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` enum('pendiente','pagado','parcial','anulado') NOT NULL DEFAULT 'pendiente',
  `notes` text DEFAULT NULL,
  `registered_by` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_services`
--

CREATE TABLE `doc_contract_services` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `service_id` int(10) UNSIGNED NOT NULL,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  `currency` enum('CLP','UF') DEFAULT 'CLP',
  `billing_type` enum('per_person','per_day','per_event') DEFAULT 'per_person',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_templates`
--

CREATE TABLE `doc_contract_templates` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `contract_type` enum('arriendo','hospedaje','proveedor') NOT NULL DEFAULT 'hospedaje',
  `body_html` text NOT NULL,
  `header_text` text DEFAULT NULL,
  `footer_text` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_contract_tiers`
--

CREATE TABLE `doc_contract_tiers` (
  `id` int(10) UNSIGNED NOT NULL,
  `contract_id` int(10) UNSIGNED NOT NULL,
  `min_guests` int(10) UNSIGNED NOT NULL,
  `max_guests` int(10) UNSIGNED DEFAULT NULL,
  `price_per_person` decimal(10,2) NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `description` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_hotels`
--

CREATE TABLE `doc_hotels` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `legal_representative` varchar(150) DEFAULT NULL,
  `representative_rut` varchar(20) DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_permissions`
--

CREATE TABLE `doc_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_roles`
--

CREATE TABLE `doc_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_role_permissions`
--

CREATE TABLE `doc_role_permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_services`
--

CREATE TABLE `doc_services` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(12,2) DEFAULT 0.00,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_spaces`
--

CREATE TABLE `doc_spaces` (
  `id` int(10) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `space_type` enum('salon','sauna','quincho','oficina','terraza','otro') NOT NULL DEFAULT 'salon',
  `description` text DEFAULT NULL,
  `capacity` int(10) UNSIGNED DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `allows_hourly` tinyint(4) NOT NULL DEFAULT 1,
  `allows_daily` tinyint(4) NOT NULL DEFAULT 1,
  `allows_monthly` tinyint(4) NOT NULL DEFAULT 0,
  `base_price_hour` decimal(12,2) DEFAULT NULL,
  `base_price_day` decimal(12,2) DEFAULT NULL,
  `base_price_month` decimal(12,2) DEFAULT NULL,
  `included_equipment` text DEFAULT NULL,
  `restrictions` text DEFAULT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `hotel_id` int(10) UNSIGNED DEFAULT NULL,
  `calendar_color` varchar(7) DEFAULT '#198754',
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_blocks`
--

CREATE TABLE `doc_space_blocks` (
  `id` int(10) UNSIGNED NOT NULL,
  `space_id` int(10) UNSIGNED NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `reason` text NOT NULL,
  `block_type` enum('mantencion','limpieza','evento_interno','reparacion','otro') NOT NULL DEFAULT 'mantencion',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_bookings`
--

CREATE TABLE `doc_space_bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `folio` varchar(50) NOT NULL,
  `space_id` int(10) UNSIGNED NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `client_name` varchar(200) DEFAULT NULL,
  `booking_mode` enum('por_hora','por_dia','por_mes','precio_cerrado') NOT NULL DEFAULT 'por_hora',
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `qty_hours` decimal(6,2) DEFAULT NULL,
  `qty_days` int(10) UNSIGNED DEFAULT NULL,
  `qty_months` int(10) UNSIGNED DEFAULT NULL,
  `base_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `surcharge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_free` tinyint(4) NOT NULL DEFAULT 0,
  `free_reason` text DEFAULT NULL,
  `booking_status` enum('borrador','confirmada','en_uso','finalizada','cancelada','no_asistio') NOT NULL DEFAULT 'confirmada',
  `charge_status` enum('no_generado','pendiente','generado','facturado','pagado','anulado') NOT NULL DEFAULT 'no_generado',
  `notes_client` text DEFAULT NULL,
  `notes_internal` text DEFAULT NULL,
  `origin` varchar(50) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `cancelled_by` int(10) UNSIGNED DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_booking_charges`
--

CREATE TABLE `doc_space_booking_charges` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL COMMENT 'Ej: Arriendo Salón principal + Extras',
  `amount` decimal(12,2) NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_booking_history`
--

CREATE TABLE `doc_space_booking_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_booking_items`
--

CREATE TABLE `doc_space_booking_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `item_type` enum('arriendo_base','extra','descuento','recargo') NOT NULL DEFAULT 'extra',
  `extra_id` int(10) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` decimal(8,2) NOT NULL DEFAULT 1.00,
  `unit` varchar(50) DEFAULT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_booking_payments`
--

CREATE TABLE `doc_space_booking_payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL COMMENT 'Monto abonado',
  `payment_date` date NOT NULL COMMENT 'Fecha del pago',
  `payment_method` enum('transferencia','efectivo','cheque','tarjeta','otro') NOT NULL DEFAULT 'transferencia',
  `reference_number` varchar(100) DEFAULT NULL COMMENT 'Nº de comprobante',
  `receipt_path` varchar(500) DEFAULT NULL COMMENT 'Ruta al comprobante (PDF/WebP)',
  `notes` text DEFAULT NULL,
  `registered_by` int(10) UNSIGNED DEFAULT NULL,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_extras`
--

CREATE TABLE `doc_space_extras` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `charge_type` enum('fijo','por_unidad','por_hora','por_dia') NOT NULL DEFAULT 'fijo',
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `active` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_space_photos`
--

CREATE TABLE `doc_space_photos` (
  `id` int(10) UNSIGNED NOT NULL,
  `space_id` int(10) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(150) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_uf_values`
--

CREATE TABLE `doc_uf_values` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doc_users`
--

CREATE TABLE `doc_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_incidencias`
--

CREATE TABLE `estados_incidencias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `orden` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `es_final` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `excel_upload`
--

CREATE TABLE `excel_upload` (
  `id` int(10) UNSIGNED NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_path` varchar(512) NOT NULL,
  `total_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `excel_upload_item`
--

CREATE TABLE `excel_upload_item` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `upload_id` int(10) UNSIGNED NOT NULL,
  `fila_nro` int(10) UNSIGNED NOT NULL,
  `id_archivo` varchar(64) NOT NULL,
  `rut` varchar(24) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `habitacion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_estados`
--

CREATE TABLE `historial_estados` (
  `id` int(11) NOT NULL,
  `incidencia_id` int(11) NOT NULL,
  `estado_anterior_id` int(11) DEFAULT NULL,
  `estado_nuevo_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hoteles`
--

CREATE TABLE `hoteles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL,
  `hotel_id` int(11) NOT NULL,
  `tipo_espacio_id` int(11) NOT NULL,
  `numero_habitacion` varchar(50) DEFAULT NULL,
  `nombre_espacio` varchar(255) DEFAULT NULL,
  `fecha_ocurrencia` date NOT NULL,
  `fecha_hora_incidente` datetime NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `usuario_denunciante_id` int(11) NOT NULL,
  `nombre_denunciante` varchar(255) DEFAULT NULL,
  `cargo_denunciante` varchar(255) DEFAULT NULL,
  `personas_afectadas` text DEFAULT NULL,
  `detalle_incidente` text NOT NULL,
  `acciones_tomadas` text DEFAULT NULL,
  `recomendaciones` text DEFAULT NULL,
  `situacion_resuelta` enum('si','no','na') DEFAULT 'no',
  `requiere_gerencia` tinyint(1) DEFAULT 0,
  `estado_actual_id` int(11) NOT NULL,
  `es_anonima` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones_enviadas`
--

CREATE TABLE `notificaciones_enviadas` (
  `id` int(11) NOT NULL,
  `tipo` enum('codigo_acceso','mencion','denuncia_anonima','cambio_estado') NOT NULL,
  `destinatario_email` varchar(255) NOT NULL,
  `destinatario_id` int(11) DEFAULT NULL,
  `asunto` varchar(500) NOT NULL,
  `mensaje` text NOT NULL,
  `incidencia_id` int(11) DEFAULT NULL,
  `comentario_id` int(11) DEFAULT NULL,
  `enviado` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_archivos`
--

CREATE TABLE `nov_archivos` (
  `id` int(11) NOT NULL,
  `novedad_id` int(11) UNSIGNED NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_checklists`
--

CREATE TABLE `nov_checklists` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `creado_por` int(10) UNSIGNED NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_checklist_preguntas`
--

CREATE TABLE `nov_checklist_preguntas` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `texto` text NOT NULL,
  `tipo_respuesta` enum('boolean','text','numeric_scale') NOT NULL,
  `escala_min` int(11) DEFAULT NULL,
  `escala_max` int(11) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_empresas`
--

CREATE TABLE `nov_empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_encargados`
--

CREATE TABLE `nov_encargados` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `periodo_desde` date DEFAULT NULL,
  `periodo_hasta` date DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_evaluaciones`
--

CREATE TABLE `nov_evaluaciones` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `ejecutado_por` int(10) UNSIGNED NOT NULL,
  `evaluado_nombre` varchar(100) NOT NULL,
  `evaluado_apellido` varchar(100) NOT NULL,
  `fecha_ejecucion` datetime NOT NULL,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_evaluacion_respuestas`
--

CREATE TABLE `nov_evaluacion_respuestas` (
  `id` int(10) UNSIGNED NOT NULL,
  `evaluacion_id` int(10) UNSIGNED NOT NULL,
  `pregunta_id` int(10) UNSIGNED NOT NULL,
  `respuesta_boolean` tinyint(1) DEFAULT NULL,
  `respuesta_texto` text DEFAULT NULL,
  `respuesta_numero` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_login_tokens`
--

CREATE TABLE `nov_login_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_en` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `creado_en` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_solicitud` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_novedades`
--

CREATE TABLE `nov_novedades` (
  `id` int(11) UNSIGNED NOT NULL,
  `recepcionista_id` int(11) NOT NULL,
  `area` varchar(100) NOT NULL,
  `detalle` text NOT NULL,
  `requiere_seguimiento` tinyint(1) NOT NULL DEFAULT 0,
  `tipo_seguimiento` varchar(50) DEFAULT NULL,
  `flexkeeping_id` varchar(100) DEFAULT NULL,
  `seguimiento_estado` tinyint(1) NOT NULL DEFAULT 0,
  `seguimiento_resuelto_at` datetime DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `hotel` enum('Atankalama','Atankalama Inn') NOT NULL DEFAULT 'Atankalama',
  `tipo_novedad` varchar(50) DEFAULT 'Otro',
  `nivel_importancia` tinyint(4) NOT NULL,
  `nivel_sugerido` tinyint(4) NOT NULL,
  `score_calculado` tinyint(4) NOT NULL,
  `detalle_calculo` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_recepcionistas`
--

CREATE TABLE `nov_recepcionistas` (
  `id` int(11) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fono` varchar(20) DEFAULT NULL,
  `correo` varchar(250) DEFAULT NULL,
  `area_id` int(10) UNSIGNED DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nov_seguimiento_comentarios`
--

CREATE TABLE `nov_seguimiento_comentarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `novedad_id` int(10) UNSIGNED NOT NULL,
  `autor` varchar(120) NOT NULL,
  `comentario` varchar(500) NOT NULL,
  `creado_at` datetime NOT NULL DEFAULT current_timestamp(),
  `urgencia` tinyint(3) UNSIGNED NOT NULL DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pre_categorias`
--

CREATE TABLE `pre_categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pre_precios`
--

CREATE TABLE `pre_precios` (
  `id` int(11) NOT NULL,
  `tipo_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `precio` varchar(20) NOT NULL DEFAULT '--',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pre_tipos`
--

CREATE TABLE `pre_tipos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temp_registros`
--

CREATE TABLE `temp_registros` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `hotel` varchar(50) NOT NULL,
  `temperatura` decimal(5,2) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `fotos` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `public_code` varchar(32) NOT NULL,
  `mode` enum('custodia','perdido') NOT NULL,
  `guest_name` varchar(120) DEFAULT NULL,
  `item_type` varchar(120) DEFAULT NULL,
  `location_label` varchar(120) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('en_custodia','retirado','extraviado','cancelado') NOT NULL DEFAULT 'en_custodia',
  `created_at` datetime NOT NULL,
  `created_by_ip` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `print_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `retrieved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_daily_seq`
--

CREATE TABLE `ticket_daily_seq` (
  `day` date NOT NULL,
  `last_seq` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_adjuntos`
--

CREATE TABLE `trell_adjuntos` (
  `id` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `ruta` varchar(500) NOT NULL,
  `tipo` enum('imagen','pdf') NOT NULL DEFAULT 'imagen',
  `tamanio` int(11) NOT NULL DEFAULT 0 COMMENT 'bytes',
  `subido_por` int(11) NOT NULL COMMENT 'FK → chk_usuarios.id',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_checklist`
--

CREATE TABLE `trell_checklist` (
  `id` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL DEFAULT 'Lista de verificación',
  `posicion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_checklist_items`
--

CREATE TABLE `trell_checklist_items` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `texto` varchar(500) NOT NULL,
  `completado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_vencimiento` date DEFAULT NULL,
  `prioridad` varchar(20) DEFAULT 'normal',
  `responsable_id` int(11) DEFAULT NULL,
  `posicion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_comentarios`
--

CREATE TABLE `trell_comentarios` (
  `id` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `tipo` enum('comentario','actividad') NOT NULL DEFAULT 'comentario',
  `mensaje` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_etiquetas`
--

CREATE TABLE `trell_etiquetas` (
  `id` int(11) NOT NULL,
  `tablero_id` int(11) DEFAULT NULL COMMENT 'NULL = etiqueta base del sistema',
  `nombre` varchar(50) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#64748b',
  `es_base` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_listas`
--

CREATE TABLE `trell_listas` (
  `id` int(11) NOT NULL,
  `tablero_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `posicion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `es_fija` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_referencias`
--

CREATE TABLE `trell_referencias` (
  `id` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL COMMENT 'Tarjeta origen',
  `tablero_destino_id` int(11) NOT NULL,
  `lista_destino_id` int(11) NOT NULL,
  `mensaje` text DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_tableros`
--

CREATE TABLE `trell_tableros` (
  `id` int(11) NOT NULL,
  `area_id` int(11) NOT NULL COMMENT 'FK → chk_areas.id',
  `nombre` varchar(100) NOT NULL,
  `fondo_color` varchar(7) NOT NULL DEFAULT '#1e40af',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_tarjetas`
--

CREATE TABLE `trell_tarjetas` (
  `id` int(11) NOT NULL,
  `lista_id` int(11) NOT NULL,
  `tablero_id` int(11) NOT NULL,
  `numero` int(11) NOT NULL COMMENT 'Correlativo por tablero',
  `titulo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `posicion` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `archivada` tinyint(1) NOT NULL DEFAULT 0,
  `completada` tinyint(1) NOT NULL DEFAULT 0,
  `es_plantilla` tinyint(1) NOT NULL DEFAULT 0,
  `creado_por` int(11) NOT NULL COMMENT 'FK → chk_usuarios.id',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_tarjeta_etiquetas`
--

CREATE TABLE `trell_tarjeta_etiquetas` (
  `id` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `etiqueta_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_tarjeta_miembros`
--

CREATE TABLE `trell_tarjeta_miembros` (
  `id` int(11) NOT NULL,
  `tarjeta_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trell_usuario_tableros`
--

CREATE TABLE `trell_usuario_tableros` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL COMMENT 'FK → chk_usuarios.id',
  `tablero_id` int(11) NOT NULL COMMENT 'FK → trell_tableros.id',
  `puede_editar` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_courses`
--

CREATE TABLE `univ_courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `hotel_id` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Para futura expansión multi-hotel',
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `area_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK opcional a tabla de áreas si existe',
  `tipo` enum('obligatorio_legal','obligatorio_area','opcional') NOT NULL DEFAULT 'opcional',
  `creditos` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `min_score_to_approve` tinyint(3) UNSIGNED NOT NULL DEFAULT 70 COMMENT 'Porcentaje 0-100',
  `total_preguntas_examen` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
  `tiempo_limite_minutos` smallint(5) UNSIGNED NOT NULL DEFAULT 15,
  `max_intentos` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `vigencia_meses` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'NULL = no vence; ej 12 = recertificar anual',
  `version` smallint(5) UNSIGNED NOT NULL DEFAULT 1,
  `requiere_retoma` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Al publicar nueva versión, fuerza retoma',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_modificacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_cron_log`
--

CREATE TABLE `univ_cron_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `proceso` varchar(60) NOT NULL COMMENT 'ej: reporte_semanal_rrhh, check_vencimientos',
  `fecha_ejecucion` datetime NOT NULL DEFAULT current_timestamp(),
  `duracion_segundos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `registros_procesados` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `emails_enviados` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `estado` enum('ok','error','parcial') NOT NULL DEFAULT 'ok',
  `mensaje` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_cursos_por_perfil`
--

CREATE TABLE `univ_cursos_por_perfil` (
  `id` int(10) UNSIGNED NOT NULL,
  `perfil` varchar(50) NOT NULL COMMENT 'Coincide con chk_usuarios.perfil',
  `course_id` int(10) UNSIGNED NOT NULL,
  `es_obligatorio` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_enrollments`
--

CREATE TABLE `univ_enrollments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'FK lógica a chk_usuarios.id',
  `course_id` int(10) UNSIGNED NOT NULL,
  `course_version` smallint(5) UNSIGNED NOT NULL COMMENT 'Versión del curso al momento de matricular',
  `ciclo` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Se incrementa en recertificaciones',
  `status` enum('asignado','en_progreso','aprobado','reprobado','bloqueado','vencido') NOT NULL DEFAULT 'asignado',
  `pagina_actual` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `intentos_usados` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `creditos_ganados` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_asignacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL COMMENT 'Calculada al aprobar según vigencia_meses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_evaluations`
--

CREATE TABLE `univ_evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `enrollment_id` int(10) UNSIGNED NOT NULL,
  `numero_intento` tinyint(3) UNSIGNED NOT NULL COMMENT '1, 2, o 3',
  `score` tinyint(3) UNSIGNED NOT NULL COMMENT 'Porcentaje 0-100',
  `preguntas_correctas` tinyint(3) UNSIGNED NOT NULL,
  `preguntas_totales` tinyint(3) UNSIGNED NOT NULL,
  `aprobado` tinyint(1) NOT NULL DEFAULT 0,
  `tiempo_total_segundos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `fecha_inicio` datetime NOT NULL,
  `fecha_fin` datetime NOT NULL,
  `ip_origen` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_evaluation_answers`
--

CREATE TABLE `univ_evaluation_answers` (
  `id` int(10) UNSIGNED NOT NULL,
  `evaluation_id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `option_id_elegida` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL si no respondió',
  `es_correcta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_options`
--

CREATE TABLE `univ_options` (
  `id` int(10) UNSIGNED NOT NULL,
  `question_id` int(10) UNSIGNED NOT NULL,
  `texto_opcion` varchar(500) NOT NULL,
  `es_correcta` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_pages`
--

CREATE TABLE `univ_pages` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `orden` tinyint(3) UNSIGNED NOT NULL COMMENT '1 a 10 habitualmente',
  `titulo` varchar(150) NOT NULL,
  `tipo` enum('html','pdf','video') NOT NULL DEFAULT 'html',
  `contenido` mediumtext NOT NULL COMMENT 'HTML crudo, ruta relativa a PDF, o URL embed de video',
  `tiempo_minimo_segundos` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Tiempo mínimo antes de habilitar Siguiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `univ_questions`
--

CREATE TABLE `univ_questions` (
  `id` int(10) UNSIGNED NOT NULL,
  `course_id` int(10) UNSIGNED NOT NULL,
  `texto_pregunta` text NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `zzz___empresas`
--

CREATE TABLE `zzz___empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acc_perfiles`
--
ALTER TABLE `acc_perfiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `acc_roles`
--
ALTER TABLE `acc_roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rol_app` (`app_id`);

--
-- Indices de la tabla `acc_rol_secciones`
--
ALTER TABLE `acc_rol_secciones`
  ADD PRIMARY KEY (`rol_id`,`seccion_id`),
  ADD KEY `fk_rs_rol` (`rol_id`),
  ADD KEY `fk_rs_seccion` (`seccion_id`);

--
-- Indices de la tabla `acc_secciones`
--
ALTER TABLE `acc_secciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_app_slug` (`app_id`,`slug`),
  ADD KEY `fk_sec_app` (`app_id`);

--
-- Indices de la tabla `acc_usuario_roles`
--
ALTER TABLE `acc_usuario_roles`
  ADD PRIMARY KEY (`usuario_id`,`rol_id`),
  ADD KEY `fk_ur_usuario` (`usuario_id`),
  ADD KEY `fk_ur_rol` (`rol_id`);

--
-- Indices de la tabla `chat_areas`
--
ALTER TABLE `chat_areas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_area` (`area_id`),
  ADD KEY `fk_conv_creador` (`creado_por`);

--
-- Indices de la tabla `chat_mantencion`
--
ALTER TABLE `chat_mantencion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mant_estado` (`estado`),
  ADD KEY `idx_mant_tipo` (`tipo`),
  ADD KEY `idx_mant_area` (`area_id`),
  ADD KEY `idx_mant_asignado` (`asignado_a`),
  ADD KEY `fk_mant_creador` (`creado_por`);

--
-- Indices de la tabla `chat_mantencion_archivos`
--
ALTER TABLE `chat_mantencion_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_march_mant` (`mantencion_id`),
  ADD KEY `fk_march_usr` (`subido_por`);

--
-- Indices de la tabla `chat_mantencion_comentarios`
--
ALTER TABLE `chat_mantencion_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mcom_mant` (`mantencion_id`),
  ADD KEY `fk_mcom_usr` (`usuario_id`);

--
-- Indices de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conv_msg` (`conversacion_id`,`created_at`),
  ADD KEY `fk_msg_usr` (`usuario_id`);

--
-- Indices de la tabla `chat_mensaje_lecturas`
--
ALTER TABLE `chat_mensaje_lecturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_msg_usr` (`mensaje_id`,`usuario_id`),
  ADD KEY `fk_lect_usr` (`usuario_id`);

--
-- Indices de la tabla `chat_notificaciones`
--
ALTER TABLE `chat_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_usr` (`usuario_id`,`leida`);

--
-- Indices de la tabla `chat_participantes`
--
ALTER TABLE `chat_participantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_conv_usr` (`conversacion_id`,`usuario_id`),
  ADD KEY `fk_part_usr` (`usuario_id`);

--
-- Indices de la tabla `chat_permisos`
--
ALTER TABLE `chat_permisos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chat_roles`
--
ALTER TABLE `chat_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chat_rol_permisos`
--
ALTER TABLE `chat_rol_permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rol_permiso` (`rol_id`,`permiso_id`),
  ADD KEY `fk_rp_permiso` (`permiso_id`);

--
-- Indices de la tabla `chat_sesiones`
--
ALTER TABLE `chat_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token` (`token`(64)),
  ADD KEY `fk_ses_usr` (`usuario_id`);

--
-- Indices de la tabla `chat_tareas`
--
ALTER TABLE `chat_tareas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarea_estado` (`estado`),
  ADD KEY `idx_tarea_asignado` (`asignado_a`),
  ADD KEY `idx_tarea_area` (`area_id`),
  ADD KEY `fk_tar_creador` (`creado_por`);

--
-- Indices de la tabla `chat_tarea_archivos`
--
ALTER TABLE `chat_tarea_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tarch_tarea` (`tarea_id`),
  ADD KEY `fk_tarch_usr` (`subido_por`);

--
-- Indices de la tabla `chat_tarea_comentarios`
--
ALTER TABLE `chat_tarea_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tcom_tarea` (`tarea_id`),
  ADD KEY `fk_tcom_usr` (`usuario_id`);

--
-- Indices de la tabla `chat_usuarios`
--
ALTER TABLE `chat_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `fk_usr_rol` (`rol_id`),
  ADD KEY `fk_usr_area` (`area_id`);

--
-- Indices de la tabla `chk_apps`
--
ALTER TABLE `chk_apps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_slug` (`slug`);

--
-- Indices de la tabla `chk_areas`
--
ALTER TABLE `chk_areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `chk_checklists`
--
ALTER TABLE `chk_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_publico` (`token_publico`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `chk_checklist_preguntas`
--
ALTER TABLE `chk_checklist_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indices de la tabla `chk_evaluaciones`
--
ALTER TABLE `chk_evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`),
  ADD KEY `ejecutado_por` (`ejecutado_por`);

--
-- Indices de la tabla `chk_evaluacion_respuestas`
--
ALTER TABLE `chk_evaluacion_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indices de la tabla `chk_login_log`
--
ALTER TABLE `chk_login_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `chk_login_tokens`
--
ALTER TABLE `chk_login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token_email` (`token`,`email`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indices de la tabla `chk_report_cache`
--
ALTER TABLE `chk_report_cache`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chk_system_logs`
--
ALTER TABLE `chk_system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indices de la tabla `chk_usuarios`
--
ALTER TABLE `chk_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indices de la tabla `chk_usuario_apps`
--
ALTER TABLE `chk_usuario_apps`
  ADD PRIMARY KEY (`usuario_id`,`app_id`),
  ADD KEY `fk_ua_app` (`app_id`);

--
-- Indices de la tabla `coci_comandas`
--
ALTER TABLE `coci_comandas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_fecha_tipo` (`fecha`,`tipo_servicio`),
  ADD KEY `idx_company_id` (`company_id`);

--
-- Indices de la tabla `coc_otp`
--
ALTER TABLE `coc_otp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recep_expires` (`recep_id`,`expires_at`);

--
-- Indices de la tabla `colacion_adicional`
--
ALTER TABLE `colacion_adicional`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `colacion_impresiones`
--
ALTER TABLE `colacion_impresiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rut` (`rut`),
  ADD KEY `idx_servicio` (`servicio_id`);

--
-- Indices de la tabla `colacion_impresion_log`
--
ALTER TABLE `colacion_impresion_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_voucher` (`voucher_id`);

--
-- Indices de la tabla `colacion_lote`
--
ALTER TABLE `colacion_lote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_tipo_id` (`servicio_tipo_id`),
  ADD KEY `idx_fecha` (`fecha_servicio`),
  ADD KEY `idx_empresa_fecha` (`empresa_id`,`fecha_servicio`),
  ADD KEY `idx_from_upload_id` (`from_upload_id`),
  ADD KEY `activo01` (`activo`);

--
-- Indices de la tabla `colacion_lote_adicional`
--
ALTER TABLE `colacion_lote_adicional`
  ADD PRIMARY KEY (`lote_id`,`adicional_id`),
  ADD KEY `adicional_id` (`adicional_id`);

--
-- Indices de la tabla `colacion_lote_servicio`
--
ALTER TABLE `colacion_lote_servicio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_serv` (`servicio_tipo_id`);

--
-- Indices de la tabla `colacion_servicio_tipo`
--
ALTER TABLE `colacion_servicio_tipo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `colacion_voucher`
--
ALTER TABLE `colacion_voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_publico` (`codigo_publico`),
  ADD UNIQUE KEY `uq_lote_num` (`lote_id`,`numero_en_lote`),
  ADD KEY `idx_colacion_voucher_estado` (`estado`);

--
-- Indices de la tabla `colacion_voucher_impresiones`
--
ALTER TABLE `colacion_voucher_impresiones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `doc_alert_config`
--
ALTER TABLE `doc_alert_config`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `doc_companies`
--
ALTER TABLE `doc_companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `doc_contracts`
--
ALTER TABLE `doc_contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_contracts_parent_version` (`parent_id`,`version_number`);

--
-- Indices de la tabla `doc_contract_attachments`
--
ALTER TABLE `doc_contract_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indices de la tabla `doc_contract_history`
--
ALTER TABLE `doc_contract_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `doc_contract_hotels`
--
ALTER TABLE `doc_contract_hotels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contract_hotel` (`contract_id`,`hotel_id`),
  ADD KEY `hotel_id` (`hotel_id`);

--
-- Indices de la tabla `doc_contract_notes`
--
ALTER TABLE `doc_contract_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contract` (`contract_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `doc_contract_payments`
--
ALTER TABLE `doc_contract_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`),
  ADD KEY `registered_by` (`registered_by`),
  ADD KEY `fk_payment_booking` (`booking_id`);

--
-- Indices de la tabla `doc_contract_services`
--
ALTER TABLE `doc_contract_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_contract_service` (`contract_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indices de la tabla `doc_contract_templates`
--
ALTER TABLE `doc_contract_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `doc_contract_tiers`
--
ALTER TABLE `doc_contract_tiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indices de la tabla `doc_hotels`
--
ALTER TABLE `doc_hotels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indices de la tabla `doc_permissions`
--
ALTER TABLE `doc_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `doc_roles`
--
ALTER TABLE `doc_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `doc_role_permissions`
--
ALTER TABLE `doc_role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_perm` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indices de la tabla `doc_services`
--
ALTER TABLE `doc_services`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `doc_spaces`
--
ALTER TABLE `doc_spaces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_type` (`space_type`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `hotel_id` (`hotel_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indices de la tabla `doc_space_blocks`
--
ALTER TABLE `doc_space_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_space_dates` (`space_id`,`start_datetime`,`end_datetime`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `doc_space_bookings`
--
ALTER TABLE `doc_space_bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `idx_space_dates` (`space_id`,`start_datetime`,`end_datetime`),
  ADD KEY `idx_status` (`booking_status`),
  ADD KEY `idx_charge` (`charge_status`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_contract` (`contract_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `cancelled_by` (`cancelled_by`);

--
-- Indices de la tabla `doc_space_booking_charges`
--
ALTER TABLE `doc_space_booking_charges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indices de la tabla `doc_space_booking_history`
--
ALTER TABLE `doc_space_booking_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `doc_space_booking_items`
--
ALTER TABLE `doc_space_booking_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `extra_id` (`extra_id`);

--
-- Indices de la tabla `doc_space_booking_payments`
--
ALTER TABLE `doc_space_booking_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking` (`booking_id`),
  ADD KEY `registered_by` (`registered_by`);

--
-- Indices de la tabla `doc_space_extras`
--
ALTER TABLE `doc_space_extras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `doc_space_photos`
--
ALTER TABLE `doc_space_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `space_id` (`space_id`);

--
-- Indices de la tabla `doc_uf_values`
--
ALTER TABLE `doc_uf_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Indices de la tabla `doc_users`
--
ALTER TABLE `doc_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_reset_token` (`reset_token`);

--
-- Indices de la tabla `excel_upload`
--
ALTER TABLE `excel_upload`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `excel_upload_item`
--
ALTER TABLE `excel_upload_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_upload` (`upload_id`);

--
-- Indices de la tabla `nov_archivos`
--
ALTER TABLE `nov_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `novedad_id_fk01` (`novedad_id`);

--
-- Indices de la tabla `nov_checklists`
--
ALTER TABLE `nov_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `nov_checklist_preguntas`
--
ALTER TABLE `nov_checklist_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indices de la tabla `nov_empresas`
--
ALTER TABLE `nov_empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `nov_encargados`
--
ALTER TABLE `nov_encargados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `nov_evaluaciones`
--
ALTER TABLE `nov_evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`),
  ADD KEY `ejecutado_por` (`ejecutado_por`);

--
-- Indices de la tabla `nov_evaluacion_respuestas`
--
ALTER TABLE `nov_evaluacion_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indices de la tabla `nov_login_tokens`
--
ALTER TABLE `nov_login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `nov_novedades`
--
ALTER TABLE `nov_novedades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recepcionista_id` (`recepcionista_id`);

--
-- Indices de la tabla `nov_recepcionistas`
--
ALTER TABLE `nov_recepcionistas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_recep_area` (`area_id`);

--
-- Indices de la tabla `nov_seguimiento_comentarios`
--
ALTER TABLE `nov_seguimiento_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_novedad` (`novedad_id`);

--
-- Indices de la tabla `pre_categorias`
--
ALTER TABLE `pre_categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pre_precios`
--
ALTER TABLE `pre_precios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_tipo_cat` (`tipo_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `pre_tipos`
--
ALTER TABLE `pre_tipos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `temp_registros`
--
ALTER TABLE `temp_registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activo` (`activo`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_public_code` (`public_code`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_guest_name` (`guest_name`);

--
-- Indices de la tabla `ticket_daily_seq`
--
ALTER TABLE `ticket_daily_seq`
  ADD PRIMARY KEY (`day`);

--
-- Indices de la tabla `trell_adjuntos`
--
ALTER TABLE `trell_adjuntos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarjeta_id` (`tarjeta_id`);

--
-- Indices de la tabla `trell_checklist`
--
ALTER TABLE `trell_checklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarjeta_id` (`tarjeta_id`);

--
-- Indices de la tabla `trell_checklist_items`
--
ALTER TABLE `trell_checklist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_checklist_id` (`checklist_id`);

--
-- Indices de la tabla `trell_comentarios`
--
ALTER TABLE `trell_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarjeta_id` (`tarjeta_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`);

--
-- Indices de la tabla `trell_etiquetas`
--
ALTER TABLE `trell_etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tablero_id` (`tablero_id`);

--
-- Indices de la tabla `trell_listas`
--
ALTER TABLE `trell_listas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tablero_id` (`tablero_id`);

--
-- Indices de la tabla `trell_referencias`
--
ALTER TABLE `trell_referencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tarjeta_id` (`tarjeta_id`),
  ADD KEY `idx_tablero_destino` (`tablero_destino_id`);

--
-- Indices de la tabla `trell_tableros`
--
ALTER TABLE `trell_tableros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_area_id` (`area_id`);

--
-- Indices de la tabla `trell_tarjetas`
--
ALTER TABLE `trell_tarjetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tablero_numero` (`tablero_id`,`numero`),
  ADD KEY `idx_lista_id` (`lista_id`),
  ADD KEY `idx_tablero_id` (`tablero_id`),
  ADD KEY `idx_creado_por` (`creado_por`);

--
-- Indices de la tabla `trell_tarjeta_etiquetas`
--
ALTER TABLE `trell_tarjeta_etiquetas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tarjeta_etiqueta` (`tarjeta_id`,`etiqueta_id`);

--
-- Indices de la tabla `trell_tarjeta_miembros`
--
ALTER TABLE `trell_tarjeta_miembros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_tarjeta_usuario` (`tarjeta_id`,`usuario_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`);

--
-- Indices de la tabla `trell_usuario_tableros`
--
ALTER TABLE `trell_usuario_tableros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario_tablero` (`usuario_id`,`tablero_id`),
  ADD KEY `idx_tablero_id` (`tablero_id`);

--
-- Indices de la tabla `univ_courses`
--
ALTER TABLE `univ_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activo` (`activo`),
  ADD KEY `idx_hotel_tipo` (`hotel_id`,`tipo`);

--
-- Indices de la tabla `univ_cron_log`
--
ALTER TABLE `univ_cron_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proceso_fecha` (`proceso`,`fecha_ejecucion`);

--
-- Indices de la tabla `univ_cursos_por_perfil`
--
ALTER TABLE `univ_cursos_por_perfil`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_perfil_curso` (`perfil`,`course_id`),
  ADD KEY `fk_cxp_course` (`course_id`);

--
-- Indices de la tabla `univ_enrollments`
--
ALTER TABLE `univ_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_course_ciclo` (`user_id`,`course_id`,`ciclo`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_vencimiento` (`fecha_vencimiento`),
  ADD KEY `fk_enroll_course` (`course_id`);

--
-- Indices de la tabla `univ_evaluations`
--
ALTER TABLE `univ_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enrollment` (`enrollment_id`);

--
-- Indices de la tabla `univ_evaluation_answers`
--
ALTER TABLE `univ_evaluation_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_evaluation` (`evaluation_id`),
  ADD KEY `idx_question_correcta` (`question_id`,`es_correcta`) COMMENT 'Para reporte de preguntas más falladas';

--
-- Indices de la tabla `univ_options`
--
ALTER TABLE `univ_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_question` (`question_id`);

--
-- Indices de la tabla `univ_pages`
--
ALTER TABLE `univ_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_curso_orden` (`course_id`,`orden`);

--
-- Indices de la tabla `univ_questions`
--
ALTER TABLE `univ_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_curso_activa` (`course_id`,`activa`);

--
-- Indices de la tabla `zzz___empresas`
--
ALTER TABLE `zzz___empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresas_nombre` (`nombre`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acc_perfiles`
--
ALTER TABLE `acc_perfiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `acc_roles`
--
ALTER TABLE `acc_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `acc_secciones`
--
ALTER TABLE `acc_secciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_areas`
--
ALTER TABLE `chat_areas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_mantencion`
--
ALTER TABLE `chat_mantencion`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_mantencion_archivos`
--
ALTER TABLE `chat_mantencion_archivos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_mantencion_comentarios`
--
ALTER TABLE `chat_mantencion_comentarios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_mensaje_lecturas`
--
ALTER TABLE `chat_mensaje_lecturas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_notificaciones`
--
ALTER TABLE `chat_notificaciones`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_participantes`
--
ALTER TABLE `chat_participantes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_permisos`
--
ALTER TABLE `chat_permisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_roles`
--
ALTER TABLE `chat_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_rol_permisos`
--
ALTER TABLE `chat_rol_permisos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_sesiones`
--
ALTER TABLE `chat_sesiones`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_tareas`
--
ALTER TABLE `chat_tareas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_tarea_archivos`
--
ALTER TABLE `chat_tarea_archivos`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_tarea_comentarios`
--
ALTER TABLE `chat_tarea_comentarios`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chat_usuarios`
--
ALTER TABLE `chat_usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_apps`
--
ALTER TABLE `chk_apps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_areas`
--
ALTER TABLE `chk_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_checklists`
--
ALTER TABLE `chk_checklists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_checklist_preguntas`
--
ALTER TABLE `chk_checklist_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_evaluaciones`
--
ALTER TABLE `chk_evaluaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_evaluacion_respuestas`
--
ALTER TABLE `chk_evaluacion_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_login_log`
--
ALTER TABLE `chk_login_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_login_tokens`
--
ALTER TABLE `chk_login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_report_cache`
--
ALTER TABLE `chk_report_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_system_logs`
--
ALTER TABLE `chk_system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `chk_usuarios`
--
ALTER TABLE `chk_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_comandas`
--
ALTER TABLE `coci_comandas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coc_otp`
--
ALTER TABLE `coc_otp`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_adicional`
--
ALTER TABLE `colacion_adicional`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_impresiones`
--
ALTER TABLE `colacion_impresiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_impresion_log`
--
ALTER TABLE `colacion_impresion_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_lote`
--
ALTER TABLE `colacion_lote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_lote_servicio`
--
ALTER TABLE `colacion_lote_servicio`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_servicio_tipo`
--
ALTER TABLE `colacion_servicio_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_voucher`
--
ALTER TABLE `colacion_voucher`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `colacion_voucher_impresiones`
--
ALTER TABLE `colacion_voucher_impresiones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_alert_config`
--
ALTER TABLE `doc_alert_config`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_companies`
--
ALTER TABLE `doc_companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contracts`
--
ALTER TABLE `doc_contracts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_attachments`
--
ALTER TABLE `doc_contract_attachments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_history`
--
ALTER TABLE `doc_contract_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_hotels`
--
ALTER TABLE `doc_contract_hotels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_notes`
--
ALTER TABLE `doc_contract_notes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_payments`
--
ALTER TABLE `doc_contract_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_services`
--
ALTER TABLE `doc_contract_services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_templates`
--
ALTER TABLE `doc_contract_templates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_contract_tiers`
--
ALTER TABLE `doc_contract_tiers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_hotels`
--
ALTER TABLE `doc_hotels`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_permissions`
--
ALTER TABLE `doc_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_roles`
--
ALTER TABLE `doc_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_role_permissions`
--
ALTER TABLE `doc_role_permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_services`
--
ALTER TABLE `doc_services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_spaces`
--
ALTER TABLE `doc_spaces`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_blocks`
--
ALTER TABLE `doc_space_blocks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_bookings`
--
ALTER TABLE `doc_space_bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_booking_charges`
--
ALTER TABLE `doc_space_booking_charges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_booking_history`
--
ALTER TABLE `doc_space_booking_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_booking_items`
--
ALTER TABLE `doc_space_booking_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_booking_payments`
--
ALTER TABLE `doc_space_booking_payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_extras`
--
ALTER TABLE `doc_space_extras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_space_photos`
--
ALTER TABLE `doc_space_photos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_uf_values`
--
ALTER TABLE `doc_uf_values`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doc_users`
--
ALTER TABLE `doc_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `excel_upload`
--
ALTER TABLE `excel_upload`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `excel_upload_item`
--
ALTER TABLE `excel_upload_item`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_archivos`
--
ALTER TABLE `nov_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_checklists`
--
ALTER TABLE `nov_checklists`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_checklist_preguntas`
--
ALTER TABLE `nov_checklist_preguntas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_empresas`
--
ALTER TABLE `nov_empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_encargados`
--
ALTER TABLE `nov_encargados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_evaluaciones`
--
ALTER TABLE `nov_evaluaciones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_evaluacion_respuestas`
--
ALTER TABLE `nov_evaluacion_respuestas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_login_tokens`
--
ALTER TABLE `nov_login_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_novedades`
--
ALTER TABLE `nov_novedades`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_recepcionistas`
--
ALTER TABLE `nov_recepcionistas`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nov_seguimiento_comentarios`
--
ALTER TABLE `nov_seguimiento_comentarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pre_categorias`
--
ALTER TABLE `pre_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pre_precios`
--
ALTER TABLE `pre_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pre_tipos`
--
ALTER TABLE `pre_tipos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temp_registros`
--
ALTER TABLE `temp_registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_adjuntos`
--
ALTER TABLE `trell_adjuntos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_checklist`
--
ALTER TABLE `trell_checklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_checklist_items`
--
ALTER TABLE `trell_checklist_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_comentarios`
--
ALTER TABLE `trell_comentarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_etiquetas`
--
ALTER TABLE `trell_etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_listas`
--
ALTER TABLE `trell_listas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_referencias`
--
ALTER TABLE `trell_referencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_tableros`
--
ALTER TABLE `trell_tableros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_tarjetas`
--
ALTER TABLE `trell_tarjetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_tarjeta_etiquetas`
--
ALTER TABLE `trell_tarjeta_etiquetas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_tarjeta_miembros`
--
ALTER TABLE `trell_tarjeta_miembros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trell_usuario_tableros`
--
ALTER TABLE `trell_usuario_tableros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_courses`
--
ALTER TABLE `univ_courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_cron_log`
--
ALTER TABLE `univ_cron_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_cursos_por_perfil`
--
ALTER TABLE `univ_cursos_por_perfil`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_enrollments`
--
ALTER TABLE `univ_enrollments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_evaluations`
--
ALTER TABLE `univ_evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_evaluation_answers`
--
ALTER TABLE `univ_evaluation_answers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_options`
--
ALTER TABLE `univ_options`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_pages`
--
ALTER TABLE `univ_pages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `univ_questions`
--
ALTER TABLE `univ_questions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `zzz___empresas`
--
ALTER TABLE `zzz___empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acc_roles`
--
ALTER TABLE `acc_roles`
  ADD CONSTRAINT `fk_rol_app` FOREIGN KEY (`app_id`) REFERENCES `chk_apps` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `acc_rol_secciones`
--
ALTER TABLE `acc_rol_secciones`
  ADD CONSTRAINT `fk_rs_rol` FOREIGN KEY (`rol_id`) REFERENCES `acc_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rs_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `acc_secciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `acc_secciones`
--
ALTER TABLE `acc_secciones`
  ADD CONSTRAINT `fk_sec_app` FOREIGN KEY (`app_id`) REFERENCES `chk_apps` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `acc_usuario_roles`
--
ALTER TABLE `acc_usuario_roles`
  ADD CONSTRAINT `fk_ur_rol` FOREIGN KEY (`rol_id`) REFERENCES `acc_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `chk_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_conversaciones`
--
ALTER TABLE `chat_conversaciones`
  ADD CONSTRAINT `fk_conv_area` FOREIGN KEY (`area_id`) REFERENCES `chat_areas` (`id`),
  ADD CONSTRAINT `fk_conv_creador` FOREIGN KEY (`creado_por`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_mantencion`
--
ALTER TABLE `chat_mantencion`
  ADD CONSTRAINT `fk_mant_area` FOREIGN KEY (`area_id`) REFERENCES `chat_areas` (`id`),
  ADD CONSTRAINT `fk_mant_asig` FOREIGN KEY (`asignado_a`) REFERENCES `chat_usuarios` (`id`),
  ADD CONSTRAINT `fk_mant_creador` FOREIGN KEY (`creado_por`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_mantencion_archivos`
--
ALTER TABLE `chat_mantencion_archivos`
  ADD CONSTRAINT `fk_march_mant` FOREIGN KEY (`mantencion_id`) REFERENCES `chat_mantencion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_march_usr` FOREIGN KEY (`subido_por`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_mantencion_comentarios`
--
ALTER TABLE `chat_mantencion_comentarios`
  ADD CONSTRAINT `fk_mcom_mant` FOREIGN KEY (`mantencion_id`) REFERENCES `chat_mantencion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mcom_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_mensajes`
--
ALTER TABLE `chat_mensajes`
  ADD CONSTRAINT `fk_msg_conv` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_msg_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_mensaje_lecturas`
--
ALTER TABLE `chat_mensaje_lecturas`
  ADD CONSTRAINT `fk_lect_msg` FOREIGN KEY (`mensaje_id`) REFERENCES `chat_mensajes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lect_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_notificaciones`
--
ALTER TABLE `chat_notificaciones`
  ADD CONSTRAINT `fk_notif_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_participantes`
--
ALTER TABLE `chat_participantes`
  ADD CONSTRAINT `fk_part_conv` FOREIGN KEY (`conversacion_id`) REFERENCES `chat_conversaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_part_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_rol_permisos`
--
ALTER TABLE `chat_rol_permisos`
  ADD CONSTRAINT `fk_rp_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `chat_permisos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_rol` FOREIGN KEY (`rol_id`) REFERENCES `chat_roles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_sesiones`
--
ALTER TABLE `chat_sesiones`
  ADD CONSTRAINT `fk_ses_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chat_tareas`
--
ALTER TABLE `chat_tareas`
  ADD CONSTRAINT `fk_tar_area` FOREIGN KEY (`area_id`) REFERENCES `chat_areas` (`id`),
  ADD CONSTRAINT `fk_tar_asig` FOREIGN KEY (`asignado_a`) REFERENCES `chat_usuarios` (`id`),
  ADD CONSTRAINT `fk_tar_creador` FOREIGN KEY (`creado_por`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_tarea_archivos`
--
ALTER TABLE `chat_tarea_archivos`
  ADD CONSTRAINT `fk_tarch_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `chat_tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tarch_usr` FOREIGN KEY (`subido_por`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_tarea_comentarios`
--
ALTER TABLE `chat_tarea_comentarios`
  ADD CONSTRAINT `fk_tcom_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `chat_tareas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tcom_usr` FOREIGN KEY (`usuario_id`) REFERENCES `chat_usuarios` (`id`);

--
-- Filtros para la tabla `chat_usuarios`
--
ALTER TABLE `chat_usuarios`
  ADD CONSTRAINT `fk_usr_area` FOREIGN KEY (`area_id`) REFERENCES `chat_areas` (`id`),
  ADD CONSTRAINT `fk_usr_rol` FOREIGN KEY (`rol_id`) REFERENCES `chat_roles` (`id`);

--
-- Filtros para la tabla `chk_checklists`
--
ALTER TABLE `chk_checklists`
  ADD CONSTRAINT `chk_checklists_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `chk_usuarios` (`email`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chk_checklist_preguntas`
--
ALTER TABLE `chk_checklist_preguntas`
  ADD CONSTRAINT `chk_checklist_preguntas_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `chk_checklists` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chk_evaluaciones`
--
ALTER TABLE `chk_evaluaciones`
  ADD CONSTRAINT `chk_evaluaciones_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `chk_checklists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chk_evaluaciones_ibfk_2` FOREIGN KEY (`ejecutado_por`) REFERENCES `chk_usuarios` (`email`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chk_evaluacion_respuestas`
--
ALTER TABLE `chk_evaluacion_respuestas`
  ADD CONSTRAINT `chk_evaluacion_respuestas_ibfk_1` FOREIGN KEY (`evaluacion_id`) REFERENCES `chk_evaluaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chk_evaluacion_respuestas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `chk_checklist_preguntas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `chk_usuario_apps`
--
ALTER TABLE `chk_usuario_apps`
  ADD CONSTRAINT `fk_ua_app` FOREIGN KEY (`app_id`) REFERENCES `chk_apps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ua_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `chk_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `colacion_impresion_log`
--
ALTER TABLE `colacion_impresion_log`
  ADD CONSTRAINT `fk_log_lote` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `colacion_voucher` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `colacion_lote`
--
ALTER TABLE `colacion_lote`
  ADD CONSTRAINT `colacion_lote_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `zzz___empresas` (`id`),
  ADD CONSTRAINT `colacion_lote_ibfk_2` FOREIGN KEY (`servicio_tipo_id`) REFERENCES `colacion_servicio_tipo` (`id`);

--
-- Filtros para la tabla `colacion_lote_adicional`
--
ALTER TABLE `colacion_lote_adicional`
  ADD CONSTRAINT `colacion_lote_adicional_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `colacion_lote_adicional_ibfk_2` FOREIGN KEY (`adicional_id`) REFERENCES `colacion_adicional` (`id`);

--
-- Filtros para la tabla `colacion_lote_servicio`
--
ALTER TABLE `colacion_lote_servicio`
  ADD CONSTRAINT `fk_lote_serv_lote` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lote_serv_tipo` FOREIGN KEY (`servicio_tipo_id`) REFERENCES `colacion_servicio_tipo` (`id`);

--
-- Filtros para la tabla `colacion_voucher`
--
ALTER TABLE `colacion_voucher`
  ADD CONSTRAINT `colacion_voucher_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `doc_companies`
--
ALTER TABLE `doc_companies`
  ADD CONSTRAINT `doc_companies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_contracts`
--
ALTER TABLE `doc_contracts`
  ADD CONSTRAINT `doc_contracts_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `doc_companies` (`id`),
  ADD CONSTRAINT `doc_contracts_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `doc_contract_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_contracts_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contract_parent` FOREIGN KEY (`parent_id`) REFERENCES `doc_contracts` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_contract_attachments`
--
ALTER TABLE `doc_contract_attachments`
  ADD CONSTRAINT `doc_contract_attachments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`),
  ADD CONSTRAINT `doc_contract_attachments_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `doc_contract_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_contract_attachments_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_contract_history`
--
ALTER TABLE `doc_contract_history`
  ADD CONSTRAINT `doc_contract_history_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_contract_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_contract_hotels`
--
ALTER TABLE `doc_contract_hotels`
  ADD CONSTRAINT `doc_contract_hotels_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_contract_hotels_ibfk_2` FOREIGN KEY (`hotel_id`) REFERENCES `doc_hotels` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `doc_contract_notes`
--
ALTER TABLE `doc_contract_notes`
  ADD CONSTRAINT `doc_contract_notes_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_contract_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `doc_users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `doc_contract_payments`
--
ALTER TABLE `doc_contract_payments`
  ADD CONSTRAINT `doc_contract_payments_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`),
  ADD CONSTRAINT `doc_contract_payments_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `doc_space_bookings` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_contract_services`
--
ALTER TABLE `doc_contract_services`
  ADD CONSTRAINT `doc_contract_services_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_contract_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `doc_services` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `doc_contract_templates`
--
ALTER TABLE `doc_contract_templates`
  ADD CONSTRAINT `doc_contract_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_contract_tiers`
--
ALTER TABLE `doc_contract_tiers`
  ADD CONSTRAINT `doc_contract_tiers_ibfk_1` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `doc_role_permissions`
--
ALTER TABLE `doc_role_permissions`
  ADD CONSTRAINT `doc_role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `doc_roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `doc_permissions` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `doc_spaces`
--
ALTER TABLE `doc_spaces`
  ADD CONSTRAINT `doc_spaces_ibfk_1` FOREIGN KEY (`hotel_id`) REFERENCES `doc_hotels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_spaces_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_spaces_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_blocks`
--
ALTER TABLE `doc_space_blocks`
  ADD CONSTRAINT `doc_space_blocks_ibfk_1` FOREIGN KEY (`space_id`) REFERENCES `doc_spaces` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_space_blocks_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_bookings`
--
ALTER TABLE `doc_space_bookings`
  ADD CONSTRAINT `doc_space_bookings_ibfk_1` FOREIGN KEY (`space_id`) REFERENCES `doc_spaces` (`id`),
  ADD CONSTRAINT `doc_space_bookings_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `doc_companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_space_bookings_ibfk_3` FOREIGN KEY (`contract_id`) REFERENCES `doc_contracts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_space_bookings_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_space_bookings_ibfk_5` FOREIGN KEY (`updated_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `doc_space_bookings_ibfk_6` FOREIGN KEY (`cancelled_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_booking_charges`
--
ALTER TABLE `doc_space_booking_charges`
  ADD CONSTRAINT `doc_space_booking_charges_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `doc_space_bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_space_booking_charges_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_booking_history`
--
ALTER TABLE `doc_space_booking_history`
  ADD CONSTRAINT `doc_space_booking_history_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `doc_space_bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_space_booking_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_booking_items`
--
ALTER TABLE `doc_space_booking_items`
  ADD CONSTRAINT `doc_space_booking_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `doc_space_bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_space_booking_items_ibfk_2` FOREIGN KEY (`extra_id`) REFERENCES `doc_space_extras` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_booking_payments`
--
ALTER TABLE `doc_space_booking_payments`
  ADD CONSTRAINT `doc_space_booking_payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `doc_space_bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doc_space_booking_payments_ibfk_2` FOREIGN KEY (`registered_by`) REFERENCES `doc_users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `doc_space_photos`
--
ALTER TABLE `doc_space_photos`
  ADD CONSTRAINT `doc_space_photos_ibfk_1` FOREIGN KEY (`space_id`) REFERENCES `doc_spaces` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `doc_users`
--
ALTER TABLE `doc_users`
  ADD CONSTRAINT `doc_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `doc_roles` (`id`);

--
-- Filtros para la tabla `excel_upload_item`
--
ALTER TABLE `excel_upload_item`
  ADD CONSTRAINT `fk_excel_item_upload` FOREIGN KEY (`upload_id`) REFERENCES `excel_upload` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `nov_archivos`
--
ALTER TABLE `nov_archivos`
  ADD CONSTRAINT `novedad_id_fk01` FOREIGN KEY (`novedad_id`) REFERENCES `nov_novedades` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `nov_checklists`
--
ALTER TABLE `nov_checklists`
  ADD CONSTRAINT `nov_checklists_ibfk_1` FOREIGN KEY (`creado_por`) REFERENCES `cat6852_incidencias`.`nov_usuarios` (`id`);

--
-- Filtros para la tabla `nov_checklist_preguntas`
--
ALTER TABLE `nov_checklist_preguntas`
  ADD CONSTRAINT `nov_checklist_preguntas_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `nov_checklists` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nov_encargados`
--
ALTER TABLE `nov_encargados`
  ADD CONSTRAINT `nov_encargados_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `nov_empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nov_evaluaciones`
--
ALTER TABLE `nov_evaluaciones`
  ADD CONSTRAINT `nov_evaluaciones_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `nov_checklists` (`id`),
  ADD CONSTRAINT `nov_evaluaciones_ibfk_2` FOREIGN KEY (`ejecutado_por`) REFERENCES `cat6852_incidencias`.`nov_usuarios` (`id`);

--
-- Filtros para la tabla `nov_evaluacion_respuestas`
--
ALTER TABLE `nov_evaluacion_respuestas`
  ADD CONSTRAINT `nov_evaluacion_respuestas_ibfk_1` FOREIGN KEY (`evaluacion_id`) REFERENCES `nov_evaluaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nov_evaluacion_respuestas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `nov_checklist_preguntas` (`id`);

--
-- Filtros para la tabla `nov_login_tokens`
--
ALTER TABLE `nov_login_tokens`
  ADD CONSTRAINT `nov_login_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `cat6852_incidencias`.`nov_usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nov_recepcionistas`
--
ALTER TABLE `nov_recepcionistas`
  ADD CONSTRAINT `fk_recep_area` FOREIGN KEY (`area_id`) REFERENCES `chat_areas` (`id`);

--
-- Filtros para la tabla `nov_seguimiento_comentarios`
--
ALTER TABLE `nov_seguimiento_comentarios`
  ADD CONSTRAINT `fk_novedad_comentarios` FOREIGN KEY (`novedad_id`) REFERENCES `nov_novedades` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pre_precios`
--
ALTER TABLE `pre_precios`
  ADD CONSTRAINT `pre_precios_ibfk_1` FOREIGN KEY (`tipo_id`) REFERENCES `pre_tipos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pre_precios_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `pre_categorias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_cursos_por_perfil`
--
ALTER TABLE `univ_cursos_por_perfil`
  ADD CONSTRAINT `fk_cxp_course` FOREIGN KEY (`course_id`) REFERENCES `univ_courses` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_enrollments`
--
ALTER TABLE `univ_enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `univ_courses` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_evaluations`
--
ALTER TABLE `univ_evaluations`
  ADD CONSTRAINT `fk_eval_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `univ_enrollments` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_evaluation_answers`
--
ALTER TABLE `univ_evaluation_answers`
  ADD CONSTRAINT `fk_ans_eval` FOREIGN KEY (`evaluation_id`) REFERENCES `univ_evaluations` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_options`
--
ALTER TABLE `univ_options`
  ADD CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `univ_questions` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_pages`
--
ALTER TABLE `univ_pages`
  ADD CONSTRAINT `fk_pages_course` FOREIGN KEY (`course_id`) REFERENCES `univ_courses` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `univ_questions`
--
ALTER TABLE `univ_questions`
  ADD CONSTRAINT `fk_questions_course` FOREIGN KEY (`course_id`) REFERENCES `univ_courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
