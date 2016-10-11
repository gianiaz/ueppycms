-- File generato con php il 11-10-2016 18:13:07
DROP TABLE IF EXISTS pagine_langs;
CREATE TABLE `pagine_langs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pagine_id` int(11) NOT NULL,
  `sottotitolo` varchar(255) COLLATE utf8_bin NOT NULL,
  `testo` text COLLATE utf8_bin NOT NULL,
  `counter` int(11) NOT NULL DEFAULT '0',
  `lingua` char(2) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pagine_id` (`pagine_id`),
  KEY `lingua` (`lingua`),
  FULLTEXT KEY `testo` (`testo`)
) ENGINE=MyISAM AUTO_INCREMENT=67 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT IGNORE INTO `pagine_langs` VALUES ("66","305","La presentazione del nostro staff","<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla molestie laoreet augue eget lacinia. Phasellus interdum velit id finibus commodo. Aliquam sed ultricies quam. Mauris a augue vitae orci mattis tristique. Sed vel lacinia sem. Integer maximus pretium nunc sed sodales. Etiam vehicula sit amet risus id dignissim. Maecenas in sodales ligula. Integer lacus sem, condimentum quis maximus at, sodales ac nibh. Donec cursus a enim sit amet dignissim. Cras eleifend consectetur ex, nec fringilla mauris suscipit et. Duis at sodales diam, in rhoncus odio. Donec eu nibh in nisl consequat aliquet. Etiam magna sem, ornare non felis eget, dapibus varius felis.<br /><br />Quisque consequat sodales tellus rutrum mattis. Nullam id condimentum orci. Fusce et sodales sapien, sit amet semper metus. Donec libero purus, cursus consectetur neque quis, luctus varius nibh. In eu congue sapien. Maecenas tempus molestie lacus, non faucibus purus tincidunt eu. Morbi finibus elit lectus, eget fermentum enim blandit quis. Duis commodo tortor dolor, sit amet sagittis augue maximus eu. Fusce tincidunt, nulla nec finibus feugiat, mauris diam rutrum lacus, sit amet facilisis erat elit vel odio. Vestibulum laoreet suscipit erat, nec laoreet magna. In vel pulvinar turpis. Sed ac varius mi, id lobortis enim. Vivamus elit odio, ullamcorper eget mauris a, lobortis ullamcorper odio. Nunc fringilla eros in ultrices laoreet.<br /><br />Fusce tristique molestie nisl nec blandit. Quisque commodo nulla sit amet neque vestibulum eleifend. Nulla vulputate velit quis dolor imperdiet dapibus. Duis pellentesque, magna nec cursus egestas, turpis urna finibus purus, nec egestas ante lorem eget sem. Quisque ipsum orci, condimentum vitae sapien a, consectetur tempor purus. In vitae ipsum facilisis, tempor ligula vel, tincidunt sapien. Aliquam consectetur, lorem tempus varius ornare, ipsum odio vulputate est, quis dignissim urna erat et dolor. Proin ullamcorper lectus nec leo volutpat, quis laoreet nisi ornare. Cras eleifend at diam nec vestibulum. Ut sagittis elementum vehicula. Nullam tristique felis et orci tincidunt, eu congue felis rhoncus.<br /><br />Curabitur vel semper lacus. Curabitur malesuada, dui a eleifend fringilla, ante erat gravida tellus, nec viverra arcu ligula id felis. Integer sit amet dui in magna venenatis pulvinar in ut libero. Suspendisse in consequat ligula. Suspendisse nec eleifend felis, quis auctor ante. Integer porta commodo urna faucibus facilisis. In luctus porttitor nibh, quis tincidunt odio dignissim nec. Vivamus sit amet posuere tortor, in ullamcorper ante. Donec eget ante ut leo eleifend egestas. Sed egestas ac urna vel aliquet. Aliquam efficitur, lorem cursus feugiat accumsan, orci ante imperdiet eros, et varius felis arcu at nunc. Suspendisse efficitur sapien quis elementum elementum. Morbi pharetra enim vitae feugiat convallis.<br /><br />Integer dictum velit sed dolor porta, sit amet ultrices dui malesuada. Ut eget lacus viverra, tristique dolor vitae, lacinia ligula. Ut pharetra nunc ligula, et mollis purus dapibus vel. Sed dui ipsum, tincidunt aliquet nisl id, iaculis fermentum velit. Sed eleifend massa non est rhoncus placerat. Integer rhoncus sapien quis sem maximus, at sollicitudin justo vehicula. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Morbi augue dolor, feugiat quis leo viverra, ornare venenatis lorem. Suspendisse efficitur ornare porttitor. Nam eget purus est. Suspendisse ac tellus ut lacus convallis aliquet a facilisis ex. Donec sagittis orci eget leo fermentum varius. Vestibulum sit amet tellus accumsan, accumsan dolor rutrum, malesuada tellus.</p>","0","it");