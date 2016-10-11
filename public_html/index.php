<?php
/***************/
/** v.1.01    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.01 (26/06/16, 15.00)                                                                     **/
/** - Aggiinto caching delle query per evitare le query duplicate                                **/
/**                                                                                              **/
/** v.1.00 (07/06/16, 18.00)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
//define('DO_QUERY_CACHE', true);
header('Content-type: text/html;charset=utf-8');
session_start();
require("vendor/autoload.php");
require("conf/boot.php");
require(LIB.'ueppy/function.ueppy_form_field.php');
use Ueppy\core\Operatore;
use Ueppy\utils\Utility;
use Ueppy\core\Lingue;
use Ueppy\core\LinkManager;
use Ueppy\core\Dba;
use Ueppy\core\Route;
use Ueppy\ecommerce\Carrello;
use DebugBar\StandardDebugBar;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Ueppy\core\Traduzioni;
use Ueppy\core\ViewManager;
use Ueppy\core\Ueppy;

$operator                   = false;
$SUPERADMIN                 = false;
$GOD                        = false;
$opzioni                    = [];
$opzioni['template_dir']    = DOC_ROOT.REL_ROOT.'public/';
$opzioni['cache_dir']       = DOC_ROOT.REL_ROOT.UPLOAD.'smarty/public/cache/';
$opzioni['compile_dir']     = DOC_ROOT.REL_ROOT.UPLOAD.'smarty/public/templates_c/';
$opzioni['caching']         = Smarty::CACHING_OFF;
$opzioni['error_reporting'] = E_ALL & ~E_NOTICE;


$smarty = new \Ueppy\core\UeppySmarty($opzioni);
require(LIB.'ueppy/smarty.functions.php');


require_once(DOC_ROOT.REL_ROOT.UPLOAD.'settings/settings.php');
$smarty->assign('SETTINGS', $sett_smarty);
if(PRODUZIONE && !SET_USE_MINIFIED) {
  $css = [DOC_ROOT.REL_ROOT.'css/layout.src.css'];
  $js  = [DOC_ROOT.REL_ROOT.'js/script.js'];
}

// estrazione e valutazione delle lingue dalla costante settata in admin
$langs                    = explode(",", SET_LANGUAGES);
$options                  = [];
$options['tableFilename'] = 'languages';


$lObj   = new Lingue($options);
$lingue = [];
foreach($langs as $lang) {
  $l        = $lObj->getBySigla($lang);
  $lingue[] = $l;
  unset($l);
}
$smarty->assign('lingue', $lingue);
$smarty->assign('languages', $langs);

$route = new Route($langs);

// Utility::pre($route);
$smarty->assign('ACTUAL_LANGUAGE', ACTUAL_LANGUAGE);

$DEBUGBAR = false;


$mainModule = $route->cmd;
$smarty->assign('cmd', $route->cmd);
$smarty->assign('mainModule', $mainModule);

if(!isset($GET_REC['act'])) {
  $act = "";
} else {
  $act = $GET_REC['act'];
}

$smarty->assign('act', $act);

/**
 * Gestore link, imposto i valori di default per la sezione admin,
 * cambierò solo il valore di extraparams nei diversi moduli a seconda dell'esigenza
 */

$lm = LinkManager::getInstance();

$params = 'cmd/home/act/test';

$URL_HOME = $lm->get($params);

// creo l'oggetto traduzioni, che avrà una sua istanza che sarà sempre la stessa.
$TRADUZIONI_OPTION               = [];
$TRADUZIONI_OPTION['sezione']    = 'public';
$TRADUZIONI_OPTION['logActions'] = false;

Traduzioni::getInstance($TRADUZIONI_OPTION);

define('DEC_POINT', Traduzioni::getLang('default', 'DEC_POINT'));
define('SET_THOUSANDS_SEP', Traduzioni::getLang('default', 'THOUSANDS_SEP'));
define('DATE_FORMAT', Traduzioni::getLang('cal', 'DATE_FORMAT'));

// STILI
$smarty->loadCSS(); // Carico i fogli di stile

$opts          = [];
$opts['debug'] = 0;
$opts['path']  = DOC_ROOT.REL_ROOT.CSS_PUB.'style.css';
$smarty->addCSS($opts);


// JS
$pathJs = [];
$smarty->loadJS(); // Carico i javascript

$opts          = [];
$opts['debug'] = 0;
$opts['path']  = LANG_DIR.ACTUAL_LANGUAGE.'/locale-public-js.json';
$smarty->addJS($opts);


/** POPUP AL CARICAMENTO DEL SITO - INIZIO **/

$popup = '';


if(SET_ENABLE_POPUP && !Utility::isPositiveInt($_COOKIE['popupviewed'])) {

  require_once(CLASSES.'Pagina.Class.php');

  $options                  = [];
  $options['tableFilename'] = 'pagine';
  $messaggio_popup          = new Pagina($options);

  $opts               = [];
  $opts['href']       = 'pop-up';
  $opts['path']       = '';
  $opts['attivo']     = 0;
  $opts['debug']      = 0;
  $opts['datiPagina'] = 1;


  $messaggio_popup = $messaggio_popup->getByHref($opts);

  if($messaggio_popup) {
    $popup = $messaggio_popup->fields[ACTUAL_LANGUAGE]['testo'];
    setcookie('popupviewed', 1, time() + (30 * 60), '/');
    $popuptitle = $messaggio_popup->additionalData['menu']->fields[ACTUAL_LANGUAGE]['dicitura'];
    $re         = '/(.*)\-([\d]+)x([\d]+)$/';
    $popupw     = 0;
    $popuph     = 0;
    if(preg_match($re, $popuptitle, $matches)) {
      $popuptitle = $matches[1];
      $popupw     = $matches[2];
      $popuph     = $matches[3];
    }
    $smarty->assign('popuptitle', $popuptitle);
    $smarty->assign('popupw', $popupw);
    $smarty->assign('popuph', $popuph);
  }
}
$smarty->assign('popup', $popup);
/** POPUP AL CARICAMENTO DEL SITO - FINE **/

$IMG0          = [];
$IMG0['path']  = '/images/site/header-default.jpg';
$IMG0['alt']   = '';
$IMG0['title'] = '';

$IMG2['path']  = '';
$IMG2['alt']   = '';
$IMG2['title'] = '';

$RSS       = false;
$PAGETITLE = '';
$TEMPLATE  = 'default.tpl';

// solo se interessa intercettare il tipo di device in navigazione
// decommentare il seguente codice:

$mobile = false;
$tablet = false;

$detect = new Mobile_Detect();

// Any mobile phone
if($detect->isMobile() && !$detect->isTablet()) {
  $mobile = true;
} elseif($detect->isTablet()) {
  $tablet = true;
}

$smarty->assign('mobile', $mobile);
$smarty->assign('tablet', $tablet);

$viewManangerObj = new ViewManager($route, $smarty);

if($route->cmd != 'api' && SET_OFFLINE && !$viewManangerObj->controller->getOperator()) {
  header('HTTP/1.1 503 Service Temporarily Unavailable');
  header('Status: 503 Service Temporarily Unavailable');

  $smarty->display(DEFAULT_TPL.'offline.tpl');
  die;
}

$viewManangerObj->controller->loadTemplate();

Ueppy::info('CMD: '.$route->cmd, false);
Ueppy::info('ACTION: '.($route->act ? $route->act : '-'), false);

$out = $viewManangerObj->controller->getOut();

$out['GLOBAL']['URL_HOME'] = $lm->get('');


foreach($out['GLOBAL'] as $globalVar => $data) {
  //Utility::pre($globalVar);
  $smarty->assign($globalVar, $data);
}

unset($out['GLOBAL']);

$smarty->assign('SMARTY_VARS', $out);

$cache_id = 0;

$site = $smarty->fetch(DOC_ROOT.REL_ROOT.'public/common/head.tpl', $cache_id);


if($viewManangerObj->controller->CONFIG['header']['show']) {
  Ueppy::info('Header file: '.basename($viewManangerObj->controller->getHeaderFile()), false);
  $site .= $smarty->fetch($viewManangerObj->controller->getHeaderFile(), $cache_id);
}

Ueppy::info('Template: '.basename($viewManangerObj->controller->getSectionFile()), false);
Ueppy::info('Template modulo principale: '.basename($viewManangerObj->controller->getMainFile()), false);

$site .= $smarty->fetch($viewManangerObj->controller->getSectionFile(), $cache_id);

Ueppy::info('Footer file: '.basename($viewManangerObj->controller->getFooterFile()), false);
if($viewManangerObj->controller->CONFIG['footer']['show']) {
  $site .= $smarty->fetch($viewManangerObj->controller->getFooterFile(), $cache_id);
}
$site .= $smarty->fetch('common/foot.tpl', $cache_id, '');

$indenter = new \Gajus\Dindent\Indenter();
$site     = $indenter->indent($site);
echo $site;
