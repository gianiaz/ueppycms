<?php
/***************/
/** v.1.00    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.00 (20/05/16, 17.39)                                                                      **/
/** - Versione stabile                                                                           **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Traduzioni;
use Ueppy\core\Vetrina;
use Ueppy\utils\Utility;
use Ueppy\core\Ueppy;
use Ueppy\core\VetrinaSettings;


$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'vetrina';
$mainObjOptions['forceAllLang']  = true;

switch($act) {

  case 'insert':

    $options                  = [];
    $options['tableFilename'] = 'vetrina_settings';

    $VetrinaSettingsObj = new VetrinaSettings($options);

    $grp = 'default';
    if(isset($_POST['gruppo']) && $_POST['gruppo']) {
      $grp = $_POST['gruppo'];
    }

    $dimensioni = $VetrinaSettingsObj->getByGruppo($grp);

    $imgSetting['tipo']                      = 'crop';
    $imgSetting['dimensione']                = $dimensioni;
    $imgSetting['options']                   = [];
    $imgSetting['options']['type_of_resize'] = 'loss';

    $mainObjOptions['imgSettings']['img'][] = $imgSetting;

    $Obj = new Vetrina($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
    }

    if(Utility::isPositiveInt($_POST['attivo'])) {
      $Obj->attivo = 1;
    } else {
      $Obj->attivo = 0;
    }

    $Obj->nome   = $_POST['nome'];
    $Obj->gruppo = $_POST['gruppo'];

    foreach($langs as $l) {

      if($operator->isAdvanced()) {
        $Obj->$l = ['img_alt' => $_POST[$l]['img_alt']];
      }
      $Obj->$l = ['url' => $_POST[$l]['url']];
      $Obj->$l = ['titolo' => $_POST[$l]['titolo']];
      $Obj->$l = ['sottotitolo' => $_POST[$l]['sottotitolo']];
      $Obj->$l = ['testo' => $_POST[$l]['testo']];
    }

    foreach($langs as $l) {
      if(isset($_POST[$l]['img']['action']) && $_POST[$l]['img']['action'] == 'del') {
        // se cancello l'immagine cancello anche alt e title che non riguardano quell'immagine
        $Obj->$l = ['img_alt' => ''];
        $Obj->$l = ['img' => ['', false]];
      }
    }


    if(isset($_FILES) && count($_FILES)) {
      foreach($langs as $l) {
        if(isset($_FILES[$l]['name']['img']) && $_FILES[$l]['size']['img']) {
          $Obj->$l = ['img' => [$_FILES[$l]['tmp_name']['img'], $_FILES[$l]['name']['img']]];
        }
      }
    }

    if($Obj->isValid()) {

      $opts          = [];
      $opts['debug'] = 0;

      $result = $Obj->save($opts);

      if($result) {

        $ajaxReturn           = [];
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

  case 'getlist':

    $Obj = new Vetrina($mainObjOptions);

    $filters = [];

    $opts            = [];
    $opts['filters'] = $filters;
    $opts['debug']   = 0;
    $opts['raw']     = 1;

    $lista = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;

    break;

  case 'sort':

    $pathJS[] = DOC_ROOT.REL_ROOT.LIB.'jquery.sortable.min.js';

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'ORDINA'));

    $Obj = new Vetrina($mainObjOptions);

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';
    $opts['operatore'] = 'AND';

    $list = $Obj->getlist($opts);

    $gruppi = [];

    foreach($list as $val) {

      $record = [];

      $record['nome'] = $val->fields['nome'];
      $record['id']   = $val->fields['id'];

      $gruppi[$val->fields['gruppo']][] = $record;

    }

    $smarty->assign('gruppi', $gruppi);

    $headerButtons[] = $BUTTONS['btnSave'];
    $headerButtons[] = $BUTTONS['btnClose'];
    $footerButtons[] = $BUTTONS['btnSave'];
    $footerButtons[] = $BUTTONS['btnClose'];

    break;


  case 'saveorder':

    $new_order = trim(preg_replace('/,[,]+/', ',', $_POST['neworder']), ',');

    if(preg_match('/([\d]+,?)+/', $new_order)) {

      $Obj = new Vetrina($mainObjOptions);

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'id';
      $filter_record['operatore'] = 'IN';
      $filter_record['valore']    = '('.$new_order.')';

      $filters[] = $filter_record;

      $opts                 = [];
      $opts['sortField']    = 'FIELD(vetrina.id,'.$new_order.')';
      $opts['filters']      = $filters;
      $opts['debug']        = 0;
      $opts['forceAllLang'] = 1;
      $list                 = $Obj->getlist($opts);

      foreach($list as $k => $Obj) {
        $Obj->ordine   = $k;
        $opts          = [];
        $opts['debug'] = 0;
        $Obj->save($opts);
      }

      $LOGGER->addLine(['text' => 'Ordine visualizzazione menu modificato', 'azione' => 'MODIFY:'.strtoupper($module_name)]);

      $ajaxReturn['result'] = 1;

    }

    break;

  case 'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new Vetrina($mainObjOptions);

      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);

      $opts          = [];
      $opts['field'] = 'attivo';

      $Obj->toggle($opts);

    }

    $ajaxReturn['result'] = 1;

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'vetrina';

      $Obj                  = new Vetrina($mainObjOptions);
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);

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

  case 'new':

    Ueppy::includeTinymce();

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions    = [];
    $attivoOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $attivoOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/

    $Obj = new Vetrina($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'get_settings_list':

    $options                  = [];
    $options['tableFilename'] = 'vetrina_settings';

    $VetrinaSettingsObj = new VetrinaSettings($options);

    $opts          = [];
    $opts['debug'] = 0;
    $opts['raw']   = 1;

    $lista = $VetrinaSettingsObj->getlist($opts);

    $data = [];
    foreach($lista as $record) {

      if($record['id'] == 1) {
        $record['cancellabile'] = 0;
      } else {
        $record['cancellabile'] = 1;
      }

      $data[] = $record;
    }

    $ajaxReturn['data']   = $data;
    $ajaxReturn['result'] = 1;

    break;

  case 'load_setting':

    if($operator->isSuperAdmin()) {
      if(Utility::isPositiveInt($_POST['id'])) {

        $options                  = [];
        $options['tableFilename'] = 'vetrina_settings';

        $VetrinaSettingsObj = new VetrinaSettings($options);
        $VetrinaSettingsObj = $VetrinaSettingsObj->getById($_POST['id']);

        if($VetrinaSettingsObj) {

          $ajaxReturn['result'] = 1;
          $ajaxReturn['data']   = $VetrinaSettingsObj->toArray();

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
    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;

  case 'del_setting':

    if($operator->isSuperAdmin()) {

      if(Utility::isPositiveInt($_POST['id'])) {

        $options                  = [];
        $options['tableFilename'] = 'vetrina_settings';

        $VetrinaSettingsObj = new VetrinaSettings($options);
        $VetrinaSettingsObj = $VetrinaSettingsObj->getById($_POST['id']);

        if($VetrinaSettingsObj) {

          $VetrinaSettingsObj->delete();

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

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }
    break;


  case 'save_setting':

    if($operator->isSuperAdmin()) {
      $options                  = [];
      $options['tableFilename'] = 'vetrina_settings';

      $VetrinaSettingsObj = new VetrinaSettings($options);

      if(Utility::isPositiveInt($_POST['id'])) {

        $VetrinaSettingsObj = $VetrinaSettingsObj->getById($_POST['id']);
      }

      $ruleOptions          = [];
      $ruleOptions['field'] = 'gruppo';
      $ruleOptions['rule']  = 'Unico';
      $ruleOptions['args']  = [];

      $ruleOptions['args']['table']      = 'vetrina_settings';
      $ruleOptions['args']['confronto']  = 'id';
      $ruleOptions['args']['escludi']    = $VetrinaSettingsObj->fields['id'];
      $ruleOptions['args']['query_part'] = '';
      $ruleOptions['args']['debug']      = 0;
      $ruleOptions['args']['errore']     = Traduzioni::getLang($module_name, 'GRUPPO_PRESENTE');

      $VetrinaSettingsObj->addRule($ruleOptions);

      $VetrinaSettingsObj->gruppo     = $_POST['gruppo'];
      $VetrinaSettingsObj->dimensioni = $_POST['dimensioni'];

      if($VetrinaSettingsObj->isValid()) {

        $result = $VetrinaSettingsObj->save();

        if($result) {

          $ajaxReturn['result'] = 1;
          $ajaxReturn['dati']   = $VetrinaSettingsObj->ajaxResponse();

        } else {

          $ajaxReturn['result'] = 0;
          $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'ERRORE_INDEFINITO')];
          $ajaxReturn['wrongs'] = [];

        }

      } else {

        $opts['glue']         = '<br />';
        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = $VetrinaSettingsObj->getErrors($opts);
        $ajaxReturn['wrongs'] = array_keys($VetrinaSettingsObj->wrongFields);

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

  case 'settings':

    if($operator->isSuperAdmin()) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'SETTINGS'));

      $headerButtons[] = $BUTTONS['btnClose'];

      $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'NEW'),
                          'icon'       => 'file-o',
                          'attributes' => ['class' => 'btn btn-primary',
                                           'id'    => 'newsetting']];

    } else {
      $urlParams ='cmd/'.$cmd;
      Header('Location: '.$lm->get($urlParams));
    }

    break;

  /* elenco */
  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));


    if($operator->isSuperAdmin()) {
      $urlParams ='cmd/'.$cmd.'/act/settings/';
      $headerButtons[]       = ['text'       => Traduzioni::getLang($module_name, 'IMPOSTAZIONI'),
                                'icon'       => 'gears',
                                'attributes' => ['class' => 'btn btn-info',
                                                 'href'  => $lm->get($urlParams)]];
    }

    $headerButtons[] = $BUTTONS['btnSort'];
    $headerButtons[] = $BUTTONS['btnNew'];
    break;

  default:
    $urlParams ='cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
