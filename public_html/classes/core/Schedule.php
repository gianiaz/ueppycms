<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (21/05/16, 15.03)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
Namespace Ueppy\core;

use Ueppy\core\Dba;
use Ueppy\utils\Utility;

class Schedule extends Dba {


  function __construct($opts = null) {

    parent::__construct($opts);

    $this->additionalData['giorno'] = 'TUTTI';
    $this->additionalData['giorni'] = [];

    $this->additionalData['ora'] = '0';
    $this->additionalData['ore'] = [0];

    $this->additionalData['minuto'] = '0';
    $this->additionalData['minuti'] = [0];
  }


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
      $list = [$list];
    }

    $arr = [];

    foreach($list as $obj) {

      $obj->additionalData['ore']    = [];
      $obj->additionalData['giorni'] = [];
      $obj->additionalData['minuti'] = [];

      $obj->additionalData['giorno'] = 'TUTTI';
      if($obj->fields['giorno'] != '*') {
        $obj->additionalData['giorni'] = explode(',', $obj->fields['giorno']);
        $obj->additionalData['giorno'] = '0';
      }

      $obj->additionalData['ora'] = 'TUTTE';
      if($obj->fields['ora'] != '*') {
        $obj->additionalData['ore'] = explode(',', $obj->fields['ora']);
        $obj->additionalData['ora'] = '0';
      }

      $obj->additionalData['minuto'] = 'TUTTI';
      if($obj->fields['minuto'] != '*') {
        $obj->additionalData['minuti'] = explode(',', $obj->fields['minuto']);
        $obj->additionalData['minuto'] = '0';
      }

      $arr[] = $obj;

    }

    if($this->getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }

  function getToRunNow() {

    $sql = 'SELECT comando from schedule WHERE attivo = 1 AND (FIND_IN_SET("'.date('j').'", giorno) > 0 || giorno = "*") AND (FIND_IN_SET("'.date('G').'", ora) > 0 || ora = "*")  AND (FIND_IN_SET("'.date('i').'", minuto) > 0 || minuto = "*")';

    $res = $this->doQuery($sql);

    $ret = [];

    while($row = mysqli_fetch_row($res)) {
      $ret[] = $row[0];
    }

    return $ret;
  }


  /*
  function save($opts = null) {
    return parent::save($opts);
  }
  */


}