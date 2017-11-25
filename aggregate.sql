
SET NAMES utf8;
SET time_zone = '+00:00';

CREATE DATABASE `mysql` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `mysql`;

CREATE TABLE `aggregate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service` varchar(255) NOT NULL,
  `item` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
