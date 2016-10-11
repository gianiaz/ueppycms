<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.00 (10/05/2016)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\core\EmailTemplates;
use Ueppy\core\Ueppy;
use Ueppy\utils\Utility;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'emails';

switch($act) {

  case 'insert':

    $ajaxReturn = [];

    $Obj = new EmailTemplates($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    } else {
      if(!$operator->isSuperAdmin()) {
        $ajaxReturn['result'] = 0;
        $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'NOT_AUTH')];
      }
    }
    if(!isset($ajaxReturn['result'])) {

      if($operator->isSuperAdmin()) {
        $Obj->nome        = strtoupper($_POST['nome']);
        $Obj->descrizione = $_POST['descrizione'];
        $Obj->chiavi      = strtoupper($_POST['chiavi']);
      }

      $Obj->oggetto = $_POST['oggetto'];
      $Obj->testo   = str_replace(HOST.REL_ROOT, '{HOST}', $_POST['testo']);

      if($Obj->isValid()) {

        $result = $Obj->save();

        if($result) {

          $ajaxReturn['result'] = 1;
          $ajaxReturn['dati']   = $Obj->ajaxResponse();

        } else {

          $ajaxReturn['result'] = 0;
          $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'ERRORE_INDEFINITO')];
          $ajaxReturn['wrongs'] = [];

        }

      } else {

        $opts['glue']             = false;
        $ajaxReturn['result']     = 0;
        $ajaxReturn['errors']     = $Obj->getErrors($opts);
        $ajaxReturn['wrongs']     = array_keys($Obj->wrongFields);
        $ajaxReturn['wrongLangs'] = array_keys($Obj->wrongLangs);

      }

    }

    break;

  case 'getlist':
    /* Elenco degli elementi */

    $Obj = new EmailTemplates($mainObjOptions);

    $filters = [];

    if(!$operator->isSuperAdmin()) {

      $filter_record              = [];
      $filter_record['chiave']    = 'superadmin';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = '0';

      $filters[] = $filter_record;

    }

    $opts            = [];
    $opts['fields']  = ['id', 'nome', 'descrizione', 'oggetto'];
    $opts['debug']   = 0;
    $opts['filters'] = $filters;
    $opts['raw']     = 1;

    $lista = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;

    break;

  case 'new':

    Ueppy::includeTinymce();

    $options                  = [];
    $options['tableFilename'] = 'emails';

    $Obj = new EmailTemplates($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj                          = $Obj->getById($_POST['id']);
      $Obj->additionalData['testo'] = str_replace('{HOST}', HOST.REL_ROOT, $Obj->fields['testo']);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

    } else {
      if($operator->isSuperAdmin()) {
        $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));
      } else {
        $urlParams = 'cmd/mails';
        Header('Location:'.$lm->get($urlParams));
        die;
      }
    }

    $smarty->assign('Obj', $Obj);

    $headerButtons = [$BUTTONS['btnSave'], $BUTTONS['btnClose']];
    if($operator->isSuperAdmin()) {
      $headerButtons[] = $BUTTONS['btnNew'];
    }
    $footerButtons = [$BUTTONS['btnSave'], $BUTTONS['btnSaveClose'], $BUTTONS['btnClose']];
    if($operator->isSuperAdmin()) {
      $footerButtons[] = $BUTTONS['btnSaveNew'];
    }
    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id']) && $operator->fields['super_admin']) {

      $options                  = [];
      $options['tableFilename'] = 'emails';

      $Obj = new EmailTemplates($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      $Obj->delete();

      $ajaxReturn           = [];
      $ajaxReturn['result'] = 1;

    } else {

      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = Traduzioni::getLang('default', 'NOT_AUTH');

    }

    break;

  case '':
    /* elenco */

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    if($operator->isGod()) {
      $headerButtons[] = $BUTTONS['btnNew'];
    }

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
