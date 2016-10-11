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
use Ueppy\core\FreeMenu;
use Ueppy\utils\Utility;
use Ueppy\core\Dba;
use Ueppy\core\Ueppy;
use Ueppy\core\FreemenuStyle;

// SE SONO AUTORIZZATO AL LIVELLO
switch($act) {

  case 'save_style':

    $options                  = [];
    $options['tableFilename'] = 'freemenu_style';

    $Obj = new FreemenuStyle($options);

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    }

    $Obj->nome   = $_POST['nome'];
    $Obj->markup = $_POST['markup'];

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

      $opts['glue']         = '<br />';
      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = $Obj->getErrors($opts);

    }

    break;

  case 'stili':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang($module_name, 'STILI_MARKUP'));

    Ueppy::includeCodeMirror();

    $urlParams       = 'cmd/'.$cmd.'/act/freemenu';
    $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'TORNA_MENU'),
                        'icon'       => 'arrow-left',
                        'attributes' => ['class' => 'btn btn-info',
                                         'href'  => $lm->get($urlParams)]];
    $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'NEW'),
                        'icon'       => 'file-o',
                        'attributes' => ['class' => 'btn btn-primary',
                                         'id'    => 'newStyleBtn']];
    break;

  case 'getstili':

    /* Elenco degli elementi */

    $options                  = [];
    $options['tableFilename'] = 'freemenu_style';

    $Obj = new Dba($options);

    $opts           = [];
    $opts['fields'] = ['id', 'nome'];
    $opts['debug']  = 0;
    $opts['raw']    = 1;
    $opts['debug']  = 0;

    $lista = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;

    break;

  case 'load_style':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'freemenu_style';

      $Obj = new Dba($options);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $ajaxReturn['result'] = 1;
        $ajaxReturn['data']   = $Obj->toArray();

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

  case 'del_style':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'freemenu_style';

      $Obj = new Dba($options);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $Obj->delete();

        $ajaxReturn           = [];
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

  case 'insert':

    $options                  = [];
    $options['tableFilename'] = $module_name;
    $options['forceAllLang']  = true;
    $options['debug']         = 0;

    $Obj = new FreeMenu($options);

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = true;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
    }

    $Obj->nome               = $_POST['nome'];
    $Obj->freemenu_styles_id = $_POST['freemenu_styles_id'];

    foreach($langs as $lang_sel) {

      $Obj->$lang_sel = ['titolo' => $_POST[$lang_sel]['titolo']];

      $righe = explode("\n", $_POST[$lang_sel]['links']);

      $dati = [];
      foreach($righe as $riga) {
        $riga = trim($riga);
        if($riga != '***' && $riga != '') {
          $dati[] = explode('***', $riga);
        }
      }

      $Obj->$lang_sel = ['dati' => json_encode($dati)];
    }

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

    break;

  case 'new':

    $options                  = [];
    $options['tableFilename'] = 'freemenu_style';

    $stili = new Dba($options);

    $opts = [];

    $stili = $stili->getlist();

    if(count($stili)) {

      $stili_options = [];

      $stili_options[0] = Traduzioni::getLang('default', 'SELECT_ONE');

      foreach($stili as $s) {
        $stili_options[$s->fields['id']] = $s->fields['nome'];
      }

      $smarty->assign('stili_options', $stili_options);

      $options                  = [];
      $options['tableFilename'] = $module_name;
      $options['debug']         = 0;

      $Obj = new FreeMenu($options);

      if(isset($_SESSION['tmp']['Obj']) && $_SESSION['tmp']['Obj'] && count($_SESSION['tmp']['Obj']->errori)) {

        $Obj = $_SESSION['tmp']['Obj'];

        if($Obj->fields['id']) {

          $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

        } else {

          $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

        }

      } elseif(isset($_POST['id']) && $_POST['id']) {

        $opts                 = [];
        $opts['forceAllLang'] = true;

        $Obj = $Obj->getById($_POST['id'], $opts);

        $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      } else {

        $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

      }

      $smarty->assign('Obj', $Obj);

      /* VALIDATE */
      $pathJS[] = DOC_ROOT.REL_ROOT.LIB.'jquery/jquery.validate.min.js';
      $pathJS[] = DOC_ROOT.REL_ROOT.LIB.'jquery/jquery.metadata.js';
      /** MESSAGGI PER LA VALIDAZIONE JAVASCRIPT - INIZIO **/
      $validate         = [];
      $validate['nome'] = '{validate:{required:true, messages:{required:\''.sprintf(Traduzioni::getLang('default', 'CAMPO_OBBLIGATORIO'), Traduzioni::getLang($module_name, 'NOME')).'\'}}}';
      $smarty->assign('validate', $validate);
      /** MESSAGGI PER LA VALIDAZIONE JAVASCRIPT - FINE **/


      //$BUTTONS['set']['save-close-new'] = [$BUTTONS['btnSave'], $BUTTONS['btnClose'], $BUTTONS['btnNew']];

      //      $BUTTONS['set']['save-close'] = , , ];

      $headerButtons = [$BUTTONS['btnSave'], $BUTTONS['btnClose']];
      $footerButtons = [$BUTTONS['btnSave'], $BUTTONS['btnSaveClose'], $BUTTONS['btnClose']];
      if($operator->isSuperAdmin()) {
        $headerButtons[] = $BUTTONS['btnNew'];
        $footerButtons[] = $BUTTONS['btnSaveNew'];
      }

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'ERROR'));

      $errore = Traduzioni::getLang($module_name, 'CREA_STILI');

    }

    break;

  case 'getlist':

    /* Elenco degli elementi */

    $options                  = [];
    $options['tableFilename'] = 'freemenu';

    $Obj = new FreeMenu($options);

    $filters = [];
    $joins   = [];

    $joins = [];

    $join              = [];
    $join['table']     = 'freemenu_styles';
    $join['alias']     = 's';
    $join['on1']       = 'id';
    $join['on2']       = 'freemenu.freemenu_styles_id';
    $join['operatore'] = '=';

    $joins[] = $join;

    $opts            = [];
    $opts['fields']  = ['id', 'nome', 's.nome as stile'];
    $opts['debug']   = 0;
    $opts['filters'] = $filters;
    $opts['joins']   = $joins;
    $opts['raw']     = 1;
    $opts['debug']   = 0;

    $lista = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'freemenu';

      $Obj = new FreeMenu($options);

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

    $urlParams       = 'cmd/'.$cmd.'/act/stili';
    $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'STILI'),
                        'icon'       => 'code',
                        'attributes' => ['class' => 'btn btn-info',
                                         'href'  => $lm->get($urlParams)]];

    if($operator->isSuperAdmin()) {
      $headerButtons[] = $BUTTONS['btnNew'];
    }

    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
