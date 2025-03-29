# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.7.39)
# Database: sudoku
# Generation Time: 2025-03-19 16:51:17 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table candidate_hidden1
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden1`;

CREATE TABLE `candidate_hidden1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_hidden2
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden2`;

CREATE TABLE `candidate_hidden2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_hidden3
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden3`;

CREATE TABLE `candidate_hidden3` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_hidden4
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden4`;

CREATE TABLE `candidate_hidden4` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_jellyfish
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_jellyfish`;

CREATE TABLE `candidate_jellyfish` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_naked2
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_naked2`;

CREATE TABLE `candidate_naked2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_naked3
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_naked3`;

CREATE TABLE `candidate_naked3` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_naked4
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_naked4`;

CREATE TABLE `candidate_naked4` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_omissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_omissions`;

CREATE TABLE `candidate_omissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_swordfish
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_swordfish`;

CREATE TABLE `candidate_swordfish` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_uniqueRectangle
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_uniqueRectangle`;

CREATE TABLE `candidate_uniqueRectangle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_xWing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_xWing`;

CREATE TABLE `candidate_xWing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_xyzWing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_xyzWing`;

CREATE TABLE `candidate_xyzWing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;



# Dump of table candidate_yWing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_yWing`;

CREATE TABLE `candidate_yWing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `count` tinyint(2) unsigned NOT NULL,
  `clueCount` tinyint(2) unsigned NOT NULL,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
