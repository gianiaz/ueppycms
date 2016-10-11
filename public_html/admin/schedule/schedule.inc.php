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
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;
use Ueppy\core\Schedule;
use Ueppy\utils\Logger;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'schedule';
$mainObjOptions['forceAllLang']  = 1;


$Obj = new Schedule($mainObjOptions);

$options                  = [];
$options['tableFilename'] = 'crontab_logs';

$LoggerObj = new Logger($options);

switch($act) {

  case 'insert':

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
    }

    if(Utility::isPositiveInt($_POST['attivo'])) {
      $Obj->attivo = 1;
    } else {
      $Obj->attivo = 0;
    }

    $Obj->comando = $_POST['comando'];

    if($_POST['giorno_del_mese'] == 'TUTTI') {
      $Obj->giorno = '*';
    } else {
      if(!isset($_POST['giorni_del_mese']) || !is_array($_POST['giorni_del_mese']) || !count($_POST['giorni_del_mese'])) {
        $Obj->addError(Traduzioni::getLang($module_name, 'SELEZIONARE_ALMENO_UN_GIORNO'), 'giorni_del_mese');
      } else {
        $Obj->giorno = implode(',', $_POST['giorni_del_mese']);
      }
    }

    if($_POST['ora_del_giorno'] == 'TUTTE') {
      $Obj->ora = '*';
    } else {
      if(!isset($_POST['ore_del_giorno']) || !is_array($_POST['ore_del_giorno']) || !count($_POST['ore_del_giorno'])) {
        $Obj->addError(Traduzioni::getLang($module_name, 'SELEZIONARE_ALMENO_UN_ORA'), 'ore_del_giorno');
      } else {
        $Obj->ora = implode(',', $_POST['ore_del_giorno']);
      }
    }

    //Utility::pre($_POST);

    if($_POST['minuto_dell_ora'] == 'TUTTI') {
      $Obj->minuto = '*';
    } else {
      if(!isset($_POST['minuti_dell_ora']) || !is_array($_POST['minuti_dell_ora']) || !count($_POST['minuti_dell_ora'])) {
        $Obj->addError(Traduzioni::getLang($module_name, 'SELEZIONARE_ALMENO_UN_ORA'), 'minuti_dell_ora');
      } else {
        $Obj->minuto = implode(',', $_POST['minuti_dell_ora']);
      }
    }

    if($Obj->isValid()) {

      $opts          = [];
      $opts['debug'] = 0;

      $result = $Obj->save($opts);

      if($result) {

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

  case 'getlist':

    $opts        = [];
    $opts['raw'] = 1;

    $lista = $Obj->getlist($opts);

    $listaFormattata = [];

    foreach($lista as $record) {
      if($record['giorno'] == '*') {
        $record['giorno'] = 'Tutti';
      }
      if($record['ora'] == '*') {
        $record['ora'] = 'Tutte';
      }
      if($record['minuto'] == '*') {
        $record['minuto'] = 'Tutti';
      }
      $listaFormattata[] = $record;

    }

    $ajaxReturn['data']   = $listaFormattata;
    $ajaxReturn['result'] = 1;

    break;

  /* Attivazione/disattivazione */
  case 'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $opts          = [];
        $opts['field'] = 'attivo';

        $Obj->toggle($opts);
      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    $ajaxReturn['result'] = 1;

    break;

  case 'del':

    if(isset($_POST['id']) && $_POST['id']) {

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $Obj->delete();
        $ajaxReturn['result'] = 1;

      } else {
        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
        } else {
          $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
        }
      }
    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }

    break;

  case 'new':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $opts                 = [];
      $opts['forceAllLang'] = true;

      $Obj = $Obj->getById($_POST['id'], $opts);

    }

    $smarty->assign('Obj', $Obj);

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions    = [];
    $attivoOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $attivoOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/


    // elenco crontabs
    $crontabsDir = DOC_ROOT.REL_ROOT.'crontabs/';

    $crons = glob($crontabsDir.'*.php');

    $crontabs = [];

    $crontabs[0] = Traduzioni::getLang('default', 'SELECT_ONE');

    foreach($crons as $cronFile) {

      $cronFile = basename($cronFile);
      $cronFile = explode('.', $cronFile);
      $cronFile = array_shift($cronFile);

      $crontabs[$cronFile] = $cronFile;

    }

    $smarty->assign('crontabs', $crontabs);

    // giorno del mese
    $giorno_del_mese          = [];
    $giorno_del_mese['TUTTI'] = Traduzioni::getLang($module_name, 'TUTTI');
    $giorno_del_mese['0']     = Traduzioni::getLang($module_name, 'SPECIFICARE');

    $smarty->assign('giorno_del_mese', $giorno_del_mese);

    // giorni_del_mese
    $giorni_del_mese = [];
    for($i = 1; $i <= 31; $i++) {
      $giorni_del_mese[$i] = $i;
    }

    $smarty->assign('giorni_del_mese', $giorni_del_mese);

    // ora del giorno;
    $ora_del_giorno          = [];
    $ora_del_giorno['TUTTE'] = Traduzioni::getLang($module_name, 'TUTTE');
    $ora_del_giorno['0']     = Traduzioni::getLang($module_name, 'SPECIFICARE');

    $smarty->assign('ora_del_giorno', $ora_del_giorno);

    // ore del giorno
    $ore_del_giorno = [];
    for($i = 0; $i <= 23; $i++) {
      $ore_del_giorno[$i] = $i;
    }

    $smarty->assign('ore_del_giorno', $ore_del_giorno);

    // minuto dell'ora
    $minuto_dell_ora          = [];
    $minuto_dell_ora['TUTTI'] = Traduzioni::getLang($module_name, 'TUTTI');
    $minuto_dell_ora['0']     = Traduzioni::getLang($module_name, 'SPECIFICARE');

    $smarty->assign('minuto_dell_ora', $minuto_dell_ora);

    // ore del giorno
    $minuti_dell_ora = [];
    for($i = 0; $i <= 60; $i += 5) {
      $minuti_dell_ora[$i.''] = $i;
    }

    $smarty->assign('minuti_dell_ora', $minuti_dell_ora);


    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'svuota_logs':

    $LoggerObj->svuota();

    $urlParams = 'cmd/'.$cmd.'/act/logs';
    header('Location:'.$lm->get($urlParams));

    break;

  case 'get_logs':

    $opts              = [];
    $opts['operatore'] = 'AND';

    $LoggerObjList = $LoggerObj->getlist($opts);

    $list = [];

    foreach($LoggerObjList as $LoggerObj) {

      $record = [];

      $record['id']         = $LoggerObj->fields['id'];
      $record['file']       = nl2br($LoggerObj->fields['file']);
      $record['autore']     = $LoggerObj->fields['autore'];
      $record['created_at'] = $LoggerObj->additionalData['created_at'];
      $record['text']       = nl2br($LoggerObj->fields['text']);
      $list[]               = $record;
    }

    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;


  case 'logs':

    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/moment/min/moment-with-locales.min.js';
    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/datatables-plugins/sorting/datetime-moment.js';

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LOGS'));

    $urlParams = 'cmd/schedule/act/svuota_logs';

    $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'SVUOTA_LOGS'),
                        'icon'       => 'trash-o',
                        'attributes' => ['class' => 'btn btn-danger',
                                         'href'  => $lm->get($urlParams),
                                         'id'    => 'emptyLogs']];
    $headerButtons[] = $BUTTONS['btnClose'];

    break;
  case '':
    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $urlParams = 'cmd/'.$cmd.'/act/logs';

    $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'LOGS'),
                        'icon'       => 'file-text-o',
                        'attributes' => ['class' => 'btn btn-info',
                                         'href'  => $lm->get($urlParams)]];

    $headerButtons[] = $BUTTONS['btnNew'];
    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;

}