-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS homeblocks_langs;
CREATE TABLE `homeblocks_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `homeblocks_id` int(11) NOT NULL,
  `testo` text COLLATE utf8_bin NOT NULL,
  `lingua` char(2) COLLATE utf8_bin NOT NULL DEFAULT 'it',
  PRIMARY KEY (`id`),
  KEY `homeblocks_id` (`homeblocks_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `homeblocks_langs` VALUES ("31","16","","it");