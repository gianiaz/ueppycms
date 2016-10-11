<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/************************************************************************************************/
/** v.1.02 (08/07/2016, 14.57)                                                                 **/
/** - Fix nella generazione di una nuova chiave su siti diversi dal sorgente del cms           **/
/**                                                                                            **/
/** v.1.01 (07/11/2015, 15.13)                                                                 **/
/** - Aggiunto autoload delle classi                                                           **/
/**                                                                                            **/
/** v.1.00 (05/09/2015)                                                                        **/
/** - Versione stabile                                                                         **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
use Ueppy\core\Dba;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'traduzioni';
$mainObjOptions['debug']         = 0;
$mainObjOptions['forceAllLang']  = 1;
$mainObjOptions['logActions']    = false;
$mainObjOptions['langs']         = ['it', 'en', 'de', 'fr', 'ru', 'es'];

$traduzioniUeppyOptions                  = [];
$traduzioniUeppyOptions['tableFilename'] = 'traduzioni_ueppy';
$traduzioniUeppyOptions['debug']         = 0;
$traduzioniUeppyOptions['forceAllLang']  = 1;
$traduzioniUeppyOptions['logActions']    = false;
$traduzioniUeppyOptions['langs']         = ['it', 'en', 'de', 'fr', 'ru', 'es'];

$lingueTraduzioni       = [];
$lingueTraduzioni['it'] = 'it - Italiano';
$lingueTraduzioni['en'] = 'en - English';
$lingueTraduzioni['de'] = 'de - Deutsch';
$lingueTraduzioni['fr'] = 'fr - Français';
$lingueTraduzioni['ru'] = 'ru - Pусский';
$lingueTraduzioni['es'] = 'es - Español';

$smarty->assign('lingueTraduzioni', $lingueTraduzioni);

if($operator->hasRights($module_name, 99)) {

  switch($act) {

    /* Salvataggio del singolo elemento tramite l'inline edit */
    case 'saveat':

      if(Utility::isPositiveInt($_POST['id'])) {

        $options = $mainObjOptions;

        if($_POST['scope'] == 'ueppy') {
          $options = $traduzioniUeppyOptions;
        }

        $Obj = new Dba($options);

        $opts['forceAllLang'] = 1;
        $opts['debug']        = 0;

        $Obj = $Obj->getById($_POST['id'], $opts);

        if($Obj) {

          $l = $_POST['lang'];

          $Obj->$l = ['dicitura' => $_POST['value']];

          if($Obj->isValid()) {

            $Obj->save();

            $ajaxReturn           = [];
            $ajaxReturn['result'] = 1;
            $ajaxReturn['dati']   = $Obj->ajaxResponse();

          } else {

            $opts['glue']         = false;
            $ajaxReturn['result'] = 0;
            $ajaxReturn['errors'] = $Obj->getErrors($opts);
            $ajaxReturn['wrongs'] = array_keys($Obj->wrongFields);

          }

        } else {
          $ajaxReturn['result'] = 0;
          if(isset($operator) && $operator->isSuperAdmin()) {
            $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')'];
          } else {
            $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')'];
          }
        }

      } else {

        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')'];
        } else {
          $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')'];
        }

      }

      break;

    /* Salvataggio della chiave di traduzione, sia essa nuova o un edit da form */
    case 'insert':

      if(Utility::isPositiveInt($_POST['id']) || $operator->isSuperAdmin()) {

        $scope = 'site';

        if(SOURCE) {
          $scope = $_POST['scope'];
        }

        $options = $mainObjOptions;

        if($scope == 'ueppy') {
          $options = $traduzioniUeppyOptions;
        }


        $Obj = new Dba($options);

        if(Utility::isPositiveInt($_POST['id'])) {

          $opts['forceAllLang'] = 1;
          $opts['debug']        = 0;

          $Obj = $Obj->getById($_POST['id'], $opts);

        }

        if($Obj) {

          if($operator->isSuperAdmin()) {
            $Obj->chiave     = strtoupper($_POST['chiave']);
            $Obj->modulo     = $_POST['modulo'];
            $Obj->sezione    = $_POST['sezione'];
            $Obj->linguaggio = $_POST['linguaggio'];
          }

          foreach($mainObjOptions['langs'] as $lang) {
            $Obj->$lang = ['dicitura' => htmlentities($_POST[$lang]['dicitura'], ENT_QUOTES, 'UTF-8', false)];
          }


          if($Obj->isValid()) {
            $opts          = [];
            $opts['debug'] = 0;
            if($Obj->save($opts)) {

              $ajaxReturn           = [];
              $ajaxReturn['result'] = 1;
              $ajaxReturn['dati']   = $Obj->ajaxResponse();

            } else {

              $ajaxReturn['result'] = 0;
              $ajaxReturn['errors'] = ['Errore indefinito'];

            }

          } else {

            $opts['glue']         = '<br />';
            $ajaxReturn['result'] = 0;
            $ajaxReturn['error']  = $Obj->getErrors($opts);
            $ajaxReturn['wrongs'] = array_keys($Obj->wrongFields);

          }

        } else {

          if($operator->fields['super_admin']) {

            $Obj = new Dba($mainObjOptions);

            $Obj->chiave     = strtoupper($_POST['chiave']);
            $Obj->modulo     = $_POST['modulo'];
            $Obj->sezione    = $_POST['sezione'];
            $Obj->linguaggio = $_POST['linguaggio'];
            $Obj->created    = time();

            foreach($mainObjOptions['langs'] as $lang) {
              $Obj->$lang = ['dicitura' => htmlentities($_POST[$lang]['dicitura'], ENT_QUOTES, 'UTF-8', false)];
            }

            $LOGGER->addLine(['text' => 'Creata la chiave di traduzione '.$_POST['modulo'].'.'.$_POST['chiave'], 'azione' => 'INSERT:'.strtoupper($module_name)]);

            if($Obj->isValid() && $Obj->save()) {
              $ajaxReturn['result'] = 1;
            } else {
              $ajaxReturn['result'] = 0;
              $ajaxReturn['errors'] = $Obj->getErrors();
            }

          } else {

            $ajaxReturn['result'] = 0;
            if(isset($operator) && $operator->isSuperAdmin()) {
              $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')'];
            } else {
              $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')'];
            }

          }

        }

      } else {

        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')'];
        } else {
          $ajaxReturn['errors'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')'];
        }

      }

      break;

    /* Estrazione degli elementi per la tabella di elenco */
    case 'getlist':

      $Obj = new Dba($mainObjOptions);

      $filters = [];

      if(Utility::isPositiveInt($_POST['non_compilate'])) {
        $filter_record              = [];
        $filter_record['chiave']    = 'dicitura';
        $filter_record['valore']    = '^[a-z|_]+\.[A-Z|0-9|_]+$';
        $filter_record['operatore'] = 'REGEXP BINARY';
        $filters[]                  = $filter_record;
      }

      $opts                 = [];
      $opts['langs']        = $mainObjOptions['langs'];
      $opts['forceAllLang'] = true;
      $opts['debug']        = 0;
      $opts['filters']      = $filters;
      $opts['operatore']    = 'AND';

      $lista = $Obj->getlist($opts);

      $list = [];

      $delButton = 0;

      if(!isset($_SESSION['selection'][$module_name])) {
        $_SESSION['selection'][$module_name] = [];
      }
      if(!isset($_SESSION['selection'][$module_name]['ueppy'])) {
        $_SESSION['selection'][$module_name]['ueppy'] = [];
      }
      if(!isset($_SESSION['selection'][$module_name]['site'])) {
        $_SESSION['selection'][$module_name]['site'] = [];
      }

      if(!$delButton) {
        foreach($_SESSION['selection'][$module_name]['site'] as $id => $del) {
          if($del) {
            $delButton = 1;
            break;
          }
        }
      }

      if(!$delButton) {
        foreach($_SESSION['selection'][$module_name]['ueppy'] as $id => $del) {
          if($del) {
            $delButton = 1;
            break;
          }
        }
      }

      foreach($lista as $Obj) {

        $record = [];

        $record['id']      = $Obj->id;
        $record['chiave']  = $Obj->chiave;
        $record['sezione'] = $Obj->sezione;
        $record['modulo']  = $Obj->modulo;
        $record['scope']   = 'site';

        $record['lingua'] = [];
        foreach($mainObjOptions['langs'] as $lang) {
          $record['lingua'][]        = $lang;
          $record[$lang.'_dicitura'] = htmlentities($Obj->fields[$lang]['dicitura'], ENT_QUOTES, 'UTF-8');
        }


        if(!$operator->fields['super_admin']) {
          $record['del'] = 0;
        } else {
          $record['del'] = 1;
        }

        if(Utility::isPositiveInt($_SESSION['selection'][$module_name]['site'][$Obj->id])) {
          $record['selected'] = 1;
        } else {
          $record['selected'] = 0;
        }

        $list[] = $record;
      }

      if(SOURCE) {

        $Obj = new Dba($traduzioniUeppyOptions);

        $filters = [];

        if(Utility::isPositiveInt($_POST['non_compilate'])) {
          $filter_record              = [];
          $filter_record['chiave']    = 'dicitura';
          $filter_record['valore']    = '^[a-z|_]+\.[A-Z|0-9|_]+$';
          $filter_record['operatore'] = 'REGEXP BINARY';
          $filters[]                  = $filter_record;
        }

        $opts                 = [];
        $opts['langs']        = $mainObjOptions['langs'];
        $opts['forceAllLang'] = true;
        $opts['debug']        = 0;
        $opts['filters']      = $filters;
        $opts['operatore']    = 'AND';

        $lista = $Obj->getlist($opts);

        foreach($lista as $Obj) {

          $record = [];

          $record['id']      = $Obj->id;
          $record['chiave']  = $Obj->chiave;
          $record['sezione'] = $Obj->sezione;
          $record['modulo']  = $Obj->modulo;
          $record['scope']   = 'ueppy';

          $record['lingua'] = [];
          foreach($mainObjOptions['langs'] as $lang) {
            $record['lingua'][]        = $lang;
            $record[$lang.'_dicitura'] = htmlentities($Obj->fields[$lang]['dicitura'], ENT_QUOTES, 'UTF-8');
          }


          if(!$operator->fields['super_admin']) {
            $record['del'] = 0;
          } else {
            $record['del'] = 1;
          }

          if(Utility::isPositiveInt($_SESSION['selection'][$module_name]['ueppy'][$Obj->id])) {
            $record['selected'] = 1;
          } else {
            $record['selected'] = 0;
          }

          $list[] = $record;
        }

      }


      $ajaxReturn['data']      = $list;
      $ajaxReturn['delButton'] = $delButton;
      $ajaxReturn['result']    = 1;

      break;

    /* Caricamento dell'elemento per la finestra modale di edit */
    case 'load':

      if(Utility::isPositiveInt($_POST['id']) && isset($_POST['scope']) && in_array($_POST['scope'], ['ueppy', 'site'])) {

        $scope = 'site';

        if(SOURCE) {
          $scope = $_POST['scope'];
        }

        $options = $mainObjOptions;

        if($scope == 'ueppy') {
          $options = $traduzioniUeppyOptions;
        }

        $Obj = new Dba($options);

        $opts['forceAllLang'] = 1;
        $opts['debug']        = 0;

        $Obj = $Obj->getById($_POST['id'], $opts);

        if($Obj) {
          $ajaxReturn['result'] = 1;

          foreach($mainObjOptions['langs'] as $lang) {
            $Obj->$lang = ['dicitura' => html_entity_decode($Obj->fields[$lang]['dicitura'], ENT_NOQUOTES, 'UTF-8')];
          }
          $ajaxReturn['data']          = $Obj->toArray();
          $ajaxReturn['data']['scope'] = $scope;

        } else {

          $ajaxReturn['result'] = 0;
          if(isset($operator) && $operator->isSuperAdmin()) {
            $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')'];
          } else {
            $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')'];
          }

        }

      } else {

        $ajaxReturn['result'] = 0;
        if(isset($operator) && $operator->isSuperAdmin()) {
          $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')'];
        } else {
          $ajaxReturn['error'] = [Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')'];
        }

      }

      break;

    case 'export':

      $options                  = [];
      $options['tableFilename'] = 'traduzioni';

      $TraduzioniObj = new Traduzioni($options);


      $TraduzioniObj->export();

      $ajaxReturn['result'] = 1;

      break;

    /* eliminazione degli elementi selezionati nella tabella di elenco */
    case 'del_selected':

      if(isset($_SESSION['selection'][$module_name]) && isset($_SESSION['selection'][$module_name]['ueppy']) && count($_SESSION['selection'][$module_name]['ueppy'])) {
        foreach($_SESSION['selection'][$module_name]['ueppy'] as $id => $delete) {
          if($delete) {
            $Obj = new Dba($traduzioniUeppyOptions);
            $Obj = $Obj->getById($id);
            if($Obj) {
              $Obj->delete();
            }
          }
        }
      }

      if(isset($_SESSION['selection'][$module_name]) && isset($_SESSION['selection'][$module_name]['site']) && count($_SESSION['selection'][$module_name]['site'])) {
        foreach($_SESSION['selection'][$module_name]['site'] as $id => $delete) {
          if($delete) {
            $Obj = new Dba($mainObjOptions);
            $Obj = $Obj->getById($id);
            if($Obj) {
              $Obj->delete();
            }
          }
        }
      }

      $_SESSION['selection'][$module_name] = [];

      $urlParams = 'cmd/'.$cmd;
      header('Location:'.$lm->get($urlParams));

      break;

    case 'select':

      $_SESSION['selection'] = [];

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

      foreach($seleziona as $record) {
        $_SESSION['selection'][$module_name][$record['scope']][$record['id']] = 1;
      }
      foreach($deseleziona as $record) {
        $_SESSION['selection'][$module_name][$record['scope']][$record['id']] = 0;
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
        $Obj = new Dba($mainObjOptions);

        $Obj = $Obj->getById($_POST['id']);
        if($Obj) {
          $LOGGER->addLine(['text' => 'Eliminata la chiave di traduzione '.$Obj->fields['modulo'].'.'.$Obj->fields['chiave'], 'azione' => 'DELETE:'.strtoupper($module_name)]);
          $Obj->delete();
        }
      }

      $ajaxReturn           = [];
      $ajaxReturn['result'] = 1;

      break;

    case 'export_global':

      $options                  = [];
      $options['tableFilename'] = 'traduzioni';
      $options['langs']         = ['it', 'en', 'de', 'fr', 'ru', 'es'];

      $TraduzioniObj = new Traduzioni($traduzioniUeppyOptions);
      $TraduzioniObj->exportGlobal();

      $ajaxReturn['result'] = 1;

      break;

    default:

      $pathJS[]  = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-switch/dist/js/bootstrap-switch.min.js';

      /* SEZIONE - INIZIO */
      $sezioneOptions           = [];
      $sezioneOptions['admin']  = 'ADMIN';
      $sezioneOptions['public'] = 'PUBLIC';

      $smarty->assign('sezioneOptions', $sezioneOptions);

      /* SEZIONE - FINE */

      /* LINGUAGGIO - INIZIO */
      $linguaggioOptions               = [];
      $linguaggioOptions['php']        = 'PHP';
      $linguaggioOptions['javascript'] = 'Javascript';

      $smarty->assign('linguaggioOptions', $linguaggioOptions);
      /* LINGUAGGIO - FINE */

      /* SCOPE - INIZIO */
      $scopeOptions          = [];
      $scopeOptions['ueppy'] = 'ueppy';
      $scopeOptions['site']  = 'site';

      $smarty->assign('scopeOptions', $scopeOptions);
      /* LINGUAGGIO - FINE */

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

      $_SESSION['tmp'] = false;

      if(!isset($_SESSION[$module_name]['noncompilate'])) {
        $_SESSION[$module_name]['noncompilate'] = 0;
      }

      $smarty->assign('noncompilate', $_SESSION[$module_name]['noncompilate']);

      $del_button = 0;

      if(isset($_SESSION['selection'][$module_name])) {
        foreach($_SESSION['selection'][$module_name] as $key => $val) {
          if($val == 1) {
            $del_button = 1;
            break;
          }
        }
      }

      if($operator->isSuperAdmin()) {
        $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'DEL_SELECTED'),
                            'icon'       => 'trash',
                            'attributes' => ['class'               => 'btn btn-danger disabled',
                                             'id'                  => 'del_selected',
                                             'data-deletemultiple' => $del_button]];

        $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'NEW'),
                            'icon'       => 'file-o',
                            'attributes' => ['class' => 'btn btn-primary',
                                             'id'    => 'newbtn']];

        if(SOURCE) {
          $headerButtons[] = ['text'       => Traduzioni::getLang($module_name, 'EXPORT_GLOBAL'),
                              'icon'       => 'download',
                              'attributes' => ['class' => 'btn btn-warning',
                                               'id'    => 'exportGlobal']];
        }
      }

      $headerButtons[] = ['text'       => Traduzioni::getLang('default', 'EXPORT'),
                          'icon'       => 'download',
                          'attributes' => ['class' => 'btn btn-success',
                                           'id'    => 'exportButton']];


      $options                  = [];
      $options['tableFilename'] = 'traduzioni';
      $options['forceAllLang']  = 1;
      $options['langs']         = ['it', 'en', 'de', 'fr', 'ru', 'es'];

      $TraduzioniObj = new Traduzioni($options);
      $TraduzioniObj = $TraduzioniObj->getById(4, ['forceAllLang' => 1]);

//      Utility::pre($TraduzioniObj);


      break;

  }

} else {

  $errore = Traduzioni::getLang('default', 'NOT_AUTH');

}
