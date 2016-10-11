<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 6.31)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\utils\Utility;

class ViewManager {

  protected $route;
  public    $controller;

  function __construct(Route $route, UeppySmarty $smarty) {

    // controller del cms
    $controllerName = 'Ueppy\\front\\'.ucfirst($route->cmd).'Controller';

    // verifica dell'esistenza del controller che potrebbe estendere quello del cms
    $controllerExtension = 'Ueppy\\controllers\\'.$route->cmd.'\\'.ucfirst($route->cmd).'Controller';
    if(class_exists($controllerExtension)) {
      $controllerName = $controllerExtension;
    }

    if(class_exists($controllerName)) {
      $this->controller = new $controllerName($route);
    } else {
      throw new \Exception($controllerName.' non esiste');
    }

  }
  
}