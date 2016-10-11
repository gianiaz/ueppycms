-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS vetrina;
CREATE TABLE `vetrina` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8 NOT NULL,
  `ordine` int(11) DEFAULT '0',
  `attivo` int(11) DEFAULT '0',
  `gruppo` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `vetrina` VALUES ("9","CMS","0","1","default","2016-10-11 16:32:40","2016-10-11 16:34:42");