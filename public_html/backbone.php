<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (27/05/16, 15.29)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/

require('conf/boot.php');
require('vendor/autoload.php');

use Ueppy\core\Db;
use Ueppy\core\Dba;
use Ueppy\utils\Logger;
use Ueppy\utils\Utility;
use Ueppy\core\Lingue;
use Ueppy\core\Operatore;
use Ueppy\core\Gruppo;
use Ueppy\core\Menu;
use Ueppy\core\Traduzioni;

if(isset($_POST['PHPSESSID']) && $_POST['PHPSESSID']) {
  session_id($_POST['PHPSESSID']);
}

$return = [];

session_start();

/**
 * Logger
 */
$options                   = [];
$options['tableFilename']  = 'logs';
$options['autoLoadLabels'] = false;

$LOGGER = new Logger($options);

if(isset($_GET['lang'])) {
  $actual_language = $_GET['lang'];
} elseif(isset($_COOKIE['upycms_lang'])) {
  $actual_language = $_COOKIE['upycms_lang'];
} else {
  $actual_language = 'it';
}

define('ACTUAL_LANGUAGE', $actual_language);
$ACTUAL_LANGUAGE = substr($actual_language, 0, 2);

$db = new db();

$db->connect();

// settaggi da db, diventano delle costanti SET_{nomechiave}
require_once(DOC_ROOT.REL_ROOT.UPLOAD.'settings/settings.php');

$langs = explode(',', SET_LANGUAGES);


$lingue = [];

$filters = [];

$filter_record              = [];
$filter_record['chiave']    = 'sigla';
$filter_record['operatore'] = 'IN';
$filter_record['valore']    = '('.implode(',', array_map('\Ueppy\core\Dba::enclose', $langs)).')';

$filters[] = $filter_record;

$options                  = [];
$options['tableFilename'] = 'languages';

$langObj = new Lingue($options);

$opts              = [];
$opts['filters']   = $filters;
$opts['operatore'] = 'AND';

$lingue_disponibili = $langObj->getlist($opts);

foreach($lingue_disponibili as $langObj) {
  $lingue[$langObj->fields['sigla']] = $langObj->fields['estesa'];
}


if(isset($_REQUEST['cmd']) && $_REQUEST['cmd']) {
  $cmd = basename($_REQUEST['cmd']);
} else {
  $cmd = '';
}

function cmd_ajax($name) {

  return strtoupper(str_replace('.php', '', basename($name)));
}

$auth_cmd = array_map('cmd_ajax', Utility::glob2(BB_DIR_AUTH, 'ONLY_FILES'));

if(!is_dir(BB_DIR_PUB)) {
  Utility::mkdirp(BB_DIR_PUB);
}
$pub_cmd = array_map('cmd_ajax', Utility::glob2(BB_DIR_PUB, 'ONLY_FILES'));

if($cmd) {

  if(in_array($cmd, $auth_cmd)) {

    // creo l'oggetto traduzioni, che avrà una sua istanza che sarà sempre la stessa.
    $options               = [];
    $options['sezione']    = 'admin';
    $options['logActions'] = false;
    $options['langs']      = ['en', 'de', 'fr', 'ru', 'es'];
    Traduzioni::getInstance($options);

    define('DEC_POINT', Traduzioni::getLang('default', 'DEC_POINT'));
    define('SET_THOUSANDS_SEP', Traduzioni::getLang('default', 'THOUSANDS_SEP'));
    define('DATE_FORMAT', Traduzioni::getLang('default', 'DATE_FORMAT'));
    if(isset($_SESSION['LOG_INFO']['LOGGED']) && $_SESSION['LOG_INFO']['UID']) {

      if(isset($_SESSION['LOG_INFO']['LOGGED']) && $_SESSION['LOG_INFO']['UID']) {
        $options                  = [];
        $options['tableFilename'] = 'operatori';
        $operator                 = new Operatore($options);
        $operator                 = $operator->getById($_SESSION['LOG_INFO']['UID']);
      }

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

      include(BB_DIR_AUTH.strtolower($cmd).'.php');

    } else {
      $return['result'] = 0;
      $return['error']  = Traduzioni::getLang('default', 'NOT_AUTH');
    }

  } elseif(in_array($cmd, $pub_cmd)) {

    $sezione_lingue = 'public';
    $lang_def       = parse_ini_file(LANG_DIR.ACTUAL_LANGUAGE.'/lang.public.ini.php', true);

    include(BB_DIR_PUB.strtolower($cmd).'.php');

  } else {

    $sezione_lingue = 'public';
    $lang_def       = parse_ini_file(LANG_DIR.ACTUAL_LANGUAGE.'/lang.public.ini.php', true);

    $return['result'] = 0;
    $return['error']  = Traduzioni::getLang('default', 'WRONG_COMMAND');
  }

} else {
  $sezione_lingue   = 'admin';
  $lang_def         = parse_ini_file(LANG_DIR.ACTUAL_LANGUAGE.'/lang.admin.ini.php', true);
  $return['result'] = 0;
  $return['error']  = Traduzioni::getLang('default', 'WRONG_COMMAND');
}
echo json_encode($return);
?>