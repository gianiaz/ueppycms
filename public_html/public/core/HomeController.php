<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 9.10)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use Ueppy\core\Ueppy;
use Ueppy\utils\Utility;

class HomeController extends MainController {

  function outDefault() {

    /*
    $out           = [];
    $out['SMARTY'] = 'Dati che userai in smarty';
    $out['OBJ']    = 'Oggetto principale';
    $out['data']   = 'Dati vari strutturati come vuoi';
    */

    $options                  = [];
    $options['tableFilename'] = 'homeblocks';
    $options['debug']         = 0;

    $HomeBlockObj = new \Ueppy\core\HomeBlock($options);

    $HomeBlockObjList = $HomeBlockObj->getlist();

    $home = [];

    foreach($HomeBlockObjList as $HomeBlockObj) {

      $home[$HomeBlockObj->fields['htmlid']] = $HomeBlockObj->fields[ACTUAL_LANGUAGE]['testo'];

    }

    $out           = [];
    $out['SMARTY'] = $home;

    return $out;
    
  }
}
