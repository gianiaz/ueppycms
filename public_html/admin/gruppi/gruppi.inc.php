<?php
/***************/
/** v.1.02    **/
/***************/
/** CHANGELOG **/
/**************************************************************************************************/
/** v.1.02 (07/11/2015, 14.54)                                                                   **/
/** - Introdotto autoloading.                                                                    **/
/** v.1.01 (05/09/2015)                                                                          **/
/**                                                                                              **/
/**************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                      **/
/** copyright: Ueppy s.r.l                                                                       **/
/**************************************************************************************************/
use Ueppy\core\Gruppo;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;
use Ueppy\core\Dba;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'gruppi';

// SE SONO AUTORIZZATO AL LIVELLO
switch($act) {

  /* Salvataggio del form di inserimento */
  case 'insert':

    $Obj = new Gruppo($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {
      $Obj = $Obj->getById($_POST['id']);
    }

    $opts                      = [];
    $opts['field']             = 'nome';
    $opts['rule']              = 'Unico';
    $opts['args']              = [];
    $opts['args']['table']     = 'gruppi';
    $opts['args']['confronto'] = 'id';
    $opts['args']['escludi']   = $Obj->fields['id'];

    $Obj->addRule($opts);

    $Obj->nome = $_POST['nome'];

    if(Utility::isPositiveInt($_POST['attivo'])) {
      $Obj->attivo = 1;
    } else {
      $Obj->attivo = 0;
    }

    if(!$Obj->fields['id']) {
      $Obj->cancellabile = 1;
    }

    $result = false;

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

      $opts['glue']         = false;
      $ajaxReturn['result'] = 0;
      $ajaxReturn['errors'] = $Obj->getErrors($opts);
      $ajaxReturn['wrongs'] = array_keys($Obj->wrongFields);

    }

    break;

  /* Estrazione elementi per tabella di elenco */
  case 'getlist':

    $Obj = new Gruppo($mainObjOptions);

    $filters = [];

    if(!$operator->isSuperAdmin()) {

      $filter_record              = [];
      $filter_record['chiave']    = 'ordine';
      $filter_record['operatore'] = '>';
      $filter_record['valore']    = $operator->additionalData['grp_fields']['ordine'];

      $filters[] = $filter_record;

    }

    $sort_field = 'ordine';
    $sort_order = 'asc';

    $opts              = [];
    $opts['sortField'] = $sort_field;
    $opts['sortOrder'] = $sort_order;
    $opts['filters']   = $filters;
    $opts['debug']     = 0;
    $opts['raw']       = 1;

    $lista = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;
    break;

  /* Pagina per la selezione dell'ordine di importanza */
  case 'sort':

    $headerButtons[] = $BUTTONS['btnSave'];
    $headerButtons[] = $BUTTONS['btnClose'];
    $footerButtons[] = $BUTTONS['btnSave'];
    $footerButtons[] = $BUTTONS['btnClose'];

    $pathJS[] = DOC_ROOT.REL_ROOT.LIB.'jquery.sortable.min.js';


    $options                  = [];
    $options['tableFilename'] = 'gruppi';

    $Obj = new Gruppo($mainObjOptions);

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang($module_name, 'ORDINA'));

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';

    $grp_list = $Obj->getlist($opts);

    $list = [];
    foreach($grp_list as $grp) {
      $list[$grp->fields['id']] = $grp->fields['nome'];
    }

    $smarty->assign('grp_list', $list);

    break;

  /* Salvataggio dell'ordine di importanza */
  case 'saveorder':

    $options                  = [];
    $options['tableFilename'] = 'gruppi';

    $Obj = new Gruppo($mainObjOptions);

    if(preg_match('/([\d]+,?)+/', $_POST['neworder'])) {

      $opts              = [];
      $opts['sortField'] = 'FIELD(id,'.$_POST['neworder'].')';
      $opts['debug']     = 0;

      $list = $Obj->getlist($opts);

      foreach($list as $k => $Obj) {
        $Obj->ordine = $k;
        $Obj->save();
      }

    }

    $ajaxReturn['result'] = 1;
    break;

  /* Form di modifica/Inserimento */
  case 'new':

    $Obj = new Gruppo($mainObjOptions);

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions    = [];
    $attivoOptions[1] = Traduzioni::getLang("default", 'SI_ANSWER');
    $attivoOptions[0] = Traduzioni::getLang("default", 'NO_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/

    if(isset($_POST['id']) && $_POST['id']) {

      $Obj = $Obj->getById($_POST['id']);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  /* Eliminazione dell'elemento */
  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'gruppi';

      $grp = new Gruppo($mainObjOptions);
      $grp = $grp->getById($_POST['id']);
      if($grp) {
        $grp->delete();
      }

    }

    $ajaxReturn['result'] = 1;

    break;

  /* cambio dello stato di visibilità/attivazione */
  case 'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'gruppi';

      $grp = new Gruppo($mainObjOptions);
      $grp = $grp->getById($_POST['id']);
      $grp->toggle(['field' => 'attivo']);

      $grp->save();

    }

    $ajaxReturn['result'] = 1;

    break;

  /* Salvataggio dei permessi */
  case 'savepermissions':

    if($operator->additionalData['grp_fields']['ordine'] > 2) {
      $menulist = $operator->getMenu(0, 100, 0);
    } else {
      $menulist = $operator->getMenu(0, 99, 0);
    }

    $options                  = [];
    $options['tableFilename'] = 'gruppi';

    $Obj = new Gruppo($options);
    $Obj = $Obj->getById($_POST['id']);

    if($Obj) {

      if($operator->isSuperAdmin() || $Obj->fields['ordine'] > $operator->additionalData['grp_fields']['ordine']) {

        $LOGGER->addLine(['text' => 'Modificati i permessi del gruppo "'.$Obj->fields['nome'].'"', 'azione' => 'MODIFY:'.strtoupper($module_name)]);

        if(Utility::isPositiveInt($_POST['all'])) {

          $Obj->all_elements = 1;
          $result            = $Obj->save();

          if($result) {
            $return['result'] = 1;
          } else {
            $return['result'] = 0;
            $return['error']  = Traduzioni::getLang('default', 'UNDEFINED_ERROR').'('.__FILE__.', '.__LINE__.')';
          }

        } else {

          $Obj->all_elements = 0;
          $result            = $Obj->save();

          $nuoviPermessi = [];

          if(isset($_POST['permessi']) && is_array($_POST['permessi'])) {
            $POST_PERMESSI = $_POST['permessi'];
          } else {
            $POST_PERMESSI = [];
          }

          foreach($menulist as $sezione) {
            foreach($sezione->additionalData['childs'] as $menu) {
              if(in_array($menu->fields['id'], $POST_PERMESSI)) {
                $nuoviPermessi[$menu->fields['id']] = 1;
              } else {
                $nuoviPermessi[$menu->fields['id']] = 0;
              }
            }
          }

          foreach($nuoviPermessi as $id_menu => $permesso) {

            $options                  = [];
            $options['tableFilename'] = 'permessi';
            $permessi                 = new Dba($options);

            $filters = [];

            $filter_record              = [];
            $filter_record['chiave']    = 'gruppi_id';
            $filter_record['operatore'] = '=';
            $filter_record['valore']    = $_POST['id'];

            $filters[] = $filter_record;

            $filter_record              = [];
            $filter_record['chiave']    = 'menu_id';
            $filter_record['operatore'] = '=';
            $filter_record['valore']    = $id_menu;

            $filters[] = $filter_record;

            $opts              = [];
            $opts['fields']    = false;
            $opts['countOnly'] = true;
            $opts['start']     = 0;
            $opts['quanti']    = 1;
            $opts['filters']   = $filters;
            $opts['operatore'] = 'AND';

            $permessi = $permessi->getlist($opts);

            if($permessi) {
              if(!$permesso) {
                $sql                      = 'DELETE FROM permessi WHERE menu_id = '.$id_menu.' AND gruppi_id = '.$_POST['id'];
                $options                  = [];
                $options['tableFilename'] = 'permessi';
                $permessi                 = new Dba($options);
                $permessi->doQuery($sql);
              }
            } else {
              if($permesso) {
                $options                  = [];
                $options['tableFilename'] = 'permessi';
                $permessi                 = new Dba($options);
                $permessi->menu_id        = $id_menu;
                $permessi->gruppi_id      = $_POST['id'];

                $opts                = [];
                $opts['forceInsert'] = true;
                $opts['debug']       = 0;

                $permessi->save($opts);
              }
            }

          }

          $ajaxReturn['result'] = 1;

        }


      } else {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = Traduzioni::getLang('default', 'NOT_AUTH').'('.__LINE__.')';

      }

    } else {

      $ajaxReturn['result'] = 0;
      $ajaxReturn['error']  = Traduzioni::getLang('default', 'BAD_PARAMS').'('.__LINE__.')';

    }
    break;

  /* Visualizzazione/modifica dei permessi per un gruppo */
  case 'permessi':

    if(Utility::isPositiveInt($_POST['id'])) {

      $menulist = $operator->getMenu(0, 100, 0);

      $tmpMenuList = $menulist;

      $menulist = [];

      foreach($tmpMenuList as $mnu) {
        $tmpMenuChilds                 = $mnu->additionalData['childs'];
        $mnu->additionalData['childs'] = [];
        foreach($tmpMenuChilds as $child) {
          $child->additionalData['level2'] = intval($child->fields['level'] / 10);
          $mnu->additionalData['childs'][] = $child;
        }
        $menulist[] = $mnu;
      }

      $smarty->assign('menuPermessi', $menulist);

      $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-switch/dist/js/bootstrap-switch.min.js';

      $Obj = new Gruppo($mainObjOptions);
      $Obj = $Obj->getById($_POST['id']);

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang($module_name, 'RIGHTS').$Obj->fields['nome']);

      $options                  = [];
      $options['tableFilename'] = 'permessi';

      $permessi = new Dba($options);

      $filters = [];

      $filter_record              = [];
      $filter_record['chiave']    = 'gruppi_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $_POST['id'];

      $filters[] = $filter_record;

      $opts              = [];
      $opts['filters']   = $filters;
      $opts['operatore'] = 'AND';

      $permessi = $permessi->getlist($opts);

      $records = [];

      foreach($permessi as $permesso) {
        $records[] = $permesso->fields['menu_id'];
      }

      $smarty->assign('permessi', $records);
      $smarty->assign('Obj', $Obj);

      $headerButtons[] = $BUTTONS['btnSave'];
      $headerButtons[] = $BUTTONS['btnClose'];

      $footerButtons[] = $BUTTONS['btnSave'];
      $footerButtons[] = $BUTTONS['btnClose'];


    } else {
      $urlParams = 'cmd/'.$cmd;
      header('Location:'.$lm->get($urlParams));
    }

    break;

  /* elenco */
  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    if($operator->isSuperAdmin()) {
      $headerButtons[] = $BUTTONS['btnSort'];
    }
    $headerButtons[] = $BUTTONS['btnNew'];
    break;

  default:
    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}

