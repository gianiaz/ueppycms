<?php
/*****************/
/** v.1.00      **/
/*****************/
/**  CHANGELOG  **/
/**************************************************************************************************/
/** v.1.01 (09/11/2015, 14.49)                                                                   **/
/** - Aggiunto autoloading e namespace.                                                          **/
/**                                                                                              **/
/** v.1.00                                                                                       **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\blog;

use Ueppy\core\LinkManager;
use Ueppy\core\Operatore;
use Ueppy\utils\Utility;
use Ueppy\core\Dba;

class NewsCategory extends Dba {

  function __construct($opts) {

    parent::__construct($opts);

    $this->predefinita = 0;
    $this->template    = 'default';

    if(isset($GLOBALS['operator']) && $GLOBALS['operator']) {
      $this->additionalData['auth'] = [$GLOBALS['operator']->fields['gruppi_id']];
    }

  }

  function getByHref($href) {

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'href';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $href;
    $filters[]                  = $filter_record;

    $opts              = [];
    $opts['start']     = '0';
    $opts['quanti']    = '1';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';
    $opts['joins']     = [];

    $l = $this->getlist($opts);

    if($l) {
      return $l[0];
    } else {
      return false;
    }

  }

  /**
   * Ridefinisce il metodo fillresults per aggiungere proprietà che non sono direttamente
   * nella tabella.
   * In questo caso richiediamo i permessi associati alla pagina.
   */
  protected function fillresults() {

    $results = parent::fillresults();

    $getById = false;

    if(!is_array($results)) {
      $getById = true;
      $results = [$results];
    }

    $arr = [];

    foreach($results as $obj) {

      $obj->additionalData['auth'] = $obj->getPermessi();

      $arr[] = $obj;

    }

    if($getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }

  function save($opts = null) {

    $this->fillEmptyData();

    $this->setOrdine();

    if($this->fields['predefinita']) {
      $sql = 'UPDATE news_category SET predefinita = 0';
      $this->doQuery($sql);
    } else {
      $sql = 'SELECT count(id) FROM news_category WHERE predefinita = 1';
      if($this->fields['id']) {
        $sql .= ' AND id != '.$this->fields['id'];
      }
      $res = $this->doQuery($sql);
      if($res) {
        $row = mysqli_fetch_row($res);
        if(!$row[0]) {
          $this->predefinita = 1;
        }
      }
    }

    $result = parent::save($opts);

    $this->savePermessi();

    return $result;

  }

  function setOrdine() {

    if(!isset($this->fields['ordine'])) {

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

  function getPermessi() {

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = $this->dataDescription['table'].'_id';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];

    $filters[] = $filter_record;

    $options                  = [];
    $options['tableFilename'] = $this->dataDescription['table'].'_gruppi_auth';

    $relazioni = new Dba($options);

    $opts              = [];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $relazioni = $relazioni->getlist($opts);

    $ids = [];

    foreach($relazioni as $relazione) {

      $ids[] = $relazione->fields['id_gruppo'];

    }

    return $ids;

  }

  function savePermessi($debug = 0) {

    $options                  = [];
    $options['tableFilename'] = 'operatori';
    $operator                 = new Operatore($options);
    $operator                 = $operator->getById($_SESSION['LOG_INFO']['UID']);

    $gruppi = array_keys($operator->getGruppiInferiori());

    $nuovi_dati = $this->additionalData['auth'];

    $dati_db = $this->getPermessi();

    $gia_presenti = array_intersect($nuovi_dati, $dati_db);

    $da_cancellare = array_diff($dati_db, $gia_presenti);

    // in pratica qui gli dico di togliere solo quelli a cui io sono autorizzato
    $da_cancellare = array_intersect($da_cancellare, $gruppi);

    $da_inserire = array_diff($nuovi_dati, $gia_presenti);

    if($debug) {
      $str = '';
      $str .= 'Nuovi dati:';
      $str .= "\n";
      $str .= print_r($nuovi_dati, true);
      $str .= "\n";
      $str .= 'Dati in DB:';
      $str .= "\n";
      $str .= print_r($dati_db, true);
      $str .= "\n";
      $str .= 'Gia presenti:';
      $str .= "\n";
      $str .= print_r($gia_presenti, true);
      $str .= "\n";
      $str .= 'Da cancellare:';
      $str .= "\n";
      $str .= print_r($da_cancellare, true);
      $str .= "\n";
      $str .= 'Da inserire:';
      $str .= "\n";
      $str .= print_r($da_inserire, true);
      $str .= "\n";

      Utility::pre($str);

    }

    if(count($da_cancellare)) {

      $sql = 'DELETE FROM '.$this->dataDescription['table'].'_gruppi_auth WHERE '.$this->dataDescription['table'].'_id = '.$this->fields['id'].' AND id_gruppo IN ('.implode(',', $da_cancellare).')';

      if($debug) {
        Utility::pre($sql);
      }

      $res = $this->doQuery($sql);

    }

    if(count($da_inserire)) {
      foreach($da_inserire as $id_gruppo) {
        $options                  = [];
        $options['tableFilename'] = $this->dataDescription['table'].'_gruppi_auth';
        $relazione                = new Dba($options);
        $field                    = $this->dataDescription['table'].'_id';
        $relazione->$field        = $this->fields['id'];
        $relazione->id_gruppo     = $id_gruppo;
        $opt['debug']             = $debug;
        $opt['forceInsert']       = 1;
        $relazione->save($opt);
      }
    }

  }

  /**
   * Data una stringa di partenza genera l'href prendendosi cura di controllare di non creare href uguali nello stesso livello di appartenenza.
   * @param type $seme
   * @param type $lang
   * @return string
   */
  function generaHref($seme, $lang = 'it') {

    $href_base = Utility::sanitize($seme);
    $href_new  = $href_base;

    $query = 'SELECT count('.$this->dataDescription['table'].'_langs.href) FROM '.$this->dataDescription['table'].' LEFT JOIN '.$this->dataDescription['table'].'_langs ON '.$this->dataDescription['table'].'.id = '.$this->dataDescription['table'].'_langs.'.$this->dataDescription['table'].'_id WHERE lingua = "'.$lang.'" AND '.$this->dataDescription['table'].'_langs.href = "{href}"';
    if($this->fields['id']) {
      $query .= ' AND '.$this->dataDescription['table'].'.id != '.$this->fields['id'];
    }

    $sql = str_replace('{href}', $href_new, $query);

    $res = $this->doQuery($sql);

    $row = mysqli_fetch_row($res);

    $i = 1;

    while($row[0] > 0) {

      $href_new = $href_base.'-'.$i;

      $sql = str_replace('{href}', $href_new, $query);

      $res = $this->doQuery($sql);

      $row = mysqli_fetch_row($res);

      $i++;

    }

    return $href_new;

  }

  function isAuth($operator) {

    if($operator->fields['super_admin']) {
      return true;
    }

    $options                  = [];
    $options['tableFilename'] = $this->dataDescription['table'].'_gruppi_auth';

    $auth = new Dba($options);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'id_gruppo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $operator->fields['gruppi_id'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = $this->dataDescription['table'].'_id';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];

    $filters[] = $filter_record;

    $opts              = [];
    $opts['start']     = '0';
    $opts['quanti']    = '1';
    $opts['debug']     = 0;
    $opts['countOnly'] = true;
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    return $auth->getlist($opts);

  }


  /**
   * Data una pagina e passato nell'array delle opzioni un array di sigle lingue vengono restituiti i link che portano
   * alla pagina stessa.
   *
   * @param array|null $opts
   *
   * $opts['langFields'] = array('it', 'en'....); Di default popolato con array a singolo valore per la lingua attuale.
   */
  function getUrl($opts = null) {

    $opzioni               = [];
    $opzioni['langFields'] = [ACTUAL_LANGUAGE];

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $lm = LinkManager::getInstance();

    $previousPage = $lm->getPage();
    $lm->setPage('index');

    $links = [];

    foreach($opzioni['langFields'] as $l) {
      $lm->setLang($l);
      $params    = 'cmd/news/cat/'.$this->fields[$l]['href'];
      $links[$l] = $lm->get($params);
    }

    $lm->setOptions(['lang' => ACTUAL_LANGUAGE, 'page' => $previousPage]);

    return $links;
  }

  function fillEmptyData() {

    foreach($this->opts['langs'] as $l) {
      if(!$this->fields[$l]['href']) {
        $this->fields[$l]['href'] = $this->generaHref($this->fields[$l]['name'], $l);
      }
    }

  }


}

?>