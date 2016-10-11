<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (17/06/16, 11.53)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use Ueppy\utils\Utility;

class NotFoundController extends MainController {

  function outDefault() {

    $out = [];

    Header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    Header("Status: 404 Not Found");

    return $out;
  }

}