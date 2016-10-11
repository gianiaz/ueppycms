-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS backup_data;
CREATE TABLE `backup_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ftp_user` varchar(255) COLLATE utf8_bin NOT NULL,
  `ftp_pwd` varchar(255) COLLATE utf8_bin NOT NULL,
  `ftp_ip` varchar(255) COLLATE utf8_bin NOT NULL,
  `ftp_wd` varchar(255) COLLATE utf8_bin NOT NULL,
  `email` varchar(255) COLLATE utf8_bin NOT NULL,
  `profile_name` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `backup_data` VALUES ;