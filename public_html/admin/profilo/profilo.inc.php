<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (07/06/16, 17.05)                                                                     **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;
use Ueppy\core\Operatore;
use Ueppy\core\Gruppo;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'operatori';
$mainObjOptions['forceAllLang']  = 1;

$Obj = new Operatore($mainObjOptions);

switch($act) {

  case 'insert':

    $opts                 = [];
    $opts['forceAllLang'] = 1;
    $Obj                  = $Obj->getById($operator->fields['id'], $opts);

    $Obj->nomecompleto = $_POST['nomecompleto'];
    $Obj->email        = $_POST['email'];
    if($_POST['passwd']) {
      if($_POST['passwd'] == $_POST['password_conferma']) {
        $Obj->passwd = $_POST['passwd'];
      } else {
        $Obj->addError(Traduzioni::getLang($module_name, 'PASSWORD_NON_CORRISPONDENTI'), ['passwd', 'password_conferma']);
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

  case '':
    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $opts                 = [];
    $opts['forceAllLang'] = true;

    $Obj = $Obj->getById($operator->fields['id'], $opts);

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

    $smarty->assign('Obj', $Obj);

    /* ELENCO GRUPPI - INIZIO */

    $options                  = [];
    $options['tableFilename'] = 'gruppi';

    $GruppoObj = new Gruppo($options);

    $opzioni        = [];
    $opzioni['raw'] = 1;

    $list             = $GruppoObj->getlist($opzioni);
    $gruppi_idOptions = [];
    foreach($list as $gruppo) {
      $gruppi_idOptions[$gruppo['id']] = $gruppo['nome'];
    }

    $smarty->assign('gruppi_idOptions', $gruppi_idOptions);

    /* ELENCO GRUPPI - FINE */

    $urlParams = '';

    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'CLOSE'),
                        'icon'       => 'close',
                        'attributes' => ['class' => 'btn btn-warning',
                                         'href'  => $lm->get($urlParams)]];

    $footerButtons[] = $BUTTONS['btnSave'];
    $footerButtons[] = ['text'       => Traduzioni::getLang('default', 'CLOSE'),
                        'icon'       => 'close',
                        'attributes' => ['class' => 'btn btn-warning',
                                         'href'  => $lm->get($urlParams)]];

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;

}