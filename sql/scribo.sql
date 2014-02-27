-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 27-02-2014 a las 09:08:46
-- Versión del servidor: 5.5.35-1
-- Versión de PHP: 5.5.9-1

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `acabado`
--

INSERT INTO `acabado` (`id`, `name`, `cost`, `value`, `type`, `data`) VALUES
(2, 'Laminado ATM', 750.00, 700.0, 'S', 'Laminado bÃ¡sico plano.');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id`, `type`, `document`, `name`, `contact`, `address`, `phone`, `mail`, `data`) VALUES
(3, 'CC', '1070599623', 'Creativos Estoner', 'Monica Cortes', 'Cll 21 B  3 16 50', '22061658', 'mcortes@estoner.org', 'Prueba'),
(4, 'NT', '9005373868', 'Productora MSB', 'Milo Rambaldy', 'Av 15 # 3-25 Milan Italia', '85412135214821212', 'caontact@msb.com', 'Nada especial.');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Volcado de datos para la tabla `material`
--

INSERT INTO `material` (`id`, `name`, `cost`, `value`, `type`, `width`, `height`, `weigth`, `data`) VALUES
(2, 'Esfix Polimero', 4200.00, 3750.00, 'S', 1250.00, 780.00, 350.00, 'Material de alta gama con soporte termico'),
(3, 'Meta cromo', 900.00, 750.00, 'P', 800.00, 800.00, 12.00, ''),
(4, 'Lima mate', 500.00, 450.00, 'P', 900.00, 450.00, 15.00, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden`
--

CREATE TABLE IF NOT EXISTS `orden` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` int(10) unsigned NOT NULL,
  `usuario_id` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `status` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `subtotal` decimal(15,2) unsigned NOT NULL,
  `iva` decimal(15,2) unsigned NOT NULL,
  `total` decimal(15,2) unsigned NOT NULL,
  `signature` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
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
  `content` longtext COLLATE utf8_unicode_ci NOT NULL,
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
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `papel_id` (`papel_id`),
  KEY `acabado_id` (`acabado_id`)
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `personal`
--

INSERT INTO `personal` (`id`, `document`, `surname`, `name`, `address`, `phone`, `mail`, `data`) VALUES
(1, '0000', 'System', 'Admin', '_NONE_', '_NONE_', '_NONE_', 'System user.'),
(2, '1070597089', 'Herran Cortes', 'Edison Javier', 'Mz 16 Cs 15 Brr Diamante', '3108048435', 'ejherran.c@gmail.com', 'Otaku!.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proceso`
--

CREATE TABLE IF NOT EXISTS `proceso` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `orden_id` bigint(99) unsigned NOT NULL,
  `emite_id` int(10) unsigned NOT NULL,
  `recibe_id` int(10) unsigned NOT NULL,
  `action` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orden_id` (`orden_id`),
  KEY `emite_id` (`emite_id`),
  KEY `recibe_id` (`recibe_id`)
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Volcado de datos para la tabla `tinta`
--

INSERT INTO `tinta` (`id`, `name`, `cost`, `value`, `type`, `data`) VALUES
(3, 'CMYK 4x0', 350.00, 350.00, 'P', 'Tinta basica para papel'),
(4, 'RGB 4x1', 300.00, 280.00, 'P', '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `personal_id`, `role`, `user`, `pass`, `data`) VALUES
(1, 1, 'R', 'root', 'ce482c0ad4781ca77cd22c43d971b1ecfe13b2b9dd5442f67464a035722d843844447bf5b63b4f91f4effaf46333ef8a3b7f39bb33a689dd68507ae5a74d84b6', 'System user.'),
(9, 2, 'R', 'ejherran', '08c8e7479c00881c9e9d57d5b473f7fd621117d21679731d0126be3d3f88ac01eb92b77f4062bba3573f09a44b0ffffbe5745d21be908e3da1749fac1acbc3d0', 'Admin...'),
(10, 2, 'A', 'ase', '3593686f0791afc2f13ccb2e96aa99eb96f110171c7181dea9770fe515b690564e6e71f9e46fc0539000dc1a2f1beaa8d9e6627b5cd8be5cbc2967369dd7ad15', 'Test de asesor');

--
-- Restricciones para tablas volcadas
--

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
-- Filtros para la tabla `proceso`
--
ALTER TABLE `proceso`
  ADD CONSTRAINT `proceso_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proceso_ibfk_2` FOREIGN KEY (`emite_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `proceso_ibfk_3` FOREIGN KEY (`recibe_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
