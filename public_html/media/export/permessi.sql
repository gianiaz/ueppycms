-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS permessi;
CREATE TABLE `permessi` (
  `gruppi_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  UNIQUE KEY `gruppi_id` (`gruppi_id`,`menu_id`),
  KEY `id_gruppo` (`gruppi_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `permessi` VALUES ("3","1"), ("3","6"), ("3","8"), ("3","24"), ("3","25"), ("3","83"), ("3","88"), ("3","202"), ("3","205"), ("3","221"), ("3","229");