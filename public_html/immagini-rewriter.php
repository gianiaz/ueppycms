<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (23/07/2016, 15.41)                                                                   **/
/** - Bugfix nell'inclusione del file di configurazione                                          **/
/**                                                                                              **/
/** v.1.00 (06/05/16, 10.17)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
$debug = false;
require("conf/boot.php");
require('vendor/autoload.php');
$cacheFile = DOC_ROOT.REL_ROOT.UPLOAD.'cache/rewrite.json';
$_404      = false;
if(file_exists($cacheFile)) {

  $urls = json_decode(file_get_contents($cacheFile), true);

  if(isset($urls['/immagini/'.$_GET['req']])) {

    $percorso = DOC_ROOT.REL_ROOT.$urls['/immagini/'.$_GET['req']];

    if(file_exists($percorso)) {

      $estensione = \Ueppy\utils\Utility::getEstensione($percorso);

      switch($estensione) {
        case 'jpg':
          Header('Content-type:image/jpeg');
          break;
        case 'png':
          Header('Content-type:image/png');
          break;
      }

      echo file_get_contents($percorso);

    } else {
      $_404 = 'Il file non esiste più';
    }

  } else {
    $_404 = 'Url non mappato';
  }

} else {
  $_404 = 'Non trovo il file di cache';
}

if($_404) {

  if($debug) {
    \Ueppy\utils\Utility::pre($_404);
  } else {

    define('ACTUAL_LANGUAGE', 'it');

    $lm = \Ueppy\core\LinkManager::getInstance();

    Header('Location:'.$lm->get('cmd/notFound'));

  }
}

