<?php
/*****************/
/***ueppy3.4.00***/
/*****************/
/**  CHANGELOG  **/
/************************************************************************************************/
/** v.3.4.00 (05/09/2015)                                                                      **/
/** - Versione stabile, da versione 3.1.04                                                     **/
/**                                                                                            **/
/************************************************************************************************/
/** Author   : Giovanni Battista Lenoci <gianiaz@gmail.com>                                    **/
/** copyright: Ueppy s.r.l                                                                     **/
/************************************************************************************************/
use Ueppy\core\Menu;
use Ueppy\utils\Utility;
use Ueppy\core\Traduzioni;

$mainObjOptions = [];

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'menu';
$mainObjOptions['files']         = ['img0', 'img1'];
$mainObjOptions['forceAllLang']  = true;
$mainObjOptions['debug']         = 0;
$mainObjOptions['loadRules']     = 1;
$mainObjOptions['imgSettings']   = [];

$imgSetting               = [];
$imgSetting['dimensione'] = '72x72';
$imgSetting['tipo']       = 'exact';

$mainObjOptions['imgSettings']['img0'][] = $imgSetting;

$imgSetting               = [];
$imgSetting['dimensione'] = '24x24';
$imgSetting['tipo']       = 'exact';

$mainObjOptions['imgSettings']['img1'][] = $imgSetting;

$Obj = new Menu($mainObjOptions);

switch($act) {

  /* inserimento */
  case 'insert':

    $array_esclusione_stringa = ['content'];

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
    } else {
      $Obj->ordine = 0;
    }

    if(!isset($Obj->fields['pubdate'])) {
      $Obj->pubdate = date('Y-m-d');
    }

    $Obj->template = $_POST['template'];

    if(isset($_POST['attivo'])) {
      $Obj->attivo = 1;
    } else {
      $Obj->attivo = 0;
    }

    $Obj->level = $_POST['level'];

    if($_POST['nomefile'] != '-') {

      $opts                      = [];
      $opts['field']             = 'nomefile';
      $opts['rule']              = 'Unico';
      $opts['args']              = [];
      $opts['args']['table']     = 'menu';
      $opts['args']['confronto'] = 'id';
      $opts['args']['escludi']   = $Obj->fields['id'];

      if($Obj->level >= 100) {
        $opts['args']['query_part'] = ' AND menu.level >= 100';
      } else {
        $opts['args']['query_part'] = ' AND menu.level < 100';
      }

      $Obj->addRule($opts);

    }

    $Obj->nomefile = $_POST['nomefile'];

    $Obj->resetRules('href');

    $restrizione   = [];
    $opts['field'] = 'href';
    $opts['rule']  = 'StrRange';
    $opts['args']  = ['min' => 1, 'max' => 255];

    $Obj->addRule($opts);

    $opts['field']             = 'href';
    $opts['rule']              = 'Unico';
    $opts['args']              = [];
    $opts['args']['table']     = 'menu_langs';
    $opts['args']['confronto'] = 'menu_id';
    $opts['args']['escludi']   = $Obj->fields['id'];

    $Obj->addRule($opts);

    $Obj->parent = $_POST['genitore'];

    // CICLO LINGUE

    foreach($lingue as $sigla_lingua => $lingua_estesa) {

      $Obj->$sigla_lingua = ['dicitura' => $_POST[$sigla_lingua]['dicitura']];
      $Obj->$sigla_lingua = ['titolo_breve' => $_POST[$sigla_lingua]['titolo_breve']];

      $Obj->$sigla_lingua = ['keywords' => $_POST[$sigla_lingua]['keywords']];
      $Obj->$sigla_lingua = ['description' => $_POST[$sigla_lingua]['description']];
      $Obj->$sigla_lingua = ['img0_alt' => ''];
      $Obj->$sigla_lingua = ['img0_title' => ''];
      $Obj->$sigla_lingua = ['img1_alt' => ''];
      $Obj->$sigla_lingua = ['img1_title' => ''];
      $Obj->$sigla_lingua = ['htmltitle' => $_POST[$sigla_lingua]['htmltitle']];

      $dic = $_POST[$sigla_lingua]['dicitura'];
      if($operator->isAdvanced()) {
        if(!isset($_POST[$sigla_lingua]['href']) || !$_POST[$sigla_lingua]['href']) {
          if($dic) {
            $href_base = Utility::sanitize($dic);
            $href      = $href_base;
            $sql       = 'SELECT count(id) FROM '.$Obj->dataDescription['table_langs'].' WHERE lingua = "'.$sigla_lingua.'" AND href="'.$href.'"';
            $res       = $Obj->doQuery($sql);
            $row       = mysqli_fetch_row($res);
            $num       = array_pop($row);
            $i         = 0;
            while($num) {
              $i++;
              $href = $href_base.'-'.$num;
              $sql  = 'SELECT count(id) FROM '.$Obj->dataDescription['table_langs'].' WHERE lingua = "'.$sigla_lingua.'" AND href="'.$href.'"';
              $res  = $Obj->doQuery($sql);
              $row  = mysqli_fetch_row($res);
              $num  = array_pop($row);
            }
            $Obj->$sigla_lingua = ['href' => $href];
          }
        } else {
          $Obj->$sigla_lingua = ['href' => $_POST[$sigla_lingua]['href']];
        }
      } else {
        if(!isset($Obj->fields[$sigla_lingua]['href']) || !$Obj->fields[$sigla_lingua]['href']) {
          if($dic) {
            $href_base = Utility::sanitize($dic);
            $href      = $href_base;
            $sql       = 'SELECT count(id) FROM '.$Obj->dataDescription['table_langs'].' WHERE lingua = "'.$sigla_lingua.'" AND href="'.$href.'"';
            $res       = $Obj->doQuery($sql);
            $row       = mysqli_fetch_row($res);
            $num       = array_pop($row);
            $i         = 0;
            while($num) {
              $i++;
              $href = $href_base.'-'.$num;
              $sql  = 'SELECT count(id) FROM '.$Obj->dataDescription['table_langs'].' WHERE lingua = "'.$sigla_lingua.'" AND href="'.$href.'"';
              $res  = $Obj->doQuery($sql);
              $row  = mysqli_fetch_row($res);
              $num  = array_pop($row);
            }
            $Obj->$sigla_lingua = ['href' => $href];
          }
        }
      }

    }

    foreach($langs as $l) {
      if(isset($_POST[$l]['img0']['action']) && $_POST[$l]['img0']['action'] == 'del') {
        // se cancello l'immagine cancello anche alt e title che non riguardano quell'immagine
        $Obj->$l = ['img0_title' => ''];
        $Obj->$l = ['img0_alt' => ''];
        $Obj->$l = ['img0' => ['', false]];
      }
      if(isset($_POST[$l]['img1']['action']) && $_POST[$l]['img1']['action'] == 'del') {
        // se cancello l'immagine cancello anche alt e title che non riguardano quell'immagine
        $Obj->$l = ['img1_title' => ''];
        $Obj->$l = ['img1_alt' => ''];
        $Obj->$l = ['img1' => ['', false]];
      }
    }

    if(isset($_FILES) && count($_FILES)) {
      foreach($langs as $l) {
        if(isset($_FILES[$l]['name']['img0']) && $_FILES[$l]['size']['img0']) {
          $Obj->$l = ['img0' => [$_FILES[$l]['tmp_name']['img0'], $_FILES[$l]['name']['img0']]];
        }
        if(isset($_FILES[$l]['name']['img1']) && $_FILES[$l]['size']['img1']) {
          $Obj->$l = ['img1' => [$_FILES[$l]['tmp_name']['img1'], $_FILES[$l]['name']['img1']]];
        }
      }
    }

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

    break;

  case 'get_parents':

    $options                  = [];
    $options['tableFilename'] = 'menu';
    $options['files']         = ['img0', 'img1'];
    $options['debug']         = 0;
    $options['imgSettings']   = [];

    $imgSetting               = [];
    $imgSetting['dimensione'] = '24x24';
    $imgSetting['tipo']       = 'exact';

    $options['imgSettings'][] = $imgSetting;

    $Obj = new Menu($options);

    $opzioni                 = [];
    $opzioni['maxLevel']     = 90;
    $opzioni['minLevel']     = 0;
    $opzioni['parent']       = 0;
    $opzioni['debug']        = 0;
    $opzioni['exclude']      = $_POST['exclude'];
    $opzioni['utente']       = $operator;
    $opzioni['soloConFigli'] = 0;

    $data = $Obj->getArrayMenu($opzioni);

    $menu = [];

    $record['text']  = Traduzioni::getLang($module_name, 'LIVELLO_BASE');
    $record['href']  = '#node-0';
    $record['nodes'] = $data;

    $menu[] = $record;

    $ajaxReturn['result'] = 1;
    $ajaxReturn['data']   = $menu;

    break;

  case 'getlist':

    $Obj = new Menu($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['valore']    = '99';
    $filter_record['operatore'] = '<=';
    $filters[]                  = $filter_record;

    $opts               = [];
    $opts['raw']        = 1;
    $opts['fields']     = ['id', 'nomefile', 'attivo', 'pubdate'];
    $opts['langFields'] = ['dicitura'];
    $opts['debug']      = 0;
    $opts['countOnly']  = false;
    $opts['filters']    = $filters;
    $opts['operatore']  = 'AND';

    $lista = $Obj->getlist($opts);

    $opts['countOnly'] = true;

    $conta = $Obj->getlist($opts);

    $ajaxReturn['data']   = $lista;
    $ajaxReturn['result'] = 1;

    break;

  case 'switchvisibility':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new Menu($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      if($Obj) {

        $opts          = [];
        $opts['field'] = 'attivo';

        $Obj->toggle($opts);

      }

    }

    $ajaxReturn['result'] = 1;

    break;

  case 'new':

    $pathJS[]  = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.js';
    $pathCSS[] = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.css';

    $Obj = new Menu($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);

    } else {

      $smarty->assign('selezione', '');

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    /** FILES DI TEMPLATE **/
    $file_di_template = glob(SECTIONS_DIR.'*.tpl');
    $files            = [];
    $files[0]         = 'Non applicabile';
    foreach($file_di_template as $val) {
      $files[str_replace([SECTIONS_DIR, '.tpl'], '', $val)] = str_replace([SECTIONS_DIR, '.tpl'], '', $val);
    }
    $smarty->assign('files_di_template', $files);
    /** FILES DI TEMPLATE **/

    /** ALBERO - INIZIO **/

    if(isset($Obj->fields['parent']) && $Obj->fields['parent']) {

      $genitore       = [];
      $genitore['id'] = $Obj->fields['parent'];

      $c['open_nodes']   = [];
      $c['open_nodes'][] = 'm-'.$Obj->fields['parent'];
      while($Obj->fields['parent']) {

        $Obj                 = $Obj->getById($Obj->fields['parent']);
        $genitore['label'][] = $Obj->fields[ACTUAL_LANGUAGE]['dicitura'];

        $c['open_nodes'][] = 'm-'.$Obj->fields['parent'];

      }
      $c['selected_node'] = array_shift($c['open_nodes']);

      $genitore['label'] = array_reverse($genitore['label']);

      $genitore['label'] = 'Livello Base > '.implode(' > ', $genitore['label']);

    } else {

      $genitore          = [];
      $genitore['id']    = 0;
      $genitore['label'] = 'Livello Base';

      $c['open_nodes']    = [];
      $c['selected_node'] = 'm-0';

    }
    setcookie('tree', json_encode($c), false, '/');
    $smarty->assign('genitore', $genitore);

    /** ALBERO - FINE **/

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions = [];

    $attivoOptions[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $attivoOptions[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

    break;

  case 'del':

    if(Utility::isPositiveInt($_POST['id'])) {

      $Obj = new Menu($mainObjOptions);

      $Obj = $Obj->getById($_POST['id']);

      $Obj->delete();

      $ajaxReturn['result'] = 1;

    }

    break;

  case 'sort':

    $pathJS[]      = DOC_ROOT.REL_ROOT.'bower_components/jquery-nestable/jquery.nestable.js';
    $headerButtons = [$BUTTONS['btnSave'], $BUTTONS['btnClose']];
    $footerButtons = [$BUTTONS['btnSave'], $BUTTONS['btnClose']];

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'ORDINA'));

    $data = [];

    $opzioni             = [];
    $opzioni['parent']   = 0;
    $opzioni['maxLevel'] = '90';

    $record['tree'] = $Obj->getTreeMenu($opzioni);
    
    $data[] = $record;

    $smarty->assign('data', $data);
    break;

  case 'saveorder':

    $result = json_decode($_POST['neworder'], true);

    foreach($result as $group => $gerarchy) {

      $Obj = new Menu($mainObjOptions);

      $gerarchy = json_decode($gerarchy, true);

      $Obj->saveGerarchy($gerarchy);

    }


    $LOGGER->addLine(['text' => 'Ordine visualizzazione menu modificato', 'azione' => 'MODIFY:'.strtoupper($module_name)]);

    $ajaxReturn['result'] = 1;

    break;

  case '':

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    $headerButtons[] = $BUTTONS['btnSort'];
    $headerButtons[] = $BUTTONS['btnNew'];

    break;

  default:

    $urlParams = 'cmd/'.$cmd;

    header('Location:'.$lm->get($urlParams));

    die;
    break;

}
