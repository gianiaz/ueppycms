<?php
/***************/
/** v.1.04    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.04 (07/11/2015, 7.40)                                                                    **/
/** - Introdotto l'utilizzo dell'autoloading                                                     **/
/**                                                                                              **/
/** v.1.03 (05/09/2015)                                                                          **/
/** - Versione stabile, a partire da versione 3.1.03                                             **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Operatore;
use Ueppy\core\Gruppo;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

$imgSetting                              = [];
$imgSetting['dimensione']                = '512x512';
$imgSetting['tipo']                      = 'crop';
$imgSetting['options']['type_of_resize'] = 'lossless';

$mainObjOptions                            = [];
$mainObjOptions['tableFilename']           = 'operatori';
$mainObjOptions['loadRules']               = true;
$mainObjOptions['debug']                   = 0;
$mainObjOptions['imgSettings']['avatar'][] = $imgSetting;

switch($act) {

  /* Estrazione degli elementi per la tabella di elenco */
  case 'getlist':

    $Obj = new Operatore($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'id';
    $filter_record['operatore'] = '!=';
    $filter_record['valore']    = $operator->fields['id'];

    $filters[] = $filter_record;

    if(!$operator->fields['super_admin']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'super_admin';
      $filter_record['operatore'] = '!=';
      $filter_record['valore']    = '1';

      $filters[] = $filter_record;

      $options                  = [];
      $options['tableFilename'] = 'gruppi';

      $GruppoObj = new Gruppo($options);

      $filters_grp = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'ordine';
      $filter_record['operatore'] = '>=';
      $filter_record['valore']    = $operator->additionalData['grp_fields']['ordine'];
      $filters_grp[]              = $filter_record;

      $opts              = [];
      $opts['filters']   = $filters_grp;
      $opts['operatore'] = 'AND';

      $GruppoObjList = $GruppoObj->getlist($opts);

      $grp_array = [];

      foreach($GruppoObjList as $GruppoObj) {
        $grp_array[] = $GruppoObj->fields['id'];
      }

      if(count($grp_array)) {
        $filter_record              = [];
        $filter_record['chiave']    = 'gruppi_id';
        $filter_record['valore']    = '('.implode(',', $grp_array).')';
        $filter_record['operatore'] = 'IN';
        $filters[]                  = $filter_record;
      }

    }

    $joins = [];

    $join              = [];
    $join['table']     = 'gruppi';
    $join['alias']     = 'g';
    $join['on1']       = 'id';
    $join['on2']       = 'operatori.gruppi_id';
    $join['operatore'] = '=';

    $joins[] = $join;

    $Obj->addField('g.nome as gruppo_string');

    $sort_field = 'nomecompleto';
    $sort_order = 'asc';

    $opts              = [];
    $opts['sortField'] = $sort_field;
    $opts['sortOrder'] = $sort_order;
    $opts['raw']       = 1;
    $opts['debug']     = 0;
    $opts['countOnly'] = false;
    $opts['filters']   = $filters;
    $opts['joins']     = $joins;
    $opts['operatore'] = 'AND';

    $lista = $Obj->getlist($opts);

    $list = [];

    foreach($lista as $record) {
      unset($record['passwd']);
      $list[] = $record;
    }

    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;


  /* Salvataggio del form di inserimento */
  case 'insert':

    $Obj = new Operatore($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    } else {
      $opts                      = [];
      $opts['field']             = 'username';
      $opts['rule']              = 'Unico';
      $opts['args']              = [];
      $opts['args']['table']     = 'operatori';
      $opts['args']['confronto'] = 'id';
      $opts['args']['escludi']   = $_POST['id'];

      $Obj->addRule($opts);
      $Obj->username = $_POST['username'];
    }

    $Obj->nomecompleto = $_POST['nomecompleto'];
    $Obj->email        = $_POST['email'];

    if(!($Obj->fields['id'])) {
      $Obj->cancellabile = 1;
    }

    if(isset($_POST['super_admin']) && $_POST['super_admin']) {
      $Obj->super_admin = 1;
    } else {
      $Obj->super_admin = 0;
    }

    if(Utility::isPositiveInt($_POST['attivo'])) {
      $Obj->attivo = 1;
    } else {
      $Obj->attivo = 0;
    }
    $Obj->level     = $_POST['level'];
    $Obj->gruppi_id = $_POST['gruppi_id'];

    // campo pass - INIZIO
    if(isset($Obj->fields['id']) && $Obj->fields['id']) {  // update

      if(isset($_POST['passwd']) && $_POST['passwd']) {
        if($_POST['passwd'] == $_POST['password_conferma']) {
          $Obj->passwd = $_POST['passwd'];
        } else {
          $Obj->addError(Traduzioni::getLang($module_name, 'PASS_NON_CORRISPONDONO'));
        }
      }

    } else { // inserimento

      if($_POST['passwd'] == $_POST['password_conferma']) {
        $Obj->passwd = $_POST['passwd'];
      } else {
        $Obj->addError(Traduzioni::getLang($module_name, 'PASS_NON_CORRISPONDONO'), ['passwd', 'password_conferma']);
      }

    }

    if(isset($_POST['avataraction']) && $_POST['avataraction'] == 'del') {
      // se cancello l'immagine cancello anche alt e title che non riguardano quell'immagine
      $Obj->avatar = ['', false];
    }

    if(isset($_FILES) && count($_FILES)) {
      if(isset($_FILES['avatar']['name']) && $_FILES['avatar']['size']) {
        $Obj->avatar = [$_FILES['avatar']['tmp_name'], $_FILES['avatar']['name']];
      }
    }

    if($Obj->isValid()) {

      $result = $Obj->save();

      if($result) {

        // inserito
        if($result == 1) {
          $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));
        } elseif($result == 2) {
          $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));
        }

        $ajaxReturn           = [];
        $ajaxReturn['result'] = 1;
        $ajaxReturn['dati']   = $Obj->ajaxResponse();

      } else {
        $ajaxReturn['result'] = 0;
        $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'ERRORE_INDEFINITO')];
        $ajaxReturn['wrongs'] = [];

      }

    } else {

      $opts['glue']         = false;
      $ajaxReturn['result'] = 0;
      $ajaxReturn['errors'] = $Obj->getErrors($opts);
      $ajaxReturn['wrongs'] = array_keys($Obj->wrongFields);

    }


    break;

  /* Assunzione dei privilegi di un altro utente */
  case 'privileges':

    if($operator->isSuperAdmin()) {

      $_SESSION['OLD_LOG_INFO']    = $_SESSION['LOG_INFO'];
      $_SESSION['LOG_INFO']['UID'] = $_POST['selezione'];

      $urlParams = '';

      Header('Location:'.$lm->get($urlParams));
    } else {
      $urlParams = 'cmd/'.$cmd;
      Header('Location:'.$lm->get($urlParams));
    }

    die;

    break;


  /* Riempimento del form di modifica/inserimento */
  case 'new':

    $Obj = new Operatore($mainObjOptions);

    if(isset($_POST['id']) && $_POST['id']) {
      $Obj = $Obj->getById($_POST['id']);
      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));
    } else {
      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));
    }

    /** SUPERADMIN OPTIONS - INIZIO **/

    $super_adminOptions    = [];
    $super_adminOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $super_adminOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('super_adminOptions', $super_adminOptions);

    /** SUPERADMIN OPTIONS - FINE   **/

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions    = [];
    $attivoOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $attivoOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/

    $options                  = [];
    $options['tableFilename'] = 'gruppi';
    $options['debug']         = 0;

    $GruppoObj = new Gruppo($options);

    $filters = [];

    $filter_record['chiave']    = 'attivo';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = '1';

    $filters[] = $filter_record;

    if(!$operator->fields['super_admin']) {

      $filter_record              = [];
      $filter_record['chiave']    = 'ordine';
      $filter_record['operatore'] = '>=';
      $filter_record['valore']    = $operator->additionalData['grp_fields']['ordine'];
      $filters[]                  = $filter_record;

    }

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $GruppoObjList = $GruppoObj->getlist($opts);

    // preparo l'array per usare il plugin di smarty che crea i select
    $lista_gruppi_options    = [];
    $lista_gruppi_options[0] = Traduzioni::getLang('default', 'SELECT_ONE');

    foreach($GruppoObjList as $GruppoObj) {
      $lista_gruppi_options[$GruppoObj->fields['id']] = $GruppoObj->fields['nome'];
    }

    $smarty->assign('lista_gruppi_options', $lista_gruppi_options);

    $smarty->assign('Obj', $Obj);

    /*  LIVELLI UTENTI */
    $levels    = [];
    $levels[0] = Traduzioni::getLang($module_name, 'BASIC');
    if($operator->fields['level'] >= 10) {
      $levels[10] = Traduzioni::getLang($module_name, 'NORMALE');
    }
    if($operator->fields['level'] >= 20) {
      $levels[20] = Traduzioni::getLang($module_name, 'ADVANCED');
    }
    $smarty->assign('levels', $levels);
    /*  LIVELLI UTENTI */

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  /* Attivazione/disattivazione di un operatore */
  case 'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new Operatore($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $opts          = [];
        $opts['field'] = 'attivo';

        $Obj->toggle($opts);
      }

    }

    $ajaxReturn['result'] = 1;

    break;

  /* Cancellazione di un operatore */
  case 'del':

    if(isset($_POST['id']) && $_POST['id']) {

      $options                  = [];
      $options['tableFilename'] = 'operatori';
      $options['debug']         = 0;

      $Obj = new Operatore($options);

      $Obj = $Obj->getById($_POST['id']);

      if($operator->isMostImportant($Obj) || $operator->isSuperAdmin()) {

        $Obj->delete();

        $ajaxReturn['result'] = 1;

      } else {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = Traduzioni::getLang('default', 'NOT_AUTH');

      }

    }


    break;

  /* elenco */
  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $headerButtons[] = $BUTTONS['btnNew'];

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}

