-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS rel_news_tags;
CREATE TABLE `rel_news_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `news_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `rel_news_tags` VALUES ;