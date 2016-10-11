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
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;
use Ueppy\blog\NewsCategory;
use Ueppy\core\Gruppo;
use Ueppy\core\Dba;
use Ueppy\core\Ueppy;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'news_category';
$mainObjOptions['forceAllLang']  = true;

switch($act) {

  case 'insert':
    /* Salvataggio elemento */
    $Obj = new NewsCategory($mainObjOptions);

    $errore = false;

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
      if(!$Obj->isAuth($operator)) {
        $errore = Traduzioni::getLang($module_name, 'NOT_AUTH_CAT');
      }
    }

    if(!$errore) {

      if($operator->isAdvanced()) {
        $Obj->template    = $_POST['template'];
        $Obj->predefinita = $_POST['predefinita'];

        if(!isset($_POST['auth']) || !$_POST['auth'] || !is_array($_POST['auth'])) {
          $Obj->additionalData['auth'] = [];
        } else {
          $Obj->additionalData['auth'] = $_POST['auth'];
        }

      }

      (isset($_POST['attivo']) && $_POST['attivo']) ? $Obj->attivo = 1 : $Obj->attivo = 0;

      foreach($lingue as $l => $lingua_estesa) {

        $Obj->$l = ['name' => $_POST[$l]['name']];
        $Obj->$l = ['testo' => $_POST[$l]['testo']];

        if($operator->isAdvanced()) {
          $Obj->$l = ['description' => $_POST[$l]['description']];
          $Obj->$l = ['htmltitle' => $_POST[$l]['htmltitle']];

          $Obj->resetRules('href');

          $opts['field'] = 'href';
          $opts['rule']  = 'StrRange';
          $opts['args']  = ['min' => 1, 'max' => 255];

          $Obj->addRule($opts);

          $opts['field']             = 'href';
          $opts['rule']              = 'Unico';
          $opts['args']              = [];
          $opts['args']['table']     = 'news_category_langs';
          $opts['args']['confronto'] = 'news_category_id';
          $opts['args']['escludi']   = $Obj->fields['id'];

          $Obj->addRule($opts);

          $Obj->$l = ['href' => $_POST[$l]['href']];

        }

      }


      $result = false;

      if($Obj->isValid()) {

        $result = $Obj->save();

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

    }

    break;

  case 'getlist':
    /* Elenco degli elementi */

    $Obj = new NewsCategory($mainObjOptions);

    $filters = [];
    $joins   = [];

    if(!$operator->isAdmin()) {

      $join              = [];
      $join['table']     = 'news_category_gruppi_auth';
      $join['alias']     = 'ncga';
      $join['on1']       = 'news_category_id';
      $join['on2']       = 'news_category.id';
      $join['operatore'] = '=';

      $joins[] = $join;


      $filter_record              = [];
      $filter_record['chiave']    = 'ncga.id_gruppo';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $operator->fields['gruppi_id'];

      $filters[] = $filter_record;

    }

    $opts            = [];
    $opts['debug']   = 0;
    $opts['filters'] = $filters;
    $opts['joins']   = $joins;
    $opts['raw']     = 1;

    $lista = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;

    break;

  case 'new':
    /* Nuovo elemento */

    // include i fogli di stile e i js per l'editor tinymce
    Ueppy::includeTinymce();

    /** FILES DI TEMPLATE **/
    $file_di_template = glob(SECTIONS_DIR.'*.tpl');
    $files            = [];
    $files['default'] = Traduzioni::getLang('default', 'DEFAULT_TPL');
    foreach($file_di_template as $val) {
      $lbl = str_replace([SECTIONS_DIR, '.tpl'], '', $val);
      if($lbl != 'default') {
        $files[$lbl] = $lbl;
      }
    }
    $smarty->assign('files_di_template', $files);
    /** FILES DI TEMPLATE **/

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions = [];

    $attivoOptions[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $attivoOptions[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/

    /* GRUPPI ABILITATI ALLA MODIFICA - INIZIO */

    $options                  = [];
    $options['tableFilename'] = 'gruppi';

    $grpObj = new Gruppo($options);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'ordine';
    $filter_record['operatore'] = '>';
    $filter_record['valore']    = $operator->additionalData['grp_fields']['ordine'];
    $filters[]                  = $filter_record;

    $opts              = [];
    $opts['sortField'] = 'ordine';
    $opts['sortOrder'] = 'ASC';
    $opts['filters']   = $filters;
    $opts['operatore'] = 'AND';

    $list = $grpObj->getlist($opts);

    $lista_gruppi = [];

    foreach($list as $obj) {

      $record                 = [];
      $record['inp_id']       = 'gruppo_'.$obj->fields['id'];
      $record['inp_name']     = 'auth[]';
      $record['inp_class']    = '';
      $record['lbl_class']    = 'radiolbl';
      $record['inp_value']    = $obj->fields['id'];
      $record['etichetta']    = $obj->fields['nome'];
      $record['inp_selected'] = false;

      $lista_gruppi[] = $record;

    }

    $smarty->assign('lista_gruppi', $lista_gruppi);

    /* GRUPPI ABILITATI ALLA MODIFICA - FINE */

    $options                  = [];
    $options['tableFilename'] = 'news_category';

    $Obj = new NewsCategory($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      if(!$Obj->isAuth($operator)) {
        $errore = Traduzioni::getLang($module_name, 'NOT_AUTH_CAT');
      }

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

    } else {

      $Obj->additionalData['auth'] = [$operator->fields['gruppi_id']];

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $options                  = [];
      $options['tableFilename'] = 'rel_news_category_news';
      $options['debug']         = 0;

      $rel = new Dba($options);

      $filters = [];

      $filter_record = [];

      $filter_record['chiave']    = 'news_category_id';
      $filter_record['operatore'] = '=';
      $filter_record['valore']    = $_POST['id'];

      $filters[] = $filter_record;

      $opts              = [];
      $opts['countOnly'] = true;
      $opts['filters']   = $filters;
      //$opts['debug']     = 1;
      $opts['operatore'] = 'AND';

      $conta = $rel->getlist($opts);

      $Obj = new NewsCategory($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      if(!$conta) {

        if($Obj) {
          $Obj->delete();
        }

        $ajaxReturn['result'] = 1;

      } else {

        $ajaxReturn['result'] = 0;
        $ajaxReturn['error']  = Traduzioni::getLang($module_name, 'CANT_DELETE');

      }

    } else {

      $ajaxReturn['result'] = 0;

    }

    break;

  case 'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new NewsCategory($mainObjOptions);

      $opts                 = [];
      $opts['forceAllLang'] = true;

      $Obj = $Obj->getById($_POST['id'], $opts);

      if($Obj) {

        $opts          = [];
        $opts['field'] = 'attivo';

        $Obj->toggle($opts);

      }

    }

    $ajaxReturn           = [];
    $ajaxReturn['result'] = 1;

    break;

  case 'sort':

    if($operator->isAdmin()) {

      $headerButtons[] = $BUTTONS['btnSave'];
      $headerButtons[] = $BUTTONS['btnClose'];
      $footerButtons[] = $BUTTONS['btnSave'];
      $footerButtons[] = $BUTTONS['btnClose'];

      $pathJS[] = DOC_ROOT.REL_ROOT.LIB.'jquery.sortable.min.js';

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'ORDINA'));

      $Obj = new NewsCategory($mainObjOptions);

      $opts              = [];
      $opts['sortField'] = 'ordine';
      $opts['sortOrder'] = 'ASC';

      $ObjList = $Obj->getlist($opts);

      $list = [];
      foreach($ObjList as $Obj) {
        $list[$Obj->fields['id']] = $Obj->fields[ACTUAL_LANGUAGE]['name'];
      }

      $smarty->assign('list', $list);

    } else {

      $urlParams = 'cmd/'.$cmd;

      header('Location:'.$lm->get($urlParams));

      die;

    }

    break;

  case 'savesort':

    if($operator->isAdmin()) {

      $Obj = new NewsCategory($mainObjOptions);

      $new_order = trim(preg_replace('/,[,]+/', ',', $_POST['neworder']), ',');

      if(preg_match('/([\d]+,?)+/', $new_order)) {


        $filters = [];

        $filter_record              = [];
        $filter_record['chiave']    = 'id';
        $filter_record['operatore'] = 'IN';
        $filter_record['valore']    = '('.$new_order.')';

        $filters[] = $filter_record;

        $opts                 = [];
        $opts['sortField']    = 'FIELD(news_category.id,'.$new_order.')';
        $opts['filters']      = $filters;
        $opts['debug']        = 0;
        $opts['forceAllLang'] = 1;
        $ObjList              = $Obj->getlist($opts);


        foreach($ObjList as $k => $Obj) {
          $Obj->ordine   = $k;
          $opts          = [];
          $opts['debug'] = 1;
          $Obj->save($opts);
        }
      }

      $LOGGER->addLine(['text' => 'Ordine visualizzazione menu modificato']);

      $ajaxReturn['result'] = 1;

    } else {

      $ajaxReturn['result'] = 0;

    }

    break;


  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    if($operator->isAdmin()) {
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
