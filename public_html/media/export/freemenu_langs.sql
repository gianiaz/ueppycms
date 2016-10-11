-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS freemenu_langs;
CREATE TABLE `freemenu_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `freemenu_id` int(11) NOT NULL,
  `titolo` varchar(255) COLLATE utf8_bin NOT NULL,
  `dati` text COLLATE utf8_bin NOT NULL,
  `lingua` char(2) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `freemenu_id` (`freemenu_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `freemenu_langs` VALUES ;