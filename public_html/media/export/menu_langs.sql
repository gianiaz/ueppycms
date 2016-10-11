-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS menu_langs;
CREATE TABLE `menu_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `dicitura` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `titolo_breve` varchar(255) COLLATE utf8_bin NOT NULL,
  `htmltitle` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `keywords` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `description` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `href` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `img0` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `img0_alt` varchar(255) COLLATE utf8_bin NOT NULL,
  `img0_title` varchar(255) COLLATE utf8_bin NOT NULL,
  `img1` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
  `img1_alt` varchar(255) COLLATE utf8_bin NOT NULL,
  `img1_title` varchar(255) COLLATE utf8_bin NOT NULL,
  `lingua` char(2) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`),
  KEY `lingua` (`lingua`),
  FULLTEXT KEY `dicitura` (`dicitura`)
) ENGINE=MyISAM AUTO_INCREMENT=1101 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `menu_langs` VALUES ("105","2","Operatori","","Gestione Operatori","user","","gestione-operatori","","","","","","","it"), ("987","2","Gestione Operatori","","Gestione Operatori","","","gestione-operatori","","","","","","","en"), ("95","3","Gruppi","","Gestione Gruppi","users","","gestione-gruppi","","","","","","","it"), ("988","3","Gestione Gruppi","","Gestione Gruppi","","","gestione-gruppi","","","","","","","en"), ("108","5","Gestione Traduzioni","","Gestione Traduzioni","language","CTRL+ALT+R","gestione-traduzioni","","","","user_chat_01.png","","","it"), ("929","5","Translation","","Translation","","CTRL+ALT+T","translation","","","","","","","en"), ("99","6","Pagine","","Pagine","file-text","CTRL+ALT+P","pagine","","","","","","","it"), ("926","6","Pagine","","CTRL+ALT+P","","","pagine","","","","","","","en"), ("107","7","Menu Admin","","Menu Admin","bars","","menu-admin","","","","kmenuedit.png","","","it"), ("1050","7","","","","","","","","","","","","","en"), ("106","8","Modelli comunicazioni","","Modelli comunicazioni","envelope-o","","modelli-comunicazioni","","","","","","","it"), ("992","8","Modelli comunicazioni","","","","","modelli-comunicazioni-1","","","","","","","en"), ("92","9","Lingue","","Gestione Lingue","globe","","gestione-lingue","","","","","","","it"), ("984","9","Gestione Lingue","","Gestione Lingue","","","gestione-lingue","","","","","","","en"), ("968","11","Editor SCSS","","Editor CSS","css3","","editor-css","","","","","","","it"), ("969","11","Editor CSS","","Editor CSS","","","editor-css","","","","","","","en"), ("9","18","Template","","Gestione Template","puzzle-piece","CTRL+ALT+A","gestione-template","","","","path3333.png","","","it"), ("934","18","Template","","Gestione Template","","CTRL+ALT+A","gestione-template","","","","","","","en"), ("14","23","Menu Liberi","","Menu Liberi","th-list","","menu-liberi","","","","","","","it"), ("994","23","Menu liberi","","","","","menu-liberi","","","","","","","en"), ("15","24","Vetrina","","Vetrina","tv","CTRL+ALT+V","vetrina","","","","vetrina.png","","","it"), ("933","24","Vetrina","","Vetrina","tv","CTRL+ALT+V","vetrina","","","","","","","en"), ("16","25","Settaggi piattaforma","","Settaggi piattaforma","gear","CTRL+ALT+F","settaggi-piattaforma","","","","system_config_services.png","","","it"), ("935","25","Settings","","Settings","","CTRL+ALT+F","settings","","","","","","","en"), ("20","46","Gestione Home Page","","Gestione Home Page","home","CTRL+ALT+H","gestione-home-page","","","","home_alt.png","","","it"), ("932","46","Gestione Home Page","","Gestione Home Page","","CTRL+ALT+H","gestione-home-page","","","","","","","en"), ("30","88","Logs","","Logs","list","","logs","","","","","","","it"), ("985","88","Logs","","Logs","","","logs-1","","","","","","","en"), ("56","131","Template Tinymce","","Template Tinymce","wpforms","","template-tinymce","","","","","","","it"), ("993","131","Template Tinymce","","","wpforms","","template-tinymce","","","","","","","en"), ("57","140","Amministrazione","","Amministrazione","wrench","","amministrazione","","","","","","","it"), ("981","140","Administration","","Administration","","","administration-1","","","","","","","en"), ("67","142","Contenuti","","Contenuti","pencil","","contenuti","","","","","","","it"), ("1059","142","","","","","","-9","","","","","","","en"), ("853","150","Configurazione","","Configurazione","gears","","configurazione","","","","","","","it"), ("1053","150","","","","","","-3","","","","","","","en"), ("63","155","Backup Manager","","Backup Manager","life-ring","","backup-manager","","","","","","","it"), ("986","155","Backup Manager","","Backup Manager","","","backup-manager","","","","","","","en"), ("855","202","Menu Pubblico","","Menu Speciali","ellipsis-v","","menu-speciali","","","","","","","it"), ("996","202","Menu Speciali","","","","","menu-speciali","","","","","","","en"), ("873","210","Seo","","Seo","key","","seo","","","","1354029714_label_edit_32.png","","","it"), ("874","210","Seo","","Seo","","","seo","","","","","","","en"), ("972","254","Editor JS - (general.js)","","Editor JS - (general.js)","file-code-o","","editor-js-generaljs","","","","","","","it"), ("973","254","Editor JS - (general.js)","","Editor JS - (general.js)","","","editor-js-generaljs","","","","","","","en"), ("989","259","File Manager","","File Manager","briefcase","","file-manager","","","","","","","it"), ("990","259","File Manager","","File Manager","briefcase","","file-manager","","","","","","","en"), ("1009","266","Operazioni pianificate","","","clock-o","","operazioni-pianificate","","","","","","","it"), ("1010","266","Scheduled tasks","","","clock-o","","scheduled-tasks","","","","","","","en"), ("1097","304","Home","","","","","home","h1-1.jpg","","","h1-1.jpg","","","it"), ("1098","305","Chi siamo","","","","","chi-siamo","h2.jpg","","","","","","it"), ("1099","306","News","","","","","news","h3.jpg","","","","","","it"), ("101","1","News","","News","newspaper-o","CTRL+ALT+N","news","","","","","","","it"), ("930","1","News","","News","","","news","","","","","","","en"), ("25","83","Categorie News","","Categorie News","cubes","","categorie-news","","","","","","","it"), ("991","83","News categories","","News categories","","","news-categories","","","","","","","en"), ("859","204","Tags","","Tags","tags","","tags","","","","","","","it"), ("995","204","Tags","","","","","tags","","","","","","","en"), ("1100","307","Profilo","","","","","profilo","","","","","","","it");