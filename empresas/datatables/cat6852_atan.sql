-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 04-06-2026 a las 14:19:32
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
-- Base de datos: `cat6852_atan`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agenciastrans`
--

CREATE TABLE `agenciastrans` (
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `codtrans` varchar(8) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agentes`
--

CREATE TABLE `agentes` (
  `cargo` varchar(100) DEFAULT NULL,
  `cifnif` varchar(30) DEFAULT NULL,
  `codagente` varchar(10) NOT NULL,
  `debaja` tinyint(1) DEFAULT 0,
  `email` varchar(100) DEFAULT NULL,
  `fechabaja` date DEFAULT NULL,
  `fechaalta` date DEFAULT NULL,
  `idcontacto` int(11) DEFAULT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `telefono1` varchar(30) DEFAULT NULL,
  `telefono2` varchar(30) DEFAULT NULL,
  `tipoidfiscal` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `albaranescli`
--

CREATE TABLE `albaranescli` (
  `apartado` varchar(10) DEFAULT NULL,
  `cifnif` varchar(30) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codagente` varchar(10) DEFAULT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `codcliente` varchar(10) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codigoenv` varchar(200) DEFAULT NULL,
  `codpago` varchar(10) NOT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `codtrans` varchar(8) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `femail` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idalbaran` int(11) NOT NULL,
  `idcontactoenv` int(11) DEFAULT NULL,
  `idcontactofact` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `nombrecliente` varchar(100) DEFAULT NULL,
  `numdocs` int(11) DEFAULT 0,
  `numero` varchar(12) NOT NULL,
  `numero2` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalbeneficio` double DEFAULT NULL,
  `totalcoste` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `albaranesprov`
--

CREATE TABLE `albaranesprov` (
  `cifnif` varchar(30) NOT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codpago` varchar(10) NOT NULL,
  `codproveedor` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `fecha` date NOT NULL,
  `femail` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idalbaran` int(11) NOT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `numdocs` int(11) DEFAULT 0,
  `numero` varchar(12) NOT NULL,
  `numproveedor` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacenes`
--

CREATE TABLE `almacenes` (
  `activo` tinyint(1) DEFAULT 1,
  `apartado` varchar(10) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codalmacen` varchar(4) NOT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `api_access`
--

CREATE TABLE `api_access` (
  `allowdelete` tinyint(1) DEFAULT NULL,
  `allowget` tinyint(1) DEFAULT NULL,
  `allowpost` tinyint(1) DEFAULT NULL,
  `allowput` tinyint(1) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idapikey` int(11) NOT NULL,
  `resource` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `api_keys`
--

CREATE TABLE `api_keys` (
  `apikey` varchar(99) NOT NULL,
  `creationdate` date NOT NULL,
  `description` varchar(150) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `fullaccess` tinyint(1) DEFAULT 0,
  `id` int(11) NOT NULL,
  `nick` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asientos`
--

CREATE TABLE `asientos` (
  `canal` int(11) DEFAULT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  `editable` tinyint(1) NOT NULL,
  `fecha` date NOT NULL,
  `idasiento` int(11) NOT NULL,
  `iddiario` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `importe` double NOT NULL DEFAULT 0,
  `numero` int(11) NOT NULL,
  `operacion` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atan_contactos`
--

CREATE TABLE `atan_contactos` (
  `id_contacto` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributos`
--

CREATE TABLE `atributos` (
  `codatributo` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `num_selector` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `atributos_valores`
--

CREATE TABLE `atributos_valores` (
  `codatributo` varchar(20) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `valor` varchar(100) NOT NULL,
  `orden` int(11) DEFAULT 100
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attached_files`
--

CREATE TABLE `attached_files` (
  `date` date NOT NULL,
  `filename` varchar(100) NOT NULL,
  `hour` time DEFAULT NULL,
  `idfile` int(11) NOT NULL,
  `mimetype` varchar(100) DEFAULT NULL,
  `path` varchar(200) NOT NULL,
  `size` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attached_files_rel`
--

CREATE TABLE `attached_files_rel` (
  `creationdate` timestamp NULL DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idfile` int(11) NOT NULL,
  `model` varchar(30) NOT NULL,
  `modelid` int(11) NOT NULL,
  `modelcode` varchar(40) DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `observations` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `alias` text DEFAULT NULL,
  `ciudad` varchar(100) NOT NULL,
  `creation_date` timestamp NULL DEFAULT NULL,
  `codeid` varchar(5) DEFAULT NULL,
  `idciudad` int(11) NOT NULL,
  `idprovincia` int(11) NOT NULL,
  `last_nick` varchar(50) DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `cifnif` varchar(30) NOT NULL,
  `codagente` varchar(10) DEFAULT NULL,
  `codcliente` varchar(10) NOT NULL,
  `codgrupo` varchar(6) DEFAULT NULL,
  `codpago` varchar(10) DEFAULT NULL,
  `codproveedor` varchar(10) DEFAULT NULL,
  `codretencion` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) DEFAULT NULL,
  `codsubcuenta` varchar(15) DEFAULT NULL,
  `codtarifa` varchar(6) DEFAULT NULL,
  `debaja` tinyint(1) DEFAULT 0,
  `diaspago` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `fechabaja` date DEFAULT NULL,
  `fechaalta` date DEFAULT NULL,
  `idcontactofact` int(11) DEFAULT NULL,
  `idcontactoenv` int(11) DEFAULT NULL,
  `langcode` varchar(10) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `personafisica` tinyint(1) DEFAULT 1,
  `razonsocial` varchar(100) DEFAULT NULL,
  `regimeniva` varchar(20) DEFAULT NULL,
  `riesgoalcanzado` double DEFAULT NULL,
  `riesgomax` double DEFAULT NULL,
  `telefono1` varchar(30) DEFAULT NULL,
  `telefono2` varchar(30) DEFAULT NULL,
  `tipoidfiscal` varchar(25) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_cambios_log`
--

CREATE TABLE `coci_cambios_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `reserva_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL si el cambio es sobre una comanda suelta',
  `comanda_id` int(10) UNSIGNED NOT NULL,
  `tabla` varchar(60) NOT NULL COMMENT 'coci_comandas | coci_voucher_clientes | coci_vouchers_genericos',
  `campo` varchar(60) NOT NULL COMMENT 'nombre del campo modificado o acción (ej: voucher_importado)',
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text DEFAULT NULL,
  `email_usuario` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_comandas`
--

CREATE TABLE `coci_comandas` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_servicio` enum('cena','colacion','colacion_especial','desayuno','almuerzo') NOT NULL,
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
  `reserva_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK a coci_reservas; NULL si la comanda no pertenece a una reserva',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cobrado` tinyint(1) NOT NULL DEFAULT 0,
  `cobrado_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_comanda_respaldos`
--

CREATE TABLE `coci_comanda_respaldos` (
  `id` int(11) NOT NULL,
  `comanda_id` int(11) NOT NULL,
  `ruta_archivo` varchar(500) NOT NULL,
  `tipo_archivo` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_detalles_orden`
--

CREATE TABLE `coci_detalles_orden` (
  `id` int(11) NOT NULL,
  `orden_id` int(11) NOT NULL,
  `producto` varchar(500) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `pagado` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1 pagado, 0 no pagado',
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `boucher` varchar(200) NOT NULL DEFAULT 'sin comentarios' COMMENT 'bloucher o comentarios'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_impresiones_log`
--

CREATE TABLE `coci_impresiones_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `comanda_id` int(10) UNSIGNED NOT NULL,
  `reserva_id` int(10) UNSIGNED DEFAULT NULL,
  `email_usuario` varchar(255) NOT NULL,
  `cantidad_nominales` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `cantidad_genericos` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_ordenes`
--

CREATE TABLE `coci_ordenes` (
  `id` int(11) NOT NULL,
  `habitacion` varchar(10) NOT NULL,
  `lugar` enum('Habitacion','Comedor') NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'hora solicitado servicio',
  `nombre_huesped` varchar(100) DEFAULT NULL,
  `tipo_solicitante` enum('particular','empresa') NOT NULL DEFAULT 'particular',
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `contract_id` int(10) UNSIGNED DEFAULT NULL,
  `nombre_contacto` varchar(200) DEFAULT NULL,
  `email_respaldo` varchar(200) DEFAULT NULL,
  `archivo_respaldo` varchar(500) DEFAULT NULL,
  `cantidad_personas` int(11) NOT NULL DEFAULT 1,
  `total` decimal(10,2) NOT NULL,
  `pagado` tinyint(1) NOT NULL DEFAULT 0,
  `voucher` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estado` enum('pendiente','cerrada') NOT NULL DEFAULT 'pendiente',
  `fecha_cierre` datetime DEFAULT NULL,
  `color_cierre` varchar(30) DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha_registro',
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_productos`
--

CREATE TABLE `coci_productos` (
  `producto_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `categoria` varchar(50) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL,
  `stock_minimo` int(11) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_reservas`
--

CREATE TABLE `coci_reservas` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL COMMENT 'Referencia o nombre de la reserva',
  `company_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK a doc_companies (BD tickets)',
  `nombre_empresa` varchar(255) DEFAULT NULL COMMENT 'Nombre libre si no hay company_id',
  `fecha_desde` date NOT NULL,
  `fecha_hasta` date NOT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_vouchers_genericos`
--

CREATE TABLE `coci_vouchers_genericos` (
  `id` int(10) UNSIGNED NOT NULL,
  `comanda_id` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(32) NOT NULL,
  `numero` smallint(5) UNSIGNED NOT NULL,
  `canjeado` tinyint(1) NOT NULL DEFAULT 0,
  `canjeado_en` datetime DEFAULT NULL,
  `impreso` tinyint(1) NOT NULL DEFAULT 0,
  `veces_impreso` int(11) DEFAULT 0,
  `impreso_en` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coci_voucher_clientes`
--

CREATE TABLE `coci_voucher_clientes` (
  `id` int(11) NOT NULL,
  `comanda_id` int(11) NOT NULL,
  `rut` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `codigo` varchar(32) NOT NULL,
  `canjeado` tinyint(1) NOT NULL DEFAULT 0,
  `canjeado_en` datetime DEFAULT NULL,
  `impreso` tinyint(1) NOT NULL DEFAULT 0,
  `veces_impreso` int(11) DEFAULT 0,
  `impreso_en` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigos_postales`
--

CREATE TABLE `codigos_postales` (
  `codpais` varchar(20) NOT NULL,
  `creation_date` timestamp NULL DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idciudad` int(11) NOT NULL,
  `idprovincia` int(11) NOT NULL,
  `last_nick` varchar(50) DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conceptos_partidas`
--

CREATE TABLE `conceptos_partidas` (
  `codconcepto` varchar(6) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactos`
--

CREATE TABLE `contactos` (
  `aceptaprivacidad` tinyint(1) DEFAULT 0,
  `admitemarketing` tinyint(1) DEFAULT 0,
  `apellidos` varchar(150) DEFAULT NULL,
  `apartado` varchar(10) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `cifnif` varchar(30) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codagente` varchar(10) DEFAULT NULL,
  `codcliente` varchar(10) DEFAULT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `codproveedor` varchar(10) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `fechaalta` date DEFAULT NULL,
  `idcontacto` int(11) NOT NULL,
  `langcode` varchar(10) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `personafisica` tinyint(1) DEFAULT 1,
  `provincia` varchar(100) DEFAULT NULL,
  `telefono1` varchar(30) DEFAULT NULL,
  `telefono2` varchar(30) DEFAULT NULL,
  `tipoidfiscal` varchar(25) DEFAULT NULL,
  `verificado` tinyint(1) DEFAULT 0,
  `web` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cronjobs`
--

CREATE TABLE `cronjobs` (
  `date` timestamp NULL DEFAULT NULL,
  `done` tinyint(1) DEFAULT 0,
  `duration` double DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT 1,
  `failed` tinyint(1) DEFAULT 0,
  `id` int(11) NOT NULL,
  `jobname` varchar(50) NOT NULL,
  `pluginname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `codcuenta` varchar(10) NOT NULL,
  `codcuentaesp` varchar(6) DEFAULT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `debe` double DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `haber` double DEFAULT NULL,
  `idcuenta` int(11) NOT NULL,
  `parent_codcuenta` varchar(10) DEFAULT NULL,
  `parent_idcuenta` int(11) DEFAULT NULL,
  `saldo` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentasbanco`
--

CREATE TABLE `cuentasbanco` (
  `activa` tinyint(1) DEFAULT 1,
  `codcuenta` varchar(10) NOT NULL,
  `codsubcuenta` varchar(15) DEFAULT NULL,
  `codsubcuentagasto` varchar(15) DEFAULT NULL,
  `descripcion` varchar(100) NOT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `sufijosepa` varchar(3) DEFAULT NULL,
  `swift` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentasbcocli`
--

CREATE TABLE `cuentasbcocli` (
  `codcliente` varchar(10) NOT NULL,
  `codcuenta` varchar(10) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `fmandato` date DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `mandato` varchar(35) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT NULL,
  `swift` varchar(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentasbcopro`
--

CREATE TABLE `cuentasbcopro` (
  `codcuenta` varchar(10) NOT NULL,
  `codproveedor` varchar(10) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `swift` varchar(11) DEFAULT NULL,
  `principal` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentasesp`
--

CREATE TABLE `cuentasesp` (
  `codcuentaesp` varchar(6) NOT NULL,
  `descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `diarios`
--

CREATE TABLE `diarios` (
  `descripcion` varchar(100) NOT NULL,
  `iddiario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `divisas`
--

CREATE TABLE `divisas` (
  `coddivisa` varchar(3) NOT NULL,
  `codiso` varchar(5) DEFAULT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `simbolo` varchar(10) DEFAULT NULL,
  `tasaconv` double NOT NULL,
  `tasaconvcompra` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `doctransformations`
--

CREATE TABLE `doctransformations` (
  `cantidad` double DEFAULT NULL,
  `id` int(11) NOT NULL,
  `iddoc1` int(11) NOT NULL,
  `iddoc2` int(11) NOT NULL,
  `idlinea1` int(11) NOT NULL,
  `idlinea2` int(11) NOT NULL,
  `model1` varchar(30) NOT NULL,
  `model2` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios`
--

CREATE TABLE `ejercicios` (
  `codejercicio` varchar(4) NOT NULL,
  `estado` varchar(15) NOT NULL,
  `fechafin` date NOT NULL,
  `fechainicio` date NOT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `longsubcuenta` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emails_notifications`
--

CREATE TABLE `emails_notifications` (
  `body` text DEFAULT NULL,
  `creationdate` date DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `subject` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `emails_sent`
--

CREATE TABLE `emails_sent` (
  `addressee` varchar(100) DEFAULT NULL,
  `attachment` tinyint(1) DEFAULT 0,
  `body` text DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `email_from` varchar(100) DEFAULT NULL,
  `html` text DEFAULT NULL,
  `id` int(11) NOT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `opened` tinyint(1) DEFAULT 0,
  `subject` varchar(300) DEFAULT NULL,
  `uuid` varchar(13) DEFAULT NULL,
  `verificode` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `administrador` varchar(100) DEFAULT NULL,
  `apartado` varchar(10) DEFAULT NULL,
  `cifnif` varchar(30) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `fechaalta` date DEFAULT NULL,
  `idempresa` int(11) NOT NULL,
  `idlogo` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `nombrecorto` varchar(32) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `personafisica` tinyint(1) DEFAULT 1,
  `provincia` varchar(100) DEFAULT NULL,
  `regimeniva` varchar(20) DEFAULT NULL,
  `telefono1` varchar(30) DEFAULT NULL,
  `telefono2` varchar(30) DEFAULT NULL,
  `tipoidfiscal` varchar(25) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_documentos`
--

CREATE TABLE `estados_documentos` (
  `activo` tinyint(1) DEFAULT 1,
  `actualizastock` int(11) DEFAULT NULL,
  `bloquear` tinyint(1) DEFAULT NULL,
  `color` varchar(15) DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `generadoc` varchar(30) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `idestado` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `predeterminado` tinyint(1) DEFAULT NULL,
  `tipodoc` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fabricantes`
--

CREATE TABLE `fabricantes` (
  `codfabricante` varchar(8) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `numproductos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturascli`
--

CREATE TABLE `facturascli` (
  `apartado` varchar(10) DEFAULT NULL,
  `cifnif` varchar(30) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codagente` varchar(10) DEFAULT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `codcliente` varchar(10) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codigoenv` varchar(200) DEFAULT NULL,
  `codigorect` varchar(20) DEFAULT NULL,
  `codpago` varchar(10) NOT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `codtrans` varchar(8) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fechadevengo` date DEFAULT NULL,
  `femail` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idasiento` int(11) DEFAULT NULL,
  `idcontactoenv` int(11) DEFAULT NULL,
  `idcontactofact` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `idfactura` int(11) NOT NULL,
  `idfacturarect` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `nombrecliente` varchar(100) NOT NULL,
  `numdocs` int(11) DEFAULT 0,
  `numero` varchar(12) NOT NULL,
  `numero2` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `pagada` tinyint(1) DEFAULT 0,
  `provincia` varchar(100) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalbeneficio` double DEFAULT NULL,
  `totalcoste` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0,
  `vencida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturasprov`
--

CREATE TABLE `facturasprov` (
  `cifnif` varchar(30) NOT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codigorect` varchar(20) DEFAULT NULL,
  `codpago` varchar(10) NOT NULL,
  `codproveedor` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fechadevengo` date DEFAULT NULL,
  `femail` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idasiento` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `idfactura` int(11) NOT NULL,
  `idfacturarect` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `numdocs` int(11) DEFAULT 0,
  `nombre` varchar(100) NOT NULL,
  `numero` varchar(12) NOT NULL,
  `numproveedor` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `pagada` tinyint(1) DEFAULT 0,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0,
  `vencida` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `familias`
--

CREATE TABLE `familias` (
  `codfamilia` varchar(8) NOT NULL,
  `codsubcuentacom` varchar(15) DEFAULT NULL,
  `codsubcuentairpfcom` varchar(15) DEFAULT NULL,
  `codsubcuentaven` varchar(15) DEFAULT NULL,
  `descripcion` varchar(100) NOT NULL,
  `madre` varchar(8) DEFAULT NULL,
  `numproductos` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formaspago`
--

CREATE TABLE `formaspago` (
  `activa` tinyint(1) DEFAULT 1,
  `codcuentabanco` varchar(10) DEFAULT NULL,
  `codpago` varchar(10) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `domiciliado` tinyint(1) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `imprimir` tinyint(1) DEFAULT 1,
  `pagado` tinyint(1) DEFAULT 0,
  `plazovencimiento` int(11) DEFAULT NULL,
  `tipovencimiento` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formatos_documentos`
--

CREATE TABLE `formatos_documentos` (
  `autoaplicar` tinyint(1) DEFAULT 1,
  `codserie` varchar(4) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idlogo` int(11) DEFAULT NULL,
  `nombre` varchar(30) DEFAULT NULL,
  `texto` text DEFAULT NULL,
  `tipodoc` varchar(30) DEFAULT NULL,
  `titulo` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gruposclientes`
--

CREATE TABLE `gruposclientes` (
  `codgrupo` varchar(6) NOT NULL,
  `codsubcuenta` varchar(15) DEFAULT NULL,
  `codtarifa` varchar(6) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `idsfiscales`
--

CREATE TABLE `idsfiscales` (
  `codeid` varchar(2) DEFAULT NULL,
  `tipoidfiscal` varchar(25) NOT NULL,
  `validar` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impuestos`
--

CREATE TABLE `impuestos` (
  `activo` tinyint(1) DEFAULT 1,
  `codimpuesto` varchar(10) NOT NULL,
  `codsubcuentarep` varchar(15) DEFAULT NULL,
  `codsubcuentarepre` varchar(15) DEFAULT NULL,
  `codsubcuentasop` varchar(15) DEFAULT NULL,
  `codsubcuentasopre` varchar(15) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `tipo` int(11) DEFAULT 1,
  `iva` double NOT NULL,
  `recargo` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impuestoszonas`
--

CREATE TABLE `impuestoszonas` (
  `codimpuesto` varchar(10) NOT NULL,
  `codimpuestosel` varchar(10) DEFAULT NULL,
  `codisopro` varchar(10) DEFAULT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `prioridad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineasalbaranescli`
--

CREATE TABLE `lineasalbaranescli` (
  `actualizastock` int(11) NOT NULL DEFAULT 0,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `coste` double DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idalbaran` int(11) NOT NULL,
  `idlinea` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `mostrar_cantidad` tinyint(1) DEFAULT 1,
  `mostrar_precio` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `salto_pagina` tinyint(1) DEFAULT 0,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineasalbaranesprov`
--

CREATE TABLE `lineasalbaranesprov` (
  `actualizastock` int(11) NOT NULL DEFAULT 0,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idalbaran` int(11) NOT NULL,
  `idlinea` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineasfacturascli`
--

CREATE TABLE `lineasfacturascli` (
  `actualizastock` int(11) NOT NULL DEFAULT -1,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `coste` double DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idfactura` int(11) NOT NULL,
  `idlinea` int(11) NOT NULL,
  `idlinearect` int(11) DEFAULT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `mostrar_cantidad` tinyint(1) DEFAULT 1,
  `mostrar_precio` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `salto_pagina` tinyint(1) DEFAULT 0,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineasfacturasprov`
--

CREATE TABLE `lineasfacturasprov` (
  `actualizastock` int(11) NOT NULL DEFAULT 1,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idfactura` int(11) NOT NULL,
  `idlinea` int(11) NOT NULL,
  `idlinearect` int(11) DEFAULT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineaspedidoscli`
--

CREATE TABLE `lineaspedidoscli` (
  `actualizastock` int(11) NOT NULL DEFAULT 0,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `coste` double DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idlinea` int(11) NOT NULL,
  `idpedido` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `mostrar_cantidad` tinyint(1) DEFAULT 1,
  `mostrar_precio` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `salto_pagina` tinyint(1) DEFAULT 0,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineaspedidosprov`
--

CREATE TABLE `lineaspedidosprov` (
  `actualizastock` int(11) NOT NULL DEFAULT 0,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idlinea` int(11) NOT NULL,
  `idpedido` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineaspresupuestoscli`
--

CREATE TABLE `lineaspresupuestoscli` (
  `actualizastock` int(11) NOT NULL DEFAULT 0,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `coste` double DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idlinea` int(11) NOT NULL,
  `idpresupuesto` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `mostrar_cantidad` tinyint(1) DEFAULT 1,
  `mostrar_precio` tinyint(1) DEFAULT 1,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `salto_pagina` tinyint(1) DEFAULT 0,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineaspresupuestosprov`
--

CREATE TABLE `lineaspresupuestosprov` (
  `actualizastock` int(11) NOT NULL DEFAULT 0,
  `cantidad` double NOT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `dtopor` double NOT NULL DEFAULT 0,
  `dtopor2` double NOT NULL DEFAULT 0,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `idlinea` int(11) NOT NULL,
  `idpresupuesto` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `irpf` double DEFAULT NULL,
  `iva` double NOT NULL,
  `orden` int(11) DEFAULT 0,
  `pvpsindto` double NOT NULL,
  `pvptotal` double NOT NULL,
  `pvpunitario` double NOT NULL,
  `recargo` double NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `servido` double NOT NULL DEFAULT 0,
  `suplido` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs`
--

CREATE TABLE `logs` (
  `channel` varchar(40) DEFAULT NULL,
  `context` text DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idcontacto` int(11) DEFAULT NULL,
  `ip` varchar(40) DEFAULT NULL,
  `level` varchar(15) NOT NULL,
  `message` text NOT NULL,
  `model` varchar(30) DEFAULT NULL,
  `modelcode` varchar(40) DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `uri` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pages`
--

CREATE TABLE `pages` (
  `icon` varchar(50) DEFAULT NULL,
  `menu` varchar(20) DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `ordernum` int(11) NOT NULL DEFAULT 100,
  `showonmenu` tinyint(1) NOT NULL DEFAULT 1,
  `submenu` varchar(20) DEFAULT NULL,
  `title` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pages_filters`
--

CREATE TABLE `pages_filters` (
  `description` varchar(50) NOT NULL,
  `filters` text DEFAULT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `nick` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pages_options`
--

CREATE TABLE `pages_options` (
  `columns` text DEFAULT NULL,
  `id` int(11) NOT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `modals` text DEFAULT NULL,
  `name` varchar(40) NOT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `rows` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagoscli`
--

CREATE TABLE `pagoscli` (
  `codpago` varchar(10) DEFAULT NULL,
  `customid` varchar(30) DEFAULT NULL,
  `customstatus` varchar(30) DEFAULT NULL,
  `fecha` date NOT NULL,
  `gastos` double DEFAULT 0,
  `hora` time DEFAULT NULL,
  `idasiento` int(11) DEFAULT NULL,
  `idpago` int(11) NOT NULL,
  `idrecibo` int(11) NOT NULL,
  `importe` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagosprov`
--

CREATE TABLE `pagosprov` (
  `codpago` varchar(10) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time DEFAULT NULL,
  `idasiento` int(11) DEFAULT NULL,
  `idpago` int(11) NOT NULL,
  `idrecibo` int(11) NOT NULL,
  `importe` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paises`
--

CREATE TABLE `paises` (
  `alias` text DEFAULT NULL,
  `codiso` varchar(2) DEFAULT NULL,
  `codpais` varchar(20) NOT NULL,
  `creation_date` timestamp NULL DEFAULT NULL,
  `last_nick` varchar(50) DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `telephone_prefix` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidas`
--

CREATE TABLE `partidas` (
  `baseimponible` double NOT NULL DEFAULT 0,
  `cifnif` varchar(30) DEFAULT NULL,
  `codcontrapartida` varchar(15) DEFAULT NULL,
  `coddivisa` varchar(3) DEFAULT NULL,
  `codserie` varchar(4) DEFAULT NULL,
  `codsubcuenta` varchar(15) NOT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `debe` double NOT NULL DEFAULT 0,
  `documento` varchar(50) DEFAULT NULL,
  `factura` varchar(15) DEFAULT NULL,
  `haber` double NOT NULL DEFAULT 0,
  `idasiento` int(11) NOT NULL,
  `idcontrapartida` int(11) DEFAULT NULL,
  `idpartida` int(11) NOT NULL,
  `idsubcuenta` int(11) NOT NULL,
  `iva` double DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `punteada` tinyint(1) NOT NULL DEFAULT 0,
  `recargo` double NOT NULL DEFAULT 0,
  `saldo` double NOT NULL DEFAULT 0,
  `tasaconv` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidoscli`
--

CREATE TABLE `pedidoscli` (
  `apartado` varchar(10) DEFAULT NULL,
  `cifnif` varchar(30) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codagente` varchar(10) DEFAULT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `codcliente` varchar(10) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codigoenv` varchar(200) DEFAULT NULL,
  `codpago` varchar(10) NOT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `codtrans` varchar(8) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `femail` date DEFAULT NULL,
  `finoferta` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idcontactoenv` int(11) DEFAULT NULL,
  `idcontactofact` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `idpedido` int(11) NOT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `nombrecliente` varchar(100) DEFAULT NULL,
  `numdocs` int(11) DEFAULT 0,
  `numero` varchar(12) NOT NULL,
  `numero2` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalbeneficio` double DEFAULT NULL,
  `totalcoste` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidosprov`
--

CREATE TABLE `pedidosprov` (
  `cifnif` varchar(30) NOT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codpago` varchar(10) NOT NULL,
  `codproveedor` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `femail` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `idpedido` int(11) NOT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `numdocs` int(11) DEFAULT 0,
  `numero` varchar(12) NOT NULL,
  `numproveedor` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestoscli`
--

CREATE TABLE `presupuestoscli` (
  `apartado` varchar(10) DEFAULT NULL,
  `cifnif` varchar(30) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `codagente` varchar(10) DEFAULT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `codcliente` varchar(10) DEFAULT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `codigoenv` varchar(200) DEFAULT NULL,
  `codpago` varchar(10) NOT NULL,
  `codpais` varchar(20) DEFAULT NULL,
  `codpostal` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `codtrans` varchar(8) DEFAULT NULL,
  `direccion` varchar(100) DEFAULT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `femail` date DEFAULT NULL,
  `finoferta` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idcontactoenv` int(11) DEFAULT NULL,
  `idcontactofact` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `idpresupuesto` int(11) NOT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `nombrecliente` varchar(100) DEFAULT NULL,
  `numdocs` int(11) DEFAULT 0,
  `numero` varchar(12) NOT NULL,
  `numero2` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totalbeneficio` double DEFAULT NULL,
  `totalcoste` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `presupuestosprov`
--

CREATE TABLE `presupuestosprov` (
  `cifnif` varchar(30) NOT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `coddivisa` varchar(3) NOT NULL,
  `codpago` varchar(10) NOT NULL,
  `codproveedor` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `dtopor1` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `editable` tinyint(1) DEFAULT NULL,
  `fecha` date NOT NULL,
  `femail` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idestado` int(11) DEFAULT NULL,
  `idpresupuesto` int(11) NOT NULL,
  `irpf` double DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netosindto` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `numdocs` int(11) DEFAULT 0,
  `nombre` varchar(100) DEFAULT NULL,
  `numero` varchar(12) NOT NULL,
  `numproveedor` varchar(50) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `operacion` varchar(20) DEFAULT NULL,
  `tasaconv` double DEFAULT NULL,
  `total` double DEFAULT NULL,
  `totaleuros` double DEFAULT NULL,
  `totalirpf` double DEFAULT NULL,
  `totaliva` double DEFAULT NULL,
  `totalrecargo` double DEFAULT NULL,
  `totalsuplidos` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `actualizado` timestamp NULL DEFAULT NULL,
  `bloqueado` tinyint(1) DEFAULT 0,
  `codfabricante` varchar(8) DEFAULT NULL,
  `codfamilia` varchar(8) DEFAULT NULL,
  `codimpuesto` varchar(10) DEFAULT NULL,
  `codsubcuentacom` varchar(15) DEFAULT NULL,
  `codsubcuentairpfcom` varchar(15) DEFAULT NULL,
  `codsubcuentaven` varchar(15) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `excepcioniva` varchar(20) DEFAULT NULL,
  `fechaalta` date DEFAULT NULL,
  `idproducto` int(11) NOT NULL,
  `nostock` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `precio` double DEFAULT 0,
  `publico` tinyint(1) DEFAULT 0,
  `referencia` varchar(30) NOT NULL,
  `secompra` tinyint(1) DEFAULT 1,
  `sevende` tinyint(1) DEFAULT 1,
  `stockfis` double DEFAULT 0,
  `tipo` varchar(20) DEFAULT NULL,
  `ventasinstock` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productosprov`
--

CREATE TABLE `productosprov` (
  `actualizado` timestamp NULL DEFAULT NULL,
  `coddivisa` varchar(3) DEFAULT NULL,
  `codproveedor` varchar(10) NOT NULL,
  `dtopor` double DEFAULT NULL,
  `dtopor2` double DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idproducto` int(11) DEFAULT NULL,
  `neto` double DEFAULT NULL,
  `netoeuros` double DEFAULT 0,
  `precio` double DEFAULT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `refproveedor` varchar(30) NOT NULL,
  `stock` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_imagenes`
--

CREATE TABLE `productos_imagenes` (
  `id` int(11) NOT NULL,
  `idfile` int(11) NOT NULL,
  `idproducto` int(11) NOT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `acreedor` tinyint(1) DEFAULT 0,
  `cifnif` varchar(30) NOT NULL,
  `codcliente` varchar(10) DEFAULT NULL,
  `codimpuestoportes` varchar(10) DEFAULT NULL,
  `codpago` varchar(10) DEFAULT NULL,
  `codproveedor` varchar(10) NOT NULL,
  `codretencion` varchar(10) DEFAULT NULL,
  `codserie` varchar(4) DEFAULT NULL,
  `codsubcuenta` varchar(15) DEFAULT NULL,
  `debaja` tinyint(1) DEFAULT 0,
  `email` varchar(100) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `fechaalta` date DEFAULT NULL,
  `fechabaja` date DEFAULT NULL,
  `idcontacto` int(11) DEFAULT NULL,
  `langcode` varchar(10) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `personafisica` tinyint(1) DEFAULT 1,
  `razonsocial` varchar(100) DEFAULT NULL,
  `regimeniva` varchar(20) DEFAULT NULL,
  `telefono1` varchar(30) DEFAULT NULL,
  `telefono2` varchar(30) DEFAULT NULL,
  `tipoidfiscal` varchar(25) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `provincias`
--

CREATE TABLE `provincias` (
  `alias` text DEFAULT NULL,
  `codeid` varchar(2) DEFAULT NULL,
  `codisoprov` varchar(10) DEFAULT NULL,
  `codpais` varchar(20) NOT NULL,
  `creation_date` timestamp NULL DEFAULT NULL,
  `idprovincia` int(11) NOT NULL,
  `last_nick` varchar(50) DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `provincia` varchar(100) NOT NULL,
  `telephone_prefix` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_interes_ciudades`
--

CREATE TABLE `puntos_interes_ciudades` (
  `alias` text DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT NULL,
  `id` int(11) NOT NULL,
  `idciudad` int(11) NOT NULL,
  `last_nick` varchar(50) DEFAULT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `nick` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibospagoscli`
--

CREATE TABLE `recibospagoscli` (
  `codcliente` varchar(10) NOT NULL,
  `coddivisa` varchar(3) DEFAULT NULL,
  `codigofactura` varchar(20) DEFAULT NULL,
  `codpago` varchar(10) DEFAULT NULL,
  `fecha` date NOT NULL,
  `fechapago` date DEFAULT NULL,
  `gastos` double DEFAULT 0,
  `idempresa` int(11) NOT NULL,
  `idfactura` int(11) DEFAULT NULL,
  `idrecibo` int(11) NOT NULL,
  `importe` double DEFAULT 0,
  `liquidado` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `pagado` tinyint(1) DEFAULT 0,
  `vencido` tinyint(1) DEFAULT 0,
  `vencimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recibospagosprov`
--

CREATE TABLE `recibospagosprov` (
  `coddivisa` varchar(3) DEFAULT NULL,
  `codigofactura` varchar(20) DEFAULT NULL,
  `codpago` varchar(10) DEFAULT NULL,
  `codproveedor` varchar(10) NOT NULL,
  `fecha` date NOT NULL,
  `fechapago` date DEFAULT NULL,
  `idempresa` int(11) NOT NULL,
  `idfactura` int(11) DEFAULT NULL,
  `idrecibo` int(11) NOT NULL,
  `importe` double DEFAULT 0,
  `liquidado` double DEFAULT 0,
  `nick` varchar(50) DEFAULT NULL,
  `numero` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `pagado` tinyint(1) DEFAULT 0,
  `vencido` tinyint(1) DEFAULT 0,
  `vencimiento` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `regularizacionimpuestos`
--

CREATE TABLE `regularizacionimpuestos` (
  `bloquear` tinyint(1) DEFAULT 1,
  `codejercicio` varchar(4) NOT NULL,
  `codsubcuentaacr` varchar(15) DEFAULT NULL,
  `codsubcuentadeu` varchar(15) DEFAULT NULL,
  `fechaasiento` date DEFAULT NULL,
  `fechafin` date NOT NULL,
  `fechainicio` date NOT NULL,
  `idasiento` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `idregiva` int(11) NOT NULL,
  `idsubcuentaacr` int(11) DEFAULT NULL,
  `idsubcuentadeu` int(11) DEFAULT NULL,
  `periodo` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `retenciones`
--

CREATE TABLE `retenciones` (
  `activa` tinyint(1) DEFAULT 1,
  `codretencion` varchar(10) NOT NULL,
  `codsubcuentaret` varchar(15) DEFAULT NULL,
  `codsubcuentaacr` varchar(15) DEFAULT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `porcentaje` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `codrole` varchar(20) NOT NULL,
  `descripcion` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_access`
--

CREATE TABLE `roles_access` (
  `allowdelete` tinyint(1) NOT NULL DEFAULT 1,
  `allowexport` tinyint(1) NOT NULL DEFAULT 1,
  `allowimport` tinyint(1) NOT NULL DEFAULT 1,
  `allowupdate` tinyint(1) NOT NULL DEFAULT 1,
  `codrole` varchar(20) NOT NULL,
  `id` int(11) NOT NULL,
  `onlyownerdata` tinyint(1) NOT NULL DEFAULT 0,
  `pagename` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles_users`
--

CREATE TABLE `roles_users` (
  `codrole` varchar(20) NOT NULL,
  `id` int(11) NOT NULL,
  `nick` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secuencias_documentos`
--

CREATE TABLE `secuencias_documentos` (
  `codejercicio` varchar(4) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `idempresa` int(11) NOT NULL,
  `idsecuencia` int(11) NOT NULL,
  `inicio` int(11) DEFAULT NULL,
  `longnumero` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `patron` varchar(50) NOT NULL,
  `tipodoc` varchar(30) NOT NULL,
  `usarhuecos` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `series`
--

CREATE TABLE `series` (
  `canal` int(11) DEFAULT NULL,
  `codserie` varchar(4) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `iddiario` int(11) DEFAULT NULL,
  `siniva` tinyint(1) NOT NULL DEFAULT 0,
  `tipo` varchar(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `name` varchar(50) NOT NULL,
  `properties` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stocks`
--

CREATE TABLE `stocks` (
  `cantidad` double DEFAULT 0,
  `codalmacen` varchar(4) NOT NULL,
  `disponible` double DEFAULT 0,
  `idproducto` int(11) NOT NULL,
  `idstock` int(11) NOT NULL,
  `pterecibir` double DEFAULT 0,
  `referencia` varchar(30) NOT NULL,
  `reservada` double DEFAULT 0,
  `stockmax` double DEFAULT 0,
  `stockmin` double DEFAULT 0,
  `ubicacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcuentas`
--

CREATE TABLE `subcuentas` (
  `codcuenta` varchar(10) NOT NULL,
  `codcuentaesp` varchar(6) DEFAULT NULL,
  `codejercicio` varchar(4) NOT NULL,
  `codsubcuenta` varchar(15) NOT NULL,
  `debe` double NOT NULL DEFAULT 0,
  `descripcion` varchar(255) NOT NULL,
  `haber` double NOT NULL DEFAULT 0,
  `idcuenta` int(11) NOT NULL,
  `idsubcuenta` int(11) NOT NULL,
  `saldo` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarifas`
--

CREATE TABLE `tarifas` (
  `aplicar` varchar(12) DEFAULT NULL,
  `codtarifa` varchar(6) NOT NULL,
  `maxpvp` tinyint(1) DEFAULT 0,
  `mincoste` tinyint(1) DEFAULT 0,
  `nombre` varchar(50) NOT NULL,
  `valorx` double DEFAULT NULL,
  `valory` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `codagente` varchar(10) DEFAULT NULL,
  `codalmacen` varchar(4) DEFAULT NULL,
  `creationdate` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `homepage` varchar(30) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL,
  `langcode` varchar(10) DEFAULT NULL,
  `lastactivity` timestamp NULL DEFAULT NULL,
  `lastbrowser` varchar(200) DEFAULT NULL,
  `lastip` varchar(40) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `logkey` varchar(100) DEFAULT NULL,
  `nick` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variantes`
--

CREATE TABLE `variantes` (
  `codbarras` varchar(20) DEFAULT NULL,
  `coste` double DEFAULT 0,
  `idatributovalor1` int(11) DEFAULT NULL,
  `idatributovalor2` int(11) DEFAULT NULL,
  `idatributovalor3` int(11) DEFAULT NULL,
  `idatributovalor4` int(11) DEFAULT NULL,
  `idproducto` int(11) NOT NULL,
  `idvariante` int(11) NOT NULL,
  `margen` double DEFAULT 0,
  `precio` double DEFAULT 0,
  `referencia` varchar(30) NOT NULL,
  `stockfis` double DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `work_events`
--

CREATE TABLE `work_events` (
  `creation_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `done` tinyint(1) DEFAULT 0,
  `done_date` timestamp NULL DEFAULT NULL,
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `nick` varchar(30) DEFAULT NULL,
  `params` text DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `workers` int(11) DEFAULT NULL,
  `worker_list` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agenciastrans`
--
ALTER TABLE `agenciastrans`
  ADD PRIMARY KEY (`codtrans`);

--
-- Indices de la tabla `agentes`
--
ALTER TABLE `agentes`
  ADD PRIMARY KEY (`codagente`),
  ADD KEY `ca_agentes_productos` (`idproducto`);

--
-- Indices de la tabla `albaranescli`
--
ALTER TABLE `albaranescli`
  ADD PRIMARY KEY (`idalbaran`),
  ADD UNIQUE KEY `uniq_codigo_albaranescli` (`codigo`,`idempresa`),
  ADD KEY `ca_albaranescli_codagente` (`codagente`),
  ADD KEY `ca_albaranescli_codalmacen` (`codalmacen`),
  ADD KEY `ca_albaranescli_codcliente` (`codcliente`),
  ADD KEY `ca_albaranescli_coddivisa` (`coddivisa`),
  ADD KEY `ca_albaranescli_codejercicio` (`codejercicio`),
  ADD KEY `ca_albaranescli_codpago` (`codpago`),
  ADD KEY `ca_albaranescli_codseries` (`codserie`),
  ADD KEY `ca_albaranescli_codtrans` (`codtrans`),
  ADD KEY `ca_albaranescli_idempresas` (`idempresa`),
  ADD KEY `ca_albaranescli_idestados` (`idestado`),
  ADD KEY `ca_albaranescli_nick` (`nick`);

--
-- Indices de la tabla `albaranesprov`
--
ALTER TABLE `albaranesprov`
  ADD PRIMARY KEY (`idalbaran`),
  ADD UNIQUE KEY `uniq_codigo_albaranesprov` (`codigo`,`idempresa`),
  ADD KEY `ca_albaranesprov_codalmacen` (`codalmacen`),
  ADD KEY `ca_albaranesprov_coddivisa` (`coddivisa`),
  ADD KEY `ca_albaranesprov_codejercicio` (`codejercicio`),
  ADD KEY `ca_albaranesprov_codpago` (`codpago`),
  ADD KEY `ca_albaranesprov_codproveedor` (`codproveedor`),
  ADD KEY `ca_albaranesprov_codserie` (`codserie`),
  ADD KEY `ca_albaranesprov_idempresa` (`idempresa`),
  ADD KEY `ca_albaranesprov_idestado` (`idestado`),
  ADD KEY `ca_albaranesprov_nick` (`nick`);

--
-- Indices de la tabla `almacenes`
--
ALTER TABLE `almacenes`
  ADD PRIMARY KEY (`codalmacen`),
  ADD KEY `ca_almacenes_empresas` (`idempresa`);

--
-- Indices de la tabla `api_access`
--
ALTER TABLE `api_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_api_access` (`idapikey`,`resource`);

--
-- Indices de la tabla `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `asientos`
--
ALTER TABLE `asientos`
  ADD PRIMARY KEY (`idasiento`),
  ADD KEY `ca_asientos_diarios` (`iddiario`),
  ADD KEY `ca_asientos_ejercicios` (`codejercicio`),
  ADD KEY `ca_asientos_empresas` (`idempresa`);

--
-- Indices de la tabla `atan_contactos`
--
ALTER TABLE `atan_contactos`
  ADD PRIMARY KEY (`id_contacto`);

--
-- Indices de la tabla `atributos`
--
ALTER TABLE `atributos`
  ADD PRIMARY KEY (`codatributo`);

--
-- Indices de la tabla `atributos_valores`
--
ALTER TABLE `atributos_valores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_values_atributos_valores` (`codatributo`,`valor`);

--
-- Indices de la tabla `attached_files`
--
ALTER TABLE `attached_files`
  ADD PRIMARY KEY (`idfile`);

--
-- Indices de la tabla `attached_files_rel`
--
ALTER TABLE `attached_files_rel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ca_attached_files_rel_files` (`idfile`),
  ADD KEY `ca_attached_files_rel_users` (`nick`);

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`idciudad`),
  ADD UNIQUE KEY `uniq_ciudad_idprovincia` (`ciudad`,`idprovincia`),
  ADD KEY `ca_ciudades_provincias` (`idprovincia`),
  ADD KEY `ca_ciudades_users_last_nick` (`last_nick`),
  ADD KEY `ca_ciudades_users_nick` (`nick`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`codcliente`),
  ADD KEY `ca_clientes_agentes` (`codagente`),
  ADD KEY `ca_clientes_formaspago` (`codpago`),
  ADD KEY `ca_clientes_grupos` (`codgrupo`),
  ADD KEY `ca_clientes_retenciones` (`codretencion`),
  ADD KEY `ca_clientes_series` (`codserie`),
  ADD KEY `ca_clientes_tarifas` (`codtarifa`);

--
-- Indices de la tabla `coci_cambios_log`
--
ALTER TABLE `coci_cambios_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comanda` (`comanda_id`),
  ADD KEY `idx_reserva` (`reserva_id`),
  ADD KEY `idx_usuario` (`email_usuario`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `coci_comandas`
--
ALTER TABLE `coci_comandas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_fecha_tipo` (`fecha`,`tipo_servicio`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_reserva_id` (`reserva_id`);

--
-- Indices de la tabla `coci_comanda_respaldos`
--
ALTER TABLE `coci_comanda_respaldos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comanda_id` (`comanda_id`);

--
-- Indices de la tabla `coci_detalles_orden`
--
ALTER TABLE `coci_detalles_orden`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orden_id` (`orden_id`);

--
-- Indices de la tabla `coci_impresiones_log`
--
ALTER TABLE `coci_impresiones_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comanda` (`comanda_id`),
  ADD KEY `idx_reserva` (`reserva_id`),
  ADD KEY `idx_usuario` (`email_usuario`),
  ADD KEY `idx_fecha` (`created_at`);

--
-- Indices de la tabla `coci_ordenes`
--
ALTER TABLE `coci_ordenes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `coci_productos`
--
ALTER TABLE `coci_productos`
  ADD PRIMARY KEY (`producto_id`);

--
-- Indices de la tabla `coci_reservas`
--
ALTER TABLE `coci_reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company` (`company_id`),
  ADD KEY `idx_fechas` (`fecha_desde`,`fecha_hasta`);

--
-- Indices de la tabla `coci_vouchers_genericos`
--
ALTER TABLE `coci_vouchers_genericos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_vg_codigo` (`codigo`),
  ADD KEY `idx_vg_comanda` (`comanda_id`);

--
-- Indices de la tabla `coci_voucher_clientes`
--
ALTER TABLE `coci_voucher_clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_vc_codigo` (`codigo`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_comanda_id` (`comanda_id`),
  ADD KEY `idx_rut` (`rut`);

--
-- Indices de la tabla `codigos_postales`
--
ALTER TABLE `codigos_postales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_codigos_postales` (`codpais`,`idprovincia`,`idciudad`,`number`),
  ADD KEY `ca_codigos_postales_users_last_nick` (`last_nick`),
  ADD KEY `ca_codigos_postales_users_nick` (`nick`),
  ADD KEY `ca_codigos_postales_provincias` (`idprovincia`),
  ADD KEY `ca_codigos_postales_ciudades` (`idciudad`);

--
-- Indices de la tabla `conceptos_partidas`
--
ALTER TABLE `conceptos_partidas`
  ADD PRIMARY KEY (`codconcepto`);

--
-- Indices de la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD PRIMARY KEY (`idcontacto`),
  ADD KEY `ca_contactos_agentes` (`codagente`),
  ADD KEY `ca_contactos_clientes` (`codcliente`),
  ADD KEY `ca_contactos_proveedores` (`codproveedor`);

--
-- Indices de la tabla `cronjobs`
--
ALTER TABLE `cronjobs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`idcuenta`),
  ADD UNIQUE KEY `uniq_codcuenta` (`codcuenta`,`codejercicio`),
  ADD KEY `ca_cuentas_cuentasesp` (`codcuentaesp`),
  ADD KEY `ca_cuentas_ejercicios` (`codejercicio`),
  ADD KEY `ca_cuentas_parent` (`parent_idcuenta`);

--
-- Indices de la tabla `cuentasbanco`
--
ALTER TABLE `cuentasbanco`
  ADD PRIMARY KEY (`codcuenta`),
  ADD KEY `ca_cuentasbanco_empresas` (`idempresa`);

--
-- Indices de la tabla `cuentasbcocli`
--
ALTER TABLE `cuentasbcocli`
  ADD PRIMARY KEY (`codcuenta`),
  ADD UNIQUE KEY `unique_cuentasbcocli_mandato` (`mandato`),
  ADD KEY `ca_cuentasbcocli_clientes` (`codcliente`);

--
-- Indices de la tabla `cuentasbcopro`
--
ALTER TABLE `cuentasbcopro`
  ADD PRIMARY KEY (`codcuenta`),
  ADD KEY `ca_cuentasbcopro_proveedores` (`codproveedor`);

--
-- Indices de la tabla `cuentasesp`
--
ALTER TABLE `cuentasesp`
  ADD PRIMARY KEY (`codcuentaesp`);

--
-- Indices de la tabla `diarios`
--
ALTER TABLE `diarios`
  ADD PRIMARY KEY (`iddiario`);

--
-- Indices de la tabla `divisas`
--
ALTER TABLE `divisas`
  ADD PRIMARY KEY (`coddivisa`);

--
-- Indices de la tabla `doctransformations`
--
ALTER TABLE `doctransformations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD PRIMARY KEY (`codejercicio`),
  ADD KEY `ca_ejercicios_empresas` (`idempresa`);

--
-- Indices de la tabla `emails_notifications`
--
ALTER TABLE `emails_notifications`
  ADD PRIMARY KEY (`name`);

--
-- Indices de la tabla `emails_sent`
--
ALTER TABLE `emails_sent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emails_sent_user` (`nick`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`idempresa`),
  ADD KEY `ca_empresas_attached_files` (`idlogo`);

--
-- Indices de la tabla `estados_documentos`
--
ALTER TABLE `estados_documentos`
  ADD PRIMARY KEY (`idestado`);

--
-- Indices de la tabla `fabricantes`
--
ALTER TABLE `fabricantes`
  ADD PRIMARY KEY (`codfabricante`);

--
-- Indices de la tabla `facturascli`
--
ALTER TABLE `facturascli`
  ADD PRIMARY KEY (`idfactura`),
  ADD UNIQUE KEY `uniq_codigo_facturascli` (`codigo`,`idempresa`),
  ADD UNIQUE KEY `uniq_number_facturascli` (`codejercicio`,`codserie`,`idempresa`,`numero`),
  ADD KEY `ca_facturascli_codagente` (`codagente`),
  ADD KEY `ca_facturascli_codalmacen` (`codalmacen`),
  ADD KEY `ca_facturascli_codcliente` (`codcliente`),
  ADD KEY `ca_facturascli_coddivisa` (`coddivisa`),
  ADD KEY `ca_facturascli_codpago` (`codpago`),
  ADD KEY `ca_facturascli_codserie` (`codserie`),
  ADD KEY `ca_facturascli_codtrans` (`codtrans`),
  ADD KEY `ca_facturascli_idasiento` (`idasiento`),
  ADD KEY `ca_facturascli_idempresa` (`idempresa`),
  ADD KEY `ca_facturascli_idestado` (`idestado`),
  ADD KEY `ca_facturascli_idfacturarect` (`idfacturarect`),
  ADD KEY `ca_facturascli_nick` (`nick`);

--
-- Indices de la tabla `facturasprov`
--
ALTER TABLE `facturasprov`
  ADD PRIMARY KEY (`idfactura`),
  ADD UNIQUE KEY `uniq_codigo_facturasprov` (`codigo`,`idempresa`),
  ADD UNIQUE KEY `uniq_numero_facturasprov` (`codejercicio`,`codserie`,`idempresa`,`numero`),
  ADD KEY `ca_facturasprov_codalmacen` (`codalmacen`),
  ADD KEY `ca_facturasprov_coddivisa` (`coddivisa`),
  ADD KEY `ca_facturasprov_codpago` (`codpago`),
  ADD KEY `ca_facturasprov_codproveedor` (`codproveedor`),
  ADD KEY `ca_facturasprov_codserie` (`codserie`),
  ADD KEY `ca_facturasprov_idasiento` (`idasiento`),
  ADD KEY `ca_facturasprov_idempresa` (`idempresa`),
  ADD KEY `ca_facturasprov_idestado` (`idestado`),
  ADD KEY `ca_facturasprov_idfacturarect` (`idfacturarect`),
  ADD KEY `ca_facturasprov_nick` (`nick`);

--
-- Indices de la tabla `familias`
--
ALTER TABLE `familias`
  ADD PRIMARY KEY (`codfamilia`);

--
-- Indices de la tabla `formaspago`
--
ALTER TABLE `formaspago`
  ADD PRIMARY KEY (`codpago`),
  ADD KEY `ca_formaspago_cuentasbanco` (`codcuentabanco`),
  ADD KEY `ca_formaspago_empresas` (`idempresa`);

--
-- Indices de la tabla `formatos_documentos`
--
ALTER TABLE `formatos_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ca_formatos_documentos_series` (`codserie`),
  ADD KEY `ca_formatos_documentos_empresas` (`idempresa`),
  ADD KEY `ca_formatos_documentos_attached_files` (`idlogo`);

--
-- Indices de la tabla `gruposclientes`
--
ALTER TABLE `gruposclientes`
  ADD PRIMARY KEY (`codgrupo`),
  ADD KEY `ca_gruposclientes_tarifas` (`codtarifa`);

--
-- Indices de la tabla `idsfiscales`
--
ALTER TABLE `idsfiscales`
  ADD PRIMARY KEY (`tipoidfiscal`),
  ADD UNIQUE KEY `uniq_idsfiscales_tipoidfiscal_codeid` (`tipoidfiscal`,`codeid`);

--
-- Indices de la tabla `impuestos`
--
ALTER TABLE `impuestos`
  ADD PRIMARY KEY (`codimpuesto`);

--
-- Indices de la tabla `impuestoszonas`
--
ALTER TABLE `impuestoszonas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ca_impuestoszonas_impuestos` (`codimpuesto`),
  ADD KEY `ca_impuestoszonas_impuestos2` (`codimpuestosel`);

--
-- Indices de la tabla `lineasalbaranescli`
--
ALTER TABLE `lineasalbaranescli`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineasalbaranescli_albaranescli2` (`idalbaran`),
  ADD KEY `ca_lineasalbaranescli_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineasalbaranescli_productos` (`idproducto`);

--
-- Indices de la tabla `lineasalbaranesprov`
--
ALTER TABLE `lineasalbaranesprov`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineasalbaranesprov_albaranesprov2` (`idalbaran`),
  ADD KEY `ca_lineasalbaranesprov_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineasalbaranesprov_productos` (`idproducto`);

--
-- Indices de la tabla `lineasfacturascli`
--
ALTER TABLE `lineasfacturascli`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineasfacturascli_facturascli2` (`idfactura`),
  ADD KEY `ca_lineasfacturascli_linearect2` (`idlinearect`),
  ADD KEY `ca_lineasfacturascli_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineasfacturascli_productos` (`idproducto`);

--
-- Indices de la tabla `lineasfacturasprov`
--
ALTER TABLE `lineasfacturasprov`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineasfacturasprov_facturasprov2` (`idfactura`),
  ADD KEY `ca_lineasfacturasprov_linearect2` (`idlinearect`),
  ADD KEY `ca_lineasfacturasprov_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineasfacturasprov_productos` (`idproducto`);

--
-- Indices de la tabla `lineaspedidoscli`
--
ALTER TABLE `lineaspedidoscli`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineaspedidoscli_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineaspedidoscli_productos` (`idproducto`),
  ADD KEY `ca_lineaspedidoscli_pedidoscli` (`idpedido`);

--
-- Indices de la tabla `lineaspedidosprov`
--
ALTER TABLE `lineaspedidosprov`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineaspedidosprov_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineaspedidosprov_pedidosprov` (`idpedido`),
  ADD KEY `ca_lineaspedidosprov_productos` (`idproducto`);

--
-- Indices de la tabla `lineaspresupuestoscli`
--
ALTER TABLE `lineaspresupuestoscli`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineaspresupuestoscli_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineaspresupuestoscli_presupuestoscli` (`idpresupuesto`),
  ADD KEY `ca_lineaspresupuestoscli_productos` (`idproducto`);

--
-- Indices de la tabla `lineaspresupuestosprov`
--
ALTER TABLE `lineaspresupuestosprov`
  ADD PRIMARY KEY (`idlinea`),
  ADD KEY `ca_lineaspresupuestosprov_impuestos` (`codimpuesto`),
  ADD KEY `ca_lineaspresupuestosprov_presupuestosprov` (`idpresupuesto`),
  ADD KEY `ca_lineaspresupuestosprov_productos` (`idproducto`);

--
-- Indices de la tabla `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`name`);

--
-- Indices de la tabla `pages_filters`
--
ALTER TABLE `pages_filters`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ca_pagesfilters_users` (`nick`);

--
-- Indices de la tabla `pages_options`
--
ALTER TABLE `pages_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_pages_options` (`name`,`nick`),
  ADD KEY `ca_pagesoptions_users` (`nick`);

--
-- Indices de la tabla `pagoscli`
--
ALTER TABLE `pagoscli`
  ADD PRIMARY KEY (`idpago`),
  ADD KEY `ca_pagoscli_asiento` (`idasiento`),
  ADD KEY `ca_pagoscli_recibo` (`idrecibo`);

--
-- Indices de la tabla `pagosprov`
--
ALTER TABLE `pagosprov`
  ADD PRIMARY KEY (`idpago`),
  ADD KEY `ca_pagosprov_asiento` (`idasiento`),
  ADD KEY `ca_pagosprov_recibo` (`idrecibo`);

--
-- Indices de la tabla `paises`
--
ALTER TABLE `paises`
  ADD PRIMARY KEY (`codpais`),
  ADD UNIQUE KEY `uniq_codiso_paises` (`codiso`),
  ADD KEY `ca_paises_users_last_nick` (`last_nick`),
  ADD KEY `ca_paises_users_nick` (`nick`);

--
-- Indices de la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD PRIMARY KEY (`idpartida`),
  ADD KEY `ca_partidas_asientos` (`idasiento`),
  ADD KEY `ca_partidas_subcuentas` (`idsubcuenta`),
  ADD KEY `ca_partidas_subcuentas2` (`idcontrapartida`);

--
-- Indices de la tabla `pedidoscli`
--
ALTER TABLE `pedidoscli`
  ADD PRIMARY KEY (`idpedido`),
  ADD UNIQUE KEY `uniq_codigo_pedidoscli` (`codigo`,`idempresa`),
  ADD KEY `ca_pedidoscli_codagente` (`codagente`),
  ADD KEY `ca_pedidoscli_codalmacen` (`codalmacen`),
  ADD KEY `ca_pedidoscli_codcliente` (`codcliente`),
  ADD KEY `ca_pedidoscli_coddivisa` (`coddivisa`),
  ADD KEY `ca_pedidoscli_codejercicio` (`codejercicio`),
  ADD KEY `ca_pedidoscli_codpago` (`codpago`),
  ADD KEY `ca_pedidoscli_codserie` (`codserie`),
  ADD KEY `ca_pedidoscli_codtrans` (`codtrans`),
  ADD KEY `ca_pedidoscli_idempresa` (`idempresa`),
  ADD KEY `ca_pedidoscli_idestado` (`idestado`),
  ADD KEY `ca_pedidoscli_nick` (`nick`);

--
-- Indices de la tabla `pedidosprov`
--
ALTER TABLE `pedidosprov`
  ADD PRIMARY KEY (`idpedido`),
  ADD UNIQUE KEY `uniq_codigo_pedidosprov` (`codigo`,`idempresa`),
  ADD KEY `ca_pedidosprov_codalmacen` (`codalmacen`),
  ADD KEY `ca_pedidosprov_coddivisa` (`coddivisa`),
  ADD KEY `ca_pedidosprov_codejercicio` (`codejercicio`),
  ADD KEY `ca_pedidosprov_codpagos` (`codpago`),
  ADD KEY `ca_pedidosprov_codproveedor` (`codproveedor`),
  ADD KEY `ca_pedidosprov_codserie` (`codserie`),
  ADD KEY `ca_pedidosprov_idempresa` (`idempresa`),
  ADD KEY `ca_pedidosprov_idestado` (`idestado`),
  ADD KEY `ca_pedidosprov_nick` (`nick`);

--
-- Indices de la tabla `presupuestoscli`
--
ALTER TABLE `presupuestoscli`
  ADD PRIMARY KEY (`idpresupuesto`),
  ADD UNIQUE KEY `uniq_codigo_presupuestoscli` (`codigo`,`idempresa`),
  ADD KEY `ca_presupuestoscli_codagente` (`codagente`),
  ADD KEY `ca_presupuestoscli_codalmacen` (`codalmacen`),
  ADD KEY `ca_presupuestoscli_codcliente` (`codcliente`),
  ADD KEY `ca_presupuestoscli_coddivisa` (`coddivisa`),
  ADD KEY `ca_presupuestoscli_codejercicio` (`codejercicio`),
  ADD KEY `ca_presupuestoscli_codpago` (`codpago`),
  ADD KEY `ca_presupuestoscli_codserie` (`codserie`),
  ADD KEY `ca_presupuestoscli_codtrans` (`codtrans`),
  ADD KEY `ca_presupuestoscli_idempresa` (`idempresa`),
  ADD KEY `ca_presupuestoscli_idestado` (`idestado`),
  ADD KEY `ca_presupuestoscli_nick` (`nick`);

--
-- Indices de la tabla `presupuestosprov`
--
ALTER TABLE `presupuestosprov`
  ADD PRIMARY KEY (`idpresupuesto`),
  ADD UNIQUE KEY `uniq_codigo_presupuestosprov` (`codigo`,`idempresa`),
  ADD KEY `ca_presupuestosprov_codalmacen` (`codalmacen`),
  ADD KEY `ca_presupuestosprov_coddivisa` (`coddivisa`),
  ADD KEY `ca_presupuestosprov_codejercicio` (`codejercicio`),
  ADD KEY `ca_presupuestosprov_idempresa` (`idempresa`),
  ADD KEY `ca_presupuestosprov_idestado` (`idestado`),
  ADD KEY `ca_presupuestosprov_codproveedor` (`codproveedor`),
  ADD KEY `ca_presupuestosprov_codserie` (`codserie`),
  ADD KEY `ca_presupuestosprov_nick` (`nick`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idproducto`),
  ADD UNIQUE KEY `uniq_productos_referencia` (`referencia`),
  ADD KEY `ca_productos_fabricantes` (`codfabricante`),
  ADD KEY `ca_productos_familias` (`codfamilia`),
  ADD KEY `ca_productos_impuestos` (`codimpuesto`);

--
-- Indices de la tabla `productosprov`
--
ALTER TABLE `productosprov`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_productosprov` (`codproveedor`,`refproveedor`,`referencia`,`coddivisa`),
  ADD KEY `ca_productosprov_divisas` (`coddivisa`),
  ADD KEY `ca_productosprov_productos` (`idproducto`);

--
-- Indices de la tabla `productos_imagenes`
--
ALTER TABLE `productos_imagenes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ca_productos_imagenes_productos` (`idproducto`),
  ADD KEY `ca_productos_imagenes_variantes` (`referencia`),
  ADD KEY `ca_productos_imagenes_attachedfiles` (`idfile`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`codproveedor`),
  ADD KEY `ca_proveedores_formaspago` (`codpago`),
  ADD KEY `ca_proveedores_retenciones` (`codretencion`),
  ADD KEY `ca_proveedores_series` (`codserie`);

--
-- Indices de la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`idprovincia`),
  ADD UNIQUE KEY `uniq_codpais_provincia` (`codpais`,`provincia`),
  ADD KEY `ca_provincias_users_last_nick` (`last_nick`),
  ADD KEY `ca_provincias_users_nick` (`nick`);

--
-- Indices de la tabla `puntos_interes_ciudades`
--
ALTER TABLE `puntos_interes_ciudades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_puntos_interes_ciudades` (`idciudad`,`name`),
  ADD KEY `ca_puntos_interes_ciudades_users_last_nick` (`last_nick`),
  ADD KEY `ca_puntos_interes_ciudades_users_nick` (`nick`);

--
-- Indices de la tabla `recibospagoscli`
--
ALTER TABLE `recibospagoscli`
  ADD PRIMARY KEY (`idrecibo`),
  ADD KEY `ca_recibospagoscli_clientes` (`codcliente`),
  ADD KEY `ca_recibospagoscli_divisas` (`coddivisa`),
  ADD KEY `ca_recibospagoscli_facturascli` (`idfactura`);

--
-- Indices de la tabla `recibospagosprov`
--
ALTER TABLE `recibospagosprov`
  ADD PRIMARY KEY (`idrecibo`),
  ADD KEY `ca_recibospagosprov_divisas` (`coddivisa`),
  ADD KEY `ca_recibospagosprov_proveedores` (`codproveedor`),
  ADD KEY `ca_recibospagosprov_facturasprov` (`idfactura`);

--
-- Indices de la tabla `regularizacionimpuestos`
--
ALTER TABLE `regularizacionimpuestos`
  ADD PRIMARY KEY (`idregiva`),
  ADD KEY `ca_regularizacionimpuestos_ejercicios` (`codejercicio`),
  ADD KEY `ca_regularizacionimpuestos_subcuentas1` (`idsubcuentaacr`),
  ADD KEY `ca_regularizacionimpuestos_subcuentas2` (`idsubcuentadeu`),
  ADD KEY `ca_regularizacionimpuestos_asientos` (`idasiento`),
  ADD KEY `ca_regularizacionimpuestos_empresas` (`idempresa`);

--
-- Indices de la tabla `retenciones`
--
ALTER TABLE `retenciones`
  ADD PRIMARY KEY (`codretencion`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`codrole`);

--
-- Indices de la tabla `roles_access`
--
ALTER TABLE `roles_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_roles_access` (`codrole`,`pagename`);

--
-- Indices de la tabla `roles_users`
--
ALTER TABLE `roles_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_roles_users` (`codrole`,`nick`),
  ADD KEY `ca_roles_users_user` (`nick`);

--
-- Indices de la tabla `secuencias_documentos`
--
ALTER TABLE `secuencias_documentos`
  ADD PRIMARY KEY (`idsecuencia`),
  ADD KEY `ca_secuencias_documentos_ejercicio` (`codejercicio`),
  ADD KEY `ca_secuencias_documentos_serie` (`codserie`),
  ADD KEY `ca_secuencias_documentos_empresas` (`idempresa`);

--
-- Indices de la tabla `series`
--
ALTER TABLE `series`
  ADD PRIMARY KEY (`codserie`),
  ADD KEY `ca_series_diarios` (`iddiario`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`name`);

--
-- Indices de la tabla `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`idstock`),
  ADD UNIQUE KEY `uniq_stocks_almacen_referencia` (`codalmacen`,`referencia`),
  ADD KEY `ca_stocks_productos` (`idproducto`),
  ADD KEY `ca_stocks_variantes` (`referencia`);

--
-- Indices de la tabla `subcuentas`
--
ALTER TABLE `subcuentas`
  ADD PRIMARY KEY (`idsubcuenta`),
  ADD UNIQUE KEY `uniq_codsubcuenta` (`codsubcuenta`,`codejercicio`),
  ADD KEY `ca_subcuentas_ejercicios` (`codejercicio`),
  ADD KEY `ca_subcuentas_cuentas` (`idcuenta`),
  ADD KEY `ca_subcuentas_cuentasesp` (`codcuentaesp`);

--
-- Indices de la tabla `tarifas`
--
ALTER TABLE `tarifas`
  ADD PRIMARY KEY (`codtarifa`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`nick`),
  ADD KEY `ca_users_pages` (`homepage`),
  ADD KEY `ca_users_company` (`idempresa`);

--
-- Indices de la tabla `variantes`
--
ALTER TABLE `variantes`
  ADD PRIMARY KEY (`idvariante`),
  ADD UNIQUE KEY `uniq_variantes_referencia` (`referencia`),
  ADD KEY `ca_variantes_atributo1` (`idatributovalor1`),
  ADD KEY `ca_variantes_atributo2` (`idatributovalor2`),
  ADD KEY `ca_variantes_atributo3` (`idatributovalor3`),
  ADD KEY `ca_variantes_atributo4` (`idatributovalor4`),
  ADD KEY `ca_variantes_productos` (`idproducto`);

--
-- Indices de la tabla `work_events`
--
ALTER TABLE `work_events`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `albaranescli`
--
ALTER TABLE `albaranescli`
  MODIFY `idalbaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `albaranesprov`
--
ALTER TABLE `albaranesprov`
  MODIFY `idalbaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `api_access`
--
ALTER TABLE `api_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asientos`
--
ALTER TABLE `asientos`
  MODIFY `idasiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atan_contactos`
--
ALTER TABLE `atan_contactos`
  MODIFY `id_contacto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `atributos_valores`
--
ALTER TABLE `atributos_valores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attached_files`
--
ALTER TABLE `attached_files`
  MODIFY `idfile` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `attached_files_rel`
--
ALTER TABLE `attached_files_rel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `idciudad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_cambios_log`
--
ALTER TABLE `coci_cambios_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_comandas`
--
ALTER TABLE `coci_comandas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_comanda_respaldos`
--
ALTER TABLE `coci_comanda_respaldos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_detalles_orden`
--
ALTER TABLE `coci_detalles_orden`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_impresiones_log`
--
ALTER TABLE `coci_impresiones_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_ordenes`
--
ALTER TABLE `coci_ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_productos`
--
ALTER TABLE `coci_productos`
  MODIFY `producto_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_reservas`
--
ALTER TABLE `coci_reservas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_vouchers_genericos`
--
ALTER TABLE `coci_vouchers_genericos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `coci_voucher_clientes`
--
ALTER TABLE `coci_voucher_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `codigos_postales`
--
ALTER TABLE `codigos_postales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contactos`
--
ALTER TABLE `contactos`
  MODIFY `idcontacto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cronjobs`
--
ALTER TABLE `cronjobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `idcuenta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `doctransformations`
--
ALTER TABLE `doctransformations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `emails_sent`
--
ALTER TABLE `emails_sent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `idempresa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estados_documentos`
--
ALTER TABLE `estados_documentos`
  MODIFY `idestado` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturascli`
--
ALTER TABLE `facturascli`
  MODIFY `idfactura` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturasprov`
--
ALTER TABLE `facturasprov`
  MODIFY `idfactura` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `formatos_documentos`
--
ALTER TABLE `formatos_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `impuestoszonas`
--
ALTER TABLE `impuestoszonas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineasalbaranescli`
--
ALTER TABLE `lineasalbaranescli`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineasalbaranesprov`
--
ALTER TABLE `lineasalbaranesprov`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineasfacturascli`
--
ALTER TABLE `lineasfacturascli`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineasfacturasprov`
--
ALTER TABLE `lineasfacturasprov`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineaspedidoscli`
--
ALTER TABLE `lineaspedidoscli`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineaspedidosprov`
--
ALTER TABLE `lineaspedidosprov`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineaspresupuestoscli`
--
ALTER TABLE `lineaspresupuestoscli`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lineaspresupuestosprov`
--
ALTER TABLE `lineaspresupuestosprov`
  MODIFY `idlinea` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pages_filters`
--
ALTER TABLE `pages_filters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pages_options`
--
ALTER TABLE `pages_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagoscli`
--
ALTER TABLE `pagoscli`
  MODIFY `idpago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagosprov`
--
ALTER TABLE `pagosprov`
  MODIFY `idpago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `partidas`
--
ALTER TABLE `partidas`
  MODIFY `idpartida` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidoscli`
--
ALTER TABLE `pedidoscli`
  MODIFY `idpedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidosprov`
--
ALTER TABLE `pedidosprov`
  MODIFY `idpedido` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestoscli`
--
ALTER TABLE `presupuestoscli`
  MODIFY `idpresupuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `presupuestosprov`
--
ALTER TABLE `presupuestosprov`
  MODIFY `idpresupuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `idproducto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productosprov`
--
ALTER TABLE `productosprov`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos_imagenes`
--
ALTER TABLE `productos_imagenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `provincias`
--
ALTER TABLE `provincias`
  MODIFY `idprovincia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `puntos_interes_ciudades`
--
ALTER TABLE `puntos_interes_ciudades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recibospagoscli`
--
ALTER TABLE `recibospagoscli`
  MODIFY `idrecibo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recibospagosprov`
--
ALTER TABLE `recibospagosprov`
  MODIFY `idrecibo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `regularizacionimpuestos`
--
ALTER TABLE `regularizacionimpuestos`
  MODIFY `idregiva` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles_access`
--
ALTER TABLE `roles_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles_users`
--
ALTER TABLE `roles_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `secuencias_documentos`
--
ALTER TABLE `secuencias_documentos`
  MODIFY `idsecuencia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `stocks`
--
ALTER TABLE `stocks`
  MODIFY `idstock` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcuentas`
--
ALTER TABLE `subcuentas`
  MODIFY `idsubcuenta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `variantes`
--
ALTER TABLE `variantes`
  MODIFY `idvariante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `work_events`
--
ALTER TABLE `work_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agentes`
--
ALTER TABLE `agentes`
  ADD CONSTRAINT `ca_agentes_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `albaranescli`
--
ALTER TABLE `albaranescli`
  ADD CONSTRAINT `ca_albaranescli_codagente` FOREIGN KEY (`codagente`) REFERENCES `agentes` (`codagente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_codcliente` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_codpago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_codseries` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_codtrans` FOREIGN KEY (`codtrans`) REFERENCES `agenciastrans` (`codtrans`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_idempresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_idestados` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranescli_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `albaranesprov`
--
ALTER TABLE `albaranesprov`
  ADD CONSTRAINT `ca_albaranesprov_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_codpago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_codproveedor` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_albaranesprov_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `almacenes`
--
ALTER TABLE `almacenes`
  ADD CONSTRAINT `ca_almacenes_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `api_access`
--
ALTER TABLE `api_access`
  ADD CONSTRAINT `api_access_api` FOREIGN KEY (`idapikey`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `asientos`
--
ALTER TABLE `asientos`
  ADD CONSTRAINT `ca_asientos_diarios` FOREIGN KEY (`iddiario`) REFERENCES `diarios` (`iddiario`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_asientos_ejercicios` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_asientos_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `atributos_valores`
--
ALTER TABLE `atributos_valores`
  ADD CONSTRAINT `ca_atributos_valores` FOREIGN KEY (`codatributo`) REFERENCES `atributos` (`codatributo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `attached_files_rel`
--
ALTER TABLE `attached_files_rel`
  ADD CONSTRAINT `ca_attached_files_rel_files` FOREIGN KEY (`idfile`) REFERENCES `attached_files` (`idfile`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_attached_files_rel_users` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD CONSTRAINT `ca_ciudades_provincias` FOREIGN KEY (`idprovincia`) REFERENCES `provincias` (`idprovincia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_ciudades_users_last_nick` FOREIGN KEY (`last_nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_ciudades_users_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `ca_clientes_agentes` FOREIGN KEY (`codagente`) REFERENCES `agentes` (`codagente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_clientes_formaspago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_clientes_grupos` FOREIGN KEY (`codgrupo`) REFERENCES `gruposclientes` (`codgrupo`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_clientes_retenciones` FOREIGN KEY (`codretencion`) REFERENCES `retenciones` (`codretencion`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_clientes_series` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_clientes_tarifas` FOREIGN KEY (`codtarifa`) REFERENCES `tarifas` (`codtarifa`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `coci_detalles_orden`
--
ALTER TABLE `coci_detalles_orden`
  ADD CONSTRAINT `coci_detalles_orden_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `coci_ordenes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `coci_voucher_clientes`
--
ALTER TABLE `coci_voucher_clientes`
  ADD CONSTRAINT `fk_voucher_comanda` FOREIGN KEY (`comanda_id`) REFERENCES `coci_comandas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `codigos_postales`
--
ALTER TABLE `codigos_postales`
  ADD CONSTRAINT `ca_codigos_postales_ciudades` FOREIGN KEY (`idciudad`) REFERENCES `ciudades` (`idciudad`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_codigos_postales_paises` FOREIGN KEY (`codpais`) REFERENCES `paises` (`codpais`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_codigos_postales_provincias` FOREIGN KEY (`idprovincia`) REFERENCES `provincias` (`idprovincia`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_codigos_postales_users_last_nick` FOREIGN KEY (`last_nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_codigos_postales_users_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `contactos`
--
ALTER TABLE `contactos`
  ADD CONSTRAINT `ca_contactos_agentes` FOREIGN KEY (`codagente`) REFERENCES `agentes` (`codagente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_contactos_clientes` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_contactos_proveedores` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD CONSTRAINT `ca_cuentas_cuentasesp` FOREIGN KEY (`codcuentaesp`) REFERENCES `cuentasesp` (`codcuentaesp`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_cuentas_ejercicios` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_cuentas_parent` FOREIGN KEY (`parent_idcuenta`) REFERENCES `cuentas` (`idcuenta`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuentasbanco`
--
ALTER TABLE `cuentasbanco`
  ADD CONSTRAINT `ca_cuentasbanco_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuentasbcocli`
--
ALTER TABLE `cuentasbcocli`
  ADD CONSTRAINT `ca_cuentasbcocli_clientes` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuentasbcopro`
--
ALTER TABLE `cuentasbcopro`
  ADD CONSTRAINT `ca_cuentasbcopro_proveedores` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ejercicios`
--
ALTER TABLE `ejercicios`
  ADD CONSTRAINT `ca_ejercicios_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `emails_sent`
--
ALTER TABLE `emails_sent`
  ADD CONSTRAINT `emails_sent_user` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `ca_empresas_attached_files` FOREIGN KEY (`idlogo`) REFERENCES `attached_files` (`idfile`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturascli`
--
ALTER TABLE `facturascli`
  ADD CONSTRAINT `ca_facturascli_codagente` FOREIGN KEY (`codagente`) REFERENCES `agentes` (`codagente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_codcliente` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_codpago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_codtrans` FOREIGN KEY (`codtrans`) REFERENCES `agenciastrans` (`codtrans`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_idasiento` FOREIGN KEY (`idasiento`) REFERENCES `asientos` (`idasiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_idfacturarect` FOREIGN KEY (`idfacturarect`) REFERENCES `facturascli` (`idfactura`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturascli_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturasprov`
--
ALTER TABLE `facturasprov`
  ADD CONSTRAINT `ca_facturasprov_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_codpago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_codproveedor` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_idasiento` FOREIGN KEY (`idasiento`) REFERENCES `asientos` (`idasiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_idfacturarect` FOREIGN KEY (`idfacturarect`) REFERENCES `facturasprov` (`idfactura`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_facturasprov_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `formaspago`
--
ALTER TABLE `formaspago`
  ADD CONSTRAINT `ca_formaspago_cuentasbanco` FOREIGN KEY (`codcuentabanco`) REFERENCES `cuentasbanco` (`codcuenta`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_formaspago_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `formatos_documentos`
--
ALTER TABLE `formatos_documentos`
  ADD CONSTRAINT `ca_formatos_documentos_attached_files` FOREIGN KEY (`idlogo`) REFERENCES `attached_files` (`idfile`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_formatos_documentos_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_formatos_documentos_series` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gruposclientes`
--
ALTER TABLE `gruposclientes`
  ADD CONSTRAINT `ca_gruposclientes_tarifas` FOREIGN KEY (`codtarifa`) REFERENCES `tarifas` (`codtarifa`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `impuestoszonas`
--
ALTER TABLE `impuestoszonas`
  ADD CONSTRAINT `ca_impuestoszonas_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_impuestoszonas_impuestos2` FOREIGN KEY (`codimpuestosel`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineasalbaranescli`
--
ALTER TABLE `lineasalbaranescli`
  ADD CONSTRAINT `ca_lineasalbaranescli_albaranescli2` FOREIGN KEY (`idalbaran`) REFERENCES `albaranescli` (`idalbaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasalbaranescli_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasalbaranescli_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineasalbaranesprov`
--
ALTER TABLE `lineasalbaranesprov`
  ADD CONSTRAINT `ca_lineasalbaranesprov_albaranesprov2` FOREIGN KEY (`idalbaran`) REFERENCES `albaranesprov` (`idalbaran`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasalbaranesprov_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasalbaranesprov_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineasfacturascli`
--
ALTER TABLE `lineasfacturascli`
  ADD CONSTRAINT `ca_lineasfacturascli_facturascli2` FOREIGN KEY (`idfactura`) REFERENCES `facturascli` (`idfactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasfacturascli_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasfacturascli_linearect2` FOREIGN KEY (`idlinearect`) REFERENCES `lineasfacturascli` (`idlinea`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasfacturascli_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineasfacturasprov`
--
ALTER TABLE `lineasfacturasprov`
  ADD CONSTRAINT `ca_lineasfacturasprov_facturasprov2` FOREIGN KEY (`idfactura`) REFERENCES `facturasprov` (`idfactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasfacturasprov_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasfacturasprov_linearect2` FOREIGN KEY (`idlinearect`) REFERENCES `lineasfacturasprov` (`idlinea`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineasfacturasprov_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineaspedidoscli`
--
ALTER TABLE `lineaspedidoscli`
  ADD CONSTRAINT `ca_lineaspedidoscli_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspedidoscli_pedidoscli` FOREIGN KEY (`idpedido`) REFERENCES `pedidoscli` (`idpedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspedidoscli_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineaspedidosprov`
--
ALTER TABLE `lineaspedidosprov`
  ADD CONSTRAINT `ca_lineaspedidosprov_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspedidosprov_pedidosprov` FOREIGN KEY (`idpedido`) REFERENCES `pedidosprov` (`idpedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspedidosprov_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineaspresupuestoscli`
--
ALTER TABLE `lineaspresupuestoscli`
  ADD CONSTRAINT `ca_lineaspresupuestoscli_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspresupuestoscli_presupuestoscli` FOREIGN KEY (`idpresupuesto`) REFERENCES `presupuestoscli` (`idpresupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspresupuestoscli_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `lineaspresupuestosprov`
--
ALTER TABLE `lineaspresupuestosprov`
  ADD CONSTRAINT `ca_lineaspresupuestosprov_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspresupuestosprov_presupuestosprov` FOREIGN KEY (`idpresupuesto`) REFERENCES `presupuestosprov` (`idpresupuesto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_lineaspresupuestosprov_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pages_filters`
--
ALTER TABLE `pages_filters`
  ADD CONSTRAINT `ca_pagesfilters_users` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pages_options`
--
ALTER TABLE `pages_options`
  ADD CONSTRAINT `ca_pagesoptions_users` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagoscli`
--
ALTER TABLE `pagoscli`
  ADD CONSTRAINT `ca_pagoscli_asiento` FOREIGN KEY (`idasiento`) REFERENCES `asientos` (`idasiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pagoscli_recibo` FOREIGN KEY (`idrecibo`) REFERENCES `recibospagoscli` (`idrecibo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagosprov`
--
ALTER TABLE `pagosprov`
  ADD CONSTRAINT `ca_pagosprov_asiento` FOREIGN KEY (`idasiento`) REFERENCES `asientos` (`idasiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pagosprov_recibo` FOREIGN KEY (`idrecibo`) REFERENCES `recibospagosprov` (`idrecibo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `paises`
--
ALTER TABLE `paises`
  ADD CONSTRAINT `ca_paises_users_last_nick` FOREIGN KEY (`last_nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_paises_users_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `partidas`
--
ALTER TABLE `partidas`
  ADD CONSTRAINT `ca_partidas_asientos` FOREIGN KEY (`idasiento`) REFERENCES `asientos` (`idasiento`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_partidas_subcuentas` FOREIGN KEY (`idsubcuenta`) REFERENCES `subcuentas` (`idsubcuenta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_partidas_subcuentas2` FOREIGN KEY (`idcontrapartida`) REFERENCES `subcuentas` (`idsubcuenta`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidoscli`
--
ALTER TABLE `pedidoscli`
  ADD CONSTRAINT `ca_pedidoscli_codagente` FOREIGN KEY (`codagente`) REFERENCES `agentes` (`codagente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_codcliente` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_codpago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_codtrans` FOREIGN KEY (`codtrans`) REFERENCES `agenciastrans` (`codtrans`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidoscli_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidosprov`
--
ALTER TABLE `pedidosprov`
  ADD CONSTRAINT `ca_pedidosprov_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_codpagos` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_codproveedor` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_pedidosprov_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuestoscli`
--
ALTER TABLE `presupuestoscli`
  ADD CONSTRAINT `ca_presupuestoscli_codagente` FOREIGN KEY (`codagente`) REFERENCES `agentes` (`codagente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_codcliente` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_codpago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_codtrans` FOREIGN KEY (`codtrans`) REFERENCES `agenciastrans` (`codtrans`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestoscli_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `presupuestosprov`
--
ALTER TABLE `presupuestosprov`
  ADD CONSTRAINT `ca_presupuestosprov_codalmacen` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_coddivisa` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_codejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_codproveedor` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_codserie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_idempresa` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_idestado` FOREIGN KEY (`idestado`) REFERENCES `estados_documentos` (`idestado`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_presupuestosprov_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `ca_productos_fabricantes` FOREIGN KEY (`codfabricante`) REFERENCES `fabricantes` (`codfabricante`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_productos_familias` FOREIGN KEY (`codfamilia`) REFERENCES `familias` (`codfamilia`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_productos_impuestos` FOREIGN KEY (`codimpuesto`) REFERENCES `impuestos` (`codimpuesto`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `productosprov`
--
ALTER TABLE `productosprov`
  ADD CONSTRAINT `ca_productosprov_divisas` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_productosprov_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_productosprov_proveedores` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos_imagenes`
--
ALTER TABLE `productos_imagenes`
  ADD CONSTRAINT `ca_productos_imagenes_attachedfiles` FOREIGN KEY (`idfile`) REFERENCES `attached_files` (`idfile`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_productos_imagenes_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_productos_imagenes_variantes` FOREIGN KEY (`referencia`) REFERENCES `variantes` (`referencia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `ca_proveedores_formaspago` FOREIGN KEY (`codpago`) REFERENCES `formaspago` (`codpago`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_proveedores_retenciones` FOREIGN KEY (`codretencion`) REFERENCES `retenciones` (`codretencion`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_proveedores_series` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `provincias`
--
ALTER TABLE `provincias`
  ADD CONSTRAINT `ca_provincias_paises` FOREIGN KEY (`codpais`) REFERENCES `paises` (`codpais`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_provincias_users_last_nick` FOREIGN KEY (`last_nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_provincias_users_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `puntos_interes_ciudades`
--
ALTER TABLE `puntos_interes_ciudades`
  ADD CONSTRAINT `ca_puntos_interes_ciudades_users_last_nick` FOREIGN KEY (`last_nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_puntos_interes_ciudades_users_nick` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `recibospagoscli`
--
ALTER TABLE `recibospagoscli`
  ADD CONSTRAINT `ca_recibospagoscli_clientes` FOREIGN KEY (`codcliente`) REFERENCES `clientes` (`codcliente`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_recibospagoscli_divisas` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_recibospagoscli_facturascli` FOREIGN KEY (`idfactura`) REFERENCES `facturascli` (`idfactura`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recibospagosprov`
--
ALTER TABLE `recibospagosprov`
  ADD CONSTRAINT `ca_recibospagosprov_divisas` FOREIGN KEY (`coddivisa`) REFERENCES `divisas` (`coddivisa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_recibospagosprov_facturasprov` FOREIGN KEY (`idfactura`) REFERENCES `facturasprov` (`idfactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_recibospagosprov_proveedores` FOREIGN KEY (`codproveedor`) REFERENCES `proveedores` (`codproveedor`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `regularizacionimpuestos`
--
ALTER TABLE `regularizacionimpuestos`
  ADD CONSTRAINT `ca_regularizacionimpuestos_asientos` FOREIGN KEY (`idasiento`) REFERENCES `asientos` (`idasiento`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_regularizacionimpuestos_ejercicios` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_regularizacionimpuestos_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_regularizacionimpuestos_subcuentas1` FOREIGN KEY (`idsubcuentaacr`) REFERENCES `subcuentas` (`idsubcuenta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_regularizacionimpuestos_subcuentas2` FOREIGN KEY (`idsubcuentadeu`) REFERENCES `subcuentas` (`idsubcuenta`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles_access`
--
ALTER TABLE `roles_access`
  ADD CONSTRAINT `ca_roles_access_roles` FOREIGN KEY (`codrole`) REFERENCES `roles` (`codrole`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `ca_roles_users_roles` FOREIGN KEY (`codrole`) REFERENCES `roles` (`codrole`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_roles_users_user` FOREIGN KEY (`nick`) REFERENCES `users` (`nick`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `secuencias_documentos`
--
ALTER TABLE `secuencias_documentos`
  ADD CONSTRAINT `ca_secuencias_documentos_ejercicio` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_secuencias_documentos_empresas` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_secuencias_documentos_serie` FOREIGN KEY (`codserie`) REFERENCES `series` (`codserie`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `series`
--
ALTER TABLE `series`
  ADD CONSTRAINT `ca_series_diarios` FOREIGN KEY (`iddiario`) REFERENCES `diarios` (`iddiario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `ca_stocks_almacenes` FOREIGN KEY (`codalmacen`) REFERENCES `almacenes` (`codalmacen`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_stocks_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_stocks_variantes` FOREIGN KEY (`referencia`) REFERENCES `variantes` (`referencia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `subcuentas`
--
ALTER TABLE `subcuentas`
  ADD CONSTRAINT `ca_subcuentas_cuentas` FOREIGN KEY (`idcuenta`) REFERENCES `cuentas` (`idcuenta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_subcuentas_cuentasesp` FOREIGN KEY (`codcuentaesp`) REFERENCES `cuentasesp` (`codcuentaesp`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_subcuentas_ejercicios` FOREIGN KEY (`codejercicio`) REFERENCES `ejercicios` (`codejercicio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `ca_users_company` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_users_pages` FOREIGN KEY (`homepage`) REFERENCES `pages` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `variantes`
--
ALTER TABLE `variantes`
  ADD CONSTRAINT `ca_variantes_atributo1` FOREIGN KEY (`idatributovalor1`) REFERENCES `atributos_valores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_variantes_atributo2` FOREIGN KEY (`idatributovalor2`) REFERENCES `atributos_valores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_variantes_atributo3` FOREIGN KEY (`idatributovalor3`) REFERENCES `atributos_valores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_variantes_atributo4` FOREIGN KEY (`idatributovalor4`) REFERENCES `atributos_valores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ca_variantes_productos` FOREIGN KEY (`idproducto`) REFERENCES `productos` (`idproducto`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
