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
use Ueppy\core\Menu;
use Ueppy\core\Db;

$mainObjOptions                  = [];
$mainObjOptions['tableFilename'] = 'menu';
$mainObjOptions['debug']         = 0;
$mainObjOptions['loadRules']     = 1;
$mainObjOptions['forceAllLang']  = 1;
$mainObjOptions['imgSettings']   = [];

$dimensioni            = [];
$dimensioni['prima']   = [];
$dimensioni['seconda'] = [];

$dim = explode('-', constant('SET_PAGINE_DIM_INLINEA'));

if(isset($dim[0])) {
  $dimensioni['prima'][] = $dim[0];
}
if(isset($dim[1])) {
  $dimensioni['seconda'][] = $dim[1];
}

if($dimensioni['prima']) {
  foreach($dimensioni['prima'] as $dim) {
    $imgSetting = [];
    if(strpos($dim, '|')) {
      $dim = explode('|', $dim);

      $imgSetting['tipo']                      = 'exact';
      $imgSetting['options']                   = [];
      $imgSetting['options']['type_of_resize'] = 'loss';

      if(isset($dim[0])) {
        $imgSetting['dimensione'] = $dim[0];
      }
      if(isset($dim[1])) {
        $imgSetting['tipo'] = $dim[1];
      }
      if(isset($dim[2])) {
        $imgSetting['options']['type_of_resize'] = $dim[2];
      }
    } else {
      if($dim == 'none') {
        $imgSetting['tipo'] = $dim;
      } else {
        $imgSetting['dimensione'] = $dim;
        $imgSetting['tipo']       = 'exact';
      }
    }
    $mainObjOptions['imgSettings']['img0'][] = $imgSetting;
  }
}

if($dimensioni['seconda']) {
  foreach($dimensioni['seconda'] as $dim) {
    $imgSetting = [];
    if(strpos($dim, '|')) {
      $dim = explode('|', $dim);

      $imgSetting['tipo']                      = 'exact';
      $imgSetting['options']                   = [];
      $imgSetting['options']['type_of_resize'] = 'loss';

      if(isset($dim[0])) {
        $imgSetting['dimensione'] = $dim[0];
      }
      if(isset($dim[1])) {
        $imgSetting['tipo'] = $dim[1];
      }
      if(isset($dim[2])) {
        $imgSetting['options']['type_of_resize'] = $dim[2];
      }
    } else {
      if($dim == 'none') {
        $imgSetting['tipo'] = $dim;
      } else {
        $imgSetting['dimensione'] = $dim;
        $imgSetting['tipo']       = 'exact';
      }
    }
    $mainObjOptions['imgSettings']['img1'][] = $imgSetting;
  }
}

$Obj = new Menu($mainObjOptions);

switch($act) {

  case 'insert':
    
    $array_esclusione_stringa = ['pagina'];

    if(Utility::isPositiveInt($_POST['id'])) {
      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);
    }

    if($operator->isSuperAdmin()) {
      $Obj->template = $_POST['template'];
      if(isset($_POST['attivo']) && $_POST['attivo']) {
        $Obj->attivo = 1;
      } else {
        $Obj->attivo = 0;
      }

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

    }

    $Obj->level = $_POST['level'];


    if($operator->isAdvanced()) {
      $Obj->robots = $_POST['robots'];
    }

    $Obj->parent = $_POST['genitore'];

    // CICLO LINGUE

    foreach($lingue as $sigla_lingua => $lingua_estesa) {

      $Obj->$sigla_lingua = ['dicitura' => $_POST[$sigla_lingua]['dicitura']];

      if($operator->isMedium()) {
        $Obj->$sigla_lingua = ['titolo_breve' => $_POST[$sigla_lingua]['titolo_breve']];
      }
      if($operator->isAdvanced()) {
        $Obj->$sigla_lingua = ['htmltitle' => $_POST[$sigla_lingua]['htmltitle']];
        $Obj->$sigla_lingua = ['description' => $_POST[$sigla_lingua]['description']];
        $Obj->$sigla_lingua = ['img0_alt' => $_POST[$sigla_lingua]['img0_alt']];
        $Obj->$sigla_lingua = ['img0_title' => $_POST[$sigla_lingua]['img0_title']];
        $Obj->$sigla_lingua = ['img1_alt' => $_POST[$sigla_lingua]['img1_alt']];
        $Obj->$sigla_lingua = ['img1_title' => $_POST[$sigla_lingua]['img1_title']];

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

  case 'getlist':

    $Obj = new Menu($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['valore']    = '99';
    $filter_record['operatore'] = '>=';
    $filters[]                  = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'nomefile';
    $filter_record['operatore'] = '!=';
    $filter_record['valore']    = 'pagina';

    $filters[] = $filter_record;


    $opts               = [];
    $opts['raw']        = 1;
    $opts['fields']     = ['id', 'nomefile', 'template', 'attivo', 'pubdate'];
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

  case 'seo':

    if($operator->isAdvanced()) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'SEO_TOOLS'));

      $headerButtons[] = $BUTTONS['btnDemo'];
      $headerButtons[] = $BUTTONS['btnClose'];

    } else {

      $urlParams = 'cmd/'.$cmd;

      header('Location:'.$lm->get($urlParams));

      die;

    }

    break;

  case 'getseo':
    /* ELENCO DEGLI ELEMENTI SEO */

    $Obj = new Menu($mainObjOptions);

    $filters = [];

    $filter_record              = [];
    $filter_record['chiave']    = 'level';
    $filter_record['valore']    = '100';
    $filter_record['operatore'] = '>=';
    $filters[]                  = $filter_record;

    $filter_record              = [];
    $filter_record['chiave']    = 'nomefile';
    $filter_record['operatore'] = '!=';
    $filter_record['valore']    = 'pagina';

    $filters[] = $filter_record;

    $opts = [];

    $opts['forceAllLang'] = 1;
    $opts['countOnly']    = false;
    $opts['filters']      = $filters;
    $opts['operatore']    = 'AND';

    $lista = $Obj->getlist($opts);
    $list  = [];

    foreach($lista as $Obj) {

      foreach($Obj->opts['langs'] as $lang) {

        $record                = [];
        $record['id']          = $Obj->fields['id'];
        $record['lingua']      = $lingue[$lang];
        $record['lang']        = $lang;
        $record['titolo']      = $Obj->fields[$lang]['dicitura'];
        $record['htmltitle']   = $Obj->fields[$lang]['htmltitle'];
        $record['description'] = $Obj->fields[$lang]['description'];
        $list[]                = $record;

      }

    }


    $ajaxReturn['data']   = $list;
    $ajaxReturn['result'] = 1;

    break;

  case 'save_seo_key':

    if($operator->isAdvanced()) {

      $Obj = new Menu($mainObjOptions);

      $opts                 = [];
      $opts['forceAllLang'] = 1;

      $Obj = $Obj->getById($_POST['id'], $opts);

      if($Obj) {

        if(in_array($_POST['lang'], $langs)) {

          $lang = $_POST['lang'];

          if(in_array($_POST['type'], ['htmltitle', 'description'])) {
            $Obj->$lang = [$_POST['type'] => $_POST['value']];
            $Obj->save();
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

    } else {
      $ajaxReturn['result'] = 0;
      if(isset($operator) && $operator->isSuperAdmin()) {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' ('.__FILE__.','.__LINE__.')';
      } else {
        $ajaxReturn['error'] = Traduzioni::getLang('default', 'BAD_PARAMS').' (Error: '.__LINE__.')';
      }
    }


    break;

  case 'switchvisibility':

    if($operator->isSuperAdmin()) {

      if(Utility::isPositiveInt($_POST['id'])) {

        $Obj = new Menu($mainObjOptions);

        $opts = ['forceAllLang' => true];

        $Obj = $Obj->getById($_POST['id'], $opts);

        if($Obj) {

          $opts          = [];
          $opts['field'] = 'attivo';

          $Obj->toggle($opts);

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

  case 'new':

    $pathJS[]  = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.js';
    $pathCSS[] = DOC_ROOT.REL_ROOT.'bower_components/bootstrap-treeview/dist/bootstrap-treeview.min.css';

    /** ATTIVO OPTIONS - INIZIO **/

    $attivoOptions = [];

    $attivoOptions[0] = Traduzioni::getLang('default', 'NO_ANSWER');
    $attivoOptions[1] = Traduzioni::getLang('default', 'SI_ANSWER');

    $smarty->assign('attivoOptions', $attivoOptions);

    /** ATTIVO OPTIONS - FINE   **/

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

    /* POSIZIONI - INIZIO */

    $posizioni = [];

    $posizioni[99]  = Traduzioni::getLang($module_name, 'MENU_RISERVATO');
    $posizioni[100] = Traduzioni::getLang($module_name, 'MENU_TOP');
    $posizioni[105] = Traduzioni::getLang($module_name, 'MENU_MIDDLE');
    $posizioni[110] = Traduzioni::getLang($module_name, 'MENU_BOTTOM');
    $posizioni[200] = Traduzioni::getLang($module_name, 'FUORI_MENU');

    $smarty->assign('list_posizione', $posizioni);

    /* POSIZIONI - FINE   */

    $Obj = new Menu($mainObjOptions);

    if(Utility::isPositiveInt($_POST['id'])) {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'MODIFY'));

      $opts                 = [];
      $opts['forceAllLang'] = 1;
      $Obj                  = $Obj->getById($_POST['id'], $opts);

    } else {

      $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'INSERT'));

    }

    $smarty->assign('Obj', $Obj);

    $NUMERO_IMMAGINI_PAGINE = intval(constant('SET_NUMERO_IMMAGINI_PAGINE'));

    $smarty->assign('NUMERO_IMMAGINI_PAGINE', $NUMERO_IMMAGINI_PAGINE);

    /** FILES DI TEMPLATE **/
    $file_di_template = glob(SECTIONS_DIR.'*.tpl');
    $files            = [];
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

    /** ROBOTS **/

    $robots_options                     = [];
    $robots_options['index,follow']     = 'index,follow';
    $robots_options['index,nofollow']   = 'index,nofollow';
    $robots_options['noindex,follow']   = 'noindex,follow';
    $robots_options['noindex,nofollow'] = 'noindex,nofollow';

    $smarty->assign('robots_options', $robots_options);

    $headerButtons = $BUTTONS['set']['save-close-new'];
    $footerButtons = $BUTTONS['set']['save-close'];

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
    $opzioni['maxLevel']     = 200;
    $opzioni['minLevel']     = 100;
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

  case 'del':

    if($operator->isSuperAdmin()) {
      if(Utility::isPositiveInt($_POST['id'])) {

        $Obj = new Menu($mainObjOptions);

        $Obj = $Obj->getById($_POST['id']);

        $Obj->delete();

        $ajaxReturn['result'] = 1;

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

  case 'sort':

    $pathJS[] = DOC_ROOT.REL_ROOT.'bower_components/jquery-nestable/jquery.nestable.js';

    $headerButtons[] = $BUTTONS['btnSave'];
    $headerButtons[] = $BUTTONS['btnClose'];
    $footerButtons[] = $BUTTONS['btnSave'];
    $footerButtons[] = $BUTTONS['btnClose'];

    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'ORDINA'));

    $posizioni = [];

    $posizioni[99]  = Traduzioni::getLang($module_name, 'MENU_RISERVATO');
    $posizioni[100] = Traduzioni::getLang($module_name, 'MENU_TOP');
    $posizioni[105] = Traduzioni::getLang($module_name, 'MENU_MIDDLE');
    $posizioni[110] = Traduzioni::getLang($module_name, 'MENU_BOTTOM');
    $posizioni[200] = Traduzioni::getLang($module_name, 'FUORI_MENU');

    $sql = 'SELECT DISTINCT(level) FROM menu WHERE level > 90 ORDER BY level ASC';
    $db  = new Db();
    $db->connect();
    $res = $db->doQuery($sql);

    $data = [];

    $group = 1;
    while($row = mysqli_fetch_row($res)) {

      $record['level'] = 'Menu di livello : '.$row[0];
      $record['group'] = $group;
      if(isset($posizioni[$row[0]])) {
        $record['level'] = $posizioni[$row[0]];
      }
      $opzioni             = [];
      $opzioni['maxLevel'] = $row[0];
      $opzioni['minLevel'] = $row[0];

      $record['tree'] = $Obj->getTreeMenu($opzioni);

      $data[] = $record;

      $group++;

    }

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

  case  '':
    $smarty->assign('titoloSezione', Traduzioni::getLang($module_name, 'TITLE').' - '.Traduzioni::getLang('default', 'LIST_ELEMENTS'));

    if($operator->isAdvanced()) {
      $headerButtons[] = $BUTTONS['btnSeo'];
      $headerButtons[] = $BUTTONS['btnSort'];
    }
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
