-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS operatori;
CREATE TABLE `operatori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomecompleto` varchar(255) CHARACTER SET utf8 NOT NULL,
  `avatar` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `username` varchar(20) CHARACTER SET utf8 NOT NULL,
  `passwd` varchar(65) CHARACTER SET utf8 NOT NULL,
  `gruppi_id` int(11) NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `attivo` int(1) NOT NULL,
  `super_admin` int(1) NOT NULL,
  `cancellabile` int(1) NOT NULL,
  `level` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `operatori` VALUES ("1","Admin","","admin","$2a$08$Wh1GXzD656QAFgCwSBf3Vu.MVTmqiLlDUmcFmS3Y3qTIbox2ZrZf6","1","admin@ueppy.com","1","1","0","20","0000-00-00 00:00:00","2016-10-11 18:12:58"), ("11","Utente di Test","","operatore","$2a$08$5sLFQ4V39k0RwQ6XZekz6u/UWgVNSihq83huOr5XUqf77HS4BzPI6","3","test@ueppy.com","1","0","1","10","0000-00-00 00:00:00","2016-09-01 16:35:18");