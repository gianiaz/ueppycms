-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS freemenu_styles;
CREATE TABLE `freemenu_styles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8 NOT NULL,
  `markup` text CHARACTER SET utf8 NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `freemenu_styles` VALUES ;