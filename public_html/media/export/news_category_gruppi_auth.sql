-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS news_category_gruppi_auth;
CREATE TABLE `news_category_gruppi_auth` (
  `id_gruppo` int(11) NOT NULL,
  `news_category_id` int(11) NOT NULL,
  KEY `id_gruppo` (`id_gruppo`,`news_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `news_category_gruppi_auth` VALUES ;