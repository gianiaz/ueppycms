<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (09/11/2015, 14.43)                                                                   **/
/** - Aggiunto namespace e autoloading delle classi.                                             **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\blog;

use Ueppy\core\Dba;

class Tag extends Dba {

  function refreshCount() {

    $sql = 'SELECT count(id), tag_id FROM `rel_news_tags` GROUP BY tag_id';

    $res = $this->doQuery($sql);

    $ids = [];

    while($row = mysqli_fetch_row($res)) {

      $ids[] = $row[1];

      $sql = 'UPDATE '.$this->dataDescription['table'].' SET count = '.$row[0].' WHERE id = '.$row[1];

      $this->doQuery($sql);

    }

    // cancello i tag che non sono presenti sopra, perchè allora vuol dire che non sono utilizzati
    $sql = 'DELETE FROM '.$this->dataDescription['table'];

    if(count($ids)) {
      $sql .= ' WHERE id NOT IN ('.implode(',', $ids).')';
    }

    $this->doQuery($sql);


  }

  /**
   * $opts['tag']   = tag
   * $opts['lang']  = it|en|ecc ecc
   * $opts['debug'] = 1
   */
  function exists($opts = null) {

    $opzioni['tag']   = '';
    $opzioni['lang']  = ACTUAL_LANGUAGE;
    $opzioni['debug'] = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if(!$opzioni['tag']) {
      $this->log('Tag non fornito, cosa dovrei controllare?', ['level' => 'error', 'dieAfterError' => true]);
    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'tag';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['tag'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'lang';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['lang'];

    $filters[] = $filter_record;

    $opts              = [];
    $opts['start']     = '0';
    $opts['quanti']    = '1';
    $opts['countOnly'] = false;
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $list = $this->getlist($opts);

    if(count($list)) {

      return $list[0]->fields['id'];

    }

    return false;

  }

  function delete($opts = null) {

    $sql = 'DELETE FROM rel_news_tags WHERE tag_id = '.$this->fields['id'];

    $this->doQuery($sql);

    parent::delete($opts);

  }

}

?>