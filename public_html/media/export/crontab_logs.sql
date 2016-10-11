-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS crontab_logs;
CREATE TABLE `crontab_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` text CHARACTER SET utf8 NOT NULL,
  `autore` varchar(100) CHARACTER SET utf8 NOT NULL,
  `text` text CHARACTER SET utf8 NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=106 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `crontab_logs` VALUES ;