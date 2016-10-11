-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS emails;
CREATE TABLE `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) CHARACTER SET utf8 NOT NULL,
  `descrizione` text COLLATE utf8_bin NOT NULL,
  `chiavi` varchar(255) COLLATE utf8_bin NOT NULL,
  `oggetto` varchar(255) CHARACTER SET utf8 NOT NULL,
  `testo` text CHARACTER SET utf8 NOT NULL,
  `superadmin` tinyint(4) NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `emails` VALUES ("5","MAIL_DI_CONTATTO","Email che arriva alla compilazione di un form di contatto generico","DATA,CODICE_RICHIESTA","{CODICE_RICHIESTA} Richiesta informazioni","<table style=\"width: 100%; height: 100%;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#f9f9f9\">\r\n<tbody>\r\n<tr>\r\n<td style=\"background-color: #f9f9f9;\" align=\"center\" valign=\"top\" bgcolor=\"#f9f9f9\">\r\n<table class=\"container\" style=\"width: 600px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\">\r\n<tbody>\r\n<tr>\r\n<td class=\"container-padding\" style=\"background-color: #ffffff; padding-left: 30px; padding-right: 30px; padding-bottom: 20px; font-size: 14px; line-height: 20px; font-family: Helvetica, sans-serif; color: #333;\" bgcolor=\"#ffffff\"><img src=\"/images/site/header-email.png\" alt=\"Ueppy\" /></td>\r\n</tr>\r\n<tr>\r\n<td class=\"container-padding\" style=\"background-color: #ffffff; padding-left: 30px; padding-right: 30px; font-size: 14px; line-height: 20px; font-family: Helvetica, sans-serif; color: #333;\" bgcolor=\"#ffffff\">\r\n<div style=\"font-weight: bold; font-size: 18px; line-height: 24px; color: #337ab7;\">Mail dal form dei contatti</div>\r\n<p>Comunicazione compilata attraverso il form dei contatti presenti sul sito.</p>\r\n<p>Dati inseriti:</p>\r\n<p>{DATA}</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>","0","2016-07-16 14:16:19","0000-00-00 00:00:00"), ("19","BACKUP","Modulo con il quale vengono inviati i backup via mail","NOME_BCK","Nuovo backup {NOME_BCK}","<table style=\"width: 100%; height: 100%;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ebebeb\">\r\n<tbody>\r\n<tr>\r\n<td style=\"background-color: #f9f9f9;\" align=\"center\" valign=\"top\" bgcolor=\"#ebebeb\">\r\n<table class=\"container\" style=\"width: 600px;\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"#ffffff\">\r\n<tbody>\r\n<tr>\r\n<td class=\"container-padding\" style=\"background-color: #ffffff; padding-left: 30px; padding-right: 30px; padding-bottom: 20px; font-size: 14px; line-height: 20px; font-family: Arial, Bitstream Vera Sans, Helvetica, Verdana, sans-serif; color: #333;\" bgcolor=\"#ffffff\"><img src=\"{HOST}images/site/header-email.png\" alt=\"Ueppy\" width=\"301\" height=\"150\" /></td>\r\n</tr>\r\n<tr>\r\n<td class=\"container-padding\" style=\"background-color: #ffffff; padding-left: 30px; padding-right: 30px; font-size: 14px; line-height: 20px; font-family: Arial, Bitstream Vera Sans, Helvetica, Verdana, sans-serif; color: #333;\" bgcolor=\"#ffffff\">\r\n<div style=\"font-weight: bold; font-size: 18px; line-height: 24px; color: #d03c0f;\">Nuovo backup</div>\r\n<p>Un nuoco backup &egrave; stato appena eseguito, in allegato il file di archivio.</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>","0","2016-05-24 17:50:24","2016-05-24 17:47:35");