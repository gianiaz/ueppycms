<?php
/******************/
/***ueppy3.1.01***/
/******************/
/**  CHANGELOG   **/
/************************************************************************************************/
/** v.3.1.01 (30/09/2013)                                                                      **/
/** - Sistemato controllo su esistenza dell'operatore nel costruttore, causava un warning.     **/
/**                                                                                            **/
/** v.3.1.00                                                                                   **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/

namespace Ueppy\core;

use Ueppy\utils\Utility;
use Ueppy\core\Menu;
use Ueppy\core\LinkManager;
use Ueppy\core\Allegati;


class Pagina extends Dba {

  protected $menuSettings = false;

  function __construct($opts = null) {

    $options_menu                  = [];
    $options_menu['tableFilename'] = 'menu';
    if($opts && isset($opts['lang'])) {
      $options_menu['lang'] = $opts['lang'];
    }

    if(isset($opts['imgSettings'])) {
      $options_menu['imgSettings'] = $opts['imgSettings'];
      unset($opts['imgSettings']);
    }

    if(isset($opts['forceAllLang'])) {
      $options_menu['forceAllLang'] = true;
    }

    parent::__construct($opts);

    $this->menuSettings = $options_menu;

    $this->additionalData['menu'] = new Menu($this->menuSettings);
    if(isset($GLOBALS['operator']) && $GLOBALS['operator']) {
      $this->additionalData['md5'] = md5(time().$GLOBALS['operator']->fields['id']);
    } else {
      $this->additionalData['md5'] = md5(time());
    }

    $this->additionalData['warnings'] = false;


  }

  /**
   * Riscrittura del metodo getbyid per fare in modo che all'estrazione venga
   * aggiunto in additionalData la proprietà menu che rappresenta il menu a cui la
   * pagina è collegata.
   *
   * @param type $id
   * @param type $opts
   * @return type object
   */
  public function getById($id, $opts = null) {

    $pagina = parent::getById($id, $opts);

    if($pagina) {

      $m = new Menu($this->menuSettings);

      $m = $m->getById($id, $opts);

      $pagina->additionalData['menu'] = $m;

    }

    return $pagina;
  }

  /**
   *
   * Il metodo cancella l'oggetto stesso, premunendosi di:
   * 1. cancellare il menu corrispondente e la sua directory di riferimento dove stanno le immagini in linea
   * 2. cancellare i commenti collegati alla pagina
   * 3. cancellare la pagina stessa (il metodo esteso nella classe base si occupa di cancellare gli allegati collegati alla pagina)
   *
   * @param array $opts
   *
   * $opts['debug'] = 0|1 // debug per l'operazione
   * @return boolean;
   */

  public function delete($opts = null) {

    $this->additionalData['menu']->delete($opts);

    $commenti = $this->getCommenti();

    foreach($commenti as $c) {
      $c->delete();
    }

    parent::delete();

    return true;
  }

  /**
   *
   * Il metodo getlist di questa classe è un po' particolare, perchè estrae i menu ad essa collegait
   * e poi se richiesto nelle opzioni estrae anche i dati della pagina stessa.
   *
   * @param type $opts
   * // solo valori aggiuntivi, vedere la documentazione di DBA per valutare tutti le opzioni disponibili
   * $opts['datiPagina'] = 0|1 Se impostato a 1 estrae i dati della pagina, altrimenti equivale a chiamare il metodo getlist nella classe menu.
   * @return Pagina
   */

  public function getlist($opts = null) {

    $opzioni                 = [];
    $opzioni['fields']       = false;
    $opzioni['langFields']   = false;
    $opzioni['forceAllLang'] = false;
    $opzioni['sortField']    = '';
    $opzioni['sortOrder']    = '';
    $opzioni['start']        = '';
    $opzioni['quanti']       = '';
    $opzioni['debug']        = 0;
    $opzioni['countOnly']    = false;
    $opzioni['filters']      = [];
    $opzioni['operatore']    = 'AND';
    $opzioni['joins']        = [];

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $filter_record              = [];
    $filter_record['chiave']    = 'nomefile';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = 'pagina';

    $opzioni['filters'][] = $filter_record;

    $lista_menu = $this->additionalData['menu']->getlist($opzioni);

    if(!isset($opts['countOnly']) || $opts['countOnly'] == false) {

      $return = [];

      foreach($lista_menu as $menu) {

        $p                         = new Pagina($this->opts);
        $p->additionalData['menu'] = $menu;

        if(isset($opts['datiPagina']) && $opts['datiPagina']) {
          $dba = new Dba($this->opts);
          $dba = $dba->getById($p->additionalData['menu']->fields['id']);
          foreach($dba->fields as $k => $v) {
            if(is_array($v)) {
              foreach($v as $kk => $vv) {
                $p->$k = [$kk => $vv];
              }
            } else {
              $p->$k = $v;
            }
          }
        }

        $return[] = $p;

      }

    } else {
      return $lista_menu;
    }


    return $return;

  }

  /**
   * Cosi come per il metodo getlist, anche per il metodo search questa classe si comporta in modo particolare, e cerca sia nei menu che nelle pagine,
   * facendo poi un unione dei risultati
   * Le opzioni passabili sono le stesse che per la classe DBA, l'unica cosa è che i campi saranno mischiati tra classe menu e class pagina, quindi li separo
   * all'interno del metodo
   */
  public function search($opts = null) {

    $opzioni              = [];
    $opzioni['srcFields'] = [];
    $opzioni['ricerca']   = '';
    $opzioni['start']     = false;
    $opzioni['quanti']    = false;
    $opzioni['debug']     = false;
    $opzioni['countOnly'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    Utility::pre($opzioni);

    if(!count($opzioni['srcFields']) || !$opzioni['ricerca']) {
      $this->log('Mi aspetto almeno un campo su cui effettuare la ricerca e una stringa da cercare, opzioni passate:'."\n".print_r($opts, true), ['level' => 'error', 'dieAfterError' => true]);
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
    }

    $optsPagina              = $opzioni;
    $optsPagina['srcFields'] = [];

    foreach($opzioni['srcFields'] as $field) {
      if(in_array($field, $this->dataDescription['desc']['ci']) || in_array($field, $this->dataDescription['desc']['cd'])) {
        $optsPagina['srcFields'][] = $field;
      }
    }

    $risultati = parent::search($optsPagina);

    $idMenu = [];

    foreach($risultati as $k => $r) {
      $idMenu[] = $r->fields['id'];
    }

    $return = [];

    if(count($idMenu)) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '('.implode(',', $idMenu).')';

      $filters[] = $filter_record;

      $opzioniMenu              = [];
      $opzioniMenu['filters']   = $filters;
      $opzioniMenu['sortField'] = 'FIELD(menu.id, '.implode(',', $idMenu).')';
      $opzioniMenu['operatore'] = 'AND';
      $opzioniMenu['joins']     = [];

      $menuPagineTrovate = $this->additionalData['menu']->getlist($opzioniMenu);

      foreach($risultati as $k => $p) {
        // sta cosa mi serve per uniformarmi con l'eventuale estrazione successiva
        $p->additionalData['pertinenza'] = $p->fields['pertinenza'];
        $p->additionalData['menu']       = $menuPagineTrovate[$k];
        $return[]                        = $p;
      }

    }

    $optsMenu              = $opzioni;
    $optsMenu['srcFields'] = [];
    $optsMenu['debug']     = 0;

    foreach($opzioni['srcFields'] as $field) {
      if(in_array($field, $this->additionalData['menu']->dataDescription['desc']['ci']) || in_array($field, $this->additionalData['menu']->dataDescription['desc']['cd'])) {
        $optsMenu['srcFields'][] = $field;
      }
    }

    $risultati = $this->additionalData['menu']->search($optsMenu);

    $idPagine = [];

    foreach($risultati as $r) {
      $idPagine[] = $r->fields['id'];
    }

    if(count($idPagine)) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '('.implode(',', $idPagine).')';

      $filters[] = $filter_record;

      $opzioni               = [];
      $opzioni['filters']    = $filters;
      $opzioni['sortField']  = 'FIELD(menu.id, '.implode(',', $idPagine).')';
      $opzioni['operatore']  = 'AND';
      $opzioni['datiPagina'] = true;
      $opzioni['joins']      = [];

      $pagine = $this->getlist($opzioni);

      foreach($pagine as $k => $p) {
        $p->additionalData['pertinenza'] = $risultati[$k]->fields['pertinenza'];
        $return[]                        = $p;
      }

    }

    return $return;


  }

  public function getPiuLette($quante) {

    $opts              = [];
    $opts['sortField'] = 'counter';
    $opts['sortOrder'] = 'DESC';
    $opts['start']     = 0;
    $opts['debug']     = 0;
    $opts['quanti']    = $quante;

    $list = parent::getlist($opts);

    $ids = [];

    foreach($list as $lst) {
      $ids[] = $lst->fields['id'];
    }

    if(count($ids)) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '('.implode(',', $ids).')';

      $filters[] = $filter_record;

      $opts               = [];
      $opts['sortField']  = 'FIELD(menu.id, '.implode(',', $ids).')';
      $opts['filters']    = $filters;
      $opts['datiPagina'] = 1;
      $opts['debug']      = 0;
      $opts['operatore']  = 'AND';

      $list = $this->getlist($opts);

      $result = [];

      foreach($list as $pagina) {

        $record            = [];
        $record['titolo']  = $pagina->additionalData['menu']->fields[$this->opts['lang']]['dicitura'];
        $record['counter'] = $pagina->fields[$this->opts['lang']]['counter'];

        $result[] = $record;
      }

      return $result;


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
      $obj->additionalData['md5']  = $obj->id;

      $arr[] = $obj;

    }

    if($getById) {
      return $arr[0];
    } else {
      return $arr;
    }

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

    $links = $this->additionalData['menu']->getUrl($opzioni);

    return $links;

  }

  function hasChilds() {

    return $this->additionalData['menu']->hasChilds();
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

    $query = 'SELECT count(menu_langs.href) FROM menu LEFT JOIN menu_langs ON menu.id = menu_langs.menu_id WHERE lingua = "'.$lang.'" AND menu_langs.href = "{href}"';
    if($this->fields['id']) {
      $query .= ' AND menu.id != '.$this->fields['id'];
    }
    $query .= ' AND menu.parent = '.$this->additionalData['menu']->fields['parent'];

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

  /**
   * Wrapper per il metodo getByHref della classe menu, le opzioni si sommano a quelle del menu,
   * vedere la documentazione del metodo per maggiori dettagli.
   *
   * Opzioni di questo metodo:
   *
   * $opts['']
   *
   *
   * @param type $opts ['datiPagina'] = [0|1] 1 se ci interessa estrarre anche il dato della pagina
   */
  function getByHref($opts = null) {

    $opzioniMetodo                 = [];
    $opzioniMetodo['href']         = '';
    $opzioniMetodo['path']         = '';
    $opzioniMetodo['forceAllLang'] = 0;
    $opzioniMetodo['attivo']       = 1;
    $opzioniMetodo['debug']        = 0;
    $opzioniMetodo['filters']      = [];

    if($opts) {
      if(!is_array($opts)) {
        $this->log('A dire il vero mi aspettavo un array.'."\n".'Parametri passati:'."\n".print_r($opts, true), ['level' => 'error', 'dieAfterError' => true]);
      }
      if(isset($opts['filters']) && !is_array($opts['filters'])) {
        $this->log('A dire il vero mi aspettavo un array.'."\n".'Parametri passati:'."\n".print_r($opts['filters'], true), ['level' => 'error', 'dieAfterError' => true]);
      }
      $opzioniMetodo = $this->array_replace_recursive($opzioniMetodo, $opts);
    }

    // creo un nuovo oggetto pagina usando le opzioni passate nel costruttore originale.
    $p = new Pagina($this->opts);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'nomefile';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = 'pagina';

    $opts['filters'][] = $filter_record;

    $p->additionalData['menu'] = $this->additionalData['menu']->getByHref($opts);

    if($p->additionalData['menu']) {

      if(isset($opts['datiPagina']) && $opts['datiPagina']) {
        // perche istanzio dba e non pagina? Perchè se lo facessi il getbyid chiamerebbe nuovamente la classe menu
        // creando un pericoloso loop.
        $dba = new Dba($this->opts);

        $opts                 = [];
        $opts['forceAllLang'] = $opzioniMetodo['forceAllLang'];
        $dba                  = $dba->getById($p->additionalData['menu']->fields['id'], $opts);
        foreach($dba->fields as $k => $v) {
          if(is_array($v)) {
            foreach($v as $kk => $vv) {
              $p->$k = [$kk => $vv];
            }
          } else {
            $p->$k = $v;
          }
        }
      }

      return $p;

    } else {

      return false;

    }

  }


  function save($opts = null) {

    $opzioni                   = [];
    $opzioni['fields']         = false;
    $opzioni['langFields']     = false;
    $opzioni['debug']          = false;
    $opzioni['forceInsert']    = false;
    $opzioni['updateAllegati'] = true;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $return = $this->additionalData['menu']->save($opzioni);

    if($return) {

      if(!$this->id) {
        $this->id            = $this->additionalData['menu']->fields['id'];
        $opts['forceInsert'] = 1;
      }

      $return = parent::save($opts);

      if($return) {
        $this->savePermessi();
        if($opzioni['updateAllegati']) {
          $this->updateAllegatiId();
        }
      }

      return $return;

    } else {

      return $return;

    }

  }

  function getPermessi() {


    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'menu_id';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];

    $filters[] = $filter_record;

    $options                  = [];
    $options['tableFilename'] = 'pagine_gruppi_auth';

    $relazioni = new Dba($options);

    $opts              = [];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $relazioni = $relazioni->getlist($opts);

    $ids = [];

    foreach($relazioni as $relazione) {

      $ids[] = $relazione->fields['gruppi_id'];

    }

    return $ids;

  }

  function savePermessi($debug = 0) {

    if(isset($this->additionalData['auth'])) {

      $nuovi_dati = $this->additionalData['auth'];

      $dati_db = $this->getPermessi();

      $gia_presenti = array_intersect($nuovi_dati, $dati_db);

      $da_cancellare = array_diff($dati_db, $gia_presenti);

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

      }

      if(count($da_cancellare)) {

        $sql = 'DELETE FROM pagine_gruppi_auth WHERE menu_id = '.$this->fields['id'].' AND gruppi_id IN ('.implode(',', $da_cancellare).')';

        $res = $this->doQuery($sql);

      }

      if(count($da_inserire)) {
        foreach($da_inserire as $gruppi_id) {
          $options                  = [];
          $options['tableFilename'] = 'pagine_gruppi_auth';
          $relazione                = new Dba($options);
          $relazione->menu_id       = $this->fields['id'];
          $relazione->gruppi_id     = $gruppi_id;
          $opt['debug']             = 0;
          $opt['forceInsert']       = 1;
          $relazione->save($opt);
        }
      }

    }

  }

  function getErrors($opts = null) {

    $opzioni          = [];
    $opzioni['glue']  = '<br />';
    $opzioni['debug'] = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
      $this->log($this->additionalData['menu']->errori);
      $this->log($this->errori);
    }


    if($opts['glue'] === false) {
      return array_merge($this->errori, $this->additionalData['menu']->errori);
    } else {
      return implode($opzioni['glue'], array_merge($this->errori, $this->additionalData['menu']->errori));
    }

  }

  function getWrongFields() {

    return array_merge($this->wrongFields, $this->additionalData['menu']->wrongFields);
  }

  function updateAllegatiId() {

    $options                  = [];
    $options['tableFilename'] = 'allegati';

    $allegati = new Allegati($options);

    $allegati->updateId($this->dataDescription['table'], $this->additionalData['md5'], $this->fields['id']);

  }

  function getCommenti($opts = null) {

    $options                  = [];
    $options['tableFilename'] = 'commenti_pagine';
    $options['debug']         = 0;

    $commenti = new Dba($options);

    $opzioni                 = [];
    $opzioni['fields']       = false;
    $opzioni['langFields']   = false;
    $opzioni['forceAllLang'] = false;
    $opzioni['sortField']    = '';
    $opzioni['sortOrder']    = '';
    $opzioni['start']        = '';
    $opzioni['quanti']       = '';
    $opzioni['debug']        = 0;
    $opzioni['countOnly']    = false;
    $opzioni['filters']      = [];
    $opzioni['operatore']    = 'AND';
    $opzioni['joins']        = [];

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $filter_record              = [];
    $filter_record['chiave']    = 'parent_id';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];

    $opzioni['filters'][] = $filter_record;

    $return = $commenti->getlist($opzioni);

    return $return;

  }

  function isValid() {

    if(isset($this->additionalData['menu']->wrongFields['img0'])) {
      $this->additionalData['warnings'] = true;
      unset($this->additionalData['menu']->wrongFields['img0']);
    }
    if(isset($this->additionalData['menu']->wrongFields['img1'])) {
      $this->additionalData['warnings'] = true;
      unset($this->additionalData['menu']->wrongFields['img1']);
    }

    return !(bool)count(array_merge($this->wrongFields, $this->additionalData['menu']->wrongFields));
  }

  function __clone() {

    $newM = clone $this->additionalData['menu'];

    $this->additionalData['menu'] = $newM;

    $this->fields['id'] = 0;


    foreach($this->opts['langs'] as $l) {
      unset($this->fields[$l]['id']);
    }

    $opzioni                   = [];
    $opzioni['updateAllegati'] = false;

    return $this->save($opzioni);

  }

}

?>