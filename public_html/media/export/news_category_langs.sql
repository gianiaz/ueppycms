-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS news_category_langs;
CREATE TABLE `news_category_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_category_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `href` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `htmltitle` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `testo` text COLLATE utf8_bin NOT NULL,
  `description` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `lingua` char(2) COLLATE utf8_bin NOT NULL DEFAULT 'it',
  PRIMARY KEY (`id`),
  KEY `news_cat_id` (`news_category_id`),
  KEY `news_category_id` (`news_category_id`),
  KEY `lingua` (`lingua`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `news_category_langs` VALUES ;