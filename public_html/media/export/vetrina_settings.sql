-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS vetrina_settings;
CREATE TABLE `vetrina_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gruppo` varchar(255) CHARACTER SET utf8 NOT NULL,
  `dimensioni` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `vetrina_settings` VALUES ("1","default","1280x640","2016-05-24 10:51:06","2016-06-13 14:32:58");