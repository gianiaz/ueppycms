<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (19/08/2016, 18.52)                                                                   **/
/** - Aggiunta la possibilità di fornire il cmd per generare url che non siano solo /news/...    **/
/** - Bugfix/implementazione copia, con modifica del titolo                                      **/
/**                                                                                              **/
/** v.1.00 (29/04/2016)                                                                          **/
/** - Versione stabile della classe                                                              **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
namespace Ueppy\blog;

use Ueppy\core\Dba;
use Ueppy\core\Linkmanager;
use Ueppy\core\Traduzioni;

use Ueppy\blog\Tag;
use Ueppy\blog\NewsCategory;
use Ueppy\utils\Time;
use Ueppy\utils\Utility;
use Ueppy\core\Operatore;


/**
 * Classe per la gestione delle News.
 */
class News extends Dba {

  function __construct($opts = null) {

    parent::__construct($opts);

    $time = new Time();

    $this->attiva_dal    = $time->toMySqlDateTime();
    $this->disattiva_dal = '0000-00-00 00:00:00';

    $this->additionalData['attiva_dal']    = $time->format('d/m/Y H:i');
    $this->additionalData['disattiva_dal'] = '-';
    $this->additionalData['scadenza']      = -1;

    $this->additionalData['tags']                  = [];
    $this->additionalData['parents']['principale'] = false;
    $this->additionalData['parents']['accessorie'] = [];
    $this->additionalData['lingue_attive']         = explode(',', SET_LANGUAGES);

    $this->stato                        = 'ATTIVO';
    $this->additionalData['genitore']   = 0;
    $this->additionalData['parents_id'] = [];
    $this->eliminato                    = 0;

    foreach($this->opts['langs'] as $l) {
      $this->fields[$l]['lingua_attiva'] = 1;
    }

    foreach($this->opts['langs'] as $l) {
      $this->additionalData['tags'][$l]['string'] = '';
    }

    if(defined('SET_NEWS_COMMENTI') && SET_NEWS_COMMENTI) {
      $this->commenti = 1;
    } else {
      $this->commenti = 0;
    }

    $this->operatori_id = 0;

    if(isset($GLOBALS['operator']) && $GLOBALS['operator']) {
      $this->operatori_id = $GLOBALS['operator']->fields['id'];
    }

  }

  /**
   * Ridefinisce il metodo fillresults per aggiungere proprietà che non sono direttamente
   * nella tabella.
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

      if($obj->additionalData['disattiva_dal'] != '-') {
        $obj->additionalData['scadenza'] = 1;
      }

      $obj->additionalData['lingue_attive'] = [];

      foreach($obj->opts['langs'] as $l) {
        if(isset($obj->fields[$l]) && $obj->fields[$l]['lingua_attiva']) {
          $obj->additionalData['lingue_attive'][] = $l;
        }
      }

      if(defined('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table'])) && constant('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table']))) {
        $obj->getParents();
      }

      $obj->additionalData['md5'] = $obj->id;

      $arr[] = $obj;
    }
    if($getById) {
      return $arr[0];
    } else {
      return $arr;
    }
  }

  /**
   *
   * @param type $format [id]
   * @param type $debug
   * @return string
   */

  function getParents($opts = null) {

    $opzioni                 = [];
    $opzioni['format']       = 'internal';
    $opzioni['forceExtract'] = false;
    $opzioni['debug']        = 0;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if(!$this->additionalData['parents']['principale'] || $opzioni['forceExtract']) {

      $options                  = [];
      $options['tableFilename'] = $this->dataDescription['table'].'_category';
      $options['debug']         = 0;

      $a_rel = new Dba($options);

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'rel.id_'.$this->dataDescription['table'];
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $this->fields['id'];

      $filters[] = $filter_record;

      $opts                 = [];
      $opts['fields']       = [$this->dataDescription['table'].'_category.id as `'.$this->dataDescription['table'].'_category_id`', 'rel.principale'];
      $opts['langFields']   = ['href', 'name'];
      $opts['sortField']    = 'rel.principale';
      $opts['forceAllLang'] = 1;
      $opts['sortOrder']    = 'DESC';
      $opts['filters']      = $filters;
      $opts['operatore']    = 'AND';
      $opts['joins']        = [];

      $join              = [];
      $join['table']     = 'rel_'.$this->dataDescription['table'].'_category_'.$this->dataDescription['table'];
      $join['alias']     = 'rel';
      $join['on1']       = $this->dataDescription['table'].'_category_id';
      $join['on2']       = $this->dataDescription['table'].'_category.id';
      $join['operatore'] = '=';
      $opts['joins'][]   = $join;

      $list = $a_rel->getlist($opts);

      $parents               = [];
      $parents['accessorie'] = [];
      $parents['principale'] = false;

      foreach($list as $val) {
        $record         = [];
        $record['href'] = $val->fields['href'];
        $record['id']   = $val->fields[$this->dataDescription['table'].'_category_id'];
        $record['name'] = $val->fields['name'];
        if($val->fields['principale']) {
          $parents['principale'] = $record;
        } else {
          $parents['accessorie'][$record['id']] = $record;
        }
      }

      if(!$parents['principale']) {
        $record                = [];
        $record['id']          = '0';
        $record['href']        = 'error';
        $record['name']        = Traduzioni::getLang($this->dataDescription['table'], 'ORFANA');
        $parents['principale'] = $record;
      }

      $this->additionalData['parents'] = $parents;

      $this->additionalData['genitore']   = $this->additionalData['parents']['principale']['id'];
      $this->additionalData['parents_id'] = array_keys($this->additionalData['parents']['accessorie']);

    }

    switch($opzioni['format']) {
      case 'toSave':
        $ids                                                       = [];
        $ids[$this->additionalData['parents']['principale']['id']] = 1;
        foreach($this->additionalData['parents']['accessorie'] as $cat) {
          $ids[$cat['id']] = 0;
        }

        return $ids;
        break;
      case 'ids':
        $ids   = [];
        $ids[] = $this->additionalData['parents']['principale']['id'];
        foreach($this->additionalData['parents']['accessorie'] as $cat) {
          $ids[] = $cat['id'];
        }

        return $ids;
        break;
      case 'text':
        $stringCategories   = [];
        $stringCategories[] = '<strong>'.$this->additionalData['parents']['principale']['name'].'</strong>';
        foreach($this->additionalData['parents']['accessorie'] as $cat) {
          $stringCategories[] = $cat['name'];
        }

        return implode(', ', $stringCategories);
        break;
    }

    return true;

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

  function save($opts = null) {

    // inserisce i dati non compilati nel caso in cui l'utente non abbia i diriti per parlo.
    $this->fillEmptyData();

    $result = parent::save($opts);

    if($result) {
      if(defined('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table'])) && constant('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table']))) {
        $this->saveParents();
      }
      $this->saveTags();
    }

    return $result;

  }

  /**
   * Per la news in oggetto ritorna il primo titolo non vuoto tenendo presente quali sono
   * le lingue attive (usato nell'elenco della tabella in admin).
   * @return string $titolo
   */
  function getTitoloElenchi() {

    foreach($this->opts['langs'] as $l) {
      if(isset($this->fields[$l]) && $this->fields[$l]['lingua_attiva'] && $this->fields[$l]['titolo']) {
        return $this->fields[$l]['titolo'];
      }
    }
  }


  function getCommenti($opts = null) {

    $options                  = [];
    $options['tableFilename'] = 'commenti_news';
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

  function getTags($debug = 0) {

    $options                  = [];
    $options['tableFilename'] = 'tags';
    $options['debug']         = 0;

    $tagObj = new Dba($options);

    $opts                 = [];
    $opts['fields']       = false;
    $opts['langFields']   = false;
    $opts['forceAllLang'] = false;
    $opts['sortField']    = '';
    $opts['sortOrder']    = '';
    $opts['start']        = '';
    $opts['quanti']       = '';
    $opts['debug']        = 0;
    $opts['countOnly']    = false;
    $opts['filters']      = [];
    $opts['operatore']    = 'AND';
    $opts['joins']        = [];

    $join              = [];
    $join['table']     = 'rel_'.$this->dataDescription['table'].'_tags';
    $join['alias']     = 'r';
    $join['on1']       = 'tag_id';
    $join['on2']       = 'tags.id';
    $join['operatore'] = '=';

    $opts['joins'][] = $join;

    $join              = [];
    $join['table']     = $this->dataDescription['table'];
    $join['alias']     = 'n';
    $join['on1']       = 'id';
    $join['on2']       = 'r.'.$this->dataDescription['table'].'_id';
    $join['operatore'] = '=';

    $opts['joins'][] = $join;

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'n.id';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $this->fields['id'];

    $filters[] = $filter_record;

    $opts['filters'] = $filters;

    if(!$this->opts['forceAllLang']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'lang';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = ACTUAL_LANGUAGE;

      $filters[] = $filter_record;

    }

    $list = $tagObj->getlist($opts);

    $this->additionalData['tags']   = [];
    $this->additionalData['tagsId'] = [];

    foreach($this->opts['langs'] as $l) {
      $this->additionalData['tags'][$l]           = [];
      $this->additionalData['tags'][$l]['string'] = '';
      $this->additionalData['tagsId'][$l]         = [];
    }

    foreach($list as $tagObj) {

      if(!isset($this->additionalData['tags'][$tagObj->fields['lang']])) {
        $this->additionalData['tags'][$tagObj->fields['lang']]           = [];
        $this->additionalData['tags'][$tagObj->fields['lang']]['string'] = [];
        $this->additionalData['tags'][$tagObj->fields['lang']]['list']   = [];
      }

      $this->additionalData['tagsId'][$tagObj->fields['lang']][] = $tagObj->id;

      $this->additionalData['tags'][$tagObj->fields['lang']]['string'][] = $tagObj->fields['tag'];

      $tag          = [];
      $tag['id']    = $tagObj->fields['id'];
      $tag['tag']   = $tagObj->fields['tag'];
      $tag['count'] = $tagObj->fields['count'];

      $this->additionalData['tags'][$tagObj->fields['lang']]['list'][$tag['id']] = $tag;
    }

    foreach($this->opts['langs'] as $l) {
      if(is_array($this->additionalData['tags'][$l]['string'])) {
        $this->additionalData['tags'][$l]['string'] = implode(', ', $this->additionalData['tags'][$l]['string']);
      }
    }

    if($debug) {
      Utility::pre($this->additionalData['tags']);
    }
  }

  function saveParents() {

    if(isset($this->additionalData['nuove_categorie'])) {

      $nuovi_dati = array_keys($this->additionalData['nuove_categorie']);
      if(isset($this->additionalData['parents_id'])) {
        $dati_db = array_merge($this->additionalData['parents_id'], [$this->additionalData['genitore']]);
      } else {
        $dati_db = [];
      }
      $gia_presenti  = array_intersect($nuovi_dati, $dati_db);
      $da_cancellare = array_diff($dati_db, $gia_presenti);
      $da_inserire   = array_diff($nuovi_dati, $gia_presenti);
      $debug         = 0;
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
        $sql = 'DELETE FROM rel_'.$this->dataDescription['table'].'_category_'.$this->dataDescription['table'].' WHERE id_'.$this->dataDescription['table'].' = '.$this->fields['id'].' AND '.$this->dataDescription['table'].'_category_id IN ('.implode(',', $da_cancellare).')';
        $res = $this->doQuery($sql);
      }

      if(count($da_inserire)) {
        foreach($da_inserire as $id_cat) {
          $options                                                     = [];
          $options['tableFilename']                                    = 'rel_'.$this->dataDescription['table'].'_category_'.$this->dataDescription['table'];
          $relazione                                                   = new Dba($options);
          $relazione->{'id_'.$this->dataDescription['table']}          = $this->fields['id'];
          $relazione->{$this->dataDescription['table'].'_category_id'} = $id_cat;
          $relazione->principale                                       = 0;
          $opt['debug']                                                = 0;
          $relazione->save($opt);
        }
      }

      $sql = 'UPDATE rel_'.$this->dataDescription['table'].'_category_'.$this->dataDescription['table'].' SET principale = 0 WHERE id_'.$this->dataDescription['table'].'="'.$this->fields['id'].'"';

      $this->doQuery($sql);

      $news_category_id = array_keys($this->additionalData['nuove_categorie']);
      $news_category_id = array_shift($news_category_id);

      $sql = 'UPDATE rel_'.$this->dataDescription['table'].'_category_'.$this->dataDescription['table'].' SET principale = 1 WHERE id_'.$this->dataDescription['table'].'="'.$this->fields['id'].'" AND '.$this->dataDescription['table'].'_category_id = '.$news_category_id;

      $this->doQuery($sql);

    }

  }


  function saveTags() {

    $tagsId = [];

    foreach($this->opts['langs'] as $l) {

      if(isset($this->additionalData['newTags'][$l])) {

        $tagsId[$l] = [];

        $tags = explode(',', $this->additionalData['newTags'][$l]);

        foreach($tags as $tag) {

          if($tag) {

            $options                  = [];
            $options['tableFilename'] = 'tags';

            $TagObj = new Tag($options);

            $opts         = [];
            $opts['lang'] = $l;
            $opts['tag']  = trim($tag);

            $idTag = $TagObj->exists($opts);

            if($idTag) {
              $tagsId[$l][] = $idTag;
            } else {

              $TagObj->tag   = trim($tag);
              $TagObj->lang  = $l;
              $TagObj->count = 0;
              $TagObj->save();

              $tagsId[$l][] = $TagObj->fields['id'];

            }

          }

        }
        $nuovi_dati = $tagsId[$l];

        if(isset($this->additionalData['tags'][$l]['list'])) {
          $dati_db = array_keys($this->additionalData['tags'][$l]['list']);
        } else {
          $dati_db = [];
        }
        $gia_presenti  = array_intersect($nuovi_dati, $dati_db);
        $da_cancellare = array_diff($dati_db, $gia_presenti);
        $da_inserire   = array_diff($nuovi_dati, $gia_presenti);
        $debug         = 0;

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
          $sql = 'DELETE FROM rel_'.$this->dataDescription['table'].'_tags WHERE '.$this->dataDescription['table'].'_id = '.$this->fields['id'].' AND tag_id IN ('.implode(',', $da_cancellare).')';
          $res = $this->doQuery($sql);
        }

        if(count($da_inserire)) {
          foreach($da_inserire as $tag_id) {

            $options                  = [];
            $options['tableFilename'] = 'rel_'.$this->dataDescription['table'].'_tags';

            $relazione                                          = new Dba($options);
            $relazione->{$this->dataDescription['table'].'_id'} = $this->fields['id'];
            $relazione->tag_id                                  = $tag_id;

            $relazione->save();

          }
        }

      }

    }

    $options                  = [];
    $options['tableFilename'] = 'tags';

    $TagObj = new Tag($options);

    $TagObj->refreshCount();

  }

  function getLingueAttive() {

    $lingue = [];
    foreach($this->additionalData['lingue_attive'] as $l) {
      if(isset($GLOBALS['lingue']) && isset($GLOBALS['lingue'][$l])) {
        $lingue[] = $GLOBALS['lingue'][$l];
      } else {
        $lingue[] = $l;
      }
    }

    return implode(', ', $lingue);
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
    $opzioni['cmd']        = $this->dataDescription['table'];
    $opzioni['langFields'] = [ACTUAL_LANGUAGE];

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $links = [];

    if(!$this->fields['id']) {
      foreach($this->opts['langs'] as $l) {
        $links[$l] = '';
      }

      return $links;
    }

    $lm = \Ueppy\core\LinkManager::getInstance();

    $previousPage = $lm->getPage();
    $lm->setPage('index');

    $cat = false;

    if(defined('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table'])) && constant('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table']))) {

      $options                  = [];
      $options['tableFilename'] = $this->dataDescription['table'].'_category';
      $options['debug']         = 0;

      $cat = new Dba($options);

      $opts                 = [];
      $opts['forceAllLang'] = true;

      $cat = $cat->getById($this->additionalData['parents']['principale']['id'], $opts);

    }

    foreach($opzioni['langFields'] as $l) {
      if(isset($this->fields[$l]) && isset($this->fields[$l]['href'])) {
        $lm->setLang($l);
        $urlParams = 'cmd/'.$opzioni['cmd'].'/act/read/href/'.$this->fields[$l]['href'];
        if($cat) {
          $urlParams .= '/cat/'.$cat->fields[$l]['href'];
        }
        $links[$l] = $lm->get($urlParams);
      }
    }

    $lm->setOptions(['lang' => ACTUAL_LANGUAGE, 'page' => $previousPage]);

    return $links;

  }


  /**
   * Estrae la news a partire dall'href.
   *
   * Accetta il solito parametro $opts, che può contenere:
   *
   * $opts = array();
   * $opts['href']   = 'href-della-'.$this->$this->dataDescription['table']; // campo obbligatorio
   * $opts['lang']   = ''; // opzionale, di default prende l'ACTUAL_LANGUAGE, se non definito "it".
   * $opts['attivo'] = '1'; // di default è 1 e indica che la news deve essere attiva, altrimenti è ignorato
   * $opts['debug']  = '0'; // 0|1 indica se stampare qualche stack di debug
   */
  function getByHref($opts = null) {

    $opzioni         = [];
    $opzioni['href'] = '';
    if(defined('ACTUAL_LANGUAGE')) {
      $opzioni['lang'] = ACTUAL_LANGUAGE;
    } else {
      $opzioni['lang'] = 'it';
    }
    $opzioni['forceAllLang'] = 0;
    $opzioni['debug']        = 0;
    $opzioni['stato']        = false;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    if(!$opzioni['href']) {
      $this->log('Parametro obbligatorio href non fornito.', ['level' => 'error', 'dieAfterError' => true]);
    }

    if($opzioni['debug']) {
      $this->log($opzioni);
    }

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'href';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $opzioni['href'];
    $filters[]                  = $filter_record;

    if($opzioni['stato']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'stato';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $opzioni['stato'];
      $filters[]                  = $filter_record;

    }

    $joins = [];

    $join              = [];
    $join['table']     = 'operatori';
    $join['alias']     = 'o';
    $join['on1']       = 'id';
    $join['on2']       = 'news.operatori_id';
    $join['operatore'] = '=';

    $joins[] = $join;


    $opts                 = [];
    $opts['forceAllLang'] = $opzioni['forceAllLang'];
    $opts['start']        = 0;
    $opts['quanti']       = 1;
    $opts['debug']        = $opzioni['debug'];
    $opts['filters']      = $filters;
    $opts['joins']        = $joins;
    $opts['operatore']    = 'AND';
    $opts['debug']        = 0;

    $this->addField('o.nomecompleto as author');

    $list = $this->getlist($opts);

    if(count($list)) {
      return $list[0];
    } else {
      return false;
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

      // devo cancellare:
      // 1. i tag associati
      // 2. le categorie associate
      // 3. i commenti

      // 1.
      $sql = 'DELETE FROM rel_'.$this->dataDescription['table'].'_tags WHERE '.$this->dataDescription['table'].'_id = "'.$this->fields['id'].'"';
      $this->doQuery($sql);

      $options                  = [];
      $options['tableFilename'] = 'tags';

      $TagObj = new Tag($options);
      $TagObj = $TagObj->refreshCount();

      // 2.
      $sql = 'DELETE FROM rel_'.$this->dataDescription['table'].'_category_'.$this->dataDescription['table'].' WHERE id_'.$this->dataDescription['table'].' = "'.$this->fields['id'].'"';
      $this->doQuery($sql);

      // 3.
      $sql = 'DELETE FROM commenti WHERE genitore = "'.$this->dataDescription['table'].'" AND id_genitore = "'.$this->fields['id'].'"';
      $this->doQuery($sql);

      parent::delete();

    } else {
      $this->eliminato = 1;
      $this->save();
    }

  }

  /**
   *
   * @param array $list elenco di oggetti news
   * @param array opzioni per la creazione
   */

  function toRss($list, $opts = null) {

    $opzioni['fname'] = 'index';
    $opzioni['title'] = 'Elenco '.$this->dataDescription['table'];
    $opzioni['lang']  = ACTUAL_LANGUAGE;

    if($opts) {
      $opzioni = $this->array_replace_recursive($opzioni, $opts);
    }

    $last_date = $list[0]->fields['data_attivazione'];

    $rss_fname = DOC_ROOT.REL_ROOT.UPLOAD.'rss_cache/'.$opzioni['fname'].'.xml';

    if(!file_exists($rss_fname) || filemtime($rss_fname) >= $last_date) {

      $rss = '<?xml version="1.0"?>';
      $rss .= "\n";
      $rss .= '<rss version="2.0">';
      $rss .= "\n";
      $rss .= "\t";
      $rss .= '<channel>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<title>'.$opzioni['title'].'</title>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<link></link>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<language>'.ACTUAL_LANGUAGE.'</language>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<pubDate>'.date('r', $last_date).'</pubDate>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<lastBuildDate>'.time().'</lastBuildDate>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<generator>ueppy cms</generator>';
      $rss .= "\n";
      $rss .= "\t\t";
      $rss .= '<webMaster>gianiaz@gianiaz.net</webMaster>';

      $lm = new Linkmanager();

      $lm_opt                = [];
      $lm_opt['host']        = HOST;
      $lm_opt['root']        = REL_ROOT;
      $lm_opt['pagina']      = 'index.php';
      $lm_opt['extraparams'] = '';
      $lm_opt['lang']        = ACTUAL_LANGUAGE;

      foreach($list as $news) {

        $link = $news->getUrl();

        $rss .= "\n";
        $rss .= "\n";
        $rss .= "\t\t";
        $rss .= '<item>';
        $rss .= "\n";
        $rss .= "\t\t\t";
        $rss .= '<title>'.$news->fields[$opzioni['lang']]['titolo'].'</title>';
        $rss .= "\n";
        $rss .= "\t\t\t";
        $rss .= '<link>'.$link[$opzioni['lang']].'</link>';
        $rss .= "\n";
        $rss .= "\t\t\t";
        $rss .= '<description>'.$news->fields[ACTUAL_LANGUAGE]['intro'].'</description>';
        $rss .= "\n";
        $rss .= "\t\t\t";
        $rss .= '<pubDate>'.date('r', $news->fields['data_attivazione']).'</pubDate>';
        $rss .= "\n";
        $rss .= "\t\t";
        $rss .= '</item>';

      }

      $rss .= "\n";
      $rss .= "\t";
      $rss .= '</channel>';
      $rss .= "\n";
      $rss .= '</rss>';

      file_put_contents($rss_fname, $rss);

    }

    return file_get_contents($rss_fname);

  }

  function canEdit($operator) {

    if($operator->isSuperAdmin() || $operator->isAdmin()) {
      return true;
    }

    if($operator->hasRights($this->dataDescription['table'])) {

      if($operator->fields['id'] == $this->fields['author']) {
        return true;
      }

      if(defined('SET_MODIFICA_'.strtoupper($this->dataDescription['table']).'_DI_ALTRI') && constant('SET_MODIFICA_'.strtoupper($this->dataDescription['table']).'_DI_ALTRI')) {

        if(defined('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table'])) && constant('SET_ENABLE_CAT_'.strtoupper($this->dataDescription['table']))) {

          $opzioni                 = [];
          $opzioni['format']       = 'ids';
          $opzioni['forceExtract'] = false;
          $opzioni['debug']        = 0;

          $categorie = $this->getParents($opzioni);

          $sql = 'SELECT '.$this->dataDescription['table'].'_category_id FROM '.$this->dataDescription['table'].'_category_gruppi_auth WHERE id_gruppo = '.$operator->fields['gruppo'];

          $res = $this->doQuery($sql);

          while($row = mysqli_fetch_row($res)) {

            if(in_array($row['id'], $categorie)) {
              return true;
            }

          }

          return false;

        }

        return true;

      }

    }

    return false;

  }

  function getCorrelate($opts = null) {

    $options           = [];
    $options['quanti'] = 4;
    $options['debug']  = false;

    if($opts) {
      $options = $this->array_replace_recursive($options, $opts);
    }

    $list = [];

    if(count($this->additionalData['tagsId'][ACTUAL_LANGUAGE])) {

      $opzioni              = [];
      $opzioni['sortField'] = 'data_attivazione';
      $opzioni['sortOrder'] = 'DESC';
      $opzioni['start']     = 0;
      $opzioni['quanti']    = $options['quanti'];
      $opzioni['debug']     = $options['debug'];
      $opzioni['joins']     = [];

      $join               = [];
      $join['table']      = 'rel_'.$this->dataDescription['table'].'_tags';
      $join['alias']      = 'rnt';
      $join['on1']        = $this->dataDescription['table'].'_id';
      $join['on2']        = $this->dataDescription['table'].'.id';
      $join['operatore']  = '=';
      $opzioni['joins'][] = $join;

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'rnt.tag_id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '('.implode(',', $this->additionalData['tagsId'][ACTUAL_LANGUAGE]).')';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = $this->dataDescription['table'].'.id';
      $filter_record['operatore'] = '!=';
      $filter_record['valore']    = $this->id;

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'attivo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 1;

      $filters[] = $filter_record;

      $opzioni['filters']   = $filters;
      $opzioni['operatore'] = 'AND';

      $list = $this->getlist($opzioni);

    }

    $idDaEscludere   = [];
    $idDaEscludere[] = $this->id;
    foreach($list as $n) {
      $idDaEscludere[] = $n->id;
    }

    $list2 = [];

    if(count($list) < $options['quanti']) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = 'NOT IN';
      $filter_record['valore']    = '('.implode(',', $idDaEscludere).')';

      $filters[] = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'attivo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 1;

      $filters[] = $filter_record;

      $opzioni              = [];
      $opzioni['sortField'] = 'data_attivazione';
      $opzioni['sortOrder'] = 'DESC';
      $opzioni['start']     = 0;
      $opzioni['quanti']    = $options['quanti'] - count($idDaEscludere) + 1;
      $opzioni['filters']   = $filters;
      $opzioni['operatore'] = 'AND';
      $opzioni['joins']     = [];

      $list2 = $this->getlist($opzioni);

    }

    $list = array_merge($list, $list2);

    $correlate = [];

    foreach($list as $n) {

      $record            = [];
      $record['titolo']  = $n->fields[ACTUAL_LANGUAGE]['titolo'];
      $record['intro']   = $n->fields[ACTUAL_LANGUAGE]['intro'];
      $record['link']    = $n->getUrl();
      $record['link']    = $record['link']['it'];
      $record['img_alt'] = Traduzioni::getLang('default', 'IMMAGINE').': '.$record['titolo'];

      $opts               = [];
      $opts['estensioni'] = 'img';
      $opts['quanti']     = 1;

      $immaginiAllegate = $n->getAllegati($opts);

      if(count($immaginiAllegate) && $immaginiAllegate[0]->fields['fileData']['nomefile']['exists'] && $immaginiAllegate[0]->fields['fileData']['nomefile']['versioni'][2]['rel_path']) {
        $record['img']       = $immaginiAllegate[0]->fields['fileData']['nomefile']['versioni'][2]['rel_path'];
        $record['img_title'] = $immaginiAllegate[0]->fields[ACTUAL_LANGUAGE]['title'];
        if($immaginiAllegate[0]->fields[ACTUAL_LANGUAGE]['alt']) {
          $record['img_alt'] = $immaginiAllegate[0]->fields[ACTUAL_LANGUAGE]['alt'];
        }
      }

      $correlate[] = $record;

    }

    $this->additionalData['correlate'] = $correlate;

  }

  function copia() {


  }

  function getAutore() {

    $options                  = [];
    $options['tableFilename'] = 'operatori';

    $OperatoreObj = new Operatore($options);

    $OperatoreObj = $OperatoreObj->getById($this->fields['operatori_id']);

    $autore = '';

    if($OperatoreObj) {

      $autore = $OperatoreObj->fields['nomecompleto'];
    }

    return $autore;
  }

  function fillEmptyData() {

    foreach($this->opts['langs'] as $l) {
      if($this->fields[$l]['lingua_attiva']) {
        if(!$this->fields[$l]['href']) {
          $this->fields[$l]['href'] = $this->generaHref($this->fields[$l]['titolo'], $l);
        }
      }
    }

  }

  function __clone() {

    $this->fields['id']           = 0;
    $this->fields['operatori_id'] = $GLOBALS['operator']->fields['id'];

    foreach($this->opts['langs'] as $l) {
      $this->fields[$l]['href'] = '';
      unset($this->fields[$l]['id']);

      $versions = [];
      $versione = 1;

      $sql = 'SELECT titolo FROM news_langs WHERE lingua="'.$l.'" AND titolo REGEXP "'.preg_quote($this->fields[$l]['titolo']).' \\\\([0-9]+\\\\)"';

      $res = $this->doQuery($sql);

      while($row = mysqli_fetch_row($res)) {
        $versioneRe = '/^'.preg_quote($this->fields[$l]['titolo']).'\s{1}\(([\d]+)\)$/';
        if(preg_match_all($versioneRe, $row[0], $m)) {
          $versions[] = $m[1][0];
        }

      }
      if($versions) {
        $versione = max($versions) + 1;
      }
      $this->$l = ['titolo' => $this->fields[$l]['titolo'].' ('.$versione.')'];
    }

  }

}

?>