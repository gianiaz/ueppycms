-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS pagine_gruppi_auth;
CREATE TABLE `pagine_gruppi_auth` (
  `gruppi_id` int(11) NOT NULL,
  `menu_id` int(11) NOT NULL,
  KEY `id_gruppo` (`gruppi_id`,`menu_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `pagine_gruppi_auth` VALUES ("1","305");