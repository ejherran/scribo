-- phpMyAdmin SQL Dump
-- version 4.1.12deb2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost:3306
-- Tiempo de generación: 29-04-2014 a las 01:24:47
-- Versión del servidor: 5.5.35-2
-- Versión de PHP: 5.5.11-3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `scribo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acabado`
--

CREATE TABLE IF NOT EXISTS `acabado` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `value` decimal(10,1) NOT NULL,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE IF NOT EXISTS `cliente` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `document` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document` (`document`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE IF NOT EXISTS `configuracion` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `document` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `web` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `storage` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `report` text COLLATE utf8_unicode_ci NOT NULL,
  `logo` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document` (`document`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `type`, `document`, `name`, `contact`, `address`, `phone`, `web`, `mail`, `storage`, `report`, `logo`) VALUES
(1, 'N', '00000000000', '_NONE_', '_NONE_', '_NONE_', '_NONE_', '_NONE_', '_NONE_', '', '', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrega`
--

CREATE TABLE IF NOT EXISTS `entrega` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `orden_id` bigint(99) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `signature` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `orden_id` (`orden_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE IF NOT EXISTS `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) unsigned NOT NULL,
  `value` decimal(10,2) unsigned NOT NULL,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `width` decimal(10,2) unsigned NOT NULL,
  `height` decimal(10,2) unsigned NOT NULL,
  `weigth` decimal(10,2) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE IF NOT EXISTS `orden` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `cliente_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `status` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `subtotal` decimal(15,2) unsigned NOT NULL,
  `iva` decimal(15,2) unsigned NOT NULL,
  `total` decimal(15,2) unsigned NOT NULL,
  `signature` longtext COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `papel`
--

CREATE TABLE IF NOT EXISTS `papel` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `orden_id` bigint(99) unsigned NOT NULL,
  `material_id` int(10) unsigned NOT NULL,
  `tinta_id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `pages` int(10) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `unit` decimal(15,2) unsigned NOT NULL,
  `value` decimal(15,2) unsigned NOT NULL,
  `storage` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `signature` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `expiry` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `material_id` (`material_id`),
  KEY `tinta_id` (`tinta_id`),
  KEY `orden_id` (`orden_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `papelAcabado`
--

CREATE TABLE IF NOT EXISTS `papelAcabado` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `papel_id` bigint(99) unsigned NOT NULL,
  `acabado_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `papel_id` (`papel_id`),
  KEY `acabado_id` (`acabado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perdida`
--

CREATE TABLE IF NOT EXISTS `perdida` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `orden_id` bigint(99) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `orden_id` (`orden_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal`
--

CREATE TABLE IF NOT EXISTS `personal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `document` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document` (`document`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `document`, `surname`, `name`, `address`, `phone`, `mail`, `data`) VALUES
(1, '000000000', 'System', 'Admin', '_NONE_', '_NONE_', '_NONE_', 'System User.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso`
--

CREATE TABLE IF NOT EXISTS `proceso` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `orden_id` bigint(99) unsigned NOT NULL,
  `emite_id` int(10) unsigned NOT NULL,
  `recibe_id` int(10) unsigned NOT NULL,
  `status` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `action` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_id` (`orden_id`),
  KEY `emite_id` (`emite_id`),
  KEY `recibe_id` (`recibe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sustrato`
--

CREATE TABLE IF NOT EXISTS `sustrato` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `orden_id` bigint(99) unsigned NOT NULL,
  `material_id` int(10) unsigned NOT NULL,
  `tinta_id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `width` decimal(15,2) unsigned NOT NULL,
  `height` decimal(15,2) unsigned NOT NULL,
  `amount` int(10) unsigned NOT NULL,
  `unit` decimal(15,2) unsigned NOT NULL,
  `value` decimal(15,2) unsigned NOT NULL,
  `storage` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `signature` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `expiry` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `material_id` (`material_id`),
  KEY `tinta_id` (`tinta_id`),
  KEY `orden_id` (`orden_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sustratoAcabado`
--

CREATE TABLE IF NOT EXISTS `sustratoAcabado` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `sustrato_id` bigint(99) unsigned NOT NULL,
  `acabado_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `papel_id` (`sustrato_id`),
  KEY `acabado_id` (`acabado_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tinta`
--

CREATE TABLE IF NOT EXISTS `tinta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE IF NOT EXISTS `usuario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `personal_id` int(10) unsigned NOT NULL,
  `role` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `personal_id` (`personal_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `personal_id`, `role`, `user`, `pass`, `data`) VALUES
(1, 1, 'R', 'root', 'ce482c0ad4781ca77cd22c43d971b1ecfe13b2b9dd5442f67464a035722d843844447bf5b63b4f91f4effaf46333ef8a3b7f39bb33a689dd68507ae5a74d84b6', 'System User.');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `entrega`
--
ALTER TABLE `entrega`
  ADD CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `entrega_ibfk_2` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `orden`
--
ALTER TABLE `orden`
  ADD CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `papel`
--
ALTER TABLE `papel`
  ADD CONSTRAINT `papel_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `papel_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `papel_ibfk_3` FOREIGN KEY (`tinta_id`) REFERENCES `tinta` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `papelAcabado`
--
ALTER TABLE `papelAcabado`
  ADD CONSTRAINT `papelAcabado_ibfk_1` FOREIGN KEY (`papel_id`) REFERENCES `papel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `papelAcabado_ibfk_2` FOREIGN KEY (`acabado_id`) REFERENCES `acabado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `perdida`
--
ALTER TABLE `perdida`
  ADD CONSTRAINT `perdida_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `perdida_ibfk_2` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `proceso`
--
ALTER TABLE `proceso`
  ADD CONSTRAINT `proceso_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proceso_ibfk_2` FOREIGN KEY (`emite_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proceso_ibfk_3` FOREIGN KEY (`recibe_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `sustrato`
--
ALTER TABLE `sustrato`
  ADD CONSTRAINT `sustrato_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sustrato_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sustrato_ibfk_3` FOREIGN KEY (`tinta_id`) REFERENCES `tinta` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `sustratoAcabado`
--
ALTER TABLE `sustratoAcabado`
  ADD CONSTRAINT `sustratoAcabado_ibfk_1` FOREIGN KEY (`sustrato_id`) REFERENCES `sustrato` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `sustratoAcabado_ibfk_2` FOREIGN KEY (`acabado_id`) REFERENCES `acabado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
