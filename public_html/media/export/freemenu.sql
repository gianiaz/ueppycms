-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS freemenu;
CREATE TABLE `freemenu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `freemenu_styles_id` int(11) DEFAULT '0',
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `freemenu` VALUES ;