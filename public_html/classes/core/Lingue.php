<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (06/11/2015, 15.10)                                                                   **/
/** - Aggiunto namespace per autoloading.                                                        **/
/**                                                                                              **/
/** v.1.01 (26/06/2013)                                                                          **/
/** - Tolta l'opzione che limitava i campi estratti, creava più casini che altro.                **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\core;
class Lingue extends Dba {

  function getBySigla($sigla, $debug = 0) {

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'sigla';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $sigla;

    $filters[] = $filter_record;

    $opts              = [];
    $opts['start']     = '0';
    $opts['quanti']    = '1';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $list = $this->getlist($opts);

    if(count($list)) {
      return $list[0];
    } else {
      return false;
    }

  }

}

?>