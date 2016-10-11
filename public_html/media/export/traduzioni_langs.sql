-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS traduzioni_langs;
CREATE TABLE `traduzioni_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `traduzioni_id` int(11) NOT NULL,
  `dicitura` text CHARACTER SET utf8 NOT NULL,
  `lingua` char(2) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `traduzioni_id_2` (`traduzioni_id`,`lingua`),
  KEY `traduzioni_id` (`traduzioni_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=34337 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `traduzioni_langs` VALUES ;