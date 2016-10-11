-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS traduzioni;
CREATE TABLE `traduzioni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chiave` varchar(50) CHARACTER SET utf8 NOT NULL,
  `modulo` varchar(255) CHARACTER SET utf8 NOT NULL,
  `linguaggio` enum('php','javascript') CHARACTER SET utf8 NOT NULL,
  `sezione` enum('public','admin') CHARACTER SET utf8 NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chiave` (`chiave`,`modulo`,`linguaggio`,`sezione`)
) ENGINE=MyISAM AUTO_INCREMENT=7366 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `traduzioni` VALUES ;