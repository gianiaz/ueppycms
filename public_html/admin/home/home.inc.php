<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (24/05/16, 12.01)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\core\HomeBlock;
use Ueppy\utils\Utility;
use Ueppy\core\Ueppy;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'homeblocks';

$Obj = new HomeBlock($mainObjOptions);

switch($act) {

  case 'insert':

    if(Utility::isPositiveInt($_POST['id'])) {

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      foreach($langs as $l) {
        $Obj->$l = ['testo' => $_POST[$l]['testo']];
      }

      $Obj->save();

      $return['result'] = 1;

      echo str_replace("\n", '', json_encode($return));
      die;

    }

    break;

  case 'getlist':

    $file     = DOC_ROOT.REL_ROOT.STRUTTURA_PUB.MAIN_MODULES.'home/home.tpl';
    $tpl_file = file_get_contents($file);

    $list = [];

    preg_match_all('/<.*class=".*mce.*".*>/', $tpl_file, $matches);

    if(count($matches)) {

      $re = '/id="([^"]*)"/';

      $ids = [];

      foreach($matches[0] as $div) {

        preg_match($re, $div, $m2);


        if($m2) {
          $ids[] = $m2[1];

        }

      }
      
      $lista = $Obj->checkBlocks($ids);

      $list = [];

      foreach($lista as $Obj) {

        $record = [];

        $record['id']     = $Obj->fields['id'];
        $record['htmlid'] = $Obj->fields['htmlid'];
        $record['testo']  = substr(strip_tags($Obj->fields[ACTUAL_LANGUAGE]['testo']), 0, 140).'';

        $list[] = $record;

      }

    }


    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;

  case
  'del':
    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'homeblocks';
      $options['debug']         = 0;

      $Obj = new HomeBlock($options);

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      $Obj->delete();

    }

    $urlParams ='cmd/'.$cmd;
    header('Location:'.$lm->get($urlParams));

    break;

  case 'new':

    if(Utility::isPositiveInt($_POST['id'])) {

      // include i fogli di stile e i js per l'editor tinymce
      Ueppy::includeTinymce();

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY').' id "'.$Obj->fields['htmlid'].'"');

      $smarty->assign('Obj', $Obj);

      $headerButtons[] = $BUTTONS['btnSave'];
      $headerButtons[] = $BUTTONS['btnClose'];

      $footerButtons[] = $BUTTONS['btnSave'];
      $footerButtons[] = $BUTTONS['btnSaveClose'];
      $footerButtons[] = $BUTTONS['btnClose'];

    }

    break;

  default :

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    break;

}