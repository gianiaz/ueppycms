#!/usr/local/bin/php
<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (23/05/16, 18.50)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
ini_set('memory_limit', '256M');
header('Content-type: text/html;charset=utf-8');

/** CONFIG **/

$globalDebug = false;

require_once('public_html/conf/config.php');

// da qui si può lasciare sempre uguale.
define('DOC_ROOT', __DIR__.'/public_html');
define('CONF_DIR', DOC_ROOT.REL_ROOT.'conf/');
define('UPLOAD', 'media/');
define('TABLES_DIR', CONF_DIR.'tables/');
define('LANG_DIR', DOC_ROOT.REL_ROOT.UPLOAD.'languages/');
define('CLASSES', DOC_ROOT.REL_ROOT.'classes/');
define('FUNCTIONS', DOC_ROOT.REL_ROOT.'functions/');
define('BACKUP_DIR', 'bck/');

/** CONFIG **/

// necessario sul lato pubblico
define('DBA_AUTOLOAD_LABELS', false);
require(DOC_ROOT.REL_ROOT.'vendor/autoload.php');

use Ueppy\core\Lingue;
use Ueppy\utils\Logger;
use Ueppy\core\Traduzioni;
use Ueppy\core\Schedule;
use Ueppy\utils\Utility;

require_once(DOC_ROOT.REL_ROOT.UPLOAD.'settings/settings.php');
$langs           = explode(",", SET_LANGUAGES);
$actual_language = SET_DEFAULT_LANG;

$options                  = [];
$options['tableFilename'] = 'languages';

$lObj = new Lingue($options);

$lingue = [];
foreach($langs as $lang) {
  $l        = $lObj->getBySigla($lang);
  $lingue[] = $l;
  unset($l);
}

if(!in_array($actual_language, $langs)) {
  $actual_language = 'it';
}

define('ACTUAL_LANGUAGE', $actual_language);

/**
 * Logger
 */
$options                   = [];
$options['tableFilename']  = 'crontab_logs';
$options['autoLoadLabels'] = false;
$options['logActions']     = false;
$LOGGER                    = new Logger($options);

// creo l'oggetto traduzioni, che avrà una sua istanza che sarà sempre la stessa.
$TRADUZIONI_OPTION               = [];
$TRADUZIONI_OPTION['sezione']    = 'admin';
$TRADUZIONI_OPTION['logActions'] = false;
$TRADUZIONI_OPTION['langs']      = ['en', 'de', 'fr', 'ru', 'es'];
Traduzioni::getInstance($TRADUZIONI_OPTION);

define('DEC_POINT', Traduzioni::getLang('default', 'DEC_POINT'));
define('SET_THOUSANDS_SEP', Traduzioni::getLang('default', 'THOUSANDS_SEP'));
define('DATE_FORMAT', Traduzioni::getLang('default', 'DATE_FORMAT'));
define('DATE_TIME_FORMAT', Traduzioni::getLang('default', 'DATE_TIME_FORMAT'));


$options                  = [];
$options['tableFilename'] = 'schedule';

if(isset($argv[1]) && preg_match('/^[a-z\-]+$/', $argv[1])) {

  $toRun = [basename($argv[1])];

} else {

  $ScheduleObj = new Schedule($options);

  $toRun = $ScheduleObj->getToRunNow();

}

foreach($toRun as $scriptName) {
  $scriptName = DOC_ROOT.REL_ROOT.'crontabs/'.$scriptName.'.php';
  if(file_exists($scriptName)) {
    require($scriptName);
  }
}