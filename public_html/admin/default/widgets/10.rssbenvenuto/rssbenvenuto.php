<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (05/07/16, 8.35)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;
use Ueppy\blog\News;

if(!function_exists('rssBenvenutoWidget')) {
  function rssBenvenutoWidget($debug = 0) {

    // Config
    $limit         = 10; // limita il numero di news
    $cacheLifeTime = 2; // ogni quanto rinnovare la cache

    $return           = [];
    $return['widget'] = 'rss';
    $return['icon']   = ' fa-rss';
    $return['size']   = 12;
    $return['conta']  = 0;
    $return['title']  = Traduzioni::getLang('rssbenvenuto', 'TITLE_WIDGET');

    $url = 'http://www.ueppy.com/benvenuto.xml';

    if($url) {

      $GLOBALS['pathJS'][] = DOC_ROOT.REL_ROOT.'bower_components/jquery.scrollTo/jquery.scrollTo.min.js';

      $cacheFile = DOC_ROOT.REL_ROOT.UPLOAD.'cache/feed-ueppycms-benvenuto.xml';

      if(!file_exists($cacheFile) || filemtime($cacheFile) < time() - $cacheLifeTime) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);

        file_put_contents($cacheFile, $response);

      }

      $rss = simplexml_load_file($cacheFile, 'SimpleXMLElement', LIBXML_NOCDATA);

      $return['items'] = [];

      if($rss) {

        //Utility::pre($rss->channel);

        $iterator = 1;

        foreach($rss->channel->item as $k => $item) {

          $record           = [];
          $record['titolo'] = (string)$item->title;
          $record['autore'] = (string)$item->children('dc', true)->creator;
          $record['url']    = (string)$item->link;
          $record['data']   = date('d/m/Y', strtotime((string)$item->pubDate));
          $description      = (string)$item->description;

          $record['descrizione'] = strip_tags($description);

          $return['items'][] = $record;

          if($iterator == $limit) {
            break;
          }

          $iterator++;

        }

      }

    }

    if($debug) {
      Utility::pre($return);
    }

    return $return;

  }

}

$widgetsData['rssbenvenuto'] = rssBenvenutoWidget();