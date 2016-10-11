<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (26/05/16, 10.23)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\core;

use Ueppy\core\Dba;
use Ueppy\utils\Utility;

class FreeMenu extends Dba {

  function fillresults() {

    $list = parent::fillresults();

    if(!$list) return false;

    if($this->getById) {
      $list = [$list];
    }

    $arr = [];

    foreach($list as $obj) {

      foreach($obj->opts['langs'] as $l) {
        if(isset($obj->fields[$l])) {
          $links          = json_decode($obj->fields[$l]['dati'], true);
          $linksElaborati = [];

          foreach($links as $link) {
            $linkElaborato             = [];
            $linkElaborato['url']      = $link[0];
            $linkElaborato['label']    = $link[1];
            $linkElaborato['nofollow'] = $link[2];
            $linkElaborato['blank']    = $link[3];

            $linksElaborati[] = $linkElaborato;
          }

          $obj->additionalData['links'][$l] = $linksElaborati;

        }
      }

      $arr[] = $obj;

    }

    if($this->getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }

  function createMarkup($opts = null) {

    $opzioni          = [];
    $opzioni['lang']  = ACTUAL_LANGUAGE;
    $opzioni['debug'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $options                  = [];
    $options['tableFilename'] = 'freemenu_style';

    $stile = new FreemenuStyle($options);
    $stile = $stile->getById($this->fields['freemenu_styles_id']);

    $s = new \Smarty();
    $s->assign('titolo', $this->fields[$opzioni['lang']]['titolo']);
    $s->assign('menu', $this->additionalData['links'][$opzioni['lang']]);
    $tpl  = $stile->getTpl();
    $html = '[errore]';
    if($tpl) {
      $html = $s->fetch($tpl);
    }

    return $html;

  }

  function delete($opts = null) {

    parent::delete($opts);
  }

}

?>