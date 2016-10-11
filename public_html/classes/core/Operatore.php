<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (06/06/16, 8.58)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

namespace Ueppy\core;

use Ueppy\utils\Utility;

class Operatore extends Dba {

  /**
   * Ridefinisce il metodo fillresults per aggiungere proprietà che non sono direttamente
   * nella tabella utenti.
   *
   * In particolare all'interno del ciclo viene istanziato l'oggetto gruppo e in seguito valorizzata la proprietà:
   *
   * <code>
   * $obj->additionalData['grp_fields']
   * </code>
   *
   * Con i dati dell'array fields del gruppo corrispondente.
   *
   * All'interno del metodo come succede per la classe Dba dobbiamo verificare lo stato della proprietà getById,
   * se impostata a true siamo arrivati a questo metodo con la richiesta di un unico oggetto e non dal metodo getlist.
   * Nel caso siamo arrivati da getById dobbiamo ricreare un array contentente l'oggetto da poter ciclare e poi restituire l'oggetto dopo
   * il ciclo. Con questo escamotage riusciamo a manipolare i dati che arrivano dai due diversi metodi nello stesso modo.
   * @return mixed Object|Array|boolean
   */
  function fillresults() {

    $results = parent::fillresults();

    if($this->getById) {
      $results = [$results];
    }

    $arr = [];

    foreach($results as $obj) {

      if(isset($obj->fields['id'])) {

        if($obj->fields['gruppi_id']) {


          $options                  = [];
          $options['tableFilename'] = 'gruppi';

          $GruppoObj = new Gruppo($options);
          $GruppoObj = $GruppoObj->getById($obj->fields['gruppi_id']);

          if($GruppoObj) {
            $obj->additionalData['grp_fields'] = $GruppoObj->fields;
          } else {
            $obj->additionalData['grp_fields'] = [];
          }

        }

      }


      $arr[] = $obj;

    }

    if($this->getById) {
      return $arr[0];
    } else {
      return $arr;
    }

  }

  /**
   * Metodo per il login, controlla che l'utente abbia inserito i dati corretti,
   * che l'utente sia attivo e che faccia parte di un gruppo attivo.
   * In caso di esito positivo vengono valorizzate:
   *
   * <code>
   *   $_SESSION['LOG_INFO']['UID'] = id_utente;
   *   $_SESSION['LOG_INFO']['LOGGED'] = 1;
   * </code>
   *
   * @param string $user Nome utente per cui effettuare il login
   * @param  string $pass Password in chiaro, ne verrà ricavato l'md5 per confrontare il valore presente nel db
   *
   * @return boolean true|false, in caso di true mette in sessione i dati dell'utente:
   */
  function login($user = "", $pass = "") {

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'attivo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'gruppi.attivo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'username';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $user;

    $filters[] = $filter_record;

    $opts = [];

    $opts['fields']       = false;
    $opts['langFields']   = false;
    $opts['forceAllLang'] = false;
    $opts['sortField']    = '';
    $opts['sortOrder']    = '';
    $opts['start']        = '';
    $opts['quanti']       = '';
    $opts['debug']        = 0;
    $opts['countOnly']    = false;
    $opts['filters']      = $filters;
    $opts['operatore']    = 'AND';
    $opts['joins']        = [];

    $join              = [];
    $join['table']     = 'gruppi';
    $join['alias']     = '';
    $join['on1']       = 'id';
    $join['operatore'] = '=';
    $join['on2']       = 'operatori.gruppi_id';

    $opts['joins'][] = $join;

    $list = $this->getlist($opts);

    if(count($list)) {
      $u = $list[0];
      require_once(LIB.'PasswordHash.php');
      $hasher = new \PasswordHash(8, false);
      //Utility::pre('Pass fornita:'.$pass."\n".'Hash: '.$u->fields['passwd']."\nRisultato:".$hasher->CheckPassword($pass, $u->fields['passwd']));
      //
      if($hasher->CheckPassword($pass, $u->fields['passwd'])) {
        $_SESSION['LOG_INFO']['UID']    = $u->id;
        $_SESSION['LOG_INFO']['LOGGED'] = 1;
        if(version_compare(PHP_VERSION, '5.4.0') >= 0 && strlen($u->fields['passwd']) !== 60) {
          require_once(LIB.'PasswordHash.php');
          $hasher    = new PasswordHash(8, false);
          $hash      = $hasher->HashPassword($pass);
          $u->passwd = $hash;
          $u->save();
        }

        return $u->fields['passwd'];
      } else {
        return false;
      }
    } else {
      return false;
    }

  }

  /**
   * Controlla le autorizzazioni su una determinata pagina partendo dal nome file,
   * e dal livello della pagina.
   *
   * Il livello è un indice di importanza della pagina ma non ha a che fare con i permessi.
   * Serve per distinguere certe classi di pagine (ad esempio quelle che hanno livello 100
   * saranno le pagine pubbliche, mentre quelle che hanno livello da 99 in su sono di amministrazione.
   * Visto che i menu pubblici/privati risiedono tutti nella stessa tabella
   * potrebbero esserci 2 pagine chiamate ad es. contatti, una per il lato amministrativo
   * e una per il lato pubblico, quindi il metodo richiede un secondo parametro che indichi in quale livello massimo cercare.
   *
   * @param string $nome_pagina Nome del file da includere
   * @param int $level Livello massimo in cui fare la ricerca.
   * @param boolean $debug Imposta il debug a true stampando alcune informazioni a video.
   */
  function hasRights($nome_pagina, $max_level = 99, $debug = 0) {

    if($nome_pagina == 'default') return true;

    if($this->fields['attivo'] && $nome_pagina) {


      $lingue = explode(',', SET_LANGUAGES);

      $options                  = [];
      $options['tableFilename'] = 'menu';
      $menuObj                  = new Menu($options);

      $opts['name']     = $nome_pagina;
      $opts['maxLevel'] = $max_level;

      $menuObj = $menuObj->getByName($opts);

      if($menuObj) {

        if($this->additionalData['grp_fields']['all_elements']) {

          return true;

        } else {

          $filters = [];

          $filter_record              = [];
          $filter_record['chiave']    = 'gruppi_id';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $this->fields['gruppi_id'];

          $filters[] = $filter_record;

          $filter_record              = [];
          $filter_record['chiave']    = 'menu_id';
          $filter_record['operatore'] = '=';
          $filter_record['valore']    = $menuObj->fields['id'];

          $filters[] = $filter_record;

          $opts              = [];
          $opts['fields']    = ['gruppi_id'];
          $opts['start']     = 0;
          $opts['quanti']    = 1;
          $opts['debug']     = $debug;
          $opts['countOnly'] = true;
          $opts['filters']   = $filters;
          $opts['operatore'] = 'AND';
          $opts['joins']     = [];

          $options                  = [];
          $options['tableFilename'] = 'permessi';
          $options['debug']         = 0;

          $Obj = new Dba($options);

          $list = $Obj->getlist($opts);

          if($list) {
            if($debug) {
              $this->log('Permessi abilitati');
            }

            return true;
          } else {
            if($debug) {
              $this->log('Permesso negato');
            }

            return false;
          }

        }

      } else {
        if($debug) {
          $this->log('Permesso negato');
        }

        return false;
      }

    } else {
      if($debug) {
        $this->log('Permesso negato');
      }

      return false;
    }
  }

  /**
   * Metodo che ritorna un array di oggetti menu, a seconda dell'operatore recuperato con
   * il metodo getById ritornerà un array compilato con oggetti menu per i quali l'utente
   * ha i permessi.
   *
   * @param int $start livello minimo del menu
   * @param int $end livello massimo del menu
   * @param boolean $debug Imposta il debug a true stampando alcune informazioni a video.
   * @return array di Oggetti menu
   */
  function getMenu($start = 0, $end = 99, $debug = 0) {

    $return = [];

    $filters = [];
    $joins   = [];


    $filter_record              = [];
    $filter_record['chiave']    = 'attivo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = 1;

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '>';
    $filter_record['valore']    = $start;

    $filters[] = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['operatore'] = '<';
    $filter_record['valore']    = $end;

    $filters[] = $filter_record;

    if(!$this->fields['super_admin']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'superadmin';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '0';

      $filters[] = $filter_record;
    }

    if(!$this->additionalData['grp_fields']['all_elements']) {

      $opts['joins'] = [];

      $join              = [];
      $join['table']     = 'permessi';
      $join['alias']     = '';
      $join['on1']       = 'menu_id';
      $join['operatore'] = '=';
      $join['on2']       = 'menu.id';

      $joins[] = $join;

      $filter_record              = [];
      $filter_record['chiave']    = 'permessi.gruppi_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $this->fields['gruppi_id'];

      $filters[] = $filter_record;

    }

    $options                  = [];
    $options['tableFilename'] = 'menu';
    $options['files']         = ['img0', 'img1'];
    $options['debug']         = $debug;
    $options['imgSettings']   = [];

    $imgSetting               = [];
    $imgSetting['dimensione'] = '72x72';
    $imgSetting['tipo']       = 'exact';

    $options['imgSettings']['img0'][] = $imgSetting;

    $imgSetting               = [];
    $imgSetting['dimensione'] = '24x24';
    $imgSetting['tipo']       = 'exact';

    $options['imgSettings']['img1'][] = $imgSetting;

    $menuObj = new menu($options);

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';
    $opts['debug']     = $debug;
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';
    $opts['joins']     = $joins;

    $lista = $menuObj->getlist($opts);

    $list    = [];
    $parents = [];

    foreach($lista as $Obj) {
      $parents[] = $Obj->fields['parent'];
    }
    $parents = array_unique($parents);

    if(count($parents)) {

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'attivo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = 1;
      $filters[]                  = $filter_record;

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '('.implode(',', $parents).')';
      $filters[]                  = $filter_record;

      $menuObj = new menu($options);

      $opts              = [];
      $opts['sortField'] = 'ordine';
      $opts['sortOrder'] = 'ASC';
      $opts['debug']     = $debug;
      $opts['filters']   = $filters;
      $opts['operatore'] = 'AND';

      $genitori = $menuObj->getlist($opts);

      foreach($genitori as $o) {

        $return[$o->fields['id']] = $o;

      }

    }

    foreach($lista as $o) {
      if(isset($return[$o->fields['parent']])) {
        $return[$o->fields['parent']]->additionalData['childs'][] = $o;
      }
    }

    return $return;

  }

  /**
   * Dato uno username restituisce l'oggetto che lo rappresenta il record, se il record non esite ritorna false.
   * @param string $user Username dell'utente richiesto.
   * @return mixed object|false
   */
  function getByUsername($user) {

    $filters = [];

    $filter_record['chiave']    = 'username';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = $user;

    $filters[] = $filter_record;

    $opts              = [];
    $opts['start']     = '0';
    $opts['quanti']    = '1';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';
    $opts['debug']     = 0;

    $list = $this->getlist($opts);

    if(count($list)) {

      return $list[0];

    } else {

      return false;

    }

  }

  /**
   * Ridefinisce il metodo save, se la proprietà $this->fields['passwd'] è un array
   * vuol dire che sono o in inserimento o in modifica di un utente per cui ho cambiato
   * la pass, riassegna al campo l'md5 da salvare su db e richiama il metodo save
   * del parent.
   *
   * @return int 0,1,2 come per il parent
   */
  function save($opts = null) {

    if(strlen($this->fields['passwd']) !== 60) {
      require_once(LIB.'PasswordHash.php');
      $hasher                 = new \PasswordHash(8, false);
      $hash                   = $hasher->HashPassword($this->fields['passwd']);
      $this->fields['passwd'] = $hash;
    }

    return parent::Save($opts);

  }

  function isBasic() {

    return $this->level == 0;

  }

  /**
   * Il metodo ritorna vero se l'utente è assegnato a livello normale o superiore.
   * @return boolean true|false
   */
  function isMedium() {

    return $this->level >= 10;

  }

  /**
   * Il metodo ritorna vero se l'utente è assegnato a livello advanced
   * @return boolean true|false
   */
  function isAdvanced() {

    return $this->level >= 20;
  }

  /**
   * Il metodo ritorna vero se l'utente è superadmin o è assegnato ad un gruppo amministrativo
   * @return boolean true|false
   */
  function isAdmin() {

    return ($this->fields['super_admin'] || in_array($this->fields['gruppi_id'], explode(',', SET_GRUPPI_ADMIN)));
  }

  /**
   * Il metodo ritorna vero se l'utente è superadmin.
   * @return boolean true|false
   */
  function isSuperAdmin() {

    return $this->fields['super_admin'];
  }

  function isGod() {

    //Utility::pre($this->fields);

    return intval(in_array($this->fields['username'], ['gianiaz']));
  }

  function getGruppiInferiori() {

    $sql = 'SELECT id, nome FROM gruppi WHERE ordine > '.$this->additionalData['grp_fields']['ordine'];

    $res = $this->doQuery($sql);

    $gruppi = [];

    while($row = mysqli_fetch_row($res)) {
      $gruppi[$row[0]] = $row[1];
    }

    return $gruppi;
  }

  function isMostImportant(Operatore $op) {

    $gruppi = $this->getGruppiInferiori();

    if(in_array($op->gruppi_id, $gruppi)) {
      return true;
    }

    return false;


  }


  function apiLogin($params) {

    $return['result']    = 0;
    $return['error']     = false;
    $return['errorCode'] = 'UNDEFINED';
    $return['data']      = [];

    if(!isset($params['username']) || !$params['username'] || !isset($params['password']) || !$params['password']) {
      $return['error']     = 'Parametri in ingresso errati, ricontrolla i dati spediti in post:'."\n".print_r($_POST, true);
      $return['errorCode'] = 'BAD_PARAMS';
    } else {
      if($this->login($params['username'], $params['password'])) {
        $object = $this->getByUsername($params['username']);

        $return['result'] = 1;

        $opzioni           = [];
        $opzioni['expose'] = ['id', 'nomecompleto', 'username', 'email'];


        $return['data'] = array_merge($object->ajaxResponse($opzioni), ['sid' => session_id()]);
      } else {
        $return['error']     = 'Login fallito';
        $return['errorCode'] = 'LOGIN_FAILED';
      }
    }

    return $return;

  }
}