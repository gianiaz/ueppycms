<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (16/06/16, 11.37)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use Ueppy\utils\Utility;

class AjaxController extends MainController {

  function outDefault() {

    $out = [];

    if($this->route->getParam('module')) {

      $filename = AJAX.strtolower($this->route->getParam('module')).'.php';

      if(file_exists($filename)) {

        include($filename);

      } else {
        $out['result'] = 0;
        $out['error']  = 'Azione non trovata: '.$this->route->getParam('module');
      }

    }

    $this->ajaxResponse($out);
  }

}
