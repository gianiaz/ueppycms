<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (06/11/2015, 15.32)                                                                   **/
/** - Aggiunto namespace per autoloading.                                                        **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;
class Gruppo extends Dba {

  function save($opts = null) {

    if(!$this->fields['id']) {
      $this->setOrdine();
    }

    return parent::save($opts);
  }

  function setOrdine() {

    $query = 'SELECT MAX(ordine)+1 FROM '.$this->dataDescription['table'];

    $res = $this->doQuery($query);

    if(mysqli_num_rows($res)) {
      $row                    = mysqli_fetch_row($res);
      $this->fields['ordine'] = $row[0];
    } else {
      $this->fields['ordine'] = 0;

    }

  }

}