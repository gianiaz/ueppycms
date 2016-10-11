-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS commenti_pagine;
CREATE TABLE `commenti_pagine` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `commento` text CHARACTER SET utf8 NOT NULL,
  `nome` varchar(255) CHARACTER SET utf8 NOT NULL,
  `email` varchar(255) CHARACTER SET utf8 NOT NULL,
  `valido` int(11) DEFAULT NULL,
  `ip` varchar(15) CHARACTER SET utf8 NOT NULL,
  `fb_data` text COLLATE utf8_bin NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `commenti_pagine` VALUES ;