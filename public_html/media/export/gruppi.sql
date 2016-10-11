-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS gruppi;
CREATE TABLE `gruppi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) CHARACTER SET utf8 NOT NULL,
  `attivo` int(1) NOT NULL DEFAULT '1',
  `all_elements` int(1) DEFAULT NULL,
  `cancellabile` int(1) NOT NULL DEFAULT '1',
  `ordine` int(1) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `gruppi` VALUES ("1","Amministrazione Sito","1","1","0","0","0000-00-00 00:00:00","2016-05-19 16:36:22"), ("3","Gestione","1","0","1","1","0000-00-00 00:00:00","2016-06-06 09:09:38");