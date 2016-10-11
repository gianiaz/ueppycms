-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS moduli_dinamici_langs;
CREATE TABLE `moduli_dinamici_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduli_dinamici_id` int(11) NOT NULL,
  `testo` text COLLATE utf8_bin NOT NULL,
  `lingua` char(2) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moduli_dinamici_id` (`moduli_dinamici_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `moduli_dinamici_langs` VALUES ;