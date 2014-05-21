-- MySQL dump 10.13  Distrib 5.5.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sctest
-- ------------------------------------------------------
-- Server version	5.5.37-1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acabado`
--

DROP TABLE IF EXISTS `acabado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acabado` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `value` decimal(10,1) NOT NULL,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cliente` (
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuracion`
--

DROP TABLE IF EXISTS `configuracion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuracion` (
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entrega`
--

DROP TABLE IF EXISTS `entrega`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entrega` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `orden_id` bigint(99) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `signature` longtext NOT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `orden_id` (`orden_id`),
  CONSTRAINT `entrega_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `entrega_ibfk_2` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=99779 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `material`
--

DROP TABLE IF EXISTS `material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) unsigned NOT NULL,
  `value` decimal(10,2) unsigned NOT NULL,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `width` decimal(10,2) unsigned NOT NULL,
  `height` decimal(10,2) unsigned NOT NULL,
  `weigth` decimal(10,2) unsigned NOT NULL,
  `discount` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orden`
--

DROP TABLE IF EXISTS `orden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orden` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `mode` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `proc` char(1) COLLATE utf8_unicode_ci NOT NULL,
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
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `orden_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `orden_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `papel`
--

DROP TABLE IF EXISTS `papel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `papel` (
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
  KEY `orden_id` (`orden_id`),
  CONSTRAINT `papel_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `papel_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `papel_ibfk_3` FOREIGN KEY (`tinta_id`) REFERENCES `tinta` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `papelAcabado`
--

DROP TABLE IF EXISTS `papelAcabado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `papelAcabado` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `papel_id` bigint(99) unsigned NOT NULL,
  `acabado_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `papel_id` (`papel_id`),
  KEY `acabado_id` (`acabado_id`),
  CONSTRAINT `papelAcabado_ibfk_1` FOREIGN KEY (`papel_id`) REFERENCES `papel` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `papelAcabado_ibfk_2` FOREIGN KEY (`acabado_id`) REFERENCES `acabado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `perdida`
--

DROP TABLE IF EXISTS `perdida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `perdida` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_id` int(10) unsigned NOT NULL,
  `orden_id` bigint(99) unsigned NOT NULL,
  `date` datetime NOT NULL,
  `valor` decimal(15,2) NOT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `orden_id` (`orden_id`),
  CONSTRAINT `perdida_ibfk_2` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `perdida_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=99572 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal`
--

DROP TABLE IF EXISTS `personal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `document` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `mail` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `document` (`document`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `proceso`
--

DROP TABLE IF EXISTS `proceso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `proceso` (
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
  KEY `recibe_id` (`recibe_id`),
  CONSTRAINT `proceso_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `proceso_ibfk_2` FOREIGN KEY (`emite_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `proceso_ibfk_3` FOREIGN KEY (`recibe_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sustrato`
--

DROP TABLE IF EXISTS `sustrato`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sustrato` (
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
  KEY `orden_id` (`orden_id`),
  CONSTRAINT `sustrato_ibfk_1` FOREIGN KEY (`orden_id`) REFERENCES `orden` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sustrato_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sustrato_ibfk_3` FOREIGN KEY (`tinta_id`) REFERENCES `tinta` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sustratoAcabado`
--

DROP TABLE IF EXISTS `sustratoAcabado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sustratoAcabado` (
  `id` bigint(99) unsigned NOT NULL AUTO_INCREMENT,
  `sustrato_id` bigint(99) unsigned NOT NULL,
  `acabado_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `papel_id` (`sustrato_id`),
  KEY `acabado_id` (`acabado_id`),
  CONSTRAINT `sustratoAcabado_ibfk_1` FOREIGN KEY (`sustrato_id`) REFERENCES `sustrato` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `sustratoAcabado_ibfk_2` FOREIGN KEY (`acabado_id`) REFERENCES `acabado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tinta`
--

DROP TABLE IF EXISTS `tinta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tinta` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `type` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `discount` text COLLATE utf8_unicode_ci,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `personal_id` int(10) unsigned NOT NULL,
  `role` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  KEY `personal_id` (`personal_id`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`personal_id`) REFERENCES `personal` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

--
-- DATA BASICS
--

INSERT INTO `configuracion` (`id`, `type`, `document`, `name`, `contact`, `address`, `phone`, `web`, `mail`, `storage`, `report`, `logo`) VALUES
(1, 'NT', '00000000000', '_NONE_', '_NONE_', '_NONE_', '_NONE_', '_NONE_', '_NONE_', '', '', '');

INSERT INTO `personal` (`id`, `status`, `document`, `surname`, `name`, `address`, `phone`, `mail`, `data`) VALUES
(1, 'A', '000000000', 'System', 'Admin', '_NONE_', '_NONE_', '_NONE_', 'System User.');

INSERT INTO `usuario` (`id`, `personal_id`, `role`, `user`, `pass`, `data`) VALUES
(1, 1, 'R', 'root', 'ce482c0ad4781ca77cd22c43d971b1ecfe13b2b9dd5442f67464a035722d843844447bf5b63b4f91f4effaf46333ef8a3b7f39bb33a689dd68507ae5a74d84b6', 'System User.');

-- Dump completed on 2014-05-21  9:00:41
