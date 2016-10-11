<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (11/07/16, 10.16)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
session_start();
header('Content-type: text/html;charset=utf-8');
require('vendor/autoload.php');
require("conf/boot.php");
require(LIB.'ueppy/function.ueppy_form_field.php');
define('DO_QUERY_CACHE', false);

use Ueppy\core\Dba;
use Ueppy\core\Db;
use Ueppy\core\Operatore;
use Ueppy\core\Gruppo;
use Ueppy\core\Lingue;
use Ueppy\core\LinkManager;
use Ueppy\core\UeppySmarty;
use Ueppy\core\Menu;
use Ueppy\core\Traduzioni;
use Ueppy\utils\Time;

use Ueppy\utils\Logger;
use Ueppy\utils\Utility;
use Ueppy\utils\Ueppy;

$opzioni                 = [];
$opzioni['template_dir'] = DOC_ROOT.REL_ROOT.ADMIN_DIR;
$opzioni['compile_dir']  = DOC_ROOT.REL_ROOT.UPLOAD.'smarty/admin/templates_c/';

$smarty = new UeppySmarty($opzioni);

$smarty->assign('DOC_ROOT', DOC_ROOT.REL_ROOT);

$smarty->assign('phpversion', phpversion());
$GET_REC = Utility::ReadUrl($_SERVER['QUERY_STRING']);

require(LIB.'ueppy/smarty.functions.php');

$smarty->assign('base_href', HOST.REL_ROOT);

require_once(DOC_ROOT.REL_ROOT.UPLOAD.'settings/settings.php');

// autoSaveTime
if(defined('SET_AUTOSAVETIME')) {
  $smarty->assign('autoSaveTime', SET_AUTOSAVETIME * 1000);
} else {
  $smarty->assign('autoSaveTime', 60000);
}
$langs = explode(",", SET_LANGUAGES);


$actual_language = 'it';

define('ACTUAL_LANGUAGE', $actual_language);
$smarty->assign('ACTUAL_LANGUAGE', $actual_language);

$sett_smarty['GRUPPI_ADMIN'] = explode(',', $sett_smarty['GRUPPI_ADMIN']);

$smarty->assign('SETTINGS', $sett_smarty);

$smarty->assign('IMGHEAD', SET_IMGHEAD);


/*
 * Gestore link, imposto i valori di default per la sezione admin,
 * cambierò solo il valore di params nei diversi moduli a seconda dell'esigenza
 */

$opzioni         = [];
$opzioni['page'] = 'admin';
$lm              = Linkmanager::getInstance($opzioni);

// creo un array delle lingue disponibili, nella forma [sigla]=>[descrizioneestesa]
$lingue = [];
$i      = 0;

$filters = [];

$filter_record              = [];
$filter_record['chiave']    = 'attivo_admin';
$filter_record['operatore'] = '=';
$filter_record['valore']    = '1';

$filters[] = $filter_record;

$options                  = [];
$options['tableFilename'] = 'languages';

$langObj = new Lingue($options);

$opts              = [];
$opts['filters']   = $filters;
$opts['operatore'] = 'AND';

$lingue_disponibili = $langObj->getlist($opts);

$flags = [];

foreach($lingue_disponibili as $langObj) {
  $lingue[$langObj->fields['sigla']]      = $langObj->fields['estesa'];
  $lingue_json[$langObj->fields['sigla']] = $langObj->fields['estesa'];
  if($langObj->fields['fileData']['img0']['exists']) {
    $flags[$langObj->fields['sigla']] = $langObj->fields['fileData']['img0']['versioni'][0]['url'];
  } else {
    $flags[$langObj->fields['sigla']] = false;
  }
  $i++;
}

$langs = array_keys($lingue);
$smarty->assign('lingue', $lingue);


$smarty->assign('helptipancor', '&nbsp;');

$smarty->assign('lingue_json', json_encode($lingue_json));

// comando
$cmd = 'default';
// azione
$act = '';

// verra messo in un campo hidden nel file header.tpl, utile per alcuni controlli js

// ASSEGNAZIONE DELLA VAR cmd, CON LA
// QUALE DECIDERO' QUALI MODULI CARICARE, E QUALI CLASSI
// QUI POSSO IMPOSTARE IL VALORE PER LA PAGINA DA CARICARE
// DI DEFAULT
if(isset($GET_REC['cmd'])) {
  $cmd = basename($GET_REC['cmd']);
}
$smarty->assign('cmd', $cmd);
$module_name = $cmd;
$smarty->assign('module_name', $module_name);

/*
 * Ogni pagina conterrà uno switch, che valuterà il secondo parametro passato
 * nell'url (l'action), qui lo assegno, e poi lo valuterò all'interno dei
 * moduli per quanto riguarda il php e i templates
 */
if(isset($GET_REC['act'])) {
  $act = $GET_REC['act'];
}
$smarty->assign('act', $act);

$smarty->assign('rel_root', REL_ROOT);

/**
 * Logger
 */
$options                   = [];
$options['tableFilename']  = 'logs';
$options['autoLoadLabels'] = false;
$options['logActions']     = false;
$LOGGER                    = new Logger($options);

$operator = false;

// creo l'oggetto traduzioni, che avrà una sua istanza che sarà sempre la stessa.
$TRADUZIONI_OPTION               = [];
$TRADUZIONI_OPTION['sezione']    = 'admin';
$TRADUZIONI_OPTION['logActions'] = false;
$TRADUZIONI_OPTION['langs']      = ['en', 'de', 'fr', 'ru', 'es'];
Traduzioni::getInstance($TRADUZIONI_OPTION);


if((!isset($_SESSION['LOG_INFO']) || !isset($_SESSION['LOG_INFO']['LOGGED']) || !$_SESSION['LOG_INFO']['UID']) &&
  isset($_COOKIE['upycms_user']) && $_COOKIE['upycms_user'] && isset($_COOKIE['upycms_pass']) && $_COOKIE['upycms_pass']
) {

  $options                  = [];
  $options['tableFilename'] = 'operatori';
  $operator                 = new Operatore($options);

  $userPotenziale = preg_replace('/\W/', '', $_COOKIE['upycms_user']);
  $operator       = $operator->getByUsername($userPotenziale);

  if($operator && $operator->fields['passwd'] == $_COOKIE['upycms_pass']) {
    if($operator->fields['attivo'] && $operator->additionalData['grp_fields']['attivo']) {
      $_SESSION['LOG_INFO']['UID']    = $operator->fields['id'];
      $_SESSION['LOG_INFO']['LOGGED'] = 1;
    } else {
      $_SESSION = false;
    }
  }

  $LOGGER->addLine(['text' => 'Accesso al pannello tramite cookie (user: '.$userPotenziale.') - '.$_SERVER['REMOTE_ADDR'], 'azione' => 'LOGIN']);

}


define('DEC_POINT', Traduzioni::getLang('default', 'DEC_POINT'));
$smarty->assign('DEC_POINT', DEC_POINT);
define('SET_THOUSANDS_SEP', Traduzioni::getLang('default', 'THOUSANDS_SEP'));
define('DATE_FORMAT', Traduzioni::getLang('default', 'DATE_FORMAT'));

if(isset($_SESSION['OLD_LOG_INFO']['LOGGED']) && $_SESSION['OLD_LOG_INFO']['UID']) {

  $options                  = [];
  $options['tableFilename'] = 'operatori';
  $realOperator             = new Operatore($options);
  $realOperator             = $realOperator->getById($_SESSION['OLD_LOG_INFO']['UID']);

  $smarty->assign('realOperator', $realOperator->nomecompleto);

}
$today = new Time();
$smarty->assign('today', $today->format('%A %e %B %Y'));

if(isset($_SESSION['LOG_INFO']['LOGGED']) && $_SESSION['LOG_INFO']['UID']) {

  if(SET_ENABLE_ECOMMERCE) {

    $id_listino = 0;

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'predefinito';
    $filter_record['operatore'] = '=';
    $filter_record['valore']    = 1;
    $filters[]                  = $filter_record;

    $options                  = [];
    $options['tableFilename'] = 'listini';

    $l = new Dba($options);

    $opts              = [];
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $l = $l->getlist($opts);

    if(count($l)) {
      $id_listino = $l[0]->fields['id'];
    }

    if(!$id_listino) {
      Utility::error(Traduzioni::getLang('ecommerce', 'LISTINO_PRINCIPALE_NON_ASSEGNATO'));
    }
    define('LISTINO_SELEZIONATO', $id_listino);

  }

  $options                  = [];
  $options['tableFilename'] = 'operatori';
  $operator                 = new Operatore($options);
  $operator                 = $operator->getById($_SESSION['LOG_INFO']['UID']);

  if(!$operator->fields['attivo'] || !$operator->additionalData['grp_fields']['attivo']) {
    header('Location:'.$lm->get('cmd/logout'));
    die;
  }

  $_SESSION['simogeoFM']['ROOT'] = DOC_ROOT.REL_ROOT.UPLOAD.'userfiles';

  // ISTANZIO L'OGGETTO MENU
  $options                  = [];
  $options['tableFilename'] = 'menu';

  $imgSetting               = [];
  $imgSetting['dimensione'] = '72x72';
  $imgSetting['tipo']       = 'exact';

  $options['imgSettings']['img0'][] = $imgSetting;

  $imgSetting               = [];
  $imgSetting['dimensione'] = '24x24';
  $imgSetting['tipo']       = 'exact';

  $options['imgSettings']['img1'][] = $imgSetting;

  $menuObj = new Menu($options);

  $smarty->assign('operator', $operator);

  if($operator->additionalData['grp_fields']['ordine'] > 2) {
    $menulist = $operator->getMenu(0, 100, 0);
  } else {
    $menulist = $operator->getMenu(0, 90, 0);
  }

  $tmpMenuList = $menulist;

  $menulist = [];

  foreach($tmpMenuList as $mnu) {
    $tmpMenuChilds                 = $mnu->additionalData['childs'];
    $mnu->additionalData['childs'] = [];
    foreach($tmpMenuChilds as $child) {
      $child->additionalData['level2'] = intval($child->fields['level'] / 10);
      $mnu->additionalData['childs'][] = $child;
    }
    $menulist[] = $mnu;
  }

  $smarty->assign('menu', $menulist);

}

// STILI
$opts        = [];
$opts['var'] = 'css_admin';
$smarty->loadCSS($opts); // Carico i fogli di stile

$pathCSS = [];

if(PRODUZIONE) {
  // JS
  $opts          = [];
  $opts['var']   = 'jsAdminProduzione';
  $opts['debug'] = 0;
} else {
  // JS
  $opts          = [];
  $opts['var']   = 'js_admin';
  $opts['debug'] = 0;
}

$smarty->loadJS($opts); // Carico i js

Traduzioni::loadJs($smarty);


$pathJS  = [];
$pathCSS = [];

// qui comincia il giro per caricare i moduli e i template, ci sono 2 template di default,
// che vengono caricati in automatico, se vengono valorizzate 2 variabili :
//
// 1 - $errore (se questa variabile non è vuota, viene mostrato il template errore.tpl, mostrando il
// contenuto della variabile).
// 2 - $messaggio (se questa variabile non è vuota, viene mostrato il template errore.tpl, mostrando il
// contenuto della variabile).

include('conf/globals.admin.php');

$detect = new Mobile_Detect();

if($detect->isMobile() && !$detect->isTablet()) {
  $mobile = true;
} elseif($detect->isTablet()) {
  $tablet = true;
}

$tablet = false;

if($mobile || $tablet) {
  $UPLOADCLASSICO = 1;
}

$GIANIAZ = false;

$options                  = [];
$options['tableFilename'] = 'allegati';

$AllegatiObj = new \Ueppy\core\Allegati($options);

$AllegatiObj->rescanRewrite();

if($operator || in_array($cmd, ['login', 'logout'])) {

  if($operator && $operator->username == 'gianiaz') {
    $GIANIAZ = true;
  }

  if(file_exists(ADMIN_DIR.$cmd.'/'.$cmd.'.inc.php')) {

    if(!in_array($cmd, $moduliSenzaTraduzioni) && !Traduzioni::moduleExists($cmd)) {
      Utility::pre('Chiavi di lingua per il modulo '.$cmd.' mancanti');
      die;
    }

    if(file_exists(DOC_ROOT.REL_ROOT.ADMIN_DIR.$cmd.'/'.$cmd.'.css')) {
      $pathCSS[] = DOC_ROOT.REL_ROOT.ADMIN_DIR.$cmd.'/'.$cmd.'.css';
    }
    if(file_exists(DOC_ROOT.REL_ROOT.ADMIN_DIR.$cmd.'/'.$cmd.'.js')) {
      $pathJS[] = DOC_ROOT.REL_ROOT.ADMIN_DIR.$cmd.'/'.$cmd.'.js';
    }

    // autoSaveTime
    if(defined('SET_AUTOSAVETIME')) {
      $autoSaveTime = SET_AUTOSAVETIME;
    } else {
      $autoSaveTime = 60;
    }

    if(in_array($module_name, $moduliSenzaAuth) || $operator->hasRights($module_name)) {

      require(ADMIN_DIR.$cmd.'/'.$cmd.'.inc.php');

      if(count($ajaxReturn)) {
        echo str_replace("\n", '', json_encode($ajaxReturn));
        die;
      }

      $smarty->assign('READONLY', $READONLY);
      $smarty->assign('UPLOADCLASSICO', $UPLOADCLASSICO);
      $smarty->assign('headerButtons', $headerButtons);
      $smarty->assign('footerButtons', $footerButtons);

      $opts         = [];
      $opts['path'] = $pathJS;

      $smarty->addJS($opts);

      $opts         = [];
      $opts['path'] = $pathCSS;

      $smarty->addCSS($opts);

    } else {

      $errore = Traduzioni::getLang('default', 'NOT_AUTH');

    }

    if(isset($errori)) {
      $smarty->assign('errori', $errori);
    }

    // se vuoi caricare diversi template questo è il punto giusto per farlo,
    // l'importante è fare il display dei template dopo che tutta la logica php
    // sia stata eseguita, in questo modo se devi lavorare sugli header della pagina
    // puoi farlo perchè a video non è stato mostrato ancora nulla.
    $smarty->display('generic/header.tpl');
    if(isset($errore) && $errore) {

      $smarty->assign('errore', $errore);

      $smarty->display('generic/snippets/header-modulo.tpl');
      $smarty->display('generic/errore.tpl');
      $smarty->display('generic/snippets/footer-modulo.tpl');

    } elseif(isset($messaggio) && $messaggio) {

      if(isset($back)) {
        $smarty->assign('back', $back);
      }

      $smarty->assign('messaggio', $messaggio);

      $smarty->display('generic/messaggio.tpl');

    } else {


      if(!in_array($cmd, $moduliSenzaActions)) {
        $smarty->display('generic/snippets/header-modulo.tpl');
      }

      $smarty->display($cmd.'/'.$cmd.'.tpl');

      if(!in_array($cmd, $moduliSenzaActions)) {
        $smarty->display('generic/snippets/footer-modulo.tpl');
      }

    }

  } else {
    //echo STRUTTURA.$cmd.".inc.php";
    $titoloSezione = 'Errore';

    $smarty->assign('titoloSezione', $titoloSezione);
    $smarty->assign('errore', 'Pagina "'.ADMIN_DIR.$cmd.'/'.$cmd.'.inc.php'.'" non trovata');

    $smarty->display('generic/header.tpl');
    $smarty->display('generic/snippets/header-modulo.tpl');
    $smarty->display('generic/errore.tpl');
    $smarty->display('generic/snippets/footer-modulo.tpl');


  }

  $smarty->display('generic/footer.tpl');

} else {
  header('Location:'.$lm->get('cmd/login'));
}