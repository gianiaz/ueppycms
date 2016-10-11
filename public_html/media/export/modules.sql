-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS modules;
CREATE TABLE `modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modulo` varchar(20) CHARACTER SET utf8 NOT NULL,
  `istanza` varchar(20) COLLATE utf8_bin NOT NULL,
  `principale` int(1) NOT NULL DEFAULT '0',
  `view` varchar(50) CHARACTER SET utf8 NOT NULL,
  `posizione` varchar(50) CHARACTER SET utf8 NOT NULL,
  `template` varchar(50) CHARACTER SET utf8 NOT NULL,
  `ordine` int(1) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=330 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `modules` VALUES ("325","menuAuto","1","0","menuAuto","Menu","header_home.tpl","0","2016-10-11 16:27:14","2016-10-11 16:27:14"), ("326","main","0","1","","Home","home.tpl","0","2016-10-11 16:34:35","2016-10-11 16:34:35"), ("327","vetrina","1","1","vetrina_statica","Home","home.tpl","1","2016-10-11 16:34:35","2016-10-11 16:34:35"), ("328","socials","1","0","socials-default","Area Social Media","footer.tpl","0","2016-10-11 16:38:44","2016-10-11 16:38:44"), ("329","menuAuto","2","0","menuAuto","Menu","header.tpl","0","2016-10-11 16:47:31","2016-10-11 16:47:31");