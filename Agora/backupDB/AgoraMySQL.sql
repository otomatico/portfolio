-- --------------------------------------------------------
-- Host:                         localhost
-- Versión del servidor:         10.4.28-MariaDB - mariadb.org binary distribution
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para agora
CREATE DATABASE IF NOT EXISTS `agora` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `agora`;

-- Volcando estructura para tabla agora.master
CREATE TABLE IF NOT EXISTS `master` (
  `IdMaster` char(36) NOT NULL DEFAULT uuid(),
  `Name` varchar(255) DEFAULT NULL COMMENT 'Almacena los combo de tipo seleccion',
  `CreateDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `ModifyDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdMaster`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Volcando estructura para tabla agora.master_option
CREATE TABLE IF NOT EXISTS `master_option` (
  `IdOption` char(36) NOT NULL DEFAULT uuid(),
  `IdMaster` char(36) NOT NULL,
  `Name` varchar(255) DEFAULT NULL COMMENT 'Almacena el nombre de la opcion',
  `Value` varchar(255) DEFAULT NULL COMMENT 'Almacena la el valor de la opcion',
  `Place` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`IdOption`),
  KEY `producto_nodo_FK` (`IdMaster`),
  CONSTRAINT `maesto_opcion_FK` FOREIGN KEY (`IdMaster`) REFERENCES `master` (`IdMaster`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena las tuplas(nombre,valor) de un combo de seleccion';


-- Volcando estructura para tabla agora.node
CREATE TABLE IF NOT EXISTS `node` (
  `IdNode` char(36) NOT NULL DEFAULT uuid(),
  `IdTemplate` char(36) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `JsonValue` varchar(255) DEFAULT NULL,
  `ModifyDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdNode`),
  KEY `nodo_plantilla` (`IdTemplate`),
  CONSTRAINT `nodo_plantilla` FOREIGN KEY (`IdTemplate`) REFERENCES `template` (`IdTemplate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Almacena los nodos de mi arbol que es el producto';

-- Volcando estructura para tabla agora.node_relation
CREATE TABLE IF NOT EXISTS `node_relation` (
  `IdRelation` int(11) NOT NULL AUTO_INCREMENT,
  `IdNode` char(36) NOT NULL DEFAULT 'error',
  `IdNodeParent` char(36) NOT NULL DEFAULT 'error',
  `IdNodeRoot` char(36) NOT NULL DEFAULT 'error',
  `Place` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdRelation`) USING BTREE,
  KEY `nodo_hijo` (`IdNode`),
  KEY `nodo_raiz` (`IdNodeParent`),
  KEY `nodo_raiz_FK` (`IdNodeRoot`),
  CONSTRAINT `nodo_hijo_FK` FOREIGN KEY (`IdNode`) REFERENCES `node` (`IdNode`),
  CONSTRAINT `nodo_padre_FK` FOREIGN KEY (`IdNodeParent`) REFERENCES `node` (`IdNode`),
  CONSTRAINT `nodo_raiz_FK` FOREIGN KEY (`IdNodeRoot`) REFERENCES `node` (`IdNode`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Volcando estructura para tabla agora.template
CREATE TABLE IF NOT EXISTS `template` (
  `IdTemplate` char(36) NOT NULL DEFAULT uuid(),
  `Name` varchar(255) DEFAULT NULL COMMENT 'Nombre de la Plantilla',
  `ModifyDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`IdTemplate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Volcando estructura para tabla agora.template_allowed_children
CREATE TABLE IF NOT EXISTS `template_allowed_children` (
  `IdTemplate` char(36) NOT NULL,
  `IdTemplateParent` char(36) NOT NULL,
  `MaxAllowed` int(11) NOT NULL,
  KEY `hijo_plantilla` (`IdTemplate`),
  KEY `padre_plantilla` (`IdTemplateParent`),
  CONSTRAINT `hijo_plantilla` FOREIGN KEY (`IdTemplate`) REFERENCES `template` (`IdTemplate`),
  CONSTRAINT `padre_plantilla` FOREIGN KEY (`IdTemplateParent`) REFERENCES `template` (`IdTemplate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Volcando estructura para tabla agora.template_field
CREATE TABLE IF NOT EXISTS `template_field` (
  `IdField` char(36) NOT NULL DEFAULT uuid(),
  `IdTemplate` char(36) NOT NULL,
  `IdType` int(11) NOT NULL,
  `IdMaster` char(36) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL COMMENT 'Nombre del Campo',
  `AttributeName` varchar(50) DEFAULT '',
  `DefaultValue` varchar(20) DEFAULT NULL,
  `Required` tinyint(1) NOT NULL DEFAULT 0,
  `Place` int(11) DEFAULT 0,
  PRIMARY KEY (`IdField`),
  KEY `formulario_plantilla` (`IdTemplate`),
  KEY `formulario_tipo` (`IdType`),
  CONSTRAINT `formulario_plantilla` FOREIGN KEY (`IdTemplate`) REFERENCES `template` (`IdTemplate`),
  CONSTRAINT `formulario_tipo` FOREIGN KEY (`IdType`) REFERENCES `type_field` (`IdType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando estructura para tabla agora.type_field
CREATE TABLE IF NOT EXISTS `type_field` (
  `IdType` int(11) NOT NULL AUTO_INCREMENT,
  `Field` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`IdType`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcando datos para la tabla agora.type_field: ~6 rows (aproximadamente)
REPLACE INTO `type_field` (`IdType`, `Field`) VALUES
	(1, 'Boleano'),
	(2, 'Numero'),
	(3, 'Texto'),
	(4, 'Seleccion'),
	(5, 'Seleccion Multiple'),
	(6, 'Recurso');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
