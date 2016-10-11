-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS backup;
CREATE TABLE `backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) COLLATE utf8_bin NOT NULL,
  `tabelle` text COLLATE utf8_bin NOT NULL,
  `directories` text COLLATE utf8_bin NOT NULL,
  `cron_h` varchar(255) COLLATE utf8_bin NOT NULL,
  `cron_d` varchar(255) COLLATE utf8_bin NOT NULL,
  `cron_dom` varchar(255) COLLATE utf8_bin NOT NULL,
  `cron_dow` varchar(255) COLLATE utf8_bin NOT NULL,
  `ftp` int(11) NOT NULL,
  `email` int(11) NOT NULL,
  `cron` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `backup` VALUES ;