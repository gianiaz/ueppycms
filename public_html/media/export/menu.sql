-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS menu;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomefile` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `attivo` tinyint(1) DEFAULT '0',
  `eliminato` int(11) NOT NULL,
  `ordine` tinyint(3) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned DEFAULT NULL,
  `is_category` tinyint(1) NOT NULL,
  `template` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `parent` int(11) DEFAULT '0',
  `superadmin` tinyint(1) DEFAULT '0',
  `pubdate` date NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=308 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `menu` VALUES ("18","template","1","0","16","10","0","default","150","0","2012-08-14","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("150","-","1","0","1","0","0","0","0","0","2012-11-09","0000-00-00 00:00:00","2016-08-05 15:06:34"), ("202","menupubblico","1","0","7","10","0","default","142","0","2012-11-12","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("23","freemenu","1","0","4","60","0","default","140","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("24","vetrina","1","0","1","50","0","default","142","0","2012-06-28","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("25","settaggi","1","0","0","10","0","default","150","0","2012-07-05","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("46","home","1","0","0","50","0","default","142","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("88","logs","1","0","2","20","0","0","140","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("131","tinymcetpl","1","0","17","50","0","default","150","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("140","-","1","0","0","0","0","0","0","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:34"), ("155","backup","1","0","7","30","0","0","140","0","2012-03-26","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("142","-","1","0","2","0","0","default","0","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:34"), ("9","languages","1","0","1","12","0","0","150","1","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("3","gruppi","1","0","1","45","0","default","140","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("6","pagine","1","0","5","40","0","default","142","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("2","operatori","1","0","0","40","0","default","140","0","0000-00-00","0000-00-00 00:00:00","2016-08-08 15:50:05"), ("8","mails","1","0","10","30","0","default","150","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("7","menu","1","0","3","11","0","default","140","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("5","traduzioni","1","0","2","10","0","default","150","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("210","seo","1","0","8","10","0","0","142","0","2012-12-06","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("259","fm","1","0","15","50","0","0","150","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("266","schedule","1","0","5","30","0","default","140","0","2016-05-21","2016-05-21 15:08:29","2016-08-05 15:06:33"), ("11","scss","1","0","18","80","0","0","150","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("254","js","1","0","19","80","0","0","150","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("304","home","1","0","0","100","0","home","0","0","2016-10-11","2016-10-11 16:15:42","2016-10-11 16:22:33"), ("305","pagina","1","0","0","100","0","default","0","0","2016-10-11","2016-10-11 16:23:00","2016-10-11 16:46:02"), ("306","news","1","0","0","100","0","default","0","0","2016-10-11","2016-10-11 16:26:46","2016-10-11 16:28:25"), ("1","news","1","0","2","35","0","default","142","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("83","news_category","1","0","3","30","0","default","142","0","0000-00-00","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("204","tags","1","0","4","38","0","0","142","0","2012-11-12","0000-00-00 00:00:00","2016-08-05 15:06:33"), ("307","profilo","1","0","0","50","0","default","0","0","2016-10-11","2016-10-11 17:20:33","2016-10-11 17:20:33");