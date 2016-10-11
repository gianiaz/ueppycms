<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 10.43)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
Namespace Ueppy\core;

use Ueppy\core\Dba;

class VetrinaSettings extends Dba {

  /*
  function __construct($opts = null) {
    parent::__construct($opts);
  }
  */

  /*
  function fillresults() {

    $list = parent::fillresults();

    if(!$list) {
      if($this->getById) {
        return false;
      } else {
        return [];
      }
    }

    if($this->getById) {
      $list = array($list);
    }

    $arr = array();

    foreach($list as $obj) {

      $arr[] = $obj;

    }

    if($this->getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }
  */

  /*
  function save($opts = null) {
    return parent::save($opts);
  }
  */

  function getByGruppo($gruppo) {

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'gruppo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $gruppo;

    $filters[] = $filter_record;

    $opzioni            = [];
    $opzioni['start']   = 0;
    $opzioni['quanti']  = 1;
    $opzioni['filters'] = $filters;

    $dimensioni = $this->getlist($opzioni);

    if($dimensioni) {
      return $dimensioni[0]->fields['dimensioni'];
    } else {
      $dimensioni = $this->getById(1);

      return $dimensioni->fields['dimensioni'];
    }

  }


}