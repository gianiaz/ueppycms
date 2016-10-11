-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS news_category;
CREATE TABLE `news_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `attivo` int(1) DEFAULT '1',
  `predefinita` tinyint(1) NOT NULL,
  `template` varchar(30) CHARACTER SET utf8 NOT NULL,
  `ordine` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `news_category` VALUES ;