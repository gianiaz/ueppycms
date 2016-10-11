-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS languages;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attivo_admin` int(11) NOT NULL,
  `sigla` varchar(3) CHARACTER SET utf8 DEFAULT NULL,
  `estesa` varchar(255) CHARACTER SET utf8 NOT NULL,
  `img0` varchar(255) CHARACTER SET utf8 NOT NULL,
  `img0_alt` varchar(255) CHARACTER SET utf8 NOT NULL,
  `img0_title` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `languages` VALUES ("1","1","it","Italiano","italy.png","","0");