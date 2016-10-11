<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (10/06/16, 9.46)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
Namespace Ueppy\core;

use Ueppy\core\Dba;

class ModuloDinamico extends Dba {

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

  function cancellabile() {

    $sql = 'SELECT count(id) FROM modules WHERE modulo = "\_DYN\_" AND view = "'.$this->realEscape($this->fields['nome']).'"';

    $res = $this->doQuery($sql);

    $row = mysqli_fetch_row($res);

    return !(bool)$row[0];

  }


}