<?php
/*****************/
/***ueppy3.1.00***/
/*****************/
/**  CHANGELOG  **/
/*************************************************************/
/** v.3.1.00                                                **/
/** - Versione stabile                                      **/
/**                                                         **/
/*************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com> **/
/** copyright: Ueppy s.r.l                                  **/
/*************************************************************/
namespace Ueppy\core;

class HomeBlock extends Dba {

  function checkBlocks($array = []) {

    if(count($array)) {

      $conta_blocchi_richiesti = count($array);

      $filters = [];

      $filter_record = [];

      $filter_record['chiave']    = 'htmlid';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $array;

      $filters[] = $filter_record;
      
      $opts = [];

      $opts['countOnly'] = true;
      $opts['filters']   = $filters;
      $opts['sortField'] = 'htmlid';
      $opts['sortOrder'] = 'ASC';
      $opts['operatore'] = 'AND';
      $opts['joins']     = [];

      $conta_blocchi_db = $this->getlist($opts);

      if($conta_blocchi_db != $conta_blocchi_richiesti) { // ce n'è meno

        foreach($array as $key) {
          $exists = $this->checkIfExists($key);
          if(!$exists) {
            $options                 = $this->opts;
            $options['forceAllLang'] = 1;
            $hb                      = new HomeBlock($options);
            $hb->htmlid              = $key;
            foreach($this->opts['langs'] as $l) {
              $hb->$l = ['testo', ''];
            }
            $hb->save();
          }
        }

      }

      $opts['countOnly'] = false;

      return $this->getlist($opts);

    }

  }


  function checkIfExists($key) {

    $query = "SELECT id FROM ".$this->dataDescription['table']." WHERE htmlid='".$key."' LIMIT 1";

    $result = $this->doQuery($query);

    return mysqli_num_rows($result);

  }

}

?>