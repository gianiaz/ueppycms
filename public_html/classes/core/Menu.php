<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (14/05/2016)                                                                          **/
/** - Aggiunta la possibilità di estrarre un url di elenco nel caso la pagina abbia figli.       **/
/**                                                                                              **/
/** v.1.00 (14/05/2016)                                                                          **/
/** - Versione stabile a partire da versione 3.1.05                                              **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\core;

use Ueppy\core\Pagina;
use Ueppy\core\Linkmanager;
use Ueppy\utils\Utility;
use Ueppy\utils\Time;


class Menu extends Dba {

  /**
   * Viene impostato un valore di default per il level a 100.
   *
   * @param array $opts array delle opzioni di costruzione dell'oggetto dba.
   */
  function __construct($opts = null) {

    parent::__construct($opts);

    $this->id                        = 0;
    $this->level                     = 100;
    $this->ordine                    = 0;
    $this->is_category               = 0;
    $this->pubdate                   = date('Y-m-d');
    $t                               = new Time($this->pubdate);
    $this->additionalData['pubdate'] = $t->format(Traduzioni::getLang('cal', 'DATE_FORMAT'));
    $this->template                  = 'default';
    $this->robots                    = 'index,follow';
    $this->eliminato                 = 0;
    foreach($this->opts['langs'] as $l) {
      $this->fields[$l]['href']         = '';
      $this->fields[$l]['titolo_breve'] = '';
      $this->fields[$l]['img0_alt']     = '';
      $this->fields[$l]['img1_alt']     = '';
      $this->fields[$l]['img0_title']   = '';
      $this->fields[$l]['img1_title']   = '';
    }
  }

  /*
   * Il metodo passato una stringa ritorna il menu che ha nel campo nomefile la stringa passata.
   * E' posisible restringere la ricerca sul livello del menu (per differenziare ad esempio admin da public
   *
   * <code>
   * $opts['name']     = 'valoredelcamponomefile'; // Passare il campo nomefile da cercare.
   * $opts['minLevel'] = 0;                        // passare il valore del livello minimo che il menu cercato puà avere, se impostato a 0 viene ignorato (default: 0)
   * $opts['maxLevel'] = 0;                        // passare il valore del livello massimo che il menu cercato puà avere, se impostato a 0 viene ignorato (default: 0)
   * $opts['debug']    = false;                    // debug, impostare a true per stampare delle info a video.
   * @param array $opts Opzioni per il metodo.
   * @return mixed object|false Ritorna l'oggetto o false in caso non ci siano record che soddisfino i criteri di ricerca.
   */
  function getByName($opts = null) {

    $opzioniMetodo             = [];
    $opzioniMetodo['name']     = '';
    $opzioniMetodo['minLevel'] = 0;
    $opzioniMetodo['maxLevel'] = 0;
    $opzioniMetodo['debug']    = false;

    if($opts) {
      if(!is_array($opts)) {
        $this->log('A dire il vero mi aspettavo un array.'."\n".'Parametri passati:'."\n".print_r($opts, true), ['level' => 'error', 'dieAfterError' => true]);
      }
      $opzioniMetodo = $this->array_replace_recursive($opzioniMetodo, $opts);
    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'nomefile';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioniMetodo['name'];

    $filters[] = $filter_record;

    if($opzioniMetodo['minLevel']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'level';
      $filter_record['operatore'] = '>=';
      $filter_record['valore']    = $opzioniMetodo['minLevel'];

      $filters[] = $filter_record;

    }

    if($opzioniMetodo['maxLevel']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'level';
      $filter_record['operatore'] = '<=';
      $filter_record['valore']    = $opzioniMetodo['maxLevel'];

      $filters[] = $filter_record;

    }

    $opts              = [];
    $opts['debug']     = $opzioniMetodo['debug'];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $list = $this->getlist($opts);

    if($list) {

      return $list[0];

    } else {

      return false;

    }

  }

  /**
   * Oltre alle normali operazioni di salvataggio il metodo esegue i seguenti compiti:
   *
   * 1. Imposta il valore di ordine al
   * @param array $opts Opzioni del metodo save di Dba.
   * @return mixed false,1,2  Ritorna false in caso di errori, 1 in caso di inserimento, 2 in caso di update
   */
  function save($opts = null) {

    $opzioni                = [];
    $opzioni['fields']      = false;
    $opzioni['langFields']  = false;
    $opzioni['debug']       = false;
    $opzioni['forceInsert'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    // inserisce i dati non compilati nel caso in cui l'utente non abbia i diriti per parlo.
    $this->fillEmptyData();

    if(!isset($this->fields['ordine'])) {
      $this->getNextOrderValue();
    }

    $result = parent::save($opzioni);

    return $result;

  }

  /** NEW **/
  function getNextOrderValue() {

    $query = "SELECT max(ordine)+1 FROM ".$this->dataDescription['table']." WHERE level = ".$this->fields['level'];

    $result = $this->doQuery($query);

    $row = mysqli_fetch_row($result);

    $this->ordine = $row[0];

  }

  /**
   * $opzioniMetodo['href']   = 'href-da-cercare';
   * $opzioniMetodo['path']   = 'href-nonno|href-padre'
   * $opzioniMetodo['attivo'] = [0|1] // se impostato a 1 estrae solo le pagine attive
   */
  function getByHref($opts = null) {

    $debugStr = '';

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

    if($opzioniMetodo['debug']) {
      $debugStr .= 'Opzioni passate:';
      $debugStr .= "\n";
      $debugStr .= print_r($opzioniMetodo, true);
    }

    if(!$opzioniMetodo['href']) {
      $this->log('Parametro href non fornito.'."\n".'Parametri passati:'."\n".print_r($opzioniMetodo, true), ['level' => 'error', 'dieAfterError' => true]);
    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'href';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioniMetodo['href'];
    $filters[]                  = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '>=';
    $filter_record['valore']    = 100;
    $filters[]                  = $filter_record;

    if($opzioniMetodo['attivo']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'attivo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 1;
      $filters[]                  = $filter_record;

    }

    $opts                 = [];
    $opts['filters']      = array_merge($opzioniMetodo['filters'], $filters);
    $opts['operatore']    = 'AND';
    $opts['forceAllLang'] = $opzioniMetodo['forceAllLang'];
    $opts['debug']        = $opzioniMetodo['debug'];

    $list = $this->getlist($opts);

    if(count($list)) {
      foreach($list as $m) {
        // Controllo su percorso della pagina
        if($m->getPercorso() == $opzioniMetodo['path']) {
          return $m;
        }
      }

      return false;
    } else {
      return false;
    }
  }

  /* NEW */
  /**
   * @return type percorso gerarchico della pagina separato da pipe |.
   */
  function getPercorso($opts = null) {

    $opzioni          = [];
    $opzioni['out']   = 'default'; // default: pippo|pluto|paperino, string: Pippo > Pluto > Paperino
    $opzioni['debug'] = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $parent = $this->fields['parent'];

    $percorso       = [];
    $percorsoString = [];

    while($parent) {

      $m = $this->getById($parent);

      if($m) {

        $percorso[]       = $m->fields[ACTUAL_LANGUAGE]['href'];
        $percorsoString[] = $m->fields[ACTUAL_LANGUAGE]['dicitura'];

        $parent = $m->fields['parent'];

      } else {

        break;

      }

    }

    $percorso         = implode('|', array_reverse($percorso));
    $percorsoString[] = 'Livello base';
    $percorsoString   = implode(' > ', array_reverse($percorsoString));

    if($opzioni['out'] == 'default') {
      return $percorso;
    } else {
      return $percorsoString;
    }

  }


  function getAncestor($menu_id) {

    $ancestor = $this->getById($menu_id);

    if($ancestor) {

      if($ancestor->fields['parent'] == 0) {

        $ancestor_id = $ancestor->fields['id'];

      } else {

        $ancestor_id = $ancestor->getAncestor($ancestor->fields['parent']);

      }

    } else {

      return false;

    }

    return $ancestor_id;

  }

  function isParentOf($id) {

    $options                  = [];
    $options['tableFilename'] = 'menu';

    $m = new Menu($options);

    $opts['debug'] = 0;
    $m             = $m->getById($id, $opts);

    if(is_object($m)) {
      $parent = $m->fields['parent'];
      while($parent) {
        if($this->fields['id'] == $m->fields['parent']) {
          return true;
        }
        $m = $m->getById($m->fields['parent']);
        if($m) {
          $parent = $m->fields['parent'];
        } else {
          return false;
        }
      }
    }

    return false;
  }

  function getProfondita() {

    $m = $this;
    $i = 1;
    while($m->fields['parent']) {
      $m = $this->getById($m->fields['parent']);
      $i++;
    }

    return $i;
  }


  function hasChilds() {

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'parent';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];
    $filters[]                  = $filter_record;

    $opts              = [];
    $opts['countOnly'] = true;
    $opts['operatore'] = 'AND';
    $opts['filters']   = $filters;
    $opts['debug']     = 0;

    return $list = $this->getlist($opts);

  }

  function getOpzioniMenu($level = 100, $parent = 0, $string = '', $exclude = 0, $max_levels = 0, $utente = false, $debug = 0) {

    $count_levels = count(explode('>', $string));

    if(!$max_levels || $max_levels > $count_levels) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'level';
      $filter_record['operatore'] = '>=';
      $filter_record['valore']    = $level;

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'parent';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $parent;

      $filters[] = $filter_record;

      if($exclude) {

        $filter_record              = [];
        $filter_record['chiave']    = 'id';
        $filter_record['operatore'] = '!=';
        $filter_record['valore']    = $exclude;

        $filters[] = $filter_record;

        $operator = 'AND:2-AND-OR:1';

        $operator   = [];
        $operator[] = ['subOperator' => 'AND',
                       'quanti'      => 2];
        $operator[] = 'AND';
        $operator[] = ['subOperator' => 'OR',
                       'quanti'      => 1];

      } else {

        $operator = 'AND';

      }

      $opts              = [];
      $opts['sortField'] = 'ordine';
      $opts['sortOrder'] = 'ASC';
      $opts['debug']     = $debug;
      $opts['filters']   = $filters;
      $opts['operatore'] = $operator;

      $mlist = $this->getlist($opts);

      $select = [];

      foreach($mlist as $m) {

        $include_this = true;

        if($m->fields['nomefile'] == 'content' && $utente) {

          $options                  = [];
          $options['tableFilename'] = 'pagine';

          $d = new Pagina($options);

          $d = $d->getById($m->fields['id']);

          if(!$utente->fields['super_admin'] && !in_array($utente->fields['gruppo'], explode(',', SET_GRUPPI_ADMIN)) && !in_array($utente->fields['gruppo'], $d->fields['auth'])) {

            $include_this = false;

          }

        }

        if($include_this) {

          $select[$m->fields['id']] = '';
          if($string) {
            $select[$m->fields['id']] .= $string.' > ';
          }
          $select[$m->fields['id']] .= $m->fields[ACTUAL_LANGUAGE]['dicitura'];

          $add = $this->getOpzioniMenu($level, $m->fields['id'], $select[$m->fields['id']], $exclude, $max_levels, $utente, $debug);

          foreach($add as $k => $v) {

            $select[$k] = $v;

          }

        }

      }

      return $select;

    } else {

      return [];

    }

  }

  /**
   * $opts = array();
   * $opts['maxLevel']     = 0;
   * $opts['minLevel']     = 0;
   * $opts['parent']       = 0;
   * $opts['exclude']      = 0;
   * $opts['utente']       = false;
   * $opts['soloConFigli'] = 0;
   * $opts['debug']        = 0;
   */

  function getArrayMenu($opts = null) {

    //$level = 100, $parent = 0, $exclude = 0, $utente = false, $soloConFigli = 0, $debug = 0) {

    $opzioni                 = [];
    $opzioni['maxLevel']     = 0;
    $opzioni['minLevel']     = 0;
    $opzioni['parent']       = 0;
    $opzioni['exclude']      = 0;
    $opzioni['utente']       = false;
    $opzioni['soloConFigli'] = 0;
    $opzioni['onlyCatPages'] = false;
    $opzioni['debug']        = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug'] == 1) {
      $this->log($opzioni);

    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '>=';
    $filter_record['valore']    = $opzioni['minLevel'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '<=';
    $filter_record['valore']    = $opzioni['maxLevel'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'parent';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['parent'];

    $filters[] = $filter_record;

    if($opzioni['exclude']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = '!=';
      $filter_record['valore']    = $opzioni['exclude'];

      $filters[] = $filter_record;

      $operator = 'AND:2-AND-OR:1';

    } else {

      $operator = 'AND';

    }

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'asc';
    $opts['debug']     = $opzioni['debug'];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $mlist = $this->getlist($opts);

    $select = [];

    foreach($mlist as $m) {

      $include_this = true;

      if($m->fields['nomefile'] == 'pagina' && $opzioni['utente']) {

        $options                  = [];
        $options['tableFilename'] = 'pagine';

        $d = new Pagina($options);

        $d = $d->getById($m->fields['id']);

        if(!$opzioni['utente']->isSuperAdmin() && !$opzioni['utente']->isAdmin() && !in_array($opzioni['utente']->fields['gruppo'], $d->getPermessi())) {

          $include_this = false;

        }

        if($opzioni['onlyCatPages'] && !$m->is_category) {
          $include_this = false;
        }

      }

      // controllo se voglio pagine senza figli, e se non le voglio controllo che ne abbia prima di includerla
      if($include_this && $opzioni['soloConFigli']) {

        $filters = [];

        $filter_record              = [];
        $filter_record['chiave']    = 'parent';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $m->fields['id'];

        $filters[] = $filter_record;

        $opts              = [];
        $opts['sortField'] = 'ordine';
        $opts['sortOrder'] = 'asc';
        $opts['countOnly'] = 1;
        $opts['debug']     = 0;
        $opts['filters']   = $filters;
        $opts['operatore'] = 'AND';

        $include_this = $this->getlist($opts);

      }

      if($include_this) {
        $record         = [];
        $record['text'] = str_replace(' ', '&nbsp;', $m->fields[ACTUAL_LANGUAGE]['dicitura']);
        $record['href'] = '#node-'.$m->fields['id'];

        $opzioni['parent'] = $m->fields['id'];

        $record['nodes'] = $this->getArrayMenu($opzioni);

        if(!count($record['nodes'])) {
          unset($record['nodes']);
        }
        $select[] = $record;
      }

    }

    return $select;

  }

  function getTreeMenu($opts = null) {

    //$level = 100, $parent = 0, $exclude = 0, $utente = false, $soloConFigli = 0, $debug = 0) {

    $opzioni                 = [];
    $opzioni['maxLevel']     = 0;
    $opzioni['minLevel']     = 0;
    $opzioni['parent']       = 0;
    $opzioni['exclude']      = 0;
    $opzioni['utente']       = false;
    $opzioni['soloConFigli'] = 0;
    $opzioni['debug']        = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['debug'] == 1) {
      $this->log($opzioni);

    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '>=';
    $filter_record['valore']    = $opzioni['minLevel'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '<=';
    $filter_record['valore']    = $opzioni['maxLevel'];

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'parent';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['parent'];

    $filters[] = $filter_record;

    if($opzioni['exclude']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = '!=';
      $filter_record['valore']    = $opzioni['exclude'];

      $filters[] = $filter_record;

      $operator = 'AND:2-AND-OR:1';

    } else {

      $operator = 'AND';

    }

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'asc';
    $opts['debug']     = $opzioni['debug'];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $mlist = $this->getlist($opts);

    $select = '';

    $ramo = '<ol class="dd-list">';

    foreach($mlist as $m) {

      $include_this = true;

      if($m->fields['nomefile'] == 'pagina' && $opzioni['utente']) {

        $options                  = [];
        $options['tableFilename'] = 'pagine';

        $d = new Pagina($options);

        $d = $d->getById($m->fields['id']);

        if(!$opzioni['utente']->isSuperAdmin() && !$opzioni['utente']->isAdmin() && !in_array($opzioni['utente']->fields['gruppo'], $d->getPermessi())) {

          $include_this = false;

        }

      }

      // controllo se voglio pagine senza figli, e se non le voglio controllo che ne abbia prima di includerla
      if($include_this && $opzioni['soloConFigli']) {

        $filters = [];

        $filter_record              = [];
        $filter_record['chiave']    = 'parent';
        $filter_record['operatore'] = '=';
        $filter_record['valore']    = $m->fields['id'];

        $filters[] = $filter_record;

        $opts              = [];
        $opts['sortField'] = 'ordine';
        $opts['sortOrder'] = 'asc';
        $opts['countOnly'] = 1;
        $opts['debug']     = 0;
        $opts['filters']   = $filters;
        $opts['operatore'] = 'AND';

        $include_this = $this->getlist($opts);

      }
      if($include_this) {
        $ramo .= "\n\t".'<li class="dd-item" data-id="'.$m->fields['id'].'">'."\n\t\t".'<div class="dd-handle">'.$m->fields[ACTUAL_LANGUAGE]['dicitura'].'</div>'."\n\t";

        $opzioni['parent'] = $m->fields['id'];
        $ramo .= $this->getTreeMenu($opzioni);

        $ramo .= '</li>'."\n";

      }

    }

    $ramo .= '</ol>';

    if($ramo != '<ol class="dd-list"></ol>') {
      $select .= $ramo;
    }

    return $select;

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

    if($this->level >= 100) {
      $query .= ' AND menu.level >= 100';
    } else {
      $query .= ' AND menu.level < 100';
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

  function haGenitore($id = 0) {

    if($id == $this->fields['id']) return true;

    $sql = 'SELECT parent FROM menu WHERE id = ';

    $ricerca = $sql.$this->fields['id'];
    $res     = $this->doQuery($ricerca);
    $row     = mysqli_fetch_row($res);

    while($row[0] > 0) {
      if($row[0] == $id) {
        return true;
      }

      $ricerca = $sql.$row[0];
      $res     = $this->doQuery($ricerca);
      $row     = mysqli_fetch_row($res);
    }

    return false;

  }

  function fillEmptyData() {

    foreach($this->opts['langs'] as $l) {
      if(!$this->fields[$l]['href']) {
        $this->fields[$l]['href'] = $this->generaHref($this->fields[$l]['dicitura'], $l);
      }
    }

  }

  function delete($opts = null) {

    $opzioni          = [];
    $opzioni['reale'] = 1;
    $opzioni['debug'] = 0;

    if($opts) {
      $opzioni = array_replace_recursive($opzioni, $opts);
    }

    if($opzioni['reale']) {

      parent::delete();

    } else {
      $this->eliminato = 1;
      $this->save();
    }

    return true;

  }

  function __clone() {

    $this->fields['id'] = 0;

    foreach($this->opts['langs'] as $l) {
      $this->fields[$l]['href'] = '';
      unset($this->fields[$l]['id']);

      $versions = [];
      $versione = 1;

      $sql = 'SELECT dicitura FROM menu_langs WHERE lingua="'.$l.'" AND dicitura REGEXP "'.preg_quote($this->fields[$l]['dicitura']).' \\\\([0-9]+\\\\)"';

      $res = $this->doQuery($sql);

      while($row = mysqli_fetch_row($res)) {
        $versioneRe = '/^'.preg_quote($this->fields[$l]['dicitura']).'\s{1}\(([\d]+)\)$/';
        if(preg_match_all($versioneRe, $row[0], $m)) {
          $versions[] = $m[1][0];
        }

      }
      if($versions) {
        $versione = max($versions) + 1;
      }
      $this->$l = ['dicitura' => $this->fields[$l]['dicitura'].' ('.$versione.')'];
    }


  }

  function saveGerarchy($array, $parent = 0) {

    $ids = [];

    foreach($array as $k => $data) {
      $ids[] = $data['id'];
      if(isset($data['children'])) {
        $this->saveGerarchy($data['children'], $data['id']);
      }
    }

    if($ids) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $ids;

      $filters[] = $filter_record;

      $opts                 = [];
      $opts['sortField']    = 'FIELD(menu.id,'.implode(',', $ids).')';
      $opts['filters']      = $filters;
      $opts['debug']        = 0;
      $opts['forceAllLang'] = 1;
      $list                 = $this->getlist($opts);

      foreach($list as $k => $Obj) {
        $Obj->ordine   = $k;
        $Obj->parent   = $parent;
        $opts          = [];
        $opts['debug'] = 0;
        $Obj->save($opts);
      }

    }


  }

  function getUrl($opts = null) {

    $opzioni               = [];
    $opzioni['langFields'] = [ACTUAL_LANGUAGE];

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $lm = \Ueppy\core\LinkManager::getInstance();

    $previousPage = $lm->getPage();
    $lm->setPage('index');

    if($this->nomefile == 'pagina') {

      foreach($opzioni['langFields'] as $l) {
        $lm->setLang($l);
        if($this->is_category) {
          $params    = 'cmd/pagina/href/'.$this->fields[$l]['href'].'/parent/'.$this->fields['parent'].'/elenco/1';
          $links[$l] = $lm->get($params);
        } else {
          $params    = 'cmd/pagina/href/'.$this->fields[$l]['href'].'/parent/'.$this->fields['parent'];
          $links[$l] = $lm->get($params);
        }
      }

    } else {

      foreach($opzioni['langFields'] as $l) {

        $lm->setLang($l);
        $params
          = 'cmd/'.$this->nomefile;
        $links[$l]
          = $lm->get($params);

      }

    }

    $lm->setOptions(['lang' => ACTUAL_LANGUAGE, 'page' => $previousPage]);

    return $links;

  }

}