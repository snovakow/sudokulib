# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.7.32)
# Database: sudoku
# Generation Time: 2024-11-30 15:48:37 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table candidate
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate`;

CREATE TABLE `candidate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_hidden1
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden1`;

CREATE TABLE `candidate_hidden1` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_hidden2
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden2`;

CREATE TABLE `candidate_hidden2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_hidden3
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden3`;

CREATE TABLE `candidate_hidden3` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_hidden4
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_hidden4`;

CREATE TABLE `candidate_hidden4` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_jellyfish
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_jellyfish`;

CREATE TABLE `candidate_jellyfish` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_naked2
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_naked2`;

CREATE TABLE `candidate_naked2` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_naked3
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_naked3`;

CREATE TABLE `candidate_naked3` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_naked4
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_naked4`;

CREATE TABLE `candidate_naked4` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_omissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_omissions`;

CREATE TABLE `candidate_omissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_swordfish
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_swordfish`;

CREATE TABLE `candidate_swordfish` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_uniqueRectangle
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_uniqueRectangle`;

CREATE TABLE `candidate_uniqueRectangle` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_visible
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_visible`;

CREATE TABLE `candidate_visible` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_xWing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_xWing`;

CREATE TABLE `candidate_xWing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_xyzWing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_xyzWing`;

CREATE TABLE `candidate_xyzWing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table candidate_yWing
# ------------------------------------------------------------

DROP TABLE IF EXISTS `candidate_yWing`;

CREATE TABLE `candidate_yWing` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table hardcoded
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hardcoded`;

CREATE TABLE `hardcoded` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL DEFAULT '',
  `puzzle` char(81) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table simple
# ------------------------------------------------------------

DROP TABLE IF EXISTS `simple`;

CREATE TABLE `simple` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table simple_all
# ------------------------------------------------------------

DROP TABLE IF EXISTS `simple_all`;

CREATE TABLE `simple_all` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table simple_hidden
# ------------------------------------------------------------

DROP TABLE IF EXISTS `simple_hidden`;

CREATE TABLE `simple_hidden` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `count` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table simple_naked
# ------------------------------------------------------------

DROP TABLE IF EXISTS `simple_naked`;

CREATE TABLE `simple_naked` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table simple_omission
# ------------------------------------------------------------

DROP TABLE IF EXISTS `simple_omission`;

CREATE TABLE `simple_omission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tables
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tables`;

CREATE TABLE `tables` (
  `tableCount` int(10) unsigned NOT NULL DEFAULT '0',
  `puzzleCount` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table unsolvable
# ------------------------------------------------------------

DROP TABLE IF EXISTS `unsolvable`;

CREATE TABLE `unsolvable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `puzzle_id` int(10) unsigned NOT NULL,
  `table` varchar(10) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
