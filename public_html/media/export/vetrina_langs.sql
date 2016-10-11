-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS vetrina_langs;
CREATE TABLE `vetrina_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vetrina_id` int(11) NOT NULL,
  `img` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `img_alt` varchar(255) COLLATE utf8_bin NOT NULL,
  `titolo` varchar(255) COLLATE utf8_bin NOT NULL,
  `sottotitolo` varchar(255) COLLATE utf8_bin NOT NULL,
  `testo` text COLLATE utf8_bin NOT NULL,
  `url` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `lingua` char(2) COLLATE utf8_bin NOT NULL DEFAULT 'it',
  PRIMARY KEY (`id`),
  KEY `vetrina_id` (`vetrina_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `vetrina_langs` VALUES ("19","9","","","Content Management System","","In informatica un content management system, in acronimo CMS (sistema di gestione dei contenuti in italiano), &egrave; uno strumento software, installato su un server web, il cui compito &egrave; facilitare la gestione dei contenuti di siti web, svincolando il webmaster da conoscenze tecniche specifiche di programmazione Web.","#","it");