<?php
/*****************/
/***ueppy3.1.01***/
/*****************/
/**  CHANGELOG  **/
/*************************************************************/
/** v.3.1.01                                                **/
/** - Bugfix, il metodo save non ritornava il risultato     **/
/**                                                         **/
/** v.3.1.00                                                **/
/** - Versione stabile                                      **/
/**                                                         **/
/*************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **/
/** copyright: Ueppy s.r.l                                  **/
/*************************************************************/
namespace Ueppy\core;

class Vetrina extends Dba {

  function __construct($opts) {

    parent::__construct($opts);

    $this->id     = 0;
    $this->ordine = 0;
    foreach($this->opts['langs'] as $l) {
      $this->fields[$l]['img_alt'] = '';
    }

  }

  function setOrdine() {

    if(!isset($this->fields['ordine'])) {

      $sql = 'SELECT MAX(ordine)+1 FROM '.$this->dataDescription['table'].' WHERE gruppo = "'.$this->fields['gruppo'].'"';

      $res = $this->doQuery($sql);

      if(mysqli_num_rows($res)) {

        $row = mysqli_fetch_row($res);

        $this->ordine = intval($row[0]);

        return;

      }

      $this->ordine = 0;

      return;

    }

  }

  function save($opts = null) {

    $this->setOrdine();

    return parent::save($opts);

  }

}

?>
