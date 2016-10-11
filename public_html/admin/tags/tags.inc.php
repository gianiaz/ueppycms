<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (03/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\utils\Utility;
use Ueppy\blog\Tag;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'tags';
$mainObjOptions['forceAllLang']  = true;

switch($act) {

  case 'select':

    $ajaxReturn = [];

    $seleziona   = [];
    $deseleziona = [];

    if(isset($_POST['seleziona'])) {
      $seleziona = $_POST['seleziona'];
    }
    if(isset($_POST['deseleziona'])) {
      $deseleziona = $_POST['deseleziona'];
    }

    if(!isset($_SESSION['selection'][$module_name])) {
      $_SESSION['selection'][$module_name] = [];
    }

    foreach($seleziona as $id) {
      $_SESSION['selection'][$module_name][$id] = 1;
    }
    foreach($deseleziona as $id) {
      $_SESSION['selection'][$module_name][$id] = 0;
    }

    $ajaxReturn['delButton'] = 0;
    foreach($_SESSION['selection'][$module_name] as $id => $del) {
      if($del) {
        $ajaxReturn['delButton'] = 1;
        break;
      }
    }

    $ajaxReturn['result'] = 1;

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new Tag($mainObjOptions);

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

  case 'del_selected':

    if(count($_SESSION['selection'][$module_name])) {

      $tags_eliminati = [];

      foreach($_SESSION['selection'][$module_name] as $id => $delete) {
        if($delete) {

          $Obj = new Tag($mainObjOptions);

          $Obj = $Obj->getById($id);

          if($Obj) {
            $Obj->delete();
          }
        }
      }

      $_SESSION['selection'][$module_name] = [];

    }

    $ajaxReturn['result'] = 1;
    break;

  case 'getlist':

    $Obj = new Tag($mainObjOptions);

    $opts          = [];
    $opts['debug'] = 0;
    $opts['raw']   = 1;

    $elenco = $Obj->getlist($opts);

    $lista = [];

    foreach($elenco as $tag) {
      if(isset($lingue[$tag['lang']])) {
        $tag['lingua'] = $lingue[$tag['lang']];
      } else {
        $tag['lingua'] = $tag['lang'];
      }

      if(Utility::isPositiveInt($_SESSION['selection'][$module_name][$tag['id']])) {
        $tag['selected'] = 1;
      } else {
        $tag['selected'] = 0;
      }

      $lista[] = $tag;
    }

    $delButton = 0;

    if(!isset($_SESSION['selection'][$module_name])) {
      $_SESSION['selection'][$module_name] = [];
    }

    foreach($_SESSION['selection'][$module_name] as $id => $del) {
      if($del) {
        $delButton = 1;
        break;
      }
    }

    $ajaxReturn['data']      = $lista;
    $ajaxReturn['delButton'] = $delButton;
    $ajaxReturn['result']    = 1;

    break;

  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $del_button = 0;

    if(isset($_SESSION['selection'][$module_name])) {
      foreach($_SESSION['selection'][$module_name] as $key => $val) {
        if($val == 1) {
          $del_button = 1;
          break;
        }
      }
    }

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'DEL_SELECTED'),
                        'icon'       => 'trash',
                        'attributes' => ['class'               => 'btn btn-danger disabled',
                                         'id'                  => 'del_selected',
                                         'data-deletemultiple' => $del_button]];

    break;

  default:

    $urlParams ='cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
