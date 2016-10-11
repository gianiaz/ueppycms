-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS allegati_langs;
CREATE TABLE `allegati_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `allegati_id` int(11) NOT NULL,
  `descrizione` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `title` varchar(255) COLLATE utf8_bin NOT NULL,
  `alt` varchar(255) COLLATE utf8_bin NOT NULL,
  `lingua` char(2) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `allegati_id` (`allegati_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=320 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `allegati_langs` VALUES ;