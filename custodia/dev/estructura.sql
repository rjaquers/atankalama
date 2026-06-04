-- phpMyAdmin SQL Dump
-- version 4.9.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 05, 2026 at 01:11 PM
-- Server version: 5.7.26
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cat6852_hotel_tickets`
--

-- --------------------------------------------------------

--
-- Table structure for table `chk_areas`
--

CREATE TABLE `chk_areas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `chk_checklists`
--

CREATE TABLE `chk_checklists` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `area` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','inactivo','eliminado') COLLATE utf8mb4_unicode_ci DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_checklist_preguntas`
--

CREATE TABLE `chk_checklist_preguntas` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `pregunta` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_respuesta` enum('boolean','text','numeric_scale') COLLATE utf8mb4_unicode_ci NOT NULL,
  `escala_min` int(11) DEFAULT NULL,
  `escala_max` int(11) DEFAULT NULL,
  `orden` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_evaluaciones`
--

CREATE TABLE `chk_evaluaciones` (
  `id` int(11) NOT NULL,
  `checklist_id` int(11) NOT NULL,
  `evaluado_nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `evaluado_apellido` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ejecutado_por` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_evaluacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_evaluacion_respuestas`
--

CREATE TABLE `chk_evaluacion_respuestas` (
  `id` int(11) NOT NULL,
  `evaluacion_id` int(11) NOT NULL,
  `pregunta_id` int(11) NOT NULL,
  `respuesta_boolean` tinyint(1) DEFAULT NULL,
  `respuesta_texto` text COLLATE utf8mb4_unicode_ci,
  `respuesta_numerica` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_login_tokens`
--

CREATE TABLE `chk_login_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` char(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `attempts` int(11) DEFAULT '0',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_report_cache`
--

CREATE TABLE `chk_report_cache` (
  `id` int(11) NOT NULL,
  `tipo_reporte` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parametros_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `resultado_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `generado_por` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_system_logs`
--

CREATE TABLE `chk_system_logs` (
  `id` int(11) NOT NULL,
  `nivel` enum('INFO','WARNING','ERROR','SECURITY','CRITICAL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modulo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `contexto_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `user_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chk_usuarios`
--

CREATE TABLE `chk_usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perfil` enum('Administrador','Operador') COLLATE utf8mb4_unicode_ci DEFAULT 'Operador',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_adicional`
--

CREATE TABLE `colacion_adicional` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` tinyint(1) NOT NULL DEFAULT '2' COMMENT '1=Principal, 2=Adicional,\r\n3=Opcional',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_impresiones`
--

CREATE TABLE `colacion_impresiones` (
  `id` int(10) UNSIGNED NOT NULL,
  `rut` varchar(20) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_impresion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `copia` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_impresion_log`
--

CREATE TABLE `colacion_impresion_log` (
  `id` bigint(20) NOT NULL,
  `lote_id` int(11) NOT NULL,
  `voucher_id` bigint(20) DEFAULT NULL,
  `accion` enum('impresion','reimpresion') NOT NULL,
  `copias` int(11) NOT NULL DEFAULT '1',
  `usuario_id` int(11) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_lote`
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
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `excel` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `activo` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_lote_adicional`
--

CREATE TABLE `colacion_lote_adicional` (
  `lote_id` int(11) NOT NULL,
  `adicional_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_lote_servicio`
--

CREATE TABLE `colacion_lote_servicio` (
  `id` int(10) UNSIGNED NOT NULL,
  `lote_id` int(11) NOT NULL,
  `servicio_tipo_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_servicio_tipo`
--

CREATE TABLE `colacion_servicio_tipo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `hora_inicio` time NOT NULL DEFAULT '00:00:00',
  `hora_fin` time NOT NULL DEFAULT '23:59:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_voucher`
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
  `scan_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `impreso_count` int(11) NOT NULL DEFAULT '0',
  `ultimo_impreso_en` timestamp NULL DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `colacion_voucher_impresiones`
--

CREATE TABLE `colacion_voucher_impresiones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rut` varchar(15) NOT NULL,
  `servicio_id` int(11) NOT NULL,
  `fecha_impresion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `excel_upload`
--

CREATE TABLE `excel_upload` (
  `id` int(10) UNSIGNED NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_path` varchar(512) NOT NULL,
  `total_rows` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `excel_upload_item`
--

CREATE TABLE `excel_upload_item` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `upload_id` int(10) UNSIGNED NOT NULL,
  `fila_nro` int(10) UNSIGNED NOT NULL,
  `id_archivo` varchar(64) NOT NULL,
  `rut` varchar(24) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `habitacion` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `public_code` varchar(32) NOT NULL,
  `mode` enum('custodia','perdido') NOT NULL,
  `guest_name` varchar(120) DEFAULT NULL,
  `item_type` varchar(120) DEFAULT NULL,
  `location_label` varchar(120) DEFAULT NULL,
  `notes` text,
  `status` enum('en_custodia','retirado','extraviado','cancelado') NOT NULL DEFAULT 'en_custodia',
  `created_at` datetime NOT NULL,
  `created_by_ip` varbinary(16) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `print_count` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `retrieved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ticket_daily_seq`
--

CREATE TABLE `ticket_daily_seq` (
  `day` date NOT NULL,
  `last_seq` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chk_areas`
--
ALTER TABLE `chk_areas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `chk_checklists`
--
ALTER TABLE `chk_checklists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `chk_checklist_preguntas`
--
ALTER TABLE `chk_checklist_preguntas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indexes for table `chk_evaluaciones`
--
ALTER TABLE `chk_evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`),
  ADD KEY `ejecutado_por` (`ejecutado_por`);

--
-- Indexes for table `chk_evaluacion_respuestas`
--
ALTER TABLE `chk_evaluacion_respuestas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluacion_id` (`evaluacion_id`),
  ADD KEY `pregunta_id` (`pregunta_id`);

--
-- Indexes for table `chk_login_tokens`
--
ALTER TABLE `chk_login_tokens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_token_email` (`token`,`email`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `chk_report_cache`
--
ALTER TABLE `chk_report_cache`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chk_system_logs`
--
ALTER TABLE `chk_system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `chk_usuarios`
--
ALTER TABLE `chk_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `colacion_adicional`
--
ALTER TABLE `colacion_adicional`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `colacion_impresiones`
--
ALTER TABLE `colacion_impresiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rut` (`rut`),
  ADD KEY `idx_servicio` (`servicio_id`);

--
-- Indexes for table `colacion_impresion_log`
--
ALTER TABLE `colacion_impresion_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_voucher` (`voucher_id`);

--
-- Indexes for table `colacion_lote`
--
ALTER TABLE `colacion_lote`
  ADD PRIMARY KEY (`id`),
  ADD KEY `servicio_tipo_id` (`servicio_tipo_id`),
  ADD KEY `idx_fecha` (`fecha_servicio`),
  ADD KEY `idx_empresa_fecha` (`empresa_id`,`fecha_servicio`),
  ADD KEY `idx_from_upload_id` (`from_upload_id`),
  ADD KEY `activo01` (`activo`);

--
-- Indexes for table `colacion_lote_adicional`
--
ALTER TABLE `colacion_lote_adicional`
  ADD PRIMARY KEY (`lote_id`,`adicional_id`),
  ADD KEY `adicional_id` (`adicional_id`);

--
-- Indexes for table `colacion_lote_servicio`
--
ALTER TABLE `colacion_lote_servicio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lote` (`lote_id`),
  ADD KEY `idx_serv` (`servicio_tipo_id`);

--
-- Indexes for table `colacion_servicio_tipo`
--
ALTER TABLE `colacion_servicio_tipo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indexes for table `colacion_voucher`
--
ALTER TABLE `colacion_voucher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_publico` (`codigo_publico`),
  ADD UNIQUE KEY `uq_lote_num` (`lote_id`,`numero_en_lote`),
  ADD KEY `idx_colacion_voucher_estado` (`estado`);

--
-- Indexes for table `colacion_voucher_impresiones`
--
ALTER TABLE `colacion_voucher_impresiones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_empresas_nombre` (`nombre`);

--
-- Indexes for table `excel_upload`
--
ALTER TABLE `excel_upload`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `excel_upload_item`
--
ALTER TABLE `excel_upload_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_upload` (`upload_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_public_code` (`public_code`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_guest_name` (`guest_name`);

--
-- Indexes for table `ticket_daily_seq`
--
ALTER TABLE `ticket_daily_seq`
  ADD PRIMARY KEY (`day`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chk_areas`
--
ALTER TABLE `chk_areas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_checklists`
--
ALTER TABLE `chk_checklists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_checklist_preguntas`
--
ALTER TABLE `chk_checklist_preguntas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_evaluaciones`
--
ALTER TABLE `chk_evaluaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_evaluacion_respuestas`
--
ALTER TABLE `chk_evaluacion_respuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_login_tokens`
--
ALTER TABLE `chk_login_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_report_cache`
--
ALTER TABLE `chk_report_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_system_logs`
--
ALTER TABLE `chk_system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chk_usuarios`
--
ALTER TABLE `chk_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_adicional`
--
ALTER TABLE `colacion_adicional`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_impresiones`
--
ALTER TABLE `colacion_impresiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_impresion_log`
--
ALTER TABLE `colacion_impresion_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_lote`
--
ALTER TABLE `colacion_lote`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_lote_servicio`
--
ALTER TABLE `colacion_lote_servicio`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_servicio_tipo`
--
ALTER TABLE `colacion_servicio_tipo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_voucher`
--
ALTER TABLE `colacion_voucher`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colacion_voucher_impresiones`
--
ALTER TABLE `colacion_voucher_impresiones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `excel_upload`
--
ALTER TABLE `excel_upload`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `excel_upload_item`
--
ALTER TABLE `excel_upload_item`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chk_checklists`
--
ALTER TABLE `chk_checklists`
  ADD CONSTRAINT `chk_checklists_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `chk_usuarios` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `chk_checklist_preguntas`
--
ALTER TABLE `chk_checklist_preguntas`
  ADD CONSTRAINT `chk_checklist_preguntas_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `chk_checklists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chk_evaluaciones`
--
ALTER TABLE `chk_evaluaciones`
  ADD CONSTRAINT `chk_evaluaciones_ibfk_1` FOREIGN KEY (`checklist_id`) REFERENCES `chk_checklists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chk_evaluaciones_ibfk_2` FOREIGN KEY (`ejecutado_por`) REFERENCES `chk_usuarios` (`email`) ON DELETE CASCADE;

--
-- Constraints for table `chk_evaluacion_respuestas`
--
ALTER TABLE `chk_evaluacion_respuestas`
  ADD CONSTRAINT `chk_evaluacion_respuestas_ibfk_1` FOREIGN KEY (`evaluacion_id`) REFERENCES `chk_evaluaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chk_evaluacion_respuestas_ibfk_2` FOREIGN KEY (`pregunta_id`) REFERENCES `chk_checklist_preguntas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `colacion_impresion_log`
--
ALTER TABLE `colacion_impresion_log`
  ADD CONSTRAINT `fk_log_lote` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `colacion_voucher` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `colacion_lote`
--
ALTER TABLE `colacion_lote`
  ADD CONSTRAINT `colacion_lote_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`),
  ADD CONSTRAINT `colacion_lote_ibfk_2` FOREIGN KEY (`servicio_tipo_id`) REFERENCES `colacion_servicio_tipo` (`id`);

--
-- Constraints for table `colacion_lote_adicional`
--
ALTER TABLE `colacion_lote_adicional`
  ADD CONSTRAINT `colacion_lote_adicional_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `colacion_lote_adicional_ibfk_2` FOREIGN KEY (`adicional_id`) REFERENCES `colacion_adicional` (`id`);

--
-- Constraints for table `colacion_lote_servicio`
--
ALTER TABLE `colacion_lote_servicio`
  ADD CONSTRAINT `fk_lote_serv_lote` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lote_serv_tipo` FOREIGN KEY (`servicio_tipo_id`) REFERENCES `colacion_servicio_tipo` (`id`);

--
-- Constraints for table `colacion_voucher`
--
ALTER TABLE `colacion_voucher`
  ADD CONSTRAINT `colacion_voucher_ibfk_1` FOREIGN KEY (`lote_id`) REFERENCES `colacion_lote` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `excel_upload_item`
--
ALTER TABLE `excel_upload_item`
  ADD CONSTRAINT `fk_excel_item_upload` FOREIGN KEY (`upload_id`) REFERENCES `excel_upload` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
