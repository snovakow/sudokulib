# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.7.32)
# Database: sudoku
# Generation Time: 2025-01-09 19:23:23 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table puzzles001
# ------------------------------------------------------------

DROP TABLE IF EXISTS `puzzles001`;

CREATE TABLE `puzzles001` (
  `id` int(10) unsigned NOT NULL,
  `puzzleData` binary(32) NOT NULL DEFAULT '00000000000000000000000000000000',
  `clueCount` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `solveType` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `hiddenSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissionSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked2Simple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked3Simple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `nakedSimple` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissionVisible` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked2Visible` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `nakedVisible` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `naked4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `hidden4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `omissions` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `uniqueRectangle` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `yWing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `xyzWing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `xWing` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `swordfish` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `jellyfish` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superRank` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superSize` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superType` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superDepth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `superCount` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii COLLATE=ascii_bin;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
