<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (08/06/16, 11.41)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\front;

use Ueppy\core\Menu;
use Ueppy\utils\Utility;

class Widget {

  private $confDir;

  protected $config;

  protected $istanza;

  protected $MenuObj;

  protected $mainController;

  protected $out;

  function __construct($MenuObj, $istanza, $mainController) {

    $this->confDir = DOC_ROOT.REL_ROOT.UPLOAD.'widgets/';

    $this->istanza = $istanza;

    $this->loadConfig();

    $this->MenuObj = $MenuObj;

    $this->mainController = $mainController;

    $this->out = $this->out();

  }

  function getOutput() {

    return $this->out;
  }

  function loadConfig() {


    $class = get_class($this);

    $widgetDir = join('', array_slice(explode('\\', $class), -2, -1));

    $confFile = $this->confDir.$widgetDir.'-'.$this->istanza.'.json';

    if(file_exists($confFile)) {
      $this->config = json_decode(file_get_contents($confFile), true);
    } else {
      $this->loadDefault();
    }

  }

  function loadDefault() {

    $class = get_class($this);

    $widgetDir = join('', array_slice(explode('\\', $class), -2, -1));

    $percorso = DOC_ROOT.REL_ROOT.MODULES.$widgetDir.'/config.json';

    if(file_exists($percorso)) {

      $default = file_get_contents($percorso);

      $default = json_decode($default, true);

      foreach($default['generali'] as $chiave => $dati) {
        $this->config[$dati['var']] = $dati['default'];
      }
      foreach($default['multilingua'] as $chiave => $dati) {
        $this->config[$dati['var']] = $dati['default'];
      }

    }

  }

}