-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS allegati;
CREATE TABLE `allegati` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomefile` varchar(255) COLLATE utf8_bin NOT NULL,
  `id_genitore` varchar(32) COLLATE utf8_bin NOT NULL,
  `genitore` varchar(50) COLLATE utf8_bin NOT NULL,
  `time` int(11) NOT NULL,
  `ordine` tinyint(4) NOT NULL,
  `estensione` varchar(4) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=219 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `allegati` VALUES ;