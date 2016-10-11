-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS schedule;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `giorno` varchar(255) COLLATE utf8_bin NOT NULL,
  `ora` varchar(255) COLLATE utf8_bin NOT NULL,
  `minuto` varchar(255) COLLATE utf8_bin NOT NULL,
  `comando` varchar(255) CHARACTER SET utf8 NOT NULL,
  `attivo` int(1) NOT NULL,
  `created_at` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `updated_at` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `schedule` VALUES ("4","*","*","*","backup","0","2016-05-24 17:26:40","2016-07-16 14:12:03"), ("5","*","0","10","traduzioni-updater","0","2016-06-07 11:12:26","2016-07-16 14:12:05");