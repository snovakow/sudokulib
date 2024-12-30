# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.7.32)
# Database: sudoku
# Generation Time: 2024-12-30 23:15:15 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table hardcoded
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hardcoded`;

CREATE TABLE `hardcoded` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL DEFAULT '',
  `puzzle` char(81) CHARACTER SET ascii NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `hardcoded` WRITE;
/*!40000 ALTER TABLE `hardcoded` DISABLE KEYS */;

INSERT INTO `hardcoded` (`id`, `title`, `puzzle`)
VALUES
	(1,'v=bnPmmAeb-SI','090004085010080900002390040000009008500030096900800000040008200003040010600703050'),
	(2,'v=ynkkMxQPUpk','005020600090004010200500003006030000000801000000090400300002007010900050004060800'),
	(3,'v=Ui1hrp7rovw','000102000060000070008000900400000003050007000200080001009000805070000060000304000'),
	(4,'v=fjWOgJqRWZI','000000000020900380030100750048020000050006000760500410400003000200845670075200000'),
	(5,'v=BjOtNij7C84','005000200090060080803000109000309000040000030000704000207000605050010020009000800'),
	(6,'Unsolvable 507','030002000025100300070900000006010008080500040000700000001003400200000701050000090'),
	(7,'Unsolvable 508','005000004070000280003002900920006300004209000030500000001600000700300106052000000'),
	(8,'Unsolvable 509','270900000000070003800063040001000000000030050020009460102000900600000000040500020'),
	(9,'Unsolvable 510','309000401010000000000801000000005300800102009006700800000904000050000070702000903'),
	(10,'Unsolvable 511','000060010450000308007400900002030000300705000000040600004009700709000082010000000'),
	(11,'Unsolvable 512','020000700009000005810000030000010600700008000050900020006007004000500090100030800'),
	(12,'Unsolvable 513','500800000004009100020070003008200700300000009010004060007030604000000050900006000'),
	(13,'Unsolvable 514','030010009006000500100000040400003200090070008005600000800002003000090070000400100'),
	(14,'Unsolvable 515','007008000600300000010090008300600007040050900008000020090007100000040006200000050'),
	(15,'Unsolvable 516','000002010090400000700050003010800400005070000600000002040001006000900830003000050'),
	(16,'Unsolvable 600','007060500000001007040000090005070900060000400900800003100000002000900060230040000'),
	(17,'Unsolvable 601','024001030800000700000050000000000008090400020018003400000000080003005100700060009'),
	(18,'Unsolvable 602','700400800005060000020001000000008001400700030009000500001020009080000700600000040'),
	(19,'Unsolvable 603','140090050002700001800006000000002300000050090000400007060800070003001002900000400'),
	(20,'Unsolvable 604','000006004500200030001000000000900200030004080007010006080150000400003009002000700'),
	(21,'Unsolvable 605','000006040000070200000800005500020800007100006030009000002050007060300100900004000'),
	(22,'Unsolvable 606','000080400500003020010700000060420009800050030007001000002000800400090050030000006'),
	(23,'Snake','607901300903070000050030000000120680002589000500000000300007906000060400700300800'),
	(24,'v=FVvL0Wk1y5o','000200001007000094060500000009000000000609000000000300000001070230000600800004000'),
	(25,'v=m9Xaa4GXs9I','605004790080000064070900508904000086700000005058000000006008007000150000000020040');

/*!40000 ALTER TABLE `hardcoded` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
