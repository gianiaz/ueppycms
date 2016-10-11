-- File generato con php il 11-10-2016 17:22:18
DROP TABLE IF EXISTS gruppi_settaggi;
CREATE TABLE `gruppi_settaggi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome_gruppo` varchar(255) COLLATE utf8_bin NOT NULL,
  `descrizione` varchar(255) COLLATE utf8_bin NOT NULL,
  `ordine` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `gruppi_settaggi` VALUES ("1","News","impostazioni relative alla sezione news","50"), ("2","Settaggi generali","impostazioni globali che coinvolgono il sito a livello globale","0"), ("3","Meta tags","Impostazioni di default relative ai meta tag della pagina","20"), ("4","Avanzate","Impostazioni avanzate che hanno effetto globale sul sito","10"), ("5","Emails","Impostazioni relative all\'invio e ricezioni delle email generate dal sito","30"), ("6","Photo Gallery","Impostazioni relative alla sezione delle gallery del sito","70"), ("7","Pagine","Impostazioni relative alla gestione delle pagine del sito","40"), ("8","Filemanager","Impostazioni relative al modulo filemanager","90"), ("9","Prodotti","Impostazioni relative alla gestione dei prodotti per cataloghi ed ecommerce","80"), ("10","Ecommerce","Impostazioni legate alla vendita dei prodotti","90"), ("11","Strutture ricettive","Impostazioni relative alla gestione di strutture ricettive quali hotels ecc","100"), ("12","Cache","Impostazioni relative alla gestione della cache del cms","15"), ("13","Google","Tutti i settaggi relativi ai servizi google","5"), ("14","Esecuzioni periodiche","Configurazione di tutti gli aspetti delle operazioni pianificate e periodiche","2"), ("15","Eventi","Impostazioni relative agli eventi","60"), ("16","Social Networks","Dati relativi agli account dei social networks","7"), ("17","Offerte","Impostazioni relative al modulo offerte","42"), ("18","Privacy Policy e Cookie Solution","Dati relativi alle leggi sulla privacy","150");