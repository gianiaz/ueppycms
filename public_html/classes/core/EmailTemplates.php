<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (09/11/2015, 15.11)                                                                   **/
/** - Aggiunto namespace e autoloading                                                           **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;

use Ueppy\core\Dba;

class EmailTemplates extends Dba {

  function __construct($opts) {

    parent::__construct($opts);

    $this->keys       = '';
    $this->superadmin = 0;
  }

  /**
   * @param type $key
   * @return type Object
   */

  public function getByKey($key = null) {

    if($key) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'nome';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = addslashes($key);

      $filters[] = $filter_record;

      $opts              = [];
      $opts['start']     = '0';
      $opts['quanti']    = '1';
      $opts['filters']   = $filters;
      $opts['operatore'] = 'AND';

      $list = $this->getlist($opts);

      if($list) {
        return $list[0];
      } else {
        return false;
      }

    } else {

      $this->log('Chiave email non fornita', ['level' => 'error', 'dieAfterError' => true]);

    }

  }

}