<?php
/****************/
/** v1.00      **/
/****************/
/** CHANGELOG  **/
/**************************************************************************************************/
/** v.1.00 (10/05/2016)                                                                          **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\core\Ueppy;

if($operator->hasRights($module_name, 99)) {

  switch($act) {

    case 'insert':

      rename(DOC_ROOT.REL_ROOT.JS_PUB.'general.js', DOC_ROOT.REL_ROOT.JS_PUB.'general.'.date('d-m-y-h-i-s').'.js.bak');

      file_put_contents(DOC_ROOT.REL_ROOT.JS_PUB.'general.js', $_POST['jsCode']);

      $return['result'] = 1;
      echo str_replace("\n", '', json_encode($return));
      die;

      break;

    default:

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE'));

      Ueppy::includeCodeMirror();

      $testo = file_get_contents(DOC_ROOT.REL_ROOT.JS_PUB.'general.js');

      $smarty->assign('testo', $testo);

      $footerButtons = [$BUTTONS['btnSave']];

  }

} else {
  $errore = Traduzioni::getLang('default', 'NOT_AUTH');
}