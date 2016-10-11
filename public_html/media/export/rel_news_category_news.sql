-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS rel_news_category_news;
CREATE TABLE `rel_news_category_news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_category_id` int(11) NOT NULL,
  `id_news` int(11) NOT NULL,
  `principale` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `rel_news_category_news` VALUES ;