-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS news;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operatori_id` int(11) NOT NULL,
  `stato` enum('ATTIVO','SPENTA','SCHEDULATA','CANCELLATA') COLLATE utf8_bin NOT NULL DEFAULT 'SPENTA',
  `eliminato` tinyint(1) NOT NULL DEFAULT '0',
  `commenti` int(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `attiva_dal` datetime NOT NULL,
  `disattiva_dal` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `news` VALUES ("26","1","ATTIVO","0","1","2016-10-11 17:12:29","2016-10-11 17:12:29","2016-10-11 17:12:00","0000-00-00 00:00:00"), ("27","1","ATTIVO","0","1","2016-10-11 17:12:57","2016-10-11 17:12:57","2016-10-11 17:12:00","0000-00-00 00:00:00");